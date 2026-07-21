<?php
	require_once ('include/platzilla/Managers/PipelineManager.php');
	require_once ('include/platzilla/Objects/FieldInterface.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');

	/**
	 * Class PicklistPipelineRelationshipManager
	 *
	 * Clase donde se definen las utilerías/métodos para gestionar las relaciones de los campos del tipo picklist a pipeline
	 */
	class PicklistPipelineRelationshipManager {
		
		/** @var PicklistPipelineRelationshipManager[]|null */
		private static $INSTANCES = null;

		/** @var PearDatabase */
		private $adb;

		/**
		 * PicklistPipelineRelationshipManager constructor.
		 *
		 * @param \PearDatabase $adb
		 */
		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
		}

		/**
		 * Obtiene el ID del valor del picklist por su valor
		 *
		 * @param string $picklistName
		 * @param string $value
		 *
		 * @return null|integer
		 */
		private function fetchPicklistValueId ($picklistName, $value) {
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

			$id = null;
			$result = $this->adb->pquery (
				"SELECT 
					{$this->adb->sql_escape_string ($picklistIdColumnName)} 
				 FROM 
				 	{$this->adb->sql_escape_string ($picklistTableName)} 
				WHERE 
					{$this->adb->sql_escape_string ($picklistValueColumnName)}=?",
				array ($value)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$row = $this->adb->fetchByAssoc($result, -1, false);
				$id  = $row[ $picklistIdColumnName ];
			}
			DatabaseUtils::closeResult($result);
			$result = null;
			return $id;
		}

		/**
		 * Obtiene el valor del picklist por su ID
		 *
		 * @param string $picklistName
		 * @param integer $id
		 *
		 * @return string|null
		 */
		private function fetchPicklistValueById ($picklistName, $id) {
			if (empty ($id)) {
				return null;
			}
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

			$value = null;
			$result = $this->adb->pquery (
				"SELECT 
					{$this->adb->sql_escape_string ($picklistValueColumnName)} 
				 FROM 
				 	{$this->adb->sql_escape_string ($picklistTableName)} 
				WHERE 
					{$this->adb->sql_escape_string ($picklistIdColumnName)}=?",
				array ($id)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$row   = $this->adb->fetchByAssoc($result, -1, false);
				$value = $row[ $picklistValueColumnName ];
			}
			DatabaseUtils::closeResult($result);
			$result = null;
			return $value;
		}

		/**
		 * Obtiene los nombres de campos pipeline que tienen relación con picklists en el módulo
		 *
		 * @param string $moduleName
		 *
		 * @return array Array de nombres de campos pipeline
		 */
		public function getPipelinesWithRelationship ($moduleName) {
			if (empty ($moduleName)) {
				return array ();
			}
			$result = $this->adb->pquery (
				"SELECT DISTINCT pipelinefieldname
				 FROM vtiger_picklist2pipeline
				 WHERE modulename=?",
				array ($moduleName)
			);
			$pipelines = array ();
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$pipelines [] = $row ['pipelinefieldname'];
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $pipelines;
		}

		/**
		 * Verifica si un pipeline tiene relación con un picklist específico
		 *
		 * @param string $moduleName
		 * @param string $pipelineFieldName
		 * @param string|null $specificPicklist Nombre del picklist específico a verificar (si es null, verifica si tiene relación con cualquier picklist)
		 *
		 * @return boolean
		 */
		public function isPipelineRelated ($moduleName, $pipelineFieldName, $specificPicklist = null) {
			if (empty ($moduleName) || empty ($pipelineFieldName)) {
				return false;
			}
			$whereClause = '';
			$params = array ($moduleName, $pipelineFieldName);
			
			if ($specificPicklist) {
				// Verificar si está relacionado con el picklist específico
				$whereClause = "AND motherpicklistname = ?";
				$params [] = $specificPicklist;
			}
			
			$result = $this->adb->pquery (
				"SELECT 1 FROM vtiger_picklist2pipeline
				 WHERE modulename=? AND pipelinefieldname=? {$whereClause} LIMIT 1",
				$params
			);
			$hasRelation = ($this->adb->num_rows ($result) > 0);
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $hasRelation;
		}

		/**
		 * Busca las relaciones asociadas al picklist por módulo
		 *
		 * @param string $moduleName
		 * @param string $motherPicklist
		 *
		 * @return array|null
		 */
		public function fetchPicklistPipelineRelationshipByModule ($moduleName, $motherPicklist = '') {
			if (empty($moduleName)) {
				return null;
			}
			if (!empty ($motherPicklist)) {
				$motherPicklist = "p2p.motherpicklistname='{$motherPicklist}' AND ";
			}
			$result = $this->adb->query (
				"SELECT 
					p2p.* 
				 FROM 
				   vtiger_picklist2pipeline p2p
				 INNER JOIN vtiger_tab t ON t.name = p2p.modulename
				 WHERE p2p.modulename='{$moduleName}' AND {$motherPicklist} 
				 EXISTS (SELECT fieldname FROM vtiger_field WHERE tabid = t.tabid AND uitype = 8192 AND fieldname = p2p.pipelinefieldname)"
			);
			if ($this->adb->num_rows ($result) > 0) {
				$arrayResults = array ();
				while ($row = $this->adb->fetchByAssoc($result, -1, false)) {
					$arrayResults [] = $row;
				}
			}
			DatabaseUtils::closeResult($result);
			$result = null;
			return (isset ($arrayResults)) ? $arrayResults : null;
		}

		/**
		 * Guarda la relación del picklist a pipeline
		 *
		 * @param string $moduleName
		 * @param string $motherPicklistName
		 * @param integer $motherPicklistFieldId
		 * @param string $pipelineFieldName
		 * @param integer $pipelineFieldId
		 * @param array $pickListPipelineRelationships (array con motherlistvalue como clave y array de valores visibles como valor)
		 * @param string $relationshipName
		 * @param boolean $locked
		 *
		 * @throws Exception
		 */
		public function savePicklistPipelineRelationship ($moduleName, $motherPicklistName, $motherPicklistFieldId, $pipelineFieldName, $pipelineFieldId, $pickListPipelineRelationships, $relationshipName = null, $locked = false) {
			if (empty ($moduleName) || empty ($motherPicklistName) || empty ($pipelineFieldName)) {
				throw new Exception ('Faltan parámetros requeridos');
			}

			// Generar nombre de relación si no existe
			if (empty ($relationshipName)) {
				$relationshipName = $motherPicklistName . '@' . $pipelineFieldName . '-' . $pipelineFieldId . '-' . rand (1, 9999);
			}

			$this->adb->startTransaction ();

			// Verificar si ya existe la relación
			$result = $this->adb->pquery (
				'SELECT * FROM vtiger_picklist2pipeline WHERE relationshipname=?',
				array ($relationshipName)
			);

			$lockedValue = ($locked) ? 1 : 0;
			
			// Si existe y no está bloqueada, eliminar los registros anteriores
			if ($this->adb->num_rows ($result) > 0) {
				$row = $this->adb->fetchByAssoc($result, -1, false);
				$existingLocked = ($row ['locked']) ? true : false;
				
				if (!$existingLocked || $locked) {
					$this->adb->pquery (
						'DELETE FROM vtiger_picklist2pipeline WHERE relationshipname=?',
						array ($relationshipName)
					);
				} else {
					DatabaseUtils::closeResult($result);
					$this->adb->completeTransaction ();
					throw new Exception ('La relación está bloqueada y no puede modificarse');
				}
			}
			DatabaseUtils::closeResult($result);

			// Insertar los nuevos registros
			foreach ($pickListPipelineRelationships as $motherValue => $visiblePipelineValues) {
				$visibleValuesJson = json_encode ($visiblePipelineValues);
				$this->adb->pquery (
					'INSERT INTO vtiger_picklist2pipeline (modulename, motherpicklistname, motherpicklistfieldid, motherlistvalue, pipelinefieldname, pipelinefieldid, pipelinevaluesvisible, relationshipname, locked) VALUE (?, ?, ?, ?, ?, ?, ?, ?, ?)',
					array ($moduleName, $motherPicklistName, $motherPicklistFieldId, $motherValue, $pipelineFieldName, $pipelineFieldId, $visibleValuesJson, $relationshipName, $lockedValue)
				);
			}

			$this->adb->completeTransaction ();
		}

		/**
		 * Elimina relación del picklist a pipeline
		 *
		 * @param string $moduleName
		 * @param string $relationshipName
		 * @param boolean $ignoreLock
		 *
		 * @return null|string
		 */
		public function deleteRelationshipPicklistPipeline ($moduleName, $relationshipName, $ignoreLock = true) {
			if (empty ($relationshipName) || empty ($moduleName)) {
				return null;
			}

			if (!$ignoreLock) {
				$whereClause = 'locked=0 AND';
			} else {
				$whereClause = '';
			}
			$this->adb->pquery (
				"DELETE FROM vtiger_picklist2pipeline WHERE {$whereClause} relationshipname=? AND modulename=?",
				array ($relationshipName, $moduleName)
			);
			return $relationshipName;
		}

		/**
		 * Elimina relación del picklist a pipeline por nombre de campos
		 *
		 * @param string $moduleName
		 * @param string $picklistFieldName
		 * @param string $pipelineFieldName
		 * @param boolean $ignoreLock
		 *
		 * @return null|string
		 */
		public function deleteRelationshipPicklistPipelineByFields ($moduleName, $picklistFieldName, $pipelineFieldName, $ignoreLock = true) {
			if (empty ($picklistFieldName) || empty ($pipelineFieldName) || empty ($moduleName)) {
				return null;
			}

			// Obtener el relationshipname de la relación existente
			$result = $this->adb->pquery (
				'SELECT DISTINCT relationshipname FROM vtiger_picklist2pipeline WHERE modulename=? AND motherpicklistname=? AND pipelinefieldname=? LIMIT 1',
				array ($moduleName, $picklistFieldName, $pipelineFieldName)
			);

			if ($this->adb->num_rows ($result) === 0) {
				DatabaseUtils::closeResult($result);
				$result = null;
				return null;
			}

			$row = $this->adb->fetchByAssoc($result, -1, false);
			$relationshipName = $row['relationshipname'];
			DatabaseUtils::closeResult($result);
			$result = null;

			// Eliminar la relación usando el método existente
			return $this->deleteRelationshipPicklistPipeline ($moduleName, $relationshipName, $ignoreLock);
		}

		/**
		 * Se obtiene un objeto PicklistPipelineRelationshipManager con los atributos de la clase
		 *
		 * @param \PearDatabase $adb
		 *
		 * @return mixed|\PicklistPipelineRelationshipManager
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
