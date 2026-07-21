<?php
	require_once ('include/utils/PlatformUtils.class.php');
	require_once ('modules/Settings/lib/ConfigApplicationsHelper.class.php');
	require_once ('modules/Settings/lib/ModuleCreator.class.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');
	require_once ('modules/Settings/lib/WizardUtils.class.php');

	global $adb, $current_user;

	$platform           = $_SESSION ['plat'];
	$oldApplicationId   = SettingsUtils::purify ($_POST, 'appidaduplicar', '');
	$oldModuleIds       = SettingsUtils::purify ($_POST, 'tabidoriginal', array ());
	$newApplicationCode = SettingsUtils::purify ($_POST, 'appnueva', '');
	$newApplicationName = SettingsUtils::purify ($_POST, 'titulonuevo', '');
	$newModuleNames     = SettingsUtils::purify ($_POST, 'codigonuevomodulo', array ());
	$newModuleLabels    = SettingsUtils::purify ($_POST, 'nombrenuevomodulo', array ());

	$existingApplication = PlatformUtils::getApplicationDataByCodeOrName ($adb, $newApplicationCode, $newApplicationName);
	if ($existingApplication !== null) {
		// Si existe la app, devolvemos al usuario a la pantalla anterior con un mensaje de error
		if ($existingApplication ['app_code']) {
			$_SESSION ['mensaje'] = array ('tipo' => 'error', 'descripcion' => "Ya existe una aplicación llamada {$newApplicationCode}");
		} else {
			$_SESSION ['mensaje'] = array ('tipo' => 'error', 'descripcion' => "Ya existe una aplicación con el título {$newApplicationName}");
		}
		header ("Location: index.php?module=Settings&action=AppDuplicator&appidaduplicar={$oldApplicationId}&appnueva={$newApplicationCode}&titulonuevo={$newApplicationName}");
		exit ();
	}

	foreach ($newModuleNames as $index => $newModuleName) {
		if (empty ($newModuleName)) {
			$_SESSION ['mensaje'] = array ('tipo' => 'error', 'descripcion' => 'Introduce el nombre código del módulo');
		} else if (empty ($newModuleLabels [ $index ])) {
			$_SESSION ['mensaje'] = array ('tipo' => 'error', 'descripcion' => 'Introduce el nombre público del módulo');
		} else if (WizardUtils::whereIsModuleNameRegistered ($adb, $newModuleName) != WizardUtils::MODULE_NOT_REGISTERED) {
			$_SESSION ['mensaje'] = array ('tipo' => 'error', 'descripcion' => "Ya existe un módulo con nombre código {$newModuleName}");
		} else if (WizardUtils::whereIsModuleLabelRegistered ($adb, $newModuleLabels [ $index ]) != WizardUtils::MODULE_NOT_REGISTERED) {
			$_SESSION ['mensaje'] = array ('tipo' => 'error', 'descripcion' => "Ya existe un módulo con nombre público {$newModuleLabels [$index]}");
		}

		if (isset ($_SESSION ['mensaje'])) {
			$_SESSION ['application-data'] = $_POST;
			header ("Location: index.php?module=Settings&action=AppDuplicator2&appidaduplicar={$oldApplicationId}&appnueva={$newApplicationCode}&titulonuevo={$newApplicationName}&duplicar=Duplicar");
			exit ();
		}
	}

	$protectedVariables = array ('module', 'action', 'Ajax', 'plat');
	$keys               = array_keys ($_POST);
	foreach ($keys as $key) {
		if (!in_array ($key, $protectedVariables)) {
			$_SESSION [ $key ] = SettingsUtils::purify ($_POST, $key);
		}
	}

	foreach ($oldModuleIds as $key => $oldModuleId) {
		$moduleMenuLabel = PlatformUtils::getModuleMenuLabel ($adb, $oldModuleId);
		PlatformUtils::initializeModuleDuplicationSessionVariables ($adb, $oldModuleId, $newModuleNames [ $key ], $newModuleLabels [ $key ], $moduleMenuLabel);
		ModuleCreator::getInstance ()->duplicateModule ($adb, $platform, $_SESSION);
	}

	ConfigApplicationsHelper::duplicateApplication ($adb, $oldApplicationId, $newApplicationCode, $newApplicationName, $newModuleNames);
	header ('Location: index.php?module=Settings&action=ConfigApps');
