<?php 

require_once('../../../config.php');
require_once('../../../lib/coursecatlib.php');


$user_id = required_param('user', PARAM_INT);
$role_id = required_param('role', PARAM_INT);

$response = "";

//Get all courses in a category recursive when it's a manager
if ($role_id == 1) {
	$managingCategories = $DB->get_records_sql('
		SELECT {context}.id, {context}.instanceid
		FROM {role_assignments}
		JOIN {context} ON {context}.id={role_assignments}.contextid
		WHERE {role_assignments}.roleid = ? AND {role_assignments}.userid = ? AND {context}.contextlevel = ?', array($role_id, $user_id, 40));
	
	foreach ($managingCategories as $i => $categoryid) {
		$coursesInCategory = coursecat::get($categoryid->instanceid)->get_courses(array('recursive' => true));
		foreach ($coursesInCategory as $id => $record) {
			$response = $response . "<option value='$record->id'>$record->fullname</option>";
		}
	}
}
//Get all courses for which teacher or students are enrolled
else {
	$result = $DB->get_records_sql('
		SELECT {role_assignments}.id, {course}.id, {course}.fullname
		FROM {role_assignments}
		JOIN {context} ON {context}.id={role_assignments}.contextid
		JOIN {course} ON {context}.instanceid={course}.id
		WHERE {role_assignments}.roleid = ? AND {role_assignments}.userid = ? AND {context}.contextlevel = ?', array($role_id, $user_id, 50));
	
	foreach ($result as $i => $record) {
		$response = $response . "<option value='$record->id'>$record->fullname</option>";
	}
}

if ($response != "") {
	$response = "<option value='-1'>All</option>" . $response;
}
else {
	$response = "<option value='0'>-</option>";
}
	


echo $response;








?>