$(document).ready(function() {
    window.scrollTo(1,0);
    loading();


    new FastClick(document.body);

    var $sectionTitle = $(document.getElementById('section-title'));


	$(document).on("click", "#home-button", function(e){
		console.log('fastClick');
        e.preventDefault();

        changeTitle();

		removeCompass();
		mainMenu();

		pushState(null, document.title, '/', true);
	});

	$(document).on("click", "#user-avatar", function(e) {
		e.preventDefault();
		e.stopPropagation();
		var lang = (danish) ? 'English' : 'Danish';
		var r = confirm('Change language to ' + lang + '?');
		if (r === true) {
			danish = (danish) ? false : true;

			var lng = (danish) ? 'dk' : 'en';
			$(this).attr('class', lng);

			setLocalStorage('danish', danish);
			document.cookie = "roskildedanish=" + danish;

			removeCompass();
			mainMenu();

			// Delete stored artists as they're not in the wrong language
			artists = null;
			schedule = null;

			pushState(null, document.title, '/', true);
		}
	});


    $(document).on("click", "#checkin", function(e){
        e.preventDefault();
		loading();
		navigator.geolocation.getCurrentPosition(getPosition, noPosition, {timeout: 8000});
    });


    $(document).on("click", "#findFriends", function(e) {
        e.preventDefault();
		loading();
		findFriends();

		pushState(null, document.title, '/find-friends');
    });


    $(document).on("click", "#remLocation", function(e){
        e.preventDefault();

        changeTitle('remLocation');

        $(document.getElementById('content')).html(mustache(templates.create_location));
        pushState(null, document.title, '/remember-location');
    });


	$(document).on("submit", "#createLocationForm", function(e) {
		e.preventDefault();
		var data = {
			action: 'createLocation',
			msg:	$(document.getElementById('message')).val(),
			title:	$(document.getElementById('title')).val()
		};

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

		pushState(null, document.title, '/my-locations');
    });


	$(document).on("click", "#map", function(e){
		e.preventDefault();
		loading();
		initRoskildeMap();
		pushState(null, document.title, '/festival-map');

		changeTitle('map');
	});


	$(document).on("click", "#schedule", function(e){
		e.preventDefault();

		getSchedule();
		pushState(null, document.title, '/festival-schedule');
	});


	$(document).on("click", ".band", function(e){
		e.preventDefault();

		var id     = $(this).data('artist').split('-');
		var artist = schedule.results[id[0]][id[1]][id[2]];
		console.log(artist);
		artist.start = artist.original_timestamp * 1000;
		artist.end   = artist.start + 3600000;

		var daysShort	= ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
		var monthNames 	= [ "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec" ];
		var nth 		= ['st', 'nd', 'rd', 'th'];

		var d 			= new Date(artist.start);

		var digit		= 	d.getDate().toString().slice(-1);
		var day 		= 	(digit > 0 && digit <= 3) ? nth[digit - 1] : nth[3];

		artist.formattedStartTime 	= d.getHours().pad() + ':' + d.getMinutes().pad();
		artist.formattedStartDate 	= daysShort[d.getDay()] + ', ' + d.getDate() + day + ' ' + monthNames[d.getMonth()];

		var data	= JSON.parse(localStorage.getItem('mySchedule'));
		var artId	= parseInt(artist['@id'], 10);

		if (data !== null) {
			for (i = 0, len = data.length; i < len; i++) {
				if (data[i].id === artId && data[i].type === 'artist') {
					artist.subscribed = true;
					break;
				}
			}
		}

		$(document.getElementById('hide-content')).hide();
		$(document.getElementById('content')).append(mustache(templates.artist_page, artist));
	});


	$(document).on("click", ".artist", function(e){
		e.preventDefault();

		contentScrollTop = $(document.getElementById('artists-scroller')).scrollTop();

		var id     = parseFloat($(this).data('artist'));
		var artist = artists.artists[id];

		artist.start = artist.original_timestamp * 1000;
		artist.end   = artist.start + 3600000;

		var daysShort	= ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
		var monthNames 	= [ "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec" ];
		var nth 		= ['st', 'nd', 'rd', 'th'];

		var d 			= new Date(artist.start);

		var digit		= 	d.getDate().toString().slice(-1);
		var day 		= 	(digit > 0 && digit <= 3) ? nth[digit - 1] : nth[3];

		artist.formattedStartTime 	= d.getHours().pad() + ':' + d.getMinutes().pad();
		artist.formattedStartDate 	= daysShort[d.getDay()] + ', ' + d.getDate() + day + ' ' + monthNames[d.getMonth()];

		var data	= JSON.parse(localStorage.getItem('mySchedule'));
		var artId	= id;

		if (data !== null) {
			for (i = 0, len = data.length; i < len; i++) {
				if (data[i].id === artId && data[i].type === 'artist') {
					artist.subscribed = true;
					break;
				}
			}
		}

		$(document.getElementById('hide-content')).hide();
		$(document.getElementById('content')).append(mustache(templates.artist_page, artist));
	});


	$(document).on("click", "#artist-close", function(e){
		e.preventDefault();
		$(document.getElementById('hide-content')).show();
		$(document.getElementById('artist-page')).remove();

		if (!isNaN(contentScrollTop)) {
			var $content = $(document.getElementById('content'));
			$content.find('.status').show();
			$(document.getElementById('artists-scroller')).scrollTop(contentScrollTop);
			contentScrollTop = null;
		}
	});


	$(document).on("click", "#createEvent", function(e){
		e.preventDefault();
		$(document.getElementById('content')).html(mustache(templates.create_event, {datetime: checkDateTime()}));

		changeTitle('createEvent');

		pushState(null, document.title, '/create-event');
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

		var iframe	= document.getElementById('map-iframe');
		iframe		= iframe.contentDocument || iframe.contentWindow.document;

		var $map	=	$(iframe.getElementById("map-canvas"));

		if ($(this).attr('id') === 'createEventMe') {
			lat = $map.data('my-location-latitude');
			lon = $map.data('my-location-longitude');
			acc = $map.data('my-location-accuracy');
		} else {
			lat = $map.data('my-marker-latitude');
			lon = $map.data('my-marker-longitude');
			acc = -1;
		}

		var data	=	$map.data('form');

		if (data.action === 'createEvent') {
			createEvent(lat, lon, acc, data);
		} else if (data.action === 'createLocation') {
			createLocation(lat, lon, acc, data);
		}
	});


	$(document).on("click", "#getEvents", function(e){
		e.preventDefault();
		loading();
		getEvents();

		pushState(null, document.title, '/events');
	});


	$(document).on("click", "#getMySchedule", function(e){
		e.preventDefault();
		loading();
		getMySchedule();

		pushState(null, document.title, '/my-schedule');
		changeTitle('getMySchedule');
	});


	$(document).on("click", '.add-to-schedule', function(e) {
		e.preventDefault();
		loading();
		addToMySchedule(e);
	});


	$(document).on("click", '.remove-from-schedule', function(e) {
		e.preventDefault();
		loading();
		removeFromMySchedule(e);
	});


	$(document).on("click", "#getArtists", function(e) {
		e.preventDefault();
		loading();
		getArtists();

		pushState(null, document.title, '/artists');
	});

	if ('ontouchstart' in window) {
		$(document).on("touchmove", ".quickfind", function(e) {
			e.preventDefault();

			var el = (e.originalEvent.targetTouches) ? document.elementFromPoint(e.originalEvent.targetTouches[0].screenX, e.originalEvent.targetTouches[0].screenY) : document.elementFromPoint(e.originalEvent.pageX, e.originalEvent.pageY);

			if (el.id) {
				if (el.id.search('link-') === 0) {
					var letter	= el.id.substr(el.id.length - 1);
					var top		= document.getElementById('artist-letter-' + letter);
					if (top) {
						$(document.getElementById('artists-scroller')).scrollTop(top.offsetTop);
					}
				}
			}
		});
	} else {
		$(document).on("click", ".quickfind li", function(e) {
			var letter	= e.currentTarget.id.substr(e.currentTarget.id.length - 1);
			var top		= document.getElementById('artist-letter-' + letter);
			if (top) {
				$(document.getElementById('artists-scroller')).scrollTop(top.offsetTop);
			}
		});
	}


    $(document).on("click", "#moreStuff", function(e) {
        e.preventDefault();
		$(document.getElementById('content')).html(mustache(templates.moreThings));
    });


    $(document).on("click", "#getTweets", function(e) {
        e.preventDefault();
        loading();
		getTweets();
		pushState(null, document.title, '/tweets');
    });


    $(document).on("click", "#getNews", function(e) {
        e.preventDefault();
        loading();
		getNews();
		pushState(null, document.title, '/news');
    });

    $(document).on("click", "#backupSchedule", function(e) {
    	console.log('backup');
        e.preventDefault();
        loading();
        backupSchedule();
    });

    $(document).on("click", "#restoreSchedule", function(e) {
        e.preventDefault();
        var r = confirm('Are you sure you want to restore your schedule?');
		if (r === true) {
			loading();
			restoreSchedule();
		}
    });

});