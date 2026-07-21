<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Managers/PricebookManager.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/SystemVariables.class.php');

	global $adb, $app_strings, $current_user, $mod_strings, $theme;

	$smarty = new vtigerCRM_Smarty ();
	if ((!empty ($_SESSION ['platInstancia'])) || (!is_admin ($current_user))) {
		$smarty->assign ('APP', $app_strings);
		$smarty->assign ('ICON_URL', vtiger_imageurl ('denied.gif', $theme));
		$smarty->display ('AccessDenied.tpl');
		exit ();
	}

	try {
		$pricebookId = PlatzillaUtils::purify ($_GET, 'record');
		if (isset ($_SESSION ['flashmessage']['data'])) {
			$pricebook = Pricebook::getInstance ();
			$pricebook->unserialize ($_SESSION ['flashmessage']['data']);
			unset ($_SESSION ['flashmessage']['data']);
		} else if (!empty ($pricebookId)) {
			$pricebook = PricebookManager::getInstance ($adb)->fetchPricebook ($pricebookId);
		} else {
			$pricebook = null;
		}

		$result = $adb->pquery ('SELECT * FROM vtiger_field WHERE tabid IN (SELECT tabid FROM vtiger_tab WHERE name=?)', array ('clientes'));
		$customerFields = array ();
		while ($row = $adb->fetchByAssoc ($result, -1, false)) {
			$customerFields [$row ['fieldname']] = getTranslatedString ($row ['fieldlabel'], 'clientes');
		}
		if ($result instanceof ADORecordSet) {
			$result->Close ();
			unset ($result);
		}

		$smarty->assign ('CUSTOMER_FIELDS', $customerFields);
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('RECORD', $pricebookId);
		$smarty->assign ('PRICEBOOK', $pricebook);
		$smarty->assign ('SYSTEM_VARIABLES', SystemVariables::getAvailableVariables ());
		if (isset ($_SESSION ['flashmessage'])) {
			$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
			$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
			unset ($_SESSION ['flashmessage']);
		}
	} catch (Exception $e) {
		$smarty->assign ('IS_ERROR', true);
		$smarty->assign ('MESSAGE', $e->getMessage ());
	}
	$smarty->display ('modules/Pricebooks/EditView.tpl');
