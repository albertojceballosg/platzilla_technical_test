<?php
	require_once('Smarty_setup.php');
	require_once('include/utils/utils.php');
	require_once('database/DatabaseConnection.php');

	global $mod_strings, $app_strings, $app_list_strings;
	global $current_language, $currentModule, $current_userid, $theme;
	global $adb, $image_path;

	require_once('modules/Vtiger/layout_utils.php');
	$appStrings=$app_strings;
	$button_del='';
	$reqModule = vtlib_purify($_REQUEST['module']);
	$focus = CRMEntity::getInstance($reqModule);

	$return_module=vtlib_purify($_REQUEST['module']);
	$delete_idstring=vtlib_purify($_REQUEST['idlist']);
	$parentTab = getParenttab();

	$smarty = new vtigerCRM_Smarty;

	$ids_list = array();
	$errorMsg = '';
	if(isset($_REQUEST['del_rec'])) {
		$url = getBasic_Advance_SearchURL();
		$delete_id_array=explode(',',$delete_idstring,-1);

		foreach ($delete_id_array as $id)
		{
			if(isPermitted($reqModule,'Delete',$id) == 'yes') {
				$sql='UPDATE vtiger_crmentity SET deleted=1 WHERE crmid=?';
				$result = $adb->pquery($sql, array($id));
				DeleteEntity($reqModule,$return_module,$focus,$id,'');
			} else {
				$ids_list[] = $id;
	        }
		}
		if(count($ids_list) > 0) {
			$ret = getEntityName($reqModule,$ids_list);
			if(count($ret) > 0) {
	       		    $errorMsg = implode(',',$ret);
			}
			echo "<table border='0' cellpadding='5' cellspacing='0' width='100%'><tr><td align='center'>";
			/** @noinspection HtmlUnknownTarget */
			echo "<div style='border: 3px solid rgb(153, 153, 153); background-color: rgb(255, 255, 255); width: 55%; position: relative; z-index: 10000000;'>

				<table border='0' cellpadding='5' cellspacing='0' width='98%'>
				<tbody><tr>
				<td rowspan='2' width='11%'><img src='themes/images/denied.gif' ></td>
				<td style='border-bottom: 1px solid rgb(204, 204, 204);' nowrap='nowrap' width='70%'>
					<span class='genHeaderSmall'>$appStrings[LBL_DUP_PERMISSION] $reqModule $errorMsg</span></td>
				</tr>
				<tr>
				<td class='small' align='right' nowrap='nowrap'>
				<a href='javascript:window.location.reload();'>$appStrings[LBL_GO_BACK]</a><br>
				</td>
				</tr>
				</tbody></table>
				</div>";
			echo '</td></tr></table>';
			exit;
		}
	}

	require('include/saveMergeCriteria.php');
	$ret_arr=getDuplicateRecordsArr($reqModule);

	$fld_values=$ret_arr[0];
	$total_num_group=count($fld_values);
	$fld_name=$ret_arr[1];
	$ui_type=$ret_arr[2];

	$smarty->assign('NAVIGATION',$ret_arr['navigation']);//Added for page navigation
	$smarty->assign('MODULE',$reqModule);
	$smarty->assign('NUM_GROUP',$total_num_group);
	$smarty->assign('FIELD_NAMES',$fld_name);
	$smarty->assign('CATEGORY',$parentTab);
	$smarty->assign('ALL_VALUES',$fld_values);
	if(isPermitted($reqModule,'Delete','') == 'yes') {
		$button_del = $app_strings['LBL_MASS_DELETE'];
	}
	$smarty->assign('DELETE',$button_del);

	$smarty->assign('MOD', return_module_language($current_language,$reqModule));
	$smarty->assign('THEME', $theme);
	$smarty->assign('IMAGE_PATH',$image_path);
	$smarty->assign('APP', $app_strings);
	$smarty->assign('CMOD', $mod_strings);
	$smarty->assign('MODE','view');
	if(isset($_REQUEST['button_view'])) {
		$smarty->assign('VIEW','true');
	}
	if(isset($_REQUEST['ajax']) && $_REQUEST['ajax'] != '') {
		$smarty->display('FindDuplicateAjax.tpl');
	} else {
		$smarty->display('FindDuplicateDisplay.tpl');
	}
