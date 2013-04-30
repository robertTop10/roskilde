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
        <script src="/js/mustache.js"></script>
        <script src="/js/fastclick.js"></script>
        <script src="/js/events.js"></script>
        <script src="/js/helpers.js"></script>
        <script src="/js/icons.js"></script>
        <script src="/js/login.js"></script>
        <script src="/js/map.js"></script>
        <script src="/js/schedule.js"></script>
        <script src="/js/templates.js"></script>

        <script>
            // navigator.onLine - check if online!
            // http://stackoverflow.com/questions/7131909/facebook-callback-appends-to-return-url
            if (window.location.hash == '#_=_') {
                window.location.hash = '';                                          // for older browsers, leaves a # behind
                history.pushState('', document.title, window.location.pathname);    // nice and clean
            }

            templates['statusLoggedOut'] = '<div class="status">Logged Out</div><div class="status"><a href="<?php echo $loginUrl; ?>" class="button">Log In</a></div>';
            
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
				// TODO - This top parsing of co-ords needs be used on all geo-location objects
				if (position && position.coords.latitude && position.coords.longitude) {
					var data		= {};
					var headings	= ['accuracy', 'latitude','longitude'];
					$.each(headings, function(i,v) {
						if (!isNaN(position.coords[v])) { data[v] = position.coords[v]; }	
					});

					createCheckIn(data.latitude, data.longitude, data.accuracy);
				}
			}
			
			
			function initCreateEventsMap(data) {
    			initMap(data, false, function(data, coords, map, markers) {
        			var m    = document.getElementById("map-canvas");
        			var $m   = $(m);
        			
        			$m.after(templates.createEventOptions);
        			$m.data({
        				'my-location-latitude': coords.coords.latitude,
        				'my-location-longitude': coords.coords.longitude,
        				'my-location-accuracy': coords.coords.accuracy,
        				'form': data
        			});
        			
					google.maps.event.addListener(map, 'click', function(e) {
						if (createEventMarker) { createEventMarker.setMap(null); }
						//createEventMarker = marker(e.latLng.jb, e.latLng.kb, map, 'Event', "http://r.oskil.de/images/logo.png", null, null, 'createEvent');
						createEventMarker = iconPin(e.latLng.jb, e.latLng.kb, map, {
							icon: 		'/images/logo.png',
							timestamp: 	new Date().getTime(),
							title: 		'Now',
							zIndex: 	null
						});

	        			$m.data({
	        				'my-marker-latitude': e.latLng.jb,
	        				'my-marker-longitude': e.latLng.kb
	        			});
						
						$(document.getElementById('createEventMarker')).show();
					});
    			});
			}


			function createCheckIn(latitude, longitude, accuracy, data) {
				console.log('createCheckIn');

				var obj = {
					user_id: 		user.id,
					fb_id: 			user.fb_id,
					user: 			user.name,

					latitude: 		latitude,
					longitude: 		longitude,
					accuracy: 		accuracy
				}
				
                $.ajax({
                    type: "POST",
                    url: "/php/api.php",
                    data: {action: 'createCheckIn', event: obj}
                }).done(function(data) {
					console.log('createCheckIn Done', data);
					finishLoading(true);
					loggedIn();
                });
			}


			function createEvent(latitude, longitude, accuracy, data) {
				console.log('createEvent');
				loading();
				
				var obj = {
					user_id: 		user.id,
					fb_id: 			user.fb_id,
					user: 			user.name,

					latitude: 		latitude,
					longitude: 		longitude,
					accuracy: 		accuracy,

					name: 			data.name,
					description: 	data.description,
					start: 			data.start,
					end: 			data.end
				}
				
                $.ajax({
                    type: "POST",
                    url: "/php/api.php",
                    data: {action: 'createEvent', event: obj}
                }).done(function(data) {
					console.log('createEvent Done', data);
					finishLoading(true);
					loggedIn();
                });

			}


			function createLocation(latitude, longitude, accuracy, data) {
				console.log('createLocation');
				loading();

				var obj = {
					user_id: 		user.id,
					fb_id: 			user.fb_id,
					user: 			user.name,

					latitude: 		latitude,
					longitude: 		longitude,
					accuracy: 		accuracy,

					title: 			data.title,
					message: 		data.msg
				}
					
				$.ajax({
					type: "POST",
					url: "/php/api.php",
					data: {action: 'createLocation', event: obj}
				}).done(function(data) {
					console.log('createLocation Done', data);
					finishLoading(true);
					loggedIn();
				});
			}


			function findFriends() {
				console.log('Find Friends');
				
				$.ajax({
					type: "POST",
					url: "/php/api.php",
					data: {action: 'findFriends', id: user.id, fb_id: user.fb_id}
				}).done(function(data) {
					initMap(data, true, function(data, coords, map, markers) {
						populateMarker(data, coords, map, markers, function(d, markers, z) {
							return iconFriend(d.latitude, d.longitude, map, {
								icon: 		d.fb_id,
								timestamp: 	d.timestamp,
								title: 		d.user,
								zIndex: 	z
							});
						});
					});
				});
			}


			function getLocation() {
				$.ajax({
					type: "POST",
					url: "/php/api.php",
					data: {action: 'getLocations', user_id: user.id, fb_id: user.fb_id, name: user.name}
				}).done(function(data) {
					initMap(data, true, function(data, coords, map, markers) {
						populateMarker(data, coords, map, markers, function(d, markers, z) {
							return iconPin(d.latitude, d.longitude, map, {
								icon: 		'/images/logo.png',
								img: 		'/images/logo.png',
								message: 	d.message,
								title: 		d.title,
								tooltip: 	true						
							});
						});
					});
				});
			}


			function getEvents() {   			
                $.ajax({
                    type: "POST",
                    url: "/php/api.php",
                    data: {action: 'getEvents'}
				}).done(function(data) {
					initMap(data, true, function(data, coords, map, markers) {
						populateMarker(data, coords, map, markers, function(d, markers, z) {
							console.log(d.start, d.end, new Date(parseInt(d.start)) + ' - ' + new Date(parseInt(d.end)));
							return iconPin(d.latitude, d.longitude, map, {
								icon: 		'/images/logo.png',
								img: 		'/images/logo.png',
								message: 	d.description,
								sTime: 		formatTime(d.start),
								eTime: 		formatTime(d.end),
								ftime: 		formatTime(d.fstart) + ' - ' + formatTime(d.fend),
								timestamp: 	d.start,
								title: 		d.name,
								tooltip: 	true,
								schedule: 	{
									button: 	true,
									start: 		d.start,
									end: 		d.end,
									fstart: 	d.fstart,
									fend: 		d.fend,
									id: 		d.id,
									latitude: 	d.latitude,
									longitude: 	d.longitude
								},
								zIndex: 	z
							});
						});
					});
				});			
			}


			function addToMySchedule(e) {
				e.preventDefault();
				console.log(e, $(e.target).data());
				var r = confirm("Do you want to add to your schedule?");
				if (r === true) {
					console.log('Adding event');
					var schedule 	= JSON.parse(localStorage.getItem('mySchedule'));
					var data 		= $(e.target).data();
					var result;

					if (schedule === null) {
						result = JSON.stringify([data]);
					} else {
						schedule.push(data);
						result = JSON.stringify(schedule);
					}

					var set = setLocalStorage('mySchedule', result);

					if (set === true) {
						$(e.target).removeClass('add-to-schedule');
						$(e.target).addClass('remove-from-schedule');
						$(e.target).text('Remove from My Schedule');

						finishLoading(true);
					} else {
						finishLoading();
					}
				} else {
					finishLoading();
				}
			}


			function removeFromMySchedule(e) {
				e.preventDefault();
				console.log(e, $(e.target).data());
				var r = confirm("Do you want to remove this from your schedule?");
				if (r === true) {
					console.log('Removing event');
					var schedule 	= JSON.parse(localStorage.getItem('mySchedule'));
					var data 		= $(e.target).data();
					var result;

					var id 			= data.id;
					var type 		= data.type;

					if (data !== null && schedule !== null && schedule.length) {
						for (i = 0, len = schedule.length; i < len; i++) {
							console.log(schedule[i].id, id, schedule[i].type);
							if (schedule[i].id === id && schedule[i].type === type) {
								schedule.splice(i, 1);
								break;
							}
						}
					}

					var set = setLocalStorage('mySchedule', JSON.stringify(schedule));

					if (set === true) {
						$(e.target).removeClass('remove-from-schedule');
						$(e.target).addClass('add-to-schedule');
						$(e.target).text('Add to My Schedule');

						finishLoading(true);
					} else {
						finishLoading();
					}
				} else {
					finishLoading();
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


			function getMySchedule() {
				console.log('getMySchedule');
				var data = JSON.parse(localStorage.getItem('mySchedule'));

				if (data !== null && data.length) {
					$.each(data, function(i,v) {
						v.formattedStart 	= formatTime(v.start);
						v.formattedEnd 		= formatTime(v.end);
					});

					data.sort(function(a, b) {
						return a.start - b.start;
					});
				}

				$(document.getElementById('content')).html(mustache(templates.mySchedule, {results: data}));
				finishLoading();
			}
            
        </script>

        <script type="text/javascript" src="//connect.facebook.net/en_US/all.js"></script>
		<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=true"></script>
        <script type="text/javascript" src="http://r.oskil.de/js/infobox_packed.js"></script>
        <script type="text/javascript" src="http://r.oskil.de/js/richmarker-compiled.js"></script>
        
        <div id="loading"><div></div><div id="confirm">Done!</div></div>
        <div id="dynamic"></div>
    </body>
</html>