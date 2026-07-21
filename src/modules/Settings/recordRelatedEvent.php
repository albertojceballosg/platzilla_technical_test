<?php
	require_once ('include/utils/utils.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	$relatedEvents = array (
		'eventName'      => SettingsUtils::purify ($_REQUEST, 'eventName'),
		'description'    => SettingsUtils::purify ($_REQUEST, 'description'),
		'event_status'   => SettingsUtils::purify ($_REQUEST, 'status'),
		'event_type'     => SettingsUtils::purify ($_REQUEST, 'eventType'),
		'startTime'      => SettingsUtils::purify ($_REQUEST, 'startTime'),
		'h_startTime'    => SettingsUtils::purify ($_REQUEST, 'h_startTime'),
		'm_startTime'    => SettingsUtils::purify ($_REQUEST, 'm_startTime'),
		'p_startTime'    => SettingsUtils::purify ($_REQUEST, 'p_startTime'),
		'startDays'      => SettingsUtils::purify ($_REQUEST, 'startDays'),
		'startDirection' => SettingsUtils::purify ($_REQUEST, 'startDirection'),
		'startDatefield' => SettingsUtils::purify ($_REQUEST, 'startDatefield'),
		'endTime'        => SettingsUtils::purify ($_REQUEST, 'endTime'),
		'h_endTime'      => SettingsUtils::purify ($_REQUEST, 'h_endTime'),
		'm_endTime'      => SettingsUtils::purify ($_REQUEST, 'm_endTime'),
		'p_endTime'      => SettingsUtils::purify ($_REQUEST, 'p_endTime'),
		'endDays'        => SettingsUtils::purify ($_REQUEST, 'endDays'),
		'endDirection'   => SettingsUtils::purify ($_REQUEST, 'endDirection'),
		'endDatefield'   => SettingsUtils::purify ($_REQUEST, 'endDatefield'),
	);

	setValueVariable (
		'record_event_cfg',
		serialize ($relatedEvents),
		SettingsUtils::purify ($_REQUEST, 'fldmodule')
	);

	exit ();
