<?php
	require_once ('include/platzilla/Managers/DataSharingManager.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb;

	try {
		$ruleId = PlatzillaUtils::purify ($_POST, 'record');
		if (empty ($ruleId)) {
			throw new Exception ('No has suministrado el ID de la regla');
		}

		$dsrm = DataSharingManager::getInstance ($adb);
		$rule = $dsrm->fetchRuleById ($ruleId);
		if (empty ($rule)) {
			throw new Exception ('No se encuentra la regla con el ID suministrado');
		}
		$dsrm->deleteRule ($rule);
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'La regla ha sido eliminada',
		);
		header ('Location: index.php?module=instancesdatasharing&action=index&parenttab=Settings');
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
		header ('Location: index.php?module=instancesdatasharing&action=index&parenttab=Settings');
	}
	exit ();
