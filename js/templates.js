var templates = {
    statusLoggedIn:     		'<div class="status">Welcome {{first_name}} {{last_name}} from {{#hometown}}{{name}}{{/hometown}}</div>' +
								'<div class="menu_button">{{> checkInButtonPartial}}</div>' +
								'<div class="menu_button">{{> findFriendsButtonPartial}}</div>' +
								'<div class="menu_button">{{> locationButtonPartial}}</div>' +
								'<div class="menu_button">{{> getLocationButtonPartial}}</div>' +
								'<div class="menu_button">{{> mapButtonPartial}}</div>' +
								'<div class="menu_button">{{> scheduleButtonPartial}}</div>' +
								'<div class="menu_button">{{> createEventPartial}}</div>' +
								'<div class="menu_button">{{> eventButtonPartial}}</div>',
								
	checkInButtonPartial:		'<button id="checkin">CHECK IN</button>',
	findFriendsButtonPartial:	'<button id="findFriends">FIND FRIENDS</button>',
	locationButtonPartial:		'<button id="remLocation">REMEMBER LOCATION</button>',
	getLocationButtonPartial:	'<button id="getLocation">LOCATIONS</button>',
	mapButtonPartial:			'<button id="map">MAP</button>',
	scheduleButtonPartial:		'<button id="schedule">SCHEDULE</button>',
	createEventPartial:			'<button id="createEvent">CREATE EVENT</button>',
	eventButtonPartial:			'<button id="getEvents">EVENTS</button>',
	
	mapCanvas:					'<div id="map-canvas" class="map_canvas"></div><div id="compass" class="compass"></div>',
	
	createEventOptions:			'<div id="createEventOptions" class="create_event_options">' +
									'<div><button id="createEventMe" class="create-event">USE MY LOCATION</button></div>' +
									'<div><span>... or tap a location</span><button id="createEventMarker" class="create-event">USE MARKED LOCATION</button></div>' +
								'</div>',

	marker:                     '<div class="roskilde_marker">' +
									'<div class="ros_marker">' +
										'<img src="{{{src}}}" height="25" width="25"/>' +
										'{{#details}}<span>{{details}}</span>{{/details}}' +
									'</div>' +
								'</div>',

	tooltip:					'<div class="ros_tooltip">' +
									'<img src="{{{src}}}" class="tt_img" height="25" width="25"/>' +
									'<div class="tt_cont">' +
										'<div class="tt_name">{{name}}</div>' +
										'<div class="tt_details">' +
											'{{#time}}<div class="tt_time">{{time}}</div>{{/time}}' +
											'{{#message}}<div class="tt_msg">{{message}}</div>{{/message}}' +
										'</div>' +
									'</div>' +
									'{{#schedule}}' +
										'{{> tooltipAddScheduleBtn}}' +
									'{{/schedule}}' +
								'</div>',


	tooltipAddScheduleBtn:		'<button class="add-to-schedule" ' +
									'data-name="{{name}}" data-location="Roskilde" data-latitude="{{latitude}}" data-longitude="{{longitude}}" {{#message}}data-description="{{message}}"{{/message}} data-start="{{start}}" data-end="{{end}}" data-type="event"' +
								'>Add to My Schedule</button>',


	artist_page:                '<div id="artist-page" class="page artist_page">' +
									'<div>' +
										'<h4>{{artistName}}</h4><span id="artist-close" class="artist_close" onclick="">&times;</span>' +
										'<div class="artist_details">' +
											'<img src="http://roskilde-festival.co.uk/{{{imageUrl}}}" height="112" width="112" /><br/>' +
											'{{country}}<br/>' +
											'{{{scene}}}<br/>' +
											'{{tidspunkt}}<br/>' +
											'<div>' +
												'<button class="add-to-schedule" onclick="alert(\"Added!\");" ' +
													'data-name="{{{artistName}}}" data-location="{{{scene}}}" data-description="{{{artistName}}} playing at {{{scene}}}" data-start="{{start}}" data-end="{{end}}" data-type="artist"' +
												'>Add to My Schedule</button>' +
											'</div>' +
										'</div>' +

										'<div class="artist_description">' +
											'{{{description}}}' +
										'</div>' +
									'</div>' +
								'</div>',


	create_location:			'<div id="createLocationPage" class="page event_page">' +
									'<form id="createLocationForm">' +
									'<h2>Remember a location</h2>' +
									'<div>' +
										'<strong>Name</strong>' +
										'<input id="title" type="text" name="name" required />' +
									'</div>' +
									'<div>' +
										'<strong>Description</strong>' +
										'<textarea id="message" name="description"></textarea>' +
									'</div>' +
									'<button type="submit">Place Location</button>' +
									'</form>' +
								'</div>',


	create_event:				'<div id="createEventPage" class="page event_page">' +
									'<form id="createEventForm">' +
									'<h2>Create an event</h2>' +
									'<div>' +
										'<strong>Name</strong>' +
										'<input type="text" name="name" required />' +
									'</div>' +
									'<div>' +
										'<strong>Description</strong>' +
										'<textarea name="description"></textarea>' +
									'</div>' +
									'{{#datetime}}' +
										'<div>' +
											'<strong>Start</strong>' +
											'<input type="datetime" name="start-time" class="event-date" required />' +
										'</div>' +
										'<div>' +
											'<strong>End</strong>' +
											'<input type="datetime" name="end-time" required />' +
										'</div>' +
									'{{/datetime}}' +
									'{{^datetime}}' +
										'<div>' +
											'<strong>Start</strong>' +
											'<select name="start-date" class="event-date" required>' +
												'{{> date_dropdown}}' +
											'</select>' +

											'<select name="start-time" class="event-date" required>' +
												'{{> time_dropdown}}' +
											'</select>' +
										'</div>' +
										'<div>' +
											'<strong>End</strong>' +
											'<select id="end-date" name="end-date" class="event_dropdown" required disabled>' +
												'{{> date_dropdown}}' +
											'</select>' +

											'<select id="end-time" name="end-time" class="event_dropdown" required disabled>' +
												'{{> time_dropdown}}' +
											'</select>' +
										'</div>' +
									'{{/datetime}}' +
									'<button type="submit">Place Location</button>' +
									'</form>' +
								'</div>',


	date_dropdown:			'<option disabled>Select a date</option>' +
								'<optgroup label="June">' +
									'<option value="24-6">Mon 24 June</option>' +
									'<option value="25-6">Tue 25 June</option>' +
									'<option value="26-6">Wed 26 June</option>' +
									'<option value="27-6">Thu 27 June</option>' +
									'<option value="28-6">Fri 28 June</option>' +
									'<option value="29-6">Sat 29 June</option>' +
									'<option value="30-6">Sun 30 June</option>' +
								'</optgroup>' +
								'<optgroup label="July">' +
									'<option value="01-7">Mon 1 July</option>' +
									'<option value="02-7">Tue 2 July</option>' +
									'<option value="03-7">Wed 3 July</option>' +
									'<option value="04-7">Thu 4 July</option>' +
									'<option value="05-7">Fri 5 July</option>' +
									'<option value="06-7">Sat 6 July</option>' +
									'<option value="07-7">Sun 7 July</option>' +
									'<option value="08-7">Mon 8 July</option>' +
									'<option value="09-7">Tue 9 July</option>' +
									'<option value="10-7">Wed 10 July</option>' +
									'<option value="11-7">Thu 11 July</option>' +
									'<option value="12-7">Fri 12 July</option>' +
									'<option value="13-7">Sat 13 July</option>' +
									'<option value="14-7">Sun 14 July</option>' +
								'</optgroup>',

	time_dropdown:				'<option disabled>Time</option>' +
								'<optgroup label="AM">' +
									'<option value="00:00">00:00</option>' +
									'<option value="00:30">00:30</option>' +
									'<option value="01:00">01:00</option>' +
									'<option value="01:30">01:30</option>' +
									'<option value="02:00">02:00</option>' +
									'<option value="02:30">02:30</option>' +
									'<option value="03:00">03:00</option>' +
									'<option value="03:30">03:30</option>' +
									'<option value="04:00">04:00</option>' +
									'<option value="04:30">04:30</option>' +
									'<option value="05:00">05:00</option>' +
									'<option value="05:30">05:30</option>' +
									'<option value="06:00">06:00</option>' +
									'<option value="06:30">06:30</option>' +
									'<option value="07:00">07:00</option>' +
									'<option value="07:30">07:30</option>' +
									'<option value="08:00">08:00</option>' +
									'<option value="08:30">08:30</option>' +
									'<option value="09:00">09:00</option>' +
									'<option value="09:30">09:30</option>' +
									'<option value="10:00">10:00</option>' +
									'<option value="10:30">10:30</option>' +
									'<option value="11:00">11:00</option>' +
									'<option value="11:30">11:30</option>' +
								'</optgroup>' +
								'<optgroup label="PM">' +
									'<option value="12:00">12:00</option>' +
									'<option value="12:30">12:30</option>' +
									'<option value="13:00">13:00</option>' +
									'<option value="13:30">13:30</option>' +
									'<option value="14:00">14:00</option>' +
									'<option value="14:30">14:30</option>' +
									'<option value="15:00">15:00</option>' +
									'<option value="15:30">15:30</option>' +
									'<option value="16:00">16:00</option>' +
									'<option value="16:30">16:30</option>' +
									'<option value="17:00">17:00</option>' +
									'<option value="17:30">17:30</option>' +
									'<option value="18:00">18:00</option>' +
									'<option value="18:30">18:30</option>' +
									'<option value="19:00">19:00</option>' +
									'<option value="19:30">19:30</option>' +
									'<option value="20:00">20:00</option>' +
									'<option value="20:30">20:30</option>' +
									'<option value="21:00">21:00</option>' +
									'<option value="21:30">21:30</option>' +
									'<option value="22:00">22:00</option>' +
									'<option value="22:30">22:30</option>' +
									'<option value="23:00">23:00</option>' +
									'<option value="23:30">23:30</option>' +
								'</optgroup>'


}