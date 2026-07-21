<?php
	error_reporting(E_ALL);
	ini_set('display_errors', '0');
	ini_set('log_errors', '1');
	
	try {
		require_once ('Smarty_setup.php');
		require_once ('include/platzilla/Managers/CalculationSystemManager.php');
		require_once ('include/utils/PlatzillaUtils.class.php');
		require_once ('modules/indicatorspanel/indicatorspanel.php');
		
	} catch (Exception $e) {
		die("Error al cargar archivos requeridos");
	}
	
	global $adb, $app_strings, $currentModule, $current_user, $mod_strings, $site_URL;
	
	
	setBugSnag ($site_URL);

	$accountId    = PlatzillaUtils::purify ($_REQUEST, 'account_id');
	$app          = PlatzillaUtils::purify ($_REQUEST, 'app');
	$boxScoreName = PlatzillaUtils::purify ($_REQUEST, 'box_score');
	$dataId       = PlatzillaUtils::purify ($_REQUEST, 'dataid');
	$from         = PlatzillaUtils::purify ($_REQUEST, 'date_from');
	$isHome       = PlatzillaUtils::purify ($_REQUEST, 'is_home', null);
	$mode         = PlatzillaUtils::purify ($_REQUEST, 'mode');
	$monthSearch  = PlatzillaUtils::purify ($_REQUEST, 'monthsearch', date ('m'));
	$recordId     = PlatzillaUtils::purify ($_REQUEST, 'record');
	$submit       = PlatzillaUtils::purify ($_REQUEST, 'submit');
	$to           = PlatzillaUtils::purify ($_REQUEST, 'date_to');
	$type         = PlatzillaUtils::purify ($_REQUEST, 'type');
	$view         = PlatzillaUtils::purify ($_REQUEST, 'viewScale', 'Month');
	

	
	$boxScore = IndicatorsPanel::getInstance ($adb, $monthSearch, $recordId, $from, $to);
	
	if (!empty ($recordId)) {
		if (!empty ($dataId)) {
		
			$boxScore->loadData ($recordId, $monthSearch, $type, $dataId);
		
			if (isset($boxScore->boxs[0])) {
			} else {
			}
		
			$dummy                          = explode ('<br>', $boxScore->boxs[0]['box_score'], 2);
			$boxScore->boxs[0]['box_score'] = strip_tags ($dummy[0]);
		
			
			$fldModule        = $boxScore->boxs [0]['sourcemodule'];
			$fieldName        = $boxScore->boxs [0]['calculatedname'];
			$calculatedSystem = $boxScore->boxs [0]['calculated_system'];
			
			
			$fields    = (!empty ($fldModule)) ? IndicatorsPanelHelper::getFieldsByModule ($adb, $fldModule) : null;
			if ($boxScore->boxs [0]['objective_scale'] == 'WEEK') {
				$theMonth = $boxScore->boxs [0]['objective_scale'][0]['month_apli'];
				$weeks    = array();
				foreach ($boxScore->boxs [0]['all_objetive'] as $key => $value) {
					if ($value['month_apli'] != $theMonth) {
						$months [$theMonth] = $weeks;
						unset ($weeks);
						$theMonth = $value ['month_apli'];
					}
					$weeks [$value['week_apli']]['end']       = $value ['date_end'];
					$weeks [$value['week_apli']]['month']     = $value ['month_apli'];
					$weeks [$value['week_apli']]['objective'] = $value ['objective'];
					$weeks [$value['week_apli']]['operator']  = $value ['operator'];
					$weeks [$value['week_apli']]['start']     = $value ['date_from'];
				}
				$months [$theMonth] = $weeks;
			}
		} else {
			$boxScore->loadData ($recordId, $monthSearch, $type);
			$fldModule        = null;
			$fieldName        = null;
			$fields           = null;
			$calculatedSystem = null;
			$months           = null;
		}
		$fulfillment = $boxScore->boxs [0]['cump_array'];
	} else {
		$fulfillment = null;
		$months      = null;
	}
	
	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('ACCOUNT_ID', $accountId);
	$smarty->assign ('ADB', $adb);
	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('BOX_SCORE', $boxScore);
	$smarty->assign ('CALCULATED_SYSTEM', $calculatedSystem);
	$smarty->assign ('CALCULATION_ENGINE', CalculationSystemManager::getInstance ($adb)->fetchCalculationsSystem ('ACTIVE'));
	$smarty->assign ('CODE_APP', $app);
	$smarty->assign ('COUNT', $current_user);
	$smarty->assign ('CURRENT_USER', $current_user);
	$smarty->assign ('FIELDS', $fields);
	$smarty->assign ('FIELD_NAME', $fieldName);
	$smarty->assign ('FLD_MODULE', $fldModule);
	$smarty->assign ('FULFILLMENT', $fulfillment);
	$smarty->assign ('IS_HOME', $isHome);
	$smarty->assign ('IS_MOTHER', empty ($_SESSION ['platInstancia']));
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('MODE', $mode);
	$smarty->assign ('MODULE', $currentModule);
	$smarty->assign ('MODULES', IndicatorsPanelHelper::getModules ($adb));
	$smarty->assign ('MONTHS', isset($months) ? $months : array());
	$smarty->assign ('MONTH_SEARCH', $monthSearch);
	$smarty->assign ('RECORD', $recordId);
	$smarty->assign ('TEMPLATE_PATH', 'themes/modern');
	$smarty->assign ('THIS_MONTH', date ('n'));
	$smarty->assign ('TYPE', $type);
	$smarty->assign ('VIEW_SEARCH', $view);
		
	$output = $smarty->fetch ('modules/indicatorspanel/EditViewBox.tpl');
	
	
	echo $output;
	