var timeout = 20;
var clock = setInterval(function(){ runCounter() }, 1000);
setInterval(function(){ sendTime() }, 60000);

var clocky = new FlipClock($('.clock'), {
});
clocky.stop();

document.getElementById('state').innerHTML = 'Active';
document.getElementById('state').style.color = 'green';

window.onmousemove = function(){resetTimeout()};
window.onscroll = function(){resetTimeout()};
window.onbeforeunload = function(){sendTime()};


function convert(seconds) {
	seconds = Number(seconds);
	var hours = Math.floor(seconds / 3600);
	var minutes = Math.floor(seconds % 3600 / 60);
	var seconds = Math.floor(seconds % 3600 % 60);
	return ((hours > 0 ? hours + ':' + (minutes < 10 ? '0' : '') : '') + minutes + ':' + (seconds < 10 ? '0' : '') + seconds); 
}


function runCounter() {
	counter++;
	totalTimeCounter++;
	timeout--;


	clocky.setTime(totalTimeCounter);
	clocky.flip();

	if (timeout <= 0) {
		sendTime();
		clearInterval(clock);
		document.getElementById('state').innerHTML = 'Not Active';
		document.getElementById('state').style.color = 'red';
	}


}


function resetTimeout() {

	if (timeout == 0) {
		clock = setInterval(function(){ runCounter() }, 1000);
		document.getElementById('state').innerHTML = 'Active';
		document.getElementById('state').style.color = 'green';
	}
	timeout = 20;

}


function sendTime() {
	if (window.XMLHttpRequest) {
		xmlhttp = new XMLHttpRequest();
	} else {
		// code for IE6, IE5
		xmlhttp = new ActiveXObject('Microsoft.XMLHTTP');
	}
	xmlhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			var messageExpired = 'Session Expired';
			var serverMessage = this.responseText;
			if (serverMessage.localeCompare(messageExpired) == 0) {
				window.location.reload();
			}
		}
	};
	xmlhttp.open('POST','/blocks/active_time_tracker/handle_time_requests.php',true);
	xmlhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	xmlhttp.send('user=' + userID + '&course=' + courseID + '&module=' + moduleType + '&instance=' + moduleInstance + '&time=' + counter);
}


function openReports() {
	window.open('/blocks/active_time_tracker/reportsPage.php','Reports','width=800,height=600');
}