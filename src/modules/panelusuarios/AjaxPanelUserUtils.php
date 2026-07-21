<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatformUtils.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/panelusuarios/lib/UsersHelper.class.php');
	require_once ('modules/Users/Users.php');
	
	global $adb, $currentModule, $current_user;
	
	$function = PlatzillaUtils::purify ($_POST, 'function');
	
	if ($function == 'ADD_USERS') {
		try {
			$instanceName = !empty ($_SESSION ['platInstancia']) ? $_SESSION ['platInstancia'] : null;
			$totalUsers   = UsersHelper::getTotalUsers ($adb);
			$usersLimit   = UsersHelper::getUsersLimit ($instanceName);
			
			if ((!empty ($instanceName)) && ($usersLimit != -1) && $totalUsers >= $usersLimit) {
				throw new Exception('El plan de suscripción actual no permite añadir más usuarios. \nPor favor actualiza tu plan');
			}
			
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode (array('error' => 'OK'));
		} catch (Exception $e) {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode (array('error' => $e->getMessage ()));
		}
	}
	exit();
