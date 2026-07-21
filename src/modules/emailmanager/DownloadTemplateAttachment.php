<?php
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/emailmanager/lib/EmailManagerUtils.class.php');
	// Agregado por EB para integrar BUGSNAG - 20200316
	global $site_URL;
	require_once ('include/bugsnag-php-2.9.2/src/Bugsnag/Autoload.php');
	$bugsnag = new Bugsnag_Client('834d564193a48c47f138dc66d2cf5e83');
	$bugsnag->setAppVersion('1.0.0');
	if ($site_URL == 'https://apphome.platzillatest.com/') {
		$bugsnag->setReleaseStage('https://apphome.platzillatest.com/');
	} else if ($site_URL == 'https://app.platzilla.com/') {
		$bugsnag->setReleaseStage('https://app.platzilla.com/');
	} else {
		$bugsnag->setReleaseStage($site_URL);
	}
	$bugsnag->setErrorReportingLevel(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_WARNING & ~E_DEPRECATED);
	// Agregado por EB para integrar BUGSNAG - 20200316

	global $adb, $current_user;

	try {
		if (!is_admin ($current_user)) {
			throw new Exception ('Acceso denegado');
		}

		$templateId = PlatzillaUtils::purify ($_GET, 'record');
		if (empty ($templateId)) {
			throw new Exception ('No has suministrado el ID de la plantilla');
		}

		$fileName = PlatzillaUtils::purify ($_GET, 'filename');
		if (empty ($fileName)) {
			throw new Exception ('No has suministrado el nombre del archivo');
		}

		$attachmentFilePath = EmailManagerUtils::getTemplateAttachmentFilePath ($adb, $templateId, $fileName, $_SESSION ['plat']);
		if (empty ($attachmentFilePath)) {
			throw new Exception ('No se encuentra registrado el archivo solicitado');
		}

		$platzillaRootFolderPath = PlatzillaUtils::getPlatzillaRootFolderPath ();
		$attachmentFullFilePath = "{$platzillaRootFolderPath}/{$attachmentFilePath}";
		$finfo = finfo_open (FILEINFO_MIME_TYPE);
		$attachmentContentType = finfo_file ($finfo, $attachmentFullFilePath);
		finfo_close ($finfo);

		header ('Pragma: public');
		header ('Expires: 0');
		header ('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header ('Cache-Control: private', false);
		header ("Content-Type: {$attachmentContentType}");
		header ('Content-Disposition: attachment;filename=' . urlencode ($fileName));
		header ('Content-Transfer-Encoding: binary');
		$file = fopen ($attachmentFullFilePath, 'r');
		while ($chunk = fread ($file, 4096)) {
			echo $chunk;
		}
		fclose ($file);
		exit ();
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
		if (empty ($templateId)) {
			header ('Location: index.php?module=emailmanager&action=TemplateListView&parenttab=Settings');
		} else {
			header ("Location: index.php?module=emailmanager&action=TemplateEditView&parenttab=Settings&record={$templateId}");
		}
	}
