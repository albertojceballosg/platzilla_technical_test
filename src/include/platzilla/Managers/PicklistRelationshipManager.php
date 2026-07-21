<?php
	require_once ('include/platzilla/Managers/PicklistManager.php');
	require_once ('include/platzilla/Objects/FieldInterface.php');
	require_once ('include/platzilla/Objects/PicklistRelationship.php');
	require_once ('include/platzilla/Objects/PicklistRelationshipMaster.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');

	/**
	 * Class PicklistRelationshipManager
	 *
	 * Clase donde se definen las utilerías/metodos para gestionar las relaciones de los campos del tipo picklist
	 */
	class PicklistRelationshipManager {
		
		/** @var PicklistRelationshipManager[]|null */
		private static $INSTANCES = null;

		/** @var PearDatabase */
		private $adb;

		/**
		 * PicklistRelationshipManager constructor.
		 *
		 * @param \PearDatabase $adb
		 */
		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
		}

		/**
		 * Chequea si el picklist existe
		 *
		 * @param $picklistName
		 *
		 * @return array|null
		 */
		private function checkIfPicklistExists ($picklistName) {
			$picklistTableName       = "vtiger_{$picklistName}";
			$picklistIdColumnName    = "{$picklistName}id";
			$picklistValueColumnName = $picklistName;
			if (
				(!DatabaseUtils::checkIfTableExists ($this->adb, $picklistTableName)) ||
				(!DatabaseUtils::checkIfColumnExists ($this->adb, $picklistTableName, $picklistValueColumnName)) ||
				(!DatabaseUtils::checkIfColumnExists ($this->adb, $picklistTableName, $picklistIdColumnName))
			) {
				return null;
			} else {
				return array (
					'tableName'       => $picklistTableName,
					'idColumnName'    => $picklistIdColumnName,
					'valueColumnName' => $picklistValueColumnName,
				);
			}
		}

		/**
		 * Busca el ID de los picklist por su valor
		 *
		 * @param array $data
		 * @param string $value
		 *
		 * @return null|integer
		 */
		private function fetchPicklistIdByValue ($data, $value) {
			$id = null;
			$tableName  = $data ['tableName'];
			$columnId   = $data ['idColumnName'];
			$columnName = $data ['valueColumnName'];

			$result = $this->adb->query (
				"SELECT 
					{$this->adb->sql_escape_string ($columnId)} 
				 FROM 
				 	{$this->adb->sql_escape_string ($tableName)} 
				WHERE 
					{$this->adb->sql_escape_string ($columnName)}='{$value}'"
			);
			if ($this->adb->num_rows ($result) > 0) {
				$row = $this->adb->fetchByAssoc($result, -1, false);
				$id  = $row[ $columnId ];
			}
			DatabaseUtils::closeResult($result);
			$result = null;
			return $id;
		}

		/**
		 * Busca el valor del picklist por su ID
		 *
		 * @param string $tableName
		 * @param string $columnName
		 * @param string $columnId
		 * @param integer $id
		 *
		 * @return string|null
		 */
		private function fetchPicklistValueById ($tableName, $columnName, $columnId, $id) {
			if (empty ($id)) {
				return null;
			}
			$value = null;
			$result = $this->adb->query (
				"SELECT 
					{$this->adb->sql_escape_string ($columnName)} 
				 FROM 
				 	{$this->adb->sql_escape_string ($tableName)} 
				WHERE 
					{$this->adb->sql_escape_string ($columnId)}={$id}"
			);
			if ($this->adb->num_rows ($result) > 0) {
				$row   = $this->adb->fetchByAssoc($result, -1, false);
				$value = $row[ $columnName ];
			}
			DatabaseUtils::closeResult($result);
			$result = null;
			return $value;
		}

		/**
		 * Busca el valor del picklist
		 *
		 * @param string $picklistName
		 *
		 * @return array|null
		 */
		private function fetchPicklistValues ($picklistName) {
			$picklistTableName       = "vtiger_{$picklistName}";
			$picklistIdColumnName    = "{$picklistName}id";
			$picklistValueColumnName = $picklistName;
			if (
				(!DatabaseUtils::checkIfTableExists ($this->adb, $picklistTableName)) ||
				(!DatabaseUtils::checkIfColumnExists ($this->adb, $picklistTableName, $picklistValueColumnName)) ||
				(!DatabaseUtils::checkIfColumnExists ($this->adb, $picklistTableName, $picklistIdColumnName))
			) {
				return null;
			}

			$result = $this->adb->query ("SELECT * FROM {$this->adb->sql_escape_string ($picklistTableName)} WHERE presence=1 ORDER BY {$this->adb->sql_escape_string ($picklistIdColumnName)}");
			if ($this->adb->num_rows ($result) > 0) {
				$values = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$values [$row [ $picklistIdColumnName ]] = $row [ $picklistValueColumnName ];
				}
			} else {
				$values = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $values;
		}

		/**
		 * Busca la relación maestra del picklist
		 *
		 * @param string $motherPicklistName
		 * @param string $daughterPicklistName
		 * @param string $relationshipName
		 *
		 * @return array|null
		 */
		private function fetchRelationshipMaster ($motherPicklistName, $daughterPicklistName, $relationshipName) {
			$picklistTableMotherName         = "vtiger_{$motherPicklistName}";
			$picklistIdMotherColumnName      = "{$motherPicklistName}id";
			$picklistValueMotherColumnName   = $motherPicklistName;
			$picklistTableDaughterName       = "vtiger_{$daughterPicklistName}";
			$picklistIdDaughterColumnName    = "{$daughterPicklistName}id";
			$picklistValueDaughterColumnName = $daughterPicklistName;
			if (
				(!DatabaseUtils::checkIfTableExists ($this->adb, $picklistTableMotherName)) ||
				(!DatabaseUtils::checkIfColumnExists ($this->adb, $picklistTableMotherName, $picklistValueMotherColumnName)) ||
				(!DatabaseUtils::checkIfColumnExists ($this->adb, $picklistTableMotherName, $picklistIdMotherColumnName)) ||
				(!DatabaseUtils::checkIfTableExists ($this->adb, $picklistTableDaughterName)) ||
				(!DatabaseUtils::checkIfColumnExists ($this->adb, $picklistTableDaughterName, $picklistValueDaughterColumnName)) ||
				(!DatabaseUtils::checkIfColumnExists ($this->adb, $picklistTableDaughterName, $picklistIdDaughterColumnName))
			) {
				return null;
			}

			$result = $this->adb->pquery('SELECT * FROM vtiger_master_picklist_relationship WHERE relationshipname=?', array ($relationshipName));
			if ($this->adb->num_rows ($result) > 0) {
				$arrayResults = array();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$values            = array ();
					$relatioshipMaster = PicklistRelationshipMaster::getInstance()
						->setId ($row ['relationid'])
						->setRelationshipName ($row ['relationshipname'])
						->setLocked(($row ['locked']) ? true : false)
						->setMotherPicklistValueId ($row ['motherlistvalueid'])
						->setDaughterPicklistValuesId (json_decode($row ['daughterlistvaluesid']));
					$motherPicklistValue = $this->fetchPicklistValueById ($picklistTableMotherName, $picklistValueMotherColumnName, $picklistIdMotherColumnName, $relatioshipMaster->getMotherPicklistValueId ());
					if (count ($relatioshipMaster->getDaughterPicklistValuesId())) {
						foreach ($relatioshipMaster->getDaughterPicklistValuesId() as $doughterId) {
							$values [ $motherPicklistValue ][] = $this->fetchPicklistValueById ($picklistTableDaughterName, $picklistValueDaughterColumnName, $picklistIdDaughterColumnName, $doughterId);
						}
						$relatioshipMaster->setRelationshipValues ($values);
					}
					$arrayResults [] = $relatioshipMaster;
				}
			} else {
				$arrayResults = null;
			}
			DatabaseUtils::closeResult($result);
			$result = null;
			return $arrayResults;
		}

		/**
		 * Obtiene la relación maestra del picklist por su ID
		 *
		 * @param PicklistRelationshipMaster $masterRelationship
		 * @param string $daughterName
		 * @param array $daughterValues
		 */
		private function getMasterRalationshipIds (&$masterRelationship, $daughterName, $daughterValues) {
			$data = $this->checkIfPicklistExists ($daughterName);
			if(empty ($data)) {
				return;
			}
			$daughterIds = array ();
			foreach ($daughterValues as $daughterValue) {
				$daughterIdValue = $this->fetchPicklistIdByValue($data, $daughterValue);
				if (empty($daughterIdValue)) {
					continue;
				}
				$daughterIds [] = $daughterIdValue;
			}
			$masterRelationship->setDaughterPicklistValuesId ($daughterIds);
		}

		/**
		 * Guarda la relación del picklist maestro
		 *
		 * @param PicklistRelationship  $picklistRelationship
		 *
		 * @throws PicklistRelationshipException
		 */
		private function saveMasterPicklistRelationship ($picklistRelationship) {
			if( empty($picklistRelationship->getPicklistRelationshipMaster())) {
				return;
			}
			foreach ($picklistRelationship->getPicklistRelationshipMaster() as $relationshipMaster) {
				$relationshipMaster->validate ();
				$daughterIds = json_encode ($relationshipMaster->getDaughterPicklistValuesId());
				$locked      = ($relationshipMaster->isLocked ()) ? 1 : 0;
				$this->adb->pquery(
					'INSERT INTO vtiger_master_picklist_relationship (relationshipname, motherlistvalueid, daughterlistvaluesid, locked) VALUE (?, ?, ?, ?)',
					array ($picklistRelationship->getRelationshipName (), $relationshipMaster->getMotherPicklistValueId (), $daughterIds, $locked)
				);
			}
		}

		/**
		 * Chequea si el picklist tiene relaciones asociadas
		 *
		 * @param string $moduleName
		 * @param string $fieldName
		 * @param boolean $motherOnly
		 *
		 * @return boolean|null
		 */
		private function checkIfRelationshipPicklist ($moduleName, $fieldName, $motherOnly = true) {
			$where = "t.name='{$moduleName}' AND motherlistname='{$fieldName}'";
			if (!$motherOnly) {
				$where = "t.name = '{$moduleName}' AND (p2p.daughterlistname='{$fieldName}' OR p2p.motherlistname='{$fieldName}')";
			}

			$result = $this->adb->query (
				"SELECT 
					p2p.* 
				 FROM 
				   vtiger_picklist2picklist p2p
				 INNER JOIN vtiger_tab t ON t.name = p2p.modulename
				 WHERE 
				 	{$where}"
			);
			$isRelationshipPicklist = ($this->adb->num_rows ($result) > 0);
			DatabaseUtils::closeResult($result);
			$result = null;
			return $isRelationshipPicklist;
		}

		/**
		 * Elimina relacion del picklist
		 *
		 * @param string $moduleName
		 * @param string $relationshipName
		 * @param boolean $ignoreLock
		 *
		 * @return null|string
		 */
		public function deleteRelationshipPicklist ($moduleName, $relationshipName, $ignoreLock = true) {
			if (empty ($relationshipName) || empty ($moduleName)) {
				return null;
			}

			if (!$ignoreLock) {
				$whereClause = 'p2p.locked=0 AND';
			} else {
				$whereClause = '';
			}
			$this->adb->pquery (
				"DELETE 
				  	p2p.*,
				  	mpr.*
				FROM 
					vtiger_picklist2picklist p2p
				INNER JOIN vtiger_master_picklist_relationship mpr ON mpr.relationshipname = p2p.relationshipname
				WHERE 
					{$whereClause}
					p2p.relationshipname=? AND
					p2p.modulename=?",
				array ($relationshipName, $moduleName)
			);
			return $relationshipName;
		}

		/**
		 * Busca picklist por modulo
		 *
		 * @param string $moduleName
		 * @param string $motherPicklist
		 *
		 * @return null|array
		 */
		public function fetchPicklistByModule ($moduleName, $motherPicklist) {
			if (empty($moduleName) || empty($motherPicklist)) {
				return null;
			}
		
			// Verificar si el campo madre ya tiene una relación como madre
			$isMother = $this->checkIfRelationshipPicklist ($moduleName, $motherPicklist);
		
			$uiType   = FieldInterface::UI_TYPE_PICKLIST;

			$result = $this->adb->pquery(
				'SELECT 
					f.fieldlabel,
					f.fieldname
				FROM vtiger_field f
				INNER JOIN vtiger_tab t ON t.tabid = f.tabid
				WHERE 
					t.name=? AND 
					f.uitype=? AND
					f.fieldname !=?',
				array ($moduleName, $uiType, $motherPicklist)
			);
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc($result, -1, false)) {
					// Excluir campos que ya son madre de otro campo
					// Pero PERMITIR campos que solo son hija (para permitir cadenas)
					if ($this->checkIfRelationshipPicklist($moduleName, $row ['fieldname'], true)) {
						continue;
					}
					$row ['values'] = $this->fetchPicklistValues ($row ['fieldname']);
					$arrayResults [] = $row;
				}
			}
			DatabaseUtils::closeResult($result);
			$result = null;
			return (isset ($arrayResults)) ? $arrayResults : null;
		}

		/**
		 * Busca las relaciones asociadas al picklist
		 *
		 * @param string|integer $relationship
		 * @param boolean $headersOnly
		 *
		 * @return null|PicklistRelationship
		 */
		public function fetchPicklistRelationship ($relationship, $headersOnly = false) {
			if (empty($relationship)) {
				return null;
			}
			$where = 'p2p.relationshipname=?';
			if (is_numeric ($relationship)) {
				$where = 'p2p.picklist2picklisid=?';
			}

			$result = $this->adb->pquery (
				"SELECT 
					p2p.* 
				 FROM 
				   vtiger_picklist2picklist p2p
				 INNER JOIN vtiger_picklist p ON p.name = p2p.motherlistname
				 WHERE {$where} AND 
				 EXISTS (SELECT name FROM vtiger_picklist WHERE name=p2p.daughterlistname)",
				array ($relationship)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$row = $this->adb->fetchByAssoc($result, -1, false);
				$relationshipObject = PicklistRelationship::getInstance ()
					->setId ($row ['picklist2picklisid'])
					->setDaughterPicklistName ($row ['daughterlistname'])
					->setLocked ($row ['locked'])
					->setModuleName ($row ['modulename'])
					->setMotherPicklistName ($row ['motherlistname'])
					->setRelationshipName ($row ['relationshipname'])
					->setPicklistRelationshipMaster (!($headersOnly) ? $this->fetchRelationshipMaster ($row ['motherlistname'], $row ['daughterlistname'], $row ['relationshipname']) : null);
			} else {
				$relationshipObject = null;
			}
			DatabaseUtils::closeResult($result);
			$result = null;
			return $relationshipObject;
		}

		/**
		 * Busca las relaciones asociadas al picklist por Modulo
		 *
		 * @param string $moduleName
		 * @param string $motherPicklist
		 *
		 * @return PicklistRelationship[]|null
		 */
		public function fetchPicklistRelationshipByModule ($moduleName, $motherPicklist = '') {
			if (empty($moduleName)) {
				return null;
			}
			if (!empty ($motherPicklist)) {
				$motherPicklist = "p2p.motherlistname='{$motherPicklist}' AND ";
			}
			$result = $this->adb->query (
				"SELECT 
					p2p.* 
				 FROM 
				   vtiger_picklist2picklist p2p
				 INNER JOIN vtiger_tab t ON t.name = p2p.modulename
				 INNER JOIN vtiger_picklist p ON p.name = p2p.motherlistname
				 WHERE p2p.modulename='{$moduleName}' AND {$motherPicklist} 
				 EXISTS (SELECT name FROM vtiger_picklist WHERE name=p2p.daughterlistname)"
			);
			if ($this->adb->num_rows ($result) > 0) {
				$arrayResults = array ();
				while ($row = $this->adb->fetchByAssoc($result, -1, false)) {
					$arrayResults [] = PicklistRelationship::getInstance ()
						->setId ($row ['picklist2picklisid'])
						->setDaughterPicklistName ($row ['daughterlistname'])
						->setLocked (($row ['locked']) ? true : false)
						->setModuleName ($row ['modulename'])
						->setMotherPicklistName ($row ['motherlistname'])
						->setRelationshipName ($row ['relationshipname'])
						->setPicklistRelationshipMaster ($this->fetchRelationshipMaster ($row ['motherlistname'], $row ['daughterlistname'], $row ['relationshipname']));
				}
			}
			DatabaseUtils::closeResult($result);
			$result = null;
			return $arrayResults;
		}

		/**
		 * Guarda el picklist y sus relaciones
		 *
		 * @param PicklistRelationship $picklistRelationship
		 * @param boolean $ignoreLock
		 *
		 * @throws PicklistRelationshipException
		 */
		public function savePicklistRelationship ($picklistRelationship, $ignoreLock = true) {
			if (!$picklistRelationship instanceof PicklistRelationship) {
				return;
			}
			$picklistRelationship->validate();

			$locked           = ($picklistRelationship->isLocked ()) ? 1 : 0;
			$thisRelationship = $this->fetchPicklistRelationship ($picklistRelationship->getRelationshipName (), true);
			if (!empty ($thisRelationship)) {
				$locked  = ($thisRelationship->isLocked ()) ? 1 : 0;
			}
			$this->adb->startTransaction ();
			if (empty ($thisRelationship)) {
				$this->adb->pquery(
					'INSERT INTO vtiger_picklist2picklist (modulename, motherlistname, daughterlistname, relationshipname, locked) VALUE (?, ?, ?, ?, ?)',
					array ($picklistRelationship->getModuleName (), $picklistRelationship->getMotherPicklistName (), $picklistRelationship->getDaughterPicklistName(), $picklistRelationship->getRelationshipName (), $locked)
				);
			} else if (($ignoreLock) || (!$locked)) {
				$dummy             = explode ('-', $picklistRelationship->getRelationshipName ());
				$motherAndDaughter = $picklistRelationship->getMotherPicklistName () . '@' . $picklistRelationship->getDaughterPicklistName ();
				if ($dummy [0] != $motherAndDaughter) {
					$picklistRelationship->setRelationshipName ($motherAndDaughter . '-' . $dummy [1]);
				}
				$this->adb->pquery(
					'UPDATE vtiger_picklist2picklist SET modulename=?, motherlistname=?, daughterlistname=?, relationshipname=?, locked=? WHERE relationshipname=?',
					array ($picklistRelationship->getModuleName (), $picklistRelationship->getMotherPicklistName (), $picklistRelationship->getDaughterPicklistName (), $picklistRelationship->getRelationshipName (), $locked, $thisRelationship->getRelationshipName ())
				);
				$this->adb->pquery ('DELETE FROM vtiger_master_picklist_relationship WHERE relationshipname=?', array ($thisRelationship->getRelationshipName ()));
			}
			$this->saveMasterPicklistRelationship ($picklistRelationship);
			$this->adb->completeTransaction ();
		}

		/**
		 * Guarda las relaciones asociadas al picklist
		 *
		 * @param Module $module
		 * @param boolean $ignoreLock
		 *
		 * @throws PicklistRelationshipException
		 */
		public function saveRelationshipPicklits ($module, $ignoreLock = true) {
			if(empty($module) || !$module instanceof Module) {
				return;
			} else if (empty ($module->getPicklistRelationship ())) {
				return;
			}
			foreach ($module->getPicklistRelationship () as $relationshipPicklist) {
				if(empty($relationshipPicklist) || !$relationshipPicklist instanceof PicklistRelationship) {
					continue;
				}

				$dataPicklist = $this->checkIfPicklistExists ($relationshipPicklist->getMotherPicklistName ());
				if(empty ($dataPicklist)) {
					continue;
				}

				foreach ($relationshipPicklist->getPicklistRelationshipMaster () as $masterRelationship) {
					foreach ($masterRelationship->getRelationshipValues () as $motherValue => $daughterValues) {
						$motherIdValue = $this->fetchPicklistIdByValue ($dataPicklist, $motherValue);
						if (empty($motherIdValue)) {
							break;
						}
						$masterRelationship->setMotherPicklistValueId ($motherIdValue);
						$this->getMasterRalationshipIds ($masterRelationship, $relationshipPicklist->getDaughterPicklistName (), $daughterValues);
					}
				}
				$this->savePicklistRelationship ($relationshipPicklist, $ignoreLock);
			}
		}

		/**
		 * Se obtiene un objeto PicklistRelationshipManager con los atributos de la clase
		 *
		 * @param \PearDatabase $adb
		 *
		 * @return mixed|\PicklistRelationshipManager
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
