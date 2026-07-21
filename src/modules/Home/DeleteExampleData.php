<?php
	require_once ('include/platzilla/Managers/PlatformManager.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/ExampleDataManager.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	$instanceCode = PlatzillaUtils::purify ($_POST, 'code');

	try {
		if (empty ($instanceCode)) {
			throw new Exception ('Imposible borradar datos! no has suministrado el código de la instancia');
		}

		$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
		$targetAdb = AdbManager::getInstance ()->getTargetInstanceAdb ($instanceCode);
		$result    = ExampleDataManager::deleteData ($targetAdb);
		PlatformManager::getInstance ($masterAdb)->updateInstancePattern ($instanceCode, false);
		$_SESSION ['flashmessage'] = array (
			'iserror' => !$result,
			'message' => $result ? 'Se han eliminado todos los datos de prueba' : 'Se ha presentado un error al eliminar los datos de prueba',
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
	}
	header ('Location: index.php?module=Home&action=CustomerView');
	exit ();
