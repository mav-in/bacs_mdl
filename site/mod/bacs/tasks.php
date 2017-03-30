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
    $BACS  = $DB->get_record('bacs', array('id' => $b), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $BACS->course), '*', MUST_EXIST);
    $CM         = get_coursemodule_from_instance('bacs', $BACS->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $CM);
$context = context_module::instance($CM->id);

//add_to_log($course->id, 'bacs', 'view', "monitor.php?id={$CM->id}", $BACS->name, $CM->id);

/// Print the page header

$PAGE->set_url('/mod/bacs/monitor.php', array('id' => $CM->id));
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

print '
<table class="generaltable accordion">
<thead>
    <tr>
        <th class="header c0" style="" scope="col">ID</th>
        <th class="header c1" style="" scope="col">Название задачи</th>
        <th class="header c2" style="" scope="col">Условие</th>
        <th class="header c3" style="" scope="col">Время</th>
        <th class="header c4 lastcol" style="" scope="col">Память</th>
    </tr>
</thead>
<tbody>';

///---
//$results = $DB->get_records_select('bacs_langs','');

//$LANGUAGES = array('C++'=>'+', 'PASCAL'=>'P', 'C'=>'C', 'Java'=>'J', 'Python 3'=>'T');
function get_lang() {
    global $DB;
    return  $DB->get_records('bacs_langs', null, null, 'id, name');
}

$res = get_lang();

//function bacs_print_task_send_form($BACS,$taskId){
function bacs_print_task_send_form($BACS) {
    global $DB;
    //global $LANGUAGES;
    $task_select='';
    $lang_select='';
    foreach ($res as $mes){
        $lang_select .= "<option value='$mes->id'>$mes->name\n";
    }
    //foreach($results as $result) {
    //	$lang_select.="<option value='format_string($result->langs_id)'>format_string($result->$str)\n";
    //}
    //$task_ids = $DB->get_records("bacs_m2m", "contest_id", $BACS->contest_id,'task_order,task_id');
    //$task_ids = $DB->get_records('bacs_tasks_to_contests', array('contest_id' => $BACS->id), 'task_order, task_id');
    $task_ids = $DB->get_records('bacs_tasks_to_contests', array('contest_id' => $BACS->id), 'task_order ASC', 'task_id, contest_id, task_order');
    $aid='A';
    $i=0;
    foreach ($task_ids as $task_id){
        $task = $DB->get_record('bacs_tasks', array('id' => $task_id->task_id), 'id, name, time_limit_millis, memory_limit_bytes, statement_url', IGNORE_MISSING);
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
            <tr class="">
                <td class="cell c0" style="cursor: pointer;" data-toggle="collapse" data-target="#collapse'.$row_id.'"><i class="icon-chevron-down"></i>'.$aid.'</td>
                <td class="cell c1" style="cursor: pointer;" data-toggle="collapse" data-target="#collapse'.$row_id.'">'.$task->name.'</td>
                <td class="cell c2" style=""><a href="http://docs.google.com/viewer?url='.$task->statement_url.'" target="_blank">Открыть</a> / <a href="'.$task->statement_url.'" target="_blank">Скачать</a></td>
                <td class="cell c3" style="cursor: pointer;" data-toggle="collapse" data-target="#collapse'.$row_id.'">'.$time.'</td>
                <td class="cell c4 lastcol" style="cursor: pointer;" data-toggle="collapse" data-target="#collapse'.$row_id.'">'.$memory.'</td>
            </tr>

            <tr class="" id="div collapse'.$row_id.'" class="collapse">
                <td colspan="7">
                    <div id="collapse'.$row_id.'" class="bs-docs-grid fcontainer clearfix collapse">
                        <div class="row-fluid show-grid">
                          <div class="span6">                          

<form enctype="multipart/form-data" action="tasks.php" method="POST">
<div class="settingsform clearfix"><input type="hidden" name="section" value="optionalsubsystems">
    <input type="hidden" name="id" value="'.$CM->id.'">
    <input type="hidden" name="task_id" value="'.$task->id.'">
    <input type="hidden" name="key" value="'.md5($USER->email.$USER->sesskey.$CM->id.$task->id).'">
    <input type="hidden" name="return" value="">
    <div class="hide">
        <input type="text" class="ignoredirty">
        <input type="password" class="ignoredirty">
    </div>
    <fieldset>
        <div class="form-item clearfix" id="admin-completiondefault">
            <div class="form-label">
              <label for="id_s__completiondefault">Компилятор</label>
            </div>
            <div class="form-setting">
                <div class="form-select defaultsnext">
                    <select id="lang_id" name="lang_id">
                      '.$lang_select.'
                    </select>
                </div>
            </div>
        </div>
    <!--
        <div class="form-item clearfix" id="admin-usetags">
            <div class="form-label">
                <label for="id_s__usetags">Запомнить выбранный компилятор</label>
            </div>
            <div class="form-setting">
                <div class="form-checkbox defaultsnext">
                    <input type="hidden" name="s__usetags" value="0">
                    <input type="checkbox" id="id_s__usetags" name="s__usetags" value="0" checked="checked">
                </div>
            </div>
        </div>
    -->
        <div class="form-item clearfix" id="admin-usetags">
            <div class="form-label">
                <label for="id_s__usetags">Очистить форму</label>
            </div>
            <div class="form-setting">
                <div class="form-checkbox defaultsnext">
                    <button type="reset" class="btn btn-warning">Очистить</button>
                </div>
            </div>
        </div>

        <div class="form-item clearfix" id="admin-completiondefault">
            <div class="form-label">
              <label for="id_s__completiondefault">Текст программы</label>
            </div>
            <div class="form-setting">
                <div class="form-select defaultsnext">
                    <textarea style="white-space: nowrap;" class="form-control" rows="10" name="source"></textarea>
                </div>
            </div>
        </div>

        <div class="form-item clearfix" id="admin-usetags">
            <div class="form-label">
                <label for="id_s__usetags">Отправить решение на проверку</label>
            </div>
            <div class="form-setting">
                <div class="form-checkbox defaultsnext">
                    <button type="submit" class="btn btn-success">Отправить</button>
                </div>
            </div>
        </div>
    </fieldset>
</div>
</form>
              </div>
              <div class="offset6">
                                <div class="well bs-component">
                                    <form class="form-horizontal">
                                        <fieldset>
                                            <div class="form-group">
                                                <div url-show="http://docs.google.com/viewer?url='.$task->statement_url.'&amp;embedded=true" class="show_docs" onclick="showDocs(this); return false;">
                                                <span><center>Посмотреть условие задачи</center></span>
                                                    <div class="file_block" style="height: 0px; display: none;">
                                                        <iframe src="" height="100%" width="100%"></iframe>
                                                    </div>
                                                </div>
                                            </div>
                                        </fieldset>
                                    </form>
                                </div>
                </div>
                </div>

    </td>
</tr>';
        $aid=chr(ord($aid)+1);
    }
//}
//<td colspan=2>load from file:<BR><input type=file name="sourcefile" style="width:100%"></td>
//bacs_print_task_send_form($BACS,2);
print '</tbody>
</table>';
}

bacs_print_task_send_form($BACS);

function bacs_submit($BACS) {
    global $_POST, $DB;
    $task_answer='';
    //if ($_GET["answer"] != "")
    //    $task_answer = stripslashes(stripslashes($_GET["answer"]));
    if (optional_param('key', 0, PARAM_INT) == md5($USER->email.$USER->sesskey.$CM->id.$task->id)) {
        $source = optional_param('source', 0, PARAM_INT);
        if (isset($source) && ($source != "")) {
            $task_answer = stripslashes(stripslashes($_POST["source"]));
            $record = new stdClass();
            $record->user_id = $USER->id;
            $record->contest_id = $BACS->id;
            $record->task_id = optional_param('task_id', 0, PARAM_INT);
            $record->lang_id = optional_param('lang_id', 0, PARAM_INT);
            $record->source = optional_param('source_id', 0, PARAM_INT);
            $record->result_id = 1;
            $record->submit_time = time();
            $lastinsertid = $DB->insert_record('bacs_submits', $record, false);
        }
    }
}

bacs_submit($BACS);

    //bacs_submit($BACS,$_POST);
//}
//<td colspan=2>load from file:<BR><input type=file name="sourcefile" style="width:100%"></td>
//bacs_print_task_send_form($BACS,2);

echo $OUTPUT->footer();