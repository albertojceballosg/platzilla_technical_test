<?php
	require ('config.inc.php');
	require_once ('include/platzilla/Managers/PlatformManager.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/potenciales_clientes/potenciales_clientes.php');

	global $application_unique_key, $dbconfig, $platPrincipal;
	
	try {
		$emailAddress = PlatzillaUtils::purify ($_POST, 'email');
		$firstName    = PlatzillaUtils::purify ($_POST, 'firstname');
		$funtion      = PlatzillaUtils::purify ($_POST, 'function', 'CREATE_INSTANCE');
		$lastName     = PlatzillaUtils::purify ($_POST, 'lastname');
		$password     = PlatzillaUtils::purify ($_POST, 'password');
		$phone        = PlatzillaUtils::purify ($_POST, 'phone');
		$profile      = PlatzillaUtils::purify ($_POST, 'profile');
		$source       = PlatzillaUtils::purify ($_POST, 'source');
		
		$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
		if ($funtion == 'PROSPECTUS_DATA') {
			$entity = CRMEntity::getInstance ('potenciales_clientes');
			$entity->column_fields = getColumnFields ('potenciales_clientes');
			$entity->mode          = 'create';
			$entity->mode          = 'create';
			$entity->id            = null;
			/** insert data */
			$entity->column_fields ['alias']                        = "{$firstName} {$lastName}";
			$entity->column_fields ['apellidos_contacto']           = $lastName;
			$entity->column_fields ['assigned_user_id']             = 1;
			$entity->column_fields ['calificacio']                  = 'Tibio';
			$entity->column_fields ['e_mail']                       = $emailAddress;
			$entity->column_fields ['empresa']                      = "{$firstName} {$lastName}";;
			$entity->column_fields ['estado_del_poten']             = 'Creado';
			$entity->column_fields ['fuente_del_poten']             = 'Platzilla-Raíles';
			$entity->column_fields ['mas_informacion_y_comentario'] = "Creado desde botón “Regístrate gratis” el " . date ('d/m/Y') . " a las " . date ('H:i:s');
			$entity->column_fields ['nombre_contacto']              = $firstName;
			$entity->column_fields ['telefono']                     = $phone;
			$entity->save ('potenciales_clientes');
		} else if ($funtion == 'CREATE_INSTANCE') {
			if (filter_var ($emailAddress, FILTER_VALIDATE_EMAIL) === false) {
				throw new Exception ('La dirección de correo que suministraste no parece ser válida', 400);
			} else
				
				$dummy = explode ('@', $emailAddress);
			$emailDomain = $dummy [1];
			
			$result = getmxrr ($emailDomain, $mxhosts);
			if ((empty ($result)) || (empty ($mxhosts))) {
				throw new Exception ('La dirección de correo que suministraste no parece ser válida', 400);
			}
			
			$pm = PlatformManager::getInstance ($masterAdb, $dbconfig ['db_serverForNewUsers']);
			if ($pm->userHasInstance ($emailAddress)) {
				throw new Exception ('Ya estás registrado en Platzilla', 400);
			}
			
			$dummy = explode ('@', $emailAddress);
			$administrator = User::getInstance ()
				->setAdministrator (true)
				->setEmail ($emailAddress)
				->setFirstName ($firstName)
				->setLastName ($lastName)
				->setPlainPassword ($password)
				->setDefaultModuleName ('Home')
				->setDefaultOperating ('MANAGEMENT_MODE')
				->setDefaultHomeTab ('ACTIVITY')
				->setUserName ($emailAddress);
			$instance = PlatformManager::getInstance ($masterAdb, $dbconfig ['db_serverForNewUsers'])->assignInstance ($platPrincipal, $emailAddress, $administrator);
			
			$masterAdb->pquery (
				'UPDATE
				vtiger_clientes
			SET
				alias=?,
				nombre_comercial=?,
				observaciones=?
			WHERE
				clientesid IN (SELECT accountid FROM vtiger_instances WHERE code=?)',
				array(trim ("{$firstName} {$lastName}"), trim ("{$firstName} {$lastName}"), $profile, $instance->getCode ())
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
				array($firstName, $lastName, $profile, $instance->getCode ())
			);
			$masterAdb->pquery ('UPDATE vtiger_instances SET status=?, source=? WHERE code=?', array('verified', $source, $instance->getCode ()));
			
			$adb = AdbManager::getInstance ()->getTargetInstanceAdb ($instance->getCode ());
			// Autenticando usuario
			$focus = new Users ();
			$focus->column_fields ['user_name'] = $emailAddress;
			$focus->retrieve_entity_info (1, 'Users');
			
			$_SESSION ['authenticated_user_menu'] = array();
			$_SESSION ['is_authenticated'] = 1;
			$_SESSION ['authenticated_user_id'] = $focus->id;
			$_SESSION ['app_unique_key'] = $application_unique_key;
			$_SESSION ['plat'] = $instance->getCode ();
			$_SESSION ['platInstancia'] = $instance->getCode (); // servirá para determinar ls bd correcta en login
			$_SESSION ['vtiger_authenticated_user_theme'] = 'centaurus';
			$_SESSION ['authenticated_user_language'] = $focus->column_fields['language'];
			unset ($_SESSION['briefing']);
			
			create_tab_data_file ();
			create_parenttab_data_file ();
			createUserPrivilegesfile ($focus->id);
			createUserSharingPrivilegesfile ($focus->id);
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
