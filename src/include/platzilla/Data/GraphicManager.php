<?php
	require_once ('include/platzilla/Data/Graphic.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('modules/graficosgenerales/lib/GraphUtils.class.php');

	class GraphicManager {

		/** @var GraphicManager[]|null */
		private static $INSTANCES = null;

		/** @var PearDatabase */
		private $adb;

		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
		}

		/**
		 * @codingStandardsIgnoreStart
		 * Esta función realiza una verificación de los datos requeridos para generar un gráfico
		 * NOTA: CodeSniffer detecta una violación de complejidad ciclomática, pero dada la complijidad del proceso
		 * se hace imposible reducir dicha complejidad.
		 * @param array $moduleName
		 * @param Graphic $chart
		 *
		 * @throws ChartException
		 */
		private function doAdvancedValidation ($moduleName, $chart) {
			foreach ($moduleName as $gModule) {
				$result = $this->adb->pquery('SELECT * FROM vtiger_tab WHERE name=?', array($gModule));
				if ($this->adb->num_rows($result) == 0) {
					DatabaseUtils::closeResult($result);
					$result = null;
					throw new ChartException (ChartException::ERROR_CHART_INVALID_MODULE_NAME);
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
			}
			$fields     = json_decode($chart->getFieldName());
			$totalField = count ($fields);
			for ($k = 0; $k < $totalField; $k++) {
				$dummyField = explode('.', $fields [$k]);
				if ($dummyField [0] == 'vtiger_subfields_values') {
					continue;
				}
				$result = $this->adb->pquery('SELECT f.* FROM vtiger_field f INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=? WHERE f.fieldname=?', array($moduleName [$k], $dummyField [1]));
				if ($this->adb->num_rows($result) == 0) {
					DatabaseUtils::closeResult($result);
					$result = null;
					throw new ChartException (ChartException::ERROR_CHART_INVALID_FIELD_NAME);
				}
				DatabaseUtils::closeResult($result);
				$result = null;
			}

			$groupBy = $chart->getGroupBy ();
			if (!empty ($groupBy)) {
				$dummyField = explode('.', $groupBy);
				if ($dummyField [0] == 'vtiger_subfields_values') {
					return;
				}
				$error = true;
				foreach ($moduleName as $gModule) {
					$result = $this->adb->pquery('SELECT f.* FROM vtiger_field f INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=? WHERE f.fieldname=?', array($gModule, $dummyField [1]));
					if ($this->adb->num_rows($result) > 0) {
						$error = false;
					}
					DatabaseUtils::closeResult($result);
					$result = null;
				}
				if($error) {
					throw new ChartException (ChartException::ERROR_CHART_INVALID_GROUP_BY);
				}
			}
		}
		// @codingStandardsIgnoreEnd

		/**
		 * @param integer $chartId
		 *
		 * @return array|null
		 */
		private function fetchChartData ($chartId) {
			if (!empty ($chartId)) {
				$result = $this->adb->pquery ('SELECT * FROM vtiger_graficos WHERE graficoid=?', array ($chartId));
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
		 * @return Graphic[]
		 */
		private function fetchDeletedCharts ($moduleName) {
			if (empty ($moduleName)) {
				return array ();
			}

			$charts = array ();
			$result = $this->adb->pquery ('SELECT * FROM vtiger_deletedelements WHERE elementtype=? AND modulename=?', array ('chart', $moduleName));
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					/** @var Graphic $chart */
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
		 * @param array $fieldData
		 *
		 * @return string|null
		 */
		private function getModuleRel ($fieldData) {
			$result = $this->adb->pquery(
				'SELECT 
				  mr.relmodule
				FROM vtiger_fieldmodulerel mr
				INNER JOIN vtiger_field f ON f.fieldid = mr.fieldid
				WHERE f.tablename=?
				AND f.fieldname=?',
				$fieldData
			);
			if ($this->adb->num_rows ($result) > 0) {
				$row = $this->adb->fetchByAssoc ($result, -1, false);
				return $row ['relmodule'];
			} else {
				return null;
			}
		}

		/**
		 * @param string $graphicType
		 *
		 * @return string
		 */
		private function setGraphicName ($graphicType) {
			$randomId = rand (1000, 9999);
			return $graphicType . $randomId;
		}

		/**
		 * @param Graphic $chart
		 *
		 * @throws ChartException
		 */
		private function validate ($chart) {
			if ((empty ($chart)) || (!($chart instanceof Graphic))) {
				throw new ChartException (ChartException::ERROR_CHART_EMPTY);
			}

			$chart->validate ();

			$moduleName = $chart->getModuleName ();
			if (empty ($moduleName)) {
				throw new ChartException (ChartException::ERROR_CHART_EMPTY_MODULE_NAME);
			} else {
				$modules = json_decode ($moduleName);
			}

			$this->doAdvancedValidation ($modules, $chart);
		}

		/**
		 * @param Graphic $chart
		 */
		public function deleteChart ($chart) {
			if ((empty ($chart)) || (!($chart instanceof Graphic))) {
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

			if (!$ignoreLock) {
				$whereClause = 'AND locked=0';
			} else {
				$whereClause = '';
			}
			$this->adb->pquery ("DELETE FROM vtiger_graficos WHERE fld_module=? {$whereClause}", array ($moduleName));
		}

		/**
		 * @param $userId
		 * @param $graphicId
		 *
		 * @return boolean
		 */
		public function delFavoriteGraphic ($userId, $graphicId) {
			if (empty ($graphicId) || empty ($userId) || !is_numeric ($graphicId) || !is_numeric ($userId)) {
				return false;
			}
			$this->adb->pquery ('DELETE FROM vtiger_user2graphics WHERE userid=? AND graficoid=?', array ($userId, $graphicId));
			return true;
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
		 * @param integer $userId
		 *
		 * @return array
		 */
		public function fetchAllFavoriteGraphics ($userId) {
			if (empty ($userId) || !is_numeric($userId)) {
				return array ();
			}
			return $this->adb->run_query_allrecords("SELECT * FROM vtiger_user2graphics WHERE userid = {$userId}");
		}

		/**
		 * @param integer $userId
		 * @param string $moduleName
		 *
		 * @return array
		 */
		public function fetchAllFavoriteByModule ($userId, $moduleName) {
			if (empty ($userId) || !is_numeric($userId) || empty ($moduleName)) {
				return array ();
			}
			$allFavorites = $this->fetchAllFavoriteGraphics($userId);
			$favorites    = array ();
			foreach ($allFavorites as $favority) {
				$chart   = $this->fetchChart ($favority ['graficoid']);
				$modules = json_decode ($chart->getModuleName ());
				if (! empty ($chart->getGroupBy ())) {
					$dummy = explode('.', $chart->getGroupBy());
					if (count($dummy) == 3) {
						if ($dummy [0] == $moduleName) {
							$favorites [] = $favority ['graficoid'];
						}
					} else {
						$moduleRel = $this->getModuleRel($dummy);
						if ($moduleRel == $moduleName) {
							$favorites [] = $favority ['graficoid'];
						}
					}
				}
				if (!in_array ($favority['graficoid'], $favorites) && in_array ($moduleName, $modules)) {
					$favorites [] = $favority ['graficoid'];
				}
			}
			return $favorites;
		}

		/**
		 * @param integer $chartId
		 *
		 * @return Graphic|null
		 */
		public function fetchChart ($chartId) {
			if (empty ($chartId)) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_graficos WHERE graficoid=?', array ($chartId));
			if ($this->adb->num_rows ($result) > 0) {
				$row   = $this->adb->fetchByAssoc ($result, -1, false);
				$chart = Graphic::getInstance ()
					->setAdvanced (intval ($row ['reporteavanzado']))
					->setApplicationCodes (json_decode ($row ['applicationcodes'], true))
					->setCompare ($row ['comparar'] == 1 ? true : false)
					->setDateGrouping (intval ($row ['dategrouping']))
					->setFieldGrid ($row ['gridoperation'])
					->setFieldName ($row ['fieldoperation'])
					->setGraphicOptions ($row ['graphicoptions'])
					->setGroupBy ($row ['fieldgrouping'])
					->setId (intval ($row ['graficoid']))
					->setLocked ($row ['locked'] == 1)
					->setModuleName ($row ['fld_module'])
					->setOperation ($row ['operation'])
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
		 * @return Graphic[]|null
		 */
		public function fetchCharts ($moduleName, $includeDeleted = false) {
			if (empty ($moduleName)) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_graficos WHERE fld_module=?', array ($moduleName));
			if ($this->adb->num_rows ($result) > 0) {
				$charts = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$charts [] = Graphic::getInstance ()
						->setAdvanced (intval ($row ['reporteavanzado']))
						->setApplicationCodes (json_decode ($row ['applicationcodes'], true))
						->setCompare ($row ['comparar'] == 1 ? true : false)
						->setDateGrouping (intval ($row ['dategrouping']))
						->setFieldGrid ($row ['gridoperation'])
						->setFieldName ($row ['fieldoperation'])
						->setGroupBy ($row ['fieldgrouping'])
						->setId (intval ($row ['graficoid']))
						->setLocked ($row ['locked'] == 1)
						->setModuleName ($row ['fld_module'])
						->setOperation ($row ['operation'])
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

		public function fetchChartOption ($graphicName, $isInstance = false) {
			if (empty ($graphicName)) {
				return null;
			}
			if ($isInstance) {
				$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
				$result    = $masterAdb->pquery ('SELECT * FROM vtiger_graphic_options WHERE graphicname=? ORDER BY objectname', array ($graphicName));
			} else {
				$result = $this->adb->pquery ('SELECT * FROM vtiger_graphic_options WHERE graphicname=? ORDER BY objectname', array ($graphicName));
			}

			if ($this->adb->num_rows ($result) > 0) {
				$chartOptions = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					if (!empty ($row ['optionvalue'])) {
						$row ['optionvalue'] = json_decode ($row ['optionvalue'], true);
						$row ['element'] = 'select';
					} else {
						$row ['element'] = 'input';
					}
					$chartOptions [] = $row;
				}
			} else {
				$chartOptions = null;
			}

			DatabaseUtils::closeResult ($result);
			$result = null;
			return $chartOptions;
		}

		/**
		 * @param integer $userId
		 *
		 * @return Graphic[]|null
		 * @throws Exception
		 */
		public function fetchFavoriteGraphics ($userId) {
			if (empty ($userId) || !is_numeric ($userId)) {
				return null;
			}

			$result = $this->adb->pquery (
				'SELECT 
						g.* 
					  FROM 
					  	vtiger_graficos g 
					  INNER JOIN vtiger_user2graphics gu ON g.graficoid = gu.graficoid 
					  WHERE 
					  	gu.userid=?',
				array ($userId)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$charts = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$charts [] = Graphic::getInstance ()
						->setAdvanced (intval ($row ['reporteavanzado']))
						->setApplicationCodes (json_decode ($row ['applicationcodes'], true))
						->setCompare ($row ['comparar'] == 1 ? true : false)
						->setDateGrouping (intval ($row ['dategrouping']))
						->setFieldGrid ($row ['gridoperation'])
						->setFieldName ($row ['fieldoperation'])
						->setGroupBy ($row ['fieldgrouping'])
						->setId (intval ($row ['graficoid']))
						->setLocked ($row ['locked'] == 1)
						->setModuleName ($row ['fld_module'])
						->setOperation ($row ['operation'])
						->setRoleIds (explode ('#', $row ['roles_grafico']))
						->setSqlQuery ($row ['sqlprimarioreporte'])
						->setTitle ($row ['title'])
						->setType ($row ['tipografico'])
						->setVariables ($row ['varreporte'])
						->setFieldCompare ($row ['fieldcompare'])
						->setCompareOperation ($row ['compareoperation']);
				}
			} else {
				$charts = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $charts;
		}

		/**
		 * @SuppressWarnings(PHPMD)
		 * @param array $graphs
		 * @param boolean $isInstance
		 * @param array $categoriesCatalog
		 * @param array $dateFilter
		 * @param array $onlyFavorites
		 *
		 * @throws Exception
		 */
		public function getBasicGraphics (&$graphs, $isInstance, $categoriesCatalog, $dateFilter, $onlyFavorites = array(), $forModule = null) {
			$applications     = array_values ($categoriesCatalog);
			$applicationCodes = array_keys ($categoriesCatalog);
			$favoriteChars    = '';
			$whereModule      = '';
			$orderBy          = '';
			if (count ($onlyFavorites)) {
				$graphIds      = $this->adb->sql_expr_datalist ($onlyFavorites);
				$favoriteChars = "g.graficoid IN {$graphIds} AND";
				$idOrder       = join (',', $onlyFavorites);
				$orderBy       = " ORDER BY FIELD (g.graficoid,{$idOrder})";
			}

			if (!empty($forModule)) {
				$whereModule = "fld_module LIKE '%\"{$forModule}%' AND";
			}

			if (!empty ($dateFilter) && key_exists('category', $dateFilter)) {
				$graphicCategory = "g.locked={$dateFilter['category']} AND";
			}
			$result = $this->adb->query (
				"SELECT
 						g.*
					  FROM 
					  	vtiger_graficos g
					  WHERE 
					  	{$favoriteChars}
					  	{$whereModule}
					  	{$graphicCategory}
					  	g.reporteavanzado=0 AND
					  	g.ishome=0
					  	{$orderBy}"
			);
			if (($result) && ($this->adb->num_rows ($result) > 0)) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$chartApplicationCodes  = json_decode ($row ['applicationcodes'], true);
					$commonApplicationCodes = !empty ($chartApplicationCodes) ? array_intersect ($applicationCodes, $chartApplicationCodes) : null;
					if (($isInstance) && ((empty ($applicationCodes)) || (empty ($commonApplicationCodes)))) {
						continue;
					}
					if (!empty ($row['graphicoptions'])){
						$dummy = json_decode ($row['graphicoptions'], true);
						if (key_exists ('chartArea', $dummy)) {
							unset ($dummy['chartArea']);
						}
						if (key_exists ('width', $dummy)) {
							$dummy['width'] = 355;
						}
						if (key_exists ('height', $dummy)) {
							$dummy['height'] = 380;
						}
						$row ['graphicoptions'] = json_encode ($dummy);
					}
					
					$row ['dataGrafico'] = GraphUtils::getGraphData ($this->adb, $row, $dateFilter);
					if ((empty ($row ['applicationcodes'])) || (empty ($applications))) {
						$row ['applicationcode'] = 'otros';
						$graphs ['others'][]     = $row;
					} else {
						foreach ($chartApplicationCodes as $applicationCode) {
							if (in_array ($applicationCode, array_keys ($applications))) {
								$row ['applicationcode']                       = $applicationCode;
								$graphs ['applications'][ $applicationCode ][] = $row;
							}
						}
					}
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
		}

		/**
		 * @param array $graphs
		 * @param boolean $isInstance
		 * @param array $applicationCatalog
		 *
		 * @throws Exception
		 */
		public function getFunnelGraphics (&$graphs, $isInstance, $applicationCatalog, $onlyFavorites = array()) {
			$applications     = $applicationCatalog ['applications'];
			$applicationCodes = $applicationCatalog ['applicationCodes'];
			$favoriteChars    = '';
			if (count ($onlyFavorites)) {
				$graphIds      = $this->adb->sql_expr_datalist ($onlyFavorites);
				$favoriteChars = "g.graficoid IN {$graphIds} AND";
			}

			$result = $this->adb->query ("SELECT g.* FROM vtiger_graficos g INNER JOIN vtiger_tab t ON t.name=g.fld_module WHERE {$favoriteChars} reporteavanzado=1 AND tipografico='funnel'");
			if (($result) && ($this->adb->num_rows ($result) > 0)) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$chartApplicationCodes = json_decode ($row ['applicationcodes'], true);
					if (($isInstance) && ((empty ($applicationCodes)) || (empty (array_intersect ($applicationCodes, $chartApplicationCodes))))) {
						continue;
					}

					$row ['dataGrafico'] = GraphUtils::getFunnelGraphData ($this->adb, $row);
					if ((empty ($row ['applicationcodes'])) || (empty ($applications))) {
						$row ['applicationcode'] = 'otros';
						$graphs ['others'][]     = $row;
						continue;
					}
					foreach ($chartApplicationCodes as $applicationCode) {
						if (in_array ($applicationCode, array_keys ($applications))) {
							$row ['applicationcode']                       = $applicationCode;
							$graphs ['applications'][ $applicationCode ][] = $row;
						} else {
							$row ['applicationcode'] = 'otros';
							$graphs ['others'] []    = $row;
						}
					}
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
		}

		/**
		 * @param Graphic $chart
		 * @param boolean $ignoreLock
		 *
		 * @return Graphic
		 * @throws ChartException
		 */
		public function saveChart ($chart, $ignoreLock = true) {
			$this->validate ($chart);

			$isDeleted = $chart->isDeleted ();
			if ($isDeleted) {
				return $chart;
			}

			$chartId = $chart->getId ();
			$data    = $this->fetchChartData ($chartId);
			if (!empty ($data)) {
				$isLocked = ($data ['locked'] == 1);
			} else {
				$chartId  = null;
				$isLocked = false;
			}

			$applicationCodes = $chart->getApplicationCodes ();
			$applicationCodes = !empty ($applicationCodes) ? json_encode ($applicationCodes) : null;
			$roleIds          = $chart->getRoleIds ();
			$roleIds          = !empty ($roleIds) ? join ('#', $roleIds) : null;
			$compare          = $chart->getCompare () ? 1 : 0;
			if (empty ($chartId)) {
				$graphicName = $this->setGraphicName ($chart->getType ());
				$this->adb->pquery (
					'INSERT INTO vtiger_graficos (name,    fld_module, fieldoperation, fieldcompare, gridoperation, operation, tipografico, title, roles_grafico, sqlprimarioreporte, varreporte, reporteavanzado, comparar, ishome, fieldgrouping, compareoperation, dategrouping, applicationcodes, graphicoptions, locked) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
					array ($graphicName, $chart->getModuleName (), $chart->getFieldName (), $chart->getFieldCompare (), $chart->getFieldGrid() ,$chart->getOperation (), $chart->getType (), $chart->getTitle (), $roleIds, $chart->getSqlQuery (), $chart->getVariables (), $chart->getAdvanced (), $compare, 0, $chart->getGroupBy (), $chart->getCompareOperation (), $chart->getDateGrouping (), $applicationCodes, $chart->getGraphicOptions (),$chart->isLocked ())
				);
				$chart->setId ($this->adb->getLastInsertID ());
			} else if (($ignoreLock) || (!$isLocked)) {
				$this->adb->pquery (
					'UPDATE vtiger_graficos SET fld_module=?, fieldoperation=?, fieldcompare=?, gridoperation=?, operation=?, tipografico=?, title=?, roles_grafico=?, sqlprimarioreporte=?, varreporte=?, reporteavanzado=?, comparar=?, ishome=?, fieldgrouping=?, compareoperation=?,dategrouping=?, applicationcodes=?, graphicoptions=?, locked=? WHERE graficoid=?',
					array ($chart->getModuleName (), $chart->getFieldName (), $chart->getFieldCompare (), $chart->getFieldGrid(), $chart->getOperation (), $chart->getType (), $chart->getTitle (), $roleIds, $chart->getSqlQuery (), $chart->getVariables (), $chart->getAdvanced (), $compare, 0, $chart->getGroupBy (), $chart->getCompareOperation (), $chart->getDateGrouping (), $applicationCodes, $chart->getGraphicOptions (),$chart->isLocked (), $chartId)
				);
			}

			return $chart;
		}

		/**
		 * @param string $moduleName
		 * @param Graphic[]|null $charts
		 * @param boolean $ignoreLock
		 */
		public function saveCharts ($moduleName, $charts, $ignoreLock = true) {
			if (empty ($moduleName)) {
				return;
			} else if (empty ($charts)) {
				$this->deleteCharts ($moduleName, $ignoreLock);
				return;
			}

			$processedChartIds = array ();
			foreach ($charts as $chart) {
				$chart->setModuleName ($moduleName);
				$this->saveChart ($chart, $ignoreLock);
				$processedChartIds [] = $chart->getId ();
			}

			if (!$ignoreLock) {
				$whereClause = 'AND locked=0';
			} else {
				$whereClause = '';
			}
			$questionMarks = str_repeat ('?, ', (count ($processedChartIds) - 1)) . '?';
			$this->adb->pquery ("DELETE FROM vtiger_graficos WHERE fld_module=? AND graficoid NOT IN ({$questionMarks}) {$whereClause}", array_merge (array ($moduleName), $processedChartIds));
		}

		/**
		 * @param integer $userId
		 * @param integer $graphicId
		 *
		 * @return boolean
		 */
		public function saveFavoriteGraphic ($userId, $graphicId) {
			if (empty ($graphicId) || empty ($userId) || !is_numeric ($graphicId) || !is_numeric ($userId)) {
				return false;
			}

			try {
				$this->adb->run_insert_data ('vtiger_user2graphics', array ('userid' => $userId, 'graficoid' => $graphicId));
				return true;
			} catch (Exception $e) {
				return false;
			}
		}

		/**
		 * @param Graphic $chart
		 *
		 * @throws ChartException
		 */
		public function validateChart ($chart) {
			$this->validate ($chart);
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return GraphicManager
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
