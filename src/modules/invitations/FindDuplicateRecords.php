<?php
	require_once ('data/CRMEntity.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/UserInfoUtil.php');
	require_once ('include/utils/utils.php');
	require_once ('include/utils/VtlibUtils.php');

	global $mod_strings, $app_strings, $current_language, $theme, $adb, $smarty;

	$reqModule      = isset ($_REQUEST ['module']) ? vtlib_purify ($_REQUEST ['module']) : '';
	$returnModule   = isset ($_REQUEST ['module']) ? vtlib_purify ($_REQUEST ['module']) : '';
	$deleteIdString = isset ($_REQUEST ['idlist']) ? vtlib_purify ($_REQUEST ['idlist']) : '';
	$parentTab      = getParenttab ();

	if ((!isset ($smarty)) || (empty ($smarty))) {
		require_once ('Smarty_setup.php');
		$smarty = new vtigerCRM_Smarty ();
	}

	$focus = CRMEntity::getInstance ($reqModule);

	$idsList = array ();
	if (isset ($_REQUEST ['del_rec'])) {
		$deleteIdArray = explode (',', $deleteIdString, -1);
		foreach ($deleteIdArray as $id) {
			if (isPermitted ($reqModule, 'Delete', $id) == 'yes') {
				$sql    = 'UPDATE vtiger_crmentity SET deleted=1 WHERE crmid=?';
				$result = $adb->pquery ($sql, array ($id));
				DeleteEntity ($reqModule, $returnModule, $focus, $id, '');
			} else {
				$idsList [] = $id;
			}
		}
	}
	if (count ($idsList) > 0) {
		$ret      = getEntityName ($reqModule, $idsList);
		$errorMsg = (count ($ret) > 0) ? implode (',', $ret) : '';
		$smarty->assign ('APP_STRINGS', $app_strings);
		$smarty->assign ('THEME', $theme);
		$smarty->assign ('MODULE', $reqModule);
		$smarty->assign ('ERROR_MESSAGE', $errorMsg);
		$smarty->display ('DuplicateRecordsError.tpl');
	} else {
		require ('include/saveMergeCriteria.php');
		$retArr        = getDuplicateRecordsArr ($reqModule);
		$fldValues     = $retArr [0];
		$totalNumGroup = count ($fldValues);
		$fldName       = $retArr [1];
		$uiType        = $retArr [2];
		$buttonDel     = (isPermitted ($reqModule, 'Delete', '') == 'yes') ? $app_strings['LBL_MASS_DELETE'] : '';

		$smarty->assign ('NAVIGATION', $retArr ['navigation']);
		$smarty->assign ('MODULE', $reqModule);
		$smarty->assign ('NUM_GROUP', $totalNumGroup);
		$smarty->assign ('FIELD_NAMES', $fldName);
		$smarty->assign ('CATEGORY', $parentTab);
		$smarty->assign ('ALL_VALUES', $fldValues);
		$smarty->assign ('DELETE', $buttonDel);
		$smarty->assign ('MOD', return_module_language ($current_language, $reqModule));
		$smarty->assign ('THEME', $theme);
		$smarty->assign ('IMAGE_PATH', "themes/$theme/images/");
		$smarty->assign ('APP', $app_strings);
		$smarty->assign ('CMOD', $mod_strings);
		$smarty->assign ('MODE', 'view');
		if (isset ($_REQUEST ['button_view'])) {
			$smarty->assign ('VIEW', 'true');
		}
		$smarty->display ((isset ($_REQUEST ['ajax']) && ($_REQUEST ['ajax'] != '')) ? 'FindDuplicateAjax.tpl' : 'FindDuplicateDisplay.tpl');
	}
