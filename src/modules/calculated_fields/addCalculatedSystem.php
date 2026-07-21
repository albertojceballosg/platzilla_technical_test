<?php
	require_once ('include/utils/utils.php');
	require_once ('modules/calculated_fields/CalculatedFields.class.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');
	require_once ('Smarty_setup.php');
	require_once ('vtlib/Vtiger/Utils.php');
	// Agregado por EB para integrar BUGSNAG - 20200326
	global $site_URL;
	require_once ('include/bugsnag-php-2.9.2/src/Bugsnag/Autoload.php');
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
	// Agregado por EB para integrar BUGSNAG - 20200326

	global $adb, $current_language, $current_user;

	$platform            = $_SESSION ['plat'];
	$objCalculatedFields = new CalculatedFieldsUtils ($adb, $platform);
	$smarty              = new vtigerCRM_Smarty ();
	$method              = SettingsUtils::purify ($_REQUEST, 'method');
	$recordId            = SettingsUtils::purify ($_REQUEST, 'record');

	try {
		if ($method == null) {
			if ($recordId != null) {
				$calculatedSystemData = $objCalculatedFields->getCalculateSystemDataById ($recordId);
				$modules[]            = $calculatedSystemData->getModuleName ();
				$relatedModules       = $objCalculatedFields->getRelatedModulesByName ($calculatedSystemData->getModuleName ());
				foreach ($relatedModules as $relation) {
					$modules [] = $relation ['name'];
				}

				$condition = "f.typeofdata LIKE 'N%' AND";
				$smarty->assign ('TITLE', $calculatedSystemData->getName ());
				$smarty->assign ('DESCRIPTION', $calculatedSystemData->getDescription ());
				$smarty->assign ('MWNF', $objCalculatedFields->getModulesForCalculations ());
				$smarty->assign ('MODULE_NAME', $calculatedSystemData->getModuleName ());
				$smarty->assign ('MODULE_FIELD', $objCalculatedFields->getColumnsByModule ($modules, $condition));
				$smarty->assign ('RECORD_ID', $recordId);
				$smarty->assign ('EQUATION_ID', $calculatedSystemData->getEquationId ());
				if (! empty($calculatedSystemData->getCalculatedData ())) {
					$calculatedDataGroup = json_decode(str_replace('&quot;', '"', $calculatedSystemData->getCalculatedData ()), true);
				} else {
					$calculatedDataGroup = $calculatedSystemData->getCalculatedData ();
				}
				$smarty->assign ('CALCULATED_DATA', $calculatedDataGroup);
			}
			$smarty->assign ('MOD', return_module_language ($current_language, 'calculated_fields'));
			$modulesForCalculations = $objCalculatedFields->getModulesForCalculations ();
			$smarty->assign ('MWNF', $modulesForCalculations);
			$smarty->assign ('ACF', $objCalculatedFields->getAllCalculateFields ());
			$smarty->display ('modules/calculated_fields/CreateCalculatedSystem.tpl');
		} else {
			$groups           = SettingsUtils::purify ($_REQUEST, 'calculatedGroup');
			$equationString   = SettingsUtils::purify ($_REQUEST, 'calculatedEquation');
			$calculatedGroups = explode (';', $groups);
			
			$dataForm            = array (
				'name'             => SettingsUtils::purify ($_REQUEST, 'title'),
				'description'      => SettingsUtils::purify ($_REQUEST, 'description'),
				'moduleName'       => SettingsUtils::purify ($_REQUEST, 'modulename'),
				'calculatedGroups' => $calculatedGroups,
				'isLocked'         => !empty ($_SESSION ['platInstancia']) ? true : false,
				'relatedModules'   => SettingsUtils::purify ($_REQUEST, 'relatedModules'),
			);
			$equation = array (
				'typeFirstElement'  => SettingsUtils::purify ($_REQUEST, 'typeFirstElement'),
				'firstField'        => SettingsUtils::purify ($_REQUEST, 'firstField'),
				'firstElement'      => SettingsUtils::purify ($_REQUEST, 'firstElement'),
				'firstValue'        => SettingsUtils::purify ($_REQUEST, 'firstValue'),
				'firstReference'    => SettingsUtils::purify ($_REQUEST, 'firstReference'),
				'operator'          => SettingsUtils::purify ($_REQUEST, 'operator'),
				'typeSecondElement' => SettingsUtils::purify ($_REQUEST, 'typeSecondElement'),
				'secondElement'     => SettingsUtils::purify ($_REQUEST, 'secondElement'),
				'secondField'       => SettingsUtils::purify ($_REQUEST, 'secondField'),
				'secondValue'       => SettingsUtils::purify ($_REQUEST, 'secondValue'),
				'secondReference'   => SettingsUtils::purify ($_REQUEST, 'secondReference'),
				'operatorGroup'     => SettingsUtils::purify ($_REQUEST, 'operatorGroup'),
			);

			$dataForm['equation'] = $equation;

			if ($method == 'SAVE') {
				$resultEquation = $objCalculatedFields->saveCalculateSystem($dataForm, $current_user);
			} else {
				$dataForm['recordId']   = SettingsUtils::purify ($_REQUEST, 'recordId');
				$dataForm['equationId'] = SettingsUtils::purify ($_REQUEST, 'equationId');
				$resultEquation = $objCalculatedFields->saveCalculateSystem($dataForm, $current_user);
			}

			$smarty->assign ('MOD', return_module_language ($current_language, 'calculated_fields'));
			$smarty->assign ('CSDES', $dataForm['name']);
			$smarty->assign ('CS', $resultEquation);
			$smarty->assign ('METHOD', $method);
			$smarty->assign ('EQ_STRING', $equationString);
			$smarty->display ('modules/calculated_fields/ResultsCalculatedSystem.tpl');
		}
	} catch (Exception $e) {
		$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
		$smarty->assign ('MESSAGE', $e->getMessage ());
		unset ($_SESSION ['flashmessage']);
		header ('Location: index.php?module=calculated_fields&action=index&parenttab=Settings');
	}
