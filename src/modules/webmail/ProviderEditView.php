<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/webmail/lib/WebmailUtils.class.php');

	global $adb, $app_strings, $current_user, $mod_strings, $theme;

	$smarty = new vtigerCRM_Smarty ();
	if (!is_admin ($current_user)) {
		$smarty->assign ('APP', $app_strings);
		$smarty->assign ('ICON_URL', vtiger_imageurl ('denied.gif', $theme));
		$smarty->display ('AccessDenied.tpl');
		exit ();
	}

	$providerName = PlatzillaUtils::purify ($_GET, 'name');
	if (!empty ($providerName)) {
		$provider = WebmailUtils::getEmailProviderByName ($adb, $providerName);
	} else {
		$provider = null;
	}
	$smarty->assign ('IS_NEW', $provider !== null);
	$smarty->assign ('MOD', $mod_strings);
	if ((isset ($_SESSION ['flashmessage'])) && (!empty ($_SESSION ['flashmessage']['data']))) {
		$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
		$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
		$smarty->assign ('PROVIDER', $_SESSION ['flashmessage']['data']);
		unset ($_SESSION ['flashmessage']);
	} else if (!empty ($providerName)) {
		$smarty->assign ('PROVIDER', $provider);
	}
	$smarty->display ('modules/webmail/ProviderEditView.tpl');
