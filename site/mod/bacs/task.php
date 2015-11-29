<?php

/**
 *
 * @package    mod
 * @subpackage bacs
 */

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

//add_to_log($course->id, 'bacs', 'monitor', "monitor.php?id={$cm->id}", $bacs->name, $cm->id);

/// Print the page header

$PAGE->set_url('/mod/bacs/task.php', array('id' => $cm->id));
$PAGE->set_title(format_string($bacs->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
// Output starts here
echo $OUTPUT->header();
echo $OUTPUT->heading($bacs->name);
$PAGE->navbar->ignore_active();
$PAGE->navbar->add('preview', new moodle_url('/a/link/if/you/want/one.php'));
$PAGE->navbar->add('name of thing', new moodle_url('/a/link/if/you/want/one.php'));

echo (int)rand(100, 999);

///---

$LANGUAGES = array('C++'=>'+', 'PASCAL'=>'P', 'C'=>'C', 'Java'=>'J', 'Python 3'=>'T');
function bacs_print_task_send_form($bacs,$taskId){
	global $LANGUAGES;
	$task_select='';
	$lang_select='';
	foreach ($LANGUAGES as $lang=>$slang){
		$lang_select.="<option value='$slang'>$lang\n";
	}
	//$task_ids = $DB->get_records("bacs_m2m", "contest_id", $bacs->contest_id,'task_order,task_id');
	$task_ids = $DB->get_records(" bacs_tasks_to_contests", "contest_id", $bacs->contest_id, 'task_order, task_id');
	$aid='A';
	foreach ($task_ids as $task_id){
		$task = $DB->get_record('bacs_tasks','task_id',$task_id->task_id);
		if ($task->task_id==$taskId)
		$sel='selected';
		else
		$sel='';
		$task_select.="<option value='$task->task_id' $sel>$aid. $task->name\n";
		$aid=chr(ord($aid)+1);
	}

	$task_answer='';
	if ($_GET["answer"]!="")
	$task_answer=stripslashes(stripslashes($_GET["answer"]));
	print '<form enctype="multipart/form-data" method="POST">
	<table class="answer_table">
	<tr>
	<td width="50%">'.get_string('task','bacs').':<BR><select name="task_id" size=1 style="width:100%">'.$task_select.'</select></td>
	<td width="50%">'.get_string('prog lang','bacs').':<BR><select name="lang" size=1 style="width:100%">'.$lang_select.'</select></td>
	</td>
	<tr>
	<td colspan=2>'.get_string('source','bacs').':<BR><textarea name="source" style="width:100%" rows=20>'.$task_answer.'</textarea></td>
	</tr>
	<tr>
	<td colspan=2>'.get_string('load from file','bacs').':<BR><input type=file name="sourcefile" style="width:100%"></td>
	</tr>
	<tr>
	<td colspan=2 align="center"><input type=submit value="'.get_string('send answer','bacs').'"></td>
	</tr>
	</table>
	</form>';
}

bacs_print_task_send_form($bacs,2)
?>
