<?php

/**
 *
 * @package    mod
 * @subpackage bacs
 */

//Проверить все isset!!!

// HEADER START BOOTSTRAP

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$b  = optional_param('b', 0, PARAM_INT);  // bacs instance ID - it should be named as the first character of the module
$edit  = optional_param('edit', 0, PARAM_BOOL); // Edit contest mode


if ($id) {
    $CM         = get_coursemodule_from_id('bacs', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $CM->course), '*', MUST_EXIST);
    $bacs  = $DB->get_record('bacs', array('id' => $CM->instance), '*', MUST_EXIST);
} elseif ($b) {
    $bacs  = $DB->get_record('bacs', array('id' => $b), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $bacs->course), '*', MUST_EXIST);
    $CM         = get_coursemodule_from_instance('bacs', $bacs->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $CM);
$context = context_module::instance($CM->id);

//add_to_log($course->id, 'bacs', 'view', "monitor.php?id={$CM->id}", $bacs->name, $CM->id);

/// Print the page header

$PAGE->set_url('/mod/bacs/monitor.php', array('id' => $CM->id));
$PAGE->set_title(format_string($bacs->name));

//$PAGE->requires->css('/mod/bacs/bootstrap/css/docs.min.css');
//$PAGE->requires->css('/mod/bacs/bootstrap/css/common.css');
//$PAGE->requires->css('/mod/bacs/bootstrap/css/bootstrap.min.css');

//$PAGE->requires->js('/mod/bacs/test_www/bootstrap/js/jquery-2.2.2.js', true);
//$PAGE->requires->js('/mod/bacs/test_www/bootstrap/js/production.js', true);
//$PAGE->requires->js('/mod/bacs/test_www/bootstrap/js/font.js', true);
$PAGE->requires->js('/mod/bacs/test_www/bootstrap/js/common.js', true);

$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
// Output starts here
echo $OUTPUT->header();
echo $OUTPUT->heading($bacs->name);
$PAGE->navbar->ignore_active();
$PAGE->navbar->add('preview', new moodle_url('/a/link/if/you/want/one.php'));
$PAGE->navbar->add('name of thing', new moodle_url('/a/link/if/you/want/one.php'));

// HEADER END BOOTSTRAP

//ИЩЕМ КОНТЕСТ
$now = time();
//if (!((int)$_GET['c_id']))
	$id = (int)$bacs->id;
//else
//	$id = (int)$_GET['c_id'];
	
$result = $DB->get_record_select('bacs', "starttime < $now AND id = $id", array($params = null), 'id, name');
//!!!Костыль: проверить на корректость проверки объекта
if (!is_object($result)) {
    if ($DB->count_records('bacs', array('id' => $id)) == 0)
        die('Not found! / Контест не найден');
    else
        die('Contest has not started / Контест ещё не начался.');
}
$cid = $result->id;
$cname = $result->name;

//ПОЛЬЗОВАТЕЛЬ
//(int)$_GET['user_id'];
//echo $USER->id;

//СТАТУС КОНТЕСТА
$starttime = $bacs->starttime;
$endtime = $bacs->endtime;
$runtime = (time() - $starttime)/60;
$tottime = ($endtime - $starttime)/60;
$status = 0;
//if ($freeze && ($runtime > $freeze) && (!$unfreeze || ($runtime < $unfreeze) )) $status = 1;
if ($status == 0 && $runtime < 0) $status = -1;
if ($status == 0 && $runtime > $tottime) $status = 2;
if ($runtime < 0) $runtime = 0;
if ($runtime > $tottime) $runtime = $tottime;
$runtime = (int)$runtime;
$tottime = (int)$tottime;
switch ($status) {
    case -1: $statustext = "Not started"; break;
    case 0: $statustext = "Running"; break;
    case 1: $statustext = "Frozen"; break;
    case 2: $statustext = "Over"; break;
}
echo "Time: <b>$runtime</b> of <b>$tottime</b>. Status: <b>$statustext</b>.<br>";

function echo_r($str) {
    echo "<pre>".$str."</pre>";
}

function get_my_groups() {
    $my_groups = groups_get_my_groups();
    $group = array(array());
    foreach ($my_groups as $msg) {
        $group['id'][] = $msg->id;
        $group['name'][] = $msg->name;
    }
    return $group;
}

function menu() {
    GLOBAL $CM;
    $link = optional_param('link', 0, PARAM_ALPHANUM);
    if (is_null($link) OR $link == "") {
        $link = 'view';
    }
    /*
    $menuItems = array(
        'view' => '<span class="glyphicon glyphicon-stats"></span> Монитор',
        'tasks' => '<span class="glyphicon glyphicon-th-list"></span> Список задач',
        'results' => '<span class="glyphicon glyphicon-tasks"></span> Мои посылки',
        'points' => '<span class="glyphicon glyphicon-cog"></span>'
    );
    */
    $menuItems = array(
        'view' => '<i class="icon-home"></i></span> Монитор',
        'tasks' => '<i class="icon-th-list"></i></span> Список задач',
        'results' => '<i class="icon-th"></i></span> Мои посылки',
        'points' => '<i class="icon-cog"></i></span>'
    );
    $msg = '<ul class="nav nav-tabs">';
    foreach($menuItems as $menuItemId => $menuItem) {
        $msg .= '<li';
        $msg .= ($menuItemId == $link ? ' class="active"':'');
        $msg .= '><a href="'.$menuItemId.'.php?id='.$CM->id.'&link='.$menuItemId.'">'.$menuItem.'</a></li>';
    }
    // TODO
    /*
    $msg .= '<li class="dropdown">
        <a href="#" data-toggle="dropdown" class="dropdown-toggle"><span class="glyphicon glyphicon-cog"></span> Настройки<b class="caret"></b></a>
        <ul class="dropdown-menu">
            <li><a href="#">Монитор</a></li>
            <li><a href="#">Задачи</a></li>
            <li class="divider"></li>
            <li><a href="#">Другие</a></li>
        </ul>
    </li>';

    $msg = '<li class="dropdown">'
    .'<a class="dropdown-toggle"'
      .' data-toggle="dropdown"'
       .'href="#">Dropdown<b class="caret"></b>'
      .'</a>'
    .'<ul class="dropdown-menu">'
      .'<div>test</div>'
    .'</ul>'
  .'</li>';
    */
        $groups = get_my_groups();
        if ($groups) {
            $msg .= '<li class="dropdown"';
            $msg .= ($menuItemId == $link ? ' class="active"':'');
            $msg .= '><a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="icon-user"></i></span> Первая группа<b class="caret"></b></a>'
                    .'<ul class="dropdown-menu">';
            foreach ($groups['name'] as $groups['id'] => $group) {
                $msg .= '<li><a href="#">'.$group.'</a></li>';
            }
            $msg .= '<li class="divider"></li><li><a href="#">Все группы</a></li></ul></li>';
        }
    //$msg .= '</ul>';
    //$msg = '<ul class="nav" id="yui_3_17_2_1_1474794429854_585"><li class="dropdown langmenu open" id="yui_3_17_2_1_1474794429854_584"><a href="#" class="dropdown-toggle" data-toggle="dropdown" title="Язык" id="yui_3_17_2_1_1474794429854_583">Русский &lrm;(ru)&lrm;<b class="caret"></b></a><ul class="dropdown-menu"><li><a title="Русский &lrm;(ru)&lrm;" href="http://172.16.0.2/mod/bacs/view.php?id=2&amp;lang=ru">Русский &lrm;(ru)&lrm;</a></li><li><a title="English &lrm;(en)&lrm;" href="http://172.16.0.2/mod/bacs/view.php?id=2&amp;lang=en">English &lrm;(en)&lrm;</a></li></ul></li></ul>';
    return $msg;
}
//Меню
print menu();

/**/
function bacs_write_task_grade($str) {    
    //$str = trim($str);
    //str_replace(' ','',$str);
    if ($str{strlen($str) - 1} == ';') {
        $str = substr($str, 0, strlen($str) - 1);
    }
    $sub = substr($str, strripos($str, ';') + 1);
    $sub = substr($sub, 0, strripos($sub, ','));
    if (strripos($sub, '-')) {
        $sub = substr($sub, strripos($sub, '-') + 1);
    }
    $kt = (int)$sub;
    for($i = 1; $i <= $kt; $i++) {
        $result[$i] = 0;
    }
    $str_array = explode(';', $str);
    foreach($str_array as $val) {
        $pstr_array = explode(',', $val);
        $ntest_array = explode('-', $pstr_array[0]);
        // $pstr_array[1]-кол-во баллов за тест
        // $pstr_array[0]-номера тестов
        if (isset($ntest_array[1])) {
            for ($i = $ntest_array[0]; $i <= $ntest_array[1]; $i++) {
                $result[$i] = $pstr_array[1];
            }
        } else {
            $result[$pstr_array[0]] = $pstr_array[1];
        }
    }
    $result = array_map('intval', $result);
    $result = implode($result, ',');
    return $result;
}

function bacs_write_task_grade_format($str) {    
    $result = "";
    $str_array = explode(',', $str);
    $str_array[] = -1;
    $j = 0;
    $n = 0;
    for ($i = 0; $i < count($str_array) - 1; $i++) {
        if ($str_array[$i] == $str_array[$i + 1]) {
            $j++;
        } else {
            $n = $i + 1;
            if ($j > 0) {
                $result = $result.(string)($n - $j).'-'.(string)$n.','.$str_array[$i].';';
                $j = 0;
            } else {
                $result = $result.(string)($n - $j).','.$str_array[$i].';';
            }
        }
    }
    return $result;
}
/*
echo bacs_write_task_grade("1,0;2,0;3-4,5;5-7,10");

echo_r(bacs_write_task_grade_format("0,1,1,1,1"));

exit;
*/
//ПРОВЕРКА НА АДМИНА

//ПРОВЕРКА ПРАВ НА ПРОСМОТР КОНТЕСТА
$post_aid = array_map('intval', $_POST['aid']);
if (isset($post_aid) && ($post_aid != "")) {
    $tasks_to_contest = $DB->get_records('bacs_tasks_to_contests', array('contest_id' => $bacs->id), 'task_order ASC', 'id, task_id, contest_id, task_order, test_points');
    $list_task_to_contest = array();
    foreach ($tasks_to_contest as $record1) {
        $list_task_to_contest[$record1->task_id] = $record1->id;
        $list_task_to_id[$record1->task_order] = $record1->task_id;
        $list_custom_points[$record1->task_order] = (($record1->test_points) == "") ? FALSE : TRUE;
        $list_points[$record1->task_order] = $record1->test_points;
    }
    for ($i = 0; $i < count($post_aid); $i++) {
        $msg = bacs_write_task_grade($_POST['data'][$i]);
        $rec = (int)$DB->get_records('bacs_tasks', array('id' => $list_task_to_id[$i], 'test_points' => $msg));
        $tasks_points = $DB->get_record('bacs_tasks', array('id' => $list_task_to_id[$i]));

        if (!$rec) {
            $record = new stdClass();
            $record->id = $list_task_to_contest[$post_aid[$i]];
            //$record->task_id = $list_task_to_id[$i];
            //$record->conest_id = $bacs->id;
            //$record->task_order = (string)$i;
            $record->test_points = $msg;
            $lastinsertid = $DB->update_record_raw('bacs_tasks_to_contests', $record, false);
        } else {
            if ($list_custom_points[$i]) {
                $record = new stdClass();
                $record->id = $list_task_to_contest[$post_aid[$i]];
                //$record->task_id = $list_task_to_id[$i];
                //$record->conest_id = $bacs->id;
                //$record->task_order = (string)$i;
                $record->test_points = NULL;
                $lastinsertid = $DB->update_record_raw('bacs_tasks_to_contests', $record, false); 
            }
        }
        //Пересчитываем баллы
        if ($list_custom_points[$i] || !$rec) {
            $point = explode(",", $msg);
            // TODO Переделать в list запрос
            $db_assign = $DB->get_records('bacs_submits', array('contest_id' => $bacs->id, 'task_id' => $list_task_to_id[$i]), 'task_id ASC', 'id, task_id');
            foreach ($db_assign as $assign) {   
                $db_points = $DB->get_record('bacs_tasks', array('id' => $assign->task_id), 'test_points', MUST_EXIST);
                $db_status = $DB->get_records('bacs_submits_tests', array('submit_id' => $assign->id), 'test_id ASC', 'test_id, status_id');
                $pretest = true;
                $pretest_failed = false;
                $failed = false;
                $j = 0;
                $points = (int)0;
                foreach ($db_status as $status) {
                    $record = new stdClass();
                    $record->id = $assign->id;
                    if (!$pretest_failed) {
                        if ($pretest && $point[$j] != 0) {
                            $pretest = false;
                        }
                        if ($status == 0) {
                            $points += $point[$j];
                        } else {
                            if (!$failed) {
                                $failed = true;
                                $test_num_failed = $status->test_id;
                            }
                            if ($pretest) {
                                $pretest_failed = true;
                                $point = 0;
                            }
                        }
                    }
                    $record->points = $points;
                    $records[] = $record;
                    $j++;
                }
//                //    var_dump($points);
//                //    exit;                
                //$lastinsertid = $DB->update_record_raw('bacs_submits', $records, false);
            }
        }
    }
}
/*
    //Для пересчёт баллов
    function points($a, $b) {
        if (!is_null($a)) {
            return $a;
        } else {
            return $b;
        }
    }
 
    $c = array_map("points", $a, $b);
    var_dump($c);
*/
/*
$post_aid = array_map('intval', $_POST['aid']);
if (isset($post_aid) && ($post_aid != "")) {
    for ($i = 0; $i < count($post_aid); $i++) {
        $record = new stdClass();
        $record->id = $post_aid[$i];
        $record->test_points = bacs_write_task_grade($_POST['data'][$i]);;
        $lastinsertid = $DB->update_record_raw('bacs_tasks', $record, false);
    }
}
*/

//Задачки

$table = new html_table();
$header = array();
$header[] = 'N';
$header[] = 'Название задачи';
$header[] = 'Тестов';
$header[] = 'Баллы за тесты';
$header[] = 'Итого баллов';
$header[] = 'Номера проверочных тестов';
$table->size  = array('32px', '');

$task_ids = $DB->get_records('bacs_tasks_to_contests', array('contest_id' => $bacs->id), 'task_order ASC', 'task_id, contest_id, task_order, test_points');
$aid = 'A';
$task_list = array();
foreach ($task_ids as $task_id){
    $pretest = true;
    $task = $DB->get_record('bacs_tasks', array('id' => $task_id->task_id), 'name, time_limit_millis, memory_limit_bytes, test_points, statement_url', IGNORE_MISSING);
    $task_list[] = $task_id->task_id;
    $cells = array();
    $cells[] = $aid;
    $cells[] = htmlspecialchars($task->name);
    if ($task_id->test_points != NULL) {
        $point_string = $task_id->test_points;
    } else {
        $point_string = $task->test_points;
    }
    $point = array_map('intval', explode(",", $point_string));
    $count_point = count($point);
    $cells[] = (int)$count_point;
    $cells[] = '<input id=data[] class="form-control" type="text" name=data[] value="'.htmlspecialchars(bacs_write_task_grade_format($point_string)).'"></input>';
    $msg = "";
    $sum_points = 0;
    for ($i = 0; $i < $count_point; $i++) {
        if ($pretest && ($point[$i] == 0)) {
            $msg .= (int)($i + 1).", ";
        } else {
            $pretest = false;
        }
        $sum_points += $point[$i];
    }
    $cells[] = (int)$sum_points;
    $substr_msg = substr($msg, 0, strlen($mes) - 2);
    if (!($msg == "")) {
        $cells[] = $substr_msg;
    } else {
        $cells[] = "-";
    }
    $aid = chr(ord($aid)+1);
    $table->data[] = $cells;
}

$table->head = $header;

echo '<form enctype="multipart/form-data" action="points.php?id='.$CM->id.'" method="POST" role="form" class="form-inline">';
//echo '<div class="form-group">';
echo html_writer::table($table);
foreach($task_list as $val) { 
      echo '<input type="hidden" name=aid[] value="'.$val.'">'; 
} 
echo '<button type="submit" class="btn btn-success">Сохранить</button>';
//echo '</div>';
echo '</form><div name="dt" id="dt">Текущее время: 2016-10-04 17:03:21</div>';

$userdate = usergetdate(time());
var_dump($userdate);
echo "<script>

    var current_date = new Date(".$userdate['year']." , ".$userdate['mon'].", ".$userdate['mday'].", ".$userdate['hours'].", ".$userdate['minutes'].", ".$userdate['seconds'].");

    function uptime(){
        var target = document.getElementById('dt');
        current_date.setSeconds(current_date.getSeconds() + 1);
        target.innerHTML = current_date;
        setTimeout('uptime()', 1000);
    }
    uptime();
    </script>";

echo $OUTPUT->footer();
