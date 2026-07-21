<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/Translator.class.php');
	require_once ('modules/Settings/lib/ModuleManagerHelper.class.php');

	global $adb, $current_user, $theme;

	$smarty = new vtigerCRM_Smarty ();
	if ((!empty ($_SESSION ['platInstancia'])) || (!is_admin ($current_user))) {
		$smarty->assign ('APP', Translator::getApplicationDictionary ());
		$smarty->assign ('ICON_URL', vtiger_imageurl ('denied.gif', $theme));
		$smarty->display ('AccessDenied.tpl');
		exit ();
	}

	$allModules                 = ModuleManagerHelper::fetchModules ($adb);
	$moduleApplications         = ModuleManagerHelper::fetchModuleApplications ($adb, array_merge ($allModules ['admin'], $allModules ['tool'], $allModules ['user']));
	$applications               = ModuleManagerHelper::fetchApplications ($adb);
	$availableEntityTypeModules = ModuleManagerHelper::fetchAvailableEntityTypeModules ($adb);
	$availableFieldTypes        = ModuleManagerHelper::fetchAvailableFieldTypes ();
	$availableGlobalPicklists   = ModuleManagerHelper::fetchAvailableGlobalPicklists ($adb);
	$availableMenus             = ModuleManagerHelper::fetchAvailableMenus ($adb);

	$smarty->assign ('ADMIN_MODULES', $allModules ['admin']);
	$smarty->assign ('APP', Translator::getApplicationDictionary ());
	$smarty->assign ('APPLICATIONS', $applications);
	$smarty->assign ('AVAILABLE_ENTITY_TYPE_MODULES', $availableEntityTypeModules);
	$smarty->assign ('AVAILABLE_FIELD_TYPES', $availableFieldTypes);
	$smarty->assign ('AVAILABLE_GLOBAL_PICKLISTS', $availableGlobalPicklists);
	$smarty->assign ('AVAILABLE_MENUS', $availableMenus);
	$smarty->assign ('MOD', Translator::getModuleDictionary ('Settings'));
	$smarty->assign ('MODULE_APPLICATIONS', $moduleApplications);
	$smarty->assign ('ROOT_FOLDER_PATH', PlatzillaUtils::getPlatzillaRootFolderPath ());
	$smarty->assign ('TOOL_MODULES', $allModules ['tool']);
	$smarty->assign ('USER_MODULES', $allModules ['user']);
	if (isset ($_SESSION ['flashmessage'])) {
		$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
		$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
		unset ($_SESSION ['flashmessage']);
	}
	$smarty->display ('Settings/ModuleManager/ModuleManager.tpl');
