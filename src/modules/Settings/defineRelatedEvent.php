<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $mod_strings;

	$fieldModuleName = SettingsUtils::purify ($_REQUEST, 'fld_module');
	$moduleName      = SettingsUtils::purify ($_REQUEST, 'module');

	$recordEvent    = unserialize (html_entity_decode (obtenerValorVariable ('record_event_cfg', $fieldModuleName)));
	$calendarValues = getAccessPickListValues ('Calendar', true);
	$dataFields     = getDataFields ($fieldModuleName);
	$directions     = array ('AFTER', 'BEFORE');
	$hours          = array ('01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');
	$minutes        = array ('00', '15', '30', '45');
	$amPms          = array ('am', 'pm');

	$eventStatuses = array ();
	foreach ($calendarValues ['status'] as $key => $eventStatus) {
		if (!is_numeric ($key)) {
			continue;
		}
		$text = getTranslatedString ($eventStatus);
		if (trim ($text) == '') {
			continue;
		}

		$option = array (
			'text'  => $text,
			'value' => $eventStatus,
		);
		if ($recordEvent ['event_status'] == $eventStatus) {
			$option ['selected'] = true;
		}
		$eventStatuses [] = $option;
	}

	$eventTypes = array ();
	foreach ($calendarValues ['activitytype'] as $key => $activityType) {
		if (!is_numeric ($key)) {
			continue;
		}
		$text = getTranslatedString ($activityType);
		if (trim ($text) == '') {
			continue;
		}

		$option = array (
			'text'  => $text,
			'value' => $activityType,
		);
		if ($recordEvent ['event_type'] == $activityType) {
			$option ['selected'] = true;
		}
		$eventTypes [] = $option;
	}

	$startDateFields = array ();
	foreach ($dataFields as $dataField) {
		$text = getTranslatedString ($dataFields ['fieldlabel']);
		if (trim ($text) == '') {
			continue;
		}

		$option = array (
			'text'  => $text,
			'value' => $dataField ['fieldname'],
		);
		if ($recordEvent ['startDatefield'] == $dataField ['fieldname']) {
			$option ['selected'] = true;
		}
		$startDateFields [] = $option;
	}

	$endDateFields = array ();
	foreach ($dataFields as $dataField) {
		$text = getTranslatedString ($dataFields ['fieldlabel']);
		if (trim ($text) == '') {
			continue;
		}

		$option = array (
			'text'  => $text,
			'value' => $dataField ['fieldname'],
		);
		if ($recordEvent ['endDatefield'] == $dataField ['fieldname']) {
			$option ['selected'] = true;
		}
		$endDateFields [] = $option;
	}

	$startDirections = array ();
	foreach ($directions as $direction) {
		$text = getTranslatedString ($direction);
		if (trim ($text) == '') {
			continue;
		}

		$option = array (
			'text'  => $text,
			'value' => $direction,
		);
		if ($recordEvent ['startDirection'] == $direction) {
			$option ['selected'] = true;
		}
		$startDirections [] = $option;
	}

	$endDirections = array ();
	foreach ($directions as $direction) {
		$text = getTranslatedString ($direction);
		if (trim ($text) == '') {
			continue;
		}

		$option = array (
			'text'  => $text,
			'value' => $direction,
		);
		if ($recordEvent ['startDirection'] == $direction) {
			$option ['selected'] = true;
		}
		$endDirections [] = $option;
	}

	$startHours = array ();
	foreach ($hours as $hour) {
		$option = array (
			'text'  => $hour,
			'value' => $hour,
		);
		if ($recordEvent ['h_startTime'] == $hour) {
			$option ['selected'] = true;
		}
		$startHours [] = $option;
	}

	$endHours = array ();
	foreach ($hours as $hour) {
		$option = array (
			'text'  => $hour,
			'value' => $hour,
		);
		if ($recordEvent ['h_endTime'] == $hour) {
			$option ['selected'] = true;
		}
		$endHours [] = $option;
	}

	$startMinutes = array ();
	foreach ($minutes as $minute) {
		$option = array (
			'text'  => $minute,
			'value' => $minute,
		);
		if ($recordEvent ['m_startTime'] == $minute) {
			$option ['selected'] = true;
		}
		$startMinutes [] = $option;
	}

	$endMinutes = array ();
	foreach ($minutes as $minute) {
		$option = array (
			'text'  => $minute,
			'value' => $minute,
		);
		if ($recordEvent ['m_endTime'] == $minute) {
			$option ['selected'] = true;
		}
		$endMinutes [] = $option;
	}

	$startAmPms = array ();
	foreach ($amPms as $amPm) {
		$option = array (
			'text'  => $amPm,
			'value' => $amPm,
		);
		if ($recordEvent ['p_startTime'] == $amPm) {
			$option ['selected'] = true;
		}
		$startAmPms [] = $option;
	}

	$endAmPms = array ();
	foreach ($amPms as $amPm) {
		$option = array (
			'text'  => $amPm,
			'value' => $amPm,
		);
		if ($recordEvent ['p_endTime'] == $amPm) {
			$option ['selected'] = true;
		}
		$endAmPms [] = $option;
	}

	$smarty = new vtigerCRM_Smarty;
	$smarty->assign ('END_AMPMS', $endAmPms);
	$smarty->assign ('END_DATE_FIELDS', $endDateFields);
	$smarty->assign ('END_DAYS', $recordEvent ['endDays']);
	$smarty->assign ('END_DIRECTIONS', $endDirections);
	$smarty->assign ('END_HOURS', $endHours);
	$smarty->assign ('END_MINUTES', $endMinutes);
	$smarty->assign ('EVENT_DESCRIPTION', $recordEvent ['description']);
	$smarty->assign ('EVENT_NAME', $recordEvent ['eventName']);
	$smarty->assign ('EVENT_STATUSES', $eventStatuses);
	$smarty->assign ('EVENT_TYPES', $eventTypes);
	$smarty->assign ('FLD_MODULE', $fieldModuleName);
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('MODULE', $moduleName);
	$smarty->assign ('START_AMPMS', $startAmPms);
	$smarty->assign ('START_DATE_FIELDS', $startDateFields);
	$smarty->assign ('START_DAYS', $recordEvent ['startDays']);
	$smarty->assign ('START_DIRECTIONS', $startDirections);
	$smarty->assign ('START_HOURS', $startHours);
	$smarty->assign ('START_MINUTES', $startMinutes);
	$smarty->assign ('VAR_ID', $recordEvent ['id']);
	$smarty->display ('Settings/recordRelatedEvent.tpl');
