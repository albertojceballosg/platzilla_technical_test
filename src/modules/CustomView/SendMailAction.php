<?php
	/*+********************************************************************************
	 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
	 * ("License"); You may not use this file except in compliance with the License
	 * The Original Code is:  vtiger CRM Open Source
	 * The Initial Developer of the Original Code is vtiger.
	 * Portions created by vtiger are Copyright (C) vtiger.
	 * All Rights Reserved.
	 ********************************************************************************/
	require_once ('include/database/PearDatabase.php');
	require_once ('modules/CustomView/CustomView.php');

	global $current_user;
	global $adb;

	$idlist     = vtlib_purify ($_POST['idlist']);
	$viewid     = vtlib_purify ($_REQUEST['viewname']);
	$camodule   = vtlib_purify ($_REQUEST['return_module']);
	$storearray = explode (";", $idlist);
	if (isset($viewid) && trim ($viewid) != "") {
		$oCustomView      = new CustomView();
		$CustomActionDtls = $oCustomView->getCustomActionDetails ($viewid);
		if (isset($CustomActionDtls)) {
			$subject  = $CustomActionDtls["subject"];
			$contents = $CustomActionDtls["content"];
		}
	}

	header ("Location: index.php?action=index&module=$camodule&viewname=$viewid");
?>