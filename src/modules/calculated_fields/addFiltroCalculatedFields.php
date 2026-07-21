<?php
	require_once ('include/utils/utils.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');
	require_once ('modules/calculated_fields/CalculatedFields.class.php');
	require_once ('Smarty_setup.php');
	require_once ('vtlib/Vtiger/Utils.php');
	
	global $adb, $current_language, $current_user,$site_URL;
	setBugSnag ($site_URL);
	
	$platform            = $_SESSION ['plat'];
	$objCalculatedFields = new CalculatedFieldsUtils ($adb, $platform);
	$recordEdit          = SettingsUtils::purify ($_REQUEST, 'record');
	$moduleName          = SettingsUtils::purify ($_REQUEST, 'moduleId');
	$customFilter        = SettingsUtils::purify ($_REQUEST, 'customFilter');
	$inRecordData        = SettingsUtils::purify ($_REQUEST, 'inRecord');
	$period              = SettingsUtils::purify ($_REQUEST, 'period');
	$periodField         = SettingsUtils::purify ($_REQUEST, 'periodfieldId');

	$dataFromStepOne = array(
		'title'       => SettingsUtils::purify ($_REQUEST, 'title'),
		'description' => SettingsUtils::purify ($_REQUEST, 'description'),
		'moduleId'    => $moduleName,
	);
	$smarty  = new vtigerCRM_Smarty ();
	if(! empty($customFilter)) {
		$smarty->assign ('CF_FILTER', unserialize(base64_decode($customFilter)));
		$smarty->assign ('FILTER_TYPE', $objCalculatedFields->getTypeOfData ());
	}
	$modules []                 = $moduleName;
	$modulesLabel [$moduleName] = getTranslatedString ($moduleName, $moduleName);
	$relatedModules             = $objCalculatedFields->getRelatedModulesByName ($moduleName);
	
	// Ordenar módulos relacionados alfabéticamente por tablabel
	if ($relatedModules) {
		usort($relatedModules, function($a, $b) {
			return strcasecmp($a['tablabel'], $b['tablabel']);
		});
	}
	
	foreach ($relatedModules as $relation) {
		$modules []                       = $relation ['name'];
		$modulesLabel[$relation ['name']] = $relation ['tablabel'];
	}
	$fieldsList = $objCalculatedFields->getColumnsByModule ($modules);

	if (!empty($fieldsGrid = $objCalculatedFields->getFieldsFromGrid ($moduleName))) {
		$fieldsList = array_merge ($fieldsList, $fieldsGrid);
	}

	// Agregar campo especial para "registro actual" cuando el módulo fuente tiene grids
	$moduleHasGrids = !empty($objCalculatedFields->getFieldsFromGrid ($moduleName));
	if ($moduleHasGrids) {
		$currentRecordField = array(
			'fieldname'  => 'current_record_id',
			'label'      => 'Registro Actual (' . getTranslatedString($moduleName, $moduleName) . ')',
			'tablename'  => 'vtiger_crmentity',
			'uitype'     => 4,
			'typeofdata' => 'N',
			'module'     => getTranslatedString($moduleName, $moduleName),
		);
		array_unshift($fieldsList, $currentRecordField); // Agregar al inicio de la lista
	}

	$smarty->assign ('DFSO', $dataFromStepOne);
	$smarty->assign ('FIELD_LIST', $fieldsList);
	$smarty->assign ('IN_RECORD', $inRecordData);
	$smarty->assign ('MOD', return_module_language ($current_language, 'calculated_fields'));
	$smarty->assign ('MODULES_LABELS', $modulesLabel);
	$smarty->assign ('OPERFID', SettingsUtils::purify ($_REQUEST, 'operationfieldId'));
	$smarty->assign ('OPERID', SettingsUtils::purify ($_REQUEST, 'operation'));
	$smarty->assign ('PERIOD', $period);
	$smarty->assign ('PERIOD_FIELDID', $periodField);

	if($recordEdit != null) {
		$smarty->assign ('COD', $recordEdit);
	}
	$smarty->display ('modules/calculated_fields/CreateCalculatedFieldsStepTwo.tpl');
