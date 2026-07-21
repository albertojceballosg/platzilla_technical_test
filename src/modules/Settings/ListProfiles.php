<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/UserInfoUtil.php');
	require_once ('include/utils/utils.php');

	global $adb, $app_strings, $current_language, $current_user, $mod_strings;
	if (!isset ($adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	$userProfileId        = fetchUserProfileId ($current_user->id);
	$empresaFacil         = obtenerValorVariable ('EMPRESA_FACIL', 'Users');
	$gestoriaFacil        = obtenerValorVariable ('GESTORIA_FACIL', 'Users');
	$clienteGestoriaFacil = obtenerValorVariable ('CLIENTE_GESTORIA_FACIL', 'Users');
	$headers              = array ($mod_strings ['LBL_LIST_NO'], $mod_strings ['LBL_LIST_TOOLS'], $mod_strings ['LBL_NEW_PROFILE_NAME'], $mod_strings ['LBL_DESCRIPTION']);

	$profiles = array ();
	$result   = $adb->query ('SELECT * FROM vtiger_profile');
	if (($result) && ($adb->num_rows ($result) > 0)) {
		while ($row = $adb->fetchByAssoc ($result)) {
			$profiles [] = array (
				'del_permission' => ($row ['profileid'] != 1) && ($row ['profileid'] != $userProfileId) ? 'yes' : 'no',
				'description'    => $row ['description'],
				'profileid'      => $row ['profileid'],
				'profilename'    => $row ['profilename'],
			);
		}
	}

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('CMOD', $mod_strings);
	$smarty->assign ('LIST_ENTRIES', $profiles);
	$smarty->assign ('LIST_HEADER', $headers);
	$smarty->assign ('MOD', return_module_language ($current_language, 'Settings'));
	if (($empresaFacil) || ($gestoriaFacil) || ($clienteGestoriaFacil)) {
		$smarty->assign ('MENU', 'false');
	}
	$smarty->display ('UserProfileList.tpl');
