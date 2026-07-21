<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/KanbanTaskUtils.class.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/CustomView/lib/CustomViewHelper.class.php');
	
	global $adb, $app_strings, $current_user, $mod_strings, $theme, $site_URL;
	setBugSnag ($site_URL);
	
	$mod_strings = return_module_language ($current_language, 'CustomView');
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
		$viewId          = PlatzillaUtils::purify ($_GET, 'record', null);
		$availableModules = KanbanTaskUtils::fetchAvailableModules ($adb);
		if (empty($availableModules)) {
			throw new Exception ('No hay modulos disponibles');
		}
		if (!empty ($viewId)) {
			$kanbanTask = KanbanTaskUtils::fecthKanbanById ($adb, $viewId);
		}
		
		$smarty->assign ('AVAILABLE_MODULES',$availableModules);
		$smarty->assign ('KANBAN_TASK', isset ($kanbanTask) ? $kanbanTask : null);
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('IS_INSTANCE', $isInstance);
		$smarty->display ('Settings/KanbanTaskEditView.tpl');
	} catch (Exception $e) {
		$smarty->assign ('LABEL', 'Volver');
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->assign ('URL', 'index.php?module=Settings&action=KanbanViewListView&parenttab=Settings&tab=kanban');
		$smarty->display ('Message.tpl');
	}