<?php
	require_once ('include/platzilla/Managers/ProductManager.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb, $current_user;

	try {
		if ((!empty ($_SESSION ['platInstancia'])) || (!is_admin ($current_user))) {
			throw new Exception ('Acceso denegado');
		}

		$basePrice   = PlatzillaUtils::purify ($_POST, 'baseprice');
		$productId   = PlatzillaUtils::purify ($_POST, 'record');
		$productName = PlatzillaUtils::purify ($_POST, 'productname');
		$type        = PlatzillaUtils::purify ($_POST, 'type');

		$pm = ProductManager::getInstance ($adb);
		if (!empty ($productId)) {
			$product = $pm->fetchProduct ($productId);
		} else {
			$product = Product::getInstance ();
		}
		if (empty ($product)) {
			throw new Exception ('El producto o servicio suministrado no está registrado');
		}

		$product->setBasePrice ($basePrice)
			->setId ($productId)
			->setName ($productName)
			->setType ($type);
		$pm->saveProduct ($product);
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'El producto o servicio ha sido guardado',
		);
		header ('Location: index.php?module=Products&action=ListView&parenttab=Settings');
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
			'data'    => !empty ($product) ? $product->serialize () : null,
		);
		$recordUriPart             = !empty ($productId) ? "&record={$productId}" : '';
		header ("Location: index.php?module=Products&action=EditView{$recordUriPart}&parenttab=Settings");
	}
	exit ();
