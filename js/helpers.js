var daysShort	= ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
var monthsShort = ['Jan', 'Feb',  'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sept', 'Oct', 'Nov', 'Dec'];

function loading() {
	$(document.getElementById('confirm')).hide();
	$(document.getElementById('loading')).show();	
}

function finishLoading(confirm) {
	var $el = $(document.getElementById('loading'));
	if (confirm === true) {
		$(document.getElementById('confirm')).show();
		setTimeout(function() {
			$el.hide();
		}, 2000);
	} else {
			$el.hide();
	}
}

function checkCalc() {
    var prop = 'width:';
    var value = 'calc(10px);';
    var el = document.createElement('div');

    el.style.cssText = prop + ["", "-webkit-", ""].join(value + prop);

    return !!el.style.length;
}

function checkDateTime() {
	var t = document.createElement("input");
	t.setAttribute("type", "datetime");
	var result = (t.type === "datetime");

	t.value = "Stoya";
	if (t.value === "Stoya") { result = false; }

	// Nicked from Modernizer, for Android 2.2
	t.setAttribute("type", ":)");
	if (t.type === ":)") { result = false; }

	return result;
}

function mustache(template, json) {
    json			= (json) ? json : {};
    var partials    = templates;

    return Mustache.to_html(template, json, partials);
}

Number.prototype.pad = function() {
    var s = String(this);
    while (s.length < 2) s = "0" + s;
    return s;
};

function timeDifference(previous, compact) {
	if (!previous) { return; }

	var current		= new Date().getTime();
	var msPerMinute = 60 * 1000;
	var msPerHour = msPerMinute * 60;
	var msPerDay = msPerHour * 24;
	var msPerMonth = msPerDay * 30;
	var msPerYear = msPerDay * 365;

	var elapsed = current - previous;

	if (elapsed < msPerMinute) {
        str      = (compact) ? 's' : ' seconds ago';
        return Math.round(elapsed/1000) + str;
	}

	else if (elapsed < msPerHour) {
		var time	= Math.round(elapsed/msPerMinute);
		var str		= (time <= 1) ? ' minute ago' : ' minutes ago';
		str			= (compact) ? 'm' : str;
		return time + str;
	}

	else if (elapsed < msPerDay ) {
		var time = Math.round(elapsed/msPerHour);
		var str	 = (time <= 1) ? ' hour ago' : ' hours ago';
		str      = (compact) ? 'h' : str;
		return time + str;   
	}

	else if (elapsed < msPerMonth) {
		var time = Math.round(elapsed/msPerDay);
		var str	 = (time <= 1) ? ' day ago' : ' days ago';
		str      = (compact) ? 'd' : str;
		return time + str;   
	}

	else if (elapsed < msPerYear) {
	    if (!compact) {
			return 'Over a month ago';
		}
	}

	else {
	    if (!compact) {
			return 'Over a year ago';
		}
	}
}

function showCompass() {
	var spinner = document.getElementById('compass');

	if (window.DeviceOrientationEvent && iOSversion()) {
		var lastHeading = 0;
		window.addEventListener('deviceorientation', function(e) {
			if (e.webkitCompassHeading) {
				var heading = (e.webkitCompassHeading + window.orientation).toFixed(2);
				spinner.style.webkitTransform = 'rotateZ(-' + heading + 'deg)';
				lastHeading = heading;
			}
		});
	} else {
		$(spinner).remove();	
	}
}


function removeCompass() {
	if (iOSversion()) {
		window.removeEventListener('deviceorientation');
	}
}


function iOSversion() {
	if (/iP(hone|od|ad)/.test(navigator.platform)) {
		// supports iOS 2.0 and later: <http://bit.ly/TJjs1V>
		var v = (navigator.appVersion).match(/OS (\d+)_(\d+)_?(\d+)?/);
		var ver = [parseInt(v[1], 10), parseInt(v[2], 10), parseInt(v[3] || 0, 10)];

		return (ver[0] >= 5);
	}

	return false;
}


function noPosition() {
	alert('Can\'t get your position');
	finishLoading();
}


function formatTime(timestamp) {
	var string	= '';
	var nth		= ['st', 'nd', 'rd', 'th'];
	var d		= new Date(parseInt(timestamp, 10));

	string		+= daysShort[d.getDay()];
	string		+= ' ';
	string		+= d.getDate();
	string		+= (d.getDate() < 3) ? nth[d.getDate() - 1] : nth[3];
	string		+= ' ';
	string		+= (new Date().getMonth() === d.getMonth() && new Date().getFullYear() === d.getFullYear()) ? '' : monthsShort[d.getMonth()];
	string		+= ' ';
	string		+= d.getHours().pad() + ':' + d.getMinutes().pad();

	return string;
}


function setLocalStorage(key, value) {
	var set = true;

	try {
		localStorage.setItem(key, value);
	} catch (error) {
		set = false;

		if (error.code === DOMException.QUOTA_EXCEEDED_ERR) {
			alert('Whoops\n\n' + 'Unable to save data to your phone.\nIf in "Private Browsing" mode switch this off and try again.');
		} else { throw error; }
	}

	return set;
}