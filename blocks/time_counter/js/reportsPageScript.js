
function showReports() {
	var courseList = document.getElementById('courses');
	var courseValue = courseList.options[courseList.selectedIndex].value;

	var roleList = document.getElementById('roles');
	var roleValue = roleList.options[roleList.selectedIndex].value;

	var moduleList = document.getElementById('modules');
	var moduleValue = moduleList.options[moduleList.selectedIndex].value;

	var usersList = document.getElementById('users');
	var userValue = usersList.options[usersList.selectedIndex].value;


	if (window.XMLHttpRequest) {
		xmlhttp = new XMLHttpRequest();
	} else {
		xmlhttp = new ActiveXObject('Microsoft.XMLHTTP');
	}
	xmlhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			document.getElementById('queryResult').innerHTML = this.responseText;
		}
	};
	xmlhttp.open('POST','/blocks/active_time_tracker/reports_handler/getReport.php',true);
	xmlhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	xmlhttp.send('user=' + userValue + '&course=' + courseValue + '&role=' + roleValue + '&module=' + moduleValue + '&export=0');
}

function exportTo(value) {
	var courseList = document.getElementById('courses');
	var courseValue = courseList.options[courseList.selectedIndex].value;

	var roleList = document.getElementById('roles');
	var roleValue = roleList.options[roleList.selectedIndex].value;

	var moduleList = document.getElementById('modules');
	var moduleValue = moduleList.options[moduleList.selectedIndex].value;

	var usersList = document.getElementById('users');
	var userValue = usersList.options[usersList.selectedIndex].value;

	
	var form = document.createElement("form");
	form.setAttribute("method", "post");
	form.setAttribute("action", "reports_handler/getReport.php");
	form.setAttribute("target", "_blank");

	
	
	var input1 = document.createElement('input');
    input1.type = 'hidden';
    input1.name = 'user'; // 'the key/name of the attribute/field that is sent to the server
    input1.value = userValue;
    form.appendChild(input1);
    
    var input2 = document.createElement('input');
    input2.type = 'hidden';
    input2.name = 'role'; // 'the key/name of the attribute/field that is sent to the server
    input2.value = roleValue;
    form.appendChild(input2);
    
    var input3 = document.createElement('input');
    input3.type = 'hidden';
    input3.name = 'course'; // 'the key/name of the attribute/field that is sent to the server
    input3.value = courseValue;
    form.appendChild(input3);
    
    var input4 = document.createElement('input');
    input4.type = 'hidden';
    input4.name = 'module'; // 'the key/name of the attribute/field that is sent to the server
    input4.value = moduleValue;
    form.appendChild(input4);
    
    var input5 = document.createElement('input');
    input5.type = 'hidden';
    input5.name = 'export'; // 'the key/name of the attribute/field that is sent to the server
    input5.value = value;
    form.appendChild(input5);
    
    
	document.body.appendChild(form);


	form.submit();
	
}

function getCourses() {
	if (window.XMLHttpRequest) {
		xmlhttp = new XMLHttpRequest();
	} else {
		xmlhttp = new ActiveXObject('Microsoft.XMLHTTP');
	}
	xmlhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			getCoursesWithID(this.responseText);
		}
	};
	xmlhttp.open('POST','/blocks/active_time_tracker/getInfo.php',true);
	xmlhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	xmlhttp.send('function=1');
}


function getCoursesWithID(userID) {
	document.getElementById('modules').innerHTML = '<option value="0">-</option>';
	document.getElementById('users').innerHTML = '<option value="0">-</option>';
	var e = document.getElementById('roles');
	var roleValue = e.options[e.selectedIndex].value;


	if (roleValue == 0) {
		alert('Please select a role!');
	}

	if (window.XMLHttpRequest) {
		xmlhttp = new XMLHttpRequest();
	} else {
		xmlhttp = new ActiveXObject('Microsoft.XMLHTTP');
	}
	xmlhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			document.getElementById('courses').innerHTML = this.responseText;
		}
	};
	xmlhttp.open('POST','/blocks/active_time_tracker/reports_handler/getCoursesForUser.php',true);
	xmlhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	xmlhttp.send('user=' + userID + '&role=' + roleValue);

}

function getModulesAndUsers() {
	var e = document.getElementById('courses');
	var courseValue = e.options[e.selectedIndex].value;
	
	var d = document.getElementById('roles');
	var roleValue = d.options[d.selectedIndex].value;
	
	if (roleValue == 1 || roleValue == 3) {
		document.getElementById('resourcesSelect').style.visibility = 'visible';
		document.getElementById('usersSelect').style.visibility = 'visible';
	}
	
	document.getElementById('modules').innerHTML = '<option value="0">-</option>';
	document.getElementById('users').innerHTML = '<option value="0">-</option>';
	if (courseValue != 'a' && courseValue != 0 && roleValue != 5) {
		getModules(courseValue);
		getUsers(courseValue, roleValue);
	}
}


function getModules(courseValue) {
	if (window.XMLHttpRequest) {
		xmlhttp = new XMLHttpRequest();
	} else {
		xmlhttp = new ActiveXObject('Microsoft.XMLHTTP');
	}
	xmlhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			document.getElementById('modules').innerHTML = this.responseText;
		}
	};
	xmlhttp.open('POST','/blocks/active_time_tracker/reports_handler/getModulesForCourse.php',true);
	xmlhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	xmlhttp.send('course=' + courseValue);

}


function getUsers(courseValue, roleValue) {
	if (window.XMLHttpRequest) {
		xmlhttp = new XMLHttpRequest();
	} else {
		xmlhttp = new ActiveXObject('Microsoft.XMLHTTP');
	}
	xmlhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			document.getElementById('users').innerHTML = this.responseText;
		}
	};
	xmlhttp.open('POST','/blocks/active_time_tracker/reports_handler/getUsersForCourse.php',true);
	xmlhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	xmlhttp.send('course=' + courseValue + '&role=' + roleValue);

}