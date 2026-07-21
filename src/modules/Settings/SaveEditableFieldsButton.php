<?php
	require_once ('include/platzilla/Managers/EditableFieldsManager.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Settings/lib/EditableFieldsHelper.class.php');

	global $adb, $current_user;

	$buttonLabel  = PlatzillaUtils::purify ($_POST, 'buttonlabel');
	$buttonName   = PlatzillaUtils::purify ($_POST, 'buttonname');
	$description  = PlatzillaUtils::purify ($_POST, 'description');
	$fields       = PlatzillaUtils::purify ($_POST, 'fields');
	$moduleName   = PlatzillaUtils::purify ($_POST, 'formodule');
	$buttonStatus = PlatzillaUtils::purify ($_POST, 'status');
	$isInstance   = !empty ($_SESSION ['platInstancia']);

	try {
		if (!is_admin ($current_user)) {
			throw new Exception ('Acceso denegado', 401);
		}

		if (empty($fields)) {
			throw new Exception ("No ha seleccionado los campos a editar con el {$buttonLabel}");
		}
		if (empty($buttonName)) {
			$label      = (empty($buttonLabel)) ? 'BUTTON-EDITABLE' : $buttonLabel;
			$buttonName = EditableFieldsHelper::getEditableFieldButtonName ($label);
		}

		$fm = FieldManager::getInstance ($adb);

		foreach ($fields as $field) {
			if (empty ($field)) {
				continue;
			}
			$objField = $fm->fetchFieldByName ($moduleName, $field);
			if (empty ($objField)) {
				continue;
			}
			$editableFilds [] = EditableFieldsField::getInstance ()
				->setButtonName ($buttonName)
				->setFieldName ($objField->getName ())
				->setFieldLabel ($objField->getLabel ());
		}

		$editableButton = EditableFieldsButton::getInstance()
			->setName ($buttonName)
			->setLabel ($buttonLabel)
			->setDescription ($description)
			->setModuleName($moduleName)
			->setStatus (($buttonStatus == 1))
			->setLocked ($isInstance)
			->setEditableFields (isset ($editableFilds) ? $editableFilds : null);

		$buttonSaved = EditableFieldsManager::getInstance ($adb)->saveEditableFieldButton ($editableButton);

		if (empty ($buttonSaved)) {
			throw new Exception ('Se ha presentado un error. Intenta más tarde');
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
