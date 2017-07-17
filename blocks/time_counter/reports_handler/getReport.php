<?php 

require_once('../../../config.php');
require_once('../../../lib/coursecatlib.php');
require_once('reportsClass.php');
include('../mpdf60/mpdf.php');

error_reporting(-1);

$user_id = required_param('user', PARAM_INT);
$role_id = required_param('role', PARAM_INT);
$course_id = required_param('course', PARAM_INT);
$module_id = required_param('module', PARAM_TEXT);
$export = required_param('export', PARAM_INT);





/**
 * Compare function to organize modules by section
 * 
 * @param unknown $a
 * @param unknown $b
 * @return unknown
 */
function compareSection($a, $b) {
	return strcmp($a->section, $b->section);
}






/**
 * Converts seconds into the format H:M:S
 * 
 * @param unknown $seconds
 * @return number
 */
function convert($seconds) {
	$hours = Math.floor(seconds / 3600);
	$minutes = Math.floor(seconds % 3600 / 60);
	$seconds = Math.floor(seconds % 3600 % 60);
	return ((hours > 0 ? hours + ':' + (minutes < 10 ? '0' : '') : '') + minutes + ':' + (seconds < 10 ? '0' : '') + seconds);
}






/**
 * Returns time of last visit given the list of all modules
 * 
 * @param unknown $allModulesOfCourse
 * @return number
 */
function getLastVisit($allModulesOfCourse) {
	$currentLastVisit = 99999999999999;
	foreach($allModulesOfCourse as $i => $record) {
		if ($record->last_visited < $currentLastVisit) {
			$currentLastVisit = $record->last_visited;
		}
	}
	
	if ($currentLastVisit == 99999999999999)
		return 0;
	else
		return $currentLastVisit;
}






/**
 * Returns the total time spent given the list of all records
 * 
 * @param unknown $allModulesOfCourse
 * @return number
 */
function getSumOfTimeSpent($allModulesOfCourse) {
	$sum = 0;
	foreach($allModulesOfCourse as $i => $record) {
		$sum += $record->time_spent;
	}
	return $sum;
}





/**
 * Returns the name of the course given the id
 * 
 * @param unknown $courseID
 * @return unknown
 */
function getCourseName($courseID) {
	global $DB;
	
	$course = $DB->get_record_sql('
			SELECT {course}.fullname
			FROM {course}
			WHERE {course}.id = ?', array($courseID));
	
	return $course->fullname;
}





/**
 * Returns all the data of a user for a specific course
 * 
 * @param unknown $courseID
 * @param unknown $userID
 * @return unknown
 */
function getAllDataOfUserForCourse($courseID, $userID) {
	global $DB;
	
	//Get all the data for this user
	$allDataForThisCourse = $DB->get_records_sql('
			SELECT {time_of_use}.id, {time_of_use}.time_spent, {time_of_use}.last_visited 
			FROM {time_of_use}
			WHERE {time_of_use}.user_id=? AND {time_of_use}.course_id=?', array($userID, $courseID));
	
	return $allDataForThisCourse;
}



/**
 * Returns the name of the module given the type and instance
 *
 * @param unknown $courseID
 * @return unknown
 */
function getModuleName($moduleTypeID, $moduleInstanceID) {
	global $DB;
	
	//Get module type name
	$moduleType = $DB->get_record_sql('
			SELECT {modules}.name
			FROM {modules}
			WHERE {modules}.id = ?', array($moduleTypeID));
	
	//Get module name
	$moduleInstance = $DB->get_record($moduleType->name, array('id' => $moduleInstanceID));
	
	return $moduleInstance->name;
}





/**
 * Returns a list of modules sorted by section
 * 
 * @param unknown $courseID
 */
function getAllModulesForCourseSorted($courseID) {
	global $DB;
	
	$modules = $DB->get_records_sql('
			SELECT {course_modules}.id, {course_modules}.module, {course_modules}.instance, {course_sections}.section, {course_sections}.name
			FROM {course_modules}
			JOIN {course_sections} ON {course_modules}.section={course_sections}.id
			WHERE {course_modules}.course = ?', array($courseID));
	
	
	usort($modules, "compareSection");
	
	return $modules;
}






/**
 * Returns the list of users participating in course depending on the permission of the requester
 * 
 * @param unknown $course
 * @param unknown $role
 * @return unknown|NULL
 */
function getAllUsersParticipatingInCourse($course, $role) {
	global $DB;
	
	switch ($role) {
		//Managers can see any role
		case 1:
			return $DB->get_records_sql('
						SELECT {user}.id ,{user}.firstname, {user}.lastname, {role_assignments}.roleid
						FROM {role_assignments}
						JOIN {context} ON {context}.id = {role_assignments}.contextid
						JOIN {user} ON {user}.id = {role_assignments}.userid
						WHERE {context}.contextlevel=50 AND {context}.instanceid=?', array($course));
			break;
		//Techers can only see students
		case 3:
			return $DB->get_records_sql('
						SELECT {user}.id ,{user}.firstname, {user}.lastname, {role_assignments}.roleid
						FROM {role_assignments}
						JOIN {context} ON {context}.id = {role_assignments}.contextid
						JOIN {user} ON {user}.id = {role_assignments}.userid
						WHERE {context}.contextlevel=50 AND {context}.instanceid=? AND {role_assignments}.roleid=5', array($course));
			break;
		default:
			return NULL;
	}
}








/**
 * Returns the table of the course by user
 *
 * @param int $courseID
 * @param int $roleID
 * @return ReportTable
 */
function getCourseReportByUser($courseID, $roleID) {
	global $DB;
	
	$table = new ReportTable(getCourseName($courseID), "User");
	
	//Get all users that participate in this course
	$users = getAllUsersParticipatingInCourse($courseID, $roleID);
	
	
	foreach ($users as $i => $record) {
		$userType = "";
		
		switch ($record->roleid) {
			case 3:
				$userType = "(Teacher)";
				break;
			case 5:
				$userType = "(Student)";
				break;
			default:
				$userType = "(Unknown)";
		}
		
		
		//Get all the data for this user
		$allDataForThisCourse = getAllDataOfUserForCourse($courseID, $record->id);

		$entry = new ReportEntry($record->firstname . $record->lastname . $userType, getLastVisit($allDataForThisCourse), getSumOfTimeSpent($allDataForThisCourse));
		
		$table->insertEntry($entry);
	}
	
	return $table;
	
}







/**
 * Returns the table of the course module by user
 *
 * @param unknown $courseID
 * @return unknown
 */
function getCourseModuleReportByUser($courseID, $moduleTypeID, $moduleInstanceID, $roleID) {
	global $DB;
	
	$table = new ReportTable(getCourseName($courseID) . " -> " . getModuleName($moduleTypeID, $moduleInstanceID), "User");
	
	//Get all users that participate in this course depending on permission
	$users = getAllUsersParticipatingInCourse($courseID, $roleID);
	
	
	//For each user get an entry of total time spent in course
	foreach ($users as $i => $record) {
		$userType = "";
		
		switch ($record->roleid) {
			case 3:
				$userType = "(Teacher)";
				break;
			case 5:
				$userType = "(Student)";
				break;
			default:
				$userType = "(Unknown)";
		}
		
		
		//Get all the data for this user
		$dataForThisCourseModule = $DB->get_record_sql('
			SELECT {time_of_use}.id, {time_of_use}.time_spent, {time_of_use}.last_visited
			FROM {time_of_use}
			WHERE {time_of_use}.user_id=? AND {time_of_use}.course_id=? AND {time_of_use}.module_type_id=? AND {time_of_use}.module_instance_id=?', 
				array($record->id, $courseID, intval($moduleTypeID), intval($moduleInstanceID)));
		
		if ($dataForThisCourseModule != NULL) {
			$table->insertEntry($record->firstname . $record->lastname . $userType, $dataForThisCourseModule->last_visited, $dataForThisCourseModule->time_spent);
		}
		else {
			$table->insertEntry($record->firstname . $record->lastname . $userType, 0, 0);
		}
	}
	
	return $table;
	
}








/**
 * Returns a table of the time accumulated by a user in specific resources and activities of a course
 * 
 * @param unknown $courseID
 * @param unknown $userID
 */
function getCourseReportByModuleForUser($courseID, $userID) {
	global $DB;
	
	
	
	$modules = getAllModulesForCourseSorted ( $courseID );
	
	$user = $DB->get_record_sql ( '
			SELECT {user}.id, {user}.firstname, {user}.lastname
			FROM {user}
			WHERE {user}.id=?', array (
			$userID
	) );
	
	$name = getCourseName ( $courseID ) . " --- " . $user->firstname . " " . $user->lastname;
	
	$table = new ReportTable($name, "Module");
	
	// Get main page data
	$dataForMainPage = $DB->get_record_sql ( '
				SELECT {time_of_use}.id, {time_of_use}.time_spent, {time_of_use}.last_visited
				FROM {time_of_use}
				WHERE {time_of_use}.user_id=? AND {time_of_use}.course_id=? AND {time_of_use}.module_type_id=? AND {time_of_use}.module_instance_id=?', array (
			$userID,
			$courseID,
			0,
			0 
	) );
	
	
	if ($dataForMainPage != NULL) {
		$entry = new ReportEntry("Main Page", $dataForMainPage->last_visited, $dataForMainPage->time_spent);
		$entry->isSection = 1;
		$table->insertEntry($entry);
	}
	
	// Collect data for each section
	$currentSection = 0;
	$currentSectionTimeAccumulated = 0;
	$currentSectionLastVisit = 99999999999999;
	$currentSectionModules = array();
	
	foreach ( $modules as $i => $record ) {
		// When we change section print data for last section
		if ($record->section != $currentSection) {
			if ($currentSectionLastVisit == 99999999999999)
				$currentSectionLastVisit = 0;
			
			$entry = new ReportEntry("Section " . $currentSection, $currentSectionLastVisit, $currentSectionTimeAccumulated);
			$entry->isSection = 1;
			$table->insertEntry($entry);
			
			foreach ($currentSectionModules as $moduleInSection)
				$table->insertEntry($moduleInSection);
			
			$currentSection = $record->section;
			$currentSectionTimeAccumulated = 0;
			$currentSectionLastVisit = 99999999999999;
			$currentSectionModules = array();
		}
		
		// Get a specific module
		$dataForModule = $DB->get_record_sql ( '
				SELECT {time_of_use}.id, {time_of_use}.time_spent, {time_of_use}.last_visited
				FROM {time_of_use}
				WHERE {time_of_use}.user_id=? AND {time_of_use}.course_id=? AND {time_of_use}.module_type_id=? AND {time_of_use}.module_instance_id=?', array (
				$userID,
				$courseID,
				$record->module,
				$record->instance 
		) );
		
		if ($dataForModule != NULL) {			
			$entry = new ReportEntry(getModuleName ( $record->module, $record->instance ), $dataForModule->last_visited, $dataForModule->time_spent);
			
			array_push($currentSectionModules, $entry);
			
			$currentSectionTimeAccumulated += $dataForModule->time_spent;
			
			if ($currentSectionLastVisit > $dataForModule->last_visited) {
				$currentSectionLastVisit = $dataForModule->last_visited;
			}
		}
	} // for
	
	
	if ($currentSectionLastVisit == 99999999999999)
		$currentSectionLastVisit = 0;
		
	$entry = new ReportEntry("Section " . $currentSection, $currentSectionLastVisit, $currentSectionTimeAccumulated);
	$entry->isSection = 1;
	$table->insertEntry($entry);
		
	foreach ($currentSectionModules as $moduleInSection)
		$table->insertEntry($moduleInSection);
	
	return $table;
}





$tables = array();

if ($role_id == 5) {
	//If user selects all courses return table with all user courses
	if ($course_id == -1) {
		
		$courses = $DB->get_records_sql('
			SELECT {course}.id, {course}.fullname
			FROM {role_assignments}
			JOIN {context} ON {context}.id={role_assignments}.contextid
			JOIN {course} ON {context}.instanceid={course}.id
			WHERE {role_assignments}.roleid = ? AND {role_assignments}.userid = ? AND {context}.contextlevel = ?', array($role_id, $USER->id, 50));
		
		
		$table = new ReportTable("All Courses", "Course Name");
		
		foreach ($courses as $i => $record) {
			$allDataForThisCourse = getAllDataOfUserForCourse($record->id, $USER->id);
			
			$entry = new ReportEntry($record->fullname, getLastVisit($allDataForThisCourse), getSumOfTimeSpent($allDataForThisCourse));
			
			$table->insertEntry($entry);
		}
		
		array_push($tables, $table);
		
	}
	
	
	//If it's a specific course
	else {
		array_push($tables, getCourseReportByModuleForUser($course_id, $USER->id));
	}
	
}

else if ($role_id == 3) {
	if ($course_id != -1) {
		if ($user_id == -1) {
			if ($module_id == -1)
				array_push($tables, getCourseReportByUser($course_id, $role_id));
			else {
				$array = preg_split('[:]', $module_id);
				array_push($tables, getCourseModuleReportByUser($course_id, $array[0], $array[1], $role_id));
			}
		}
		else if ($user_id >= 1) {
			array_push($tables, getCourseReportByModuleForUser($course_id, $user_id));
		}
	}
	else {
		$result = $DB->get_records_sql('
		SELECT {role_assignments}.id, {course}.id, {course}.fullname
		FROM {role_assignments}
		JOIN {context} ON {context}.id={role_assignments}.contextid
		JOIN {course} ON {context}.instanceid={course}.id
		WHERE {role_assignments}.roleid = ? AND {role_assignments}.userid = ? AND {context}.contextlevel = ?', array($role_id, $USER->id, 50));
		
		foreach ($result as $i => $record) {
			array_push($tables, getCourseReportByUser($record->id, $role_id));
		}
	}
	
}

else if ($role_id == 1) {
	if ($course_id != -1) {
		if ($user_id == -1) {
			if ($module_id == -1)
				array_push($tables, getCourseReportByUser($course_id, $role_id));
			else {
				$array = preg_split('[:]', $module_id);
				array_push($tables, getCourseModuleReportByUser($course_id, $array[0], $array[1], $role_id));
			}
				
		}
		else if ($user_id >= 1) {
			array_push($tables, getCourseReportByModuleForUser($course_id, $user_id));
		}
	}
	else {
		$managingCategories = $DB->get_records_sql('
			SELECT {context}.id, {context}.instanceid
			FROM {role_assignments}
			JOIN {context} ON {context}.id={role_assignments}.contextid
			WHERE {role_assignments}.roleid = ? AND {role_assignments}.userid = ? AND {context}.contextlevel = ?', 
				array($role_id, $USER->id, 40));
		
		foreach ($managingCategories as $i => $categoryid) {
			$coursesInCategory = coursecat::get($categoryid->instanceid)->get_courses(array('recursive' => true));
			foreach ($coursesInCategory as $id => $record) {
				array_push($tables, getCourseReportByUser($record->id, $role_id));
			}
		}
	}
	
}

else {
	echo "<p>Unknow Role!</p>";
	exit();
}

$output = "";

foreach ($tables as $table)
	$output .= $table->getHtmlTable();

if ($export == 1) {
	$mpdf=new mPDF('c');
	$stylesheet = file_get_contents('../reportsPageStyle.css'); // external css
	$mpdf->WriteHTML($stylesheet,1);
	$mpdf->WriteHTML($output,2);
	$mpdf->Output();
	exit();
}

else if ($export == 2) {
	// Open the output stream
	$fh = fopen('php://output', 'w');
	
	// Start output buffering (to capture stream contents)
	ob_start();
	
	// CSV Header
	$header = array('Table', $table->tableSorting, 'Last Visit', 'Time Accumulated');
	fputcsv($fh, $header);
	
	foreach ($tables as $table) {
		
		// CSV Data
		foreach ($table->arrayOfEntries as $entry) {
			if ($entry->entryLastVisit > 0)
				$line = array($table->tableName, $entry->entryName, gmdate("M d Y", $entry->entryLastVisit), gmdate("H:i:s", $entry->entryTimeAccumulated));
			else
				$line = array($table->tableName, $entry->entryName, 'Never', '00:00:00');
				
			fputcsv($fh, $line);
		}
		$line = array($table->tableName, 'Total', ' ', gmdate("H:i:s", $table->getTotalTimeOfEntries()));
		fputcsv($fh, $line);
		
		$emptyline = array(' ', ' ', ' ', ' ');
		fputcsv($fh, $emptyline);
		fputcsv($fh, $emptyline);
		fputcsv($fh, $emptyline);
		fputcsv($fh, $emptyline);
		
	}
	
	// Get the contents of the output buffer
	$string = ob_get_clean();
	
	// Set the filename of the download
	$filename = 'report';
	
	// Output CSV-specific headers
	header('Pragma: public');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Cache-Control: private', false);
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename="' . $filename . '.csv";');
	header('Content-Transfer-Encoding: binary');
	
	// Stream the CSV data
	exit($string);
}

echo $output;
	
	
	
	
?>