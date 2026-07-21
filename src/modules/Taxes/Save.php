<?php
	require_once ('include/platzilla/Managers/TaxManager.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb, $current_user;

	try {
		if ((!empty ($_SESSION ['platInstancia'])) || (!is_admin ($current_user))) {
			throw new Exception ('Acceso denegado');
		}

		$conditionGroups = PlatzillaUtils::purify ($_POST, 'conditiongroups');
		$description     = PlatzillaUtils::purify ($_POST, 'description');
		$isDefault       = PlatzillaUtils::purify ($_POST, 'isdefault');
		$percentage      = PlatzillaUtils::purify ($_POST, 'percentage');
		$taxId           = PlatzillaUtils::purify ($_POST, 'record');
		$taxName         = PlatzillaUtils::purify ($_POST, 'taxname');

		$tm = TaxManager::getInstance ($adb);
		if (!empty ($taxId)) {
			$tax = $tm->fetchTax ($taxId);
		} else {
			$tax = Tax::getInstance ();
		}
		if (empty ($tax)) {
			throw new Exception ('El impuesto suministrado no está registrado');
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
					$conditions [] = TaxCondition::getInstance ()
						->setComparator ($conditionGroupCondition ['comparator'])
						->setGroupId ($groupId)
						->setId ($conditionId)
						->setOperator ($conditionGroupCondition ['operator'])
						->setTaxId ($taxId)
						->setValue ($conditionGroupCondition ['value'])
						->setVariableName ($conditionGroupCondition ['variablename'])
						->setVariableType ($conditionGroupCondition ['variabletype']);
					$conditionId++;
				}

				$groups [] = TaxConditionGroup::getInstance ()
					->setConditions ($conditions)
					->setId ($groupId)
					->setOperator ($conditionGroup ['operator'])
					->setTaxId ($taxId);
				$groupId++;
			}
		} else {
			$groups = null;
		}

		$tax->setConditionGroups ($groups)
			->setDefault ($isDefault == 1)
			->setDescription ($description)
			->setId ($taxId)
			->setName ($taxName)
			->setPercentage ($percentage);
		$tm->saveTax ($tax);
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'El impuesto ha sido guardado',
		);
		header ('Location: index.php?module=Taxes&action=ListView&parenttab=Settings');
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
			'data'    => !empty ($tax) ? $tax->serialize () : null,
		);
		$recordUriPart             = !empty ($taxId) ? "&record={$taxId}" : '';
		header ("Location: index.php?module=Taxes&action=EditView{$recordUriPart}&parenttab=Settings");
	}
	exit ();
