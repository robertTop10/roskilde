function iconMe(lat, lon, map) {
	var img = new google.maps.MarkerImage("/images/me.png", null, null, new google.maps.Point(5,5), new google.maps.Size(10,10));
	var obj = {
		position: 	new google.maps.LatLng(lat, lon),
		map: 		map,
		zIndex: 	100,
		title: 		'Me',
		icon: 		img
	}
	
	return new google.maps.Marker(obj);
}


function iconFriend(lat, lon, map, params) {
	var img 		= "http://graph.facebook.com/" + params.icon + "/picture";
	var diff 		= timeDifference(params.timestamp);

	var infowindow 	= createTooltip({src: img, name: params.title, time: diff});

	diff 			= timeDifference(params.timestamp, true);

	var obj = {
		position: 	new google.maps.LatLng(lat, lon),
		map: 		map,
		zIndex: 	params.zIndex,
		content: 	mustache(templates.marker, {src: img, details: diff}),
		flat: 		true
	}

	var marker = new RichMarker(obj);

	google.maps.event.addListener(marker, 'click', function() {
		if (openInfoWindow) { openInfoWindow.close(); };
		openInfoWindow = infowindow;
		infowindow.open(map, marker);
	});

	return marker;
}


function iconPin(lat, lon, map, params) {
	var obj = {
		position: 	new google.maps.LatLng(lat, lon),
		map: 		map,
		zIndex: 	params.zIndex,
		content: 	mustache(templates.marker, {src: params.icon}),
		flat: 		true
	}

	var marker = new RichMarker(obj);

	// TODO - Do tooltip stuff
	if (params.tooltip === true) {
		var infowindow 	= createTooltip({
			src: 		params.img, 
			name: 		params.title, 
			details: 	params.diff, 
			message: 	params.message,
			time: 		params.time
		});

		google.maps.event.addListener(marker, 'click', function() {
			if (openInfoWindow) { openInfoWindow.close(); };
			openInfoWindow = infowindow;
			infowindow.open(map, marker);
		});					
	}
	
	return marker;			
}
