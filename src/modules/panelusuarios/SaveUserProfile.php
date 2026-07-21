<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/panelusuarios/lib/UsersHelper.class.php');

	/** @var Users $current_user */
	global $adb, $currentModule, $current_user;

	$defaultModule    = PlatzillaUtils::purify ($_POST, 'default_module');
	$defaultOperating = PlatzillaUtils::purify ($_POST, 'default_operating', 'MANAGEMENT_MODE');
	$defaultTab       = PlatzillaUtils::purify ($_POST, 'default_home_tab', 'ACTIVITY');
	$firstName        = PlatzillaUtils::purify ($_POST, 'first_name', null);
	$lastName         = PlatzillaUtils::purify ($_POST, 'last_name', null);
	$userImage        = PlatzillaUtils::purify ($_POST, 'userimage');

	$rootFolderPath = PlatzillaUtils::getPlatzillaRootFolderPath ();

	try {
		if (empty ($lastName)) {
			throw new Exception ('No se han suministrado los apellidos del usuario');
		} else if ((empty ($userImage)) || (empty ($userImage ['type'])) || (!in_array ($userImage ['type'], array ('AVATAR', 'IMAGE')))) {
			throw new Exception ('No has suministrado un método para adjuntar la imagen de perfil');
		} else if (($userImage ['type'] == 'AVATAR') && (empty ($userImage ['uri'])) && (empty ($userImage ['data']))) {
			throw new Exception ('No has seleccionado el avatar');
		} else if (($userImage ['type'] == 'AVATAR') && (!empty ($userImage ['data'])) && (!file_exists ("{$rootFolderPath}/modules/{$currentModule}/avatars/{$userImage ['data']}"))) {
			throw new Exception ('El avatar seleccionado no existe');
		} else if (($userImage ['type'] == 'IMAGE') && (empty ($userImage ['uri'])) && (empty ($userImage ['data']))) {
			throw new Exception ('No has suministrado una imagen de perfil');
		}

		$userProfileDetails = array (
			'default_module'    => $defaultModule,
			'default_operating' => $defaultOperating,
			'default_home_tab'  => $defaultTab,
			'first_name'        => $firstName,
			'last_name'         => $lastName,
		);
		UsersHelper::updateUserProfile ($adb, $current_user->id, $userProfileDetails);
		UsersHelper::saveImage ($adb, $_SESSION ['plat'], $current_user->id, $userImage ['type'], $userImage ['data']);
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'Se ha actualizado el perfil de usuario',
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
			'data'    => $_POST,
		);
	}
	header ('Location: index.php?module=panelusuarios&action=EditUserProfile');
	exit ();
