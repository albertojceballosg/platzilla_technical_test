<?php
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb, $current_user;

	$moduleName      = PlatzillaUtils::purify ($_POST, 'modulename');
	$prefix          = PlatzillaUtils::purify ($_POST, 'prefix');
	$initialSequence = PlatzillaUtils::purify ($_POST, 'initialsequence');
	$currentSequence = PlatzillaUtils::purify ($_POST, 'currentsequence');

	try {
		if (!is_admin ($current_user)) {
			throw new Exception ('Acceso denegado', 401);
		} else if (empty ($moduleName)) {
			throw new Exception ('No has suministrado el nombre del módulo');
		} else if (($prefix === null) || (trim ($prefix) === '')) {
			throw new Exception ('No has suministrado el prefijo');
		} else if (($initialSequence === null) || (trim ($initialSequence) === '')) {
			throw new Exception ('No has suministrado la secuencia inicial');
		} else if (($currentSequence === null) || (trim ($currentSequence) === '')) {
			throw new Exception ('No has suministrado la secuencia actual');
		}

		$result = $adb->pquery ('SELECT * FROM vtiger_modentity_num WHERE semodule=?', array ($moduleName));
		if ($adb->num_rows ($result) == 1) {
			$adb->pquery (
				'UPDATE vtiger_modentity_num SET prefix=?, start_id=?, cur_id=? WHERE semodule=? AND active=?',
				array ($prefix, $initialSequence, $currentSequence, $moduleName, 1)
			);
		} else if ($adb->num_rows ($result) > 1) {
			$adb->pquery ('DELETE FROM vtiger_modentity_num WHERE semodule=?', array ($moduleName));
			$adb->pquery (
				'INSERT INTO vtiger_modentity_num (num_id, semodule, prefix, start_id, cur_id, active) VALUES (?, ?, ?, ?, ?, ?)',
				array ($adb->getUniqueID ('vtiger_modentity_num'), $moduleName, $prefix, $initialSequence, $currentSequence, 1)
			);
		} else {
			$adb->pquery (
				'INSERT INTO vtiger_modentity_num (num_id, semodule, prefix, start_id, cur_id, active) VALUES (?, ?, ?, ?, ?, ?)',
				array ($adb->getUniqueID ('vtiger_modentity_num'), $moduleName, $prefix, $initialSequence, $currentSequence, 1)
			);
		}
		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json');
		echo json_encode ('OK');
	} catch (Exception $e) {
		if ($e->getCode () == 401) {
			header ('HTTP/1.1 401 Access denied');
		} else {
			header ('HTTP/1.1 400 Bad request');
		}
		header ('Content-Type: application/json');
		echo json_encode ($e->getMessage ());
	}
	exit ();
