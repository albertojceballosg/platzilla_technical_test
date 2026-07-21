<?php
	require_once ('vtigerversion.php');
	require_once ('Smarty_setup.php');
	require_once ("data/Tracker.php");
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ("include/utils/PlatzillaUtils.class.php");
	require_once ("include/utils/utils.php");
	require_once ('vtlib/Vtiger/Language.php');

	global $adb, $current_language, $currentModule, $default_charset, $display_language, $plat, $moduleList, $vtiger_current_version;

	if (isset ($_GET ['impersonationtoken'])) {
		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('IMPERSONATION_TOKEN', vtlib_purify ($_GET ['impersonationtoken']));
		if (isset ($_SESSION ['login_error'])) {
			$smarty->assign ('LOGIN_ERROR', $_SESSION ['login_error']);
			unset ($_SESSION ['login_error']);
		}
		$smarty->display ('AnonymousInstanceLogin.tpl');
	} else if ((isset ($_GET ['user'])) && ($_GET ['user'] == 'guest')) {
		$smarty = new vtigerCRM_Smarty ();
		$smarty->display ('GuestInstanceLogin.tpl');
	} else if (isset ($_GET ['token'])) {
		$token         = PlatzillaUtils::purify ($_GET, 'token');
		$encodedEmail  = substr ($token, 0, 40);
		$plainPassword = substr ($token, 40);
		$result        = $adb->pquery ('SELECT * FROM vtiger_instances WHERE SHA1(administrator)=? AND status=?', array ($encodedEmail, 'unverified'));
		if ($adb->num_rows ($result) > 0) {
			$row    = $adb->fetchByAssoc ($result, -1, false);
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('PLAIN_PASSWORD', $plainPassword);
			$smarty->assign ('USER_NAME', $row ['administrator']);
			$smarty->display ('TokenInstanceLogin.tpl');
		} else {
			header ('Location: index.php');
		}
		DatabaseUtils::closeResult ($result);
		$result = null;
	} else {
		define ("IN_LOGIN", true);
		$current_module_strings = return_module_language ($current_language, 'Users');
		$current_module_strings ['VLD_ERROR'] = base64_decode ('UGxlYXNlIHJlcGxhY2UgdGhlIFN1Z2FyQ1JNIGxvZ29zLg==');
		$app_strings = return_application_language ('en_us');

		$result = $adb->query ('SELECT * FROM vtiger_organizationdetails');
		//Handle for allowed organation logo/logoname likes UTF-8 Character
		$companyDetails             = array ();
		$companyDetails ['name']    = $adb->query_result ($result, 0, 'organizationname');
		$companyDetails ['website'] = $adb->query_result ($result, 0, 'website');
		$companyDetails ['logo']    = decode_html ($adb->query_result ($result, 0, 'logoname'));

		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ("APP", $app_strings);
		$smarty->assign ("COMPANY_DETAILS", $companyDetails);
		$smarty->assign ("FONDO", rand (1, 4));
		$smarty->assign ("IMAGE_PATH", "include/images/");
		$smarty->assign ("LBL_CHARSET", isset ($app_strings ['LBL_CHARSET']) ? $app_strings ['LBL_CHARSET'] : $default_charset);
		$smarty->assign ("PRINT_URL", "phprint.php?jt=" . session_id () . $GLOBALS['request_string']);
		$smarty->assign ("VTIGER_VERSION", $vtiger_current_version);
		if (isset ($_SESSION ['flashmessage'])) {
			$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
			$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
			unset ($_SESSION ['flashmessage']);
		}
		if (obtenerValorVariable ('multilanguage', 'Users') == 'true') {
			$idioma = get_select_options_with_id (Vtiger_Language::getAll (), $display_language);
			$smarty->assign ("IDIOMA", $idioma);
		}
		$smarty->display ('Login.tpl');
	}
