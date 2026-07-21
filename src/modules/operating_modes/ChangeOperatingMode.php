<?php
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/operating_modes/lib/OperatingModesHelper.class.php');

	global $adb, $current_user, $site_URL;

	setBugSnag ($site_URL);

	$selectedMode = PlatzillaUtils::purify ($_POST, 'selectedmode');
	$moduleName   = PlatzillaUtils::purify ($_POST,'flmodule');
	$isInstance   = !empty ($_SESSION ['platInstancia']);
	try {
		if (empty ($moduleName)) {
			throw new Exception ('No se encontró el modulo!');
		}

		if (empty ($selectedMode)) {
			throw new Exception ('Modo de operación no encontrado!');
		}
		
		$isAllowedMde = OperatingModesHelper::getInstance ()->checkAvailableOperatingMode ($adb, $selectedMode);
		if ($isInstance && !$isAllowedMde) {
			throw new Exception ('Modo de operación no disponible!');
		}

		$operatingMode = OperatingModesHelper::getInstance()->updateUserProfile ($adb, $current_user->id, $selectedMode);
		if (empty ($operatingMode)) {
			throw new Exception ('Modo de operación no encontrado!');
		}

		header('Access-Control-Allow-Origin: *');
		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json; charset=utf-8');
		echo json_encode (array ('error' => 'OK', 'btnTitle' => $operatingMode->getLabel (), 'btnClass' => $operatingMode->getAttributes()['btn-class']));
	} catch (Exception $e) {
		header('Access-Control-Allow-Origin: *');
		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json; charset=utf-8');
		echo json_encode (array('error' => $e->getMessage ()));
	}
	exit();
