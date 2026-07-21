<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Data/ApplicationsManager.php');
	require_once ('include/platzilla/Data/BoxScoreManager.php');
	require_once ('include/platzilla/Data/GraphicManager.php');
	require_once ('include/platzilla/Managers/PlatformSubscriptionManager.php');
	require_once ('include/platzilla/Managers/UserManager.php');
	require_once ('include/platzilla/Utils/JSGraphicUtils.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/DataViewUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Courses/lib/CoursesHelper.php');
	require_once ('modules/Home/lib/HomeUtils.class.php');
	require_once ('modules/News/lib/NewsUtils.php');
	require_once ('modules/notification_center/lib/NotificationHelper.class.php');
	require_once ('modules/notifications/lib/NotificationUtils.class.php');
	require_once ('modules/notifications/lib/NotificationPeriodUtils.class.php');
	require_once ('modules/operating_modes/lib/OperatingModesHelper.class.php');
	require_once ('modules/panelusuarios/lib/UsersHelper.class.php');
	require_once ('modules/store/lib/StoreUtils.class.php');
	require_once ('modules/indicatorspanel/lib/IndicatorsPanelHelper.class.php');
	require_once ('modules/systemalerts/systemalerts.php');
	require_once ('modules/systemalerts/lib/SystemAlertsHelper.class.php');
	require_once ('modules/webmail/lib/WebmailUtils.class.php');
	require_once ('include/utils/GetParentGroups.php');
	require_once ('include/utils/UserInfoUtil.php');
	require_once ('modules/calculated_fields/CalculatedFields.class.php');
	require_once ('modules/Reports/lib/ReportUtils.class.php');
	
	global $adb, $app_strings, $current_language, $current_user, $currentModule, $platPrincipal, $webMailClient, $theme, $site_URL;
	
	setBugSnag ($site_URL);
	
	$selectedTab = PlatzillaUtils::purify ($_GET, 'tab', null);
	
	try {
		$masterAdb  = AdbManager::getInstance ()->getMasterAdb ();
		$isInstance = !empty ($_SESSION ['platInstancia']);
		if ($isInstance) {
			if (!StoreUtils::isInstanceVerified ($_SESSION ['platInstancia'])) {
				throw new Exception ('Debes verificar tu cuenta', 400);
			}
			$psm          = PlatformSubscriptionManager::getInstance ($masterAdb);
			$subscription = $psm->fetchSubscription ($_SESSION ['platInstancia']);
			if ((empty ($subscription)) || ($subscription->getStatus () == PlatformSubscription::STATUS_INACTIVE)) {
				throw new Exception ('Tu suscripción se encuentra inactiva', 403);
			}
			
			$canCreateRecords = true;
		} else {
			$canCreateRecords = true;
		}
		$userCharts = GraphicManager::getInstance ($adb)->fetchAllFavoriteGraphics ($current_user->id);
		
		// No usar BoxScore en esta versión
		// Tab panel de control, Box Score
		//BoxScoreManager::getInstance ($adb)->fetchAllFavorites ($current_user->id);
		$myBoxScore = array ();
		$excludedCategories = array ('Marco','Infraestructura','Actividades','Revision','Control','Mejoras');
		$categories         = GraphUtils::getCategories ($excludedCategories);
		foreach ($categories as $key => $category) {
			$categoryCatalg [ $key ] = array (
				'app_code' => $key,
				'app_name' => $category,
			);
		}
		$smarty = new vtigerCRM_Smarty ();
		if (count ($userCharts)) {
			$favoriteCharts = array_column ($userCharts, 'graficoid');
		} else {
			$favoriteCharts = null;
		}
		$objectDate     = new DateTime();
		$dateTo         = $objectDate->format ('Y-m-d');
		$objectDate     = new DateTime();
		$objectDate->modify ('-3 month');
		$dateFrom       = $objectDate->format ('Y-m-d');
		$dateFilter = array (
			'dateFrom' => $dateFrom,
			'dateTo'   => $dateTo,
		);
		// Obtener los gráficos básicos
		GraphicManager::getInstance($adb)->getBasicGraphics ($graphs, $isInstance, $categories, $dateFilter);
		$graphsUtils = JSGraphicUtils::getInstance ($adb);
			
		$smarty->register_function ('loadGraphic', array(&$graphsUtils, 'fetchGoogleChartJs'));
		$smarty->assign ('ACTIVE_TAB', '');
		$smarty->assign ('APPLICATIONS', $categoryCatalg);
		$smarty->assign ('COLORS', array ('#2ecc71', '#e74c3c', '#f39c12', '#3fcfbb', '#626f70', '#8f44ad'));
		$smarty->assign ('FAVORITES', $favoriteCharts);
		$smarty->assign ('GRAPHS', $graphs);
		$smarty->assign ('IS_ADMIN', is_admin ($current_user));
		$smarty->assign ('IS_FAVORITES', true);
		$smarty->assign ('OPERATIONS', GraphUtils::getDefinedOperations ());
		
		if (count ($myBoxScore)) {
			require_once ('modules/indicatorspanel/indicatorspanel.php');
			require_once ('modules/indicatorspanel/lib/IndicatorsPanelHelper.class.php');
			
			$mod_strings      = return_module_language('es_es','indicatorspanel');
			$monthSearch      = date ('m');
			$favoriteBoxScore = array_column ($myBoxScore, 'boxscorename');
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
						$boxScore->loadData ($record, $monthSearch, $type, 0, $favoriteBoxScore);
						$blocks               = $boxScore->getBlocks ($record, $type);
						$calculations         = null;
						$allBoxScore[ $code ] = array ($boxScore, $blocks, $calculations, $record);
					}
				}
			}
			$categoryCatalg = (array ('all' => array ('config_applicationsid' => 0, 'app_code' => 'all', 'app_name' => $mod_strings['ALL_APLICATIONS'])) + $categoryCatalg);
			$smarty->assign ('APPLICATIONS', $categoryCatalg);
			$smarty->assign ('FAVORITES', $favoriteBoxScore);
			$smarty->assign ('IS_HOME', true);
			$smarty->assign ('MODSTRING', $mod_strings);
			$smarty->assign ('MODULE', 'indicatorspanel');
			$smarty->assign ('THEME', 'centaurus');
			$smarty->assign ('activeTab', null);
			$smarty->assign ('APPCODE', 'all');
			//assigning variables to editview boxscore
			$smarty->assign ('ALL_BOX_SCORE', $allBoxScore);
			$smarty->assign ('MONTH_SEARCH', $monthSearch);
			$smarty->assign ('VIEW_SEARCH', $view);
			$smarty->assign ('CODE_FIRST', $codeFirst);
			$smarty->assign ('YEAR_DATE', date ('Y'));
		}
		// Reports
		$platform         = $_SESSION ['plat'];
		$CalculatedFields = new CalculatedFieldsUtils ($adb, $platform);
		$CalculatedFields->getAllCalculateSystem ();
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
		
		// get Aplications Catalog
		$applicationsArray = HomeUtils::getAppCatalog ($adb, $_SESSION ['platInstancia']);
		$applicationCodes  = (!empty($applicationsArray)) ? $applicationsArray ['applicationCodes'] : null;
		
		// get the folders
		$folders = ReportUtils::getAvailableFolders ($adb, $current_user);
		if (!empty ($folders)) {
			$favorites  = array ();
			$folderTabs = array ();
			foreach ($folders as $folderIndex => $folder) {
				$reports = $folder ['reports'];
				if (empty ($reports)) {
					continue;
				}
				
				foreach ($reports as $reportIndex => $report) {
					if ($report ['locked']) {
						$favorites[] = $report ['reportid'];
					}
					$reportApplicationCodes = !empty ($report ['applicationcodes']) ? json_decode ($report ['applicationcodes']) : null;
					if (empty ($reportApplicationCodes)) {
						continue;
					} else if ((empty ($applicationCodes)) || (empty (array_intersect ($applicationCodes, $reportApplicationCodes)))) {
						unset ($folders [ $folderIndex ]['reports'][ $reportIndex ]);
					}
				}
				
				if (!empty ($folders [ $folderIndex ]['reports'])) {
					$folderTabs [] = array ('foldername' => $folder['foldername'], 'folderid' => $folder['folderid']);
				}
			}
		}
		if (count ($favorites)) {
			$folderTabs [] = array ('foldername' => 'Personalizados', 'folderid' => rand ());
		}
		$smarty->assign ('APP', $app_strings);
		$smarty->assign ('AVAILABLE_APPLICATIONS', (!empty($applicationsArray)) ? $applicationsArray ['applications'] : null);
		$smarty->assign ('AVAILABLE_FOLDERS', $folders);
		$smarty->assign ('AVAILABLE_GROUPS', $groups);
		$smarty->assign ('AVAILABLE_MODULES', ReportUtils::getAvailableModules ($adb));
		$smarty->assign ('AVAILABLE_ROLES', $roles);
		$smarty->assign ('AVAILABLE_STANDARD_FILTER_PERIODS', ReportUtils::getAvailableStandardFilterPeriods ());
		$smarty->assign ('AVAILABLE_USERS', $users);
		$smarty->assign ('FAVORITES_REPORTS', $favorites);
		$smarty->assign ('FOLDERS_TAB', isset ($folderTabs) ? $folderTabs : null);
		$smarty->assign ('IS_INSTANCE', !empty ($_SESSION ['platInstancia']));
		$smarty->assign ('MOD', return_module_language ($current_language, 'Reports'));
		$smarty->assign ('MODULE', 'Reports');
		$smarty->assign ('THEME', $theme);
		
		// System alerts
		$first = new DateTime();
		$first->modify ('first day of this month');
		$from = $first->format ('Y-m-d');
		$last = new DateTime();
		$last->modify ('last day of this month');
		$to          = $last->format ('Y-m-d');
		$scaleSearch = 'Month';
		$countAlerts = 0;
		$alertModString = return_module_language($current_language,'systemalerts');
		$app          = 'all';
		$optionsMenu = getHeaderArray ();
		$appReady = array ('all' => $alertModString['ALL_APLICATIONS']);
		
		foreach ($optionsMenu as $optionMenu) {
			$code = str_replace (array ('&oacute;'), array('o'), $optionMenu['name']);
			$code = strtolower ($code);
			if (in_array ($code, array_keys ($appReady)) || $code == 'revision') {
				continue;
			}
			$appReady[ $code ] = $optionMenu ['name'];
			$alert = SystemAlerts::getInstance ($adb, $scaleSearch, $code, $from, $to, 'no');
			if ($alert->alerts != null) {
				$alerts[ $code ]              = $alert->alerts;
				$alerts[ $code ] ['app_name'] = $optionMenu['name'];
				$countAlerts                  = ($countAlerts + $alerts[ $code ]['countAlert']);
			}
		}
		
		$categories = array_merge (array ('all' => $alertModString['ALL_APLICATIONS']), $categories);
		$categories ['KR']  = 'KR';
		$smarty->assign ('THEME', $theme);
		$smarty->assign ('MODSTRING', $alertModString);
		$smarty->assign ('MODULE', 'systemalerts');
		$smarty->assign ('VIEW_SEARCH', $scaleSearch);
		$smarty->assign ('DATE_FROM', $from);
		$smarty->assign ('FIRST_DAY', $from);
		$smarty->assign ('DATE_TO', $to);
		$smarty->assign ('ALERT_APPLICATIONS', $categories);
		$smarty->assign ('TAB_ACTIVE', $app);
		$smarty->assign ('LABEL_ALL_APLICATIONS', $alertModString['ALL_APLICATIONS']);
		$smarty->assign ('ALL_ALERTS', $alerts);
		$smarty->assign ('LABEL_OPERATOR', SystemAlertsHelper::getOperator ());
		$smarty->assign ('COUNT_ALL_ALERTS', $countAlerts);
		
		// Comunes a todos los tabs
		$smarty->assign ('APP', $app_strings);
		$smarty->assign ('CAN_CREATE_RECORDS', $canCreateRecords);
		$smarty->assign ('DEFAULT_OPERATING', $current_user->defaultOperating);
		$smarty->assign ('IS_ADMIN', is_admin ($current_user));
		$smarty->assign ('IS_INSTANCE', !empty ($_SESSION ['platInstancia']));
		$smarty->assign ('ROOT_FOLDER_PATH', PlatzillaUtils::getPlatzillaRootFolderPath ());
		$smarty->assign ('IS_MOTHER', empty ($_SESSION ['platInstancia']));
		$smarty->assign ('SELECTED_TAB', (!empty($selectedTab)) ? $selectedTab : 'graphics');
		$smarty->assign ('TAB_GROUP', $groupTab);
		$smarty->assign ('THEME', $theme);
		$smarty->assign ('OPERATING_MODES', $operatingMode);
		$smarty->display ('Home/CotrolPanelListView.tpl');
	} catch (Exception $e) {
		$code   = $e->getCode ();
		$smarty = new vtigerCRM_Smarty ();
	}
