<?php
	require_once ('include/platzilla/Objects/Report.php');
	require_once ('include/platzilla/Objects/ReportFolder.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');

	class ReportsManager {
		/** @var ReportsManager[]|null */
		private static $INSTANCES = null;

		/** @var PearDatabase */
		private $adb;

		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
		}

		/**
		 * @param Report $report
		 * @param string $moduleTableName
		 *
		 * @throws Exception
		 */
		private function createReport ($report, $moduleTableName) {
			$reportId = $this->adb->getUniqueID ('vtiger_selectquery');
			try {
				$applicationCodes = $report->getApplicationCodes ();
				$this->adb->startTransaction ();
				$this->adb->pquery ('INSERT INTO vtiger_selectquery (queryid, startindex, numofobjects) VALUES (?, ?, ?)', array ($reportId, 0, 0));
				$this->adb->pquery (
					'INSERT INTO vtiger_report (reportid, folderid, reportname, description, reporttype, queryid, state, customizable, category, owner, sharingtype, applicationcodes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
					array ($reportId, $report->getFolder ()->getId (), $report->getName (), $report->getDescription (), $report->getType (), $reportId, $report->getStatus (), 1, 1, $report->getOwner (), $report->getVisibility (), !empty ($applicationCodes) ? json_encode ($applicationCodes) : null)
				);
				$report->setId ($reportId);
				$this->saveModules ($report);
				$this->saveColumns ($report, $moduleTableName);
				$this->saveSortColumns ($report, $moduleTableName);
				$this->saveTotalColumns ($report, $moduleTableName);
				$this->saveStandardFilter ($report, $moduleTableName);
				$this->saveAdvancedFilterGroups ($report, $moduleTableName);
				$this->saveVisibility ($report);
				$this->saveSchedule ($report);
				$this->adb->completeTransaction ();
			} catch (Exception $e) {
				$this->deleteReport ($report);
				throw $e;
			}
		}

		/**
		 * @param integer $reportId
		 */
		private function deleteReportById ($reportId) {
			if (empty ($reportId)) {
				return;
			}

			$this->adb->startTransaction ();
			$this->adb->pquery ('DELETE FROM vtiger_scheduled_reports WHERE reportid=?', array ($reportId));
			$this->adb->pquery ('DELETE FROM vtiger_reportsharing WHERE reportid=?', array ($reportId));
			$this->adb->pquery ('DELETE FROM vtiger_relcriteria WHERE queryid=?', array ($reportId));
			$this->adb->pquery ('DELETE FROM vtiger_relcriteria_grouping WHERE queryid=?', array ($reportId));
			$this->adb->pquery ('DELETE FROM vtiger_reportdatefilter WHERE datefilterid=?', array ($reportId));
			$this->adb->pquery ('DELETE FROM vtiger_reportsummary WHERE reportsummaryid=?', array ($reportId));
			$this->adb->pquery ('DELETE FROM vtiger_reportsortcol WHERE reportid=?', array ($reportId));
			$this->adb->pquery ('DELETE FROM vtiger_reportgroupbycolumn WHERE reportid=?', array ($reportId));
			$this->adb->pquery ('DELETE FROM vtiger_selectcolumn WHERE queryid=?', array ($reportId));
			$this->adb->pquery ('DELETE FROM vtiger_reportmodules WHERE reportmodulesid=?', array ($reportId));
			$this->adb->pquery ('DELETE FROM vtiger_report WHERE reportid=?', array ($reportId));
			$this->adb->pquery ('DELETE FROM vtiger_selectquery WHERE queryid=?', array ($reportId));
			$this->adb->completeTransaction ();
		}

		/**
		 * @param integer $reportId
		 *
		 * @return ReportAdvancedFilterGroup[]|null
		 */
		private function fetchAdvancedFilterGroupsByReportId ($reportId) {
			$result = $this->adb->pquery ('SELECT * FROM vtiger_relcriteria_grouping WHERE queryid=? ORDER BY groupid', array ($reportId));
			if ($this->adb->num_rows ($result) > 0) {
				$groups = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$sequence  = intval ($row ['groupid']);
					$groups [] = ReportAdvancedFilterGroup::getInstance ()
						->setFilters ($this->fetchAdvancedFiltersByGroupId ($reportId, $sequence))
						->setReportId ($reportId)
						->setSequence ($sequence)
						->setOperator ($row ['group_condition']);
				}
			} else {
				$groups = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $groups;
		}

		/**
		 * @param integer $reportId
		 * @param integer $groupId
		 *
		 * @return ReportAdvancedFilter[]|null
		 */
		private function fetchAdvancedFiltersByGroupId ($reportId, $groupId) {
			$result = $this->adb->pquery ('SELECT * FROM vtiger_relcriteria WHERE queryid=? AND groupid=? ORDER BY columnindex', array ($reportId, $groupId));
			if ($this->adb->num_rows ($result) > 0) {
				$filters = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$dummy          = explode (':', $row ['columnname']);
					$labelAndModule = $this->parseVtigerReportColumnName ($dummy [2], $dummy [3]);

					$columnName = $dummy [1];
					$dataType   = $dummy [4];
					$fieldLabel = $labelAndModule ['fieldlabel'];
					$fieldName  = $dummy [3];
					$moduleName = $labelAndModule ['modulename'];
					$tableName  = $dummy [0];
					$filters [] = ReportAdvancedFilter::getInstance ()
						->setColumnName ($columnName)
						->setComparator ($row ['comparator'])
						->setDataType ($dataType)
						->setFieldName ($fieldName)
						->setGroupId ($groupId)
						->setLabel ($fieldLabel)
						->setModuleName ($moduleName)
						->setOperator ($row ['column_condition'])
						->setReportId ($reportId)
						->setSequence (intval ($row ['columnindex']))
						->setTableName ($tableName)
						->setValue ($row ['value']);
				}
			} else {
				$filters = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $filters;
		}

		/**
		 * @param integer $reportId
		 *
		 * @return ReportColumn[]|null
		 * @throws Exception
		 */
		private function fetchColumnsByReportId ($reportId) {
			$result = $this->adb->pquery ('SELECT * FROM vtiger_selectcolumn WHERE queryid=? ORDER BY columnindex', array ($reportId));
			if ($this->adb->num_rows ($result) > 0) {
				$columns = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$dummy     = explode (':', $row ['columnname']);
					$tableName = $dummy [0];
					if ($tableName == 'vtiger_subfields_values') {
						$labelAndModule = explode ('@', $dummy [2]);
						$moduleName     = $labelAndModule [0] . '@vtiger';
						$dummyData      = explode ('_', $labelAndModule [1], 2);
						$fieldLabel     = $dummyData [1];
					} else {
						$labelAndModule = $this->parseVtigerReportColumnName($dummy [2], $dummy [3]);
						$fieldLabel     = $labelAndModule ['fieldlabel'];
						$moduleName     = $labelAndModule ['modulename'];
					}
					$columnName = $dummy [1];
					$dataType   = $dummy [4];
					$fieldName  = $dummy [3];

					$columns [] = ReportColumn::getInstance ()
						->setColumnName ($columnName)
						->setDataType ($dataType)
						->setFieldName ($fieldName)
						->setLabel ($fieldLabel)
						->setModuleName ($moduleName)
						->setReportId ($reportId)
						->setSequence (intval ($row ['columnindex']))
						->setTableName ($tableName);
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
		 * @return Report[]
		 */
		private function fetchDeletedReports ($moduleName) {
			if (empty ($moduleName)) {
				return array ();
			}

			$reports = array ();
			$result  = $this->adb->pquery ('SELECT * FROM vtiger_deletedelements WHERE elementtype=? AND modulename=?', array ('report', $moduleName));
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					/** @var Report $report */
					$report = unserialize ($row ['serializedobject']);
					$report->setDeleted (true);
					$reports [] = $report;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $reports;
		}

		/**
		 * @param integer $reportId
		 *
		 * @return array|null
		 */
		private function fetchReportData ($reportId) {
			if (!empty ($reportId)) {
				$result = $this->adb->pquery ('SELECT * FROM vtiger_report WHERE reportid=?', array ($reportId));
				if (($result) && ($this->adb->num_rows ($result) > 0)) {
					$row = $this->adb->fetchByAssoc ($result, -1, false);
				} else {
					$row = null;
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
			} else {
				$row = null;
			}
			return $row;
		}

		/**
		 * @param integer $reportId
		 *
		 * @return ReportSchedule|null
		 */
		private function fetchScheduleByReportId ($reportId) {
			$result = $this->adb->pquery ('SELECT * FROM vtiger_scheduled_reports WHERE reportid=?', array ($reportId));
			if ($this->adb->num_rows ($result) > 0) {
				$row = $this->adb->fetchByAssoc ($result, -1, false);
				if (!empty ($row ['recipients'])) {
					$recipients = json_decode ($row ['recipients'], true);
					$groups     = $recipients ['groups'];
					$roles      = $recipients ['roles'];
					$rs         = $recipients ['rs'];
					$users      = $recipients ['users'];
				} else {
					$groups = null;
					$roles  = null;
					$rs     = null;
					$users  = null;
				}

				if (!empty ($row ['schedule'])) {
					$schedule  = json_decode ($row ['schedule'], true);
					$frequency = $schedule ['scheduletype'];
					if (in_array ($frequency, array (ReportScheduleInterface::FREQUENCY_MONTHLY, ReportScheduleInterface::FREQUENCY_YEARLY))) {
						$day = $schedule ['date'];
					} else if (in_array ($frequency, array (ReportScheduleInterface::FREQUENCY_BIWEEKLY, ReportScheduleInterface::FREQUENCY_WEEKLY))) {
						$day = $schedule ['day'];
					} else {
						$day = null;
					}
					$month = $schedule ['month'];
					$time  = $schedule ['time'];
				} else {
					$frequency = null;
					$day       = null;
					$month     = null;
					$time      = null;
				}

				$reportSchedule = ReportSchedule::getInstance ($frequency, $time, $day, $month)
					->setFormat ($row ['format'])
					->setGroups ($groups)
					->setReportId ($reportId)
					->setRoles ($roles)
					->setRolesAndSubordinates ($rs)
					->setUsers ($users);
			} else {
				$reportSchedule = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $reportSchedule;
		}

		/**
		 * @param integer $reportId
		 *
		 * @return ReportSharingEntity[]|null
		 */
		private function fetchSharingEntitiesByReportId ($reportId) {
			$result = $this->adb->pquery ('SELECT * FROM vtiger_reportsharing WHERE reportid=?', array ($reportId));
			if ($this->adb->num_rows ($result) > 0) {
				$entities = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$entities [] = ReportSharingEntity::getInstance ()
						->setId (intval ($row ['shareid']))
						->setType ($row ['setype']);
				}
			} else {
				$entities = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $entities;
		}

		/**
		 * @param integer $reportId
		 *
		 * @return ReportColumn[]|null
		 */
		private function fetchSortColumnsByReportId ($reportId) {
			$result = $this->adb->pquery ('SELECT * FROM vtiger_reportsortcol WHERE reportid=? ORDER BY sortcolid', array ($reportId));
			if ($this->adb->num_rows ($result) > 0) {
				$columns = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					if ($row ['columnname'] == 'none') {
						continue;
					}
					$dummy          = explode (':', $row ['columnname']);
					$labelAndModule = $this->parseVtigerReportColumnName ($dummy [2], $dummy [3]);

					$columnName = $dummy [1];
					$dataType   = $dummy [4];
					$fieldLabel = $labelAndModule ['fieldlabel'];
					$fieldName  = $dummy [3];
					$moduleName = $labelAndModule ['modulename'];
					$tableName  = $dummy [0];
					$columns [] = ReportColumn::getInstance ()
						->setColumnName ($columnName)
						->setDataType ($dataType)
						->setFieldName ($fieldName)
						->setLabel (str_replace ('_', ' ', $fieldLabel))
						->setModuleName ($moduleName)
						->setReportId ($reportId)
						->setSequence (intval ($row ['sortcolid']))
						->setSortOrder ($row ['sortorder'])
						->setTableName ($tableName);
				}
			} else {
				$columns = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return !empty ($columns) ? $columns : null;
		}

		/**
		 * @param string $moduleName
		 * @param array $relatedModuleNames
		 * @param integer $reportId
		 *
		 * @return ReportStandardFilter|null
		 */
		private function fetchStandardFilterByReportId ($moduleName, $relatedModuleNames, $reportId) {
			$result = $this->adb->pquery ('SELECT * FROM vtiger_reportdatefilter WHERE datefilterid=?', array ($reportId));
			if ($this->adb->num_rows ($result) > 0) {
				$row    = $this->adb->fetchByAssoc ($result, -1, false);
				$dummy  = $this->parseVtigerStandardFilterColumnName ($moduleName, $relatedModuleNames, $row ['datecolumnname']);
				$filter = ReportStandardFilter::getInstance ()
					->setColumnName ($dummy ['columnname'])
					->setEndDate ($row ['enddate'])
					->setFieldName ($dummy ['fieldname'])
					->setLabel ($dummy ['fieldlabel'])
					->setModuleName ($dummy ['modulename'])
					->setPeriod ($row ['datefilter'])
					->setReportId ($reportId)
					->setStartDate ($row ['startdate'])
					->setTableName ($dummy ['tablename']);
			} else {
				$filter = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $filter;
		}

		/**
		 * @param integer $reportId
		 *
		 * @return ReportColumn[]|null
		 */
		private function fetchTotalColumnsByReportId ($reportId) {
			$result = $this->adb->pquery ('SELECT * FROM vtiger_reportsummary WHERE reportsummaryid=? ORDER BY summarytype', array ($reportId));
			if ($this->adb->num_rows ($result) > 0) {
				$columns = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$columnName = explode (':', $row ['columnname']);
					if (isset ($columnName [3])) {
						$dummy     = explode ('_', $columnName [3]);
						$operation = $dummy [ (count ($dummy) - 1) ];
						array_pop ($dummy);
						$label = join (' ', $dummy);
					} else {
						$operation = null;
						$label     = null;
					}

					$columns [] = ReportColumn::getInstance ()
						->setColumnName ($columnName [2])
						->setDataType (null)
						->setFieldName ($columnName [2])
						->setLabel ($label)
						->setModuleName (null)
						->setReportId ($reportId)
						->setSequence (intval ($row ['summarytype']))
						->setTableName ($columnName [1])
						->setTotalsOperation ($operation);
				}
			} else {
				$columns = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $columns;
		}

		/**
		 * @param ReportAdvancedFilterGroup $group
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
		 * El string demencial que pusieron los Apus (thank you!, come again!) en vtiger_selectcolumn.columnname tiene el formato
		 * tablenameALoApu:columnnameALoApu:ModuleName_ListViewDisplayLabel:fieldname:DataType
		 * donde a ListViewDisplayLabel le reemplazan los espacios por underscores '_'
		 *
		 * @param string $vtigerLabelAndModule
		 * @param string $fieldName
		 *
		 * @return array
		 */
		private function parseVtigerReportColumnName ($vtigerLabelAndModule, $fieldName) {
			$result = $this->adb->pquery (
				"SELECT
					f.*,
					t.name AS modulename,
					(CASE REPLACE(CONCAT(t.name, '_', f.fieldlabel), ' ', '_') WHEN ? THEN 1 ELSE 0 END) AS fullmatch
				FROM
					vtiger_field f
					INNER JOIN vtiger_tab t ON t.tabid=f.tabid
				WHERE
					REPLACE(CONCAT(t.name, '_', f.fieldlabel), ' ', '_')=? OR
					(? LIKE CONCAT(t.name, '_%') AND f.fieldname=?)
				ORDER BY
					fullmatch ASC,
					CHAR_LENGTH(t.name) DESC",
				array ($vtigerLabelAndModule, $vtigerLabelAndModule, $vtigerLabelAndModule, $fieldName)
			);
			if ($this->adb->num_rows ($result) == 0) {
				$fieldLabel = null;
				$moduleName = null;
			} else {
				$row        = $this->adb->fetchByAssoc ($result, -1, false);
				$fieldLabel = $row ['fieldlabel'];
				$moduleName = $row ['modulename'];
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			return array (
				'fieldlabel' => $fieldLabel,
				'modulename' => $moduleName,
			);
		}

		/**
		 * El string demencial que pusieron los Apus (thank you!, come again!) en vtiger_reportdatefilter.datecolumnname tiene el formato
		 * tablename:columnname:fieldname:ModuleName_ListViewDisplayLabel
		 * donde a ListViewDisplayLabel le reemplazan los espacios por underscores '_'
		 *
		 * @param string $moduleName
		 * @param array $relatedModuleNames
		 * @param string $vtigerColumnName
		 *
		 * @return array
		 */
		private function parseVtigerStandardFilterColumnName ($moduleName, $relatedModuleNames, $vtigerColumnName) {
			$dummy = explode (':', $vtigerColumnName);
			if (isset ($dummy [3])) {
				$keys = array ("{$dummy [0]}%:{$dummy [1]}:{$dummy [2]}:{$moduleName}_%");
				if (!empty ($relatedModuleNames)) {
					foreach ($relatedModuleNames as $relatedModuleName) {
						$keys [] = "{$dummy [0]}%:{$dummy [1]}:{$dummy [2]}:{$relatedModuleName}_%";
					}
				}

				$n            = count ($keys);
				$whereClauses = array ();
				for ($i = 0; $i < $n; $i++) {
					$whereClauses [] = "CONCAT(f.tablename, ':', f.columnname, ':', f.fieldname, ':', t.name, '_') LIKE ?";
				}
				$whereClauses = join (' OR ', $whereClauses);

				$result = $this->adb->pquery (
					"SELECT
						f.*,
						t.name AS modulename
					FROM
						vtiger_field f
						INNER JOIN vtiger_tab t ON t.tabid=f.tabid
					WHERE
						{$whereClauses}",
					$keys
				);
				if ($this->adb->num_rows ($result) == 0) {
					$columnName = $dummy [1];
					$fieldLabel = null;
					$fieldName  = $dummy [2];
					$moduleName = null;
					$tableName  = $dummy [0];
				} else {
					$row        = $this->adb->fetchByAssoc ($result, -1, false);
					$columnName = $row ['columnname'];
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
				'datatype'   => null,
				'fieldlabel' => $fieldLabel,
				'fieldname'  => $fieldName,
				'modulename' => $moduleName,
				'tablename'  => $tableName,
			);
		}

		/**
		 * @param Report $report
		 * @param string $moduleTableName
		 *
		 * @throws ReportAdvancedFilterException
		 * @throws ReportAdvancedFilterGroupException
		 */
		private function saveAdvancedFilterGroups ($report, $moduleTableName) {
			$reportId = $report->getId ();
			$this->adb->pquery ('DELETE FROM vtiger_relcriteria WHERE queryid=?', array ($reportId));
			$this->adb->pquery ('DELETE FROM vtiger_relcriteria_grouping WHERE queryid=?', array ($reportId));

			$groups = $report->getAdvancedFilterGroups ();
			if (empty ($groups)) {
				return;
			}

			foreach ($groups as $group) {
				$filters = $group->getFilters ();
				if (empty ($filters)) {
					continue;
				}

				$group->setReportId ($reportId);

				$sequence            = $group->getSequence ();
				$conditionExpression = $this->getAdvancedFilterGroupConditionExpression ($group);
				$this->adb->pquery (
					'INSERT INTO vtiger_relcriteria_grouping (groupid, queryid, group_condition, condition_expression) VALUES (?, ?, ?, ?)',
					array ($sequence, $reportId, $group->getOperator (), $conditionExpression)
				);
				$this->saveAdvancedFilters ($group, $report->getModuleName (), $moduleTableName);
			}
		}

		/**
		 * @param ReportAdvancedFilterGroup $group
		 * @param string $moduleName
		 * @param string $moduleTableName
		 *
		 * @throws ReportAdvancedFilterException
		 */
		private function saveAdvancedFilters ($group, $moduleName, $moduleTableName = null) {
			$filters = $group->getFilters ();
			if (empty ($filters)) {
				return;
			}

			$reportId = $group->getReportId ();
			$groupId  = $group->getSequence ();
			foreach ($filters as $filter) {
				$filterModuleName = $filter->getModuleName ();
				$filterTableName  = $filter->getTableName ();
				$filter->setReportId ($reportId);
				if (empty ($filterModuleName)) {
					$filter->setModuleName ($moduleName);
				}
				if (empty ($filterTableName)) {
					$filter->setTableName ($moduleTableName);
				}
				if ($filter->getGroupId () === null) {
					$filter->setGroupId ($groupId);
				}

				$sequence   = $filter->getSequence ();
				$label      = str_replace (' ', '_', $filter->getLabel ());
				$columnName = "{$filter->getTableName ()}:{$filter->getColumnName ()}:{$filter->getModuleName ()}_{$label}:{$filter->getFieldName ()}:{$filter->getDataType ()}";
				$this->adb->pquery (
					'INSERT INTO vtiger_relcriteria (queryid, columnindex, columnname, comparator, value, groupid, column_condition) VALUES (?, ?, ?, ?, ?, ?, ?)',
					array ($reportId, $sequence, $columnName, $filter->getComparator (), $filter->getValue (), $groupId, $filter->getOperator ())
				);
			}
		}

		/**
		 * @param Report $report
		 * @param string $moduleTableName
		 */
		private function saveColumns ($report, $moduleTableName) {
			$reportId = $report->getId ();
			$this->adb->pquery ('DELETE FROM vtiger_selectcolumn WHERE queryid=?', array ($reportId));

			$columns = $report->getColumns ();
			foreach ($columns as $index => $column) {
				$columnModuleName = $column->getModuleName ();
				$columnTableName  = $column->getTableName ();
				$column->setReportId ($reportId);
				if (empty ($columnModuleName)) {
					$column->setModuleName ($report->getModuleName ());
				}
				if (empty ($columnTableName)) {
					$column->setTableName ($moduleTableName);
				}

				$label      = str_replace (' ', '_', $column->getLabel ());
				$columnName = "{$column->getTableName ()}:{$column->getColumnName ()}:{$column->getModuleName ()}_{$label}:{$column->getFieldName ()}:{$column->getDataType ()}";
				$this->adb->pquery (
					'INSERT INTO vtiger_selectcolumn (queryid, columnindex, columnname) VALUES (?, ?, ?)',
					array ($reportId, $index, $columnName)
				);
			}
		}

		/**
		 * @param Report $report
		 */
		private function saveModules ($report) {
			$reportId = $report->getId ();
			$this->adb->pquery ('DELETE FROM vtiger_reportmodules WHERE reportmodulesid=?', array ($reportId));
			$relatedModuleNames = $report->getRelatedModuleNames ();
			$this->adb->pquery (
				'INSERT INTO vtiger_reportmodules (reportmodulesid, primarymodule, secondarymodules) VALUES (?, ?, ?)',
				array ($reportId, $report->getModuleName (), !empty ($relatedModuleNames) ? join (':', $relatedModuleNames) : null)
			);
		}

		/**
		 * @param Report $report
		 */
		private function saveSchedule ($report) {
			$reportId = $report->getId ();
			$this->adb->pquery ('DELETE FROM vtiger_scheduled_reports WHERE reportid=?', array ($reportId));

			$schedule = $report->getSchedule ();
			if (empty ($schedule)) {
				return;
			}

			$dummy = array (
				'date'         => self::convertNullToEmptyString ($schedule->getDay ()),
				'day'          => self::convertNullToEmptyString ($schedule->getWeekDay ()),
				'month'        => self::convertNullToEmptyString ($schedule->getMonth ()),
				'scheduletype' => self::convertNullToEmptyString ($schedule->getFrequency ()),
				'time'         => self::convertNullToEmptyString ($schedule->getTime ()),
			);

			$recipients = array (
				'groups' => self::convertNullToEmptyArray ($schedule->getGroups ()),
				'roles'  => self::convertNullToEmptyArray ($schedule->getRoles ()),
				'rs'     => self::convertNullToEmptyArray ($schedule->getRolesAndSubordinates ()),
				'users'  => self::convertNullToEmptyArray ($schedule->getUsers ()),
			);

			$this->adb->pquery (
				'INSERT INTO vtiger_scheduled_reports (reportid, recipients, schedule, format, next_trigger_time) VALUES (?, ?, ?, ?, ?)',
				array ($reportId, json_encode ($recipients), json_encode ($dummy), $schedule->getFormat (), date ('Y-m-d H:i:s'))
			);
		}

		/**
		 * @param Report $report
		 * @param string $moduleTableName
		 */
		private function saveSortColumns ($report, $moduleTableName) {
			$reportId = $report->getId ();
			$this->adb->pquery ('DELETE FROM vtiger_reportsortcol WHERE reportid=?', array ($reportId));

			$type          = $report->getType ();
			$reportColumns = $type == ReportInterface::TYPE_SUMMARY ? $report->getSortColumns () : null;
			if (!empty ($reportColumns)) {
				$columns = $reportColumns;
			} else {
				$columns = array ();
			}

			foreach ($columns as $index => $column) {
				$columnModuleName = $column->getModuleName ();
				$columnTableName  = $column->getTableName ();
				$column->setReportId ($reportId);
				if (empty ($columnModuleName)) {
					$column->setModuleName ($report->getModuleName ());
				}
				if (empty ($columnTableName)) {
					$column->setTableName ($moduleTableName);
				}

				$label      = str_replace (' ', '_', $column->getLabel ());
				$columnName = "{$column->getTableName ()}:{$column->getColumnName ()}:{$column->getModuleName ()}_{$label}:{$column->getFieldName ()}:{$column->getDataType ()}";
				$this->adb->pquery (
					'INSERT INTO vtiger_reportsortcol (reportid, sortcolid, columnname, sortorder) VALUES (?, ?, ?, ?)',
					array ($reportId, ($index + 1), $columnName, $column->getSortOrder ())
				);
				if ($column->getDataType () == FieldInterface::DATA_TYPE_DATE) {
					$this->adb->pquery (
						'INSERT INTO vtiger_reportgroupbycolumn (reportid, sortid, sortcolname, dategroupbycriteria) VALUES (?, ?, ?, ?)',
						array ($reportId, ($index + 1), $columnName, 'None')
					);
				}
			}

			$n = count ($columns);
			for ($index = $n; $index < 3; $index++) {
				$this->adb->pquery (
					'INSERT INTO vtiger_reportsortcol (reportid, sortcolid, columnname, sortorder) VALUES (?, ?, ?, ?)',
					array ($reportId, ($index + 1), 'none', ReportColumnInterface::SORT_ORDER_ASCENDING)
				);
			}
		}

		/**
		 * @param Report $report
		 * @param string $moduleTableName
		 *
		 * @throws ReportStandardFilterException
		 */
		private function saveStandardFilter ($report, $moduleTableName) {
			$reportId = $report->getId ();
			$this->adb->pquery ('DELETE FROM vtiger_reportdatefilter WHERE datefilterid=?', array ($reportId));

			$standardFilter = $report->getStandardFilter ();
			if (empty ($standardFilter)) {
				return;
			}

			$standardFilterEndDate    = $standardFilter->getEndDate ();
			$standardFilterModuleName = $standardFilter->getModuleName ();
			$standardFilterStartDate  = $standardFilter->getStartDate ();
			$standardFilterTableName  = $standardFilter->getTableName ();
			$standardFilter->setReportId ($reportId);
			if (empty ($standardFilterModuleName)) {
				$standardFilter->setModuleName ($report->getModuleName ());
			}
			if (empty ($standardFilterTableName)) {
				$standardFilter->setTableName ($moduleTableName);
			}

			$label      = str_replace (' ', '_', $standardFilter->getLabel ());
			$columnName = "{$standardFilter->getTableName ()}:{$standardFilter->getColumnName ()}:{$standardFilter->getFieldName ()}:{$standardFilter->getModuleName ()}_{$label}";
			$endDate    = !empty ($standardFilterEndDate) ? $standardFilterEndDate->format ('Y-m-d') : null;
			$startDate  = !empty ($standardFilterStartDate) ? $standardFilterStartDate->format ('Y-m-d') : null;
			$this->adb->pquery (
				'INSERT INTO vtiger_reportdatefilter (datefilterid, datecolumnname, datefilter, startdate, enddate) VALUES (?, ?, ?, ?, ?)',
				array ($reportId, $columnName, $standardFilter->getPeriod (), $startDate, $endDate)
			);
		}

		/**
		 * @param Report $report
		 * @param string $moduleTableName
		 */
		private function saveTotalColumns ($report, $moduleTableName) {
			$reportId = $report->getId ();
			$this->adb->pquery ('DELETE FROM vtiger_reportsummary WHERE reportsummaryid=?', array ($reportId));

			$columns = $report->getTotalColumns ();
			if (empty ($columns)) {
				return;
			}

			foreach ($columns as $index => $column) {
				$columnModuleName = $column->getModuleName ();
				$columnTableName  = $column->getTableName ();
				$column->setReportId ($reportId);
				if (empty ($columnModuleName)) {
					$column->setModuleName ($report->getModuleName ());
				}
				if (empty ($columnTableName)) {
					$column->setTableName ($moduleTableName);
				}

				$operation = $column->getTotalsOperation ();
				switch ($operation) {
					case ReportColumnInterface::TOTALS_OPERATION_AVERAGE:
						$dummy = 3;
						break;
					case ReportColumnInterface::TOTALS_OPERATION_MAXIMUM:
						$dummy = 5;
						break;
					case ReportColumnInterface::TOTALS_OPERATION_MINIMUM:
						$dummy = 4;
						break;
					case ReportColumnInterface::TOTALS_OPERATION_SUM:
						$dummy = 2;
						break;
					default:
						$dummy = 1;
						break;
				}
				$columnName = "cb:{$column->getTableName ()}:{$column->getColumnName ()}:{$column->getLabel ()}_{$column->getTotalsOperation ()}:{$dummy}";
				$this->adb->pquery (
					'INSERT INTO vtiger_reportsummary (reportsummaryid, summarytype, columnname) VALUES (?, ?, ?)',
					array ($reportId, ($index + 1), $columnName)
				);
			}
		}

		/**
		 * @param Report $report
		 */
		private function saveVisibility ($report) {
			$reportId = $report->getId ();
			$this->adb->pquery ('DELETE FROM vtiger_reportsharing WHERE reportid=?', array ($reportId));

			$entities   = $report->getShareWith ();
			$visibility = $report->getVisibility ();
			if (($visibility != ReportInterface::VISIBILITY_SHARED) || (empty ($entities))) {
				return;
			}

			foreach ($entities as $entity) {
				$this->adb->pquery (
					'INSERT INTO vtiger_reportsharing (reportid, shareid, setype) VALUES (?, ?, ?)',
					array ($reportId, $entity->getId (), $entity->getType ())
				);
			}
		}

		/**
		 * @param Report $report
		 * @param string $moduleTableName
		 *
		 * @throws Exception
		 */
		private function updateReport ($report, $moduleTableName) {
			$this->adb->startTransaction ();
			$reportId         = $report->getId ();
			$applicationCodes = $report->getApplicationCodes ();
			$this->adb->pquery (
				'UPDATE vtiger_report SET folderid=?, reportname=?, description=?, reporttype=?, state=?, owner=?, sharingtype=?, applicationcodes=? WHERE reportid=?',
				array ($report->getFolder ()->getId (), $report->getName (), $report->getDescription (), $report->getType (), $report->getStatus (), $report->getOwner (), $report->getVisibility (), !empty ($applicationCodes) ? json_encode ($applicationCodes) : null, $reportId)
			);
			$this->saveModules ($report);
			$this->saveColumns ($report, $moduleTableName);
			$this->saveSortColumns ($report, $moduleTableName);
			$this->saveTotalColumns ($report, $moduleTableName);
			$this->saveStandardFilter ($report, $moduleTableName);
			$this->saveAdvancedFilterGroups ($report, $moduleTableName);
			$this->saveVisibility ($report);
			$this->saveSchedule ($report);
			$this->adb->completeTransaction ();
		}

		/**
		 * @param Report $report
		 *
		 * @throws ReportException
		 * @throws ReportColumnException
		 * @throws ReportStandardFilterException
		 */
		private function validate ($report) {
			if ((empty ($report)) || (!($report instanceof Report))) {
				throw new ReportException (ReportException::ERROR_REPORT_EMPTY);
			}

			$report->validate ();

			$moduleName = $report->getModuleName ();
			if (empty ($moduleName)) {
				throw new ReportException (ReportException::ERROR_REPORT_EMPTY_MODULE_NAME);
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_tab WHERE name=?', array ($moduleName));
			if ($this->adb->num_rows ($result) == 0) {
				$e = new ReportException (ReportException::ERROR_REPORT_INVALID_MODULE_NAME);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			if (isset ($e)) {
				throw $e;
			}
		}

		/**
		 * @param ReportFolder $folder
		 *
		 * @throws ReportException
		 */
		private function validateFolder ($folder) {
			if ((empty ($folder)) || (!($folder instanceof ReportFolder))) {
				throw new ReportException (ReportException::ERROR_REPORT_FOLDER_EMPTY);
			}

			$folder->validate ();

			$folderId = $folder->getId ();
			if (!empty ($folderId)) {
				$result = $this->adb->pquery ('SELECT * FROM vtiger_reportfolder WHERE folderid=?', array ($folderId));
				if ($this->adb->num_rows ($result) == 0) {
					DatabaseUtils::closeResult ($result);
					$result = null;
					throw new ReportException (ReportException::ERROR_REPORT_FOLDER_INVALID_FOLDER_ID);
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_reportfolder WHERE foldername=?', array ($folder->getName ()));
			if ($this->adb->num_rows ($result) == 0) {
				DatabaseUtils::closeResult ($result);
				$result = null;
				return;
			}

			$row = $this->adb->fetchByAssoc ($result, -1, false);
			DatabaseUtils::closeResult ($result);
			$result = null;
			if ((empty ($folderId)) || ($row ['folderid'] != $folderId)) {
				throw new ReportException (ReportException::ERROR_REPORT_FOLDER_DUPLICATE_NAME);
			}
		}

		/**
		 * @param mixed $argument
		 *
		 * @return mixed|array
		 */
		private static function convertNullToEmptyArray ($argument) {
			return isset ($argument) ? $argument : array ();
		}

		/**
		 * @param mixed $argument
		 *
		 * @return mixed|array
		 */
		private static function convertNullToEmptyString ($argument) {
			return isset ($argument) ? $argument : '';
		}

		/**
		 * @param Field $field
		 */
		public function deleteFieldFromReports ($field) {
			$this->adb->pquery (
				'DELETE FROM vtiger_relcriteria WHERE columnname LIKE ?',
				array ("{$field->getTableName ()}:{$field->getColumnName ()}:{$field->getModuleName ()}_%:{$field->getName ()}:%")
			);
			$this->adb->pquery (
				'DELETE FROM vtiger_reportdatefilter WHERE datecolumnname LIKE ?',
				array ("{$field->getTableName ()}:{$field->getColumnName ()}:{$field->getName ()}:{$field->getModuleName ()}_%")
			);
			$this->adb->pquery (
				'DELETE FROM vtiger_reportsortcol WHERE columnname LIKE ?',
				array ("{$field->getTableName ()}:{$field->getColumnName ()}:{$field->getModuleName ()}_%:{$field->getName ()}:%")
			);
			$this->adb->pquery (
				'DELETE FROM vtiger_selectcolumn WHERE columnname LIKE ?',
				array ("{$field->getTableName ()}:{$field->getColumnName ()}:{$field->getModuleName ()}_%:{$field->getName ()}:%")
			);

			$this->adb->pquery (
				'DELETE FROM vtiger_reportsummary WHERE columnname LIKE ?',
				array ("cb:{$field->getTableName ()}:{$field->getColumnName ()}:%")
			);
		}

		/**
		 * @param ReportFolder $folder
		 */
		public function deleteFolder ($folder) {
			if ((empty ($folder)) || (!($folder instanceof ReportFolder))) {
				return;
			}

			$folderId = $folder->getId ();
			if (empty ($folderId)) {
				return;
			}

			$this->adb->startTransaction ();
			$this->adb->pquery ('DELETE FROM vtiger_relcriteria_grouping WHERE queryid IN (SELECT reportid FROM vtiger_report WHERE folderid IN (SELECT folderid FROM vtiger_reportfolder WHERE folderid=?))', array ($folderId));
			$this->adb->pquery ('DELETE FROM vtiger_relcriteria WHERE queryid IN (SELECT reportid FROM vtiger_report WHERE folderid IN (SELECT folderid FROM vtiger_reportfolder WHERE folderid=?))', array ($folderId));
			$this->adb->pquery ('DELETE FROM vtiger_selectcolumn WHERE queryid IN (SELECT reportid FROM vtiger_report WHERE folderid IN (SELECT folderid FROM vtiger_reportfolder WHERE folderid=?))', array ($folderId));
			$this->adb->pquery ('DELETE FROM vtiger_scheduled_reports WHERE reportid IN (SELECT reportid FROM vtiger_report WHERE folderid IN (SELECT folderid FROM vtiger_reportfolder WHERE folderid=?))', array ($folderId));
			$this->adb->pquery ('DELETE FROM vtiger_reportsharing WHERE reportid IN (SELECT reportid FROM vtiger_report WHERE folderid IN (SELECT folderid FROM vtiger_reportfolder WHERE folderid=?))', array ($folderId));
			$this->adb->pquery ('DELETE FROM vtiger_reportdatefilter WHERE datefilterid IN (SELECT reportid FROM vtiger_report WHERE folderid IN (SELECT folderid FROM vtiger_reportfolder WHERE folderid=?))', array ($folderId));
			$this->adb->pquery ('DELETE FROM vtiger_reportsummary WHERE reportsummaryid IN (SELECT reportid FROM vtiger_report WHERE folderid IN (SELECT folderid FROM vtiger_reportfolder WHERE folderid=?))', array ($folderId));
			$this->adb->pquery ('DELETE FROM vtiger_reportgroupbycolumn WHERE reportid IN (SELECT reportid FROM vtiger_report WHERE folderid IN (SELECT folderid FROM vtiger_reportfolder WHERE folderid=?))', array ($folderId));
			$this->adb->pquery ('DELETE FROM vtiger_reportsortcol WHERE reportid IN (SELECT reportid FROM vtiger_report WHERE folderid IN (SELECT folderid FROM vtiger_reportfolder WHERE folderid=?))', array ($folderId));
			$this->adb->pquery ('DELETE FROM vtiger_reportmodules WHERE reportmodulesid IN (SELECT reportid FROM vtiger_report WHERE folderid IN (SELECT folderid FROM vtiger_reportfolder WHERE folderid=?))', array ($folderId));
			$this->adb->pquery ('DELETE FROM vtiger_selectquery WHERE queryid IN (SELECT reportid FROM vtiger_report WHERE folderid IN (SELECT folderid FROM vtiger_reportfolder WHERE folderid=?))', array ($folderId));
			$this->adb->pquery ('DELETE FROM vtiger_report WHERE folderid IN (SELECT folderid FROM vtiger_reportfolder WHERE folderid=?)', array ($folderId));
			$this->adb->pquery ('DELETE FROM vtiger_reportfolder WHERE folderid=?', array ($folderId));
			$this->adb->completeTransaction ();
		}

		/**
		 * @param Module $module
		 */
		public function deleteModuleFromReports ($module) {
			$moduleName = $module->getName ();
			$result     = $this->adb->pquery (
				'SELECT * FROM vtiger_reportmodules WHERE secondarymodules=? OR secondarymodules LIKE ? OR secondarymodules LIKE ? OR secondarymodules LIKE ?',
				array ($moduleName, "{$moduleName}:%", "%:{$moduleName}", "%:{$moduleName}:%")
			);
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$this->deleteReportById ($row ['reportmodulesid']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
		}

		/**
		 * @param Report $report
		 */
		public function deleteReport ($report) {
			if ((empty ($report)) || (!($report instanceof Report))) {
				return;
			}

			$reportId = $report->getId ();
			if (empty ($reportId)) {
				return;
			}

			$moduleName = $report->getModuleName ();
			$identifier = $report->getId ();
			if ((!empty ($moduleName)) && (!empty ($identifier))) {
				$this->adb->pquery ('DELETE FROM vtiger_deletedelements WHERE elementtype=? AND modulename=? AND identifier=?', array ('report', $moduleName, $identifier));
				$this->adb->pquery ('INSERT INTO vtiger_deletedelements (elementtype, modulename, identifier, deletedon, serializedobject) VALUES (?, ?, ?, ?, ?)', array ('report', $moduleName, $identifier, date ('Y-m-d h:i:s'), serialize ($report)));
			}
			$this->deleteReportById ($reportId);
		}

		/**
		 * @param string $moduleName
		 * @param boolean $ignoreLock
		 */
		public function deleteReports ($moduleName, $ignoreLock = true) {
			if (empty ($moduleName)) {
				return;
			}

			if (!$ignoreLock) {
				$joinClause = 'AND r.locked=0';
			} else {
				$joinClause = '';
			}
			$result = $this->adb->pquery ("SELECT rm.* FROM vtiger_reportmodules rm INNER JOIN vtiger_report r ON r.reportid=rm.reportmodulesid {$joinClause} WHERE rm.primarymodule=?", array ($moduleName));
			if ($this->adb->num_rows ($result) > 0) {
				$this->adb->startTransaction ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$reportId = $row ['reportmodulesid'];
					$this->adb->pquery ('DELETE FROM vtiger_scheduled_reports WHERE reportid=?', array ($reportId));
					$this->adb->pquery ('DELETE FROM vtiger_reportsharing WHERE reportid=?', array ($reportId));
					$this->adb->pquery ('DELETE FROM vtiger_relcriteria WHERE queryid=?', array ($reportId));
					$this->adb->pquery ('DELETE FROM vtiger_relcriteria_grouping WHERE queryid=?', array ($reportId));
					$this->adb->pquery ('DELETE FROM vtiger_reportdatefilter WHERE datefilterid=?', array ($reportId));
					$this->adb->pquery ('DELETE FROM vtiger_reportsummary WHERE reportsummaryid=?', array ($reportId));
					$this->adb->pquery ('DELETE FROM vtiger_reportsortcol WHERE reportid=?', array ($reportId));
					$this->adb->pquery ('DELETE FROM vtiger_reportgroupbycolumn WHERE reportid=?', array ($reportId));
					$this->adb->pquery ('DELETE FROM vtiger_selectcolumn WHERE queryid=?', array ($reportId));
					$this->adb->pquery ('DELETE FROM vtiger_report WHERE reportid=?', array ($reportId));
					$this->adb->pquery ('DELETE FROM vtiger_selectquery WHERE queryid=?', array ($reportId));
					$this->adb->pquery ('DELETE FROM vtiger_reportmodules WHERE reportmodulesid=?', array ($reportId));
				}
				$this->adb->completeTransaction ();
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
		}

		/**
		 * @param integer $folderId
		 *
		 * @return ReportFolder|null
		 */
		public function fetchFolderById ($folderId) {
			if (empty ($folderId)) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_reportfolder WHERE folderid=?', array ($folderId));
			if ($this->adb->num_rows ($result) > 0) {
				$row    = $this->adb->fetchByAssoc ($result, -1, false);
				$folder = ReportFolder::getInstance ()
					->setId ($folderId)
					->setDescription ($row ['description'])
					->setName ($row ['foldername'])
					->setProtected ($row ['protected'] == 1)
					->setStatus ($row ['state']);
			} else {
				$folder = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $folder;
		}

		/**
		 * @param string $folderName
		 *
		 * @return ReportFolder|null
		 */
		public function fetchFolderByName ($folderName) {
			if (empty ($folderName)) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_reportfolder WHERE foldername=?', array ($folderName));
			if ($this->adb->num_rows ($result) > 0) {
				$row = $this->adb->fetchByAssoc ($result, -1, false);
				return ReportFolder::getInstance ()
					->setId (intval ($row ['folderid']))
					->setDescription ($row ['description'])
					->setName ($row ['foldername'])
					->setProtected ($row ['protected'] == 1)
					->setStatus ($row ['state']);
			} else {
				$folder = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $folder;
		}

		/**
		 * @param string $moduleName
		 * @param string $reportName
		 *
		 * @return Report|null
		 */
		public function fetchReport ($moduleName, $reportName) {
			if ((empty ($moduleName)) || (empty ($reportName))) {
				return null;
			}

			$result = $this->adb->pquery (
				'SELECT
					r.*,
					rm.primarymodule AS modulename,
					rm.secondarymodules AS relatedmodulenames
				FROM
					vtiger_report r
					INNER JOIN vtiger_reportmodules rm ON rm.reportmodulesid=r.reportid AND rm.primarymodule=?
				WHERE
					r.reportname=?',
				array ($moduleName, $reportName)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$row                = $this->adb->fetchByAssoc ($result, -1, false);
				$reportId           = intval ($row ['reportid']);
				$applicationCodes   = !empty ($row ['applicationcodes']) ? json_decode ($row ['applicationcodes']) : null;
				$relatedModuleNames = !empty ($row ['relatedmodulenames']) ? explode (':', $row ['relatedmodulenames']) : null;
				$report             = Report::getInstance ()
					->setId ($reportId)
					->setAdvancedFilterGroups ($this->fetchAdvancedFilterGroupsByReportId ($reportId))
					->setApplicationCodes ($applicationCodes)
					->setColumns ($this->fetchColumnsByReportId ($reportId))
					->setDescription ($row ['description'])
					->setFolder ($this->fetchFolderById ($row ['folderid']))
					->setLocked ($row ['locked'] == 1)
					->setModuleName ($moduleName)
					->setName ($reportName)
					->setOwner (intval ($row ['owner']))
					->setRelatedModuleNames ($relatedModuleNames)
					->setSchedule ($this->fetchScheduleByReportId ($reportId))
					->setShareWith ($this->fetchSharingEntitiesByReportId ($reportId))
					->setSortColumns ($this->fetchSortColumnsByReportId ($reportId))
					->setStandardFilter ($this->fetchStandardFilterByReportId ($moduleName, $relatedModuleNames, $reportId))
					->setStatus ($row ['state'])
					->setTotalColumns ($this->fetchTotalColumnsByReportId ($reportId))
					->setType ($row ['reporttype'])
					->setVisibility ($row ['sharingtype']);
			} else {
				$report = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $report;
		}

		/**
		 * @param string $moduleName
		 * @param boolean $includeDeleted
		 *
		 * @return Report[]|null
		 */
		public function fetchReports ($moduleName, $includeDeleted = false) {
			if (empty ($moduleName)) {
				return null;
			}

			$result = $this->adb->pquery (
				'SELECT
					r.*,
					rm.primarymodule AS modulename,
					rm.secondarymodules AS relatedmodulenames
				FROM
					vtiger_report r
					INNER JOIN vtiger_reportmodules rm ON rm.reportmodulesid=r.reportid AND rm.primarymodule=?',
				array ($moduleName)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$reports = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$reportId           = intval ($row ['reportid']);
					$applicationCodes   = !empty ($row ['applicationcodes']) ? json_decode ($row ['applicationcodes']) : null;
					$relatedModuleNames = !empty ($row ['relatedmodulenames']) ? explode (':', $row ['relatedmodulenames']) : null;
					$reports []         = Report::getInstance ()
						->setId ($reportId)
						->setAdvancedFilterGroups ($this->fetchAdvancedFilterGroupsByReportId ($reportId))
						->setApplicationCodes ($applicationCodes)
						->setColumns ($this->fetchColumnsByReportId ($reportId))
						->setDescription ($row ['description'])
						->setFolder ($this->fetchFolderById ($row ['folderid']))
						->setLocked ($row ['locked'] == 1)
						->setModuleName ($moduleName)
						->setName ($row ['reportname'])
						->setOwner (intval ($row ['owner']))
						->setRelatedModuleNames ($relatedModuleNames)
						->setSchedule ($this->fetchScheduleByReportId ($reportId))
						->setShareWith ($this->fetchSharingEntitiesByReportId ($reportId))
						->setSortColumns ($this->fetchSortColumnsByReportId ($reportId))
						->setStandardFilter ($this->fetchStandardFilterByReportId ($moduleName, $relatedModuleNames, $reportId))
						->setStatus ($row ['state'])
						->setTotalColumns ($this->fetchTotalColumnsByReportId ($reportId))
						->setType ($row ['reporttype'])
						->setVisibility ($row ['sharingtype']);
				}

				if ($includeDeleted) {
					$deletedReports = $this->fetchDeletedReports ($moduleName);
				} else {
					$deletedReports = array ();
				}
				$reports = array_merge ($reports, $deletedReports);
			} else {
				$reports = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $reports;
		}

		/**
		 * @param ReportFolder $folder
		 *
		 * @return ReportFolder|null
		 * @throws ReportException
		 */
		public function saveFolder ($folder) {
			$this->validateFolder ($folder);

			$folderId = $folder->getId ();
			if (empty ($folderId)) {
				$this->adb->pquery (
					'INSERT INTO vtiger_reportfolder (foldername, description, state, protected) VALUES (?, ?, ?, ?)',
					array ($folder->getName (), $folder->getDescription (), $folder->getStatus (), $folder->isProtected ())
				);
				$folder->setId ($this->adb->getLastInsertID ());
			} else {
				$this->adb->pquery (
					'UPDATE vtiger_reportfolder SET foldername=?, description=?, state=?, protected=? WHERE folderid=?',
					array ($folder->getName (), $folder->getDescription (), $folder->getStatus (), $folder->isProtected (), $folderId)
				);
			}
			return $folder;
		}

		/**
		 * @param Report $report
		 * @param string|null $moduleTableName
		 * @param boolean $ignoreLock
		 *
		 * @return Report
		 * @throws ReportException
		 */
		public function saveReport ($report, $moduleTableName = null, $ignoreLock = true) {
			$folder = $report->getFolder ();
			if ((empty ($folder)) || (!($folder instanceof ReportFolder))) {
				throw new ReportException (ReportException::ERROR_REPORT_FOLDER_EMPTY);
			}

			$folder->validate ();

			$dummy = $this->fetchFolderByName ($folder->getName ());
			if (empty ($dummy)) {
				$folder->setId (null);
				$folder = $this->saveFolder ($folder);
				$report->setFolder ($folder);
			} else if ($folder->getId () != $dummy->getId ()) {
				$report->setFolder ($dummy);
			}

			$this->validate ($report);

			$isDeleted = $report->isDeleted ();
			if ($isDeleted) {
				return $report;
			}

			$reportId = $report->getId ();
			$data     = $this->fetchReportData ($reportId);
			if (!empty ($data)) {
				$isLocked = ($data ['locked'] == 1);
			} else {
				$isLocked = false;
				$reportId = null;
			}

			if (empty ($reportId)) {
				$this->createReport ($report, $moduleTableName);
			} else if (($ignoreLock) || (!$isLocked)) {
				$this->updateReport ($report, $moduleTableName);
			}

			return $report;
		}

		/**
		 * @param string $moduleName
		 * @param Report[]|null $reports
		 * @param string|null $moduleTableName
		 * @param boolean $ignoreLock
		 */
		public function saveReports ($moduleName, $reports, $moduleTableName = null, $ignoreLock = true) {
			if (empty ($moduleName)) {
				return;
			} else if (empty ($reports)) {
				$this->deleteReports ($moduleName, $ignoreLock);
				return;
			}

			$processedReportIds = array ();
			foreach ($reports as $report) {
				$reportModuleName = $report->getModuleName ();
				if (empty ($reportModuleName)) {
					$report->setModuleName ($moduleName);
				}
				$this->saveReport ($report, $moduleTableName, $ignoreLock);
				$processedReportIds [] = $report->getId ();
			}

			if (!$ignoreLock) {
				$joinClause = 'AND r.locked=0';
			} else {
				$joinClause = '';
			}
			$questionMarks = str_repeat ('?, ', (count ($processedReportIds) - 1)) . '?';
			$result        = $this->adb->pquery (
				"SELECT
					rm.*
				FROM
					vtiger_reportmodules rm
					INNER JOIN vtiger_report r ON r.reportid=rm.reportmodulesid {$joinClause}
				WHERE
					rm.primarymodule=? AND
					reportmodulesid NOT IN ({$questionMarks})",
				array_merge (array ($moduleName), $processedReportIds)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$this->adb->startTransaction ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$reportId = $row ['reportmodulesid'];
					$this->adb->pquery ('DELETE FROM vtiger_scheduled_reports WHERE reportid=?', array ($reportId));
					$this->adb->pquery ('DELETE FROM vtiger_reportsharing WHERE reportid=?', array ($reportId));
					$this->adb->pquery ('DELETE FROM vtiger_relcriteria WHERE queryid=?', array ($reportId));
					$this->adb->pquery ('DELETE FROM vtiger_relcriteria_grouping WHERE queryid=?', array ($reportId));
					$this->adb->pquery ('DELETE FROM vtiger_reportdatefilter WHERE datefilterid=?', array ($reportId));
					$this->adb->pquery ('DELETE FROM vtiger_reportsummary WHERE reportsummaryid=?', array ($reportId));
					$this->adb->pquery ('DELETE FROM vtiger_reportsortcol WHERE reportid=?', array ($reportId));
					$this->adb->pquery ('DELETE FROM vtiger_reportgroupbycolumn WHERE reportid=?', array ($reportId));
					$this->adb->pquery ('DELETE FROM vtiger_selectcolumn WHERE queryid=?', array ($reportId));
					$this->adb->pquery ('DELETE FROM vtiger_report WHERE reportid=?', array ($reportId));
					$this->adb->pquery ('DELETE FROM vtiger_selectquery WHERE queryid=?', array ($reportId));
					$this->adb->pquery ('DELETE FROM vtiger_reportmodules WHERE reportmodulesid=?', array ($reportId));
				}
				$this->adb->completeTransaction ();
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
		}

		/**
		 * @param Report $report
		 *
		 * @throws ReportException
		 */
		public function validateReport ($report) {
			$this->validate ($report);
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return ReportsManager
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
