<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/preloaded_tasks/lib/PrecreatedTaskUtils.class.php');
	require_once ('modules/grid_view/lib/GridViewHelper.class.php');

	global $adb, $currentModule, $mod_strings, $theme, $site_URL;

	setBugSnag ($site_URL);

	$record       = PlatzillaUtils::purify ($_GET, 'record', null);
	
	try {
		$preCreatedTask   = new PrecreatedTaskUtils ();
		$availableModules = GridViewHelper::fetchAvailableModules ($adb);
		if (empty($availableModules)) {
			throw new Exception ('No hay modulos disponibles');
		}
		
		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('AVAILABLE_MODULES',$availableModules);
		$smarty->assign ('TASK', $preCreatedTask->fetchPreCreatedTaskById ($record));
		$smarty->assign ('AREA_TASK', $preCreatedTask->fetchAreaActivity ());
		$smarty->assign ('MOD', $mod_strings);
		if (isset ($_SESSION ['flashmessage'])) {
			$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
			$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
			unset ($_SESSION ['flashmessage']);
		}
		$smarty->display ('modules/preloaded_tasks/EditView.tpl');
	} catch (Exception $e) {
		$smarty->assign ('HOW_USE', null);
		$smarty->assign ('IS_ERROR', true);
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->display ('modules/preloaded_tasks/ListView.tpl');
	}
