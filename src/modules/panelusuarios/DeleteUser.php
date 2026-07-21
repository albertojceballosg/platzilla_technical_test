<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/panelusuarios/lib/UsersHelper.class.php');
	require_once ('modules/Users/Users.php');

	global $adb, $current_user;

	$userId         = PlatzillaUtils::purify ($_POST, 'record');
	$assignedUserId = PlatzillaUtils::purify ($_POST,'assigned_user_id');
	$instanceName   = !empty ($_SESSION ['platInstancia']) ? $_SESSION ['platInstancia'] : null;

	$smarty = new vtigerCRM_Smarty ();
	if (!is_admin ($current_user)) {
		$smarty->assign ('IS_ADMIN', false);
		$smarty->display ('AccessDenied.tpl');
		exit ();
	}

	try {
		$result = $adb->pquery ('SELECT * FROM vtiger_users WHERE id=?', array ($userId));
		if ((!$result) || ($adb->num_rows ($result) == 0)) {
			throw new Exception ('El usuario solicitado no se encuentra registrado');
		}

		/** @var Users|stdClass $user */
		$user = new Users ();
		$user->retrieve_entity_info ($userId, 'Users');

		if (is_admin ($user)) {
			throw new Exception ('No está permitido eliminar el usuario administrador');
		}

		$userName = $user->column_fields ['user_name'];

		if (!empty ($instanceName)) {
			$totalUsers = UsersHelper::getTotalUsers ($adb);
			$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
			$result    = $masterAdb->pquery ('SELECT instanceid FROM vtiger_instances WHERE code=?', array ($instanceName));
			if (($result) && ($masterAdb->num_rows ($result) > 0)) {
				$row        = $masterAdb->fetchByAssoc ($result, -1, false);
				$instanceId = $row ['instanceid'];
				$masterAdb->pquery ('DELETE FROM vtiger_instanceusers WHERE instancecode=? AND username=?', array ($instanceName, $userName));
				$masterAdb->pquery ('UPDATE vtiger_instances SET activeusers=? WHERE instanceid=?', array (($totalUsers - 1), $instanceId));
			}
		}
		$user->transformOwnerShipAndDelete ($userId, $assignedUserId);

		header ('Access-Control-Allow-Origin: *');
		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json; charset=utf-8');
		echo json_encode(array('error' => 'OK', 'html' => 'El usuario ha sido eliminado'));
	} catch (Exception $e) {
		header ('Access-Control-Allow-Origin: *');
		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json; charset=utf-8');
		echo json_encode(array('error' => $e->getMessage()));
	}
	exit ();
