<?php

	class CalculatedFieldsHelper {

		private static $SELECTED_UITYPE = array ('7', '9', '2204');

		/**
		 * Filtra y procesa valores de grid según la operación especificada
		 *
		 * @param array $valuesGrid
		 * @param string $relField
		 * @param string $colField
		 * @param string $operation
		 *
		 * @return array
		 */
		private static function filterGridValues ($valuesGrid, $relField, $colField, $operation = 'SUM') {
			$totalValues = count ($valuesGrid);
			$result = array();
			
			// Agrupar valores por relField
			$groupedValues = array();
			for ($j = 0; $j < $totalValues; $j++) {
				$relValue = $valuesGrid[$j][$relField];
				$colValue = floatval($valuesGrid[$j][$colField]);
				
				if (!isset($groupedValues[$relValue])) {
					$groupedValues[$relValue] = array();
					}
				$groupedValues[$relValue][] = $colValue;
			}
			
			// Aplicar operación según el tipo
			foreach ($groupedValues as $relValue => $values) {
				switch (strtoupper($operation)) {
					case 'SUM':
						$result[$relValue] = array_sum($values);
						break;
					case 'MAX':
						$result[$relValue] = max($values);
						break;
					case 'MIN':
						$result[$relValue] = min($values);
						break;
					case 'COUNT':
						$result[$relValue] = count($values);
						break;
					case 'AVG':
						$result[$relValue] = count($values) > 0 ? array_sum($values) / count($values) : 0;
						break;
					default:
						$result[$relValue] = array_sum($values); // Default a SUM
						break;
				}
			}
			
			return $result;
		}
		
		/**
		 * @param array $calculatedRefrence
		 *
		 * @return array
		 */
		private static function getReferenceToCalculated ($calculatedRefrence) {
			$referenceGroup = array ();
			foreach ($calculatedRefrence as $reference) {
				if (!empty($reference)) {
					$referenceGroup[] = $reference;
				}
			}
			return $referenceGroup;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $table
		 * @param string $fieldId
		 * @param string $value
		 *
		 * @return integer
		 * @throws Exception
		 */
		public static function getRecord ($adb, $table, $fieldId, $value) {
			$first     = true;
			$recordId  = 0;
			$where     = '';
			$result    = $adb->query ("SHOW COLUMNS FROM {$table}");
			$numOfRows = $adb->num_rows ($result);

			if ($numOfRows > 0) {
				while ($row = $adb->fetchByAssoc ($result)) {
					if ($row ['field'] != $fieldId) {
						if ($first) {
							$where .= "CONVERT({$row ['field']} USING utf8) LIKE '{$value}' ";
							$first = false;
						} else {
							$where .= " OR CONVERT({$row ['field']} USING utf8) LIKE '{$value}' ";
						}
					}
				}
				$result = $adb->query(
					"SELECT
 						   t.{$fieldId} AS id
 						  FROM {$table} t
 						  INNER JOIN vtiger_crmentity e ON e.crmid = t.{$fieldId} 
 						  WHERE
 						   e.deleted=0 
 						  AND ({$where})"
				);

				$numOfRows = $adb->num_rows ($result);
				if ($numOfRows > 0) {
					$row = $adb->fetchByAssoc ($result);
					$recordId = $row ['id'];
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
			}
			return $recordId;
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return array|boolean
		 * @throws Exception
		 */
		public static function getModulesWithGridCalculatedFields ($adb) {
			$results = $adb->pquery ('SHOW TABLES LIKE ?', array ('vtiger_subfields_special'));
			if (!$adb->num_rows ($results)) {
				return false;
			}
			$results      = $adb->pquery (
				'SELECT vtiger_field.fieldid, vtiger_field.fieldlabel, vtiger_tab.tablabel, vtiger_tab.name, vtiger_subfields_special.subfieldsid, vtiger_subfields_special.label
				FROM vtiger_field
				INNER JOIN vtiger_tab ON  vtiger_tab.tabid = vtiger_field.tabid
				INNER JOIN vtiger_subfields_special ON  vtiger_subfields_special.fieldid = vtiger_field.fieldid
				WHERE vtiger_field.uitype = ?
				AND vtiger_subfields_special.uitype = ?
				AND vtiger_field.tabid NOT IN(9,10,16,15,8,29)
				AND vtiger_tab.presence = 0 AND vtiger_tab.isentitytype=1',
				array ('2202', '2204')
			);
			$resultsArray = array ();
			$numOfRows    = $adb->num_rows ($results);
			if ($numOfRows > 0) {
				while ($row = $adb->fetchByAssoc ($results)) {
					$resultsArray[] = $row;
				}
				return $resultsArray;
			} else {
				return false;
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $fieldId
		 *
		 * @return array
		 * @throws Exception
		 */
		public static function getGridColumns ($adb, $fieldId) {
			$resultsArray = array ();
			$dummy        = join ("','", self::$SELECTED_UITYPE);
			$results      = $adb->pquery (
				"SELECT `subfieldsid`, `name`, `label`, `action_field`, `uitype`
				FROM vtiger_subfields_special
				WHERE `fieldid` = ?
				AND uitype IN('{$dummy}')",
				array ($fieldId)
			);
			$numOfRows    = $adb->num_rows ($results);
			if ($numOfRows > 0) {
				while ($row = $adb->fetchByAssoc ($results)) {
					$resultsArray[] = $row;
				}
			}
			return $resultsArray;
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $subfieldId
		 *
		 * @return array|boolean
		 * @throws Exception
		 */
		public static function getEquationData ($adb, $subfieldId) {
			$sql = 'SELECT `action_field`
 					FROM vtiger_subfields_special
					WHERE subfieldsid = ?';

			$result = $adb->pquery ($sql, array ($subfieldId));

			if (($result) && ($adb->num_rows ($result) > 0)) {
				$row = $adb->fetchByAssoc ($result, -1, false);
				if ($row ['action_field'] != 'null') {
					return unserialize (base64_decode ($row ['action_field']));
				} else {
					return false;
				}
			}
			return false;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $tabName
		 * @param string $tableName
		 *
		 * @return string|null
		 */
		public static function getFieldRelFromTable ($adb, $tabName, $tableName) {
			if (empty ($tabName) || empty ($tableName)) {
				return null;
			}
			$results = $adb->pquery(
				'SELECT
				    fieldname
				FROM
				    vtiger_field f
				INNER JOIN vtiger_fieldmodulerel fmr ON f.fieldid = fmr.fieldid
				WHERE
				    fmr.module=? AND fmr.relmodule = (
					SELECT
					    t.name
					FROM
					    vtiger_tab t
					INNER JOIN vtiger_field f ON t.tabid = f.tabid
					WHERE
					    f.tablename=?
					LIMIT 1
				)',
				array ($tabName, $tableName)
			);
			if ($adb->num_rows ($results) > 0) {
				$row = $adb->fetchByAssoc ($results);
				$fieldName = $row ['fieldname'];
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($fieldName)) ? $fieldName : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $tableName
		 *
		 * @return mixed|null
		 */
		public static function getFieldIdFromTable ($adb, $tableName) {
			if (empty ($tableName)) {
				return null;
			}
			$results = $adb->pquery(
				'SELECT
				    e.entityidfield
				FROM
				    vtiger_entityname e
				INNER JOIN vtiger_field f ON e.tabid = f.tabid
				WHERE
				    f.tablename=?
				LIMIT 1',
				array ($tableName)
			);
			if ($adb->num_rows ($results) > 0) {
				$row = $adb->fetchByAssoc ($results);
				$entityFieldId = $row ['entityidfield'];
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($entityFieldId)) ? $entityFieldId : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $tabName
		 *
		 * @return array|null
		 */
		public static function getRelatedTables ($adb, $tabName) {
			if (empty ($tabName)) {
				return null;
			}
			$results = $adb->pquery (
				'SELECT DISTINCT
				    f.tablename
				FROM
					vtiger_field f
				INNER JOIN vtiger_tab t ON f.tabid = t.tabid
				INNER JOIN vtiger_fieldmodulerel r ON t.name = r.relmodule
				WHERE
				   r.module=?',
				array ($tabName)
			);
			
			if ($adb->num_rows ($results) > 0) {
				while ($row = $adb->fetchByAssoc ($results)) {
					$relatedTables [] = $row ['tablename'];
				}
			}
			DatabaseUtils::closeResult ($results);
			$results = null;
			return (isset ($relatedTables)) ? $relatedTables : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $fieldId
		 *
		 * @return string|boolean
		 * @throws Exception
		 */
		public static function getModuleName ($adb, $fieldId) {
			$sql = 'SELECT vtiger_tab.tablabel, vtiger_tab.name
					FROM vtiger_tab
					INNER JOIN vtiger_field ON  vtiger_field.tabid = vtiger_tab.tabid
					WHERE vtiger_field.fieldid = ?';
			$results = $adb->pquery ($sql, array ($fieldId));

			if (($results) && ($adb->num_rows ($results) > 0)) {
				$row = $adb->fetchByAssoc ($results);
				return $row;
			}
			return false;
		}

		/**
		 * Resuelve los nombres reales de los campos grid usando el fieldid
		 * 
		 * @param PearDatabase $adb
		 * @param string $gridFieldName Nombre del campo grid (ej: articulos_venta)
		 * @param string $baseFieldName Nombre base del campo (ej: cantidad, articulo)
		 * @param string $moduleName Nombre del módulo para obtener el tabid correcto
		 * 
		 * @return string|null Nombre real del campo (ej: cantidad_5820) o null si no se encuentra
		 */
		public static function resolveGridFieldName($adb, $gridFieldName, $baseFieldName, $moduleName) {
			// Obtener el fieldid del campo grid incluyendo el tabid del módulo
			$sql = "SELECT f.fieldid FROM vtiger_field f INNER JOIN vtiger_tab t ON t.tabid = f.tabid WHERE f.columnname = '".$gridFieldName."' AND f.uitype = 2202 AND t.name = '".$moduleName."'";
			//$result = $adb->pquery($sql, array($gridFieldName, $moduleName));
			$result = $adb->pquery($sql);
			
			if (!$result || $adb->num_rows($result) == 0) {
				return null;
			}
			
			$row = $adb->fetchByAssoc($result);
			$fieldId = $row['fieldid'];
			
			// Buscar el campo real en vtiger_subfields_special
			$sql = "SELECT name FROM vtiger_subfields_special WHERE fieldid = ? AND name LIKE ?";
			$searchPattern = $baseFieldName . '_%';
			$result = $adb->pquery($sql, array($fieldId, $searchPattern));
			
			if (!$result || $adb->num_rows($result) == 0) {
				return null;
			}
			
			$row = $adb->fetchByAssoc($result);
			$realFieldName = $row['name'];
			
			return $realFieldName;
		}

		/**
		 * Gets the module name for a record ID by querying vtiger_crmentity
		 * 
		 * @param PearDatabase $adb Database connection
		 * @param integer $recordId ID of the record
		 * @return string|null Module name or null if not found
		 */
		private static function getModuleNameByRecordId($adb, $recordId) {
			$sql = "SELECT setype FROM vtiger_crmentity WHERE crmid = ? AND deleted = 0";
			$result = $adb->pquery($sql, array($recordId));
			
			if ($result && $adb->num_rows($result) > 0) {
				$moduleName = $adb->query_result($result, 0, 'setype');
				return $moduleName;
			}
			return null;
		}

		/**
		 * Gets the display value for a record based on the module's main display field
		 * 
		 * @param PearDatabase $adb Database connection
		 * @param integer $recordId ID of the record to get display value for
		 * @param string $moduleName Name of the module
		 * @return string|null Display value or null if not found
		 */
		private static function getDisplayValueForRecord($adb, $recordId, $moduleName) {
			try {
				// Get the main entity data for the module
				$entityData = PlatformUtils::getCrmEntity($adb, $moduleName);
				$displayField = $entityData->list_link_field;
				
				$sql = "SELECT $displayField FROM {$entityData->table_name} WHERE {$entityData->table_index} = ?";
				$result = $adb->pquery($sql, array($recordId));
				
				if ($result && $adb->num_rows($result) > 0) {
					$displayValue = $adb->query_result($result, 0, $displayField);
					return $displayValue;
				}
			} catch (Exception $e) {
				error_log("[CalculatedFieldsHelper::getDisplayValueForRecord] Error getting display value for record $recordId: " . $e->getMessage());
			}
			return null;
		}

		/**
		 * Gets the main grid field name for a module
		 * 
		 * @param PearDatabase $adb Database connection
		 * @param string $moduleName Name of the module
		 * @return string|null Grid field name or null if not found
		 */
		public static function getGridFieldNameFromModule($adb, $moduleName) {
			$sql = "SELECT f.fieldname FROM vtiger_field f 
					INNER JOIN vtiger_tab t ON t.tabid = f.tabid 
					WHERE f.uitype = 2202 AND t.name = ? LIMIT 1";
			$result = $adb->pquery($sql, array($moduleName));
			
			if ($result && $adb->num_rows($result) > 0) {
				$gridFieldName = $adb->query_result($result, 0, 'fieldname');
				return $gridFieldName;
			}
			return null;
		}

		/**
		 * Gets the fieldid for a grid field
		 * 
		 * @param PearDatabase $adb Database connection
		 * @param string $gridFieldName Name of the grid field
		 * @param string $moduleName Name of the module
		 * @return integer|null Field ID or null if not found
		 */
		private static function getGridFieldId($adb, $gridFieldName, $moduleName) {
			$sql = "SELECT f.fieldid FROM vtiger_field f 
					INNER JOIN vtiger_tab t ON t.tabid = f.tabid 
					WHERE f.fieldname = ? AND f.uitype = 2202 AND t.name = ?";
			$result = $adb->pquery($sql, array($gridFieldName, $moduleName));
			
			if ($result && $adb->num_rows($result) > 0) {
				$fieldId = $adb->query_result($result, 0, 'fieldid');
				return $fieldId;
			}
			return null;
		}

		/**
		 * Gets the subfieldsid for a specific subfield
		 * 
		 * @param PearDatabase $adb Database connection
		 * @param integer $fieldId ID of the main grid field
		 * @param string $subfieldName Name of the subfield (e.g., "cantidad_5820")
		 * @return integer|null Subfields ID or null if not found
		 */
		private static function getSubfieldsId($adb, $fieldId, $subfieldName) {
			$sql = "SELECT subfieldsid FROM vtiger_subfields_special 
					WHERE fieldid = ? AND name = ?";
			$result = $adb->pquery($sql, array($fieldId, $subfieldName));
			
			if ($result && $adb->num_rows($result) > 0) {
				$subfieldsId = $adb->query_result($result, 0, 'subfieldsid');
				return $subfieldsId;
			}
			return null;
		}

		/**
		 * Applies the specified operation to an array of values
		 * This method unifies operation processing for both cross-record and traditional grid data
		 * 
		 * @param array $values Array of numeric values
		 * @param string $operation Operation to perform (SUM, COUNT, AVG, MAX, MIN)
		 * @return array Array structured for _IN_ARRAY_ processing (simple values for cross-record, keyed for traditional)
		 */
		private static function applyOperationToValues($values, $operation) {
			if (empty($values)) {
				return array();
			}
			
			$operationUpper = strtoupper($operation);
			
			switch ($operationUpper) {
				case 'SUM':
					// Return values as-is for summing
					$result = $values;
					break;
					
				case 'COUNT':
					// Convert to array of 1s for counting
					$result = array_fill(0, count($values), 1);
					break;
					
				case 'AVG':
					// Return values as-is for averaging (calculation done later)
					$result = $values;
					break;
					
				case 'MAX':
					// Return single maximum value
					$maxValue = max($values);
					$result = array($maxValue);
					break;
					
				case 'MIN':
					// Return single minimum value
					$minValue = min($values);
					$result = array($minValue);
					break;
					
				default:
					// Default to SUM behavior
					$result = $values;
					break;
			}
			
			return $result;
		}

		/**
		 * @deprecated Use applyOperationToValues instead. Kept for backward compatibility.
		 */
		private static function buildArrayForInArrayProcessing($values, $fieldName, $operation) {
			return self::applyOperationToValues($values, $operation);
		}

		/**
		 * Checks if a field name is already resolved (contains underscore + numbers)
		 * 
		 * @param string $fieldName Field name to check
		 * @return boolean True if already resolved, false otherwise
		 */
		private static function isFieldNameResolved($fieldName) {
			return (strpos($fieldName, '_') !== false && preg_match('/\d+$/', $fieldName));
		}

		/**
		 * Ensures a field name is resolved, resolving it if necessary
		 * 
		 * @param PearDatabase $adb Database connection
		 * @param string $fieldName Field name to resolve
		 * @param string $gridFieldName Grid field name
		 * @param string $moduleName Module name
		 * @return string|null Resolved field name or null if resolution fails
		 */
		private static function ensureFieldNameResolved($adb, $fieldName, $gridFieldName, $moduleName) {
			if (self::isFieldNameResolved($fieldName)) {
				return $fieldName;
			}
			
			$resolvedName = self::resolveGridFieldName($adb, $gridFieldName, $fieldName, $moduleName);
			if ($resolvedName) {
				return $resolvedName;
			}
			
			return null;
		}

		/**
		 * Searches for values in grids from other records based on relation field matches
		 * This function implements cross-record grid searching for calculated fields
		 * 
		 * @param PearDatabase $adb Database connection
		 * @param CalculationElement $theElement Calculation element configuration
		 * @param mixed $targetRecord Target record ID to search for
		 * @return array Array structured for _IN_ARRAY_ processing
		 */
		public static function getGridValuesFromOtherRecords($adb, $theElement, $targetRecord) {
			
			// STEP 1: Parse element configuration
			$relatedModules = $theElement->getRelatedModules(); // "vtiger_subfields_special.articulo"
			$columnName = $theElement->getColumnName();         // "vtiger_subfields_special.cantidad"
			$sourceModule = $theElement->getModuleName();       // "orden_de_venta"
			$operation = $theElement->getOperationName();       // "SUM", "AVG", "MAX", "MIN", "COUNT"
			
			// Clean relatedModules format
			if (strpos($relatedModules, '.') === 0) {
				$relatedModules = substr($relatedModules, 1);
			}
			$parts = explode('.', $relatedModules);
			// Para formato "facturas.vtiger_subfields_special.articulo", el campo de relación es parts[2]
			$relationField = isset($parts[2]) ? $parts[2] : (isset($parts[1]) ? $parts[1] : null);
			
			// Get value field
			list($gridTable, $valueField) = explode('.', $columnName); // "cantidad"
			

			
			// STEP 2: Get display value for target record
			// First determine the correct module for the target record
			$targetModule = self::getModuleNameByRecordId($adb, $targetRecord);
			if (!$targetModule) {

				return array();
			}
			
			$targetValue = self::getDisplayValueForRecord($adb, $targetRecord, $targetModule);
			if (!$targetValue) {

				return array();
			}
			
			// Clean HTML entities from target value for proper comparison
			$cleanTargetValue = html_entity_decode($targetValue, ENT_QUOTES, 'UTF-8');
			
			
			// STEP 3: Get grid field ID and resolve real field names (if not already resolved)
			$gridFieldName = self::getGridFieldNameFromModule($adb, $sourceModule);
			if (!$gridFieldName) {

				return array();
			}
			
			// Ensure field names are resolved
			// Solo resolver el relationField si no es vtiger_subfields_special (que es nombre de tabla)
			if ($relationField && $relationField !== 'vtiger_subfields_special') {
			$realRelationField = self::ensureFieldNameResolved($adb, $relationField, $gridFieldName, $sourceModule);
			} else {
				$realRelationField = $relationField;
			}
			$realValueField = self::ensureFieldNameResolved($adb, $valueField, $gridFieldName, $sourceModule);
			
			if (!$realRelationField || !$realValueField) {
				return array();
			}
			
			// STEP 4: Get subfieldsid for both fields
			$gridFieldId = self::getGridFieldId($adb, $gridFieldName, $sourceModule);
			$relationSubfieldsId = self::getSubfieldsId($adb, $gridFieldId, $realRelationField);
			$valueSubfieldsId = self::getSubfieldsId($adb, $gridFieldId, $realValueField);
			
			if (!$relationSubfieldsId || !$valueSubfieldsId) {
				return array();
			}
			
			// STEP 4: Parse and apply WHERE filter
			$whereClause = "";
			$sqlFilter = $theElement->getSqlFilter();
			if (!empty($sqlFilter)) {
				// Parse SQL filter
				$where = json_decode(str_replace('&quot;', '"', $sqlFilter));
				if ($where === null) {
					// If JSON decode fails, use as plain text
					$where = str_replace('&quot;', '"', $sqlFilter);
					$where = trim($where, '"');
				}
				
				// Replace __RECORD__ placeholder with proper SQL condition using alias
				if (is_string($where)) {
					// Get the target record's module entity to build proper condition
					$targetEntity = PlatformUtils::getCrmEntity($adb, $targetModule);
					if ($targetEntity && !empty($targetEntity->table_name) && !empty($targetEntity->table_index)) {
						// Use alias 't' instead of full table name to match the JOIN
						$targetCondition = "t.{$targetEntity->table_index}={$targetRecord}";
						$whereClause = str_replace('__RECORD__', $targetCondition, $where);
					} else {
						$whereClause = str_replace('__RECORD__', $targetRecord, $where); // Fallback
					}
				} else if (is_object($where) || is_array($where)) {
					// Handle complex WHERE structures if needed
					$whereClause = ""; // For now, skip complex filters
				}			
			}
			
			// STEP 5: Build SQL query with WHERE filter applied
			$sql = "SELECT c.crmid FROM vtiger_crmentity c";
			$whereConditions = array("c.setype = '$sourceModule'", "c.deleted = 0");
			
			if (!empty($whereClause)) {
				// For grid elements, we need to join with both source and target module tables
				$sourceEntity = PlatformUtils::getCrmEntity($adb, $sourceModule);
				$targetEntity = PlatformUtils::getCrmEntity($adb, $targetModule);
				
				if ($sourceEntity && !empty($sourceEntity->table_name) && !empty($sourceEntity->table_index)) {
					$sql .= " INNER JOIN {$sourceEntity->table_name} s ON s.{$sourceEntity->table_index} = c.crmid";
				}
				
				if ($targetEntity && !empty($targetEntity->table_name) && !empty($targetEntity->table_index)) {
					$sql .= " INNER JOIN {$targetEntity->table_name} t ON t.{$targetEntity->table_index} = {$targetRecord}";
				}
				
				$whereConditions[] = "($whereClause)";
				
			}
			
			$sql .= " WHERE " . implode(' AND ', $whereConditions);
			
			$result = $adb->query($sql);
			
			$allMatchingValues = array(); // Array with all found values
			$recordsProcessed = 0;

			while ($row = $adb->fetchByAssoc($result)) {
				$recordId = $row['crmid'];
				$recordsProcessed++;
				
				// Get relation array for this record
				$relationBlob = getFieldGridValue($relationSubfieldsId, $recordId);
				if (!$relationBlob) continue;
				
				$relationArray = unserialize(base64_decode($relationBlob));
				if (!is_array($relationArray)) continue;

				// Find indexes where target value appears
				$matchingIndexes = array_keys($relationArray, $cleanTargetValue);
				
				if (!empty($matchingIndexes)) {
					// Get value array for this record
					$valueBlob = getFieldGridValue($valueSubfieldsId, $recordId);
					if (!$valueBlob) continue;
					
					$valueArray = unserialize(base64_decode($valueBlob));
					if (!is_array($valueArray)) continue;
					
					// Extract values at matching indexes
					foreach ($matchingIndexes as $index) {
						if (isset($valueArray[$index])) {
							$allMatchingValues[] = floatval($valueArray[$index]);
						}
					}
				} 
			}

			// STEP 6: Build array for _IN_ARRAY_ processing
			$processedArray = self::buildArrayForInArrayProcessing($allMatchingValues, $realValueField, $operation);
			
			return $processedArray;
		}

		/**
		 * @param PearDatabase $adb
		 * @param CalculationElement $theElement
		 * @param integer $record
		 *
		 * @return integer|array
		 */
		public static function getValueFromGrid ($adb, $theElement, $record = 0) {
			
			// OPTIMIZACIÓN: Usar tablas summary para campos calculados (uitype 2204)
			$optimizedValue = self::getValueFromSummaryTable($adb, $theElement, $record);
			if ($optimizedValue !== null) {
				return $optimizedValue;
			}
			
			$valuesGrid                                               = array ();
			
			// Manejar caso cuando getRelatedModules() está vacío (válido para elementos grid simples)
			$relatedModules = $theElement->getRelatedModules();
			if (!empty($relatedModules)) {
				// Manejar formato incorrecto que empieza con punto
				if (strpos($relatedModules, '.') === 0) {
					$relatedModules = substr($relatedModules, 1); // Quitar el punto inicial
				}
				
				$parts = explode('.', $relatedModules);
				if (count($parts) >= 3) {
					list ($relModule, $fieldGridName, $gridTable, $relField) = array_pad($parts, 4, null);
				} else {
					// Formato simplificado: tabla.campo
					$relModule = $theElement->getModuleName();
					$fieldGridName = null; // Se determinará automáticamente
					$gridTable = $parts[0];
					$relField = isset($parts[1]) ? $parts[1] : null;
				}
			} else {
				// Para elementos grid simples, usar valores por defecto
				$relModule = $theElement->getModuleName();
				$fieldGridName = null; // Se determinará automáticamente
				$relField = null;
			}
			
			list ($gridTable,  $colField)                             = explode ('.', $theElement->getColumnName ());
			$where                                                    = json_decode (str_replace ('&quot;', '"', $theElement->getSqlFilter ()));

			// CORRECCIÓN: Si json_decode falla, usar el filtro como texto plano
			if ($where === null) {
				$where = str_replace ('&quot;', '"', $theElement->getSqlFilter ());
				// Quitar comillas externas si existen
				$where = trim($where, '"');
			}

			$fieldObjects = FieldManager::getInstance ($adb)->fetchFields ($theElement->getModuleName ());
			foreach ($fieldObjects as $field) {
				if (!empty($field->getGrid ())) {
					// Si fieldGridName es null, usar el nombre del campo grid encontrado
					$gridFieldName = ($fieldGridName !== null) ? $fieldGridName : $field->getName();

					// NOTE: Field names should already be resolved in getCalculationElementByName()
					// Use unified resolution method as fallback
					$resolvedColField = self::ensureFieldNameResolved($adb, $colField, $gridFieldName, $theElement->getModuleName());
					if ($resolvedColField) {
						$colField = $resolvedColField;
					}
					
					if (!empty($relField)) {
						$resolvedRelField = self::ensureFieldNameResolved($adb, $relField, $gridFieldName, $theElement->getModuleName());
						if ($resolvedRelField) {
							$relField = $resolvedRelField;
						}
					}
					$valuesGrid = GridFieldUtils::getGridValues ($adb, $theElement->getModuleName (), $gridFieldName, $record);
					break;
				}
			}
			// Track if we got data from cross-record search
			$usedCrossRecordSearch = false;
			
			if (empty ($valuesGrid)) {
				// If traditional grid search returns empty, try cross-record search
				// This is specifically for calculated fields that need to search across other records
				$valuesGrid = self::getGridValuesFromOtherRecords($adb, $theElement, $record);

			if (empty ($valuesGrid)) {
				return array ();
			}

				$usedCrossRecordSearch = true;
			}

			// Only apply WHERE filter if we got data from traditional grid search
			// Cross-record search data is already filtered and shouldn't be filtered again
			if (!empty ($where) && !$usedCrossRecordSearch) {
				$actualDieOnError = $adb->dieOnError;
				$adb->setDieOnError (false);
				$entity    = PlatformUtils::getCrmEntity ($adb, $theElement->getModuleName ());
				$condition = "{$entity->table_name}.{$entity->table_index}={$record}";
				
				// Manejar diferentes tipos de filtro
				if (is_string($where)) {
					$whereClause = str_replace('__RECORD__', $condition, $where);
				} else {
					// Si es array u objeto, convertir a string (caso JSON complejo)
					$whereClause = $condition; // Fallback simple
				}
				
				$result    = $adb->query("SELECT * FROM {$entity->table_name} WHERE {$whereClause}");
				$adb->setDieOnError($actualDieOnError);

				if (!$result) {
					$_SESSION ['flashmessage'] = array(
						'iserror' => true,
						'message' => "en la configuración del filtro del elemento calculado&nbsp;<a title='" . $theElement->getDescription() . "'   href='index.php?module=calculated_fields&amp;action=index&amp;parenttab=Settings'>{$theElement->getName ()} </a>",
						'data'    => null,
					);
				}

				return ((!$result) || ($adb->num_rows ($result) < 1)) ? array () : self::filterGridValues ($valuesGrid, $relField, $colField);
			} else if ($usedCrossRecordSearch) {
			}
			
			// For cross-record search data, we don't need to filter - just apply the operation
			if ($usedCrossRecordSearch) {
				// Data is already processed and ready for operation
				return $valuesGrid;
			} else {
				// For traditional grid data, apply filtering
				return self::filterGridValues ($valuesGrid, $relField, $colField);
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param array $fieldsData
		 * @param integer $subfieldId
		 *
		 * @return string
		 */
		public static function saveCalculatedField ($adb, $fieldsData, $subfieldId) {
			$typeElement        = $fieldsData ['typeElement'];
			$subFieldName       = $fieldsData ['subFieldName'];
			$calculatedRefrence = $fieldsData ['calculatedRefrence'];
			$elemValue          = $fieldsData ['elemValue'];
			$operator           = $fieldsData ['operator'];
			$operatorGroup      = $fieldsData ['operatorGroup'];
			$totalOperations    = count ($typeElement);
			$equation           = '';
			$operated           = '(';
			$indexGroup         = 0;
			$indexOperator      = 0;
			$actionField        = base64_encode (serialize ($fieldsData));

			$referenceGroup = self::getReferenceToCalculated ($calculatedRefrence);

			$operatedGroup = array_fill_keys ($referenceGroup, '');

			for ($op = 0; $op < $totalOperations; $op++) {
				switch ($typeElement[ $op ]) {
					case 'c':
						$needlePos = strpos ($subFieldName[ $op ], '@');
						if ($needlePos !== false) {
							list($field, $dividend) = explode ('@', $subFieldName[ $op ]);
							$results = ($dividend == 9) ? '/100' : '';
							$field   = "{$field}[]";
						} else {
							$results = '';
							$field   = "{$subFieldName[ $op ]}[]";
						}
						$operated .= " Number(jQuery(\"input[name='" . $field . "']\").map(function(){return jQuery(this).val()" . $results . ';}).get( x ))';
						break;
					case 'v':
						$operated .= $elemValue[ $op ];
						break;
					case 'r':
						if (key_exists ($calculatedRefrence[ $op ], $operatedGroup)) {
							$operated .= $operatedGroup[ $calculatedRefrence[ $op ] ];
						} else {
							$operated .= $calculatedRefrence[ $op ];
						}
						break;
					default:
						$operated .= '';
						break;
				}

				if ((($op % 2) == 1) && ($op > 0)) {
					if ($op < ($totalOperations - 1)) {
						$operated .= ')';
						$operatedGroup[ $referenceGroup[ $indexGroup ] ] = $operated;
						$equation .= $operated . $operatorGroup[ $indexGroup ];
						$operated = '(';
						$indexGroup++;
					} else {
						$operated .= ')';
						$equation .= $operated;
					}
				} else {
					$operated .= $operator[ $indexOperator ];
					$indexOperator++;
				}
			}

			$adb->pquery (
				'UPDATE vtiger_subfields_special SET `data_field` = ?, `action_field` = ? WHERE subfieldsid = ? ',
				array ($equation, $actionField, $subfieldId)
			);
			return $equation;
		}

	/**
	 * Obtiene valores de campos calculados de grid usando tablas summary optimizadas
	 * 
	 * @param PearDatabase $adb
	 * @param CalculationElement $theElement
	 * @param int $record
	 * @return array|null Array con valores si es campo calculado, null si debe usar método tradicional
	 */
	private static function getValueFromSummaryTable($adb, $theElement, $record) {
		// Verificar si es un campo de vtiger_subfields_special
		$columnName = $theElement->getColumnName();
		if (strpos($columnName, 'vtiger_subfields_special.') !== 0) {
			return null; // No es campo grid
		}
		
		// Extraer el nombre del campo
		list($table, $fieldName) = explode('.', $columnName, 2);
		
		// Verificar si es uitype 2204 (campo calculado)
		$result = $adb->pquery(
			"SELECT sf.uitype, sf.fieldid, f.columnname 
			 FROM vtiger_subfields_special sf 
			 INNER JOIN vtiger_field f ON f.fieldid = sf.fieldid 
			 WHERE sf.name = ? AND sf.uitype = 2204 LIMIT 1",
			array($fieldName)
		);
		
		if (!$result || $adb->num_rows($result) == 0) {
			return null; // No es campo calculado uitype 2204
		}
		
		$row = $adb->fetchByAssoc($result);
		$gridColumnName = $row['columnname'];
		$moduleName = $theElement->getModuleName();
		
		// Construir nombre de tabla summary
		$summaryTableName = "vtiger_grid_summary_{$gridColumnName}";
		
		// Verificar si la tabla summary existe
		$tableExists = $adb->pquery("SHOW TABLES LIKE ?", array($summaryTableName));
		if (!$tableExists || $adb->num_rows($tableExists) == 0) {
			return null; // Tabla summary no existe, usar método tradicional
		}
		
		// Verificar si la columna existe en la tabla summary
		$columnExists = $adb->pquery(
			"SHOW COLUMNS FROM {$summaryTableName} LIKE ?",
			array($fieldName)
		);
		if (!$columnExists || $adb->num_rows($columnExists) == 0) {
			return null; // Columna no existe en tabla summary, usar método tradicional
		}
		
		// Si record es 0 (sin registro específico), no intentar consultar
		if ($record == 0 || empty($record)) {
			return null; // Sin registro específico, usar método tradicional
		}
		
		// Obtener valor directamente de tabla summary
		// Las tablas summary usan recordid como columna de ID
		$summaryResult = $adb->pquery(
			"SELECT {$fieldName} FROM {$summaryTableName} WHERE recordid = ?",
			array($record)
		);
		
		if ($summaryResult && $adb->num_rows($summaryResult) > 0) {
			$summaryRow = $adb->fetchByAssoc($summaryResult);
			$value = $summaryRow[$fieldName];
			
			// Retornar en formato esperado por el sistema
			return array($value);
		}
		
		return null; // No se encontró valor, usar método tradicional
	}

}
