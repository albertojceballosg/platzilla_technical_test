<?php
	require_once ('include/utils/utils.php');

	global $adb;

	$module = $_REQUEST['modulename'];

	if (!empty($module)) {
		// Módulos especiales que no requieren registro en vtiger_report2module
		$specialModules = array('supplier_part_work');
		if (in_array($module, $specialModules)) {
			echo 'report_active';
		} else {
			$tabid    = getTabid ($module);
			$result   = $adb->pquery ('SELECT * FROM vtiger_report2module WHERE tabid = ? AND active = 1', array ($tabid));
			$num_rows = $adb->num_rows ($result);
			if ($num_rows > 0) {
				echo 'report_active';
			} else {
				echo 'report_inactive';
			}
		}
	} else {
		echo 'ERROR';
	}
	exit();
