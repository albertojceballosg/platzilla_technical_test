<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatformUtils.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/panelusuarios/lib/UsersHelper.class.php');
	require_once ('modules/Users/Users.php');

	global $adb, $currentModule, $current_user;

	$firstName        = PlatzillaUtils::purify ($_POST, 'first_name');
	$lastName         = PlatzillaUtils::purify ($_POST, 'last_name');
	$password         = PlatzillaUtils::purify ($_POST, 'user_password');
	$passwordRepeated = PlatzillaUtils::purify ($_POST, 'user_password_repeated');
	$roleId           = PlatzillaUtils::purify ($_POST, 'roleid');
	$status           = PlatzillaUtils::purify ($_POST, 'status');
	$userId           = PlatzillaUtils::purify ($_POST, 'record');
	$userName         = PlatzillaUtils::purify ($_POST, 'user_name');
	$userImage        = PlatzillaUtils::purify ($_POST, 'userimage');

	$instanceName = !empty ($_SESSION ['platInstancia']) ? $_SESSION ['platInstancia'] : null;

	$smarty = new vtigerCRM_Smarty ();
	if (!is_admin ($current_user)) {
		$smarty->assign ('IS_ADMIN', false);
		$smarty->display ('AccessDenied.tpl');
		exit ();
	}

	$totalUsers = UsersHelper::getTotalUsers ($adb);
	$usersLimit = UsersHelper::getUsersLimit ($instanceName);

	if ((!empty ($instanceName)) && ($usersLimit != -1) && ($totalUsers >= $usersLimit) && (empty ($userId))) {
		header ('Location: index.php?module=panelusuarios&action=index&parenttab=Settings');
		exit ();
	}

	try {
		if (empty ($userId)) {
			if (empty ($userName)) {
				throw new Exception ('No has suministrado el nombre de usuario');
			} else if (!filter_var ($userName, FILTER_VALIDATE_EMAIL)) {
				throw new Exception ('El nombre de usuario suministrado no es una dirección de correo electrónico válida');
			} else if (empty ($password)) {
				throw new Exception ('No has suministrado la contraseña');
			} else if (empty ($passwordRepeated)) {
				throw new Exception ('No has suministrado la contraseña repetida');
			} else if ($password != $passwordRepeated) {
				throw new Exception ('Las contraseñas suministradas no coinciden');
			}

			$result = $adb->pquery ('SELECT * FROM vtiger_users WHERE user_name=?', array ($userName));
			if (($result) && ($adb->num_rows ($result) > 0)) {
				throw new Exception ('El nombre de usuario ya se encuentra registrado');
			} else if (PlatformUtils::isInstanceEmailRegistered ($userName)) {
				throw new Exception ("La dirección de correo {$userName} ya está registrada en otra instancia");
			}
		} else {
			$result = $adb->pquery ('SELECT * FROM vtiger_users WHERE id=?', array ($userId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				throw new Exception ('El usuario solicitado no se encuentra registrado');
			} else if ((!empty ($password)) || (!empty ($passwordRepeated))) {
				if (empty ($password)) {
					throw new Exception ('No has suministrado la contraseña');
				} else if (empty ($passwordRepeated)) {
					throw new Exception ('No has suministrado la contraseña repetida');
				} else if ($password != $passwordRepeated) {
					throw new Exception ('Las contraseñas suministradas no coinciden');
				}
			}
		}

		$rootFolderPath = PlatzillaUtils::getPlatzillaRootFolderPath ();
		if (empty ($lastName)) {
			throw new Exception ('No has suministrado los apellidos del usuario');
		} else if (empty ($roleId)) {
			throw new Exception ('No has suministrado el rol del usuario');
		} else if (empty ($status)) {
			throw new Exception ('No has suministrado el status del usuario');
		} else if ((empty ($userImage)) || (empty ($userImage ['type'])) || (!in_array ($userImage ['type'], array ('AVATAR', 'IMAGE')))) {
			throw new Exception ('No has suministrado un método para adjuntar la imagen de perfil');
		} else if (($userImage ['type'] == 'AVATAR') && (empty ($userImage ['uri'])) && (empty ($userImage ['data']))) {
			throw new Exception ('No has seleccionado el avatar');
		} else if (($userImage ['type'] == 'AVATAR') && (!empty ($userImage ['data'])) && (!file_exists ("{$rootFolderPath}/modules/{$currentModule}/avatars/{$userImage ['data']}"))) {
			throw new Exception ('El avatar seleccionado no existe');
		} else if (($userImage ['type'] == 'IMAGE') && (empty ($userImage ['uri'])) && (empty ($userImage ['data']))) {
			throw new Exception ('No has suministrado una imagen de perfil');
		}

		$user = UsersHelper::saveUser (
			$adb,
			array (
				'firstname'        => $firstName,
				'id'               => $userId,
				'lastname'         => $lastName,
				'password'         => $password,
				'passwordrepeated' => $passwordRepeated,
				'roleid'           => $roleId,
				'status'           => $status,
				'username'         => $userName,
			)
		);
		UsersHelper::saveImage ($adb, $_SESSION ['plat'], $user->id, $userImage ['type'], $userImage ['data']);
		UsersHelper::registerInstanceUser ($instanceName, $user);
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'El usuario ha sido guardado',
		);

		header ('Location: index.php?module=panelusuarios&action=index&parenttab=Settings');
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
			'data'    => $_POST,
		);
		header ('Location: index.php?module=panelusuarios&action=EditView&parenttab=Settings' . (!empty ($userId) ? "&record={$userId}" : ''));
	}
	exit ();
