<?php
	require_once ('include/platzilla/Managers/PricebookManager.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb, $current_user;

	try {
		if ((!empty ($_SESSION ['platInstancia'])) || (!is_admin ($current_user))) {
			throw new Exception ('Acceso denegado');
		}

		$pricebookId = PlatzillaUtils::purify ($_POST, 'record');
		if (empty ($pricebookId)) {
			throw new Exception ('No has suministrado el ID de la tarifa');
		}

		$pricebook = Pricebook::getInstance ()
			->setId ($pricebookId);
		PricebookManager::getInstance ($adb)->deletePricebook ($pricebook);
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'La tarifa ha sido eliminada',
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
	}
	header ('Location: index.php?module=Pricebooks&action=ListView&parenttab=Settings');
	exit ();
