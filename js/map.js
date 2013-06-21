// GENERIC FUNCTIONS
// ------------------------------------------------------------

function initMap(data, fit, cb) {
	console.log('initMap', data);
	$content.html(templates.mapCanvas);

	if (typeof data === 'string') {data = JSON.parse(data); } // FF and jQuery not recognising a JSON response

	iframe = document.getElementById('map-iframe');

	$(iframe).load(function (){
		iframeDoc		= iframe.contentDocument || iframe.contentWindow.document;
		m				= iframeDoc.getElementById("map-canvas");

		navigator.geolocation.getCurrentPosition(function(coords) {
			console.log(999999, coords);
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

	if (pointerEvents) {
		overlay = new USGSOverlay(imageBounds, '/new-images/map.svg', map);
	} else {
		var festival = new google.maps.GroundOverlay("/new-images/map.gif", imageBounds, {clickable: false});
		festival.setMap(map);
	}
}


// INITMAP CALLBACK
// ------------------------------------------------------------

function gotLocation(data, fit, cb, coords, error) {
	console.log('gotLocation', data, coords);

	if (iframe) {
		var me			= new google.maps.LatLng(coords.coords.latitude, coords.coords.longitude);
		var center      = (typeof fit === 'object') ? new google.maps.LatLng(fit.coords.latitude, fit.coords.longitude) : me;

		var mapOptions	= {
			center: center,
			maxZoom: 18,
			zoom: 15,
			disableDefaultUI: true,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		};

		map = new google.maps.Map(m, mapOptions);

		google.maps.event.addListenerOnce(map, 'idle', function(){
			finishLoading();
		});

		setRoskildeMap(map);
		showCompass();

		markers = [];

		if (!error) {
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

		initMap(null, festivalCoords, function() {
			if (iframe) {
				google.maps.event.addListenerOnce(map, 'idle', function(){
					$(document.getElementById('facilties')).addClass('animate');
				});

				if (checkCalc) { $(iframe).addClass('shift'); }
				$(iframe).before(templates.facilties);
				$(document.getElementById('compass')).addClass('shift');

				if (markers[0] && markers[0]['title'] === 'Me') {
					markers[0].setMap(map);
				}

				var h = $(document.getElementById('test-height')).outerHeight();
				console.log(h);
				if (h !== 60) {
					$(document.getElementById('facilties-menu')).css('height', 60 + (60 - h));
				}
			}
		});
	}).fail(function(error) { ajaxFail(error); });
}


// MARKERS
// ------------------------------------------------------------

function populateMarker(data, coords, map, markers, cb) {
    var length	= data.result.length;
    var z		= length + 1;
    for (var i = 0; i < length; i++) {
        var d	= data.result[i];
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

USGSOverlay.prototype = new google.maps.OverlayView({
	clickable: false
});

USGSOverlay.prototype.onAdd = function() {

	// Note: an overlay's receipt of onAdd() indicates that
	// the map's panes are now available for attaching
	// the overlay to the map via the DOM.
	var addImage	= (svg) ? '' : ' image';

	// Create the DIV and set some basic attributes.
	var div = document.createElement('div');
	div.style.border = "none";
	div.style.borderWidth = "0px";
	div.style.position = "absolute";
	div.className = 'festival_map' + addImage;

	// Create an IMG element and attach it to the DIV. <object type="image/svg+xml"  width="100%" height="100%" data="test.svg"></object>
	if (svg) {
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
};


// Clear markers
// ------------------------------------------------------------

function clearOverlays() {
  for (var i = 0, l = markers.length; i < l; i++ ) {
    markers[i].setMap(null);
  }
  markers = [];
}

function initCluster() {
	markerCluster = new MarkerClusterer(map, markers, {
		maxZoom: 16,
		styles: [{
			backgroundSize: '100%',
			height: 45,
			textColor: 'white',
			width: 45,
			url: '/new-images/cluster.png'
		}]
	});
}


function assignTooltips() {
	$.each(markers, function(i, v) {
		google.maps.event.addListener(v, 'click', function() {
			if (openInfoWindow) { openInfoWindow.close(); }
			openInfoWindow = v.infowindow;
			v.infowindow.open(map, v);

			google.maps.event.addListener(v.infowindow, 'domready', function(e) {
				var $el = $(iframeDoc).find('button');

				$el.off('click');
				$el.on('click', function(e) {
					e.preventDefault();
					loading();
					if ($(e.target).hasClass('add-to-schedule')) { loading(); addToMySchedule(e); }
					else if ($(e.target).hasClass('remove-from-schedule')) { loading(); removeFromMySchedule(e); }
				});
			});

		});
	});
}