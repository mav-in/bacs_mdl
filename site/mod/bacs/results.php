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
    $BACS  = $DB->get_record('bacs', array('id' => $CM->instance), '*', MUST_EXIST);
} elseif ($b) {
    $BACS = $DB->get_record('bacs', array('id' => $b), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $BACS->course), '*', MUST_EXIST);
    $CM         = get_coursemodule_from_instance('bacs', $BACS->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $CM);
$context = context_module::instance($CM->id);

//add_to_log($course->id, 'bacs', 'view', "monitor.php?id={$CM->id}", $BACS->name, $CM->id);

/// Print the page header

$PAGE->set_url('/mod/bacs/results.php', array('id' => $CM->id));
$PAGE->set_title(format_string($BACS->name));

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
echo $OUTPUT->heading($BACS->name);
$PAGE->navbar->ignore_active();
$PAGE->navbar->add('preview', new moodle_url('/a/link/if/you/want/one.php'));
$PAGE->navbar->add('name of thing', new moodle_url('/a/link/if/you/want/one.php'));

// HEADER END BOOTSTRAP

//ИЩЕМ КОНТЕСТ
$now = time();
//if (!((int)$_GET['c_id']))
	$id = (int)$BACS->id;
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
class status_contest {
    private $starttime = 0;
    private $endtime = 0;
    private $runtime = 0;
    private $tottime = 0;
    private $status = 0;

    public function __construct() {

    }
    
    public function set() {
        GLOBAL $BACS;
        $starttime = $BACS->starttime;
        $endtime = $BACS->endtime;
        $runtime = (time() - $starttime) / 60;
        $tottime = ($endtime - $starttime) / 60;
        $status = 0; //if ($freeze && ($runtime > $freeze) && (!$unfreeze || ($runtime < $unfreeze) )) $status = 1;
        if ($status == 0 && $runtime < 0) {
            $status = -1;
        } elseif ($status == 0 && $runtime > $tottime) {
            $status = 2;
        }
        if ($runtime < 0) {
            $runtime = 0;
        } elseif ($runtime > $tottime) {
            $runtime = $tottime;
        }
        $this->starttime = $starttime;
        $this->endtime = $endtime;
        $this->runtime = $runtime;
        $this->tottime = $tottime;
        $this->status = $status;         
    }
    
    public function get_status() {
        return $this->status;
    }
    
    public function get_statustext() {
        switch ($this->status) {
            case -1: $statustext = "Not started"; break;
            case 0: $statustext = "Running"; break;
            case 1: $statustext = "Frozen"; break;
            case 2: $statustext = "Over"; break;
            default: $statustext = "Unknown";
        }
        return $statustext;
    }
    
    public function get_fullstatusstring() {
        return 'Time: <b>'.(int)$this->runtime.'</b> of <b>'.(int)$this->tottime.'</b>. Status: <b>'.$this->get_statustext().'</b>.<br>';
    }
    
    public function get_endtime() {
        return $this->endtime;
    }
}

$status_contest = new status_contest();
$status_contest->set();
print $status_contest->get_fullstatusstring();

//ПРОВЕРКА НА АДМИНА

//ПРОВЕРКА ПРАВ НА ПРОСМОТР КОНТЕСТА

//Задачки

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

$verdict_acm = array('Unknown',
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

$lang_id = $DB->get_records('bacs_langs', null, 'lang_id DESC', 'lang_id, name');
$lang = array();
foreach ($lang_id as $msg) {
    $lang[$msg->lang_id] = $msg->name;
}

print '
<table class="generaltable accordion">
<thead>
    <tr>
        <th class="header c0" style="" scope="col">№</th>
        <th class="header c1" style="" scope="col">ID</th>
        <th class="header c2" style="" scope="col">Язык</th>
        <th class="header c3" style="" scope="col">Баллы</th>
        <th class="header c4" style="" scope="col">Результат</th>
        <th class="header c5 lastcol" style="" scope="col">Отправлено</th>
    </tr>
</thead>
<tbody>';

$task_ids = $DB->get_records('bacs_submits', array('contest_id' => $BACS->id, 'user_id' => $USER->id), 'submit_time DESC');
$task_tasks = $DB->get_records('bacs_tasks_to_contests', array('contest_id' => 1), 'task_id ASC');
$data = array();
foreach ($task_tasks as $task_id){
    $data[$task_id->task_id] = (int)$task_id->task_order;
}
$i = 0;
$date = "";
$count = count($task_ids);
//print '<tr><td colspan="6"></td></tr>';
foreach ($task_ids as $task_id){
    $row_id = 'el'.$i++;
    if ($date != userdate($task_id->submit_time,"%d %B %Y (%A)")) {
        print '<tr><td colspan="7">'.userdate($task_id->submit_time,"%d %B %Y (%A)").'</td></tr>';
    }
    print '
    <!--Панель A #####################################################################################-->
            <tr class="" style="cursor: pointer;" data-toggle="collapse" data-target="#collapse'.$row_id.'" id="package1">
                <td class="cell c0 accordion-toggle arrow-up" style=""><i class="icon-chevron-down"></i>'.$count--.'</td>
                <td class="cell c1" style="">'.i_to_aid($data[$task_id->task_id]).'</td>
                <td class="cell c2" style="">'.$lang[$task_id->lang_id].'</td>
                <td class="cell c3" style="">'.$task_id->points.'</td>
                <td class="cell c4" style="">'.$verdict[$task_id->result_id].'</td>
                <td class="cell c5 lastcol" style="">'.userdate($task_id->submit_time,"%H:%M:%S").'</td>
            </tr>

            <tr class="" id="div collapse'.$row_id.'" class="collapse">
                <td colspan="7">
                    <div id="collapse'.$row_id.'" class="bs-docs-grid fcontainer clearfix collapse">
                        <div class="row-fluid show-grid">
                          <div class="span5">';

                        $db_points = $DB->get_records('bacs_submits_tests', array('submit_id' => $task_id->id), 'id ASC');
                        $j = 0;
                        foreach ($db_points as $record){  
                            echo 'Тест №'.++$j.': ';
                            echo ($record->status_id)?'Не пройден':'Пройден';
                            echo '</br>';
                        }
                        
                        if ($task_id->info) {
                            echo 'Сообщение компилятора: '.htmlspecialchars($task_id->info);
                        }
                     print '

              </div>
              <div class="offset5">
                                <div class="well bs-component">
                                    <form class="form-horizontal">
                                        <fieldset>
                                        <div class="form-group">
                                            <div url-show="#" class="show_docs" onclick="showDocs(this); return false;">
                                            <span><center>Посмотреть решение</center></span>
                                                <div class="file_block" style="height: 0px; display: none;">
                                                    <textarea style="white-space: nowrap; width: 100%;" class="form-control" rows="20">'.htmlspecialchars($task_id->source).'</textarea>
                                                </div>
                                            </div>
                                        </div>
                                        </fieldset>
                                    </form>
                                </div>
                </div>
                </div>
<!--//samples-->       
<table class="generaltable accordion table-bordered">
<thead>
    <tr>
        <th class="header c0" style="" scope="col">№</th>
        <th class="header c1" style="" scope="col">input</th>
        <th class="header c2" style="" scope="col">output</th>
        <th class="header c3" style="" scope="col">вывод</th>
        <th class="header c4 lastcol" style="" scope="col">вердикт</th>
    </tr>
</thead>
<tbody>
    <tr>
        <td class="header c0" style="" scope="col">1</td>
        <td class="header c1" style="" scope="col">input</td>
        <td class="header c2" style="" scope="col">output</td>
        <td class="header c3" style="" scope="col">вывод</td>
        <td class="header c4 lastcol" style="" scope="col">OK/FAIL</td>
    </tr>
</tbody>
</table>
<!--//samples-->
    </td>
</tr>';
    $date = userdate($task_id->submit_time,"%d %B %Y (%A)");
}
print '</tbody>
</table>';

echo $OUTPUT->footer();
