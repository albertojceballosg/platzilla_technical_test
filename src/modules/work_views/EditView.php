<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/work_views/lib/WorksViewHelper.class.php');
	
	global $adb, $current_user, $mod_strings;
	
	$isInstance  = !empty ($_SESSION ['platInstancia']);
	
	$smarty = new vtigerCRM_Smarty ();
	if (!is_admin ($current_user)) {
		$smarty->assign ('APP', $app_strings);
		$smarty->assign ('ICON_URL', vtiger_imageurl ('denied.gif', $theme));
		$smarty->display ('AccessDenied.tpl');
		exit ();
	}
	try {
		if (isset ($_SESSION ['flashmessage'])) {
			$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
			$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
			unset ($_SESSION ['flashmessage']);
		}
		$viewId = PlatzillaUtils::purify ($_GET, 'record', null);
		
		if (!empty ($viewId)) {
			$jobView = WorksViewHelper::fetchWorkViewById ($adb, $current_user->id, $viewId);
		}
		
		$smarty->assign ('AVAILABLE_VIEWS',WorksViewInterface::VIEWS);
		$smarty->assign ('AVAILABLE_VIEWS_STATUS',WorksViewInterface::VIEWS_STATUS);
		$smarty->assign ('IS_INSTANCE', $isInstance);
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('WORK_VIEW', isset ($jobView) ? $jobView : null);
		$smarty->display ('modules/work_views/EditView.tpl');
	} catch (Exception $e) {
		$smarty->assign ('LABEL', 'Volver');
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->assign ('URL', 'index.php?module=work_views&action=index&parenttab=Settings');
		$smarty->display ('Message.tpl');
	}