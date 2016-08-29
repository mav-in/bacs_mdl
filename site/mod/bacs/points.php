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
    $cm         = get_coursemodule_from_id('bacs', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $bacs  = $DB->get_record('bacs', array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($b) {
    $bacs  = $DB->get_record('bacs', array('id' => $b), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $bacs->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('bacs', $bacs->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

//add_to_log($course->id, 'bacs', 'view', "monitor.php?id={$cm->id}", $bacs->name, $cm->id);

/// Print the page header

$PAGE->set_url('/mod/bacs/monitor.php', array('id' => $cm->id));
$PAGE->set_title(format_string($bacs->name));

$PAGE->requires->css('/mod/bacs/bootstrap/css/docs.min.css');
$PAGE->requires->css('/mod/bacs/bootstrap/css/common.css');
$PAGE->requires->css('/mod/bacs/bootstrap/css/bootstrap.min.css');

$PAGE->requires->js('/mod/bacs/test_www/bootstrap/js/jquery-2.2.2.js', true);
$PAGE->requires->js('/mod/bacs/test_www/bootstrap/js/production.js', true);
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
/*
$post_aid = array_map('intval', $_POST['aid']);
if (isset($post_aid) && ($post_aid != "")) {
    for ($i = 0; $i < count($post_aid); $i++) {
        $msg = bacs_write_task_grade($_POST['data'][$i]);
        $rec = $DB->count_records('bacs_tasks', array('id' => 1, 'test_points' => $msg));
        if ($rec) {
            $record = new stdClass();
            $record->task_id = $post_aid[$i];
            $record->conest_id = 1;
            $record->test_points = $msg;
            $lastinsertid = $DB->update_record_raw('bacs_tasks_to_contests', $record, false);
        }
        var_dump($rec);
    }
}
*/

 $post_aid = array_map('intval', $_POST['aid']);
if (isset($post_aid) && ($post_aid != "")) {
    for ($i = 0; $i < count($post_aid); $i++) {
        $record = new stdClass();
        $record->id = $post_aid[$i];
        $record->test_points = bacs_write_task_grade($_POST['data'][$i]);;
        $lastinsertid = $DB->update_record_raw('bacs_tasks', $record, false);
    }
}
 

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

$task_ids = $DB->get_records('bacs_tasks_to_contests', array('contest_id' => $bacs->id), 'task_order ASC', 'task_id, contest_id, task_order');
$aid = 'A';
$task_list = array();
foreach ($task_ids as $task_id){
    $task = $DB->get_record('bacs_tasks', array('id' => $task_id->task_id), 'name, time_limit_millis, memory_limit_bytes, test_points, statement_url', IGNORE_MISSING);
    $task_list[] = $task_id->task_id;
    $cells = array();
    $cells[] = $aid;
    $cells[] = htmlspecialchars($task->name);
    $point = array_map('intval', explode(",", $task->test_points));
    $count_point = count($point);
    $cells[] = (int)$count_point;
    $cells[] = '<input id=data[] class="form-control" type="text" name=data[] value="'.htmlspecialchars(bacs_write_task_grade_format($task->test_points)).'"></input>';
    $msg = "";
    $sum_points = 0;
    for ($i = 0; $i < $count_point; $i++) {
        if ($point[$i] == 0) {
            $msg .= (int)($i + 1).", ";
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

echo '<form enctype="multipart/form-data" action="points.php?id='.$cm->id.'" method="POST" role="form" class="form-inline">';
//echo '<div class="form-group">';
echo html_writer::table($table);
foreach($task_list as $val) { 
      echo '<input type="hidden" name=aid[] value="'.$val.'">'; 
} 
echo '<button type="submit" class="btn btn-success">Сохранить</button>';
//echo '</div>';
echo '</form>';

echo $OUTPUT->footer();
