<?php
	set_time_limit (0);
	
	require_once ('data/CRMEntity.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatformUtils.class.php');
	require_once ('include/utils/PlatformUtils.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Home/lib/WorkingDayUtils.class.php');
	require_once ('cron/modules/report_rails/ReportRailsCronHelper.class.php');
	
	global $adb, $platPrincipal;
	
	/** @var Users $current_user */
	$current_user = CRMEntity::getInstance ('Users');
	$current_user->retrieveCurrentUserInfoFromFile (1);
	$today = date ("F j, Y, g:i a");
	try {
		require ('config.inc.php');
		$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
		if (PlatformUtils::isModuleEnabled ($masterAdb, 'report_rails')) {
			echo $today . ': Módulo Reporte de raíles activo en la plataforma principal' . PHP_EOL;
		} else {
			echo $today . ': Modulo Reporte de raíles no activo en la plataforma principal. Saltando' . PHP_EOL;
		}
	} catch (Exception $e) {
		echo $today . ": Plataforma principal: {$e->getMessage ()}" . PHP_EOL;
	}
	
	try {
		echo $today . ': Iniciando escaneo de Reportes  '. $masterAdb->dbName  . PHP_EOL;
		$firstDay         = WorkingDayUtils::getFirstDayWeek ($masterAdb);
		$fromDate         = date ('Y-m-d', strtotime ("{$firstDay} - 1 week"));
		$toDate           = date ('Y-m-d', strtotime ($fromDate . '+6 day'));
		$thisDate         = date ('Y-m-d');
		$nextFromDate     = date ('Y-m-d', strtotime ($fromDate . '+7 day'));
		$nextDueDate      = date ('Y-m-d', strtotime ($nextFromDate . '+6 day'));
		$dummy            = explode ('_', $masterAdb->dbName);
		$week             = date ('W', strtotime ($fromDate));
		
		$weeklyReportCode   = base64_encode ("{$dummy[2]}-{$week}-{$fromDate}");
		$helperClass        = new ReportRailsCronHelper ($masterAdb);
		$hasReport          = $helperClass->hasWeeklyReport ($masterAdb, $weeklyReportCode, 'ACTUAL');
		if (($thisDate == $toDate && !$hasReport)) {
			echo 'Hoy es el último día de la semana. => ' . PHP_EOL;
			$weeklyReportCode = $helperClass->fetchDailyReport ($masterAdb, $fromDate, $toDate);
			if (!empty ($weeklyReportCode)) {
				$helperClass->saveWeeklyReport ($masterAdb, $dummy[2], $weeklyReportCode, 'ACTUAL');
			}
			$week               = date ('W', strtotime ($nextFromDate));
			$upcomingReportCode = base64_encode ("{$dummy[2]}-{$week}-{$nextFromDate}");
			$upcomingReportCode = $helperClass->fetchUpcomingActivities ($masterAdb, $upcomingReportCode, $nextFromDate, $nextDueDate);
			if (!empty ($upcomingReportCode)) {
				$helperClass->saveWeeklyReport ($masterAdb, $dummy[2], $upcomingReportCode, 'UPCOMING');
			}
			$upcomingReportCode = $helperClass->fetchUpcomingTabActivities ($masterAdb, $upcomingReportCode, $nextFromDate, $nextDueDate);
			if (!empty ($upcomingReportCode)) {
				$helperClass->saveWeeklyReport ($masterAdb, $dummy[2], $upcomingReportCode, 'UPCOMING_TAB');
			}
			$bscs = CalculatedSystemUtils::fetchCalculatedBoxScoreData ($masterAdb);
			if (count ($bscs) > 0) {
				echo 'escaneando indicadores en '. $masterAdb->dbName . PHP_EOL;
				$helperClass->setBoxScoreCalculated($masterAdb, $toDate, $bscs);
				unset($bscs);
			}
		}
		$instances = PlatformUtils::getValidInstances ();
		if (empty ($instances)) {
			return;
		}
		unset ($helperClass);
		foreach ($instances as $instance) {
			$targetAdb        = AdbManager::getInstance ()->getTargetInstanceAdb ($instance ['code']);
			$firstDay         = WorkingDayUtils::getFirstDayWeek ($targetAdb);
			$fromDate         = date ('Y-m-d', strtotime ("{$firstDay} - 1 week"));
			$toDate           = date ('Y-m-d', strtotime ($fromDate . '+6 day'));
			$nextFromDate     = date ('Y-m-d', strtotime ($fromDate . '+7 day'));
			$nextDueDate      = date ('Y-m-d', strtotime ($nextFromDate . '+6 day'));
			$week             = date ('W', strtotime ($fromDate));
			
			$weeklyReportCode = base64_encode ("{$instance ['code']}-{$week}-{$fromDate}");
			$helperClass      = new ReportRailsCronHelper ($targetAdb);
			if (($thisDate != $toDate) || ($helperClass->hasWeeklyReport ($masterAdb, $weeklyReportCode, 'ACTUAL'))) {
				continue;
			}
			
			$weeklyReportCode = $helperClass->fetchDailyReport ($targetAdb, $fromDate, $toDate);
			
			if (!empty ($weeklyReportCode)) {
				$helperClass->saveWeeklyReport ($masterAdb, $instance ['code'], $weeklyReportCode, 'ACTUAL');
			}
			$week               = date ('W', strtotime ($nextFromDate));
			$upcomingReportCode = base64_encode ("{$instance ['code']}-{$week}-{$nextFromDate}");
			if (!$helperClass->hasWeeklyReport ($masterAdb, $upcomingReportCode, 'UPCOMING')) {
			     $upcomingReportCode = $helperClass->fetchUpcomingActivities ($targetAdb, $upcomingReportCode, $nextFromDate, $nextDueDate);
				if (!empty ($upcomingReportCode)) {
					$helperClass->saveWeeklyReport ($masterAdb, $instance ['code'], $upcomingReportCode, 'UPCOMING');
				}
			}
			
			if (!$helperClass->hasWeeklyReport ($masterAdb, $upcomingReportCode, 'UPCOMING_TAB')) {
				$upcomingReportCode = $helperClass->fetchUpcomingTabActivities ($targetAdb, $upcomingReportCode, $nextFromDate, $nextDueDate);
				if (!empty ($upcomingReportCode)) {
					$helperClass->saveWeeklyReport ($masterAdb, $instance ['code'], $upcomingReportCode, 'UPCOMING_TAB');
				}
			}
			
			$bscs = CalculatedSystemUtils::fetchCalculatedBoxScoreData ($targetAdb);
			if (count ($bscs) > 0) {
				echo 'escaneando indicadores en '. $targetAdb->dbName . PHP_EOL;
				$helperClass->setBoxScoreCalculated($targetAdb, $toDate, $bscs);
			}
			unset ($helperClass);
		}
	} catch (Exception $e) {
		echo "Plataforma principal: {$e->getMessage ()}" . PHP_EOL;
	}
