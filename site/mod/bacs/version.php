<?php

/**
 *
 * @package    mod
 * @subpackage bacs
 */
defined('MOODLE_INTERNAL') || die();

$module->version = 2014051101;       // The current module version (Date: YYYYMMDDXX)
$module->requires = 2013110500;    // Requires this Moodle version
$module->component = 'mod_bacs'; // Full name of the plugin (used for diagnostics)
$module->maturity = MATURITY_BETA;
$module->cron = 30; //run cron every 30 sekonds
