<?php
	// SUPRIMIR WARNINGS Y NOTICES DE PANTALLA (solo logs)
	// PERO MANTENER ERRORES FATALES VISIBLES
	
	// Configurar para mostrar SOLO errores fatales y parse errors
	// Suprimir: E_WARNING, E_NOTICE, E_DEPRECATED, E_STRICT
	error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR);
	ini_set('display_errors', '1'); // Mantener display ON para errores fatales
	
	// Establecer el directorio raíz correctamente
	$currentDir = dirname(__FILE__);
	$rootDir = dirname(dirname($currentDir));
	// Normalizar el path eliminando slashes duplicados
	$rootDir = rtrim($rootDir, '/\\');
	chdir($rootDir);
	
	// CRÍTICO: Cargar config.inc.php primero para inicializar $adb
	require_once($rootDir . '/config.inc.php');
	require_once($rootDir . '/include/database/PearDatabase.php');
	
	// Inicializar $adb manualmente si no existe
	if (!isset($adb)) {
		$adb = PearDatabase::getInstance();
	}
	
	// Declarar globales ANTES de cargar otros archivos
	global $adb, $app_strings, $current_user, $mod_strings, $theme, $site_URL;
	
	// Ahora cargar Smarty_setup
	require_once($rootDir . '/Smarty_setup.php');
	
	// Initialize $current_user if not already set by Smarty_setup
	if (!isset($current_user) || !is_object($current_user)) {
		require_once($rootDir . '/include/utils/UserInfoUtil.php');
		require_once($rootDir . '/modules/Users/Users.php');
		$current_user = new Users();
		$current_user->retrieveCurrentUserInfoFromFile(Users::getActiveAdminId());
	}
	
	// Ahora cargar el resto de utilidades
	require_once($rootDir . '/include/utils/CommonUtils.php');
	require_once($rootDir . '/include/utils/PlatzillaUtils.class.php');
	require_once($rootDir . '/include/utils/utils.php');
	require_once($rootDir . '/modules/notifications/lib/NotificationUtils.class.php');
	
	// Agregado por EB para integrar BUGSNAG - 20200213
	require_once($rootDir . '/include/bugsnag-php-2.9.2/src/Bugsnag/Autoload.php');
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

	$smarty = new vtigerCRM_Smarty ();

	$notificationId    = PlatzillaUtils::purify ($_GET, 'notificationId');
	$notificationName  = PlatzillaUtils::purify ($_GET,'notificationName');
	$record            = PlatzillaUtils::purify ($_GET, 'record');
	$moduleName        = PlatzillaUtils::purify ($_GET, 'formodule');
	$notification      = null;
	$notificationModal = null;
	$titleModal        = null;
	$checkModal        = true;
	$isInstance        = !empty ($_SESSION ['platInstancia']);
	$moduleLabel       = getTabname (getTabid ($moduleName));

	if (!$notificationId && !empty ($notificationName)) {
		$sql            = 'SELECT notificationid FROM vtiger_notifications WHERE name=?';
		$result         = $adb->pquery ($sql, array ($notificationName));
		$notificationId = intval ($adb->query_result ($result, 0, 'notificationid'));
	}

	if (!empty ($notificationId)) {
		$notification      = NotificationUtils::fetchNotification ($adb, $notificationId);
		
		// Validar que $notification no sea null antes de usar sus métodos
		if ($notification === null) {
			error_log("ERROR: No se pudo obtener la notificación con ID: " . $notificationId);
			echo '<div class="alert alert-warning">No se pudo cargar la notificación solicitada.</div>';
			exit();
		}
		
		$notificationModal = $notification->getModal ();
		if (!empty ($notificationModal) && in_array ($notification->getName (), array ('AUTOMATED_ACTIVITIES_FIRST_TIME', 'AUTOMATED_ACTIVITIES_SAVE_RECORD'))) {
			$smarty->assign ('AUTOMATED_ACTIVITIES', NotificationUtils::getAutomatedActivities ($adb, $moduleName, $current_user));
			$smarty->assign ('FORMODULE', $moduleLabel);
			$smarty->assign ('MODULE_NAME', $moduleName);
			$smarty->assign ('PROMO_TEXT', $notificationModal->getInputText ());
			$isVideo = strpos ($notificationModal->getExitText (), 'player.vimeo');
			if ($isVideo !== false) {
				$smarty->assign ('PROMO_VIDEO', trim (strip_tags ($notificationModal->getExitText (), '')));
			} else {
				$smarty->assign ('PROMO_VIDEO', null);
			}
			$titleModal = 'Tareas automatizadas';
			$checkModal = false;
			$notificationModal->setInputText ($smarty->fetch ('modules/notifications/AutomatedActivities.tpl'));
		}
		if (!empty ($notificationModal)) {
			$buttonIds = ($notificationModal->getCustomButton()) ? json_decode(str_replace('&quot;', '"', $notificationModal->getCustomButton())) : null;
			if ($buttonIds) {
				$notificationModal->setButtonLinks (NotificationUtils::fetchCustomButtonsData($adb, $buttonIds));
			} else {
				$notificationModal->setButtonLinks (null);
			}
		} else {
			$notificationModal->setButtonLinks (null);
		}
	}
	$smarty->assign ('CHECK_MODAL',$checkModal);
	$smarty->assign ('NOTIFICACTION', $notificationModal);
	$smarty->assign ('RECORD', $record);
	$smarty->assign ('TITLE_MODAL',$titleModal);
	$smarty->display ('modules/notifications/NotificationsModal.tpl');
