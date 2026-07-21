<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/backgroundtasks/lib/BackgroundTasksUtils.class.php');
	require_once ('modules/Settings/lib/CreateCustomButtonHelper.class.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb, $current_language;

	$keyword    = (isset ($_SESSION ['queryFiltroForModule'])) && (!empty ($_SESSION ['queryFiltroForModule'])) ? $_SESSION ['queryFiltroForModule'] : null;
	$moduleName = SettingsUtils::purify ($_GET, 'formodule');

	$isInstance     = !empty ($_SESSION ['platInstancia']);
	$modulesData    = CreateCustomButtonHelper::getVisibleModulesData ($adb, $keyword);
	$availableViews = CreateCustomButtonHelper::getViewsAvailable ();
	$buttonTypes    = CreateCustomButtonHelper::getTypesAvailable ();

	if (empty ($modulesData)) {
		$tasks = null;
	} else {
		$moduleNames = array ();
		foreach ($modulesData as $moduleData) {
			$moduleNames [] = $moduleData ['name'];
		}
		$tasks = BackgroundTasksUtils::getTasks ($adb, $moduleNames, $isInstance ? BackgroundTaskInterface::SCOPE_USER : null, true);
	}

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('AVAILABLE_BACKGROUND_TASKS', $tasks);
	$smarty->assign ('IS_INSTANCE', $isInstance);
	$smarty->assign ('MOD', return_module_language ($current_language, 'Settings'));
	$smarty->assign ('MODULE_NAME', $moduleName);
	$smarty->assign ('MODULESFREE', $modulesData);
	$smarty->assign ('TIPOSBOTON', $buttonTypes);
	$smarty->assign ('VISTASDISPONIBLES', $availableViews);
	$smarty->display ('Settings/CreateCustomButton.tpl');
