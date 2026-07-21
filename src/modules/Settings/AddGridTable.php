<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Managers/FieldManager.php');
	require_once ('include/platzilla/Managers/ModuleEditPermissionManager.php');
	require_once ('include/platzilla/Managers/ModuleManager.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/jQueryUtils.php');
	require_once ('include/utils/PlatformUtils.class.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/calculated_fields/CalculatedFields.class.php');
	require_once ('modules/PickList/PickListUtils.php');
	require_once ('modules/Settings/lib/LayoutBlockListHelper.class.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');
	require_once ('modules/Settings/lib/WizardUtils.class.php');
	require_once ('vtlib/Vtiger/Language.php');

	global $adb, $app_strings, $current_user, $mod_strings, $theme;

	$fieldModuleName = PlatzillaUtils::purify ($_GET, 'formodule');
	$mode            = PlatzillaUtils::purify ($_GET, 'mode');
	$returnModule    = PlatzillaUtils::purify ($_GET, 'return_module');

	$advFilterOptions = array (
		'e' => '' . $mod_strings['equals'] . '',
		'n' => '' . $mod_strings['not equal to'] . '',
		'c' => '' . $mod_strings['contains'] . '',
		'k' => '' . $mod_strings['does not contain'] . '',
		'l' => '' . $mod_strings['less than'] . '',
		'g' => '' . $mod_strings['greater than'] . '',
		'm' => '' . $mod_strings['less or equal'] . '',
		'h' => '' . $mod_strings['greater or equal'] . '',
	);

	$imagePath       = "themes/{$theme}/images";
	$fieldTypeImages = array (
		"{$imagePath}/text.gif",
		"{$imagePath}/number.gif",
		"{$imagePath}/percent.gif",
		"{$imagePath}/currency.gif",
		"{$imagePath}/date.gif",
		"{$imagePath}/email.gif",
		"{$imagePath}/phone.gif",
		"{$imagePath}/picklist.gif",
		"{$imagePath}/url.gif",
		"{$imagePath}/checkbox.gif",
		"{$imagePath}/text.gif",
		"{$imagePath}/picklist.gif",
		"{$imagePath}/time.PNG",
		"{$imagePath}/bl_bar.jpg",
	);
	$fieldTypes      = array (
		$mod_strings ['Text'],
		$mod_strings ['Number'],
		$mod_strings ['Percent'],
		$mod_strings ['Currency'],
		$mod_strings ['Date'],
		$mod_strings ['Email'],
		$mod_strings ['Phone'],
		$mod_strings ['PickList'],
		$mod_strings ['LBL_URL'],
		$mod_strings ['LBL_CHECK_BOX'],
		$mod_strings ['LBL_TEXT_AREA'],
		$mod_strings ['LBL_MULTISELECT_COMBO'],
		$mod_strings ['Time'],
		$mod_strings ['LBL_PROGRESS_BAR_CONFIG'],
	);

	$activeApplications = LayoutBlockListHelper::getActiveApplicationsByModule ($adb, $fieldModuleName);
	$cfEntries          = PlatformUtils::getFieldListEntries ($adb, $current_user, $fieldModuleName, $activeApplications);
	$lstCampos          = obtieneListaCamposCampoGrid ($cfEntries[0]['module']);

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('ACTIVE_APPLICATIONS', $activeApplications);
	$smarty->assign ('BLOCKS', LayoutBlockListHelper::getModuleBlocks ($adb, $fieldModuleName));
	$smarty->assign ('CFENTRIES', $cfEntries);
	$smarty->assign ('CFIMAGECOMBO', $fieldTypeImages);
	$smarty->assign ('CFTEXTCOMBO', $fieldTypes);
	$smarty->assign ('ENTITY_MODULES', LayoutBlockListHelper::getEntityModules ($adb, $fieldModuleName));
	$smarty->assign ('IS_SUPERADMIN', is_superadmin ($current_user));
	$smarty->assign ('IS_INSTANCE', !empty ($_SESSION ['platInstancia']));
	$smarty->assign ('JS_DATEFORMAT', parse_calendardate ($app_strings ['NTC_DATE_FORMAT']));
	$smarty->assign ('LISTLANGUAGUES', Vtiger_Language::getAll ());
	$smarty->assign ('LISTMODULES', LayoutBlockListHelper::getVisibleModules ($adb));
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('MODE', $mode);
	$smarty->assign ('MODULE', $fieldModuleName);
	$smarty->assign ('MODULE_LABEL', ModuleManager::getInstance ($adb)->fetchModule ($fieldModuleName, true)->getLabel ());
	$smarty->assign ('MODULES', LayoutBlockListHelper::getCustomFieldSupportedModules ($adb, $fieldModuleName));
	$smarty->assign ('RELATED_LISTS', LayoutBlockListHelper::getModuleRelatedLists ($adb, $fieldModuleName));
	$smarty->assign ('REPORTAVAILABLE', LayoutBlockListHelper::getAvailableReport ($adb, $current_user, $fieldModuleName));
	$smarty->assign ('RETURN_MODULE', $returnModule);
	$smarty->assign ('TABLE_FIELDS', $lstCampos);
	$smarty->assign ('THEME', $theme);

	$smarty->assign ('_FLD_MODULE', $fieldModuleName);
	$smarty->assign ('CAMPOS_GRID_DEFINIDOS', obtieneListaCamposCampoGrid ($fieldModuleName));
	$smarty->assign ('MODULE_OPTIONS', WizardUtils::getModuleListAsOptions ($adb));
	$smarty->assign ('MODULE_WITH_GRID', getModulesWithGridFields ($fieldModuleName, '2202'));
	$smarty->assign ('MODULE_WITH_LIST', getModulesWithGridFields ($fieldModuleName, '15'));
	$smarty->assign ('FILTER_CONDITION', $advFilterOptions);
	$smarty->assign ('FIELD_TYPE_OPTIONS', WizardUtils::getFieldTypesAsOptions (true));
	$smarty->assign ('FIELD_GRID_OPTIONS', WizardUtils::getFieldTypesAsOptions (true));
	$smarty->assign ('MODULE', 'Settings');
	$smarty->display ('Settings/AddGridFields.tpl');
