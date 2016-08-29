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

$PAGE->set_url('/mod/bacs/results.php', array('id' => $CM->id));
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

//ПРОВЕРКА НА АДМИНА

//ПРОВЕРКА ПРАВ НА ПРОСМОТР КОНТЕСТА

//Задачки

function menu() {
    GLOBAL $CM;
    $link = optional_param('link', 0, PARAM_ALPHANUM);
    if (is_null($link)) {
        $link = 'view';
    }
    $menuItems = array(
        'view' => '<span class="glyphicon glyphicon-stats"></span> Монитор',
        'tasks' => '<span class="glyphicon glyphicon-th-list"></span> Список задач',
        'results' => '<span class="glyphicon glyphicon-tasks"></span> Мои посылки'
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
    */
    $msg .= '</ul>';
    return $msg;
}
//Меню
print menu();

print '<div class="panel-group" id="collapse-group">
<!--Панель 1 #####################################################################################-->
    <div class="panel panel-default">
        <div class="panel-heading">
            <div class="row">
                <div class="col-xs-1 col-lg-1">№</div>
                <div class="col-xs-1 col-lg-1">ID</div>
                <div class="col-xs-2 col-lg-2">Подробно</div>
                <div class="col-xs-2 col-lg-2">Баллы</div>
                <!-- Optional: clear the XS cols if their content doesnt match in height -->
                <!--<div class="clearfix visible-xs-block"></div>-->
                <div class="col-xs-3 col-lg-4">Результат</div>
                <div class="col-xs-3 col-lg-2">Отправлено</div>
            </div>
        </div>
    </div>';

///---

function i_to_aid ($task_aid_num){
    $msg = chr($task_aid_num % 26 + 65);
    $num = (int)($task_aid_num / 26);
    if ($num > 0) {
        $msg .= $num;
    }
    return $msg;
}

$verdict = array('Unknown',
'Pending',
'Running',
'ServerError',
'CompileError',
'RuntimeError',
'FailTest',
'CPUTimeLimitExceeded',
'RealTimeLimitExceeded',
'MemoryLimitExceeded',
'OutputLimitExceeded',
'PresentationError',
'WrongAnswer',
'Accepted',
'IncorrectRequest',
'InsufficientData',
'QueriesLimitExceeded',
'ExcessData');

$task_ids = $DB->get_records('bacs_submits', array('contest_id' => $bacs->id, 'user_id' => $USER->id), 'submit_time DESC');
$task_tasks = $DB->get_records('bacs_tasks_to_contests', array('contest_id' => 1), 'task_id ASC');
$data = array();
foreach ($task_tasks as $task_id){
    $data[$task_id->task_id] = (int)$task_id->task_order;
}
$i = 0;
$date = "";
foreach ($task_ids as $task_id){
    $row_id = 'el'.$i++;
    if ($date != userdate($task_id->submit_time,"%d %B %Y (%A)")) {
        print '<div>'.userdate($task_id->submit_time,"%d %B %Y (%A)").'</div>';
    }
    print '
    <!--Панель A #####################################################################################-->
        <div class="panel panel-default">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-1 col-lg-1"><a data-toggle="collapse" data-parent="#collapse-group" href="#'.$row_id.'">N</a></div>
                    <div class="col-xs-1 col-lg-1"><a data-toggle="collapse" data-parent="#collapse-group" href="#'.$row_id.'">'.i_to_aid($data[$task_id->task_id]).'</a></div>
                    <!-- Optional: clear the XS cols if their content doesnt match in height -->
                    <!--<div class="clearfix visible-xs-block"></div>-->
                    <div class="col-xs-2 col-lg-2"></div>
                    <div class="col-xs-2 col-lg-2">'.$task_id->points.'</div>
                    <div class="col-xs-3 col-lg-4">'.$verdict[$task_id->result_id].'</div>
                    <div class="col-xs-3 col-lg-2">'.userdate($task_id->submit_time,"%H:%M:%S").'</div>
                </div>
            </div>
            <!--clas="in" - show-->
            <div id="'.$row_id.'" class="panel-collapse collapse">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="well bs-component">';
  
                        $db_points = $DB->get_records('bacs_submits_tests', array('submit_id' => $task_id->id), 'id ASC');
                        foreach ($db_points as $record){    
                            echo $record->status_id.'</br>';
                        }
                        
                     print '</div>
                        </div>

                        <div class="col-lg-6">
                            <div class="well bs-component">
                                <form class="form-horizontal">
                                    <fieldset>
                                        <div class="form-group">
                                            <div url-show="#" class="show_docs" onclick="showDocs(this); return false;">
                                            <span><center>Посмотреть решение</center></span>
                                                <div class="file_block" style="height: 0px;">
                                                    <textarea>Решение</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </fieldset>
                                </form>
                            </div>
                        </div>				  
                    </div>
                </div>
            </div>
        </div>';
    $date = userdate($task_id->submit_time,"%d %B %Y (%A)");
}
print '</div>';

echo $OUTPUT->footer();
