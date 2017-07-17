<?php 

require_once('../../../config.php');


function compareSection($a, $b) {
	return strcmp($a->section, $b->section);
}

$course_id = required_param('course', PARAM_INT);

$modules = $DB->get_records_sql('
		SELECT {course_modules}.id, {course_modules}.module, {course_modules}.instance, {course_sections}.section, {course_sections}.name
		FROM {course_modules}
		JOIN {course_sections} ON {course_modules}.section={course_sections}.id
		WHERE {course_modules}.course = ?', array($course_id));


usort($modules, "compareSection");


$response = "<option value='-1'>All</option>";

$currentSection = -1;
foreach ($modules as $i => $record) {
	if ($record->section != $currentSection) {
		$currentSection = $record->section;
		if ($record->name == NULL) {
			$response = $response . "<option disabled><b>Section $record->section</b></option>";
		}
		else {
			$response = $response . "<option disabled><b>$record->name</b></option>";
		}
	}
	
	//Get module type name
	$moduleType = $DB->get_record_sql('
			SELECT {modules}.name
			FROM {modules}
			WHERE {modules}.id = ?', array($record->module));
	
	//Get module name
	$mod = $DB->get_record($moduleType->name, array('id' => $record->instance));
	
	
	$response = $response . "<option value='$record->module:$record->instance'>$mod->name</option>";
	
}

echo $response;



?>