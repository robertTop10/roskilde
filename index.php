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

// Get User ID
$FBuser = $facebook->getUser();

$avatar = ($FBuser && is_numeric($FBuser)) ? '<div id="user-avatar"><img src="https://graph.facebook.com/'.$FBuser.'/picture?width=80&height=80" height="40" width="40" /></div>' : '<div id="user-avatar" class="none"></div>';
?>
<!DOCTYPE HTML>
<!-- html lang="en" manifest="/php/cache-manifest.appcache" -->
<html lang="en">
    <head>
    	<!-- Amazon -->
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
        <div id="menu" class="menu">Roskilde 2013<?php echo $avatar; ?></div>
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
	        //console.log(applicationCache.status);
	        
        	appCacheStatus();

            // navigator.onLine - check if online!
            // http://stackoverflow.com/questions/7131909/facebook-callback-appends-to-return-url

            window.location.hash = '';

           	if (typeof history.pushState === "function") {
	           	pushState('', document.title, window.location.pathname, true);

	           	window.addEventListener("popstate", function(e) {
	           		if (window.location.pathname === '/') {
	           			removeCompass();
						mainMenu();
	           		} else {
	           			// Basically disables the forward button for now, till we can introduce routing
	           			pushState('', document.title, '/', true);
	           		}
				});
           	}

            templates['statusLoggedOut'] = '<div class="status">Logged Out</div><div class="status"><a href="<?php echo $loginUrl; ?>" class="button">Log In</a></div>';
            
            // User vars
			var fbUser;
            var user;

            // Cache schedule obj
			var schedule;

			// Map Markers
			var openInfoWindow;
			var createEventMarker;
			
			// map.js - Keep these global and delete them when on menu to keep memory down
			var map;
			var markers;

			// Keep track of AJAX request and abort them if need be.
			var xhr;

			// Language
			var danish = false;

			// For resetting scroll position
			var contentScrollTop;


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
				
                xhr = $.ajax({
                    type: "POST",
                    url: "/php/api.php",
                    data: data
                }).done(function(data) {
					console.log('Friends', data);
                }).fail(function(error) { ajaxFail(error); });

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
					var iframe	= document.getElementById('map-iframe');
					$(iframe).after(templates.createEventOptions);

					iframe		= iframe.contentDocument || iframe.contentWindow.document;

					var m		= iframe.getElementById("map-canvas");
        			var $m   	= $(m);
        			
        			$m.data({
        				'my-location-latitude': coords.coords.latitude,
        				'my-location-longitude': coords.coords.longitude,
        				'my-location-accuracy': coords.coords.accuracy,
        				'form': data
        			});
        			
					google.maps.event.addListener(map, 'click', function(e) {
						if (createEventMarker) { createEventMarker.setMap(null); }
						//createEventMarker = marker(e.latLng.jb, e.latLng.kb, map, 'Event', "http://r.oskil.de/images/logo.png", null, null, 'createEvent');
						createEventMarker = iconPin(e.latLng.lat(), e.latLng.lng(), map, {
							icon: 		'/images/logo.png',
							timestamp: 	new Date().getTime(),
							title: 		'Now',
							zIndex: 	null
						});

	        			$m.data({
	        				'my-marker-latitude': e.latLng.lat(),
	        				'my-marker-longitude': e.latLng.lng()
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
				
                xhr = $.ajax({
                    type: "POST",
                    url: "/php/api.php",
                    data: {action: 'createCheckIn', event: obj}
                }).done(function(data) {
					console.log('createCheckIn Done', data);
					finishLoading(true);
					loggedIn();
                }).fail(function(error) { ajaxFail(error); });
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
				
                xhr = $.ajax({
                    type: "POST",
                    url: "/php/api.php",
                    data: {action: 'createEvent', event: obj}
                }).done(function(data) {
					console.log('createEvent Done', data);
					finishLoading(true);
					loggedIn();
                }).fail(function(error) { ajaxFail(error); });

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
					
				xhr = $.ajax({
					type: "POST",
					url: "/php/api.php",
					data: {action: 'createLocation', event: obj}
				}).done(function(data) {
					console.log('createLocation Done', data);
					finishLoading(true);
					loggedIn();
				}).fail(function(error) { ajaxFail(error); });
			}


			function findFriends() {
				console.log('Find Friends');
				
				if (navigator.onLine === true) {
					xhr = $.ajax({
						type: "POST",
						url: "/php/api.php",
						data: {action: 'findFriends', id: user.id, fb_id: user.fb_id}
					}).done(function(data) {
						if (data.result.length === 0) {
							alert('None of your friends have checked in.');
						}
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
						
						setLocalStorage('friends', JSON.stringify({time: new Date().getTime(), friends: data}), true);

					}).fail(function(error) { ajaxFail(error); });
				} else {
					var data = JSON.parse(localStorage.getItem('friends'));

					if (data !== null) {
						alert('Your phone is offline. This data is cached from:\n\n' + timeDifference(data.time));

						data = data.friends;

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
					} else {
						alert('Sorry, your phone is offline and there is no cached data.');
						mainMenu();
					}
				}
			}


			function getLocation() {
				if (navigator.onLine === true) {
					xhr = $.ajax({
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

						setLocalStorage('locations', JSON.stringify({time: new Date().getTime(), locations: data}), true);
					}).fail(function(error) { ajaxFail(error); });
				} else {
					var data = JSON.parse(localStorage.getItem('locations'));

					if (data !== null) {
						alert('Your phone is offline. This data is cached from:\n\n' + timeDifference(data.time));

						data = data.locations;

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
					} else {
						alert('Sorry, your phone is offline and there is no cached data.');
						mainMenu();
					}
				}
			}


			function getEvents() {   			
                xhr = $.ajax({
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
				}).fail(function(error) { ajaxFail(error); });		
			}


			function addToMySchedule(e) {
				e.preventDefault();
				console.log(e, $(e.target).data());
				var text = (danish) ? "Vil du tilføje dette til dit skema?" : "Do you want to add to your schedule?";
				var r = confirm(text);
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
						var str = (danish) ?  'Fjern fra mit skema' : 'Remove from My Schedule';
						$(e.target).text(str);

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
				var text = (danish) ? "Vil du fjerne dette fra dit skema?" : "Do you want to remove this from your schedule?";
				var r = confirm(text);
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
						var str = (danish) ? 'Tilføj til mit skema' : 'Add to My Schedule';
						$(e.target).text(str);

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
				var data 	= JSON.parse(localStorage.getItem('mySchedule'));
				var exists	= 0;

				if (data !== null && data.length) {
					$.each(data, function(i,v) {
						v.formattedStart 	= formatTime(v.start);
						v.formattedEnd 		= formatTime(v.end);
					});

					data.sort(function(a, b) {
						return a.start - b.start;
					});

					exists = data.length;
				}

				$(document.getElementById('content')).html(mustache(templates.mySchedule, {results: data, length: exists, restore: (user.backup === '1') }));
				finishLoading();
			}


			function getArtists() {
				console.log('getArtists');

				if (typeof artists !== 'object') {
					$.getJSON('/php/feeds/artistsJSON.php', function(data) {
						artists = data;
						processArtists(data);
					});
				} else {
					processArtists(artists);
				}
			}


			function processArtists(artists) {
				var a = [];
				$.each(artists.artists, function(i,v) {
					if (artists.indexes[i]) {
						a.push({header: artists.indexes[i]});
					}

					a.push(v);
				});

				$(document.getElementById('content')).html(mustache(templates.listArtists, {artists: a}));
				console.log(a);
				finishLoading();
			}


			function getNews() {
				if (navigator.onLine) {
					$.getJSON('/php/feeds/newsJSON.php', function(data) {
						console.log(data);
						$(document.getElementById('content')).html(mustache(templates.news, {news: data}));

						$(document.getElementById('content')).find('a').each(function(i, v) {
							$(this).attr('target', '_blank');
						});

						finishLoading();
					});
				} else {
					alert('Sorry, Roskilde news can only be accessed when online.');
					finishLoading();
				}
			}


			function getTweets() {
				if (navigator.onLine) {
					$.getJSON('/php/feeds/twitterJSON.php', function(data) {
						console.log(data);
						$(document.getElementById('content')).html(mustache(templates.tweets, {tweets: data}));
						finishLoading();
					});
				} else {
					alert('Sorry, Roskilde tweets can only be accessed when online.');
					finishLoading();
				}
			}

			function backupSchedule() {
				var data = localStorage.getItem('mySchedule');

				if (data) {
					xhr = $.ajax({
	                    type: "POST",
	                    url: "/php/api.php",
	                    data: {action: 'backupSchedule', data: data }
					}).done(function(data) {
						if (data.status === true) {
							user.backup = '1';

							if (!document.getElementById('restoreSchedule')) {
								$(document.getElementById('cloud-schedule')).append(templates.restoreButton);
							}
						}

						finishLoading(true);
					});
				} else {
					alert('Sorry, cannot access your schedule.');
					finishLoading();
				}
			}

			function restoreSchedule() {
				xhr = $.ajax({
                    type: "POST",
                    url: "/php/api.php",
                    data: {action: 'restoreSchedule' }
				}).done(function(data) {
					console.log('done', data);
					if (data.result && data.result[0]) {
						setLocalStorage('mySchedule', data.result[0]);
						getMySchedule();
					} else {
						alert('Sorry, couldn\'t restore your schedule. Try refreshing the page and trying again');
						finishLoading();
					}
				});
			}
            
        </script>

        <script type="text/javascript" src="//connect.facebook.net/en_US/all.js"></script>
		<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=true"></script>
        <script type="text/javascript" src="/js/infobox_packed.js"></script>
        <script type="text/javascript" src="/js/richmarker-compiled.js"></script>
        
        <div id="loading"><div></div><div id="confirm">Done!</div></div>
        <div id="dynamic"></div>
    </body>
</html>