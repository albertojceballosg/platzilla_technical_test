<?php
	require_once ('include/platzilla/Managers/FilterManager.php');
	require_once ('include/platzilla/Objects/ModuleEditPermissionConditionGroup.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');

	class SystemAlertFilterUtils extends FilterManager {

		/** @var SystemAlertFilterUtils[]|null */
		protected static $INSTANCES = null;

		/**
		 * @param FilterGroupInterface $filterGroupClassName
		 * @param FilterInterface $filterClassName
		 * @param integer $moduleName
		 *
		 * @return array|null
		 * @throws Exception
		 */
		protected function fetchFilterGroupsByEntityId ($filterGroupClassName, $filterClassName, $moduleName) {
			$result = $this->adb->pquery ('SELECT * FROM vtiger_systemalerts_filtergroups WHERE modulename=? ORDER BY groupid', array ($moduleName));
			if ($this->adb->num_rows ($result) > 0) {
				$groups = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$groupId   = intval ($row ['groupid']);
					$groups [] = $filterGroupClassName::getInstance ()
						->setId ($groupId)
						->setFilters ($this->fetchFiltersByGroupId ($filterClassName, $moduleName, $groupId))
						->setModuleName ($moduleName)
						->setOperator ($row ['operator']);
				}
			} else {
				$groups = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $groups;
		}

		/**
		 * @param ModuleEditPermissionConditionGroup $group
		 * @param integer $idAlert
		 *
		 * @throws FilterException
		 */
		protected function saveFilters ($group, $idAlert) {
			$moduleName = $group->getModuleName ();
			$groupId    = $group->getId ();
			$filters    = $group->getFilters ();
			if (empty ($filters)) {
				$this->adb->pquery ('DELETE FROM vtiger_systemalerts_filters WHERE modulename=? AND groupid=? AND systemalerts_id=?', array ($moduleName, $groupId, $idAlert));
				return;
			}

			$processedSequences = array ();
			foreach ($filters as $filter) {
				if ($filter->getGroupId () === null) {
					$filter->setGroupId ($groupId);
				}
				$this->validateFilter ($filter);

				$sequence = $filter->getSequence ();
				$result   = $this->adb->pquery ('SELECT * FROM vtiger_systemalerts_filters WHERE modulename=? AND groupid=? AND  systemalerts_id=? AND sequence=?', array ($moduleName, $groupId, $idAlert, $sequence));
				if ($this->adb->num_rows ($result) == 0) {
					$this->adb->pquery (
						'INSERT INTO vtiger_systemalerts_filters (systemalerts_id, modulename, groupid, sequence, fieldname, label, comparator, value, operator) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
						array ($idAlert, $moduleName, $groupId, $sequence, $filter->getFieldName (), $filter->getLabel (), $filter->getComparator (), $filter->getValue (), $filter->getOperator ())
					);
				} else {
					$this->adb->pquery (
						'UPDATE vtiger_systemalerts_filters SET fieldname=?, label=?, comparator=?, value=?, operator=? WHERE modulename=? AND groupid=? AND  systemalerts_id=? AND sequence=?',
						array ($filter->getFieldName (), $filter->getLabel (), $filter->getComparator (), $filter->getValue (), $filter->getOperator (), $moduleName, $groupId, $idAlert, $sequence)
					);
				}
				DatabaseUtils::closeResult ($result);
				$result                = null;
				$processedSequences [] = $sequence;
			}

			$questionMarks = str_repeat ('?, ', (count ($processedSequences) - 1)) . '?';
			$this->adb->pquery ("DELETE FROM vtiger_systemalerts_filters WHERE modulename=? AND groupid=? AND sequence NOT IN ({$questionMarks})", array_merge (array ($moduleName, $groupId), $processedSequences));
		}

		/**
		 * @param string $moduleName
		 * @param CRMEntity $entity
		 *
		 * @return boolean
		 */
		public function isEditable ($moduleName, $entity) {
			$groups = $this->fetchFilterGroupsByEntityId (ModuleEditPermissionConditionGroup::class, ModuleEditPermissionCondition::class, $moduleName);
			if (empty ($groups)) {
				return true;
			} else if (get_class ($entity) != $moduleName) {
				return false;
			} else {
				return !parent::evaluateFilterGroups ($groups, $entity, $moduleName);
			}
		}
		
		/**
		 * @param string $moduleName
		 * @param integer $entityId
		 *
		 * @return boolean
		 * @throws Exception
		 */
		public function isRemovable ($moduleName, $entityId) {
			if (empty($moduleName) || empty($entityId) || !is_numeric ($entityId)) {
				return true;
			}
			$removable = true;
			$relatedData = $this->adb->pquery (
				'SELECT
					    f.columnname,
					    fmr.module
					 FROM
					    vtiger_fieldmodulerel fmr
					 INNER JOIN vtiger_field f ON
					    fmr.fieldid = f.fieldid
					 INNER JOIN vtiger_tab tab ON
					    tab.name = fmr.module AND tab.presence != -1
					 WHERE
						fmr.relmodule=?',
				array ($moduleName)
			);
			if ($this->adb->num_rows ($relatedData) > 0) {
				$relatedItems = array ();
				while ($row = $this->adb->fetchByAssoc ($relatedData, -1, false)) {
					try {
						$entity  = CRMEntity::getInstance ($row ['module']);
					} catch (Exception $e) {
						continue;
					}
					$relatedItems [] = array ($row['columnname'], $entity->table_name, $entity->table_index );
				}
				DatabaseUtils::closeResult ($relatedData);
				$relatedData = null;
				unset($entity);
				foreach ($relatedItems as $relatedItem) {
					$result = $this->adb->query (
						"SELECT
								{$relatedItem [0]}
							  FROM
							  	{$relatedItem [1]} t
							  INNER JOIN vtiger_crmentity crm ON  crm.crmid = t.{$relatedItem [2]} AND crm.deleted = 0
							  WHERE
							  	t.{$relatedItem [0]}={$entityId}"
					);
					
					if ($this->adb->num_rows ($result) > 0) {
						$removable = false;
						break;
					}
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
			}
			return $removable;
		}
		
		/**
		 * @param integer $alertId
		 * @param string $moduleName
		 *
		 * @return ModuleEditPermissionConditionGroup[]|null
		 */
		public function fetchConditionGroups ($alertId, $moduleName) {
			$groups = parent::fetchFilterGroupsByEntityId (BackgroundTaskFilterGroup::class, BackgroundTaskFilter::class, $alertId);
			if (empty ($groups)) {
				return null;
			}

			/** @var BackgroundTaskFilterGroup $group */
			foreach ($groups as $group) {
				$group->setModuleName ($moduleName);
			}
			return $groups;
		}

		/**
		 * @param string $moduleName
		 * @param ModuleEditPermissionConditionGroup[] $groups
		 * @param integer $idAlert
		 *
		 * @throws FilterException
		 * @throws FilterGroupException
		 */
		public function saveFilterGroups ($moduleName, $groups, $idAlert) {
			if (empty ($groups)) {
				$this->adb->pquery ('DELETE FROM vtiger_systemalerts_filters WHERE modulename=? AND systemalerts_id=?', array ($moduleName, $idAlert));
				$this->adb->pquery ('DELETE FROM vtiger_systemalerts_filtergroups WHERE modulename=? AND systemalerts_id=?', array ($moduleName, $idAlert));
				return;
			}

			$this->validateFilterGroupSequenceNumbers ($groups);
			$processedGroupIds = array ();
			foreach ($groups as $group) {
				$filters = $group->getFilters ();
				if (empty ($filters)) {
					continue;
				}

				$groupId = $group->getId ();
				$result  = $this->adb->pquery ('SELECT * FROM vtiger_systemalerts_filtergroups WHERE modulename=? AND groupid=? AND systemalerts_id=?', array ($moduleName, $groupId, $idAlert));
				if ($this->adb->num_rows ($result) == 0) {
					$this->adb->pquery (
						'INSERT INTO vtiger_systemalerts_filtergroups (systemalerts_id, modulename, groupid, operator) VALUES (?, ?, ?, ?)',
						array ($idAlert, $moduleName, $groupId, $group->getOperator ())
					);
				} else if(!empty($group->getOperator ())) {
					$this->adb->pquery (
						'UPDATE vtiger_systemalerts_filtergroups SET operator=? WHERE modulename=? AND groupid=? AND systemalerts_id=?',
						array ($group->getOperator (), $moduleName, $groupId, $idAlert)
					);
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
				$this->saveFilters ($group, $idAlert);
				$processedGroupIds [] = $groupId;
			}

			if (!empty ($processedGroupIds)) {
				$questionMarks = str_repeat ('?, ', (count ($processedGroupIds) - 1)) . '?';
				$this->adb->pquery ("DELETE FROM vtiger_systemalerts_filtergroups WHERE modulename=? AND groupid NOT IN ({$questionMarks})", array_merge (array ($moduleName), $processedGroupIds));
			}
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return SystemAlertFilterUtils
		 */
		public static function getInstance (PearDatabase $adb) {
			if (self::$INSTANCES === null) {
				self::$INSTANCES = array ();
			}
			if (!isset (self::$INSTANCES [ $adb->dbName ])) {
				self::$INSTANCES [ $adb->dbName ] = new self ($adb, 'vtiger_systemalerts', 'systemalerts_id');
			}
			return self::$INSTANCES [ $adb->dbName ];
		}

	}
