<?php
	require_once ('include/platzilla/Data/EntityHistory.php');
	require_once ('include/platzilla/Objects/FieldInterface.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');

	/**
	 * Class EntityHistoryManager
	 *
	 * Esta clase hace referencia a los métodos que gestionan la base de datos del Histórico de cambios.
	 */
	class EntityHistoryManager {

		/** @var EntityHistoryManager */
		private static $INSTANCES = null;

		/** @var PearDatabase */
		private $adb;

		/**
		 * EntityHistoryManager constructor.
		 *
		 * @param PearDatabase $adb
		 */
		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
		}

		/**
		 * Valida que el formato y datos de la fecha esten correctos
		 *
		 * @param string $date
		 * @param string $format
		 *
		 * @return boolean
		 */
		private function validateDate ($date, $format = 'Y-m-d') {
			if (empty ($date)) {
				return false;
			}
			$objectDate = DateTime::createFromFormat ($format, $date);
			return $objectDate && $objectDate->format ($format) == $date;
		}

		/**
		 * Devuelve lista de registros de histórico de cambio de un determinado
		 *
		 * @param integer $entityId
		 *
		 * @return EntityHistory[]|null
		 * @throws Exception
		 */
		public function fetchEntityHistory ($entityId) {
			if (empty ($entityId) || !is_numeric ($entityId)) {
				return null;
			}

			$result = $this->adb->pquery (
				"SELECT
					crmu.*,
					f.fieldname,
					f.fieldlabel,
					f.uitype,
					t.tablabel,
					t.name,
					CONCAT(u.first_name, ' ', u.last_name) AS username
				FROM
					vtiger_crmentityutils crmu
					LEFT JOIN vtiger_field f ON f.fieldid = crmu.field
					LEFT JOIN vtiger_tab t ON t.tabid = crmu.module
					LEFT JOIN vtiger_users u ON u.id = crmu.modifiedby
				WHERE
					registryid=?
				ORDER BY crmu.date DESC",
				array ($entityId)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$histories = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$histories [] = EntityHistory::getInstance ()
						->setId ($row ['crmentityid'])
						->setCreatedDate ($row ['date'])
						->setFieldId ($row ['field'])
						->setFieldLabel ($row ['fieldlabel'])
						->setFieldName ($row ['fieldname'])
						->setModuleId ($row ['module'])
						->setModuleName ($row ['name'])
						->setModuleLabel ($row ['tablabel'])
						->setModifiedBy ($row ['modifiedby'])
						->setModifiedOn ($row ['modifiedon'])
						->setNewValue ($row ['newvalue'])
						->setOldValue ($row ['oldvalue'])
						->setRegistryId ($row ['registryid'])
						->setUiType ($row ['uitype'])
						->setUserName (ucwords ($row ['username']));
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($histories)) ? $histories : null;
		}

		/**
		 * Devuelve lista de registros de histórico de cambio de un determinado campo
		 *
		 * @param string $fieldName
		 * @param string $from
		 * @param null|string $to
		 *
		 * @return EntityHistory[]|null
		 * @throws Exception
		 */
		public function fetchEntityHistoryByField ($fieldName, $from, $to = null) {
			if (empty ($to) && !empty($from)) {
				$objectDate = new DateTime();
				$objectDate->modify ('+1 day');
				$to = $objectDate->format ('Y-m-d');
			}

			$dateRange = ($this->validateDate ($from) && $this->validateDate ($to)) ? " (DATE(crmu.date) BETWEEN STR_TO_DATE('{$from}','%Y-%m-%d') AND STR_TO_DATE('{$to}','%Y-%m-%d')) AND" : '';

			$result = $this->adb->query (
				"SELECT
					crmu.*,
					f.fieldname,
					f.fieldlabel,
					f.uitype,
					t.tablabel,
					t.name,
					CONCAT(u.first_name, ' ', u.last_name) AS username
				FROM
					vtiger_crmentityutils crmu
					LEFT JOIN vtiger_field f ON f.fieldid = crmu.field
					LEFT JOIN vtiger_tab t ON t.tabid = crmu.module
					LEFT JOIN vtiger_users u ON u.id = crmu.modifiedby
				WHERE
					{$dateRange}
					f.fieldname='{$fieldName}' 
				ORDER BY crmu.crmentityid DESC"
			);
			if ($this->adb->num_rows ($result) > 0) {
				$histories = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$histories [] = EntityHistory::getInstance ()
						->setId ($row ['crmentityid'])
						->setCreatedDate ($row ['date'])
						->setFieldId ($row ['field'])
						->setFieldLabel ($row ['fieldlabel'])
						->setFieldName ($row ['fieldname'])
						->setModuleId ($row ['module'])
						->setModuleName ($row ['name'])
						->setModuleLabel ($row ['tablabel'])
						->setModifiedBy ($row ['modifiedby'])
						->setModifiedOn ($row ['modifiedon'])
						->setNewValue ($row ['newvalue'])
						->setOldValue ($row ['oldvalue'])
						->setRegistryId ($row ['registryid'])
						->setUiType ($row ['uitype'])
						->setUserName (ucwords ($row ['username']));
				}
			} else {
				$histories = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($histories)) ? $histories : null;
		}

		/**
		 * Devuelve lista de registros de histórico de cambios por modulos y lista relacionadas
		 *
		 * @param integer $entityId
		 * @param string $from
		 * @param string|null $to
		 * @param array|null $fieldNames
		 * @param string|null $sqlFilter
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public function fetchEntityRelationshipHistory ($entityId, $from, $to = null, $fieldNames = null, $sqlFilter = null) {
			if (empty ($entityId) || !is_numeric ($entityId)) {
				return null;
			}

			$result = $this->adb->pquery (
				'SELECT 
						rel.relcrmid,
						tab.name
					  FROM 
					  	vtiger_crmentityrel rel
					  LEFT JOIN vtiger_tab tab ON tab.name = rel.relmodule
					  LEFT JOIN vtiger_crmentity crm ON crm.crmid = rel.relcrmid 
					  WHERE 
					  	rel.crmid=? AND 
					  	crm.deleted=?',
				array ($entityId, 0)
			);

			if ($this->adb->num_rows ($result) > 0) {
				$histories = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					if ($historyRel = $this->searchEntityHistory ($row ['relcrmid'], $from, $to, $fieldNames, $sqlFilter)) {
						foreach ($historyRel as $history) {
							if ($history->getUiType() == FieldInterface::UI_TYPE_MODULE_REFERENCE) {
								continue;
							}
							$relationshipHistories [] = $history;
						}

						$histories [] = array(
							'type'         => 'crmentityrel',
							'relationship' => $row ['name'],
							'histories'    => $relationshipHistories,
						);
						unset ($relationshipHistories);
					}
				}
			} else {
				$histories = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($histories)) ? $histories : null;
		}

		/**
		 * Devuelve lista de tareas relacionadas con el registro del histórico de cambios actual
		 *
		 * @param $entityId
		 * @param $from
		 * @param null $to
		 * @param null $fieldNames
		 * @param null $sqlFilter
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public function fetchTaskToRelatedRecord ($entityId, $from, $to = null, $fieldNames = null, $sqlFilter = null) {
			if (empty ($entityId) || !is_numeric ($entityId)) {
				return null;
			}

			$result = $this->adb->pquery (
				'SELECT 
						rel.activityid, 
						act.subject
					  FROM 
					  	vtiger_seactivityrel rel 
					  LEFT JOIN vtiger_activity act ON  act.activityid = rel.activityid
					  LEFT JOIN vtiger_crmentity crm ON crm.crmid = rel.activityid 
					  WHERE 
					  	rel.crmid=? AND 
					  	crm.deleted=?',
				array ($entityId, 0)
			);

			if ($this->adb->num_rows ($result) > 0) {
				$histories = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					if ($historyTask = $this->searchEntityHistory ($row ['activityid'], $from, $to, $fieldNames, $sqlFilter)) {
						foreach ($historyTask as $history) {
							if ($history->getUiType() != FieldInterface::UI_TYPE_PICKLIST) {
								continue;
							}
							$taskToRelatedRecord [] = $history;
						}
						$histories [] = array(
							'type'         => 'seactivityrel',
							'relationship' => $row ['subject'],
							'histories'    => $taskToRelatedRecord,
						);
						unset ($taskToRelatedRecord);
					}
				}
			} else {
				$histories = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($histories)) ? $histories : null;
		}

		/**
		 * Devuelve lista de registros de histórico de cambios basada en condiciones de busqueda avanzada o temporal.
		 *
		 * @param integer $entityId
		 * @param string $from
		 * @param string|null $to
		 * @param array|null $fieldNames
		 * @param string|null $sqlFilter
		 *
		 * @return EntityHistory[]|null
		 * @throws Exception
		 */
		public function searchEntityHistory ($entityId, $from, $to = null, $fieldNames = null, $sqlFilter = null) {
			if (empty ($to) && !empty($from)) {
				$objectDate = new DateTime();
				$objectDate->modify ('+1 day');
				$to = $objectDate->format ('Y-m-d');
			}

			$dateRange    = ($this->validateDate ($from) && $this->validateDate ($to)) ? " (DATE(crmu.date) BETWEEN STR_TO_DATE('{$from}','%Y-%m-%d') AND STR_TO_DATE('{$to}','%Y-%m-%d')) AND" : '';
			$inFieldNames = (!empty($fieldNames) && is_array ($fieldNames)) ? ' f.fieldname IN ( "' . implode ('", "', array_unique ($fieldNames)) . '") AND' : '';
			$sqlFilter    = (!empty($sqlFilter)) ? "({$sqlFilter})  AND " : '';

			$result = $this->adb->query (
				"SELECT
					crmu.*,
					f.fieldname,
					f.fieldlabel,
					f.uitype,
					t.tablabel,
					t.name,
					CONCAT(u.first_name, ' ', u.last_name) AS username
				FROM
					vtiger_crmentityutils crmu
					LEFT JOIN vtiger_field f ON f.fieldid = crmu.field
					LEFT JOIN vtiger_tab t ON t.tabid = crmu.module
					LEFT JOIN vtiger_users u ON u.id = crmu.modifiedby
				WHERE
					{$dateRange}
					{$sqlFilter}
					{$inFieldNames}
					registryid={$entityId}"
			);
			if ($this->adb->num_rows ($result) > 0) {
				$histories = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$histories [] = EntityHistory::getInstance ()
						->setId ($row ['crmentityid'])
						->setCreatedDate ($row ['date'])
						->setFieldId ($row ['field'])
						->setFieldLabel ($row ['fieldlabel'])
						->setFieldName ($row ['fieldname'])
						->setModuleId ($row ['module'])
						->setModuleName ($row ['name'])
						->setModuleLabel ($row ['tablabel'])
						->setModifiedBy ($row ['modifiedby'])
						->setModifiedOn ($row ['modifiedon'])
						->setNewValue ($row ['newvalue'])
						->setOldValue ($row ['oldvalue'])
						->setRegistryId ($row ['registryid'])
						->setUiType ($row ['uitype'])
						->setUserName (ucwords ($row ['username']));
				}
			} else {
				$histories = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($histories)) ? $histories : null;
		}

		/**
		 * Guarda una lista de de objetos de históricos de cambios
		 *
		 * @param EntityHistory[] $fullRecordHistory
		 *
		 * @throws EntityHistoryException
		 */
		public function saveAllEntityHistory ($fullRecordHistory) {
			if ((empty ($fullRecordHistory)) || (!is_array ($fullRecordHistory))) {
				return;
			}

			foreach ($fullRecordHistory as $recordHistory) {
				$this->saveEntityHistory ($recordHistory);
			}
		}

		/**
		 * Guarda un objeto histórico de cambios
		 *
		 * @param EntityHistory $recordHistory
		 *
		 * @throws EntityHistoryException
		 */
		public function saveEntityHistory ($recordHistory) {
			if ((empty ($recordHistory)) || (!($recordHistory instanceof EntityHistory))) {
				return;
			}

			$recordHistory->validate ();
			if (in_array (
					$recordHistory->getUiType (), array (
						FieldInterface::UI_TYPE_TEXTAREA,
						FieldInterface::UI_TYPE_VIDEO,
						FieldInterface::UI_TYPE_APP,
						FieldInterface::UI_TYPE_OPERATION_ROW,
						FieldInterface::UI_TYPE_SUMMARY_ROW,
						FieldInterface::UI_TYPE_TABLE_FIELD,
						FieldInterface::UI_TYPE_ATTACHMENTS,
						FieldInterface::UI_TYPE_GRID,
					)
				)
			) {
				$oldValue = (strlen ($recordHistory->getOldValue () > 500)) ? substr ($recordHistory->getOldValue (), 500) : $recordHistory->getOldValue ();
				$newValue = (strlen ($recordHistory->getNewValue () > 500)) ? substr ($recordHistory->getNewValue (), 500) : $recordHistory->getNewValue ();
			} else {
				$oldValue = $recordHistory->getOldValue ();
				$newValue = $recordHistory->getNewValue ();
			}
			if (empty ($recordHistory->getId ())) {
				$this->adb->pquery (
					'INSERT INTO vtiger_crmentityutils ( module, field, oldvalue, newvalue, modifiedby, modifiedon, registryid, date)
					VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
					array ($recordHistory->getModuleId (), $recordHistory->getFieldId (), $oldValue, $newValue, $recordHistory->getModifiedBy (), $recordHistory->getModifiedOn (), $recordHistory->getRegistryId (), $recordHistory->getCreatedDate ())
				);
			} else {
				$this->adb->pquery (
					'UPDATE vtiger_crmentityutils SET module=?, field=?, oldvalue=?, newvalue=?, modifiedby=?, modifiedon=?, registryid=?, date=? WHERE crmentityid=?',
					array ($recordHistory->getModuleId (), $recordHistory->getFieldId (), $oldValue, $newValue, $recordHistory->getModifiedBy (), $recordHistory->getModifiedOn (), $recordHistory->getRegistryId (), $recordHistory->getCreatedDate (), $recordHistory->getId ())
				);
			}
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return EntityHistoryManager
		 */
		public static function getInstance (PearDatabase $adb) {
			if (self::$INSTANCES === null) {
				self::$INSTANCES = array ();
			}
			if (!isset (self::$INSTANCES [ $adb->dbName ])) {
				self::$INSTANCES[ $adb->dbName ] = new self ($adb);
			}
			return self::$INSTANCES [ $adb->dbName ];
		}

	}
