<?php
/*+*******************************************************************************
 *  The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *
 *********************************************************************************/

require_once 'data/CRMEntity.php';
require_once 'modules/CustomView/CustomView.php';
require_once 'include/utils/CommonUtils.php';
require_once ('include/utils/GridFieldUtils.class.php');
require_once 'include/Webservices/Utils.php';
require_once 'include/Webservices/RelatedModuleMeta.php';

/**
 * Description of QueryGenerator
 *
 * @author MAK
 */
class QueryGenerator {
	private $module;
	private $customViewColumnList;
	private $stdFilterList;
	private $conditionals;
	private $manyToManyRelatedModuleConditions;
	private $groupType;
	private $whereFields;
	/**
	 *
	 * @var VtigerCRMObjectMeta
	 */
	private $meta;
	/**
	 *
	 * @var Users
	 */
	private $user;
	private $advFilterList;
	private $fields;
	private $referenceModuleMetaInfo;
	private $moduleNameFields;
	private $referenceFieldInfoList;
	private $referenceFieldList;
	private $ownerFields;
	private $columns;
	private $fromClause;
	private $whereClause;
	private $query;
	private $groupInfo;
	private $conditionInstanceCount;
	private $conditionalWhere;
	public static $AND = 'AND';
	public static $OR = 'OR';
	private $customViewFields;
	public $summaryTable;
	public $summaryLabels;
	public $summaryColumns;

		/** checkGridSummaryTableIntegrity
		 * Verifica la integridad de la tabla temporal de totales de un campo grid.
		 *
		 * Toma una muestra aleatoria del 3% (máx 100 registros) de la tabla temporal de totales, compara cada total con el valor real
		 * disponible en vtiger_subfields_values. Si existe alguna diferencia, devuelve false; si todo es íntegro, devuelve true.
		 *
		 * @param PearDatabase $adb Conexión a la base de datos
		 * @param string $moduleName Nombre del módulo
		 * @param string $gridName Nombre del campo grid
		 * @param string $temporaryTable Nombre de la tabla temporal (ej: vtiger_grid_summary_<campo grid>)
		 * @return bool true si la tabla es íntegra, false si debe ser reconstruida
		 */
		public static function checkGridSummaryTableIntegrity($adb, $moduleName, $gridName, $temporaryTable) {
			// 1. Contar registros y calcular tamaño de muestra
			$resCount = $adb->pquery("SELECT COUNT(*) as total FROM `$temporaryTable`");
			if (!$resCount || $adb->num_rows($resCount) ==0) {
				if ($resCount) { $resCount->free(); unset($resCount); }
				return false; // Si no hay resultado, hay que reconstruir la tabla temporal
			}
			$total = intval($adb->query_result($resCount, 0, 'total'));
			if ($total == 0) {
				return false;//No hay datos de totales. Reconstruir.
			}
			$sampleSize = max(5, min(600, ceil($total * 0.05)));

			// 2. Obtener muestra aleatoria única
			$aux = "SELECT DISTINCT recordid FROM ".$temporaryTable;
			$resAllIds = $adb->pquery($aux);
			if (!$resAllIds || $adb->num_rows($resAllIds) == 0) {
				if ($resAllIds) { $resAllIds->free(); unset($resAllIds); }
				return false;// No hay registros en la temporal, reconstruir
			}
			$allIds = array();
			for ($i = 0; $i < $adb->num_rows($resAllIds); $i++) {
				$allIds[] = $adb->query_result($resAllIds, $i, 'recordid');
			};
			shuffle($allIds);
			$sampleIds = array_slice($allIds, 0, $sampleSize);
			
			// 3. Obtener columnas de la tabla temporal 
			$exp= "SELECT tabid FROM vtiger_entityname WHERE modulename='".$moduleName."'";
			$result = $adb->pquery($exp);
			if (!$result || $adb->num_rows($result) == 0) {
				unset($exp,$sampleIds);
				if ($resAllIds) { $resAllIds->free(); 
				unset($resAllIds); }
				if ($result) {
					$result->free(); 
					unset($result); 
				}
				return true;//No existe el módulo
			}
			$vtabid= $adb->query_result($result, 0, 'tabid');
			$exp= "SELECT fieldid as fieldid FROM vtiger_field WHERE columnname='".$gridName."' AND tabid = ". $vtabid;

			$result = $adb->pquery($exp);
			if (!$result || $adb->num_rows($result) == 0) {
				if ($result) { $result->free(); unset($result); }
				if ($resAllIds) { $resAllIds->free(); unset($resAllIds); }
				unset($exp, $sampleIds);
				return true;
			}
			$fieldid_t= $adb->query_result($result, 0, 'fieldid');
			
			$exp= "SELECT name, subfieldsid FROM vtiger_subfields_special WHERE fieldid =".$fieldid_t. " AND `uitype`=2204";
			$resCols = $adb->pquery($exp);
			$columns = array();
			for ($i = 0; $i < $adb->num_rows($resCols); $i++) {
				$colName = $adb->query_result($resCols, $i, 'name');
				$subfieldid = $adb->query_result($resCols, $i, 'subfieldsid');
				$columns[] = ['name' => $colName, 'subfieldsid' => $subfieldid];
			}
			// 4. Para cada registro de la muestra
			foreach ($sampleIds as $recordId) {
				// Obtener fila de la tabla temporal
				$resRow = $adb->pquery("SELECT * FROM `$temporaryTable` WHERE recordid=?", array($recordId));
				if (!$resRow || $adb->num_rows($resRow) == 0) continue;
				$rowStored = $adb->fetch_array($resRow);
				$i=0;
				foreach ($columns as $colx) {
					$column = $colx['name'];
					$subfieldsid = $colx['subfieldsid'];
					$valorTemporal = isset($rowStored[$column]) ? floatval($rowStored[$column]) : 0;

					// Obtener valor real desde vtiger_subfields_values
					$resVal = $adb->pquery("SELECT field_values FROM vtiger_subfields_values WHERE subfieldsid = ? AND modulecfid = ?", array($subfieldsid, $recordId));
					$valorReal = 0;
					if ($resVal && $adb->num_rows($resVal)) {
						$fieldValuesArr = unserialize(base64_decode($adb->query_result($resVal, 0, 'field_values')));
						$fieldValuesArrCount = count($fieldValuesArr);
						for ($i = 0; $i < $fieldValuesArrCount; $i++) {		
							$valorReal = $valorReal + floatval($fieldValuesArr[$i]);
						}
						$valorReal = round(floatval($valorReal), 2);
					}
					// 3.4 Comparar con tolerancia para decimales
					if (is_numeric($valorTemporal) && is_numeric($valorReal)) {
						if (abs($valorTemporal - $valorReal) > 0.001) {
							if ($resRow) { $resRow->free(); unset($resRow); }
							if ($resAllIds) { $resAllIds->free(); unset($resAllIds); }
							$resVal->free(); unset($resVal, $fieldValuesArr);
							unset($exp, $sampleIds, $columns);
							return false;
						}
					}
				}
			}
			if ($resRow) { $resRow->free(); unset($resRow); }
			if ($resAllIds) { $resAllIds->free(); unset($resAllIds); }
			$resVal->free(); unset($resVal, $fieldValuesArr);
			unset($exp, $sampleIds, $columns);
			return true;
		}

	/**
	 * @codingStandardsIgnoreStart
	 * Esta función realiza comparaciones para ejecutar las clausulas WHERE
	 * Nota: aunque presenta Function's cyclomatic complexity no es posible refactorizar
	 * @param string $operator
	 * @param string $value
	 *
	 * @return string
	 */
	private function getSqlOperator ($operator, &$value) {
		$sqlOperator = '';
		switch($operator) {
			case 'e':
				$sqlOperator = '=';
				break;
			case 'n':
				$sqlOperator = '<>';
				break;
			case 's':
				$sqlOperator = 'LIKE';
				$value = "$value%";
				break;
			case 'ew':
				$sqlOperator = 'LIKE';
				$value = "%$value";
				break;
			case 'c':
				$sqlOperator = 'LIKE';
				$value = "%$value%";
				break;
			case 'k':
				$sqlOperator = 'NOT LIKE';
				$value = "%$value%";
				break;
			case 'l':
				$sqlOperator = '<';
				break;
			case 'g':
				$sqlOperator = '>';
				break;
			case 'm':
				$sqlOperator = '<=';
				break;
			case 'h':
				$sqlOperator = '>=';
				break;
			case 'a':
				$sqlOperator = '>';
				break;
			case 'b':
				$sqlOperator = '<';
				break;
			case 'in':
				$sqlOperator = 'IN';
				break;
			default:
				$sqlOperator = '';
				break;
		}
		return $sqlOperator;
	}
	/** @codingStandardsIgnoreEnd */

	/**
 * Constructor de QueryGenerator.
 * @codingStandardsIgnoreEnd 
 * Inicializa el generador de consultas SQL para un módulo específico, asociando la metadata necesaria,
 * el usuario actual y las estructuras internas para la construcción dinámica de queries (campos, filtros, vistas personalizadas, etc).
 * Este constructor prepara el objeto para construir consultas avanzadas, incluyendo integración con Custom Views,
 * filtros avanzados y manejo de campos summary de grids.
 *
 * @param string $module Nombre del módulo para el cual se generarán las consultas (por ejemplo, 'Accounts', 'Contacts').
 * @param Users $user Objeto usuario actual, utilizado para permisos y restricciones en la generación de queries.
 *
 * @return void
 */
 		public function __construct($module, $user) {
			$db = PearDatabase::getInstance();
			$this->module = $module;
			$this->customViewColumnList = null;
			$this->stdFilterList = null;
			$this->conditionals = array();
			$this->user = $user;
			$this->advFilterList = null;
			$this->fields = array();
			$this->referenceModuleMetaInfo = array();
			$this->moduleNameFields = array();
			$this->whereFields = array();
			$this->groupType = self::$AND;
			$this->meta = $this->getMeta($module);
			$this->moduleNameFields[$module] = $this->meta->getNameFields();
			$this->referenceFieldInfoList = $this->meta->getReferenceFieldDetails();
			$this->referenceFieldList = array_keys($this->referenceFieldInfoList);;
			$this->ownerFields = $this->meta->getOwnerFields();
			$this->columns = null;
			$this->fromClause = null;
			$this->whereClause = null;
			$this->query = null;
			$this->conditionalWhere = null;
			$this->groupInfo = '';
			$this->manyToManyRelatedModuleConditions = array();
			$this->conditionInstanceCount = 0;
			$this->customViewFields = array();
			$this->summaryTable   = array ();
			$this->summaryLabels  = array ();
			$this->summaryColumns = array ();
		}

	/**
	 *
	 * @param String:ModuleName $module
	 * @return EntityMeta
	 */
	public function getMeta($module) {
		$db = PearDatabase::getInstance();
		if (empty($this->referenceModuleMetaInfo[$module])) {
			$handler = vtws_getModuleHandlerFromName($module, $this->user);
			$meta = $handler->getMeta();
			$this->referenceModuleMetaInfo[$module] = $meta;
			$this->moduleNameFields[$module] = $meta->getNameFields();
		}
		return $this->referenceModuleMetaInfo[$module];
	}

	public function reset() {
		$this->fromClause = null;
		$this->whereClause = null;
		$this->columns = null;
		$this->query = null;
	}

	public function setFields($fields) {
		$this->fields = $fields;
	}

	public function setSummaryColumns ($gridColumns) {
		$this->summaryColumns = $gridColumns;
	}

	public function getSummaryLabels () {
		return $this->summaryLabels;
	}

	public function getCustomViewFields() {
		return $this->customViewFields;
	}

	public function getFields() {
		return $this->fields;
	}

	public function getWhereFields() {
		return $this->whereFields;
	}

	public function getOwnerFieldList() {
		return $this->ownerFields;
	}

	public function getModuleNameFields($module) {
		return $this->moduleNameFields[$module];
	}

	public function getReferenceFieldList() {
		return $this->referenceFieldList;
	}

	public function getReferenceFieldInfoList() {
		return $this->referenceFieldInfoList;
	}

	public function getModule () {
		return $this->module;
	}

	public function getSummaryColumns () {
		return $this->summaryColumns;
	}

	public function getConditionalWhere() {
		return $this->conditionalWhere;
	}

	public function getDefaultCustomViewQuery() {
		$customView = new CustomView($this->module);
		$viewId = $customView->getViewId($this->module);
		return $this->getCustomViewQueryById($viewId);
	}

	public function initForDefaultCustomView() {
		$customView = new CustomView($this->module);
		$viewId = $customView->getViewId($this->module);
		$this->initForCustomViewById($viewId);
	}

	/**
	 * @codingStandardsIgnoreStart
	 *  @SuppressWarnings(PHPMD)
	 * Esta función inicializa los parámetros para integrar las vistas Custom y Kanban aunque presenta
	 * Function's nesting level (6) exceeds 5; es imposible refactorizar.
	 * @param integer $viewId
	 * @param integer $isKanbanView
	 */
	public function initForCustomViewById($viewId , $isKanbanView = 0) {
		global $current_user, $adb;
		$lstRelationsFields         = array('parent_id');
		$columns                    = array();
		$customView                 = new CustomView($this->module);
		$this->customViewColumnList = $customView->getColumnsListByCvid($viewId);
		
		foreach ($this->customViewColumnList as $customViewColumnInfo) {
			$details = explode(':', $customViewColumnInfo);
			if (in_array($details[1],$lstRelationsFields) && (esVistaCliente($current_user->id)))//Campos que hacen referencia a la misma cuenta en la vista de cliente se desactivan
				continue;
			if(empty($details[2]) && $details[1] == 'crmid' && $details[0] == 'vtiger_crmentity') {
				$name = 'id';
				$this->customViewFields[] = $name;
			} else if ($details[0] == 'vtiger_subfields_values') {
				$tableName = 'vtiger_grid_summary_' . $details [2];
				$moduleAndField = explode ('@', $details [3], 2);
				if (!in_array($tableName, array_values ($this->summaryTable))) {
					$this->summaryTable [][$moduleAndField [0]] = $tableName;
				}
				$columnName = $tableName . '.' . $details[1];
				$columns [] = $columnName;
				$this->summaryLabels = array_merge ($this->summaryLabels, array ($details[1] => $moduleAndField [1]));
				$this->fields[]      = $details[2];
			} else {
				$this->fields[] = $details[2];
				$this->customViewFields[] = $details[2];
			}
		}

		if($this->module == 'Calendar' && !in_array('activitytype', $this->fields)) {
			$this->fields[] = 'activitytype';
		}

		if($this->module == 'Documents') {
			if(in_array('filename', $this->fields)) {
				if(!in_array('filelocationtype', $this->fields)) {
					$this->fields[] = 'filelocationtype';
				}
				if(!in_array('filestatus', $this->fields)) {
					$this->fields[] = 'filestatus';
				}
			}
		}
		$this->fields[] = 'id';
		$this->stdFilterList = $customView->getStdFilterByCvid($viewId, $isKanbanView);
		$this->advFilterList = $customView->getAdvFilterByCvid($viewId, $isKanbanView);
		if(is_array($this->stdFilterList)) {
			$value = array();
			if(!empty($this->stdFilterList['columnname'])) {
				$this->startGroup('');
				$name = explode(':',$this->stdFilterList['columnname']);
				$name = $name[2];
				$value[] = $this->fixDateTimeValue($name, $this->stdFilterList['startdate']);
				$value[] = $this->fixDateTimeValue($name, $this->stdFilterList['enddate'], false);
				$this->addCondition($name, $value, 'BETWEEN');
			}
		}
		if($this->conditionInstanceCount <= 0 && is_array($this->advFilterList) && count($this->advFilterList) > 0) {
			$this->startGroup('');
		} elseif($this->conditionInstanceCount > 0 && is_array($this->advFilterList) && count($this->advFilterList) > 0) {
			$this->addConditionGlue(self::$AND);
		}
		if(is_array($this->advFilterList) && count($this->advFilterList) > 0) {
			foreach ($this->advFilterList as $groupindex=>$groupcolumns) {
				$filtercolumns = $groupcolumns['columns'];
				if(count($filtercolumns) > 0) {
					$this->startGroup('');
					foreach ($filtercolumns as $index=>$filter) {
						$name = explode(':',$filter['columnname']);
						if(empty ($name[2]) && $name [1] == 'crmid' && $name [0] == 'vtiger_crmentity') {
							$name = $this->getSQLColumn('id');
						} else if($name[0] == 'vtiger_subfields_values') {
							$tableName = 'vtiger_grid_summary_' . $name [2];
							$moduleAndField = explode ('@', $name [3], 2);
							if (!in_array($tableName, array_values ($this->summaryTable))) {
								$this->summaryTable [][$moduleAndField [0]] = $tableName;
							}
							$name = "{$tableName}.{$name[1]}";
						} else {
							$name = $name[2];
						}
						$this->addCondition($name, $filter['value'], $filter['comparator']);
						$columncondition = $filter['column_condition'];
						if(!empty($columncondition)) {
							$this->addConditionGlue($columncondition);
						}
					}
					$this->endGroup();
					$groupConditionGlue = $groupcolumns['condition'];
					if(!empty($groupConditionGlue))
						$this->addConditionGlue($groupConditionGlue);
				}
			}
		}
		if($this->conditionInstanceCount > 0) {
			$this->endGroup();
		}
		if (count ($columns)) {
			$this->columns = implode (', ', $columns);
		}
	}
	/** @codingStandardsIgnoreEnd */

	public function getCustomViewQueryById($viewId) {
		$this->initForCustomViewById($viewId);
		return $this->getQuery();
	}

	/**
	 * Genera y retorna la consulta SQL principal para el módulo actual, incluyendo integración dinámica de campos summary (resumen) y manejo de tablas temporales asociadas a grillas.
	 * Este método construye el SELECT, FROM y WHERE, validando y recalculando valores summary si es necesario, y asegurando la coherencia de los datos presentados.
	 *
	 * @author Equipo Platzilla
	 * @copyright Platzilla (c) 2025
	 * @version 1.0
	 * @date Última modificación: 2025-05-22
	 *
	 * @access public
	 * @return string Consulta SQL generada para el módulo y condiciones actuales.
	 */
	public function getQuery() {
		global $adb;
		// Si la consulta ya fue generada previamente, se reutiliza
		if(empty($this->query)) {
			$conditionedReferenceFields = array();
			// Combina los campos de filtrado y los campos seleccionados para procesar referencias y propietarios
			$allFields = array_merge($this->whereFields,$this->fields);
			// Procesa cada campo para obtener metadatos de referencia y propietario
			foreach ($allFields as $fieldName) {
				if(in_array($fieldName,$this->referenceFieldList)) {
					$moduleList = $this->referenceFieldInfoList[$fieldName];
					foreach ($moduleList as $module) {
							// Si no existen metadatos de nombre para el módulo de referencia, los obtiene
					if(empty($this->moduleNameFields[$module])) {
							$meta = $this->getMeta($module);
						}
					}
				} elseif(in_array($fieldName, $this->ownerFields )) {
						// Procesa campos de tipo propietario (Users/Groups)
					/*$meta = $this->getMeta('Users');
					$meta = $this->getMeta('Groups');*/
					$this->getMeta('Users');
					$this->getMeta('Groups');
				}
			}
			// Construcción de la consulta SQL principal
			$query = "SELECT ";
			$selectClause = $this->getSelectClauseColumnSQL();
			$query .= $selectClause;
			$fromClause = $this->getFromClause();
			$query .= $fromClause;
			$query .= $this->getWhereClause();
			// Si existen tablas summary asociadas a campos grid, se valida y recalcula su coherencia
			if (count($this->summaryTable)) {
				$filltable = null; 
				//2025-05-19 GGC Agregado para controlar si se llena de nuevo la tabla temporal
				// Itera sobre cada tabla summary temporal
				$summaryTableA = "";
				foreach ($this->summaryTable as $summaryTable) {
					if ($summaryTableA === $summaryTable) continue;
					$summaryTableA = $summaryTable;
					foreach ($summaryTable as $moduleName => $tableName) {
						$dummy = explode('_', $tableName, 4);
						// Verifica si la tabla temporal tiene registros
						$check = $adb->pquery("SELECT COUNT(*) as count FROM {$tableName}");
						$resultado = 0;
						if ($check && $adb->num_rows($check) > 0) {
							$resultado = intval($adb->query_result($check, 0, 'count'));
						}
						if ($resultado > 0) {
							// Determina si la tabla temporal está íntegra (true) o debe reconstruirse (false)
							$isIntact = self::checkGridSummaryTableIntegrity($adb, $moduleName, $dummy[3], $tableName);
							$gridColumnNames = GridFieldUtils::getSummaryGridFields($adb, $moduleName, $dummy[3]);
							if ($isIntact) {
								// Tabla íntegra, solo llenar los datos faltantes
								GridFieldUtils::fillTemporaryTable($adb, $gridColumnNames, $moduleName, $dummy[3], $tableName);
							} else {
								// Tabla inconsistente, reconstruir la tabla temporal
								$this->summaryColumns = GridFieldUtils::createTempGridValues($adb, $moduleName, $dummy[3], $tableName, $isIntact, false);
							}
						} else {
							// Si la tabla temporal no existe o está vacía, se crea y llena con los valores summary actuales
							$this->summaryColumns = GridFieldUtils::createTempGridValues($adb, $moduleName, $dummy[3], $tableName, true);
						}
					}
				}
			}
			// Almacena la consulta generada para futuras llamadas
			$this->query = $query;
			// Retorna la consulta SQL generada
			return $query;
		} else {
			return $this->query;
		}
	}

	public function getSQLColumn($name) {
		if ($name == 'id') {
			$baseTable = $this->meta->getEntityBaseTable();
			$moduleTableIndexList = $this->meta->getEntityTableIndexList();
			$baseTableIndex = $moduleTableIndexList[$baseTable];
			return $baseTable.'.'.$baseTableIndex;
		}

		$moduleFields = $this->meta->getModuleFields();
		$field        = $moduleFields[$name];
		$sql          = '';

		if ($field->getUiType () == '2202') {
			return null;
		}
		//TODO optimization to eliminate one more lookup of name, incase the field refers to only
		//one module or is of type owner.
		$column = $field->getColumnName();
		return $field->getTableName().'.'.$column;
	}

	public function getSelectClauseColumnSQL (){
		$columns       = array();
		$moduleFields = $this->meta->getModuleFields();
		$accessibleFieldList = array_keys($moduleFields);
		$accessibleFieldList[] = 'id';
		$this->fields = array_intersect($this->fields, $accessibleFieldList);
		foreach ($this->fields as $field) {
			$sql = $this->getSQLColumn($field);
			if (!empty ($sql)) {
				$columns[] = $sql;
			}
		}
		if (in_array ('vtiger_crmentity.smownerid', $columns)) {
			$columns[] = 'vtiger_users.imagename';
		}
		$stringColumns = implode (', ', $columns);
		if (empty ($this->columns)) {
			$this->columns = $stringColumns;
		} else {
			$this->columns .= ', '. $stringColumns;
		}
		return $this->columns;
	}

	public function getFromClause() {
		if(!empty($this->query) || !empty($this->fromClause)) {
			return $this->fromClause;
		}
		$moduleFields = $this->meta->getModuleFields();
		$tableList = array();
		$tableJoinMapping   = array();
		$tableJoinCondition = array();
		$fieldJoinIndex     = null;
		foreach ($this->fields as $fieldName) {
			if ($fieldName == 'id') {
				continue;
			}

			$field = $moduleFields[$fieldName];
			$baseTable = $field->getTableName();
			$tableIndexList = $this->meta->getEntityTableIndexList();
			$baseTableIndex = $tableIndexList[$baseTable];
			if($field->getFieldDataType() == 'reference') {
				$moduleList = $this->referenceFieldInfoList[$fieldName];
				$tableJoinMapping[$field->getTableName()] = 'INNER JOIN';
				foreach($moduleList as $module) {
					if($module == 'Users') {
						$tableJoinCondition[$fieldName]['vtiger_users'] = $field->getTableName().
								".".$field->getColumnName()." = vtiger_users.id";
						$tableJoinCondition[$fieldName]['vtiger_groups'] = $field->getTableName().
								".".$field->getColumnName()." = vtiger_groups.groupid";
						$tableJoinMapping['vtiger_users'] = 'LEFT JOIN';
						$tableJoinMapping['vtiger_groups'] = 'LEFT JOIN';
					}
				}
			} elseif($field->getFieldDataType() == 'owner') {
				$tableList['vtiger_users'] = 'vtiger_users';
				$tableList['vtiger_groups'] = 'vtiger_groups';
				$tableJoinMapping['vtiger_users'] = 'LEFT JOIN';
				$tableJoinMapping['vtiger_groups'] = 'LEFT JOIN';
			} else if($field->getUiType() == '2202') {
				$tableName = "vtiger_grid_summary_{$fieldName}";
				if (!in_array($tableName, array_keys ($tableJoinMapping))) {
					$tableIndex = ($baseTable == 'vtiger_crmentity') ? 'crmid' : $baseTableIndex;
					$tableJoinMapping[$tableName] = 'LEFT JOIN';
					$tableJoinCondition[$fieldName][$tableName] = $tableName . '.recordid = ' . $baseTable . '.' . $tableIndex;
				}
			}
			$tableList[$field->getTableName()] = $field->getTableName();
				$tableJoinMapping[$field->getTableName()] =
						$this->meta->getJoinClause($field->getTableName());
		}
		$baseTable = $this->meta->getEntityBaseTable();
		$moduleTableIndexList = $this->meta->getEntityTableIndexList();
		$baseTableIndex = $moduleTableIndexList[$baseTable];
		foreach ($this->whereFields as $fieldName) {
			if(empty($fieldName)) {
				continue;
			}
			$field = $moduleFields[$fieldName];
			if(empty ($field)) {
				// not accessible field.
				$dummyData = explode('.', $fieldName);
				$tableName = $dummyData [0];
				if ($tableName == 'vtiger_users') {
					$dummy = explode (', ', $this->columns);
					if (!in_array ('vtiger_users.imagename', $dummy)) {
						$tableJoinMapping[$tableName] = 'LEFT JOIN';
						$tableJoinCondition[$fieldName][$tableName] = $tableName . '.id = vtiger_crmentity.smownerid';
					}
				} else if (!in_array($tableName, array_keys ($tableJoinMapping))) {
					$tableIndex = ($baseTable == 'vtiger_crmentity') ? 'crmid' : $baseTableIndex;
					$tableJoinMapping[$tableName] = 'LEFT JOIN';
					$tableJoinCondition[$fieldName][$tableName] = $tableName . '.recordid = ' . $baseTable . '.' . $tableIndex;
				}
				continue;
			}
			$baseTable = $field->getTableName();
			// When a field is included in Where Clause, but not is Select Clause, and the field table is not base table,
			// The table will not be present in tablesList and hence needs to be added to the list.
			if(empty($tableList[$baseTable])) {
				$tableList[$baseTable] = $field->getTableName();
				$tableJoinMapping[$baseTable] = $this->meta->getJoinClause($field->getTableName());
			}
			if($field->getFieldDataType() == 'reference') {
				$moduleList = $this->referenceFieldInfoList[$fieldName];
				$tableJoinMapping[$field->getTableName()] = 'INNER JOIN';
				foreach($moduleList as $module) {
					$meta = $this->getMeta($module);
					$nameFields = $this->moduleNameFields[$module];
					$nameFieldList = explode(',',$nameFields);
					foreach ($nameFieldList as $index=>$column) {
						// for non admin user users module is inaccessible.
						// so need hard code the tablename.
						if($module == 'Users') {
							$instance = CRMEntity::getInstance($module);
							$referenceTable = $instance->table_name;
							$tableIndexList = $instance->tab_name_index;
							$referenceTableIndex = $tableIndexList[$referenceTable];
						} else {
							$referenceField = $meta->getFieldByColumnName($column);
							$referenceTable = $referenceField->getTableName();
							$tableIndexList = $meta->getEntityTableIndexList();
							$referenceTableIndex = $tableIndexList[$referenceTable];
						}
						if(isset($moduleTableIndexList[$referenceTable])) {
							$referenceTableName = "$referenceTable $referenceTable$fieldName";
							$referenceTable = "$referenceTable$fieldName";
						} else {
							$referenceTableName = $referenceTable;
						}
						//should always be left join for cases where we are checking for null
						//reference field values.
						$tableJoinMapping[$referenceTableName] = 'LEFT JOIN';
						$tableJoinCondition[$fieldName][$referenceTableName] = $baseTable.'.'.
							$field->getColumnName().' = '.$referenceTable.'.'.$referenceTableIndex;
					}
				}
			} elseif($field->getFieldDataType() == 'owner') {
				$tableList['vtiger_users'] = 'vtiger_users';
				$tableList['vtiger_groups'] = 'vtiger_groups';
				$tableJoinMapping['vtiger_users'] = 'LEFT JOIN';
				$tableJoinMapping['vtiger_groups'] = 'LEFT JOIN';
			} else {
				$tableList[$field->getTableName()] = $field->getTableName();
				$tableJoinMapping[$field->getTableName()] =
						$this->meta->getJoinClause($field->getTableName());
			}
		}

		$defaultTableList = $this->meta->getEntityDefaultTableList();
		foreach ($defaultTableList as $table) {
			if(!in_array($table, $tableList)) {
				$tableList[$table] = $table;
				$tableJoinMapping[$table] = 'INNER JOIN';
			}
		}
		$ownerFields = $this->meta->getOwnerFields();
		if (count($ownerFields) > 0) {
			$ownerField = $ownerFields[0];
		}
		$baseTable = $this->meta->getEntityBaseTable();
		$sql = " FROM $baseTable ";
		unset($tableList[$baseTable]);
		foreach ($defaultTableList as $tableName) {
			$sql .= " $tableJoinMapping[$tableName] $tableName ON $baseTable.".
					"$baseTableIndex = $tableName.$moduleTableIndexList[$tableName]";
			unset($tableList[$tableName]);
		}
		foreach ($tableList as $tableName) {
			if($tableName == 'vtiger_users') {
				$field = $moduleFields[$ownerField];
				$sql .= " $tableJoinMapping[$tableName] $tableName ON ".$field->getTableName().".".
					$field->getColumnName()." = $tableName.id";
			} else if($tableName == 'vtiger_groups') {
				$field = $moduleFields[$ownerField];
				$sql .= " $tableJoinMapping[$tableName] $tableName ON ".$field->getTableName().".".
					$field->getColumnName()." = $tableName.groupid";
			} else if (!empty ($fieldJoinIndex)) {
				$sql .= " {$tableJoinMapping[$tableName]} {$tableName} ON {$baseTable}.
					{$baseTableIndex} = {$tableName}.{$fieldJoinIndex}";
			} else {
				$sql .= " $tableJoinMapping[$tableName] $tableName ON $baseTable.".
					"$baseTableIndex = $tableName.$moduleTableIndexList[$tableName]";
			}
		}

		if( $this->meta->getTabName() == 'Documents') {
			$tableJoinCondition['folderid'] = array(
				'vtiger_attachmentsfolder'=>"$baseTable.folderid = vtiger_attachmentsfolder.folderid"
			);
			$tableJoinMapping['vtiger_attachmentsfolder'] = 'INNER JOIN';
		}

		foreach ($tableJoinCondition as $fieldName=>$conditionInfo) {
			foreach ($conditionInfo as $tableName=>$condition) {
				if(!empty($tableList[$tableName])) {
					$tableNameAlias = $tableName.'2';
					$condition = str_replace($tableName, $tableNameAlias, $condition);
				} else {
					$tableNameAlias = '';
				}
				$sql .= " $tableJoinMapping[$tableName] $tableName $tableNameAlias ON $condition";
			}
		}

		foreach ($this->manyToManyRelatedModuleConditions as $conditionInfo) {
			$relatedModuleMeta = RelatedModuleMeta::getInstance($this->meta->getTabName(),
					$conditionInfo['relatedModule']);
			$relationInfo = $relatedModuleMeta->getRelationMeta();
			$relatedModule = $this->meta->getTabName();
			$sql .= ' INNER JOIN '.$relationInfo['relationTable']." ON ".
			$relationInfo['relationTable'].".$relationInfo[$relatedModule]=".
				"$baseTable.$baseTableIndex";
		}

		$sql .= $this->meta->getEntityAccessControlQuery();
		$this->fromClause = $sql;
		return $sql;
	}

	public function getWhereClause() {
		if(!empty($this->query) || !empty($this->whereClause)) {
			return $this->whereClause;
		}
		$deletedQuery = $this->meta->getEntityDeletedQuery();
		$sql = '';
		if(!empty($deletedQuery)) {
			$sql .= " WHERE $deletedQuery";
		}
		if($this->conditionInstanceCount > 0) {
			$sql .= ' AND ';
		} elseif(empty($deletedQuery)) {
			$sql .= ' WHERE ';
		}

		$moduleFieldList      = $this->meta->getModuleFields();
		$baseTable            = $this->meta->getEntityBaseTable();
		$moduleTableIndexList = $this->meta->getEntityTableIndexList();
		$baseTableIndex       = $moduleTableIndexList[$baseTable];
		$groupSql             = $this->groupInfo;
		$fieldSqlList         = array();
		foreach ($this->conditionals as $index=>$conditionInfo) {
			$fieldName = $conditionInfo['name'];
			$field     = $moduleFieldList[$fieldName];
			if(empty($field)) {
				$dummy     = explode ('.', $fieldName, 2);
				if (count ($dummy) > 1 ) {
					$valueSqlList = $this->getSqlOperator ($conditionInfo['operator'], $conditionInfo['value']) . ' ' . $conditionInfo['value'];
					if(!is_array($valueSqlList)) {
						$valueSqlList = array($valueSqlList);
					}
				} else {
					continue;
				}
			} else {
				$valueSqlList = $this->getConditionValue($conditionInfo['value'],
					$conditionInfo['operator'], $field);
				if(!is_array($valueSqlList)) {
					$valueSqlList = array($valueSqlList);
				}
			}
			$fieldSql = '(';
			$fieldGlue = '';

			foreach ($valueSqlList as $valueSql) {
				if (in_array($fieldName, $this->referenceFieldList)) {
					$moduleList = $this->referenceFieldInfoList[$fieldName];
					foreach($moduleList as $module) {
						$nameFields = $this->moduleNameFields[$module];
						$nameFieldList = explode(',',$nameFields);
						$meta = $this->getMeta($module);
						$columnList = array();
						foreach ($nameFieldList as $column) {
							if($module == 'Users') {
								$instance = CRMEntity::getInstance($module);
								$referenceTable = $instance->table_name;
								if(count($this->ownerFields) > 0 ||
										$this->getModule() == 'Quotes') {
									$referenceTable .= '2';
								}
							} else {
								$referenceField = $meta->getFieldByColumnName($column);
								$referenceTable = $referenceField->getTableName();
							}
							if(isset($moduleTableIndexList[$referenceTable])) {
								$referenceTable = "$referenceTable$fieldName";
							}
							$columnList[] = "$referenceTable.$column";
						}
						if(count($columnList) > 1) {
							$columnSql = getSqlForNameInDisplayFormat(array('first_name'=>$columnList[0],'last_name'=>$columnList[1]),'Users');
						} else {
							$columnSql = implode('', $columnList);
						}

						$fieldSql .= "$fieldGlue trim($columnSql) $valueSql";
						$fieldGlue = ' OR';
					}
				} elseif (in_array($fieldName, $this->ownerFields)) {
					$concatSql = getSqlForNameInDisplayFormat (array('first_name' => "vtiger_users.first_name", 'last_name' => "vtiger_users.last_name"), 'Users');
					$fieldSql .= "$fieldGlue trim($concatSql) $valueSql or " . "vtiger_groups.groupname $valueSql";
				} else if ($fieldName == 'vtiger_users.id') {
					$fieldSql .= $fieldGlue . ' ' . $fieldName . ' ' . $valueSql;
				} else {
					if($fieldName == 'birthday' && !$this->isRelativeSearchOperators(
							$conditionInfo['operator'])) {
						$fieldSql .= "$fieldGlue DATE_FORMAT(".$field->getTableName().'.'.
								$field->getColumnName().",'%m%d') ".$valueSql;
					} else if(!empty($field)) {
						$fieldSql .= "$fieldGlue " . $field->getTableName() . '.' .
							$field->getColumnName() . ' ' . $valueSql;
					} else {
						$fieldSql .= "$fieldGlue " . $fieldName . ' ' . $valueSql;
					}
				}
				$fieldGlue = ' OR';
			}
			$fieldSql .= ')';
			$fieldSqlList[$index] = $fieldSql;
		}
		foreach ($this->manyToManyRelatedModuleConditions as $index=>$conditionInfo) {
			$relatedModuleMeta = RelatedModuleMeta::getInstance($this->meta->getTabName(),
					$conditionInfo['relatedModule']);
			$relationInfo = $relatedModuleMeta->getRelationMeta();
			$relatedModule = $this->meta->getTabName();
			$fieldSql = "(".$relationInfo['relationTable'].'.'.
			$relationInfo[$conditionInfo['column']].$conditionInfo['SQLOperator'].
			$conditionInfo['value'].")";
			$fieldSqlList[$index] = $fieldSql;
		}

		$groupSql = $this->makeGroupSqlReplacements($fieldSqlList, $groupSql);

		if($this->conditionInstanceCount > 0) {
			$this->conditionalWhere = $groupSql;
			$sql .= $groupSql;
		}
		$sql .= " AND $baseTable.$baseTableIndex > 0";
		$this->whereClause = $sql;
		return $sql;
	}

	/**
	 *
	 * @param mixed $value
	 * @param String $operator
	 * @param WebserviceField $field
	 */
	public function getConditionValue($value, $operator, $field) {

		$operator = strtolower($operator);
		$db = PearDatabase::getInstance();

		if(is_string($value)) {
			$valueArray = explode(',' , $value);
		} elseif(is_array($value)) {
			$valueArray = $value;
		} else{
			$valueArray = array($value);
		}
		$sql = array();
		if($operator == 'between' || $operator == 'bw') {
			if($field->getFieldName() == 'birthday') {
				$valueArray[0] = getValidDBInsertDateTimeValue($valueArray[0]);
				$valueArray[1] = getValidDBInsertDateTimeValue($valueArray[1]);
				$sql[] = "BETWEEN DATE_FORMAT(".$db->quote($valueArray[0]).", '%m%d') AND ".
						"DATE_FORMAT(".$db->quote($valueArray[1]).", '%m%d')";
			} else {
				if($this->isDateType($field->getFieldDataType())) {
					$valueArray[0] = getValidDBInsertDateTimeValue($valueArray[0]);
					$valueArray[1] = getValidDBInsertDateTimeValue($valueArray[1]);
				}
				$sql[] = "BETWEEN ".$db->quote($valueArray[0])." AND ".
							$db->quote($valueArray[1]);
			}
			return $sql;
		}
		foreach ($valueArray as $value) {
			if(!$this->isStringType($field->getFieldDataType())) {
				$value = trim($value);
			}
			if((strtolower(trim($value)) == 'null') ||
					(trim($value) == '' && !$this->isStringType($field->getFieldDataType())) &&
							($operator == 'e' || $operator == 'n')) {
				if($operator == 'e'){
					$sql[] = "IS NULL";
					continue;
				}
				$sql[] = "IS NOT NULL";
				continue;
			} elseif($field->getFieldDataType() == 'boolean') {
				$value = strtolower($value);
				if ($value == 'yes') {
					$value = 1;
				} elseif($value == 'no') {
					$value = 0;
				}
			} elseif($this->isDateType($field->getFieldDataType())) {
				$value = getValidDBInsertDateTimeValue($value);
			}

			if($field->getFieldName() == 'birthday' && !$this->isRelativeSearchOperators(
					$operator)) {
				$value = "DATE_FORMAT(".$db->quote($value).", '%m%d')";
			} else {
				$value = $db->sql_escape_string($value);
			}

			if(trim($value) == '' && ($operator == 's' || $operator == 'ew' || $operator == 'c')
					&& ($this->isStringType($field->getFieldDataType()) ||
					$field->getFieldDataType() == 'picklist' ||
					$field->getFieldDataType() == 'multipicklist')) {
				$sql[] = "LIKE ''";
				continue;
			}

			if(trim($value) == '' && ($operator == 'k') &&
					$this->isStringType($field->getFieldDataType())) {
				$sql[] = "NOT LIKE ''";
				continue;
			}
			$sqlOperator = $this->getSqlOperator($operator, $value);

			if(!$this->isNumericType($field->getFieldDataType()) &&
					($field->getFieldName() != 'birthday' || ($field->getFieldName() == 'birthday'
							&& $this->isRelativeSearchOperators($operator)))){
				$value = "'$value'";
			}
			if($this->isNumericType($field->getFieldDataType()) && empty($value)) {
				$value = '0';
			}
			$sql[] = "$sqlOperator $value";
		}

		return $sql;
	}

	private function makeGroupSqlReplacements($fieldSqlList, $groupSql) {
		$pos = 0;
		$nextOffset = 0;
		foreach ($fieldSqlList as $index => $fieldSql) {
			$pos = strpos($groupSql, $index.'', $nextOffset);
			if($pos !== false) {
				$beforeStr = substr($groupSql,0,$pos);
				$afterStr = substr($groupSql, $pos + strlen($index));
				$nextOffset = strlen($beforeStr.$fieldSql);
				$groupSql = $beforeStr.$fieldSql.$afterStr;
			}
		}
		return $groupSql;
	}

	private function isRelativeSearchOperators($operator) {
		$nonDaySearchOperators = array('l','g','m','h');
		return in_array($operator, $nonDaySearchOperators);
	}
	private function isNumericType($type) {
		return ($type == 'integer' || $type == 'double' || $type == 'currency');
	}

	private function isStringType($type) {
		return ($type == 'string' || $type == 'text' || $type == 'email' || $type == 'reference');
	}

	private function isDateType($type) {
		return ($type == 'date' || $type == 'datetime');
	}

	private function fixDateTimeValue($name, $value, $first = true) {
		$moduleFields = $this->meta->getModuleFields();
		$field = $moduleFields[$name];
		$type = $field->getFieldDataType();
		if($type == 'datetime') {
			if(strrpos($value, ' ') === false) {
				if($first) {
					return $value.' 00:00:00';
				}else{
					return $value.' 23:59:59';
				}
			}
		}
		return $value;
	}

	public function addCondition($fieldname,$value,$operator,$glue= null,$newGroup = false,
			$newGroupType = null) {
		$conditionNumber = $this->conditionInstanceCount++;
		$this->groupInfo .= "$conditionNumber ";
		$this->whereFields[] = $fieldname;
		$this->reset();
		$this->conditionals[$conditionNumber] = $this->getConditionalArray($fieldname,
				$value, $operator);
	}

	public function addRelatedModuleCondition($relatedModule,$column, $value, $SQLOperator) {
		$conditionNumber = $this->conditionInstanceCount++;
		$this->groupInfo .= "$conditionNumber ";
		$this->manyToManyRelatedModuleConditions[$conditionNumber] = array('relatedModule'=>
			$relatedModule,'column'=>$column,'value'=>$value,'SQLOperator'=>$SQLOperator);
	}

	private function getConditionalArray($fieldname,$value,$operator) {
		if(is_string($value)) {
			$value = trim($value);
		} elseif(is_array($value)) {
			$value = array_map(trim, $value);
		}
		return array('name'=>$fieldname,'value'=>$value,'operator'=>$operator);
	}

	public function startGroup($groupType) {
		$this->groupInfo .= " $groupType (";
	}

	public function endGroup() {
		$this->groupInfo .= ')';
	}

	public function addConditionGlue($glue) {
		$this->groupInfo .= " $glue ";
	}

	public function addUserSearchConditions($input) {
		global $log,$default_charset;
		if($input['searchtype']=='advance') {

			$json = new Zend_Json();
			$advft_criteria = $_REQUEST['advft_criteria'];
			if(!empty($advft_criteria))	$advft_criteria = $json->decode($advft_criteria);
			$advft_criteria_groups = $_REQUEST['advft_criteria_groups'];
			if(!empty($advft_criteria_groups))	$advft_criteria_groups = $json->decode($advft_criteria_groups);

			if(empty($advft_criteria) || count($advft_criteria) <= 0) {
				return ;
			}

			$advfilterlist = getAdvancedSearchCriteriaList($advft_criteria, $advft_criteria_groups, $this->getModule());

			if(empty($advfilterlist) || count($advfilterlist) <= 0) {
				return ;
			}

			if($this->conditionInstanceCount > 0) {
				$this->startGroup(self::$AND);
			} else {
				$this->startGroup('');
			}
			foreach ($advfilterlist as $groupindex=>$groupcolumns) {
				$filtercolumns = $groupcolumns['columns'];
				if(count($filtercolumns) > 0) {
					$this->startGroup('');
					foreach ($filtercolumns as $index=>$filter) {
						$name = explode(':',$filter['columnname']);
						if(empty($name[2]) && $name[1] == 'crmid' && $name[0] == 'vtiger_crmentity') {
							$name = $this->getSQLColumn('id');
						} else {
							$name = $name[2];
						}
						$this->addCondition($name, $filter['value'], $filter['comparator']);
						$columncondition = $filter['column_condition'];
						if(!empty($columncondition)) {
							$this->addConditionGlue($columncondition);
						}
					}
					$this->endGroup();
					$groupConditionGlue = $groupcolumns['condition'];
					if(!empty($groupConditionGlue))
						$this->addConditionGlue($groupConditionGlue);
				}
			}
			$this->endGroup();
		} elseif($input['type']=='dbrd') {
			if($this->conditionInstanceCount > 0) {
				$this->startGroup(self::$AND);
			} else {
				$this->startGroup('');
			}
			$allConditionsList = $this->getDashBoardConditionList();
			$conditionList = $allConditionsList['conditions'];
			$relatedConditionList = $allConditionsList['relatedConditions'];
			$noOfConditions = count($conditionList);
			$noOfRelatedConditions = count($relatedConditionList);
			foreach ($conditionList as $index=>$conditionInfo) {
				$this->addCondition($conditionInfo['fieldname'], $conditionInfo['value'],
						$conditionInfo['operator']);
				if($index < $noOfConditions - 1 || $noOfRelatedConditions > 0) {
					$this->addConditionGlue(self::$AND);
				}
			}
			foreach ($relatedConditionList as $index => $conditionInfo) {
				$this->addRelatedModuleCondition($conditionInfo['relatedModule'],
						$conditionInfo['conditionModule'], $conditionInfo['finalValue'],
						$conditionInfo['SQLOperator']);
				if($index < $noOfRelatedConditions - 1) {
					$this->addConditionGlue(self::$AND);
				}
			}
			$this->endGroup();
		} else {
			if(isset($input['search_field']) && $input['search_field'] !="") {
				$fieldName=vtlib_purify($input['search_field']);
			} else {
				return ;
			}
			if($this->conditionInstanceCount > 0) {
				$this->startGroup(self::$AND);
			} else {
				$this->startGroup('');
			}
			$moduleFields = $this->meta->getModuleFields();
			$field = $moduleFields[$fieldName];
			$type = $field->getFieldDataType();
			if(isset($input['search_text']) && $input['search_text']!="") {
				// search other characters like "|, ?, ?" by jagi
				$value = $input['search_text'];
				$stringConvert = function_exists(iconv) ? @iconv("UTF-8",$default_charset,$value)
						: $value;
				if(!$this->isStringType($type)) {
					$value=trim($stringConvert);
				}

				if($type == 'picklist') {
					global $mod_strings;
					// Get all the keys for the for the Picklist value
					$mod_keys = array_keys($mod_strings, $value);
					if(sizeof($mod_keys) >= 1) {
						// Iterate on the keys, to get the first key which doesn't start with LBL_      (assuming it is not used in PickList)
						foreach($mod_keys as $mod_idx=>$mod_key) {
							$stridx = strpos($mod_key, 'LBL_');
							// Use strict type comparision, refer strpos for more details
							if ($stridx !== 0) {
								$value = $mod_key;
								break;
							}
						}
					}
				}
				if($type == 'currency') {
					// Some of the currency fields like Unit Price, Total, Sub-total etc of Inventory modules, do not need currency conversion
					if($field->getUIType() == '72') {
						$value = CurrencyField::convertToDBFormat($value, null, true);
					} else {
						$currencyField = new CurrencyField($value);
						if($this->getModule() == 'Potentials' && $fieldName == 'amount') {
							$currencyField->setNumberofDecimals(2);
						}
						$value = $currencyField->getDBInsertedValue();
					}
				}
			}
			if(!empty($input['operator'])) {
				$operator = $input['operator'];
			} elseif(trim(strtolower($value)) == 'null'){
				$operator = 'e';
			} else {
				if(!$this->isNumericType($type) && !$this->isDateType($type)) {
					$operator = 'c';
				} else {
					$operator = 'h';
				}
			}
			$this->addCondition($fieldName, $value, $operator);
			$this->endGroup();
		}
	}

	public function getDashBoardConditionList() {
		global $adb;
		if(isset($_REQUEST['leadsource'])) {
			$leadSource = $_REQUEST['leadsource'];
		}
		if(isset($_REQUEST['date_closed'])) {
			$dateClosed = $_REQUEST['date_closed'];
		}
		if(isset($_REQUEST['sales_stage'])) {
			$salesStage = $_REQUEST['sales_stage'];
		}
		if(isset($_REQUEST['closingdate_start'])) {
			$dateClosedStart = $_REQUEST['closingdate_start'];
		}
		if(isset($_REQUEST['closingdate_end'])) {
			$dateClosedEnd = $_REQUEST['closingdate_end'];
		}
		if(isset($_REQUEST['owner'])) {
			$owner = vtlib_purify($_REQUEST['owner']);
		}
		if (isset($_REQUEST ['owners'])) {
			$owners = vtlib_purify ($_REQUEST['owners']);
			$dummy  = explode (',', $owners);
			$owners = $adb->sql_expr_datalist ($dummy);
		}
		if(isset($_REQUEST['campaignid'])) {
			$campaignId = vtlib_purify($_REQUEST['campaignid']);
		}
		if(isset($_REQUEST['quoteid'])) {
			$quoteId = vtlib_purify($_REQUEST['quoteid']);
		}
		if(isset($_REQUEST['invoiceid'])) {
			$invoiceId = vtlib_purify($_REQUEST['invoiceid']);
		}
		if(isset($_REQUEST['purchaseorderid'])) {
			$purchaseOrderId = vtlib_purify($_REQUEST['purchaseorderid']);
		}

		$conditionList = array();
		if(!empty($dateClosedStart) && !empty($dateClosedEnd)) {

			$conditionList[] = array('fieldname'=>'closingdate', 'value'=>$dateClosedStart,
				'operator'=>'h');
			$conditionList[] = array('fieldname'=>'closingdate', 'value'=>$dateClosedEnd,
				'operator'=>'m');
		}
		if(!empty($salesStage)) {
			if($salesStage == 'Other') {
				$conditionList[] = array('fieldname'=>'sales_stage', 'value'=>'Closed Won',
					'operator'=>'n');
				$conditionList[] = array('fieldname'=>'sales_stage', 'value'=>'Closed Lost',
					'operator'=>'n');
			} else {
				$conditionList[] = array('fieldname'=>'sales_stage', 'value'=> $salesStage,
					'operator'=>'e');
			}
		}
		if(!empty($leadSource)) {
			$conditionList[] = array('fieldname'=>'leadsource', 'value'=>$leadSource,
					'operator'=>'e');
		}
		if(!empty($dateClosed)) {
			$conditionList[] = array('fieldname'=>'closingdate', 'value'=>$dateClosed,
					'operator'=>'h');
		}
		if(!empty($owner)) {
			$conditionList[] = array('fieldname'=>'assigned_user_id', 'value'=>$owner,
					'operator'=>'e');
		}
		if (!empty ($owners)) {
			$conditionList [] = array(
				'fieldname' => 'vtiger_users.id',
				'value'     => $owners,
				'operator'  => 'in',
			);
		}
		$relatedConditionList = array();
		if(!empty($campaignId)) {
			$relatedConditionList[] = array('relatedModule'=>'Campaigns','conditionModule'=>
				'Campaigns','finalValue'=>$campaignId, 'SQLOperator'=>'=');
		}
		if(!empty($quoteId)) {
			$relatedConditionList[] = array('relatedModule'=>'Quotes','conditionModule'=>
				'Quotes','finalValue'=>$quoteId, 'SQLOperator'=>'=');
		}
		if(!empty($invoiceId)) {
			$relatedConditionList[] = array('relatedModule'=>'Invoice','conditionModule'=>
				'Invoice','finalValue'=>$invoiceId, 'SQLOperator'=>'=');
		}
		if(!empty($purchaseOrderId)) {
			$relatedConditionList[] = array('relatedModule'=>'PurchaseOrder','conditionModule'=>
				'PurchaseOrder','finalValue'=>$purchaseOrderId, 'SQLOperator'=>'=');
		}
		return array('conditions'=>$conditionList,'relatedConditions'=>$relatedConditionList);
	}

	public function initForGlobalSearchByType($type, $value, $operator='s') {
		$fieldList = $this->meta->getFieldNameListByType($type);
		if($this->conditionInstanceCount <= 0) {
			$this->startGroup('');
		} else {
			$this->startGroup(self::$AND);
		}
		$nameFieldList = explode(',',$this->getModuleNameFields($this->module));
		foreach ($nameFieldList as $nameList) {
			$field = $this->meta->getFieldByColumnName($nameList);
			$this->fields[] = $field->getFieldName();
		}
		foreach ($fieldList as $index => $field) {
			$fieldName = $this->meta->getFieldByColumnName($field);
			$this->fields[] = $fieldName->getFieldName();
			if($index > 0) {
				$this->addConditionGlue(self::$OR);
			}
			$this->addCondition($fieldName->getFieldName(), $value, $operator);
		}
		$this->endGroup();
		if(!in_array('id', $this->fields)) {
				$this->fields[] = 'id';
		}
	}

}
?>