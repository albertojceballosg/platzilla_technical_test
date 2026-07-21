<?php

	/**
	 * Smarty tab entity id modifier plugin
	 *
	 * Note: This modifier plugin has been created only for Platzilla
	 * Type: modifier
	 * Name: module_pluralize
	 * Purpose: get crmentity id from case number
	 *
	 * @param string $moduleName
	 * @param PearDatabase $db
	 * @param string $caseNumber
	 *
	 * @return integer|string
	 */
	function smarty_modifier_crmentity_id ($moduleName, $caseNumber, $adb) {
		if (
			empty ($moduleName) || !is_string ($moduleName) ||
			empty ($caseNumber) || !is_string ($caseNumber)
		) {
			return '';
		}
		
		$sql    = "SELECT crmid FROM vtiger_crmentity WHERE setype=? AND deleted=? AND case_number=?";
		$result = $adb->pquery ($sql, array ($moduleName, 0, $caseNumber));
		$crmId  = $adb->query_result ($result, 0, "crmid");
		return $crmId;
	}
