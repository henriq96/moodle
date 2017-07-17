<?php
require_once('../../config.php');

global $USER;

$user_id = required_param('user', PARAM_INT);
$course_id = required_param('course', PARAM_INT);
$module_type_id = required_param('module', PARAM_INT);
$module_instance_id = required_param('instance', PARAM_INT);
$time_spent = required_param('time', PARAM_INT);
$current_time = time();

if ($user_id != $USER->id)
	exit("Session Expired");

$timeUsedRecord = $DB->get_record('time_of_use', array('user_id'=>$user_id, 'course_id'=>$course_id, 'module_type_id'=>$module_type_id, 'module_instance_id'=>$module_instance_id));

$record1 = new stdClass();
$record1->user_id = $user_id;
$record1->course_id = $course_id;
$record1->module_type_id = $module_type_id;
$record1->module_instance_id = $module_instance_id;
$record1->time_spent = $time_spent;
$record1->last_visited = $current_time;

if ($timeUsedRecord != NULL) {
	$record1->id = $timeUsedRecord->id;
	$DB->update_record('time_of_use', $record1, $bulk=false);
}
else {
	$DB->insert_record('time_of_use', $record1, false);
}



