<?php
	require_once ('include/platzilla/Managers/ProfileManager.php');
	require_once ('include/platzilla/Managers/RoleManager.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb, $current_user;

	$profileName    = PlatzillaUtils::purify ($_POST, 'profilename');
	$newProfileName = PlatzillaUtils::purify ($_POST, 'transferto');

	try {
		if (!is_admin ($current_user)) {
			throw new Exception ('Acceso denegado');
		}

		if (empty ($profileName)) {
			throw new Exception ('No has suministrado el perfil a eliminar');
		}

		$pm                  = ProfileManager::getInstance ($adb);
		$rm                  = RoleManager::getInstance ($adb);
		$profile             = $pm->fetchProfile (urldecode ($profileName));
		$mainApplicationCode = isset ($profile) ? $profile->getMainApplicationCode () : null;
		if (empty ($profile)) {
			throw new Exception ("El perfil {$profileName} no se encuentra registrado");
		} else if (!empty ($mainApplicationCode)) {
			throw new Exception ("El perfil {$profileName} está asignado a una aplicación");
		}

		$newProfile = $pm->fetchProfile ($newProfileName);
		if (!empty ($newProfile)) {
			$roles = $rm->fetchRolesByProfileName ($profile->getName ());
		} else {
			$roles = null;
		}

		if (!empty ($roles)) {
			foreach ($roles as $role) {
				$role->setProfiles (array ($newProfile));
				$rm->saveRole ($role);
			}
		}

		$pm->deleteProfile ($profile);
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'El perfil ha sido eliminado',
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
	}
	header ('Location: index.php?module=Settings&action=ProfileListView&parenttab=Settings');
	exit ();
