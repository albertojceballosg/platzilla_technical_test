<?php
	ini_set ('display_errors', 1);
	error_reporting (E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_STRICT);
	set_include_path (get_include_path () . ':' . realpath (__DIR__));
	require_once ('Smarty_setup.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/database/PearDatabase.php');

	$token = PlatzillaUtils::purify ($_GET, 'token');

	$smarty = new vtigerCRM_Smarty ();
	try {
		if (!empty ($token)) {
			$adb    = AdbManager::getInstance ()->getMasterAdb ();
			$adb->connect ();
			$result = $adb->pquery (
				"SELECT i.* FROM vtiger_instances i WHERE MD5(i.code)=?",
				array ($token)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				throw new Exception ('Solicitud inválida');
			}

			$instance = $adb->fetchByAssoc ($result, -1, false);
			$smarty->assign ('INSTANCE', $instance);
			$smarty->assign ('TOKEN', $token);
			$smarty->display ('ForgotPasswordForm.tpl');
		} else {
			$smarty->display ('ForgotPassword.tpl');
		}
	} catch (Exception $e) {
		$smarty->assign ('IS_ERROR', true);
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->display ('ForgotPasswordMessage.tpl');
	}

