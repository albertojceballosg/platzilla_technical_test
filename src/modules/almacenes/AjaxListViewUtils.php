<?php
	require_once ('Smarty_setup.php');
	require_once ('modules/how_use/lib/HowToUseHelper.php');
	require_once ('modules/indicatorspanel/indicatorspanel.php');
	require_once ('modules/indicatorspanel/lib/IndicatorsPanelHelper.class.php');
	require_once ('modules/Reports/lib/ReportUtils.class.php');
	require_once ('include/platzilla/Data/GraphicManager.php');
	require_once ('include/platzilla/Objects/ApplicationInterface.php');
	require_once ('include/platzilla/Objects/ApplicationSubscriptionInterface.php');
	require_once ('include/platzilla/Utils/ListViewUtils.php');
	require_once ('include/platzilla/Utils/JSGraphicUtils.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/CalendarViewUtils.class.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/GetParentGroups.php');
	require_once ('include/utils/KanbanViewUtils.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/UserInfoUtil.php');

	global $adb, $app_strings, $current_user, $current_module, $mod_strings, $site_URL, $theme;

	setBugSnag ($site_URL);

	$function       = PlatzillaUtils::purify ($_REQUEST, 'function');
	$moduleName     = PlatzillaUtils::purify ($_REQUEST, 'module');
	$statusButtons  = PlatzillaUtils::purify ($_POST, 'buttons');
	$idModeSelected = PlatzillaUtils::purify ($_POST, 'howusename');
	$isInstance     = !empty ($_SESSION ['platInstancia']);

	if ($function == 'GRAPHIC_TAB') {
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
			$graphs = array (
				'applications'     => array (),
				'boxscoresimple'   => array (),
				'boxscoreadvanced' => array (),
				'others'           => array (),
			);
			$objectDate     = new DateTime();
			$dateTo         = $objectDate->format ('Y-m-d');
			$objectDate     = new DateTime();
			$objectDate->modify ('-3 month');
			$dateFrom       = $objectDate->format ('Y-m-d');
			$dateFilter = array (
				'dateFrom' => $dateFrom,
				'dateTo'   => $dateTo,
				'category' => 0,
			);

			$relatedView = array();
			$howToUse = HowToUseHelper::getDefaultMode ($adb, $moduleName, $idModeSelected, 'GRAPHIC_VIEW');
			if(!empty($howToUse['howUseId'])) {
				$relatedView = GraphUtils::customSort ($howToUse ['relatedView'], $howToUse ['viewId']);
			}

			// Get the charts base
			GraphicManager::getInstance($adb)->getBasicGraphics ($graphs, $isInstance, $categories, $dateFilter, $relatedView, $moduleName);
			$graphsUtils = JSGraphicUtils::getInstance ($adb);
			$smarty = new vtigerCRM_Smarty ();
			$smarty->register_function ('loadGraphic', array(&$graphsUtils, 'fetchGoogleChartJs'));
			$smarty->assign ('ACTIVE_TAB', '');
			$smarty->assign ('APPLICATIONS', $categoryCatalg);
			$smarty->assign ('COLORS', array ('#2ecc71', '#e74c3c', '#f39c12', '#3fcfbb', '#626f70', '#8f44ad'));
			$smarty->assign ('FL_MODULE', $moduleName);
			$smarty->assign ('GRAPHS', $graphs);
			$smarty->assign ('GRAPHIC_CATEGORY', 'STANDARD');
			$smarty->assign ('HIDEEN_TAB', 1);
			$smarty->assign ('HOW_USENAME', $idModeSelected);
			$smarty->assign ('IS_ADMIN', is_admin ($current_user));
			$smarty->assign ('IS_INSTANCE', $isInstance);
			$smarty->assign ('MODULE', $currentModule);
			$smarty->assign ('OPERATIONS', GraphUtils::getDefinedOperations ());
			$smarty->assign ('STATUS_BUTTONS', $statusButtons);
			$smarty->assign ('STATUS_TOTAL_BUTTONS', ((array_sum (array_values ($statusButtons))) + 1));
			$htmlOutput = $smarty->fetch ('ListViewGraphics.tpl');
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
	} else if ($function == 'BOX_SCORE_TAB') {
		try {
			if(empty ($moduleName)) {
				throw new Exception ('Módulo no encontrado');
			}
			$relatedView = array();
			$howToUse    = HowToUseHelper::getDefaultMode ($adb, $moduleName, $idModeSelected, 'BOX_SCORE');

			if(!empty($howToUse['howUseId'])) {
				$relatedView = $howToUse['relatedView'];
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
			$categoryCatalg = (array ('all' => array ('config_applicationsid' => 0, 'app_code' => 'all', 'app_name' => $mod_strings['ALL_APLICATIONS'])) + $categoryCatalg);
			$smarty->assign ('APPLICATIONS', $categoryCatalg);
			$smarty->assign ('FAVORITES', null);
			$smarty->assign ('MODSTRING', $mod_strings);
			$smarty->assign ('MODULE', 'indicatorspanel');
			$smarty->assign ('RELATED_VIEW', $relatedView);
			$smarty->assign ('THEME', 'centaurus');
			$smarty->assign ('TAB_ACTIVE', null);
			$smarty->assign ('APPCODE', 'all');
			$smarty->assign ('STATUS_BUTTONS', $statusButtons);
			$smarty->assign ('STATUS_TOTAL_BUTTONS', ((array_sum (array_values ($statusButtons))) + 1));
			//assigning variables to editview boxscore
			$smarty->assign ('ALL_BOX_SCORE', $allBoxScore);
			$smarty->assign ('MONTH_SEARCH', $monthSearch);
			$smarty->assign ('VIEW_SEARCH', $view);
			$smarty->assign ('CODE_FIRST', $codeFirst);
			$smarty->assign ('YEAR_DATE', date ('Y'));
			$htmlOutput = $smarty->fetch ('ListViewBoxScore.tpl');
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
	} else if ($function == 'VIEW-CALENDAR') {
		try {
			if(empty ($moduleName)) {
				throw new Exception ('Módulo no encontrado');
			}
			$viewId      = PlatzillaUtils::purify ($_POST, 'record');
			$relatedView = null;
			$howToUse = HowToUseHelper::getDefaultMode ($adb, $moduleName, $idModeSelected, 'CALENDAR');
			if (!empty($howToUse['howUseId'])) {
				$viewId      = (empty($viewId)) ? $howToUse['viewId'] : $viewId;
				$relatedView = $howToUse ['relatedView'];
			}

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
			$smarty->assign ('DATA', $data);
			$smarty->assign ('MOD', $mod_strings);
			$smarty->assign ('MODULE', $moduleName);
			$smarty->assign ('RELATED_VIEW', $relatedView);
			$smarty->assign ('VIEW', $view);
			$smarty->assign ('VIEW_ID', $viewId);
			$smarty->assign ('STATUS_BUTTONS', $statusButtons);
			$smarty->assign ('STATUS_TOTAL_BUTTONS', ((array_sum (array_values ($statusButtons))) + 1));
			$htmlOutput = $smarty->fetch ('ListViewCalendar.tpl'); // CalendarView.tpl
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
	} else if ($function == 'VIEW-KANBAN') {
		try {
			$kanbanData    = PlatzillaUtils::purify ($_POST, 'kanban');
			$isSearch      = PlatzillaUtils::purify ($_POST, 'is_search');
			$relatedView   = null;
			$howToUse      = HowToUseHelper::getDefaultMode ($adb, $moduleName, $idModeSelected, 'KANBAN_VIEW');
			if (!empty($howToUse ['howUseId'])) {
				$kanbanView = KanbanViewUtils::getKanbanViewById ($adb, $howToUse['viewId']);
				$defaultKanban ['kanbanviewid'] = $kanbanView->getIdKanban();
				$defaultKanban ['fieldname']    = $kanbanView->getFieldName ();
				$relatedView                    = $howToUse ['relatedView'];
			} else {
				$defaultKanban = KanbanViewUtils::isDefaultView($adb, $moduleName, $current_user->id);
			}
			if(empty ($moduleName)) {
				throw new Exception ('Módulo no encontrado');
			} else if (empty($kanbanData) || !is_array($kanbanData)) {
				$kanbanData = $defaultKanban;
			}
			if (!empty($defaultKanban) && empty ($isSearch)) {
				$kanbanData = $defaultKanban;
			}

			if (!empty($kanbanData['fieldname']) && (!empty($kanbanData['kanbanviewid']))) {
				$smarty = new vtigerCRM_Smarty ();
				$recordsModule = KanbanViewUtils::getRecordsModuleView($adb, $moduleName, $current_user, $kanbanData['kanbanviewid']);
				$rulesColors   = KanbanViewUtils::getKanbanViewRules($adb, $kanbanData['kanbanviewid'], $kanbanData['fieldname'], $moduleName, $current_user);
				$viewKanban    = KanbanViewUtils::getKanbanViewById($adb, $kanbanData['kanbanviewid']);
				$canEdit       = (is_admin ($current_user)) ? 'yes' : 'no';

				$smarty->assign ('APP', $app_strings);
				$smarty->assign ('KANBAN_VIEW', $kanbanData['kanbanviewid']);
				$smarty->assign ('ITEMVIEWS', $recordsModule);
				$smarty->assign ('IS_INSTANCE', $isInstance);
				$smarty->assign ('CV_EDIT_PERMIT', $canEdit);
				$smarty->assign ('VIEWNAME', $viewKanban->getLabel());
				$smarty->assign ('MODULENAME', $moduleName);
				$smarty->assign ('MODULE', $moduleName);
				$smarty->assign ('FIELDNAME', $kanbanData['fieldname']);
				$smarty->assign ('RELATED_VIEW', $relatedView);
				$smarty->assign ('RULECOLORS', $rulesColors);
				$smarty->assign ('REQUEST_FROM', 'listView');
				$smarty->assign ('CUSTOMVIEW_OPTION', $customViewHtml);
				$smarty->assign ('STATUS_BUTTONS', $statusButtons);
				$smarty->assign ('STATUS_TOTAL_BUTTONS', ((array_sum (array_values ($statusButtons))) + 1));
				$smarty->assign ('KANBAN_LIST', KanbanViewUtils::getAvailableViewsByModule($adb, $moduleName));
			} else {
				throw new Exception ('Kanban no encontrado');
			}
			$htmlOutput = $smarty->fetch ('ListViewKanbanView.tpl');
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
	} else if ($function == 'VIEW-REPORT') {
		try {
			if(empty ($moduleName)) {
				throw new Exception ('Módulo no encontrado');
			}

			$reporCategory = PlatzillaUtils::purify ($_REQUEST, 'reporCategory', 'STANDARD');

			$reporCategory = ($reporCategory == 'STANDARD') ? 0 : 1;
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

			$relatedView = null;
			$howToUse = HowToUseHelper::getDefaultMode ($adb, $moduleName, $idModeSelected, 'REPORT');
			if (!empty ($howToUse['howUseId']) && !$reporCategory) {
				$relatedView = $howToUse ['relatedView'];
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
			$folders = ReportUtils::getAvailableFolders ($adb, $current_user, $moduleName, $reporCategory);
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
			$smarty->assign ('APP', $app_strings);
			$smarty->assign ('AVAILABLE_APPLICATIONS', $applications);
			$smarty->assign ('AVAILABLE_FOLDERS', $folders);
			$smarty->assign ('AVAILABLE_GROUPS', $groups);
			$smarty->assign ('AVAILABLE_MODULES', ReportUtils::getAvailableModules ($adb));
			$smarty->assign ('AVAILABLE_ROLES', $roles);
			$smarty->assign ('AVAILABLE_STANDARD_FILTER_PERIODS', ReportUtils::getAvailableStandardFilterPeriods ());
			$smarty->assign ('AVAILABLE_USERS', $users);
			$smarty->assign ('HIDDEN_TITLE', true);
			$smarty->assign ('IS_INSTANCE', !empty ($_SESSION ['platInstancia']));
			$smarty->assign ('MOD', $mod_strings);
			$smarty->assign ('MODULE', $moduleName);
			$smarty->assign ('RELATED_VIEW', $relatedView);
			$smarty->assign ('REPORT_CATEGORY', $reporCategory);
			$smarty->assign ('STATUS_BUTTONS', $statusButtons);
			$smarty->assign ('STATUS_TOTAL_BUTTONS', ((array_sum (array_values ($statusButtons))) + 1));
			$smarty->assign ('THEME', $theme);
			$htmlOutput = $smarty->fetch ('ListViewReport.tpl');
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK', 'html' => $htmlOutput, 'category' => $reporCategory ));
		} catch (Exception $e) {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	} else if ($function == 'SAVE-DEFAULT-VIEW') {
		$userId  = PlatzillaUtils::purify ($_POST, 'user_id');
		$tabView = PlatzillaUtils::purify ($_POST, 'tabview');
		try {
			if (empty ($moduleName)) {
				throw new Exception ('Módulo no encontrado');
			}

			ListViewUtils::saveGeneralView ($adb, $moduleName, $userId, $tabView);
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK'));
		} catch (Exception $e) {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	} else if ($function == 'SET-DEFAULT-VIEW') {
		$statusButtons = PlatzillaUtils::purify ($_GET, 'buttons');
		$moduleName    = PlatzillaUtils::purify ($_GET, 'module');
		try {
			if (empty ($moduleName)) {
				throw new Exception ('Módulo no encontrado');
			}
			if (empty($statusButtons)) {
				throw new Exception ('Imposible identificar las vistas disponibles!');
			}

			$createdViews   = explode ('@', $statusButtons);
			$availableViews = ListViewUtils::fetchAvailableGeneralView($adb, $createdViews);

			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('APP', $app_strings);
			$smarty->assign ('AVAILABLE_VIEWS', $availableViews);
			$smarty->assign ('MODULE', $moduleName);
			$smarty->assign ('USER_ID', $current_user->id);
		} catch (Exception $e) {
			$smarty->assign ('MESSAGE', $e->getMessage ());
			$smarty->assign ('TYPE', 'ERROR');
		}
		$smarty->display ('UpdateDefaultListView.tpl');
	} else if ($function == 'KANBAN-VIEW-CARD') {
		try {
			$viewId   = PlatzillaUtils::purify($_GET, 'viewId');
			$recordId = PlatzillaUtils::purify($_GET, 'record');

			if (empty ($moduleName)) {
				throw new Exception ('Módulo no encontrado');
			} else if (empty ($viewId)) {
				throw new Exception ('Kanban no encontrado');
			} else if (empty ($recordId)) {
				throw new Exception ('Registro no encontrado');
			}
			$viewKanban    = KanbanViewUtils::getKanbanViewById($adb, $viewId);
			$recordsModule = KanbanViewUtils::getRecordsModuleView($adb, $moduleName, $current_user, $viewId);
			$cardData      = array();

			foreach ($viewKanban->getKanbanCards () as $card) {
				foreach ($recordsModule[ $recordId ] as $fieldName => $fieldVlue) {
					if ($card->getFieldName () == $fieldName) {
						$cardData[ $card->getFieldLabel() ] = $fieldVlue;
						break;
					}
				}
			}

			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('CARD_DATA', $cardData);
			$smarty->display ('modules/kanban_views/DetailViewMdal.tpl');
		} catch (Exception $e) {
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('MESSAGE', $e->getMessage ());
			$smarty->assign ('TYPE', 'ERROR');
			$smarty->assign ('CARD_DATA', array());
			$smarty->display ('modules/kanban_views/DetailViewMdal.tpl');
		}
	}

	exit();
