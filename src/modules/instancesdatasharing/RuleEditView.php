<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/SystemVariables.class.php');
	require_once ('include/utils/Translator.class.php');
	require_once ('modules/instancesdatasharing/lib/DataSharingUtils.class.php');

	global $adb, $app_strings, $current_user, $theme;

	$smarty = new vtigerCRM_Smarty ();
	if (!is_admin ($current_user)) {
		$smarty->assign ('APP', $app_strings);
		$smarty->assign ('ICON_URL', vtiger_imageurl ('denied.gif', $theme));
		$smarty->display ('AccessDenied.tpl');
		exit ();
	}

	try {
		$ruleId = PlatzillaUtils::purify ($_GET, 'record');
		if (isset ($_SESSION ['flashmessage']['data'])) {
			$rule = DataSharingRule::getInstance ();
			$rule->unserialize ($_SESSION ['flashmessage']['data']);
			$moduleName = $rule->getModuleName ();
		} else if (!empty ($ruleId)) {
			$rule = DataSharingUtils::fetchRule ($adb, $ruleId);
			$moduleName = $rule->getModuleName ();
		} else {
			$rule = null;
			$moduleName = null;
		}

		$smarty->assign ('AVAILABLE_FIELDS', DataSharingUtils::fetchAvailableFieldsData ($adb, $moduleName));
		$smarty->assign ('AVAILABLE_MODULES', DataSharingUtils::fetchAvailableEntityModules ($adb));
		$smarty->assign ('AVAILABLE_PICKLIST_VALUES', DataSharingUtils::fetchAvailablePicklistValues ($adb, $moduleName));
		$smarty->assign ('AVAILABLE_STATUSES', DataSharingRule::getAvailableStatuses ());
		$smarty->assign ('AVAILABLE_USERS', DataSharingUtils::fetchAvailableUsers ($adb));
		$smarty->assign ('MOD', Translator::getModuleDictionary ('instancesdatasharing'));
		$smarty->assign ('RULE', $rule);
		$smarty->assign ('RECORD', $ruleId);
		$smarty->assign ('SYSTEM_VARIABLES', SystemVariables::getAvailableVariables ());
		$smarty->assign ('SYSTEM_VARIABLE_TYPES', SystemVariables::getAvailableVariableTypes ());
		if (isset ($_SESSION ['flashmessage'])) {
			$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
			$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
			unset ($_SESSION ['flashmessage']);
		}
		$smarty->display ('modules/instancesdatasharing/RuleEditView.tpl');
	} catch (Exception $e) {
		$smarty->assign ('LABEL', 'Volver');
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->assign ('URL', 'index.php?module=instancesdatasharing&action=ListView&parenttab=Settings');
		$smarty->display ('Message.tpl');
	}
