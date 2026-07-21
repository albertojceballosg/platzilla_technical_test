<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Managers/ModuleManager.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/HtmlGenerator.class.php');
	require_once ('include/utils/PlatformUtils.class.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');
	require_once ('modules/Settings/lib/WizardUtils.class.php');

	global $adb, $current_user, $mod_strings, $theme;
	if (!isset ($adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	$moduleLabel   = SettingsUtils::purify ($_REQUEST, 'nombrePublico');
	$moduleName    = SettingsUtils::purify ($_REQUEST, 'nombreCodigo');
	$moduleType    = SettingsUtils::purify ($_REQUEST, 'tipoModulo');
	$parentModule  = SettingsUtils::purify ($_REQUEST, 'moduloPadre');
	$platInstancia = SettingsUtils::purify ($_SESSION, 'platInstancia');

	$message            = '';
	$moduleNameRegisteredOn = WizardUtils::whereIsModuleNameRegistered ($adb, $moduleName);
	$moduleLabelRegisteredOn = WizardUtils::whereIsModuleLabelRegistered ($adb, $moduleLabel);
	if ($moduleNameRegisteredOn != WizardUtils::MODULE_NOT_REGISTERED) {
		switch ($moduleNameRegisteredOn) {
			case WizardUtils::MODULE_REGISTERED_IN_MASTER:
				$message = "No puede utilizar el nombre <strong>{$moduleName}</strong> para su aplicación ya que existe como módulo genérico de nuestro sistema ¡Elija otro nombre por favor!";
				break;
			case WizardUtils::MODULE_REGISTERED_IN_OTHER_INSTANCE:
			case WizardUtils::MODULE_REGISTERED_IN_THIS_INSTANCE:
				$message = "Ya existe un módulo llamado <strong>{$moduleName}</strong>. ¡Elija otro nombre por favor!";
				break;
			default:
				$message = '';
				break;
		}
		echo "<div class=\"alert alert-danger\"><strong>Error:</strong> {$message}</div>";
		require_once ('modules/Settings/wizardPaso1.php');
	} else if ($moduleLabelRegisteredOn != WizardUtils::MODULE_NOT_REGISTERED) {
		switch ($moduleLabelRegisteredOn) {
			case WizardUtils::MODULE_REGISTERED_IN_MASTER:
				$message = "No puede utilizar el nombre público <strong>{$moduleLabel}</strong> para su aplicación ya que existe como módulo genérico de nuestro sistema ¡Elija otro nombre por favor!";
				break;
			case WizardUtils::MODULE_REGISTERED_IN_THIS_INSTANCE:
				$message = "Ya existe un módulo llamado <strong>{$moduleLabel}</strong>. ¡Elija otro nombre por favor!";
				break;
			default:
				$message = '';
				break;
		}
		echo "<div class=\"alert alert-danger\"><strong>Error:</strong> {$message}</div>";
		require_once ('modules/Settings/wizardPaso1.php');
	} else {
		$smarty = new vtigerCRM_Smarty ();
		$platform = (!is_admin ($current_user)) && (isset ($_SESSION ['plat'])) ? $_SESSION ['plat'] : null;
		if ($moduleType == 'Simple') {
			$newModule = Module::getInstance ()
				->setLabel ($moduleLabel)
				->setMenuLabel ($parentModule)
				->setName ($moduleName)
				->setPresence (Module::PRESENCE_VISIBLE)
				->setShowInAdminConsole (true)
				->setType (Module::TYPE_USER);
			ModuleManager::getInstance ($adb)->saveModule ($newModule, true);
			create_tab_data_file ();
			PlatformUtils::clearModuleDuplicationSessionVariables ();
			$smarty->assign ('MODULE_NAME', $moduleName);
			$smarty->assign ('SHOW_PANEL_CONFIGURATION_BUTTON', false);
			$smarty->display ('Settings/ModuleManager/ModuleCreated.tpl');
		} else {
			$protectedVariables = array ('module', 'action', 'Ajax', 'plat');
			$keys               = array_keys ($_POST);
			foreach ($keys as $key) {
				if (!in_array ($key, $protectedVariables)) {
					$_SESSION [ $key ] = SettingsUtils::purify ($_POST, $key);
				}
			}

			if ((!isset ($_POST ['isAdmin'])) && (isset ($_SESSION ['isAdmin']))) {
				unset ($_SESSION ['isAdmin']);
				unset ($_SESSION ['appMadre']);
			} else if (isset ($_POST ['isAdmin'])) {
				unset ($_SESSION ['moduloPadre']);
			}

			$blocks = array ();
			$n      = isset ($_SESSION ['nombreBloque']) ? count ($_SESSION ['nombreBloque']) : 0;
			for ($i = 0; $i < $n; $i++) {
				if (empty ($_SESSION ['nombreBloque'] [ $i ])) {
					continue;
				}
				$blocks [ $_SESSION ['nombreBloque'] [ $i ] ] = isset ($_SESSION ['visibilidadBloque'][ $i ]) ? $_SESSION ['visibilidadBloque'][ $i ] : 0;
			}

			$smarty->assign ('BLOCKS', $blocks);
			$smarty->assign ('ID_DLG_CREACION_MODULOS', 'dlgCreaModulos');
			$smarty->assign ('MOD', $mod_strings);
			$smarty->assign ('MODULE', SettingsUtils::purify ($_REQUEST, 'module'));
			$smarty->assign ('THEME', $theme);
			$smarty->display ('Settings/ModuleManager/wizardPaso2.tpl');
		}
	}
