<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Data/BoxScoreManager.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Home/lib/WorkingDayUtils.class.php');
	require_once ('modules/indicatorspanel/indicatorspanel.php');
	require_once ('modules/indicatorspanel/lib/IndicatorsPanelHelper.class.php');

	global $adb, $app_strings, $currentModule, $current_user, $mod_strings, $site_URL;
	setBugSnag ($site_URL);

	$function   = PlatzillaUtils::purify ($_REQUEST, 'function');
	$moduleName = PlatzillaUtils::purify ($_REQUEST, 'fldmodule');

	try {
		if (empty ($function)) {
			throw new Exception ('Operación no encontrada');
		} else if (empty ($moduleName)) {
			throw new Exception ('Seleccione un módulo');
		}

		if ($function == 'getFields') {
			echo json_encode (IndicatorsPanelHelper::getFieldsByModule ($adb, $moduleName));
		} else if ($function == 'GET-WEEK-DATA') {
			try {
				$month = PlatzillaUtils::purify ($_REQUEST, 'month');
				if (empty ($month)) {
					throw new Exception ('Mes no encontrado!');
				}
				$firstDay    = WorkingDayUtils::getFirstDayWeek ($adb);
				$firstDayNum = WorkingDayUtils::getDayOfWeek ($firstDay);
				$today    = new DateTime();
				$today->setISODate ($today->format ('o'), $today->format ('W'));
				$fromDate = date ('Y-m-d', strtotime ("{$firstDay} - 0 week"));
				
				list ($year, $thisMonth, $initDay) = explode ('-', $fromDate);
				$totalDays                         = cal_days_in_month(CAL_GREGORIAN, $month, $year);
				$initDay                           = ($month == $thisMonth) ? intval ($initDay) : 1;
				$firstWeek 					       = date ('W', strtotime ("$year-$month-01"));
				for ($day = $initDay; $day <= $totalDays; $day++) {
					if (checkdate ($month, $day, $year)) {
						$date    = "$year-$month-$day";
						$week    = date ('w', strtotime ($date));
						$numWeek = date ('W', strtotime ($date));
						if($week == $firstDayNum){ // $firstDay
							$weeks [$numWeek]['start'] = date ('d-m-Y', strtotime ($date));
							$weeks [$numWeek]['end']   = date ('d-m-Y', strtotime ($weeks [$numWeek]['start'] . '+6 day'));
							$weeks [$numWeek]['objective'] = null;
							$weeks [$numWeek]['operator'] = null;
						}
					}
				}
				
				if (!isset ($weeks) || empty($weeks)) {
					throw new Exception ('Imposible determinar semanas!');
				}
				if (!isset ($weeks [$firstWeek]['start']) && ($weeks [$firstWeek]['end'])) {
					$weeks [$firstWeek]['start'] = date('d-m-Y', strtotime ($weeks[$firstWeek]['end']. ' - 6 days'));
				}
				if ((isset ($weeks [$numWeek]['start'])) && (!isset ($weeks [$numWeek]['end']))) {
					$weeks [$numWeek]['end'] = date('d-m-Y', strtotime ($weeks[$numWeek]['start']. ' + 6 days'));
				}
				
				$smarty = new vtigerCRM_Smarty ();
				$smarty->assign ('MOD', $mod_strings);
				$smarty->assign ('MONTH', $month);
				$smarty->assign('WEEKS', $weeks);
				$htmlOutput = $smarty->fetch('modules/indicatorspanel/Objets/WeekObjetiveDetailView.tpl');
				header ('Access-Control-Allow-Origin: *');
				header ('HTTP/1.1 200 OK');
				header ('Content-Type: application/json; charset=utf-8');
				echo json_encode (array('error' => 'OK', 'html' => $htmlOutput));
			} catch (Exception $e) {
				header ('Access-Control-Allow-Origin: *');
				header ('HTTP/1.1 200 OK');
				header ('Content-Type: application/json; charset=utf-8');
				echo json_encode (array('error' => $e->getMessage()));
			}
		} else if ($function == 'updateFavorite') {
			$boxScoreName = PlatzillaUtils::purify ($_REQUEST, 'boxscorename');
			$faClass      = 'fa-star';
			$title        = 'Ya no es mi favorito';
			$bsm          = BoxScoreManager::getInstance ($adb);
			$myBoxScore   = $bsm->fetchAllFavorites ($current_user->id);
			if (count ($myBoxScore)) {
				if (in_array ($boxScoreName, array_column ($myBoxScore,'boxscorename'))) {
					$bsm->delFavorite(intval ($current_user->id), $boxScoreName);
					$faClass = 'fa-star-o';
					$title   = 'Convertir en mi favorito';
				} else {
					$isFavorite = $bsm->saveFavorite (intval ($current_user->id), $boxScoreName);
					if (!$isFavorite) {
						$faClass = 'fa-star';
						$title   = 'Ya no es mi favorito';
					}
				}
			} else {
				$isFavorite = $bsm->saveFavorite (intval ($current_user->id), $boxScoreName);
				if (!$isFavorite) {
					$faClass = 'fa-star';
					$title   = 'Ya no es mi favorito';
				}
			}
			$faClass = "<span id='fa-{$boxScoreName}' class='fa {$faClass}'></span>";
			echo json_encode (array ('title' => $title, 'faclass' => $faClass));
		} else if ($function == 'UPDATE_RAILES') {
			try {
				$boxScoreName = PlatzillaUtils::purify ($_REQUEST, 'boxscorename');
				$railesStatus = PlatzillaUtils::purify ($_REQUEST, 'status');
				if (empty ($boxScoreName) || empty ($railesStatus)) {
					throw new Exception ('Datos insuficientes!');
				}
				$railesStatus = ($railesStatus == 'HIDE') ? 'SHOW' : 'HIDE';
				IndicatorsPanelHelper::updateRailes ($adb, $boxScoreName, $railesStatus);
				
				header ('Access-Control-Allow-Origin: *');
				header ('HTTP/1.1 200 OK');
				header ('Content-Type: application/json; charset=utf-8');
				echo json_encode (array('error' => 'OK'));
			} catch (Exception $e) {
				header ('Access-Control-Allow-Origin: *');
				header ('HTTP/1.1 200 OK');
				header ('Content-Type: application/json; charset=utf-8');
				echo json_encode (array('error' => $e->getMessage()));
			}
		}
	} catch (Exception $e) {
		echo $e->getMessage ();
	}
	exit ();
