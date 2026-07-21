<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/preloaded_tasks/lib/PrecreatedTaskUtils.class.php');

	global $adb, $currentModule, $mod_strings, $theme, $site_URL;

	setBugSnag ($site_URL);

	$record = PlatzillaUtils::purify ($_GET, 'record', null);
	
	try {
		$preCreatedTask = new PrecreatedTaskUtils ();
		
		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('AREA_TASK', $preCreatedTask->fetchAreaActivityById ($record));
		$smarty->assign ('MOD', $mod_strings);
		if (isset ($_SESSION ['flashmessage'])) {
			$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
			$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
			unset ($_SESSION ['flashmessage']);
		}
		$smarty->display ('modules/preloaded_tasks/EditViewArea.tpl');
	} catch (Exception $e) {
		$smarty->assign ('HOW_USE', null);
		$smarty->assign ('IS_ERROR', true);
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->display ('modules/preloaded_tasks/ListView.tpl');
	}
