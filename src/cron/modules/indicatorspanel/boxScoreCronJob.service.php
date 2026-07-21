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
		if (PlatformUtils::isModuleEnabled ($masterAdb, 'indicatorspanel')) {
			echo $today . ': Módulo Panel Indicadores activo en la plataforma principal' . PHP_EOL;
		} else {
			echo $today . ': Modulo Panel Indicadores no activo en la plataforma principal. Saltando' . PHP_EOL;
		}
	} catch (Exception $e) {
		echo $today . ": Plataforma principal: {$e->getMessage ()}" . PHP_EOL;
	}
	function scanAlertsIndicators ($dBase, $scaleSearchs, $optionsMenu, $today, $from, $to) {
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
	
	/**
	 * @param PearDatabase $dBase
	 * @param array $bscs
	 *
	 * @return void
	 * @throws Exception
	 */
	function scanCalculates ($dBase, $bscs) {
		$theDate = new DateTime();
		$theDate->modify('last monday');
		$thisMonday = $theDate->format('Y-m-d');
		$processedCalculation = array();
		$objCalculatedFields  = new CalculatedFieldsUtils ($dBase, '');
		$bsm                  = BoxScoreManager::getInstance ($dBase);
		foreach ($bscs as $bsc) {
			if (in_array ($bsc['box_score_dataid'], $processedCalculation)) {
				continue;
			}
			$result = $objCalculatedFields->getCalculateSystemById ($bsc['calculated_system'], 0, 'boxScore', 0, false);
			echo 'Ejecutando calculo en '. $bsc['calculated_system'] . ' ' . $thisMonday . ' ' . $dBase->dbName . PHP_EOL;
			$bsm->saveDataWeekly ($bsc ['boxscoreid'], $bsc ['box_score_dataid'], $result, $thisMonday);
			$processedCalculation [] = $bsc['box_score_dataid'];
		}
		
	}
	try {
		echo $today . ': Iniciando escaneo de Indicadores  '. $masterAdb->dbName  . PHP_EOL;
		
		echo 'Seleccionando los cálculos del sistema para  '. $masterAdb->dbName . PHP_EOL;
		$bscs = CalculatedSystemUtils::fetchCalculatedBoxScoreData ($masterAdb);
		if (count ($bscs) > 0) {
			scanCalculates ($masterAdb, $bscs);
		}
		
		$instances = PlatformUtils::getValidInstances ();
		if (empty ($instances)) {
			return;
		}
		
		foreach ($instances as $instance) {
			$targetAdb = AdbManager::getInstance ()->getTargetInstanceAdb ($instance ['code']);
			if (PlatformUtils::isModuleEnabled ($targetAdb, 'indicatorspanel')) {
				echo 'Iniciando escaneo de Indicadores en '. $targetAdb->dbName . PHP_EOL;
				$bscs = CalculatedSystemUtils::fetchCalculatedBoxScoreData ($targetAdb);
				if (count ($bscs) > 0) {
					echo 'escaneando indicadores en '. $targetAdb->dbName . PHP_EOL;
					scanCalculates ($targetAdb, $bscs);
				}
			}
		}
	} catch (Exception $e) {
		echo "Plataforma principal: {$e->getMessage ()}" . PHP_EOL;
	}
	