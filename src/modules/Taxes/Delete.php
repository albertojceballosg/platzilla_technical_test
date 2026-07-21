<?php
	require_once ('include/platzilla/Managers/TaxManager.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb, $current_user;

	try {
		if ((!empty ($_SESSION ['platInstancia'])) || (!is_admin ($current_user))) {
			throw new Exception ('Acceso denegado');
		}

		$taxId = PlatzillaUtils::purify ($_POST, 'record');
		if (empty ($taxId)) {
			throw new Exception ('No has suministrado el ID del impuesto');
		}

		$tax = Tax::getInstance ()
			->setId ($taxId);
		TaxManager::getInstance ($adb)->deleteTax ($tax);
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'El impuesto ha sido eliminado',
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
	}
	header ('Location: index.php?module=Taxes&action=ListView&parenttab=Settings');
	exit ();
