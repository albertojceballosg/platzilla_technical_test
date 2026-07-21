<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Managers/ProductManager.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb, $app_strings, $current_user, $mod_strings, $theme;

	$smarty = new vtigerCRM_Smarty ();
	if ((!empty ($_SESSION ['platInstancia'])) || (!is_admin ($current_user))) {
		$smarty->assign ('APP', $app_strings);
		$smarty->assign ('ICON_URL', vtiger_imageurl ('denied.gif', $theme));
		$smarty->display ('AccessDenied.tpl');
		exit ();
	}

	try {
		$productId = PlatzillaUtils::purify ($_GET, 'record');
		if (isset ($_SESSION ['flashmessage']['data'])) {
			$product = Product::getInstance ();
			$product->unserialize ($_SESSION ['flashmessage']['data']);
			unset ($_SESSION ['flashmessage']['data']);
		} else if (!empty ($productId)) {
			$product = ProductManager::getInstance ($adb)->fetchProduct ($productId);
		} else {
			$product = null;
		}

		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('PRODUCT', $product);
		if (isset ($_SESSION ['flashmessage'])) {
			$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
			$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
			unset ($_SESSION ['flashmessage']);
		}
	} catch (Exception $e) {
		$smarty->assign ('IS_ERROR', true);
		$smarty->assign ('MESSAGE', $e->getMessage ());
	}
	$smarty->display ('modules/Products/EditView.tpl');
