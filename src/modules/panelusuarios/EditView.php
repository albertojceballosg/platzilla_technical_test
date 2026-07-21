<?php
	require_once ('Smarty_setup.php');
	require_once ('include/Webservices/Utils.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/panelusuarios/lib/UsersHelper.class.php');
	require_once ('modules/Users/Users.php');

	global $adb, $currentModule, $current_user;

	$smarty = new vtigerCRM_Smarty ();
	if (!is_admin ($current_user)) {
		$smarty->assign ('IS_ADMIN', false);
		$smarty->display ('AccessDenied.tpl');
		exit ();
	}

	try {
		$availableAvatarFileNames = UsersHelper::getAvatarFileNames ();
		$userId                   = PlatzillaUtils::purify ($_REQUEST, 'record');
		if (!empty ($userId)) {
			$user               = UsersHelper::getUser ($adb, $userId);
			$userAvatarFileName = UsersHelper::getUserAvatarFileName ($_SESSION ['plat'], $user->column_fields ['imagename'], $availableAvatarFileNames);
			if (empty ($userAvatarFileName)) {
				$userImageUri = "{$_SESSION ['plat']}/user_images/{$user->column_fields ['imagename']}";
			} else {
				$userImageUri = null;
			}
		} else {
			$user               = null;
			$userAvatarFileName = null;
			$userImageUri       = null;
		}

		$smarty->assign ('AVAILABLE_AVATAR_FILE_NAMES', $availableAvatarFileNames);
		$smarty->assign ('AVAILABLE_ROLES', UsersHelper::getRoles ($adb));
		$smarty->assign ('MODULE_NAME', $currentModule);
		$smarty->assign ('RECORD', $userId);
		$smarty->assign ('USER_AVATAR_FILE_NAME', $userAvatarFileName);
		$smarty->assign ('USER_IMAGE_URI', $userImageUri);
		if (isset ($_SESSION ['flashmessage']['data'])) {
			$smarty->assign ('USER', $_SESSION ['flashmessage']['data']);
			unset ($_SESSION ['flashmessage']['data']);
		} else {
			/** @var Users|stdClass $focus */
			$focus = new Users ();
			if (!empty ($userId)) {
				$focus->retrieve_entity_info ($userId, 'Users');
			}
			$smarty->assign ('USER', $focus->column_fields);
		}
		if (isset ($_SESSION ['flashmessage'])) {
			$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
			$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
			unset ($_SESSION ['flashmessage']);
		}
		$smarty->display ('modules/panelusuarios/EditView.tpl');
	} catch (Exception $e) {
		$smarty->assign ('LABEL', 'Volver');
		$smarty->assign ('MESSAGE', 'El usuario solicitado no se encuentra registrado');
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->assign ('URL', 'index.php?module=panelusuarios&action=indexs&parenttab=Settings');
		$smarty->display ('Message.tpl');
	}
