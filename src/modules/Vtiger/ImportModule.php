<?php
	set_time_limit (0);

	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/CsvFileImporter.class.php');
	require_once ('include/utils/XlsxFileImporter.class.php');

	global $adb, $currentModule, $current_user, $mod_strings, $upload_badext;

	$method = $_SERVER ['REQUEST_METHOD'];
	if ($method == 'POST') {
		$importFiles = PlatzillaUtils::purify ($_POST, 'importfile');

		try {
			if (empty ($importFiles)) {
				throw new Exception ('No has suministrado los archivos a importar');
			}

			foreach ($importFiles as $index => $importFile) {
				$tempFilePath = tempnam ('/tmp', 'import-');
				$fileType     = substr ($importFile ['data'], (strpos ($importFile ['data'], 'data:') + 5), (strpos ($importFile ['data'], ';base64,') - 5));
				$fileContents = base64_decode (str_replace (' ', '+', substr ($importFile ['data'], (strpos ($importFile ['data'], 'base64,') + 7))));
				file_put_contents ($tempFilePath, $fileContents);
				$platzillaRootFolderPath = PlatzillaUtils::getPlatzillaRootFolderPath ();
				$moduleFilePath          = "modules/{$currentModule}/{$currentModule}.php";
				if (!file_exists ("{$platzillaRootFolderPath}/{$moduleFilePath}")) {
					throw new Exception ("El módulo {$currentModule} no está registrado");
				}

				if ($fileType === 'text/csv') {
					CsvFileImporter::getInstance ($adb)->import ($tempFilePath, $currentModule, $current_user->id);
				} else {
					XlsxFileImporter::getInstance ($adb)->import ($tempFilePath, $currentModule, $current_user);
				}
			}
			$_SESSION ['flashmessage'] = array (
				'iserror' => false,
				'message' => 'Se importaron los registros del archivo suministrado',
			);
		} catch (Exception $e) {
			$_SESSION ['flashmessage'] = array (
				'iserror' => true,
				'message' => $e->getMessage (),
			);
		}
		header ("Location: index.php?module={$currentModule}&action=index");
		exit ();
	} else {
		$smarty = new vtigerCRM_Smarty();
		$smarty->assign ('MAX_FILE_SIZE', PlatzillaUtils::getMaxFileSizeInMb ());
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('MODULE_NAME', $currentModule);
		$smarty->display ('ImportModule.tpl');
	}
