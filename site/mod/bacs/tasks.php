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

print '
<div class="panel-group" id="collapse-group">
<!--Панель 1 #####################################################################################-->
    <div class="panel panel-default">
        <div class="panel-heading">
            <div class="row">
                <div class="col-xs-1 col-lg-1">ID</div>
                <div class="col-xs-4 col-lg-6">Название задачи</div>
                <!-- Optional: clear the XS cols if their content doesnt match in height -->
                <!--<div class="clearfix visible-xs-block"></div>-->
                <div class="col-xs-3 col-lg-3">Условие</div>
                <div class="col-xs-2 col-lg-1">Время</div>
                <div class="col-xs-2 col-lg-1">Память</div>
            </div>
        </div>
    </div>';

///---
//$results = $DB->get_records_select('bacs_langs','');

//$LANGUAGES = array('C++'=>'+', 'PASCAL'=>'P', 'C'=>'C', 'Java'=>'J', 'Python 3'=>'T');
$res = $DB->get_records('bacs_langs', null, null, 'id, name');

//function bacs_print_task_send_form($bacs,$taskId){
    //global $LANGUAGES;
    $task_select='';
    $lang_select='';
    foreach ($res as $mes){
        $lang_select .= "<option value='$mes->id'>$mes->name\n";
    }
    //foreach($results as $result) {
    //	$lang_select.="<option value='format_string($result->langs_id)'>format_string($result->$str)\n";
    //}
    //$task_ids = $DB->get_records("bacs_m2m", "contest_id", $bacs->contest_id,'task_order,task_id');
    //$task_ids = $DB->get_records('bacs_tasks_to_contests', array('contest_id' => $bacs->id), 'task_order, task_id');
    $task_ids = $DB->get_records('bacs_tasks_to_contests', array('contest_id' => $bacs->id), 'task_order ASC', 'task_id, contest_id, task_order');
    $aid='A';
    $i=0;
    foreach ($task_ids as $task_id){
        $task = $DB->get_record('bacs_tasks', array('id' => $task_id->task_id), 'name, time_limit_millis, memory_limit_bytes, statement_url', IGNORE_MISSING);
        /*
        if ($task->task_id == $taskId)
            $sel='selected';
        else
        */
            $sel='';
        $task_select.="<option value='$task_id->task_id' $sel>$aid. $task->name\n";
        $time = (int)($task->time_limit_millis / 1000);
        $memory = (int)($task->memory_limit_bytes / 1048576);
        $row_id = 'el'.$i++;
        print '
        <!--Панель A #####################################################################################-->
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-1 col-lg-1"><a data-toggle="collapse" data-parent="#collapse-group" href="#'.$row_id.'">'.$aid.'</a></div>
                        <div class="col-xs-4 col-lg-6"><a data-toggle="collapse" data-parent="#collapse-group" href="#'.$row_id.'">'.$task->name.'</a></div>
                        <!-- Optional: clear the XS cols if their content doesnt match in height -->
                        <!--<div class="clearfix visible-xs-block"></div>-->
                        <div class="col-xs-3 col-lg-3"><a href="http://docs.google.com/viewer?url='.$task->statement_url.'" target="_blank">Открыть</a> / <a href="'.$task->statement_url.'" target="_blank">Скачать</a></div>
                        <div class="col-xs-2 col-lg-1">'.$time.'</div>
                        <div class="col-xs-2 col-lg-1">'.$memory.'</div>
                    </div>
                </div>
                <!--clas="in" - show-->
                <div id="'.$row_id.'" class="panel-collapse collapse">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-lg-6">

                                <div class="well bs-component">
                                    <!--<form class="form-horizontal">-->
                                        

        <form enctype="multipart/form-data" action="tasks.php" method="POST">
        <fieldset>
        <div class="container-fluid">
          <input type="hidden" name="id" value="'.$CM->id.'" />
          <input type="hidden" name="task_id" value="'.$task_id->task_id.'" />
          <div class="row">
            Язык:
          </div>
          <div class="row">
            <div class="col-xs-4 col-md-4" style=";">
                <select class="form-control" id="select" name="lang">
                    '.$lang_select.'
                </select>
                <!--<div class="checkbox">
                    <label>
                        <input type="checkbox">Запомнить язык
                    </label>
                </div>-->
            </div>
            <div class="col-xs-4 col-md-4" style=";">
                <button type="reset" class="btn btn-warning">Очистить</button>
            </div>
            <div class="col-xs-4 col-md-4" style=";">
                <button type="submit" class="btn btn-success">Отправить</button>
            </div>
          </div>
          <div class="row">
            Код:
          </div>
          <div class="row">
            <div class="col-xs-12 col-md-12" style=";">
                <textarea class="form-control" rows="10" name="source"></textarea>
            </div>
          </div>
        </div>
        </fieldset>
        </form>

                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="well bs-component">
                                    <form class="form-horizontal">
                                        <fieldset>
                                            <div class="form-group">
                                                <div url-show="http://docs.google.com/viewer?url='.$task->statement_url.'&amp;embedded=true" class="show_docs" onclick="showDocs(this); return false;">
                                                <span><center>Посмотреть условие задачи</center></span>
                                                    <div class="file_block" style="height: 0px;">
                                                        <iframe src="" height="858" width="620"></iframe>
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
        $aid=chr(ord($aid)+1);
    }
//}
//<td colspan=2>load from file:<BR><input type=file name="sourcefile" style="width:100%"></td>
//bacs_print_task_send_form($bacs,2);
print '</div>';


    $task_answer='';
    //if ($_GET["answer"] != "")
    //    $task_answer = stripslashes(stripslashes($_GET["answer"]));
    if (isset($_POST["source"]) && ($_POST["source"] != "")) {
        $task_answer = stripslashes(stripslashes($_POST["source"]));
        $record = new stdClass();
        $record->user_id = $USER->id;
        $record->contest_id = 1;
        $record->task_id = $_POST["task_id"];
        $record->lang_id = $_POST["lang"];
        $record->source = $_POST["source"];
        $record->result_id = 1;
        $record->submit_time = time();
        $lastinsertid = $DB->insert_record('bacs_submits', $record, false);
    }
    //bacs_submit($bacs,$_POST);
//}
//<td colspan=2>load from file:<BR><input type=file name="sourcefile" style="width:100%"></td>
//bacs_print_task_send_form($bacs,2);
    
echo $OUTPUT->footer();
