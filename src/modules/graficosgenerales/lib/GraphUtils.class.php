<?php
	require_once ('include/platzilla/Data/GraphicManager.php');
	require_once ('include/platzilla/Data/FieldGridManager.php');
	require_once ('include/platzilla/Objects/FieldInterface.php');
	require_once ('include/utils/GridFieldUtils.class.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/utils.php');
    require_once ('modules/graficosgenerales/StackedGraphFix.php');
	abstract class GraphUtils {
		const OPERATION_COUNT   = 1;
		const OPERATION_SUM     = 2;
		const OPERATION_AVERAGE = 3;
		const OPERATION_MAXIMUN = 4;
		const OPERATION_MINIMUN = 5;

		const DATE_GROUPING_DAILY     = 1;
		const DATE_GROUPING_WEEKLY    = 2;
		const DATE_GROUPING_MONTHLY   = 3;
		const DATE_GROUPING_QUARTERLY = 4;
		const DATE_GROUPING_BIANNUAL  = 5;
		const DATE_GROUPING_ANNUAL    = 6;

		const GRAPH_TYPE_AREA   = 'area';
		const GRAPH_TYPE_BARS   = 'bar';
		const GRAPH_TYPE_COLUMN = 'column';
		const GRAPH_TYPE_COMBO  = 'combo';
		const GRAPH_TYPE_DONUT  = 'donut';
		const GRAPH_TYPE_FUNNEL = 'funnel';
		const GRAPH_TYPE_LINE   = 'line';
		const GRAPH_TYPE_PIE    = 'pie';
		const GRAPH_TYPE_TABLE  = 'table';

		/** @var array */
		private static $summaryTable = array ();

		/** @var array */
		private static $summaryColumns = array ();

		private static function applyOperationToData ($graphData, $data) {
			$gridData = explode('@', $graphData ['gridoperation']);
			$items = array_unique (array_column ($data,$gridData [ 1 ]));
			foreach ($items as $item) {
				$calculated = 0;
				$sum        = 0;
				$count      = 0;
				foreach ($data as $row) {
					if ($row [ $gridData [ 1 ] ] == $item) {
						if ($graphData ['operation'] == self::OPERATION_COUNT) {
							$calculated++;
						} else if ($graphData ['operation'] == self::OPERATION_SUM) {
							$calculated += $row ['contador'];
						} else if ($graphData ['operation'] == self::OPERATION_AVERAGE) {
							$count++;
							$sum += $row ['contador'];
							$calculated = ($count > 0) ? ($sum / $count) : 0;
						} else {
							continue;
						}
					}
				}
				if ($calculated) {
					$dataResults [] = array(
						$graphData['fieldgrouping'] => $item,
						'contador'                  => number_format($calculated, 2, '.', ''),
					);
				}
			}
			return $dataResults;
		}

		private static function combineArray ($options) {
			$merge = array ();
			foreach ($options as $k => $v) {
				if (count ($v) > 1 || (array_keys ($v) !== range (0, (count ($v) - 1)))) {
					$merge [$k] = self::combineArray ($v);
				} else {
					if (empty($merge)) {
						$merge = array_combine (array ($k), array_values($v));
					} else {
						$merge = array_merge ($merge, array_combine(array ($k), array_values($v)));
					}
				}
			}
			return $merge;
		}

		private static function getBoxScoreSimpleWeeklyData (PearDatabase $adb, $boxScoreDataId, $encodedVariables) {
			$variables    = json_decode ($encodedVariables, true);
			$whereClauses = array ('box_score_dataid=?');
			$arguments    = array ($boxScoreDataId);
			if ((!empty ($variables ['fecha_desde'])) && (!empty ($variables ['fecha_hasta']))) {
				$whereClauses [] = 'fecha BETWEEN ? AND ?';
				$arguments []    = $variables ['fecha_desde'];
				$arguments []    = $variables ['fecha_hasta'];
			} else {
				$whereClauses [] = 'fecha>=(CURDATE() - INTERVAL 10 WEEK)';
			}
			$whereClauses = join (' AND ', $whereClauses);

			$result = $adb->pquery (
				"SELECT *, WEEK(fecha, 1) AS semana FROM vtiger_box_score_data_semanal WHERE {$whereClauses} ORDER BY fecha ASC",
				$arguments
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$data = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$data [ $row ['semana'] ] = $row;
			}
			return $data;
		}

		private static function getCalculationSelect ($graphData, $fields, $operation, &$fieldLabel) {
			$operationElements = explode(';',$graphData ['fieldcompare']);
			$selectCalculation = array ();
			$total             = count ($operationElements);
			for ($k = 0; $k < $total; $k = ($k + 2)) {
				$key   = array_search($operationElements [$k], $fields);
				$dummy = explode('.', $operationElements [$k]);
				if ($dummy [0] == 'vtiger_subfields_values') {
					$dummyGrid            = explode('@', $dummy [1]);
					$gridTable            = 'vtiger_grid_summary_' . $dummyGrid [0];
					$selectCalculation [] = self::getSelectClausesChart($gridTable . '.' . $dummyGrid [1], $operation[$key], '');
				} else {
					$selectCalculation [] = self::getSelectClausesChart ($fields[ $key ], $operation[ $key ], '');
				}
				if ($k == 0) {
					$selectCalculation [] = $operationElements[ ($k + 1)];
				}
			}
			$selectCalculation [] = 'AS calculo';
			$fieldLabel []        = 'Cálculo';
			return join (' ', $selectCalculation);
		}

		private static function getColumnName (PearDatabase $adb, $moduleName, $fieldName) {
			if ((empty ($moduleName)) || (empty ($fieldName))) {
				return $fieldName;
			}

			$result = $adb->pquery ('SELECT * FROM vtiger_field WHERE fieldname=? AND tabid IN (SELECT tabid FROM vtiger_tab WHERE name=?)', array ($fieldName, $moduleName));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return $fieldName;
			}

			$row = $adb->fetchByAssoc ($result, -1, false);
			return ($row ['tablename'] == 'vtiger_crmentity') ? 'crm.' . $row ['columnname'] : $row ['columnname'];
		}

		private static function getCompareOperation ($operTypo, $compareField) {
			if ($operTypo == self::OPERATION_COUNT) {
				return "COUNT({$compareField}) AS contador2";
			} else if ($operTypo == self::OPERATION_SUM) {
				return "SUM({$compareField}) AS contador2";
			} else if ($operTypo == self::OPERATION_AVERAGE) {
				return "AVG({$compareField})  AS contador2";
			} else {
				return null;
			}
		}

		private static function getDateGrouping ($graphData, &$selectClauses, &$fieldLabel) {
			switch ($graphData ['dategrouping']) {
				case 1:
					$selectClauses [] = 'YEAR(crm.createdtime) AS yeardata';
					$selectClauses [] = 'CONCAT_ws("-", YEAR(crm.createdtime), LPAD(MONTH(crm.createdtime),2,0), LPAD(DAY(crm.createdtime),2,0)) AS stringdata';
					$selectClauses [] = 'DAY(crm.createdtime) AS dategrouping';
					$fieldLabel []    = 'Año - Mes - Día';
					break;
				case 2:
					$selectClauses [] = 'YEAR(crm.createdtime) AS yeardata';
					$selectClauses [] = 'CONCAT_ws("-", YEAR(crm.createdtime), LPAD(WEEK(crm.createdtime),2,0)) AS stringdata';
					$selectClauses [] = 'WEEK(crm.createdtime) AS dategrouping';
					$fieldLabel []    = 'Año - Semana';
					break;
				case 3:
					$selectClauses [] = 'YEAR(crm.createdtime) AS yeardata';
					$selectClauses [] = 'CONCAT_ws("-", YEAR(crm.createdtime), LPAD(MONTH(crm.createdtime),2,0)) AS stringdata';
					$selectClauses [] = 'MONTH(crm.createdtime) AS dategrouping';
					$fieldLabel []    = 'Año - Mese';
					break;
				case 4:
					$selectClauses [] = 'YEAR(crm.createdtime) AS yeardata';
					$selectClauses [] = 'CONCAT_ws("-", YEAR(crm.createdtime), LPAD(QUARTER(crm.createdtime),2,0)) AS stringdata';
					$selectClauses [] = 'QUARTER(crm.createdtime) AS dategrouping';
					$fieldLabel []    = 'Año - Trimestre';
					break;
				case 5:
					$selectClauses [] = 'YEAR(crm.createdtime) AS yeardata';
					$selectClauses [] = 'CONCAT_ws("-", YEAR(crm.createdtime), LPAD(CEIL(MONTH(crm.createdtime) / 6),2,0)) AS stringdata';
					$selectClauses [] = 'CEIL(MONTH(crm.createdtime) / 6) AS dategrouping';
					$fieldLabel []    = 'Año - Semestre';
					break;
				default:
					$selectClauses [] = 'YEAR(crm.createdtime) AS stringdata';
					$selectClauses [] = 'YEAR(crm.createdtime) AS dategrouping';
					$fieldLabel []    = 'Años';
					break;
			}
		}

		private static  function getDataTypeFromUiType ($uiType) {
			if (empty ($uiType)) {
				$dataType = null;
			} else if ($uiType == FieldInterface::UI_TYPE_CHECKBOX) {
				$dataType = FieldInterface::DATA_TYPE_CHECKBOX;
			} else if (in_array ($uiType, array (FieldInterface::UI_TYPE_ATTACHMENTS, FieldInterface::UI_TYPE_CODE, FieldInterface::UI_TYPE_IMAGE_DISPLAY, FieldInterface::UI_TYPE_MODIFIED_BY, FieldInterface::UI_TYPE_MODULE_RECORDS, FieldInterface::UI_TYPE_MODULE_REFERENCE, FieldInterface::UI_TYPE_MULTI_SELECT, FieldInterface::UI_TYPE_OWNER, FieldInterface::UI_TYPE_PHONE, FieldInterface::UI_TYPE_PICKLIST, FieldInterface::UI_TYPE_PIPELINE, FieldInterface::UI_TYPE_SKYPE, FieldInterface::UI_TYPE_TEXT, FieldInterface::UI_TYPE_TEXTAREA, FieldInterface::UI_TYPE_URL))) {
				$dataType = FieldInterface::DATA_TYPE_VARCHAR;
			} else if (in_array ($uiType, array (FieldInterface::UI_TYPE_CREATED_TIME, FieldInterface::UI_TYPE_DATETIME))) {
				$dataType = FieldInterface::DATA_TYPE_DATETIME;
			} else if (in_array ($uiType, array (FieldInterface::UI_TYPE_CURRENCY, FieldInterface::UI_TYPE_PERCENTAGE, FieldInterface::UI_TYPE_CALCULATED_LINK, FieldInterface::UI_TYPE_CALCULATED, FieldInterface::UI_TYPE_SUMMARY_ROW))) {
				$dataType = FieldInterface::DATA_TYPE_NUMBER;
			} else if (in_array ($uiType, array (FieldInterface::UI_TYPE_DATE))) {
				$dataType = FieldInterface::DATA_TYPE_DATE;
			} else if ($uiType == FieldInterface::UI_TYPE_EMAIL) {
				$dataType = FieldInterface::DATA_TYPE_EMAIL;
			} else if (in_array ($uiType, array (FieldInterface::UI_TYPE_NUMBER))) {
				$dataType = FieldInterface::DATA_TYPE_NEGATIVE_NUMBER;
			} else if (in_array ($uiType, array (FieldInterface::UI_TYPE_TIME))) {
				$dataType = FieldInterface::DATA_TYPE_TIME;
			} else {
				$dataType = FieldInterface::DATA_TYPE_VARCHAR;
			}
			return $dataType;
		}

		private static function getDescriptionEntity (PearDatabase $adb, $moduleName) {
			$result = $adb->pquery ('SELECT fieldname FROM vtiger_entityname WHERE modulename=?', array ($moduleName));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			$row = $adb->fetchByAssoc ($result, -1, false);
			return $row ['fieldname'];
		}

		private static function getEntityData (PearDatabase $adb, $moduleName) {
			$result = $adb->pquery ('SELECT * FROM vtiger_entityname WHERE modulename=?', array ($moduleName));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			return $adb->fetchByAssoc ($result, -1, false);
		}

		private static function getEntityField (PearDatabase $adb, $tableName) {
			$result = $adb->pquery ('SELECT entityidfield FROM vtiger_entityname WHERE tablename=?', array ($tableName));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			$row = $adb->fetchByAssoc ($result, -1, false);
			return $row ['entityidfield'];
		}

		private static function tableHasColumn (PearDatabase $adb, $tableName, $columnName) {
			if (empty ($tableName) || empty ($columnName)) {
				return false;
			}
			$result = $adb->pquery ("SHOW COLUMNS FROM {$tableName} LIKE ?", array ($columnName));
			return (($result) && ($adb->num_rows ($result) > 0));
		}

		private static function getModuleTableIndex ($moduleName) {
			if (empty ($moduleName)) {
				return null;
			}
			$rootDir = dirname (dirname (dirname (__FILE__)));
			$moduleFile = $rootDir . '/modules/' . $moduleName . '/' . $moduleName . '.php';
			if (!file_exists ($moduleFile)) {
				return null;
			}
			require_once ($moduleFile);
			if (!class_exists ($moduleName)) {
				return null;
			}
			$instance = new $moduleName ();
			if (isset ($instance->table_index)) {
				return $instance->table_index;
			}
			return null;
		}

		/**
		 * @codingStandardsIgnoreStart
		 * Función que genera la clausula From del SQL para obtener la data para los gráficos
		 * NOTA: CodeSniffer detecta una violación de complejidad ciclomática, pero dada la complijidad del proceso
		 * se hace imposible reducir dicha complejidad.
		 * @param PearDatabase $adb
		 * @param array $modules
		 * @param array $from
		 * @param array $tableName
		 */
		private static function getFromClause ($adb,$modules, &$from, &$tableName) {
			$modules = array_values (array_unique ($modules));
			$totalModules = count ($modules);
			if ($totalModules >= 3) {
				self::sortModules ($adb, $modules);
				$k = 1;
				foreach ($modules as $module) {
					for ($i = $k; $i < $totalModules; $i++) {
						$result         = self::getModulesRel($adb, array($module, $modules [$i]));
						$mainModuleData = self::getEntityData($adb, $modules [$i]);
						if (!empty($result) && $i == 1) {
							$mainModuleData = self::getEntityData($adb, $result[0]['main']);
							$tableName []   = $mainModuleData ['tablename'];
							$from []        = "INNER JOIN {$mainModuleData['tablename']} ON {$mainModuleData['tablename']}.{$mainModuleData['entityidfield']} = crm.crmid";
							$from []        = "LEFT JOIN {$result[0]['deptable']} ON {$result[0]['deptable']}.{$result[0]['field']} ={$mainModuleData['tablename']}.{$mainModuleData['entityidfield']}";
							$tableName []   = $result[0]['deptable'];
						} else if ((!empty($result)) && ($i > 1)) {
							$mainModuleData = self::getEntityData($adb, $result[0]['main']);
							if (!in_array ($mainModuleData['tablename'], $tableName)) {
								$from [] = "LEFT JOIN {$mainModuleData['tablename']} ON {$mainModuleData['tablename']}.{$mainModuleData['entityidfield']} = {$result[0]['deptable']}.{$result[0]['field']}";
							} else if (!in_array ($result[0]['deptable'], $tableName)) {
								$from [] = "LEFT JOIN {$result[0]['deptable']} ON {$result[0]['deptable']}.{$result[0]['field']} = {$mainModuleData['tablename']}.{$mainModuleData['entityidfield']}";
							}
							$tableName [] = $result[0]['deptable'];
							$tableName [] = $mainModuleData['tablename'];
						} else if (!in_array ($mainModuleData['tablename'], $tableName)) {
							$from []      = "LEFT JOIN {$mainModuleData['tablename']} ON {$mainModuleData['tablename']}.{$mainModuleData['entityidfield']} = crm.crmid";
							$tableName [] = $mainModuleData['tablename'];
						}
					}
					$k++;
				}
			} else if ($totalModules == 2) {
				$result = self::getModulesRel($adb, $modules);
				if (!empty($result)) {
					$mainModuleData = self::getEntityData($adb, $result[0]['main']);
					$tableName []   = $mainModuleData ['tablename'];
					$from []        = "INNER JOIN {$mainModuleData['tablename']} ON {$mainModuleData['tablename']}.{$mainModuleData['entityidfield']} = crm.crmid ";
					$from []        = "LEFT JOIN {$result[0]['deptable']} ON {$result[0]['deptable']}.{$result[0]['field']} ={$mainModuleData['tablename']}.{$mainModuleData['entityidfield']}";
					$tableName []   = $result[0]['deptable'];
				} else {
					$mainModuleData = self::getEntityData($adb, $modules[0]);
					$from []        = "LEFT JOIN {$mainModuleData['tablename']} ON {$mainModuleData['tablename']}.{$mainModuleData['entityidfield']} = crm.crmid";
					$tableName []   = $mainModuleData ['tablename'];
					$mainModuleData = self::getEntityData($adb, $modules[1]);
					$from []        = "LEFT JOIN {$mainModuleData['tablename']} ON {$mainModuleData['tablename']}.{$mainModuleData['entityidfield']} = crm.crmid";
					$tableName []   = $mainModuleData ['tablename'];
				}
			} else {
				$mainModuleData = self::getEntityData ($adb, $modules[0]);
				if (!in_array ($mainModuleData['tablename'], $tableName)) {
					$tableName [] = $mainModuleData ['tablename'];
					$from []      = "INNER JOIN {$mainModuleData['tablename']} ON {$mainModuleData['tablename']}.{$mainModuleData['entityidfield']} = crm.crmid ";
				}
			}
		}
		// @codingStandardsIgnoreEnd

		/**
		 * @param PearDatabase $adb
		 * @param array $row
		 * @param array $graphData
		 * @param array $data
		 * @param array $record
		 * @param array $arguments
		 *
		 * @throws Exception
		 */
		private static function getGraphicRecords ($adb, $row, $graphData, &$data, &$record, $arguments) {
			list ($fieldOperation, $uitypeFieldOperation, $tableName, $fieldDescription, $selectClauses, $uitypeFieldDescription, $labelFieldDescription) = $arguments;
			$pointPos       = strpos ($fieldOperation, '.');
			$fieldOperation = ($pointPos === false) ? $fieldOperation : substr ($fieldOperation, ($pointPos + 1));
			$columnNames    = array_keys ($row);
			foreach ($columnNames as $columnName) {
				if ($columnName != $fieldOperation) {
					$record [ $columnName ] = $row [ $columnName ];
				} else {
					if ($uitypeFieldOperation == FieldInterface::UI_TYPE_MODULE_REFERENCE) {
						$key   = $fieldOperation;
						$value = self::getValueFmr ($adb, $tableName, $fieldOperation, $row [ $fieldOperation ]);
					} else if ($uitypeFieldOperation == FieldInterface::UI_TYPE_OWNER) {
						$key   = 'assigned_user_id';
						$value = self::getUserFullName ($adb, $row [ $fieldOperation ]);
					} else if ($uitypeFieldOperation == FieldInterface::UI_TYPE_GRID) {
						if ($uitypeFieldDescription == FieldInterface::UI_TYPE_MODULE_REFERENCE) {
							$row [ $fieldDescription ] = self::getValueFmr ($adb, $tableName, $fieldDescription, $row [ $fieldDescription ]);
						} else {
							$row [ $fieldDescription ] = $labelFieldDescription . ': ' . $row [ $fieldDescription ];
						}
						self::getGridGraphicData ($adb, $graphData, $selectClauses, $row, $data);
					} else {
						$key   = $fieldOperation;
						$value = $row [ $fieldOperation ];
					}
					if (isset($value) && isset($key)) {
						$value = (strlen ($value) > 23) ? substr ($value, 0, 22) . '...' : $value;
						$record [ $key ] = $value;
						unset ($value,$key);
					}
				}
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param array $graphData
		 * @param string $selectedFields
		 * @param array $row
		 * @param $data
		 *
		 * @throws Exception
		 */
		private static function getGridGraphicData ($adb, $graphData, $selectedFields, $row, &$data) {
			list ($gridFileName,$titleField,$dataField, $isSummary) = explode ('@', $graphData ['gridoperation']);
			list ($entityId, $entityDescription)                    = explode (',', $selectedFields);
			$summaryRow  = (!empty($isSummary)) ? true : false;
			$gridValues  = GridFieldUtils::getGridValues ($adb,$graphData ['fld_module'],$gridFileName, $row [ $entityId ], $summaryRow);
			if ($summaryRow) {
				$gridSummaryValues [] = $gridValues ['summary'];
				$fieldValues          = array_column($gridSummaryValues, $dataField);
				if (!empty ($fieldValues)) {
					$titles = array_fill(0, count ($fieldValues),$row [ trim ($entityDescription) ]);
				}
				unset($gridSummaryValues);
			} else {
				$titles      = array_column ($gridValues, $titleField);
				$fieldValues = array_column ($gridValues, $dataField);
			}

			if (!empty ($titles) && (!empty ($titles[0]))) {
				foreach (array_combine ($titles, $fieldValues) as $key => $value) {
					$data [] = array(
						$titleField => $key,
						'contador'  => $value,
					);
				}
			}
		}

		private static function getRegularDateGraphRawData (PearDatabase $adb, $moduleName, $fieldOperation, $dateGrouping, $dateFilter) {
			$tableName    = self::getEntityTableName ($adb, $moduleName);
			$idColumnName = self::getEntityIdField ($adb, $moduleName);
			$dayFrom      = $dateFilter['dateFrom'];
			$dayUntil     = $dateFilter['dateTo'];
			$dummy        = explode ('-', $dayFrom);
			$today        = new DateTime ();
			$today->setDate ($dummy[0], $dummy[1], $dummy[2]);

			$selectClauses = array ();
			switch ($dateGrouping) {
				case 2:
					$selectClauses []  = "DATE_FORMAT({$fieldOperation}, '%v/%Y') AS label";
					$dateFromClause    = 'CURDATE() - INTERVAL 4 WEEK';
					$startDateInterval = new DateInterval ('P30D');
					$startDate         = $today->sub ($startDateInterval);
					$dateInterval      = new DateInterval ('P7D');
					break;
				case 3:
					$selectClauses []  = "DATE_FORMAT({$fieldOperation}, '%m/%Y') AS label";
					$dateFromClause    = 'CURDATE() - INTERVAL 4 MONTH';
					$startDateInterval = new DateInterval ('P4M');
					$startDate         = $today->sub ($startDateInterval);
					$dateInterval      = new DateInterval ('P1M');
					break;
				case 4:
					$selectClauses []  = "CONCAT(QUARTER({$fieldOperation}), '/', YEAR({$fieldOperation})) AS label";
					$dateFromClause    = 'CURDATE() - INTERVAL 4 QUARTER';
					$startDateInterval = new DateInterval ('P1Y');
					$startDate         = $today->sub ($startDateInterval);
					$dateInterval      = new DateInterval ('P3M');
					break;
				case 5:
					$selectClauses []  = "CONCAT(IF(MONTH({$fieldOperation})<7, 1, 2), '/', YEAR({$fieldOperation})) AS label";
					$dateFromClause    = 'CURDATE() - INTERVAL 365 DAY';
					$startDateInterval = new DateInterval ('P1Y');
					$startDate         = $today->sub ($startDateInterval);
					$dateInterval      = new DateInterval ('P6M');
					break;
				case 6:
					$selectClauses []  = "YEAR({$fieldOperation}) AS label";
					$dateFromClause    = 'CURDATE() - INTERVAL 365 DAY';
					$startDateInterval = new DateInterval ('P1Y');
					$startDate         = $today->sub ($startDateInterval);
					$dateInterval      = new DateInterval ('P1Y');
					break;
				default:
					$selectClauses []  = "DATE_FORMAT({$fieldOperation}, '%d/%m/%Y') AS label";
					$dateFromClause    = 'CURDATE() - INTERVAL 30 DAY';
					$startDateInterval = new DateInterval ('P30D');
					$startDate         = $today->sub ($startDateInterval);
					$dateInterval      = new DateInterval ('P1D');
					break;
			}

			$selectClauses [] = 'COUNT(*) AS contador';
			$selectClauses    = join (', ', $selectClauses);

			if (!empty($dayFrom) && !empty($dayUntil)) {
				$where = " AND DATE(crm.modifiedtime) BETWEEN STR_TO_DATE('{$dayFrom}','%Y-%m-%d') AND STR_TO_DATE('{$dayUntil}','%Y-%m-%d')";
			} else {
				$where = " AND DATE({$fieldOperation}) BETWEEN {$dateFromClause} AND CURDATE() ";
			}

			$result = $adb->query ("SELECT {$selectClauses} FROM {$tableName} tq INNER JOIN vtiger_crmentity crm ON crm.crmid=tq.{$idColumnName} WHERE crm.deleted=0 {$where} GROUP BY label");
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$databaseData = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$databaseData [ $row ['label'] ] = $row ['contador'];
			}

			return array (
				'data'         => $databaseData,
				'dateinterval' => $dateInterval,
				'startdate'    => $startDate,
			);
		}

		private static function getModuleNameByField (PearDatabase $adb, $fieldName) {
			if (empty ($fieldName)) {
				return null;
			}
			$result = $adb->pquery ('SELECT name FROM vtiger_tab t INNER JOIN vtiger_field f ON f.tabid = t.tabid WHERE f.fieldname=?', array ($fieldName));
			if ($adb->num_rows ($result) > 0) {
				$row = $adb->fetchByAssoc ($result, -1, false);
				return $row ['name'];
			}
			return null;
		}

		private static function getRelationField (PearDatabase $adb, $fieldName, $modules, &$tableName) {
			foreach ($modules as $module) {
				$result = $adb->pquery ("SELECT f.fieldname, f.tablename FROM vtiger_field f INNER JOIN vtiger_tab t ON t.tabid = f.tabid  WHERE f.fieldname LIKE '{$fieldName}%' AND t.name='{$module}'");
				if (($result) && ($adb->num_rows ($result) > 0)) {
					$row    = $adb->fetchByAssoc ($result, -1, false);
					if (!in_array ($row ['tablename'], $tableName)) {
						$tableName [] = $row ['tablename'];
						return $row;
					}
				}
			}
			return null;
		}

		/**
		 * @param array $graphData
		 * @param array $fieldOperation
		 *
		 * @return array
		 */
		private static function getSelectClauses ($graphData, $fieldOperation) {
			$selectClauses = array ();
			if ($graphData ['operation'] == self::OPERATION_COUNT) {
				$selectClauses [] = $fieldOperation;
				$selectClauses [] = 'COUNT(*) AS contador';
			} else if ($graphData ['operation'] == self::OPERATION_SUM) {
				$selectClauses [] = $fieldOperation;
				$selectClauses [] = "SUM({$graphData ['fieldgrouping']}) AS contador";
			} else if ($graphData ['operation'] == self::OPERATION_AVERAGE) {
				$selectClauses [] = $fieldOperation;
				$selectClauses [] = "AVG({$graphData ['fieldgrouping']}) AS contador";
			}

			if ((($graphData['tipografico'] == 'barra') || ($graphData['tipografico'] == 'puntos')) && (!empty($graphData['fieldcompare']))) {
				$selectClauses [] = self::getCompareOperation ($graphData['compareoperation'], $graphData['fieldcompare']);
			}
			return $selectClauses;
		}

		private static function getSelectClausesChart ($fieldOperation, $operation, $field) {
			$selectClauses = '';

			if ($operation == self::OPERATION_COUNT) {
				$selectClauses = "COUNT({$fieldOperation})";
			} else if ($operation == self::OPERATION_SUM) {
				$selectClauses = "IFNULL(SUM({$fieldOperation}),0)";
			} else if ($operation == self::OPERATION_AVERAGE) {
				$selectClauses = "IFNULL(AVG({$fieldOperation}),0)";
			} else if ($operation == self::OPERATION_MAXIMUN) {
				$selectClauses = "IFNULL(MAX({$fieldOperation}),0)";
			} else if ($operation == self::OPERATION_MINIMUN) {
				$selectClauses = "IFNULL(MIN({$fieldOperation}),0)";
			}
			$selectClauses .= (!empty ($field)) ? ' AS '. $field : '';
			return $selectClauses;
		}

		private static function modifyDateIfIsOnLastYearDays ($date) {
			if ((date ('m', $date) == '12') && (date ('d', $date) > 28)) {
				return strtotime (date ('Y-m-28', $date) . ' -0 week');
			} else {
				return $date;
			}
		}

		private static function getEquation ($fields, $values, $operators) {
			$equation   = '';
			$typeofdata = array (
				'V'  => array ('e' => ' LIKE "@"', 'n' => ' NOT LIKE "@"', 's' => ' LIKE "@%"', 'ew' => ' LIKE "%@"', 'c' => ' LIKE "%@%"', 'k' => ' NOT LIKE "%@%"', 'in' => ' IS @', 'inn' => ' IS @'),
				'N'  => array ('e' => ' = ', 'n' => ' != ', 'l' => ' < ', 'g' => ' > ', 'm' => ' <= ', 'h' => ' >= ', 'in' => ' IS @', 'inn' => ' IS @'),
				'T'  => array ('e' => ' = "@"', 'n' => ' != "@"', 'l' => ' < "@"', 'g' => ' > "@"', 'm' => ' <= "@"', 'h' => ' >= "@"', 'bw' => ' * BETWEEN DATE( "@" ) AND DATE( "_"  )', 'b' => ' * < DATE( "@" )', 'a' => ' * > DATE( "@" )', 'in' => ' IS @', 'inn' => ' IS @'),
				'I'  => array ('e' => ' = ', 'n' => ' != ', 'l' => ' < ', 'g' => ' > ', 'm' => ' <= ', 'h' => ' >= ', 'in' => ' IS @', 'inn' => ' IS @'),
				'C'  => array ('e' => ' = ', 'n' => ' != ', 'in' => ' IS @', 'inn' => ' IS @'),
				'D'  => array ('e' => ' = "@"', 'n' => ' != "@"', 'l' => ' < "@"', 'g' => ' > "@"', 'm' => ' <= "@"', 'h' => ' >= "@"', 'bw' => ' * BETWEEN DATE( "@" ) AND DATE( "_"  )', 'b' => ' * < DATE( "@" )', 'a' => ' * > DATE( "@" )', 'in' => ' IS @', 'inn' => ' IS @'),
				'DT' => array ('e' => ' = "@"', 'n' => ' != "@"', 'l' => ' < "@"', 'g' => ' > "@"', 'm' => ' <= "@"', 'h' => ' >= "@"', 'bw' => ' * BETWEEN DATE( "@" ) AND DATE( "_"  )', 'b' => ' * < DATE( "@" )', 'a' => ' * > DATE( "@" )', 'in' => ' IS @', 'inn' => ' IS @'),
				'NN' => array ('e' => ' = ', 'n' => ' != ', 'l' => ' < ', 'g' => ' > ', 'm' => ' <= ', 'h' => ' >= ', 'in' => ' IS @', 'inn' => ' IS @'),
				'E'  => array ('e' => ' LIKE "@"', 'n' => ' NOT LIKE "@"', 's' => ' LIKE "@%"', 'ew' => ' LIKE "%@"', 'c' => ' LIKE "%@%"', 'k' => ' NOT LIKE "%@%"', 'in' => ' IS @', 'inn' => ' IS @'),
			);

			list($fieldType, $fieldName) = explode ('@', $fields);
			list($min, $max) = explode (',', $values);

			$operated = $typeofdata[ $fieldType ][ $operators ];

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

		private static function getUserFullName (PearDatabase $adb, $userId) {
			$result = $adb->pquery ('SELECT * FROM vtiger_users WHERE id=?', array ($userId));
			if ($adb->num_rows ($result) > 0) {
				$row      = $adb->fetchByAssoc ($result, -1, false);
				$fullName = trim ("{$row ['first_name']} {$row ['last_name']}");
			} else {
				$fullName = $userId;
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
			return $fullName;
		}

		private static function searchCommonFields (PearDatabase $adb, $modules, &$tableName, $table) {
			foreach ($modules as $module) {
				$moduleTable = self::getEntityTableName($adb, $module);
				if ($moduleTable == $table) {
					continue;
				}
				$result = $adb->pquery ("SELECT f.fieldname, f.tablename FROM vtiger_field f INNER JOIN vtiger_tab t ON t.tabid = f.tabid  WHERE f.fieldname IN (SELECT fieldname FROM vtiger_field WHERE tablename = '{$table}') AND uitype = 10 AND t.name='{$module}'");
				if (($result) && ($adb->num_rows ($result) > 0)) {
					$row    = $adb->fetchByAssoc ($result, -1, false);
					$tableName [] = $table;
					return "LEFT JOIN {$table} ON {$table}.{$row ['fieldname']} = {$row ['tablename']}.{$row ['fieldname']}";
				}
			}
			$fieldIndex    = self::getEntityField ($adb, $table);
			if (empty($fieldIndex)) {
				return null;
			}
			$relationIndex = substr ($fieldIndex, 0, -3);
			foreach ($tableName as $tablename) {
				$result = $adb->pquery ("SELECT fieldname FROM vtiger_field WHERE fieldname LIKE '{$relationIndex}%' AND tablename='{$tablename}'");
				if (($result) && ($adb->num_rows ($result) > 0)) {
					$row    = $adb->fetchByAssoc ($result, -1, false);
					return "LEFT JOIN {$table} ON {$table}.{$fieldIndex} = {$tablename}.{$row ['fieldname']}";
				}
			}
			return null;
		}

		private static function searchRelationField (PearDatabase $adb, $moduleName, $fieldName) {
			if (empty ($moduleName) || empty ($fieldName)) {
				return null;
			}
			$result = $adb->pquery (
				'SELECT 
					fm.relmodule,
					en.tablename,
					en.entityidfield,
					en.fieldname,
					f.fieldlabel
				FROM 
					`vtiger_entityname` en
					INNER JOIN `vtiger_fieldmodulerel` fm ON fm.relmodule = en.modulename 
					INNER JOIN `vtiger_field` f ON f.fieldid = fm.fieldid
				WHERE 
				fm.module=? AND f.fieldname=?',
				array ($moduleName, $fieldName)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			return $adb->fetchByAssoc ($result, -1, false);
		}

		/**
		 * @param PearDatabase $adb
		 * @param array $parameters
		 * @param array $fieldLabel
		 * @param array $from
		 * @param array $tableName
		 * @param array $selectClauses
		 */
		private static function setGridSqlRelation (PearDatabase $adb, $parameters, &$fieldLabel, &$from, &$tableName, &$selectClauses) {
			$dummy         = $parameters ['dummy'];
			$modules       = $parameters ['modules'];
			$operation     = $parameters ['operation'];
			$k             = $parameters ['indexFor'];
			$dummyGrid     = explode('@', $dummy [1]);
			$label         = self::getFieldLabel ($adb, $dummyGrid [0]);
			$fieldLabel [] = $label . '(' . ucfirst (preg_replace ('/_{1,}\\d+$/', '', $dummyGrid [1])) . ')';
			$gridTable     = 'vtiger_grid_summary_' . $dummyGrid [0];
			if (!in_array ($gridTable, $tableName)) {
				$moduleGril     = self::getModuleNameByField($adb, $dummyGrid[0]);
				$mainModuleData = self::getEntityData($adb, $moduleGril);
				$from []        = "LEFT JOIN `{$gridTable}` ON `{$gridTable}`.`recordid`= {$mainModuleData['tablename']}.{$mainModuleData['entityidfield']}";
				$tableName [] = $gridTable;
				if (!in_array ($mainModuleData['tablename'], $tableName)) {
					$from []  = "LEFT JOIN `{$mainModuleData['tablename']}` ON `{$mainModuleData['tablename']}`.`{$mainModuleData['entityidfield']}`=`crm`.`crmid`";
				}
			}
			$selectClauses [] = self::getSelectClausesChart ($gridTable.'.'.$dummyGrid [1], $operation[ $k ], $dummyGrid [1]);
			if (!array_search ($gridTable, array_column (self::$summaryTable, $modules[ $k ]))) {
				self::$summaryTable = array ($modules[ $k ] => $gridTable);
			}
		}

		private static function setGridTableFromFilter ($adb, $graphData, &$tableName, &$from) {
			$filters = json_decode($graphData ['varreporte'],true);
			foreach ($filters ['filterField'] as $filter) {
				$dummy = explode('.', $filter);
				if ($dummy [0] == 'vtiger_subfields_values') {
					$dummyGrid = explode('@', $dummy [1]);
					$gridTable = 'vtiger_grid_summary_' . $dummyGrid [0];
					if (!in_array ($gridTable, $tableName)) {
						$moduleGril     = self::getModuleNameByField ($adb, $dummyGrid[0]);
						$mainModuleData = self::getEntityData($adb, $moduleGril);
						$from []        = "LEFT JOIN `{$gridTable}` ON `{$gridTable}`.`recordid`= {$mainModuleData['tablename']}.{$mainModuleData['entityidfield']}";
						$tableName []   = $gridTable;
						if (!in_array ($mainModuleData['tablename'], $tableName)) {
							$from []  = "LEFT JOIN `{$mainModuleData['tablename']}` ON `{$mainModuleData['tablename']}`.`{$mainModuleData['entityidfield']}`=`crm`.`crmid`";
						}
					}
					if (!array_search ($gridTable, array_column (self::$summaryTable, $moduleGril))) {
						self::$summaryTable = array ($moduleGril => $gridTable);
					}
				}
			}
		}

		private static function sortModules ($adb, &$modules) {
			$totalModules = count ($modules);
			$k            = 1;
			$swSwap = false;
			foreach ($modules as $module) {
				for ($i = $k; $i < $totalModules; $i++) {
					$result = self::getModulesRel ($adb, array ($module, $modules[ $i ]));
					if (empty($result)) {
						continue;
					} else if(($k == 1) && ($i == 1)) {
						break;
					} else if(($k == 1) && ($i == 2)) {
						$tmp         = $modules [1];
						$modules [1] = $modules [2];
						$modules [2] = $tmp;
						$swSwap      = true;
						break;
					} else if(($k == 2) && ($i == 2)) {
						$tmp         = $modules [0];
						$modules [0] = $modules [2];
						$modules [2] = $tmp;
						$swSwap      = true;
						break;
					}
				}
				if ($swSwap) {
					break;
				}
				$k++;
			}
		}

		private static function prepareFields (PearDatabase $adb, &$fields) {
			$totalFiels = count($fields);
			for ($k = 0; $k < $totalFiels; $k++) {
				$dummy = explode('.', $fields[ $k ]);
				if ($dummy [0] != 'vtiger_subfields_values') {
					$uiType = self::getUiType ($adb, $dummy [0], $dummy [1]);
					$fields[ $k ] = self::getDataTypeFromUiType ($uiType) . '@' . $fields[ $k ];
					continue;
				}
				$dummyGrid    = explode('@', $dummy [1]);
				$fields[ $k ] = "N@vtiger_grid_summary_{$dummyGrid[0]}.{$dummyGrid[1]}";
			}
		}
		
		/**
		 * @param array $codIds
		 * @param integer $first
		 *
		 * @return array
		 */
		public static function customSort ($codIds, $first) {
			if (!count ($codIds) || count ($codIds) == 1) {
				return $codIds;
			}
			
			$customIds[] = $first;
			foreach ($codIds as $value) {
				if ($value == $first) {
					continue;
				}
				$customIds[] = $value;
			}
			return $customIds;
		}
		
		public static function deleteGraph (PearDatabase $adb, $graphId) {
			$gm    = GraphicManager::getInstance ($adb);
			$chart = $gm->fetchChart ($graphId);
			if (!empty ($chart)) {
				$gm->deleteChart ($chart);
			}
		}

		public static function getAppCode (PearDatabase $adb, $applicationId) {
			$result = $adb->pquery ('SELECT app_code FROM vtiger_config_applications WHERE config_applicationsid=?', array ($applicationId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			$row = $adb->fetchByAssoc ($result, -1, false);
			return $row ['app_code'];
		}

		public static function getApplicationId (PearDatabase $adb, $moduleId) {
			$result = $adb->pquery (
				'SELECT config_applicationsid AS appid FROM vtiger_configapps_tab AS cat INNER JOIN vtiger_tab AS t WHERE t.tabid=cat.tabid AND t.tabid=?',
				array ($moduleId)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$row = $adb->fetchByAssoc ($result, -1, false);
			return $row ['config_applicationsid'];
		}

		public static function getBoxScoreSimpleGraphData (PearDatabase $adb, $graphData) {
			if ((empty ($graphData)) || (empty ($graphData ['sqlprimarioreporte']))) {
				return null;
			}

			$result = $adb->query ($graphData ['sqlprimarioreporte']);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$data = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$row ['semanal'] = self::getBoxScoreSimpleWeeklyData ($adb, $row ['box_score_dataid'], $graphData ['varreporte']);
				$data []         = $row;
			}
			return $data;
		}

		/**
		 * @return array
		 */
		public static function getCategories ($excludedCategories = null) {
			$categories     = array_map('html_entity_decode', array_column (getHeaderArray (), 'name'));
			$categoriesKeys = str_replace (
				array ('á', 'á', 'é', 'é','í', 'í','ó', 'ó', 'ú', 'ú'),
				array ('a', 'a','e','e', 'i', 'i', 'o', 'o','u','u'),
				$categories
			);
			$resultArray = array_combine ($categoriesKeys, $categories);
			if (!empty ($excludedCategories) && is_array ($excludedCategories)) {
				foreach ($excludedCategories as $category) {
					if (in_array ($category, $categoriesKeys)) {
						unset ($resultArray [$category]);
					}
				}
			}
			return $resultArray;
		}

		public static function getDatesFromVariables ($graphData) {
			$dates     = array ();
			$variables = json_decode ($graphData ['varreporte'], true);
			if ((!empty ($variables ['fecha_desde'])) && (!empty ($variables ['fecha_hasta']))) {
				$monday = date ('Y-m-d', strtotime ('last monday', strtotime ("{$variables ['fecha_desde']} + 1 day")));
				for ($i = 0; $i < 10; $i++) {
					$date     = self::modifyDateIfIsOnLastYearDays (strtotime ("{$monday} +{$i} week"));
					$dates [] = array ('date' => date ('Y-m-d', $date), 'week' => (int) date ('W', $date));
				}
			} else {
				$monday = date ('Y-m-d', strtotime ('last monday', strtotime ('tomorrow')));
				for ($i = 9; $i >= 0; $i--) {
					$date     = self::modifyDateIfIsOnLastYearDays (strtotime ("{$monday} -{$i} week"));
					$dates [] = array ('date' => date ('Y-m-d', $date), 'week' => (int) date ('W', $date));
				}
			}
			return $dates;
		}

		public static function getDefinedDateGroupings () {
			return array (
				self::DATE_GROUPING_DAILY     => 'Año - Día',
				self::DATE_GROUPING_WEEKLY    => 'Año - Semana',
				self::DATE_GROUPING_MONTHLY   => 'Año - Mes',
				self::DATE_GROUPING_QUARTERLY => 'Año - Trimeste',
				self::DATE_GROUPING_BIANNUAL  => 'Año - Semestre',
				self::DATE_GROUPING_ANNUAL    => 'Anual',
			);
		}

		public static function getDefinedGraphTypes () {
			return array (
				array (self::GRAPH_TYPE_AREA => 'Area', 'columns' => 'MULTIPLE'),
				array (self::GRAPH_TYPE_BARS => 'Barras', 'columns' => 'MULTIPLE'),
				array (self::GRAPH_TYPE_DONUT => 'Circular', 'columns' => 'SINGLE'),
				array (self::GRAPH_TYPE_COLUMN => 'Columnas', 'columns' => 'MULTIPLE'),
				array (self::GRAPH_TYPE_COMBO => 'Combo', 'columns' => 'MULTIPLE'),
				array (self::GRAPH_TYPE_FUNNEL => 'Embudo', 'columns' => 'SINGLE'),
				array (self::GRAPH_TYPE_LINE => 'Linea', 'columns' => 'MULTIPLE'),
				array (self::GRAPH_TYPE_TABLE => 'Tabla', 'columns' => 'MULTIPLE'),
			);
		}

		public static function getDefinedOperations () {
			return array (
				self::OPERATION_COUNT   => 'Conteo',
				self::OPERATION_SUM     => 'Suma',
				self::OPERATION_AVERAGE => 'Promedio',
				self::OPERATION_MAXIMUN => 'Valor máximo',
				self::OPERATION_MINIMUN => 'Valor mínimo',
			);
		}

		/**
		 * @param PearDatabase $adb
		 * @param string$moduleName
		 * @param array $row
		 * @param array $columns
		 * @param string $fieldType
		 *
		 * @throws Exception
		 */
		public static function getGridFields (PearDatabase $adb, $moduleName, $row, &$columns, $fieldType = 'string') {
			$fieldsGrid = FieldGridManager::getInstance ($adb)->fetchFieldGrid ($moduleName, $row ['fieldname']);
			if (!$fieldsGrid) {
				return;
			}
			foreach ($fieldsGrid as $field) {
				$typeOfData = self::getDataTypeFromUiType ($field->getUiType ());
				if (
					($fieldType == 'string') &&
					(($typeOfData != FieldInterface::DATA_TYPE_VARCHAR) ||
					(in_array ($field->getUiType (), array (FieldInterface::UI_TYPE_CALCULATED, FieldInterface::UI_TYPE_GRID, FieldInterface::UI_TYPE_SUMMARY_ROW))))
				) {
					continue;
				} else if (($fieldType == 'numeric') && (!in_array ($field->getUiType (),array (FieldInterface::UI_TYPE_NUMBER, FieldInterface::UI_TYPE_PERCENTAGE, FieldInterface::UI_TYPE_CALCULATED, FieldInterface::UI_TYPE_SUMMARY_ROW)))) {
					continue;
				}
				$uitype     = ($fieldType == 'string') ? FieldInterface::UI_TYPE_GRID : $field->getUiType ();
				$fieldLabel = html_entity_decode(getTranslatedString($row ['fieldlabel'], $moduleName), ENT_QUOTES, 'UTF-8');
				$dummy      = explode ('_', $field->getName ());
				array_pop ($dummy);
				$fieldName     = join ('_', $dummy);

				if ($field->getUiType () == FieldInterface::UI_TYPE_SUMMARY_ROW) {
					$summaryConfig = unserialize (base64_decode ($field->getDataField ()));
					$summaryFields = array_column($summaryConfig, 'field');
					foreach ($summaryFields as $column) {
						if($column != 'false') {
							$dummy = explode ('_', $column);
							array_pop ($dummy);
							$colunmName = join ('_', $dummy);
							
							// Formatear nombre de columna: subtotal_articulos → Subtotal Articulos
							$columnLabel = ucfirst(str_replace('_', ' ', $colunmName));
							
							// Construir label mejorado con prefijo [GRID] para mejor visibilidad
							$improvedLabel = '[GRID] ' . $fieldLabel . ' - Total: ' . $columnLabel;
							
							$columns [] = array (
								'fieldname'  => $row ['fieldname'] . '@' . $column . '@' .$fieldName,
								'label'      => $improvedLabel,
								'tablename'  => 'vtiger_subfields_values',
								'uitype'     => $uitype,
								'typeofdata' => $typeOfData,
							);
						}
					}
				}
			}
		}

		public static function getTypeOfData () {
			return array (
				'V'  => array ('e' => 'igual', 'n' => 'no igual a', 's' => 'empieza con', 'ew' => 'termina con', 'c' => 'contiene', 'k' => 'no contiene','in' => 'es nulo','inn' => 'no es nulo'),
				'N'  => array ('e' => 'igual', 'n' => 'no igual a', 'l' => 'menor que', 'g' => 'mayor que', 'm' => 'menor o igual', 'h' => 'mayor o igual','in' => 'es nulo','inn' => 'no es nulo'),
				'T'  => array ('e' => 'igual', 'n' => 'no igual a', 'l' => 'menor que', 'g' => 'mayor que', 'm' => 'menor o igual', 'h' => 'mayor o igual', 'bw' => 'entre', 'b' => 'antes', 'a' => 'después','in' => 'es nulo','inn' => 'no es nulo'),
				'I'  => array ('e' => 'igual', 'n' => 'no igual a', 'l' => 'menor que', 'g' => 'mayor que', 'm' => 'menor o igual', 'h' => 'mayor o igual','in' => 'es nulo','inn' => 'no es nulo'),
				'C'  => array ('e' => 'igual', 'n' => 'no igual a','in' => 'es nulo','inn' => 'no es nulo'),
				'D'  => array ('e' => 'igual', 'n' => 'no igual a', 'l' => 'menor que', 'g' => 'mayor que', ' m ' => 'menor o igual', 'h' => 'mayor o igual', 'bw' => 'entre', 'b' => 'antes', 'a' => 'después','in' => 'es nulo','inn' => 'no es nulo'),
				'DT' => array ('e' => 'igual', 'n' => 'no igual a', 'l' => 'menor que', 'g' => 'mayor que', 'm' => 'menor o igual', 'h' => 'mayor o igual', 'bw' => 'entre', 'b' => 'antes', 'a' => 'después','in' => 'es nulo','inn' => 'no es nulo'),
				'NN' => array ('e' => 'igual', 'n' => 'no igual a', 'l' => 'menor que', 'g' => 'mayor que', 'm' => 'menor o igual', 'h' => 'mayor o igual','in' => 'es nulo','inn' => 'no es nulo'),
				'E'  => array ('e' => 'igual', 'n' => 'no igual a', 's' => 'empieza con', 'ew' => 'termina con', 'c' => 'contiene', 'k' => 'no contiene','in' => 'es nulo','inn' => 'no es nulo'),
			);
		}

		public static function getEntityTableName (PearDatabase $adb, $moduleName) {
			$result = $adb->pquery ('SELECT tablename FROM vtiger_entityname WHERE modulename=?', array ($moduleName));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			$row = $adb->fetchByAssoc ($result, -1, false);
			return $row ['tablename'];
		}

		public static function getEntityIdField (PearDatabase $adb, $moduleName) {
			$result = $adb->pquery ('SELECT entityidfield FROM vtiger_entityname WHERE modulename=?', array ($moduleName));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			$row = $adb->fetchByAssoc ($result, -1, false);
			return $row ['entityidfield'];
		}

		public static function getFieldLabel (PearDatabase $adb, $fieldName) {
			$result = $adb->pquery ('SELECT fieldlabel FROM vtiger_field WHERE fieldname=?', array ($fieldName));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			$row = $adb->fetchByAssoc ($result, -1, false);
			return $row ['fieldlabel'];
		}

		public static function getFiltersGroup (PearDatabase $adb, $filters) {
			if (empty($filters)) {
				return null;
			}
			$filtersByModule = array ();
			$resultArray     = array();
			$filters         = json_decode ($filters, true);
			$index           = 0;
			$indexGroup      = 0;
			foreach ($filters ['moduleFilter'] as $moduleName) {
				$tableName = self::getEntityTableName ($adb, $moduleName);
				$totalFilterField = count ($filters ['filterField']);
				for ($k = $index; $k < $totalFilterField; $k++) {
					$dummy = explode('.', $filters ['filterField'][ $k ]);
					if ($dummy [0] == 'vtiger_subfields_values') {
						$dummyGrid = explode ('@', $dummy [1]);
						$moduleField = self::getModuleNameByField($adb, $dummyGrid [0]);
						if ($moduleField != $moduleName) {
							$index = $k;
							break;
						}
					} else if ($dummy [0] != $tableName) {
						$index = $k;
						break;
					}
					$filtersByModule [$moduleName] ['filterField'][]     = $filters ['filterField'][ $k ];
					$filtersByModule [$moduleName] ['filterOperator'][]  = $filters ['filterOperator'][ $k ];
					$filtersByModule [$moduleName] ['filterValue'][]     = $filters ['filterValue'][ $k ];
					$filtersByModule [$moduleName] ['indexGrupo'][]      = $filters ['indexGrupo'][ $k ];

					if (($k == 0)) {
						$filtersByModule [$moduleName] ['filterJoin'][]      = $filters ['filterJoin'][ $indexGroup ];
						$filtersByModule [$moduleName] ['filterGroupJoin'][] = $filters ['filterGroupJoin'][ $indexGroup ];
						$indexGroup++;
					} else if ($filters ['indexGrupo'][ $k ] != $filters ['indexGrupo'][ ($k - 1) ]) {
						$filtersByModule [$moduleName] ['filterJoin'][]      = $filters ['filterJoin'][ $indexGroup ];
						$filtersByModule [$moduleName] ['filterGroupJoin'][] = $filters ['filterGroupJoin'][ $indexGroup ];
						$indexGroup++;
					}
					$filters ['filterField'][ $k ] = null;
				}

				$resultArray [] = $filtersByModule;
				unset ($filtersByModule);
			}
			return $resultArray;
		}

		public static function getFunnelGraphData (PearDatabase $adb, $graphData) {
			$sql = html_entity_decode ($graphData ['sqlprimarioreporte'], ENT_QUOTES, 'UTF-8');

			$result = $adb->query ($sql);
			$numRows = $adb->num_rows ($result);

			if ((!$result) || ($numRows == 0)) {
				return null;
			}

			$data = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$data [] = $row;
			}

			return $data;
		}

		public static function getRegularFunnelData (PearDatabase $adb, $graphData, $dateFilter) {
			$entityData = self::getEntityData ($adb, $graphData ['fld_module']);
			if (empty ($entityData)) {
				return null;
			}

			$tableName    = $entityData ['tablename'];
			$idColumnName = $entityData ['entityidfield'];
			if (!self::tableHasColumn ($adb, $tableName, $idColumnName)) {
				$moduleTableIndex = self::getModuleTableIndex ($graphData ['fld_module']);
				if (!empty ($moduleTableIndex) && self::tableHasColumn ($adb, $tableName, $moduleTableIndex)) {
					$idColumnName = $moduleTableIndex;
				}
			}
			$dayFrom      = $dateFilter ['dateFrom'];
			$dayUntil     = $dateFilter ['dateTo'];
			$where        = '';

			// Campo por el cual agrupar (ej: fase_de_venta)
			$groupField = !empty ($graphData ['fieldgrouping']) ? $graphData ['fieldgrouping'] : $graphData ['fieldoperation'];
			// Campo sobre el cual aplicar la operación (ej: valor_oportunidad)
			$valueField = $graphData ['fieldoperation'];
			$groupFieldSql = (strpos ($groupField, '.') === false) ? ('tq.' . $groupField) : $groupField;
			$valueFieldSql = (strpos ($valueField, '.') === false) ? ('tq.' . $valueField) : $valueField;
			// Operación a aplicar (1=COUNT, 2=SUM, 3=AVG)
			$operation = intval ($graphData ['operation']);

			// Construir la cláusula de agregación
			if ($operation == self::OPERATION_COUNT) {
				$aggregation = 'COUNT(*) AS contador';
			} else if ($operation == self::OPERATION_SUM) {
				$aggregation = "SUM({$valueFieldSql}) AS contador";
			} else if ($operation == self::OPERATION_AVERAGE) {
				$aggregation = "AVG({$valueFieldSql}) AS contador";
			} else {
				$aggregation = 'COUNT(*) AS contador';
			}

			// Filtro de fechas - Deshabilitado para gráficos de embudo
			// Los gráficos de embudo muestran todos los datos sin restricción temporal

			$query = "SELECT 
					{$groupFieldSql} AS stringdata,
					{$aggregation}
				FROM 
					{$tableName} tq
					INNER JOIN vtiger_crmentity crm ON crm.crmid = tq.{$idColumnName} AND crm.deleted = 0
				WHERE 
					1 {$where}
				GROUP BY 
					{$groupFieldSql}
				ORDER BY 
					contador DESC";

			$result = $adb->query ($query);
			$numRows = $adb->num_rows ($result);
		
			if ((!$result) || ($numRows == 0)) {
				return array ();
			}

			$data = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$data [] = $row;
			}
		
			return $data;
		}

		public static function getGraphById (PearDatabase $adb, $graphId) {
			$result = $adb->pquery ('SELECT * FROM vtiger_graficos WHERE graficoid=?', array ($graphId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			$row = $adb->fetchByAssoc ($result, -1, false);
			$row ['applicationcodes'] = !empty ($row ['applicationcodes']) ? json_decode ($row ['applicationcodes'], true) : null;
			if (!empty($row['varreporte'])) {
				$row['varreporte'] = json_decode ($row['varreporte'], true);
			}
			return $row;
		}

		/**
		 * @param PearDatabase $adb
		 * @param array $graphData
		 * @param array $dateFilter
		 *
		 * @return array
		 * @throws Exception
		 */
		public static function getGraphData (PearDatabase $adb, &$graphData, $dateFilter) {
			// Forzar que los gráficos funnel NO usen Google Charts (no tiene soporte nativo)
			if ($graphData ['tipografico'] === 'funnel') {
				$graphData ['google'] = false;
				// Si tiene SQL personalizado, usar getFunnelGraphData
				if (!empty ($graphData ['sqlprimarioreporte'])) {
					return self::getFunnelGraphData ($adb, $graphData);
				}
				// Verificar si fld_module es un JSON array y extraer el primer módulo
				$fldModule = json_decode (str_replace ('&quot;', '"', $graphData ['fld_module']));
				if (is_array ($fldModule) && count ($fldModule) > 0) {
					$graphData ['fld_module'] = $fldModule [0];
				}
				// Si fieldoperation es un JSON array, extraer el primer campo
				$fieldOp = json_decode (str_replace ('&quot;', '"', $graphData ['fieldoperation']));
				if (is_array ($fieldOp) && count ($fieldOp) > 0) {
					// Extraer solo el nombre del campo (sin tabla)
					$fieldParts = explode ('.', $fieldOp [0]);
					$graphData ['fieldoperation'] = end ($fieldParts);
				}
				// Si fieldgrouping tiene formato tabla.campo, extraer solo el campo
				if (!empty ($graphData ['fieldgrouping']) && strpos ($graphData ['fieldgrouping'], '.') !== false) {
					$groupParts = explode ('.', $graphData ['fieldgrouping']);
					$graphData ['fieldgrouping'] = end ($groupParts);
				}
				return self::getRegularFunnelData ($adb, $graphData, $dateFilter);
			}
			
			if (StackedGraphFix::shouldUseFix($graphData)) {
				$graphData['google'] = true;
				return StackedGraphFix::getStackedGraphData($adb, $graphData, $dateFilter);
            }

			$modules = json_decode (str_replace ('&quot;', '"',$graphData ['fld_module']));
			$graphData ['google'] = true;
			if (!is_array ($modules)) {
				$graphData ['google'] = false;
				return self::getRegularGraphData ($adb, $graphData, $dateFilter);
			}
			$dayFrom       = $dateFilter['dateFrom'];
			$dayUntil      = $dateFilter['dateTo'];
			$fields        = json_decode (str_replace ('&quot;', '"',$graphData ['fieldoperation']));
			$fieldLabel    = array ();
			$from []       = 'vtiger_crmentity crm';
			$selectClauses = array ();
			$tableName     = array ();
			$whereDate     = 'crm.createdtime';
			$where         = 'AND crm.deleted=0';
			if (!empty($graphData ['dategrouping'])) {
				self::getFromClause ($adb, $modules, $from, $tableName);
				self::getDateGrouping ($graphData, $selectClauses, $fieldLabel);
				$groupByClause = 'dategrouping';
			} else if (!empty($graphData ['fieldgrouping'])) {
				$dummyGrouping = explode ('.', $graphData ['fieldgrouping']);
				$uitype    = self::getUiType ($adb, $dummyGrouping [0], $dummyGrouping [1]);
				$whereDate = ($uitype == FieldInterface::UI_TYPE_DATE || $uitype == FieldInterface::UI_TYPE_DATETIME) ? $graphData ['fieldgrouping'] : $whereDate;
				if (count ($dummyGrouping) == 2) {
					$mainModuleData = self::searchRelationField($adb, $modules[0], $dummyGrouping [1]);
					if (!empty ($mainModuleData)) {
						$selectClauses [] = "{$graphData ['fieldgrouping']} AS groupingdata";
						$selectClauses [] = "(SELECT {$mainModuleData['fieldname']} FROM {$mainModuleData ['tablename']} WHERE {$mainModuleData['tablename']}.{$mainModuleData['entityidfield']} = groupingdata )AS stringdata";
						$groupByClause    = 'stringdata';
						$fieldLabel []    = $mainModuleData['fieldlabel'];
					} else {
						$selectClauses [] = $graphData ['fieldgrouping'] . ' AS stringdata';
						$groupByClause    = 'stringdata';
					}
					self::getFromClause ($adb, $modules, $from, $tableName);
				} else {
					$mainModuleData   = self::getEntityData($adb, $dummyGrouping [0]);
					$depModuleData    = self::getEntityData($adb, $dummyGrouping [1]);
					$relationModuleData = self::searchRelationField($adb, $dummyGrouping [1], $dummyGrouping [2]);

					$tableName []     = $mainModuleData ['tablename'];
					$from []          = "INNER JOIN {$mainModuleData['tablename']} ON {$mainModuleData['tablename']}.{$mainModuleData['entityidfield']} = crm.crmid";
					$from []          = "LEFT JOIN {$depModuleData['tablename']} ON {$depModuleData['tablename']}.{$dummyGrouping[2]} ={$mainModuleData['tablename']}.{$mainModuleData['entityidfield']}";
					$tableName []     = $depModuleData['tablename'];

					if (!empty($relationModuleData)) {
						$selectClauses [] = "{$depModuleData['tablename']}.{$dummyGrouping[2]} AS groupingdata";
						$selectClauses [] = "(SELECT {$relationModuleData['fieldname']} FROM {$relationModuleData ['tablename']} WHERE {$relationModuleData['tablename']}.{$mainModuleData['entityidfield']} = groupingdata )AS stringdata";
					} else {
						$selectClauses [] = "{$depModuleData['tablename']}.{$dummyGrouping[2]} AS stringdata";
					}
					$groupByClause = 'stringdata';
					$modulesDiff      = array_values (array_diff ($modules, array ($dummyGrouping [0])));
					self::getFromClause ($adb, $modulesDiff, $from, $tableName);
				}
			} else {
				return null;
			}

			$operation      = json_decode ($graphData ['operation']);
			$totalOperation = count($fields);
			for ($k = 0; $k < $totalOperation; $k++) {
				$dummy = explode ('.', $fields[ $k ]);
				$label = self::getFieldLabel ($adb, $dummy [1]);
				if ((($dummy [0] != 'vtiger_subfields_values')) && (in_array ($dummy [0], $tableName))) {
					$selectClauses [] = self::getSelectClausesChart ($fields[ $k ], $operation[ $k ], $dummy [1]);
					$fieldLabel []    = $label;
					continue;
				} else if ($dummy [0] == 'vtiger_subfields_values') {
					$parameters = array (
						'dummy'     => $dummy,
						'modules'   => $modules,
						'operation' => $operation,
						'indexFor'  => $k,
					);
					self::setGridSqlRelation ($adb, $parameters, $fieldLabel, $from, $tableName, $selectClauses);
					unset($parameters);
					continue;
				}
			}

			if (!empty ($graphData ['fieldcompare'])) {
				$selectClauses [] = self::getCalculationSelect ($graphData, $fields, $operation, $fieldLabel);
			}

			if (!empty ($graphData ['varreporte'])) {
				self::setGridTableFromFilter($adb, $graphData, $tableName, $from);
			}

			$selectClauses = join (', ', $selectClauses);
			$fromClauses   = join (' ', $from);
			$orderClauses  = '';
			$pos          = strpos ($selectClauses, 'yeardata');
			if ($pos !== false ) {
				$orderClauses = 'ORDER BY yeardata ASC, dategrouping ASC';
			}

			if (count (self::$summaryTable)) {
				$tableNameA = " "; 
				foreach (self::$summaryTable as $moduleName => $tableName) {
					$dummy = explode ('_', $tableName, 4);
					if ($tableName === $tableNameA) continue;
					$tableNameA = $tableName;
					self::$summaryColumns = GridFieldUtils::createTempGridValues ($adb, $moduleName, $dummy [3], $tableName, true);
				}
			}

			if (!empty($dayFrom) && !empty($dayUntil)) {
				$where .= " AND DATE({$whereDate}) BETWEEN STR_TO_DATE('{$dayFrom}','%Y-%m-%d') AND STR_TO_DATE('{$dayUntil}','%Y-%m-%d')";
			}

			if (!empty($graphData['sqlprimarioreporte'])) {
				$where .= ' AND ( ' . json_decode (str_replace ('&quot;', '"', $graphData['sqlprimarioreporte'])) . ' )';
			}

			$adb->query("SET lc_time_names = 'es_ES'");

			$result = $adb->query (
				"SELECT
					{$selectClauses}
				FROM
					{$fromClauses}
				WHERE
					1
					{$where}
				GROUP BY
				{$groupByClause}
				{$orderClauses}"
			);
			if ($adb->num_rows ($result) == 0) {
				return array ();
			}
			$data      = array ();
			$firstLine = true;
			$isPieChart = (isset($graphData['tipografico']) && in_array($graphData['tipografico'], array('donut', 'pie')));

			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				if (in_array('yeardata', array_keys($row))) {
					unset ($row ['yeardata']);
				}
				if (in_array('dategrouping', array_keys($row))) {
					unset ($row ['dategrouping']);
				} else if (in_array('groupingdata', array_keys($row))) {
					unset ($row ['groupingdata']);
				}
				
				// Para gráficos circulares y donut, filtrar registros con valores negativos
			if ($isPieChart) {
				$hasNegativeValue = false;
				foreach ($row as $key => $value) {
					if ($key !== 'stringdata' && is_numeric($value) && $value < 0) {
						$hasNegativeValue = true;
						break;
					}
				}
				// Saltar este registro si tiene valores negativos
				if ($hasNegativeValue) {
					continue;
				}
			}
				
				if ($firstLine) {
					$dataFirstLine = str_replace (array_keys ($row), $fieldLabel, array_keys ($row));
					// Verificar todas las columnas para asegurar que tengan etiqueta
					foreach ($dataFirstLine as $index => $label) {
						if (empty ($label)) {
							if ($index == 0) {
								// Primera columna sin etiqueta, usar nombre de agrupación
								$dataFirstLine[$index] = 'Categoría';
							} else {
								// Otras columnas sin etiqueta, usar nombre de operación
								$dataFirstLine[$index] = 'N°';
								if (!empty($operation[$index - 1])) {
									$definedOperation = self::getDefinedOperations();
									$dataFirstLine[$index] = $definedOperation[$operation[$index - 1]];
								}
							}
						}
					}
					$data []   = $dataFirstLine;
					$firstLine = false;
				}
				$values  = array_values ($row);
				$data [] = array_map (
					function ($value, $key) {
						return (empty ($value) && $key > 0) ? 0.0 : $value;
						},
					$values, array_keys ($values)
				);
			}

			return $data;
		}
		// @codingStandardsIgnoreEnd

		/**
		 * @param PearDatabase $adb
		 * @param string $tableName
		 * @param string $fieldOperation
		 *
		 * @return integer|null
		 * @throws Exception
		 */
		public static function getUiType (PearDatabase $adb, $tableName, $fieldOperation) {
			$result = $adb->pquery ('SELECT uitype FROM vtiger_field WHERE tablename IN (?, ?) AND fieldname=?', array ($tableName, 'vtiger_crmentity', $fieldOperation));
			if ($adb->num_rows ($result) > 0) {
				$row    = $adb->fetchByAssoc ($result, -1, false);
				$uiType = $row ['uitype'];
			} else {
				$uiType = null;
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
			return $uiType;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $tableName
		 * @param string $fieldOperation
		 * @param integer $crmId
		 *
		 * @return string|null
		 * @throws Exception
		 */
		public static function getValueFmr (PearDatabase $adb, $tableName, $fieldOperation, $crmId) {
			$result = $adb->pquery (
				'SELECT relmodule FROM vtiger_fieldmodulerel fmr INNER JOIN vtiger_field f ON f.fieldid=fmr.fieldid WHERE f.tablename=? AND f.fieldname=?',
				array ($tableName, $fieldOperation)
			);
			if ($adb->num_rows ($result) == 0) {
				return null;
			}

			$valueFmr = null;
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$tablaFmr       = self::getEntityTableName ($adb, $row ['relmodule']);
				$descriptionFmr = self::getDescriptionEntity ($adb, $row ['relmodule']);
				$fieldIdFmr     = self::getEntityIdField ($adb, $row ['relmodule']);

				$fmrResult = $adb->query ("SELECT {$descriptionFmr} FROM {$tablaFmr} WHERE {$fieldIdFmr}={$crmId}");
				if ($adb->num_rows ($fmrResult) > 0) {
					$data     = $adb->fetchByAssoc ($fmrResult, -1, false);
					$valueFmr = $data [ $descriptionFmr ];
					break;
				}
			}
			return $valueFmr;
		}

		/**
		 * @param PearDatabase $adb
		 * @param array $arguments
		 */
		public static function saveBoxScoreGraph (PearDatabase $adb, $arguments) {
			$arguments ['roles_grafico'] = implode ('#', $arguments ['roles_grafico']);
			$adb->pquery (
				'INSERT INTO vtiger_graficos (fld_module, fieldoperation, operation, tipografico, title, roles_grafico, sqlprimarioreporte, varreporte, reporteavanzado, comparar) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
				array ($arguments)
			);
		}

		/**
		 * @param array $fields
		 * @param array $fieldData
		 */
		public static function getFieldDataType (&$fields, $fieldData) {
			$totalFields = count ($fields);
			foreach ($fieldData as $field) {
				for ($k = 0; $k < $totalFields; $k++) {
					if ($fields[ $k ] == $field['fieldname']) {
						$fields[ $k ] = $field['typeofdata'] . '@' . $fields[ $k ];
					}
				}
			}
		}

		/**
		 * @param array $options
		 *
		 * @return array|null
		 */
		public static function getOptionChart ($options, $graphicType) {
			if (!count ($options)) {
				return null;
			}

			$results = array (
				'width'       => 355,
				'height'      => 380,
				'forceIFrame' => true,
			);
			foreach ($options as $key => $value) {
				if ((count ($value) > 1) || (array_keys ($value) !== range (0, (count ($value) - 1)))) {
					$results [ $key ] = self::combineArray ($value);
				} else {
					$results = array_merge ($results, array_combine (array ($key), array_values ($value)));
				}
			  }
			if (isset($options['width']) && is_array($options['width']) && count($options['width']) === 1) {
				$results['width'] = reset($options['width']);
			}
			if (isset($options['height']) && is_array($options['height']) && count($options['height']) === 1) {
				$results['height'] = reset($options['height']);
			}
			if (($graphicType == 'combo') && ($results['series'] !== '')) {
				$results['series'] = array ($results['series'] => array ('type' => 'line'));
			} else if ($graphicType == 'table') {
				unset($results['height']);
				$results['width']         = '100%';
				$results['cssClassNames'] = array (
					'headerRow'   => 'platzilla-headerRow',
					'tableRow'    => 'platzilla-tableRow',
					'oddTableRow' => 'platzilla-oddtableRow',
					'tableCell'   => 'platzilla-tableCell',
				);
			}
			return $results;
		}

		/**
		 * Genera el sql aplicar para obtener la data del gráfico
		 *
		 * @param PearDatabase $adb
		 * @param array $filterData
		 *
		 * @return string
		 * @throws Exception
		 */
		public static function getSqlFilterGraph (PearDatabase $adb, $filterData) {
			$fields      = $filterData ['filterField'];
			$operators   = $filterData ['filterOperator'];
			$values      = $filterData ['filterValue'];
			$joins       = $filterData ['filterJoin'];
			$groupJoins  = $filterData ['filterGroupJoin'];
			$grupoIndex  = $filterData ['indexGrupo'];

			self::prepareFields ($adb, $fields);

			$totalOperations = count ($fields);
			$totalGroup      = count ($groupJoins);
			$myGroup         = $grupoIndex[0];
			$nextOper        = 0;
			$equation        = '( ';
			$indexGroup      = 0;
			$indexJoin       = 0;

			if ($totalOperations > 0) {
				for ($op = 0; $op < $totalOperations; $op++) {
					$equation .= self::getEquation ($fields[ $op ], $values[ $op ], $operators[ $op ]);

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
		 * Obtiene los campos disponibles para gráficos de un módulo
		 * 
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @return array
		 */
		public static function getGraphicalColumnsData (PearDatabase $adb, $moduleName) {
			$result = $adb->pquery (
				'SELECT
					f.columnname,
					f.fieldname,
					f.fieldlabel,
					f.tablename,
					f.uitype,
					f.typeofdata
				FROM
					vtiger_field f
					INNER JOIN vtiger_blocks b ON f.block=b.blockid AND b.visible=0 AND b.display_status=1
					INNER JOIN vtiger_tab t ON t.tabid=f.tabid
				WHERE
					f.presence IN (0, 2) AND
					f.uitype NOT IN (4, 11, 13, 17, 19, 52, 69, 70, 257) AND
					t.name=?',
				array ($moduleName)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$columns = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$fieldtype  = explode ('~', $row ['typeofdata']);
				if ($row ['uitype'] != FieldInterface::UI_TYPE_GRID) {
					$columns [] = array(
						'fieldname'  => $row ['fieldname'],
						'label'      => html_entity_decode(getTranslatedString($row ['fieldlabel'], $moduleName), ENT_QUOTES, 'UTF-8'),
						'tablename'  => $row ['tablename'],
						'uitype'     => $row ['uitype'],
						'typeofdata' => $fieldtype[0],
					);
				} else {
					self::getGridFields ($adb, $moduleName, $row, $columns, 'numeric');
				}
			}

			usort (
				$columns,
				function ($columnA, $columnB) {
					return strcmp ($columnA ['label'], $columnB ['label']);
				}
			);
			return $columns;
		}

		/**
		 * Obtiene los campos numéricos de un módulo para operaciones
		 * 
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @return array
		 */
		public static function getNumericColumns (PearDatabase $adb, $moduleName) {
			$result = $adb->pquery (
				'SELECT
					f.fieldname,
					f.fieldlabel,
					f.tablename,
					f.uitype
				FROM
					vtiger_field f
					INNER JOIN vtiger_blocks b ON f.block=b.blockid AND b.visible=0 AND b.display_status=1
					INNER JOIN vtiger_tab t ON t.tabid=f.tabid
				WHERE
					f.presence IN (0, 2) AND
					f.uitype IN (7, 51, 71, 72) AND
					t.name=?',
				array ($moduleName)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$columns = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$columns [] = array (
					'fieldname' => $row ['fieldname'],
					'label'     => html_entity_decode (getTranslatedString ($row ['fieldlabel'], $moduleName), ENT_QUOTES, 'UTF-8'),
					'tablename' => $row ['tablename'],
					'uitype'    => $row ['uitype'],
				);
			}

			usort (
				$columns,
				function ($columnA, $columnB) {
					return strcmp ($columnA ['label'], $columnB ['label']);
				}
			);
			return $columns;
		}

		/**
		 * Obtiene la lista de módulos disponibles para gráficos
		 * 
		 * @param PearDatabase $adb
		 * @return array
		 */
		public static function getModules (PearDatabase $adb) {
			$result = $adb->query (
				'SELECT
					tabid,
					name,
					tablabel
				FROM
					vtiger_tab
				WHERE
					presence IN (0, 2) AND
					isentitytype=1 AND
					customized IN (0, 1) AND
					name NOT IN ("todotasks", "Documents", "procesos")
				ORDER BY
					tablabel'
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return array ();
			}
			$modules = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$modules [] = $row;
			}
			return $modules;
		}

		/**
		 * Obtiene los operadores matemáticos disponibles para cálculos en campos grid
		 * 
		 * @return array
		 */
		public static function getOperatorsCalculations () {
			return array(
				'+' => 'Suma (+)',
				'-' => 'Resta (-)',
				'*' => 'Multiplicación (*)',
				'/' => 'División (/)'
			);
		}

		/**
		 * Obtiene la relación entre dos módulos
		 * 
		 * @param PearDatabase $adb
		 * @param array $moduleName Array con dos nombres de módulos
		 * @return array|null
		 */
		public static function getModulesRel (PearDatabase $adb, $moduleName) {
			if (empty($moduleName)) {
				return null;
			}
			$result = $adb->pquery (
				'SELECT 
					fm.`relmodule` as main,
					fm.`module` as dep,
					t.tablabel as label,
					f.fieldname as field,
					f.fieldlabel as f_label,
					f.tablename as deptable
				FROM 
					`vtiger_fieldmodulerel` fm
					INNER JOIN `vtiger_tab` t ON t.name = fm.relmodule
					INNER JOIN `vtiger_field` f ON f.fieldid = fm.fieldid
				WHERE 
				`module`=? AND `relmodule`=?
				UNION
				SELECT 
					fm.`relmodule` as main,
					fm.`module` as dep,
					t.tablabel as label,
					f.fieldname as field,
					f.fieldlabel as f_label,
					f.tablename as deptable
				FROM 
					`vtiger_fieldmodulerel` fm
				INNER JOIN `vtiger_tab` t ON t.name = fm.relmodule
				INNER JOIN `vtiger_field` f ON f.fieldid = fm.fieldid
				WHERE 
					`module`=? AND `relmodule`=?',
				array ($moduleName[ 0 ], $moduleName[ 1 ],$moduleName[ 1 ], $moduleName[ 0 ])
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			$modules = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$row ['dep_label'] = getTabIdLabelByName ($row ['dep']);
				$modules []        = $row;
			}
			return $modules;
		}

	}
