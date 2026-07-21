<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	$fieldId    = SettingsUtils::purify ($_REQUEST, 'moduleFieldId');
	$moduleName = SettingsUtils::purify ($_REQUEST, 'gridModule');

	if ($fieldId != 'null') {
		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('SUB_CAMPOS_GRID', obtieneListaSubCamposCampoGrid ($fieldId, false));
		$smarty->assign ('MODULE_NAME', $moduleName);
		$columnTemplate = $smarty->fetch ('Settings/gridFields.tpl');
		echo $columnTemplate;
	} else {
		echo 'Uops! no se encontraron columnas';
	}
	exit ();
