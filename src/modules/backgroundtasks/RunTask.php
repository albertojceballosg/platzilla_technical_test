<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/backgroundtasks/lib/BackgroundTasksRunner.class.php');
	require_once ('modules/backgroundtasks/lib/BackgroundTasksUtils.class.php');

	global $adb, $site_URL;
	setBugSnag ($site_URL);

	$entityId     = PlatzillaUtils::purify ($_REQUEST, 'record');
	$returnAction = PlatzillaUtils::purify ($_REQUEST, 'return_action');
	$returnModule = PlatzillaUtils::purify ($_REQUEST, 'return_module');
	$returnRecord = PlatzillaUtils::purify ($_REQUEST, 'return_record');
	$taskName     = PlatzillaUtils::purify ($_REQUEST, 'taskname');

	$recordUriPart = !empty ($returnRecord) ? "&record={$returnRecord}" : '';

	try {
		if (empty ($taskName)) {
			throw new Exception ('No has suministrado el nombre de la tarea');
		}

		if (empty ($returnAction)) {
			throw new Exception ('No has suministrado la acción');
		}

		if (empty ($returnModule)) {
			throw new Exception ('No has suministrado el módulo');
		}

		BackgroundTasksRunner::getInstance ($adb, $_SESSION ['plat'])->runManuallyTriggeredTask ($taskName, $entityId);
		header ("Location: index.php?module={$returnModule}&action={$returnAction}{$recordUriPart}");
	} catch (Exception $e) {
		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('LABEL', 'Volver');
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->assign ('URL', "index.php?module={$returnModule}&action={$returnAction}{$recordUriPart}");
		$smarty->display ('Message.tpl');
	}
