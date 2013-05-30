// GENERIC FUNCTIONS
// ------------------------------------------------------------

function initMap(data, fit, cb) {
	console.log('initMap', data);
	$(document.getElementById('content')).html(templates.mapCanvas);

	if (typeof data === 'string') {data = JSON.parse(data); } // FF and jQuery not recognising a JSON response

	$(document.getElementById('map-iframe')).load(function (){
		navigator.geolocation.getCurrentPosition(function(coords) {
			gotLocation(data, fit, cb, coords);
		}, function(error) {
			gotLocation(data, fit, cb, festivalCoords, true);
		}, {timeout: 8000});
	});
}


function setRoskildeMap(map) {
	var se	=	new google.maps.LatLng(55.608, 12.0542);
	var nw	=	new google.maps.LatLng(55.62535, 12.10675);
	var imageBounds = new google.maps.LatLngBounds(se, nw);
	var festival = new google.maps.GroundOverlay("http://r.oskil.de/new-images/map.gif", imageBounds, {clickable: false});
	festival.setMap(map);
}


// INITMAP CALLBACK
// ------------------------------------------------------------

function gotLocation(data, fit, cb, coords, error) {
	console.log('gotLocation', data, coords);

	var iframe	= document.getElementById('map-iframe');

	if (iframe) {
		iframe		= iframe.contentDocument || iframe.contentWindow.document;


		var m			= iframe.getElementById("map-canvas");
		var me			= new google.maps.LatLng(coords.coords.latitude, coords.coords.longitude);
		var center      = (typeof fit === 'object') ? new google.maps.LatLng(fit.coords.latitude, fit.coords.longitude) : me;

		var mapOptions	= {
			center: center,
			zoom: 15,
			disableDefaultUI: true,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		};

		map = new google.maps.Map(iframe.getElementById("map-canvas"), mapOptions);

		google.maps.event.addListenerOnce(map, 'idle', function(){
			finishLoading();
		});

		setRoskildeMap(map);

		markers = [];

	    if (!error) {
	        // Update to new callback func
	        //markers.push(marker(coords.coords.latitude, coords.coords.longitude, map, 'Me', user.fb_id, false, 100));
	        markers.push(iconMe(coords.coords.latitude, coords.coords.longitude, map));
	    }


	    if (cb) { cb(data, coords, map, markers); }

	    if (fit === true) { fitToMarkers(markers, map); }

		showCompass();
	}
}


// SHOW ROSKILE MAP
// ------------------------------------------------------------

function initRoskildeMap() {
	initMap(null, festivalCoords, function() {
		console.log('geoData goes here');
	});
}


// MARKERS
// ------------------------------------------------------------

function populateMarker(data, coords, map, markers, cb) {
    var length 	= data.result.length;
    var z		= length + 1;
    for (var i = 0; i < length; i++) {
        var d 	= data.result[i];
        markers.push(cb(d, markers, z--));
    }
}


function fitToMarkers(markers, map) {
	var bounds = new google.maps.LatLngBounds();
	var length = markers.length;
	for (var i = 0; i < length; i++) {
		bounds.extend(new google.maps.LatLng(markers[i].getPosition().lat(), markers[i].getPosition().lng()));
		map.fitBounds(bounds);
	}
}