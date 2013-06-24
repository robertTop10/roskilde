<?php
header('Content-Type: text/cache-manifest');
?>
CACHE MANIFEST

CACHE:

/html/frame.html

# CSS
http://yui.yahooapis.com/3.9.1/build/cssreset/cssreset-min.css
/css/new.css

# Images
<?php
	if ($handle = opendir('../new-images')) {
	    while (false !== ($entry = readdir($handle))) {
	    	if (substr($entry, -4) === '.gif' || substr($entry, -4) === '.png' || substr($entry, -4) === '.ico' || substr($entry, -4) === '.jpg') {
	    		if ($entry !== 'og-image.png') {
		        	echo "/new-images/$entry\n";
		        }
	    	}
	    }

	    closedir($handle);
	}
?>

# JS
http://ajax.googleapis.com/ajax/libs/jquery/2.0.2/jquery.min.js
<?php
	if ($handle = opendir('../js')) {
	    while (false !== ($entry = readdir($handle))) {
	    	if (substr($entry, -3) === '.js') {
	        	echo "/js/$entry\n";
	    	}
	    }

	    closedir($handle);
	}
?>

# Google Maps
http://maps.googleapis.com/maps/api/js?sensor=true&v=3.12
http://maps.gstatic.com/intl/en_us/mapfiles/api-3/12/15/main.js

# Schelude
/php/feeds/allJSON.json
/php/feeds/facilitiesJSON.json

# Facebook
/php/channel.php
http://connect.facebook.net/en_US/all.js


NETWORK:
*

#http://facebook.com
#https://facebook.com
#http://graph.facebook.com
#https://graph.facebook.com
#http://akamaihd.net
#https://akamaihd.net
#http://maps.gstatic.com
#https://maps.gstatic.com
#http://maps.googleapis.com
#https://maps.googleapis.com

#/php/api.php
#/php/feeds/newsJSON.php
#/feeds/twitterJSON.php

#http://roskilde-festival.co.uk
#http://roskilde-festival.dk


# index.php <?php  echo date ("F d Y H:i:s", filemtime('../index.php')).PHP_EOL; ?>
# js <?php  echo date ("F d Y H:i:s", filemtime('../js')).PHP_EOL; ?>
# css <?php  echo date ("F d Y H:i:s", filemtime('../css')).PHP_EOL; ?>
# images <?php  echo date ("F d Y H:i:s", filemtime('../new-images')).PHP_EOL; ?>
# cache-manifest.php <?php  echo date ("F d Y H:i:s", filemtime('cache-manifest.php')); ?>