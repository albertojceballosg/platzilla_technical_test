<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/UserInfoUtil.php');

	global $adb, $currentModule;

	$recordIds = PlatzillaUtils::purify ($_POST, 'recordids');

	if (!empty ($recordIds)) {
		foreach ($recordIds as $recordId) {
			$result = $adb->pquery ('SELECT * FROM vtiger_crmentity WHERE deleted=0 AND crmid=? AND setype=?', array ($recordId, $currentModule));
			if ((!$result) || ($adb->num_rows ($result) == 0) || (isPermitted ($currentModule, 'EditView', $recordId) != 'yes')) {
				continue;
			}

			/** @var CRMEntity|object $entity */
			$entity = CRMEntity::getInstance ($currentModule);
			$entity->retrieve_entity_info ($recordId, $currentModule);
			$entity->mode = 'edit';
			$entity->id   = $recordId;

			$fieldNames = array_keys ($entity->column_fields);
			$hasChanges = false;
			foreach ($fieldNames as $fieldName) {
				if ((!isset ($_POST [ $fieldName ])) || ($_POST [ $fieldName ] === '')) {
					continue;
				}

				$fieldValue = PlatzillaUtils::purify ($_POST, $fieldName);
				if ($fieldName == 'assigned_user_id') {
					$assignType = PlatzillaUtils::purify ($_POST, 'assigntype');
					$fieldValue = $assignType == 'T' ? PlatzillaUtils::purify ($_POST, 'assigned_group_id') : $fieldValue;
				} else if (!is_array ($fieldValue)) {
					$fieldValue = trim ($fieldValue);
				}

				$entity->column_fields [ $fieldName ] = $fieldValue;
				$hasChanges                           = true;
			}
			if ($hasChanges) {
				$entity->save ($currentModule);
			}
		}
	}
	header ("Location: index.php?module={$currentModule}&action=index");
	exit ();
