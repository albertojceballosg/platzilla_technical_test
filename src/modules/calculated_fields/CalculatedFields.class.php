<?php
	require_once ('log4php/LoggerManager.php');
	require_once ('include/platzilla/Data/EntityHistoryManager.php');
	require_once ('include/platzilla/Managers/FieldManager.php');
	require_once ('include/platzilla/Managers/CalculationElementManager.php');
	require_once ('include/platzilla/Managers/CalculationSystemManager.php');
	require_once ('include/platzilla/Objects/FieldInterface.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/GridFieldUtils.class.php');
	require_once ('include/utils/PlatformUtils.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/calculated_fields/lib/CalculatedFieldsHelper.class.php');

	/**
	 * Class CalculatedFieldsUtils
	 *
	 * En esta clase se implementa los métodos necesarios del motor de cálculos en el sistema
	 */
	class CalculatedFieldsUtils {

		private $adb;

		private $logger;

		private $platform;

		private $valuesInGrid = null;

	private $currentOperation = null;

		/**
		 * CalculatedFieldsUtils constructor.
		 *
		 * @param PearDatabase $adb
		 * @param $platform
		 */
		public function __construct (PearDatabase $adb, $platform) {
			$this->platform = $platform;
			if (!is_dir (__DIR__ . "/../../{$this->platform}/logs/calculatedsystem")) {
				mkdir (__DIR__ . "/../../{$this->platform}/logs/calculatedsystem", 0777, true);
			}
			$this->adb = $adb;
		}

		/**
		 * Obtiene el resultado de la ejecución de la ecuación del cálculo
		 *
		 * @param CalculationEquation[] $equations
		 * @param integer $record
		 *
		 * @return string
		 */
		private function equationResult ($equations, $record = 0) {
			$results       = array ();
			$resultsGroup  = array ();
			$equationValue = '';
			$itemSeparator = ' ';
			foreach ($equations as $element) {
				$dataOne = $this->getElementValue ($element->getFirstElementType (), $element->getFirstElement (), $element->getId (), $record);
				$dataTwo = $this->getElementValue ($element->getSecondElementType (), $element->getSecondElement (), $element->getId (), $record);
				$element->setOperation (($element->getOperation () == 'x') ? '*' : $element->getOperation ());

				if (is_array ($dataOne)) {
					// Preservar datos de grid para procesamiento posterior
					if ($this->valuesInGrid === null) {
						$this->valuesInGrid = $dataOne;
					}
					$dataOne = '_IN_ARRAY_';
				} else if (is_array ($dataTwo)) {
					// Preservar datos de grid para procesamiento posterior
					if ($this->valuesInGrid === null) {
						$this->valuesInGrid = $dataTwo;
					}
					$dataTwo = '_IN_ARRAY_';
				}

				$results[ $element->getIndexGroup () ]      = "( {$dataOne} {$element->getOperation ()} {$dataTwo} )";
				$resultsGroup[ $element->getIndexGroup () ] = array (
					'results'  => $results[ $element->getIndexGroup () ],
					'operator' => $element->getOperationGroup (),
				);
			}
			foreach ($resultsGroup as $item) {
				$equationValue .= implode (' ', $item) . $itemSeparator;
			}
			$equationValue = substr ($equationValue, 0, -2);
			return $equationValue;
		}

		/**
		 * @deprecated This method has been replaced by getEnhancedCalculateSystemResult
		 * Kept for potential backward compatibility, but should not be used
		 */
		private function getCalculateSystemResult ($moduleName, $sql, $record, $relModules) {
			// Redirect to enhanced version
			return $this->getEnhancedCalculateSystemResult($moduleName, $sql, $record, $relModules);
		}


		/**
		 * Enhanced version that includes uitype=10 fields and UNION queries
		 * @param string $moduleName
		 * @param string $sql
		 * @param integer $record
		 * @param string $relModules
		 * @return integer
		 */
		private function getEnhancedCalculateSystemResult ($moduleName, $sql, $record, $relModules) {
			$record  = (!$record) ? $this->getLastModuleRecord ($moduleName) : $record;
			$results = 0;
			$modules = (!empty($relModules)) ? explode (';', $relModules) : null;
			$entity  = PlatformUtils::getCrmEntity ($this->adb, $moduleName);


			if (!empty ($entity)) {
				$actualDieOnError = $this->adb->dieOnError;
				$this->adb->setDieOnError (false);

				// Check if SQL really needs related modules before building joins
				$needsRelatedModules = false;
				if (!empty($relModules)) {
					foreach ($modules as $module) {
						if (preg_match("/vtiger_{$module}\.|{$module}\./", $sql)) {
							$needsRelatedModules = true;
							break;
						}
					}
				}
				
				// Use enhanced join clause only if really needed
				$joinClause = ($needsRelatedModules) ? $this->getEnhancedJoinClause ($modules, $sql, $entity, $record) : '';
				
				// Build WHERE condition: if we need related modules, the join clause includes the record filter
				// If no related modules needed, we filter by the specific record
				if ($needsRelatedModules) {
					$where = 1; // Enhanced join already includes record filter
				} else {
					$where = "{$entity->table_name}.{$entity->table_index} = $record";
				}

				// Build main query
				$mainQuery = "SELECT {$sql} AS value FROM {$entity->table_name} INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = {$entity->table_name}.{$entity->table_index} AND vtiger_crmentity.deleted = 0 {$joinClause} WHERE {$where}";
				$query = $this->adb->query($mainQuery);
				$numOfRows  = $this->adb->num_rows ($query);

				if ($numOfRows > 0) {
					while ($row = $this->adb->fetchByAssoc ($query)) {
						$results = $row['value'];
					}
				}

				$this->adb->setDieOnError ($actualDieOnError);
			}

			return $results;
		}

		/**
		 * Obtiene la estructura de la ecuación
		 *
		 * @param array $data
		 * @param integer $equationId
		 *
		 * @return array
		 */
		private function getEquations ($data, $equationId) {
			$equation         = $data ['equation'];
			$calculatedGroups = $data ['calculatedGroups'];
			$totalGroups      = count ($equation ['typeFirstElement']);
			$equations        = array ();
			for ($g = 0; $g < $totalGroups; $g++) {
				$firstType = $equation ['typeFirstElement'][ $g ];
				$operator  = $equation ['operator'][ $g ];
				if ($firstType == 'e') {
					$firstEle = $equation ['firstElement'][ $g ];
				} else if ($firstType == 'c') {
					$firstEle = $equation ['firstField'][ $g ];
				} else if ($firstType == 'v') {
					$firstEle = $equation ['firstValue'][ $g ];
				} else {
					$searchResult = array_search ($equation ['firstReference'][ $g ], $calculatedGroups);
					$firstEle = ($searchResult !== false) ? (string) $searchResult : 'Grupo no definido';
				}

				$secondType    = $equation ['typeSecondElement'][ $g ];
				$operatorGroup = $equation ['operatorGroup'][ $g ];
				if ($secondType == 'e') {
					$secondEle = $equation ['secondElement'][ $g ];
				} else if ($secondType == 'v') {
					$secondEle = $equation ['secondValue'][ $g ];
				} else if ($secondType == 'c') {
					$secondEle = $equation ['secondField'][ $g ];
				} else {
					$searchResult = array_search ($equation ['secondReference'][ $g ], $calculatedGroups);
					$secondEle = ($searchResult !== false) ? (string) $searchResult : 'Grupo no definido';
				}
				$equations [] = CalculationEquation::getInstance ()
					->setId ($equationId)
					->setFirstElement ($firstEle)
					->setFirstElementType ($firstType)
					->setIndexGroup ($g)
					->setOperationGroup ($operatorGroup)
					->setOperation ($operator)
					->setSecondElement ($secondEle)
					->setSecondElementType ($secondType);
			}
			return $equations;
		}

		/**
		 * Obtiene las cláusula Join que forman parte de la estructura de la ecuación
		 *
		 * @param array $relModules
		 * @param string $sql
		 * @param CRMEntity $entity
		 *
		 * @return string
		 */
		private function getJoinClause ($relModules, $sql, $entity) {
			$joinClauseMain = "INNER JOIN vtiger_crmentityrel ON vtiger_crmentityrel.crmid = {$entity->table_name}.{$entity->table_index}  ";
			$joinClauseRel  = '';
			foreach ($relModules as $theModule) {
				if (preg_match ("/{$theModule}/", $sql)) {
					$entityModule = PlatformUtils::getCrmEntity ($this->adb, $theModule);
					$joinClauseRel .= "INNER JOIN {$entityModule->table_name} ON {$entityModule->table_name}.{$entityModule->table_index} = vtiger_crmentityrel.relcrmid  ";
				}
			}
			$joinClause = (!empty($joinClauseRel)) ? $joinClauseMain . $joinClauseRel : '';
			return $joinClause;
		}

		/**
		 * Obtiene el último record del módulo que solicita el cálculo del sistema
		 *

		/**
		 * Enhanced version of getJoinClause that includes uitype=10 fields like get_related_list does
		 * @param array $relModules
		 * @param string $sql
		 * @param CRMEntity $entity
		 * @param integer $recordId
		 * @return string
		 */
		private function getEnhancedJoinClause ($relModules, $sql, $entity, $recordId) {
			// Main join using vtiger_crmentityrel (bidirectional)
			$joinClauseMain = "INNER JOIN vtiger_crmentityrel ON (vtiger_crmentityrel.relcrmid = {$entity->table_name}.{$entity->table_index} OR vtiger_crmentityrel.crmid = {$entity->table_name}.{$entity->table_index})";
			$joinClauseRel = '';

			// Add joins for related modules that are actually used in the SQL
			foreach ($relModules as $theModule) {
				// Check if module is referenced in SQL (either as vtiger_module or just module)
				if (preg_match("/vtiger_{$theModule}\.|{$theModule}\./", $sql)) {
					$entityModule = PlatformUtils::getCrmEntity ($this->adb, $theModule);
					if ($entityModule) {
						$joinClauseRel .= "INNER JOIN {$entityModule->table_name} ON ({$entityModule->table_name}.{$entityModule->table_index} = vtiger_crmentityrel.relcrmid OR {$entityModule->table_name}.{$entityModule->table_index} = vtiger_crmentityrel.crmid) ";
					}
				} else {
					error_log("[modules\calculated_fields\CalculatedFields.class.php::getEnhancedJoinClause] Skipping module not used in SQL: $theModule");
				}
			}

			// Add WHERE condition for the specific record
			$whereCondition = " AND (vtiger_crmentityrel.crmid = $recordId OR vtiger_crmentityrel.relcrmid = $recordId)";

			$joinClause = (!empty($joinClauseRel)) ? $joinClauseMain . $joinClauseRel . $whereCondition : '';
			return $joinClause;
		}


		/**
		 * Get additional UNION queries for uitype=10 fields (like get_related_list does)
		 * @param string $currentModule
		 * @param string $relatedModule
		 * @param integer $recordId
		 * @return string
		 */
		private function getUiType10UnionQueries ($currentModule, $relatedModule, $recordId) {
			$unionQueries = '';
			
			// Get uitype=10 fields that reference from relatedModule to currentModule
			$result = $this->adb->pquery (
				'SELECT
					vtiger_field.fieldid,
					vtiger_field.tablename,
					vtiger_field.columnname
				FROM
					vtiger_fieldmodulerel
					INNER JOIN vtiger_field ON vtiger_fieldmodulerel.fieldid=vtiger_field.fieldid
				WHERE
					vtiger_fieldmodulerel.module=? AND
					vtiger_fieldmodulerel.relmodule=?',
				array ($relatedModule, $currentModule)
			);

			if ($result && $this->adb->num_rows($result) > 0) {
				while ($row = $this->adb->fetchByAssoc($result)) {
					$relatedEntity = PlatformUtils::getCrmEntity($this->adb, $relatedModule);
					$tableName = $row['tablename'];
					$columnName = $row['columnname'];
					$tableIndex = $relatedEntity->tab_name_index[$tableName];

					$unionQueries .= " UNION SELECT vtiger_crmentity.*, {$tableName}.* ";
					$unionQueries .= " FROM vtiger_crmentity INNER JOIN {$tableName} ";
					$unionQueries .= " ON (vtiger_crmentity.crmid = {$tableName}.{$tableIndex} AND vtiger_crmentity.deleted = 0) ";
					$unionQueries .= " WHERE {$tableName}.{$columnName} = $recordId";
				}
			}

			return $unionQueries;
		}

		/**
		 *
		 * @return array
		 * @throws Exception
		 */
		private function getLastModuleRecord ($moduleName) {
			$entity  = PlatformUtils::getCrmEntity ($this->adb, $moduleName);
			$results = null;
			if ($entity) {
				$query     = $this->adb->pquery (
					"SELECT
						cm.{$entity->table_index}
					FROM
						{$entity->table_name} cm
						INNER JOIN vtiger_crmentity crm ON crm.crmid=cm.{$entity->table_index}
					WHERE
						crm.setype=? AND
						crm.deleted=?
					ORDER BY
						cm.{$entity->table_index} DESC
					LIMIT 1",
					array ($moduleName, '0')
				);
				$numOfRows = $this->adb->num_rows ($query);
				if ($numOfRows > 0) {
					while ($row = $this->adb->fetchByAssoc ($query)) {
						$results = $row [ $entity->table_index ];
					}
				}
			}
			return $results;
		}

		/**
		 * Obtiene las condiciones se aplicarán en el cálculo del sistema
		 *
		 * @param CalculationElement $element
		 * @param $record
		 *
		 * @return string
		 * @throws Exception
		 */
		private function getConditionValue ($element, $record) {
			$recordValue = '';
			list($relModule, $tableRelModule, $fieldRelModule) = explode ('.', $element->getRelatedModules ());
			
			// Manejar campo especial "registro actual"
			if ($fieldRelModule === 'current_record_id') {
				return $record;
			}
			
			// OPTIMIZACIÓN: Detectar campos grid calculados (uitype 2204) para usar tablas summary
			if ($tableRelModule === 'vtiger_subfields_special') {
				// Verificar si es un campo calculado de grid (uitype 2204)
				$isCalculatedGridField = $this->isCalculatedGridField($fieldRelModule);
				if ($isCalculatedGridField) {
					// Es un campo calculado de grid - usar tabla summary optimizada
					return $record; // Usar registro actual para JOIN con tabla summary
				}
			}
			
			// Map display names to technical module names
			$moduleNameMap = array(
				'Planes de servicios' => 'plan_de_mantenimiento',
				'Ordenes de trabajo' => 'orden_de_trabajo',
				'Contratos de servicio' => 'contratos_de_servicio'
			);
			
			// Use mapped name if available, otherwise use original
			$technicalModuleName = isset($moduleNameMap[$relModule]) ? $moduleNameMap[$relModule] : $relModule;
			
			
			$entityModule = PlatformUtils::getCrmEntity ($this->adb, $technicalModuleName);
			if ($element->getModuleName () == $relModule) {
				$query     = $this->adb->query ("SELECT  {$fieldRelModule}  FROM {$tableRelModule} WHERE {$entityModule->table_index} = {$record}");
				$numOfRows = $this->adb->num_rows ($query);
				if ($numOfRows > 0) {
					while ($row = $this->adb->fetchByAssoc ($query)) {
						$recordValue = $row[ $fieldRelModule ];
					}
				}
			} else if ($this->isCrmEntity ($record)) {
				// For cross-module relationships, we need to get the field value from the target record
				// The record ID we have is from the source module, we need to return it as the condition value
				
				// Check if this is a direct field reference (like contrato_, contrato)
				// In most cases, we want to use the record ID directly as the condition value
				if (strpos($fieldRelModule, 'contrato') !== false || 
					strpos($fieldRelModule, 'plan_de_servicios') !== false ||
					strpos($fieldRelModule, '_id') !== false) {
					// Direct relationship field - use record ID directly
					$recordValue = $record;
				} else {
					// Complex relationship - try to get the actual field value
				$entity    = PlatformUtils::getCrmEntity ($this->adb, $element->getModuleName ());
					if ($entity && $entityModule) {
				$query     = $this->adb->query (
					"SELECT
							{$tableRelModule}.{$fieldRelModule}
						  FROM
						  	{$entity->table_name}
						  INNER JOIN vtiger_crmentityrel ON vtiger_crmentityrel.crmid = {$entity->table_name}.{$entity->table_index}
						  INNER JOIN {$tableRelModule} ON {$tableRelModule}.{$entityModule->table_index} = vtiger_crmentityrel.relcrmid
						  WHERE
						  {$entity->table_name}.{$entity->table_index} = {$record}"
				);
				$numOfRows = $this->adb->num_rows ($query);
				if ($numOfRows > 0) {
					while ($row = $this->adb->fetchByAssoc ($query)) {
						$recordValue = $row[ $fieldRelModule ];
					}
						} else {
							// Fallback to record ID if query fails
							$recordValue = $record;
						}
					} else {
						// Entity not found, use record ID as fallback
						$recordValue = $record;
					}
				}
			} else {
				$recordValue = $record;
			}
			return $recordValue;
		}

		/**
		 * Elimina caracteres especiales en el nombre del cálculo del sistema
		 *
		 * @param string $string
		 *
		 * @return string
		 */
		private function sanitizeString ($string) {
			$string = str_replace (
				array ('á', 'á', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
				array ('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
				$string
			);
			$string = str_replace (
				array ('é', 'é', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
				array ('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
				$string
			);
			$string = str_replace (
				array ('í', 'í', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
				array ('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
				$string
			);
			$string = str_replace (
				array ('ó', 'ó', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
				array ('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
				$string
			);
			$string = str_replace (
				array ('ú', 'ú', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
				array ('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
				$string
			);
			$string = str_replace (
				array ('ñ', 'Ñ', 'ç', 'Ç'),
				array ('n', 'N', 'c', 'C'),
				$string
			);

			$string   = str_replace (
				array ('·', '$', '%', '&', '/', '(', ')', '?', '¡', '¿', '[', '^', ']', '+', '}', '{', '¨', '´', '>', '< ', ';', ',', ':', '.', ' )', ' '),
				'_',
				$string
			);
			$string   = substr (strtolower ($string), 0, 12);
			$randomId = rand (100, 999);
			return $string . $randomId;
		}

		/**
		 * Actualiza los cálculo del sistema basado en el record id del módulo
		 *
		 * @param $id
		 * @param $record
		 * @param $moduleName
		 * @param $calculateValue
		 * @param $userId
		 *
		 * @return null|array
		 * @throws Exception
		 */
		private function updateCalculateByRecord ($id, $record, $moduleName, $calculateValue, $userId) {
			if (empty($id)) {
				return null;
			}
			$entity = PlatformUtils::getCrmEntity ($this->adb, $moduleName);
			if ($entity) {
				$query     = $this->adb->pquery ('SELECT tablename, columnname, tabid, fieldid, fieldname FROM vtiger_field WHERE paradicional=?', array ($id));
				$numOfRows = $this->adb->num_rows ($query);
				if ($numOfRows > 0) {
					$row     = $this->adb->fetchByAssoc ($query);
					$results = $this->adb->query ("SELECT {$row['columnname']} FROM {$row['tablename']} WHERE {$entity->table_index} = {$record}");
					if ($this->adb->num_rows ($results) > 0) {
						$recordValue = $this->adb->fetchByAssoc ($results);
						$oldValue    = $recordValue [ $row['columnname'] ];
					} else {
						$oldValue = 0;
					}

					$this->adb->query ("UPDATE {$row['tablename']} SET {$row['columnname']} = {$calculateValue} WHERE {$entity->table_index} = {$record}");
					$calculateNewValue = ((is_int (intval ($oldValue))) && (intval ($oldValue) > 0)) ? round ($calculateValue, 0, PHP_ROUND_HALF_UP) : $calculateValue;

					if ($oldValue != $calculateValue) {
						$histories [] = EntityHistory::getInstance ()
							->setId (0)
							->setCreatedDate (date ('Y-m-d h:i:s'))
							->setFieldId ($row ['fieldid'])
							->setModuleId ($row ['tabid'])
							->setModifiedBy ($userId)
							->setModifiedOn (1)
							->setNewValue ($calculateNewValue)
							->setOldValue ($oldValue)
							->setRegistryId ($record);
						$updated [] = array (
							'module'    => $moduleName,
							'record'    => $record,
							'fieldname' => $row ['fieldname'],
							'calculated' => $calculateValue,
						);

						try {
							EntityHistoryManager::getInstance ($this->adb)->saveAllEntityHistory ($histories);
						} catch (Exception $e) {
							$_SESSION ['flashmessage'] = array (
								'iserror' => true,
								'message' => $e->getMessage (),
								'data'    => null,
							);
						}
					}
				}
			}
			return (isset($updated)) ? $updated : null;
		}

		/**
		 * Obtiene el valor de los elementos de cálculo que forman parte del cálculo del sistema
		 *
		 * @param $elementType
		 * @param $element
		 * @param $equationId
		 * @param integer $record
		 *
		 * @return array|integer|string
		 */
		private function getElementValue ($elementType, $element, $equationId, $record = 0) {
			switch ($elementType) {
				case 'v':
					$results = $element;
					break;
				case 'c':
					$needlePos = strpos ($element, '@');
					if ($needlePos !== false) {
						list($field, $dividend) = explode ('@', $element);
						$results = ($dividend == 9) ? "( {$field}/ 100 )" : $field;
					} else {
						$results = $element;
					}
					break;
				case 'e':
					$results = $this->getCalculationElementByName ($element, $record);
					break;
				case 'r':
					$results = $this->getReferenceValue ($element, $equationId, $record);
					break;
				default:
					$results = 0;
					break;
			}
			return $results;
		}

		/**
		 * Obtiene la estructura de la ecuación aplicar en el cálculo del sistema
		 *
		 * @param $fields
		 * @param $values
		 * @param $operators
		 *
		 * @return string
		 */
		private function getEquation ($fields, $values, $operators) {
			$equation   = '';
			$typeOfData = array (
				'V'  => array ('e' => ' LIKE "@"', 'n' => ' NOT LIKE "@"', 's' => ' LIKE "@%"', 'ew' => ' LIKE "%@"', 'c' => ' LIKE "%@%"', 'k' => ' NOT LIKE "%@%"'),
				'N'  => array ('e' => ' = ', 'n' => ' != ', 'l' => ' < ', 'g' => ' > ', 'm' => ' <= ', 'h' => ' >= '),
				'T'  => array ('e' => ' = "@"', 'n' => ' != "@"', 'l' => ' < "@"', 'g' => ' > "@"', 'm' => ' <= "@"', 'h' => ' >= "@"', 'bw' => ' * BETWEEN DATE( "@" ) AND DATE( "_"  )', 'b' => ' * < DATE( "@" )', 'a' => ' * > DATE( "@" )'),
				'I'  => array ('e' => ' = ', 'n' => ' != ', 'l' => ' < ', 'g' => ' > ', 'm' => ' <= ', 'h' => ' >= '),
				'C'  => array ('e' => ' = ', 'n' => ' != '),
				'D'  => array ('e' => ' = "@"', 'n' => ' != "@"', 'l' => ' < "@"', 'g' => ' > "@"', 'm' => ' <= "@"', 'h' => ' >= "@"', 'bw' => ' * BETWEEN DATE( "@" ) AND DATE( "_"  )', 'b' => ' * < DATE( "@" )', 'a' => ' * > DATE( "@" )'),
				'DT' => array ('e' => ' = "@"', 'n' => ' != "@"', 'l' => ' < "@"', 'g' => ' > "@"', 'm' => ' <= "@"', 'h' => ' >= "@"', 'bw' => ' * BETWEEN DATE( "@" ) AND DATE( "_"  )', 'b' => ' * < DATE( "@" )', 'a' => ' * > DATE( "@" )'),
				'NN' => array ('e' => ' = ', 'n' => ' != ', 'l' => ' < ', 'g' => ' > ', 'm' => ' <= ', 'h' => ' >= '),
				'E'  => array ('e' => ' LIKE "@"', 'n' => ' NOT LIKE "@"', 's' => ' LIKE "@%"', 'ew' => ' LIKE "%@"', 'c' => ' LIKE "%@%"', 'k' => ' NOT LIKE "%@%"'),
			);

			list($fieldType, $fieldName) = explode ('@', $fields);
			list($min, $max) = explode (',', $values);

			$operated = $typeOfData[ $fieldType ][ $operators ];

			$posValue = strripos ($operated, '@');
			if ($posValue !== false) {
				$operated = str_replace ('@', $min, $operated);
				if (!empty($max)) {
					$operated = str_replace ('_', $max, $operated);
				}
			}
			$posField = strripos ($operated, '*');
			if ($posField !== false) {
				$equation .= str_replace ('*', $fieldName, $operated);
			} else if ($posValue === false) {
				$equation .= $fieldName . $operated . $min;
			} else {
				$equation .= $fieldName . $operated;
			}
			return $equation;
		}

		/**
		 * Obtiene el valor de los elementos de cálculo referenciados
		 *
		 * @param $groupId
		 * @param $equationId
		 *
		 * @return integer|string
		 * @throws Exception
		 */
		private function getReferenceValue ($groupId, $equationId, $record = 0) {
			$referenceValue = 0;
			$result         = $this->adb->pquery (
				'SELECT * FROM vtiger_calculated_equation WHERE calculated_equationid=? AND groupindex=?',
				array ($equationId, $groupId)
			);
			$numOfRows      = $this->adb->num_rows ($result);
			if ($numOfRows > 0) {
				$row              = $this->adb->fetchByAssoc ($result);
				$dataOne          = $this->getElementValue ($row['firstelemettype'], $row['firstelement'], $row['calculated_equationid'], $record);
				$dataTwo          = $this->getElementValue ($row['secondelementtype'], $row['secondelement'], $row['calculated_equationid'], $record);
				$row['operation'] = ($row['operation'] == 'x') ? '*' : $row['operation'];
				$referenceValue   = "( {$dataOne} {$row['operation']} {$dataTwo} )";
			}
			return $referenceValue;
		}
		
		/**
		 * @param CalculationElement $theElement
		 * @param CRMEntity $entity
		 *
		 * @return string
		 */
		private function getJoinClauseToElement ($theElement, $entity, $where) {
			$joinClause    = '';
			$processTables = array ('vtiger_crmentity');
			if (!preg_match ("/{$entity->table_name}/", $theElement->getColumnName ())) {
				list ($tableName, $field) = explode ('.', $theElement->getColumnName ());
				$relatedField             = CalculatedFieldsHelper::getFieldRelFromTable ($this->adb, $theElement->getModuleName (), $tableName);
				$relatedFieldId           = CalculatedFieldsHelper::getFieldIdFromTable ($this->adb, $tableName);
				if (!empty ($relatedField) && !empty ($relatedFieldId)) {
					$joinClause .= " INNER JOIN {$tableName} ON {$tableName}.{$relatedFieldId} = {$entity->table_name}.{$relatedField}";
					$processTables[] = $tableName;
				}
			}
			if (!empty ($theElement->getPeriodField ()) && (!preg_match ("/{$entity->table_name}/", $theElement->getPeriodField ()))) {
				list ($tableName, $field) = explode ('.', $theElement->getPeriodField ());
				$relatedField             = CalculatedFieldsHelper::getFieldRelFromTable ($this->adb, $theElement->getModuleName (), $tableName);
				$relatedFieldId           = CalculatedFieldsHelper::getFieldIdFromTable ($this->adb, $tableName);

		/**
		 * Enhanced version of getJoinClause that includes uitype=10 fields like get_related_list does
		 * @param array $relModules
		 * @param string $sql
		 * @param CRMEntity $entity
		 * @param integer $recordId
		 * @return string
		 */

				if (!empty ($relatedField) && !empty ($relatedFieldId)) {
					$joinClause .= " INNER JOIN {$tableName} ON {$tableName}.{$relatedFieldId} = {$entity->table_name}.{$relatedField}";
					$processTables [] = $tableName;
				}
			}
			
			if (!empty ($theElement->getRelatedModules ()) && !empty($theElement->getSqlFilter ())) {
				$relModules = $this->getRelatedModulesByName ($theElement->getModuleName ());
				foreach ($relModules as $relation) {
					$modules [] = $relation ['name'];
				}
				$joinClause .= $this->getJoinClause($modules, $theElement->getSqlFilter (), $entity);
			}
			
			if (!empty ($theElement->getSqlFilter ()) && count ($processTables) > 0) {
				$relatedTables = CalculatedFieldsHelper::getRelatedTables ($this->adb, $theElement->getModuleName ());
				$totalTables = count ($relatedTables);
				if ($totalTables > 0) {
					foreach ($relatedTables as $relatedTable) {
						if (in_array ($relatedTable, $processTables)) {
							continue;
						} else if (preg_match ("/{$relatedTable}/", $theElement->getSqlFilter ())) {
							$relatedField = CalculatedFieldsHelper::getFieldRelFromTable ($this->adb, $theElement->getModuleName (), $relatedTable);
							$relatedFieldId = CalculatedFieldsHelper::getFieldIdFromTable ($this->adb, $relatedTable);
							if (!empty ($relatedField) && !empty ($relatedFieldId)) {
								$joinClause .= " INNER JOIN {$relatedTable} ON {$relatedTable}.{$relatedFieldId} = {$entity->table_name}.{$relatedField}";
							}
						}
					}
				}
			}
			
			return $joinClause;
		}
		
		/**
		 * Obtiene la cláusula where del cálculo del sistema
		 *
		 * @param string $where
		 * @param CalculationElement $theElement
		 * @param $record
		 *
		 * @return string
		 * @throws Exception
		 */
		private function getSqlFromWhereClause ($where, $theElement, $record) {
			// Check if this is a grid element - grid elements should NOT use UNION strategy
			$columnName = $theElement->getColumnName();
			$relatedModules = $theElement->getRelatedModules();
			
			// Enhanced grid element detection - generic patterns only
			$isGridElement = (strpos($columnName, '_IN_ARRAY_') !== false) || 
							(strpos($columnName, 'vtiger_subfields_special') !== false) ||
							(strpos($relatedModules, 'vtiger_subfields_special') !== false) ||
							(preg_match('/\.\w+_\d+$/', $relatedModules)) || // Matches patterns like .articulo_5820
							(preg_match('/\w+_\d+/', $columnName)); // Matches patterns like cantidad_5820
			
			
			// Check if this element needs related modules and should use UNION strategy
			// BUT exclude grid elements which have their own processing logic
			if (!empty($theElement->getRelatedModules()) && $record > 0 && !$isGridElement) {
				return $this->getEnhancedSqlWithUnion($where, $theElement, $record);
			}
			
			
			// Fallback to original method for simple cases and grid elements
			$entity       = PlatformUtils::getCrmEntity ($this->adb, $theElement->getModuleName ());
			$crmJoin      = "INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = {$entity->table_name}.{$entity->table_index} AND vtiger_crmentity.deleted = 0";
			$selectClause = "{$this->getSqlToCalculationElement ($theElement->getOperationName (), $theElement->getColumnName ())}";
			$joinClause   =  $this->getJoinClauseToElement ($theElement, $entity, $where);
			$recordValue  = ((!empty ($theElement->getRelatedModules ())) && ($record > 0)) ? $this->getConditionValue ($theElement, $record) : '';
			$period       = $this->getSQLRangeDate ($theElement);
			if (!empty ($where)) {
				$where = str_replace ('__RECORD__', $recordValue, $where);
				$where = ' AND ' . $where;
			}
			$sql = "{$selectClause} FROM {$entity->table_name} {$crmJoin} {$joinClause} WHERE 1 {$where} {$period}";
			return $sql;
		}

		/**
		 * Enhanced SQL generation using UNION strategy like get_related_list
		 * @param string $where
		 * @param CalculationElement $theElement
		 * @param integer $record
		 * @return string
		 */
		private function getEnhancedSqlWithUnion($where, $theElement, $record) {
			$entity = PlatformUtils::getCrmEntity($this->adb, $theElement->getModuleName());
			$period = $this->getSQLRangeDate($theElement);
			
			// Parse related modules info
			$relatedModulesStr = $theElement->getRelatedModules();
			$parts = explode('.', $relatedModulesStr);
			
			// Handle different formats: "orden_de_trabajo.vtiger_orden_de_trabajo.plan_de_servicios" or "Trabajos.vtiger_orden_de_trabajo.plan_de_servicios"
			if (count($parts) >= 3) {
				$relModule = $parts[0];
				$tableRelModule = $parts[1];
				$fieldRelModule = $parts[2];
				
				// Map display names to technical module names
				$moduleNameMap = array(
					'Planes de servicios' => 'plan_de_mantenimiento',
					'Ordenes de trabajo' => 'orden_de_trabajo',
					'Contratos de servicio' => 'contratos_de_servicio',
					'Trabajos' => 'orden_de_trabajo',
					'trabajos' => 'orden_de_trabajo'
				);
				
				// Use mapped name if available, otherwise use original
				$relModule = isset($moduleNameMap[$relModule]) ? $moduleNameMap[$relModule] : $relModule;
				
			} else {
				// Fallback to original method to avoid infinite loop
				$entity = PlatformUtils::getCrmEntity($this->adb, $theElement->getModuleName());
				$crmJoin = "INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = {$entity->table_name}.{$entity->table_index} AND vtiger_crmentity.deleted = 0";
				$selectClause = $this->getSqlToCalculationElement($theElement->getOperationName(), $theElement->getColumnName());
				$joinClause = $this->getJoinClauseToElement($theElement, $entity, $where);
				$recordValue = ((!empty($theElement->getRelatedModules())) && ($record > 0)) ? $this->getConditionValue($theElement, $record) : '';
				if (!empty($where)) {
					$where = str_replace('__RECORD__', $recordValue, $where);
					$where = ' AND ' . $where;
				}
				return "{$selectClause} FROM {$entity->table_name} {$crmJoin} {$joinClause} WHERE 1 {$where} {$period}";
			}
			
			$relatedEntity = PlatformUtils::getCrmEntity($this->adb, $relModule);
			
			if (!$relatedEntity) {
				// Fallback to original method to avoid infinite loop
				$entity = PlatformUtils::getCrmEntity($this->adb, $theElement->getModuleName());
				$crmJoin = "INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = {$entity->table_name}.{$entity->table_index} AND vtiger_crmentity.deleted = 0";
				$selectClause = $this->getSqlToCalculationElement($theElement->getOperationName(), $theElement->getColumnName());
				$joinClause = $this->getJoinClauseToElement($theElement, $entity, $where);
				$recordValue = ((!empty($theElement->getRelatedModules())) && ($record > 0)) ? $this->getConditionValue($theElement, $record) : '';
				if (!empty($where)) {
					$where = str_replace('__RECORD__', $recordValue, $where);
					$where = ' AND ' . $where;
				}
				return "{$selectClause} FROM {$entity->table_name} {$crmJoin} {$joinClause} WHERE 1 {$where} {$period}";
			}
						
			// Get the base field name without aggregation function for UNION parts
			$baseFieldName = $theElement->getColumnName();
			
			// Build WHERE condition
			$recordValue = $this->getConditionValue($theElement, $record);
			$whereCondition = '';
			if (!empty($where)) {
				$whereCondition = ' AND ' . str_replace('__RECORD__', $recordValue, $where);
			}
			
			// PART 1: Query using vtiger_crmentityrel (bidirectional)
			$sql1 = "SELECT {$baseFieldName} AS calculation_value, {$relatedEntity->table_name}.{$relatedEntity->table_index} AS record_id
					FROM {$relatedEntity->table_name} 
					INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = {$relatedEntity->table_name}.{$relatedEntity->table_index} AND vtiger_crmentity.deleted = 0
					INNER JOIN vtiger_crmentityrel ON (vtiger_crmentityrel.relcrmid = {$relatedEntity->table_name}.{$relatedEntity->table_index} OR vtiger_crmentityrel.crmid = {$relatedEntity->table_name}.{$relatedEntity->table_index})
					WHERE (vtiger_crmentityrel.crmid = $record OR vtiger_crmentityrel.relcrmid = $record) {$whereCondition} {$period}";
			
			// PART 2: Query using direct uitype=10 field relationship
			// Exclude records that were already found in PART 1 to avoid duplicates
			$sql2 = "SELECT {$baseFieldName} AS calculation_value, {$relatedEntity->table_name}.{$relatedEntity->table_index} AS record_id
					FROM {$relatedEntity->table_name}
					INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = {$relatedEntity->table_name}.{$relatedEntity->table_index} AND vtiger_crmentity.deleted = 0
					WHERE {$relatedEntity->table_name}.{$fieldRelModule} = $record {$period}
					AND {$relatedEntity->table_name}.{$relatedEntity->table_index} NOT IN (
						SELECT {$relatedEntity->table_name}.{$relatedEntity->table_index}
						FROM {$relatedEntity->table_name} 
						INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = {$relatedEntity->table_name}.{$relatedEntity->table_index} AND vtiger_crmentity.deleted = 0
						INNER JOIN vtiger_crmentityrel ON (vtiger_crmentityrel.relcrmid = {$relatedEntity->table_name}.{$relatedEntity->table_index} OR vtiger_crmentityrel.crmid = {$relatedEntity->table_name}.{$relatedEntity->table_index})
						WHERE (vtiger_crmentityrel.crmid = $record OR vtiger_crmentityrel.relcrmid = $record) {$whereCondition} {$period}
					)";
			
			// Get the aggregation function
			$aggregationFunction = $theElement->getOperationName();
			
			// Combine with UNION ALL to preserve all values, then apply the aggregation function
			$finalSql = "SELECT {$aggregationFunction}(calculation_value) FROM (
							({$sql1}) 
							UNION ALL 
							({$sql2})
						) AS combined_results";
			
			
			return $finalSql;
		}

		private function getSQLRangeDate ($theElement) {
			if (empty($theElement->getPeriod ()) &&  empty($theElement->getPeriodField ())) {
				return null;
			}
			switch ($theElement->getPeriod ()) {
				case 'LAST_YEAR':
					$results = " AND (YEAR ({$theElement->getPeriodField ()}) = YEAR(CURRENT_DATE - INTERVAL 1 YEAR))";
					break;
				case 'THIS_YEAR':
					$results = " AND YEAR({$theElement->getPeriodField ()}) = YEAR(CURRENT_DATE) ";
					break;
				case 'LAST_SIX_MONTHS':
					$results = " AND ({$theElement->getPeriodField ()} >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)) ";
					break;
				case 'LAST_THREE_MONTHS':
					$results = " AND ({$theElement->getPeriodField ()} >= DATE_SUB(CURRENT_DATE, INTERVAL 3 MONTH)) ";
					break;
				case 'LAST_TWO_MONTHS':
					$results = " AND ({$theElement->getPeriodField ()} >= DATE_SUB(CURRENT_DATE, INTERVAL 2 MONTH)) ";
					break;
				case 'LAST_MONTH':
					$results = " AND (MONTH({$theElement->getPeriodField ()}) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)) ";
					break;
				case 'THIS_MONTH':
					$results = " AND (MONTH({$theElement->getPeriodField ()}) = MONTH(CURRENT_DATE))";
					break;
				case 'LAST_15_DAYS':
					$results = " AND ({$theElement->getPeriodField ()} >= CURDATE() - INTERVAL 15 DAY)";
					break;
				case 'PREVIOUS_WEEK':
					$results = " AND (WEEK({$theElement->getPeriodField ()}) = WEEK(CURRENT_DATE - INTERVAL 1 WEEK)) ";
					break;
				case 'CURRENT_WEEK':
					$results = " AND (WEEK({$theElement->getPeriodField ()}) = WEEK(CURRENT_DATE)) ";
					break;
				default:
					$results = '';
					break;
			}
			return $results;
		}
		/**
		 * Obtiene el sql de los elemntos de cálculo
		 *
		 * @param $operationType
		 * @param $fieldToCalculation
		 *
		 * @return string
		 */
		private function getSqlToCalculationElement ($operationType, $fieldToCalculation) {
			switch ($operationType) {
				case 'SUM':
					$results = "SELECT SUM(  {$fieldToCalculation}  ) ";
					break;
				case 'MAX':
					$results = "SELECT MAX(  {$fieldToCalculation}  )";
					break;
				case 'MIN':
					$results = "SELECT MIN(  {$fieldToCalculation}  )";
					break;
				case 'COUNT':
					$results = "SELECT COUNT(  {$fieldToCalculation}  ) ";
					break;
				case 'AVG':
					$results = "SELECT AVG(  {$fieldToCalculation}  ) ";
					break;
				default:
					$results = '';
					break;
			}
			return $results;
		}
		
		/**
		 * Verifica si el record pertenece a un módulo del tipo CRMEntity
		 *
		 * @param $record
		 *
		 * @return boolean
		 */
		private function isCrmEntity ($record) {
			$query     = $this->adb->query ("SELECT * FROM vtiger_crmentityrel WHERE vtiger_crmentityrel.crmid = {$record}");
			$numOfRows = $this->adb->num_rows ($query);
			return ($numOfRows > 0) ? true : false;
		}

		/**
		 * Elimina un cálculo del sistema según su ID
		 *
		 * @param string $id
		 *
		 * @return boolean
		 * @throws Exception
		 */
		public function delCalculatedSystem ($id = '') {
			if (empty($id) && $id == null) {
				return false;
			}
			$csm         = CalculationSystemManager::getInstance ($this->adb);
			$calculation = $csm->fetchCalculationSystem ($id);
			if ($calculation) {
				$this->adb->query ("UPDATE vtiger_field SET paradicional= '' WHERE paradicional= '{$calculation->getCalculationName ()}'");
				$csm->deleteCalculationSystem ($calculation);
			} else {
				return false;
			}
			return true;
		}

		/**
		 * Elimina un Elemento de cálculo
		 *
		 * @param string $id
		 *
		 * @return boolean
		 * @throws Exception
		 */
		public function delCalculatedFields ($id = '') {
			if (empty($id) && $id == null) {
				return false;
			}
			$cem     = CalculationElementManager::getInstance ($this->adb);
			$element = $cem->fetchCalculationElement ($id);
			if ($element) {
				$cem->deleteCalculationElement ($element);
			} else {
				return false;
			}
			return true;
		}

		/**
		 * Obtiene una colección de objetos CalculationElement
		 *
		 * @return CalculationElement[]|null
		 * @throws Exception
		 */
		public function getAllCalculateFields () {
			return CalculationElementManager::getInstance ($this->adb)->fetchCalculationsElements ();
		}

		/**
		 * Obtiene una colección de objetos CalculationSystemException
		 *
		 * @param $current_user
		 *
		 * @return CalculationSystem[]
		 * @throws CalculationSystemException
		 * @throws Exception
		 */
		public function getAllCalculateSystem ($current_user) {
			date_default_timezone_set ($current_user->time_zone);
			$resultsArray = array ();
			$this->logger = new Logger ('calculatedsystem', array ('appender' => array ('File' => "{$this->platform}/logs/calculatedsystem/calculosdelsistema.log")));
			$csm          = CalculationSystemManager::getInstance ($this->adb);
			$calculations = $csm->fetchCalculationsSystem ();
			foreach ($calculations as $calculation) {
				if ($calculation->getStatus () == CalculationSystemInterface::STATUS_ACTIVE) {
					$sql           = $this->equationResult ($calculation->getEquation ());
					$hasArray      = strpos ($sql, '_IN_ARRAY_');
					$equationValue = (!empty($calculation->getModuleName ()) && $hasArray === false) ? $this->getEnhancedCalculateSystemResult ($calculation->getModuleName (), $sql, 0, $calculation->getRelatedModules ()) : 0;
					$calculation->setResult ($equationValue);
					$calculation->setUpdatedDate (date ('Y-m-d H:m:s'));
					$csm->updateCalculationResult ($calculation);
				}
				$resultsArray [] = $calculation;
			}
			$this->logger->emit ('INFO', 'Actualizando todos los cálculos');
			return $resultsArray;
		}

		/**
		 * Obiene la lista de módulos habilitados
		 *
		 * @return array
		 * @throws Exception
		 */
		public function getAllModules () {
			$query        = $this->adb->query (
				'SELECT
					vtiger_tab.*
				FROM
					vtiger_tab
					INNER JOIN vtiger_field ON vtiger_field.tabid = vtiger_tab.tabid
				WHERE
					vtiger_tab.presence IN (0, 2) AND
					vtiger_tab.customized=1
				GROUP BY
					vtiger_tab.tabid
				ORDER BY
					vtiger_tab.tablabel ASC'
			);
			$resultsArray = array ();
			$numOfRows    = $this->adb->num_rows ($query);
			if ($numOfRows > 0) {
				while ($row = $this->adb->fetchByAssoc ($query)) {
					$resultsArray[] = $row;
				}
			}
			return $resultsArray;
		}

		/**
		 * Obtiene la lista de elementos de calculos por nombres
		 *
		 * @param integer $elementName
		 * @param integer $record
		 *
		 * @return integer|array
		 * @throws CalculationElementException
		 * @throws Exception
		 */
		public function getCalculationElementByName ($elementName, $record = 0) {
			$theElement      = null;
			$resultOfElement = 0;
			$cem             = CalculationElementManager::getInstance ($this->adb);
			$theElement      = $cem->fetchCalculationElement ($elementName);
			if ((empty ($theElement)) || (!($theElement instanceof CalculationElement))) {
				return $resultOfElement;
			}
			// Enhanced grid element detection - same logic as getSqlFromWhereClause
			$columnName = $theElement->getColumnName();
			$relatedModules = $theElement->getRelatedModules();
			
			$isGridElement = (strpos($columnName, '_IN_ARRAY_') !== false) || 
							(strpos($columnName, 'vtiger_subfields_special') !== false) ||
							(strpos($relatedModules, 'vtiger_subfields_special') !== false) ||
							(preg_match('/\.\w+_\d+$/', $relatedModules)) || // Matches patterns like .articulo_5820
							(preg_match('/\w+_\d+/', $columnName)); // Matches patterns like cantidad_5820

			
			// Resolve grid field names if this is a grid element
			if ($isGridElement !== false) {
				
				// Get grid field name for the module
				$gridFieldName = CalculatedFieldsHelper::getGridFieldNameFromModule($this->adb, $theElement->getModuleName());
				
				if ($gridFieldName) {
					
					// Resolve columnName if it contains vtiger_subfields_special
					$currentColumnName = $theElement->getColumnName();
					if (strpos($currentColumnName, 'vtiger_subfields_special.') !== false) {
						$fieldName = str_replace('vtiger_subfields_special.', '', $currentColumnName);
						$realFieldName = CalculatedFieldsHelper::resolveGridFieldName($this->adb, $gridFieldName, $fieldName, $theElement->getModuleName());
						
						if ($realFieldName) {
							$theElement->setColumnName("vtiger_subfields_special.$realFieldName");
						}
					}
					
					// Resolve relatedModules if it contains vtiger_subfields_special
					$currentRelatedModules = $theElement->getRelatedModules();
					if (strpos($currentRelatedModules, 'vtiger_subfields_special') !== false) {
						// Clean the relatedModules format first
						$cleanRelatedModules = $currentRelatedModules;
						$hasLeadingDot = false;
						
						// Check if it starts with a dot and remember it
						if (strpos($cleanRelatedModules, '.') === 0) {
							$hasLeadingDot = true;
							$cleanRelatedModules = substr($cleanRelatedModules, 1);
						}
						
						$fieldName = str_replace('vtiger_subfields_special.', '', $cleanRelatedModules);
						$realFieldName = CalculatedFieldsHelper::resolveGridFieldName($this->adb, $gridFieldName, $fieldName, $theElement->getModuleName());
						
						if ($realFieldName) {
							// Always remove leading dot - we want clean format
							$resolvedRelatedModules = "vtiger_subfields_special.$realFieldName";
							$theElement->setRelatedModules($resolvedRelatedModules);
						}
					}
				}
				
				// Store the operation for later use in getCalculateSystemById
				$this->currentOperation = $theElement->getOperationName();
								$this->valuesInGrid = CalculatedFieldsHelper::getValueFromGrid ($this->adb, $theElement, $record);
				return $this->valuesInGrid;
			}
			$actualDieOnError = $this->adb->dieOnError;
			$this->adb->setDieOnError (false);
			$where   = json_decode (str_replace ('&quot;', '"', $theElement->getSqlFilter ()));
			$sql     = $this->getSqlFromWhereClause ($where, $theElement, $record);

			$results = $this->adb->query ($sql);
			$this->adb->setDieOnError ($actualDieOnError);
			if ($results) {
				$elementValue    = array_values ($this->adb->fetchByAssoc ($results));
				$resultOfElement = (!empty ($elementValue[0])) ? $elementValue[0] : 0;
			} else {
				$resultOfElement = 0;
				if ($results === false) {
					$_SESSION ['flashmessage'] = array (
						'iserror' => true,
						'message' => "en la configuración del filtro del elemento calculado&nbsp;<a title='" . $theElement->getDescription () . "'   href='index.php?module=calculated_fields&amp;action=index&amp;parenttab=Settings'>{$theElement->getName ()} </a>",
						'data'    => null,
					);
				}
			}
			$theElement->setResult ($resultOfElement)
				->setUpdatedDate (date ('Y-m-d H:m:s'));

			$cem->updateCalculationElementResult ($theElement);

			return $resultOfElement;
		}

		/**
		 * Obtiene la lista de elementos de calculos según su ID
		 *
		 * @param integer|string $elementId
		 *
		 * @return boolean|CalculationElement|null
		 * @throws Exception
		 */
		public function getCalculateFieldsById ($elementId) {
			$cem        = CalculationElementManager::getInstance ($this->adb);
			$theElement = $cem->fetchCalculationElement ($elementId);
			if ($theElement) {
				return $theElement;
			}
			return null;
		}

		/**
		 * Obtiene la lista de cálculos del sistema según su ID y ejecuta los cálculos respectivos
		 *
		 * @param string $id
		 * @param integer $record
		 * @param string $action
		 * @param integer $userId
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public function getCalculateSystemById ($id = '', $record = 0, $action = '', $userId = 0, $gridStatus) {
			if (empty($id)) {
				return null;
			}
			$calculation = CalculationSystemManager::getInstance ($this->adb)->fetchCalculationSystem ($id);
			if ($calculation) {
				$logFieldName = 'calculo_sistema_id_' . $id;
				$this->logger = new Logger ('calculatedsystem', array ('appender' => array ('File' => "{$this->platform}/logs/calculatedsystem/{$logFieldName}.log")));
				$this->logger->emit ('INFO', 'Ejecutando formula del cálculo: ' . $calculation->getName ());

				$sql = $this->equationResult ($calculation->getEquation (), $record);
				if ($sql == null) {
					$equationValue = 0;
				} else if (is_array ($this->valuesInGrid)) {
					
					// Check if this is cross-record search data (simple array of values)
					$isSimpleValueArray = !empty($this->valuesInGrid) && is_numeric(array_keys($this->valuesInGrid)[0]);
					
					if ($isSimpleValueArray) {
						// For cross-record search data, calculate result directly
						
						// Get the operation from the stored currentOperation
						$operation = !empty($this->currentOperation) ? strtoupper($this->currentOperation) : 'SUM';
						
						// Apply the correct operation
						switch ($operation) {
							case 'SUM':
								$equationValue = array_sum($this->valuesInGrid);
								break;
							case 'AVG':
								$equationValue = count($this->valuesInGrid) > 0 ? array_sum($this->valuesInGrid) / count($this->valuesInGrid) : 0;
								break;
							case 'COUNT':
								$equationValue = count($this->valuesInGrid);
								break;
							case 'MAX':
								$equationValue = !empty($this->valuesInGrid) ? max($this->valuesInGrid) : 0;
								break;
							case 'MIN':
								$equationValue = !empty($this->valuesInGrid) ? min($this->valuesInGrid) : 0;
								break;
							default:
								$equationValue = array_sum($this->valuesInGrid); // Default to SUM
								break;
						}
						
						// Clear the stored operation after use
						$this->currentOperation = null;
						// Continue to normal flow for result processing
					} else {
						// Traditional grid processing
						// Permitir procesamiento de grid independientemente del gridStatus
						// Solo bloquear si hay un error explícito
						if ($gridStatus === false) {
						return null;
					}
						// Continuar procesamiento incluso con gridStatus vacío o null
					$updatedProducts  = array ();
					$entity           = PlatformUtils::getCrmEntity ($this->adb, $calculation->getModuleName ());
					$actualDieOnError = $this->adb->dieOnError;
					$this->adb->setDieOnError (false);
					foreach ($this->valuesInGrid as $relField => $colField) {
						$relRecord     = CalculatedFieldsHelper::getRecord ($this->adb, $entity->table_name, $entity->table_index, $relField);
						$sqlCol        = str_replace ('_IN_ARRAY_', $colField, $sql);
							$equationValue = (!empty($calculation->getModuleName ()) && ($relRecord != 0)) ? $this->getEnhancedCalculateSystemResult ($calculation->getModuleName (), $sqlCol, $relRecord, $calculation->getRelatedModules ()) : null;

						if ($equationValue !== null) {
							$strToCol   = str_replace (array ('(', '+', '-', '*', '/', '_IN_ARRAY_', ')'), array (''), $sql);
							$columnName = explode ('.', $strToCol);
							$objField   = FieldManager::getInstance($this->adb)->fetchFieldByName($calculation->getModuleName (), $columnName [1], true);
							$oldValues  = $this->adb->run_query_allrecords("SELECT {$objField->getColumnName()} FROM {$objField->getTableName()} WHERE {$entity->table_index} = {$relRecord}");
							$tabId      = $this->adb->run_query_allrecords("SELECT tabid FROM vtiger_field WHERE fieldid= {$objField->getId()}");

							$this->adb->query ("UPDATE {$objField->getTableName()} SET {$objField->getColumnName()} = {$equationValue} WHERE {$entity->table_index} = {$relRecord}");

							$histories [] = EntityHistory::getInstance ()
								->setId (0)
								->setCreatedDate (date ('Y-m-d h:i:s'))
								->setFieldId ($objField->getId())
								->setModuleId ($tabId [0]['tabid'])
								->setModifiedBy ($userId)
								->setModifiedOn (1)
								->setNewValue ($equationValue)
								->setOldValue ($oldValues [0][ $objField->getColumnName() ])
								->setRegistryId ($relRecord);
							$updatedProducts [] = array (
								'module'    => $calculation->getModuleName (),
								'record'    => $relRecord,
								'fieldname' => $objField->getName (),
							);
						}
					}
					$this->logger->emit ('INFO', 'Actualizando cálculo: ' . $calculation->getName ());
					if (!empty($updatedProducts)) {
						EntityHistoryManager::getInstance ($this->adb)->saveAllEntityHistory ($histories);
						$_SESSION ['flashmessage'] = array (
							'iserror' => false,
							'message' => 'Se ha actualizado el inventario.',
							'data'    => null,
						);
					}
					$this->adb->setDieOnError ($actualDieOnError);
					return (!empty ($updatedProducts)) ? $updatedProducts : null;
					}
				} else {
					$equationValue = (!empty($calculation->getModuleName ())) ? $this->getEnhancedCalculateSystemResult ($calculation->getModuleName (), $sql, $record, $calculation->getRelatedModules ()) : 0;
				}
				$this->logger->emit ('INFO', 'Actualizando cálculo: ' . $calculation->getName ());
				if (!empty ($action) && !empty ($equationValue)) {
					if ($action == 'boxScore') {
						return $equationValue;
					}
					return $this->updateCalculateByRecord ($id, $record, $calculation->getModuleName (), $equationValue, $userId);
				}
				return null;
			}
			return null;
		}

		/**
		 * Obtiene la lista de cálculos del sistema según su ID
		 *
		 * @param integer|string $recordId
		 *
		 * @return boolean|CalculationSystem
		 * @throws Exception
		 */
		public function getCalculateSystemDataById ($recordId) {
			$calculation = CalculationSystemManager::getInstance ($this->adb)->fetchCalculationSystem ($recordId);

			if ($calculation) {
				return $calculation;
			} else {
				return false;
			}
		}

		/**
		 * Obtiene el listado del campos según el módulo
		 *
		 * @param string|array $moduleName
		 * @param string $condition
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public function getColumnsByModule ($moduleName, $condition = '') {
			if (is_array ($moduleName)) {
				$questionMarks = str_repeat ('?, ', (count ($moduleName) - 1)) . '?';
				$condition .= " t.name IN ({$questionMarks})";
				$parameter = $moduleName;
			} else {
				$condition .= ' t.name = ?';
				$parameter [] = $moduleName;
			}
			$inUiType = $this->adb->sql_expr_datalist (
				array (
					FieldInterface::UI_TYPE_CODE,
					FieldInterface::UI_TYPE_PHONE,
					FieldInterface::UI_TYPE_EMAIL,
					FieldInterface::UI_TYPE_URL,
					FieldInterface::UI_TYPE_MODIFIED_BY,
					FieldInterface::UI_TYPE_IMAGE_REFERENCE,
					FieldInterface::UI_TYPE_GRID,
				)
			);
			$result = $this->adb->pquery (
				"SELECT
					f.fieldname,
					f.fieldlabel,
					f.tablename,
					f.uitype,
					f.typeofdata,
					t.name,
					t.tablabel
				FROM
					vtiger_field f
					INNER JOIN vtiger_blocks b ON f.block=b.blockid AND b.visible=0 AND b.display_status=1
					INNER JOIN vtiger_tab t ON t.tabid=f.tabid
				WHERE
					f.presence IN (0, 2) AND
					f.uitype NOT IN {$inUiType} AND
					{$condition}
					ORDER BY t.tablabel, f.fieldlabel ASC",
				$parameter
			);
			if ((!$result) || ($this->adb->num_rows ($result) == 0)) {
				return null;
			}

			$columns = array ();
			$row     = $this->adb->fetchByAssoc ($result, -1, false);
			while ($row) {
				$fieldtype  = explode ('~', $row ['typeofdata']);
				$moduleLabel = html_entity_decode($row['tablabel'], ENT_QUOTES, 'UTF-8');
				$columns [] = array (
					'fieldname'  => $row ['fieldname'],
					'label'      => html_entity_decode (getTranslatedString ($row ['fieldlabel'], $moduleName), ENT_QUOTES, 'UTF-8'),
					'tablename'  => $row ['tablename'],
					'uitype'     => $row ['uitype'],
					'typeofdata' => $fieldtype[0],
					'module'     => $row ['name'], // Nombre técnico para uso interno
					'module_label' => $moduleLabel, // Etiqueta traducida de vtiger_tab.tablabel
				);
				$row        = $this->adb->fetchByAssoc ($result, -1, false);
			}

			usort (
				$columns,
				function ($columnA, $columnB) {
					// Primero ordenar por etiqueta del módulo alfabéticamente
					$moduleComparison = strcasecmp($columnA['module_label'], $columnB['module_label']);
					if ($moduleComparison !== 0) {
						return $moduleComparison;
					}
					// Si son del mismo módulo, ordenar por label del campo alfabéticamente
					return strcasecmp($columnA['label'], $columnB['label']);
				}
			);
			return $columns;
		}

		/**
		 * Obtiene el resultado de los elementos de cálculo
		 *
		 * @param integer $elementId
		 *
		 * @return float|integer
		 * @throws Exception
		 */
		public function getElementResults ($elementId) {
			$results    = 0;
			$cem        = CalculationElementManager::getInstance ($this->adb);
			$theElement = $cem->fetchCalculationElement ($elementId);
			if ($theElement) {
				return $theElement->getResult ();
			}

			return $results;
		}

		/**
		 * Obtien el tipo de datos de uncampo dado
		 *
		 * @param array $fields
		 * @param array $fieldData
		 */
		public function getFieldDataType (&$fields, $fieldData) {
			$totalFields = count ($fields);
			foreach ($fieldData as $field) {
				for ($k = 0; $k < $totalFields; $k++) {
					if ($fields[ $k ] == $field['tablename'] . '.' . $field['fieldname']) {
						$fields[ $k ] = $field['typeofdata'] . '@' . $fields[ $k ];
					}
				}
			}
		}

		/**
		 * Obtiene los subcampos de un campo tipo tabla
		 *
		 * @param $moduleName
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public function getFieldsFromGrid ($moduleName) {
			$uiType   = FieldInterface::UI_TYPE_GRID;
			$inUiType = $this->adb->sql_expr_datalist (
				array (
					FieldInterface::UI_TYPE_CODE,
					FieldInterface::UI_TYPE_PHONE,
					FieldInterface::UI_TYPE_EMAIL,
					FieldInterface::UI_TYPE_URL,
					FieldInterface::UI_TYPE_MODIFIED_BY,
					FieldInterface::UI_TYPE_IMAGE_REFERENCE,
					//FieldInterface::UI_TYPE_CALCULATED,
					//FieldInterface::UI_TYPE_SUMMARY_ROW,
				)
			);

			$result = $this->adb->query (
				"SELECT
					sf.*,
					f.columnname,
					f.fieldlabel,
					t.name as module_name,
					t.tablabel as module_label
				FROM
					vtiger_subfields_special sf
					INNER JOIN vtiger_field f ON f.fieldid = sf.fieldid
					INNER JOIN vtiger_tab t ON t.tabid=f.tabid
				WHERE
					f.uitype = {$uiType} AND
					f.presence IN (0, 2) AND
					sf.uitype NOT IN  {$inUiType}  AND
					t.name='{$moduleName}'"
			);
			if ((!$result) || ($this->adb->num_rows ($result) == 0)) {
				return null;
			}

			$columns = array ();
			while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
				$relatedModule = ($row ['uitype'] == 10) ? $row ['relmodule'] . '.' : '';
				
				// Construir labels descriptivos para campos grid
				$gridFieldLabel = html_entity_decode(getTranslatedString($row['fieldlabel'], $moduleName), ENT_QUOTES, 'UTF-8');
				$subFieldLabel = html_entity_decode($row['label'], ENT_QUOTES, 'UTF-8');
				$moduleLabel = html_entity_decode($row['module_label'], ENT_QUOTES, 'UTF-8');
				
				$columns []    = array (
					'fieldname'  => str_replace (array ('_', $row ['fieldid']), array (''), $row ['name']),
					'label'      => $subFieldLabel . ' (' . $gridFieldLabel . ')',
					'tablename'  => 'vtiger_subfields_special',
					'uitype'     => $row ['uitype'],
					'typeofdata' => (($row ['uitype'] == 7) || ($row ['uitype'] == 9)) ? 'N' : ((($row ['uitype'] == 70)) ? 'DT' : 'V'),
					'module'     => $moduleLabel . ' - ' . $gridFieldLabel,
				);
			}

			usort (
				$columns,
				function ($columnA, $columnB) {
					// Primero ordenar por módulo alfabéticamente
					$moduleComparison = strcasecmp($columnA['module'], $columnB['module']);
					if ($moduleComparison !== 0) {
						return $moduleComparison;
					}
					// Si son del mismo módulo, ordenar por label del campo alfabéticamente
					return strcasecmp($columnA['label'], $columnB['label']);
				}
			);
			return $columns;
		}

		/**
		 * @return integer
		 * @throws Exception
		 */
		public function getLastEquationId () {
			$results   = 0;
			$sql       = 'SELECT `calculated_equationid` FROM `vtiger_calculated_equation` WHERE 1 ORDER BY calculated_equationid DESC LIMIT 1';
			$query     = $this->adb->query ($sql);
			$numOfRows = $this->adb->num_rows ($query);
			if ($numOfRows > 0) {
				while ($row = $this->adb->fetchByAssoc ($query)) {
					$results = $row['calculated_equationid'];
				}
			}
			return $results;
		}

		/**
		 * @return array
		 * @throws Exception
		 */
		public function getModulesWithNumericFields () {
			$query        = $this->adb->pquery (
				'SELECT
					t.*
				FROM
					vtiger_tab t
					INNER JOIN vtiger_field f ON f.tabid = t.tabid
				WHERE
					t.presence IN (0, 2) AND
					t.isentitytype=1 AND
					f.typeofdata LIKE (?)
				GROUP BY
					t.tabid
				ORDER BY
					t.tablabel ASC',
				array ('N%')
			);
			$resultsArray = array ();
			$numOfRows    = $this->adb->num_rows ($query);
			if ($numOfRows > 0) {
				while ($row = $this->adb->fetchByAssoc ($query)) {
					$resultsArray[] = $row;
				}
			}
			return $resultsArray;
		}

		/**
		 * Obtiene módulos que pueden ser usados en cálculos del sistema
		 * Incluye módulos con campos numéricos, elementos calculados activos, o que son objetivo de cálculos
		 *
		 * @return array
		 * @throws Exception
		 */
		public function getModulesForCalculations () {
			$query = $this->adb->pquery (
				'SELECT DISTINCT
					t.*,
					CASE 
						WHEN EXISTS (
							SELECT 1 FROM vtiger_field f 
							WHERE f.tabid = t.tabid AND f.typeofdata LIKE ?
						) THEN 1 ELSE 0 
					END as has_numeric_fields,
					CASE 
						WHEN EXISTS (
							SELECT 1 FROM vtiger_calculated_fields cf 
							WHERE cf.modulename = t.name AND cf.active = 1
						) THEN 1 ELSE 0 
					END as has_calculated_elements,
					CASE 
						WHEN EXISTS (
							SELECT 1 FROM vtiger_calculated_system cs 
							WHERE cs.modulename = t.name AND cs.active = 1
						) THEN 1 ELSE 0 
					END as has_system_calculations
				FROM
					vtiger_tab t
				WHERE
					t.presence IN (0, 2) AND
					t.isentitytype = 1 AND
					(
						-- Criterio A: Módulos con campos numéricos nativos
						EXISTS (
							SELECT 1 FROM vtiger_field f 
							WHERE f.tabid = t.tabid AND f.typeofdata LIKE ?
						)
						OR
						-- Criterio B: Módulos con elementos calculados activos
						EXISTS (
							SELECT 1 FROM vtiger_calculated_fields cf 
							WHERE cf.modulename = t.name AND cf.active = 1
						)
						OR
						-- Criterio C: Módulos objetivo de cálculos del sistema
						EXISTS (
							SELECT 1 FROM vtiger_calculated_system cs 
							WHERE cs.modulename = t.name AND cs.active = 1
						)
					)
				ORDER BY
					t.tablabel ASC',
				array('N%', 'N%')
			);
			
			$resultsArray = array ();
			$numOfRows    = $this->adb->num_rows ($query);
			
			
			if ($numOfRows > 0) {
				while ($row = $this->adb->fetchByAssoc ($query)) {
					$resultsArray[] = $row;
				}
			}
			
			return $resultsArray;
		}

		/**
		 * @param  integer $tabId
		 *
		 * @return array|mixed
		 * @throws Exception
		 */
		public function getModuleByid ($tabId) {
			$results   = array ();
			$query     = $this->adb->query ('SELECT * FROM `vtiger_tab` WHERE tabid=' . $tabId);
			$numOfRows = $this->adb->num_rows ($query);
			if ($numOfRows > 0) {
				while ($row = $this->adb->fetchByAssoc ($query)) {
					$results = $row;
				}
			}
			return $results;
		}

		/**
		 * @param integer $idTab
		 *
		 * @return array
		 * @throws Exception
		 */
		public function getModuleFieldByTabid ($idTab) {
			$query        = $this->adb->pquery ('SELECT `fieldid`,`fieldlabel` FROM `vtiger_field` WHERE `tabid` = ? AND `columnname` NOT IN(?,?,?)', array ($idTab, 'modifiedtime', 'createdtime', 'smownerid'));
			$resultsArray = array ();
			$numOfRows    = $this->adb->num_rows ($query);
			if ($numOfRows > 0) {
				while ($row = $this->adb->fetchByAssoc ($query)) {
					$resultsArray[] = $row;
				}
			}
			return $resultsArray;
		}

		/**
		 * @param $moduleName
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public function getRelatedModulesByName ($moduleName) {
			$result = $this->adb->pquery (
				'SELECT DISTINCT
					trel.*
				FROM
					vtiger_tab trel
					INNER JOIN vtiger_relatedlists rl ON rl.related_tabid=trel.tabid
					INNER JOIN vtiger_module_report mr ON mr.tabid=rl.related_tabid
					INNER JOIN vtiger_tab t ON t.tabid=rl.tabid
				WHERE
					trel.isentitytype=1 AND
					trel.name NOT IN (?, ?, ?, ?) AND
					trel.presence=0 AND
					rl.label<>? AND
					mr.reportavailable=1 AND
					t.name=?
				UNION
				SELECT DISTINCT
					trel.*
				FROM
					vtiger_tab trel
					INNER JOIN vtiger_module_report mr ON mr.tabid=trel.tabid
					INNER JOIN vtiger_fieldmodulerel fmr ON fmr.relmodule=trel.name
					INNER JOIN vtiger_tab t ON t.name=fmr.module
				WHERE
					trel.isentitytype=1 AND
					trel.name NOT IN (?, ?, ?, ?) AND
					trel.presence=0 AND
					mr.reportavailable=1 AND
					t.name=?',
				array ('Emails', 'Events', 'Webmails', 'Calendar', 'Activity History', $moduleName, 'Emails', 'Events', 'Webmails', 'Calendar', $moduleName)
			);
			if ((!$result) || ($this->adb->num_rows ($result) == 0)) {
				return null;
			}

			$relatedModules = array ();
			while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
				$row ['tablabel']                 = getTranslatedString ($row ['tablabel'], $row ['name']);
				$relatedModules [ $row ['name'] ] = $row;
			}
			uasort (
				$relatedModules,
				function ($moduleA, $moduleB) {
					return strcmp ($moduleA ['tablabel'], $moduleB ['tablabel']);
				}
			);
			return $relatedModules;
		}

		/**
		 * @return array
		 */
		public function getTypeOfData () {
			return array (
				'V'  => array ('e' => 'igual', 'n' => 'no igual a', 's' => 'empieza con', 'ew' => 'termina con', 'c' => 'contiene', 'k' => 'no contiene'),
				'N'  => array ('e' => 'igual', 'n' => 'no igual a', 'l' => 'menor que', 'g' => 'mayor que', 'm' => 'menor o igual', 'h' => 'mayor o igual'),
				'T'  => array ('e' => 'igual', 'n' => 'no igual a', 'l' => 'menor que', 'g' => 'mayor que', 'm' => 'menor o igual', 'h' => 'mayor o igual', 'bw' => 'entre', 'b' => 'antes', 'a' => 'después'),
				'I'  => array ('e' => 'igual', 'n' => 'no igual a', 'l' => 'menor que', 'g' => 'mayor que', 'm' => 'menor o igual', 'h' => 'mayor o igual'),
				'C'  => array ('e' => 'igual', 'n' => 'no igual a'),
				'D'  => array ('e' => 'igual', 'n' => 'no igual a', 'l' => 'menor que', 'g' => 'mayor que', ' m ' => 'menor o igual', 'h' => 'mayor o igual', 'bw' => 'entre', 'b' => 'antes', 'a' => 'después'),
				'DT' => array ('e' => 'igual', 'n' => 'no igual a', 'l' => 'menor que', 'g' => 'mayor que', 'm' => 'menor o igual', 'h' => 'mayor o igual', 'bw' => 'entre', 'b' => 'antes', 'a' => 'después'),
				'NN' => array ('e' => 'igual', 'n' => 'no igual a', 'l' => 'menor que', 'g' => 'mayor que', 'm' => 'menor o igual', 'h' => 'mayor o igual'),
				'E'  => array ('e' => 'igual', 'n' => 'no igual a', 's' => 'empieza con', 'ew' => 'termina con', 'c' => 'contiene', 'k' => 'no contiene'),
			);
		}

		/**
		 * @param array $filterData
		 *
		 * @return string
		 * @throws Exception
		 */
		public function getSqlFilter ($filterData) {
			$fields     = $filterData ['filterField'];
			$operators  = $filterData ['filterOperator'];
			$values     = $filterData ['filterValue'];
			$joins      = $filterData ['filterJoin'];
			$groupJoins = $filterData ['filterGroupJoin'];
			$moduleName = $filterData ['moduleFilter'];
			$grupoIndex = $filterData ['indexGrupo'];

			$modules[]      = $moduleName;
			$relatedModules = $this->getRelatedModulesByName ($moduleName);
			foreach ($relatedModules as $relation) {
				$modules [] = $relation ['name'];
			}
			$fieldData = $this->getColumnsByModule ($modules);
			$this->getFieldDataType ($fields, $fieldData);

			$totalOperations = count ($fields);
			$totalGroup      = count ($groupJoins);
			$myGroup         = $grupoIndex[0];
			$nextOper        = 0;
			$equation        = '( ';
			$indexGroup      = 0;
			$indexJoin       = 0;

			if ($totalOperations > 0) {
				for ($op = 0; $op < $totalOperations; $op++) {
					$equation .= $this->getEquation ($fields[ $op ], $values[ $op ], $operators[ $op ]);

					$nextOper = ($nextOper < $totalOperations) ? ($nextOper + 1) : $op;

					if ($grupoIndex[ $nextOper ] != $myGroup) {
						$myGroup = $grupoIndex[ $nextOper ];
						if ($op < ($totalOperations - 1)) {
							$equation .= ' )';
							if ($indexGroup == 0) {
								$equation = '( ' . $equation;
							}
							$equation = $equation . ' ) ' . $groupJoins[ $indexGroup ] . ' ( ( ';
							$indexGroup++;
						} else if ($totalGroup > 0) {
							$equation = $equation . ' ))';
						} else {
							$equation = $equation . ' )';
						}
					} else {
						if ($op < ($totalOperations - 1)) {
							$equation = $equation . ' ) ' . $joins[ $indexJoin ] . ' ( ';
							$indexJoin++;
						} else {
							if ($indexGroup == 0) {
								$equation = $equation . ' ) ';
							} else {
								$equation = $equation . ' ) )';
							}
						}
					}
				}
				return $equation;
			} else {
				return '';
			}
		}

		/**
		 * @param array $data
		 * @param $current_user
		 * @param integer $elementId
		 *
		 * @return float|integer
		 * @throws CalculationElementException
		 * @throws Exception
		 */
		public function saveCalculationElement ($data, $current_user, $elementId = 0) {
			if (!empty($data)) {
				$action     = ($elementId > 0) ? 'update' : 'insert';
				$element    = CalculationElement::getInstance ()
					->setId ($elementId)
					->setElementName ($this->sanitizeString ($data ['title']))
					->setFieldLabel ($data ['operLabel'])
					->setColumnName ($data ['operField'])
					->setDescription ($data ['description'])
					->setLocked ($data ['isLocked'])
					->setModuleName ($data ['module'])
					->setName ($data ['title'])
					->setPeriod ($data ['period'])
					->setPeriodField ($data ['periodField'])
					->setOperationName ($data ['oper'])
					->setRelatedModules ($data ['inRecord'])
					->setResult (0.00)
					->setSqlFilter ($data ['sqlFilter'])
					->setSqlData ($data ['arrayFilter'])
					->setUpdatedDate (date ('Y-m-d H:m:s'))
					->setStatus (CalculationElement::STATUS_ACTIVE);
				$cem        = CalculationElementManager::getInstance ($this->adb);
				$theElement = $cem->saveCalculationElement ($element, $action);
				if ($theElement) {
					$elementLocalValue = $this->getCalculationElementByName ($theElement->getElementName ());
					return (!is_array ($elementLocalValue)) ? $elementLocalValue : 0;
				}
			}
			return 0;
		}

		/**
		 * @param array $data
		 * @param $current_user
		 *
		 * @return float|integer
		 * @throws CalculationSystemException
		 * @throws Exception
		 */
		public function saveCalculateSystem ($data, $current_user) {
			date_default_timezone_set ($current_user->time_zone);
			$calculation = null;
			if (!empty ($data)) {
				if (key_exists ('equationId', $data)) {
					$equationId         = $data ['equationId'];
					$calculatedSystemId = $data ['recordId'];
					$action             = 'update';
				} else {
					$equationId         = ($this->getLastEquationId () + 1);
					$calculatedSystemId = 0;
					$action             = 'insert';
				}
				$equations     = $this->getEquations ($data, $equationId);
				$sql           = $this->equationResult ($equations);
				$equationValue = $this->getEnhancedCalculateSystemResult ($data ['moduleName'], $sql, 0, $data ['relatedModules']);
				$calculation   = CalculationSystem::getInstance ()
					->setId ($calculatedSystemId)
					->setCalculatedData (json_encode ($data['equation'], (JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT)))
					->setCalculationName ($this->sanitizeString ($data ['name']))
					->setDescription ($data ['description'])
					->setEquation ($equations)
					->setEquationId ($equationId)
					->setLocked ($data ['isLocked'])
					->setModuleName ($data ['moduleName'])
					->setName ($data ['name'])
					->setRelatedModules ($data ['relatedModules'])
					->setResult ($equationValue)
					->setStatus (CalculationSystemInterface::STATUS_ACTIVE)
					->setUpdatedDate (date ('Y-m-d H:m:s'));
				CalculationSystemManager::getInstance ($this->adb)->saveCalculationSystem ($calculation, $action);
				return $equationValue;
			} else {
				return 0;
			}
		}

		/**
		 * @param $id
		 *
		 * @return boolean|integer
		 * @throws Exception
		 */
		public function setStatusToCalculatedSystem ($id) {
			$status      = CalculationSystemInterface::STATUS_INACTIVE;
			$infoLogger  = 'Desactivado el cálculo: ';
			$csm         = CalculationSystemManager::getInstance ($this->adb);
			$calculation = $csm->fetchCalculationSystem ($id);

			if ($calculation) {
				if ($calculation->getStatus () == CalculationSystemInterface::STATUS_INACTIVE) {
					$calculation->setStatus (CalculationSystemInterface::STATUS_ACTIVE);
					$status     = CalculationSystemInterface::STATUS_ACTIVE;
					$infoLogger = 'Activado el cálculo: ';
				} else {
					$calculation->setStatus (CalculationSystemInterface::STATUS_INACTIVE);
				}

				$logFieldName = 'calculo_sistema_id_' . $id;
				$this->logger = new Logger ('calculatedsystem', array ('appender' => array ('File' => "{$this->platform}/logs/calculatedsystem/{$logFieldName}.log")));
				$this->logger->emit ('INFO', $infoLogger . $calculation->getName ());

				$csm->changeStatusToCalculatedSystem ($calculation);

				return ($status == CalculationSystemInterface::STATUS_INACTIVE) ? 0 : 1;
			} else {
				return false;
			}
		}

		/**
		 * Verifica si un campo es un campo calculado de grid (uitype 2204)
		 * 
		 * @param string $fieldName Nombre del campo (ej: subtotal_5820)
		 * @return bool true si es uitype 2204, false en caso contrario
		 */
		private function isCalculatedGridField($fieldName) {
			$result = $this->adb->pquery(
				"SELECT uitype FROM vtiger_subfields_special WHERE name = ? AND uitype = 2204 LIMIT 1",
				array($fieldName)
			);
			return ($result && $this->adb->num_rows($result) > 0);
		}
	}