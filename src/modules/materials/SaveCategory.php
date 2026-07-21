<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/materials/lib/FolderUtils.class.php');

	global $platPrincipal, $mod_strings, $site_URL;

	setBugSnag ($site_URL);

	$description  = PlatzillaUtils::purify ($_POST, 'categorydescription');
	$categoryName = PlatzillaUtils::purify ($_POST, 'categoryname');
	$record       = PlatzillaUtils::purify ($_POST,'record');
	$status       = PlatzillaUtils::purify ($_POST,'status');
	try {
		if (empty ($categoryName)) {
			throw new Exception ('El nombre del documento requerido!');
		}

		FolderUtils::getInstance ($platPrincipal)->saveCategory (
			Category::getInstance ()
				->setId ($record)
				->setDescription ($description)
				->setName ($categoryName)
				->setStatus($status)
		);

		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => (empty ($record)) ? 'Se ha guardado la categoría' : 'Se ha actualizado la categoría',
		);
		header ("Location: index.php?module=materials&action=EditViewCategory&record={$record}");
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
			'data'    => $newsData,
		);
		header ("Location: index.php?module=materials&action=EditViewCategory&record={$record}");
	}
	exit ();
