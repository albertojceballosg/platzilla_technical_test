<?php
	require_once ('Smarty_setup.php');
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

	$fieldsData = array ();
	foreach ($fieldModuleAccesses as $moduleName => $moduleLabel) {
		$result = getDefOrgFieldList ($moduleName);
		if ((!$result) || ($adb->num_rows ($result) == 0)) {
			$fieldsData [ $moduleName ] = array ();
			continue;
		}

		$languageStrings = return_module_language ($current_language, $moduleName);

		$fieldData = array ();
		while ($row = $adb->fetchByAssoc ($result)) {
			$fieldType        = explode ('~', $row ['typeofdata']);
			$fieldDisplayType = $row ['displaytype'];
			$fieldLabel       = $row ['fieldlabel'];
			$fieldPresence    = $row ['presence'];
			$mandatory        = '';
			if ($fieldType [1] == 'M') {
				$mandatory = '<span style="color: red">*</span>';
			}

			$fieldData [] = (isset ($languageStrings [ $fieldLabel ])) && ($languageStrings [ $fieldLabel ]) ? "{$mandatory} {$languageStrings [ $fieldLabel ]}" : "{$mandatory} {$fieldLabel}";

			$attributes = array ();
			if ($fieldType [1] == 'M') {
				$attributes ['disabled'] = 'disabled';
			}
			if (($row ['visible'] == 0) && ($fieldDisplayType != 3) && ($row ['presence'] != 0)) {
				if ($fieldLabel == 'Activity Type') {
					$attributes ['disabled'] = 'disabled';
				}
				$checked = true;
			} else if (($fieldDisplayType == 3) || ($row ['presence'] == '0')) {
				$checked = true;
			} else {
				$checked = false;
			}
			$fieldData [] = HtmlGenerator::renderCheckbox (null, $row ['fieldid'], null, '', $checked, $attributes);
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
	$smarty->assign ('MODE', 'edit');
	$smarty->display ('Settings/FieldAccess.tpl');
