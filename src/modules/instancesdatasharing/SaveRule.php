<?php
	require_once ('include/platzilla/Managers/DataSharingManager.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb;

	try {
		$moduleName  = PlatzillaUtils::purify ($_POST, 'modulename');
		$ruleDetails = PlatzillaUtils::purify ($_POST, 'details');
		$ruleId      = PlatzillaUtils::purify ($_POST, 'record');
		$ruleName    = PlatzillaUtils::purify ($_POST, 'rulename');
		$ruleStatus  = PlatzillaUtils::purify ($_POST, 'rulestatus');

		if (empty ($ruleName)) {
			throw new Exception ('No has suministrado el nombre');
		} else if (empty ($ruleStatus)) {
			throw new Exception ('No has suministrado el status');
		} else if (empty ($ruleDetails)) {
			throw new Exception ('No has suministrado los detalles');
		}

		$details = array ();
		foreach ($ruleDetails as $ruleDetailId => $ruleDetail) {
			foreach ($ruleDetail ['fields'] as $fieldName => $fieldData) {
				if (empty ($fieldData ['parametertype'])) {
					continue;
				}
				$actionType = !empty ($fieldData ['actiontype']) ? $fieldData ['actiontype'] : DataSharingRuleDetail::ACTION_SEND_ONLY;
				$details [] = DataSharingRuleDetail::getInstance ()
					->setActionType ($actionType)
					->setParameterFormula ($fieldData ['parameterformula'])
					->setParameterType ($fieldData ['parametertype'])
					->setRuleId ($ruleId)
					->setSourceModuleName ($moduleName)
					->setTargetFieldName ($fieldName)
					->setTargetModuleName ($moduleName);
			}
		}
		$rule = DataSharingRule::getInstance ()
			->setDetails ($details)
			->setId ($ruleId)
			->setLocked (!empty ($_SESSION ['platInstancia']))
			->setModuleName ($moduleName)
			->setName ($ruleName)
			->setStatus ($ruleStatus);
		DataSharingManager::getInstance ($adb)->saveRule ($rule);
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'La regla ha sido guardada',
		);
		header ('Location: index.php?module=instancesdatasharing&action=index&parenttab=Settings');
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
			'data'    => isset ($rule) ? $rule->serialize () : null,
		);
		$recordUriPart             = !empty ($ruleId) ? "&record={$ruleId}" : '';
		header ("Location: index.php?module=instancesdatasharing&action=RuleEditView{$recordUriPart}&parenttab=Settings");
	}
	exit ();
