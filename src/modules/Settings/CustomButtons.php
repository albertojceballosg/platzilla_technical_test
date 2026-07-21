<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/Settings/lib/CreateCustomButtonHelper.class.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb, $current_language;

	$forModule  = SettingsUtils::purify ($_REQUEST, 'formodule', SettingsUtils::purify ($_SESSION, 'queryFiltroForModule'));
	$isInstance = !empty ($_SESSION ['platInstancia']);

	if (!empty ($forModule)) {
		$result = $adb->pquery ('SELECT * FROM vtiger_custombuttons WHERE module=?', array ($forModule));
	} else if ($isInstance) {
		$result = $adb->query ('SELECT * FROM vtiger_tab WHERE presence IN (0, 2)');
		if (($result) && ($adb->num_rows ($result) > 0)) {
			$moduleNames = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$moduleNames [] = $row ['name'];
			}
			$questionMarks = str_repeat ('?, ', (count ($moduleNames) - 1)) . '?';
			$result = $adb->pquery ("SELECT * FROM vtiger_custombuttons WHERE module IN ({$questionMarks})", $moduleNames);
		} else {
			$result = null;
		}
	} else {
		$result = $adb->query ('SELECT * FROM vtiger_custombuttons');
	}

	$customButtons = array ();
	if (($result) && ($adb->num_rows ($result) > 0)) {
		$availableViews       = CreateCustomButtonHelper::getViewsAvailable ();
		$availableButtonTypes = CreateCustomButtonHelper::getTypesAvailable ();
		while ($row = $adb->fetchByAssoc ($result)) {
			$row ['active']      = $row ['active'] == 1 ? 'Activa' : 'Inactiva';
			$row ['modulelabel'] = getTabIdLabelByName ($row ['module']);
			foreach ($availableViews as $availableView) {
				if ($row ['action'] == $availableView ['name']) {
					$row ['viewlabel'] = $availableView ['label'];
					break;
				}
			}
			foreach ($availableButtonTypes as $availableButtonType) {
				if ($row ['type'] == $availableButtonType ['name']) {
					$row ['typelabel'] = $availableButtonType ['label'];
					break;
				}
			}
			$customButtons[] = $row;
		}
	}

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('CUSTOMBUTTONS', $customButtons);
	$smarty->assign ('IS_INSTANCE', $isInstance);
	$smarty->assign ('MOD', return_module_language ($current_language, 'Settings'));
	$smarty->assign ('MODULE_NAME', $forModule);
	if ((isset ($_SESSION ['error_borrado'])) && ($_SESSION ['error_borrado'] != '')) {
		$smarty->assign ('MSG_ERROR', $_SESSION ['error_borrado']);
		unset ($_SESSION ['error_borrado']);
	}
	$smarty->display ('Settings/customButtons.tpl');

	if (!empty ($formodule)) {
		$_SESSION ['queryFiltroForModule'] = $formodule;
	}
