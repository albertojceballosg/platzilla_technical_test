<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/materials/lib/FolderUtils.class.php');

	global $currentModule, $current_user, $platPrincipal, $mod_strings, $theme, $site_URL;

	setBugSnag ($site_URL);

	$categoryId = PlatzillaUtils::purify ($_GET, 'record', null);

	if (!is_admin ($current_user)) {
		$smarty->assign ('APP', $app_strings);
		$smarty->assign ('ICON_URL', vtiger_imageurl ('denied.gif', $theme));
		$smarty->display ('AccessDenied.tpl');
		exit ();
	}

	$smarty = new vtigerCRM_Smarty ();
	try {
		$folderUtils = FolderUtils::getInstance ($platPrincipal);

		$smarty->assign ('CATEGORY_STATUS',  FolderInterface::FILE_CATEGORY_STATUS);
		$smarty->assign ('CATEGORY', $folderUtils->fetchCategoryById ($categoryId, true, true));
		$smarty->assign ('MOD', $mod_strings);
		if (isset ($_SESSION ['flashmessage'])) {
			$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
			$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
			unset ($_SESSION ['flashmessage']);
		}
	} catch (Exception $e) {
		$smarty->assign ('LABEL', 'Volver');
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->assign ('URL', 'index.php?module=materials&action=ListView&parenttab=Settings');
		$smarty->display ('Message.tpl');
	}
	$smarty->display ('modules/materials/EditViewCategory.tpl');
