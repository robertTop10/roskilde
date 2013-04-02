<?php
function iOSDetect() {
   $browser = strtolower($_SERVER['HTTP_USER_AGENT']); // Checks the user agent
   if(strstr($browser, 'iphone') || strstr($browser, 'ipod')) {
      $device = 'iPhone';
   } else { $device = 'default'; }	
   return($device);
}
?>
<!DOCTYPE HTML>
<!--html lang="en" manifest="/php/cache-manifest.appcache"-->
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="user-scalable=no, initial-scale=1, minimum-scale=1">
        
        <title>Roskilde 2013</title>
        <link href="/images/favicon.ico" rel="shortcut icon" type="image/x-icon" />
        
        <meta name="apple-mobile-web-app-capable" content="yes" />

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

            var user = {};
			var schedule;
			var openInfoWindow;
            
            window.fbAsyncInit = function() {
                // init the FB JS SDK
                FB.init({
                    appId      : '357860537664045', // App ID from the App Dashboard
                    channelUrl : '//r.oskil.de/php/channel.php', // Channel File for x-domain communication
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
            
                       
            function login() {
                FB.login(function(response) {
                    if (response.authResponse) {
                        loggedIn();
                    } else {
                        loggedOut();
                    }
                });                
            }

            function loggedIn() {
                FB.api('/me', function(response) {
                    user = response;
                    $(document.getElementById('content')).html(mustache(templates.statusLoggedIn, response));
					                    
                    checkUser(function(data) {
						if (data.user && !isNaN(data.user.id)) {
							user = data.user;
							FB.api('/me/friends', function(response) {
								postFriends(response);
							});
						}
						
						finishLoading();

                    });
                });
            }
            
            function loggedOut() {
                $(document.getElementById('content')).html(mustache(templates.statusLoggedOut));
				finishLoading();
            }
            
            function checkUser(cb) {
                var data    = user;
                data.fb_id  = user.id;
                data.action = 'auth';
                
                $.ajax({
                    type: "POST",
                    url: "http://r.oskil.de/php/api.php",
                    data: user
                }).done(function(data) {
                    if (cb) { cb(data); }
                });
            }
			
			function postFriends(friends) {
				console.log(friends);
				var data 		= {};
				data.action		= 'friends';
				data.id 		= user.id;
				data.fb_id		= user.fb_id;
				data.friends	= friends.data;
				
                $.ajax({
                    type: "POST",
                    url: "http://r.oskil.de/php/api.php",
                    data: data
                }).done(function(data) {
                    //if (cb) { cb(data); }
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
						finishLoading();
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
						url: "http://r.oskil.de/php/api.php",
						data: position
					}).done(function(data) {
						console.log(data);
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
					url: "http://r.oskil.de/php/api.php",
					data: {action: 'findFriends', id: user.id, fb_id: user.fb_id}
				}).done(function(data) {
					initMap(data);
				});
			}
			
			function initMap(data, mode) {
				console.log('ff', data);
				$(document.getElementById('content')).html(templates.mapCanvas);
				
				if (typeof data === 'string') {data = JSON.parse(data) }; // FF and jQuery not recognising a JSON response
				
				navigator.geolocation.getCurrentPosition(function(coords) {
					console.log(coords);
					var m			= document.getElementById("map-canvas");
					var me			= new google.maps.LatLng(coords.coords.latitude, coords.coords.longitude);
					var mapOptions 	= {
						center: me,
						zoom: 4,
						disableDefaultUI: true,
						mapTypeId: google.maps.MapTypeId.ROADMAP
					};
					var map = new google.maps.Map(document.getElementById("map-canvas"), mapOptions);
					
					setRoskildeMap(map);
					
					var markers = [];
					markers.push(marker(coords.coords.latitude, coords.coords.longitude, map, 'Me', user.fb_id, false, 100));

					var length 	= data.length;
					var z		= length + 1;
					for (var i = 0; i < length; i++) {
						var d = data[i];
						var name = (mode !== 'locations') ? d.name : d.message;
						markers.push(marker(d.latitude, d.longitude, map, name, d.fb_id, d.timestamp, z--, mode));
					}
					
					fitToMarkers(markers, map);
					
					showCompass();
					
				}, noPosition);
				
			}

			function initRoskildeMap() {
				$(document.getElementById('content')).html(templates.mapCanvas);
				
				navigator.geolocation.getCurrentPosition(function(coords) {
					var m			= document.getElementById("map-canvas");
					var center		= new google.maps.LatLng(55.619080, 12.077461);
					var mapOptions 	= {
						center: center,
						zoom: 15,
						disableDefaultUI: true,
						mapTypeId: google.maps.MapTypeId.ROADMAP
					};
					var map = new google.maps.Map(document.getElementById("map-canvas"), mapOptions);
					
					setRoskildeMap(map);
					
					var markers = [];
					markers.push(marker(coords.coords.latitude, coords.coords.longitude, map, 'Me', user.fb_id, false, 100));
					/*
					// Roskilde Geo Data can go in here
					var length = data.length;
					for (var i = 0; i < length; i++) {
						var d = data[i];
						var name = (mode !== 'locations') ? d.name : d.message;
						markers.push(marker(d.latitude, d.longitude, map, name, d.fb_id, d.timestamp, null, mode));
					}
					
					fitToMarkers(markers, map);
					*/
					
					showCompass();
				}, noPosition);
				
			}

			function setRoskildeMap(map) {
				var se	=	new google.maps.LatLng(55.608, 12.0542);
				var nw	=	new google.maps.LatLng(55.62535, 12.10675);
				var imageBounds = new google.maps.LatLngBounds(se, nw);
				var festival = new google.maps.GroundOverlay("http://r.oskil.de/images/map.gif", imageBounds);
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
                    pixelOffset: new google.maps.Size(-80, (boxText.offsetHeight + 30) * -1),
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
						url: "http://r.oskil.de/php/api.php",
						data: position
					}).done(function(data) {
						console.log(data);
					});
				}
				}, noPosition);
			}
			
			function getLocation() {
				$.ajax({
					type: "POST",
					url: "http://r.oskil.de/php/api.php",
					data: {action: 'getLocations', user_id: user.id, fb_id: user.fb_id, name: user.name}
				}).done(function(data) {
					initMap(data, 'locations');
				});
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
					$.getJSON('http://r.oskil.de/php/xml2jsonTest.php', function(data) {
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
				var html = '<div class="stages"><div id="date" class="stage_name">' + days[new Date(dates[i] * 1000).getDay()] + ' ' + date + ((date - 1 > 3) ? nth[3] : nth[date - 1]) + '</div>';
				
				$.each(data.stages, function(i,v) {
				    html += '<div class="stage_name">' + v + '</div>';
				});
				html    += '</div>';
				
				var style = '';
				if (checkCalc() === false) {
    				var width = $('#content').outerWidth() - 60;
    				style = ' style="width: ' + width + 'px;"'
				}
				
				html    += '<div class="schedule_scroller"' + style + '>';
				html    += '<div class="schedule_container">';

				
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
				
				$('.schedule_container').css('width', width + 'px');
				
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
						html += '<div class="band" style="margin-left: ' + margin + 'px;"><div>' + stage[min]['artistName'] + '</div><span>' + time.getHours() + ':' + time.getMinutes().pad() + '</span></div>';
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
				$(document.getElementById('loading')).show();	
			}
			
			function finishLoading() {
				$(document.getElementById('loading')).hide();
			}
			
            function checkCalc() {
                var prop = 'width:';
                var value = 'calc(10px);';
                var el = document.createElement('div');
                
                el.style.cssText = prop + ["", "-webkit-", ""].join(value + prop);
                
                return !!el.style.length;
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
				
                $(document).on("click", "#login", function(e){
                    e.preventDefault();
                    login();
                });
				
                $(document).on("click", "#checkin", function(e){
                    e.preventDefault();
					loading();
					navigator.geolocation.getCurrentPosition(getPosition, noPosition);
                });
				
                $(document).on("click", "#findFriends", function(e){
                    e.preventDefault();
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
					getLocation();
                });
				
				$(document).on("click", "#map", function(e){
					e.preventDefault();
					initRoskildeMap();
				});

				$(document).on("click", "#schedule", function(e){
					e.preventDefault();
					getSchedule();
				});

            });
            
            var templates = {
                statusLoggedOut:    		'<div class="status">Logged Out</div><button id="login">Log In</button>',
                statusLoggedIn:     		'<div class="status">Welcome {{first_name}} {{last_name}} from {{#hometown}}{{name}}{{/hometown}}</div>' +
											'<div class="menu_button">{{> checkInButtonPartial}}</div>' +
											'<div class="menu_button">{{> findFriendsButtonPartial}}</div>' +
											'<div class="menu_button">{{> locationButtonPartial}}</div>' +
											'<div class="menu_button">{{> getLocationButtonPartial}}</div>' +
											'<div class="menu_button">{{> mapButtonPartial}}</div>' +
											'<div class="menu_button">{{> scheduleButtonPartial}}</div>',
											
				checkInButtonPartial:		'<button id="checkin">CHECK IN</button>',
				findFriendsButtonPartial:	'<button id="findFriends">FIND FRIENDS</button>',
				locationButtonPartial:		'<button id="remLocation">REMEMBER LOCATION</button>',
				getLocationButtonPartial:	'<button id="getLocation">LOCATIONS</button>',
				mapButtonPartial:			'<button id="map">MAP</button>',
				scheduleButtonPartial:		'<button id="schedule">SCHEDULE</button>',
				
				mapCanvas:					'<div id="map-canvas" class="map_canvas"></div><div id="compass" class="compass"></div>',
				messageBox:					'<div id="message" class="message"><textarea></textarea><button id="messageSubmit{{action}}">SUBMIT</button></div>',
				
				marker:                     '<div class="ros_marker">' +
				                                '<img src="{{{src}}}" height="25" width="25"/>' +
				                                '<span>{{#time}}{{time}}{{/time}}</span>' +
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
											'</div>'

            }
        </script>
		<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=true"></script>
        <script type="text/javascript" src="http://r.oskil.de/js/infobox_packed.js"></script>
        <script type="text/javascript" src="http://r.oskil.de/js/richmarker-compiled.js"></script>
        
        <div id="loading"><div></div></div>
        <div id="dynamic"></div>
    </body>
</html>