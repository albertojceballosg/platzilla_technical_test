<?php
	require_once ('include/utils/utils.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');
	require_once ('modules/calculated_fields/CalculatedFields.class.php');
	require_once ('Smarty_setup.php');
	require_once ('vtlib/Vtiger/Utils.php');
	
	global $adb, $current_language, $current_user, $site_URL;
	setBugSnag ($site_URL);
	
	$calculatedFilter    = '';
	$tableName           = null;
	$isOperGrid          = null;
	$method              = SettingsUtils::purify ($_REQUEST, 'methodo');
	$recordEdit          = SettingsUtils::purify ($_REQUEST, 'record');
	$moduleName          = SettingsUtils::purify ($_REQUEST, 'moduleId');
	$filterField         = SettingsUtils::purify ($_REQUEST, 'customFilter');
	$inRecordData        = SettingsUtils::purify ($_REQUEST, 'inRecord');
	$period              = SettingsUtils::purify ($_REQUEST, 'period');
	$periodField         = SettingsUtils::purify ($_REQUEST, 'periodfieldId');
	$operationfieldLabel = SettingsUtils::purify ($_REQUEST, 'operationfieldLabel', null);
	
	$dataFromStepOne  = array(
		'title'       => SettingsUtils::purify ($_REQUEST, 'title'),
		'description' => SettingsUtils::purify ($_REQUEST, 'description'),
		'moduleId'    => $moduleName,
	);
	
	$operationFieldId = SettingsUtils::purify ($_REQUEST, 'operationfieldId', null);
	$operation        = SettingsUtils::purify ($_REQUEST, 'operation', null);
	
	$platform                   = $_SESSION ['plat'];
	$objCalculatedFields        = new CalculatedFieldsUtils ($adb, $platform);
	$modules[]                  = $moduleName;
	$modulesLabel [$moduleName] = getTranslatedString ($moduleName, $moduleName);
	$relatedModules             = $objCalculatedFields->getRelatedModulesByName ($moduleName);
	foreach ($relatedModules as $relation) {
		$modules []                       = $relation ['name'];
		$modulesLabel[$relation ['name']] = $relation ['tablabel'];
	}
	$fieldsList = $objCalculatedFields->getColumnsByModule ($modules);
	
	$smarty     = new vtigerCRM_Smarty ();
	$smarty->assign ('CFF', $filterField);
	$smarty->assign ('DFSO', $dataFromStepOne);
	$smarty->assign ('OPERATION_FIELD_ID', $operationFieldId);
	$smarty->assign ('OPERATION_FIELD_LABEL', $operationfieldLabel);
	$smarty->assign ('OPERATION', $operation);
	$smarty->assign ('FIELD_LIST', $fieldsList);
	$smarty->assign ('IN_RECORD', $inRecordData);
	$smarty->assign ('IS_GRID', $isOperGrid);
	$smarty->assign ('MOD', return_module_language ($current_language, 'calculated_fields'));
	$smarty->assign ('MODULE_SOURCE', $moduleName);
	$smarty->assign ('MODULES_LABELS', $modulesLabel);
	$smarty->assign ('OPERFID', SettingsUtils::purify ($_REQUEST, 'operationfieldId'));
	$smarty->assign ('OPERID', SettingsUtils::purify ($_REQUEST, 'operation'));
	$smarty->assign ('PERIOD', $period);
	$smarty->assign ('PERIOD_FIELDID', $periodField);

	if($recordEdit != null) {
		$smarty->assign ('COD', $recordEdit);
	}
	$smarty->display ('modules/calculated_fields/CreateCalculatedFieldsStepPeriod.tpl');
