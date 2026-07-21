<?php
	require_once ('include/platzilla/Objects/Chart.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('modules/graficosgenerales/lib/GraphUtils.class.php');

	class ChartManager {
		/** @var ChartManager[]|null */
		private static $INSTANCES = null;

		/** @var PearDatabase */
		private $adb;

		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
		}

		/**
		 * @param Chart $chart
		 *
		 * @throws ChartException
		 * @throws Exception
		 */
		private function doAdvancedValidation (&$chart) {
			$fields      = $chart->getFieldName();
			$totalFields = count ($fields);
			for ($k = 0; $k < $totalFields; $k++) {
				list ($tableName, $fieldName) = explode('.', $fields[ $k ]);
				if ($tableName == 'vtiger_subfields_values') {
					$fieldGrid = $this->validateGridField ($fieldName, $chart);
					$fields [$k] = 'vtiger_subfields_values.' .$fieldGrid;
					continue;
				}
				$result = $this->adb->pquery ('SELECT f.* FROM vtiger_field f  WHERE f.fieldname=? AND f.tablename=?', array ($fieldName, $tableName));
				if ($this->adb->num_rows ($result) == 0) {
					DatabaseUtils::closeResult ($result);
					$result = null;
					throw new ChartException (ChartException::ERROR_CHART_INVALID_FIELD_NAME);
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
			}
			$chart->setFieldName ($fields);

			$groupBy = $chart->getGroupBy ();
			if (!empty ($groupBy)) {
				$dummy = explode('.', $groupBy);
				if (($dummy[ 0 ] != 'vtiger_subfields_values') && count($dummy) <= 1) {
					$result = $this->adb->pquery ('SELECT f.* FROM vtiger_field f  WHERE f.fieldname=? AND f.tablename=?', array ($dummy[ 1 ], $dummy[ 0 ]));
					if ($this->adb->num_rows($result) == 0) {
						DatabaseUtils::closeResult($result);
						$result = null;
						throw new ChartException (ChartException::ERROR_CHART_INVALID_GROUP_BY);
					}
					DatabaseUtils::closeResult($result);
					$result = null;
				}
			}
		}

		/**
		 * @param string $chartName
		 *
		 * @return array|null
		 */
		private function fetchChartData ($chartName) {
			if (!empty ($chartName)) {
				$result = $this->adb->pquery ('SELECT * FROM vtiger_graficos WHERE name=?', array ($chartName));
				if ($this->adb->num_rows ($result) > 0) {
					$row = $this->adb->fetchByAssoc ($result, -1, false);
				} else {
					$row = null;
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
			} else {
				$row = null;
			}
			return $row;
		}

		/**
		 * @param string $moduleName
		 *
		 * @return Chart[]
		 */
		private function fetchDeletedCharts ($moduleName) {
			if (empty ($moduleName)) {
				return array ();
			}

			$charts = array ();
			$result = $this->adb->pquery ('SELECT * FROM vtiger_deletedelements WHERE elementtype=? AND modulename=?', array ('chart', $moduleName));
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					/** @var Chart $chart */
					$chart = unserialize ($row ['serializedobject']);
					$chart->setDeleted (true);
					$charts [] = $chart;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $charts;
		}

		/**
		 * @param Chart $chart
		 *
		 * @throws ChartException
		 * @throws Exception
		 */
		private function validate (&$chart) {
			if ((empty ($chart)) || (!($chart instanceof Chart))) {
				throw new ChartException (ChartException::ERROR_CHART_EMPTY);
			}

			$chart->validate ();

			$moduleNames = $chart->getModuleName ();
			if (empty ($moduleNames)) {
				throw new ChartException (ChartException::ERROR_CHART_EMPTY_MODULE_NAME);
			}
			foreach ($moduleNames as $moduleName) {
				$result = $this->adb->pquery ('SELECT * FROM vtiger_tab WHERE name=?', array ($moduleName));
				if ($this->adb->num_rows ($result) == 0) {
					DatabaseUtils::closeResult ($result);
					$result = null;
					throw new ChartException (ChartException::ERROR_CHART_INVALID_MODULE_NAME);
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
			}
			$this->doAdvancedValidation ($chart);
		}

		/**
		 * @param string $gridField
		 * @param Chart $chart
		 *
		 * @return string|null
		 * @throws ChartException
		 * @throws Exception
		 */
		private function validateGridField ($gridField, &$chart) {
			$dummy  = explode('@', $gridField);
			$result = $this->adb->pquery ('SELECT f.* FROM vtiger_field f  WHERE f.fieldname=?', array ($dummy[ 0 ]));
			if ($this->adb->num_rows ($result) == 0) {
				DatabaseUtils::closeResult ($result);
				$result = null;
				throw new ChartException (ChartException::ERROR_CHART_INVALID_FIELD_NAME);
			} else {
				$row = $this->adb->fetchByAssoc ($result, -1, false);
				$dummyFidldName = explode('_', $dummy[ 1 ]);
				$fieldId        = array_pop ($dummyFidldName);
				$gridField      = str_replace ($fieldId, $row ['fieldid'], $gridField);
				$calculation    = str_replace ($fieldId, $row ['fieldid'], $chart->getFieldCompare ());
				$chart->setFieldCompare ($calculation);
				if (!empty($chart->getVariables())) {
					$filters                = json_decode ($chart->getVariables(), true);
					$filters['filterField'] = str_replace ($fieldId, $row ['fieldid'], $filters['filterField']);

					$sqlFilter = GraphUtils::getSqlFilterGraph ($this->adb, $filters);
					$chart->setSqlQuery (json_encode ($sqlFilter, (JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT)));
					$chart->setVariables (json_encode ($filters));
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $gridField;
		}

		/**
		 * @param Chart $chart
		 */
		public function deleteChart ($chart) {
			if ((empty ($chart)) || (!($chart instanceof Chart))) {
				return;
			}

			$chartId = $chart->getId ();
			if (empty ($chartId)) {
				return;
			}

			$moduleName = $chart->getModuleName ();
			$identifier = $chartId;
			if ((!empty ($moduleName)) && (!empty ($identifier))) {
				$this->adb->pquery ('DELETE FROM vtiger_deletedelements WHERE elementtype=? AND modulename=? AND identifier=?', array ('chart', $moduleName, $identifier));
				$this->adb->pquery ('INSERT INTO vtiger_deletedelements (elementtype, modulename, identifier, deletedon, serializedobject) VALUES (?, ?, ?, ?, ?)', array ('chart', $moduleName, $identifier, date ('Y-m-d h:i:s'), serialize ($chart)));
			}
			$this->adb->pquery ('DELETE FROM vtiger_graficos WHERE graficoid=?', array ($chart->getId ()));
		}

		/**
		 * @param string $applicationCode
		 */
		public function deleteChartApplicationCode ($applicationCode) {
			$result = $this->adb->pquery ('SELECT * FROM vtiger_graficos WHERE applicationcodes LIKE ?', array ('%' . json_encode ("'{$applicationCode}'") . '%'));
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$chart               = $this->fetchChart (intval ($row ['graficoid']));
					$oldApplicationCodes = $chart->getApplicationCodes ();
					$newApplicationCodes = array ();
					foreach ($oldApplicationCodes as $oldApplicationCode) {
						if ($oldApplicationCode != $applicationCode) {
							$newApplicationCodes [] = $oldApplicationCode;
						}
					}
					$chart->setApplicationCodes ($newApplicationCodes);
					$this->saveChart ($chart);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
		}

		/**
		 * @param string $moduleName
		 * @param boolean $ignoreLock
		 */
		public function deleteCharts ($moduleName, $ignoreLock = true) {
			if (empty ($moduleName)) {
				return;
			}
			$stringModules = json_encode($moduleName);
			if (!$ignoreLock) {
				$whereClause = 'AND locked=0';
			} else {
				$whereClause = '';
			}
			$this->adb->pquery ("DELETE FROM vtiger_graficos WHERE fld_module=? {$whereClause}", array ($stringModules));
		}

		/**
		 * @param Field $field
		 */
		public function deleteFieldFromCharts ($field) {
			$moduleName = $field->getModuleName ();
			$fieldName  = $field->getName ();
			if ((empty ($moduleName)) || (empty ($fieldName))) {
				return;
			}

			$this->adb->pquery (
				'DELETE FROM vtiger_graficos WHERE fld_module=? AND (fieldoperation=? OR fieldgrouping=?)',
				array ($moduleName, $fieldName, $fieldName)
			);
		}

		/**
		 * @param integer $chartId
		 *
		 * @return Chart|null
		 */
		public function fetchChart ($chartId) {
			if (empty ($chartId)) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_graficos WHERE graficoid=?', array ($chartId));
			if ($this->adb->num_rows ($result) > 0) {
				$row   = $this->adb->fetchByAssoc ($result, -1, false);
				$chart = Chart::getInstance ()
					->setAdvanced (intval ($row ['reporteavanzado']))
					->setApplicationCodes (json_decode ($row ['applicationcodes'], true))
					->setCompare ($row ['comparar'] == 1 ? true : false)
					->setChartOptions (json_decode ($row ['graphicoptions'], true))
					->setDateGrouping (intval ($row ['dategrouping']))
					->setFieldGrid ($row ['gridoperation'])
					->setFieldName (json_decode (str_replace ('&quot;', '"', $row ['fieldoperation'])))
					->setGroupBy ($row ['fieldgrouping'])
					->setId (intval ($row ['graficoid']))
					->setLocked ($row ['locked'] == 1)
					->setModuleName (json_decode (str_replace ('&quot;', '"',$row ['fld_module'])))
					->setName ($row ['name'])
					->setOperation (json_decode ($row ['operation']))
					->setRoleIds (explode ('#', $row ['roles_grafico']))
					->setSqlQuery ($row ['sqlprimarioreporte'])
					->setTitle ($row ['title'])
					->setType ($row ['tipografico'])
					->setVariables ($row ['varreporte'])
					->setFieldCompare ($row ['fieldcompare'])
					->setCompareOperation ($row ['compareoperation']);
			} else {
				$chart = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $chart;
		}

		/**
		 * @param string $moduleName
		 * @param boolean $includeDeleted
		 *
		 * @return Chart[]|null
		 */
		public function fetchCharts ($moduleName, $includeDeleted = false) {
			if ($moduleName != 'graficosgenerales') {
				return null;
			}
			if (empty ($moduleName)) {
				return null;
			}

			$result = $this->adb->query ('SELECT * FROM vtiger_graficos WHERE fld_module IS NOT  NULL AND fieldoperation IS NOT NULL ');
			if ($this->adb->num_rows ($result) > 0) {
				$charts = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$charts [] = Chart::getInstance ()
						->setAdvanced (intval ($row ['reporteavanzado']))
						->setApplicationCodes (json_decode ($row ['applicationcodes'], true))
						->setCompare ($row ['comparar'] == 1 ? true : false)
						->setChartOptions (json_decode ($row ['graphicoptions'], true))
						->setDateGrouping (intval ($row ['dategrouping']))
						->setFieldGrid ($row ['gridoperation'])
						->setFieldName (json_decode (str_replace ('&quot;', '"', $row ['fieldoperation'])))
						->setGroupBy ($row ['fieldgrouping'])
						->setId (intval ($row ['graficoid']))
						->setLocked ($row ['locked'] == 1)
						->setModuleName (json_decode (str_replace ('&quot;', '"',$row ['fld_module'])))
						->setName ($row ['name'])
						->setOperation (json_decode ($row ['operation']))
						->setRoleIds (explode ('#', $row ['roles_grafico']))
						->setSqlQuery ($row ['sqlprimarioreporte'])
						->setTitle ($row ['title'])
						->setType ($row ['tipografico'])
						->setVariables ($row ['varreporte'])
						->setFieldCompare ($row ['fieldcompare'])
						->setCompareOperation ($row ['compareoperation']);
				}

				if ($includeDeleted) {
					$deletedCharts = $this->fetchDeletedCharts ($moduleName);
				} else {
					$deletedCharts = array ();
				}

				$charts = array_merge ($charts, $deletedCharts);
			} else {
				$charts = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $charts;
		}

		/**
		 * @param Chart $chart
		 * @param boolean $ignoreLock
		 *
		 * @return Chart
		 * @throws ChartException
		 */
		public function saveChart ($chart, $ignoreLock = true) {
			$this->validate ($chart);

			$isDeleted = $chart->isDeleted ();
			if ($isDeleted) {
				return $chart;
			}

			$chartName = $chart->getName ();
			$data    = $this->fetchChartData ($chartName);
			if (!empty ($data)) {
				$isLocked = ($data ['locked'] == 1);
			} else {
				$chartName  = null;
				$isLocked = false;
			}

			$applicationCodes = $chart->getApplicationCodes ();
			$applicationCodes = !empty ($applicationCodes) ? json_encode ($applicationCodes) : null;
			$chartOptions     = $chart->getChartOptions ();
			$chartOptions     = !empty ($chartOptions) ? json_encode ($chartOptions, JSON_FORCE_OBJECT) : null;
			$moduleNames      = $chart->getModuleName ();
			$moduleNames      = !empty ($moduleNames) ? json_encode ($moduleNames) : null;
			$fieldNames       = $chart->getFieldName ();
			$fieldNames       = !empty ($fieldNames) ? json_encode ($fieldNames) : null;
			$fieldOperation   = $chart->getOperation ();
			$fieldOperation   = !empty ($fieldOperation) ? json_encode ($fieldOperation) : null;
			$roleIds          = $chart->getRoleIds ();
			$roleIds          = !empty ($roleIds) ? join ('#', $roleIds) : null;
			$compare          = $chart->getCompare () ? 1 : 0;
			if (empty ($chartName)) {
				$this->adb->pquery (
					'INSERT INTO vtiger_graficos (name, fld_module, fieldoperation, fieldcompare, gridoperation, operation, tipografico, title, roles_grafico, sqlprimarioreporte, varreporte, reporteavanzado, comparar, ishome, fieldgrouping, compareoperation, dategrouping, applicationcodes, graphicoptions, locked) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
					array ($chart->getName (), $moduleNames, $fieldNames, $chart->getFieldCompare (), $chart->getFieldGrid() ,$fieldOperation, $chart->getType (), $chart->getTitle (), $roleIds, $chart->getSqlQuery (), $chart->getVariables (), $chart->getAdvanced (), $compare, 0, $chart->getGroupBy (), $chart->getCompareOperation (), $chart->getDateGrouping (), $applicationCodes, $chartOptions, $chart->isLocked ())
				);
				$chart->setId ($this->adb->getLastInsertID ());
			} else if (($ignoreLock) || (!$isLocked)) {
				$this->adb->pquery (
					'UPDATE vtiger_graficos SET fld_module=?, fieldoperation=?, fieldcompare=?, gridoperation=?, operation=?, tipografico=?, title=?, roles_grafico=?, sqlprimarioreporte=?, varreporte=?, reporteavanzado=?, comparar=?, ishome=?, fieldgrouping=?, compareoperation=?,dategrouping=?, applicationcodes=?, graphicoptions=?, locked=? WHERE name=?',
					array ($moduleNames, $fieldNames, $chart->getFieldCompare (), $chart->getFieldGrid(), $fieldOperation, $chart->getType (), $chart->getTitle (), $roleIds, $chart->getSqlQuery (), $chart->getVariables (), $chart->getAdvanced (), $compare, 0, $chart->getGroupBy (), $chart->getCompareOperation (), $chart->getDateGrouping (), $applicationCodes, $chartOptions, $chart->isLocked (), $chartName)
				);
			}

			return $chart;
		}

		/**
		 * @param string $module
		 * @param boolean $ignoreLock
		 */
		public function saveCharts ($module, $ignoreLock = true) {
			if ((empty ($module)) || (!($module instanceof Module))) {
				return null;
			}
			$charts = $module->getCharts ();
			if ( empty($charts)) {
				return null;
			}

			$processedChartNames = array ();
			foreach ($charts as $chart) {
				$this->saveChart ($chart, $ignoreLock);
				$processedChartNames [] = $chart->getName ();
			}

			if (!$ignoreLock) {
				$whereClause = ' AND locked=0';
			} else {
				$whereClause = '';
			}

			$graphicNames = $this->adb->sql_expr_datalist ($processedChartNames);
			$sql           = "DELETE FROM vtiger_graficos WHERE (name IS NULL  OR  name NOT IN {$graphicNames}) {$whereClause}";
			$this->adb->query ($sql);
		}

		/**
		 * @param Chart $chart
		 *
		 * @throws ChartException
		 */
		public function validateChart ($chart) {
			$this->validate ($chart);
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return ChartManager
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
