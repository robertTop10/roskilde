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
	console.log(params);
	// TODO - Do tooltip stuff
	if (params.tooltip === true) {
		var infowindow	= createTooltip({
			src:		params.img,
			name:		params.title,
			details:	params.diff,
			message:	params.message,
			time:		params.time,
			schedule:	params.schedule
		});

		google.maps.event.addListener(marker, 'click', function() {
			if (openInfoWindow) { openInfoWindow.close(); }
			openInfoWindow = infowindow;
			infowindow.open(map, marker);

			setTimeout(function() {
				google.maps.event.addDomListener(document.getElementsByClassName('ros_tooltip')[0], 'click', function(e) {
					console.log('CLICK', e);
					if ($(e.target).hasClass('add-to-schedule')) { addToMySchedule(e); }
				});
			}, 50);
		});
	}

	return marker;
}
