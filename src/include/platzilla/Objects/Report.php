<?php
	require_once ('include/platzilla/Exceptions/ReportException.php');
	require_once ('include/platzilla/Objects/ReportAdvancedFilterGroup.php');
	require_once ('include/platzilla/Objects/ReportColumn.php');
	require_once ('include/platzilla/Objects/ReportFolder.php');
	require_once ('include/platzilla/Objects/ReportInterface.php');
	require_once ('include/platzilla/Objects/ReportSchedule.php');
	require_once ('include/platzilla/Objects/ReportSharingEntity.php');
	require_once ('include/platzilla/Objects/ReportStandardFilter.php');
	require_once ('include/platzilla/Utils/MiscellaneousUtils.php');

	class Report implements ReportInterface {
		/** @var integer */
		private $id;

		/** @var ReportAdvancedFilterGroup[] */
		private $advancedFilterGroups;

		/** @var string[] */
		private $applicationCodes;

		/** @var ReportColumn[] */
		private $columns;

		/** @var boolean */
		private $deleted;

		/** @var string */
		private $description;

		/** @var ReportFolder */
		private $folder;

		/** @var boolean */
		private $locked;

		/** @var string */
		private $moduleName;

		/** @var string */
		private $name;

		/** @var integer */
		private $owner;

		/** @var string[] */
		private $relatedModuleNames;

		/** @var ReportSchedule */
		private $schedule;

		/** @var ReportSharingEntity[] */
		private $shareWith;

		/** @var ReportColumn[] */
		private $sortColumns;

		/** @var ReportStandardFilter */
		private $standardFilter;

		/** @var string */
		private $status;

		/** @var ReportColumn[] */
		private $totalColumns;

		/** @var string */
		private $type;

		/** @var string */
		private $visibility;

		/**
		 * @param ReportAdvancedFilterGroup[]|ReportColumn[]|ReportStandardFilter $elements
		 * @param string $oldModuleName
		 * @param string $newModuleName
		 */
		private function changeModuleName ($elements, $oldModuleName, $newModuleName) {
			if ((empty ($elements)) || ($oldModuleName == $newModuleName)) {
				return;
			}

			if (!is_array ($elements)) {
				$elements = array ($elements);
			}

			$n = count ($elements);
			for ($i = 0; $i < $n; $i++) {
				if (
					(is_object ($elements [ $i ])) &&
					(is_callable (array ($elements [ $i ], 'getModuleName'))) &&
					(is_callable (array ($elements [ $i ], 'setModuleName'))) &&
					($oldModuleName == $elements [ $i ]->getModuleName ())
				) {
					$elements [ $i ]->setModuleName ($newModuleName);
				}
			}
		}

		/**
		 * @param string $oldTableName
		 * @param string $newTableName
		 */
		private function changeAdvancedFiltersTableName ($oldTableName, $newTableName) {
			if (empty ($this->advancedFilterGroups)) {
				return;
			}

			$n = count ($this->advancedFilterGroups);
			for ($i = 0; $i < $n; $i++) {
				$this->advancedFilterGroups [ $i ]->setTableName ($oldTableName, $newTableName);
			}
		}

		/**
		 * @param ReportColumn[]|ReportStandardFilter $elements
		 * @param string $oldTableName
		 * @param string $newTableName
		 */
		private function changeTableName ($elements, $oldTableName, $newTableName) {
			if (empty ($elements)) {
				return;
			}

			if (is_array ($elements)) {
				$n = count ($elements);
				for ($i = 0; $i < $n; $i++) {
					if (
						(is_object ($elements [ $i ])) &&
						(is_callable (array ($elements [ $i ], 'setTableName'))) &&
						(is_callable (array ($elements [ $i ], 'getTableName'))) &&
						($elements [ $i ]->getTableName () == $oldTableName)
					) {
						$elements [ $i ]->setTableName ($newTableName);
					}
				}
			} else if (
				(is_object ($elements)) &&
				(is_callable (array ($elements, 'setTableName'))) &&
				(is_callable (array ($elements, 'getTableName'))) &&
				($elements->getTableName () == $oldTableName)
			) {
				$elements->setTableName ($newTableName);
			}
		}

		/**
		 * @param integer $reportId
		 * @param ReportAdvancedFilterGroup[] $sourceGroups
		 */
		private function copyAdvancedFilterGroups ($reportId, $sourceGroups) {
			$groups = array ();
			foreach ($sourceGroups as $sourceGroup) {
				$found = false;
				foreach ($this->advancedFilterGroups as $targetGroup) {
					if ($sourceGroup->getSequence () != $targetGroup->getSequence ()) {
						continue;
					} else if (!$targetGroup->isEqualTo ($sourceGroup)) {
						$targetGroup->copyValuesFrom ($sourceGroup);
					}
					$groups [] = $targetGroup;
					$found     = true;
					break;
				}
				if (!$found) {
					$groups [] = $sourceGroup->duplicate ($reportId, null);
				}
			}
			$this->advancedFilterGroups = $groups;
		}

		/**
		 * @param Report $report
		 */
		private function copyAdvancedFilterGroupsFrom ($report) {
			$sourceGroups = $report->getAdvancedFilterGroups ();
			if ((empty ($sourceGroups)) && (empty ($this->advancedFilterGroups))) {
				return;
			}

			if (empty ($sourceGroups)) {
				$this->advancedFilterGroups = null;
			} else if (empty ($this->advancedFilterGroups)) {
				$groups = array ();
				foreach ($sourceGroups as $sourceGroup) {
					$groups [] = $sourceGroup->duplicate ($this->id, $sourceGroup->getSequence ());
				}
				$this->advancedFilterGroups = $groups;
			} else {
				$this->copyAdvancedFilterGroups ($report->getId (), $sourceGroups);
			}
		}

		/**
		 * @param integer $reportId
		 * @param ReportColumn[] $sourceColumns
		 */
		private function copyColumns ($reportId, $sourceColumns) {
			$columns = array ();
			foreach ($sourceColumns as $sourceColumn) {
				$found = false;
				foreach ($this->columns as $targetColumn) {
					if ($sourceColumn->getSequence () != $targetColumn->getSequence ()) {
						continue;
					} else if (!$targetColumn->isEqualTo ($sourceColumn)) {
						$targetColumn->copyValuesFrom ($sourceColumn);
					}
					$columns [] = $targetColumn;
					$found      = true;
					break;
				}
				if (!$found) {
					$columns [] = $sourceColumn->duplicate ($reportId);
				}
			}
			$this->columns = $columns;
		}

		/**
		 * @param Report $report
		 */
		private function copyColumnsFrom ($report) {
			$sourceColumns = $report->getColumns ();
			if ((empty ($sourceColumns)) && (empty ($this->columns))) {
				return;
			}

			if (empty ($sourceColumns)) {
				$this->columns = null;
			} else if (empty ($this->columns)) {
				$columns = array ();
				foreach ($sourceColumns as $sourceColumn) {
					$columns [] = $sourceColumn->duplicate ($report->getId ());
				}
				$this->columns = $columns;
			} else {
				$this->copyColumns ($report->getId (), $sourceColumns);
			}
		}

		/**
		 * @param Report $report
		 */
		private function copyFolderFrom ($report) {
			$sourceFolder = $report->getFolder ();
			if ((empty ($sourceFolder)) && (empty ($this->folder))) {
				return;
			}

			if (empty ($sourceFolder)) {
				$this->folder = null;
			} else if (empty ($this->folder)) {
				$this->folder = $sourceFolder->duplicate ($this->id);
			} else {
				$this->folder->copyValuesFrom ($sourceFolder);
			}
		}

		/**
		 * @param Report $report
		 */
		private function copyScheduleFrom ($report) {
			$sourceSchedule = $report->getSchedule ();
			if ((empty ($sourceSchedule)) && (empty ($this->schedule))) {
				return;
			}

			if (empty ($sourceSchedule)) {
				$this->schedule = null;
			} else if (empty ($this->schedule)) {
				$this->schedule = $sourceSchedule->duplicate ($this->id);
			} else {
				$this->schedule->copyValuesFrom ($sourceSchedule);
			}
		}

		/**
		 * @param ReportSharingEntity[] $sourceEntities
		 */
		private function copySharingEntities ($sourceEntities) {
			$entities = array ();
			foreach ($sourceEntities as $sourceEntity) {
				$found = false;
				foreach ($this->shareWith as $targetEntity) {
					if (($sourceEntity->getId () != $targetEntity->getId ()) || ($sourceEntity->getType () != $targetEntity->getType ())) {
						continue;
					} else if (!$targetEntity->isEqualTo ($sourceEntity)) {
						$targetEntity->copyValuesFrom ($sourceEntity);
					}
					$entities [] = $targetEntity;
					$found       = true;
					break;
				}
				if (!$found) {
					$entities [] = $sourceEntity->duplicate ($sourceEntity->getId ());
				}
			}
			$this->shareWith = $entities;
		}

		/**
		 * @param Report $report
		 */
		private function copySharingEntitiesFrom ($report) {
			$sourceEntities = $report->getShareWith ();
			if ((empty ($sourceEntities)) && (empty ($this->shareWith))) {
				return;
			}

			if (empty ($sourceEntities)) {
				$this->shareWith = null;
			} else if (empty ($this->shareWith)) {
				$entities = array ();
				foreach ($sourceEntities as $sourceEntity) {
					$entities [] = $sourceEntity->duplicate ($report->getId ());
				}
				$this->shareWith = $entities;
			} else {
				$this->copySharingEntities ($sourceEntities);
			}
		}

		/**
		 * @param integer $reportId
		 * @param ReportColumn[] $sourceColumns
		 */
		private function copySortColumns ($reportId, $sourceColumns) {
			$columns = array ();
			foreach ($sourceColumns as $sourceColumn) {
				$found = false;
				foreach ($this->sortColumns as $targetColumn) {
					if ($sourceColumn->getSequence () != $targetColumn->getSequence ()) {
						continue;
					} else if (!$targetColumn->isEqualTo ($sourceColumn)) {
						$targetColumn->copyValuesFrom ($sourceColumn);
					}
					$columns [] = $targetColumn;
					$found      = true;
					break;
				}
				if (!$found) {
					$columns [] = $sourceColumn->duplicate ($reportId);
				}
			}
			$this->sortColumns = $columns;
		}

		/**
		 * @param Report $report
		 */
		private function copySortColumnsFrom ($report) {
			$sourceColumns = $report->getSortColumns ();
			if ((empty ($sourceColumns)) && (empty ($this->sortColumns))) {
				return;
			}

			if (empty ($sourceColumns)) {
				$this->sortColumns = null;
			} else if (empty ($this->sortColumns)) {
				$columns = array ();
				foreach ($sourceColumns as $sourceColumn) {
					$columns [] = $sourceColumn->duplicate ($report->getId ());
				}
				$this->sortColumns = $columns;
			} else {
				$this->copySortColumns ($report->getId (), $sourceColumns);
			}
		}

		/**
		 * @param Report $report
		 */
		private function copyStandardFilterFrom ($report) {
			$sourceStandardFilter = $report->getStandardFilter ();
			if ((empty ($sourceStandardFilter)) && (empty ($this->standardFilter))) {
				return;
			}

			if (empty ($sourceStandardFilter)) {
				$this->standardFilter = null;
			} else if (empty ($this->standardFilter)) {
				$this->standardFilter = $sourceStandardFilter->duplicate ($this->id);
			} else {
				$this->standardFilter->copyValuesFrom ($sourceStandardFilter);
			}
		}

		/**
		 * @param integer $reportId
		 * @param ReportColumn[] $sourceColumns
		 */
		private function copyTotalColumns ($reportId, $sourceColumns) {
			$columns = array ();
			foreach ($sourceColumns as $sourceColumn) {
				$found = false;
				foreach ($this->totalColumns as $targetColumn) {
					if ($sourceColumn->getSequence () != $targetColumn->getSequence ()) {
						continue;
					} else if (!$targetColumn->isEqualTo ($sourceColumn)) {
						$targetColumn->copyValuesFrom ($sourceColumn);
					}
					$columns [] = $targetColumn;
					$found      = true;
					break;
				}
				if (!$found) {
					$columns [] = $sourceColumn->duplicate ($reportId);
				}
			}
			$this->totalColumns = $columns;
		}

		/**
		 * @param Report $report
		 */
		private function copyTotalColumnsFrom ($report) {
			$sourceColumns = $report->getTotalColumns ();
			if ((empty ($sourceColumns)) && (empty ($this->totalColumns))) {
				return;
			}

			if (empty ($sourceColumns)) {
				$this->totalColumns = null;
			} else if (empty ($this->totalColumns)) {
				$columns = array ();
				foreach ($sourceColumns as $sourceColumn) {
					$columns [] = $sourceColumn->duplicate ($report->getId ());
				}
				$this->totalColumns = $columns;
			} else {
				$this->copyTotalColumns ($report->getId (), $sourceColumns);
			}
		}

		/**
		 * @param integer $newReportId
		 * @param string $oldCodeFieldName
		 * @param string $newCodeFieldName
		 *
		 * @return null|ReportAdvancedFilterGroup[]
		 */
		private function duplicateAdvancedFilterGroups ($newReportId, $oldCodeFieldName, $newCodeFieldName) {
			if (empty ($this->advancedFilterGroups)) {
				return null;
			}
			$groups = array ();
			foreach ($this->advancedFilterGroups as $group) {
				$groups [] = $group->duplicate ($newReportId, $group->getSequence (), $oldCodeFieldName, $newCodeFieldName);
			}
			return $groups;
		}

		/**
		 * @param integer $newReportId
		 * @param string $oldCodeFieldName
		 * @param string $newCodeFieldName
		 *
		 * @return null|ReportColumn[]
		 */
		private function duplicateColumns ($newReportId, $oldCodeFieldName, $newCodeFieldName) {
			if (empty ($this->columns)) {
				return null;
			}
			$columns = array ();
			foreach ($this->columns as $column) {
				$columns [] = $column->duplicate ($newReportId, $oldCodeFieldName, $newCodeFieldName);
			}
			return $columns;
		}

		/**
		 * @param integer $newReportId
		 * @param string $oldCodeFieldName
		 * @param string $newCodeFieldName
		 *
		 * @return ReportColumn[]|null
		 */
		private function duplicateSortColumns ($newReportId, $oldCodeFieldName, $newCodeFieldName) {
			if (empty ($this->sortColumns)) {
				return null;
			}
			$columns = array ();
			foreach ($this->sortColumns as $column) {
				$columns [] = $column->duplicate ($newReportId, $oldCodeFieldName, $newCodeFieldName);
			}
			return $columns;
		}

		/**
		 * @param integer $newReportId
		 * @param string $oldCodeFieldName
		 * @param string $newCodeFieldName
		 *
		 * @return ReportColumn[]|null
		 */
		private function duplicateTotalColumns ($newReportId, $oldCodeFieldName, $newCodeFieldName) {
			if (empty ($this->totalColumns)) {
				return null;
			}
			$columns = array ();
			foreach ($this->totalColumns as $column) {
				$columns [] = $column->duplicate ($newReportId, $oldCodeFieldName, $newCodeFieldName);
			}
			return $columns;
		}

		/**
		 * @param Report $report
		 *
		 * @return boolean
		 */
		private function isDeeplyEqualTo ($report) {
			if (
				(!MiscellaneousUtils::areObjectArraysEqual ($this->advancedFilterGroups, $report->getAdvancedFilterGroups ())) ||
				(!MiscellaneousUtils::areObjectArraysEqual ($this->columns, $report->getColumns ())) ||
				(!MiscellaneousUtils::areObjectsEqual ($this->folder, $report->getFolder ())) ||
				(!MiscellaneousUtils::areObjectsEqual ($this->schedule, $report->getSchedule ())) ||
				(!MiscellaneousUtils::areObjectArraysEqual ($this->sortColumns, $report->getSortColumns ())) ||
				(!MiscellaneousUtils::areObjectsEqual ($this->standardFilter, $report->getStandardFilter ())) ||
				(!MiscellaneousUtils::areObjectArraysEqual ($this->totalColumns, $report->getTotalColumns ()))
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * @throws ReportAdvancedFilterException
		 * @throws ReportAdvancedFilterGroupException
		 * @throws ReportException
		 */
		private function validateAdvancedFilterGroups () {
			if (empty ($this->advancedFilterGroups)) {
				return;
			}

			if (!is_array ($this->advancedFilterGroups)) {
				throw new ReportException (ReportException::ERROR_REPORT_INVALID_ADVANCED_FILTER_GROUPS);
			}

			foreach ($this->advancedFilterGroups as $group) {
				if (!($group instanceof ReportAdvancedFilterGroup)) {
					throw new ReportException (ReportException::ERROR_REPORT_INVALID_ADVANCED_FILTER_GROUP);
				} else {
					$group->validate ();
				}
			}
		}

		/**
		 * @throws ReportColumnException
		 * @throws ReportException
		 */
		private function validateColumns () {
			if ((empty ($this->columns)) || (!is_array ($this->columns))) {
				throw new ReportException (ReportException::ERROR_REPORT_INVALID_COLUMNS);
			}

			foreach ($this->columns as $column) {
				if (!($column instanceof ReportColumn)) {
					throw new ReportException (ReportException::ERROR_REPORT_INVALID_COLUMN);
				} else {
					$column->validate ();
				}
			}
		}

		/**
		 * @throws ReportException
		 * @throws ReportScheduleException
		 */
		private function validateSchedule () {
			if (empty ($this->schedule)) {
				return;
			}

			if (!($this->schedule instanceof ReportSchedule)) {
				throw new ReportException (ReportException::ERROR_REPORT_INVALID_SCHEDULE);
			} else {
				$this->schedule->validate ();
			}
		}

		/**
		 * @throws ReportException
		 * @throws ReportSharingEntityException
		 */
		private function validateSharingEntities () {
			if (($this->visibility != ReportInterface::VISIBILITY_SHARED)) {
				return;
			}

			if ((empty ($this->shareWith)) || (!is_array ($this->shareWith))) {
				throw new ReportException (ReportException::ERROR_REPORT_INVALID_SHARING_ENTITIES);
			}

			foreach ($this->shareWith as $sharingEntity) {
				if (!($sharingEntity instanceof ReportSharingEntity)) {
					throw new ReportException (ReportException::ERROR_REPORT_INVALID_SHARING_ENTITY);
				} else {
					$sharingEntity->validate ();
				}
			}
		}

		/**
		 * @throws ReportColumnException
		 * @throws ReportException
		 */
		private function validateSortColumns () {
			if (empty ($this->sortColumns)) {
				return;
			}

			if (!is_array ($this->sortColumns)) {
				throw new ReportException (ReportException::ERROR_REPORT_INVALID_SORT_COLUMNS);
			}

			foreach ($this->sortColumns as $column) {
				if (!($column instanceof ReportColumn)) {
					throw new ReportException (ReportException::ERROR_REPORT_INVALID_SORT_COLUMN);
				} else {
					$column->validate ();
				}
			}
		}

		/**
		 * @throws ReportException
		 * @throws ReportStandardFilterException
		 */
		private function validateStandardFilter () {
			if (empty ($this->standardFilter)) {
				return;
			}

			if (!($this->standardFilter instanceof ReportStandardFilter)) {
				throw new ReportException (ReportException::ERROR_REPORT_INVALID_STANDARD_FILTER);
			} else {
				$this->standardFilter->validate ();
			}
		}

		/**
		 * @throws ReportColumnException
		 * @throws ReportException
		 */
		private function validateTotalColumns () {
			if (empty ($this->totalColumns)) {
				return;
			}

			if (!is_array ($this->totalColumns)) {
				throw new ReportException (ReportException::ERROR_REPORT_INVALID_TOTAL_COLUMNS);
			}

			foreach ($this->totalColumns as $column) {
				if (!($column instanceof ReportColumn)) {
					throw new ReportException (ReportException::ERROR_REPORT_INVALID_TOTAL_COLUMN);
				} else {
					$column->validate ();
				}
			}
		}

		public function __construct () {
			$this->deleted        = false;
			$this->locked         = false;
		}

		/**
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}

		/**
		 * @return ReportAdvancedFilterGroup[]
		 */
		public function getAdvancedFilterGroups () {
			return $this->advancedFilterGroups;
		}

		/**
		 * @return string[]
		 */
		public function getApplicationCodes () {
			return $this->applicationCodes;
		}

		/**
		 * @return ReportColumn[]
		 */
		public function getColumns () {
			return $this->columns;
		}

		/**
		 * @return string
		 */
		public function getDescription () {
			return $this->description;
		}

		/**
		 * @return ReportFolder
		 */
		public function getFolder () {
			return $this->folder;
		}

		/**
		 * @return string
		 */
		public function getModuleName () {
			return $this->moduleName;
		}

		/**
		 * @return string
		 */
		public function getName () {
			return $this->name;
		}

		/**
		 * @return integer
		 */
		public function getOwner () {
			return $this->owner;
		}

		/**
		 * @return string[]
		 */
		public function getRelatedModuleNames () {
			return $this->relatedModuleNames;
		}

		/**
		 * @return ReportSchedule
		 */
		public function getSchedule () {
			return $this->schedule;
		}

		/**
		 * @return ReportSharingEntity[]
		 */
		public function getShareWith () {
			return $this->shareWith;
		}

		/**
		 * @return ReportColumn[]
		 */
		public function getSortColumns () {
			return $this->sortColumns;
		}

		/**
		 * @return ReportStandardFilter
		 */
		public function getStandardFilter () {
			return $this->standardFilter;
		}

		/**
		 * @return string
		 */
		public function getStatus () {
			return $this->status;
		}

		/**
		 * @return ReportColumn[]
		 */
		public function getTotalColumns () {
			return $this->totalColumns;
		}

		/**
		 * @return string
		 */
		public function getType () {
			return $this->type;
		}

		/**
		 * @return string
		 */
		public function getVisibility () {
			return $this->visibility;
		}

		/**
		 * @return boolean
		 */
		public function isDeleted () {
			return $this->deleted;
		}

		/**
		 * @return boolean
		 */
		public function isLocked () {
			return $this->locked;
		}

		/**
		 * @param integer $id
		 *
		 * @return Report
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}

		/**
		 * @param ReportAdvancedFilterGroup[] $advancedFilterGroups
		 *
		 * @return Report
		 */
		public function setAdvancedFilterGroups ($advancedFilterGroups) {
			if (($advancedFilterGroups === null) || ((is_array ($advancedFilterGroups)) && (!empty ($advancedFilterGroups)))) {
				$this->advancedFilterGroups = $advancedFilterGroups;
			}
			return $this;
		}

		/**
		 * @param string[] $applicationCodes
		 *
		 * @return Report
		 */
		public function setApplicationCodes ($applicationCodes) {
			if ((is_array ($applicationCodes)) && (!empty ($applicationCodes))) {
				$this->applicationCodes = $applicationCodes;
			}
			return $this;
		}

		/**
		 * @param ReportColumn[] $columns
		 *
		 * @return Report
		 */
		public function setColumns ($columns) {
			if ((is_array ($columns)) && (!empty ($columns))) {
				$this->columns = $columns;
			}
			return $this;
		}

		/**
		 * @param boolean $deleted
		 *
		 * @return Report
		 */
		public function setDeleted ($deleted) {
			if (is_bool ($deleted)) {
				$this->deleted = $deleted;
			}
			return $this;
		}

		/**
		 * @param string $description
		 *
		 * @return Report
		 */
		public function setDescription ($description) {
			$this->description = $description;
			return $this;
		}

		/**
		 * @param ReportFolder $folder
		 *
		 * @return Report
		 */
		public function setFolder ($folder) {
			if ((!empty ($folder)) && ($folder instanceof ReportFolder)) {
				$this->folder = $folder;
			}
			return $this;
		}

		/**
		 * @param boolean $locked
		 *
		 * @return Report
		 */
		public function setLocked ($locked) {
			if (is_bool ($locked)) {
				$this->locked = $locked;
			}
			return $this;
		}

		/**
		 * @param string $moduleName
		 *
		 * @return Report
		 */
		public function setModuleName ($moduleName) {
			$this->changeModuleName ($this->advancedFilterGroups, $this->moduleName, $moduleName);
			$this->changeModuleName ($this->columns, $this->moduleName, $moduleName);
			$this->changeModuleName ($this->sortColumns, $this->moduleName, $moduleName);
			$this->changeModuleName ($this->standardFilter, $this->moduleName, $moduleName);
			$this->changeModuleName ($this->totalColumns, $this->moduleName, $moduleName);
			$this->moduleName = $moduleName;
			return $this;
		}

		/**
		 * @param string $name
		 *
		 * @return Report
		 */
		public function setName ($name) {
			$this->name = $name;
			return $this;
		}

		/**
		 * @param integer $owner
		 *
		 * @return Report
		 */
		public function setOwner ($owner) {
			$this->owner = $owner;
			return $this;
		}

		/**
		 * @param string[] $relatedModuleNames
		 *
		 * @return Report
		 */
		public function setRelatedModuleNames ($relatedModuleNames) {
			if (($relatedModuleNames === null) || ((is_array ($relatedModuleNames)) && (!empty ($relatedModuleNames)))) {
				$this->relatedModuleNames = $relatedModuleNames;
			}
			return $this;
		}

		/**
		 * @param ReportSchedule $schedule
		 *
		 * @return Report
		 */
		public function setSchedule ($schedule) {
			if (($schedule === null) || ($schedule instanceof ReportSchedule)) {
				$this->schedule = $schedule;
			}
			return $this;
		}

		/**
		 * @param ReportSharingEntity[] $shareWith
		 *
		 * @return Report
		 */
		public function setShareWith ($shareWith) {
			if (($shareWith === null) || ((is_array ($shareWith)) && (!empty ($shareWith)))) {
				$this->shareWith = $shareWith;
			}
			return $this;
		}

		/**
		 * @param ReportColumn[] $sortColumns
		 *
		 * @return Report
		 */
		public function setSortColumns ($sortColumns) {
			if (($sortColumns === null) || ((is_array ($sortColumns)) && (!empty ($sortColumns)))) {
				$this->sortColumns = $sortColumns;
			}
			return $this;
		}

		/**
		 * @param ReportStandardFilter $standardFilter
		 *
		 * @return Report
		 */
		public function setStandardFilter ($standardFilter) {
			if (($standardFilter === null) || ($standardFilter instanceof ReportStandardFilter)) {
				$this->standardFilter = $standardFilter;
			}
			return $this;
		}

		/**
		 * @param integer $status
		 *
		 * @return Report
		 */
		public function setStatus ($status) {
			if (in_array ($status, array (self::STATUS_CUSTOMIZED, self::STATUS_SAVED))) {
				$this->status = $status;
			}
			return $this;
		}

		/**
		 * @param ReportColumn[] $totalColumns
		 *
		 * @return Report
		 */
		public function setTotalColumns ($totalColumns) {
			if (($totalColumns === null) || ((is_array ($totalColumns)) && (!empty ($totalColumns)))) {
				$this->totalColumns = $totalColumns;
			}
			return $this;
		}

		/**
		 * @param string $type
		 *
		 * @return Report
		 */
		public function setType ($type) {
			if (in_array ($type, array (self::TYPE_SUMMARY, self::TYPE_TABULAR))) {
				$this->type = $type;
			}
			return $this;
		}

		/**
		 * @param string $visibility
		 *
		 * @return Report
		 */
		public function setVisibility ($visibility) {
			if (in_array ($visibility, array (self::VISIBILITY_PRIVATE, self::VISIBILITY_PUBLIC, self::VISIBILITY_SHARED))) {
				$this->visibility = $visibility;
			}
			return $this;
		}

		/**
		 * @param Report $report
		 */
		public function copyValuesFrom ($report) {
			if ((empty ($report)) || (!($report instanceof Report))) {
				return;
			}

			$this->applicationCodes   = $report->getApplicationCodes ();
			$this->description        = $report->getDescription ();
			$this->moduleName         = $report->getModuleName ();
			$this->name               = $report->getName ();
			$this->relatedModuleNames = $report->getRelatedModuleNames ();
			$this->status             = $report->getStatus ();
			$this->type               = $report->getType ();
			$this->visibility         = $report->getVisibility ();
			$this->copyAdvancedFilterGroupsFrom ($report);
			$this->copyColumnsFrom ($report);
			$this->copyFolderFrom ($report);
			$this->copyScheduleFrom ($report);
			$this->copySharingEntitiesFrom ($report);
			$this->copySortColumnsFrom ($report);
			$this->copyStandardFilterFrom ($report);
			$this->copyTotalColumnsFrom ($report);
		}

		/**
		 * @param integer $newReportId
		 * @param integer $newOwnerId
		 * @param string $oldCodeFieldName
		 * @param string $newCodeFieldName
		 *
		 * @return Report
		 * @throws ReportException
		 */
		public function duplicate ($newReportId, $newOwnerId, $oldCodeFieldName = null, $newCodeFieldName = null) {
			$this->validate ();
			$object = new self ();
			return $object->setId ($newReportId)
				->setAdvancedFilterGroups ($this->duplicateAdvancedFilterGroups ($newReportId, $oldCodeFieldName, $newCodeFieldName))
				->setApplicationCodes ($this->applicationCodes)
				->setColumns ($this->duplicateColumns ($newReportId, $oldCodeFieldName, $newCodeFieldName))
				->setDescription ($this->description)
				->setFolder (!empty ($this->folder) ? $this->folder->duplicate ($this->folder->getId ()) : null)
				->setModuleName ($this->moduleName)
				->setName ($this->name)
				->setOwner ($newOwnerId)
				->setRelatedModuleNames ($this->relatedModuleNames)
				->setSchedule (!empty ($this->schedule) ? $this->schedule->duplicate ($newReportId) : null)
				->setSortColumns ($this->duplicateSortColumns ($newReportId, $oldCodeFieldName, $newCodeFieldName))
				->setStandardFilter (!empty ($this->standardFilter) ? $this->standardFilter->duplicate ($newReportId) : null)
				->setStatus ($this->status)
				->setTotalColumns ($this->duplicateTotalColumns ($newReportId, $oldCodeFieldName, $newCodeFieldName))
				->setType ($this->type)
				->setVisibility ($this->visibility != ReportInterface::VISIBILITY_SHARED ? $this->visibility : ReportInterface::VISIBILITY_PRIVATE);
		}

		/**
		 * @param Report $report
		 * @param boolean $deepCheck
		 *
		 * @return boolean
		 */
		public function isEqualTo ($report, $deepCheck = true) {
			if (
				(empty ($report)) ||
				(!($report instanceof Report)) ||
				($this->description != $report->getDescription ()) ||
				($this->moduleName != $report->getModuleName ()) ||
				($this->name != $report->getName ()) ||
				($this->status != $report->getStatus ()) ||
				($this->type != $report->getType ()) ||
				(!MiscellaneousUtils::areArrayValuesEqual ($this->applicationCodes, $report->getApplicationCodes ())) ||
				(!MiscellaneousUtils::areArrayValuesEqual ($this->relatedModuleNames, $report->getRelatedModuleNames ())) ||
				(($deepCheck) && (!$this->isDeeplyEqualTo ($report)))
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * @param string $oldTableName
		 * @param string $newTableName
		 */
		public function setTableName ($oldTableName, $newTableName) {
			$this->changeAdvancedFiltersTableName ($oldTableName, $newTableName);
			$this->changeTableName ($this->columns, $oldTableName, $newTableName);
			$this->changeTableName ($this->sortColumns, $oldTableName, $newTableName);
			$this->changeTableName ($this->standardFilter, $oldTableName, $newTableName);
			$this->changeTableName ($this->totalColumns, $oldTableName, $newTableName);
		}

		/**
		 * @throws ReportException
		 */
		public function validate () {
			if ($this->deleted) {
				return;
			} else if (empty ($this->applicationCodes)) {
				throw new ReportException (ReportException::ERROR_REPORT_EMPTY_APPLICATION_CODES);
			} else if (!isset ($this->folder)) {
				throw new ReportException (ReportException::ERROR_REPORT_EMPTY_FOLDER);
			} else if (!($this->folder instanceof ReportFolder)) {
				throw new ReportException (ReportException::ERROR_REPORT_INVALID_FOLDER);
			} else if (empty ($this->moduleName)) {
				throw new ReportException (ReportException::ERROR_REPORT_EMPTY_MODULE_NAME);
			} else if (empty ($this->name)) {
				throw new ReportException (ReportException::ERROR_REPORT_EMPTY_NAME);
			} else if (empty ($this->owner)) {
				throw new ReportException (ReportException::ERROR_REPORT_EMPTY_OWNER);
			} else if (empty ($this->type)) {
				throw new ReportException (ReportException::ERROR_REPORT_EMPTY_TYPE);
			} else if (empty ($this->visibility)) {
				throw new ReportException (ReportException::ERROR_REPORT_EMPTY_VISIBILITY);
			}
			$this->folder->validate ();
			$this->validateAdvancedFilterGroups ();
			$this->validateColumns ();
			$this->validateSchedule ();
			$this->validateSharingEntities ();
			$this->validateSortColumns ();
			$this->validateStandardFilter ();
			$this->validateTotalColumns ();
		}

		/**
		 * @return Report
		 */
		public static function getInstance () {
			return new self ();
		}

	}
