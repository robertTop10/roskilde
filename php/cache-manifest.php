<?php
header('Content-Type: text/cache-manifest');
?>
CACHE MANIFEST

CACHE:
/index.html

# CSS
http://yui.yahooapis.com/3.9.1/build/cssreset/cssreset-min.css
/css/main.css

# Images
/images/favicon.ico 
/images/logo.gif  
/images/spinner.gif
/images/map.gif

# JS
http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js
/js/mustache.js

# Schelude
# CHANGE ---------------------------------------------------------------------------------
http://r.oskil.de/php/xml2jsonTest.php

# Google Maps
http://maps.googleapis.com/maps/api/js?sensor=true
/js/richmarker-compiled.js
/js/infobox_packed.js

# CHANGE ---------------------------------------------------------------------------------
http://www.google.com/intl/en_us/mapfiles/close.gif

# Facebook
/php/channel.php
http://connect.facebook.net/en_US/all.js


NETWORK:
*

# index.html <?php  echo date ("F d Y H:i:s", filemtime('../index.html')).PHP_EOL; ?>
# cache-manifest.php <?php  echo date ("F d Y H:i:s", filemtime('cache-manifest.php')); ?>