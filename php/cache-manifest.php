<?php
header('Content-Type: text/cache-manifest');
?>
CACHE MANIFEST

CACHE:

/html/frame.html

# CSS
http://yui.yahooapis.com/3.9.1/build/cssreset/cssreset-min.css
/css/main.css

# Images
<?php
	if ($handle = opendir('../images')) {
	    while (false !== ($entry = readdir($handle))) {
	    	if (substr($entry, -4) === '.gif' || substr($entry, -4) === '.png' || substr($entry, -4) === '.ico') {
	        	echo "/images/$entry\n";
	    	}
	    }

	    closedir($handle);
	}
?>

# JS
http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js
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
http://maps.googleapis.com/maps/api/js?sensor=true
http://maps.gstatic.com/intl/en_us/mapfiles/api-3/12/11/main.js

# Schelude
/php/feeds/scheduleJSON.php
/php/feeds/artistsJSON.php

# CHANGE ---------------------------------------------------------------------------------
http://www.google.com/intl/en_us/mapfiles/close.gif

# Facebook
/php/channel.php
http://connect.facebook.net/en_US/all.js


NETWORK:
*

# index.php <?php  echo date ("F d Y H:i:s", filemtime('../index.php')).PHP_EOL; ?>
# cache-manifest.php <?php  echo date ("F d Y H:i:s", filemtime('cache-manifest.php')); ?>