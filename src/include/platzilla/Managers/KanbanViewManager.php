<?php
	require_once ('include/platzilla/Objects/KanbanView.php');
	require_once ('include/platzilla/Objects/KanbanFieldConfig.php');
	require_once ('include/platzilla/Objects/KanbanCardConfig.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');

	class KanbanViewManager {

		/** @var KanbanViewManager */
		private static $INSTANCE = null;

		/** @var PearDatabase  */
		private $adb;

		/**
		 * Constructor
		 *
		 * @param PearDatabase $adb
		 */
		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
		}

		/**
		 * @param $moduleName
		 * @param $viewId
		 *
		 * @return ViewAdvancedFilterGroup[]|null
		 * @throws Exception
		 */
		private function fetchAdvancedFilterGroupsByViewId ($moduleName, $viewId) {
			$result = $this->adb->pquery ('SELECT * FROM vtiger_kvadvfilter_grouping WHERE kanbanviewid=? ORDER BY groupid', array ($viewId));
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
		 * @return ViewAdvancedFilter[]|null
		 * @throws Exception
		 */
		private function fetchAdvancedFiltersByGroupId ($moduleName, $viewId, $groupId) {
			$result = $this->adb->pquery ('SELECT * FROM vtiger_kvadvfilter WHERE kanbanviewid=? AND groupid=? ORDER BY columnindex', array ($viewId, $groupId));
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
		 * @return null|ViewStandardFilter
		 * @throws Exception
		 */
		private function fetchStandardFilterByViewId ($moduleName, $viewId) {
			$result = $this->adb->pquery ('SELECT * FROM vtiger_kvstdfilter WHERE kanbanviewid=?', array ($viewId));
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
		 * @param string $moduleName
		 * @param string $vtigerColumnName
		 *
		 * @return array
		 * @throws Exception
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
		 * @param KanbanView $kanbanView
		 * @param string $moduleTableName
		 *
		 * @throws Exception
		 * @throws ViewAdvancedFilterException
		 * @throws ViewAdvancedFilterGroupException
		 */
		private function saveAdvancedFilterGroups ($kanbanView, $moduleTableName = null) {
			$viewId = $kanbanView->getIdKanban ();
			$groups = $kanbanView->getAdvancedFilterGroups ();
			if (empty ($groups)) {
				$this->adb->pquery ('DELETE FROM vtiger_kvadvfilter WHERE kanbanviewid=?', array ($viewId));
				$this->adb->pquery ('DELETE FROM vtiger_kvadvfilter_grouping WHERE kanbanviewid=?', array ($viewId));
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
				$result              = $this->adb->pquery ('SELECT * FROM vtiger_kvadvfilter_grouping WHERE kanbanviewid=? AND groupid=?', array ($viewId, $sequence));
				if ($this->adb->num_rows ($result) == 0) {
					$this->adb->pquery (
						'INSERT INTO vtiger_kvadvfilter_grouping (groupid, kanbanviewid, group_condition, condition_expression) VALUES (?, ?, ?, ?)',
						array ($sequence, $viewId, $group->getOperator (), $conditionExpression)
					);
				} else {
					$this->adb->pquery (
						'UPDATE vtiger_kvadvfilter_grouping SET group_condition=?, condition_expression=? WHERE kanbanviewid=? AND groupid=?',
						array ($group->getOperator (), $conditionExpression, $viewId, $sequence)
					);
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
				$this->saveAdvancedFilters ($group, $kanbanView->getModuleName (), $moduleTableName);
				$processedSequences [] = $sequence;
			}

			if (!empty ($processedSequences)) {
				$questionMarks = str_repeat ('?, ', (count ($processedSequences) - 1)) . '?';
				$this->adb->pquery ("DELETE FROM vtiger_kvadvfilter_grouping WHERE kanbanviewid=? AND groupid NOT IN ({$questionMarks})", array_merge (array ($viewId), $processedSequences));
			}
		}

		/**
		 * @param ViewAdvancedFilterGroup $group
		 * @param string $moduleName
		 * @param string $moduleTableName
		 *
		 * @throws Exception
		 * @throws ViewAdvancedFilterException
		 */
		private function saveAdvancedFilters ($group, $moduleName, $moduleTableName = null) {
			$groupId = $group->getSequence ();
			$filters = $group->getFilters ();
			$viewId  = $group->getViewId ();
			if (empty ($filters)) {
				$this->adb->pquery ('DELETE FROM vtiger_kvadvfilter WHERE kanbanviewid=? AND groupid=?', array ($viewId, $groupId));
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
				$result     = $this->adb->pquery ('SELECT * FROM vtiger_kvadvfilter WHERE kanbanviewid=? AND columnindex=? AND groupid=?', array ($viewId, $sequence, $groupId));
				if ($this->adb->num_rows ($result) == 0) {
					$this->adb->pquery (
						'INSERT INTO vtiger_kvadvfilter (kanbanviewid, columnindex, columnname, comparator, value, groupid, column_condition) VALUES (?, ?, ?, ?, ?, ?, ?)',
						array ($viewId, $sequence, $columnName, $filter->getComparator (), $filter->getValue (), $groupId, $filter->getOperator ())
					);
				} else {
					$this->adb->pquery (
						'UPDATE vtiger_kvadvfilter SET columnname=?, comparator=?, value=?, column_condition=? WHERE kanbanviewid=? AND columnindex=? AND groupid=?',
						array ($columnName, $filter->getComparator (), $filter->getValue (), $filter->getOperator (), $viewId, $sequence, $groupId)
					);
				}
				DatabaseUtils::closeResult ($result);
				$result                = null;
				$processedSequences [] = $sequence;
			}

			$questionMarks = str_repeat ('?, ', (count ($processedSequences) - 1)) . '?';
			$this->adb->pquery ("DELETE FROM vtiger_kvadvfilter WHERE kanbanviewid=? AND groupid=? AND columnindex NOT IN ({$questionMarks})", array_merge (array ($viewId, $groupId), $processedSequences));
		}

		/**
		 * @param KanbanView $kanbanView
		 * @param null $moduleTableName
		 *
		 * @throws Exception
		 * @throws ViewStandardFilterException
		 */
		private function saveStandardFilter ($kanbanView, $moduleTableName = null) {
			$viewId         = $kanbanView->getIdKanban ();
			$standardFilter = $kanbanView->getStandardFilter ();
			if (empty ($standardFilter)) {
				$this->adb->pquery ('DELETE FROM vtiger_kvstdfilter WHERE kanbanviewid=?', array ($viewId));
				return;
			}

			$standardFilterEndDate    = $standardFilter->getEndDate ();
			$standardFilterModuleName = $standardFilter->getModuleName ();
			$standardFilterStartDate  = $standardFilter->getStartDate ();
			$standardFilterTableName  = $standardFilter->getTableName ();

			$standardFilter->setViewId ($viewId);
			if (empty ($standardFilterModuleName)) {
				$standardFilter->setModuleName ($kanbanView->getModuleName ());
			}
			if (empty ($standardFilterTableName)) {
				$standardFilter->setTableName ($moduleTableName);
			}
			$this->validateStandardFilter ($standardFilter);

			$label      = str_replace (' ', '_', $standardFilter->getLabel ());
			$columnName = "{$standardFilter->getTableName ()}:{$standardFilter->getColumnName ()}:{$standardFilter->getFieldName ()}:{$standardFilter->getModuleName ()}_{$label}";
			$endDate    = !empty ($standardFilterEndDate) ? $standardFilterEndDate->format ('Y-m-d') : null;
			$startDate  = !empty ($standardFilterStartDate) ? $standardFilterStartDate->format ('Y-m-d') : null;
			$result     = $this->adb->pquery ('SELECT * FROM vtiger_kvstdfilter WHERE kanbanviewid=?', array ($viewId));
			if ($this->adb->num_rows ($result) == 0) {
				$this->adb->pquery (
					'INSERT INTO vtiger_kvstdfilter (kanbanviewid, columnname, stdfilter, startdate, enddate) VALUES (?, ?, ?, ?, ?)',
					array ($viewId, $columnName, $standardFilter->getPeriod (), $startDate, $endDate)
				);
			} else {
				$this->adb->pquery (
					'UPDATE vtiger_kvstdfilter SET columnname=?, stdfilter=?, startdate=?, enddate=? WHERE kanbanviewid=?',
					array ($columnName, $standardFilter->getPeriod (), $startDate, $endDate, $viewId)
				);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
		}

		/**
		 * @param ViewColorFilter|ViewAdvancedFilter $object
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
		 * @param ViewAdvancedFilter$filter
		 *
		 * @throws Exception
		 * @throws ViewAdvancedFilterException
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
		 * @param ViewStandardFilter $filter
		 *
		 * @throws Exception
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
			$this->adb->pquery ('DELETE FROM vtiger_kanbanfield_card_config WHERE kanbanviewid IN (SELECT kanbanviewid FROM vtiger_kanbanviews WHERE modulename=? AND fieldname=?)', array ($field->getModuleName (), $field->getName ()));
			$this->adb->pquery ('DELETE FROM vtiger_kanbanfield_config WHERE kanbanviewid IN (SELECT kanbanviewid FROM vtiger_kanbanviews WHERE modulename=? AND fieldname=?)', array ($field->getModuleName (), $field->getName ()));
			$this->adb->pquery ('DELETE FROM vtiger_kvadvfilter WHERE kanbanviewid IN (SELECT kanbanviewid FROM vtiger_kanbanviews WHERE modulename=? AND fieldname=?)', array ($field->getModuleName (), $field->getName ()));
			$this->adb->pquery ('DELETE FROM vtiger_kvadvfilter_grouping WHERE kanbanviewid IN (SELECT kanbanviewid FROM vtiger_kanbanviews WHERE modulename=? AND fieldname=?)', array ($field->getModuleName (), $field->getName ()));
			$this->adb->pquery ('DELETE FROM vtiger_kvstdfilter WHERE kanbanviewid IN (SELECT kanbanviewid FROM vtiger_kanbanviews WHERE modulename=? AND fieldname=?)', array ($field->getModuleName (), $field->getName ()));
			$this->adb->pquery ('DELETE FROM vtiger_kanbanviews WHERE modulename=? AND fieldname=?', array ($field->getModuleName (), $field->getName ()));
		}

		/**
		 * @param KanbanView $kanban
		 */
		public function deleteKanbanView ($kanban) {
			if ((empty ($kanban)) || (!($kanban instanceof KanbanView))) {
				return;
			}
			$this->adb->pquery ('DELETE FROM vtiger_kanbanfield_config WHERE kanbanviewid=?', array ($kanban->getIdKanban ()));
			$this->adb->pquery ('DELETE FROM vtiger_kanbanfield_card_config WHERE kanbanviewid=?', array ($kanban->getIdKanban ()));
			$this->adb->pquery ('DELETE FROM vtiger_kvadvfilter WHERE kanbanviewid=?', array ($kanban->getIdKanban ()));
			$this->adb->pquery ('DELETE FROM vtiger_kvadvfilter_grouping WHERE kanbanviewid=?', array ($kanban->getIdKanban ()));
			$this->adb->pquery ('DELETE FROM vtiger_kvstdfilter WHERE kanbanviewid=?', array ($kanban->getIdKanban ()));
			$this->adb->pquery ('DELETE FROM vtiger_kanbanviews WHERE kanbanviewid=?', array ($kanban->getIdKanban ()));
		}

		/**
		 * @param string $moduleName
		 * @param boolean $ignoreLock
		 */
		public function deleteKanbanViews ($moduleName, $ignoreLock = true) {
			if (empty ($moduleName)) {
				return;
			}

			if (!$ignoreLock) {
				$whereClause = 'AND locked=0';
			} else {
				$whereClause = '';
			}

			$this->adb->pquery (
				"DELETE FROM vtiger_kanbanfield_card_config WHERE kanbanviewid IN (SELECT kanbanviewid FROM vtiger_kanbanviews WHERE modulename=? {$whereClause})",
				array ($moduleName)
			);
			$this->adb->pquery (
				"DELETE FROM vtiger_kanbanfield_config WHERE kanbanviewid IN (SELECT kanbanviewid FROM vtiger_kanbanviews WHERE modulename=? {$whereClause})",
				array ($moduleName)
			);
			$this->adb->pquery (
				"DELETE FROM vtiger_kanbanviews WHERE modulename=? {$whereClause}",
				array ($moduleName)
			);
		}

		/**
		 * @param integer $viewId
		 *
		 * @return KanbanCardConfig[]|null
		 * @throws Exception
		 */
		public function fetchKanbanCard ($viewId) {
			if (empty($viewId)) {
				return null;
			}
			$result = $this->adb->pquery (
				'SELECT
						kc.*, f.fieldname, f.fieldlabel
					  FROM vtiger_kanbanfield_card_config kc
					  INNER JOIN vtiger_field f ON f.fieldid = kc.fieldid
					  WHERE kanbanviewid=?',
				array ($viewId)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$kanbanCards = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$kanbanCards[] = KanbanCardConfig::getInstance ()
						->setIdCardField ($row ['fieldcardid'])
						->setFieldName ($row ['fieldname'])
						->setFieldLabel ($row ['fieldlabel'])
						->setIdKanban ($row ['kanbanviewid'])
						->setIdField ($row ['fieldid']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return !empty ($kanbanCards) ? $kanbanCards : null;
		}

		/**
		 * @param integer $viewId
		 * @param string $fieldName
		 * @param string|null $moduleName  Requerido cuando $fieldName es un pipeline
		 *
		 * @return KanbanFieldConfig[]|null
		 * @throws Exception
		 */
		public function fetchKanbanField ($viewId, $fieldName, $moduleName = null) {
			if (empty($viewId)) {
				return null;
			}
			// Soporte pipeline: los valores viven en vtiger_pipelines.values (JSON) y se indexan por pickfieldid
			$pipelineValues = null;
			if (!empty ($moduleName) && !empty ($fieldName)) {
				$pipelineResult = $this->adb->pquery (
					'SELECT `values` FROM vtiger_pipelines WHERE modulename=? AND fieldname=?',
					array ($moduleName, $fieldName)
				);
				if ($pipelineResult && $this->adb->num_rows ($pipelineResult) > 0) {
					$pipelineRow = $this->adb->fetchByAssoc ($pipelineResult, -1, false);
					$decoded     = !empty ($pipelineRow ['values']) ? json_decode ($pipelineRow ['values'], true) : null;
					if (is_array ($decoded) && !empty ($decoded)) {
						$pipelineValues = array_values ($decoded);
					}
				}
				DatabaseUtils::closeResult ($pipelineResult);
				$pipelineResult = null;
			}

			if ($pipelineValues !== null) {
				$result = $this->adb->pquery (
					'SELECT kf.* FROM vtiger_kanbanfield_config kf WHERE kanbanviewid=? ORDER BY kf.kanbanfieldconfigid',
					array ($viewId)
				);
			} else {
				$result = $this->adb->pquery (
					"SELECT
						kf.*,
						ff.{$fieldName} AS label
					FROM
						vtiger_kanbanfield_config kf
						INNER JOIN vtiger_{$fieldName} ff ON ff.{$fieldName}id = kf.pickfieldid
					WHERE
						kanbanviewid=?
					ORDER BY
						kf.kanbanfieldconfigid",
					array ($viewId)
				);
			}
			if ($this->adb->num_rows ($result) > 0) {
				$kanbanField = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					// Para pipelines el label se resuelve desde el JSON indexado por pickfieldid
					if ($pipelineValues !== null) {
						$idx           = intval ($row ['pickfieldid']);
						$row ['label'] = isset ($pipelineValues [$idx]) ? $pipelineValues [$idx] : null;
					}
					$kanbanField[] = KanbanFieldConfig::getInstance ()
						->setIdKanbanFieldConfig ($row ['kanbanfieldconfigid'])
						->setBackgroundColor ($row ['backgroundcolor'])
						->setFieldName ($row ['label'])
						->setIdKanban ($row ['kanbanviewid'])
						->setIdPickField ($row ['pickfieldid'])
						->setFieldNameOperation ($row ['fieldname'])
						->setOperation ($row ['operation']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return !empty ($kanbanField) ? $kanbanField : null;
		}

		/**
		 * @param string $moduleName
		 *
		 * @return KanbanView[]|null
		 * @throws Exception
		 */
		public function fetchKanbanViews ($moduleName) {
			$result = $this->adb->pquery (
				'SELECT
					kv.*,
					t.name modulelabel,
					t.tablabel titlemodulelabel,
					f.fieldname,
					f.fieldlabel titlefieldlabel
				FROM
					vtiger_kanbanviews kv
					INNER JOIN vtiger_tab t ON t.tabid = kv.moduletabid
					INNER JOIN vtiger_field f ON f.fieldid = kv.fieldid AND f.tabid = kv.moduletabid
				WHERE
					kv.modulename=?',
				array ($moduleName)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$kanbanViews = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$kanbanViews[] = KanbanView::getInstance ()
						->setIdKanban ($row ['kanbanviewid'])
						->setAdvancedFilterGroups ($this->fetchAdvancedFilterGroupsByViewId ($row ['modulename'], $row ['kanbanviewid']))
						->setCodeApplication ($row ['aplicationcode'])
						->setCreationDate ($row ['datecreation'])
						->setFieldName ($row ['fieldname'])
						->setKanbanCard ($this->fetchKanbanCard ($row ['kanbanviewid']))
						->setKanbanField ($this->fetchKanbanField ($row ['kanbanviewid'], $row ['fieldname'], $row ['modulename']))
						->setKanbanName ($row ['kanbaname'])
						->setIdField ($row ['fieldid'])
						->setInListView ($row ['isvisibleinlist'])
						->setDefaultView ($row ['isdefaultview'])
						->setLabel ($row ['label'])
						->setLocked ($row ['locked'])
						->setIdTabModule ($row ['moduletabid'])
						->setModuleName ($row ['modulename'])
						->setStandardFilter ($this->fetchStandardFilterByViewId ($row ['modulename'], $row ['kanbanviewid']));
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return !empty ($kanbanViews) ? $kanbanViews : null;
		}

		/**
		 * @param integer|string $searchView
		 *
		 * @return boolean|KanbanView
		 * @throws Exception
		 */
		public function fetchKanbanView ($searchView) {
			if (empty($searchView)) {
				return null;
			}
			$searchWhere = (is_numeric ($searchView)) ? 'kv.kanbanviewid=?' : 'kv.kanbaname=?';
			$result      = $this->adb->pquery (
				"SELECT kv.*,
						t.name modulelabel,
						t.tablabel titlemodulelabel,
						f.fieldname,
						f.fieldlabel titlefieldlabel
					  FROM vtiger_kanbanviews kv
					  INNER JOIN vtiger_tab t ON t.tabid = kv.moduletabid
					  INNER JOIN vtiger_field f ON f.fieldid = kv.fieldid AND f.tabid = kv.moduletabid
					  WHERE  {$searchWhere}",
				array ($searchView)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$row        = $this->adb->fetchByAssoc ($result, -1, false);
				$kanbanView = KanbanView::getInstance ()
					->setIdKanban ($row ['kanbanviewid'])
					->setAdvancedFilterGroups ($this->fetchAdvancedFilterGroupsByViewId ($row ['modulename'], $row ['kanbanviewid']))
					->setCodeApplication ($row ['aplicationcode'])
					->setCreationDate ($row ['datecreation'])
					->setFieldName ($row ['fieldname'])
					->setKanbanCard ($this->fetchKanbanCard ($row ['kanbanviewid']))
					->setKanbanField ($this->fetchKanbanField ($row ['kanbanviewid'], $row ['fieldname'], $row ['modulename']))
					->setIdField ($row ['fieldid'])
					->setInListView ($row ['isvisibleinlist'])
					->setDefaultView ($row ['isdefaultview'])
					->setKanbanName ($row ['kanbaname'])
					->setLabel ($row ['label'])
					->setLocked ($row ['locked'])
					->setIdTabModule ($row ['moduletabid'])
					->setModuleName ($row ['modulename'])
					->setStandardFilter ($this->fetchStandardFilterByViewId ($row ['modulename'], $row ['kanbanviewid']));
			} else {
				$kanbanView = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $kanbanView;
		}

		/**
		 * @param integer $viewId
		 *
		 * @return KanbanView|null
		 * @throws Exception
		 */
		public function fetchKanbanViewById ($viewId) {
			if (empty ($viewId)) {
				return null;
			}

			$result = $this->adb->pquery (
				'SELECT 
						kv.*, 
						t.name modulelabel, 
						t.tablabel titlemodulelabel,  
						f.fieldname, 
						f.fieldlabel titlefieldlabel 
					  FROM vtiger_kanbanviews kv
					  INNER JOIN vtiger_tab t ON t.tabid = kv.moduletabid
					  INNER JOIN vtiger_field f ON f.fieldid = kv.fieldid AND f.tabid = kv.moduletabid
					  WHERE kv.kanbanviewid = ?',
				array ($viewId)
			);

			if ($this->adb->num_rows ($result) > 0) {
				$row        = $this->adb->fetchByAssoc ($result, -1, false);
				$kanbanView = KanbanView::getInstance ()
					->setIdKanban ($row ['kanbanviewid'])
					->setAdvancedFilterGroups ($this->fetchAdvancedFilterGroupsByViewId ($row ['modulename'], $viewId))
					->setCodeApplication ($row ['aplicationcode'])
					->setCreationDate ($row ['datecreation'])
					->setFieldName ($row ['fieldname'])
					->setKanbanCard ($this->fetchKanbanCard ($row ['kanbanviewid']))
					->setKanbanField ($this->fetchKanbanField ($row ['kanbanviewid'], $row ['fieldname'], $row ['modulename']))
					->setIdField ($row ['fieldid'])
					->setInListView ($row ['isvisibleinlist'])
					->setDefaultView ($row ['isdefaultview'])
					->setKanbanName ($row ['kanbaname'])
					->setLabel ($row ['label'])
					->setLocked ($row ['locked'])
					->setIdTabModule ($row ['moduletabid'])
					->setModuleName ($row ['modulename'])
					->setStandardFilter ($this->fetchStandardFilterByViewId ($row ['modulename'], $viewId));
			} else {
				$kanbanView = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $kanbanView;
		}

		/**
		 * @param $kanbanView
		 *
		 * @return KanbanView|null
		 * @throws Exception
		 * @throws KanbanViewException
		 */
		public function saveKanbanCard ($kanbanView) {
			if ((empty ($kanbanView)) || (!($kanbanView instanceof KanbanView))) {
				return null;
			}
			$viewId = $kanbanView->getIdKanban ();
			$cards  = $kanbanView->getKanbanCards ();
			if (empty ($cards)) {
				return $kanbanView;
			}
			foreach ($cards as $cardIndex => $kanbanCard) {
				if ((empty ($kanbanCard)) || (!($kanbanCard instanceof KanbanCardConfig))) {
					continue;
				}
				try {
					$kanbanCard->validate ();
				} catch (Exception $ex) {
					throw $ex;
				}
				if (!empty ($kanbanCard->getFieldName ())) {
					$result = $this->adb->pquery (
						'SELECT
							f.fieldid
						FROM
							vtiger_field f
							INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=?
						WHERE
							f.fieldname=?',
						array ($kanbanView->getModuleName (), $kanbanCard->getFieldName ())
					);
					if ($this->adb->num_rows ($result) > 0) {
						$row = $this->adb->fetchByAssoc ($result, -1, false);
						$kanbanCard->setIdField ($row ['fieldid']);
					} else {
						$row = null;
					}
					DatabaseUtils::closeResult ($result);
					$result = null;
					if ($row === null) {
						continue;
					}

					if (empty ($kanbanCard->getIdCardField ())) {
						$this->adb->pquery ('DELETE FROM vtiger_kanbanfield_card_config WHERE kanbanviewid = ? ', array ($viewId));
						$viewId = null;
						$this->adb->pquery (
							'INSERT INTO vtiger_kanbanfield_card_config (kanbanviewid, fieldId, fieldname) VALUES (?, ?, ?)',
							array ($kanbanView->getIdKanban (), $kanbanCard->getIdField (), $kanbanCard->getFieldName ())
						);
						$lastId = $this->adb->getLastInsertID ();
						$kanbanCard->setIdCardField ($lastId);
					} else {
						$this->adb->pquery (
							'UPDATE vtiger_kanbanfield_card_config SET fieldId=? WHERE kanbanviewid=? AND fieldcardid=?',
							array ($kanbanCard->getIdField (), $kanbanView->getIdKanban (), $kanbanCard->getIdCardField ())
						);
					}
				}
			}
			return $kanbanView;
		}

		/**
		 * @param KanbanView $kanbanView
		 *
		 * @return null|KanbanView
		 * @throws Exception
		 * @throws KanbanViewException
		 */
		public function saveKanbanField ($kanbanView) {
			if ((empty ($kanbanView)) || (!($kanbanView instanceof KanbanView))) {
				return null;
			}
			$viewId = $kanbanView->getIdKanban ();
			$fields = $kanbanView->getKanbanField ();
			// Detectar si el campo agrupador es un pipeline; en ese caso pickfieldid ya viene
			// del formulario como el indice del array JSON (0-based) y no hay tabla vtiger_{fieldname}
			$isPipeline = false;
			if (!empty ($kanbanView->getModuleName ()) && !empty ($kanbanView->getFieldName ())) {
				$pipelineCheck = $this->adb->pquery (
					'SELECT 1 FROM vtiger_pipelines WHERE modulename=? AND fieldname=? LIMIT 1',
					array ($kanbanView->getModuleName (), $kanbanView->getFieldName ())
				);
				$isPipeline = ($pipelineCheck && $this->adb->num_rows ($pipelineCheck) > 0);
				DatabaseUtils::closeResult ($pipelineCheck);
				$pipelineCheck = null;
			}
			if (empty ($fields)) {
				return $kanbanView;
			}
			foreach ($fields as $fieldIndex => $kanbanField) {
				if ((empty ($kanbanField)) || (!($kanbanField instanceof KanbanFieldConfig))) {
					continue;
				}
				try {
					$kanbanField->validate ();
				} catch (Exception $ex) {
					throw $ex;
				}
				if (!empty ($kanbanField->getFieldName ()) || $isPipeline) {
					if ($isPipeline) {
						// Para pipelines el idPickField ya es el indice valido del JSON; no se consulta tabla picklist
						$row = true;
					} else {
						$result = $this->adb->query (
							"SELECT
								{$kanbanView->getFieldName()}id AS pickfieldid
							FROM
								vtiger_{$kanbanView->getFieldName()}
							WHERE
								{$kanbanView->getFieldName()}='{$kanbanField->getFieldName ()}'"
						);
						if ($this->adb->num_rows ($result) > 0) {
							$row = $this->adb->fetchByAssoc ($result, -1, false);
							$kanbanField->setIdPickField ($row ['pickfieldid']);
						} else {
							$row = null;
						}
						DatabaseUtils::closeResult ($result);
						$result = null;
					}
					if ($row === null) {
						continue;
					}

					if (empty ($kanbanField->getIdKanbanFieldConfig ())) {
						$this->adb->pquery ('DELETE FROM vtiger_kanbanfield_config WHERE kanbanviewid = ? ', array ($viewId));
						$viewId = null;
						$this->adb->pquery (
							'INSERT INTO vtiger_kanbanfield_config (kanbanviewid, backgroundcolor, pickfieldid, fieldname, operation) VALUES (?, ?, ?, ?, ?)',
							array ($kanbanView->getIdKanban (), $kanbanField->getBackgroundColor (), $kanbanField->getIdPickField (), $kanbanField->getFieldNameOperation (), $kanbanField->getOperation())
						);
						$lastId = $this->adb->getLastInsertID ();
						$kanbanField->setIdKanbanFieldConfig ($lastId);
					} else {
						$this->adb->pquery (
							'UPDATE vtiger_kanbanfield_config SET backgroundcolor=?, pickfieldid=?, fieldname=?, operation=? WHERE kanbanviewid=? AND kanbanfieldconfigid=?',
							array ($kanbanField->getBackgroundColor (), $kanbanField->getIdPickField (), $kanbanField->getFieldNameOperation (), $kanbanField->getOperation(), $kanbanView->getIdKanban (), $kanbanField->getIdKanbanFieldConfig ())
						);
					}
				}
			}
			return $kanbanView;
		}

		/**
		 * @param KanbanView $kanbanView
		 * @param boolean $ignoreLock
		 *
		 * @return KanbanView|null
		 * @throws KanbanViewException
		 * @throws Exception
		 */
		public function saveKanbanView ($kanbanView, $ignoreLock = true) {
			if ((empty ($kanbanView)) || (!($kanbanView instanceof KanbanView))) {
				return null;
			}
			$kanbanView->validate ();

			$result = $this->adb->pquery (
				'SELECT
					f.fieldid,
					f.tabid
				FROM
					vtiger_field f
					INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=?
				WHERE
					f.fieldname=?',
				array ($kanbanView->getModuleName (), $kanbanView->getFieldName ())
			);
			if ($this->adb->num_rows ($result) > 0) {
				$row      = $this->adb->fetchByAssoc ($result, -1, false);
				$fieldId  = $row ['fieldid'];
				$moduleId = $row ['tabid'];
			} else {
				$fieldId  = null;
				$moduleId = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			if ((empty ($fieldId)) || (empty ($moduleId))) {
				return null;
			}
			$kanbanView->setIdField ($fieldId)->setIdTabModule ($moduleId);

			$this->adb->startTransaction ();
			try {
				if (empty ($kanbanView->getIdKanban ())) {
					$this->adb->pquery (
						'INSERT INTO vtiger_kanbanviews (kanbaname, label, modulename, moduletabid, fieldid, fieldname, aplicationcode, isvisibleinlist, isdefaultview, locked) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
						array ($kanbanView->getKanbaName (), $kanbanView->getLabel (), $kanbanView->getModuleName (), $moduleId, $fieldId, $kanbanView->getFieldName (), $kanbanView->getCodeApplication (), $kanbanView->getInListView (), $kanbanView->getIsDefaultView (), $kanbanView->isLocked ())
					);
					$kanbanView->setIdKanban ($this->adb->getLastInsertID ());
				} else {
					$whereClause = !$ignoreLock ? 'AND locked=0' : '';
					$this->adb->pquery (
						"UPDATE vtiger_kanbanviews SET kanbaname=?, label=?, modulename=?, moduletabid=?, fieldid=?, fieldname=?, aplicationcode=?, isvisibleinlist=?, isdefaultview=?, locked=? WHERE kanbanviewid=? {$whereClause}",
						array ($kanbanView->getKanbaName (), $kanbanView->getLabel (), $kanbanView->getModuleName (), $moduleId, $fieldId, $kanbanView->getFieldName (), $kanbanView->getCodeApplication (), $kanbanView->getInListView (), $kanbanView->getIsDefaultView (), $kanbanView->isLocked (), $kanbanView->getIdKanban ())
					);
				}

				$this->saveKanbanField ($kanbanView);
				$this->saveKanbanCard ($kanbanView);
				$this->saveAdvancedFilterGroups($kanbanView);
				$this->saveStandardFilter($kanbanView);
			} catch (Exception $ex) {
				// Marcar la transaccion como fallida para que CompleteTrans() haga ROLLBACK (no COMMIT)
				if (isset ($this->adb->database) && method_exists ($this->adb->database, 'FailTrans')) {
					$this->adb->database->FailTrans ();
				}
				$this->adb->completeTransaction ();
				throw $ex;
			}
			$this->adb->completeTransaction ();
			return $kanbanView;
		}

		/**
		 * @param string $moduleName
		 * @param KanbanView[] $views
		 * @param boolean $ignoreLock
		 *
		 * @throws Exception
		 * @throws KanbanViewException
		 */
		public function saveKanbanViews ($moduleName, $views, $ignoreLock = true) {
			if (empty ($moduleName)) {
				return;
			} else if (empty ($views)) {
				$this->deleteKanbanViews ($moduleName, $ignoreLock);
				return;
			}

			$processedViewIds = array ();
			foreach ($views as $kanbanView) {
				$kanbanView->setModuleName ($moduleName);
				$this->saveKanbanView ($kanbanView, $ignoreLock);
				$processedViewIds [] = $kanbanView->getIdKanban ();
			}

			if (!$ignoreLock) {
				$whereClause = 'AND locked=0';
			} else {
				$whereClause = '';
			}
			$questionMarks = str_repeat ('?, ', (count ($processedViewIds) - 1)) . '?';
			$this->adb->pquery (
				"DELETE FROM vtiger_kanbanfield_card_config WHERE kanbanviewid IN (SELECT kanbanviewid FROM vtiger_kanbanviews WHERE modulename=? AND kanbanviewid NOT IN ({$questionMarks}) {$whereClause})",
				array_merge (array ($moduleName), $processedViewIds)
			);
			$this->adb->pquery (
				"DELETE FROM vtiger_kanbanfield_config WHERE kanbanviewid IN (SELECT kanbanviewid FROM vtiger_kanbanviews WHERE modulename=? AND kanbanviewid NOT IN ({$questionMarks}) {$whereClause})",
				array_merge (array ($moduleName), $processedViewIds)
			);
			$this->adb->pquery (
				"DELETE FROM vtiger_kanbanviews WHERE modulename=? AND kanbanviewid NOT IN ({$questionMarks}) {$whereClause}",
				array_merge (array ($moduleName), $processedViewIds)
			);
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return KanbanViewManager
		 */
		public static function getInstance (PearDatabase $adb) {
			if (self::$INSTANCE === null) {
				self::$INSTANCE = array ();
			}
			if (!isset (self::$INSTANCE [ $adb->dbName ])) {
				self::$INSTANCE[ $adb->dbName ] = new self ($adb);
			}
			return self::$INSTANCE [ $adb->dbName ];
		}

	}
