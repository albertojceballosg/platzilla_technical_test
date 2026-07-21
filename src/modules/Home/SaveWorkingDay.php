<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Home/lib/WorkingDayUtils.class.php');
	
	global $adb, $current_user;
	
	$description         = PlatzillaUtils::purify ($_POST, 'description_working_day');
	$record              = PlatzillaUtils::purify ($_POST, 'record');
	$regularHoursDay     = PlatzillaUtils::purify ($_POST, 'regular_hours_day');
	$regularWorkingHours = PlatzillaUtils::purify ($_POST, 'regular_working_hours');
	$workingDayStatus    = PlatzillaUtils::purify ($_POST, 'working_day_status');
	$workingDayType      = PlatzillaUtils::purify ($_POST, 'working_day_type');
	$workingDays         = PlatzillaUtils::purify ($_POST, 'working_days');
	
	$isInstance = !empty ($_SESSION ['platInstancia']);
	
	try {
		if (empty ($regularWorkingHours)) {
			throw new Exception ('El total de horas de la Jornadas es requerido!');
		}
		
		if (!count($regularHoursDay)) {
			throw new Exception ('El horario regular de la Jornadas es requerido!');
		}
		
		if (empty ($workingDayType)) {
			throw new Exception ('El tipo de Jornadas es requerido!');
		}
		
		$isRegisteredName = WorkingDayUtils::checkWorkingDayName ($adb, $workingDayType);
		if (empty ($workingDayType)) {
			throw new Exception ('El tipo de Jornadas ya esta registrado');
		}
		$daysOfWeek   = WorkingDayUtils::getDaysOfWeek ($adb);
		$theDayOfWeek = null;
		if (!empty ($workingDays) && !empty ($daysOfWeek)) {
			$selectedDays = array_keys ($workingDays);
			$theDayOfWeek = array();
			foreach ($daysOfWeek as $day) {
				if (!in_array ($day['picklistvalue'], $selectedDays)) {
					continue;
				}
				$myDayOfWeek     = $workingDays[ $day['picklistvalue'] ];
				$theDayOfWeek [] = WorkingDaysOfWeek::getInstance ()
					->setAfternoonDueTime ($myDayOfWeek['gbf'])
					->setAfternoonStartTime ($myDayOfWeek['gbi'])
					->setMorningDueTime ($myDayOfWeek['gaf'])
					->setMorningStartTime ($myDayOfWeek['gai'])
					->setWorkingDayId (intval ($record))
					->setWorkingDayName ($day['picklistvalue'])
					->setWorkingHours (intval ($myDayOfWeek['hours']));
			}
			unset ($myDayOfWeek);
		}
		
		$theWorkingDay = WorkingDayMaster::getInstance ()
			->setId ($record)
			->setAfternoonDueTime ($regularHoursDay[3])
			->setAfternoonStartTime ($regularHoursDay[2])
			->setDescription ($description)
			->setMorningDueTime ($regularHoursDay[1])
			->setMorningStartTime ($regularHoursDay[0])
			->setRegularWorkingHours ($regularWorkingHours)
			->setWorkingDayName ($workingDayType)
			->setWorkingDaysOfWeek ($theDayOfWeek)
			->setWorkingDayStatus ($workingDayStatus);
		
		WorkingDayManager::getInstance ($adb)->saveWorkingDayMaster ($theWorkingDay);
		
		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('AVAILABLE_WORKING_DAYS', WorkingDayManager::getInstance ($adb)->fetchWorkingDay ());
		$smarty->assign ('ONLY_ENNABLED', true);
		$htmlOutput [0] = $smarty->fetch ('Home/TabsContents/available_working_days.tpl');
		$smarty->assign ('ONLY_ENNABLED', false);
		$htmlOutput [1] = $smarty->fetch ('Home/TabsContents/available_working_days.tpl');
		header('Access-Control-Allow-Origin: *');
		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json; charset=utf-8');
		echo json_encode (array ('error' => 'OK', 'html' => $htmlOutput));
	} catch (Exception $e) {
		header('Access-Control-Allow-Origin: *');
		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json; charset=utf-8');
		echo json_encode (array('error' => $e->getMessage ()));
	}
	exit ();
