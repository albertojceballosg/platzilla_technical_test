<?php
	require_once ('data/CRMEntity.php');
	require_once ('include/platzilla/Objects/FieldInterface.php');
	require_once ('include/platzilla/Managers/FieldManager.php');
	require_once ('include/platzilla/Managers/KanbanViewManager.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/NumberHelper.class.php');
	require_once ('modules/CustomView/CustomView.php');
	require_once ('include/ListView/ListView.php');

	/**
	 * Class KanbanViewUtils
	 *
	 * En esta clase se implementan los métodos de administración y configuración de las vistas Kanban
	 */
	abstract class KanbanViewUtils {

		/**
		 * Registros por página
		 */
		const RECORDS_PER_PAGE = 500;
		
		/**
		 * Devuelve los valores de un pipeline (array indexado) si el campo es de tipo pipeline;
		 * null si no es pipeline o si no tiene valores configurados.
		 *
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param string $fieldName
		 *
		 * @return array|null
		 */
		private static function getPipelineValues ($adb, $moduleName, $fieldName) {
			if (empty ($moduleName) || empty ($fieldName)) {
				return null;
			}
			$result = $adb->pquery (
				'SELECT `values` FROM vtiger_pipelines WHERE modulename=? AND fieldname=?',
				array ($moduleName, $fieldName)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			$row    = $adb->fetchByAssoc ($result, -1, false);
			$values = !empty ($row ['values']) ? json_decode ($row ['values'], true) : null;
			return (is_array ($values) && !empty ($values)) ? array_values ($values) : null;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $fieldName
		 * @param null $statusValue
		 * @param string|null $moduleName  Requerido cuando $fieldName es un pipeline
		 *
		 * @return integer|string
		 */
		private static  function getFiledStatusName ($adb, $fieldName, $statusValue, $moduleName = null) {
			if (empty ($fieldName) || (!isset ($statusValue)) || ($statusValue === '')) {
				return null;
			}
			// Soporte pipeline: $statusValue se interpreta como índice dentro del array de valores
			if (!empty ($moduleName)) {
				$pipelineValues = self::getPipelineValues ($adb, $moduleName, $fieldName);
				if ($pipelineValues !== null) {
					$idx = intval ($statusValue);
					return isset ($pipelineValues [$idx]) ? $pipelineValues [$idx] : null;
				}
			}
			$result = $adb->query ("SELECT {$fieldName} FROM vtiger_{$fieldName} WHERE {$fieldName}id ={$statusValue}");
			if ($adb->num_rows ($result) > 0) {
				$row = $adb->fetchByAssoc ($result, -1, false);
			}
			return (isset($row)) ? $row [$fieldName] : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param integer $kanbanId
		 *
		 * @return null|string
		 * @throws Exception
		 */
		private static function getSqlKanban ($adb, $moduleName, $kanbanId, $current_user) {
			$viewId = self::getCustomView ($adb, $moduleName);
			$focus = new $moduleName ();
			$focus->initSortbyField ($moduleName);
			$queryGenerator = new QueryGenerator ($moduleName, $current_user);
			$queryGenerator->initForCustomViewById ($viewId, $kanbanId);
			$query = $queryGenerator->getQuery ();
			
			// Excluir actividades de tipo "Job" para el módulo Calendar (tareas)
			if ($moduleName === 'Calendar') {
				$query .= " AND vtiger_activity.activitytype != 'Job'";
			}
			
			return $query;
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $viewId
		 *
		 * @throws Exception
		 */
		public static function deleteView (PearDatabase $adb, $viewId) {
			if (empty ($viewId)) {
				throw new Exception ('No se ha suministrado el ID de la vista a eliminar');
			}

			$adb->pquery ('DELETE FROM vtiger_kanbanfield_config WHERE kanbanviewid=?', array ($viewId));
			$adb->pquery ('DELETE FROM vtiger_kanbanfield_card_config WHERE kanbanviewid = ? ', array ($viewId));
			$adb->pquery ('DELETE FROM vtiger_kvadvfilter WHERE kanbanviewid=?', array ($viewId));
			$adb->pquery ('DELETE FROM vtiger_kvadvfilter_grouping WHERE kanbanviewid=?', array ($viewId));
			$adb->pquery ('DELETE FROM vtiger_kvstdfilter WHERE kanbanviewid=?', array ($viewId));
			$adb->pquery ('DELETE FROM vtiger_kanbanviews WHERE kanbanviewid=?', array ($viewId));
		}

		/**
		 * @param PearDatabase $adb
		 * @param Users $current_user
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function getAvailableApplications (PearDatabase $adb, $current_user) {
			$local_user = clone $current_user;
			require ('user_privileges/user_privileges.php');
			$profileIds = implode (',', $current_user_profiles);

			if (!empty($profileIds) && $profileIds != '') {
				$profileIds = " where profileid in ({$profileIds}) ";
			} else {
				$profileIds = '';
			}

			$applications     = array ();
			$applicationCodes = array ();
			$appCodesProfile  = array ();

			$resultProfile = $adb->pquery ("select REPLACE(REPLACE(applicationcodes, '\"]', '\''), '[\"', '\'') applicationcodes from vtiger_profile {$profileIds}", array ());

			if (($resultProfile) && ($adb->num_rows ($resultProfile) > 0) && $current_user->is_admin == 'off') {
				while ($row = $adb->fetchByAssoc ($resultProfile, -1, false)) {
					$appCodesProfile[] = $row ['applicationcodes'];
				}
				$appCodes = implode (',', $appCodesProfile);
				if (!empty($appCodes) && $appCodes != '') {
					$appCodesProfileIn = " AND app_code IN ({$appCodes})";
				} else {
					$appCodesProfileIn = '';
				}
			} else {
				$appCodesProfileIn = '';
			}

			$result = $adb->query ("SELECT config_applicationsid, app_code, app_name FROM vtiger_config_applications WHERE app_status='Activa' {$appCodesProfileIn}");

			// Get the application catalog
			if (($result) && ($adb->num_rows ($result) > 0)) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$applicationCode                   = $row ['app_code'];
					$applications [ $applicationCode ] = $row;
					$applicationCodes []               = $applicationCode;
				}
			} else {
				$applications     = null;
				$applicationCodes = null;
			}

			return $applications;
		}

		/**
		 * @param PearDatabase $adb
		 * @param Users $current_user
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function getAvailableApplicationsView (PearDatabase $adb, $current_user) {
			$local_user = clone $current_user;
			require ('user_privileges/user_privileges.php');
			$profileIds = implode (',', $current_user_profiles);
			if (!empty($profileIds) && $profileIds != '') {
				$profileIds = " where profileid in ({$profileIds}) ";
			} else {
				$profileIds = '';
			}

			$applications      = array ();
			$applicationCodes  = array ();
			$appCodesProfile   = array ();
			$appCodesProfileIn = '';

			$resultProfile = $adb->pquery ("select REPLACE(REPLACE(applicationcodes, '\"]', '\''), '[\"', '\'') applicationcodes from vtiger_profile {$profileIds}", array ());

			if (($resultProfile) && ($adb->num_rows ($resultProfile) > 0) && $current_user->is_admin == 'off') {
				while ($row = $adb->fetchByAssoc ($resultProfile, -1, false)) {
					$appCodesProfile[] = $row ['applicationcodes'];
				}
				$appCodes = implode (',', $appCodesProfile);
				if (!empty ($appCodes) && $appCodes != '') {
					$appCodesProfileIn = " AND app_code IN ({$appCodes})";
				}
			} else {
				$appCodesProfileIn = '';
			}

			$result = $adb->query ("SELECT ica.app_code, ica.app_name FROM vtiger_config_applications ica INNER JOIN vtiger_kanbanviews kw ON kw.aplicationcode = ica.app_code WHERE app_status='Activa' {$appCodesProfileIn} GROUP BY ica.app_code");
			// Get the application catalog
			if (($result) && ($adb->num_rows ($result) > 0)) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$applicationCode                   = $row ['app_code'];
					$applications [ $applicationCode ] = $row;
					$applicationCodes []               = $applicationCode;
				}
			} else {
				$applications     = null;
				$applicationCodes = null;
			}

			return $applications;
		}

		/**
		 * @param PearDatabase $adb
		 * @param Users $current_user
		 * @param boolean $isAdmin
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function getAvailableModules (PearDatabase $adb, $current_user, $isAdmin = null) {
			$local_user = clone $current_user;
			require ('user_privileges/user_privileges.php');
			$tabsapp = array ();
			$tapp    = array ();

			$application = self::getAvailableApplications ($adb, $current_user);

			foreach ($application as $value) {
				$result = $adb->pquery (
					"SELECT capp.config_applicationsid,
															capp.app_code,
															capp.app_name,
															tab.tabid,
															tab.name,
															tab.tablabel
													FROM vtiger_config_applications capp
													INNER JOIN vtiger_configapps_tab ctab ON ctab.config_applicationsid = capp.config_applicationsid
													INNER JOIN vtiger_tab tab ON tab.tabid = ctab.tabid
													WHERE app_status='Activa' AND tab.presence IN (0, 2) AND tab.customized IN (0, 1) AND tab.isentitytype=1 AND
														  capp.app_code=?
													ORDER BY tab.tablabel ASC",
					array ($value['app_code'])
				);

				// Get the tab application catalog
				if (($result) && ($adb->num_rows ($result) > 0)) {
					while ($row = $adb->fetchByAssoc ($result, -1, false)) {
						if (!empty($isAdmin)) {
							if ($isAdmin == 'on') {
								$tapp['tabid']    = $row ['tabid'];
								$tapp['name']     = $row ['name'];
								$tapp['tablabel'] = $row ['tablabel'];
								$tabsapp []       = $tapp;
							} else if ($profileTabsPermission[ $row ['tabid'] ] == 0) {
								$tapp['tabid']    = $row ['tabid'];
								$tapp['name']     = $row ['name'];
								$tapp['tablabel'] = $row ['tablabel'];
								$tabsapp []       = $tapp;
							}
						}
					}
				} else {
					$tabsapp = null;
					$tapp    = null;
				}
			}

			return $tabsapp;
		}

		/**
		 * @param PearDatabase $adb
		 * @param Users $current_user
		 * @param string $appSelect
		 * @param boolean $isAdmin
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function getAvailableModulesByApp (PearDatabase $adb, $current_user, $appSelect, $isAdmin = null) {
			$local_user = clone $current_user;
			require ('user_privileges/user_privileges.php');
			$tabsapp = array ();
			$tapp    = array ();

			$result = $adb->pquery (
				"SELECT capp.config_applicationsid,
														capp.app_code,
														capp.app_name,
														tab.tabid,
														tab.name,
														tab.tablabel
												FROM vtiger_config_applications capp
												INNER JOIN vtiger_configapps_tab ctab ON ctab.config_applicationsid = capp.config_applicationsid
												INNER JOIN vtiger_tab tab ON tab.tabid = ctab.tabid
												WHERE app_status='Activa' AND tab.presence IN (0, 2) AND tab.customized IN (0, 1) AND tab.isentitytype=1 AND
													  capp.app_code=?
												ORDER BY tab.tablabel ASC",
				array ($appSelect)
			);

			// Get the tab application catalog
			if (($result) && ($adb->num_rows ($result) > 0)) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					if (!empty($isAdmin)) {
						if ($isAdmin == 'on') {
							$tapp['tabid']    = $row ['tabid'];
							$tapp['name']     = $row ['name'];
							$tapp['tablabel'] = $row ['tablabel'];
							$tabsapp []       = $tapp;
						} else if ($profileTabsPermission[ $row ['tabid'] ] == 0) {
							$tapp['tabid']    = $row ['tabid'];
							$tapp['name']     = $row ['name'];
							$tapp['tablabel'] = $row ['tablabel'];
							$tabsapp []       = $tapp;
						}
					}
				}
			} else {
				$tabsapp = null;
				$tapp    = null;
			}

			return $tabsapp;
		}

		/**
		 * @param PearDatabase $adb
		 * @param Users $current_user
		 * @param string $appSelect
		 * @param boolean $isAdmin
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function getAvailableModulesByAppView (PearDatabase $adb, $current_user, $appSelect, $isAdmin = null) {
			$local_user = clone $current_user;
			require ('user_privileges/user_privileges.php');
			$tabsapp = array ();
			$tapp    = array ();

			$result = $adb->pquery (
				"SELECT tab.tabid,
											tab.name,
											tab.tablabel
									FROM vtiger_config_applications capp
									INNER JOIN vtiger_configapps_tab ctab ON ctab.config_applicationsid = capp.config_applicationsid
									INNER JOIN vtiger_tab tab ON tab.tabid = ctab.tabid
									INNER JOIN vtiger_kanbanviews kw ON kw.aplicationcode = capp.app_code
									WHERE app_status='Activa' AND tab.presence IN (0, 2) AND tab.customized IN (0, 1)
											AND tab.isentitytype=1 AND tab.tabid = kw.moduletabid AND capp.app_code=?
									GROUP BY tab.tabid
									ORDER BY tab.tablabel ASC",
				array ($appSelect)
			);

			// Get the tab application catalog
			if (($result) && ($adb->num_rows ($result) > 0)) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					if (!empty($isAdmin)) {
						if ($isAdmin == 'on') {
							$tapp['tabid']    = $row ['tabid'];
							$tapp['name']     = $row ['name'];
							$tapp['tablabel'] = $row ['tablabel'];
							$tabsapp []       = $tapp;
						} else if ($profileTabsPermission[ $row ['tabid'] ] == 0) {
							$tapp['tabid']    = $row ['tabid'];
							$tapp['name']     = $row ['name'];
							$tapp['tablabel'] = $row ['tablabel'];
							$tabsapp []       = $tapp;
						}
					}
				}
			} else {
				$tabsapp = null;
				$tapp    = null;
			}

			return $tabsapp;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function getAvailableModuleFields (PearDatabase $adb, $moduleName) {
			if (!$moduleName) {
				return null;
			}

			$result = $adb->pquery (
				'SELECT
					f.*,
					t.name AS modulename
				FROM
					vtiger_field f
					INNER JOIN vtiger_tab t ON t.tabid=f.tabid
				WHERE
					t.presence IN (0, 2) AND
					f.uitype IN (?, ?, ?, ?) AND
					t.name=?
				ORDER BY
					f.fieldlabel',
				array (FieldInterface::UI_TYPE_GLOBAL_PICKLIST, FieldInterface::UI_TYPE_MULTI_SELECT, FieldInterface::UI_TYPE_PICKLIST, FieldInterface::UI_TYPE_PIPELINE, $moduleName)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$fields = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$row ['fieldlabel'] = getTranslatedString ($row ['fieldlabel'], $moduleName);
				$fields []          = $row;
			}
			return $fields;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function getAvailableModuleFieldsCards (PearDatabase $adb, $moduleName) {
			if (!$moduleName) {
				return null;
			}
			// Nota: se permiten PICKLIST y CALCULATED porque el valor mostrado se almacena
			// directamente en la tabla del modulo (vtiger_<modulo>), por lo que no se requiere
			// resolucion adicional para renderizarlos en la tarjeta.
			$inUiType = $adb->sql_expr_datalist (
				array (
					FieldInterface::UI_TYPE_MODIFIED_BY,
					FieldInterface::UI_TYPE_IMAGE_REFERENCE,
					FieldInterface::UI_TYPE_GRID,
					FieldInterface::UI_TYPE_CHECKBOX,
					FieldInterface::UI_TYPE_ATTACHMENTS,
					FieldInterface::UI_TYPE_CALCULATED_LINK,
				)
			);
			$result = $adb->query (
				"SELECT 
						f.*, 
						t.name AS modulename 
					  FROM 
					  	vtiger_field f INNER JOIN vtiger_tab t ON t.tabid = f.tabid 
					  WHERE 
					  t.presence IN (0, 2) AND 
					  f.uitype NOT IN {$inUiType}  AND 
					  t.name='{$moduleName}'"
			);

			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$fields = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$fieldtype  = explode ('~', $row ['typeofdata']);
				$row ['typeofdata'] = $fieldtype [0];
				$row ['fieldlabel'] = getTranslatedString ($row ['fieldlabel'], $moduleName);
				$fields []          = $row;
			}
			return $fields;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $fieldName
		 * @param string|null $moduleName  Requerido cuando $fieldName es un pipeline
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function getAvailableModuleFieldsPickList (PearDatabase $adb, $fieldName, $moduleName = null) {
			if (!$fieldName) {
				return null;
			}
			// Soporte pipeline: los valores viven en vtiger_pipelines.values (JSON).
			// Se utiliza el índice del array como pickfieldid (identificador entero).
			if (!empty ($moduleName)) {
				$pipelineValues = self::getPipelineValues ($adb, $moduleName, $fieldName);
				if ($pipelineValues !== null) {
					$fields = array ();
					foreach ($pipelineValues as $index => $value) {
						$fields [] = array (
							'picklabel'   => $value,
							'pickfieldid' => $index,
						);
					}
					return $fields;
				}
			}
			$result = $adb->query ("SELECT ff.{$fieldName} picklabel, ff.{$fieldName}id pickfieldid FROM vtiger_{$fieldName} ff");

			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$fields = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$fields [] = $row;
			}
			return $fields;
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $tabId
		 * @param string $app
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function getAvailableViews (PearDatabase $adb, $tabId, $app) {
			$result = $adb->pquery (
				'SELECT kv.*,
									 t.name modulelabel,
									 t.tablabel titlemodulelabel,
									 f.fieldname,
									 f.fieldlabel titlefieldlabel,
									 total.__total_records__
							FROM vtiger_kanbanviews kv
							INNER JOIN vtiger_tab t ON t.tabid = kv.moduletabid
							INNER JOIN vtiger_field f ON f.fieldid = kv.fieldid AND f.tabid = kv.moduletabid
							CROSS JOIN (SELECT COUNT(*) AS __total_records__ FROM vtiger_kanbanviews) AS total
						WHERE kv.moduletabid = ? AND kv.aplicationcode = ?
						ORDER BY
							kv.kanbanviewid DESC',
				array ($tabId, $app)
			);

			if ($adb->num_rows ($result) > 0) {
				$records = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$records [] = $row;
				}
			} else {
				$records = null;
			}

			return $records;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function getAvailableViewsByModule (PearDatabase $adb, $moduleName) {
			$result = $adb->query (
				"SELECT kanbanviewid, label, fieldname, locked
						FROM vtiger_kanbanviews
						WHERE modulename = '{$moduleName}' AND isvisibleinlist=1
						ORDER BY label DESC"
			);

			if ($adb->num_rows ($result) > 0) {
				$records = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$records [] = $row;
				}
			} else {
				$records = null;
			}

			return $records;
		}

		/**
		 * @return array
		 */
		public static function getCalculationOperators () {
			$calculationsData = array ();
			$typeOfString     = array ('V','DT', 'T', 'N', 'NN');
			$typeOfNumber     = array ( 'N', 'NN');

			$stdObject                    = new stdClass ();
			$stdObject->label             = 'Promedio';
			$stdObject->typeOfData        = $typeOfNumber;
			$calculationsData ['AVG']     = $stdObject;
			$stdObject                    = new stdClass ();
			$stdObject->label             = 'Contar el número de registros';
			$stdObject->typeOfData        = $typeOfString;
			$calculationsData ['COUNT']   = $stdObject;
			$stdObject                    = new stdClass ();
			$stdObject->label             = 'Desviación estándar';
			$stdObject->typeOfData        = $typeOfNumber;
			$calculationsData ['STD']     = $stdObject;
			$stdObject                    = new stdClass ();
			$stdObject->label             = 'Máximo';
			$stdObject->typeOfData        = $typeOfNumber;
			$calculationsData ['MAX']     = $stdObject;
			$stdObject                    = new stdClass ();
			$stdObject->label             = 'Mínimo';
			$stdObject->typeOfData        = $typeOfNumber;
			$calculationsData ['MIN']     = $stdObject;
			$stdObject                    = new stdClass ();
			$stdObject->label             = 'Suma';
			$stdObject->typeOfData        = $typeOfNumber;
			$calculationsData ['SUM']     = $stdObject;
			$stdObject                    = new stdClass ();
			$stdObject->label             = 'Varianza';
			$stdObject->typeOfData        = $typeOfNumber;
			$calculationsData ['VAR_POP'] = $stdObject;

			return $calculationsData;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 *
		 * @return integer|null
		 * @throws Exception
		 */
		public static function getCustomView (PearDatabase $adb, $moduleName) {
			$viewId = null;
			$result = $adb->pquery (
				"SELECT cv.* FROM vtiger_customview cv WHERE cv.entitytype=? AND (cv.setdefault=1 OR cv.viewname = 'All') ORDER BY cv.setdefault  LIMIT 1",
				array ($moduleName)
			);
			if (($result) && ($adb->num_rows ($result) > 0)) {
				$row    = $adb->fetchByAssoc ($result, -1, false);
				$viewId = $row ['cvid'];
				return $viewId;
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function getCustomViewByModule (PearDatabase $adb, $moduleName) {
			$result = $adb->pquery (
				'SELECT cvid, viewname  FROM vtiger_customview WHERE entitytype=?  ORDER BY viewname',
				array ($moduleName)
			);
			if ($adb->num_rows ($result) > 0) {
				$records = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$records [] = $row;
				}
			} else {
				$records = null;
			}

			return $records;
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $crmId
		 *
		 * @return integer|string
		 * @throws Exception
		 */
		public static function getEntityValue ($adb, $crmId) {
			if (empty ($crmId) || !is_numeric ($crmId) || $crmId <= 0) {
				return $crmId;
			}
			$value = $crmId;
			$query = $adb->query (
				"SELECT
						e.tablename,
						e.fieldname,
						e.entityidfield
					FROM
						vtiger_entityname e
					INNER JOIN vtiger_crmentity crm ON crm.setype = e.modulename
					WHERE
					crm.crmid = {$crmId}"
			);
			if ($adb->num_rows ($query) > 0) {
				$row   = $adb->fetchByAssoc ($query, -1, false);
				$query = $adb->query (
					"SELECT
						{$row ['fieldname']} AS fieldvalue
					FROM
						{$row ['tablename']}
					WHERE
					{$row ['entityidfield']} = {$crmId}"
				);
				if ($adb->num_rows ($query) > 0) {
					$row   = $adb->fetchByAssoc ($query, -1, false);
					$value = $row ['fieldvalue'];
				}
			}
			return $value;
		}

		/**
		 * @param PearDatabase $adb
		 * @param Users $current_user
		 * @param null $keyword
		 * @param null $page
		 * @param null $moduleNames
		 *
		 * @return array
		 * @throws Exception
		 */
		public static function getKanbanViews (PearDatabase $adb, $current_user, $keyword = null, $page = null, $moduleNames = null) {
			$application             = self::getAvailableApplications ($adb, $current_user);
			$moduleNamesWhereClauses = self::getKanbanViewsWhereClausesByModuleName ($moduleNames);
			$keywordWhereClauses     = self::getKanbanViewsWhereClausesByKeyword ($keyword);

			$whereClauses = array_filter (array_merge ($moduleNamesWhereClauses ['where'], $keywordWhereClauses ['where']));
			$arguments    = array_filter (array_merge ($moduleNamesWhereClauses ['arguments'], $keywordWhereClauses ['arguments']));
			$whereClause  = !empty ($whereClauses) ? ' WHERE ' . join (' AND ', $whereClauses) : '';

			if ((empty ($page)) || ($page <= 0)) {
				$startRecord = 0;
			} else {
				$startRecord = (($page - 1) * self::RECORDS_PER_PAGE);
			}

			$limit = self::RECORDS_PER_PAGE;

			$resultView = self::getResultView ($adb, $whereClause, $startRecord, $limit, $arguments, $application);

			return $resultView;
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $viewId
		 *
		 * @return KanbanView|mixed
		 * @throws Exception
		 */
		public static function getKanbanViewById (PearDatabase $adb, $viewId) {
			if (empty ($viewId)) {
				throw new Exception ('No se ha suministrado el ID de la vista kanban');
			}
			$view = KanbanViewManager::getInstance($adb)->fetchKanbanViewById ($viewId);

			return $view;
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $viewId
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function getKanbanViewCards (PearDatabase $adb, $viewId) {
			if (empty ($viewId)) {
				throw new Exception ('No se ha suministrado el ID de la vista');
			}

			$result = $adb->pquery (
				'SELECT
							kvr.*,
							t.tablabel,
							f.fieldlabel,
							f.fieldname,
							f.uitype
						FROM
							vtiger_kanbanfield_card_config kvr
							INNER JOIN vtiger_kanbanviews kv ON kv.kanbanviewid = kvr.kanbanviewid
							INNER JOIN vtiger_tab t ON t.name=kv.modulename
							INNER JOIN vtiger_field f ON f.tabid=t.tabid AND f.fieldid=kvr.fieldId
						WHERE
							kvr.kanbanviewid=?
						ORDER BY
							kvr.fieldcardid',
				array ($viewId)
			);

			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$fieldsCard = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$fieldsCard [] = $row;
			}
			return $fieldsCard;
		}

		/**
		 * @param string $keyword
		 *
		 * @return array
		 */
		private static function getKanbanViewsWhereClausesByKeyword ($keyword) {
			if (!empty ($keyword)) {
				$whereClauses = array ('(kv.label LIKE ? OR kv.modulename LIKE ?)');
				$arguments    = array ("%{$keyword}%", "%{$keyword}%");
			} else {
				$whereClauses = array ();
				$arguments    = array ();
			}

			return array (
				'where'     => $whereClauses,
				'arguments' => $arguments,
			);
		}

		/**
		 * @param string $moduleNames
		 *
		 * @return array
		 */
		private static function getKanbanViewsWhereClausesByModuleName ($moduleNames) {
			if (!empty ($moduleNames)) {
				$questionMarks = str_repeat ('?, ', (count ($moduleNames) - 1)) . '?';
				$whereClauses  = array ("t.name IN ({$questionMarks})");
				$arguments     = $moduleNames;
			} else {
				$whereClauses = array ();
				$arguments    = array ();
			}

			return array (
				'where'     => $whereClauses,
				'arguments' => $arguments,
			);
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $viewId
		 * @param integer $fieldName
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function getKanbanViewRules (PearDatabase $adb, $viewId, $fieldName, $moduleName, $current_user) {
			if (empty ($viewId)) {
				throw new Exception ('No se ha suministrado el ID de la vista');
			}
			$adb->dieOnError = false;
			$listQuery        = self::getSqlKanban ($adb, $moduleName, $viewId, $current_user);
			$calculationNames = self::getCalculationOperators();
			$numberingHelper  = NumberHelper::getInstance ($adb, $current_user);
			// Detectar si el campo agrupador es un pipeline (los valores viven en vtiger_pipelines, no en vtiger_{fieldName})
			$pipelineValues = self::getPipelineValues ($adb, $moduleName, $fieldName);
			if ($pipelineValues !== null) {
				// Caso pipeline: el picklabel se resuelve desde el JSON indexado por pickfieldid
				$result = $adb->pquery (
					"SELECT
								kvr.*,
								t.tablabel,
								f.fieldlabel
							FROM
								vtiger_kanbanfield_config kvr
								INNER JOIN vtiger_kanbanviews kv ON kv.kanbanviewid = kvr.kanbanviewid
								INNER JOIN vtiger_tab t ON t.name=kv.modulename
								INNER JOIN vtiger_field f ON f.tabid=t.tabid AND f.fieldname=kv.fieldname
							WHERE
								kvr.kanbanviewid=?
							ORDER BY
								kvr.kanbanfieldconfigid",
					array ($viewId)
				);
			} else {
				$result = $adb->pquery (
					"SELECT
								kvr.*,
								t.tablabel,
								f.fieldlabel,
								ff.{$fieldName} picklabel
							FROM
								vtiger_kanbanfield_config kvr
								INNER JOIN vtiger_kanbanviews kv ON kv.kanbanviewid = kvr.kanbanviewid
								INNER JOIN vtiger_tab t ON t.name=kv.modulename
								INNER JOIN vtiger_field f ON f.tabid=t.tabid AND f.fieldname=kv.fieldname
								INNER JOIN vtiger_{$fieldName} ff ON ff.{$fieldName}id = kvr.pickfieldid
							WHERE
								kvr.kanbanviewid=?
							ORDER BY
								kvr.kanbanfieldconfigid",
					array ($viewId)
				);
			}
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				throw new Exception ('No se ha encontrado la configuración del campo kanban');
			}

			$rules = array ();
			$fm    = FieldManager::getInstance($adb);
			$totalGeneral = 0;
			$isSumOperation = false;
			
			// Primera pasada: calcular valores y detectar si es operación SUM
			$tempRules = array();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				// Para campos pipeline: resolver picklabel desde los values JSON indexados por pickfieldid
				if ($pipelineValues !== null) {
					$idx = intval ($row ['pickfieldid']);
					$row ['picklabel'] = isset ($pipelineValues [$idx]) ? $pipelineValues [$idx] : null;
				}
				$row ['calculation'] = null;
				$row ['calculationRaw'] = 0;
				if (!empty ($row ['fieldname']) && !empty ($row ['operation']) && !empty($listQuery)) {
					$filedStatusName    = self::getFiledStatusName ($adb, $fieldName, $row['pickfieldid'], $moduleName);
					$fieldNameCondition = (!empty($filedStatusName)) ?  " AND {$fieldName} = '{$filedStatusName}'" : '';
					$listQueryExt = $listQuery . $fieldNameCondition;
					if ($row ['operation'] == 'COUNT') {
						$queryExec   = "SELECT {$row ['operation']}(DISTINCT {$row ['fieldname']}) AS value  " . strstr ($listQueryExt, strtoupper ('FROM'));
					} else {
						$queryExec   = "SELECT {$row ['operation']}({$row ['fieldname']}) AS value  " . strstr ($listQueryExt, strtoupper ('FROM'));
					}
					
					$calculation = $adb->query ($queryExec);
					if ((!$calculation) || ($adb->num_rows ($calculation) == 0)) {
						throw new Exception ('¡Ha ocurrido un error al cargar la vista Kanban!. Te recomendamos revisar la configuración de la vista y corregir cualquier inconsistencia.');
					}
					
					$dummy       = explode ('.', $row ['fieldname']);
					$objField    = $fm->fetchFieldByName($moduleName, $dummy [1], true);

					if ($adb->num_rows ($calculation) > 0) {
						$rowValue = $adb->fetchByAssoc ($calculation, -1, false);
						$row ['calculationRaw'] = floatval($rowValue ['value']);
						$row ['calculation'] = $numberingHelper->setNumberFormat ($rowValue ['value']);
						
						// Detectar si es operación SUM
						if ($row ['operation'] == 'SUM') {
							$isSumOperation = true;
						}
					}

					$row ['fieldname'] = (!empty($objField)) ? $objField->getLabel() : '';
					$row ['operation'] = $calculationNames [ $row ['operation'] ]->label;
				}

				$tempRules [] = $row;
			}
			
			// Segunda pasada: calcular total general sumando TODAS las columnas y agregar porcentajes
			if ($isSumOperation) {
				foreach ($tempRules as $row) {
					$totalGeneral += $row['calculationRaw'];
				}
			}
			
			foreach ($tempRules as $row) {
				if ($isSumOperation && $totalGeneral > 0 && $row['calculationRaw'] > 0) {
					$percentage = ($row['calculationRaw'] / $totalGeneral) * 100;
					$row['percentage'] = number_format($percentage, 2) . '%';
				} else {
					$row['percentage'] = null;
				}
				$rules[] = $row;
			}
			
			$adb->dieOnError = true;
			return $rules;
		}

		/**
		 * Obtiene registros para vista Kanban con límite de 100 registros por columna/estado
		 * 
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param Users $current_user
		 * @param integer $view
		 * @param integer $page Número de página (default: 1)
		 * @param integer $recordsPerColumn Registros por columna (default: 100)
		 *
		 * @return array Con keys: records, pagination (total, page, recordsPerColumn, totalPages, hasMore)
		 * @throws Exception
		 */
		public static function getRecordsModuleView (PearDatabase $adb, $moduleName, $current_user, $view, $page = 1, $recordsPerColumn = 100) {
		
			try {
				$local_user = clone $current_user;
				require ('user_privileges/user_privileges.php');
				if (file_exists ("{$_SESSION ['plat']}/modules/{$moduleName}/{$moduleName}.php")) {
					checkFileAccessForInclusion ("{$_SESSION ['plat']}/modules/{$moduleName}/{$moduleName}.php");
					require_once ("{$_SESSION ['plat']}/modules/{$moduleName}/{$moduleName}.php");
				} else {
					checkFileAccessForInclusion ("modules/{$moduleName}/{$moduleName}.php");
					require_once ("modules/{$moduleName}/{$moduleName}.php");
				}

				// Obtener configuración de campos de las tarjetas
				$fieldsConfig = self::getKanbanViewCards ($adb, $view);
				$fconfig      = array ();
				if ($fieldsConfig != null && count ($fieldsConfig) > 0) {
					foreach ($fieldsConfig as $key => $item) {
						$fconfig[] = $item['fieldname'];
						$fconfig[] = $item['uitype'];
					}
				}

				// Obtener información de la vista Kanban
				$kanbanView = self::getKanbanViewById($adb, $view);
				$fieldName = $kanbanView->getFieldName();

				// Obtener estados/columnas configurados
				$states = self::getKanbanStates($adb, $view, $fieldName, $moduleName);
				$stateCount = count($states);

				// Obtener query base
				$listQuery = self::getSqlKanban ($adb, $moduleName, $view, $current_user);
				$baseQueryFrom = strstr($listQuery, strtoupper('FROM'));
			
				// Calcular offset para cada columna según la página
				$offset = ($page - 1) * $recordsPerColumn;

				// Construir UNION de queries para obtener hasta 100 registros de cada estado
				$unionQueries = array();
				$totalRecordsByState = array();
			
				foreach ($states as $state) {
					// Contar registros totales de este estado
					$countQuery = "SELECT COUNT(*) as total {$baseQueryFrom} AND vtiger_{$moduleName}.{$fieldName} = ? ";
					$countResult = $adb->pquery($countQuery, array($state['name']));
					$stateTotal = (int)$adb->query_result($countResult, 0, 'total');
					$totalRecordsByState[$state['name']] = $stateTotal;
				
					// Si este estado tiene registros para esta página, agregarlo al UNION
					if ($stateTotal > $offset) {
						$stateQuery = "(SELECT * {$baseQueryFrom} AND vtiger_{$moduleName}.{$fieldName} = " . 
						              $adb->quote($state['name']) . 
						              " LIMIT {$recordsPerColumn} OFFSET {$offset})";
						$unionQueries[] = $stateQuery;
					}
				}

				// Ejecutar query combinada
				$records = null;
				if (!empty($unionQueries)) {
					$finalQuery = implode(" UNION ALL ", $unionQueries);
					$result = $adb->query($finalQuery);
					$recordCount = $adb->num_rows($result);
				
					if ($recordCount > 0) {
						$records = array();
						while ($row = $adb->fetchByAssoc($result, -1, false)) {
							$fieldsCard = array();
							foreach ($row as $key => $item) {
								if (in_array($key, $fconfig)) {
									$configKey = array_search($key, $fconfig);
									if ($fconfig[($configKey + 1)] == 10) {
										$item = self::getEntityValue($adb, $item);
									}
									$fieldsCard[$key] = nl2br(mberegi_replace('[\n|\r|\n\r|\t||\x0B]', ' ', trim($item)));
									$fieldsCard[$key] = addslashes($fieldsCard[$key]);
									$fieldsCard[$key] = str_replace('"', "'", $fieldsCard[$key]);
								}
							}
							$records[$row[$moduleName . 'id']] = $fieldsCard;
						}
					}
				}

				// Calcular paginación total
				$totalRecords = array_sum($totalRecordsByState);
				$maxRecordsPerState = max($totalRecordsByState);
				$totalPages = ceil($maxRecordsPerState / $recordsPerColumn);
				$hasMore = $page < $totalPages;
						
				// Retornar registros con información de paginación
				$resultData = array(
					'records' => $records,
					'pagination' => array(
						'total' => (int)$totalRecords,
						'page' => (int)$page,
						'recordsPerColumn' => (int)$recordsPerColumn,
						'totalPages' => (int)$totalPages,
						'hasMore' => $hasMore,
						'showing' => ($records !== null) ? count($records) : 0,
						'stateDistribution' => $totalRecordsByState
					)
				);
				return $resultData;
				
			} catch (Exception $e) {
				error_log("[KANBAN] ERROR en getRecordsModuleView: " . $e->getMessage());
				error_log("[KANBAN] Stack trace: " . $e->getTraceAsString());
				throw $e;
			}
		}

		/**
		 * Obtiene los estados/columnas configurados para una vista Kanban
		 *
		 * @param PearDatabase $adb
		 * @param integer $viewId
		 * @param string $fieldName
		 * @param string|null $moduleName  Requerido cuando $fieldName es un pipeline
		 *
		 * @return array Array de estados con keys: id, name
		 * @throws Exception
		 */
		private static function getKanbanStates(PearDatabase $adb, $viewId, $fieldName, $moduleName = null) {
			// Soporte pipeline: el valor textual vive en vtiger_pipelines.values (JSON) indexado por pickfieldid
			$pipelineValues = (!empty ($moduleName)) ? self::getPipelineValues ($adb, $moduleName, $fieldName) : null;
			if ($pipelineValues !== null) {
				$result = $adb->pquery (
					'SELECT kvr.pickfieldid FROM vtiger_kanbanfield_config kvr WHERE kvr.kanbanviewid = ? ORDER BY kvr.kanbanfieldconfigid',
					array ($viewId)
				);
				if ((!$result) || ($adb->num_rows ($result) == 0)) {
					throw new Exception ('No se encontraron estados configurados para esta vista Kanban');
				}
				$states = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$idx = intval ($row ['pickfieldid']);
					if (isset ($pipelineValues [$idx])) {
						$states [] = array (
							'id'   => $idx,
							'name' => $pipelineValues [$idx],
						);
					}
				}
				return $states;
			}

			$result = $adb->pquery(
				"SELECT ff.{$fieldName}id, ff.{$fieldName} as state_name
				 FROM vtiger_kanbanfield_config kvr
				 INNER JOIN vtiger_{$fieldName} ff ON ff.{$fieldName}id = kvr.pickfieldid
				 WHERE kvr.kanbanviewid = ?
				 ORDER BY kvr.kanbanfieldconfigid",
				array($viewId)
			);
		
			if ((!$result) || ($adb->num_rows($result) == 0)) {
				throw new Exception('No se encontraron estados configurados para esta vista Kanban');
			}
		
			$states = array();
			while ($row = $adb->fetchByAssoc($result, -1, false)) {
				$states[] = array(
					'id' => $row["{$fieldName}id"],
					'name' => $row['state_name']
				);
			}
			return $states;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $modulename
		 * @param integer $userId
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function isDefaultView (PearDatabase $adb, $modulename, $userId = 0) {
			if (empty($modulename)) {
				return null;
			}

			if ($userId > 0) {
				$forTheUser = "AND NOT EXISTS (SELECT kanbanviewid FROM vtiger_user_kanbanview_preferences WHERE kanbanviewid=kv.kanbanviewid AND disabledby={$userId})";
			}

			$query = $adb->pquery (
				"SELECT
						kv.kanbanviewid, kv.fieldname
					  FROM
					  	vtiger_kanbanviews kv
					  WHERE
					    kv.modulename=? AND
					    kv.isdefaultview=?
					    {$forTheUser}",
				array ($modulename, 1)
			);
			if (($query) && ($adb->num_rows ($query) > 0)) {
				return $adb->fetchByAssoc ($query, -1, false);
			}
			return null;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $whereClause
		 * @param integer $startRecord
		 * @param integer $limit
		 * @param array $arguments
		 * @param string $application
		 *
		 * @return array
		 * @throws Exception
		 */
		public static function getResultView (PearDatabase $adb, $whereClause, $startRecord, $limit, $arguments, $application) {
			$result = $adb->pquery (
				"SELECT kv.*,
									 t.name modulelabel,
									 t.tablabel titlemodulelabel,
									 f.fieldname,
									 f.fieldlabel titlefieldlabel,
									 total.__total_records__
							FROM vtiger_kanbanviews kv
							INNER JOIN vtiger_tab t on t.tabid = kv.moduletabid
							INNER JOIN vtiger_field f on f.fieldid = kv.fieldid AND f.tabid = kv.moduletabid
							CROSS JOIN (SELECT COUNT(*) AS __total_records__ FROM vtiger_kanbanviews) AS total
						{$whereClause}
						ORDER BY
							kv.kanbanviewid DESC
						LIMIT {$startRecord}, {$limit}",
				$arguments
			);

			if ($adb->num_rows ($result) > 0) {
				$startRecord++;
				$totalRecords = null;
				$records      = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					if ($totalRecords === null) {
						$totalRecords = intval ($row ['__total_records__']);
					}
					$row['aplicationName'] = $application[ $row['aplicationcode'] ]['app_name'];
					$records []            = $row;
				}
				$endRecord  = count ($records);
				$totalPages = ceil ($totalRecords / self::RECORDS_PER_PAGE);
			} else {
				$totalRecords = 0;
				$records      = null;
				$endRecord    = 0;
				$totalPages   = 0;
			}

			return array (
				'startRecord'  => $startRecord,
				'endRecord'    => $endRecord,
				'totalRecords' => $totalRecords,
				'page'         => empty ($page) ? 1 : intval ($page),
				'totalPages'   => $totalPages,
				'records'      => $records,
			);
		}

		/**
		 * @param string $string
		 *
		 * @return string string
		 */
		public static function sanitizeString ($string) {
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
			$string   = substr (strtolower ($string), 0, 22);
			$randomId = rand (100, 999);
			return $string . $randomId;
		}

		/**
		 * @param PearDatabase $adb
		 * @param array $arguments
		 * @param integer $viewId
		 *
		 * @return null
		 * @throws Exception
		 */
		public static function setDefaultKanbanView (PearDatabase $adb, $arguments, $viewId) {
			if (!$arguments ['isdefaultview']) {
				return null;
			}
			$query = $adb->pquery (
				'SELECT
						*
					  FROM
					  	vtiger_user_kanbanview_preferences
					  WHERE
					   kanbanviewid= ? AND disabledby=?',
				array ($viewId, $arguments ['userid'])
			);
			if (($adb->num_rows ($query) == 0) && (!$arguments ['defaultKV'])) {
				$adb->pquery (
					'INSERT vtiger_user_kanbanview_preferences(kanbanviewid, disabledby) VALUES (?,?)',
					array ($viewId, $arguments ['userid'])
				);
			} else if (($adb->num_rows ($query) > 0) && ($arguments ['defaultKV'])) {
				$adb->pquery (
					'DELETE FROM vtiger_user_kanbanview_preferences WHERE kanbanviewid=?  AND disabledby=?',
					array ($viewId, $arguments ['userid'])
				);
			}
			return null;
		}

	/**
	 * @param PearDatabase $adb
	 * @param integer $recordId
	 * @param string $fieldName
	 * @param string $tabName
	 * @param integer $valueId
	 * @param string $moduleName
	 * @param Users $currentUser
	 *
	 * @return boolean|null
	 * @throws Exception
	 */
	public static function updateFieldValueView (PearDatabase $adb, $recordId, $fieldName, $tabName, $valueId, $moduleName = null, $currentUser = null) {
		// Si tenemos módulo y usuario, registrar auditoría
		$shouldAudit = !empty($moduleName) && !empty($currentUser);
		$oldData = array();
		$newData = array();
		
		if ($shouldAudit) {
			require_once('data/CRMEntity.php');
			require_once('data/CrmEntityUtils.php');
			
			try {
				// Obtener datos antiguos del registro completo
				$focus = CRMEntity::getInstance($moduleName);
				$focus->id = $recordId;
				$focus->mode = 'edit';
				$focus->retrieve_entity_info($recordId, $moduleName);
				$oldData = $focus->column_fields;
			} catch (Exception $e) {
				// Si falla la recuperación, continuar sin auditoría
				$shouldAudit = false;
			}
		}
		
		// Determinar el nuevo valor
		$newValue = '';
		if ($valueId == 'todo') {
			$newValue = '';
		} else {
			// Soporte pipeline: resolver el valor textual desde el JSON de vtiger_pipelines
			$pipelineModule = !empty ($moduleName) ? $moduleName : $tabName;
			$pipelineValues = self::getPipelineValues ($adb, $pipelineModule, $fieldName);
			if ($pipelineValues !== null) {
				$idx = intval ($valueId);
				if (!isset ($pipelineValues [$idx])) {
					return null;
				}
				$newValue = $pipelineValues [$idx];
			} else {
				$result = $adb->pquery (
					"SELECT {$fieldName} FROM vtiger_{$fieldName} WHERE {$fieldName}id = ?",
					array ($valueId)
				);

				if ((!$result) || ($adb->num_rows ($result) == 0)) {
					return null;
				}
				$view = $adb->fetchByAssoc ($result, -1, false);
				$newValue = $view["{$fieldName}"];
			}
		}
		
		// Ejecutar el UPDATE
		$adb->pquery (
			"UPDATE vtiger_{$tabName} SET {$fieldName}=? WHERE {$tabName}id = ?",
			array ($newValue, $recordId)
		);
		
		// Registrar auditoría si corresponde
		if ($shouldAudit && !empty($oldData)) {
			try {
				// Construir newData con el campo modificado
				$newData = $oldData;
				// Agregar indicador de que el cambio fue hecho desde Kanban
				$newData[$fieldName] = $newValue . ' (Kanban)';
				
				// Actualizar modifiedtime en vtiger_crmentity
				$today = date('Y-m-d H:i:s');
				$adb->pquery(
					'UPDATE vtiger_crmentity SET modifiedtime=?, modifiedby=? WHERE crmid=?',
					array($today, $currentUser->id, $recordId)
				);
				
				// Registrar en histórico de auditoría con indicador de origen
				CrmEntityUtils::audit($adb, $recordId, $moduleName, $oldData, $newData, $currentUser->id);
				
			} catch (Exception $e) {
				// Log del error pero no fallar la operación
				error_log("[KANBAN-AUDIT] Error al registrar auditoría: " . $e->getMessage());
			}
		}
		
		return true;
	}

	/**
	 * @param PearDatabase $adb
	 * @param array $arguments
	 *
	 * @throws Exception
	 */
	public static function updateView (PearDatabase $adb, $arguments) {
		$viewId = $arguments ['kanbanviewid'];
		if ((!empty($arguments ['prevView'])) && (intval ($arguments ['prevView']) != $viewId)) {
			$adb->query ("UPDATE vtiger_kanbanviews SET  isdefaultview=0 WHERE modulename= '{$arguments ['modulename']}' AND kanbanviewid != {$viewId}");
		}
		if ($arguments ['mode'] == 'edit') {
			$isDefaultView = ((!$arguments ['isdefaultview']) && ($arguments ['defaultKV'])) ? 1 : $arguments ['isdefaultview'];
		} else {
			$isDefaultView = $arguments ['defaultKV'];
		}
		$arguments ['isdefaultview'] = $isDefaultView;
		self::setDefaultKanbanView ($adb, $arguments, $viewId);
	}

}