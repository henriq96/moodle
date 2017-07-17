<?php 



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






?>