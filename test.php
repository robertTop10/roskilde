<?php
	$time = 1366385692000;
	echo $time.PHP_EOL.'</br>';
	$time = $time / 1000;
	echo date('D d M Y - H:i:s', $time).PHP_EOL.'<br/>';
	$newtime = date('D d M Y', $time);
	echo $time.PHP_EOL.'</br>';
    $minutes = date('i', $time);
    $minutes = ($minutes - ($minutes % 15) < 10) ? '00' : $minutes - ($minutes % 15);
    echo $minutes.PHP_EOL.'<br/>';
    $newtime = $newtime.' '.date('H', $time).':'.$minutes.':00';
    echo $newtime.PHP_EOL.'<br/>';
    echo 'Time: '.strtotime($newtime) * 1000;