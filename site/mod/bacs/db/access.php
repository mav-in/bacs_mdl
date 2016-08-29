<?php

/**
 *
 * @package    local
 * @subpackage bacs
 */
defined('MOODLE_INTERNAL') || die();

$capabilities = array(
    'mod/bacs:addinstance' => array(
        'riskbitmask' => RISK_SPAM | RISK_PERSONAL | RISK_XSS | RISK_CONFIG | RISK_DATALOSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            //'coursecreator' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            //'teacher' => CAP_ALLOW,
            //'student' => CAP_ALLOW,
            //'guest' => CAP_ALLOW,
            //'user' => CAP_ALLOW,
            //'fontpage' => CAP_ALLOW,
        ),
        'clonepermissionsfrom' => 'moodle/course:manageactivities'
    ),

    'mod/bacs:view' => array(

        'captype' => 'read',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            //'coursecreator' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
            'student' => CAP_ALLOW,
            //'guest' => CAP_ALLOW,
            //'user' => CAP_ALLOW,
            //'fontpage' => CAP_ALLOW,
        )
    ),
    
    'mod/bacs:submit' => array(

        'riskbitmask' => RISK_SPAM,
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            //'coursecreator' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
            'student' => CAP_ALLOW,
            //'guest' => CAP_ALLOW,
            //'user' => CAP_ALLOW,
            //'fontpage' => CAP_ALLOW,
        )
    ),
);
