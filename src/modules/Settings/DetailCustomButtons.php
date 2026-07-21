<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb, $app_strings, $current_language, $theme;
	if (!isset ($adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	$buttonId = SettingsUtils::purify ($_REQUEST, 'record');

	$result = $adb->pquery ('SELECT * FROM vtiger_custombuttons WHERE custombuttonid=?', array ($buttonId));
	if (($result) && ($adb->num_rows ($result) > 0)) {
		$customButton            = $adb->fetchByAssoc ($result);
		$customButton ['active'] = $customButton ['active'] == 1 ? 'Activa' : 'Inactiva';
		$customButton ['module'] = getTabIdLabelByName ($customButton['module']);

		$availableViews = getVistasDisponiblesParaBotonesPersonalizados ();
		foreach ($availableViews as $availableView) {
			if ($customButton ['action'] == $availableView ['name']) {
				$customButton ['viewlabel'] = $availableView ['label'];
				break;
			}
		}

		$availableButtonTypes = getTiposDisponiblesParaBotonesPersonalizados ();
		foreach ($availableButtonTypes as $availableButtonType) {
			if ($customButton ['type'] == $availableButtonType ['name']) {
				$customButton ['viewlabel'] = $availableButtonType ['label'];
				break;
			}
		}
	} else {
		$customButton = null;
	}

	$smarty = new vtigerCRM_smarty ();
	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('CUSTOMBUTTON', $customButton);
	$smarty->assign ('MOD', return_module_language ($current_language, 'Settings'));
	$smarty->assign ('MODULE', 'Settings');
	$smarty->assign ('THEME', $theme);
	$smarty->display ('Settings/DetailCustomButtons.tpl');
