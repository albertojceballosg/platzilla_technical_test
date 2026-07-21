<?php
	require_once ('include/platzilla/Managers/ViewManager.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb;

	$cvid      = vtlib_purify ($_REQUEST ['record']);
	$module    = vtlib_purify ($_REQUEST ['dmodule']);
	$smodule   = vtlib_purify ($_REQUEST ['smodule']);
	$parenttab = getParentTab ();

	try {
		if (empty ($cvid)) {
			throw new Exception ('No has suministrado el ID de la vista a eliminar');
		}

		$vm   = ViewManager::getInstance ($adb);
		$view = $vm->fetchViewById ($module, $cvid);
		if ($view->getDefault () == View::DEFAULT_YES) {
			throw new Exception ('No está permitido eliminar la vista por defecto');
		}

		$vm->deleteView ($view);

		$adb->pquery ('DELETE FROM vtiger_user_module_preferences WHERE default_cvid=?', array ($cvid));
		$_SESSION ['lvs'][ $module ]['viewname'] = '';
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
	}

	if (!empty ($smodule)) {
		$urlPart = "&smodule={$smodule}";
	} else {
		$urlPart = '';
	}
	header ("Location: index.php?module={$module}&action=ListView&parenttab={$parenttab}{$urlPart}");
	exit ();
