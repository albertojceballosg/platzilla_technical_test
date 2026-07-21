<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/grid_view/lib/GridViewHelper.class.php');

	global $adb, $app_strings, $current_user, $mod_strings, $theme, $site_URL;
	setBugSnag ($site_URL);

	$smarty = new vtigerCRM_Smarty ();
	if (!is_admin ($current_user)) {
		$smarty->assign ('APP', $app_strings);
		$smarty->assign ('ICON_URL', vtiger_imageurl ('denied.gif', $theme));
		$smarty->display ('AccessDenied.tpl');
		exit ();
	}
	$gridViewId = PlatzillaUtils::purify ($_GET, 'record');
	try {
		$availableBoxes = GridViewHelper::fetchAvailableBoxes ($adb);
		if (empty($availableBoxes)) {
			throw new Exception ('No hay cuadriculas disponibles');
		}
		$availableModules = GridViewHelper::fetchAvailableModules ($adb);
		if (empty($availableModules)) {
			throw new Exception ('No hay modulos disponibles');
		}

		$smarty->assign ('AVAILABLE_BOXES',$availableBoxes);
		$smarty->assign ('AVAILABLE_MODULES',$availableModules);
		$smarty->assign ('AVAILABLE_POSITION',GridViewInterface::GRID_VIEW_POSITION);
		$smarty->assign ('GRID_VIEW', GridViewHelper::fetchGridViewById($adb, $record));
		$smarty->assign ('IS_INSTANCE', !empty ($_SESSION ['platInstancia']) ? true : false);
		$smarty->assign ('MOD', $mod_strings);
	} catch (Exception $e) {
		$smarty->assign ('LABEL', 'Volver');
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->assign ('URL', 'index.php?module=grid_view&action=ListView&parenttab=Settings');
		$smarty->display ('Message.tpl');
	}
	$smarty->display ('modules/grid_view/EditView.tpl');
