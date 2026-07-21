<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');
	global $adb;
	$moduleName  = SettingsUtils::purify ($_REQUEST, 'selectedModule');
	if($moduleName != '-') {
		$relatedList=isPresentRelatedLists($moduleName);
	} else {
		echo false;
		exit;
	}
	if(! empty($relatedList)) {
		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign('MODULE_NAME',$moduleName);
		$smarty->assign('RELATED_LIST', $relatedList);
		$relatedBlck = $smarty->fetch('Settings/relatedListTogrid.tpl');
		echo $relatedBlck;
	} else {
		echo false;
	}
	exit ();
