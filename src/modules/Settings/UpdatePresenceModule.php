<?php
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/store/lib/StoreUtils.class.php');

	global $adb, $current_user;

	$appCode  = PlatzillaUtils::purify ($_POST, 'appcod');
	$tabName  = PlatzillaUtils::purify ($_POST, 'tabname');
	$presence = PlatzillaUtils::purify ($_POST, 'presence');

	$isInstance   = !empty ($_SESSION ['platInstancia']) ? true : false;

	try {
		if (!is_admin ($current_user) || (!$isInstance)) {
			throw new Exception ('Acceso denegado', 401);
		}

		if (empty($tabName)) {
			throw new Exception ('Error en la identificación del módulo!');
		}

		if (empty($appCode)) {
			throw new Exception ('Error en la identificación de la aplicación!');
		}

		$response = StoreUtils::updatePresenceModules ($adb, $tabName, $appCode, (($presence == -1) ? 0 : -1));

		if (empty ($response)) {
			throw new Exception ('Se ha presentado un error. Intenta más tarde');
		}
		create_parenttab_data_file();

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
	exit ();
