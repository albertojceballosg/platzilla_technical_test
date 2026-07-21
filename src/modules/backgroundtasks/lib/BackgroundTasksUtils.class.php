<?php
	require_once ('include/platzilla/Objects/FieldInterface.php');
	require_once ('include/platzilla/Managers/BackgroundTaskManager.php');
	require_once ('include/platzilla/Managers/GlobalPicklistManager.php');
	require_once ('include/platzilla/Managers/PicklistManager.php');
	require_once ('include/platzilla/Managers/UserManager.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/GridFieldUtils.class.php');

	abstract class BackgroundTasksUtils {

		/**
		 * @param BackgroundTaskManager $btm
		 * @param integer $taskId
		 * @param string $actionData
		 *
		 * @return BackgroundTaskParameter[]|null
		 */
		private static function getTaskActionParameters (BackgroundTaskManager $btm, $taskId, $actionData) {
			if (empty ($actionData ['parameters'])) {
				return null;
			}

			$parameters = array ();
			foreach ($actionData ['parameters'] as $parameterName => $parameterData) {
				$parameter = $btm->fetchEmptyParameter ($actionData ['actiontype'], $parameterName)
					->setActionName ($actionData ['actionname'])
					->setTaskId ($taskId);

				if ($parameter->showExpanded ()) {
					$types         = array ();
					$valueFormulas = array ();
					foreach ($parameterData as $expandedKey => $parameterValues) {
						$types [ $expandedKey ]         = !empty ($parameterValues ['type']) ? $parameterValues ['type'] : null;
						$valueFormulas [ $expandedKey ] = !empty ($parameterValues ['valueformula']) ? $parameterValues ['valueformula'] : null;
					}
				} else if (is_array ($parameterData)) {
					$types         = !empty ($parameterData ['type']) ? $parameterData ['type'] : null;
					$valueFormulas = !empty ($parameterData ['valueformula']) ? $parameterData ['valueformula'] : null;
				} else {
					$types         = null;
					$valueFormulas = !empty ($parameterData) ? $parameterData : null;
				}
				$parameters [] = $parameter->setType ($types)
					->setValueFormula ($valueFormulas);
			}
			return $parameters;
		}

		/**
		 * @param BackgroundTaskManager $btm
		 * @param array $taskData
		 *
		 * @return BackgroundTaskAction[]|null
		 */
		private static function getTaskActions (BackgroundTaskManager $btm, $taskData) {
			if (empty ($taskData ['actions'])) {
				return null;
			}

			$taskId  = $taskData ['taskid'];
			$actions = array ();
			foreach ($taskData ['actions'] as $actionData) {
				$actions [] = $btm->fetchEmptyAction ($actionData ['actiontype'], null)
					->setName ($actionData ['actionname'])
					->setParameters (self::getTaskActionParameters ($btm, $taskId, $actionData))
					->setTaskId ($taskId)
					->setType ($actionData ['actiontype']);
			}
			return $actions;
		}

		/**
		 * @param array $taskData
		 *
		 * @return BackgroundTaskFilterGroup[]|null
		 */
		private static function getTaskFilterGroups ($taskData) {
			$moduleName       = $taskData ['modulename'];
			$filterGroupsData = $taskData ['filtergroups'];
			if ((empty ($moduleName)) || (empty ($filterGroupsData))) {
				return null;
			}

			$taskId  = $taskData ['taskid'];
			$groupId = 1;
			$groups  = array ();
			foreach ($filterGroupsData as $filterGroupData) {
				if (empty ($filterGroupData ['filters'])) {
					continue;
				}

				$filterId = 1;
				$filters  = array ();
				foreach ($filterGroupData ['filters'] as $filterData) {
					$filters [] = BackgroundTaskFilter::getInstance ()
						->setComparator ($filterData ['comparator'])
						->setFieldName ($filterData ['fieldname'])
						->setGroupId ($groupId)
						->setLabel ($filterData ['fieldname'])
						->setModuleName ($moduleName)
						->setOperator (!empty ($filterData ['operator']) ? $filterData ['operator'] : null)
						->setSequence ($filterId)
						->setTaskId ($taskId)
						->setValue ($filterData ['value']);
					$filterId++;
				}

				$groups [] = BackgroundTaskFilterGroup::getInstance ()
					->setId ($groupId)
					->setFilters ($filters)
					->setModuleName ($moduleName)
					->setOperator (!empty ($filterGroupData ['operator']) ? $filterGroupData ['operator'] : null)
					->setTaskId ($taskId);
				$groupId++;
			}
			return count ($groups) > 0 ? $groups : null;
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $taskId
		 */
		public static function deleteTaskById (PearDatabase $adb, $taskId) {
			$btm  = BackgroundTaskManager::getInstance ($adb);
			$task = $btm->fetchTaskById ($taskId, true);
			$btm->deleteTask ($task);
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $taskName
		 */
		public static function deleteTaskByName (PearDatabase $adb, $taskName) {
			$btm  = BackgroundTaskManager::getInstance ($adb);
			$task = $btm->fetchTaskByName ($taskName);
			$btm->deleteTask ($task);
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $taskId
		 */
		public static function disableTask (PearDatabase $adb, $taskId) {
			$btm  = BackgroundTaskManager::getInstance ($adb);
			$task = $btm->fetchTaskById ($taskId);
			if (!empty ($task)) {
				$task->setStatus (BackgroundTaskInterface::STATUS_DISABLED);
				$btm->saveTask ($task);
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $taskId
		 *
		 * @return BackgroundTask
		 * @throws Exception
		 */
		public static function duplicateTask (PearDatabase $adb, $taskId) {
			$task = BackgroundTaskManager::getInstance ($adb)->fetchTaskById ($taskId);
			if (empty ($task)) {
				throw new Exception ('No se encuentra registrada la tarea con el ID suministrado');
			}

			return $task->duplicate (null)->setName (null);
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $taskId
		 */
		public static function enableTask (PearDatabase $adb, $taskId) {
			$btm  = BackgroundTaskManager::getInstance ($adb);
			$task = $btm->fetchTaskById ($taskId);
			if (!empty ($task)) {
				$task->setStatus (BackgroundTaskInterface::STATUS_ENABLED);
				$btm->saveTask ($task);
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $actionType
		 * @param array $selectedParameterValues
		 *
		 * @return BackgroundTaskAction|null
		 */
		public static function getAvailableAction (PearDatabase $adb, $actionType, array $selectedParameterValues = null) {
			return BackgroundTaskManager::getInstance ($adb)->fetchEmptyAction ($actionType, $selectedParameterValues);
		}

		/**
		 * @param PearDatabase $adb
		 * @param array $selectedParameterValues
		 *
		 * @return BackgroundTaskAction[]|null
		 */
		public static function getAvailableActions (PearDatabase $adb, array $selectedParameterValues = null) {
			return BackgroundTaskManager::getInstance ($adb)->fetchAvailableActions ($selectedParameterValues);
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return string[]|null
		 */
		public static function getAvailableCategories (PearDatabase $adb) {
			return BackgroundTaskManager::getInstance ($adb)->fetchAvailableCategories ();
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return string[]|null
		 */
		public static function getAvailableEvents (PearDatabase $adb) {
			return BackgroundTaskManager::getInstance ($adb)->fetchAvailableEvents ();
		}

		/**
		 * @return string[]
		 */
		public static function getAvailableEventInstants () {
			return array (BackgroundTaskInterface::EVENT_INSTANT_BEFORE, BackgroundTaskInterface::EVENT_INSTANT_AFTER);
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return string[]|null
		 */
		public static function getAvailableEntityModules (PearDatabase $adb) {
			$result = $adb->pquery ('SELECT t.* FROM vtiger_tab t WHERE (t.presence IN (0, 2) AND t.isentitytype=1) OR t.name IN (?, ?) ORDER BY t.tablabel', array ('instances', 'notifications'));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$modules = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$modules [] = $row;
			}
			return $modules;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 *
		 * @return string[]|null
		 */
		public static function getAvailableFieldsData (PearDatabase $adb, $moduleName) {
			if (empty ($moduleName)) {
				return null;
			} else if ($moduleName == 'instances') {
				$result = $adb->pquery ('SELECT * FROM vtiger_tab WHERE name=?', array ($moduleName));
				if ((!$result) || ($adb->num_rows ($result) == 0)) {
					return null;
				}

				$row    = $adb->fetchByAssoc ($result, -1, false);
				$fields = array (
					array ('tabid' => $row ['tabid'], 'columnname' => 'code', 'fieldlabel' => 'Nombre/Código', 'fieldname' => 'code', 'presence' => 0, 'uitype' => 1),
					array ('tabid' => $row ['tabid'], 'columnname' => 'name', 'fieldlabel' => 'Empresa', 'fieldname' => 'name', 'presence' => 0, 'uitype' => 1),
					array ('tabid' => $row ['tabid'], 'columnname' => 'administrator', 'fieldlabel' => 'Usuario', 'fieldname' => 'administrator', 'presence' => 0, 'uitype' => 13),
					array ('tabid' => $row ['tabid'], 'columnname' => 'accountid', 'fieldlabel' => 'Cuenta', 'fieldname' => 'accountid', 'presence' => 0, 'uitype' => 10),
					array ('tabid' => $row ['tabid'], 'columnname' => 'billingplan', 'fieldlabel' => 'Plan', 'fieldname' => 'billingplan', 'presence' => 0, 'uitype' => 1),
					array ('tabid' => $row ['tabid'], 'columnname' => 'activeusers', 'fieldlabel' => 'Usuarios activos', 'fieldname' => 'activeusers', 'presence' => 0, 'uitype' => 7),
					array ('tabid' => $row ['tabid'], 'columnname' => 'status', 'fieldlabel' => 'Status', 'fieldname' => 'status', 'presence' => 0, 'uitype' => 1),
					array ('tabid' => $row ['tabid'], 'columnname' => 'verificationcode', 'fieldlabel' => 'Código de verificación', 'fieldname' => 'verificationcode', 'presence' => 0, 'uitype' => 1),
					array ('tabid' => $row ['tabid'], 'columnname' => 'registrationdate', 'fieldlabel' => 'Fecha registro', 'fieldname' => 'registrationdate', 'presence' => 0, 'uitype' => 5),
					array ('tabid' => $row ['tabid'], 'columnname' => 'servicestartdate', 'fieldlabel' => 'Fecha contrato', 'fieldname' => 'servicestartdate', 'presence' => 0, 'uitype' => 5),
				);
				usort (
					$fields,
					function ($fieldA, $fieldB) {
						return $fieldA ['fieldlabel'] > $fieldB ['fieldlabel'];
					}
				);
				return $fields;
			} else if ($moduleName == 'notifications') {
				$result = $adb->pquery ('SELECT * FROM vtiger_tab WHERE name=?', array ($moduleName));
				if ((!$result) || ($adb->num_rows ($result) == 0)) {
					return null;
				}

				$row    = $adb->fetchByAssoc ($result, -1, false);
				$fields = array (
					array ('tabid' => $row ['tabid'], 'columnname' => 'name', 'fieldlabel' => 'Nombre', 'fieldname' => 'name', 'presence' => 0, 'uitype' => 1),
					array ('tabid' => $row ['tabid'], 'columnname' => 'contents', 'fieldlabel' => 'Contenido', 'fieldname' => 'contents', 'presence' => 0, 'uitype' => 19),
					array ('tabid' => $row ['tabid'], 'columnname' => 'status', 'fieldlabel' => 'Status', 'fieldname' => 'status', 'presence' => 0, 'uitype' => 1),
					array ('tabid' => $row ['tabid'], 'columnname' => 'sendbyemail', 'fieldlabel' => 'Enviar al panel de mensajes', 'fieldname' => 'sendbyemail', 'presence' => 0, 'uitype' => 1),
				);
				usort (
					$fields,
					function ($fieldA, $fieldB) {
						return $fieldA ['fieldlabel'] > $fieldB ['fieldlabel'];
					}
				);
				return $fields;
			}

			$result = $adb->pquery (
				'SELECT
					f.*,
					CASE f.uitype WHEN ? THEN (SELECT relmodule FROM vtiger_fieldmodulerel WHERE fieldid=f.fieldid LIMIT 1) ELSE NULL END AS relatedmodulename
				FROM
					vtiger_field f
					INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=?
				WHERE
					f.uitype NOT IN (?)',
				array (FieldInterface::UI_TYPE_MODULE_REFERENCE, $moduleName, FieldInterface::UI_TYPE_MODULE_RECORDS)
			);
			if (($result) || ($adb->num_rows ($result) > 0)) {
				$fields = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					if ($row ['uitype'] == FieldInterface::UI_TYPE_GRID) {
						$row ['gridfields'] = GridFieldUtils::getGridFields ($adb, $moduleName, $row ['fieldname']);
					}
					$row ['fieldlabel'] = getTranslatedString ($row ['fieldlabel'], $moduleName);
					$fields []          = $row;
				}
				usort (
					$fields,
					function ($fieldA, $fieldB) {
						return $fieldA ['fieldlabel'] > $fieldB ['fieldlabel'];
					}
				);
			} else {
				$fields = null;
			}
			return $fields;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 *
		 * @return array|null
		 */
		public static function getAvailablePicklistValues (PearDatabase $adb, $moduleName) {
			if ((empty ($moduleName)) || ($moduleName == 'instances' || ($moduleName == 'notifications'))) {
				return null;
			}

			$result = $adb->pquery (
				'SELECT * FROM vtiger_field WHERE uitype IN (?, ?, ?) AND tabid IN (SELECT tabid FROM vtiger_tab WHERE name=?)',
				array (FieldInterface::UI_TYPE_GLOBAL_PICKLIST, FieldInterface::UI_TYPE_MULTI_SELECT, FieldInterface::UI_TYPE_PICKLIST, $moduleName)
			);
			if ($adb->num_rows ($result) > 0) {
				$availablePicklistValues = array ();
				$gpm                     = GlobalPicklistManager::getInstance ($adb);
				$pm                      = PicklistManager::getInstance ($adb);
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					if ($row ['uitype'] == FieldInterface::UI_TYPE_GLOBAL_PICKLIST) {
						$picklistValues = $gpm->fetchPicklistRawValues ($row ['fieldname']);
					} else {
						$picklistValues = $pm->fetchPicklistRawValues ($row ['fieldname']);
					}
					if (!empty ($picklistValues)) {
						sort ($picklistValues);
					}
					$availablePicklistValues [ $row ['fieldname'] ] = $picklistValues;
				}
			} else {
				$availablePicklistValues = null;
			}
			return $availablePicklistValues;
		}

		/**
		 * @return string[]
		 */
		public static function getAvailableStatuses () {
			return array (BackgroundTaskInterface::STATUS_ENABLED, BackgroundTaskInterface::STATUS_DISABLED);
		}

		/**
		 * @return string[]
		 */
		public static function getAvailableTriggers () {
			return array (BackgroundTaskInterface::TRIGGER_EVENT, BackgroundTaskInterface::TRIGGER_DAILY_SCHEDULE, BackgroundTaskInterface::TRIGGER_MANUAL, BackgroundTaskInterface::TRIGGER_TIMED_SCHEDULE);
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $platform
		 *
		 * @return null|User[]
		 */
		public static function getAvailableUsers (PearDatabase $adb, $platform) {
			return UserManager::getInstance ($adb, $platform)->fetchUsers ();
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $taskId
		 *
		 * @return null|array
		 * @throws Exception
		 */
		public static function getRelatedModulesInTasks (PearDatabase $adb, $taskId) {
			if (empty ($taskId)) {
				throw new Exception ('No has suministrado el ID de la tarea');
			}
			$mainModule = null;
			$result = $adb->pquery('SELECT modulename FROM vtiger_bgtasks_data WHERE taskid=?', array($taskId));
			if ($adb->num_rows ($result) > 0) {
				$row = $adb->fetchByAssoc ($result, -1, false);
				$mainModule = $row ['modulename'];
			}

			if (empty($mainModule)) {
				return null;
			}
			$result = $adb->pquery(
				'SELECT 
						bgt.parameterformula, 
						tab.tablabel 
					  FROM 
					  	vtiger_bgtasks_data_parameters bgt 
					  INNER JOIN vtiger_tab tab ON tab.name = bgt.parameterformula  
					  WHERE 
					  	bgt.taskid=? AND 
					  	bgt.parametername=?',
				array ($taskId, 'modulename')
			);
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					if ($row ['parameterformula'] == $mainModule) {
						$modulesInTasks [] = array (
							'record_id' => "ID del registro que se está procesando: {$row ['tablabel']} ({$row ['parameterformula']})"
						);
					} else {
						$modulesInTasks [] = array (
							"{$row ['parameterformula']}@RECORD_ID" => "ID del registro de: {$row ['tablabel']} ({$row ['parameterformula']}) creado en acción previa"
						);
					}
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return isset($modulesInTasks) ? $modulesInTasks : null;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string[] $moduleNames
		 * @param boolean $scope
		 * @param boolean $headersOnly
		 *
		 * @return BackgroundTask[]|null
		 */
		public static function getTasks (PearDatabase $adb, $moduleNames, $scope, $headersOnly = false) {
			return BackgroundTaskManager::getInstance ($adb)->fetchTasksClassifiedByScope ($moduleNames, $scope, $headersOnly);
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $taskId
		 * @param boolean $headersOnly
		 *
		 * @return BackgroundTask|null
		 * @throws Exception
		 */
		public static function getTaskById (PearDatabase $adb, $taskId, $headersOnly = false) {
			if (empty ($taskId)) {
				throw new Exception ('No has suministrado el ID de la tarea');
			}

			return BackgroundTaskManager::getInstance ($adb)->fetchTaskById ($taskId, $headersOnly);
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $taskName
		 *
		 * @return BackgroundTask|null
		 * @throws Exception
		 */
		public static function getTaskByName (PearDatabase $adb, $taskName) {
			if (empty ($taskName)) {
				throw new Exception ('No has suministrado el nombre de la tarea');
			}

			return BackgroundTaskManager::getInstance ($adb)->fetchTaskByName ($taskName);
		}

		/**
		 * @param PearDatabase $adb
		 * @param array $taskData
		 * @param boolean $isInstance
		 *
		 * @return BackgroundTask
		 * @throws Exception
		 */
		public static function saveTask (PearDatabase $adb, $taskData, $isInstance = false) {
			$btm  = BackgroundTaskManager::getInstance ($adb);
			$task = BackgroundTask::getInstance ()
				->setActions (self::getTaskActions ($btm, $taskData))
				->setCategory ($taskData ['category'])
				->setDescription ($taskData ['description'])
				->setEvent ($taskData ['event'])
				->setEventInstant ($taskData ['eventinstant'])
				->setFilterGroups (self::getTaskFilterGroups ($taskData))
				->setFrequency ($taskData ['frequency'])
				->setId ($taskData ['taskid'])
				->setLocked ($isInstance)
				->setModuleName ($taskData ['modulename'])
				->setName ($taskData ['taskname'])
				->setProtected ($taskData ['protected'])
				->setScope ($taskData ['scope'])
				->setStatus ($taskData ['taskstatus'])
				->setTrigger ($taskData ['trigger'])
				->setUrlVideo ($taskData ['videourl']);
			return $btm->saveTask ($task);
		}

	}
