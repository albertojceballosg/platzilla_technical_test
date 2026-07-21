<?php
	require_once ('include/platzilla/Managers/PricebookManager.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb, $current_user;

	try {
		if ((!empty ($_SESSION ['platInstancia'])) || (!is_admin ($current_user))) {
			throw new Exception ('Acceso denegado');
		}

		$conditionGroups            = PlatzillaUtils::purify ($_POST, 'conditiongroups');
		$description                = PlatzillaUtils::purify ($_POST, 'description');
		$isDefault                  = PlatzillaUtils::purify ($_POST, 'isdefault');
		$multiplier                 = PlatzillaUtils::purify ($_POST, 'multiplier');
		$pricebookId                = PlatzillaUtils::purify ($_POST, 'record');
		$pricebookName              = PlatzillaUtils::purify ($_POST, 'pricebookname');

		$pbm = PricebookManager::getInstance ($adb);
		if (!empty ($pricebookId)) {
			$pricebook = $pbm->fetchPricebook ($pricebookId);
		} else {
			$pricebook = Pricebook::getInstance ();
		}
		if (empty ($pricebook)) {
			throw new Exception ('La tarifa suministrada no está registrada');
		}

		if (!empty ($conditionGroups)) {
			$groups  = array ();
			$groupId = 1;
			foreach ($conditionGroups as $conditionGroup) {
				$conditionGroupConditions = $conditionGroup ['conditions'];
				if (empty ($conditionGroupConditions)) {
					continue;
				}
				$conditions  = array ();
				$conditionId = 1;
				foreach ($conditionGroupConditions as $conditionGroupCondition) {
					$conditions [] = PricebookCondition::getInstance ()
						->setComparator ($conditionGroupCondition ['comparator'])
						->setGroupId ($groupId)
						->setId ($conditionId)
						->setOperator ($conditionGroupCondition ['operator'])
						->setPricebookId ($pricebookId)
						->setValue ($conditionGroupCondition ['value'])
						->setVariableName ($conditionGroupCondition ['variablename'])
						->setVariableType ($conditionGroupCondition ['variabletype']);
					$conditionId++;
				}

				$groups [] = PricebookConditionGroup::getInstance ()
					->setConditions ($conditions)
					->setId ($groupId)
					->setOperator ($conditionGroup ['operator'])
					->setPricebookId ($pricebookId);
				$groupId++;
			}
		} else {
			$groups = null;
		}

		$pricebook->setConditionGroups ($groups)
			->setDefault ($isDefault == 1)
			->setDescription ($description)
			->setId ($pricebookId)
			->setMultiplier ($multiplier)
			->setName ($pricebookName);
		$pbm->savePricebook ($pricebook);
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'La tarifa ha sido guardada',
		);
		header ('Location: index.php?module=Pricebooks&action=ListView&parenttab=Settings');
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
			'data'    => !empty ($pricebook) ? $pricebook->serialize () : null,
		);
		$recordUriPart             = !empty ($pricebookId) ? "&record={$pricebookId}" : '';
		header ("Location: index.php?module=Pricebooks&action=EditView{$recordUriPart}&parenttab=Settings");
	}
	exit ();
