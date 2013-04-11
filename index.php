<?php

require 'php/fb/facebook.php';

// Create our Application instance (replace this with your appId and secret).
$facebook = new Facebook(array(
  'appId'  => '357860537664045',
  'secret' => 'a8a4b0405be32159babf93ab054f35d2'
));

$logoutUrl  = $facebook->getLogoutUrl();
$loginUrl 	= $facebook->getLoginUrl(array(
    "display"=>"touch",
    "redirect_uri"=>"http://".$_SERVER['HTTP_HOST']."/php/fb/redirect.php"
));
  
function iOSDetect() {
   $browser = strtolower($_SERVER['HTTP_USER_AGENT']); // Checks the user agent
   if(strstr($browser, 'iphone') || strstr($browser, 'ipod')) {
      $device = (strstr($browser, 'safari')) ? 'iPhone' : 'default';
   } else { $device = 'default'; }	
   return($device);
}
?>
<!DOCTYPE HTML>
<!--html lang="en" manifest="/php/cache-manifest.appcache"-->
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1, minimum-scale=1, maximum-scale=1" />
        
        <title>Roskilde 2013</title>
        <link href="/images/favicon.ico" rel="shortcut icon" type="image/x-icon" />
        
        <meta name="apple-mobile-web-app-capable" content="yes" />
        <meta name="format-detection" content="telephone=no" />

        <link rel="apple-touch-icon-precomposed" href="/images/icons/icon.png" />
        <link rel="apple-touch-icon-precomposed" sizes="72x72" href="/images/icons/icon-ipad.png" />
        <link rel="apple-touch-icon-precomposed" sizes="114x114" href="/images/icons/icon-iphone-retina.png" />
        <link rel="apple-touch-icon-precomposed" sizes="144x144" href="/images/icons/icon-ipad-retina.png" />
        <link rel="apple-touch-startup-image" sizes="320x460" href="/images/icons/apple-touch-startup-image-320x460.png" />
        <link rel="apple-touch-startup-image" sizes="640x920" href="/images/icons/apple-touch-startup-image-640x920.png" />
        <link rel="apple-touch-startup-image" sizes="640x1096" href="/images/icons/apple-touch-startup-image-640x1096.png" />

		<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/3.9.1/build/cssreset/cssreset-min.css" />
		<link rel="stylesheet" href="css/main.css" type="text/css" />
    </head>
    
    <body<?php if(iOSDetect() == 'iPhone') { echo ' class="ios"';} ?>>
        <div id="menu" class="menu" onclick="">Roskilde 2013. Menu</div>
        <div id="content"></div>
        

        <div id="fb-root"></div>
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
        <script src="http://r.oskil.de/js/mustache.js"></script>
        
        <script>
            // navigator.onLine - check if online!
            // http://stackoverflow.com/questions/7131909/facebook-callback-appends-to-return-url
            if (window.location.hash == '#_=_') {
                window.location.hash = '';                                          // for older browsers, leaves a # behind
                history.pushState('', document.title, window.location.pathname);    // nice and clean
            }
            
			var fbUser;
            var user;
			var schedule;
			var openInfoWindow;
			var createEventMarker;
			
			var festivalCoords   = {
    			coords: {
        			accuracy: 1,
                    latitude: 55.619080,
                    longitude: 12.077461
                }
			}
            
            window.fbAsyncInit = function() {
                // init the FB JS SDK
                FB.init({
                    appId      : '357860537664045', // App ID from the App Dashboard
                    channelUrl : 'http://r.oskil.de/php/channel.php', // Channel File for x-domain communication
                    status     : true, // check the login status upon init?
                    cookie     : true, // set sessions cookies to allow your server to access the session?
                    xfbml      : false  // parse XFBML tags on this page?
                });

                // Additional initialization code such as adding Event Listeners goes here
                FB.getLoginStatus(function(response) {
                    if (response.status === 'connected') {
                        loggedIn();
                    } else {
                        loggedOut();
                    }
                });
                
            };

            (function(d){
                var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
                if (d.getElementById(id)) {return;}
                js = d.createElement('script'); js.id = id; js.async = true;
                js.src = "//connect.facebook.net/en_US/all.js";
                ref.parentNode.insertBefore(js, ref);
            }(document));
            

            // LOGIN ----------------------------------------------------------------------------------------------------

            function loggedIn() {
                FB.api('/me', function(response) {
                    fbUser = response;
                    $(document.getElementById('content')).html(mustache(templates.statusLoggedIn, response));
					                    
                    checkUser();
                });
            }
            
            function loggedOut() {
                $(document.getElementById('content')).html(mustache(templates.statusLoggedOut));
				finishLoading();
            }
            
            function checkUser() {
                var data    = fbUser;
                data.fb_id  = data.id;
                data.action = 'auth';
				
                
                $.ajax({
                    type: "POST",
                    url: "/php/api.php",
                    data: data
                }).done(function(data) {
					if (data.result && !isNaN(data.result.id)) {
						user = data.result;
						FB.api('/me/friends', function(response) {
							postFriends(response);
						});
					}
					
					finishLoading();

				});
            }


            // GEOLOCATION

            // FUNCTIONS ----------------------------------------------------------------------------------------------------

			
			function postFriends(friends) {
				var data 		= {};
				data.action		= 'friends';
				data.id 		= user.id;
				data.fb_id		= user.fb_id;
				data.friends	= friends.data;
				
                $.ajax({
                    type: "POST",
                    url: "/php/api.php",
                    data: data
                }).done(function(data) {
					console.log('Friends', data);
                });

			}

			function getPosition(position) {
				if (position && position.coords.latitude && position.coords.longitude) {
					var data		= {};
					var headings	= ['accuracy', 'heading', 'latitude','longitude'];
					$.each(headings, function(i,v) {
						if (!isNaN(position.coords[v])) { data[v] = position.coords[v]; }	
					});
					
					logCheckIn({coords: data}, function(data) {
						console.log('Logged CheckIn', data);
						finishLoading(true);
					});
				}
			}
			
			function logCheckIn(position, cb) {
				if (user && user.id) {
					position.action		= 'checkin';
					position.user_id	= user.id;
					position.fb_id		= user.fb_id;
					position.name		= user.name;
					
					$.ajax({
						type: "POST",
						url: "/php/api.php",
						data: position
					}).done(function(data) {
						if (cb) { cb(data); }
					});
				}
			}
			
			function noPosition() {
				alert('Can\'t get your position :(');
				finishLoading();
			}
			
			function findFriends() {
				console.log('Find Friends');
				
				$.ajax({
					type: "POST",
					url: "/php/api.php",
					data: {action: 'findFriends', id: user.id, fb_id: user.fb_id}
				}).done(function(data) {
					initMap(data, true, function(data, coords, map, markers) {
						populateMarker(data, coords, map, markers);
					});
				});
			}
			
			function initMap(data, fit, cb) {
				console.log('initMap', data);
				$(document.getElementById('content')).html(templates.mapCanvas);
				
				if (typeof data === 'string') {data = JSON.parse(data) }; // FF and jQuery not recognising a JSON response
				
				navigator.geolocation.getCurrentPosition(function(coords) {
				    console.log(coords);
                    gotLocation(data, fit, cb, coords);					
				}, function(error) {
				    gotLocation(data, fit, cb, festivalCoords, true);
				}, {timeout: 8000});
				
			}
			
			
			function gotLocation(data, fit, cb, coords, error) {
				console.log('gotLocation', data, coords);
				
				var m			= document.getElementById("map-canvas");
				var me			= new google.maps.LatLng(coords.coords.latitude, coords.coords.longitude);
				var center      = (typeof fit === 'object') ? new google.maps.LatLng(fit.coords.latitude, fit.coords.longitude) : me;

				var mapOptions 	= {
					center: center,
					zoom: 15,
					disableDefaultUI: true,
					mapTypeId: google.maps.MapTypeId.ROADMAP
				};
				var map = new google.maps.Map(document.getElementById("map-canvas"), mapOptions);
				
				google.maps.event.addListenerOnce(map, 'idle', function(){
						finishLoading();
				});
				
				setRoskildeMap(map);
				
				var markers = [];
				
                if (!error) {
                    markers.push(marker(coords.coords.latitude, coords.coords.longitude, map, 'Me', user.fb_id, false, 100));
                }

                if (cb) { cb(data, coords, map, markers); }
                
                if (fit === true) { fitToMarkers(markers, map); }
				
				showCompass();
			}


			function initRoskildeMap() {
    			initMap(null, festivalCoords, function() {
        			console.log('geoData goes here');
    			});
			}
			
			
			function initCreateEventsMap(data) {
    			initMap(data, false, function(data, coords, map, markers) {
        			var m    = document.getElementById("map-canvas");
        			var $m   = $(m);
        			
        			$m.after(templates.createEventOptions);
        			$m.data({'my-location': coords.coords.latitude + ',' + coords.coords.longitude, form: data});
        			
					google.maps.event.addListener(map, 'click', function(e) {
						if (createEventMarker) { createEventMarker.setMap(null); }
						createEventMarker = marker(e.latLng.jb, e.latLng.kb, map, 'Event', "http://r.oskil.de/images/logo.png", null, null, 'createEvent');
						$(m).data({'my-marker': e.latLng.jb + ',' + e.latLng.kb});
						
						$(document.getElementById('createEventMarker')).show();
					});
    			});
			}
			
			
			function createEvent(latLon, data) {
				console.log(latLon, data);
				loading();

				var latLon = latLon.split(',');
				var latitude	= latLon[0];
				var longitude	= latLon[1];
				
				var obj = {
					name: data.name,
					description: data.description,
					start: data.start,
					end: data.end,
					latitude: latitude,
					longitude: longitude,
					user_id: user.id
				}
				
                $.ajax({
                    type: "POST",
                    url: "/php/api.php",
                    data: {action: 'createEvent', event: obj}
                }).done(function(data) {
                    //if (cb) { cb(data); }
					console.log('create event', data);
					finishLoading(true);
					loggedIn();
                });

			}
			
			
			function getEvents() {
    			console.log('getEvents');
    			loading();
    			
                $.ajax({
                    type: "POST",
                    url: "/php/api.php",
                    data: {action: 'getEvents'}
				}).done(function(data) {
					initMap(data, true, function(data, coords, map, markers) {
						populateMarker(data, coords, map, markers);
					});
				});			
			}
			

			function setRoskildeMap(map) {
				var se	=	new google.maps.LatLng(55.608, 12.0542);
				var nw	=	new google.maps.LatLng(55.62535, 12.10675);
				var imageBounds = new google.maps.LatLngBounds(se, nw);
				var festival = new google.maps.GroundOverlay("http://r.oskil.de/images/map.gif", imageBounds, {clickable: false});
				festival.setMap(map);	
			}


			function marker(lat, lon, map, title, icon, timestamp, zIndex, mode) {
				if (timestamp) {
					var infowindow = createTooltip({src: "http://graph.facebook.com/" + icon + "/picture", name: title, time: timeDifference(timestamp)});
				}

				if (title === 'Me') {
					var img = new google.maps.MarkerImage("http://r.oskil.de/images/me.png", null, null, new google.maps.Point(5,5), new google.maps.Size(10,10));
				} else if (mode === 'locations') {
					var img = new google.maps.MarkerImage("http://r.oskil.de/images/location.png", null, null, new google.maps.Point(10,10), new google.maps.Size(10,10));
				}
				
				var obj = {
					position: new google.maps.LatLng(lat, lon),
					map: map
				}
				
				if (zIndex) {
					obj.zIndex = zIndex;	
				}

				if (img) {
    				obj.title  = title;
					obj.icon   = img;
				} else if (mode === 'createEvent') {
					obj.content    = mustache(templates.marker, {src: icon});
					obj.flat       = true;
				} else {
					obj.content    = mustache(templates.marker, {src: "http://graph.facebook.com/" + icon + "/picture", time: timeDifference(timestamp, true)});
					obj.flat       = true;
				}

				var marker = (img) ? new google.maps.Marker(obj) : new RichMarker(obj);

			
				if (timestamp) {
					google.maps.event.addListener(marker, 'click', function() {
						if (openInfoWindow) { openInfoWindow.close(); };
						openInfoWindow = infowindow;
						infowindow.open(map, marker);
					});
				}
				
				return marker;
			}
			
			function fitToMarkers(markers, map) {
				var bounds = new google.maps.LatLngBounds();
				var length = markers.length;
				for (var i = 0; i < length; i++) {
					bounds.extend(new google.maps.LatLng(markers[i]['position']['jb'], markers[i]['position']['kb']));
					map.fitBounds(bounds);
				}
			}
			
			function createTooltip(obj) {
				var html = mustache(templates.tooltip, obj);
				var boxText = document.getElementById("dynamic");
				boxText.style.width = '160px';
				boxText.innerHTML	= '<span style="width: 18px; height: 17px; display: block; float: right; vertical-align: top;"></span>' + html; // span is to compensate for the close button

				var myOptions = {
                    content: html,
                    pixelOffset: new google.maps.Size(-80, (boxText.offsetHeight + 40) * -1),
                    closeBoxURL: "http://www.google.com/intl/en_us/mapfiles/close.gif",
                    infoBoxClearance: new google.maps.Size(20, 40),
                    pane: "floatPane"
				};
				
				return new InfoBox(myOptions);	
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
					var time = Math.round(elapsed/msPerMinute);
					var str	 = (time <= 1) ? ' minute ago' : ' minutes ago';
					str      = (compact) ? 'm' : str;
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
			
			function rememberLocation(msg) {
			     // TODO - Make this like create events
				console.log('Remember Location');
				navigator.geolocation.getCurrentPosition(function(position) {
				if (user && user.id) {
					position.action		= 'postLocation';
					position.user_id	= user.id;
					position.fb_id		= user.fb_id;
					position.name		= user.name;
					position.message	= msg;
					
					$.ajax({
						type: "POST",
						url: "/php/api.php",
						data: position
					}).done(function(data) {
						console.log(data);
					});
				}
				}, noPosition, {timeout: 8000});
			}


			function getLocation() {
				$.ajax({
					type: "POST",
					url: "/php/api.php",
					data: {action: 'getLocations', user_id: user.id, fb_id: user.fb_id, name: user.name}
				}).done(function(data) {
					initMap(data, true, function(data, coords, map, markers) {
						populateMarker(data, coords, map, markers);
					});
				});
			}


			function populateMarker(data, coords, map, markers) {
                var length 	= data.result.length;
                var z		= length + 1;
                for (var i = 0; i < length; i++) {
                    var d = data.result[i];
                    markers.push(marker(d.latitude, d.longitude, map, d.name, null, d.timestamp, z--));
                }
			}

			function getSchedule() {
				loading();
				
				var d = ['Sat, 30 Jun 2012 12:00:00 +0200', 'Sun, 01 Jul 2012 12:00:00 +0200', 'Mon, 02 Jul 2012 12:00:00 +0200', 'Tue, 03 Jul 2012 12:00:00 +0200', 'Wed, 04 Jul 2012 12:00:00 +0200', 'Thu, 05 Jul 2012 12:00:00 +0200', 'Fri, 06 Jul 2012 12:00:00 +0200', 'Sat, 07 Jul 2012 12:00:00 +0200', 'Sun, 08 Jul 2012 12:00:00 +0200']
				var dates = [];
				var stages = [];

				$.each(d, function(i,v) {
					dates.push(new Date(v).getTime() / 1000);	
				});

					
				if (typeof schedule !== 'object') {
					$.getJSON('/php/xml2jsonTest.php', function(data) {
						schedule = data;
						processDates(data, dates, stages);
					});
				} else {
					processDates(schedule, dates, stages);
				}
			}


			function processDates(data, dates, stages) {
			    var days         = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
			    var daysShort    = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
			    var nth          = ['st', 'nd', 'rd', 'th'];
			    var widths       = [];

				stages = data.stages;
				
				var i = 0;
				
				var date = new Date(dates[i] * 1000).getDate();
				var html = '<div id="stages" class="stages"><div id="date" class="stage_name">' + days[new Date(dates[i] * 1000).getDay()] + ' ' + date + ((date - 1 > 3) ? nth[3] : nth[date - 1]) + '</div>';
				
				$.each(data.stages, function(i,v) {
				    html += '<div class="stage_name">' + v + '</div>';
				});
				html    += '</div>';
				
				var style = '';
				if (checkCalc() === false) {
    				var width = $(document.getElementById('content')).outerWidth() - 60;
    				style = ' style="width: ' + width + 'px;"'
				}
				
				html    += '<div class="schedule_scroller"' + style + '>';
				html    += '<div id ="schedule-container" class="schedule_container">';

				
				$.each(data.keys, function(n,key) {			    
					html += '<div class="day">';
					html += '<div class="stage">';
					
    				var min     = dates[i];
    				var max     = min + 72000;
    				while (min < max) {
    				    var time    = new Date(min * 1000);
    				    html    += '<div class="time">' + time.getHours() + ':' + time.getMinutes().pad() + '</div>';
    				    min = min + 900;
    				}
    				
    				var date = new Date(dates[i] * 1000).getDate();
    				html += '<div class="name_day">' + days[new Date(dates[i] * 1000).getDay()] + ' ' + date + ((date - 1 > 3) ? nth[3] : nth[date - 1]) + '</div></div>';

					$.each(data.results[key], function(name, stage) {
    					html += populateStage(name, dates, stage, i);
					});
					i++;
					html += '</div>';
				});

				html += '</div>';
				html += '</div>';
				
				document.getElementById('content').innerHTML = html;
				
				var width = 0;
				$('.day').each(function(i,v) {
    				width = width + $(this).outerWidth();
    				widths.push(width);
				});
				
				var $schedule	= $(document.getElementById('schedule-container'));
				var $stages		= $(document.getElementById('stages'));
				$schedule.css('width', width + 'px');
				
				if ($schedule.outerHeight() !== $stages.outerHeight()) {
					$stages.css('height', $schedule.outerHeight());	
				}
				
				var min = -1;
				var max = 0;
				var el  = document.getElementById('date');
				
				$('.schedule_scroller').on('scroll gesturechange', function(e) {
				    if ($(e.currentTarget)[0].scrollLeft > widths[max] && (max + 1) <= widths.length) {
    				    min++;
    				    max++;
    				    var date = new Date(dates[max] * 1000).getDate();
    				    el.innerHTML = daysShort[new Date(dates[max] * 1000).getDay()] + ' ' + date + ((date - 1 > 3) ? nth[3] : nth[date - 1]);
				    } else if ($(e.currentTarget)[0].scrollLeft < widths[min] && min >= 0) {
    				    min--;
    				    max--;
    				    var date = new Date(dates[max] * 1000).getDate();
    				    el.innerHTML = daysShort[new Date(dates[max] * 1000).getDay()] + ' ' + date + ((date - 1 > 3) ? nth[3] : nth[date - 1]);
				    }
				});
				
				finishLoading();
			}


			function populateStage(name, dates, stage, i) {
				var html = '<div class="stage">';
				var min = dates[i];
				var max = min + 72000;
				var margin = 0;
				    
				while (min < max) {
					if (stage[min]) {
						var time = new Date(stage[min]['original_timestamp'] * 1000);
						html += '<div class="band" style="margin-left: ' + margin + 'px;" data-artist="' + i + '-' + name + '-' + min + '" onclick=""><div>' + stage[min]['artistName'] + '</div><span>' + time.getHours() + ':' + time.getMinutes().pad() + '</span></div>';
						margin = -90;
					} else {
						margin = margin + 30;	
					}
					min = min + 900;
				}
				html += '</div>';
				
				return html;
				//console.log(new Date(max * 1000));
			}

			
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
				
				if (result === true) {
					// Nicked from Modernizer, for Android 2.2
					t.setAttribute("type", ":)");
					if (t.type === ":)") { result = false; }
				}
				
				return result;
			}
            
            function mustache(template, json) {
                var json        = (json) ? json : {};
                var partials    = templates;
        
                return Mustache.to_html(template, json, partials);
            }
            
            Number.prototype.pad = function(){
                var s = String(this);
                while (s.length < 2) s = "0" + s;
                return s;
            }
            
            $(document).ready(function() {
                window.scrollTo(1,0);
                loading();
				
                $(document).on("click", "#checkin", function(e){
                    e.preventDefault();
					loading();
					navigator.geolocation.getCurrentPosition(getPosition, noPosition, {timeout: 8000});
                });
				
                $(document).on("click", "#findFriends", function(e){
                    e.preventDefault();
					loading();
					findFriends();
                });
				
				$(document).on("click", "#menu", function(e){
                    e.preventDefault();
					removeCompass();
					loggedIn();
				});

                $(document).on("click", "#remLocation", function(e){
                    e.preventDefault();
					$(document.getElementById('content')).append(mustache(templates.messageBox, {action: 'Location'}));
                });
				
                $(document).on("click", "#messageSubmitLocation", function(e){
                    e.preventDefault();
					var $msg	= $(document.getElementById('message'));
					var msg 	= $msg.find('textarea').val();
					
					if (msg.length > 0) {
						rememberLocation(msg);
					}
					
					$msg.remove();
					
                });

                $(document).on("click", "#getLocation", function(e){
                    e.preventDefault();
					loading();
					getLocation();
                });
				
				$(document).on("click", "#map", function(e){
					e.preventDefault();
					loading();
					initRoskildeMap();
				});

				$(document).on("click", "#schedule", function(e){
					e.preventDefault();
					getSchedule();
				});
				
				$(document).on("click", ".band", function(e){
					e.preventDefault();
					
					var id     = $(this).data('artist').split('-');
					var artist = schedule.results[id[0]][id[1]][id[2]];
					
					$(document.getElementById('content')).append(mustache(templates.artist_page, artist));

				});
				
				$(document).on("click", "#artist-close", function(e){
					e.preventDefault();
				    $('#artist-page').remove();
				});
				
				$(document).on("click", "#createEvent", function(e){
					e.preventDefault();
					$(document.getElementById('content')).html(mustache(templates.create_event, {datetime: checkDateTime()}));
				});

				$(document).on("change", ".event-date", function(e){
					e.preventDefault();
					console.log('DO BASIC DATE CHECKING',$(this));
					console.log($(document.getElementById('createEventForm')).serializeArray());
					$.each($(document.getElementById('createEventForm')).serializeArray(), function(i,v) {
						console.log(i,v);
						var time;
						if (v.name === 'start-date' || v.name === 'start-time') {
							console.log('322332432');
						}
					});
				});
				
				$(document).on("submit", "#createEventForm", function(e) {
					e.preventDefault();
					
					var checkDate	= checkDateTime();
					var proceed		= true;
					var form		= $(this).serializeArray();
					var year		= new Date().getFullYear();
					var data		= {};

					$.each(form, function(i,v) {
						if (v.name === 'start-date' ||  v.name === 'end-date') {
							var alpha	= v.name.split('-')[0];
							var dates	= v.value.split('-');
							
							data[alpha + '-date'] = dates[0];
							data[alpha + '-month'] = dates[1];
						} else {
							data[v.name] = v.value;
						}

						if (proceed === true && v.name !== 'description' && v.value == false) {
							proceed = false;	
						}
					});
					console.log(proceed, data);
					
					var length	= (checkDate) ? 4 : 6;
					if (form.length !== length || proceed === false) {
						alert('Something seems to be missing');	
						return;
					} else {
						if (checkDate) {
							var startTime	= new Date(data['start-time']).getTime();
							var endTime		= new Date(data['end-time']).getTime();
						} else {
							var start	= data['start-time'].split(':');
							var end		= data['end-time'].split(':');
							
							var startTime	= new Date(year, (data['start-month'] - 1), data['start-date'], start[0], start[1], 0).getTime();
							var endTime		= new Date(year, (data['end-month'] - 1), data['end-date'], end[0], end[1], 0).getTime();
						}
						
						data.start	= startTime;
						data.end	= endTime;
						
						console.log(data);
						
						initCreateEventsMap(data);
					}
				});


				$(document).on("click", ".create-event", function(e){
					e.preventDefault();
					var $map	=	$(document.getElementById("map-canvas"));
					var latLon	=	($(this).attr('id') === 'createEventMe') ? $map.data('my-location') : $map.data('my-marker');
					var data	= $map.data('form');
					
					createEvent(latLon, data);
				});
				
				
				$(document).on("click", "#getEvents", function(e){
					e.preventDefault();				
					getEvents();
				});

            });
            
            var templates = {
                statusLoggedOut:    		'<div class="status">Logged Out</div><div class="status"><a href="<?php echo $loginUrl; ?>" class="button">Log In</a></div>',
                statusLoggedIn:     		'<div class="status">Welcome {{first_name}} {{last_name}} from {{#hometown}}{{name}}{{/hometown}}</div>' +
											'<div class="menu_button">{{> checkInButtonPartial}}</div>' +
											'<div class="menu_button">{{> findFriendsButtonPartial}}</div>' +
											'<div class="menu_button">{{> locationButtonPartial}}</div>' +
											'<div class="menu_button">{{> getLocationButtonPartial}}</div>' +
											'<div class="menu_button">{{> mapButtonPartial}}</div>' +
											'<div class="menu_button">{{> scheduleButtonPartial}}</div>' +
											'<div class="menu_button">{{> createEventPartial}}</div>' +
											'<div class="menu_button">{{> eventButtonPartial}}</div>',
											
				checkInButtonPartial:		'<button id="checkin">CHECK IN</button>',
				findFriendsButtonPartial:	'<button id="findFriends">FIND FRIENDS</button>',
				locationButtonPartial:		'<button id="remLocation">REMEMBER LOCATION</button>',
				getLocationButtonPartial:	'<button id="getLocation">LOCATIONS</button>',
				mapButtonPartial:			'<button id="map">MAP</button>',
				scheduleButtonPartial:		'<button id="schedule">SCHEDULE</button>',
				createEventPartial:			'<button id="createEvent">CREATE EVENT</button>',
				eventButtonPartial:			'<button id="getEvents">EVENTS</button>',
				
				mapCanvas:					'<div id="map-canvas" class="map_canvas"></div><div id="compass" class="compass"></div>',
				
				createEventOptions:			'<div id="createEventOptions" class="create_event_options">' +
												'<div><button id="createEventMe" class="create-event">USE MY LOCATION</button></div>' +
												'<div><span>... or tap a location</span><button id="createEventMarker" class="create-event">USE MARKED LOCATION</button></div>' +
											'</div>',
				messageBox:					'<div id="message" class="message"><textarea></textarea><button id="messageSubmit{{action}}">SUBMIT</button></div>',
				
				marker:                     '<div class="roskilde_marker">' +
												'<div class="ros_marker">' +
													'<img src="{{{src}}}" height="25" width="25"/>' +
													'<span>{{#time}}{{time}}{{/time}}</span>' +
												'</div>' +
											'</div>',
				                            
				tooltip:					'<div class="ros_tooltip">' +
												'<img src="{{{src}}}" class="tt_img" height="25" width="25"/>' +
												'<div class="tt_cont">' +
													'<div class="tt_name">{{name}}</div>' +
													'<div class="tt_details">' +
														'<div class="tt_time">{{time}}</div>' +
														'{{#message}}<div class="tt_msg">{{message}}</div>{{/message}}' +
													'</div>' +
												'</div>' +
											'</div>',
											
				artist_page:                '<div id="artist-page" class="page artist_page">' +
				                                '<div>' +
    				                                '<h4>{{artistName}}</h4><span id="artist-close" class="artist_close" onclick="">&times;</span>' +
    				                                '<div class="artist_details">' +
    				                                    '<img src="http://roskilde-festival.co.uk/{{{imageUrl}}}" height="112" width="112" /><br/>' +
        				                                '{{country}}<br/>' +
        				                                '{{{scene}}}<br/>' +
        				                                '{{tidspunkt}}<br/>' +
    				                                '</div>' +
    
    				                                '<div class="artist_description">' +
        				                                '{{{description}}}' +
    				                                '</div>' +
				                                '</div>' +
				                            '</div>',

				create_event:				'<div id="createEventPage" class="page event_page">' +
												'<form id="createEventForm">' +
												'<h2>Create an event</h2>' +
												'<div>' +
													'<strong>Name</strong>' +
													'<input type="text" name="name" required />' +
												'</div>' +
												'<div>' +
													'<strong>Description</strong>' +
													'<textarea name="description"></textarea>' +
												'</div>' +
												'{{#datetime}}' +
													'<div>' +
														'<strong>Start</strong>' +
														'<input type="datetime" name="start-time" required />' +
													'</div>' +
													'<div>' +
														'<strong>End</strong>' +
														'<input type="datetime" name="end-time" required />' +
													'</div>' +
												'{{/datetime}}' +
												'{{^datetime}}' +
													'<div>' +
														'<strong>Start</strong>' +
														'<select name="start-date" class="event-date" required>' +
															'<option disabled>Select a date</option>' +
															'<optgroup label="June">' +
																'<option value="24-6">Mon 24 June</option>' +
																'<option value="25-6">Tue 25 June</option>' +
																'<option value="26-6">Wed 26 June</option>' +
																'<option value="27-6">Thu 27 June</option>' +
																'<option value="28-6">Fri 28 June</option>' +
																'<option value="29-6">Sat 29 June</option>' +
																'<option value="30-6">Sun 30 June</option>' +
															'</optgroup>' +
															'<optgroup label="July">' +
																'<option value="01-7">Mon 1 July</option>' +
																'<option value="02-7">Tue 2 July</option>' +
																'<option value="03-7">Wed 3 July</option>' +
																'<option value="04-7">Thu 4 July</option>' +
																'<option value="05-7">Fri 5 July</option>' +
																'<option value="06-7">Sat 6 July</option>' +
																'<option value="07-7">Sun 7 July</option>' +
																'<option value="08-7">Mon 8 July</option>' +
																'<option value="09-7">Tue 9 July</option>' +
																'<option value="10-7">Wed 10 July</option>' +
																'<option value="11-7">Thu 11 July</option>' +
																'<option value="12-7">Fri 12 July</option>' +
																'<option value="13-7">Sat 13 July</option>' +
																'<option value="14-7">Sun 14 July</option>' +
															'</optgroup>' +
														'</select>' +

														'<select name="start-time" required>' +
															'<option disabled>Time</option>' +
															'<optgroup label="AM">' +
																'<option value="00:00">00:00</option>' +
																'<option value="00:30">00:30</option>' +
																'<option value="01:00">01:00</option>' +
																'<option value="01:30">01:30</option>' +
																'<option value="02:00">02:00</option>' +
																'<option value="02:30">02:30</option>' +
																'<option value="03:00">03:00</option>' +
																'<option value="03:30">03:30</option>' +
																'<option value="04:00">04:00</option>' +
																'<option value="04:30">04:30</option>' +
																'<option value="05:00">05:00</option>' +
																'<option value="05:30">05:30</option>' +
																'<option value="06:00">06:00</option>' +
																'<option value="06:30">06:30</option>' +
																'<option value="07:00">07:00</option>' +
																'<option value="07:30">07:30</option>' +
																'<option value="08:00">08:00</option>' +
																'<option value="08:30">08:30</option>' +
																'<option value="09:00">09:00</option>' +
																'<option value="09:30">09:30</option>' +
																'<option value="10:00">10:00</option>' +
																'<option value="10:30">10:30</option>' +
																'<option value="11:00">11:00</option>' +
																'<option value="11:30">11:30</option>' +
															'</optgroup>' +
															'<optgroup label="PM">' +
																'<option value="12:00">12:00</option>' +
																'<option value="12:30">12:30</option>' +
																'<option value="13:00">13:00</option>' +
																'<option value="13:30">13:30</option>' +
																'<option value="14:00">14:00</option>' +
																'<option value="14:30">14:30</option>' +
																'<option value="15:00">15:00</option>' +
																'<option value="15:30">15:30</option>' +
																'<option value="16:00">16:00</option>' +
																'<option value="16:30">16:30</option>' +
																'<option value="17:00">17:00</option>' +
																'<option value="17:30">17:30</option>' +
																'<option value="18:00">18:00</option>' +
																'<option value="18:30">18:30</option>' +
																'<option value="19:00">19:00</option>' +
																'<option value="19:30">19:30</option>' +
																'<option value="20:00">20:00</option>' +
																'<option value="20:30">20:30</option>' +
																'<option value="21:00">21:00</option>' +
																'<option value="21:30">21:30</option>' +
																'<option value="22:00">22:00</option>' +
																'<option value="22:30">22:30</option>' +
																'<option value="23:00">23:00</option>' +
																'<option value="23:30">23:30</option>' +
															'</optgroup>' +
														'</select>' +
													'</div>' +
													'<div>' +
														'<strong>End</strong>' +
														'<select name="end-date" class="event-date" required>' +
															'<option disabled>Select a date</option>' +
															'<optgroup label="June">' +
																'<option value="24-6">Mon 24 June</option>' +
																'<option value="25-6">Tue 25 June</option>' +
																'<option value="26-6">Wed 26 June</option>' +
																'<option value="27-6">Thu 27 June</option>' +
																'<option value="28-6">Fri 28 June</option>' +
																'<option value="29-6">Sat 29 June</option>' +
																'<option value="30-6">Sun 30 June</option>' +
															'</optgroup>' +
															'<optgroup label="July">' +
																'<option value="01-7">Mon 1 July</option>' +
																'<option value="02-7">Tue 2 July</option>' +
																'<option value="03-7">Wed 3 July</option>' +
																'<option value="04-7">Thu 4 July</option>' +
																'<option value="05-7">Fri 5 July</option>' +
																'<option value="06-7">Sat 6 July</option>' +
																'<option value="07-7">Sun 7 July</option>' +
																'<option value="08-7">Mon 8 July</option>' +
																'<option value="09-7">Tue 9 July</option>' +
																'<option value="10-7">Wed 10 July</option>' +
																'<option value="11-7">Thu 11 July</option>' +
																'<option value="12-7">Fri 12 July</option>' +
																'<option value="13-7">Sat 13 July</option>' +
																'<option value="14-7">Sun 14 July</option>' +
															'</optgroup>' +
														'</select>' +

														'<select name="end-time" required>' +
															'<option disabled>Time</option>' +
															'<optgroup label="AM">' +
																'<option value="00:00">00:00</option>' +
																'<option value="00:30">00:30</option>' +
																'<option value="01:00">01:00</option>' +
																'<option value="01:30">01:30</option>' +
																'<option value="02:00">02:00</option>' +
																'<option value="02:30">02:30</option>' +
																'<option value="03:00">03:00</option>' +
																'<option value="03:30">03:30</option>' +
																'<option value="04:00">04:00</option>' +
																'<option value="04:30">04:30</option>' +
																'<option value="05:00">05:00</option>' +
																'<option value="05:30">05:30</option>' +
																'<option value="06:00">06:00</option>' +
																'<option value="06:30">06:30</option>' +
																'<option value="07:00">07:00</option>' +
																'<option value="07:30">07:30</option>' +
																'<option value="08:00">08:00</option>' +
																'<option value="08:30">08:30</option>' +
																'<option value="09:00">09:00</option>' +
																'<option value="09:30">09:30</option>' +
																'<option value="10:00">10:00</option>' +
																'<option value="10:30">10:30</option>' +
																'<option value="11:00">11:00</option>' +
																'<option value="11:30">11:30</option>' +
															'</optgroup>' +
															'<optgroup label="PM">' +
																'<option value="12:00">12:00</option>' +
																'<option value="12:30">12:30</option>' +
																'<option value="13:00">13:00</option>' +
																'<option value="13:30">13:30</option>' +
																'<option value="14:00">14:00</option>' +
																'<option value="14:30">14:30</option>' +
																'<option value="15:00">15:00</option>' +
																'<option value="15:30">15:30</option>' +
																'<option value="16:00">16:00</option>' +
																'<option value="16:30">16:30</option>' +
																'<option value="17:00">17:00</option>' +
																'<option value="17:30">17:30</option>' +
																'<option value="18:00">18:00</option>' +
																'<option value="18:30">18:30</option>' +
																'<option value="19:00">19:00</option>' +
																'<option value="19:30">19:30</option>' +
																'<option value="20:00">20:00</option>' +
																'<option value="20:30">20:30</option>' +
																'<option value="21:00">21:00</option>' +
																'<option value="21:30">21:30</option>' +
																'<option value="22:00">22:00</option>' +
																'<option value="22:30">22:30</option>' +
																'<option value="23:00">23:00</option>' +
																'<option value="23:30">23:30</option>' +
															'</optgroup>' +
														'</select>' +
													'</div>' +
												'{{/datetime}}' +										
												'<div>Remember to convert to Danish Time - Returned as 2011-10-18T00:00:00.00Z</div>' +
												'<button type="submit">Place Location</button>' +
												'</form>' +
											'</div>'

            }
        </script>
		<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=true"></script>
        <script type="text/javascript" src="http://r.oskil.de/js/infobox_packed.js"></script>
        <script type="text/javascript" src="http://r.oskil.de/js/richmarker-compiled.js"></script>
        
        <div id="loading"><div></div><div id="confirm">Done!</div></div>
        <div id="dynamic"></div>
    </body>
</html>'<div>Remember to convert to Danish Time - Returned as 2011-10-18T00:00:00.00Z</div>' +
												'<button type="submit">Place Location</button>' +
												'</form>' +
											'</div>'

            }
        </script>
		<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=true"></script>
        <script type="text/javascript" src="http://r.oskil.de/js/infobox_packed.js"></script>
        <script type="text/javascript" src="http://r.oskil.de/js/richmarker-compiled.js"></script>
        
        <div id="loading"><div></div><div id="confirm">Done!</div></div>
        <div id="dynamic"></div>
    </body>
</html>