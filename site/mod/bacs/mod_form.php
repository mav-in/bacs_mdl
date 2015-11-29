<?php

/**
 *
 * @package    mod
 * @subpackage bacs
 */
defined('MOODLE_INTERNAL') || die();

require_once ($CFG->dirroot . '/course/moodleform_mod.php');

class mod_bacs_mod_form extends moodleform_mod {

  function definition() {

    $mform = $this->_form;

    $mform->addElement('text', 'name', get_string('contestname', 'bacs'), array('size' => '50'));
    $mform->setType('name', PARAM_TEXT);
    $mform->addRule('name', null, 'required', null, 'client');

    $mform->addElement('date_time_selector', 'starttime', get_string('from', 'bacs'),array('startyear' => 2014, 'stopyear'  => 2020,'step' => 10));
    $mform->addElement('date_time_selector', 'endtime', get_string('to', 'bacs'),array('startyear' => 2014, 'stopyear'  => 2020,'step' => 10));
    
    $mform->addElement('advcheckbox', 'upsolving', get_string('upsolving', 'bacs'),'',array('group' => 1),array(0, 1));
    $mform->addHelpButton('upsolving', 'upsolving', 'bacs');

    $this->standard_coursemodule_elements();

    $this->add_action_buttons(true, false, null);
  }

}
