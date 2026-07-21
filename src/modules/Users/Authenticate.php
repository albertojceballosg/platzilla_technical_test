<?php
	require_once ('include/logging.php');
	require_once ('include/platzilla/Managers/PlatformManager.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/UserInfoUtil.php');
	require_once ('modules/store/lib/StoreUtils.class.php');
	require_once ('modules/Users/CreateUserPrivilegeFile.php');
	require_once ('modules/Users/LoginHistory.php');
	require_once ('modules/Users/Users.php');
	require_once ('user_privileges/audit_trail.php');

	global $adb, $application_unique_key, $audit_trail, $import_dir, $mod_strings, $default_charset, $platPrincipal, $record;

	$instanceData       = null;
	$impersonationToken = PlatzillaUtils::purify ($_POST, 'impersonationtoken');
	$user               = PlatzillaUtils::purify ($_POST, 'user');
	$adb                = AdbManager::getInstance ()->getMasterAdb ();
	if (!empty ($impersonationToken)) {
		$instanceCode   = PlatzillaUtils::purify ($_POST, 'instancecode');
		$temporaryAdmin = PlatformManager::getInstance ($adb)->fetchInstanceTemporaryAdmin ($instanceCode, $impersonationToken);
		if (empty ($temporaryAdmin)) {
			$_SESSION ['flashmessage'] = array (
				'iserror' => true,
				'message' => 'Credenciales inválidas',
			);
			header ("Location: index.php?module=Users&action=Login&impersonationtoken={$impersonationToken}");
			exit ();
		}

		$userName = $temporaryAdmin->getUserName ();
		unset ($adb);
		$adb   = AdbManager::getInstance ()->getTargetInstanceAdb ($instanceCode);
		$focus = new Users ();
		$focus->retrieve_entity_info ($temporaryAdmin->getId (), 'Users');
		$focus->authenticated             = true;
		$instanceData                     = array (
			'code'   => $instanceCode,
			'domain' => $instanceCode,
		);
		$_SESSION ['impersonation_token'] = $impersonationToken;
	} else if ($user == 'guest') {
		$instance     = PlatformManager::getInstance ($adb)->fetchInstance ('appguest', true);
		$userName     = 'guest';
		$userPassword = md5 ('guest');
		unset ($adb);
		$adb  = AdbManager::getInstance ()->getTargetInstanceAdb ($instance->getCode ());
		$um   = UserManager::getInstance ($adb, null);
		$user = $um->fetchUserByUsername ($userName);
		if (!empty ($user)) {
			$um->deleteUser ($user);
		}
		$user = User::getInstance ()
			->setAdministrator (false)
			->setDefaultModuleName ('Walkthrough')
			->setEmail ('guest@platzilla.com')
			->setFirstName ('Usuario')
			->setLastName ('Invitado')
			->setPlainPassword ($userPassword)
			->setStatus (User::STATUS_ACTIVE)
			->setUserName ($userName);
		$um->saveUser ($user);

		$focus = new Users ();
		$focus->retrieve_entity_info ($user->getId (), 'Users');
		$focus->authenticated         = true;
		$instanceData                 = array (
			'code'   => $instance->getCode (),
			'domain' => $instance->getCode (),
		);
		$_SESSION ['firstConnection'] = false;
		$_SESSION ['isGuestUser']     = true;
	} else {
		$userName     = PlatzillaUtils::purify ($_REQUEST, 'user_name');
		$userPassword = PlatzillaUtils::purify ($_REQUEST, 'user_password');

		$result = $adb->pquery (
			'SELECT
				i.*
			FROM
				vtiger_instanceusers iu
				INNER JOIN vtiger_instances i ON i.code=iu.instancecode
			WHERE
				iu.username = ?',
			array ($userName)
		);
		if (!$result) {
			$_SESSION ['flashmessage'] = array (
				'iserror' => true,
				'message' => 'Se ha presentado un error inesperado. Intenta más tarde',
			);
			header ('Location: index.php');
			exit ();
		}

		if ($adb->num_rows ($result) > 0) {
			$instanceData = $adb->fetchByAssoc ($result, -1, false);
			unset ($adb);
			$adb = AdbManager::getInstance ()->getTargetInstanceAdb ($instanceData ['code']);
		}
		DatabaseUtils::closeResult ($result);
		$result = null;

		/** @var Users|stdClass $focus */
		$focus                              = new Users ();
		$focus->column_fields ['user_name'] = to_html ($userName);
		$focus->load_user ($userPassword);
		if (!$focus->is_authenticated ()) {
			$result = $adb->pquery ('SELECT user_name, id, crypt_type FROM vtiger_users WHERE user_name=?', array ($userName));
			if (($result) && ($adb->num_rows ($result) > 0)) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$cryptType = $row['crypt_type'];
					/* PHP 5.3 WIN implementation of crypt API not compatible with earlier version */
					if ((strtolower ($cryptType) == 'md5') && (version_compare (PHP_VERSION, '5.3.0') >= 0) && (strtoupper (substr (PHP_OS, 0, 3)) === 'WIN')) {
						header ("Location: modules/Migration/PHP5.3_PasswordHelp.php");
						exit ();
					}
				}
			}
			$_SESSION ['flashmessage'] = array (
				'iserror' => true,
				'message' => 'Credenciales inválidas',
			);
			header ('Location: index.php');
			exit ();
		}

		$_SESSION ['firstConnection'] = (!empty ($instanceData ['status'])) && ($instanceData ['status'] == 'unverified');
	}
	$_SESSION ['app_unique_key']                  = $application_unique_key;
	$_SESSION ['authenticated_user_email']        = $focus->column_fields ['email1'];
	$_SESSION ['authenticated_user_fullname']     = trim ("{$focus->column_fields ['first_name']} {$focus->column_fields ['last_name']}");
	$_SESSION ['authenticated_user_id']           = $focus->id;
	$_SESSION ['authenticated_user_name']         = $userName;
	$_SESSION ['authenticated_user_language']     = !empty($focus->column_fields['language']) ? $focus->column_fields['language'] : 'es_es';
	$_SESSION ['authenticated_user_menu']         = array ();
	$_SESSION ['is_authenticated']                = 1;
	$_SESSION ['vtiger_authenticated_user_theme'] = 'centaurus';
	if (!empty ($instanceData)) {
		$_SESSION ['domain']        = $instanceData ['domain'];
		$_SESSION ['plat']          = $instanceData ['code'];
		$_SESSION ['platInstancia'] = $instanceData ['code'];
	} else {
		$_SESSION ['plat']          = $platPrincipal;
		$_SESSION ['platInstancia'] = '';
	}

	// Recreate permission files
	create_tab_data_file ();
	create_parenttab_data_file ();
	createUserPrivilegesfile ($focus->id);
	createUserSharingPrivilegesfile ($focus->id);
	session_regenerate_id ();

	// Recording the audit trail
	$adb->pquery (
		'INSERT INTO vtiger_audit_trial (sessionid, userid, module, action, recordid, actiondate) VALUES (?, ?, ?, ?, ?, ?)',
		array (session_id (), $focus->id, 'Users', 'Authenticate', null, $adb->formatDate (date ('Y-m-d H:i:s'), true))
	);

	// Recording the login info
	$loginHistory = new LoginHistory ();
	$loginHistory->user_login ($focus->column_fields["user_name"], $_SERVER ['REMOTE_ADDR'], date ('Y/m/d H:i:s'));

	//Security related entries
	createUserPrivilegesfile ($focus->id);

	// Clear all uploaded import files for this user if it exists
	$dummy = "{$import_dir}/IMPORT_{$focus->id}";
	if (file_exists ($dummy)) {
		unlink ($dummy);
	}

	unset ($_SESSION ['briefing']);
	header ('Location: index.php');
