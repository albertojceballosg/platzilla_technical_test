<?php
	require_once ('include/platzilla/Managers/PlatformManager.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/backgroundtasks/lib/BackgroundTasksRunner.class.php');

	global $current_user, $platPrincipal;

	$applicationCodes = PlatzillaUtils::purify ($_POST, 'applicationcodes');

	try {
		if ((empty ($_SESSION ['platInstancia'])) || (!is_admin ($current_user))) {
			throw new Exception ('Acceso denegado', 401);
		} else if (empty ($applicationCodes)) {
			throw new Exception ('No has suministrado los códigos de las aplicaciones', 400);
		}

		$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
		$instance  = PlatformManager::getInstance ($masterAdb)->fetchInstanceAsCrmEntity ($_SESSION ['platInstancia']);
		BackgroundTasksRunner::getInstance ($masterAdb, $platPrincipal)->runEventTriggeredTasks (
			'STORE OPERATION',
			BackgroundTaskInterface::EVENT_INSTANT_BEFORE,
			$instance
		);

		foreach ($applicationCodes as $applicationCode) {
			PlatformManager::getInstance ($masterAdb)->uninstallInstanceApplication ($_SESSION ['platInstancia'], $applicationCode);
		}
		create_tab_data_file ();
		create_parenttab_data_file ();
		createUserPrivilegesfile ($current_user->id);
		createUserSharingPrivilegesfile ($current_user->id);

		BackgroundTasksRunner::getInstance ($masterAdb, $platPrincipal)->runEventTriggeredTasks (
			'STORE OPERATION',
			BackgroundTaskInterface::EVENT_INSTANT_AFTER,
			$instance
		);

		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'Las aplicaciones han sido desinstaladas',
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
	}
	header ('Location: index.php');
	exit ();
