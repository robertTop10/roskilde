$(document).ready(function() {
    window.scrollTo(1,0);
    loading();
	
    $(document).on("click", "#checkin", function(e){
        e.preventDefault();
		loading();
		navigator.geolocation.getCurrentPosition(getPosition, noPosition, {timeout: 8000});
    });
	
    $(document).on("click", "#findFriends", function(e){
        e.preventDefault();
		loading();
		findFriends();
    });
	
	$(document).on("click", "#menu", function(e){
        e.preventDefault();
        loading();
        
		removeCompass();
		loggedIn();
	});

    $(document).on("click", "#remLocation", function(e){
        e.preventDefault();
        $(document.getElementById('content')).html(mustache(templates.create_location));
    });

	$(document).on("submit", "#createLocationForm", function(e) {
		e.preventDefault();
		var data = {
			action: 'createLocation',
			msg: 	$(document.getElementById('message')).val(),
			title: 	$(document.getElementById('title')).val()	
		}

		if (data.title.length > 0) {
			initCreateEventsMap(data);
			//rememberLocation(title, msg);
		} else {
			alert('Enter a title for this location.');
		}
	});


    $(document).on("click", "#getLocation", function(e){
        e.preventDefault();
		loading();
		getLocation();
    });
	
	$(document).on("click", "#map", function(e){
		e.preventDefault();
		loading();
		initRoskildeMap();
	});

	$(document).on("click", "#schedule", function(e){
		e.preventDefault();
		getSchedule();
	});
	
	$(document).on("click", ".band", function(e){
		e.preventDefault();
		
		var id     = $(this).data('artist').split('-');
		var artist = schedule.results[id[0]][id[1]][id[2]];
		
		$(document.getElementById('content')).append(mustache(templates.artist_page, artist));

	});
	
	$(document).on("click", "#artist-close", function(e){
		e.preventDefault();
		$('#artist-page').remove();
	});

	$(document).on("click", "#createEvent", function(e){
		e.preventDefault();
		$(document.getElementById('content')).html(mustache(templates.create_event, {datetime: checkDateTime()}));
	});

	$(document).on("change", ".event-date", function(e){
		e.preventDefault();

		if (checkDateTime()) {
			// Check dates for dateinput
			if ($(this).attr('name') === 'start-time') {
				var $end		= $('input[name="end-time"]');
				var startVal	= $(this).val();
				var endVal		= $end.val();

				$end.attr('min', startVal);
				if (isNaN(new Date(endVal).getTime()) || new Date(startVal).getTime() > new Date(endVal).getTime()) {
					$end.val(startVal);
				}
			}
		} else {
			var startDate, startTime, endDate, endTime;

			$.each($(document.getElementById('createEventForm')).serializeArray(), function(i,v) {
				if (v.name === 'start-date') { startDate = v.value; }
				if (v.name === 'start-time') { startTime = v.value; }
			});

			if (startDate && startTime) {
				var $date		= $(document.getElementById('end-date'));
				var $time		= $(document.getElementById('end-time'));

				var $html		= $(templates.date_dropdown);
				startDate		= startDate.split('-');

				var endDateVal = $date.val();

				$html.find('option').each(function(i, v) {
					var val = $(this).val().split('-');
					if (parseInt(val[1], 10) < parseInt(startDate[1], 10)) {
						$(this).remove();
					} else if (parseInt(val[0], 10) < parseInt(startDate[0], 10) && parseInt(val[1], 10) <= parseInt(startDate[1], 10)) {
						$(this).remove();
					} else if (endDateVal === val[0] + '-' + val[1]) {
						$(this).attr('selected', 'selected');
					}
				});

				$date.html($html).removeAttr('disabled');
				$time.removeAttr('disabled');

				if ($(this).attr('name') === 'start-time') {
					var val = $(this).val();
					$time.val(val);
				}
			}
		}
	});

	$(document).on("submit", "#createEventForm", function(e) {
		e.preventDefault();

		var checkDate	= checkDateTime();
		var proceed		= true;
		var form		= $(this).serializeArray();
		var year		= new Date().getFullYear();
		var data		= {
			action: 'createEvent'
		};

		$.each(form, function(i,v) {
			if (v.name === 'start-date' ||  v.name === 'end-date') {
				var alpha	= v.name.split('-')[0];
				var dates	= v.value.split('-');

				data[alpha + '-date'] = dates[0];
				data[alpha + '-month'] = dates[1];
			} else {
				data[v.name] = v.value;
			}

			if (proceed === true && v.name !== 'description' && v.value === false) {
				proceed = false;
			}
		});

		var length	= (checkDate) ? 4 : 6;
		if (form.length !== length || proceed === false) {
			alert('Something seems to be missing.');
			return;
		} else {
			var startTime, endTime;

			if (checkDate) {
				startTime	= new Date(data['start-time']).getTime();
				endTime		= new Date(data['end-time']).getTime();
			} else {
				var start	= data['start-time'].split(':');
				var end		= data['end-time'].split(':');

				startTime	= new Date(year, (data['start-month'] - 1), data['start-date'], start[0], start[1], 0).getTime();
				endTime		= new Date(year, (data['end-month'] - 1), data['end-date'], end[0], end[1], 0).getTime();
			}

			data.start	= startTime;
			data.end	= endTime;

			if (startTime >= endTime) {
				alert('An event start time can\'t be before the end time.');
			} else {
				initCreateEventsMap(data);
			}
		}
	});


	$(document).on("click", ".create-event", function(e){
		e.preventDefault();

		var lat, lon, acc;
		var $map	=	$(document.getElementById("map-canvas"));

		if ($(this).attr('id') === 'createEventMe') {
			lat = $map.data('my-location-latitude');
			lon = $map.data('my-location-longitude');
			acc = $map.data('my-location-accuracy');
		} else {
			lat = $map.data('my-marker-latitude');
			lon = $map.data('my-marker-longitude');
			acc = -1;
		}

		var data	= 	$map.data('form');
		
		if (data.action === 'createEvent') {
			createEvent(lat, lon, acc, data);
		} else if (data.action === 'createLocation') {
			createLocation(lat, lon, acc, data)
		}
	});
	
	
	$(document).on("click", "#getEvents", function(e){
		e.preventDefault();				
		loading();
		getEvents();
	});

});