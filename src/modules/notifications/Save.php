<?php
	require_once ('include/platzilla/Managers/NotificationManager.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/notifications/lib/NotificationPeriodUtils.class.php');
	require_once ('modules/notifications/lib/NotificationUtils.class.php');
	// Agregado por EB para integrar BUGSNAG - 20200213
	global $site_URL, $root_directory;
	require_once ('include/bugsnag-php-2.9.2/src/Bugsnag/Autoload.php');
	$bugsnag = new Bugsnag_Client('834d564193a48c47f138dc66d2cf5e83');
	$bugsnag->setAppVersion('1.0.0');
	if ($site_URL == 'https://apphome.platzillatest.com/') {
		$bugsnag->setReleaseStage('https://apphome.platzillatest.com/');
	} else if ($site_URL == 'https://app.platzilla.com/') {
		$bugsnag->setReleaseStage('https://app.platzilla.com/');
	} else {
		$bugsnag->setReleaseStage($site_URL);
	}
	$bugsnag->setErrorReportingLevel(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_WARNING & ~E_DEPRECATED);
	// Agregado por EB para integrar BUGSNAG - 20200213

	global $adb, $current_user, $default_timezone;

	if (isset ($default_timezone) && function_exists ('date_default_timezone_set')) {
		date_default_timezone_set ($default_timezone);
	} else if (function_exists ('date_default_timezone_set')) {
		date_default_timezone_set ('UTC');
	}

	try {
		
		$dataSource     = PlatzillaUtils::purify ($_POST, 'datasource');
		if (empty($dataSource)) {
			$dataSource = PlatzillaUtils::purify ($_REQUEST, 'datasource');
		}
		
		$contents       = vtlib_purify ($_POST ['contents'], true);
		$event = PlatzillaUtils::purify ($_POST, 'event');
		
		$eventParameter = PlatzillaUtils::purify ($_POST, 'eventparameter');
		$scope          = PlatzillaUtils::purify ($_POST, 'notificationsfrom');
		$moduleFilter   = PlatzillaUtils::purify ($_POST, 'modulename');
		$columnPeriod   = PlatzillaUtils::purify ($_POST, 'columnPeriod');
		$filterPeriod   = PlatzillaUtils::purify ($_POST, 'filterPeriod');
		// Get standardFilter as array (contains startdate and enddate when filterPeriod is 'custom')
		$standardFilterRaw = isset($_POST['standardfilter']) && is_array($_POST['standardfilter']) ? $_POST['standardfilter'] : null;
		$standardFilter = null;
		
		$name           = PlatzillaUtils::purify ($_POST, 'notificationname');
		$description    = PlatzillaUtils::purify ($_POST, 'description');
		$usersFilter    = PlatzillaUtils::purify ($_POST, 'notificationusers');
		$status         = PlatzillaUtils::purify ($_POST, 'notificationstatus');
		$view           = PlatzillaUtils::purify ($_POST, 'notificationview');
		$moduleNames    = PlatzillaUtils::purify ($_POST, 'modulenames');
		$style          = PlatzillaUtils::purify ($_POST, 'notificationtype');
		$action         = PlatzillaUtils::purify ($_POST, 'notificationsaction');
		$notificationId = PlatzillaUtils::purify ($_POST, 'record');
		$buttonIds      = PlatzillaUtils::purify ($_POST, 'custombuttons');
		$sendByEmail    = PlatzillaUtils::purify ($_POST, 'sendByEmail');
		$modalInputText = vtlib_purify ($_POST ['modalInputText'], true);
		$modalExitText  = vtlib_purify ($_POST ['modalExitText'], true);

		$filterData = array (
			'filterField'     => PlatzillaUtils::purify ($_REQUEST, 'filterField'),
			'filterOperator'  => PlatzillaUtils::purify ($_REQUEST, 'filterOperator'),
			'filterValue'     => PlatzillaUtils::purify ($_REQUEST, 'filterValue'),
			'filterJoin'      => PlatzillaUtils::purify ($_REQUEST, 'filterJoin'),
			'filterGroupJoin' => PlatzillaUtils::purify ($_REQUEST, 'conditionGroups'),
			'indexGrupo'      => PlatzillaUtils::purify ($_REQUEST, 'indexGrupo'),
			'fieldId'         => PlatzillaUtils::purify ($_REQUEST, 'fieldId'),
			'moduleFilter'    => $moduleFilter,
		);
		$type        = 'SCREEN';
		$isLocked    = ($scope == 'USERS');
		$sqlFilter   = NotificationUtils::getSqlFilter ($adb, $filterData);
		$buttonsData = (!empty($buttonIds)) ? json_encode ($buttonIds, (JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT)) : null;

		if (!empty($sqlFilter)) {
			$sqlFilter   = json_encode ($sqlFilter, (JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT));
			$arrayFilter = json_encode ($filterData);
		} else {
			$arrayFilter = null;
		}

		if ($style == Notification::STYLE_MODAL) {
			$moduleNames = array ($moduleFilter);
			// Only auto-assign view if not explicitly set by user
			if (empty($view)) {
				$view = ($event == Notification::EVENT_CREATE_RECORD || $event == Notification::EVENT_EDIT_RECORD) ? Notification::EDIT_VIEW : Notification::DETAIL_VIEW;
			}
		} else if ($style == Notification::STYLE_ALERT) {
			// For ALERT style, if moduleFilter is empty or "Users", set moduleNames to empty array
			// ALERT notifications can be global (all modules) or module-specific
			if (empty($moduleFilter) || $moduleFilter === 'Users') {
				$moduleNames = array();
			} else {
				$moduleNames = array ($moduleFilter);
			}
		}

		// For ALERT type or ALWAYS event, period filters don't apply
		// Convert empty period values to null to avoid storing empty strings
		if ($style == Notification::STYLE_ALERT || $event == Notification::EVENT_ALWAYS) {
			if (empty($columnPeriod)) {
				$columnPeriod = null;
			}
			if (empty($filterPeriod)) {
				$filterPeriod = null;
			}
			// Clear standard filter when period is not needed
			$standardFilter = null;
		} else {
			// For other types, process period filters normally
			if ($filterPeriod === 'custom' && !empty($standardFilterRaw) && is_array($standardFilterRaw)) {
				// Use custom dates from POST when filterPeriod is 'custom'
				$standardFilter = array(
					'startdate' => isset($standardFilterRaw['startdate']) ? $standardFilterRaw['startdate'] : null,
					'enddate' => isset($standardFilterRaw['enddate']) ? $standardFilterRaw['enddate'] : null
				);
				// Validate that both dates are present
				if (empty($standardFilter['startdate']) || empty($standardFilter['enddate'])) {
					$standardFilter = null;
				}
			} else if (!empty($filterPeriod) && $filterPeriod !== 'custom') {
				// Calculate dates from period name when filterPeriod is not 'custom'
				$standardFilter = NotificationPeriodUtils::getStandarFiltersStartAndEndDate ($filterPeriod);
			} else {
				// No period filter
				$standardFilter = null;
			}
		}

		// Prepare standardFilter for saving
		// If null, pass null directly (not json_encode which would create string "null")
		// If array, encode it to JSON string
		$standardFilterForSave = null;
		if ($standardFilter !== null && is_array($standardFilter)) {
			$standardFilterForSave = json_encode ($standardFilter, (JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT));
		}
		
		$filterGroup = NotificationFilter::getInstance ()
			->setAdvancedFilter ($arrayFilter)
			->setColumnPeriod ($columnPeriod)
			->setFilterPeriod ($filterPeriod)
			->setModuleFilter ($moduleFilter)
			->setSqlFilter ($sqlFilter)
			->setStandardFilter ($standardFilterForSave)
			->setUsersFilter ($usersFilter);

		$modal = NotificationModal::getInstance ()
			->setCustomButton ($buttonsData)
			->setExitText (str_replace ('\"', "'",trim ($modalExitText)))
			->setInputText (str_replace ('\"', "'",trim ($modalInputText)))
			->setModuleName ($moduleFilter);

		$notification = Notification::getInstance ()
			->setId ($notificationId)
			->setAction ($action)
			->setContents ($contents)
			->setCreatedTime (time ())
			->setDescription ($description)
			->setEvent ($event)
			->setEventParameter ($eventParameter)
			->setFilter ($filterGroup)
			->setLocked ($isLocked)
			->setModal (($style == Notification::STYLE_MODAL) ? $modal : null)
			->setModuleNames ($moduleNames)
			->setName ($name)
			->setSendByEmail ($sendByEmail)
			->setScope ($scope)
			->setStatus ($status)
			->setStyle ($style)
			->setType ($type)
			->setView ($view);
		
		// Validate notification before saving
		try {
			$notification->validate();
		} catch (Exception $validationException) {
			// If validation fails, redirect to EditView with error message
			if (!empty($dataSource) && $dataSource == 'wizard') {
				header ('HTTP/1.1 400 Bad request');
				header ('Content-Type: application/json');
				echo json_encode ($validationException->getMessage ());
				exit ();
			} else {
				$_SESSION ['flashmessage'] = array (
					'iserror' => true,
					'message' => $validationException->getMessage (),
					'data'    => isset ($notification) ? $notification->serialize () : null,
				);
				$recordUriPart = !empty ($notificationId) ? "&record={$notificationId}" : '';
				$redirectUrl = "index.php?module=notifications&action=EditView&parenttab=Settings{$recordUriPart}";
				header ("Location: {$redirectUrl}");
				exit ();
			}
		}
		
		NotificationManager::getInstance ($adb)->saveNotification ($notification);

		if ($dataSource == 'wizard') {
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json');
			echo json_encode ('OK');
		} else {
			$_SESSION ['flashmessage'] = array (
				'iserror' => false,
				'message' => 'La notificación ha sido guardada',
			);
			header ('Location: index.php?module=notifications&action=ListView&parenttab=Settings');
		}
	} catch (Exception $e) {
		// Log critical errors only
		error_log("[Notifications Save] Error: " . $e->getMessage());
		if (!empty($dataSource) && $dataSource == 'wizard') {
			header ('HTTP/1.1 400 Bad request');
			header ('Content-Type: application/json');
			echo json_encode ($e->getMessage ());
			exit ();
		} else {
			$_SESSION ['flashmessage'] = array (
				'iserror' => true,
				'message' => $e->getMessage (),
				'data'    => isset ($notification) ? $notification->serialize () : null,
			);
			$recordUriPart = !empty ($notificationId) ? "&record={$notificationId}" : '';
			// Use absolute URL to ensure proper redirection
			$redirectUrl = "index.php?module=notifications&action=EditView&parenttab=Settings{$recordUriPart}";
			header ("Location: {$redirectUrl}");
			exit ();
		}
	}
