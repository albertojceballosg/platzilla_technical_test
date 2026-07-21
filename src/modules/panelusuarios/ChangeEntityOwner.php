<?php
	require_once ('Smarty_setup.php');
	require_once ('data/CRMEntity.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb, $app_strings, $currentModule, $current_user, $mod_strings, $theme, $site_URL;
	setBugSnag ($site_URL);

	$userId       = PlatzillaUtils::purify ($_GET, 'record');
	$instanceName = !empty ($_SESSION ['platInstancia']) ? $_SESSION ['platInstancia'] : null;

	$smarty = new vtigerCRM_Smarty ();
	if (!is_admin ($current_user)) {
		$smarty->assign ('IS_ADMIN', false);
		$smarty->display ('AccessDenied.tpl');
		exit ();
	}
	try {
		if (empty($userId)) {
			throw new Exception ('Usuario no encontrado!');
		} else {
			$result = $adb->pquery ('SELECT * FROM vtiger_users WHERE id=?', array ($userId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				throw new Exception ('El usuario solicitado no se encuentra registrado');
			}
		}

		$userList = str_replace ('value=1>', 'value=1 selected="selected">', getUserslist(false));

		$smarty->assign ('APP', $app_strings);
		$smarty->assign ('ASSINGN_TYPE', 'U');
		$smarty->assign ('CHANGE_OWNER', $userList);
		$smarty->assign ('MASS_EDIT', '0');
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('MODULE', 'panelusuarios');
		$smarty->assign ('MODE', 'edit');
		$smarty->assign ('RECORD', $userId);
		$smarty->assign ('RETURN_ACTION', 'USER-SAVE');
	} catch (Exception $e) {
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('TYPE', 'ERROR');
	}
	$smarty->display ('ChangeAndDeleteEntityOwnerModal.tpl');
