<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/materials/lib/FolderUtils.class.php');

	global $currentModule, $platPrincipal, $mod_strings;

	setBugSnag ($site_URL);

	$selectedTab = PlatzillaUtils::purify ($_GET, 'tab', 'folder-tab');
	$folderId    = PlatzillaUtils::purify ($_POST, 'folderid', null);

	$smarty = new vtigerCRM_Smarty ();
	try {
		$fu = FolderUtils::getInstance ($platPrincipal);
		$smarty->assign ('AVAILABLE_STATUS', FolderInterface::FOLDER_AVAILABLE_STATUS);
		$smarty->assign ('AVAILABLE_TYPE', FolderInterface::FILE_AVAILABLE_TYPES);
		$smarty->assign ('CATEGORY_STATUS',  FolderInterface::FILE_CATEGORY_STATUS);
		$smarty->assign ('CATEGORIES', $fu->fetchCategories(true));
		$smarty->assign ('FEATURED_STATUS',  FolderInterface::FILE_FEATURED_STATUS);
		$smarty->assign ('FOLDERS', $fu->fetchFolders());
		$smarty->assign ('FOLDER_ID', $folderId);
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('SELECTED_TAB', (empty ($folderId)) ? $selectedTab : 'files-tab');
		$smarty->assign ('SITE_URL', "{$site_URL}index.php?module=store&action=BulletinBoard&id=");
		if (isset ($_SESSION ['flashmessage'])) {
			$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
			$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
			unset ($_SESSION ['flashmessage']);
		}
		$smarty->display ('modules/materials/ListView.tpl');
	} catch (Exception $e) {
		$smarty->assign ('LABEL', 'Volver');
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->assign ('URL', "index.php?module=News&action=ListView&parenttab=Settings&return_module={$returnModule}&return_action={$returnAction}");
		$smarty->display ('Message.tpl');
	}
