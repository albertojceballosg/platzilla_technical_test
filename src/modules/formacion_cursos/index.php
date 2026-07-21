<?php

	global $currentModule;
	global $smarty;
	
	checkFileAccessForInclusion("modules/$currentModule/ListView.php");
	
	if(isset($_REQUEST['vista']) && $_REQUEST['vista'] != '') {
		$start = vtlib_purify($_REQUEST['vista']);
	} else {
		$start = 2;
	}
	
	$smarty->assign('VISTAORIGEN', $start);
	require_once("modules/$currentModule/ListView.php");

?>
