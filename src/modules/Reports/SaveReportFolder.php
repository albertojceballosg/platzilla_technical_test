<?php
	require_once ('modules/Reports/Reports.php');
	require_once ('include/logging.php');
	require_once ('include/database/PearDatabase.php');

	global $adb, $default_charset;

	$local_log = LoggerManager::getLogger ('index');

	$focus     = new Reports();
	$rfid       = vtlib_purify ($_REQUEST['record']);
	$mode       = vtlib_purify ($_REQUEST['savemode']);
	$foldername = vtlib_purify ($_REQUEST['foldername']);
	$foldername = function_exists ('iconv') ? iconv ('UTF-8', $default_charset, $foldername) : $foldername;
	$folderdesc = vtlib_purify ($_REQUEST['folderdesc']);
	$foldername = str_replace ('*amp*', '&', $foldername);
	$folderdesc = str_replace ('*amp*', '&', $folderdesc);

	$isProtected = !empty ($_SESSION ['platInstancia']) ? false : true;
	if ($mode == 'Save') {
		if ($rfid == '') {
			$sql        = 'INSERT INTO vtiger_reportfolder (FOLDERNAME,DESCRIPTION,STATE, protected) VALUES (?,?,?,?)';
			$sql_params = array (trim ($foldername), $folderdesc, 'SAVED', $isProtected);
			/** @noinspection PhpUndefinedMethodInspection */
			$result = $adb->pquery ($sql, $sql_params);
			if ($result != false) {
				header ('Location: index.php?action=ReportsAjax&file=ListView&mode=ajax&module=Reports');
			} else {
				require ('modules/Vtiger/header.php');
				echo 'Error while inserting the record';
			}
		}
	} else if ($mode == 'Edit') {
		if ($rfid != '') {
			$sql    = 'UPDATE vtiger_reportfolder SET FOLDERNAME=?, DESCRIPTION=?, protected=? WHERE folderid=?';
			$params = array (trim ($foldername), $folderdesc, $isProtected, $rfid);
			/** @noinspection PhpUndefinedMethodInspection */
			$result = $adb->pquery ($sql, $params);
			if ($result != false) {
				header ('Location: index.php?action=ReportsAjax&file=ListView&mode=ajax&module=Reports');
			} else {
				require ('modules/Vtiger/header.php');
				echo 'Error while inserting the record';
			}
		}
	}
