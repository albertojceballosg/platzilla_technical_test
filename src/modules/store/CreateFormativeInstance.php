<?php
	require ('config.inc.php');
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Managers/PlatformManager.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/News/lib/AdQueueHelper.class.php');
	require_once ('modules/store/lib/StoreUtils.class.php');

	global $application_unique_key, $dbconfig, $platPrincipal;

	$adQueueIds   = PlatzillaUtils::purify ($_POST, 'adQueueIds', null);
	$emailAddress = PlatzillaUtils::purify ($_POST, 'usuarioEmail');
	$ebook        = PlatzillaUtils::purify ($_POST, 'ebookId', null);
	$passWoord    = PlatzillaUtils::purify ($_POST, 'codeToken', null);

	try {
		if (filter_var ($emailAddress, FILTER_VALIDATE_EMAIL) === false) {
			throw new Exception ('La dirección de correo que suministraste no parece ser válida', 400);
		}

		$dummy        = explode ('@', $emailAddress);
		$emailDomain  = $dummy [1];
		$firstName    = 'Admin';
		$lastName     = $dummy [0];
		$password     = (empty($passWoord)) ? StoreUtils::randomPassword (8) : $passWoord;
		$source       = 'B.B.@' . $password;
		$profile      = 'Registrado en modo informativo';

		$result = getmxrr ($emailDomain, $mxhosts);
		if ((empty ($result)) || (empty ($mxhosts))) {
			throw new Exception ('La dirección de correo que suministraste no parece ser válida', 400);
		}

		$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
		$pm  = PlatformManager::getInstance ($masterAdb, $dbconfig ['db_serverForNewUsers']);
		if ($pm->userHasInstance ($emailAddress)) {
			throw new Exception ('Ya estás registrado en Platzilla', 400);
		}

		$dummy         = explode ('@', $emailAddress);
		$administrator = User::getInstance ()
			->setAdministrator (true)
			->setEmail ($emailAddress)
			->setFirstName ($firstName)
			->setLastName ($lastName)
			->setPlainPassword ($password)
			->setDefaultModuleName('Home')
			->setDefaultOperating ('FORMATIVE_MODE')
			->setDefaultHomeTab ('MATERIALS')
			->setUserName ($emailAddress);
		$instance      = PlatformManager::getInstance ($masterAdb, $dbconfig ['db_serverForNewUsers'])->assignInstance ($platPrincipal, $emailAddress, $administrator);

		$masterAdb->pquery (
			'UPDATE
					vtiger_clientes
				SET
					alias=?,
					nombre_comercial=?,
					observaciones=?
				WHERE
					clientesid IN (SELECT accountid FROM vtiger_instances WHERE code=?)',
			array (trim ("{$firstName} {$lastName}"), trim ("{$firstName} {$lastName}"), $profile, $instance->getCode ())
		);
		$masterAdb->pquery (
			'UPDATE
					vtiger_contactos
				SET
					nombre=?,
					apellidos=?,
					observaciones=?
				WHERE
					clientes IN (SELECT accountid FROM vtiger_instances WHERE code=?)',
			array ($firstName, $lastName, $profile, $instance->getCode ())
		);
		$masterAdb->pquery ('UPDATE vtiger_instances SET status=?, source=? WHERE code=?', array ('verified', $source, $instance->getCode ()));

		$adb = AdbManager::getInstance ()->getTargetInstanceAdb ($instance->getCode ());
		// Autenticando usuario
		$focus                              = new Users ();
		$focus->column_fields ['user_name'] = $emailAddress;
		$focus->retrieve_entity_info (1, 'Users');

		$_SESSION ['authenticated_user_menu']         = array ();
		$_SESSION ['is_authenticated']                = 1;
		$_SESSION ['authenticated_user_id']           = $focus->id;
		$_SESSION ['app_unique_key']                  = $application_unique_key;
		$_SESSION ['plat']                            = $instance->getCode ();
		$_SESSION ['platInstancia']                   = $instance->getCode (); // servirá para determinar ls bd correcta en login
		$_SESSION ['vtiger_authenticated_user_theme'] = 'centaurus';
		$_SESSION ['authenticated_user_language']     = $focus->column_fields['language'];
		unset ($_SESSION['briefing']);

		create_tab_data_file ();
		create_parenttab_data_file ();
		createUserPrivilegesfile ($focus->id);
		createUserSharingPrivilegesfile ($focus->id);
		if (!empty ($adQueueIds)) {
			AdQueueHelper::getInstance()->saveSharingByAdQueues ($adQueueIds, $emailAddress, $focus->id);
		}
		header('Access-Control-Allow-Origin: *');
		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json; charset=utf-8');
		echo json_encode (array ('error' => 'OK'));
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
		echo json_encode (array ('error' => $e->getMessage ()));
	}
	exit ();
