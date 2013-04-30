function getSchedule() {
	loading();

	var d = ['Sat, 30 Jun 2012 12:00:00 +0200', 'Sun, 01 Jul 2012 12:00:00 +0200', 'Mon, 02 Jul 2012 12:00:00 +0200', 'Tue, 03 Jul 2012 12:00:00 +0200', 'Wed, 04 Jul 2012 12:00:00 +0200', 'Thu, 05 Jul 2012 12:00:00 +0200', 'Fri, 06 Jul 2012 12:00:00 +0200', 'Sat, 07 Jul 2012 12:00:00 +0200', 'Sun, 08 Jul 2012 12:00:00 +0200'];
	var dates = [];
	var stages = [];

	$.each(d, function(i,v) {
		dates.push(new Date(v).getTime() / 1000);
	});


	if (typeof schedule !== 'object') {
		$.getJSON('/php/scheduleJSON.php', function(data) {
			schedule = data;
			processDates(data, dates, stages);
		});
	} else {
		processDates(schedule, dates, stages);
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
	var html = '<div id="stages" class="stages"><div id="date" class="stage_name">' + days[new Date(dates[i] * 1000).getDay()] + ' ' + date + ((date - 1 > 3) ? nth[3] : nth[date - 1]) + '</div>';

	$.each(data.stages, function(i,v) {
		html += '<div class="stage_name">' + v + '</div>';
	});
	html    += '</div>';

	var style = '';
	if (checkCalc() === false) {
		var width = $(document.getElementById('content')).outerWidth() - 60;
		style = ' style="width: ' + width + 'px;"';
	}

	html    += '<div class="schedule_scroller"' + style + '>';
	html    += '<div id ="schedule-container" class="schedule_container">';


	$.each(data.keys, function(n,key) {
		html += '<div class="day">';
		html += '<div class="stage">';

		var min     = dates[i];
		var max     = min + 72000;
		while (min < max) {
			var time    = new Date(min * 1000);
			html    += '<div class="time">' + time.getHours() + ':' + time.getMinutes().pad() + '</div>';
			min = min + 900;
		}

		var date = new Date(dates[i] * 1000).getDate();
		html += '<div class="name_day">' + days[new Date(dates[i] * 1000).getDay()] + ' ' + date + ((date - 1 > 3) ? nth[3] : nth[date - 1]) + '</div></div>';

		$.each(data.results[key], function(name, stage) {
			html += populateStage(name, dates, stage, i);
		});
		i++;
		html += '</div>';
	});

	html += '</div>';
	html += '</div>';

	document.getElementById('content').innerHTML = html;

	var width = 0;
	$('.day').each(function(i,v) {
		width = width + $(this).outerWidth();
		widths.push(width);
	});

	var $schedule	= $(document.getElementById('schedule-container'));
	var $stages		= $(document.getElementById('stages'));
	$schedule.css('width', width + 'px');

	if ($schedule.outerHeight() !== $stages.outerHeight()) {
		$stages.css('height', $schedule.outerHeight());
	}

	var min = -1;
	var max = 0;
	var el  = document.getElementById('date');

	$('.schedule_scroller').on('scroll gesturechange', function(e) {
		if ($(e.currentTarget)[0].scrollLeft > widths[max] && (max + 1) <= widths.length) {
			min++;
			max++;
			var date = new Date(dates[max] * 1000).getDate();
			el.innerHTML = daysShort[new Date(dates[max] * 1000).getDay()] + ' ' + date + ((date - 1 > 3) ? nth[3] : nth[date - 1]);
		} else if ($(e.currentTarget)[0].scrollLeft < widths[min] && min >= 0) {
			min--;
			max--;
			var date = new Date(dates[max] * 1000).getDate();
			el.innerHTML = daysShort[new Date(dates[max] * 1000).getDay()] + ' ' + date + ((date - 1 > 3) ? nth[3] : nth[date - 1]);
		}
	});

	finishLoading();
}


function populateStage(name, dates, stage, i) {
	var html = '<div class="stage">';
	var min = dates[i];
	var max = min + 72000;
	var margin = 0;

	while (min < max) {
		if (stage[min]) {
			var time = new Date(stage[min]['original_timestamp'] * 1000);
			html += '<div class="band" style="margin-left: ' + margin + 'px;" data-artist="' + i + '-' + name + '-' + min + '" onclick=""><div>' + stage[min]['artistName'] + '</div><span>' + time.getHours() + ':' + time.getMinutes().pad() + '</span></div>';
			margin = -90;
		} else {
			margin = margin + 30;
		}
		min = min + 900;
	}
	html += '</div>';

	return html;
	//console.log(new Date(max * 1000));
}