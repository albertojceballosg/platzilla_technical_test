<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Managers/ScoringBoxManager.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatformUtils.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/utils.php');
	
	global $adb, $app_strings, $current_user, $mod_strings, $theme;
	
	$smarty = new vtigerCRM_Smarty ();
	if (!is_admin ($current_user)) {
		$smarty->assign ('APP', $app_strings);
		$smarty->assign ('ICON_URL', vtiger_imageurl ('denied.gif', $theme));
		$smarty->display ('AccessDenied.tpl');
		exit ();
	}
	try {
		$isInstance       = !empty ($_SESSION ['platInstancia']);
		$selectedInstance = PlatzillaUtils::purify ($_REQUEST, 'instance', null );
		$tab              = PlatzillaUtils::purify ($_REQUEST, 'tab', 'BOX_SCORE_MOTHER');
		$targetAdb        = ($isInstance) ? AdbManager::getInstance ()->getMasterAdb () : $adb;
		$mother           = ScoringBoxManager::getInstance ($targetAdb)->fetchScoringAllDataBoxes ();
		if (empty ($mother)) {
			throw new Exception ('No se encontraron indicadores registrados');
		}
		
		$instances =  (!$isInstance) ? PlatformUtils::getValidInstances () : null;
		if (!empty ($instances)) {
			$selectedInstance = empty ($selectedInstance) ? $instances[0]['code'] : $selectedInstance;
			$daughters = array();
			foreach ($instances as $instance) {
				$targetAdb = AdbManager::getInstance ()->getTargetInstanceAdb ($instance ['code']);
				if (PlatformUtils::isModuleEnabled ($targetAdb, 'indicatorspanel')) {
					$daughters [$instance ['code']] = ScoringBoxManager::getInstance ($targetAdb)->fetchScoringAllDataBoxes ();
					$daughters [$instance ['code']]['instance'] = $instance;
				}
			}
			if (count ($daughters)) {
				foreach ($mother as $motherScore) {
					foreach ($daughters as $codeInstance => $boxScores) {
						foreach ($boxScores as $index => $boxScore) {
							if (!is_numeric ($index)) {
								continue;
							}
							if ($boxScore->getName () == $motherScore->getName ()) {
								$motherScore->sharedOn [] = $boxScores['instance'];
								break;
							}
						}
					}
				}
			}
		} else if ($isInstance) {
			$daughters = array();
			$dummy = explode ('_', $adb->dbName);
			$selectedInstance = $dummy[2];
			$daughters [$selectedInstance] = ScoringBoxManager::getInstance ($adb)->fetchScoringAllDataBoxes ();
		}
		$smarty->assign ('ADB', $adb);
		$smarty->assign ('DAUGHTERS', isset ($daughters) ? $daughters[$selectedInstance] : null);
		$smarty->assign ('INSTANCES', $instances);
		$smarty->assign ('IS_INSTANCE',$isInstance);
		$smarty->assign ('MOD_STRINGS', $mod_strings);
		$smarty->assign ('MOTHER', $mother);
		$smarty->assign ('SELECTED_INSTANCE', $selectedInstance);
		$smarty->assign ('SELECTED_TAB',$tab);
		$smarty->display ('Settings/BoxScore/ListView.tpl');
	} catch (Exception $e) {
		$smarty->assign ('LABEL', 'Volver');
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->assign ('URL', 'index.php?module=');
		$smarty->display ('Message.tpl');
	}
