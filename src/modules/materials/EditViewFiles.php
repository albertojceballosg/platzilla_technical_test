<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/materials/lib/FolderUtils.class.php');

	global $currentModule, $current_user, $platPrincipal, $mod_strings, $theme, $site_URL;

	setBugSnag ($site_URL);

	$fileId   = PlatzillaUtils::purify ($_GET, 'record', null);
	$fileType = PlatzillaUtils::purify ($_GET, 'type', null);

	if (!is_admin ($current_user)) {
		$smarty->assign ('APP', $app_strings);
		$smarty->assign ('ICON_URL', vtiger_imageurl ('denied.gif', $theme));
		$smarty->display ('AccessDenied.tpl');
		exit ();
	}

	$smarty = new vtigerCRM_Smarty ();
	try {
		$folderUtils = FolderUtils::getInstance ($platPrincipal);
		$folders     = $folderUtils->fetchFolders (true);
		if (!empty($folders)) {
			foreach ($folders as $folder) {
				$files [] = $folderUtils->fetchDocuments ('', $folder->getId());
			}
		}

		$smarty->assign ('AVAILABLE_TYPE', FolderInterface::FILE_AVAILABLE_TYPES);
		$smarty->assign ('DEFAULT_PHOTO', FolderInterface::FILE_DEFAULT_IMAGE);
		$smarty->assign ('FEATURED_STATUS',  FolderInterface::FILE_FEATURED_STATUS);
		$smarty->assign ('FILE', $folderUtils->fetchDocumentById ($fileId));
		$smarty->assign ('FILES', (isset ($files)) ? $files : null);
		$smarty->assign ('FOLDERS', $folders);
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('TYPE', $fileType);
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
	$smarty->display ('modules/materials/EditViewFiles.tpl');
