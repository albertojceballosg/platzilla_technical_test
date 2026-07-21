<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatformUtils.class.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/Settings/lib/EditLabelsHelper.class.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');
	require_once ('vtlib/Vtiger/Language.php');

	global $adb, $app_strings, $currentModule, $current_user, $mod_strings, $theme;
	if (!isset ($adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	$fieldModuleName = SettingsUtils::purify ($_REQUEST, 'fld_module');
	$forModuleName   = SettingsUtils::purify ($_REQUEST, 'formodule');
	$language        = SettingsUtils::purify ($_REQUEST, 'lang', 'es_es');
	$mode            = SettingsUtils::purify ($_REQUEST, 'mode');
	$platform        = SettingsUtils::purify ($_SESSION, 'plat', '');

	$moduleName = $forModuleName ? $forModuleName : $fieldModuleName;

	if ($mode == 'edit') {
		$arguments = vtlib_purify ($_REQUEST);
		$variables = array ('fld_module', 'module', 'parenttab', 'action', 'lang', 'mode');
		try {
			EditLabelsHelper::writeLanguageFile ($platform, $moduleName, $language, $arguments, $variables);
		} catch (Exception $ignored) {
			// El error se asignaba a una variable smarty, pero en la plantilla no se utiliza esa variable
		}
	}

	if (!empty ($moduleName)) {
		if (!isFileAccessible ("modules/{$moduleName}/language/{$language}.lang.php")) {
			$language = 'es_es';
		}

		$data           = PlatformUtils::getFieldListEntries ($adb, $current_user, $moduleName);
		$moduleLanguage = return_module_language ($language, $moduleName, false);

		foreach ($data as &$blocks) {
			if (!isset ($blocks ['field'])) {
				continue;
			}
			foreach ($blocks ['field'] as &$fields) {
				if (!isset ($moduleLanguage [ $fields ['fieldlabel'] ])) {
					continue;
				}
				$fields ['label'] = $moduleLanguage [ $fields ['fieldlabel'] ];
				unset ($moduleLanguage [ $fields ['fieldlabel'] ]);
			}
		}

		$applicationLanguage = null;
	} else {
		if (!isFileAccessible ("include/language/{$language}.lang.php")) {
			$language = 'es_es';
		}
		$data                = array ();
		$moduleLanguage      = null;
		$applicationLanguage = return_application_language ($language);
	}

	$languages     = Vtiger_Language::getAll ();

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('FLD_MODULE', $moduleName);
	$smarty->assign ('LANG', $language);
	$smarty->assign ('LANGUAGE', $languages [ $language ]);
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('THEME', $theme);
	if (!empty ($moduleName)) {
		$smarty->assign ('CFENTRIES', $data);
		$smarty->assign ('LISTLABELS', $moduleLanguage);
	} else {
		$smarty->assign ('LISTLABELS', $applicationLanguage);
	}
	$smarty->display ("{$currentModule}/editLabels.tpl");
