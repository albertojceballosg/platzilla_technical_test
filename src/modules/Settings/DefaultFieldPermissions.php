<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/HtmlGenerator.class.php');
	require_once ('include/utils/UserInfoUtil.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb, $app_strings, $current_language, $mod_strings, $theme;
	if (!isset ($adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	$fieldModuleName = SettingsUtils::purify ($_REQUEST, 'fld_module');

	$fieldModuleAccesses = array ();
	$result              = $adb->query ('SELECT DISTINCT(t.name), t.tablabel FROM vtiger_profile2field pf INNER JOIN vtiger_tab t ON t.tabid=pf.tabid');
	if (($result) && ($adb->num_rows ($result) > 0)) {
		while ($row = $adb->fetchByAssoc ($result)) {
			$fieldModuleAccesses [ $row ['name'] ] = getTranslatedString ($row ['tablabel'], $row ['name']);
		}
	}
	asort ($fieldModuleAccesses);

	$fieldsData          = array ();
	foreach ($fieldModuleAccesses as $moduleName => $moduleLabel) {
		$result = getDefOrgFieldList ($moduleName);
		if ((!$result) || ($adb->num_rows ($result) == 0)) {
			$fieldsData [ $moduleName ] = array ();
			continue;
		}

		$languageStrings = return_module_language ($current_language, $moduleName);

		$fieldData = array ();
		while ($row = $adb->fetchByAssoc ($result)) {
			$fieldType    = explode ('~', $row ['typeofdata']);
			$fieldUiType  = $row ['uitype'];
			$fieldLabel   = $row ['fieldlabel'];
			$fieldData [] = (isset ($languageStrings [ $fieldLabel ])) && ($languageStrings [ $fieldLabel ]) ? $languageStrings [ $fieldLabel ] : $fieldLabel;

			if (($row ['visible'] == 0) || ($row ['presence'] != 1) || (($fieldUiType == 117) && ($fieldType [1] == 'M'))) {
				$fieldData [] = HtmlGenerator::renderImage (vtiger_imageurl ('prvPrfSelectedTick.gif', $theme));
			} else {
				$fieldData [] = HtmlGenerator::renderImage (vtiger_imageurl ('no.gif', $theme));
			}
		}
		$fieldsData [ $moduleName ] = array_chunk (array_chunk ($fieldData, 2), 4);
	}

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('CMOD', $mod_strings);
	$smarty->assign ('DEF_MODULE', $fieldModuleName ? $fieldModuleName : 'Calendar');
	$smarty->assign ('FIELD_INFO', $fieldModuleAccesses);
	$smarty->assign ('FIELD_LISTS', $fieldsData);
	$smarty->assign ('MOD', return_module_language ($current_language, 'Settings'));
	$smarty->assign ('MODE', 'view');
	$smarty->display ('Settings/FieldAccess.tpl');
