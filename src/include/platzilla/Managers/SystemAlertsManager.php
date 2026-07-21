<?php
	require_once ('include/platzilla/Objects/AlertsOfSystem.php');
	require_once ('include/platzilla/Objects/AlertFilterGroup.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	class SystemAlertsManager {
		
		/** @var SystemAlertsManager[]|null */
		private static $INSTANCES = null;
		
		/** @var PearDatabase */
		private $adb;
		
		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
		}
		
		/**
		 * @param integer $alertId
		 * @param integer $groupId
		 *
		 * @return AlertFilter[]|null
		 * @throws Exception
		 */
		private function fetchFiltersByGroupId ($alertId, $groupId) {
			$result = $this->adb->pquery ("SELECT * FROM vtiger_systemalerts_filters WHERE systemalerts_id=? AND groupid=? ORDER BY sequence", array ($alertId, $groupId));
			if ($this->adb->num_rows ($result) > 0) {
				$filters = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$filters [] = AlertFilter::getInstance ()
						->setAlertId ($alertId)
						->setComparator ($row ['comparator'])
						->setFieldName ($row ['fieldname'])
						->setGroupId ($groupId)
						->setLabel ($row ['label'])
						->setModuleName ($row ['modulename'])
						->setOperator ($row ['operator'])
						->setSequence (intval ($row ['sequence']))
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
		 * @param integer $alertId
		 *
		 * @return AlertFilterGroup[]|null
		 * @throws Exception
		 */
		private function fetchFilterGroupsByAlertId ($alertId) {
			$result = $this->adb->pquery ('SELECT * FROM vtiger_systemalerts_filtergroups WHERE systemalerts_id=? ORDER BY groupid', array ($alertId));
			if ($this->adb->num_rows ($result) > 0) {
				$groups = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$groupId   = intval ($row ['groupid']);
					$alertId   = intval ($row ['systemalerts_id']);
					$groups [] = AlertFilterGroup::getInstance ()
						->setAlertId ($alertId)
						->setId ($groupId)
						->setFilters ($this->fetchFiltersByGroupId ($alertId, $groupId))
						->setModuleName ($row ['modulename'])
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
		 * @param AlertFilterGroup $group
		 * @param integer $idAlert
		 *
		 * @throws FilterException
		 */
		private function saveFilters ($group, $idAlert) {
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
		 * @param AlertFilter $filter
		 * @throws FilterException
		 */
		private function validateFilter ($filter) {
			$groupId    = $filter->getGroupId ();
			$moduleName = $filter->getModuleName ();
			if (empty ($moduleName)) {
				throw new FilterException (FilterException::ERROR_FILTER_EMPTY_MODULE_NAME);
			} else if (!isset ($groupId)) {
				throw new FilterException (FilterException::ERROR_FILTER_EMPTY_GROUP_ID);
			}
			
			$result = $this->adb->pquery ('SELECT * FROM vtiger_tab WHERE name=?', array ($moduleName));
			if ($this->adb->num_rows ($result) == 0) {
				$e = new FilterException (FilterException::ERROR_FILTER_INVALID_MODULE_NAME);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			if (isset ($e)) {
				throw $e;
			}
		}
		
		/**
		 * @param AlertFilterGroup $groups
		 *
		 * @throws FilterException
		 * @throws FilterGroupException
		 */
		private function validateFilterGroupSequenceNumbers ($groups) {
			// Validar los números de secuencia de grupos y filtros
			$processedGroupIds        = array ();
			$processedFilterSequences = array ();
			foreach ($groups as $group) {
				$groupId = $group->getId ();
				if (in_array ($groupId, $processedGroupIds)) {
					throw new FilterGroupException (FilterGroupException::ERROR_FILTER_GROUP_ID_ALREADY_TAKEN);
				}
				
				$filters = $group->getFilters ();
				if (!empty ($filters)) {
					foreach ($filters as $filter) {
						$sequence = $filter->getSequence ();
						if (in_array ($sequence, $processedFilterSequences)) {
							throw new FilterException (FilterException::ERROR_FILTER_SEQUENCE_ALREADY_TAKEN);
						}
						$processedFilterSequences [] = $sequence;
					}
				}
				$processedFilterSequences = array ();
				$processedGroupIds [] = $groupId;
			}
		}
		/**
		 * @return $systenAlerts[]|null
		 * @throws Exception
		 */
		public function fetchSystemAlerts () {
			$result = $this->adb->query (
				'SELECT
					  sa.*
				FROM
				  vtiger_systemalerts sa
				INNER JOIN vtiger_tab t ON t.name = sa.tab_name AND t.presence IN (0, 2)'
			);
			if ($this->adb->num_rows ($result) > 0) {
				$systenAlerts = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$systenAlerts[] = AlertsOfSystem::getInstance ()
						->setAlertId ($row ['systemalerts_id'])
						->setAlertName ($row ['name'])
						->setAlertTitle ($row ['alert'])
						->setAppCode ($row ['code_app'])
						->setBoxScoreId ($row ['boxscore_id'])
						->setDescription ($row ['description '])
						->setFiltroGrupo ($this->fetchFilterGroupsByAlertId ($row ['systemalerts_id']))
						->setIndicatorId ($row ['indicator_id'])
						->setLocked ($row ['locked'])
						->setScale ($row ['scale'])
						->setStatus ($row ['status'])
						->setSourceAlert ($row ['source_alert'])
						->setTabId ($row ['tab_id'])
						->setTabName ($row ['tab_name'])
						->setTabLabel ($row ['tab_label']);
				}
			} else {
				$systenAlerts = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $systenAlerts;
		}
		
		/**
		 * @param AlertsOfSystem $systemAlert
		 * @param boolean $ignoreLock
		 *
		 * @throws Exception
		 */
		public function saveSystemAlert ($systemAlert, $ignoreLock = true) {
			if (empty ($systemAlert)) {
				return;
			}
			
			$moduleName = $systemAlert->getTabName ();
			$this->adb->startTransaction ();
			$result = $this->adb->pquery ('SELECT tabid FROM vtiger_tab WHERE name=? AND presence IN (0, 2)',	array($moduleName));
			if ($this->adb->num_rows ($result) > 0) {
				$row = $this->adb->fetchByAssoc ($result, -1, false);
				$systemAlert->setTabId ($row['tabid']);
			} else {
				return;
			}
			$result = $this->adb->pquery ('SELECT systemalerts_id, locked FROM vtiger_systemalerts WHERE name=?',	array($systemAlert->getTabName ()));
			if ($this->adb->num_rows ($result) > 0) {
				$row      = $this->adb->fetchByAssoc ($result, -1, false);
				$isLocked = ($row ['locked'] == 1);
				$alertId  = intval($row ['systemalerts_id']);
				$systemAlert->setLocked ($row ['locked']);
			} else {
				$isLocked = false;
				$alertId = null;
			}
			if (empty ($alertId)) {
				$this->adb->pquery (
					'INSERT INTO vtiger_systemalerts (name, code_app, alert, source_alert, indicator_id, boxscore_id, tab_id, tab_name, tab_label, status, scale, users_ids, description) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
					array ($systemAlert->getAlertName (), $systemAlert->getAppCode (), $systemAlert->getAlertTitle (), $systemAlert->getSourceAlert (), $systemAlert->getIndicatorId (), $systemAlert->getBoxscoreId (), $systemAlert->getTabId (), $systemAlert->getTabName (), $systemAlert->getTabLabel (), $systemAlert->getStatus (), $systemAlert->getScale (), 1, $systemAlert->getDescription ()));
				$alertId = $this->adb->getLastInsertID ();
				$systemAlert->setAlertId ($alertId);
			} else if (($ignoreLock) || (!$isLocked)) {
				$this->adb->pquery (
					'UPDATE vtiger_systemalerts SET code_app=?, alert=?, source_alert=?, indicator_id=?, boxscore_id=?, tab_id=?, tab_name=?, tab_label=?, status=?, scale=?, description=?, locked=?  WHERE name=?',
					array($systemAlert->getAppCode (), $systemAlert->getAlertTitle (), $systemAlert->getSourceAlert (), $systemAlert->getIndicatorId (), $systemAlert->getBoxscoreId (), $systemAlert->getTabId (), $systemAlert->getTabName (), $systemAlert->getTabLabel (), $systemAlert->getStatus (), $systemAlert->getScale (), $systemAlert->getDescription (), $systemAlert->getLocked (), $systemAlert->getAlertName ())
				);
			}
			$this->adb->completeTransaction ();
			DatabaseUtils::closeResult ($result);
			$result = null;
			$this->saveAlertsFilterGroups ($systemAlert);
		}
		
		/**
		 * @param AlertsOfSystem $systemAlert
		 *
		 * @throws FilterException
		 * @throws FilterGroupException
		 */
		public function saveAlertsFilterGroups ($systemAlert) {
			if (!count ($systemAlert->getFiltroGrupo ())) {
				$this->adb->pquery ('DELETE FROM vtiger_systemalerts_filters WHERE modulename=? AND systemalerts_id=?', array ($systemAlert->getTabName (), $systemAlert->getAlertId ()));
				$this->adb->pquery ('DELETE FROM vtiger_systemalerts_filtergroups WHERE modulename=? AND systemalerts_id=?', array ($systemAlert->getTabName (), $systemAlert->getAlertId ()));
				return;
			}
			
			
			
			$processedGroupIds = array ();
			$idAlert           = $systemAlert->getAlertId ();
			foreach ($systemAlert->getFiltroGrupo () as $group) {
				$filters = $group->getFilters ();
				if (empty ($filters)) {
					continue;
				}
				$this->validateFilterGroupSequenceNumbers ($group);
				$groupId = $group->getId ();
				$result  = $this->adb->pquery ('SELECT * FROM vtiger_systemalerts_filtergroups WHERE modulename=? AND groupid=? AND systemalerts_id=?', array ($group->getModuleName (), $groupId, $idAlert));
				if ($this->adb->num_rows ($result) == 0) {
					$this->adb->pquery (
						'INSERT INTO vtiger_systemalerts_filtergroups (systemalerts_id, modulename, groupid, operator) VALUES (?, ?, ?, ?)',
						array ($idAlert, $group->getModuleName (), $groupId, $group->getOperator ())
					);
				} else if(!empty($group->getOperator ())) {
					$this->adb->pquery (
						'UPDATE vtiger_systemalerts_filtergroups SET operator=? WHERE modulename=? AND groupid=? AND systemalerts_id=?',
						array ($group->getOperator (), $group->getModuleName (), $groupId, $idAlert)
					);
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
				$this->saveFilters ($group, $idAlert);
				$processedGroupIds [] = $groupId;
			}
			
			if (!empty ($processedGroupIds)) {
				$questionMarks = str_repeat ('?, ', (count ($processedGroupIds) - 1)) . '?';
				$this->adb->pquery ("DELETE FROM vtiger_systemalerts_filtergroups WHERE modulename=? AND groupid NOT IN ({$questionMarks})", array_merge (array ($group->getModuleName ()), $processedGroupIds));
			}
		}
		
		/**
		 * @param PearDatabase $adb
		 *
		 * @return SystemAlertsManager
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
