<?php
	require_once ('Smarty_setup.php');
	require_once ('data/CRMEntity.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb, $mod_strings, $theme;
	if (!isset ($adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	$newModuleIds = SettingsUtils::purify ($_POST, 'moduleids');
	$prefixes     = SettingsUtils::purify ($_POST, 'prefixes');
	$startIds     = SettingsUtils::purify ($_POST, 'startids');
	$currentIds   = SettingsUtils::purify ($_POST, 'currentids');

	$result = $adb->query (
		"SELECT
			t.tabid,
			t.name,
			IFNULL(men.prefix, '') AS prefix,
			IFNULL(men.start_id, '') AS start_id,
			IFNULL(men.cur_id, '') AS cur_id
		FROM
			vtiger_tab t
			LEFT JOIN vtiger_modentity_num men ON men.semodule=t.name
		WHERE
			t.isentitytype=1 AND
			t.presence<>-1 AND
			t.tabid IN (SELECT DISTINCT tabid FROM vtiger_field WHERE uitype='4')
		ORDER BY
			t.name"
	);

	if (($result) && ($adb->num_rows ($result) > 0)) {
		$oldModules = array ();
		while ($row = $adb->fetchByAssoc ($result)) {
			$oldModules [ $row ['tabid'] ] = $row;
		}
	} else {
		$oldModules = null;
	}

	if (empty ($newModuleIds)) {
		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('MODULES', $oldModules);
		$smarty->display ('Settings/CustomModEntityNo.tpl');
		exit ();
	}

	$n = count ($newModuleIds);
	for ($i = 0; $i < $n; $i++) {
		$moduleId = $newModuleIds [ $i ];
		if (!isset ($oldModules [ $moduleId ])) {
			continue;
		}
		$moduleName = $oldModules [ $moduleId ]['name'];
		$result     = $adb->pquery ('SELECT men.* FROM vtiger_modentity_num men INNER JOIN vtiger_tab t ON t.name=men.semodule WHERE t.tabid=?', array ($moduleId));
		if ((!$result) || ($adb->num_rows ($result) == 0)) {
			$adb->pquery (
				'INSERT INTO vtiger_modentity_num (num_id, semodule, prefix, start_id, cur_id, active) VALUES ((SELECT id+1 FROM vtiger_modentity_num_seq), ?, ?, ?, ?, ?)',
				array ($moduleName, $prefixes [ $i ], $startIds [ $i ], $currentIds [ $i ], 1)
			);
			$adb->query ('UPDATE vtiger_modentity_num_seq SET id=id+1', true);
		} else {
			$adb->pquery (
				'UPDATE vtiger_modentity_num SET prefix=?, start_id=?, cur_id=? WHERE semodule=?',
				array ($prefixes [ $i ], $startIds [ $i ], $currentIds [ $i ], $moduleName)
			);
		}
	}
	header ('Location: index.php?module=Settings&action=CustomModEntityNo&parenttab=Settings');
