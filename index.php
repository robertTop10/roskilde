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

$avatar = ($FBuser && is_numeric($FBuser)) ? '<div id="user-avatar"><img src="https://graph.facebook.com/'.$FBuser.'/picture?width=80&height=80" /></div>' : '<div id="user-avatar" class="none"></div>';
?>
<!DOCTYPE HTML>
<html lang="en" manifest="/php/cache-manifest.appcache">
<!-- html lang="en" -->
    <head>
    	<!-- Amazon -->
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1, minimum-scale=1, maximum-scale=1" />
        
        <title>Roskilde 2013</title>
		<meta name="description" content="The app for Roskilde Festival 2013. Find your friends, find the artists." />
        <meta name="keywords" content="Roskilde Festival, Roskilde, Festival, Denmark, Friends, Music" />

		<meta property="og:title" content="Roskilde 2013" />
		<meta property="og:type" content="website" />
		<meta property="og:url" content="http://r.oskil.de" />
		<meta property="og:image" content="http://r.oskil.de/new-images/og-image.png" />
		<meta property="og:site_name" content="Roskilde 2013" />
		<meta property="og:description" content="The app for Roskilde Festival 2013. Find your friends, find the artists." />

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
		<link rel="stylesheet" href="css/new.css" type="text/css" />
    </head>
    
    <body<?php if(iOSDetect() == 'iPhone') { echo ' class="ios"';} ?>>
        <div id="menu" class="menu">
        	<div id="home-button" class="home_button"><span /></div>
        	<h2 id="section-title"></div>
        	Roskilde 2013
        	<?php echo $avatar; ?>
       	</div>
        <div id="content"></div>
        

        <div id="fb-root"></div>
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
        <script src="http://maps.googleapis.com/maps/api/js?sensor=true"></script>
        <script src="/js/mustache.js"></script>
        <script src="/js/fastclick.js"></script>
        <script src="/js/events.js"></script>
        <script src="/js/helpers.js"></script>
        <script src="/js/icons.js"></script>
        <script src="/js/login.js"></script>
        <script src="/js/map.js"></script>
        <script src="/js/schedule.js"></script>
        <script src="/js/templates.js"></script>
        <script src="/js/index.js"></script>

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
	           			changeTitle();
						mainMenu();
	           		} else {
	           			// Basically disables the forward button for now, till we can introduce routing
	           			pushState('', document.title, '/', true);
	           		}
				});
           	}

            templates['statusLoggedOut'] 	= 	'<div class="status">';
            templates['statusLoggedOut'] 	+= 	'<div class="main_menu_logo"></div>';
            templates['statusLoggedOut'] 	+= 	'<ul>';
            templates['statusLoggedOut'] 	+= 	'<li>Check in and find your friends</li>';
            templates['statusLoggedOut'] 	+= 	'<li>Festival <a id="schedule" href="#">Schedule</a> and <a id="getArtists" href="#">Artists</a></li>';
            templates['statusLoggedOut'] 	+= 	'<li>Create your own schedule</li>';
            templates['statusLoggedOut'] 	+= 	'<li>Create events for the whole festival to see</li>';
            templates['statusLoggedOut'] 	+= 	'<li>Available offline</li>';
            templates['statusLoggedOut'] 	+= 	'</ul>';
            templates['statusLoggedOut'] 	+= 	'</div>';
            templates['statusLoggedOut'] 	+= 	'<div class="status"><a href="<?php echo $loginUrl; ?>" class="button fb_button">Facebook Login</a></div>';
            
            var content 	= document.getElementById('content');
            var $content 	= $(content);

            // User vars
			var fbUser;
            var user;

            // Cache schedule obj
			var schedule;

			// Cacge facilties onj
			var facilties;

			// Map Markers
			var openInfoWindow;
			var createEventMarker;
			
			// map.js - Keep these global and delete them when on menu to keep memory down
			var map;
			var markers;
			var markerCluster;

			// Map Elements
			var iframe;
			var iframeDoc;
			var m;

			// Keep track of AJAX request and abort them if need be.
			var xhr;

			// Language
			var danish = false;

			// For resetting scroll position
			var contentScrollTop;

			// SVG support
			var svg = checkSVG();

            // Remember title after downloading
            var title = null;


			var festivalCoords   = {
				coords: {
					accuracy: 1,
					latitude: 55.619080,
					longitude: 12.077461
				}
			}            
        </script>

        <script type="text/javascript" src="//connect.facebook.net/en_US/all.js"></script>
        <script type="text/javascript" src="/js/infobox-compiled.js"></script>
        <script type="text/javascript" src="/js/richmarker-compiled.js"></script>
        <script type="text/javascript" src="/js/markerclusterer-compiled.js"></script>
        
        <div id="loading">
        	<div><span></span></div>
        	<div id="confirm">Done!</div>
       	</div>
        <div id="dynamic"></div>
    </body>
</html>