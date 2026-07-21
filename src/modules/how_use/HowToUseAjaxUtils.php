<?php
	require_once ('include/utils/KanbanViewUtils.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/indicatorspanel/indicatorspanel.php');
	require_once ('modules/indicatorspanel/lib/IndicatorsPanelHelper.class.php');
	require_once ('modules/how_use/lib/HowToUseHelper.php');
	require_once ('modules/CustomView/CustomView.php');
	require_once ('modules/Reports/lib/ReportUtils.class.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/CalendarViewUtils.class.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/DataViewUtils.php');

	global $adb, $app_strings, $current_user, $current_module, $mod_strings, $site_URL, $theme;

	setBugSnag ($site_URL);

	$function   = PlatzillaUtils::purify ($_REQUEST, 'function');
	$moduleName = PlatzillaUtils::purify ($_POST, 'formodule');
	if ($function == 'LIST_VIEW') {
		try {
			if(empty ($moduleName)) {
				throw new Exception ('Jooder Módulo no encontrado  '.$moduleName);
			}
			$customView     = new CustomView ($moduleName);
			$customViewHtml = $customView->getCustomViewCombo ();
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK', 'html' => $customViewHtml));
		} catch (Exception $e) {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	} else if ($function == 'KANBAN_VIEW') {
		try {
			if(empty ($moduleName)) {
				throw new Exception ('Módulo no encontrado');
			}
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign('KANBAN_LIST', KanbanViewUtils::getAvailableViewsByModule($adb, $moduleName));
			$htmlOutput = $smarty->fetch ('modules/how_use/KanbanOptions.tpl');
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK', 'html' => $htmlOutput));
		} catch (Exception $e) {
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	} else if ($function == 'BOX_SCORE') {
		try {
			if(empty ($moduleName)) {
				throw new Exception ('Módulo no encontrado');
			}
			$categories = GraphUtils::getCategories ();
			foreach ($categories as $key => $category) {
				$categoryCatalg [ $key ] = array (
					'app_code' => $key,
					'app_name' => $category,
				);
			}
			$mod_strings      = return_module_language('es_es','indicatorspanel');
			$monthSearch      = date ('m');
			$view             = 'Month';
			$n                = count ($categoryCatalg);
			if ($n > 0 && (!empty($categoryCatalg))) {
				$categoryCode = array_column ($categoryCatalg, 'app_code');
				$codeFirst    = $categoryCode[0];
				for ($i = 0; $i < $n; $i++) {
					$code = $categoryCode[ $i ];
					if ($code != 'all') {
						$bsDefault = IndicatorsPanelHelper::getIndicatorDefault ($adb, $code, $view);
						$record    = $bsDefault['boxscoreid'];
						$boxScore  = IndicatorsPanel::getInstance ($adb, $monthSearch, $record, null, null);
						$boxScore->loadData ($record, $monthSearch, $type, 0, array (), $moduleName);
						$blocks               = $boxScore->getBlocks ($record, $type);
						$calculations         = null;
						$allBoxScore[ $code ] = array ($boxScore, $blocks, $calculations, $record);
					}
				}
			}
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('APPLICATIONS', $categoryCatalg);
			$smarty->assign ('ALL_BOX_SCORE', $allBoxScore);
			$htmlOutput = $smarty->fetch ('modules/how_use/BoxScoreOptions.tpl');
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK', 'html' => $htmlOutput));
		} catch (Exception $e) {
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	} else if ($function == 'GRAPHIC_VIEW') {
		try {
			if(empty ($moduleName)) {
				throw new Exception ('Módulo no encontrado');
			}
			$categories = GraphUtils::getCategories ();
			foreach ($categories as $key => $category) {
				$categoryCatalg [ $key ] = array (
					'app_code' => $key,
					'app_name' => $category,
				);
			}
			GraphicManager::getInstance($adb)->getBasicGraphics ($graphs, false, $categories, null, null, $moduleName);
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('APPLICATIONS', $categoryCatalg);
			$smarty->assign ('GRAPHS', $graphs);
			$htmlOutput = $smarty->fetch ('modules/how_use/GraphicOptions.tpl');
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK', 'html' => $htmlOutput));
		} catch (Exception $e) {
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	} else if ($function == 'REPORT') {
		try {
			if(empty ($moduleName)) {
				throw new Exception ('Módulo no encontrado');
			}
			// Constructing the Role Array
			$roleDetails = getAllRoleDetails ();
			unset ($roleDetails ['H1']);
			$roles = array ();
			foreach ($roleDetails as $roleId => $roleInfo) {
				$roles [ $roleId ] = $roleInfo [0];
			}

			// Constructing the User Array
			$usersDetails = getAllUserName ();
			$users        = array ();
			foreach ($usersDetails as $userId => $userInfo) {
				$users [ $userId ] = $userInfo;
			}

			// Constructing the Group Array
			$groupsDetails = getAllGroupName ();
			$groups        = array ();
			foreach ($groupsDetails as $id => $groupInfo) {
				$groups [ $id ] = $groupInfo;
			}

			// get all applications
			if (!empty ($_SESSION ['platInstancia'])) {
				$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
				$instanceDatabaseName = "pg_crm_{$_SESSION ['platInstancia']}";
				$result               = $masterAdb->pquery (
					"SELECT
				ica.config_applicationsid,
				ica.app_code,
				ica.app_name
			FROM
				vtiger_instanceapplications ia
				INNER JOIN vtiger_instances i ON i.code=ia.instancecode
				INNER JOIN vtiger_config_applications mca ON mca.app_code=ia.applicationcode
				INNER JOIN {$instanceDatabaseName}.vtiger_config_applications ica ON ica.app_code=mca.app_code AND ica.app_status='Activa'
			WHERE
				ia.status IN (?, ?) AND
				i.code=?",
					array (ApplicationSubscriptionInterface::STATUS_ACTIVE, ApplicationSubscriptionInterface::STATUS_SUBSCRIBED, $_SESSION ['platInstancia'])
				);
			} else {
				$result = $adb->pquery ('SELECT config_applicationsid, app_code, app_name FROM vtiger_config_applications WHERE app_status=?', array (ApplicationInterface::STATUS_ACTIVE));
			}
			if (($result) && ($adb->num_rows ($result) > 0)) {
				$applications     = array ();
				$applicationCodes = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$row ['modules'] = ReportUtils::getApplicationModules ($adb, $row ['config_applicationsid']);
					$applications []     = $row;
					$applicationCodes [] = $row ['app_code'];
				}
			} else {
				$applications     = null;
				$applicationCodes = null;
			}

			// get all the folders
			$folders = ReportUtils::getAvailableFolders ($adb, $current_user, $moduleName);
			if (!empty ($folders)) {
				foreach ($folders as $folderIndex => $folder) {
					$reports = $folder ['reports'];
					if (empty ($reports)) {
						continue;
					}
					foreach ($reports as $reportIndex => $report) {
						$reportApplicationCodes = !empty ($report ['applicationcodes']) ? json_decode ($report ['applicationcodes']) : null;
						if (empty ($reportApplicationCodes)) {
							continue;
						} else if ((empty ($applicationCodes)) || (empty (array_intersect ($applicationCodes, $reportApplicationCodes)))) {
							unset ($folders [ $folderIndex ]['reports'][ $reportIndex ]);
						}
					}
				}
			}
			$mod_strings = return_module_language ($current_language, 'Reports');
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('AVAILABLE_APPLICATIONS', $applications);
			$smarty->assign ('AVAILABLE_FOLDERS', $folders);
			$htmlOutput = $smarty->fetch ('modules/how_use/ReportOptions.tpl');
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK', 'html' => $htmlOutput));
		} catch (Exception $e) {
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	} else if ($function == 'CALENDAR') {
		try {
			if(empty ($moduleName)) {
				throw new Exception ('Módulo no encontrado');
			}
			$viewId           = PlatzillaUtils::purify ($_POST, 'record');
			$calendarViewData = CalendarViewUtils::getCalendarViews ($adb, null, null, array($moduleName));

			if (!empty($calendarViewData) && is_array($calendarViewData) && empty($viewId)) {
				if (key_exists ('records', $calendarViewData)) {
					$viewId = $calendarViewData[ 'records' ][ 0 ][ 'calendarviewid' ];
				}
			}
			if (empty ($viewId)) {
				throw new Exception ('No se ha encontrado ID para la vista calendario');
			}

			$view          = CalendarViewUtils::getCalendarViewById ($adb, $viewId);
			$data          = CalendarViewUtils::getCalendarData ($adb, $view, $current_user, $ruleId);
			$calendarViews = CalendarViewUtils::getCalendarViews ($adb);

			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('CALENDAR_VIEWS', $calendarViewData);
			$htmlOutput = $smarty->fetch ('modules/how_use/CalendarOptions.tpl');
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK', 'html' => $htmlOutput));
		} catch (Exception $e) {
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	} else if ($function == 'PROFILE_USE') {
		try {
			$data = array (
				'typeId'   => PlatzillaUtils::purify ($_POST, 'type'),
				'sectorId' => PlatzillaUtils::purify ($_POST, 'sector'),
				'phaseId'  => PlatzillaUtils::purify ($_POST, 'phase'),
			);

			$profiles = ProfilesHowToUseManager::getInstance($adb)->fetchProfilesHowToUseData ($data);

			if (empty ($profiles)) {
				throw new Exception('Upoos!, No se encontro un perfil adecuado');
			}
			$descriptions = array_column ($profiles, 'description');
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('PROFILES_USE', $profiles);
			$htmlOutput = $smarty->fetch ('modules/how_use/profileOptions.tpl');
			$smarty->assign ('PROFILES_USE', $profiles);
			$htmlHelp = $smarty->fetch ('modules/how_use/profileHelp.tpl');
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK', 'html' => $htmlOutput, 'help' => $htmlHelp));
		} catch (Exception $e) {
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	} else if ($function == 'ACTIVATE_PROFILE') {
		try {
			$profileId = PlatzillaUtils::purify ($_POST, 'profile');
			if(empty ($profileId)) {
				throw new Exception('Perfil no identificado');
			}
			$profile = ProfilesHowToUseManager::getInstance ($adb)->fetchProfilesHowToUseById ($profileId);
			if (empty ($profile)) {
				throw new Exception ('Perfil no disponible');
			}

			ProfilesHowToUseManager::getInstance ($adb)->setActiveProfile ($profile);

			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK', 'html' => 'El perfil ha sido activado!'));
		} catch (Exception $e) {
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	} else if ($function == 'KANBAN_TASK_VIEW') {
		try {
			if(empty ($moduleName)) {
				throw new Exception ('Módulo no encontrado');
			}
			
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign('KANBAN_TASK_LIST', DataViewUtils::fetchAvailableViews ($adb, 'Calendar', $current_user));
			$htmlOutput = $smarty->fetch ('modules/how_use/KanbanTaskOptions.tpl');
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK', 'html' => $htmlOutput));
		} catch (Exception $e) {
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	}
	exit();
