<?php
	require_once ('Smarty_setup.php');
	require_once ('modules/Home/lib/HomeUtils.class.php');
	require_once ('modules/operating_modes/lib/OperatingModesHelper.class.php');
	require_once ('modules/panelusuarios/lib/UsersHelper.class.php');
	require_once ('include/platzilla/Objects/UserInterface.php');

	global $adb, $current_user;

	$availableAvatarFileNames = UsersHelper::getAvatarFileNames ();
	$user                     = UsersHelper::getUser ($adb, $current_user->id);
	$userAvatarFileName       = UsersHelper::getUserAvatarFileName ($_SESSION ['plat'], $user->column_fields ['imagename'], $availableAvatarFileNames);
	if (empty ($userAvatarFileName)) {
		$userImageUri = "{$_SESSION ['plat']}/user_images/{$user->column_fields ['imagename']}";
	} else {
		$userImageUri = null;
	}
	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('AVAILABLE_AVATAR_FILE_NAMES', $availableAvatarFileNames);
	$smarty->assign ('AVAILABLE_DEFAULT_MODULES', HomeUtils::getAvailableDefaultModules ($adb));
	$smarty->assign ('AVAIABLE_OPERATING_MODES', OperatingModesHelper::getInstance()->fetchAvailableOperatingModes ());
	$smarty->assign ('AVAILABLE_TABS', UserInterface::HOME_TABS);
	$smarty->assign ('MODULE_NAME', 'panelusuarios');
	$smarty->assign ('MODULE_WITH_TABS', array ('Home'));
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('USER', $user->column_fields);
	$smarty->assign ('USER_AVATAR_FILE_NAME', $userAvatarFileName);
	$smarty->assign ('USER_IMAGE_URI', $userImageUri);
	if (isset ($_SESSION ['flashmessage'])) {
		$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
		$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
		unset ($_SESSION ['flashmessage']);
	}
	$smarty->display ('modules/panelusuarios/EditUserProfile.tpl');
