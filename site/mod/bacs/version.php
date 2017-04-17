<?php

/**
 *
 * @package    mod
 * @subpackage bacs
 */
defined('MOODLE_INTERNAL') || die();

$plugin->version = 2016081600;       // The current module version (Date: YYYYMMDDXX)
$plugin->requires = 2014051200;    // Requires this Moodle version
$plugin->component = 'mod_bacs'; // Full name of the plugin (used for diagnostics)
//Supported value is any of the predefined constants MATURITY_ALPHA, MATURITY_BETA, MATURITY_RC or MATURITY_STABLE.
$plugin->maturity = MATURITY_BETA;
$plugin->cron = 30; //run cron every 30 sekonds
$plugin->release = 'v0.0-r1';
