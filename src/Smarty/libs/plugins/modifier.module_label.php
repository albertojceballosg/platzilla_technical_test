<?php

	/**
	 * Smarty tab label modifier plugin
	 *
	 * Note: This modifier plugin has been created only for Platzilla
	 * Type: modifier
	 * Name: module_pluralize
	 * Purpose: get module label from tabname
	 * Author: Platzilla, Ing. Wilfredo Araujo
	 *
	 * @param string $moduleName
	 * @param PearDatabase $db
	 *
	 * @return string
	 */
	function smarty_modifier_module_label ($moduleName, $adb) {
		if (empty ($moduleName) || !is_string ($moduleName)) {
			return $moduleName;
		}
		$sql    = "SELECT tablabel FROM vtiger_tab WHERE name=?";
		$result = $adb->pquery ($sql, array ($moduleName));
		$label  = $adb->query_result ($result, 0, "tablabel");
		return $label;
	}
