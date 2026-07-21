<?php
	require ('config.inc.php');
	require_once ('include/platzilla/Managers/PlatformManager.php');
	require_once ('include/platzilla/Objects/User.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/store/lib/CreateInstanceFromPatternHelper.class.php');
	require_once ('modules/Users/Users.php');

	global $adb, $application_unique_key, $dbconfig, $platPrincipal;

	try {
		$company       = PlatzillaUtils::purify ($_REQUEST, 'company');
		$email         = PlatzillaUtils::purify ($_REQUEST, 'usuarioEmail');
		$firstName     = PlatzillaUtils::purify ($_REQUEST, 'name');
		$lastName      = PlatzillaUtils::purify ($_REQUEST, 'lastname');
		$plainPassword = PlatzillaUtils::purify ($_REQUEST, 'clave');
		$patternCode   = PlatzillaUtils::purify ($_REQUEST, 'pattern');
		$testData      = PlatzillaUtils::purify ($_REQUEST, 'testData');

		$administrator = User::getInstance ()
			->setEmail ($email)
			->setFirstName ($firstName)
			->setAdministrator (true)
			->setLastName ($lastName)
			->setPlainPassword ($plainPassword)
			->setUserName ($email);

		if (empty ($patternCode)) {
			throw new Exception ('No has suministrado el código de la instancia patrón');
		}

		$patternInstance = PlatformManager::getInstance ($adb)->fetchInstance ($patternCode);
		if (empty ($patternInstance)) {
			throw new Exception ("La instancia con el código {$patternCode} no está registrada");
		}

		$applicationCodes = array ();
		foreach ($patternInstance->getApplications () as $objApp) {
			$applicationCodes [] = $objApp->getCode ();
		}

		$adb = AdbManager::getInstance ()->getMasterAdb ();
		$pm  = PlatformManager::getInstance ($adb, $dbconfig ['db_serverForNewUsers']);

		if ($pm->userHasInstance ($email)) {
			throw new Exception ('Ya tiene una instancia asignada');
		}

		$instance     = $pm->createInstance ($platPrincipal, $company, $administrator, null, $applicationCodes);
		$instanceName = $instance->getCode ();
		//Crea copia de los datos desde la instancia patrón
		if ($testData == 'Si') {
			$patternAdb = AdbManager::getInstance ()->getTargetInstanceAdb ($patternCode);
			$instanceNew = AdbManager::getInstance ()->getTargetInstanceAdb ($instanceName);
			$allModules = array ();
			foreach ($instance->getApplications () as $objApp) {
				foreach ($objApp->getModules () as $module) {
					if (!in_array ($module->getName (), $allModules)) {
						$allModules [] = $module->getName ();
						$table         = CreateInstanceFromPatternHelper::getAllModuleData ($patternAdb, $module);
						if (!empty($table)) {
							CreateInstanceFromPatternHelper::setAllDataToModule ($instanceNew, $table);
						}
					}
				}
			}

			$patternAdb         = AdbManager::getInstance ()->getTargetInstanceAdb ($instanceName);
			$where              = array ('type' => 'js', 'onclick' => 'clearTestData()', 'active' => 1);
			$delDataTestButtoms = CreateInstanceFromPatternHelper::getCustomButtonByWhere ($adb, $where);
			CreateInstanceFromPatternHelper::addBatchCustomButton ($patternAdb, $delDataTestButtoms);
			PlatformManager::getInstance ($adb)->updateInstancePattern ($instanceName, true);
		}

		$adb = AdbManager::getInstance ()->getTargetInstanceAdb ($instanceName);
		// Autenticando usuario
		$focus                              = new Users ();
		$focus->column_fields ['user_name'] = $email;
		$focus->load_user ($plainPassword, null);
		if ($focus->is_authenticated ()) {
			// Agregando código generado por Dioran para cambios del menú (eliminación de archivos e implementación de variable de sesión)
			$_SESSION ['authenticated_user_menu']         = array ();
			$_SESSION ['is_authenticated']                = 1;
			$_SESSION ['authenticated_user_id']           = $focus->id;
			$_SESSION ['app_unique_key']                  = $application_unique_key;
			$_SESSION ['firstConnection']                 = true;
			$_SESSION ['plat']                            = $instanceName;
			$_SESSION ['platInstancia']                   = $instanceName; // servirá para determinar ls bd correcta en login
			$_SESSION ['vtiger_authenticated_user_theme'] = 'centaurus';
			$_SESSION ['authenticated_user_language']     = $focus->column_fields['language'];
			unset ($_SESSION['briefing']);

			$adb = AdbManager::getInstance ()->getTargetInstanceAdb ($instanceName);
			create_tab_data_file ();
			create_parenttab_data_file ();
			createUserPrivilegesfile ($focus->id);
			createUserSharingPrivilegesfile ($focus->id);
		}
		header ('Location: index.php');
	} catch (Exception $e) {
		session_destroy ();
		header ('Location: index.php?action=Login&module=Users&errorTrial=' . urlencode ($e->getMessage ()));
	}
	exit ();
