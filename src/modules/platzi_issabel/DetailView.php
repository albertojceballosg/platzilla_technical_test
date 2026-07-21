<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Managers/PlatformSubscriptionManager.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/platzi_issabel/lib/PlatziIssabel.class.php');
	require_once ('modules/platzi_issabel/platzi_issabel.php');
	
	global $adb, $app_strings, $mod_strings, $currentModule;
	
	$isInstance = !empty ($_SESSION ['platInstancia']);
	$uniqueId   = PlatzillaUtils::purify ($_REQUEST, 'uniqueid');
	try {
		if ($isInstance) {
			$platziIssabel = new platzi_issabel();
			$platziIssabel->checkModuleSubscription ($currentModule);
		}
		if (empty ($uniqueId)) {
			throw new Exception ('Grabación no identificada!');
		}
		$objectIssabel = PlatziIssabel::getInstance ($_SESSION ['plat']);
		$recording     = $objectIssabel->getMonitorByUniqueId ($uniqueId);
		$filebyUid     = $objectIssabel->getAudioByUniqueId ($uniqueId);
		$smarty = new vtigerCRM_Smarty ();
		if (isset ($_SESSION ['flashmessage'])) {
			$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
			$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
			unset ($_SESSION ['flashmessage']);
		}
		
		$smarty->assign ('RECORDING', $recording);
		$smarty->assign ('RECORDING_AUDIO', $filebyUid);
		$smarty->assign ('MOD',$mod_strings);
		$smarty->display ('modules/platzi_issabel/DetailView.tpl');
	} catch (Exception $e) {
		$code   = $e->getCode ();
		$issabelMonitoring = $objectIssabel->fetchIssabelMonitoring (null);
		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('ISSABEL_MONITORING', $issabelMonitoring);
		$smarty->assign ('MOD',$mod_strings);
		if ($code === 400) {
			$smarty->assign('IS_ERROR', true);
			$smarty->assign ('MENSAJE', 'Debes verificar tu cuenta');
			$smarty->display ('instanciaUnverified.tpl');
		} else if ($code === 403) {
			$smarty->assign('IS_ERROR', true);
			$smarty->assign ('LABEL', 'Tu suscripción');
			$smarty->assign ('MESSAGE', $e->getMessage ());
			$smarty->assign ('TYPE', 'ERROR');
		} else {
			$smarty->assign('IS_ERROR', true);
			$smarty->assign ('LABEL', 'Se ha presentado un error fatal');
			$smarty->assign ('MESSAGE', $e->getMessage ());
			$smarty->assign ('TYPE', 'ERROR');
		}
		$smarty->display ('modules/platzi_issabel/ListView.tpl');
	}

