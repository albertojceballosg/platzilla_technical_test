<?php
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb;

	$fieldName  = SettingsUtils::purify ($_REQUEST, 'campo');
	$moduleName = SettingsUtils::purify ($_REQUEST, 'fldmodule');

	try {
		if (empty ($fieldName)) {
			throw new Exception ('No se ha suministrado el nombre del campo');
		}
		if (empty ($moduleName)) {
			throw new Exception ('No se ha suministrado el nombre del módulo');
		}

		$result = $adb->pquery (
			'SELECT
			f.*
		FROM
			vtiger_field f
			INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=?
		WHERE
			f.fieldname=?',
			array ($moduleName, $fieldName)
		);
		if ((!$result) || ($adb->num_rows ($result) == 0)) {
			throw new Exception ("No se encuentra registrado el campo {$fieldName} para el módulo {$moduleName}");
		}

		$row = $adb->fetchByAssoc ($result, -1, false);
		$typeOfData = explode ('~', $row ['typeofdata']);
		if ($typeOfData [1] == 'M') {
			$typeOfData [1] = 'O';
		} else {
			$typeOfData [1] = 'M';
		}

		$adb->pquery (
			'UPDATE
			 	vtiger_field f
			 	INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=?
			 SET
			 	f.typeofdata=?
			 WHERE
			 	f.fieldname=?',
			array ($moduleName, join ('~', $typeOfData), $fieldName)
		);

		$response = $typeOfData [1];
	} catch (Exception $ignored) {
		$response = $ignored->getMessage ();
	}
	echo $response;
	exit ();

