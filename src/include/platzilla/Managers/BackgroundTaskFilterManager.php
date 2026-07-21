<?php
	require_once ('include/platzilla/Managers/FilterManager.php');
	require_once ('include/platzilla/Objects/BackgroundTaskFilterGroup.php');

	class BackgroundTaskFilterManager extends FilterManager {
		/** @var BackgroundTaskFilterManager[]|null */
		protected static $INSTANCES = null;

		/**
		 * @param BackgroundTask $task
		 * @param CRMEntity $entity
		 *
		 * @return boolean
		 */
		public function evaluateFilters ($task, $entity) {
			$moduleName = $task->getModuleName ();
			$groups     = $task->getFilterGroups ();
			return parent::evaluateFilterGroups ($groups, $entity, $moduleName);
		}

		/**
		 * @param integer $taskId
		 *
		 * @return BackgroundTaskFilterGroup[]|null
		 */
		public function fetchTaskFilterGroupsByTaskId ($taskId) {
			$groups = parent::fetchFilterGroupsByEntityId (BackgroundTaskFilterGroup::class, BackgroundTaskFilter::class, $taskId);
			if (empty ($groups)) {
				return null;
			}

			/** @var BackgroundTaskFilterGroup $group */
			foreach ($groups as $group) {
				$group->setTaskId ($taskId);
			}
			return $groups;
		}

		/**
		 * @param integer $taskId
		 * @param integer $groupId
		 *
		 * @return BackgroundTaskFilter[]|null
		 */
		public function fetchTaskFiltersByGroupId ($taskId, $groupId) {
			$filters = parent::fetchFiltersByGroupId (BackgroundTaskFilter::class, $taskId, $groupId);
			if (empty ($filters)) {
				return null;
			}

			/** @var BackgroundTaskFilter $filter */
			foreach ($filters as $filter) {
				$filter->setTaskId ($taskId);
			}
			return $filters;
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return BackgroundTaskFilterManager
		 */
		public static function getInstance (PearDatabase $adb) {
			if (self::$INSTANCES === null) {
				self::$INSTANCES = array ();
			}
			if (!isset (self::$INSTANCES [ $adb->dbName ])) {
				self::$INSTANCES [ $adb->dbName ] = new self ($adb, 'vtiger_bgtasks_data', 'taskid');
			}
			return self::$INSTANCES [ $adb->dbName ];
		}

	}
