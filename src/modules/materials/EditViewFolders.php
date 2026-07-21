<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/materials/lib/FolderUtils.class.php');

	global $currentModule, $current_user, $platPrincipal, $mod_strings, $theme, $site_URL;

	setBugSnag ($site_URL);

	$folderId = PlatzillaUtils::purify ($_GET, 'record', null);

	if (!is_admin ($current_user)) {
		$smarty->assign ('APP', $app_strings);
		$smarty->assign ('ICON_URL', vtiger_imageurl ('denied.gif', $theme));
		$smarty->display ('AccessDenied.tpl');
		exit ();
	}

	try {
		$fu               = FolderUtils::getInstance ($platPrincipal);
		$categories       = $fu->fetchCategories (false, true);
		$selectedCategory = null;
		$folderFound      = false;
		if (!empty ($categories)) {
			foreach ($categories as $category) {
				if (empty($category->getFolders())) {
					continue;
				}
				foreach ($category->getFolders() as $folder) {
					if ($folder->getId() == $folderId) {
						$folder->setCategory($category->getId());
						$selectedCategory = $category->getId();
						$folderFound      = true;
						break;
					}
				}
				if ($folderFound) {
					break;
				}
			}
		}
		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('AVAILABLE_STATUS', FolderInterface::FOLDER_AVAILABLE_STATUS);
		$smarty->assign ('CATEGORIES', $categories);
		$smarty->assign ('CATEGORY_SELECTED', $selectedCategory);
		$smarty->assign ('FOLDER', $fu->fetchFolderById ($folderId,true));
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('SITE_URL', $site_URL);
		$smarty->assign ('UPLOAD_MAXSIZE', (PlatzillaUtils::getMaxFileSizeInMb () * 1024 * 1024));
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
	$smarty->display ('modules/materials/EditViewFolders.tpl');
