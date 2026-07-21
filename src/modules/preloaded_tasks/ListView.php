<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/preloaded_tasks/lib/PrecreatedTaskUtils.class.php');
	require_once ('modules/grid_view/lib/GridViewHelper.class.php');
	
	global $adb, $currentModule, $mod_strings, $site_URL;
	
	setBugSnag ($site_URL);
	
	$selectedTab  = PlatzillaUtils::purify ($_GET, 'tab', 'TASK');
	$returnAction = PlatzillaUtils::purify ($_GET, 'return_action', 'ListView');
	
	$smarty = new vtigerCRM_Smarty ();
	try {
		if ((!empty ($_SESSION ['platInstancia'])) || ($current_user->is_admin != 'on')) {
			throw new Exception ('Solo el usuario administrador de la plataforma madre, puede actulizar las Tareas predefinidas');
		}
		$preCreatedTask   = new PrecreatedTaskUtils ();
		$availableModules = GridViewHelper::fetchAvailableModules ($adb);
		if (empty($availableModules)) {
			throw new Exception ('No hay modulos disponibles');
		}
		
		$smarty->assign ('AVAILABLE_MODULES',$availableModules);
		$smarty->assign ('TASK_LIST', $preCreatedTask->fetchPreCreatedTask ());
		$smarty->assign ('AREA_TASK', $preCreatedTask->fetchAreaActivity ());
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('RETURN_ACTION', $returnAction);
		$smarty->assign ('SELECTED_TAB', $selectedTab);
		if (isset ($_SESSION ['flashmessage'])) {
			$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
			$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
			unset ($_SESSION ['flashmessage']);
		}
		$smarty->display ('modules/preloaded_tasks/ListView.tpl');
	} catch (Exception $e) {
		$smarty->assign ('HOW_USE', null);
		$smarty->assign ('IS_ERROR', true);
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->display ('modules/preloaded_tasks/ListView.tpl');
	}
