<?php

  date_default_timezone_set('Europe/Copenhagen');

  $startTime = $_GET['startTime'] / 1000;
  $endTime   = $_GET['endTime'] / 1000;

  $subject   = $_GET['subject'];
  $desc      = $_GET['desc'];

  $ical = "BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//hacksw/handcal//NONSGML v1.0//EN
BEGIN:VEVENT
UID:" . md5(uniqid(mt_rand(), true)) . "r.oskil.de
DTSTAMP:" . gmdate('Ymd').'T'. gmdate('His') . "Z
DTSTART:".date('Ymd\THis', $startTime)."Z
DTEND:".date('Ymd\THis', $endTime)."Z
SUMMARY:".$subject."
DESCRIPTION:".$desc." (Danish Time, GMT+0200)
BEGIN:VALARM
X-APPLE-DEFAULT-ALARM:TRUE
ACTION:AUDIO
DESCRIPTION:".$desc."
TRIGGER;VALUE=DATE-TIME:".date('Ymd\THis', $startTime - 900)."Z
END:VALARM
END:VEVENT
END:VCALENDAR";

  //set correct content-type-header
  header('Content-type: text/calendar; charset=utf-8');
  header('Content-Disposition: inline; filename=calendar.ics');
  echo $ical;
  exit;

  //TRIGGER:-P15M