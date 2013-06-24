function getSchedule() {
	loading();

	//var d = ['Sun, 01 Jul 2012 12:00:00 +0200', 'Mon, 02 Jul 2012 12:00:00 +0200', 'Tue, 03 Jul 2012 12:00:00 +0200', 'Wed, 04 Jul 2012 12:00:00 +0200', 'Thu, 05 Jul 2012 12:00:00 +0200', 'Fri, 06 Jul 2012 12:00:00 +0200', 'Sat, 07 Jul 2012 12:00:00 +0200', 'Sun, 08 Jul 2012 12:00:00 +0200'];
	var d = ['Sun, 30 Jun 2013 12:00:00 +0200', 'Mon, 01 Jul 2013 12:00:00 +0200', 'Tue, 02 Jul 2013 12:00:00 +0200', 'Wed, 03 Jul 2013 12:00:00 +0200', 'Thu, 04 Jul 2013 12:00:00 +0200', 'Fri, 05 Jul 2013 12:00:00 +0200', 'Sat, 06 Jul 2013 12:00:00 +0200', 'Sun, 07 Jul 2013 12:00:00 +0200'];
	var dates = [];
	var stages = [];

	$.each(d, function(i,v) {
		dates.push(new Date(v).getTime() / 1000);
	});


	if (schedule && schedule.stages) {
		processDates(schedule, dates, stages);
	} else {
		//xhr = $.getJSON('/php/feeds/allJSON.json', function(data) {
			//schedule = data;
			//processDates(data, dates, stages);
		//});

		getAllJSON(function(data) {
			schedule = data;
			processDates(data, dates, stages);
		});
	}
}


function processDates(data, dates, stages) {
    var days         = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    var daysShort    = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    var nth          = ['st', 'nd', 'rd', 'th'];
    var widths       = [];

	stages = data.stages;

	var i = 0;

	var date = new Date(dates[i] * 1000).getDate();
	var html =	'<div id="hide-content">' +
				'<div id="stages" class="stages">' +
				'<div class="stage_name stage_header">' +
				'<div id="date">' +
					date + ((date - 1 > 3) ? nth[3] : nth[date - 1]) +
					'<span class="block">' + daysShort[new Date(dates[i] * 1000).getDay()] + '</span>' +
				'</div></div>';

	$.each(data.stages, function(i,v) {
		html += '<div class="stage_name stage_' + v.toLowerCase() + '">' +
					'<div class="show_name"><span>' + v + '</span></div>' +
				'</div>';
	});
	html    += '</div>';

	var style = '';
	if (checkCalc() === false) {
		var width = $content.outerWidth() - 69;
		style = ' style="width: ' + width + 'px;"';
	}

	html    += '<div id="schedule-skip" class="schedule_skip button"></div>';
	html    += '<div id="schedule-scroller" class="schedule_scroller needsclick"' + style + '>';
	html    += '<div id ="schedule-container" class="schedule_container">';


	$.each(data.keys, function(n,key) {
		html += '<div class="day">';
		html += '<div class="stage">';

		var min     = dates[i];
		var max     = min + 61200;
		while (min < max) {
			var time    = new Date(min * 1000);
			html    += '<div class="time"><div>';
			if (time.getMinutes() === 0) {
				var hours = (time.getHours() < 12) ? time.getHours() : time.getHours() - 12;
				html += (hours === 0) ? 12 : hours;
				html += (time.getHours() < 12) ? '<span class="block">AM</span>' : '<span class="block">PM</span>';
			}
			html	+= '</div></div>';
			min = min + 900;
		}

		var date = new Date(dates[i] * 1000).getDate();
		//html += '<div class="name_day">' + days[new Date(dates[i] * 1000).getDay()] + ' ' + date + ((date - 1 > 3) ? nth[3] : nth[date - 1]) + '</div></div>';
		html += '</div>';

		$.each(data.results[key], function(name, stage) {
			html += populateStage(name, dates, stage, i);
		});
		i++;
		html += '</div>';
	});

	html += '</div>';
	html += '</div>';
	html += '</div>';

	content.innerHTML = html;

	changeTitle('schedule');

	var width = 0;
	$('.day').each(function(i,v) {
		width = width + $(this).outerWidth();
		widths.push(width);
	});

	scheduleOffsets = widths;
	scheduleOffsets.pop();

	var $schedule	= $(document.getElementById('schedule-container'));
	$schedule.css('width', (width + 5) + 'px');

	resizeStages();

	var min = -1;
	var max = 0;
	var el  = document.getElementById('date');

	$('.schedule_scroller').on('scroll gesturechange resetMaxMin', function(e) {
		if (e.type === 'resetMaxMin') {
			setTimeout(function() {
				min = 0;
				max = 1;
			}, 0);
		} else {
			if ($(e.currentTarget)[0].scrollLeft > widths[max] && (max + 1) <= widths.length) {
				min++;
				max++;
				var date = new Date(dates[max] * 1000).getDate();
				el.innerHTML = daysShort[new Date(dates[max] * 1000).getDay()] + '<span class="block">' + date + ((date - 1 > 3) ? nth[3] : nth[date - 1]) + '</span>';
			} else if ($(e.currentTarget)[0].scrollLeft < widths[min] && min >= 0) {
				min--;
				max--;
				var date = new Date(dates[max] * 1000).getDate();
				el.innerHTML = daysShort[new Date(dates[max] * 1000).getDay()] + '<span class="block">' + date + ((date - 1 > 3) ? nth[3] : nth[date - 1]) + '</span>';
			}
		}
	});

	finishLoading();
}


function populateStage(name, dates, stage, i) {
	var html = '<div class="stage">';
	var min = dates[i];
	var max = min + 61200;
	var margin = 0;

	while (min < max) {
		if (stage[min]) {
			var time = new Date(stage[min]['original_timestamp'] * 1000);
			html += '<div class="js-artist band needsclick" style="margin-left: ' + margin + 'px;" data-artist="' + stage[min]['@id'] + '" onclick=""><div>' + stage[min]['artistName'] + '</div><span>' + time.getHours() + ':' + time.getMinutes().pad() + '</span></div>';
			margin = -90;
		} else {
			margin = margin + 30;
		}
		min = min + 900;
	}
	html += '</div>';

	return html;
}

function resizeStages() {
	var $schedule	= $(document.getElementById('schedule-container'));
	var $stages		= $(document.getElementById('stages'));

	var sh = $schedule.outerHeight();
	var st = $stages.outerHeight(true);

	if (sh < (st - 5) || sh > (st + 5)) {
	//if ($schedule.outerHeight() !== $stages.outerHeight(true)) {
		$stages.css('height', $schedule.outerHeight());
	}
}