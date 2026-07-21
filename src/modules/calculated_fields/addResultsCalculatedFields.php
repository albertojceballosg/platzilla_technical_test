<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');
	require_once ('modules/calculated_fields/CalculatedFields.class.php');
	require_once ('vtlib/Vtiger/Utils.php');
	
	global $adb, $current_language, $current_user, $site_URL;
	setBugSnag ($site_URL);

	$platform            = $_SESSION ['plat'];
	$objCalculatedFields = new CalculatedFieldsUtils ($adb, $platform);
	$recordEdit          = SettingsUtils::purify ($_REQUEST, 'record');
	$moduleName          = SettingsUtils::purify ($_REQUEST, 'moduleId');
	$customFilter        = SettingsUtils::purify ($_REQUEST, 'customFilter');
	$name                = SettingsUtils::purify ($_REQUEST, 'title');
	
	$sqlFilter           = '';
	$arrayFilter         = '';
	try {
		if (!empty($customFilter)) {
			$filterData = unserialize (base64_decode ($customFilter));
			$sqlFilter  = $objCalculatedFields->getSqlFilter($filterData);

			if (!empty($sqlFilter)) {
				$sqlFilter   = json_encode ($sqlFilter, (JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT));
				$arrayFilter = json_encode ($filterData);
			}
		}

		$dataFields = array (
			'title'       => $name,
			'description' => SettingsUtils::purify ($_REQUEST, 'description'),
			'module'      => $moduleName,
			'sqlFilter'   => $sqlFilter,
			'arrayFilter' => $arrayFilter,
			'operField'   => SettingsUtils::purify ($_REQUEST, 'operationfieldId'),
			'oper'        => SettingsUtils::purify ($_REQUEST, 'operation'),
			'isLocked'    => !empty ($_SESSION ['platInstancia']) ? true : false,
			'inRecord'    => SettingsUtils::purify ($_REQUEST, 'inRecord'),
			'operLabel'   => SettingsUtils::purify ($_REQUEST, 'operationfieldLabel', null),
			'period'      => SettingsUtils::purify ($_REQUEST, 'period'),
			'periodField' => SettingsUtils::purify ($_REQUEST, 'periodfieldId', null),
		);
		$smarty     = new vtigerCRM_Smarty ();
		if ($recordEdit != null) {
			$smarty->assign ('COD', $recordEdit);
			$smarty->assign ('EDIT', true);
			$calculationElement = $objCalculatedFields->saveCalculationElement ($dataFields, $current_user, $recordEdit);
		} else {
			$calculationElement = $objCalculatedFields->saveCalculationElement ($dataFields, $current_user);
		}
		$smarty->assign ('MOD', return_module_language($current_language, 'calculated_fields'));
		$smarty->assign ('CEDES', $name);
		$smarty->assign ('CE', $calculationElement);
		$smarty->display ('modules/calculated_fields/CreateCalculatedFieldsStepFour.tpl');
	} catch (Exception $e) {
		$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
		$smarty->assign ('MESSAGE', $e->getMessage ());
		unset ($_SESSION ['flashmessage']);
		header ('Location: index.php?module=calculated_fields&action=index&parenttab=Settings');
	}
