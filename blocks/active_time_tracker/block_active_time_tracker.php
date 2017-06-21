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
    	global $DB;
    	
    	$moduleType = $DB->get_record('modules', array('id'=>$PAGE->cm->module));
    	
    	if ($moduleType != NULL)
    		$moduleName = $DB->get_record($moduleType->name, array('id'=>$PAGE->cm->instance));
    	
    	
    	$course = $DB->get_record('course', array('id'=>$PAGE->course->id));
    	
    	
    	$javascript = "<br><br>Counter:<p id='counter'></p><br>Timeout in:<p id='timeout'></p>";
    	
    	$javascript = $javascript . "<script>
			var counter = 0;
			var timeout = 120;
			var clock = setInterval(function(){ runCounter() }, 1000);
			window.onmousemove = function(){resetTimeout()};
			window.onscroll = function(){resetTimeout()};
			window.onbeforeunload = function(){alert('bye');};
    			
			function runCounter() {
				counter++;
				timeout--;
    			
				document.getElementById('counter').innerHTML = counter;
				document.getElementById('timeout').innerHTML = timeout;
    			
				if (timeout == 0) {
					clearInterval(clock);
				}
    			
			}
    			
			function resetTimeout() {

				if (timeout == 0) {
					counter = 0;
					clock = setInterval(function(){ runCounter() }, 1000);
				}

				timeout = 120;
			}
			</script>";
    	
    	
    	$this->content->text = "This course is: $course->fullname<br><br>This module type is: $moduleType->name<br><br>This module name is: $moduleName->name" .  $javascript;
    	
    	echo '<br><br><br>';
    	//print_object($PAGE->context);
    	//print_object($PAGE->cm);
    	
    	
    	return $this->content;
    }
    
    
    
    
    public function instance_allow_multiple() {
    	return false;
    }
}