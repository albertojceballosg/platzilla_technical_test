<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Home/lib/WorkingDayUtils.class.php');
	
	global $adb, $app_strings, $current_user, $currentModule, $mod_strings, $theme, $site_URL;
	setBugSnag ($site_URL);
	
	$function   = PlatzillaUtils::purify ($_POST, 'function');
	
	$smarty     = new vtigerCRM_Smarty ();
	if ($function == 'WORKING-TYPE') {
		try {
			$record = PlatzillaUtils::purify ($_POST, 'record');
			if (empty ($record)) {
				throw new Exception ('Tipo de jornada laboral no identificada!');
			}
			
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('ID', $record);
			$smarty->assign ('USER_WORKING_DAY', WorkingDayManager::getInstance ($adb)->getWorkingDayById ($record, false));
			$htmlOutput = $smarty->fetch ('Home/WorkingDayDetailView.tpl');
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK', 'html' => $htmlOutput));
		} catch (Exception $e) {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	} else if ($function == 'USER-WORKING-TYPE') {
		try {
			$record = PlatzillaUtils::purify ($_POST, 'record');
			if (empty ($record)) {
				throw new Exception ('Tipo de jornada laboral no identificada!');
			}
			
			WorkingDayUtils::setWorkingDayToUser ($adb, $current_user->id, $record);
			
			$htmlOutput = "Su jornada laboral ha sido actualizada con éxito!";
			
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK', 'html' => $htmlOutput));
		} catch (Exception $e) {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	} else if ($function == 'WORKING-DAY-EDIT') {
		try {
			$record     = PlatzillaUtils::purify ($_POST, 'record');
			$idTemplate = PlatzillaUtils::purify ($_POST, 'template_id');
			if (empty ($record)) {
				throw new Exception ('Tipo de jornada laboral no identificada!');
			}
			$workingDay = WorkingDayManager::getInstance ($adb)->getWorkingDayById ($record, false);
			$daysWeek   = WorkingDayUtils::getDaysOfWeek ($adb);
			
			if (!empty ($workingDay)) {
				$totalDays = count ($daysWeek);
				$days       = array ();
				for ($k = 0; $k < $totalDays; $k++) {
					$itFound = false;
					if (!empty ($workingDay->getWorkingDaysOfWeek ())) {
						foreach ($workingDay->getWorkingDaysOfWeek () as $dayOfWeek) {
							if ($dayOfWeek->getWorkingDayName () == $daysWeek[$k]['picklistvalue']) {
								$itFound = true;
							}
						}
					}
					if ($itFound) {
						unset ($daysWeek[ $k ]);
					}
				}
			}
			
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('DAYS_WEEK', (!empty($daysWeek)) ? $daysWeek : null);
			$smarty->assign ('ID', $idTemplate);
			$smarty->assign ('WORKING_DAY', $workingDay);
			$smarty->assign ('WORKING_DAY_STATUS', WorkingDayInterface::WORKING_DAY_STATUS);
			$htmlOutput = $smarty->fetch ('Home/WorkingDayEditView.tpl');
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK', 'html' => $htmlOutput));
		} catch (Exception $e) {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
		
	}
	exit();
