<?php
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb;
	if (!isset ($adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	$allowFilters  = isset ($_REQUEST ['permitirFiltrosListas']) ? 1 : 0;
	$moduleName    = SettingsUtils::purify ($_REQUEST, 'fldmodule');
	$registerEvent = isset ($_REQUEST ['registrarEventoAlInsertarRegistro']) ? true : false;

	if (!$moduleName) {
		exit ();
	}
	$adb->pquery ('UPDATE vtiger_tab SET permite_filtros_listas=? WHERE name=?', array ($allowFilters, $moduleName));

	if (!$registerEvent) {
		exit ();
	}
	$adb->pquery ("DELETE FROM vtiger_variables WHERE varname='record_event_cfg' AND tabid=?", array (getTabid ($moduleName)));
	exit ();
