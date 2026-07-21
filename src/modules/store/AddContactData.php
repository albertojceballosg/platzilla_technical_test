<?php
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $current_user, $platPrincipal;

	$firstName     = PlatzillaUtils::purify ($_POST, 'firstname');
	$lastName      = PlatzillaUtils::purify ($_POST, 'lastname');
	$plainPassword = PlatzillaUtils::purify ($_POST, 'password');
	$phoneNumber   = PlatzillaUtils::purify ($_POST, 'phonenumber');
	$profile       = PlatzillaUtils::purify ($_POST, 'profile');

	try {
		if ((empty ($_SESSION ['platInstancia'])) || (!is_admin ($current_user))) {
			throw new Exception ('Acceso denegado', 401);
		} else if (empty ($profile)) {
			throw new Exception ('No has suministrado el código de la aplicación', 400);
		}

		$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
		$masterAdb->pquery (
			'UPDATE
				vtiger_clientes
			SET
				alias=?,
				nombre_comercial=?,
				telefono=?,
				observaciones=?
			WHERE
				clientesid IN (SELECT accountid FROM vtiger_instances WHERE code=?)',
			array (trim ("{$firstName} {$lastName}"), trim ("{$firstName} {$lastName}"), !empty ($phoneNumber) ? $phoneNumber : null, $profile, $_SESSION ['platInstancia'])
		);
		$masterAdb->pquery (
			'UPDATE
				vtiger_contactos
			SET
				nombre=?,
				apellidos=?,
				telefono=?,
				observaciones=?
			WHERE
				clientes IN (SELECT accountid FROM vtiger_instances WHERE code=?)',
			array ($firstName, $lastName, !empty ($phoneNumber) ? $phoneNumber : null, $profile, $_SESSION ['platInstancia'])
		);
		$masterAdb->pquery ('UPDATE vtiger_instances SET status=? WHERE code=?', array ('verified', $_SESSION ['platInstancia']));

		$targetAdb     = AdbManager::getInstance ()->getTargetInstanceAdb ($_SESSION ['platInstancia']);
		$um            = UserManager::getInstance ($targetAdb, null);
		$administrator = $um->fetchUserById ($_SESSION ['authenticated_user_id']);
		$administrator->setFirstName ($firstName)
			->setLastName ($lastName)
			->setPlainPassword ($plainPassword);
		$um->saveUser ($administrator);
		create_tab_data_file ();
		create_parenttab_data_file ();
		createUserPrivilegesfile ($_SESSION ['authenticated_user_id']);
		createUserSharingPrivilegesfile ($_SESSION ['authenticated_user_id']);
		if (isset ($_SESSION ['firstConnection'])) {
			unset ($_SESSION ['firstConnection']);
		}
		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json');
		echo json_encode ('OK');
	} catch (Exception $e) {
		$statusCode = !empty ($e->getCode ()) ? $e->getCode () : 500;
		switch ($statusCode) {
			case 400:
				$statusMessage = 'Bad request';
				break;
			case 401:
				$statusMessage = 'Access denied';
				break;
			default:
				$statusMessage = 'Internal server error';
				break;
		}
		header ("HTTP/1.1 {$statusCode} {$statusMessage}");
		header ('Content-Type: application/json');
		echo json_encode ($e->getMessage ());
	}
	exit ();
