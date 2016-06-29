<?php

/**
 *
 * @package    mod
 * @subpackage bacs
 */

// HEADER STANDART START

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

//add_to_log($course->id, 'bacs', 'view', "cron_tasks.php?id={$cm->id}", $bacs->name, $cm->id);

/// Print the page header

$PAGE->set_url('/mod/bacs/cron_tasks.php', array('id' => $cm->id));
$PAGE->set_title(format_string($bacs->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
// Output starts here
echo $OUTPUT->header();
echo $OUTPUT->heading($bacs->name);
$PAGE->navbar->ignore_active();
$PAGE->navbar->add('preview', new moodle_url('/a/link/if/you/want/one.php'));
$PAGE->navbar->add('name of thing', new moodle_url('/a/link/if/you/want/one.php'));

// HEADER STANDART END

if (has_capability('mod/bacs:addinstance',$context)) $student = false;
else $student = true;
if (time()<$bacs->starttime) $contest_has_started = false;
else $contest_has_started = true;
if ($DB->get_record('bacs_tasks_to_contests', array('contest_id' => $bacs->id))) $contest_has_tasks = true;
else $contest_has_tasks = false;

if (!$student)
{  
    include './test_www/BacsApi/Client.php';
    //require_once(dirname(__FILE__).'/lib.php');
    $apiClient = new Bacs\Client();

    try{
        $res = $apiClient->getProblems();
        foreach($res as $mes){
            print_r($mes->getId());
            print_r($mes->getInfo());
            print_r($mes->getTimeLimitMillis());
            print_r($mes->getMemoryLimitBytes());

            //id	task_id	name	time_limit_millis	memory_limit_bytes	count_tests	statement_url	revision
            $record = new stdClass();
            $record->task_id         = $mes->getId();
            $record->name         = $mes->getInfo()["Name"];
            $record->time_limit_millis = $mes->getTimeLimitMillis();
            $record->memory_limit_bytes = $mes->getMemoryLimitBytes();
            $record->count_tests = null;
            $record->statement_url = null;
            $record->revision = null;

            $lastinsertid = $DB->insert_record('bacs_tasks', $record, false);
        }
    }catch(Exception $e){
        print_r($e->getMessage());
    }

    class cron {
        function get_tasks() {

        }
    }
}

echo $OUTPUT->footer();