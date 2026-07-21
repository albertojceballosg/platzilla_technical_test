<?php
	require_once ('include/platzilla/Managers/ModuleManager.php');
	require_once ('include/platzilla/Managers/ProfileManager.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Settings/lib/ProfileHelper.class.php');

	global $adb;

	$applicationCodes     = PlatzillaUtils::purify ($_POST, 'applicationcodes');
	$globalEditPermission = PlatzillaUtils::purify ($_POST, 'globaleditpermission');
	$globalViewPermission = PlatzillaUtils::purify ($_POST, 'globalviewpermission');
	$profileData          = PlatzillaUtils::purify ($_POST, 'moduleprofiles');
	$profileDescription   = PlatzillaUtils::purify ($_POST, 'description');
	$profileId            = PlatzillaUtils::purify ($_POST, 'profileid');
	$profileName          = PlatzillaUtils::purify ($_POST, 'profilename');

	try {
		if (empty ($profileName)) {
			throw new Exception ('No se ha suministrado el nombre del perfil');
		}

		$applicationCodes = (is_array ($applicationCodes)) && (!empty ($applicationCodes)) ? array_filter ($applicationCodes) : null;

		// Obtener el perfil en la base de datos o crear uno si es nuevo
		$pm = ProfileManager::getInstance ($adb);
		if (!empty ($profileId)) {
			$profile = $pm->fetchProfile ($profileName, true)
				->setId ($profileId);
		} else {
			$profile = Profile::getInstance ()
				->setName ($profileName);
		}
		$profile->setDescription ($profileDescription)
			->setEditPermission (!empty ($globalEditPermission) ? ProfileInterface::PERMISSION_ALLOW : ProfileInterface::PERMISSION_DENY)
			->setSecondaryApplicationCodes ($applicationCodes)
			->setViewPermission (!empty ($globalViewPermission) ? ProfileInterface::PERMISSION_ALLOW : ProfileInterface::PERMISSION_DENY);

		// Obtener los perfiles de módulos, campos y vistas
		$fieldProfiles      = array ();
		$moduleProfiles     = array ();
		$viewProfiles       = array ();
		$modules            = ModuleManager::getInstance ($adb)->fetchModules ();
		$profileModuleNames = array_keys ($profileData);
		foreach ($modules as $thisModule) {
			$moduleName        = $thisModule->getName ();
			$permissions       = isset ($profileData [ $moduleName ]) ? $profileData [ $moduleName ] : null;
			$moduleProfile     = ProfileHelper::getModuleProfile ($adb, $profile, $thisModule, $permissions);
			$moduleProfiles [] = $moduleProfile;

			if (!$thisModule->getIsEntityType ()) {
				continue;
			}

			$fields = $thisModule->getFields ();
			if (!empty ($fields)) {
				foreach ($fields as $field) {
					$fieldName        = $field->getName ();
					$permissions      = isset ($profileData [ $moduleName ]['fieldpermissions'][ $fieldName ]) ? $profileData [ $moduleName ]['fieldpermissions'][ $fieldName ] : null;
					$fieldProfiles [] = ProfileHelper::getFieldProfile ($adb, $profile, $thisModule, $field, $moduleProfile->getAccessPermission (), $permissions);;
				}
			}

			$views = $thisModule->getViews ();
			if (!empty ($views)) {
				foreach ($views as $view) {
					$viewName        = $view->getName ();
					$defaultView     = isset ($profileData [ $moduleName ]['defaultview']) ? $profileData [ $moduleName ]['defaultview'] : null;
					$permissions     = isset ($profileData [ $moduleName ]['viewpermissions'][ $viewName ]) ? $profileData [ $moduleName ]['viewpermissions'][ $viewName ] : null;
					$viewProfiles [] = ProfileHelper::getViewProfile ($adb, $profile, $thisModule, $view, $moduleProfile->getAccessPermission (), $permissions, $defaultView);
				}
			}
		}

		$profile->setFieldProfiles (!empty ($fieldProfiles) ? $fieldProfiles : null)
			->setModuleProfiles (!empty ($moduleProfiles) ? $moduleProfiles : null)
			->setViewProfiles (!empty ($viewProfiles) ? $viewProfiles : null);
		$pm->saveProfile ($profile);

		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'El perfil ha sido guardado',
		);
		header ('Location: index.php?module=Settings&action=ProfileListView&parenttab=Settings');
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
			'data'    => isset ($profile) ? $profile : null,
		);
		header ('Location: index.php?module=Settings&action=ProfileEditView&parenttab=Settings');
	}
	exit ();
