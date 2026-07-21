<?php
	require_once ('include/platzilla/Managers/ApplicationManager.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb, $current_user;

	if ((!empty ($_SESSION ['platInstancia'])) || ($current_user->is_admin != 'on')) {
		header ('Location: index.php');
		exit ();
	}

	$newApplicationCode        = PlatzillaUtils::purify ($_POST, 'newapplicationcode');
	$newApplicationDescription = PlatzillaUtils::purify ($_POST, 'newapplicationdescription');
	$newApplicationName        = PlatzillaUtils::purify ($_POST, 'newapplicationname');
	$oldApplicationCode        = PlatzillaUtils::purify ($_POST, 'oldapplicationcode');
	$newApplicationModulesData = PlatzillaUtils::purify ($_POST, 'modules');

	$am = ApplicationManager::getInstance ($adb);
	try {
		if (empty ($oldApplicationCode)) {
			throw new Exception ('No se ha suministrado el nombre de la aplicación a duplicar');
		} else if (empty ($newApplicationCode)) {
			throw new Exception ('No se ha suministrado el nuevo nombre');
		} else if (empty ($newApplicationName)) {
			throw new Exception ('No se ha suministrado el nuevo título');
		} else if (empty ($newApplicationDescription)) {
			throw new Exception ('No se ha suministrado la nueva descripción');
		} else if (empty ($newApplicationModulesData)) {
			throw new Exception ('No se ha suministrado la información de los nuevos módulos');
		}

		$result = $adb->pquery ('SELECT * FROM vtiger_config_applications WHERE app_name=? OR app_code=? LIMIT 1', array ($newApplicationName, $newApplicationCode));
		if (($result) && ($adb->num_rows ($result) > 0)) {
			$row = $adb->fetchByAssoc ($result, -1, false);
			if ($row ['app_code'] == $newApplicationCode) {
				throw new Exception ("El código de la aplicación {$newApplicationCode} ya ha sido registrado");
			} else {
				throw new Exception ("El título de la aplicación {$newApplicationName} ya ha sido registrado");
			}
		}

		$oldApplication = $am->fetchApplication ($oldApplicationCode);
		if (empty ($oldApplication)) {
			throw new Exception ("La aplicación {$oldApplicationCode} no está registrada");
		}

		$oldApplicationModules = $oldApplication->getModules ();
		foreach ($oldApplicationModules as $oldApplicationModule) {
			$oldApplicationModuleName = $oldApplicationModule->getName ();
			if (!isset ($newApplicationModulesData [ $oldApplicationModuleName ])) {
				throw new Exception ("No se ha suministrado la información del módulo {$oldApplicationModuleName}");
			} else if (!isset ($newApplicationModulesData [ $oldApplicationModuleName ]['newmodulename'])) {
				throw new Exception ("No se ha suministrado el código del nuevo módulo {$oldApplicationModuleName}");
			} else if (!isset ($newApplicationModulesData [ $oldApplicationModuleName ]['newmoduletitle'])) {
				throw new Exception ("No se ha suministrado el título del nuevo módulo {$newApplicationModulesData [ $oldApplicationModuleName ]['newmodulename']}");
			} else if (!isset ($newApplicationModulesData [ $oldApplicationModuleName ]['newmenulabel'])) {
				throw new Exception ("No se ha suministrado el menú donde aparecerá el nuevo módulo {$newApplicationModulesData [ $oldApplicationModuleName ]['newmodulename']}");
			}

			$result = $adb->pquery (
				'SELECT * FROM vtiger_tab WHERE name=? OR tablabel=? LIMIT 1',
				array ($newApplicationModulesData [ $oldApplicationModuleName ]['newmodulename'], $newApplicationModulesData [ $oldApplicationModuleName ]['newmoduletitle'])
			);
			if (($result) && ($adb->num_rows ($result) > 0)) {
				$row = $adb->fetchByAssoc ($result, -1, false);
				if ($row ['name'] == $newApplicationModulesData [ $oldApplicationModuleName ]['newmodulename']) {
					throw new Exception ("El código del módulo {$newApplicationModulesData [ $oldApplicationModuleName ]['newmodulename']} ya ha sido registrado");
				} else {
					throw new Exception ("El título del módulo {$newApplicationModulesData [ $oldApplicationModuleName ]['newmoduletitle']} ya ha sido registrado");
				}
			}

			$result = $adb->pquery ('SELECT * FROM vtiger_parenttab WHERE parenttab_label=? LIMIT 1', array ($newApplicationModulesData [ $oldApplicationModuleName ]['newmenulabel']));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				throw new Exception ("El menú {$newApplicationModulesData [ $oldApplicationModuleName ]['newmenulabel']} no está registrado");
			}
		}

		$am->duplicateApplication ($oldApplication, $newApplicationCode, $newApplicationName, $newApplicationDescription, $newApplicationModulesData);
		create_tab_data_file ();
		create_parenttab_data_file ();

		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => "La aplicación {$oldApplicationCode} ha sido duplicada",
		);
		header ('Location: index.php?module=Settings&action=ConfigApps&parenttab=Settings');
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
			'data'    => $_POST,
		);
//		var_dump ($e->getMessage ());
		header ('Location: index.php?module=Settings&action=AppDuplicator&parenttab=Settings');
	}
	exit ();
