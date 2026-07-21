<?php
	global $application_unique_key, $adb;

	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/InstanceCreator.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/invitations/lib/InvitationsManager.class.php');
	require_once ('modules/store/lib/StoreUtils.class.php');
	require_once ('modules/Users/Users.php');

	try {
		$instanceName = InvitationsManager::getInstance ()->process ($_REQUEST);

		/** @var Users|stdClass $focus */
		$focus = new Users ();
		if ((!isset ($_SESSION ['authenticated_user_id'])) || (!$_SESSION ['authenticated_user_id'])) {
			// Autenticando usuario
			$focus->column_fields ['user_name'] = to_html ($_REQUEST ['usuarioEmail']);
			$focus->load_user ($_REQUEST ['clave'], null);
			$_SESSION ['authenticated_user_id']           = $focus->id;
			$_SESSION ['vtiger_authenticated_user_theme'] = 'centaurus';
			$_SESSION ['firstConnection']                 = true;
		} else {
			$focus->retrieve_entity_info ($_SESSION ['authenticated_user_id'], 'Users');
		}

		$adb              = AdbManager::getInstance ()->getMasterAdb ();
		$result           = $adb->pquery ('SELECT instanceid, verificationcode FROM vtiger_instances WHERE code=?', array ($instanceName), true);
		$row              = $adb->fetch_array ($result);
		$verificationCode = $row ['verificationcode'];

		if (!isset ($_SESSION ['plat'])) {
			$_SESSION ['plat'] = $instanceName;
		}

		if (!isset ($_SESSION ['platInstancia'])) {
			$_SESSION ['platInstancia'] = $instanceName; // servirá para determinar ls bd correcta en login
		}

		$_SESSION ['app_unique_key']          = $application_unique_key;
		$_SESSION ['is_authenticated']        = 1;
		$_SESSION ['authenticated_user_menu'] = array ();
		unset ($_SESSION ['briefing']);

		$adb = AdbManager::getInstance ()->getTargetInstanceAdb ($instanceName);
		create_tab_data_file ();
		create_parenttab_data_file ();
		createUserPrivilegesfile ($focus->id);
		createUserSharingPrivilegesfile ($focus->id);
		DemoDataManager::create ($instanceName, $focus);
		header ('Location: index.php');
	} catch (Exception $e) {
		session_destroy ();
		header ('Location: index.php?module=store&action=invitation&error=' . urlencode ($e->getMessage ()));
	}
	exit ();
