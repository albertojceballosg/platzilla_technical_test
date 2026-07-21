<?php
	require ('config.inc.php');
	require_once ('include/platzilla/Managers/PlatformManager.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	
	global $application_unique_key, $dbconfig, $platPrincipal;
	
	$function     = PlatzillaUtils::purify ($_REQUEST, 'function');
	$moduleName   = PlatzillaUtils::purify ($_REQUEST, 'flmodule');
	$emailAddress = PlatzillaUtils::purify ($_REQUEST, 'email');
	if ($function == 'CHECK-EMAIL') {
		try {
			$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
			$pm        = PlatformManager::getInstance ($masterAdb, $dbconfig ['db_serverForNewUsers']);
			if ($pm->userHasInstance ($emailAddress)) {
				throw new Exception ('Ya estás registrado en Platzilla', 400);
			}
			
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode (array('error' => 'OK',));
		} catch (Exception $e) {
			header('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode (array('error' => $e->getMessage ()));
		}
	}
	exit();