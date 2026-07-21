<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/Translator.class.php');
	require_once ('modules/Settings/lib/RoleHelper.class.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb, $current_user, $theme;

	$smarty = new vtigerCRM_Smarty ();
	if (!is_admin ($current_user)) {
		$smarty->assign ('APP', Translator::getApplicationDictionary ());
		$smarty->assign ('ICON_URL', vtiger_imageurl ('denied.gif', $theme));
		$smarty->display ('AccessDenied.tpl');
		exit ();
	}

	$roleId = PlatzillaUtils::purify ($_GET, 'roleid');
	try {
		if (empty ($roleId)) {
			throw new Exception ('No has suministrado el rol');
		}

		$role = RoleHelper::fetchRole ($adb, $roleId, true);
		if (empty ($role)) {
			throw new Exception ('El rol suministrado no está registrado');
		}

		$roleUsers = RoleHelper::fetchRoleUsers ($adb, $_SESSION ['plat'], $roleId);
		if (!empty ($roleUsers)) {
			usort (
				$roleUsers,
				function (User $userA, User $userB) {
					return strcmp (trim ("{$userA->getFirstName ()} {$userA->getLastName ()}"), trim ("{$userB->getFirstName ()} {$userB->getLastName ()}"));
				}
			);
		}

		$smarty->assign ('ROLE', $role);
		$smarty->assign ('ROLE_USERS', $roleUsers);
		$smarty->display ('Settings/RoleDetailView.tpl');
	} catch (Exception $e) {
		$smarty->assign ('LABEL', 'Volver');
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->assign ('URL', 'javascript:;');
		$smarty->display ('Message.tpl');
	}
