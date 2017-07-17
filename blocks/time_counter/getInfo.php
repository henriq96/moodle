<?php 
require_once '../../config.php';

$functionID = required_param('function', PARAM_INT);

//This returns user id of the current user
if ($functionID == 1) {
	echo $USER->id;
}



?>