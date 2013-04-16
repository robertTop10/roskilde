function initMap(data, fit, cb) {
	console.log('initMap', data);
	$(document.getElementById('content')).html(templates.mapCanvas);
	
	if (typeof data === 'string') {data = JSON.parse(data) }; // FF and jQuery not recognising a JSON response
	
	navigator.geolocation.getCurrentPosition(function(coords) {
        gotLocation(data, fit, cb, coords);					
	}, function(error) {
	    gotLocation(data, fit, cb, festivalCoords, true);
	}, {timeout: 8000});				
}


function setRoskildeMap(map) {
	var se	=	new google.maps.LatLng(55.608, 12.0542);
	var nw	=	new google.maps.LatLng(55.62535, 12.10675);
	var imageBounds = new google.maps.LatLngBounds(se, nw);
	var festival = new google.maps.GroundOverlay("http://r.oskil.de/images/map.gif", imageBounds, {clickable: false});
	festival.setMap(map);	
}