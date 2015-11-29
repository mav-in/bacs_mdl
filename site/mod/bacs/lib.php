<?php

/**
 *
 * @package    mod
 * @subpackage bacs
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @global object
 * @param object $bacs
 * @return bool|int
 */
function bacs_add_instance($bacs) {
  global $DB;

  return $DB->insert_record("bacs", $bacs);
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @global object
 * @param object $bacs
 * @return bool
 */
function bacs_update_instance($bacs) {
  global $DB;

  $bacs->id = $bacs->instance;

  return $DB->update_record("bacs", $bacs);
}

/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @global object
 * @param int $id
 * @return bool
 */
function bacs_delete_instance($id) {
  global $DB;

  if (!$bacs = $DB->get_record("bacs", array("id" => $id)))
    {
    return false;
    }

  $result = true;

  if (!$DB->delete_records("bacs", array("id" => $bacs->id)))
    {
    $result = false;
    }

  return $result;
}

function bacs_cron() {

  return true;
}
