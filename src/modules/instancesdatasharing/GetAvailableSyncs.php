<?php
	require_once ('include/platzilla/Managers/ModuleManager.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/instancesdatasharing/lib/DataSharingUtils.class.php');

	global $adb;

	try {
		$instanceCode = $_SESSION ['platInstancia'];
		$moduleName   = PlatzillaUtils::purify ($_GET, 'modulename');
		$recordId     = PlatzillaUtils::purify ($_GET, 'record');

		$receivedSyncs = null;
		$sentSyncs     = null;
		$syncs         = DataSharingUtils::fetchSyncs ($adb, $instanceCode, $moduleName, $recordId);
		$mm            = ModuleManager::getInstance ($adb);
		$syncModule    = $mm->fetchModule ($moduleName, true);
		if (!empty ($syncs)) {
			foreach ($syncs as $sync) {
				if ($sync->getSourceInstanceCode () == $instanceCode) {
					$recordId        = $sync->getSourceRecordId ();
					$identifierLabel = FieldManager::getInstance ($adb)->fetchFieldByName ($moduleName, $syncModule->getEntityIdentifier (), true)->getLabel ();
					$identifierValue = PlatformUtils::getCrmEntity ($adb, $moduleName, $recordId)->column_fields [ $syncModule->getEntityIdentifier () ];
					$ruleId          = $sync->getRuleId ();
					if ($ruleId == DataSharingRequest::RULE_FULL) {
						$ruleName = 'Los registros seleccionados y sus registros relacionados';
					} else if ($ruleId == DataSharingRequest::RULE_MINIMAL) {
						$ruleName = 'Sólo los registros seleccionados, sin los registros relacionados';
					} else {
						$rule     = DataSharingUtils::fetchRule ($adb, $ruleId);
						$ruleName = $rule->getName ();
					}
					$sentSyncs [] = array (
						'syncid'             => intval ($sync->getId ()),
						'identifierlabel'    => $identifierLabel,
						'identifiervalue'    => $identifierValue,
						'rulename'           => $ruleName,
						'sourceemailaddress' => $sync->getSourceEmailAddress (),
						'targetemailaddress' => $sync->getTargetEmailAddress (),
					);
				} else {
					$recordId         = $sync->getTargetRecordId ();
					$identifierLabel  = FieldManager::getInstance ($adb)->fetchFieldByName ($moduleName, $syncModule->getEntityIdentifier (), true)->getLabel ();
					$identifierValue  = PlatformUtils::getCrmEntity ($adb, $moduleName, $recordId)->column_fields [ $syncModule->getEntityIdentifier () ];
					$receivedSyncs [] = array (
						'syncid'             => intval ($sync->getId ()),
						'identifierlabel'    => $identifierLabel,
						'identifiervalue'    => $identifierValue,
						'rulename'           => '',
						'sourceemailaddress' => $sync->getSourceEmailAddress (),
						'targetemailaddress' => $sync->getTargetEmailAddress (),
					);
				}
			}
		}

		$data = array ('received' => $receivedSyncs, 'sent' => $sentSyncs);

		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json');
		echo json_encode ($data);
	} catch (Exception $e) {
		header ('HTTP/1.1 400 Bad request');
		header ('Content-Type: application/json');
		echo json_encode ($e->getMessage ());
	}
	exit ();
