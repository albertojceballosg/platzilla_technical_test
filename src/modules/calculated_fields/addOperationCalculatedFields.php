<?php
	require_once ('include/utils/utils.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');
	require_once ('modules/calculated_fields/CalculatedFields.class.php');
	require_once ('Smarty_setup.php');
	require_once ('vtlib/Vtiger/Utils.php');
	
	global $adb, $current_language, $current_user, $site_URL;
	setBugSnag ($site_URL);
	
	$calculatedFilter = '';
	$tableName        = null;
	$isOperGrid       = null;
	$method           = SettingsUtils::purify ($_REQUEST, 'methodo');
	$recordEdit       = SettingsUtils::purify ($_REQUEST, 'record');
	$moduleName       = SettingsUtils::purify ($_REQUEST, 'moduleId');
	$filterField      = SettingsUtils::purify ($_REQUEST, 'filterField');
	$inRecordData     = SettingsUtils::purify ($_REQUEST, 'inRecord');
	$customFilter      = SettingsUtils::purify ($_REQUEST, 'customFilter', null);
	$period           = SettingsUtils::purify ($_REQUEST, 'period');
	$periodField      = SettingsUtils::purify ($_REQUEST, 'periodfieldId');

	$dataFromStepOne  = array(
		'title'       => SettingsUtils::purify ($_REQUEST, 'title'),
		'description' => SettingsUtils::purify ($_REQUEST, 'description'),
		'moduleId'    => $moduleName,
	);

	if ($filterField != null && empty($customFilter)) {
		$filterData   = array(
			'filterField'     => $filterField,
			'filterOperator'  => SettingsUtils::purify($_REQUEST, 'filterOperator'),
			'filterValue'     => SettingsUtils::purify($_REQUEST, 'filterValue'),
			'filterJoin'      => SettingsUtils::purify($_REQUEST, 'filterJoin'),
			'filterGroupJoin' => SettingsUtils::purify($_REQUEST, 'conditionGroups'),
			'indexGrupo'      => SettingsUtils::purify($_REQUEST, 'indexGrupo'),
			'moduleFilter'    => $moduleName,
		);

		$calculatedFilter = base64_encode(serialize($filterData));
	} else if (!empty($customFilter)) {
		$calculatedFilter = $customFilter;
	}
	$platform                   = $_SESSION ['plat'];
	$objCalculatedFields        = new CalculatedFieldsUtils ($adb, $platform);
	$modules[]                  = $moduleName;
	$modulesLabel [$moduleName] = getTranslatedString ($moduleName, $moduleName);
	$relatedModules      = $objCalculatedFields->getRelatedModulesByName ($moduleName);
	foreach ($relatedModules as $relation) {
		$modules []                       = $relation ['name'];
		$modulesLabel[$relation ['name']] = $relation ['tablabel'];
	}

	if ($filterField != null) {
		$hasGrid = false;
		foreach ($filterField as $key => $fieldValue) {
			$isGrid = strpos ($fieldValue, 'vtiger_subfields_special');
			if ($isGrid !== false) {
				$hasGrid = true;
				break;
			}
		}
		if ($hasGrid) {
			// Cuando el módulo fuente ES un campo grid, obtener subcampos del grid
			$fieldsList = $objCalculatedFields->getFieldsFromGrid ($moduleName);
			// Removida restricción: $isOperGrid = 'SUM';
			// Ahora permite todas las operaciones para campos grid (incluyendo uitype 2204)
		} else {
			// Cuando el módulo fuente NO es grid, obtener campos normales
			$fieldsList = $objCalculatedFields->getColumnsByModule ($modules);
			
			// ADEMÁS, agregar SOLO campos calculados grid (uitype 2204) de todos los módulos que tengan grids
			foreach ($modules as $moduleToCheck) {
				$gridFields = $objCalculatedFields->getFieldsFromGrid ($moduleToCheck);
				if (!empty($gridFields)) {
					// Filtrar para mostrar SOLO uitype 2204 (campos calculados de grid)
					$calculatedGridFields = array_filter($gridFields, function($field) {
						return isset($field['uitype']) && $field['uitype'] == 2204;
					});
					if (!empty($calculatedGridFields)) {
						$fieldsList = array_merge($fieldsList, $calculatedGridFields);
					}
				}
			}
		}
	} else {
		// Obtener campos normales de todos los módulos
		$fieldsList = $objCalculatedFields->getColumnsByModule ($modules);
		
		// ADEMÁS, agregar SOLO campos calculados grid (uitype 2204) de todos los módulos que tengan grids
		foreach ($modules as $moduleToCheck) {
			$gridFields = $objCalculatedFields->getFieldsFromGrid ($moduleToCheck);
			if (!empty($gridFields)) {
				// Filtrar para mostrar SOLO uitype 2204 (campos calculados de grid)
				$calculatedGridFields = array_filter($gridFields, function($field) {
					return isset($field['uitype']) && $field['uitype'] == 2204;
				});
				if (!empty($calculatedGridFields)) {
					$fieldsList = array_merge($fieldsList, $calculatedGridFields);
				}
			}
		}
	}
	$smarty  = new vtigerCRM_Smarty ();
	$smarty->assign ('CFF', $calculatedFilter);
	$smarty->assign ('DFSO', $dataFromStepOne);
	$smarty->assign ('FIELD_LIST', $fieldsList);
	$smarty->assign ('IN_RECORD', $inRecordData);
	$smarty->assign ('IS_GRID', $isOperGrid);
	$smarty->assign ('MOD', return_module_language ($current_language, 'calculated_fields'));
	$smarty->assign ('MODULES_LABELS', $modulesLabel);
	$smarty->assign ('MODULE_SOURCE', $moduleName);
	$smarty->assign ('OPERFID', SettingsUtils::purify ($_REQUEST, 'operationfieldId'));
	$smarty->assign ('OPERID', SettingsUtils::purify ($_REQUEST, 'operation'));
	$smarty->assign ('PERIOD', $period);
	$smarty->assign ('PERIOD_FIELDID', $periodField);

	if($recordEdit != null) {
		$smarty->assign ('COD', $recordEdit);
	}
	$smarty->display ('modules/calculated_fields/CreateCalculatedFieldsStepThree.tpl');
