<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Managers/ApplicationManager.php');
	require_once ('include/platzilla/Managers/ModuleManager.php');
	require_once ('include/platzilla/Managers/ProfileManager.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb, $app_strings, $current_language, $mod_strings;

	$profileName = PlatzillaUtils::purify ($_GET, 'profilename');
	$duplicate   = PlatzillaUtils::purify ($_GET, 'duplicate', false);

	$applications = ApplicationManager::getInstance ($adb)->fetchApplications (true);
	if (!empty ($applications)) {
		$applicationModuleNames = array ();
		foreach ($applications as $application) {
			$applicationModules = $application->getModules ();
			if (empty ($applicationModules)) {
				continue;
			}
			foreach ($applicationModules as $applicationModule) {
				$applicationModuleNames [ $application->getCode () ][] = $applicationModule->getName ();
			}
		}
	} else {
		$applicationModuleNames = null;
	}

	$platformModules = ModuleManager::getInstance ($adb)->fetchModules (false, array ('Documents', 'ModTracker'));
	foreach ($platformModules as $index => $platformModule) {
		if (($platformModule->getPresence () == -1) || ($platformModule->getType () == ModuleInterface::TYPE_TOOL)) {
			unset ($platformModules [ $index ]);
			continue;
		}
		$platformModule->setLabel (getTranslatedString ($platformModule->getLabel (), $platformModule->getName ()));
	}
	$platformModules = array_values ($platformModules);
	usort (
		$platformModules,
		function (Module $moduleA, Module $moduleB) {
			return strcmp ($moduleA->getLabel (), $moduleB->getLabel ());
		}
	);

	if (isset ($_SESSION ['flashmessage']['data'])) {
		$profile = $_SESSION ['flashmessage']['data'];
	} else if ((!empty ($duplicate)) && (!empty ($profileName))) {
		$oldProfile = ProfileManager::getInstance ($adb)->fetchProfile ($profileName);
		if (!empty ($oldProfile)) {
			$oldMainApplicationCode = $oldProfile->getMainApplicationCode ();
			$profile                = $oldProfile->duplicate (null, null, null, null)
				->setSecondaryApplicationCodes (!empty ($oldMainApplicationCode) ? array ($oldMainApplicationCode) : null);
		} else {
			$profile = null;
		}
	} else if (!empty ($profileName)) {
		$profile = ProfileManager::getInstance ($adb)->fetchProfile ($profileName);
	} else {
		$profile = null;
	}

	if (isset ($profile)) {
		$mainApplicationCode       = isset ($profile) ? $profile->getMainApplicationCode () : null;
		$secondaryApplicationCodes = isset ($profile) ? $profile->getSecondaryApplicationCodes () : null;
		if ($profile->getId () == 1) {
			$applicationCode = null;
			$applicationName = 'Todas';
		} else if (!empty ($secondaryApplicationCodes)) {
			foreach ($applications as $application) {
				if ($application->getCode () == $secondaryApplicationCodes [0]) {
					$applicationCode = $application->getCode ();
					$applicationName = $application->getName ();
					break;
				}
			}
		} else {
			$applicationCode = null;
			$applicationName = null;
		}
	} else {
		$mainApplicationCode = null;
		$applicationCode = null;
		$applicationName = null;
	}


	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('APPLICATION_CODE', $applicationCode);
	$smarty->assign ('APPLICATION_MODULE_NAMES', $applicationModuleNames);
	$smarty->assign ('APPLICATION_NAME', $applicationName);
	$smarty->assign ('APPLICATIONS', $applications);
	$smarty->assign ('CMOD', $mod_strings);
	$smarty->assign ('IS_APPLICATION_PROFILE', isset ($profile) ? !empty ($mainApplicationCode) : false);
	$smarty->assign ('MOD', return_module_language ($current_language, 'Settings'));
	$smarty->assign ('MODULES', $platformModules);
	$smarty->assign ('PROFILE', $profile);
	if (isset ($_SESSION ['flashmessage'])) {
		$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
		$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
		unset ($_SESSION ['flashmessage']);
	}
	$smarty->display ('Settings/ProfileEditView.tpl');
