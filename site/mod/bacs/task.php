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

//echo (int)rand(100, 999);
//$bacs->contest_id

///---

$LANGUAGES = array('C++'=>'+', 'PASCAL'=>'P', 'C'=>'C', 'Java'=>'J', 'Python 3'=>'T');
function bacs_print_task_send_form($bacs,$taskId){
	global $LANGUAGES;
        global $DB;
	$task_select='';
	$lang_select='';
	foreach ($LANGUAGES as $lang=>$slang){
		$lang_select.="<option value='$slang'>$lang\n";
	}
	//$task_ids = $DB->get_records("bacs_m2m", "contest_id", $bacs->contest_id,'task_order,task_id');
	//$task_ids = $DB->get_records("bacs_tasks_to_contests", "contest_id", $bacs->contest_id, 'task_order, task_id');
        //$task_ids = $DB->get_records("bacs_tasks_to_contests", "contest_id", $bacs->id, 'task_order, task_id');
        $task_ids = $DB->get_records('bacs_tasks_to_contests', array('contest_id' => $bacs->id), 'task_order ASC', 'task_id, contest_id, task_order');
	$aid='A';
	foreach ($task_ids as $task_id){
		$task = $DB->get_record('bacs_tasks', array('id' => $task_id->task_id));
		if ($task->task_id==$taskId)
		$sel='selected';
		else
		$sel='';
		$task_select.="<option value='$task->task_id' $sel>$aid. $task->name\n";
		$aid=chr(ord($aid)+1);
	}

	$task_answer='';
	//if ($_GET["answer"]!="")
	//$task_answer=stripslashes(stripslashes($_GET["answer"]));
	print '
<form enctype="multipart/form-data" method="POST">
<div class="container-fluid">
  <div class="row">
    Задача:
  </div>
  <div class="row">
    <div class="col-xs-12 col-md-12" style=";">
        <select class="form-control" id="select" name="task_id" >
            '.$task_select.'
        </select>
    </div>
  </div>
  <div class="row">
    Язык:
  </div>
  <div class="row">
    <div class="col-xs-8 col-md-6" style=";">
        <select class="form-control" id="select" name="lang">
            '.$lang_select.'
        </select>
        <!--<div class="checkbox">
            <label>
                <input type="checkbox">Запомнить язык
            </label>
        </div>-->
    </div>
    <div class="col-xs-2 col-md-3" style=";">
        <button type="reset" class="btn btn-default">Очистить</button>
    </div>
    <div class="col-xs-2 col-md-3" style=";">
        <button type="submit" class="btn btn-danger">Отправить</button>
    </div>
  </div>
  <div class="row">
    Код:
  </div>
  <div class="row">
    <div class="col-xs-12 col-md-12" style=";">
        <textarea class="form-control" rows="10" id="textArea"></textarea>
    </div>
  </div>
</div>
</form>
';
}

bacs_print_task_send_form($bacs,2)
?>
