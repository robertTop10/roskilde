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
		$(document.getElementById('content')).append(mustache(templates.messageBox, {action: 'Location'}));
    });
	
    $(document).on("click", "#messageSubmitLocation", function(e){
        e.preventDefault();
		var $msg	= $(document.getElementById('message'));
		var msg 	= $msg.find('textarea').val();
		
		if (msg.length > 0) {
			rememberLocation(msg);
		}
		
		$msg.remove();
		
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
		console.log('DO BASIC DATE CHECKING',$(this));
		console.log($(document.getElementById('createEventForm')).serializeArray());
		if (checkDateTime()) {
			// Check dates
		} else {
			var startDate, startTime, endDate, endTime;

			$.each($(document.getElementById('createEventForm')).serializeArray(), function(i,v) {
				if (v.name === 'start-date') { startDate = v.value; }
				if (v.name === 'start-time') { startTime = v.value; }
			});

			if (startDate && startTime) {
				var $date 	= $(document.getElementById('end-date'));
				var $time	= $(document.getElementById('end-time'));

				var $html = $(templates.time_dropdown);
				console.log($html.find('option'));
				$html.find('option').each(function(i, v) {

				});

				console.log($html);

				$date.removeAttr('disabled');
				$time.removeAttr('disabled');
			}
			/*
			var start 	= [];
			var end 	= [];
			$.each($(document.getElementById('createEventForm')).serializeArray(), function(i,v) {
				if (v.name === 'start-date') {
					console.log(v.value);
					var val = v.value.split('-');
					start['date'] 	= val[0];
					start['month']	= val[1];
				} 

				if (v.name === 'start-time') {
					console.log(v.value);
					var val = v.value.split(':');
					start['hour'] 	= val[0];
					start['mins']	= val[1];							
				}
			});

			if (Object.keys(start).length === 4) {
				var startTimestamp = new Date(new Date().getFullYear(), (start['month'] - 1), start['date'], start['hour'], start['mins'], 0);
			}
			console.log(start, startTimestamp, end);
			*/
		}
	});
	
	$(document).on("submit", "#createEventForm", function(e) {
		e.preventDefault();
		
		var checkDate	= checkDateTime();
		var proceed		= true;
		var form		= $(this).serializeArray();
		var year		= new Date().getFullYear();
		var data		= {};

		$.each(form, function(i,v) {
			if (v.name === 'start-date' ||  v.name === 'end-date') {
				var alpha	= v.name.split('-')[0];
				var dates	= v.value.split('-');
				
				data[alpha + '-date'] = dates[0];
				data[alpha + '-month'] = dates[1];
			} else {
				data[v.name] = v.value;
			}

			if (proceed === true && v.name !== 'description' && v.value == false) {
				proceed = false;	
			}
		});
		console.log(proceed, data);
		
		var length	= (checkDate) ? 4 : 6;
		if (form.length !== length || proceed === false) {
			alert('Something seems to be missing');	
			return;
		} else {
			if (checkDate) {
				var startTime	= new Date(data['start-time']).getTime();
				var endTime		= new Date(data['end-time']).getTime();
			} else {
				var start	= data['start-time'].split(':');
				var end		= data['end-time'].split(':');
				
				var startTime	= new Date(year, (data['start-month'] - 1), data['start-date'], start[0], start[1], 0).getTime();
				var endTime		= new Date(year, (data['end-month'] - 1), data['end-date'], end[0], end[1], 0).getTime();
			}
			
			data.start	= startTime;
			data.end	= endTime;
			
			initCreateEventsMap(data);
		}
	});


	$(document).on("click", ".create-event", function(e){
		e.preventDefault();
		var $map	=	$(document.getElementById("map-canvas"));
		var latLon	=	($(this).attr('id') === 'createEventMe') ? $map.data('my-location') : $map.data('my-marker');
		var data	= $map.data('form');
		
		createEvent(latLon, data);
	});
	
	
	$(document).on("click", "#getEvents", function(e){
		e.preventDefault();				
		loading();
		getEvents();
	});

});