<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/UserInfoUtil.php');

	global $adb;

	$groupDescription = PlatzillaUtils::purify ($_POST, 'groupdescription');
	$groupId          = PlatzillaUtils::purify ($_POST, 'groupid');
	$groupName        = PlatzillaUtils::purify ($_POST, 'groupname');
	$groupMembers     = PlatzillaUtils::purify ($_POST, 'groupmembers');

	try {
		if (empty ($groupName)) {
			throw new Exception ('No se ha suministrado el nombre del grupo');
		}

		$whereClauses = array ('groupname=?');
		$arguments    = array ($groupName);
		if (!empty ($groupId)) {
			$whereClauses [] = 'groupid!=?';
			$arguments []    = $groupId;
		}
		$whereClause = join (' AND ', $whereClauses);
		$result      = $adb->pquery ("SELECT groupname FROM vtiger_groups WHERE {$whereClause}", $arguments);
		if (($result) && ($adb->num_rows ($result) > 0)) {
			throw new Exception ("Ya existe un grupo con el nombre '{$groupName}'");
		}

		$result = $adb->pquery ('SELECT user_name FROM vtiger_users WHERE user_name=?', array ($groupName));
		if (($result) && ($adb->num_rows ($result) > 0)) {
			throw new Exception ("Ya existe un usuario con el nombre '{$groupName}'");
		}

		$members = array ();
		if (!empty ($groupMembers)) {
			foreach ($groupMembers as $member) {
				$dummy = explode ('::', $member);
				$id    = $dummy [1];
				switch ($dummy [0]) {
					case 'group':
						$type = 'groups';
						break;
					case 'role':
						$type = 'roles';
						break;
					case 'rs':
						$type = 'rs';
						break;
					default:
						$type = 'users';
						break;
				}

				if (!isset ($members [ $type ])) {
					$members [ $type ] = array ();
				}
				$members [ $type ][] = $id;
			}
		}

		if (empty ($groupId)) {
			createGroup ($groupName, $members, $groupDescription);
		} else {
			updateGroup ($groupId, $groupName, $members, $groupDescription);
		}
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'Se ha guardado la información del grupo',
		);
		header ('Location: index.php?module=Settings&action=listgroups&parenttab=Settings');
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
		header ("Location: index.php?module=Settings&action=GroupEditView&parenttab=Settings&groupId={$groupId}");
	}
	exit ();
