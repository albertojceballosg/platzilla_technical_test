<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/materials/lib/FolderUtils.class.php');

	global $currentModule, $current_user, $platPrincipal, $mod_strings, $theme, $site_URL;

	setBugSnag ($site_URL);

	$description  = PlatzillaUtils::purify ($_POST, 'filedescription');
	$fileName     = PlatzillaUtils::purify ($_POST, 'filename');
	$featured     = PlatzillaUtils::purify ($_POST,'featured');
	$publicName   = PlatzillaUtils::purify ($_POST, 'publicname');
	$folderId     = PlatzillaUtils::purify ($_POST, 'folderid');
	$record       = PlatzillaUtils::purify ($_POST, 'record', null);
	$relatedFiles = PlatzillaUtils::purify ($_POST,'relatedfiles');
	$type         = PlatzillaUtils::purify ($_POST, 'type');
	$url          = PlatzillaUtils::purify ($_POST, 'fileurl');
	$urlPublic    = PlatzillaUtils::purify ($_POST,'urlInblog');
	$myPhoto      = PlatzillaUtils::purify ($_POST, 'myphoto', null);
	$photoType    = PlatzillaUtils::purify ($_POST, 'imageType', null);
	$uploadMax    = (PlatzillaUtils::getMaxFileSizeInMb () * (1024 * 1024));
	try {
		if (empty ($fileName)) {
			throw new Exception ('El nombre del documento requerido!');
		}

		if (empty ($folderId)) {
			throw new Exception ('La carpeta es requerida!');
		}

		if (empty ($url)) {
			throw new Exception ('La url del documento es requerida!');
		}

		$fu    = FolderUtils::getInstance ($platPrincipal);
		$photo = $fu->getImageEBook ($uploadMax);
			$fu->saveFile (
				Document::getInstance ()
					->setId ($record)
					->setDescription ($description)
					->setFeatured ($featured)
					->setName ($fileName)
					->setPublicName($publicName)
					->setPhoto (!empty ($photo) ? $photo : $myPhoto)
					->setPhotoType ($photoType)
					->setFolderId ($folderId)
					->setRelatedFiles ($relatedFiles)
					->setType ($type)
					->setUrl ($url)
					->setUrlPublic ($urlPublic)
			);

		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => (empty ($record)) ? 'Se ha guardado el documento' : 'Se ha actualizado el documento',
		);
		header ("Location: index.php?module=materials&action=EditViewFiles&record={$record}");
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
			'data'    => $newsData,
		);
		header ("Location: index.php?module=materials&action=EditViewFiles&record={$record}");
	}
	exit ();
