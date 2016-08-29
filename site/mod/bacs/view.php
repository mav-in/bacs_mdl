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
    $COURSE     = $DB->get_record('course', array('id' => $CM->course), '*', MUST_EXIST);
    $BACS       = $DB->get_record('bacs', array('id' => $CM->instance), '*', MUST_EXIST);
} elseif ($b) {
    $BACS       = $DB->get_record('bacs', array('id' => $b), '*', MUST_EXIST);
    $COURSE     = $DB->get_record('course', array('id' => $BACS->course), '*', MUST_EXIST);
    $CM         = get_coursemodule_from_instance('bacs', $BACS->id, $COURSE->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($COURSE, true, $CM);
$CONTEXT = context_module::instance($CM->id);

//add_to_log($COURSE->id, 'bacs', 'view', "monitor.php?id={$CM->id}", $BACS->name, $CM->id);

/// Print the page header

$PAGE->set_url('/mod/bacs/view.php', array('id' => $CM->id));
$PAGE->set_title(format_string($BACS->name));

$PAGE->requires->css('/mod/bacs/bootstrap/css/docs.min.css');
$PAGE->requires->css('/mod/bacs/bootstrap/css/common.css');
$PAGE->requires->css('/mod/bacs/bootstrap/css/bootstrap.min.css');

$PAGE->requires->js('/mod/bacs/test_www/bootstrap/js/jquery-2.2.2.js', true);
$PAGE->requires->js('/mod/bacs/test_www/bootstrap/js/production.js', true);
//$PAGE->requires->js('/mod/bacs/test_www/bootstrap/js/font.js', true);
$PAGE->requires->js('/mod/bacs/test_www/bootstrap/js/common.js', true);

$PAGE->set_heading(format_string($COURSE->fullname));
$PAGE->set_context($CONTEXT);
// Output starts here
echo $OUTPUT->header();
echo $OUTPUT->heading(htmlspecialchars($BACS->name));
$PAGE->navbar->ignore_active();
$PAGE->navbar->add('preview', new moodle_url('/a/link/if/you/want/one.php'));
$PAGE->navbar->add('name of thing', new moodle_url('/a/link/if/you/want/one.php'));

// HEADER END BOOTSTRAP

//СПОСОК КОНТЕСТОВ
//$fields='*'
//$results = $DB->get_records('bacs',array('upsolving' => $BACS->id));
/*
$results = $DB->get_records('bacs', array('upsolving' => $BACS->id));
$table = new html_table();
$table->head = array('Contest', 'Starttime', 'Stoptime');
foreach($results as $result) {
    $table->data[] = array(format_string($result->name), format_string($result->starttime), format_string($result->endtime));
};
echo html_writer::table($table);
*/

//ИЩЕМ КОНТЕСТ
$now = time();
$result = $DB->get_record_select('bacs', "starttime < $now AND id = $BACS->id", array($params = null), 'id, name');
if (!is_object($result)) {
    if ($DB->count_records('bacs', array('id' => $BACS->id)) == 0) {
        //error(get_string('not_found', 'bacs'));
    } else {
        //error(get_string('not_started', 'bacs'));
    }
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

//ПРОВЕРКА НА АДМИНА

//ПРОВЕРКА ПРАВ НА ПРОСМОТР КОНТЕСТА

class role {
    private $my_role;
    
    public function __construct() {
        GLOBAL $CONTEXT, $USER;
        $this->my_role = get_user_roles($CONTEXT, $USER->id, true);
    }
    
    public function is_student() {
        GLOBAL $CONTEXT;
        return (has_capability('mod/bacs:addinstance',$CONTEXT))?FALSE:TRUE;
    }    
}

//СТАТИСТИКА
//SELECT MIN(`submit_time`), `user_id`, `task_id` FROM `mdl_bacs_submits` WHERE `contest_id` = 2 GROUP BY `task_id` HAVING MIN(`submit_time`)

class pupils {
    private $users;
    
    private function set_users_module() {
        GLOBAL $DB, $CM;
        $role = $DB->get_record('role', array('shortname' => 'student')); 
        $context_instance = context_module::instance($CM->id);
        //$context_instance = context_course::instance($COURSE->id);
        $this->users = get_role_users($role->id, $context_instance);        
    }

    private function set_users_course() {
        GLOBAL $DB, $COURSE;
        $role = $DB->get_record('role', array('shortname' => 'student')); 
        $context_instance = context_course::instance($COURSE->id);
        //$context_instance = context_course::instance($COURSE->id);
        $this->users = get_role_users($role->id, $context_instance);        
    }
    
    public function get_users() {
        // TODO * галочка права
        $this->set_users_course();
        return $this->users;
    }
}

class contest extends pupils {
    private $contest = NULL;
    private $count = 0;
    private $submits;

    public function __construct() {
        GLOBAL $DB, $BACS;
        $this->contest = $DB->get_records('bacs_tasks_to_contests', array('contest_id' => $BACS->id), 'task_order ASC', 'task_id, contest_id, task_order');
        $this->count = count($this->contest);
    }
    
    //TODO переделать
    public function get_submits() {
        GLOBAL $DB, $BACS, $status_contest;
        $this->submits = $DB->get_records_select('bacs_submits', "submit_time <= ".$status_contest->get_endtime()." AND contest_id = $BACS->id", array($params = null), 'points ASC, submit_time DESC');        
        return $this->submits;
    }
    
    public function get_contest() {
        return $this->contest;
    }
   
    public function get_num_of_tasks() {
        return $this->count;
    }
    
    public function get_tasks_id() {
        $lid = array();
        foreach($this->contest as $result) {
            $lid[$result->task_id. " " .$result->contest_id] = $result->task_order;
        };
        return $lid;
    }
}

// TODO сделать статический массив в классе
function id_to_aid ($task_aid_num){
    $num = (int)($task_aid_num / 26);
    if ($num > 0) {
        $msg = chr($task_aid_num % 26 + 65);
        $msg .= $num;
    } else {
        $msg = chr($task_aid_num + 65);
    }
    return $msg;
}

//МОНИТОР
class MyRec {
    var $fault;
    var $points; //+
    var $ac;
    var $ac_time;
    var $submit_time;
}

class Boo {
    var $id;
    var $name;
    var $pen;
    var $solved;
    var $start_time;
}

    
function user_cmp_for_school($a, $b) {
    if ($a->pen < $b->pen) {
        return 1;
    } else if ($a->pen > $b->pen) {
        return -1;
    } else if ($a->name < $b->name) {
        return -1;
    } else if ($a->name > $b->name) {
        return 1;
    } else {
        return 0;
    }
}

function user_cmp_for_acm($a, $b) {
    if ($a->solved > $b->solved) {
        return -1;
    } else if ($a->solved < $b->solved) {
        return 1;
    } elseif ($a->pen < $b->pen) {
        return -1;
    } else if ($a->pen > $b->pen) {
        return 1;
    } else if ($a->name < $b->name) {
        return -1;
    } else if ($a->name > $b->name) {
        return 1;
    } else {
        return 0;
    }
}

//class monito
function formtime($sec)
{
    $hr = (int)($sec / 3600);
    $min = (int)($sec / 60) % 60;
    if ($min < 10)
        $min = '0'.$min;

    return ($hr.':'.$min);
}

class monitor extends contest{
    private $header;
    private $size;
    private $data;
    private $source;
    private $hh;
    private $cstats;
    private $stat_user;
    private $usern;

    private function formtime($sec) {
        $hr = (int)($sec / 3600);
        $min = (int)($sec / 60) % 60;
        if ($min < 10) {
            $min = '0' . $min;
        }

        return ($hr.':'.$min);
    }
    
    public function set_header() {
        $header = array();
        $header[] = 'N';
        $header[] = 'User name';
        for ($i = 0; $i < $this->get_num_of_tasks(); $i++) {
            $header[] = id_to_aid($i);
        }
        $header[] = '+';
        $header[] = 'Points';
        $this->header = $header;
    }
    
    public function set_size() {
        $size = array();
        $size[] = 'N';
        $size[] = array('32px', '');
        for ($i = 0; $i < $this->get_num_of_tasks(); $i++) {
            $size[] = '32px';
        }
        $table->size[] = '32px';
        $table->size[] = '50%';
        $this->size = $size;
    }
    
    public function get_header() {
        return $this->header;
    }
    
    public function get_size() {
        return $this->header;
    }

    public function init_data() {
        GLOBAL $BACS;
        $users = $this->get_users();
        $data = array();
        foreach ($users as $result){
            if (!isset($rec)) { //Если первый проход
                $rec = new MyRec();
                $rec->fault = 0; //Ошибок
                $rec->ac = 0; //Правильных
                $rec->points = 0; //+
                $rec->ac_time = 0; //Время правильного
            }

            //for ($i = 0; $i < $monitor->get_num_of_tasks(); $i++) {
                //$data[$mes->id][$i." 1"] = $rec;
                $data[$result->id]["0 1"] = $rec;
            //}
        }
        
        $lid = $this->get_tasks_id();
        $submits = $this->get_submits();
        foreach($submits as $result) {
            //$res = $DB->get_record_select('bacs_tasks', "id = $result->task_id", array($params = null), 'task_id');
            $cur_uid = format_string($result->user_id); //Получаем id пользователя
            $lit = $lid[$result->task_id." ".$result->contest_id];
            //$lit = isset($lid[$result->task_id." ".$result->contest_id]) ? $lid[$result->task_id." ".$result->contest_id] : ''; //Литера
            //if (!isset($data[$cur_uid]["1 1"])) continue;
            $rec = $data[$cur_uid][$lit];
            //$rec = isset($data[$cur_uid][$lit]) ? $data[$cur_uid][$lit] : ''; //Собираем статистику по связке пользователь+[задача+контест]
            if (!$rec) { //Если первый проход
                $rec = new MyRec();
                $rec->fault = 0; //Ошибок
                $rec->ac = 0; //Правильных
                $rec->points = 0; //+
                $rec->ac_time = 0; //Время правильного
            }
            //if ($rec->ac)//Если в предыдущеем проходе решение принято - пропускаем цикл
                //continue;
            if ((format_string($result->result_id) == 13) OR (format_string($result->result_id) == 12)) { //Сохраняем данные по принятой задачке //+
                $rec->ac = 1;
                $rec->ac_time = $result->submit_time - $BACS->starttime;
            } //else {
                //if (format_string($result->result_id) != 0) {
                    ++$rec->fault; //Считаем кол-во фолов
                //    $rec->submit_time = $result->submit_time - $BACS->starttime;
                //}
            //}
            $rec->points = $result->points; //+
            $data[$cur_uid][$lit] = $rec; //Возвращаем полученные данные
        }
        $this->source = $data;
        return $this->source;
    }
    
    public function calc_stat() {
        $data = $this->source;
        $usern = 0;
        $cstat = array(array());
        $hh = array();
        if (isset($data)) {
            foreach ($data as $cur_uid => $result) {
                $u = new Boo();
                $u->id = $cur_uid;
                $hh[$cur_uid] = $usern;
                $u->pen = 0;
                $u->solved = 0;        
                $u->points = 0; //+
                foreach ($result as $lit => $rec) {
                    //if ($rec->ac) { //+
                    if ($rec->points > 0) {
                        ++$u->solved;
                        //$u->pen += (int)($rec->ac_time / 60); //+
                        //$u->pen += $rec->fault * 20; //+
                        $u->pen += (int)$rec->points;
                        $cstat[0][$lit] = ++$сstat[0][$lit];
                    }
                    $cstat[1][$lit] += $rec->fault;
                    $u->points += (int)$rec->points; //+
                }

                $user[$usern] = $u;
                ++$usern;
            }
        }
        $this->hh = $hh;
        $this->cstat = $cstat;
        $this->stat_user = $user;
        $this->usern = $usern;
    }
    
    public function get_hh() {
        return $this->hh;
    }
    
    public function get_cstat() {
        return $this->cstat;
    }
    
    public function get_stat_user() {
        return $this->stat_user;
    }
    
    public function get_usern() {
        return $this->usern;
    }
    
    public function get_username() {
        GLOBAL $DB;
        $hh = $this->hh;
        $user = $this->stat_user;
        if (isset($user)) {
            $list = array();
            foreach ($user as $i => $u) {
                if ((int)$u->id == 0)
                    continue;
                $list[] = $u->id;
            }
            $results = $DB->get_records_list('user', 'id', $list, null, 'id, firstname, lastname');
            foreach($results as $result) {
                $user[$hh[$result->id]]->name = format_string($result->firstname).' '.format_string($result->lastname);
                //$user[$hh[$row[0]]]->start_time = $row[2];
            };
        }
        $this->stat_user = $user;
        return $this->stat_user;
    }

    private function get_msg($data, $lid, $uid) {
        $cells = array();
        foreach ($lid as $tmp => $lit) {
            $rec = isset($data[$uid][$lit]) ? $data[$uid][$lit] : '';
                //var_dump($rec);
            if (!$rec) $msg = '&nbsp;';
            else {
                if ($rec->ac) {
                   $msg = "<font color=green>".$rec->points."&nbsp;"; //+
                   $msg .= '<sub>[';
                   //if ($rec->fault)
                       $msg .= (int)$rec->fault;
                   $time = formtime($rec->ac_time);
                   $msg .= "]</sub></font>";
                   //$msg .= "<br><font size=-2>$time</font></font>"; //+
                } else {
                    if ($rec->fault) {
                        $time = formtime($rec->submit_time);
                        //$msg .= "<br><font size=-2>$time</font></font>"; //+
                        $msg = "<font color=red>".$rec->points."&nbsp;"; //+
                        $msg .= "<sub>[$rec->fault]</sub></font>";
                    }
                    else $msg = '-';
                }
            }
            $cells[] = $msg;
        }
        return $cells;
    }

    public function get_data($data, $lid, $uid) {
        $table = array();
        
        $usern = $this->usern;
        $user = $this->stat_user;

        if ($usern) {
            usort($user, "user_cmp_for_school");
        }
        
        //$result = array();
        for ($i = 0; $i < $usern; ++$i) {
            $place[$i] = $i + 1;
            if ($i && ($user[$i - 1]->solved == $user[$i]->solved) && ($user[$i - 1]->pen == $user[$i]->pen)) $place[$i] = $place[$i - 1];
            //$result = array($place[$i], $user[$i]->name);
            $cells = array();
            $cells[] = $place[$i];
            $cells[] = $user[$i]->name;
            $uid = $user[$i]->id;
            foreach ($this->get_msg($data, $lid, $uid) as $msg) {
                $cells[] = $msg;
            }
            $cells[] = $user[$i]->solved;
            $cells[] = $user[$i]->pen;
            //$table->align = array('center','center','center','center','center');

            $table[] = $cells;
        }
        return $table;
    }
}

//ЗАГОЛОВОК

$table = new html_table();
$monitor = new monitor();
$monitor->set_header();
//$monitor->set_size();
$table->head = $monitor->get_header();
//$table->size = $monitor->get_size();

$results = $monitor->get_submits();
$users = $monitor->get_users();
$lid = $monitor->get_tasks_id();
unset($data);
$data = $monitor->init_data();

$monitor->calc_stat();
$hh = $monitor->get_hh();
$cstat = $monitor->get_cstat();
$user = $monitor->get_stat_user();
$usern = $monitor->get_usern();
$user = $monitor->get_username();

$table->data = $monitor->get_data($data, $lid, $uid);

$table->data[] = array('','<font color=green>Удачных решений:</font>',$cstat[0][0],$cstat[0][1],$cstat[0][1],'','');
$table->data[] = array('','<font color=red>Попыток:</font>',$cstat[1][0],$cstat[1][1],$cstat[1][2],'','');
$table->data[] = array('','Всего попыток:',$cstat[0][0]+$cstat[1][0],$cstat[0][1]+$cstat[1][1],$cstat[0][2]+$cstat[1][2],'','');
//Печатаем табличку
echo html_writer::table($table);

echo $OUTPUT->footer();