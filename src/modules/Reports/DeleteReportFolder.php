<?php
	require_once ('include/logging.php');
	require_once ('include/database/PearDatabase.php');

	global $adb;
	global $mod_strings;

	/** @noinspection PhpUndefinedClassInspection */
	$local_log = LoggerManager::getLogger ('index');
	$rfid      = vtlib_purify ($_REQUEST['record']);
	if ($rfid != '') {
		$records_in_folder = $adb->pquery ('SELECT * FROM vtiger_report WHERE folderid=?', array ($rfid));
		if ($adb->num_rows ($records_in_folder) > 0) {
			echo getTranslatedString ('LBL_FLDR_NOT_EMPTY', 'Reports');
		} else {
			$sql .= 'DELETE FROM vtiger_reportfolder WHERE folderid=?';
			$result = $adb->pquery ($sql, array ($rfid));
			if ($result != false) {
				$pquery = 'DELETE FROM vtiger_report WHERE folderid=?';
				$res    = $adb->pquery ($pquery, array ($rfid));
				if ($res != '') {
					header ('Location: index.php?action=ReportsAjax&mode=ajax&file=ListView&module=Reports');
				} else {
					require ('modules/Vtiger/header.php');
					echo 'Error while deleting the reports of the folder';
				}
			} else {
				require ('modules/Vtiger/header.php');
				echo 'Error while deleting the folder';
			}
		}
	}
