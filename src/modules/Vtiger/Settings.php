<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/UserInfoUtil.php');
	require_once ('include/utils/utils.php');
	require_once ('include/utils/VtlibUtils.php');

	global $adb, $current_user, $mod_strings, $theme;

	$action    = PlatzillaUtils::purify ($_REQUEST, 'action');
	$forModule = PlatzillaUtils::purify ($_REQUEST, 'formodule');
	$module    = PlatzillaUtils::purify ($_REQUEST, 'module');

	$smarty = new vtigerCRM_Smarty ();
	if ((!is_admin ($current_user)) && (isPermitted ($module, $action) == 'no')) {
		$smarty->display ('OperationNotPermitted.tpl');
		exit ();
	}

	$menuEntries = array ();

	$menuEntries ['LayoutEditor']['location']  = "index.php?module={$module}&action=LayoutBlockList&parenttab={$module}&formodule={$forModule}";
	$menuEntries ['LayoutEditor']['image_src'] = 'fa fa-tasks yellow-bg';
	$menuEntries ['LayoutEditor']['desc']      = getTranslatedString ('LBL_LAYOUT_EDITOR_DESCRIPTION');
	$menuEntries ['LayoutEditor']['label']     = getTranslatedString ('LBL_LAYOUT_EDITOR');

	if (vtlib_isModuleActive ('FieldFormulas')) {
		$modules = com_vtGetModules ($adb);
		if (in_array (getTranslatedString ($forModule), $modules)) {
			$result = $adb->query ("SELECT * FROM vtiger_settings_field WHERE name='LBL_FIELDFORMULAS' AND active=0");
			if (($result) && ($adb->num_rows ($result) > 0)) {
				$row                                        = $adb->fetchByAssoc ($result, -1, false);
				$menuEntries ['FieldFormulas']['location']  = "{$row ['linkto']}&formodule={$forModule}";
				$menuEntries ['FieldFormulas']['image_src'] = $row ['iconpath'];
				$menuEntries ['FieldFormulas']['desc']      = getTranslatedString ($row ['description'], 'FieldFormulas');
				$menuEntries ['FieldFormulas']['label']     = getTranslatedString ($row ['name'], 'FieldFormulas');
			}
		}
	}

	if (vtlib_isModuleActive ('Tooltip')) {
		$result = $adb->query ("SELECT * FROM vtiger_settings_field WHERE name='LBL_TOOLTIP_MANAGEMENT' AND active=0");
		if (($result) && ($adb->num_rows ($result) > 0)) {
			$row                                  = $adb->fetchByAssoc ($result, -1, false);
			$menuEntries ['Tooltip']['location']  = "{$row ['linkto']}&formodule={$forModule}";
			$menuEntries ['Tooltip']['image_src'] = vtiger_imageurl ($row ['iconpath'], $theme);
			$menuEntries ['Tooltip']['desc']      = getTranslatedString ($row ['description'], 'Tooltip');
			$menuEntries ['Tooltip']['label']     = getTranslatedString ($row ['name'], 'Tooltip');
		}
	}

	$smarty->assign ('MENU_ENTRIES', $menuEntries);
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('MODULE', $forModule);
	$smarty->assign ('MODULE_LBL', getTranslatedString ($forModule));
	$smarty->display ('modules/Vtiger/Settings.tpl');
