<?php



class block_active_time_tracker extends block_base {
	
    public function init() {
        $this->title = get_string('active_time_tracker', 'block_active_time_tracker');
    }
    
    // The PHP tag and the curly bracket for the class definition 
    // will only be closed after there is another function added in the next section.
    
    private function get_javascript() {
    	
    	
    	return $javascript;
    }
    
    
    public function get_content() {
    	if ($this->content !== null) {
    		return $this->content;
    	}
    	
    	$this->content =  new stdClass;
    	
    	global $PAGE;
    	global $USER;
    	global $DB;
    	
    	$moduleTypeID = $PAGE->cm->module;
    	
    	$moduleType = $DB->get_record('modules', array('id'=>$moduleTypeID));
    	
    	$moduleInstanceID= 0;
    	
    	if ($moduleType != NULL) {
    		$moduleInstanceID = $PAGE->cm->instance;
    		$moduleName = $DB->get_record($moduleType->name, array('id'=>$PAGE->cm->instance));
    	}
    	else {
    		$moduleTypeID = 0;
    	}
    	
    	$courseID = $PAGE->course->id;
    	$course = $DB->get_record('course', array('id'=>$courseID));
    	
    	$userID = $USER->id;
    	
    	$timeUsedRecord = $DB->get_record('time_of_use', array('user_id'=>$userID, 'course_id'=>$courseID, 'module_type_id'=>$moduleTypeID, 'module_instance_id'=>$moduleInstanceID));
    	if ($timeUsedRecord != null) {
    		$timeUsed = $timeUsedRecord->time_spent;
    	}
    	else {
    		$timeUsed = 0;
    	}
    	
    	$totalTime = $DB->get_records('time_of_use', array('user_id'=>$userID, 'course_id'=>$courseID));
    	$totalTimeUsed = 0;
    	
    	if ($totalTime!= null) {
    		foreach ($totalTime as $i => $record) {
    			$totalTimeUsed += $record->time_spent;
    		}
    	}
    	
    	
    	$html = "
				<link rel='stylesheet' href='/blocks/active_time_tracker/flipclock.css'>
				<script src='/blocks/active_time_tracker/jslibraries/jquery-3.2.1.min.js'></script>
				<script src='/blocks/active_time_tracker/jslibraries/flipclock.min.js'></script>
				
				<center>
					<div class='clock'></div>
					<p id='totalTimeCounter' style='font-family:verdana; font-size: 100%; border: 2px solid; color: white; background-color: black;'></p>
					<p id='state' style='font-family: courier; font-size: 160%;'></p>
					<p id='state'></p>
					<b><p>$course->fullname</p></b>
					<button onclick='openReports()'>Details</button>
				</center>
				<br>
				<p id='server'></p>";
    	
    	$javascript = $html. "
			<script>
			
			var counter = $timeUsed;
			var totalTimeCounter = $totalTimeUsed;
			var courseID = $courseID;
			var userID = $USER->id;
			var moduleType = $moduleTypeID;
			var moduleInstance = $moduleInstanceID;

			
			</script>
			<script src='/blocks/active_time_tracker/blockScript.js'>
			</script>";
    	
    	
    	$this->content->text = $javascript;
    	
    	echo '<br><br><br>';
    	//print_object($PAGE->context);
    	//print_object($PAGE->cm);
    	
    	
    	return $this->content;
    }
    
    
    
    
    public function instance_allow_multiple() {
    	return false;
    }
}