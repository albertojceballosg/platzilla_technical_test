<?php
/**
 * Alternative entrance for common actions that affects any module without adding it to its DetailViewAjax
 * Use example: index.php?modules=Accounts&action=AccountsAjax&file=DetailViewAjax&commonquery&ajxaction=MY_CUSTOM_FUNCTION
 * @author Etienne Gómez (EGC)
 * @copyright Copyright (c) 2013, Timemanagement_
 * @version 1.0 17/11/2013 02:46:32
 * @filesource
 */
global $currentModule, $adb;

$ajxaction = $_REQUEST['ajxaction'];

/**
 * Get progress value and color via ajax call
 */
if ($ajxaction == 'GET_PROGRESS') {
	$progress = getProgressBarValue($_REQUEST['fieldname'], null, $_REQUEST['record'], $_REQUEST['module']);
	$color = getProgressColor($progress);
	
	echo json_encode(array('progress' => round($progress*100), 'color' => $color));
}

/**
 * Get the a field an value to update on the DetailView of a module, according to the todotasks block on that module.
 */
if ($ajxaction == 'GET_TASKS_STATUS_FIELD') {
	$result = $adb->pquery("select vtiger_field.tablename, vtiger_field.columnname, vtiger_field.fieldname, fieldlabel from vtiger_field
		inner join vtiger_blocks_properties on (fieldname=update_parentfield)
		INNER JOIN vtiger_tab USING(tabid)
		where vtiger_tab.name=?",
		array($_REQUEST['module']));
	
	list($tablename, $columnname, $fieldname, $fieldlabel) = $adb->fetch_row($result);
	$value = '';
	
	if ($fieldname && $columnname && $tablename) {
		$focus = CRMEntity::getInstance($_REQUEST['module']);
		$focus->retrieve_entity_info($_REQUEST['record'], $_REQUEST['module']);
		$value = $focus->column_fields[$fieldname];
	}
	
	echo json_encode(array('fieldlabel' => $fieldlabel, 'value' => $value));
}
?>