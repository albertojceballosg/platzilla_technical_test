<?php
	set_time_limit (0);
	
	require_once ('data/CRMEntity.php');
	require_once ('include/utils/PlatformUtils.class.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/PlatformUtils.class.php');
    require_once ('modules/indicatorspanel/lib/IndicatorsPanelHelper.class.php');
	require_once ('modules/calculated_fields/CalculatedFields.class.php');
    require_once ('modules/systemalerts/systemalerts.php');
    require_once ('modules/systemalerts/lib/SystemAlertsHelper.class.php');
    require_once ('modules/systemalerts/lib/SystemAlertFilterUtils.class.php');
    require_once ('include/utils/DataViewUtils.php');
	require_once ('include/utils/CalculatedSystemUtils.class.php');
    require_once ('include/utils/CommonUtils.php');
    require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/platzilla/Data/BoxScoreManager.php');
	
	global $adb, $platPrincipal;
	
	/** @var Users $current_user */
	$current_user = CRMEntity::getInstance ('Users');
	$current_user->retrieveCurrentUserInfoFromFile (1);
	$today = date("F j, Y, g:i a");
	try {
		require ('config.inc.php');
		$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
		if (PlatformUtils::isModuleEnabled ($masterAdb, 'systemalerts')) {
			echo $today . ': Módulo systemalerts activo en la plataforma principal' . PHP_EOL;
		} else {
			echo $today . ': Modulo systemalerts no activo en la plataforma principal. Saltando' . PHP_EOL;
		}
	} catch (Exception $e) {
		echo $today . ": Plataforma principal: {$e->getMessage ()}" . PHP_EOL;
	}
	function scanAlerts ($dBase, $scaleSearchs, $optionsMenu, $today, $from, $to) {
		$countAlerts = 0;
		foreach ($scaleSearchs as $scaleSearch) {
			echo $today . ': Iniciando escaneo de alertas (' . $scaleSearch. ') desde: ' . $from . ' hasta: ' . $to . PHP_EOL;
			$appReady = array ('all' => 'Todas las alertas');
			foreach ($optionsMenu as $optionMenu) {
				$code = str_replace (array ('&oacute;'), array('o'), $optionMenu['name']);
				$code = strtolower ($code);
				if (in_array ($code, array_keys ($appReady)) || $code == 'revision') {
					continue;
				}
				$appReady[ $code ] = $optionMenu ['name'];
				$alert = SystemAlerts::getInstance ($dBase, $scaleSearch, $code, $from, $to);
				if ($alert->alerts != null) {
					$alerts[ $code ]  = $alert->alerts;
					$alerts[ $code ] ['app_name'] = $optionMenu['name'];
					$countAlerts                  = ($countAlerts + $alerts[ $code ]['countAlert']);
				}
			}
		}
		echo 'Se han revisado las Alertas en '.$dBase->dbName . PHP_EOL;
		echo 'Numero de alertas encontradas: ' . $countAlerts . PHP_EOL;
		unset($appReady);
	}
	
	try {
		echo $today . ': Iniciando escaneo de alertas' . PHP_EOL;
		$first = new DateTime();
		$first->modify ('monday this week');
		$from = $first->format ('Y-m-d');
		$last = new DateTime();
		$last->modify ('last day of this week');
		$to           = $last->format ('Y-m-d');
		$scaleSearchs = array('Month', 'Week');
		$app          = 'all';
		$countAlerts  = 0;
		echo 'Obteniendo aplicaciones..' . PHP_EOL;
		create_parenttab_data_file ();
		$optionsMenu = getHeaderArray ();
		echo 'escaneando alertas en '. $masterAdb->dbName . PHP_EOL;
		scanAlerts ($masterAdb, $scaleSearchs, $optionsMenu, $today, $from, $to);
		
		$instances = PlatformUtils::getValidInstances ();
		if (empty ($instances)) {
			return;
		}
		
		foreach ($instances as $instance) {
			$targetAdb = AdbManager::getInstance ()->getTargetInstanceAdb ($instance ['code']);
			if (PlatformUtils::isModuleEnabled ($targetAdb, 'backgroundtasks')) {
				echo 'escaneando alertas en '. $targetAdb->dbName . PHP_EOL;
				scanAlerts ($targetAdb, $scaleSearchs, $optionsMenu, $today, $from, $to);
			}
		}
	} catch (Exception $e) {
		echo "Plataforma principal: {$e->getMessage ()}" . PHP_EOL;
	}
	