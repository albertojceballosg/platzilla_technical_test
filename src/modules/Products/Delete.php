<?php
	require_once ('include/platzilla/Managers/ProductManager.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb, $current_user;

	try {
		if ((!empty ($_SESSION ['platInstancia'])) || (!is_admin ($current_user))) {
			throw new Exception ('Acceso denegado');
		}

		$productId = PlatzillaUtils::purify ($_POST, 'record');
		if (empty ($productId)) {
			throw new Exception ('No has suministrado el ID del producto o servicio a eliminar');
		}

		$product = Product::getInstance ()
			->setId ($productId);
		ProductManager::getInstance ($adb)->deleteProduct ($product);
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'El producto o servicio ha sido eliminado',
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
	}
	header ('Location: index.php?module=Products&action=ListView&parenttab=Settings');
	exit ();
