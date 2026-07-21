<?php
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb;
	if (!isset ($adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	$categoryId = SettingsUtils::purify ($_REQUEST, 'record');

	if ($categoryId == 1) {
		$_SESSION ['error_borrado'] = 'La categoría no puede ser eliminada porque contiene los módulos personalizados de clientes';
	} else {
		$result = $adb->pquery (
			'SELECT * FROM vtiger_config_applications WHERE app_category=? OR app_category LIKE ? OR app_category LIKE ? OR app_category LIKE ?',
			array ($categoryId, "{$categoryId}#%", "%#{$categoryId}", "%#{$categoryId}#%")
		);
		if ($adb->num_rows ($result) > 0) {
			$_SESSION ['error_borrado'] = 'La categoría no puede ser eliminada porque tiene aplicaciones asociadas';
		} else {
			$adb->pquery ('DELETE FROM vtiger_category_apps WHERE catappid=?', array ($categoryId));
		}
	}

	header ('Location: index.php?module=Settings&action=CategoryApps&parenttab=Settings');
