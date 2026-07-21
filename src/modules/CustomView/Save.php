<?php
	require_once ('include/platzilla/Managers/FieldManager.php');
	require_once ('include/platzilla/Managers/ViewManager.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Users/CreateUserPrivilegeFile.php');

	global $adb, $current_user, $profileCustomViewsPermission;

	$advancedFilterGroupsData = PlatzillaUtils::purify ($_POST, 'advancedfiltergroups');
	$colorFilterGroupsData    = PlatzillaUtils::purify ($_POST, 'colorfiltergroups');
	$columnsData              = PlatzillaUtils::purify ($_POST, 'columns');
	$groupId                  = PlatzillaUtils::purify ($_POST,'groupid');
	$groupName                = PlatzillaUtils::purify ($_POST,'groupname');
	$isDefault                = PlatzillaUtils::purify ($_POST, 'isdefault');
	$isSearchView             = PlatzillaUtils::purify ($_POST,'isSearchView');
	$isDeskView               = PlatzillaUtils::purify ($_POST,'isDeskView');
	$isLocked                 = intval (PlatzillaUtils::purify ($_POST,'locked', 0));
	$moduleName               = PlatzillaUtils::purify ($_POST, 'modulename');
	$standardFilterData       = PlatzillaUtils::purify ($_POST, 'standardfilter');
	$status                   = PlatzillaUtils::purify ($_POST, 'status');
	$viewId                   = PlatzillaUtils::purify ($_POST, 'record');
	$viewName                 = PlatzillaUtils::purify ($_POST, 'name');
	$parentTab                = PlatzillaUtils::purify ($_POST, 'parenttab');
	$isInstance               = !empty ($_SESSION ['platInstancia']);
	
	$record = $viewId;
	if ($isInstance && !empty ($viewId)) {
		$viewId = ($isLocked === 1) ? $viewId : null;
	}

	$isAdmin = ($current_user->is_admin == 'on');
	try {
		$fm = FieldManager::getInstance ($adb);

		// Crear las columnas
		if (!empty ($columnsData)) {
			$columns        = array ();
			$columnSequence = 0;
			foreach ($columnsData as $columnData) {
				if (empty ($columnData)) {
					continue;
				}

				$dummy     = explode (':', $columnData);
				$fieldName = $dummy [2];
				$moduleAndField = explode('@', $dummy [3], 2);
				$field     = $fm->fetchFieldByName ($moduleName, $fieldName);
				if (empty ($field)) {
					continue;
				}
				if ($dummy [0] == 'vtiger_subfields_values') {
					$field->setColumnName ($dummy [1]);
					$field->setLabel ($moduleAndField [1]);
					$field->setTableName ($dummy [0]);
					$field->setUiType(Field::UI_TYPE_NUMBER);
				}

				$columns [] = ViewColumn::getInstance ($field)
					->setSequence ($columnSequence)
					->setViewId ($viewId);
				$columnSequence++;
			}
		} else {
			$columns = null;
		}

		// Crear el filtro estándar
		if ((!empty ($standardFilterData ['column'])) || (!empty ($standardFilterData ['period']))) {
			$dummy     = explode (':', $standardFilterData ['column']);
			$fieldName = $dummy [2];
			$field     = $fm->fetchFieldByName ($moduleName, $fieldName);
			if (!empty ($field)) {
				$standardFilter = ViewStandardFilter::getInstance ($field)
					->setEndDate (!empty ($standardFilterData ['enddate']) ? DateTime::createFromFormat ('Y/m/d', $standardFilterData ['enddate']) : null)
					->setPeriod ($standardFilterData ['period'])
					->setStartDate (!empty ($standardFilterData ['startdate']) ? DateTime::createFromFormat ('Y/m/d', $standardFilterData ['startdate']) : null)
					->setViewId ($viewId);
			} else {
				$standardFilter = null;
			}
		} else {
			$standardFilter = null;
		}

		// Crear los filtros avanzados
		if (!empty ($advancedFilterGroupsData)) {
			$advancedFilterGroupSequence = 0;
			$advancedFilterSequence      = 0;
			$advancedFilterGroups        = array ();
			foreach ($advancedFilterGroupsData as $advancedFilterGroupData) {
				if (empty ($advancedFilterGroupData)) {
					continue;
				}
				$advancedFilters = array ();
				foreach ($advancedFilterGroupData ['filters'] as $index => $advancedFilterData) {
					if (empty ($advancedFilterData)) {
						continue;
					}
					$columnName = $advancedFilterData ['column'];
					$dummy      = explode (':', $columnName);
					$fieldName  = $dummy [2];
					$field      = $fm->fetchFieldByName ($moduleName, $fieldName);
					if (empty ($field) && $dummy [0] != 'vtiger_subfields_values') {
						continue;
					} else if ($dummy [0] != 'vtiger_subfields_values') {
						unset($dummy);
						$dummy = null;
					} else {
						$field = null;
					}
					$comparator         = $advancedFilterData ['comparator'];
					$value              = $advancedFilterData ['value'];
					$operator           = isset ($advancedFilterData ['operator']) ? $advancedFilterData ['operator'] : null;
					$advancedFilters [] = ViewAdvancedFilter::getInstance ($field, $dummy)
						->setComparator ($comparator)
						->setGroupId ($advancedFilterGroupSequence)
						->setOperator ($operator)
						->setSequence ($advancedFilterSequence)
						->setValue ($value)
						->setViewId ($viewId);
					$advancedFilterSequence++;
				}

				if (!empty ($advancedFilters)) {
					$advancedFilterGroupOperator = $advancedFilterGroupData ['operator'];
					$advancedFilterGroups []     = ViewAdvancedFilterGroup::getInstance ()
						->setFilters ($advancedFilters)
						->setOperator ($advancedFilterGroupOperator)
						->setSequence ($advancedFilterGroupSequence)
						->setViewId ($viewId);
					$advancedFilterGroupSequence++;
				}
			}
		} else {
			$advancedFilterGroups = null;
		}

		// Crear los filtros de color
		if (!empty ($colorFilterGroupsData)) {
			$colorFilterGroupSequence = 0;
			$colorFilterSequence      = 0;
			$colorFilterGroups        = array ();
			foreach ($colorFilterGroupsData as $colorFilterGroupData) {
				if (empty ($colorFilterGroupData)) {
					continue;
				}
				$colorFilters = array ();
				foreach ($colorFilterGroupData ['filters'] as $index => $colorFilterData) {
					if (empty ($colorFilterData)) {
						continue;
					}

					$columnName = $colorFilterData ['column'];
					$dummy      = explode (':', $columnName);
					$fieldName  = $dummy [2];
					$field      = $fm->fetchFieldByName ($moduleName, $fieldName);
					if (empty ($field) && $dummy [0] != 'vtiger_subfields_values') {
						continue;
					} else if ($dummy [0] != 'vtiger_subfields_values') {
						unset($dummy);
						$dummy = null;
					} else {
						$field = null;
					}
					$comparator         = $colorFilterData ['comparator'];
					$value              = $colorFilterData ['value'];
					$operator           = isset ($colorFilterData ['operator']) ? $colorFilterData ['operator'] : null;
					$colorFilters [] = ViewColorFilter::getInstance ($field, $dummy)
						->setComparator ($comparator)
						->setEndDate (!empty ($colorFilterData ['enddate']) ? DateTime::createFromFormat ('Y/m/d', $colorFilterData ['enddate']) : null)
						->setGroupId ($colorFilterGroupSequence)
						->setOperator ($operator)
						->setSequence ($colorFilterSequence)
						->setStartDate(!empty ($colorFilterData ['startdate']) ? DateTime::createFromFormat ('Y/m/d', $colorFilterData ['startdate']) : null)
						->setValue ($value)
						->setViewId ($viewId);
					$colorFilterSequence++;
				}

				if (!empty ($colorFilters)) {
					$colorFilterGroupColor = $colorFilterGroupData ['color'];
					$colorFilterGroups []     = ViewColorFilterGroup::getInstance ()
						->setColor ($colorFilterGroupColor)
						->setFilters ($colorFilters)
						->setSequence ($colorFilterGroupSequence)
						->setViewId ($viewId);
					$colorFilterGroupSequence++;
				}
			}
		} else {
			$colorFilterGroups = null;
		}

		if (empty ($_SESSION ['platInstancia']) && ($isSearchView == 1)) {
			$adb->pquery ('UPDATE `vtiger_customview` SET `searchview`= 0 WHERE `searchview`=? AND `entitytype`=?', array (1, $moduleName));
			$viewSearch = View::SEARCH_YES;
		} else {
			$viewSearch = View::SEARCH_NO;
		}
		
		if (empty ($_SESSION ['platInstancia']) && ($isDeskView == 1)) {
			$adb->pquery ('UPDATE `vtiger_customview` SET `deskview`= 0 WHERE `deskview`=? AND `entitytype`=?', array (1, $moduleName));
			$viewDesk = View::SHOW_ON_DESK_YES;
		} else {
			$viewDesk = View::SHOW_ON_DESK_NO;
		}

		// Crear la vista
		if ($isAdmin) {
			$viewIsDefault = $isDefault == 1 ? View::DEFAULT_YES : View::DEFAULT_NO;
			$viewStatus = View::STATUS_PUBLIC;
		} else {
			$viewIsDefault = View::DEFAULT_NO;
			$viewStatus = (!empty ($status)) || ($isDefault == 1) ? View::STATUS_APPROVED : View::STATUS_PRIVATE;
		}
		
		if (empty ($groupId)) {
			$groupView = ViewManager::getInstance ($adb)->fetchViewGroupByName ($moduleName, $groupName);
		}
		// Create the Group
		if (!empty ($groupName) && empty ($groupView)) {
			$groupView = ViewGroup::getInstance()
				->setId($groupId)
				->setLocked($isInstance)
				->setName($groupName)
				->setModuleName($moduleName);
		}

		// create the object view
		$view = View::getInstance ()
			->setAdvancedFilterGroups ($advancedFilterGroups)
			->setColorFilterGroups ($colorFilterGroups)
			->setColumns ($columns)
			->setDefault ($viewIsDefault)
			->setDeskView ($viewDesk)
			->setId ($viewId)
			->setLocked ($isInstance)
			->setModuleName ($moduleName)
			->setName ($viewName)
			->setOwner ($current_user->id)
			->setSearchView ($viewSearch)
			->setStandardFilter ($standardFilter)
			->setStatus ($viewStatus)
			->setViewGroup(isset ($groupView) ? $groupView : null);

		ViewManager::getInstance ($adb)->saveView ($view);
		$viewId = $view->getId ();

		// Actualizar preferencias de usuario
		if ((!$isAdmin) && ($isDefault == 1)) {
			$result = $adb->pquery ('SELECT * FROM vtiger_user_module_preferences WHERE userid=? AND tabid IN (SELECT tabid FROM vtiger_tab WHERE name=?)', array ($current_user->id, $moduleName));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				$adb->pquery ('INSERT INTO vtiger_user_module_preferences (userid, tabid, default_cvid) SELECT ?, tabid, ? FROM vtiger_tab WHERE name=?', array ($current_user->id, $viewId, $moduleName));
			} else {
				$adb->pquery ('UPDATE vtiger_user_module_preferences SET default_cvid=? WHERE userid=? AND tabid IN (SELECT tabid FROM vtiger_tab WHERE name=?)', array ($viewId, $current_user->id, $moduleName));
			}
		} else {
			$adb->pquery ('DELETE FROM vtiger_user_module_preferences WHERE userid=? AND tabid IN (SELECT tabid FROM vtiger_tab WHERE name=?)', array ($current_user->id, $moduleName));
		}

		createUserPrivilegesfile ($current_user->id);
		createUserSharingPrivilegesfile ($current_user->id);
		$profileCustomViewsPermission [ $cvid ] = 0;
		if ($parentTab == 'Home') {
			header ('Location: index.php?module=Home&action=index&tab=ACTIVITY');
		} else {
			header ("Location: index.php?module={$moduleName}&action=ListView");
		}
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
			'data'    => isset ($view) ? serialize ($view) : null,
		);
		$urlPart = !empty ($record) ? "&record={$record}" : '';
		header ("Location: index.php?module={$moduleName}&action=CustomView{$urlPart}");
	}
	exit ();
