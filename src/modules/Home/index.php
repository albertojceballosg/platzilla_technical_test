<?php
	// Habilitar output buffering para evitar ERR_INCOMPLETE_CHUNKED_ENCODING
	ob_start();
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
	require_once ('modules/Home/lib/WorkingDayUtils.class.php');
	require_once ('modules/News/lib/NewsUtils.php');
	require_once ('modules/notification_center/lib/NotificationHelper.class.php');
	require_once ('modules/notifications/lib/NotificationUtils.class.php');
	require_once ('modules/notifications/lib/NotificationPeriodUtils.class.php');
	require_once ('modules/operating_modes/lib/OperatingModesHelper.class.php');
	require_once ('modules/panelusuarios/lib/UsersHelper.class.php');
	require_once ('modules/store/lib/StoreUtils.class.php');
	require_once ('modules/webmail/lib/WebmailUtils.class.php');

	global $adb, $app_strings, $current_language, $current_user, $currentModule, $platPrincipal, $webMailClient, $theme, $site_URL, $mod_strings;


	setBugSnag ($site_URL);
	
	$agent        = PlatzillaUtils::purify ($_REQUEST, 'report_agent', null);
	$instance     = PlatzillaUtils::purify ($_REQUEST, 'report_instance', null);
	$selectedTab  = PlatzillaUtils::purify ($_REQUEST, 'tab', null);
	$groupTab     = PlatzillaUtils::purify ($_REQUEST, 'tab_group', null);
	$week		  = PlatzillaUtils::purify ($_REQUEST, 'selectedWeek', null);
	$firstDay     = null;
	$offsetMonth  = 3;
	$isInstance   = !empty ($_SESSION ['platInstancia']);
	$instanceCode = null;
	$viewTitle    = ''; // Inicializar para evitar variable indefinida
	// Inicializar $period según el modo operativo (necesario para tabs como ACTIVITY_REPORT)
	if ($current_user->defaultOperating == 'MANAGEMENT_MODE') {
		$period = 'today';
		$viewTitle = 'Actividades en curso';
	}
	
	if (empty ($selectedTab)) {
		if ($current_user->defaultOperating == 'FORMATIVE_MODE') {
			$selectedTab = 'TRAINING';
			$viewTitle   = '';
		} else if ($current_user->defaultOperating == 'MANAGEMENT_MODE') {
			$selectedTab =  'ACTIONS_PROGRESS';
			$viewTitle   = 'Actividades en curso';
		} else if ($current_user->defaultOperating == 'DIRECTION_MODE') {
			// Cargar agentes con caché simple para DIRECTION_MODE
			$cacheKey = "home_agents_{$current_user->id}";
			if (isset($_SESSION[$cacheKey]) && (time() - $_SESSION[$cacheKey]['timestamp'] < 600)) {
				// Usar caché pero cargar objetos para tener acceso a métodos
				$availableAgents = UsersHelper::FetchAgents ($adb, true);
			} else {
				$availableAgents = UsersHelper::FetchAgents ($adb, true);
				// Guardar timestamp para caché simple
				$_SESSION[$cacheKey] = array(
					'timestamp' => time()
				);
			}
			$selectedTab     = 'SUMMARY';
			if (empty ($agent)) {
				$selectedAgent             = $current_user->id ;
				$_REQUEST ['report_agent'] = $selectedAgent;
			} else {
				$selectedAgent = $agent;
			}
			
			$viewTitle    = 'Informe semanal de estado';
			$firstDay     = WorkingDayUtils::getFirstDayWeek ($adb);
			if (empty ($week)) {
				$lastReport = HomeUtils::getLastWeeklyReport ($adb, $isInstance);
				if (!empty ($lastReport)) {
					$period           = "{$lastReport['date_start']}@{$lastReport['due_date']}";
					$selectedAgent    = $lastReport['agentid'];
					$selectedInstance = $lastReport['instance_code'];
					$instanceEmail    = $lastReport['instance_mail'];
					$instanceCode     =  $selectedInstance . ';' . $instanceEmail;
					$availableReports = HomeUtils::fetchAvailableWeeklyReport ($adb, $selectedInstance, $isInstance, $period);
				} else {
					$fromDate = date ('Y-m-d', strtotime ("{$firstDay} - 1 week"));
					$toDate = date ('Y-m-d', strtotime ($fromDate . '+6 day'));
					$period           = "{$fromDate}@{$toDate}";
					$selectedAgent    = null;
					$selectedInstance = null;
				}
			} else {
				$period	      = $week;
			}
			if (!empty ($availableAgents)) {
				foreach ($availableAgents as $agent) {
					if ($agent->getId() == $selectedAgent) {
						$instances = $agent->getPlatformInstance ();
						if (empty ($instance)) {
							$instance   = $agent->getPlatformInstance ()[0]->getCode();
							$instance  .= ";{$agent->getPlatformInstance ()[0]->getAdministrator ()->getEmail ()}";
							$_REQUEST ['report_instance'] = $instance;
						}
						break;
					}
				}
			}
		}
	}
	//$groupTab = (empty($groupTab) && ($current_user->defaultOperating == 'MANAGEMENT_MODE')) ? 'ACTIVITY' : $groupTab;
	// diferent view with records
	try {
		$masterAdb  = AdbManager::getInstance ()->getMasterAdb ();
		
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

		// Cargar cursos solo si estamos en Modo Formativo con caché simple
		$courses = null;
		if ($current_user->defaultOperating == 'FORMATIVE_MODE') {
			$cacheKey = "home_courses_{$current_user->id}";
			if (isset($_SESSION[$cacheKey]) && (time() - $_SESSION[$cacheKey]['timestamp'] < 300)) {
				// Usar caché pero cargar objetos para tener acceso a métodos
				$courses = CoursesHelper::fetchCourses($masterAdb);
			} else {
				$courses = CoursesHelper::fetchCourses($masterAdb);
				// Guardar timestamp para caché simple
				$_SESSION[$cacheKey] = array(
					'timestamp' => time()
				);
			}
		}
		
		$tabRecords    = ($groupTab == 'records') ? HomeUtils::getCustomViewOnDesk ($adb) : null;
	$omh           = OperatingModesHelper::getInstance();
	$operatingMode = $omh->fetchOperatingMode($current_user->defaultOperating, 'Home');

	// Validar que $operatingMode no sea null
	if (empty($operatingMode)) {
		$instanceName = !empty($_SESSION['platInstancia']) ? $_SESSION['platInstancia'] : 'Instancia Madre';
		$userName = $current_user->column_fields['first_name'] . ' ' . $current_user->column_fields['last_name'];
		error_log("[Home/index.php] ERROR: fetchOperatingMode retornó null | Instancia: {$instanceName} | Usuario: {$userName} (ID: {$current_user->id}) | defaultOperating: {$current_user->defaultOperating}");
		throw new Exception('No se pudo cargar la configuración del modo operativo. Por favor contacte al administrador.', 500);
	}
	
	if (!empty ($tabRecords) && $groupTab == 'records') {
		$selectedTab = array_keys ($tabRecords)[0];
		$omh->getTabsRecords ($tabRecords, $operatingMode);
	}
	
	if (count ($operatingMode->getTabTabs ())) {
			$arguments = array (
				'adb'              => $adb,
				'current_user'     => $current_user,
				'isInstance'       => $isInstance,
				'canCreateRecords' => $canCreateRecords,
				'platPrincipal'    => $platPrincipal,
				'app_strings'      => $app_strings,
				'site_URL'         => $site_URL,
				'view'             => '',
				'moduleTab'        => '',
				'periodTime'       => (!empty($period)) ? $period : 'today',
				'instanceCode'     => (empty($instanceCode)) ? $_REQUEST ['report_instance'] : $instanceCode,
			);
			
			foreach ($operatingMode->getTabTabs () as $tabTab) {
				if($groupTab == 'records' && !empty ($tabRecords)) {
					$arguments ['view']      = $tabTab->getId ();
					$arguments ['moduleTab'] = $tabTab->getModesContent ()->getName ();
				}
				$omh->fillTabsContent ($tabTab->getModesContent (), $arguments);
			}
		}
		
		// Evaluate on-screen notifications (ALERT, NOTIFY, MODAL)
	// For Home, we check multiple modules to catch global notifications like RECORD_NO_CREATE
	
	// First, check for Home-specific notifications
	$notificationDataHome = array(
		'view' => 'HOME',
		'module' => 'Home',
		'mode' => 'view',
		'recordId' => null,
		'platform' => $_SESSION['plat'],
		'style' => 'ALERT',
		'user' => $current_user,
	);
	$notificationsHome = NotificationUtils::fetchApplicableOnScreenNotifications($adb, $notificationDataHome);
	
	// Then, check for daily_report notifications (like RECORD_NO_CREATE)
	$notificationDataDaily = array(
		'view' => 'HOME',
		'module' => 'daily_report',
		'mode' => 'view',
		'recordId' => null,
		'platform' => $_SESSION['plat'],
		'style' => 'ALERT',
		'user' => $current_user,
	);
	$notificationsDaily = NotificationUtils::fetchApplicableOnScreenNotifications($adb, $notificationDataDaily);
	
	// Merge both arrays
	$notifications = array_merge($notificationsHome, $notificationsDaily);
		
		$smarty = new vtigerCRM_Smarty ();
		// Comunes a todos los tabs
		$smarty->assign ('AVAILABLE_AGENTS', isset($availableAgents) ? $availableAgents : null);
		$smarty->assign ('AVAILABLE_REPORTS', isset($availableReports) ? $availableReports : null);
		$smarty->assign ('APP', $app_strings);
		$smarty->assign ('CAN_CREATE_RECORDS', $canCreateRecords);
		$smarty->assign ('DEFAULT_OPERATING', $current_user->defaultOperating);
		$smarty->assign ('FIRST_DAY', $firstDay);
		$smarty->assign ('IS_ADMIN', is_admin ($current_user));
		$smarty->assign ('IS_INSTANCE', !empty ($_SESSION ['platInstancia']));
		$smarty->assign ('INSTANCES', isset($instances) ? $instances : null);
		$smarty->assign ('INSTANCE_ID', isset($selectedInstance) ? $selectedInstance : null);
		$smarty->assign ('ROOT_FOLDER_PATH', PlatzillaUtils::getPlatzillaRootFolderPath ());
		$smarty->assign ('IS_MOTHER', empty ($_SESSION ['platInstancia']));
		$smarty->assign ('SELECTED_TAB', $selectedTab);
		$smarty->assign ('SELECTED_WEEK', $period);
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('TAB_GROUP', $groupTab);
		$smarty->assign ('THEME', $theme);
		$smarty->assign ('OFFSET_MONTH', $offsetMonth);
		$smarty->assign ('OPERATING_MODES', $operatingMode);
		$smarty->assign ('AGENT_ID',  isset($availableAgents) ? $selectedAgent : null);
		$smarty->assign ('VIEW_TITLE', $viewTitle);
		$smarty->assign ('NOTIFICATIONS', $notifications);

		if (isset ($_SESSION ['flashmessage'])) {
			$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
			$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
			unset ($_SESSION ['flashmessage']);
		}
		$smarty->display ('Home/index.tpl');
	} catch (Exception $e) {
		$code   = $e->getCode ();
		$smarty = new vtigerCRM_Smarty ();
		if ($code === 400) {
			$smarty->assign ('MENSAJE', 'Debes verificar tu cuenta');
			$smarty->display ('instanciaUnverified.tpl');
		} else if ($code === 403) {
			$smarty->assign ('LABEL', 'Tu suscripción');
			$smarty->assign ('MESSAGE', $e->getMessage ());
			$smarty->assign ('TYPE', 'ERROR');
			$smarty->assign ('URL', 'index.php?module=Home&action=index');
			$smarty->display ('Message.tpl');
		} else {
			$smarty->assign ('LABEL', 'Se ha presentado un error fatal');
			$smarty->assign ('MESSAGE', $e->getMessage ());
			$smarty->assign ('TYPE', 'ERROR');
			$smarty->assign ('URL', 'index.php?module=Home&action=index');
			$smarty->display ('Message.tpl');
		}
	}
	// Asegurar que el output buffer se vacíe correctamente
	ob_end_flush();
	exit;
