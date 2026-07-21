<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/Settings/lib/ConfigApplicationsHelper.class.php');

	global $adb, $current_language;

	$applicationImagesPath = 'storage/appsimages';
	$applicationsData      = ConfigApplicationsHelper::getApplicationsData ($adb, $applicationImagesPath);

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('APPLICATIONS', $applicationsData);
	$smarty->assign ('IMAGES_PATH', $applicationImagesPath);
	$smarty->assign ('MOD', return_module_language ($current_language, 'Settings'));
	$smarty->assign ('TIMESTAMP', time ());
	if (isset ($_SESSION ['flashmessage'])) {
		$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
		$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
		unset ($_SESSION ['flashmessage']);
	}
	// Si hay errores actualizando, se notifica al usuario
	if (!empty ($_SESSION ['error_update'])) {
		$smarty->assign ('IS_ERROR', true);
		$smarty->assign ('MESSAGE', $_SESSION ['error_update']);
		unset ($_SESSION ['error_update']);
	}
	$smarty->display ('Settings/ConfigApps.tpl');
