<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/backgroundtasks/lib/BackgroundTasksUtils.class.php');
	require_once ('modules/Settings/lib/CreateCustomButtonHelper.class.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb, $current_language;

	$keyword  = (isset ($_SESSION ['queryFiltroForModule'])) && (!empty ($_SESSION ['queryFiltroForModule'])) ? $_SESSION ['queryFiltroForModule'] : null;
	$buttonId = SettingsUtils::purify ($_REQUEST, 'record');

	$modulesData        = CreateCustomButtonHelper::getVisibleModulesData ($adb, $keyword);
	$customButton       = CreateCustomButtonHelper::getCustomButtonData ($adb, $buttonId);
	$backgroundTaskName = CreateCustomButtonHelper::getBackgroundTaskName ($customButton);
	if (!empty ($backgroundTaskName)) {
		$customButton ['type']               = 'backgroundtask';
		$customButton ['backgroundtaskname'] = $backgroundTaskName;
	}

	$fieldModuleList = null;
	if(! empty($customButton{'module'})) {
		$fieldModuleList = CreateCustomButtonHelper::getModuleColumnsData($adb,$customButton{'module'});
	}

	$isInstance     = !empty ($_SESSION ['platInstancia']) ? true : false;
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
	$smarty->assign ('CUSTOMBUTTON', $customButton);
	$smarty->assign ('FIELD_LIST', $fieldModuleList);
	$smarty->assign ('FILTER_TYPE', CreateCustomButtonHelper::getTypeOfData ());
	$smarty->assign ('MOD', return_module_language ($current_language, 'Settings'));
	$smarty->assign ('MODULESFREE', $modulesData);
	$smarty->assign ('TIPOSBOTON', $buttonTypes);
	$smarty->assign ('VISTASDISPONIBLES', $availableViews);
	$smarty->display ('Settings/EditCustomButton.tpl');
