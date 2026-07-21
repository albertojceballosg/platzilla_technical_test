<?php
	require_once ('modules/Settings/lib/ConfigApplicationsHelper.class.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb, $current_user, $root_directory;
	if (!isset ($adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	$binFile       = SettingsUtils::purify ($_FILES, 'binFile');
	$category      = SettingsUtils::purify ($_REQUEST, 'category');
	$code          = SettingsUtils::purify ($_REQUEST, 'code');
	$description   = SettingsUtils::purify ($_REQUEST, 'descripcion');
	$hiddenBinFile = SettingsUtils::purify ($_REQUEST, 'binFile_hidden');
	$recordId      = SettingsUtils::purify ($_REQUEST, 'record');
	$mode          = SettingsUtils::purify ($_REQUEST, 'mode');
	$modules       = SettingsUtils::purify ($_REQUEST, 'nestable-output');
	$name          = SettingsUtils::purify ($_REQUEST, 'name');
	$price         = SettingsUtils::purify ($_REQUEST, 'price');
	$status        = SettingsUtils::purify ($_REQUEST, 'status');
	$url           = SettingsUtils::purify ($_REQUEST, 'url');

	try {
		$imagesDirectoryPath = __DIR__ . '/../../storage/appsimages';
		$arguments           = array (
			'id'          => $recordId,
			'category'    => $category,
			'code'        => $code,
			'description' => $description,
			'image'       => file_exists ("{$imagesDirectoryPath}/{$code}.png") ? 1 : 0,
			'modules'     => $modules,
			'name'        => $name,
			'price'       => $price,
			'status'      => $status,
			'url'         => $url,
		);
		ConfigApplicationsHelper::validateArguments ($adb, $arguments);
		$fileData = ConfigApplicationsHelper::extractFileData ($binFile, $hiddenBinFile);
		if ($mode == 'edit') {
			ConfigApplicationsHelper::updateApplication ($adb, $arguments, $fileData);
		} else {
			ConfigApplicationsHelper::createApplication ($adb, $arguments, $fileData);
		}
		header ('Location: index.php?parenttab=Settings&module=Settings&action=ConfigApps');
	} catch (Exception $e) {
		$modules              = json_decode ($modules, true);
		$applicationModuleIds = array ();
		foreach ($modules as $module) {
			$applicationModuleIds [] = $module ['id'];
		}
		$_SESSION ['application-data']  = array (
			'id'            => $recordId,
			'category'      => $category,
			'code'          => $code,
			'description'   => $description,
			'image'         => file_exists ("{$imagesDirectoryPath}/{$code}.png") ? 1 : 0,
			'hiddenbinfile' => $hiddenBinFile,
			'modules'       => $applicationModuleIds,
			'name'          => $name,
			'price'         => $price,
			'status'        => $status,
			'url'           => $url,
		);
		$_SESSION ['application-error'] = $e->getMessage ();
		if ($mode == 'edit') {
			$action        = 'EditApps';
			$recordUrlPart = "&record={$recordId}";
		} else {
			$action        = 'CreateApp';
			$recordUrlPart = '';
		}
		header ("Location: index.php?module=Settings&action={$action}&parenttab=Settings{$recordUrlPart}");
	}
