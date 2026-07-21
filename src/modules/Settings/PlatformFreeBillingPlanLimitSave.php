<?php
	require_once ('include/platzilla/Managers/PlatformFreeBillingPlanLimitManager.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb, $current_user;

	try {
		if ((!empty ($_SESSION ['platInstancia'])) || (!is_admin ($current_user))) {
			throw new Exception ('Acceso denegado');
		}

		$maxRecords = PlatzillaUtils::purify ($_POST, 'maxrecords');
		$moduleName = PlatzillaUtils::purify ($_POST, 'modulename');

		$limit = PlatformFreeBillingPlanLimit::getInstance ()
			->setMaxRecords ($maxRecords)
			->setModuleName ($moduleName);
		PlatformFreeBillingPlanLimitManager::getInstance ($adb)->saveLimit ($limit);
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'El límite para el plan gratuito ha sido guardado',
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
	}
	header ('Location: index.php?module=Settings&action=PlatformBillingPlanListView&parenttab=Settings&tab=module-limits');
	exit ();