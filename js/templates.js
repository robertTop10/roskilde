var templates = {
    statusLoggedIn:				'<div class="scroller">' +
    								'<div class="main_menu_logo"></div>' +
    								'<div class="menu_row red">' +
										'<div class="menu_button">{{> getMySchedulePartial}}</div>' +
										'<div class="menu_button">{{> scheduleButtonPartial}}</div>' +
										'<div class="menu_button">{{> checkInButtonPartial}}</div>' +
										'<div class="menu_button">{{> findFriendsButtonPartial}}</div>' +
									'</div>' +
    								'<div class="menu_row">' +
	    								'<div class="menu_button">{{> locationButtonPartial}}</div>' +
										'<div class="menu_button">{{> getLocationButtonPartial}}</div>' +
										'<div class="menu_button">{{> createEventPartial}}</div>' +
										'<div class="menu_button">{{> eventButtonPartial}}</div>' +
									'</div>' +
    								'<div class="menu_row black">' +
	    								'<div class="menu_button">{{> mapButtonPartial}}</div>' +
										'<div class="menu_button">{{> getArtistsPartial}}</div>' +
										'<div class="menu_button">{{> getNewsFeedPartial}}</div>' +
										'<div class="menu_button">{{> getTwitterFeedPartial}}</div>' +
									'</div>' +
								'</div>',

	checkInButtonPartial:		'<button id="checkin">{{#danish}}TJEK IND{{/danish}}{{^danish}}CHECK IN{{/danish}}</button>',
	findFriendsButtonPartial:	'<button id="findFriends">{{#danish}}FIND MINE VENNER{{/danish}}{{^danish}}FIND MY FRIENDS{{/danish}}</button>',
	locationButtonPartial:		'<button id="remLocation">{{#danish}}HUSK STED{{/danish}}{{^danish}}REMEMBER LOCATION{{/danish}}</button>',
	getLocationButtonPartial:	'<button id="getLocation">{{#danish}}MINE STEDER{{/danish}}{{^danish}}MY LOCATIONS{{/danish}}</button>',
	mapButtonPartial:			'<button id="map">{{#danish}}FESTIVAL KORT{{/danish}}{{^danish}}FESTIVAL MAP{{/danish}}</button>',
	scheduleButtonPartial:		'<button id="schedule">{{#danish}}FESTIVAL TIDSPLAN{{/danish}}{{^danish}}FESTIVAL SCHEDULE{{/danish}}</button>',
	createEventPartial:			'<button id="createEvent">{{#danish}}SKAB BEGIVENHED{{/danish}}{{^danish}}CREATE EVENT{{/danish}}</button>',
	eventButtonPartial:			'<button id="getEvents">{{#danish}}ARRANGEMENTER{{/danish}}{{^danish}}EVENTS{{/danish}}</button>',
	getMySchedulePartial:		'<button id="getMySchedule">{{#danish}}MIN TIDSPLAN{{/danish}}{{^danish}}MY SCHEDULE{{/danish}}</button>',
	getArtistsPartial:			'<button id="getArtists">{{#danish}}KUNSTNERE{{/danish}}{{^danish}}ARTISTS{{/danish}}</button>',
	morePartial:				'<button id="moreStuff">{{#danish}}MERE{{/danish}}{{^danish}}MORE{{/danish}}</button>',

	getNewsFeedPartial: 		'<button id="getNews">{{#danish}}NYHEDER{{/danish}}{{^danish}}NEWS{{/danish}}</button>',
	getTwitterFeedPartial: 		'<button id="getTweets">TWITTER</button>',

	userAvatarImg: 				'<img src="https://graph.facebook.com/{{id}}/picture?width=80&height=80" height="40" width="40" />',

	mapCanvas:					'<iframe id="map-iframe" src="/html/frame.html" class="map_canvas" height="100%" width="100%" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"></iframe>' +
								'<div id="compass" class="compass"></div>',

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
											'{{#sTime}}<div class="tt_time">{{sTime}}</div>{{/sTime}}' +
											'{{#eTime}}<div class="tt_time">{{eTime}}</div>{{/eTime}}' +
											'{{#time}}<div class="tt_time">{{time}}</div>{{/time}}' +
											'{{#message}}<div class="tt_msg">{{message}}</div>{{/message}}' +
										'</div>' +
									'</div>' +
									'{{#schedule}}' +
										'{{#subscribed}}' +
											'{{> tooltipRemoveScheduleBtn}}' +
										'{{/subscribed}}' +
										'{{^subscribed}}' +
											'{{> tooltipAddScheduleBtn}}' +
										'{{/subscribed}}' +
									'{{/schedule}}' +
								'</div>',


	tooltipAddScheduleBtn:		'<button class="add-to-schedule" ' +
									'data-id="{{id}}" data-name="{{name}}" data-location="Roskilde" data-latitude="{{latitude}}" data-longitude="{{longitude}}" {{#message}}data-description="{{message}}"{{/message}} data-start="{{start}}" data-end="{{end}}" data-fstart="{{fstart}}" data-fend="{{fend}}" data-type="event"' +
								'>{{#danish}}Tilføj til mit skema{{/danish}}{{^danish}}Add to My Schedule{{/danish}}</button>',

	tooltipRemoveScheduleBtn:	'<button class="remove-from-schedule" ' +
									'data-id="{{id}}" data-name="{{name}}" data-location="Roskilde" data-latitude="{{latitude}}" data-longitude="{{longitude}}" {{#message}}data-description="{{message}}"{{/message}} data-start="{{start}}" data-end="{{end}}" data-fstart="{{fstart}}" data-fend="{{fend}}" data-type="event"' +
								'>{{#danish}}Fjern fra mit skema{{/danish}}{{^danish}}Remove from My Schedule{{/danish}}</button>',


	artist_page:                '<div id="artist-page" class="page artist_page">' +
										'<h4>{{artistName}}<small>/{{country}}</small></h4>' +
										'<span id="artist-close" class="artist_close">&times;</span>' +
										'<div class="artist_overview">' +
											'<div class="artist_frame">' +
												'<img src="http://roskilde-festival.co.uk/{{{imageUrl}}}" height="112" width="112" />' +
											'</div>' +
											'<div class="artist_details">' +
												'<h6>{{formattedStartTime}} - {{formattedStartDate}}</h6>' +
												'<h6>{{{scene}}}</h6>' +
											'</div>' +
										'</div>' +
										'<div>' +
											'{{#subscribed}}' +
												'<button class="remove-from-schedule" ' +
													'data-id="{{@id}}" data-name="{{{artistName}}}" data-location="{{{scene}}}" data-description="{{{scene}}}" data-start="{{start}}" data-end="{{end}}" data-type="artist"' +
												'>{{#danish}}Fjern fra mit skema{{/danish}}{{^danish}}Remove from My Schedule{{/danish}}</button>' +
											'{{/subscribed}}' +
											'{{^subscribed}}' +
												'<button class="add-to-schedule" ' +
													'data-id="{{@id}}" data-image="http://roskilde-festival.co.uk/{{{mediumimageUrl}}}" data-name="{{{artistName}}}" data-location="{{{scene}}}" data-description="{{{scene}}}" data-start="{{start}}" data-end="{{end}}" data-type="artist"' +
												'>{{#danish}}Tilføj til mit skema{{/danish}}{{^danish}}Add to My Schedule{{/danish}}</button>' +
											'{{/subscribed}}' +
											'{{#external}}' +
												'{{#spotify}}' +
													'<a href="spotify:artist:{{spotify}}" class="button spotify_button">Listen in Spotify</a>' +
												'{{/spotify}}' +
												'{{#facebook}}' +
													'<a href="http://facebook.com/{{facebook}}" class="button fb_button" target="_blank">View on Facebook</a>' +
												'{{/facebook}}' +
											'{{/external}}' + 
										'</div>' +

										'<div class="artist_description text_bg">' +
											'{{#description}}' +
												'{{#danish}}{{{dk}}}{{/danish}}' +
												'{{^danish}}{{{uk}}}{{/danish}}' +
											'{{/description}}' +
											'{{#related_count}}<h6>Related Artists</h6>{{/related_count}}' +
											'{{#related}}' +
												'<a href="#" class="js-artist" data-artist="{{rf_id}}"">{{name}}</a><br/>' +
											'{{/related}}' +
										'</div>' +
								'</div>',


	create_location:			'<div class="scroller">' +
									'<div id="createLocationPage" class="page event_page form_page">' +
										'<form id="createLocationForm">' +
										'<div>' +
											'<strong>Name</strong>' +
											'<div class="form_background">' +
												'<input id="title" type="text" name="name" autocomplete="off" required />' +
											'</div>' +
										'</div>' +
										'<div>' +
											'<strong>Description</strong>' +
											'<div class="form_background">' +
												'<textarea id="message" name="description"></textarea>' +
											'</div>' +
										'</div>' +
										'<button type="submit">Place Location</button>' +
										'</form>' +
									'</div>' +
								'</div>',


	create_event:				'<div class="scroller">' +
									'<div id="createEventPage" class="page event_page form_page">' +
										'<form id="createEventForm">' +
										'<div>' +
											'<strong>Name</strong>' +
											'<div class="form_background">' +
												'<input type="text" name="name" required />' +
											'</div>' +
										'</div>' +
										'<div>' +
											'<strong>Description</strong>' +
											'<div class="form_background">' +
												'<textarea name="description"></textarea>' +
											'</div>' +
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
									'</div>' +
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
								'</optgroup>',

	mySchedule:					'<div class="scroller">' +
									'<div class="status">' +
										'{{#results}}' +
											'<div class="my_event_cont">' +
												'<h2>{{name}}</h2>' +
												'<div class="artist_overview">' +
													'<div class="artist_frame_small">' +
														'{{#image}}<img src="{{{image}}}" height="56" width="56" />{{/image}}' +
														'{{^image}}<img src="/new-images/schedule-logo.png" height="56" width="56" />{{/image}}' +
													'</div>' +
													'<div class="artist_details_small">' +
														'<h6>' +
															'{{formattedStart}}' +
															'{{#formattedEnd}}<span class="my_event_end"> / {{formattedEnd}}</span>{{/formattedEnd}}' +
														'</h6>' +
														'<h6>{{description}}</h6>' +
													'</div>' +
												'</div>' +
												'<div class="my_event_cal">' +
													'<a href="/php/ics.php?startTime={{start}}&endTime={{end}}&subject={{name}}&desc={{description}}">{{#danish}}Tilføj til kalender{{/danish}}{{^danish}}Add to your Calendar{{/danish}}</a>' +
												'</div>' +
											'</div>' +
										'{{/results}}' +
										'{{#length}}' +
											'{{> backupPartial}}' +
										'{{/length}}' +
										'{{^results}}' +
											'<div class="text_bg">' +
												'<p>You haven\'t added any events to your schedule</p>' +
												'<p><strong>Creating your own schedule</strong></p>' +
												'<p>You can create your own personalised schedule by adding events and artists to it.</p>' +
												'<p>On the "Events" or an "Artist" page ("<a id="schedule" href="#">Festival Schedule</a>" or "<a id="getArtists" href="#">Artists</a>" > Click on a band you\'re interested in) and click "Add to My Schedule"</p>' +
												'<p>Viola, all the events and artists you\'ve added will appear here, making it easy to keep track of what you want to do at Roskilde.</p>' +
											'</div>' +
										'{{/results}}' +
										'{{^length}}' +
											'{{#restore}}{{> backupPartial}}{{/restore}}' +
										'{{/length}}' +
									'</div>' +
								'</div>',


	backupPartial:				'<div id="cloud-schedule">' +
									'<hr/>' +
									'<div class="text_bg">' +
										'<p>To save battery life, we store your schedule on your phone.</p>' +
										'<p>You can backup your schedule online, so you can view it on multiple devices or if you need to clear your browser\'s data.</p>' +
									'</div>' +
									'{{#length}}<p><button id="backupSchedule">BACKUP MY SCHEDULE</button></p>{{/length}}' +
									'{{#restore}}{{> restoreButton}}{{/restore}}' +
								'</div>',

	restoreButton:				'<button id="restoreSchedule">RESTORE MY SCHEDULE</button>',


	listArtists:				'<div id="hide-content">' +
									'<div class="rel">' +
										'<div id="artists-scroller" class="scroller needsclick">' +
											'<div class="status needsclick">' +
												'{{#artists}}' +
													'{{#header}}' +
														'<div id="artist-letter-{{header}}" class="artist_header">{{header}}</div>' +
													'{{/header}}' +
													'{{^header}}' +
														'<div class="js-artist dark_box needsclick" data-artist="{{@id}}">' +
															'<img src="http://roskilde-festival.co.uk/{{{mediumimageUrl}}}" height="56" width="56" />' +
															'<h4>{{{artistName}}} <small>/{{country}}</small></h4>' +
															'<h6><small>{{text}}</small></h6>' +
														'</div>' +
													'{{/header}}' +
												'{{/artists}}' +
											'</div>' +
										'</div>' +
										'<div class="quickfind">' +
											'<ol>' +
												'<li id="link-a" data-letter="a"></li>' +
												'<li id="link-b" data-letter="b"></li>' +
												'<li id="link-c" data-letter="c"></li>' +
												'<li id="link-d" data-letter="d"></li>' +
												'<li id="link-e" data-letter="e"></li>' +
												'<li id="link-f" data-letter="f"></li>' +
												'<li id="link-g" data-letter="g"></li>' +
												'<li id="link-h" data-letter="h"></li>' +
												'<li id="link-i" data-letter="i"></li>' +
												'<li id="link-j" data-letter="j"></li>' +
												'<li id="link-k" data-letter="k"></li>' +
												'<li id="link-l" data-letter="l"></li>' +
												'<li id="link-m" data-letter="m"></li>' +
												'<li id="link-n" data-letter="n"></li>' +
												'<li id="link-o" data-letter="o"></li>' +
												'<li id="link-p" data-letter="p"></li>' +
												'<li id="link-q" data-letter="q"></li>' +
												'<li id="link-r" data-letter="r"></li>' +
												'<li id="link-s" data-letter="s"></li>' +
												'<li id="link-t" data-letter="t"></li>' +
												'<li id="link-u" data-letter="u"></li>' +
												'<li id="link-v" data-letter="v"></li>' +
												'<li id="link-w" data-letter="w"></li>' +
												'<li id="link-x" data-letter="x"></li>' +
												'<li id="link-y" data-letter="y"></li>' +
												'<li id="link-z" data-letter="z"></li>' +
											'</ol>' +
										'</div>' +
									'</div>' +
								'</div>',

	moreThings:					'<div class="scroller">' +
									'<div class="second_page">' +
										'<div class="menu_button">{{> getNewsFeedPartial}}</div>' +
										'<div class="menu_button">{{> getTwitterFeedPartial}}</div>' +
									'</div>' +
								'</div>',


	news:						'<div class="scroller">' +
									'<div class="status">' +
										'{{#news}}' +
											'<div class="dark_box news">' +
												'<h2>{{title}}</h2>' +
												'{{#image}}<img src="{{image}}" height="61" width="61"/>{{/image}}' +
												'{{{description}}}' +
											'</div>' +
										'{{/news}}' +
									'</div>' +
								'</div>',


	tweets:						'<div class="scroller">' +
									'<div class="status">' +
										'{{#tweets}}' +
											'<div class="dark_box tweet">' +
												'<a href="http://twitter.com/{{from_user}}/status/{{id_str}}" target="_blank">' +
													'{{#img}}<img src="{{img}}" height="36" width="36"/>{{/img}}' +
													'<h3>{{from_user_name}}</h3>' +
													'{{{html}}}' +
												'</a>' +
											'</div>' +
										'{{/tweets}}' +
									'</div>' +
								'</div>'

}