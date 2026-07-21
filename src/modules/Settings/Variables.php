<?php
	require_once ('Smarty_setup.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb;
	if (!isset ($adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	$function      = SettingsUtils::purify ($_REQUEST, 'function');
	$moduleId      = SettingsUtils::purify ($_REQUEST, 'tabid');
	$moduleName    = SettingsUtils::purify ($_REQUEST, 'formodule');
	$variableId    = SettingsUtils::purify ($_REQUEST, 'variableid');
	$variableName  = SettingsUtils::purify ($_REQUEST, 'varname');
	$variableValue = SettingsUtils::purify ($_REQUEST, 'value');

	if ($function == 'save') {
		if (!$variableId) {
			$sql        = 'INSERT INTO vtiger_variables (tabid, varname, value) VALUES (?, ?, ?)';
			$parameters = array ($moduleId, $variableName, $variableValue);
		} else {
			$sql        = 'UPDATE vtiger_variables SET tabid=?, varname=?, value=? WHERE variableid=?';
			$parameters = array ($moduleId, $variableName, $variableValue, $variableId);
		}
		$adb->pquery ($sql, $parameters);
	}

	$variableGroups = array ();
	if (empty ($moduleName)) {
		$sql    = 'SELECT v.*, t.tablabel FROM vtiger_variables v LEFT JOIN vtiger_tab t ON t.tabid=v.tabid ORDER BY v.tabid, v.variableid';
		$result = $adb->query ($sql);
	} else {
		$sql    = 'SELECT v.*, t.tablabel FROM vtiger_variables v LEFT JOIN vtiger_tab t ON t.tabid=v.tabid WHERE t.name IN (?) ORDER BY v.tabid, v.variableid';
		$result = $adb->pquery ($sql, array ($moduleName));
	}
	if (($result) && ($adb->num_rows ($result) > 0)) {
		while ($row = $adb->fetchByAssoc ($result)) {
			if (!isset ($variableGroups [ $row ['tablabel'] ])) {
				$variableGroups [ $row ['tablabel'] ] = array ();
			}
			$variableGroups [ $row ['tablabel'] ][] = $row;
		}
	}

	$modulesData = array ();
	$result      = $adb->query ('SELECT tablabel, tabid, name FROM vtiger_tab WHERE presence=0 ORDER BY tabid');
	if (($result) && ($adb->num_rows ($result) > 0)) {
		while ($row = $adb->fetchByAssoc ($result)) {
			$modulesData [] = $row;
		}
	}

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('VAR_GROUPS', $variableGroups);
	$smarty->assign ('MODULES', $modulesData);
	$smarty->assign ('FOR_MODULE', $moduleName);
	$smarty->display ('Settings/VariablesUI.tpl');
