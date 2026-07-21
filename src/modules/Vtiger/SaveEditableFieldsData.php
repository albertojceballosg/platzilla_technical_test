<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Managers/EditableFieldsManager.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Settings/lib/EditableFieldsHelper.class.php');

	global $adb, $current_user;

	$buttonName = PlatzillaUtils::purify ($_POST, 'buttonname');
	$moduleName = PlatzillaUtils::purify ($_POST, 'formodule');
	$recordId   = PlatzillaUtils::purify ($_POST, 'record');
	$isInstance = !empty ($_SESSION ['platInstancia']);

	try {
		if (empty($buttonName)) {
			throw new Exception ('Conjunto de campos no identificados...');
		}

		$button = EditableFieldsManager::getInstance($adb)->fetchEditableButtom($buttonName, false);
		if (empty($button)) {
			throw new Exception ('Conjunto de campos no identificados...');
		}

		$fieldData = array ();
		foreach ($button->getEditableFields() as $field) {
			$fieldData[ $field->getFieldName () ] = PlatzillaUtils::purify ($_POST, $field->getFieldName ());
		}

		$results = EditableFieldsHelper::saveDataFronListView($adb, $current_user->id, $button, $fieldData, $recordId);

		if ($results != 'ok') {
			throw new Exception($results);
		}

		header('Access-Control-Allow-Origin: *');
		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json; charset=utf-8');
		echo json_encode (array ('error' => 'OK'));
	} catch (Exception $e) {
		header('Access-Control-Allow-Origin: *');
		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json; charset=utf-8');
		echo json_encode (array('error' => $e->getMessage ()));
	}
	exit ();
