<?php
	require_once ('modules/Vtiger/EditView.php');

	/** @var $focus CRMEntity|stdClass */
	global $adb, $focus, $smarty;

	$result = $adb->query ($focus->get_related_list ($focus->id, getTabid ('reservas'), getTabid ('espacios'), array ('SELECT'), true));
	if (($result) && ($adb->num_rows ($result) > 0)) {
		$locations = array ();
		while ($row = $adb->fetchByAssoc ($result, -1, false)) {
			$locations [] = $row;
		}
	} else {
		$locations = null;
	}

	$result = $adb->query ($focus->get_related_list ($focus->id, getTabid ('reservas'), getTabid ('usuarios_colladito'), array ('SELECT'), true));
	if (($result) && ($adb->num_rows ($result) > 0)) {
		$users = array ();
		while ($row = $adb->fetchByAssoc ($result, -1, false)) {
			$users [] = $row;
		}
	} else {
		$users = null;
	}

	$smarty->assign ('RELATED_LOCATIONS', $locations);
	$smarty->assign ('RELATED_USERS', $users);
	$smarty->display ('modules/reservas/EditView.tpl');
