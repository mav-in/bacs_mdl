<?php

/**
 *
 * @package    mod
 * @subpackage bacs
 */

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

//add_to_log($course->id, 'bacs', 'view', "index.php?id={$cm->id}", $bacs->name, $cm->id);

/// Print the page header

$PAGE->set_url('/mod/bacs/index.php', array('id' => $cm->id));
$PAGE->set_title(format_string($bacs->name));

$PAGE->requires->css('/mod/bacs/bootstrap/css/bootstrap.min.css');

$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
// Output starts here
echo $OUTPUT->header();
echo $OUTPUT->heading($bacs->name);
$PAGE->navbar->ignore_active();
$PAGE->navbar->add('preview', new moodle_url('/a/link/if/you/want/one.php'));
$PAGE->navbar->add('name of thing', new moodle_url('/a/link/if/you/want/one.php'));

// HEADER END BOOTSTRAP

if (has_capability('mod/bacs:addinstance',$context)) $student = false;
else $student = true;
if (time()<$bacs->starttime) $contest_has_started = false;
else $contest_has_started = true;
if ($DB->get_record('bacs_tasks_to_contests', array('contest_id' => $bacs->id))) $contest_has_tasks = true;
else $contest_has_tasks = false;

echo html_writer::script('', $CFG->wwwroot.'/mod/bacs/bootstrap/js/bootstrap.min.js');
echo html_writer::script('', $CFG->wwwroot.'/mod/bacs/bootstrap/js/npm.js');

echo '<div class="bs-component">
<table class="table table-striped table-hover ">
  <tr>
	<th>1</th><th>2</th><th>3</th>
  </tr>  <tr>
	<td>1</td><td>2</td><td>3</td>
  </tr>
  <tr>
	<td>1</td><td>2</td><td>3</td>
  </tr>
</table>
</div>

<div class="mw-collapsible" style="width:400px">
Это сворачивающийся текст.<br>
Продолжение сворачивающегося текста.
</div>';

if (!$student)
{
    echo $OUTPUT->box_start();
    echo '<strong>Время начала контеста:</strong> ';
    echo userdate($bacs->starttime);
    echo "<br>";
    echo '<strong>Время окончания контеста:</strong> ';
    echo userdate($bacs->endtime);
    if ($edit) echo "<br><br> Вы можете изменить время контеста, зайдя в его настройки.";
    echo $OUTPUT->box_end();
    
    if (!$edit && !$contest_has_tasks)
    {
        echo $OUTPUT->box("Вы ещё не добавили задачи в контест. Это можно сделать в режиме редактрирования");
    }
    else echo $OUTPUT->box("Добавьте задачи");    
    
}

echo $OUTPUT->footer();