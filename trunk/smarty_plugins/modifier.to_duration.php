<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.to_duration.php
 * Type:     modifier
 * Name:     to_duration
 * Purpose:  convert seconds to duration
 * -------------------------------------------------------------
 */
function smarty_modifier_to_duration($seconds = 0) {
	$seconds = (int) $seconds;

	if ($seconds < 0)
		throw new Exception("cannot convert negative duration");
	if ($seconds >= 60 * 60 * 24)
		throw new Exception("cannot convert more than 1 day duration");
	$hours = (int) ($seconds / 3600);
	$seconds -= 3600 * $hours;
	$hours = ($hours < 10) ? "0" . $hours : $hours;

	$minutes = (int) ($seconds / 60);
	$seconds -= 60 * $minutes;
	$minutes = ($minutes < 10) ? "0" . $minutes : $minutes;

	$seconds = ($seconds < 10) ? "0" . $seconds : $seconds;

	return $hours . ':' . $minutes . ':' . $seconds;
}
?>
