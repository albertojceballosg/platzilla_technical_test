<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/materials/lib/FolderUtils.class.php');

	global $adb, $app_strings, $current_user, $mod_strings, $theme, $platPrincipal, $site_URL;

	setBugSnag ($site_URL);

	$fileId = PlatzillaUtils::purify ($_POST, 'record', null);
	$smarty = new vtigerCRM_Smarty ();
	try {
		if (!is_admin ($current_user)) {
			throw new Exception ('No autorizado para realizar esta operación!');
		}

		if (empty ($fileId)) {
			throw new Exception ('Documento no identificado');
		}

		FolderUtils::getInstance ($platPrincipal)->deleteDocument ($fileId);

		header('Access-Control-Allow-Origin: *');
		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json; charset=utf-8');
		echo json_encode (array ('error' => 'OK'));
	} catch (Exception $e) {
		header('Access-Control-Allow-Origin: *');
		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json; charset=utf-8');
		echo json_encode (array('error' => $e->getMessage ()));
	}
	exit();
