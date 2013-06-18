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

function checkSVG() {
	var a	= false;
	var ua	= navigator.userAgent;

	a = (document.implementation.hasFeature("http://www.w3.org/TR/SVG11/feature#BasicStructure", "1.1"));

	a = (ua.toLowerCase().indexOf("android") >= 0 || ua.toLowerCase().indexOf("windows phone") >= 0 || ua.toLowerCase().indexOf("blackberry") >= 0) ? false : a;

	return a;
}

function checkWinMob() {
	var ua	= navigator.userAgent;
	return (ua.toLowerCase().indexOf("windows phone") >= 0);
}

function mustache(template, json) {
    json			= (json) ? json : {};
    json.danish		= danish;
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

	var str;

	var current		= new Date().getTime();
	var msPerMinute = 60 * 1000;
	var msPerHour = msPerMinute * 60;
	var msPerDay = msPerHour * 24;
	var msPerMonth = msPerDay * 30;
	var msPerYear = msPerDay * 365;

	var elapsed = current - previous;

	if (elapsed < msPerMinute) {
		if (danish === true) {
			str      = (compact) ? 's' : ' sekunder siden';
		} else {
			str      = (compact) ? 's' : ' seconds ago';
		}
        return Math.round(elapsed/1000) + str;
	}

	else if (elapsed < msPerHour) {
		var time	= Math.round(elapsed/msPerMinute);
		if (danish === true) {
			str		= (time <= 1) ? ' minut siden' : ' minutter siden';
		} else {
			str		= (time <= 1) ? ' minute ago' : ' minutes ago';
		}
		str			= (compact) ? 'm' : str;
		return time + str;
	}

	else if (elapsed < msPerDay ) {
		var time = Math.round(elapsed/msPerHour);
		if (danish === true) {
			str = (time <= 1) ? ' time siden' : ' timer siden';
		} else {
			str = (time <= 1) ? ' hour ago' : ' hours ago';
		}
		str      = (compact) ? 'h' : str;
		return time + str;   
	}

	else if (elapsed < msPerMonth) {
		var time = Math.round(elapsed/msPerDay);
		if (danish === true) {
			str	= (time <= 1) ? ' dag siden' : ' dage siden';
		} else {
			str = (time <= 1) ? ' day ago' : ' days ago';
		}
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
	spinner.className = 'compass';

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


function setLocalStorage(key, value, grace) {
	var set = true;

	try {
		localStorage.setItem(key, value);
	} catch (error) {
		set = false;

		if (!grace) {
			if (error.code === DOMException.QUOTA_EXCEEDED_ERR) {
				alert('Whoops\n\n' + 'Unable to save data to your phone.\nIf in "Private Browsing" mode switch this off and try again.');
			} else {
				alert('Whoops\n\n' + 'Unable to save data to your phone.');
				//throw error; 
			}
		}
	}

	return set;
}


function ajaxFail(error) {
	if (error.status > 0) {
		if (error.status === 401) {
			alert('We\'re unable to correctly authenticate you.\nRefreshing the page should fix this problem.');
		} else {
			alert('There seems to be a problem on our server.\nRefreshing the page should fix this problem.');
		}
	}

	finishLoading();
}


function pushState(obj, title, path, replaceState) {
	if (typeof history.pushState === "function") {
		if (replaceState === true) {
			history.replaceState(obj, title, path);
		} else {
			history.pushState(obj, title, path);
		}
	}
}


function appCacheStatus() {
	var cacheStatusValues = [];
	cacheStatusValues[0] = 'uncached';
	cacheStatusValues[1] = 'idle';
	cacheStatusValues[2] = 'checking';
	cacheStatusValues[3] = 'downloading';
	cacheStatusValues[4] = 'updateready';
	cacheStatusValues[5] = 'obsolete';

	var cache = window.applicationCache;
	cache.addEventListener('cached', logEvent, false);
	cache.addEventListener('checking', logEvent, false);
	cache.addEventListener('downloading', logEvent, false);
	cache.addEventListener('error', logEvent, false);
	cache.addEventListener('noupdate', logEvent, false);
	cache.addEventListener('obsolete', logEvent, false);
	cache.addEventListener('progress', logEvent, false);
	cache.addEventListener('updateready', logEvent, false);

	function logEvent(e) {
	    var online, status, type, message;
	    online = (navigator.onLine) ? 'yes' : 'no';
	    status = cacheStatusValues[cache.status];
	    type = e.type;
	    message = 'online: ' + online;
	    message+= ', event: ' + type;
	    message+= ', status: ' + status;
	    if (type == 'error' && navigator.onLine) {
	        message+= ' (prolly a syntax error in manifest)';
	    }
	    console.log(message);
	}

	window.applicationCache.addEventListener(
	    'updateready',
	    function(){
	        window.applicationCache.swapCache();
	        console.log('swap cache has been called');
	    },
	    false
	);

	//setInterval(function(){cache.update()}, 10000);
}

function changeTitle(location) {
	var $el = $(document.getElementById('section-title'));

	if (!location) {
		$el.empty().removeClass('two_lines');
	} else {
		var title ={
			'en': {
				'getMySchedule' : ['My<br/>Schedule', 1],
				'schedule'		: ['Festival<br/>Schedule', 1],
				'findFriends'	: ['Find<br/>Friends', 1],
				'remLocation'	: ['Remember<br/>location', 1],
				'getLocation'	: ['My<br/>Locations', 1],
				'createEvent'	: ['Create<br/>Event', 1],
				'getEvents'		: ['Events', 0],
				'map'			: ['Festival<br/>Map', 1],
				'getArtists'	: ['Artists', 0],
				'getNews'		: ['News', 0],
				'getTweets'		: ['Tweets', 0]
			},
			'dn': {
				'getMySchedule' : ['Min<br/>Tidsplan', 1],
				'schedule'		: ['Festival<br/>Tidsplan', 1],
				'findFriends'	: ['Find Mine<br/>Venner', 1],
				'remLocation'	: ['Husk<br/>Sted', 1],
				'getLocation'	: ['Mine<br/>Steder', 1],
				'createEvent'	: ['Skab<br/>Begivenhed', 1],
				'getEvents'		: ['Arrangementer', 0],
				'map'			: ['Festival<br/>Kort', 1],
				'getArtists'	: ['Kunstnere', 0],
				'getNews'		: ['Nyheder', 0],
				'getTweets'		: ['Tweets', 0]
			}
		};

		var lang = (danish) ? 'dn' : 'en';
		var text = title[lang][location][0];
		var two  = (title[lang][location][1] === 1) ? 'addClass' : 'removeClass';

		$el.html(text)[two]('two_lines');
	}
}