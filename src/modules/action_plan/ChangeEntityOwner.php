<?php
	require_once ('Smarty_setup.php');
	require_once ('data/CRMEntity.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb, $app_strings, $currentModule, $current_user, $mod_strings, $theme, $site_URL;
	setBugSnag ($site_URL);

	$record = PlatzillaUtils::purify ($_GET, 'record');
	try {
		$modalTitle  = 'Asignar expediente';
		$focus       = CRMEntity::getInstance ($currentModule);
		$focus->id   = $record;
		$focus->mode = 'edit';
		$focus->retrieve_entity_info ($record, $currentModule);
		$listFieldsName = array_values ($focus->list_fields_name);
		if (count ($listFieldsName) >= 2) {
			$modalTitle = $focus->column_fields [ $listFieldsName[1] ];
		}
		$userSelected = $focus->column_fields['assigned_user_id'];
		$userList     = str_replace ('value=' . $userSelected. '>', 'value=' . $userSelected . ' selected="selected">', getUserslist(false));

		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('APP', $app_strings);
		$smarty->assign ('ASSINGN_TYPE', 'U');
		$smarty->assign ('CHANGE_OWNER', $userList);
		$smarty->assign ('MASS_EDIT', '0');
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('MODULE', $currentModule);
		$smarty->assign ('MODAL_TITLE', $modalTitle);
		$smarty->assign ('MODE', 'edit');
		$smarty->assign ('RECORD', $record);
		$smarty->assign ('RETURN_ACTION', 'KANBA-SAVE');
	} catch (Exception $e) {
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('TYPE', 'ERROR');
	}
	$smarty->display ('ChangeEntityOwnerModal.tpl');
