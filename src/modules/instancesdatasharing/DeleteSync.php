<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/instancesdatasharing/lib/DataSharingUtils.class.php');

	global $adb;

	$returnAction   = PlatzillaUtils::purify ($_POST, 'returnaction', 'ListView');
	$returnModule   = PlatzillaUtils::purify ($_POST, 'returnmodule', 'Home');
	$returnRecordId = PlatzillaUtils::purify ($_POST, 'returnid');
	$syncId         = PlatzillaUtils::purify ($_POST, 'record');
	try {
		$sync = DataSharingUtils::fetchSync ($adb, $syncId);
		if (empty ($sync)) {
			throw new Exception ('No has suministrado el registro a eliminar');
		}

		$sourceInstanceCode = $sync->getSourceInstanceCode ();
		$targetInstanceCode = $sync->getTargetInstanceCode ();
		if ($sourceInstanceCode == $_SESSION ['platInstancia']) {
			$sourceAdb = $adb;
			if (empty ($targetInstanceCode)) {
				$targetAdb = AdbManager::getInstance ()->getMasterAdb ();
			} else {
				$targetAdb = AdbManager::getInstance ()->getTargetInstanceAdb ($targetInstanceCode);
			}
		} else {
			if (empty ($sourceInstanceCode)) {
				$sourceAdb = AdbManager::getInstance ()->getMasterAdb ();
			} else {
				$sourceAdb = AdbManager::getInstance ()->getSourceInstanceAdb ($sourceInstanceCode);
			}
			$targetAdb = $adb;
		}

		DataSharingUtils::deleteSync ($sourceAdb, $sourceInstanceCode, $sync->getSourceModuleName (), $sync->getSourceRecordId ());
		DataSharingUtils::deleteSync ($targetAdb, $targetInstanceCode, $sync->getTargetModuleName (), $sync->getTargetRecordId ());

		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'Se dejó de compartir el registro seleccionado',
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
	}
	if (empty ($returnRecordId)) {
		header ("Location: index.php?module={$returnModule}&action={$returnAction}");
	} else {
		header ("Location: index.php?module={$returnModule}&action={$returnAction}&record={$returnRecordId}");
	}
	exit ();
