<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/materials/lib/FolderUtils.class.php');

	global $currentModule, $current_user, $platPrincipal, $mod_strings, $theme, $site_URL;

	setBugSnag ($site_URL);

	$category    = PlatzillaUtils::purify ($_POST, 'category');
	$description = PlatzillaUtils::purify ($_POST, 'description');
	$folderName  = PlatzillaUtils::purify ($_POST, 'name');
	$record      = PlatzillaUtils::purify ($_POST, 'record', null);
	$status      = PlatzillaUtils::purify ($_POST, 'status');
	$video       = PlatzillaUtils::purify ($_POST, 'video_url');
	$myPhoto     = PlatzillaUtils::purify ($_POST, 'myphoto', null);
	$uploadMax   = (PlatzillaUtils::getMaxFileSizeInMb () * (1024 * 1024));

	try {
		if (empty ($folderName)) {
			throw new Exception ('El nombre de la carpeta requerido!');
		}

		if (empty ($description)) {
			throw new Exception ('La descripción de la la carpeta requerida!');
		}
		$fu    = FolderUtils::getInstance ($platPrincipal);
		$photo = $fu->uploadPhoto ($folderName, $uploadMax, 'FOLDER');

		$fu->saveFolder(
			Folder::getInstance ()
				->setId ($record)
				->setDescription($description)
				->setName ($folderName)
				->setVideo ($video)
				->setStatus ($status)
				->setPhoto (!empty ($photo) ? $photo : $myPhoto)
				->setCategory ($category)
		);

		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => (empty ($record)) ? 'Se ha guardado la carpeta' : 'Se ha actualizado la carpeta',
		);
		header ("Location: index.php?module=materials&action=EditViewFolders&record={$record}");
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
			'data'    => $newsData,
		);
		header ("Location: index.php?module=materials&action=EditViewFolders&record={$record}");
	}
	exit ();
