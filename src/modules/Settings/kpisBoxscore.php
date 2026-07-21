<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb, $current_language;
	if (!isset ($adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	$forModule = SettingsUtils::purify ($_REQUEST, 'formodule', SettingsUtils::purify ($_SESSION, 'queryFiltroForModule'));

	$sql = 'SELECT * FROM vtiger_kpisboxscore';
	if (!empty ($forModule)) {
		$sql .= ' WHERE module=?';
		$result = $adb->pquery ($sql, array ($forModule));
	} else {
		$result = $adb->query ($sql);
	}

	$kpis = array ();
	if (($result) && ($adb->num_rows ($result) > 0)) {
		$availableViews       = getVistasDisponiblesParaBotonesPersonalizados ();
		$availableButtonTypes = getTiposDisponiblesParaBotonesPersonalizados ();
		while ($row = $adb->fetchByAssoc ($result)) {
			$row['active']      = ($row['active'] == 1) ? 'Activa' : 'Inactiva';
			$row['modulelabel'] = getTabIdLabelByName ($row['module']);
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
			$kpis [] = $row;
		}
	}

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('KPIS', $kpis);
	$smarty->assign ('MOD', return_module_language ($current_language, 'Settings'));
	if ((isset ($_SESSION ['error_borrado'])) && ($_SESSION ['error_borrado'] != '')) {
		$smarty->assign ('MSG_ERROR', $_SESSION ['error_borrado']);
		unset ($_SESSION ['error_borrado']);
	}
	$smarty->display ('Settings/kpisBoxscore.tpl');
