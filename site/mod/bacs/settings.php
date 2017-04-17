<?php

/**
 *
 * @package    mod
 * @subpackage bacs
 */
defined('MOODLE_INTERNAL') || die();

//Check what fulltree means!
//if ($ADMIN->fulltree) {
    $settings->add(
        new admin_setting_configcheckbox(
            'bacs/checkpoint',
            get_string('checkpoint', 'mod_bacs'),
            get_string('configcheckpoint', 'mod_bacs'),
            1
        )
    );
    $settings->add(
        new admin_setting_configtext(
            'bacs/numberfield',
            get_string('numberfield', 'mod_bacs'),
            get_string('confignumberfield', 'mod_bacs'),
            777,
            PARAM_INT,
            6
        )
    );
//}
