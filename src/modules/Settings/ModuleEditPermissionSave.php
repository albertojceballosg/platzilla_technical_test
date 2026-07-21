<?php
	require_once ('include/platzilla/Managers/ModuleEditPermissionManager.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb, $current_user;

	try {
		if (!is_admin ($current_user)) {
			throw new Exception ('Acceso denegado');
		}

		$moduleName       = PlatzillaUtils::purify ($_POST, 'formodule');
		$filterGroupsData = PlatzillaUtils::purify ($_POST, 'filtergroups');
		if (empty ($moduleName)) {
			throw new Exception ('No has suministrado el nombre del módulo');
		}

		$groupId = 1;
		$groups  = array ();
		foreach ($filterGroupsData as $filterGroupData) {
			if (empty ($filterGroupData ['filters'])) {
				continue;
			}

			$filterId = 1;
			$filters  = array ();
			foreach ($filterGroupData ['filters'] as $filterData) {
				$filters [] = ModuleEditPermissionCondition::getInstance ()
					->setComparator ($filterData ['comparator'])
					->setFieldName ($filterData ['fieldname'])
					->setGroupId ($groupId)
					->setLabel ($filterData ['fieldname'])
					->setModuleName ($moduleName)
					->setOperator (!empty ($filterData ['operator']) ? $filterData ['operator'] : null)
					->setSequence ($filterId)
					->setValue ($filterData ['value']);
				$filterId++;
			}

			$groups [] = ModuleEditPermissionConditionGroup::getInstance ()
				->setId ($groupId)
				->setFilters ($filters)
				->setModuleName ($moduleName)
				->setOperator (!empty ($filterGroupData ['operator']) ? $filterGroupData ['operator'] : null);
			$groupId++;
		}

		ModuleEditPermissionManager::getInstance ($adb)->saveFilterGroups ($moduleName, $groups);
		header ("Location: index.php?module=Settings&action=LayoutBlockList&parenttab=Settings&formodule={$moduleName}");
	} catch (Exception $e) {
		header ('HTTP/1.1 400 Bad request');
		header ('Content-Type: application/json');
		echo json_encode ($e->getMessage ());
	}
	exit ();
