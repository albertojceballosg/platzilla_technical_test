<?php
	require_once ('include/platzilla/Managers/FieldManager.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb, $current_user;

	try {
		if (!is_admin ($current_user)) {
			throw new Exception ('No está autorizado a ejecutar la operación');
		}

		$fieldId    = SettingsUtils::purify ($_REQUEST, 'fieldid');
		$label      = SettingsUtils::purify ($_REQUEST, 'label');
		$isInstance = !empty ($_SESSION ['platInstancia']);

		if (empty ($label)) {
			throw new Exception ('No has suministrado la etiqueta');
		}

		$fm    = FieldManager::getInstance ($adb);
		$field = $fm->fetchFieldById ($fieldId);
		if (empty ($field)) {
			throw new Exception ('El campo suministrado no se encuentra registrado');
		}

		$field->setLabel ($label)->setLocked ($isInstance);
		$fm->updateFieldHeader ($field);
		echo '';
	} catch (Exception $e) {
		echo $e->getMessage ();
	}
	exit ();
