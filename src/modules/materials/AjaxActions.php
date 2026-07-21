<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/materials/lib/FolderUtils.class.php');
	require_once ('include/utils/Zip.class.php');

	global $adb, $currentModule, $current_user, $platPrincipal, $mod_strings, $theme, $site_URL;

	setBugSnag ($site_URL);

	$functionName = PlatzillaUtils::purify ($_REQUEST, 'function', null);
	$smarty       = new vtigerCRM_Smarty ();
	$isInstance   = !empty ($_SESSION ['platInstancia']);

	try {
		if (empty ($functionName)) {
			throw new Exception ('Método no definido!');
		} else if ($functionName == 'DOCUMENT_PAGE') {
			$dataRecord = PlatzillaUtils::purify ($_REQUEST, 'recordData', null);
			if(empty($dataRecord)) {
				throw new Exception ('Documento no definido!');
			}
			$dataRecord = explode ('_', $dataRecord);
			$fileId     = intval (array_pop ($dataRecord));
			$fu         = FolderUtils::getInstance ($platPrincipal);
			$file       = $fu->fetchDocumentById ($fileId);
			if (empty($file)) {
				throw new Exception ('El documento ya no esta disponible.');
			} else {
				$numViewed = ($file->getViewed() + 1);
				$fu->updateViewed($file->getId(), $numViewed);
			}
			if (!empty ($file->getRelatedFiles())) {
				foreach ($file->getRelatedFiles() as $fileCod) {
					$relatedFiles [] = $fu->fetchDocumentById ($fileCod);
				}
			}

			$smarty->assign ('DEFAULT_PHOTO', FolderInterface::FILE_DEFAULT_IMAGE);
			$smarty->assign ('FILE', $file);
			$smarty->assign ('FILES', (isset ($relatedFiles)) ? $relatedFiles : null);
			$smarty->assign ('SITE_URL', $site_URL);
			$bufferOut = $smarty->fetch ('Smarty/templates/centaurus/Home/TabsContents/EBook.tpl');
		} else if ($functionName == 'FOLDER_PAGE') {
			$fu    = FolderUtils::getInstance($platPrincipal);
			$menu  = $fu->getDocumentTabMenu ();
			$files = $fu->getLastDocuments ();
			if (empty($files)) {
				throw new Exception ('No hay documentos disponibles');
			}
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('DEFAULT_PHOTO', FolderInterface::FILE_DEFAULT_IMAGE);
			$smarty->assign('DATA_MULTIPLIER', 1);
			$smarty->assign ('FILES', $files);
			$smarty->assign ('SITE_URL', $site_URL);
			$smarty->assign ('IS_INSTANCE', $isInstance);
			$smarty->assign ('MENU', $menu);
			$bufferOut = $smarty->fetch ('Home/TabsContents/Documents.tpl');
		} else if ($functionName == 'DOWNLOAD_DOCUMENT') {
			$fileId = PlatzillaUtils::purify ($_REQUEST, 'record', null);
			if(empty ($fileId)) {
				throw new Exception ('Documento no disponible!');
			}
			$fu   = FolderUtils::getInstance ($platPrincipal);
			$file = $fu->fetchDocumentById ($fileId);
			if (empty($file)) {
				throw new Exception ('El documento ya no esta disponible.');
			}
			$filename     = 'platzilla_' . time() . '_' . date ('Y-m-d');
			$downloadFile = $file->getFolderName () . '/' . $file->getPublicName();
			$zip          = new Zip;
			if (file_exists ($downloadFile)) {
				$fu->setDownloadedFile ($adb, $fileId, $current_user->id);
				$zip->readFile ($downloadFile);
				$zip->download ($filename);
			} else {
				throw new Exception ('El documento ya no esta disponible.');
			}
			$bufferOut = null;
		}

		header('Access-Control-Allow-Origin: *');
		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json; charset=utf-8');
		echo json_encode (array ('error' => 'OK', 'html' => $bufferOut));
	} catch (Exception $e) {
		header('Access-Control-Allow-Origin: *');
		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json; charset=utf-8');
		echo json_encode (array('error' => $e->getMessage ()));
	}
	exit ();
