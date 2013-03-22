<?php

require_once dirname(__FILE__) . '/template-tags/debug.php';
require_once dirname(__FILE__) . '/template-tags/params.php';
require_once dirname(__FILE__) . '/template-tags/post.php';
require_once dirname(__FILE__) . '/template-tags/string.php';
require_once dirname(__FILE__) . '/template-tags/theme.php';


function utc_date($format, $now = null) {
	if (!$now) {
		$now = time();
	}
	$date = new DateTime("@$now");
	$date->setTimezone(new DateTimeZone('UTC'));
	return $date->format($format);
}

function strtotime_tz($timezone, $time, $now = null) {
	if (!$now) {
		$now = time();
	}
	$defaultTimezone = date_default_timezone_get();
	date_default_timezone_set($timezone);
	$time = strtotime($time, $now);
	date_default_timezone_set($defaultTimezone);
	return $time;
}
