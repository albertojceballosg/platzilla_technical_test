<?php
	require_once ('include/platzilla/Managers/PlatformBillingPlanManager.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb, $current_user;

	try {
		if ((!empty ($_SESSION ['platInstancia'])) || (!is_admin ($current_user))) {
			throw new Exception ('Acceso denegado');
		}

		$planId = PlatzillaUtils::purify ($_POST, 'record');
		if (empty ($planId)) {
			throw new Exception ('No has suministrado el ID del plan a eliminar');
		}

		$plan = PlatformBillingPlan::getInstance ()
			->setId ($planId);
		PlatformBillingPlanManager::getInstance ($adb)->deletePlan ($plan);
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'El plan ha sido eliminado',
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
	}
	header ('Location: index.php?module=Settings&action=PlatformBillingPlanListView&parenttab=Settings');
