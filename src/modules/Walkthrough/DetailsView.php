<?php
	require_once ('include/utils/PlatzillaUtils.class.php');

	$page = PlatzillaUtils::purify ($_GET, 'page', '2a');

	$smarty = new vtigerCRM_Smarty ();
	$smarty->display ("modules/Walkthrough/DetailsView-{$page}.tpl");
