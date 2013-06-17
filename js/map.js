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
	var se	=	new google.maps.LatLng(55.6076, 12.0528);
	var nw	=	new google.maps.LatLng(55.6309, 12.1097);
	var imageBounds = new google.maps.LatLngBounds(se, nw);

	overlay = new USGSOverlay(imageBounds, '/new-images/map.svg', map);
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
			maxZoom: 18,
			zoom: 15,
			disableDefaultUI: true,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		};

		map = new google.maps.Map(iframe.getElementById("map-canvas"), mapOptions);

		google.maps.event.addListenerOnce(map, 'idle', function(){
			finishLoading();
		});

		setRoskildeMap(map);
		showCompass();

		markers = [];

	    if (!error) {
	        // Update to new callback func
	        //markers.push(marker(coords.coords.latitude, coords.coords.longitude, map, 'Me', user.fb_id, false, 100));
	        markers.push(iconMe(coords.coords.latitude, coords.coords.longitude, map));
	    }


	    if (cb) { cb(data, coords, map, markers); }

	    if (fit === true) { fitToMarkers(markers, map); }
	}
}


// SHOW ROSKILE MAP
// ------------------------------------------------------------

function initRoskildeMap() {
	xhr = $.ajax({
	    type: "POST",
	    url: "/php/feeds/facilitiesJSON.json"
	}).done(function(data) {
		facilties = data;
		console.log(data);

		initMap(null, festivalCoords, function() {
			console.log('geoData goes here');

			var $iframe	= $(document.getElementById('map-iframe'));

			if ($iframe) {
				$iframe.addClass('shift');
				$(document.getElementById('content')).prepend(templates.facilties);
				$(document.getElementById('compass')).addClass('shift');
			}
		});
	}).fail(function(error) { ajaxFail(error); });
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


// CUSTOM OVERLAYS
// ------------------------------------------------------------

function USGSOverlay(bounds, image, map) {

  // Now initialize all properties.
  this.bounds_ = bounds;
  this.image_ = image;
  this.map_ = map;

  // We define a property to hold the image's
  // div. We'll actually create this div
  // upon receipt of the add() method so we'll
  // leave it null for now.
  this.div_ = null;

  // Explicitly call setMap() on this overlay
  this.setMap(map);
}

USGSOverlay.prototype = new google.maps.OverlayView();

USGSOverlay.prototype.onAdd = function() {

	// Note: an overlay's receipt of onAdd() indicates that
	// the map's panes are now available for attaching
	// the overlay to the map via the DOM.

	var addImage = (svg) ? '' : ' image';

	// Create the DIV and set some basic attributes.
	var div = document.createElement('div');
	div.style.border = "none";
	div.style.borderWidth = "0px";
	div.style.position = "absolute";
	div.className = 'festival_map' + addImage;

	// Create an IMG element and attach it to the DIV. <object type="image/svg+xml"  width="100%" height="100%" data="test.svg"></object>
	if (svg === true) {
		var obj = document.createElement("object");
		obj.type ="image/svg+xml";
		obj.data = this.image_;
		obj.style.width = "100%";
		obj.style.height = "100%";
		div.appendChild(obj);
	}

	// Set the overlay's div_ property to this DIV
	this.div_ = div;

	// We add an overlay to a map via one of the map's panes.
	// We'll add this overlay to the overlayImage pane.
	var panes = this.getPanes();
	panes.overlayLayer.appendChild(div);
};

USGSOverlay.prototype.draw = function() {

  // Size and position the overlay. We use a southwest and northeast
  // position of the overlay to peg it to the correct position and size.
  // We need to retrieve the projection from this overlay to do this.
  var overlayProjection = this.getProjection();

  // Retrieve the southwest and northeast coordinates of this overlay
  // in latlngs and convert them to pixels coordinates.
  // We'll use these coordinates to resize the DIV.
  var sw = overlayProjection.fromLatLngToDivPixel(this.bounds_.getSouthWest());
  var ne = overlayProjection.fromLatLngToDivPixel(this.bounds_.getNorthEast());

  // Resize the image's DIV to fit the indicated dimensions.
  var div = this.div_;
  div.style.left = sw.x + 'px';
  div.style.top = ne.y + 'px';
  div.style.width = (ne.x - sw.x) + 'px';
  div.style.height = (sw.y - ne.y) + 'px';
}


// Clear markers
// ------------------------------------------------------------

function clearOverlays() {
  for (var i = 0, l = markers.length; i < l; i++ ) {
    markers[i].setMap(null);
  }
  markers = [];
}