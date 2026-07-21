<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Settings/lib/PanelViewHelper.class.php');

	global $adb, $current_user, $mod_strings;

	$smarty = new vtigerCRM_Smarty();
	if (!is_admin ($current_user)) {
			$smarty->assign ('IS_ADMIN', false);
			$smarty->display ('AccessDenied.tpl');
			exit ();
	}
	try {
		$modules = PanelViewHelper::fetchAvailableModules ($adb, $current_user->id);
		if (isset ($_SESSION ['flashmessage']['data']) && !empty ($_SESSION ['flashmessage']['data'])) {
			$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
			$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
			$_SESSION ['flashmessage']['data'] = null;
		}
		$smarty->assign ('MODULES', $modules);
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('CURRENT_USER', $current_user->id);
		$smarty->assign ('MODULE', 'Settings');
		$smarty->assign ('VIEW_STATUS', array('SHOW' => 'Ocultar', 'HIDDEN' => 'Mostrar'));
		$smarty->display ('Settings/TaskViewPanel.tpl');
	
	} catch (Exception $e) {
		$smarty->assign ('MODULES', null);
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
		$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
		
		$smarty->display ('Settings/TaskViewPanel.tpl');
	}
	