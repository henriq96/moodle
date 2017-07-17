<?php 
require_once('../../../config.php');

$course_id = required_param('course', PARAM_INT);
$role_id = required_param('role', PARAM_INT);


//Only show students its name
if ($role_id == 5) {
	$user = $DB->get_record_sql('
			SELECT {user}.firstname, {user}.lastname
			FROM {user}
			WHERE {user}.id = ?', array($USER->id));
	
	echo "<option value='$USER->id'>$user->firstname $user->lastname</option>";
}


//Only show teachers the students
if ($role_id == 3) {
	//maybe make sure that this user is a teacher for the course?
	
	echo "<option value='-1'>All</option>";
	
	$users = $DB->get_records_sql('
		SELECT {user}.id ,{user}.firstname, {user}.lastname
		FROM {role_assignments}
		JOIN {context} ON {context}.id = {role_assignments}.contextid
		JOIN {user} ON {user}.id = {role_assignments}.userid
		WHERE {context}.contextlevel=50 AND {context}.instanceid=? AND {role_assignments}.roleid=5', array($course_id));
	
	foreach ($users as $i => $user) {
		echo "<option value='$user->id'>$user->firstname $user->lastname</option>";
	}
	
	
}


//Show managers any role
if ($role_id == 1) {
	//maybe make sure that this user is a manager for the course?
	
	echo "<option value='-1'>All</option>";
	
	$users = $DB->get_records_sql('
		SELECT {user}.id ,{user}.firstname, {user}.lastname, {role_assignments}.roleid
		FROM {role_assignments}
		JOIN {context} ON {context}.id = {role_assignments}.contextid
		JOIN {user} ON {user}.id = {role_assignments}.userid
		WHERE {context}.contextlevel=50 AND {context}.instanceid=?', array($course_id));
	
	foreach ($users as $i => $user) {
		if ($user->roleid == 5) {
			echo "<option value='$user->id'>$user->firstname $user->lastname (Student)</option>";
		}
		else if ($user->roleid == 3) {
			echo "<option value='$user->id'>$user->firstname $user->lastname (Teacher)</option>";
		}
		else {
			echo "<option value='$user->id'>$user->firstname $user->lastname (Unknown)</option>";
		}
	}
	
	
}






?>