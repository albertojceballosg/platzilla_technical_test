<?php
	require_once ('include/platzilla/Managers/ViewProfileManager.php');
	require_once ('include/platzilla/Objects/View.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');

	class ViewManager {
		/** @var ViewManager[]|null */
		private static $INSTANCES = null;

		/** @var PearDatabase */
		private $adb;

		/** @var string[] */
		private $createdCvGroup;

		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
			$this->createdCvGroup = array ();
		}

		/**
		 * @param View $view
		 * @param string $moduleTableName
		 *
		 * @throws Exception
		 */
		private function createView ($view, $moduleTableName) {
			$viewId    = $this->adb->getUniqueID ('vtiger_customview');
			$cvGroupId = (!empty($view->getViewGroup())) ? $view->getViewGroup()->getId() : null;
			try {
				$this->adb->startTransaction ();
				$this->adb->pquery (
					'INSERT INTO vtiger_customview (cvid, viewname, setdefault, setmetrics, entitytype, status, userid, locked, searchview, deskview, cvgroupid) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
					array ($viewId, $view->getName (), $view->getDefault (), $view->getShowCountInMenu (), $view->getModuleName (), $view->getStatus (), $view->getOwner (), $view->isLocked (), $view->getSearchView (), $view->getDeskView (),$cvGroupId)
				);
				$view->setId ($viewId);
				$this->createDefaultViewProfiles ($view);
				$this->saveColumns ($view, $moduleTableName);
				$this->saveStandardFilter ($view, $moduleTableName);
				$this->saveAdvancedFilterGroups ($view, $moduleTableName);
				$this->saveColorFilterGroups ($view, $moduleTableName);
				$this->saveViewGroup ($view);
				$this->adb->completeTransaction ();
			} catch (Exception $e) {
				$this->deleteView ($view);
				throw $e;
			}
		}

		/**
		 * @param View $view
		 */
		private function createDefaultViewProfiles ($view) {
			$viewName = $view->getName ();
			if (empty ($viewName)) {
				return;
			}

			ViewProfileManager::getInstance ($this->adb)->createDefaultProfiles ($view->getModuleName (), $view->getName ());
		}

		/**
		 * @param View $view
		 */
		private function deleteViewProfiles ($view) {
			$viewName = $view->getName ();
			if (empty ($viewName)) {
				return;
			}

			ViewProfileManager::getInstance ($this->adb)->deleteProfiles ($view->getModuleName (), $view->getName ());
		}

		/**
		 * @param $moduleName
		 */
		private function deleteViewGroup ($moduleName) {
			if (empty($moduleName)) {
				return;
			}
			$this->adb->pquery(
				'DELETE FROM vtiger_customview_master_group WHERE entitytype=? AND locked=?',
				array($moduleName, 0)
			);
		}

		/**
		 * @param string $moduleName
		 * @param integer $viewId
		 *
		 * @return null|ViewAdvancedFilterGroup[]
		 */
		private function fetchAdvancedFilterGroupsByViewId ($moduleName, $viewId) {
			$result = $this->adb->pquery ('SELECT * FROM vtiger_cvadvfilter_grouping WHERE cvid=? ORDER BY groupid', array ($viewId));
			if ($this->adb->num_rows ($result) > 0) {
				$groups = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$sequence  = intval ($row ['groupid']);
					$groups [] = ViewAdvancedFilterGroup::getInstance ()
						->setFilters ($this->fetchAdvancedFiltersByGroupId ($moduleName, $viewId, $sequence))
						->setSequence ($sequence)
						->setOperator ($row ['group_condition'])
						->setViewId ($viewId);
				}
			} else {
				$groups = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $groups;
		}

		/**
		 * @param string $moduleName
		 * @param integer $viewId
		 * @param integer $groupId
		 *
		 * @return null|ViewAdvancedFilter[]
		 */
		private function fetchAdvancedFiltersByGroupId ($moduleName, $viewId, $groupId) {
			$result = $this->adb->pquery ('SELECT * FROM vtiger_cvadvfilter WHERE cvid=? AND groupid=? ORDER BY columnindex', array ($viewId, $groupId));
			if ($this->adb->num_rows ($result) > 0) {
				$filters = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$dummy      = $this->parseVtigerColumnName ($moduleName, $row ['columnname']);
					$filters [] = ViewAdvancedFilter::getInstance ()
						->setColumnName ($dummy ['columnname'])
						->setComparator ($row ['comparator'])
						->setDataType ($dummy ['datatype'])
						->setFieldName ($dummy ['fieldname'])
						->setGroupId ($groupId)
						->setLabel ($dummy ['fieldlabel'])
						->setModuleName ($dummy ['modulename'])
						->setOperator ($row ['column_condition'])
						->setSequence (intval ($row ['columnindex']))
						->setTableName ($dummy ['tablename'])
						->setValue ($row ['value'])
						->setViewId ($viewId);
				}
			} else {
				$filters = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $filters;
		}

		/**
		 * @param string $moduleName
		 * @param integer $viewId
		 *
		 * @return null|ViewColorFilterGroup[]
		 */
		private function fetchColorFilterGroupsByViewId ($moduleName, $viewId) {
			$result = $this->adb->pquery ('SELECT * FROM vtiger_cvadvcolor_grouping WHERE cvid=? ORDER BY groupid', array ($viewId));
			if ($this->adb->num_rows ($result) > 0) {
				$groups = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$sequence  = intval ($row ['groupid']);
					$groups [] = ViewColorFilterGroup::getInstance ()
						->setFilters ($this->fetchColorFiltersByGroupId ($moduleName, $viewId, $sequence))
						->setColor ($row ['group_color'])
						->setSequence ($sequence)
						->setViewId ($viewId);
				}
			} else {
				$groups = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $groups;
		}

		/**
		 * @param string $moduleName
		 * @param integer $viewId
		 * @param integer $groupId
		 *
		 * @return null|ViewColorFilter[]
		 */
		private function fetchColorFiltersByGroupId ($moduleName, $viewId, $groupId) {
			$result = $this->adb->pquery ('SELECT * FROM vtiger_cvadvcolor WHERE cvid=? AND groupid=? ORDER BY columnindex', array ($viewId, $groupId));
			if ($this->adb->num_rows ($result) > 0) {
				$filters = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$dummy      = $this->parseVtigerColumnName ($moduleName, $row ['columnname']);
					$filters [] = ViewColorFilter::getInstance ()
						->setColumnName ($dummy ['columnname'])
						->setComparator ($row ['comparator'])
						->setEndDate ($row ['enddate'])
						->setDataType ($dummy ['datatype'])
						->setFieldName ($dummy ['fieldname'])
						->setGroupId ($groupId)
						->setLabel ($dummy ['fieldlabel'])
						->setModuleName ($dummy ['modulename'])
						->setOperator ($row ['column_condition'])
						->setSequence (intval ($row ['columnindex']))
						->setStartDate ($row ['startdate'])
						->setTableName ($dummy ['tablename'])
						->setValue ($row ['value'])
						->setViewId ($viewId);
				}
			} else {
				$filters = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $filters;
		}

		/**
		 * @param string $moduleName
		 * @param integer $viewId
		 *
		 * @return null|ViewColumn[]
		 */
		public function fetchColumnsByViewId ($moduleName, $viewId) {
			$result = $this->adb->pquery ('SELECT * FROM vtiger_cvcolumnlist WHERE cvid=? ORDER BY columnindex', array ($viewId));
			if ($this->adb->num_rows ($result) > 0) {
				$columns = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$dummy      = $this->parseVtigerColumnName ($moduleName, $row ['columnname']);
					$columns [] = ViewColumn::getInstance ()
						->setColumnName ($dummy ['columnname'])
						->setDataType ($dummy ['datatype'])
						->setFieldId ($dummy ['fieldid'])
						->setFieldName ($dummy ['fieldname'])
						->setLabel ($dummy ['fieldlabel'])
						->setModuleName ($dummy ['modulename'])
						->setSequence (intval ($row ['columnindex']))
						->setTableName ($dummy ['tablename'])
						->setViewId ($viewId);
				}
			} else {
				$columns = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $columns;
		}

		/**
		 * @param string $moduleName
		 *
		 * @return View[]
		 */
		private function fetchDeletedViews ($moduleName) {
			if (empty ($moduleName)) {
				return array ();
			}

			$views  = array ();
			$result = $this->adb->pquery ('SELECT * FROM vtiger_deletedelements WHERE elementtype=? AND modulename=?', array ('view', $moduleName));
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					/** @var View $view */
					$view = unserialize ($row ['serializedobject']);
					$view->setDeleted (true);
					$views [] = $view;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $views;
		}

		/**
		 * @param string $moduleName
		 * @param integer $viewId
		 *
		 * @return null|ViewStandardFilter
		 */
		private function fetchStandardFilterByViewId ($moduleName, $viewId) {
			$result = $this->adb->pquery ('SELECT * FROM vtiger_cvstdfilter WHERE cvid=?', array ($viewId));
			if ($this->adb->num_rows ($result) > 0) {
				$row    = $this->adb->fetchByAssoc ($result, -1, false);
				$dummy  = $this->parseVtigerColumnName ($moduleName, $row ['columnname']);
				$filter = ViewStandardFilter::getInstance ()
					->setColumnName ($dummy ['columnname'])
					->setEndDate ($row ['enddate'])
					->setFieldName ($dummy ['fieldname'])
					->setLabel ($dummy ['fieldlabel'])
					->setModuleName ($dummy ['modulename'])
					->setPeriod ($row ['stdfilter'])
					->setStartDate ($row ['startdate'])
					->setTableName ($dummy ['tablename'])
					->setViewId ($viewId);
			} else {
				$filter = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $filter;
		}

		/**
		 * @param string $moduleName
		 * @param integer $cvGroupId
		 *
		 * @return null|ViewGroup
		 * @throws Exception
		 */
		private function fetchViewGroup ($moduleName, $cvGroupId) {
			if (empty($cvGroupId) || empty($moduleName)) {
				return null;
			}
			$result = $this->adb->pquery ('SELECT * FROM vtiger_customview_master_group WHERE entitytype=? AND cvgroupid=?', array ($moduleName, $cvGroupId));
			if ($this->adb->num_rows ($result) > 0) {
				$row       = $this->adb->fetchByAssoc ($result, -1, false);
				$viewGroup = ViewGroup::getInstance ()
					->setId ($row ['cvgroupid'])
					->setLocked(($row ['locked']) ? true : false)
					->setModuleName ($moduleName)
					->setName ($row ['groupname']);
			} else {
				$viewGroup = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $viewGroup;
		}

		/**
		 * @param ViewAdvancedFilterGroup $group
		 *
		 * @return string|null
		 */
		private function getAdvancedFilterGroupConditionExpression ($group) {
			$filters = $group->getFilters ();
			if (empty ($filters)) {
				return null;
			}
			$lastFilter = array_pop ($filters);

			$conditionExpression = array ();
			if (!empty ($filters)) {
				foreach ($filters as $filter) {
					$conditionExpression [] = $filter->getSequence ();
					$conditionExpression [] = $filter->getOperator ();
				}
			}
			$conditionExpression [] = $lastFilter->getSequence ();

			return ' ' . join (' ', $conditionExpression) . ' ';
		}

		/**
		 * @param ViewColorFilterGroup $group
		 *
		 * @return string|null
		 */
		private function getColorFilterGroupConditionExpression ($group) {
			$filters = $group->getFilters ();
			if (empty ($filters)) {
				return null;
			}
			$lastFilter = array_pop ($filters);

			$conditionExpression = array ();
			if (!empty ($filters)) {
				foreach ($filters as $filter) {
					$conditionExpression [] = $filter->getSequence ();
					$conditionExpression [] = $filter->getOperator ();
				}
			}
			$conditionExpression [] = $lastFilter->getSequence ();

			return ' ' . join (' ', $conditionExpression) . ' ';
		}

		/**
		 * @param View $view
		 * @param string $moduleTableName
		 *
		 * @throws ViewAdvancedFilterException
		 * @throws ViewAdvancedFilterGroupException
		 */
		private function saveAdvancedFilterGroups ($view, $moduleTableName = null) {
			$viewId = $view->getId ();
			$groups = $view->getAdvancedFilterGroups ();
			if (empty ($groups)) {
				$this->adb->pquery ('DELETE FROM vtiger_cvadvfilter WHERE cvid=?', array ($viewId));
				$this->adb->pquery ('DELETE FROM vtiger_cvadvfilter_grouping WHERE cvid=?', array ($viewId));
				return;
			}

			$this->validateAdvancedFilterGroupSequenceNumbers ($groups);

			$processedSequences = array ();
			foreach ($groups as $group) {
				$filters = $group->getFilters ();
				if (empty ($filters)) {
					continue;
				}

				$group->setViewId ($viewId);

				$sequence            = $group->getSequence ();
				$conditionExpression = $this->getAdvancedFilterGroupConditionExpression ($group);
				$result              = $this->adb->pquery ('SELECT * FROM vtiger_cvadvfilter_grouping WHERE cvid=? AND groupid=?', array ($viewId, $sequence));
				if ($this->adb->num_rows ($result) == 0) {
					$this->adb->pquery (
						'INSERT INTO vtiger_cvadvfilter_grouping (groupid, cvid, group_condition, condition_expression) VALUES (?, ?, ?, ?)',
						array ($sequence, $viewId, $group->getOperator (), $conditionExpression)
					);
				} else {
					$this->adb->pquery (
						'UPDATE vtiger_cvadvfilter_grouping SET group_condition=?, condition_expression=? WHERE cvid=? AND groupid=?',
						array ($group->getOperator (), $conditionExpression, $viewId, $sequence)
					);
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
				$this->saveAdvancedFilters ($group, $view->getModuleName (), $moduleTableName);
				$processedSequences [] = $sequence;
			}

			if (!empty ($processedSequences)) {
				$questionMarks = str_repeat ('?, ', (count ($processedSequences) - 1)) . '?';
				$this->adb->pquery ("DELETE FROM vtiger_cvadvfilter_grouping WHERE cvid=? AND groupid NOT IN ({$questionMarks})", array_merge (array ($viewId), $processedSequences));
			}
		}

		/**
		 * @param ViewAdvancedFilterGroup $group
		 * @param string $moduleName
		 * @param string $moduleTableName
		 *
		 * @throws ViewAdvancedFilterException
		 */
		private function saveAdvancedFilters ($group, $moduleName, $moduleTableName = null) {
			$groupId = $group->getSequence ();
			$filters = $group->getFilters ();
			$viewId  = $group->getViewId ();
			if (empty ($filters)) {
				$this->adb->pquery ('DELETE FROM vtiger_cvadvfilter WHERE cvid=? AND groupid=?', array ($viewId, $groupId));
				return;
			}

			$processedSequences = array ();
			foreach ($filters as $filter) {
				$filterModuleName = $filter->getModuleName ();
				$filterTableName  = $filter->getTableName ();
				$filter->setViewId ($viewId);
				if (empty ($filterModuleName)) {
					$filter->setModuleName ($moduleName);
				}
				if (empty ($filterTableName)) {
					$filter->setTableName ($moduleTableName);
				}
				if ($filter->getGroupId () === null) {
					$filter->setGroupId ($groupId);
				}
				$this->validateAdvancedFilter ($filter);

				$sequence   = $filter->getSequence ();
				$label      = str_replace (' ', '_', $filter->getLabel ());
				if ($filter->getTableName () == 'vtiger_subfields_values') {
					$joinModuleLabel = '@';
					$filter->setColumnName($this->setGridColumnName ($filter));
				} else {
					$joinModuleLabel = '_';
				}
				$columnName = "{$filter->getTableName ()}:{$filter->getColumnName ()}:{$filter->getFieldName ()}:{$filter->getModuleName ()}{$joinModuleLabel}{$label}:{$filter->getDataType ()}";
				$result     = $this->adb->pquery ('SELECT * FROM vtiger_cvadvfilter WHERE cvid=? AND columnindex=? AND groupid=?', array ($viewId, $sequence, $groupId));
				if ($this->adb->num_rows ($result) == 0) {
					$this->adb->pquery (
						'INSERT INTO vtiger_cvadvfilter (cvid, columnindex, columnname, comparator, value, groupid, column_condition) VALUES (?, ?, ?, ?, ?, ?, ?)',
						array ($viewId, $sequence, $columnName, $filter->getComparator (), $filter->getValue (), $groupId, $filter->getOperator ())
					);
				} else {
					$this->adb->pquery (
						'UPDATE vtiger_cvadvfilter SET columnname=?, comparator=?, value=?, column_condition=? WHERE cvid=? AND columnindex=? AND groupid=?',
						array ($columnName, $filter->getComparator (), $filter->getValue (), $filter->getOperator (), $viewId, $sequence, $groupId)
					);
				}
				DatabaseUtils::closeResult ($result);
				$result                = null;
				$processedSequences [] = $sequence;
			}

			$questionMarks = str_repeat ('?, ', (count ($processedSequences) - 1)) . '?';
			$this->adb->pquery ("DELETE FROM vtiger_cvadvfilter WHERE cvid=? AND groupid=? AND columnindex NOT IN ({$questionMarks})", array_merge (array ($viewId, $groupId), $processedSequences));
		}

		/**
		 * @param View $view
		 * @param string $moduleTableName
		 *
		 * @throws ViewColorFilterException
		 * @throws ViewColorFilterGroupException
		 */
		private function saveColorFilterGroups ($view, $moduleTableName = null) {
			$viewId = $view->getId ();
			$groups = $view->getColorFilterGroups ();
			if (empty ($groups)) {
				$this->adb->pquery ('DELETE FROM vtiger_cvadvcolor WHERE cvid=?', array ($viewId));
				$this->adb->pquery ('DELETE FROM vtiger_cvadvcolor_grouping WHERE cvid=?', array ($viewId));
				return;
			}

			$this->validateColorFilterGroupSequenceNumbers ($groups);

			$processedSequences = array ();
			foreach ($groups as $group) {
				$filters = $group->getFilters ();
				if (empty ($filters)) {
					continue;
				}

				$group->setViewId ($viewId);

				$sequence            = $group->getSequence ();
				$conditionExpression = $this->getColorFilterGroupConditionExpression ($group);
				$result              = $this->adb->pquery ('SELECT * FROM vtiger_cvadvcolor_grouping WHERE cvid=? AND groupid=?', array ($viewId, $sequence));
				if ($this->adb->num_rows ($result) == 0) {
					$this->adb->pquery (
						'INSERT INTO vtiger_cvadvcolor_grouping (groupid, cvid, group_color, condition_expression) VALUES (?, ?, ?, ?)',
						array ($sequence, $viewId, $group->getColor (), $conditionExpression)
					);
				} else {
					$this->adb->pquery (
						'UPDATE vtiger_cvadvcolor_grouping SET group_color=?, condition_expression=? WHERE cvid=? AND groupid=?',
						array ($group->getColor (), $conditionExpression, $viewId, $sequence)
					);
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
				$this->saveColorFilters ($group, $view->getModuleName (), $moduleTableName);
				$processedSequences [] = $sequence;
			}

			if (!empty ($processedSequences)) {
				$questionMarks = str_repeat ('?, ', (count ($processedSequences) - 1)) . '?';
				$this->adb->pquery ("DELETE FROM vtiger_cvadvcolor_grouping WHERE cvid=? AND groupid NOT IN ({$questionMarks})", array_merge (array ($viewId), $processedSequences));
			}
		}

		/**
		 * @param ViewColorFilterGroup $group
		 * @param string $moduleName
		 * @param string $moduleTableName
		 *
		 * @throws ViewColorFilterException
		 */
		private function saveColorFilters ($group, $moduleName, $moduleTableName = null) {
			$groupId = $group->getSequence ();
			$filters = $group->getFilters ();
			$viewId  = $group->getViewId ();
			if (empty ($filters)) {
				$this->adb->pquery ('DELETE FROM vtiger_cvadvcolor WHERE cvid=? AND groupid=?', array ($viewId, $groupId));
				return;
			}

			$processedSequences = array ();
			foreach ($filters as $filter) {
				$filterModuleName = $filter->getModuleName ();
				$filterTableName  = $filter->getTableName ();
				$filter->setViewId ($viewId);
				if (empty ($filterModuleName)) {
					$filter->setModuleName ($moduleName);
				}
				if (empty ($filterTableName)) {
					$filter->setTableName ($moduleTableName);
				}
				if ($filter->getGroupId () === null) {
					$filter->setGroupId ($groupId);
				}
				$this->validateColorFilter ($filter);

				$sequence   = $filter->getSequence ();
				$label      = str_replace (' ', '_', $filter->getLabel ());
				$startDate  = !empty ($filter->getStartDate ()) ? $filter->getStartDate ()->format ('Y-m-d') : null;
				$endDate    = !empty ($filter->getEndDate ()) ? $filter->getEndDate ()->format ('Y-m-d') : null;
				if ($filter->getTableName () == 'vtiger_subfields_values') {
					$joinModuleLabel = '@';
					$filter->setColumnName($this->setGridColumnName ($filter));
				} else {
					$joinModuleLabel = '_';
				}
				$columnName = "{$filter->getTableName ()}:{$filter->getColumnName ()}:{$filter->getFieldName ()}:{$filter->getModuleName ()}$joinModuleLabel{$label}:{$filter->getDataType ()}";
				$result     = $this->adb->pquery ('SELECT * FROM vtiger_cvadvcolor WHERE cvid=? AND columnindex=? AND groupid=?', array ($viewId, $sequence, $groupId));
				if ($this->adb->num_rows ($result) == 0) {
					$this->adb->pquery (
						'INSERT INTO vtiger_cvadvcolor (cvid, columnindex, columnname, comparator, value, groupid, column_condition, startdate, enddate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
						array ($viewId, $sequence, $columnName, $filter->getComparator (), $filter->getValue (), $groupId, $filter->getOperator (), $startDate, $endDate)
					);
				} else {
					$this->adb->pquery (
						'UPDATE vtiger_cvadvcolor SET columnname=?, comparator=?, value=?, column_condition=?, startdate=?, enddate=? WHERE cvid=? AND columnindex=? AND groupid=?',
						array ($columnName, $filter->getComparator (), $filter->getValue (), $filter->getOperator (), $startDate, $endDate, $viewId, $sequence, $groupId)
					);
				}
				DatabaseUtils::closeResult ($result);
				$result                = null;
				$processedSequences [] = $sequence;
			}

			$questionMarks = str_repeat ('?, ', (count ($processedSequences) - 1)) . '?';
			$this->adb->pquery ("DELETE FROM vtiger_cvadvcolor WHERE cvid=? AND groupid=? AND columnindex NOT IN ({$questionMarks})", array_merge (array ($viewId, $groupId), $processedSequences));
		}

		/**
		 * @param View $view
		 * @param string|null $moduleTableName
		 */
		private function saveColumns ($view, $moduleTableName = null) {
			$columns = $view->getColumns ();
			if (empty ($columns)) {
				return;
			}

			$viewId             = $view->getId ();
			$processedSequences = array ();
			foreach ($columns as $column) {
				$columnModuleName = $column->getModuleName ();
				$columnTableName  = $column->getTableName ();
				$column->setViewId ($viewId);
				if (empty ($columnModuleName)) {
					$column->setModuleName ($view->getModuleName ());
				}
				if (empty ($columnTableName)) {
					$column->setTableName ($moduleTableName);
				}
				$this->validateColumn ($column);
				$sequence   = $column->getSequence ();
				$label      = str_replace (' ', '_', $column->getLabel ());
				if ($column->getTableName () == 'vtiger_subfields_values') {
					$joinModuleLabel = '@';
					$column->setColumnName($this->setGridColumnName ($column));
				} else {
					$joinModuleLabel = '_';
				}

				$columnName = "{$column->getTableName ()}:{$column->getColumnName ()}:{$column->getFieldName ()}:{$column->getModuleName ()}{$joinModuleLabel}{$label}:{$column->getDataType ()}";
				$result     = $this->adb->pquery ('SELECT * FROM vtiger_cvcolumnlist WHERE cvid=? AND columnindex=?', array ($viewId, $sequence));
				if ($this->adb->num_rows ($result) == 0) {
					$this->adb->pquery ('INSERT INTO vtiger_cvcolumnlist (cvid, columnindex, columnname) VALUES (?, ?, ?)', array ($viewId, $sequence, $columnName));
				} else {
					$this->adb->pquery ('UPDATE vtiger_cvcolumnlist SET columnname=? WHERE cvid=? AND columnindex=?', array ($columnName, $viewId, $sequence));
				}
				DatabaseUtils::closeResult ($result);
				$result                = null;
				$processedSequences [] = $sequence;
			}

			$questionMarks = str_repeat ('?, ', (count ($processedSequences) - 1)) . '?';
			$this->adb->pquery ("DELETE FROM vtiger_cvcolumnlist WHERE cvid=? AND columnindex NOT IN ({$questionMarks})", array_merge (array ($viewId), $processedSequences));
		}

		/**
		 * @param View $view
		 * @param string $moduleTableName
		 *
		 * @throws ViewStandardFilterException
		 */
		private function saveStandardFilter ($view, $moduleTableName = null) {
			$viewId         = $view->getId ();
			$standardFilter = $view->getStandardFilter ();
			if (empty ($standardFilter)) {
				$this->adb->pquery ('DELETE FROM vtiger_cvstdfilter WHERE cvid=?', array ($viewId));
				return;
			}

			$standardFilterEndDate    = $standardFilter->getEndDate ();
			$standardFilterModuleName = $standardFilter->getModuleName ();
			$standardFilterStartDate  = $standardFilter->getStartDate ();
			$standardFilterTableName  = $standardFilter->getTableName ();

			$standardFilter->setViewId ($viewId);
			if (empty ($standardFilterModuleName)) {
				$standardFilter->setModuleName ($view->getModuleName ());
			}
			if (empty ($standardFilterTableName)) {
				$standardFilter->setTableName ($moduleTableName);
			}
			$this->validateStandardFilter ($standardFilter);

			$label      = str_replace (' ', '_', $standardFilter->getLabel ());
			$columnName = "{$standardFilter->getTableName ()}:{$standardFilter->getColumnName ()}:{$standardFilter->getFieldName ()}:{$standardFilter->getModuleName ()}_{$label}";
			$endDate    = !empty ($standardFilterEndDate) ? $standardFilterEndDate->format ('Y-m-d') : null;
			$startDate  = !empty ($standardFilterStartDate) ? $standardFilterStartDate->format ('Y-m-d') : null;
			$result     = $this->adb->pquery ('SELECT * FROM vtiger_cvstdfilter WHERE cvid=?', array ($viewId));
			if ($this->adb->num_rows ($result) == 0) {
				$this->adb->pquery (
					'INSERT INTO vtiger_cvstdfilter (cvid, columnname, stdfilter, startdate, enddate) VALUES (?, ?, ?, ?, ?)',
					array ($viewId, $columnName, $standardFilter->getPeriod (), $startDate, $endDate)
				);
			} else {
				$this->adb->pquery (
					'UPDATE vtiger_cvstdfilter SET columnname=?, stdfilter=?, startdate=?, enddate=? WHERE cvid=?',
					array ($columnName, $standardFilter->getPeriod (), $startDate, $endDate, $viewId)
				);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
		}

		/**
		 * @param View $view
		 *
		 * @throws Exception
		 */
		private function saveViewGroup ($view) {
			if (empty ($view->getViewGroup())) {
				return;
			} else if (
				count ($this->createdCvGroup) &&
				in_array ($view->getViewGroup()->getName (), $this->createdCvGroup) &&
				!empty ($view->getViewGroup()->getName ()) &&
				empty ($view->getViewGroup()->getId ())
			) {
				$groupName = $view->getViewGroup ()->getName ();
				$this->updateCvByGroup ($view, $groupName);
				return;
			}

			$view->getViewGroup ()->validate();
			$locked = ($view->getViewGroup ()->isLocked()) ? 1 : 0;
			if (empty($view->getViewGroup ()->getId())) {
				$cgGroupId = $this->adb->getUniqueID ('vtiger_customview_master_group');
				$this->adb->pquery(
					'INSERT INTO vtiger_customview_master_group (cvgroupid, groupname, entitytype, locked) VALUES (?, ?, ?, ?)',
					array($cgGroupId, $view->getViewGroup()->getName(), $view->getViewGroup()->getModuleName(), $locked)
				);
				$this->adb->pquery (
					'UPDATE vtiger_customview SET cvgroupid=? WHERE cvid=?',
					array ($cgGroupId, $view->getId())
				);
				$this->createdCvGroup [] = $view->getViewGroup ()->getName();
			} else {
				$this->adb->pquery (
					'UPDATE vtiger_customview_master_group SET groupname=?, locked=? WHERE cvgroupid=?',
					array ($view->getViewGroup()->getName(), $locked, $view->getViewGroup()->getId())
				);
			}
		}

		/**
		 * @param ViewColumn|ViewColorFilter|ViewAdvancedFilter $object
		 *
		 * @return string
		 */
		private function setGridColumnName ($object) {
			$fieldOject = FieldManager::getInstance ($this->adb)->fetchFieldByName($object->getModuleName(), $object->getFieldName ());
			$dummy = explode ('_', $object->getColumnName ());
			array_pop ($dummy);
			$columnName  = join ('_', $dummy);
			return $columnName . '_' . $fieldOject->getId ();
		}

		/**
		 * El string demencial que pusieron los Apus (thank you!, come again!) en vtiger_cvcolumnlist.columnname tiene el formato
		 * tablename:columnname:fieldname:ModuleName_ListViewDisplayLabel:DataType
		 * donde a ListViewDisplayLabel le reemplazan los espacios por underscores '_'
		 *
		 * Para mostrar el pésimo diseño de esta solución, imaginemos que el módulo se llama test_module y la etiqueta sea My test field. El resultado final para esa columna sería:
		 * tablename:columnname:fieldname:test_module_My_test_field:DataType
		 *
		 * Otro ejemplo, imaginemos módulo module y etiqueta My test field. Resultado final:
		 * tablename:columnname:fieldname:module_My_test_field:DataType
		 *
		 * Uno más (no cansa esto), módulo my_test_module, etiqueta código. Resultado final:
		 * tablename:columnname:fieldname:my_test_module_código:DataType
		 *
		 * Esto significa que es imposible obtener con precisión el nombre del módulo y la etiqueta haciendo explode por el caracter '_' en el 4to elemento
		 *
		 * La solución consiste en buscarlo directamente en la base de datos
		 *
		 * @param string $moduleName
		 * @param string $vtigerColumnName
		 *
		 * @return array
		 */
		private function parseVtigerColumnName ($moduleName, $vtigerColumnName) {
			$dummy = explode (':', $vtigerColumnName);
			if ($dummy [0] == 'vtiger_subfields_values') {
				$moduleAndLabel = explode('@', $dummy [3], 2);
				$columnName = $dummy [1];
				$fieldId    = 0;
				$fieldLabel = $moduleAndLabel [1];
				$fieldName  = $dummy [2];
				$tableName  = $dummy [0];
			} else if (isset ($dummy [3])) {
				$key    = "{$dummy [0]}:{$dummy [1]}:{$dummy [2]}:{$moduleName}_%";
				$result = $this->adb->pquery (
					"SELECT
						f.*,
						t.name AS modulename
					FROM
						vtiger_field f
						INNER JOIN vtiger_tab t ON t.tabid=f.tabid
					WHERE
						CONCAT(f.tablename, ':', f.columnname, ':', f.fieldname, ':', t.name, '_') LIKE ?",
					array ($key)
				);
				if ($this->adb->num_rows ($result) == 0) {
					$columnName = $dummy [1];
					$fieldId    = 0;
					$fieldLabel = null;
					$fieldName  = $dummy [2];
					$moduleName = null;
					$tableName  = $dummy [0];
				} else {
					$row        = $this->adb->fetchByAssoc ($result, -1, false);
					$columnName = $row ['columnname'];
					$fieldId    = $row ['fieldid'];
					$fieldLabel = $row ['fieldlabel'];
					$fieldName  = $row ['fieldname'];
					$moduleName = $row ['modulename'];
					$tableName  = $row ['tablename'];
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
			} else {
				$columnName = isset ($dummy [1]) ? $dummy [1] : null;
				$fieldLabel = null;
				$fieldName  = isset ($dummy [2]) ? $dummy [2] : null;
				$moduleName = null;
				$tableName  = isset ($dummy [0]) ? $dummy [0] : null;
			}

			return array (
				'columnname' => $columnName,
				'datatype'   => isset ($dummy [4]) ? $dummy [4] : null,
				'fieldid'    => $fieldId,
				'fieldlabel' => $fieldLabel,
				'fieldname'  => $fieldName,
				'modulename' => $moduleName,
				'tablename'  => $tableName,
			);
		}

		/**
		 * @param View $view
		 * @param string $groupName
		 */
		private function updateCvByGroup ($view, $groupName) {
			if (empty ($groupName)) {
				return;
			}
			$this->adb->query (
				"UPDATE vtiger_customview SET cvgroupid=(SELECT cvgroupid FROM vtiger_customview_master_group WHERE groupname='{$groupName}') WHERE cvid={$view->getId()}"
			);
		}

		/**
		 * @param View $view
		 * @param string $moduleTableName
		 *
		 * @throws Exception
		 */
		private function updateView ($view, $moduleTableName) {
			$this->adb->startTransaction ();
			$viewId    = $view->getId ();
			$cvGroupId = (!empty($view->getViewGroup())) ? $view->getViewGroup()->getId() : null;
			$this->adb->pquery (
				'UPDATE vtiger_customview SET viewname=?, setdefault=?, setmetrics=?, entitytype=?, status=?, userid=?, locked=?, searchview=?, deskview=?, cvgroupid=? WHERE cvid=?',
				array ($view->getName (), $view->getDefault (), $view->getShowCountInMenu (), $view->getModuleName (), $view->getStatus (), $view->getOwner (), $view->isLocked (), $view->getSearchView (), $view->getDeskView (),  $cvGroupId, $viewId)
			);
			$this->saveColumns ($view, $moduleTableName);
			$this->saveStandardFilter ($view, $moduleTableName);
			$this->saveAdvancedFilterGroups ($view, $moduleTableName);
			$this->saveColorFilterGroups ($view, $moduleTableName);
			$this->saveViewGroup ($view);
			$this->adb->completeTransaction ();
		}

		/**
		 * @param View $view
		 *
		 * @throws ViewException
		 * @throws ViewColumnException
		 * @throws ViewStandardFilterException
		 */
		private function validate ($view) {
			if ((empty ($view)) || (!($view instanceof View))) {
				throw new ViewException (ViewException::ERROR_VIEW_EMPTY);
			}

			$view->validate ();

			$moduleName = $view->getModuleName ();
			if (empty ($moduleName)) {
				throw new ViewException (ViewException::ERROR_VIEW_EMPTY_MODULE_NAME);
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_tab WHERE name=?', array ($moduleName));
			if ($this->adb->num_rows ($result) == 0) {
				DatabaseUtils::closeResult ($result);
				$result = null;
				throw new ViewException (ViewException::ERROR_VIEW_INVALID_MODULE_NAME);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			$result = $this->adb->pquery ('SELECT v.* FROM vtiger_customview v WHERE v.viewname=? AND v.entitytype=?', array ($view->getName (), $view->getModuleName ()));
			if ($this->adb->num_rows ($result) == 0) {
				DatabaseUtils::closeResult ($result);
				$result = null;
				return;
			}

			$row = $this->adb->fetchByAssoc ($result, -1, false);
			DatabaseUtils::closeResult ($result);
			$result = null;
			$viewId = $view->getId ();
			if ((empty ($viewId)) || ($row ['cvid'] != $viewId)) {
				throw new ViewException (ViewException::ERROR_VIEW_DUPLICATE_NAME);
			}
		}

		/**
		 * @param ViewAdvancedFilter $filter
		 *
		 * @throws ViewAdvancedFilterException
		 * @throws Exception
		 */
		private function validateAdvancedFilter ($filter) {
			$groupId    = $filter->getGroupId ();
			$moduleName = $filter->getModuleName ();
			$tableName  = $filter->getTableName ();
			$viewId     = $filter->getViewId ();
			if (empty ($viewId)) {
				throw new ViewAdvancedFilterException (ViewAdvancedFilterException::ERROR_VIEW_ADVANCED_FILTER_EMPTY_VIEW_ID);
			} else if (empty ($moduleName)) {
				throw new ViewAdvancedFilterException (ViewAdvancedFilterException::ERROR_VIEW_ADVANCED_FILTER_EMPTY_MODULE_NAME);
			} else if (empty ($tableName)) {
				throw new ViewAdvancedFilterException (ViewAdvancedFilterException::ERROR_VIEW_ADVANCED_FILTER_EMPTY_TABLE_NAME);
			} else if (!isset ($groupId)) {
				throw new ViewAdvancedFilterException (ViewAdvancedFilterException::ERROR_VIEW_ADVANCED_FILTER_EMPTY_GROUP_ID);
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_tab WHERE name=?', array ($moduleName));
			if ($this->adb->num_rows ($result) == 0) {
				DatabaseUtils::closeResult ($result);
				$result = null;
				throw new ViewAdvancedFilterException (ViewAdvancedFilterException::ERROR_VIEW_ADVANCED_FILTER_INVALID_MODULE_NAME);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			$result = $this->adb->pquery ('SELECT f.* FROM vtiger_field f INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=? WHERE f.fieldname=?', array ($moduleName, $filter->getFieldName ()));
			if ($this->adb->num_rows ($result) == 0) {
				DatabaseUtils::closeResult ($result);
				$result = null;
				throw new ViewAdvancedFilterException (ViewAdvancedFilterException::ERROR_VIEW_ADVANCED_FILTER_INVALID_FIELD_NAME);
			}

			$row = $this->adb->fetchByAssoc ($result, -1, false);
			DatabaseUtils::closeResult ($result);
			$result = null;
			if ($tableName == 'vtiger_subfields_values') {
				return;
			} else if ($row ['columnname'] != $filter->getColumnName ()) {
				throw new ViewAdvancedFilterException (ViewAdvancedFilterException::ERROR_VIEW_ADVANCED_FILTER_INVALID_COLUMN_NAME);
			} else if ($row ['tablename'] != $tableName) {
				throw new ViewAdvancedFilterException (ViewAdvancedFilterException::ERROR_VIEW_ADVANCED_FILTER_INVALID_TABLE_NAME);
			}
		}

		/**
		 * @param ViewColorFilter $filter
		 *
		 * @throws ViewColorFilterException
		 */
		private function validateColorFilter ($filter) {
			$groupId    = $filter->getGroupId ();
			$moduleName = $filter->getModuleName ();
			$tableName  = $filter->getTableName ();
			$viewId     = $filter->getViewId ();
			if (empty ($viewId)) {
				throw new ViewColorFilterException (ViewColorFilterException::ERROR_VIEW_COLOR_FILTER_EMPTY_VIEW_ID);
			} else if (empty ($moduleName)) {
				throw new ViewColorFilterException (ViewColorFilterException::ERROR_VIEW_COLOR_FILTER_EMPTY_MODULE_NAME);
			} else if (empty ($tableName)) {
				throw new ViewColorFilterException (ViewColorFilterException::ERROR_VIEW_COLOR_FILTER_EMPTY_TABLE_NAME);
			} else if (!isset ($groupId)) {
				throw new ViewColorFilterException (ViewColorFilterException::ERROR_VIEW_COLOR_FILTER_EMPTY_GROUP_ID);
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_tab WHERE name=?', array ($moduleName));
			if ($this->adb->num_rows ($result) == 0) {
				DatabaseUtils::closeResult ($result);
				$result = null;
				throw new ViewColorFilterException (ViewColorFilterException::ERROR_VIEW_COLOR_FILTER_INVALID_MODULE_NAME);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			$result = $this->adb->pquery ('SELECT f.* FROM vtiger_field f INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=? WHERE f.fieldname=?', array ($moduleName, $filter->getFieldName ()));
			if ($this->adb->num_rows ($result) == 0) {
				DatabaseUtils::closeResult ($result);
				$result = null;
				throw new ViewColorFilterException (ViewColorFilterException::ERROR_VIEW_COLOR_FILTER_INVALID_FIELD_NAME);
			}

			$row = $this->adb->fetchByAssoc ($result, -1, false);
			DatabaseUtils::closeResult ($result);
			$result = null;
			if ($tableName == 'vtiger_subfields_values') {
				return;
			} else if ($row ['columnname'] != $filter->getColumnName ()) {
				throw new ViewColorFilterException (ViewColorFilterException::ERROR_VIEW_COLOR_FILTER_INVALID_COLUMN_NAME);
			} else if ($row ['tablename'] != $tableName) {
				throw new ViewColorFilterException (ViewColorFilterException::ERROR_VIEW_COLOR_FILTER_INVALID_TABLE_NAME);
			}
		}

		/**
		 * @param ViewAdvancedFilterGroup[] $groups
		 *
		 * @throws ViewAdvancedFilterException
		 * @throws ViewAdvancedFilterGroupException
		 */
		private function validateAdvancedFilterGroupSequenceNumbers ($groups) {
			// Validar los números de secuencia de grupos y filtros
			$processedGroupSequences  = array ();
			$processedFilterSequences = array ();
			foreach ($groups as $group) {
				if (in_array ($group->getSequence (), $processedGroupSequences)) {
					throw new ViewAdvancedFilterGroupException (ViewAdvancedFilterGroupException::ERROR_VIEW_ADVANCED_FILTER_GROUP_SEQUENCE_ALREADY_TAKEN);
				}

				$filters = $group->getFilters ();
				if (!empty ($filters)) {
					foreach ($filters as $filter) {
						if (in_array ($filter->getSequence (), $processedFilterSequences)) {
							throw new ViewAdvancedFilterException (ViewAdvancedFilterException::ERROR_VIEW_ADVANCED_FILTER_SEQUENCE_ALREADY_TAKEN);
						}
						$processedFilterSequences [] = $filter->getSequence ();
					}
				}

				$processedGroupSequences [] = $group->getSequence ();
			}
		}

		/**
		 * @param ViewColorFilterGroup[] $groups
		 *
		 * @throws ViewColorFilterException
		 * @throws ViewColorFilterGroupException
		 */
		private function validateColorFilterGroupSequenceNumbers ($groups) {
			// Validar los números de secuencia de grupos y filtros
			$processedGroupSequences  = array ();
			$processedFilterSequences = array ();
			foreach ($groups as $group) {
				if (in_array ($group->getSequence (), $processedGroupSequences)) {
					throw new ViewColorFilterGroupException (ViewColorFilterGroupException::ERROR_VIEW_COLOR_FILTER_GROUP_SEQUENCE_ALREADY_TAKEN);
				}

				$filters = $group->getFilters ();
				if (!empty ($filters)) {
					foreach ($filters as $filter) {
						if (in_array ($filter->getSequence (), $processedFilterSequences)) {
							throw new ViewColorFilterException (ViewColorFilterException::ERROR_VIEW_COLOR_FILTER_SEQUENCE_ALREADY_TAKEN);
						}
						$processedFilterSequences [] = $filter->getSequence ();
					}
				}

				$processedGroupSequences [] = $group->getSequence ();
			}
		}

		/**
		 * @param ViewColumn $column
		 *
		 * @throws ViewColumnException
		 */
		private function validateColumn ($column) {
			$moduleName = $column->getModuleName ();
			$tableName  = $column->getTableName ();
			$viewId     = $column->getViewId ();
			if (empty ($viewId)) {
				throw new ViewColumnException (ViewColumnException::ERROR_VIEW_COLUMN_EMPTY_VIEW_ID);
			} else if (empty ($moduleName)) {
				throw new ViewColumnException (ViewColumnException::ERROR_VIEW_COLUMN_EMPTY_MODULE_NAME);
			} else if (empty ($tableName)) {
				throw new ViewColumnException (ViewColumnException::ERROR_VIEW_COLUMN_EMPTY_TABLE_NAME);
			}
		}

		/**
		 * @param ViewStandardFilter $filter
		 *
		 * @throws ViewStandardFilterException
		 */
		private function validateStandardFilter ($filter) {
			$moduleName = $filter->getModuleName ();
			$tableName  = $filter->getTableName ();
			$viewId     = $filter->getViewId ();
			if (empty ($viewId)) {
				throw new ViewStandardFilterException (ViewStandardFilterException::ERROR_VIEW_STANDARD_FILTER_EMPTY_VIEW_ID);
			} else if (empty ($moduleName)) {
				throw new ViewStandardFilterException (ViewStandardFilterException::ERROR_VIEW_STANDARD_FILTER_EMPTY_MODULE_NAME);
			} else if (empty ($tableName)) {
				throw new ViewStandardFilterException (ViewStandardFilterException::ERROR_VIEW_STANDARD_FILTER_EMPTY_TABLE_NAME);
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_tab WHERE name=?', array ($filter->getModuleName ()));
			if ($this->adb->num_rows ($result) == 0) {
				DatabaseUtils::closeResult ($result);
				$result = null;
				throw new ViewStandardFilterException (ViewStandardFilterException::ERROR_VIEW_STANDARD_FILTER_INVALID_MODULE_NAME);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			$result = $this->adb->pquery ('SELECT f.* FROM vtiger_field f INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=? WHERE f.fieldname=?', array ($filter->getModuleName (), $filter->getFieldName ()));
			if ($this->adb->num_rows ($result) == 0) {
				DatabaseUtils::closeResult ($result);
				$result = null;
				throw new ViewStandardFilterException (ViewStandardFilterException::ERROR_VIEW_STANDARD_FILTER_INVALID_FIELD_NAME);
			}

			$row = $this->adb->fetchByAssoc ($result, -1, false);
			DatabaseUtils::closeResult ($result);
			$result = null;
			if ($row ['columnname'] != $filter->getColumnName ()) {
				throw new ViewStandardFilterException (ViewStandardFilterException::ERROR_VIEW_STANDARD_FILTER_INVALID_COLUMN_NAME);
			} else if ($row ['tablename'] != $filter->getTableName ()) {
				throw new ViewStandardFilterException (ViewStandardFilterException::ERROR_VIEW_STANDARD_FILTER_INVALID_TABLE_NAME);
			}
		}

		/**
		 * @param Field $field
		 */
		public function deleteFieldFromViews ($field) {
			$columnName = "{$field->getTableName ()}:{$field->getColumnName ()}:{$field->getName ()}:{$field->getModuleName ()}_%";
			$this->adb->pquery ('DELETE FROM vtiger_cvadvcolor WHERE columnname LIKE ?', array ($columnName));
			$this->adb->pquery ('DELETE FROM vtiger_cvadvfilter WHERE columnname LIKE ?', array ($columnName));
			$this->adb->pquery ('DELETE FROM vtiger_cvcolumnlist WHERE columnname LIKE ?', array ($columnName));
			$this->adb->pquery ('DELETE FROM vtiger_cvstdfilter WHERE columnname LIKE ?', array ($columnName));
			
			// Limpieza adicional usando solo el nombre de columna (para campos calculados y otros casos especiales)
			$fieldColumnName = $field->getColumnName();
			$this->adb->pquery ('DELETE FROM vtiger_cvadvcolor WHERE columnname LIKE ?', array ("%:{$fieldColumnName}:%"));
			$this->adb->pquery ('DELETE FROM vtiger_cvadvfilter WHERE columnname LIKE ?', array ("%:{$fieldColumnName}:%"));
			$this->adb->pquery ('DELETE FROM vtiger_cvcolumnlist WHERE columnname LIKE ?', array ("%:{$fieldColumnName}:%"));
			$this->adb->pquery ('DELETE FROM vtiger_cvstdfilter WHERE columnname LIKE ?', array ("%:{$fieldColumnName}:%"));
		}

		/**
		 * @param View $view
		 */
		public function deleteView ($view) {
			if ((empty ($view)) || (!($view instanceof View))) {
				return;
			}

			$viewId = $view->getId ();
			if (empty ($viewId)) {
				return;
			}

			$moduleName = $view->getModuleName ();
			$identifier = $view->getId ();
			$this->adb->startTransaction ();
			if ((!empty ($moduleName)) && (!empty ($identifier))) {
				$this->adb->pquery ('DELETE FROM vtiger_deletedelements WHERE elementtype=? AND modulename=? AND identifier=?', array ('view', $moduleName, $identifier));
				$this->adb->pquery ('INSERT INTO vtiger_deletedelements (elementtype, modulename, identifier, deletedon, serializedobject) VALUES (?, ?, ?, ?, ?)', array ('view', $moduleName, $identifier, date ('Y-m-d h:i:s'), serialize ($view)));
			}

			$this->adb->startTransaction ();
			$this->deleteViewProfiles ($view);
			$this->adb->pquery ('DELETE FROM vtiger_cvadvcolor WHERE cvid=?', array ($viewId));
			$this->adb->pquery ('DELETE FROM vtiger_cvadvcolor_grouping WHERE cvid=?', array ($viewId));
			$this->adb->pquery ('DELETE FROM vtiger_cvadvfilter WHERE cvid=?', array ($viewId));
			$this->adb->pquery ('DELETE FROM vtiger_cvadvfilter_grouping WHERE cvid=?', array ($viewId));
			$this->adb->pquery ('DELETE FROM vtiger_cvstdfilter WHERE cvid=?', array ($viewId));
			$this->adb->pquery ('DELETE FROM vtiger_cvcolumnlist WHERE cvid=?', array ($viewId));
			$this->adb->pquery ('DELETE FROM vtiger_customview WHERE cvid=?', array ($viewId));
			$this->adb->completeTransaction ();
		}

		/**
		 * @param string $moduleName
		 * @param boolean $ignoreLock
		 */
		public function deleteViews ($moduleName, $ignoreLock = true) {
			if (empty ($moduleName)) {
				return;
			}

			if (!$ignoreLock) {
				$whereClause = 'AND locked=0';
			} else {
				$whereClause = '';
			}

			$this->adb->startTransaction ();
			$this->adb->pquery ("DELETE FROM vtiger_cvadvcolor WHERE cvid IN (SELECT cvid FROM vtiger_customview WHERE entitytype=? {$whereClause})", array ($moduleName));
			$this->adb->pquery ("DELETE FROM vtiger_cvadvcolor_grouping WHERE cvid IN (SELECT cvid FROM vtiger_customview WHERE entitytype=? {$whereClause})", array ($moduleName));
			$this->adb->pquery ("DELETE FROM vtiger_cvadvfilter WHERE cvid IN (SELECT cvid FROM vtiger_customview WHERE entitytype=? {$whereClause})", array ($moduleName));
			$this->adb->pquery ("DELETE FROM vtiger_cvadvfilter_grouping WHERE cvid IN (SELECT cvid FROM vtiger_customview WHERE entitytype=? {$whereClause})", array ($moduleName));
			$this->adb->pquery ("DELETE FROM vtiger_cvstdfilter WHERE cvid IN (SELECT cvid FROM vtiger_customview WHERE entitytype=? {$whereClause})", array ($moduleName));
			$this->adb->pquery ("DELETE FROM vtiger_cvcolumnlist WHERE cvid IN (SELECT cvid FROM vtiger_customview WHERE entitytype=? {$whereClause})", array ($moduleName));
			$this->adb->pquery ("DELETE FROM vtiger_customview WHERE entitytype=? {$whereClause}", array ($moduleName));
			$this->adb->completeTransaction ();
		}

		/**
		 * @param string $moduleName
		 * @param boolean $headersOnly
		 *
		 * @return null|View
		 * @throws Exception
		 */
		public function fetchDefaultView ($moduleName, $headersOnly = false) {
			if (empty ($moduleName)) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_customview WHERE entitytype=? AND setdefault=?', array ($moduleName, 1));
			if ($this->adb->num_rows ($result) > 0) {
				$row    = $this->adb->fetchByAssoc ($result, -1, false);
				$viewId = intval ($row ['cvid']);
				if (!$headersOnly) {
					$advancedFilterGroups = $this->fetchAdvancedFilterGroupsByViewId ($moduleName, $viewId);
					$colorFilterGroups    = $this->fetchColorFilterGroupsByViewId ($moduleName, $viewId);
					$columns              = $this->fetchColumnsByViewId ($moduleName, $viewId);
					$standardFilter       = $this->fetchStandardFilterByViewId ($moduleName, $viewId);
				} else {
					$advancedFilterGroups = null;
					$colorFilterGroups    = null;
					$columns              = null;
					$standardFilter       = null;
				}
				$view = View::getInstance ()
					->setAdvancedFilterGroups ($advancedFilterGroups)
					->setColorFilterGroups ($colorFilterGroups)
					->setColumns ($columns)
					->setDefault ($row ['setdefault'])
					->setId ($viewId)
					->setLocked ($row ['locked'] == 1)
					->setModuleName ($moduleName)
					->setName ($row ['viewname'])
					->setOwner (intval ($row ['userid']))
					->setShowCountInMenu (intval ($row ['setmetrics']))
					->setStandardFilter ($standardFilter)
					->setStatus (intval ($row ['status']))
					->setViewGroup($this->fetchViewGroup($moduleName, $row ['cvgroupid']));
			} else {
				$view = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $view;
		}

		/**
		 * @param string $moduleName
		 * @param string $viewName
		 * @param boolean $headersOnly
		 *
		 * @return View|null
		 */
		public function fetchView ($moduleName, $viewName, $headersOnly = false) {
			if ((empty ($moduleName)) || (empty ($viewName))) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_customview WHERE entitytype=? AND viewname=?', array ($moduleName, $viewName));
			if ($this->adb->num_rows ($result) > 0) {
				$row    = $this->adb->fetchByAssoc ($result, -1, false);
				$viewId = intval ($row ['cvid']);
				$view   = View::getInstance ()
					->setDefault ($row ['setdefault'])
					->setId ($viewId)
					->setLocked ($row ['locked'] == 1)
					->setModuleName ($moduleName)
					->setName ($viewName)
					->setOwner (intval ($row ['userid']))
					->setSearchView ($row ['searchview'])
					->setDeskView ($row ['deskview'])
					->setShowCountInMenu (intval ($row ['setmetrics']))
					->setStatus (intval ($row ['status']))
					->setViewGroup($this->fetchViewGroup($moduleName, $row ['cvgroupid']));
				if (!$headersOnly) {
					$view->setAdvancedFilterGroups ($this->fetchAdvancedFilterGroupsByViewId ($moduleName, $viewId))
						->setColorFilterGroups ($this->fetchColorFilterGroupsByViewId ($moduleName, $viewId))
						->setColumns ($this->fetchColumnsByViewId ($moduleName, $viewId))
						->setStandardFilter ($this->fetchStandardFilterByViewId ($moduleName, $viewId));
				}
			} else {
				$view = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $view;
		}

		/**
		 * @param string $moduleName
		 * @param integer $viewId
		 * @param boolean $headersOnly
		 *
		 * @return null|View
		 */
		public function fetchViewById ($moduleName, $viewId, $headersOnly = false) {
			if ((empty ($moduleName)) || (empty ($viewId))) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_customview WHERE entitytype=? AND cvid=?', array ($moduleName, $viewId));
			if ($this->adb->num_rows ($result) > 0) {
				$row  = $this->adb->fetchByAssoc ($result, -1, false);
				$view = View::getInstance ()
					->setAdvancedFilterGroups (!$headersOnly ? $this->fetchAdvancedFilterGroupsByViewId ($moduleName, $viewId) : null)
					->setColorFilterGroups (!$headersOnly ? $this->fetchColorFilterGroupsByViewId ($moduleName, $viewId) : null)
					->setColumns (!$headersOnly ? $this->fetchColumnsByViewId ($moduleName, $viewId) : null)
					->setDefault ($row ['setdefault'])
					->setId ($viewId)
					->setLocked ($row ['locked'] == 1)
					->setModuleName ($moduleName)
					->setName ($row ['viewname'])
					->setOwner (intval ($row ['userid']))
					->setSearchView ($row ['searchview'])
					->setDeskView ($row ['deskview'])
					->setShowCountInMenu (intval ($row ['setmetrics']))
					->setStandardFilter (!$headersOnly ? $this->fetchStandardFilterByViewId ($moduleName, $viewId) : null)
					->setStatus (intval ($row ['status']))
					->setViewGroup($this->fetchViewGroup($moduleName, $row ['cvgroupid']));
			} else {
				$view = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $view;
		}

		/**
		 * @param string $moduleName
		 * @param boolean $includeDeleted
		 * @param boolean $headersOnly
		 *
		 * @return View[]|null
		 * @throws Exception
		 */
		public function fetchViews ($moduleName, $includeDeleted = false, $headersOnly = false) {
			if (empty ($moduleName)) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_customview WHERE entitytype=?', array ($moduleName));
			if ($this->adb->num_rows ($result) > 0) {
				$views = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$viewId = intval ($row ['cvid']);
					$view   = View::getInstance ()
						->setDefault ($row ['setdefault'])
						->setId ($viewId)
						->setLocked ($row ['locked'] == 1)
						->setModuleName ($moduleName)
						->setName ($row ['viewname'])
						->setOwner (intval ($row ['userid']))
						->setSearchView ($row ['searchview'])
						->setDeskView ($row ['deskview'])
						->setShowCountInMenu (intval ($row ['setmetrics']))
						->setStatus (intval ($row ['status']))
						->setViewGroup($this->fetchViewGroup($moduleName, $row ['cvgroupid']));
					if (!$headersOnly) {
						$view->setAdvancedFilterGroups ($this->fetchAdvancedFilterGroupsByViewId ($moduleName, $viewId))
							->setColorFilterGroups ($this->fetchColorFilterGroupsByViewId ($moduleName, $viewId))
							->setColumns ($this->fetchColumnsByViewId ($moduleName, $viewId))
							->setStandardFilter ($this->fetchStandardFilterByViewId ($moduleName, $viewId));
					}
					$views [] = $view;
				}

				if ($includeDeleted) {
					$deletedViews = $this->fetchDeletedViews ($moduleName);
				} else {
					$deletedViews = array ();
				}

				$views = array_merge ($views, $deletedViews);
			} else {
				$views = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $views;
		}

		/**
		 * @param integer $groupId
		 *
		 * @return null|ViewGroup
		 * @throws Exception
		 */
		public function fetchViewGroupById ($groupId) {
			if (empty($groupId) || !is_numeric($groupId)) {
				return null;
			}
			$result = $this->adb->pquery ('SELECT * FROM vtiger_customview_master_group WHERE cvgroupid=?', array ($groupId));
			if ($this->adb->num_rows ($result) > 0) {
				$row = $this->adb->fetchByAssoc($result, -1, false);
				$viewGroup = ViewGroup::getInstance()
					->setId ($row ['cvgroupid'])
					->setLocked(($row ['locked'] ? true : false))
					->setModuleName ($row ['entitytype'])
					->setName($row ['groupname']);
			} else {
				$viewGroup = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $viewGroup;
		}
		
		/**
		 * @param string $moduleName
		 * @param string $groupName
		 *
		 * @return null|ViewGroup
		 * @throws Exception
		 */
		public function fetchViewGroupByName ($moduleName, $groupName) {
			if (empty ($groupName) || empty ($moduleName)) {
				return null;
			}
			$result = $this->adb->pquery ('SELECT * FROM vtiger_customview_master_group WHERE entitytype=? AND groupname=?  LIMIT 1' , array ($moduleName, $groupName));
			if ($this->adb->num_rows ($result) > 0) {
				$row = $this->adb->fetchByAssoc($result, -1, false);
				$viewGroup = ViewGroup::getInstance()
					->setId ($row ['cvgroupid'])
					->setLocked (($row ['locked'] ? true : false))
					->setModuleName ($row ['entitytype'])
					->setName ($row ['groupname']);
			} else {
				$viewGroup = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $viewGroup;
		}
		
		/**
		 * @param string $moduleName
		 *
		 * @return null|ViewGroup[]
		 * @throws Exception
		 */
		public function fetchViewGroupByModule ($moduleName) {
			if (empty($moduleName)) {
				return null;
			}
			$result = $this->adb->pquery ('SELECT * FROM vtiger_customview_master_group WHERE entitytype=?', array ($moduleName));
			if ($this->adb->num_rows ($result) > 0) {
				$viewGroup = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$viewGroup [] = ViewGroup::getInstance()
						->setId ($row ['cvgroupid'])
						->setLocked(($row ['locked'] ? true : false))
						->setModuleName ($row ['entitytype'])
						->setName($row ['groupname']);
				}
			} else {
				$viewGroup = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $viewGroup;
		}

		/**
		 * @param View $view
		 * @param string|null $moduleTableName
		 * @param boolean $ignoreLock
		 *
		 * @return View
		 * @throws Exception
		 * @throws ViewException
		 */
		public function saveView ($view, $moduleTableName = null, $ignoreLock = true) {
			$this->validate ($view);

			$isDeleted = $view->isDeleted ();
			if ($isDeleted) {
				return $view;
			}

			$viewId = $view->getId ();
			if (!empty ($viewId)) {
				$result = $this->adb->pquery ('SELECT * FROM vtiger_customview v WHERE cvid=?', array ($viewId));
			} else {
				$result = $this->adb->pquery ('SELECT * FROM vtiger_customview v WHERE viewname=? AND entitytype=?', array ($view->getName (), $view->getModuleName ()));
			}

			if ($this->adb->num_rows ($result) > 0) {
				$row      = $this->adb->fetchByAssoc ($result, -1, false);
				$viewId   = intval ($row ['cvid']);
				$isLocked = ($row ['locked'] == 1);
			} else {
				$viewId   = null;
				$isLocked = false;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			if (empty ($viewId)) {
				$this->createView ($view, $moduleTableName);
			} else if (($ignoreLock) || (!$isLocked)) {
				$this->updateView ($view, $moduleTableName);
			}

			$isDefault = $view->getDefault ();
			if ($isDefault == View::DEFAULT_YES) {
				$this->adb->pquery ('UPDATE vtiger_customview SET setdefault=0 WHERE entitytype=? AND cvid<>?', array ($view->getModuleName (), $view->getId ()));
			} else {
				$result = $this->adb->pquery ('SELECT * FROM vtiger_customview v WHERE entitytype=? AND setdefault=1', array ($view->getModuleName ()));
				if ($this->adb->num_rows ($result) == 0) {
					$this->adb->pquery ('UPDATE vtiger_customview SET setdefault=1 WHERE entitytype=? AND viewname=?', array ($view->getModuleName (), 'All'));
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
			}

			return $view;
		}

		/**
		 * @param string $moduleName
		 * @param View[]|null $views
		 * @param string|null $moduleTableName
		 * @param boolean $ignoreLock
		 *
		 * @throws Exception
		 */
		public function saveViews ($moduleName, $views, $moduleTableName = null, $ignoreLock = true) {
			if (empty ($moduleName)) {
				return;
			} else if (empty ($views)) {
				$this->deleteViews ($moduleName, $ignoreLock);
				return;
			}

			$this->deleteViewGroup ($moduleName);
			$processedViewIds      = array ();
			foreach ($views as $view) {
				$viewModuleName = $view->getModuleName ();
				if (empty ($viewModuleName)) {
					$view->setModuleName ($moduleName);
				}
				if (!empty($view->getViewGroup ())) {
					$view->getViewGroup ()->setId (null);
				}

				$this->saveView ($view, $moduleTableName, $ignoreLock);
				$processedViewIds []      = $view->getId ();
			}

			if (!$ignoreLock) {
				$whereClause = 'AND locked=0';
			} else {
				$whereClause = '';
			}
			$questionMarks = str_repeat ('?, ', (count ($processedViewIds) - 1)) . '?';
			$this->adb->pquery (
				"DELETE FROM vtiger_cvadvcolor WHERE cvid IN (SELECT cvid FROM vtiger_customview WHERE entitytype=? AND cvid NOT IN ({$questionMarks}) {$whereClause})",
				array_merge (array ($moduleName), $processedViewIds)
			);
			$this->adb->pquery (
				"DELETE FROM vtiger_cvadvcolor_grouping WHERE cvid IN (SELECT cvid FROM vtiger_customview WHERE entitytype=? AND cvid NOT IN ({$questionMarks}) {$whereClause})",
				array_merge (array ($moduleName), $processedViewIds)
			);
			$this->adb->pquery (
				"DELETE FROM vtiger_cvadvfilter WHERE cvid IN (SELECT cvid FROM vtiger_customview WHERE entitytype=? AND cvid NOT IN ({$questionMarks}) {$whereClause})",
				array_merge (array ($moduleName), $processedViewIds)
			);
			$this->adb->pquery (
				"DELETE FROM vtiger_cvadvfilter_grouping WHERE cvid IN (SELECT cvid FROM vtiger_customview WHERE entitytype=? AND cvid NOT IN ({$questionMarks}) {$whereClause})",
				array_merge (array ($moduleName), $processedViewIds)
			);
			$this->adb->pquery (
				"DELETE FROM vtiger_cvstdfilter WHERE cvid IN (SELECT cvid FROM vtiger_customview WHERE entitytype=? AND cvid NOT IN ({$questionMarks}) {$whereClause})",
				array_merge (array ($moduleName), $processedViewIds)
			);
			$this->adb->pquery (
				"DELETE FROM vtiger_cvcolumnlist WHERE cvid IN (SELECT cvid FROM vtiger_customview WHERE entitytype=? AND cvid NOT IN ({$questionMarks}) {$whereClause})",
				array_merge (array ($moduleName), $processedViewIds)
			);
			$this->adb->pquery (
				"DELETE FROM vtiger_customview WHERE entitytype=? AND cvid NOT IN ({$questionMarks}) {$whereClause}",
				array_merge (array ($moduleName), $processedViewIds)
			);
		}

		/**
		 * @param View $view
		 *
		 * @throws ViewException
		 * @throws ViewColumnException
		 * @throws ViewStandardFilterException
		 */
		public function validateView ($view) {
			$this->validate ($view);
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return ViewManager
		 */
		public static function getInstance (PearDatabase $adb) {
			if (self::$INSTANCES === null) {
				self::$INSTANCES = array ();
			}
			if (!isset (self::$INSTANCES [ $adb->dbName ])) {
				self::$INSTANCES [ $adb->dbName ] = new self ($adb);
			}
			return self::$INSTANCES [ $adb->dbName ];
		}

	}
