<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/how_use/lib/HowToUseHelper.php');

	global $adb, $app_strings, $current_user, $mod_strings, $theme, $site_URL;
	setBugSnag ($site_URL);

	$smarty = new vtigerCRM_Smarty ();
	if (!is_admin ($current_user)) {
		$smarty->assign ('APP', $app_strings);
		$smarty->assign ('ICON_URL', vtiger_imageurl ('denied.gif', $theme));
		$smarty->display ('AccessDenied.tpl');
		exit ();
	}

	$record = PlatzillaUtils::purify ($_POST, 'record');

	try {
		if (empty ($record)) {
			throw new Exception ('Tipo de empresa no encontrado!');
		}
		HowToUseHelper::deleteType ($adb, $record);

		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'Se ha eliminado el tipo de empresa!',
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
	}
	header ('Location: index.php?module=how_use&action=ListView&tab=company_type&parenttab=Settings');
