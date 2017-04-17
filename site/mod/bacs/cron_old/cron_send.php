<?php //

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

//add_to_log($course->id, 'bacs', 'view', "cron_send.php?id={$cm->id}", $bacs->name, $cm->id);

/// Print the page header

$PAGE->set_url('/mod/bacs/cron_send.php', array('id' => $cm->id));
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

if (has_capability('mod/bacs:addinstance',$context))
    $student = false;
else
    $student = true;

if (time()<$bacs->starttime)
    $contest_has_started = false;
else
    $contest_has_started = true;

if ($DB->get_records('bacs_tasks_to_contests', array('contest_id' => $bacs->id)))
    $contest_has_tasks = true;
else
    $contest_has_tasks = false;

include './api/Client.php';

$apiClient = new Bacs\Client();

// SQL запрашиваем результаты
// OOF_OLD
if ($apiClient->Ping()) {
    $resdb = $DB->get_records('bacs_cron', array('status_id' => 2), 'timestamp ASC');
    $sync_ids = array();
    $ida = array();
    $ids = array();
    $err = array();
    $i = 0;
    foreach($resdb as $item){
       //$ids[++$i] = (array)$item;
        $sync_ids[] = $item->sync_submit_id;
        $ida[] = $item->id;
        $ids[] = $item->submit_id;
        $err[] = $item->error;
    }
    try{
        if (isset($ids[0])) {
            $res = $apiClient->getResultAll($sync_ids);
            
            $i = 0;
            
            foreach($res as $item){
                //print_r($item);
                if(!is_null($item->getBuildStatus()) AND !is_null($item->getSystemStatus())) {
                    $transaction = $DB->start_delegated_transaction();                   
                    $tests = $item->getTestGroup();
                    $points = 0;
                    $max_time_used = 0;
                    $max_memory_used = 0;
                    $test_num_failed = NULL;
                    $pretest = true;
                    $pretest_failed = false;
                    $failed = false;
                    if ($tests) {
                        // TODO Переделать в list запрос
                        $db_assign = $DB->get_record('bacs_submits', array('id' => $ids[$i]), 'task_id', MUST_EXIST);
                        $db_points = $DB->get_record('bacs_tasks', array('id' => $db_assign->task_id), 'test_points', MUST_EXIST);
                        $point = explode(",", $db_points->test_points);
                        $records = array();
                        foreach ($tests as $rec) {
                            $record = new stdClass();
                            $record->submit_id = $ids[$i];
                            $record->test_id = $rec->getTestId();
                            $record->status_id = (int)$rec->getStatus();
                            $time_used = $rec->getTimeUsageMillis();
                            $memory_used = $rec->getMemoryUsageBytes();
                            if ($time_used > $max_time_used) {
                                $max_time_used = $time_used;
                            }
                            if ($memory_used > $max_memory_used) {
                                $max_memory_used = $memory_used;
                            }
                            if (!$pretest_failed) {
                                if ($pretest && $point[$record->test_id] != 0) {
                                    $pretest = false;
                                }
                                if ($rec->getStatus() == 0) {
                                    $points += $point[$record->test_id];
                                } else {
                                    if (!$failed) {
                                        $failed = true;
                                        $test_num_failed = $rec->getTestId();
                                    }
                                    if ($pretest) {
                                        $pretest_failed = true;
                                        $point = 0;
                                    }
                                }
                            }
                            $record->time_used = $time_used;
                            $record->memory_used = $memory_used;
                            $records[] = $record;
                        }
                        $lastinsertid = $DB->insert_records('bacs_submits_tests', $records);
                    }

                    $rec = new stdClass();
                    $rec->id = $ids[$i];
                    $rec->result_id = $item->getSystemStatus();
                    $rec->test_num_failed = $test_num_failed;
                    $rec->points = $points;
                    $rec->info = $item->getBuildOutput();
                    $rec->max_time_used = $max_time_used;
                    $rec->max_memory_used = $max_memory_used;
                    $lastinsertid = $DB->update_record_raw('bacs_submits', $rec);             
                    unset($rec);
                    
                    $rec1 = new stdClass();
                    $rec1->id = $ida[$i];
                    $rec1->submit_id = $ids[$i];
                    $rec1->status_id = 3;
                    $rec1->timestamp = time();
                    $lastinsertid = $DB->update_record_raw('bacs_cron', $rec1);
                    unset($rec1);

                    $transaction->allow_commit();
                } else {
                    $transaction = $DB->start_delegated_transaction();
                    $rec1 = new stdClass();
                    $rec1->id = $ida[$i];
                    $rec1->submit_id = $ids[$i];
                    $rec1->error = ++$err[$i];
                    $rec1->timestamp = time();
                    $lastinsertid = $DB->update_record_raw('bacs_cron', $rec1);                  
                    unset($rec1);
                    
                    if ($err[$i] > 60) {
                        $rec = new stdClass();
                        $rec->id = $ids[$i];
                        $rec->result_id = 0;
                        $lastinsertid = $DB->update_record_raw('bacs_submits', $rec);             
                        unset($rec);
                    }
                    
                    $transaction->allow_commit();
                }
                $i += 1;
            }
        }
    }catch(Exception $e){
        print_r($e->getMessage());
    }
}

// SQL выборка задач на обработку
// IN: id user_id contest_id task_id lang_id source submit_time result_id sync_submit_id test_num_failed max_time_used max_memory_used info
// OUT: id submit_id sync_submit_id submit_type status_id flag error timestamp
$task_ids = $DB->get_records('bacs_submits', array('result_id' => 1), null, 'id, submit_time');
//$task_ids = $DB->get_records('bacs_submits', array('contest_id' => $bacs->id, 'result_id' => 0));
foreach($task_ids as $mes){
    $transaction = $DB->start_delegated_transaction();
    
    $record = new stdClass();
    $record->submit_id = $mes->id;
    $record->sync_submit_id = NULL;
    $record->submit_type = 0;
    $record->status_id = 1;
    $record->flag = 0;
    $record->error = 0;
    $record->timestamp = time();
    $lastinsertid = $DB->insert_record('bacs_cron', $record, false);
    
    $update = new stdClass();
    $update->id = $mes->id;
    $update->result_id = 2;
    $lastinsertid1 = $DB->update_record_raw('bacs_submits', $update);
    
    $transaction->allow_commit();
}

// Получение ID задачи
// Ограничить выборку
// TODO убрать, сделать по массиву id
if ($apiClient->Ping()) {
    $cron_ids = $DB->get_records('bacs_cron', array('status_id' => 1), 'timestamp ASC', 'id, submit_id, error');
    try{
        // Формирование пакета
        $all = array();
        $err = array();
        foreach($cron_ids as $mes){
            $result = $DB->get_record('bacs_submits', array('id' => $mes->submit_id), 'task_id, source, lang_id', IGNORE_MISSING);
            $result_task = $DB->get_record('bacs_tasks', array('id' => $result->task_id), 'task_id', IGNORE_MISSING);
            $all[] = new Bacs\model\Submit($result_task->task_id, $result->source, $result->lang_id);
            $err[] = $mes->error;
            var_dump($result);
        }
        $res = $apiClient->sendSubmitAll($all);
        var_dump($res);
        $i = 0;
        foreach($cron_ids as $mes){
            $record = new stdClass();
            $record->id = $mes->id;
            if(isset($res[$i])) {
                $record->sync_submit_id = $res[$i];
                $record->status_id = 2;
            } else {
                $record->error = ++$err[$i];
            }
            $record->timestamp = time();
            $lastinsertid = $DB->update_record('bacs_cron', $record);        
            ++$i;
        }
    }catch(Exception $e){
        print_r($e->getMessage());
    }
}
   
if (!$student)
{  
    //require_once(dirname(__FILE__).'/lib.php');
    
    class cron {
        function get_tasks() {

        }
    }
}

echo $OUTPUT->footer();