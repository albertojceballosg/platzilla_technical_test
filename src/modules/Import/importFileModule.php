﻿<?php
	set_time_limit (600);
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/CsvFileImporter.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/XlsxFileImporter.class.php');
	require_once ('include/utils/utils.php');

	global $adb, $app_strings, $current_user, $upload_badext;

	$moduleName = PlatzillaUtils::purify ($_REQUEST, 'return_module');

	try {
		if ((empty ($_FILES ['import_file']['name'])) || ($_FILES ['import_file']['size'] <= 0) || (!empty ($_FILES ['import_file']['error']))) {
			throw new Exception ('El archivo suministrado no es válido');
		}

		$uploadedFileName = sanitizeUploadFileName ($_FILES ['import_file']['name'], $upload_badext);
		$uploadedFileType = $_FILES ['import_file']['type'];
		$uploadedFileSize = $_FILES ['import_file']['size'];
		$uploadedFilePath = $_FILES ['import_file']['tmp_name'];
		$uploadFolderPath = decideFilePath ();
		$newFilePath      = "{$newFilePath}{$current_user->id}_{$uploadedFileName}";
		$uploadStatus     = move_uploaded_file ($uploadedFilePath, $newFilePath);

		if (!$uploadStatus) {
			throw new Exception ('Se ha presentado un error al recibir el archivo. Intenta nuevamente');
		}

		$moduleFilePath = "modules/{$moduleName}/{$moduleName}.php";
		if (!file_exists (__DIR__ . "/../../{$moduleFilePath}")) {
			throw new Exception ("El módulo {$moduleName} no está registrado");
		}

		if ($uploadedFileType === 'text/csv') {
			CsvFileImporter::getInstance ($adb)->import ($newFilePath, $moduleName, $current_user->id);
		} else {
			XlsxFileImporter::getInstance ($adb)->import ($newFilePath, $moduleName, $current_user->id);
		}
	} catch (Exception $e) {
		if ((!empty ($newFilePath)) && (file_exists ($newFilePath))) {
			unlink ($newFilePath);
		}
		$errorMessage = $e->getMessage ();
	}
	$smarty = new vtigerCRM_Smarty();
	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('MODULE', $moduleName);
	$smarty->assign ('ERROR_MESS', isset ($errorMessage) ? $errorMessage : null);
	$smarty->display ('modules/Import/ImportTemplate2.tpl');
