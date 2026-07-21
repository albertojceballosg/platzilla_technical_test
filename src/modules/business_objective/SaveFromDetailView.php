<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Managers/EditableFieldsManager.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Settings/lib/EditableFieldsHelper.class.php');

	global $adb, $current_user;

	$fieldName  = PlatzillaUtils::purify ($_POST, 'name');
	$fieldValue = PlatzillaUtils::purify ($_POST, 'value');
	$recordId   = PlatzillaUtils::purify ($_GET, 'record');
	$isInstance = !empty ($_SESSION ['platInstancia']);

	try {
		if (empty($fieldName)) {
			throw new Exception ('Campo no identificado!');
		}

		$field = FieldManager::getInstance ($adb)->fetchFieldByName ($currentModule, $fieldName, true);
		if (empty ($field)) {
			throw new Exception ('Campo no encontrado!');
		}
		$fieldData [$fieldName] = $fieldValue;
		$results                = EditableFieldsHelper::saveDataFromDetailView ($adb, $current_user->id, $field, $fieldData, $recordId);

		if ($results != 'ok') {
			throw new Exception($results);
		}
	} catch (Exception $e) {
		header ('HTTP/1.1 400 Bad request');
		header ('Content-Type: application/json');
		header ('Content-Type: application/json; charset=utf-8');
		echo $e->getMessage ();
	}
	exit ();
