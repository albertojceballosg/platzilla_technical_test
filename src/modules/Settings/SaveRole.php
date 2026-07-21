<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Settings/lib/RoleHelper.class.php');

	global $adb, $current_user;

	$parentRoleId = PlatzillaUtils::purify ($_POST, 'parentroleid');
	$profileIds   = PlatzillaUtils::purify ($_POST, 'profileids');
	$roleId       = PlatzillaUtils::purify ($_POST, 'roleid');
	$roleName     = PlatzillaUtils::purify ($_POST, 'rolename');

	try {
		if (!is_admin ($current_user)) {
			throw new Exception ('Acceso denegado');
		}

		$arguments = array (
			'parentroleid' => $parentRoleId,
			'profileids'   => $profileIds,
			'roleid'       => $roleId,
			'rolename'     => $roleName,
		);
		RoleHelper::saveRole ($adb, $arguments);
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'El rol ha sido guardado',
		);
		header ('Location: index.php?module=Settings&action=listroles&parenttab=Settings');
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
			'data'    => array (
				'parentroleid' => $parentRoleId,
				'profileids'   => $profileIds,
				'roleid'       => $roleId,
				'rolename'     => $roleName,
			),
		);
		$roleIdUriPart             = !empty ($roleId) ? "&roleid={$roleId}" : '';
		$parentRoleIdUriPart       = !empty ($parentRoleId) ? "&parentroleid={$parentRoleId}" : '';
		header ("Location: index.php?module=Settings&action=RoleEditView{$roleIdUriPart}{$parentRoleIdUriPart}&parenttab=Settings");
	}
	exit ();
