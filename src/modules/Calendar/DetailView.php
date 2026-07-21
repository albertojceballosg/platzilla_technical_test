<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/EditViewUtils.class.php');
	require_once ('modules/backgroundtasks/lib/BackgroundTasksRunner.class.php');
	require_once ('modules/Calendar/Calendar.php');
	require_once ('modules/Calendar/lib/CalendarHelper.class.php');
	require_once ('modules/PickList/PickListUtils.php');
	require_once ('vtlib/Vtiger/Link.php');

	global $adb, $app_strings, $currentModule, $current_user, $mod_strings, $theme;

	$action       = PlatzillaUtils::purify ($_REQUEST, 'action');
	$activityId   = PlatzillaUtils::purify ($_REQUEST, 'record');
	$activityMode = PlatzillaUtils::purify ($_REQUEST, 'activity_mode');
	$isDuplicate  = PlatzillaUtils::purify ($_REQUEST, 'isDuplicate', false);
	$returnAction = PlatzillaUtils::purify ($_REQUEST, 'return_action');
	$returnId     = PlatzillaUtils::purify ($_REQUEST, 'return_id');
	$returnModule = PlatzillaUtils::purify ($_REQUEST, 'return_module');
	$viewType     = PlatzillaUtils::purify ($_REQUEST, 'viewtype');

	$activity = CalendarHelper::getActivityData ($adb, $activityId);
	if (empty ($activity)) {
		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('LABEL', 'Volver');
		$smarty->assign ('MESSAGE', 'La tarea seleccionada no existe');
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->assign ('URL', "index.php?module={$currentModule}&action=ListView");
		$smarty->display ('Message.tpl');
		exit ();
	}

	if (empty ($activityMode)) {
		$activityMode = CalendarHelper::getActivityMode ($activity);
	}
	$category = getParentTab ();
	$moduleId = getTabid ($currentModule);

	/** @var CRMEntity|stdClass $focus */
	$focus = CRMEntity::getInstance ($currentModule);
	$focus->retrieve_entity_info ($activityId, $currentModule);
	$focus->name                           = $focus->column_fields ['subject'];
	$focus->column_fields ['description']  = html_entity_decode ($focus->column_fields ['description']);
	if (!$isDuplicate) {
		$focus->id = $activityId;
	}

	$oldDieOnError = $adb->dieOnError;
	$adb->setDieOnError (false);
	BackgroundTasksRunner::getInstance ($adb, $_SESSION ['plat'])->runEventTriggeredTasks ('READ', BackgroundTaskInterface::EVENT_INSTANT_BEFORE, $focus);
	$adb->setDieOnError ($oldDieOnError);

	$invitedUsers = CalendarHelper::getInvitedUsers ($adb, $activityId);

	$assignedUser = null;
	$result = $adb->pquery ('SELECT * FROM vtiger_users WHERE id=?', array ($focus->column_fields ['assigned_user_id']));
	if ($adb->num_rows ($result) > 0) {
		$row = $adb->fetchByAssoc ($result, -1, false);
		$assignedUser = trim ("{$row ['first_name']} {$row ['last_name']}");
	}
	if ($result instanceof ADORecordSet) {
		$result->Close ();
		$result = null;
	}

	if (empty ($assignedUser)) {
		$result = $adb->pquery ('SELECT * FROM vtiger_groups WHERE groupid=?', array ($focus->column_fields ['assigned_user_id']));
		if ($adb->num_rows ($result) > 0) {
			$row          = $adb->fetchByAssoc ($result, -1, false);
			$assignedUser = $row ['groupName'];
		}
		if ($result instanceof ADORecordSet) {
			$result->Close ();
			$result = null;
		}
	}
	$focus->column_fields ['assigned_user_id'] = $assignedUser;

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('ACTIVITYDATA', $focus->column_fields);
	$smarty->assign ('ACTIVITY_MODE', $activityMode);
	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('CATEGORY', $category);
	$smarty->assign ('CHECK', Button_Check ($currentModule));
	$smarty->assign ('EDIT_PERMISSION', isPermitted ($currentModule, 'EditView', $activityId));
	$smarty->assign ('ID', $focus->id);
	$smarty->assign ('IMAGE_PATH', "themes/{$theme}/images/");
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('MODULE', $currentModule);
	$smarty->assign ('NAME', $focus->name);
	$smarty->assign ('RELATED', Calendar::getRelatedEntities ($adb, $activityId));
	$smarty->assign ('THEME', $theme);
	if (!empty ($returnModule)) {
		$smarty->assign ('RETURN_MODULE', $returnModule);
	}
	if (!empty ($returnAction)) {
		$smarty->assign ('RETURN_ACTION', $returnAction);
	}
	if (!empty ($returnId)) {
		$smarty->assign ('RETURN_ID', $returnId);
	}
	if (isPermitted ('Calendar', 'EditView', $activityId) == 'yes') {
		$smarty->assign ('EDIT_DUPLICATE', 'permitted');
	}
	if (isPermitted ('Calendar', 'Delete', $activityId) == 'yes') {
		$smarty->assign ('DELETE', 'permitted');
	}
	$smarty->display ('ActivityDetailView.tpl');

	$oldDieOnError = $adb->dieOnError;
	$adb->setDieOnError (false);
	BackgroundTasksRunner::getInstance ($adb, $_SESSION ['plat'])->runEventTriggeredTasks ('READ', BackgroundTaskInterface::EVENT_INSTANT_AFTER, $focus);
	$adb->setDieOnError ($oldDieOnError);
