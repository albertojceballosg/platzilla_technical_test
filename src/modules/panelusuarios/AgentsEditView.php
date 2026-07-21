<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Managers/PlatformManager.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/DataViewUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/panelusuarios/lib/UsersHelper.class.php');

	global $adb, $currentModule, $current_user;

	$smarty = new vtigerCRM_Smarty ();
	if (!is_admin ($current_user)) {
		$smarty->assign ('IS_ADMIN', false);
		$smarty->display ('AccessDenied.tpl');
		exit ();
	}
	if (isset ($_SESSION ['flashmessage']['data'])) {
		$smarty->assign ('USER', $_SESSION ['flashmessage']['data']);
		unset ($_SESSION ['flashmessage']['data']);
	}
	try {
		$userId   = PlatzillaUtils::purify ($_REQUEST, 'record', null);
		$intances = PlatformManager::getInstance ($adb)->fetchInstances (null, null, 1, 1000, true, true);
		$availableInstaces = array ();
		foreach ($intances['records'] as $instance) {
			if ($instance->getCode() == 'appdemo') {
				continue;
			}
			$instance->setApplications (null);
			$instance->setBillingPlan (null);
			$instance->setUsers (null);
			$availableInstaces[] = $instance;
		}
		
		$smarty->assign ('AGENT', UsersHelper::getAgent ($adb, $userId));
		$smarty->assign ('AVAILABLE_USERS', DataViewUtils::getAvailableUserAndAvatar ($adb, $current_user));
		$smarty->assign ('INSTANCES', $availableInstaces);
		$smarty->assign ('MODULE_NAME', $currentModule);
		$smarty->assign ('RECORD', $userId);
		$smarty->assign ('STATUS', AgentsInterface::AGENT_STATUS);
		
		if (isset ($_SESSION ['flashmessage'])) {
			$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
			$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
			unset ($_SESSION ['flashmessage']);
		}
		$smarty->display ('modules/panelusuarios/AgentEditView.tpl');
	} catch (Exception $e) {
		var_dump ($e);
		$smarty->assign ('LABEL', 'Volver');
		$smarty->assign ('MESSAGE', 'El usuario Agente no se encuentra registrado');
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->assign ('URL', 'index.php?module=panelusuarios&action=AgentListView&parenttab=Settings');
		$smarty->display ('Message.tpl');
	}
