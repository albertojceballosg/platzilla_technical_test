<?php
	require_once ('include/platzilla/Objects/BackgroundTask.php');
	require_once ('include/platzilla/Managers/BackgroundTaskFilterManager.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/SystemVariables.class.php');
	require_once ('log4php/LoggerManager.php');
	require_once ('data/CRMEntity.php');

	class BackgroundTaskManager {
		const RECORDS_PER_PAGE = 25;

		/** @var BackgroundTaskManager[]|null */
		private static $INSTANCES = null;

		/** @var integer[][]|null */
		private static $RUNNING_TASKS_IDS = null;

		private static $SEARCH_LAST_ID = '@RECORD_ID';

		/** @var PearDatabase */
		private $adb;

		/** @var array */
		private $output;

		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
		}

		/**
		 * @param array $parameterConfiguration
		 * @param array $selectedParameterValues
		 * @param string $actionHandlerClassName
		 *
		 * @return array|mixed|null
		 */
		private function evaluateDefaultOptionsFormula ($parameterConfiguration, $selectedParameterValues, $actionHandlerClassName) {
			switch ($parameterConfiguration ['defaultoptionstype']) {
				case BackgroundTaskParameterConfigurationInterface::OPTION_TYPE_HANDLER:
					$rootFolderPath = PlatzillaUtils::getPlatzillaRootFolderPath ();
					if (file_exists ("{$rootFolderPath}/modules/backgroundtasks/handlers/{$actionHandlerClassName}.class.php")) {
						require_once ("modules/backgroundtasks/handlers/{$actionHandlerClassName}.class.php");
						if (is_callable (array ($actionHandlerClassName, 'getDefaultOptions'))) {
							$defaultOptions = call_user_func_array (array ($actionHandlerClassName, 'getDefaultOptions'), array ($this->adb, $parameterConfiguration, $selectedParameterValues));
						} else {
							$defaultOptions = null;
						}
					} else {
						$defaultOptions = null;
					}
					break;
				case BackgroundTaskParameterConfigurationInterface::OPTION_TYPE_JSON:
					$defaultOptions = json_decode ($parameterConfiguration ['defaultoptionsformula'], true);
					break;
				case BackgroundTaskParameterConfigurationInterface::OPTION_TYPE_LITERAL:
					$defaultOptions = array ('label' => $parameterConfiguration ['defaultoptionsformula'], 'attributes' => null);
					break;
				case BackgroundTaskParameterConfigurationInterface::OPTION_TYPE_SQL:
					$defaultOptions = array ();
					$options        = $this->evaluateSql ($parameterConfiguration ['defaultoptionsformula'], $selectedParameterValues, null, false);
					if (!empty ($options)) {
						foreach ($options as $option) {
							$key                     = (is_array ($option)) ? array_shift (array_values (array_slice ($option, 0, 1))) : $option;
							$value                   = (is_array ($option)) ? array_shift (array_values (array_slice ($option, 1, 1))) : $option;
							$attributes              = count ($option) > 2 ? array_slice ($option, 2) : null;
							$defaultOptions [ $key ] = array (
								'label'      => $value,
								'attributes' => $attributes,
							);
						}
					}
					break;
				default:
					$defaultOptions = null;
					break;
			}
			return $defaultOptions;
		}

		/**
		 * @param BackgroundTask $task
		 * @param CRMEntity $entity
		 *
		 * @return boolean
		 */
		private function evaluateFilters ($task, $entity) {
			return BackgroundTaskFilterManager::getInstance ($this->adb)->evaluateFilters ($task, $entity);
		}

		/**
		 * @param string $parameterFormula
		 * @param array $dataSourceValues
		 *
		 * @return mixed
		 */
		private function evaluateRelatedSourceFieldParameterFormula ($parameterFormula, $dataSourceValues) {
			if (!empty ($dataSourceValues)) {
				$moduleName = isset ($dataSourceValues ['record_module']) ? $dataSourceValues ['record_module'] : null;
				$fieldValue = isset ($dataSourceValues [ $parameterFormula ]) ? $dataSourceValues [ $parameterFormula ] : null;
				$values     = $this->evaluateSourceField ($moduleName, $parameterFormula, $fieldValue);
			} else {
				$values = $parameterFormula;
			}
			return $values;
		}

		/**
		 * @param string $parameterFormula
		 * @param array $dataSourceValues
		 *
		 * @return mixed
		 */
		private function evaluateSourceFieldParameterFormula ($parameterFormula, $dataSourceValues) {
			if (isset ($dataSourceValues [ $parameterFormula ])) {
				$values = $dataSourceValues [ $parameterFormula ];
			} else {
				$values = null;
			}
			return $values;
		}

		/**
		 * @param string $parameterFormula
		 * @param array $dataSourceValues
		 *
		 * @return mixed
		 */
		private function evaluateSourceGridFieldParameterFormula ($parameterFormula, $dataSourceValues) {
			if (empty ($dataSourceValues)) {
				$values = $parameterFormula;
			} else if (isset ($dataSourceValues ['record_id'])) {
				$moduleName = $this->getModuleName ($dataSourceValues ['record_id']);
				$entityId   = $dataSourceValues ['record_id'];
				$values     = $this->evaluateSourceGridField ($moduleName, $parameterFormula, $entityId);
			} else {
				$values = null;
			}
			return $values;
		}

		/**
		 * @param string $parameterType
		 * @param string $parameterFormula
		 * @param array $selectedParameterValues
		 * @param array $dataSourceValues
		 *
		 * @return mixed
		 */
		private function evaluateParameterFormula ($parameterType, $parameterFormula, $selectedParameterValues, $dataSourceValues) {
			if ($parameterType == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_CUSTOM_SQL) {
				$values = $this->evaluateSql ($parameterFormula, $selectedParameterValues, $dataSourceValues);
			} else if ($parameterType == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_INSTANCE_EMAILS) {
				$values = $parameterFormula;
			} else if ($parameterType == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_LITERAL) {
				$values = !empty ($dataSourceValues)
					? $this->substituteVariables ($parameterFormula, $selectedParameterValues, $dataSourceValues)
					: $parameterFormula;
			} else if (($parameterType == null) && ($parameterFormula !== null)) {
				$values = $parameterFormula;
			} else if ($parameterType == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_FORMULA) {
				$isFound = strpos($parameterFormula, self::$SEARCH_LAST_ID);
				if ($isFound !== false) {
					$values = $this->getModulesLastId ($parameterFormula);
				} else {
					$values = $this->evaluateDateFormula ($parameterFormula, $dataSourceValues);
				}
			} else if ($parameterType == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_VARIABLE) {
				$isFound = strpos($parameterFormula, self::$SEARCH_LAST_ID);
				if ($isFound !== false) {
					$values = $this->getModulesLastId ($parameterFormula);
				} else {
					$values = $this->substituteVariables ($parameterFormula, $selectedParameterValues, $dataSourceValues);
				}
			} else if ($parameterType == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_RELATED_SOURCE_FIELD) {
				$values = $this->evaluateRelatedSourceFieldParameterFormula ($parameterFormula, $dataSourceValues);
			} else if (in_array ($parameterType, array (BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_EMAIL_SOURCE_FIELD, BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_RECORD_ID_SOURCE_FIELD, BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_SOURCE_FIELD, BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_RECORD_ID_PROJECT_FIELD))) {
				$values = $this->evaluateSourceFieldParameterFormula ($parameterFormula, $dataSourceValues);
			} else if ($parameterType == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_SOURCE_GRID_FIELD) {
				$values = $this->evaluateSourceGridFieldParameterFormula ($parameterFormula, $dataSourceValues);
			} else if (($parameterType == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_PREVIOUS_OUTPUT) && (isset ($this->output [ $parameterFormula ]))) {
				$values = $this->output [ $parameterFormula ];
			} else {
				$values = null;
			}
			return $values;
		}

		/**
		 * @param string $moduleName
		 * @param string $fieldName
		 * @param string $fieldValue
		 *
		 * @return mixed
		 */
		private function evaluateSourceField ($moduleName, $fieldName, $fieldValue) {
			if ((empty ($moduleName)) || (empty ($fieldName)) || (empty ($fieldValue))) {
				return $fieldValue;
			}

			$result = $this->adb->pquery (
				'SELECT
					fmr.relmodule
				FROM
					vtiger_field f
					INNER JOIN vtiger_fieldmodulerel fmr ON fmr.fieldid=f.fieldid
				WHERE
					f.uitype=10 AND
					f.fieldname=? AND
					f.tabid IN (SELECT tabid FROM vtiger_tab WHERE name=?)',
				array ($fieldName, $moduleName)
			);
			if ($this->adb->num_rows ($result) == 0) {
				DatabaseUtils::closeResult ($result);
				$result = null;
				return $fieldValue;
			}

			$fieldData = $this->adb->fetchByAssoc ($result, -1, false);
			DatabaseUtils::closeResult ($result);
			$result = null;

			$result = $this->adb->pquery ('SELECT * FROM vtiger_entityname WHERE modulename=?', array ($fieldData ['relmodule']));
			if ($this->adb->num_rows ($result) > 0) {
				$relatedEntityData = $this->adb->fetchByAssoc ($result, -1, false);
				$relatedFieldName  = $relatedEntityData ['fieldname'];

				/** @var CRMEntity|object $relatedEntity */
				$relatedEntity = CRMEntity::getInstance ($fieldData ['relmodule']);
				$relatedEntity->retrieve_entity_info ($fieldValue, $fieldData ['relmodule']);

				$value = isset ($relatedEntity->column_fields [ $relatedFieldName ]) ? $relatedEntity->column_fields [ $relatedFieldName ] : $fieldValue;
			} else {
				$value = $fieldValue;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $value;
		}

		/**
		 * @param string $moduleName
		 * @param string $fullGridFieldName
		 * @param integer $entityId
		 *
		 * @return array|null
		 */
		private function evaluateSourceGridField ($moduleName, $fullGridFieldName, $entityId) {
			if ((empty ($moduleName)) || (empty ($fullGridFieldName)) || (empty ($entityId))) {
				return null;
			}

			$dummy = explode ('.', $fullGridFieldName);
			if ((!is_array ($dummy)) || (count ($dummy) != 2)) {
				return null;
			}

			$gridName      = $dummy [0];
			$gridFieldName = $dummy [1];
			$gridValues    = GridFieldUtils::getGridValues ($this->adb, $moduleName, $gridName, $entityId);
			if (empty ($gridValues)) {
				return null;
			}

			$values = array ();
			foreach ($gridValues as $gridValue) {
				$values [] = $gridValue [ $gridFieldName ];
			}
			return $values;
		}

		/**
		 * @param string $sql
		 * @param array $selectedParameterValues
		 * @param array $dataSourceValues
		 * @param boolean $onlyArrayValues
		 *
		 * @return mixed
		 */
		private function evaluateSql ($sql, $selectedParameterValues = null, $dataSourceValues = null, $onlyArrayValues = true) {
			if (empty ($sql)) {
				return null;
			}

			$sql = $this->substituteVariables ($sql, $selectedParameterValues, $dataSourceValues);
			$this->adb->setDieOnError (false);
			$result = $this->adb->query ($sql);
			$this->adb->setDieOnError (true);
			if ($this->adb->num_rows ($result) == 0) {
				DatabaseUtils::closeResult ($result);
				$result = null;
				return null;
			}

			if ($this->adb->num_rows ($result) == 1) {
				if ($onlyArrayValues) {
					$values = array_values ($this->adb->fetchByAssoc ($result, -1, false));
				} else {
					$values = $this->adb->fetchByAssoc ($result, -1, false);
				}
			} else {
				$values = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					if ($onlyArrayValues) {
						$values [] = array_values ($row);
					} else {
						$values [] = $row;
					}
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			if (($onlyArrayValues) && (count ($values) == 1)) {
				return $values [0];
			} else {
				return $values;
			}
		}

		/**
		 * @param integer $taskId
		 *
		 * @return BackgroundTaskAction[]|null
		 */
		private function fetchActions ($taskId) {
			if (empty ($taskId)) {
				return null;
			}

			$result = $this->adb->pquery (
				'SELECT
					a.*,
					cfg.scope,
					cfg.handlerclass,
					cfg.handlermethod
				FROM
					vtiger_bgtasks_data_actions a
					INNER JOIN vtiger_bgtasks_cfg_actions cfg ON cfg.actiontype=a.actiontype
				WHERE
					a.taskid=?
				ORDER BY
					a.actionorder',
				array ($taskId)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$actions = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$actions [] = BackgroundTaskAction::getInstance ()
						->setHandlerClass ($row ['handlerclass'])
						->setHandlerMethod ($row ['handlermethod'])
						->setName ($row ['actionname'])
						->setParameters ($this->fetchParameters ($taskId, $row ['actionname'], $row ['actiontype'], $row ['handlerclass']))
						->setScope ($row ['scope'])
						->setSequence (intval ($row ['actionorder']))
						->setTaskId ($taskId)
						->setType ($row ['actiontype']);
				}
			} else {
				$actions = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $actions;
		}

		/**
		 * @param string $actionType
		 * @param string $actionHandlerClassName
		 * @param array $selectedParameterValues
		 *
		 * @return BackgroundTaskParameter[]|null
		 */
		private function fetchAvailableParameters ($actionType, $actionHandlerClassName, $selectedParameterValues) {
			if (empty ($actionType)) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_bgtasks_cfg_parameters WHERE actiontype=? ORDER BY parameterorder', array ($actionType));
			if ($this->adb->num_rows ($result) > 0) {
				$parameters = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$parameters [] = BackgroundTaskParameter::getInstance ()
						->setActionType ($row ['actiontype'])
						->setAvailableTypes ($this->fetchParameterAvailableTypes ($actionType, $row ['parametername']))
						->setDefaultOptions ($this->evaluateDefaultOptionsFormula ($row, $selectedParameterValues, $actionHandlerClassName))
						->setDefaultOptionsType ($row ['defaultoptionstype'])
						->setDefaultOptionsFormula ($row ['defaultoptionsformula'])
						->setIsMandatory ($row ['ismandatory'] == 1 ? true : false)
						->setIsMultiValued ($row ['ismultivalued'] == 1 ? true : false)
						->setName ($row ['parametername'])
						->setRefreshOnChanges ($row ['refreshonchanges'] == 1 ? true : false)
						->setSequence (intval ($row ['parameterorder']))
						->setShowExpanded ($row ['showexpanded'] == 1 ? true : false)
						->setTranslationModule ($row ['translationmodule'])
						->setValueFormula (isset ($selectedParameterValues [ $row ['parametername'] ]) ? $selectedParameterValues [ $row ['parametername'] ] : null);
				}
			} else {
				$parameters = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $parameters;
		}

		/**
		 * @param string $moduleName
		 *
		 * @return BackgroundTask[]
		 */
		private function fetchDeletedTasks ($moduleName) {
			if (empty ($moduleName)) {
				return array ();
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_deletedelements WHERE elementtype=? AND modulename=?', array ('backgroundtask', $moduleName));
			if ($this->adb->num_rows ($result) > 0) {
				$tasks = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					/** @var BackgroundTask $task */
					$task = unserialize ($row ['serializedobject']);
					$task->setDeleted (true);
					$tasks [] = $task;
				}
			} else {
				$tasks = array ();
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $tasks;
		}

		/**
		 * @param integer $taskId
		 *
		 * @return BackgroundTaskFilterGroup[]|null
		 */
		private function fetchFilterGroups ($taskId) {
			return BackgroundTaskFilterManager::getInstance ($this->adb)->fetchTaskFilterGroupsByTaskId ($taskId);
		}

		/**
		 * @param string $actionType
		 * @param string $parameterName
		 *
		 * @return string[]|null
		 */
		private function fetchParameterAvailableTypes ($actionType, $parameterName) {
			if ((empty ($actionType)) || (empty ($parameterName))) {
				return null;
			}

			$result = $this->adb->pquery (
				'SELECT * FROM vtiger_bgtasks_cfg_parameteroptions WHERE actiontype=? AND parametername=?',
				array ($actionType, $parameterName)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$options = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$options [] = $row ['parametertype'];
				}
			} else {
				$options = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $options;
		}

		/**
		 * @param integer $taskId
		 * @param string $actionName
		 * @param string $actionType
		 * @param string $actionHandlerClassName
		 *
		 * @return BackgroundTaskParameter[]|null
		 */
		private function fetchParameters ($taskId, $actionName, $actionType, $actionHandlerClassName) {
			if ((empty ($taskId)) || (empty ($actionName)) || (empty ($actionType))) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_bgtasks_cfg_parameters WHERE actiontype=? ORDER BY parameterorder', array ($actionType));
			if ($this->adb->num_rows ($result) > 0) {
				$selectedParameterValues = array ();
				$parameters              = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$parameterName = $row ['parametername'];
					$showExpanded  = $row ['showexpanded'] == 1 ? true : false;
					$parameter     = self::fetchEmptyParameter ($actionType, $row ['parametername'])
						->setActionName ($actionName)
						->setTaskId ($taskId);
					$options       = $this->getParameterOptions ($parameter, $taskId, $actionName, $selectedParameterValues);
					if (!empty ($options)) {
						$parameter
							->setDefaultOptions ($this->evaluateDefaultOptionsFormula ($row, $selectedParameterValues, $actionHandlerClassName))
							->setType ($options ['types'])
							->setValue ($options ['values'])
							->setValueFormula ($options ['valueformulas']);
					}
					if (!$showExpanded) {
						$selectedParameterValues [ $parameterName ] = $parameter->getValue ();
					}
					$parameters [] = $parameter;
				}
			} else {
				$parameters = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $parameters;
		}

		/**
		 * @param integer $taskId
		 *
		 * @return array|null
		 */
		private function fetchTaskData ($taskId) {
			if (!empty ($taskId)) {
				$result = $this->adb->pquery ('SELECT * FROM vtiger_bgtasks_data WHERE taskid=?', array ($taskId));
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
		 * @param integer $entityId
		 *
		 * @return string
		 */
		private function getModuleName ($entityId) {
			if (empty ($entityId)) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_crmentity WHERE crmid=?', array ($entityId));
			if ($this->adb->num_rows ($result) > 0) {
				$row        = $this->adb->fetchByAssoc ($result, -1, false);
				$moduleName = $row ['setype'];
			} else {
				$moduleName = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $moduleName;
		}

		/**
		 * @param BackgroundTaskParameter $parameter
		 * @param integer $taskId
		 * @param string $actionName
		 * @param array $selectedParameterValues
		 *
		 * @return array|null
		 */
		private function getParameterOptions ($parameter, $taskId, $actionName, $selectedParameterValues) {
			if (empty ($parameter)) {
				return null;
			}

			$parameterName = $parameter->getName ();
			$result        = $this->adb->pquery (
				'SELECT * FROM vtiger_bgtasks_data_parameters p WHERE p.taskid=? AND p.actionname=? AND p.parametername=?',
				array ($taskId, $actionName, $parameterName)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$showExpanded  = $parameter->showExpanded ();
				$isMultiValued = $parameter->isMultiValued ();
				if ($showExpanded) {
					$expandedKey   = null;
					$values        = array ();
					$types         = array ();
					$valueFormulas = array ();
					while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
						$expandedKey                    = $row ['expandedkey'];
						$types [ $expandedKey ]         = $row ['parametertype'];
						$valueFormulas [ $expandedKey ] = $row ['parameterformula'];
						$values [ $expandedKey ]        = $this->evaluateParameterFormula ($row ['parametertype'], $row ['parameterformula'], $selectedParameterValues, null);
					}
				} else {
					$row           = $this->adb->fetchByAssoc ($result, -1, false);
					$expandedKey   = null;
					$types         = $row ['parametertype'];
					$valueFormulas = $row ['parameterformula'];
					$values        = $this->evaluateParameterFormula ($row ['parametertype'], $row ['parameterformula'], $selectedParameterValues, null);
				}
				$options = array (
					'types'         => $types,
					'valueformulas' => $valueFormulas,
					'values'        => ($isMultiValued) && (!is_array ($values)) ? array ($values) : $values,
				);
			} else {
				$options = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $options;
		}

		/**
		 * @param BackgroundTask $task
		 *
		 * @return BackgroundTask
		 */
		private function fixTask ($task) {
			$trigger   = $task->getTrigger ();
			$frequency = $task->getFrequency ();
			if ($trigger == BackgroundTaskInterface::TRIGGER_EVENT) {
				$task->setFrequency (null);
			} else if ($trigger == BackgroundTaskInterface::TRIGGER_MANUAL) {
				$task->setEvent (null);
				$task->setEventInstant (null);
				$task->setFrequency (null);
			} else if (in_array ($trigger, array (BackgroundTaskInterface::TRIGGER_DAILY_SCHEDULE, BackgroundTaskInterface::TRIGGER_TIMED_SCHEDULE))) {
				$task->setEvent (null);
				$task->setEventInstant (null);
				$task->setFrequency ($frequency !== null ? $frequency : 0);
			}
			return $task;
		}

		/**
		 * @param BackgroundTask $task
		 * @param CRMEntity $sourceEntity
		 *
		 * @return CRMEntity[]
		 */
		private function getDataSources ($task, $sourceEntity) {
			$entities   = null;
			$trigger    = $task->getTrigger ();
			$moduleName = $task->getModuleName ();
			if (in_array ($trigger, array (BackgroundTaskInterface::TRIGGER_DAILY_SCHEDULE, BackgroundTaskInterface::TRIGGER_TIMED_SCHEDULE))) {
				if (empty ($moduleName)) {
					$entities = null;
				} else if ($moduleName == 'instances') {
					$result = $this->adb->query ('SELECT * FROM vtiger_instances');
					if ($this->adb->num_rows ($result) > 0) {
						require_once ('modules/instances/instances.php');
						$entities = array ();
						while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
							$entity                = instances::getInstance ();
							$entity->id            = intval ($row ['instanceid']);
							$entity->column_fields = $row;
							if ($this->evaluateFilters ($task, $entity)) {
								$entities [] = $entity;
							}
						}
					} else {
						$entities = null;
					}
					DatabaseUtils::closeResult ($result);
					$result = null;
				} else {
					$result = $this->adb->pquery ('SELECT * FROM vtiger_crmentity WHERE deleted=0 AND setype=?', array ($moduleName));
					if ($this->adb->num_rows ($result) > 0) {
						$entities = array ();
						while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
							/** @var CRMEntity $entity */
							$entity                = CRMEntity::getInstance ($moduleName);
							$entity->retrieve_entity_info ($row ['crmid'], $moduleName, $this->adb);
							if ($this->evaluateFilters ($task, $entity)) {
								$entities [] = $entity;
							}
						}
					} else {
						$entities = null;
					}
					DatabaseUtils::closeResult ($result);
					$result = null;
				}
			} else if (
				(in_array ($trigger, array (BackgroundTaskInterface::TRIGGER_EVENT, BackgroundTaskInterface::TRIGGER_MANUAL))) &&
				(!empty ($sourceEntity)) &&
				($this->evaluateFilters ($task, $sourceEntity))
			) {
				$entities = array ($sourceEntity);
			}
			return !empty ($entities) ? $entities : null;
		}

		/**
		 * @param Logger $logger
		 * @param BackgroundTaskAction[] $actions
		 * @param CRMEntity[] $dataSourceValues
		 * @param string $platform
		 *
		 * @throws Exception
		 */
		private function runTaskActions (Logger $logger, $actions, $dataSourceValues = null, $platform = null) {
			foreach ($actions as $action) {
				$handlerClassName  = $action->getHandlerClass ();
				$handlerMethodName = $action->getHandlerMethod ();

				$logger->emit ('INFO', "Localizando clase gestora {$handlerClassName}");
				$handlerClassFilePath = "modules/backgroundtasks/handlers/{$handlerClassName}.class.php";
				if (!file_exists (__DIR__ . "/../../../{$handlerClassFilePath}")) {
					throw new Exception ("No se encuentra la clase gestora {$handlerClassName}");
				}

				$logger->emit ('INFO', "Determinando si la clase gestora {$handlerClassName} contiene el método {$handlerMethodName}");
				require_once ($handlerClassFilePath);
				/** @var BackgroundTaskActionHandler $handler */
				$handler = call_user_func_array (array ($handlerClassName, 'getInstance'), array ($this->adb, $logger, $platform));
				if (!is_callable (array ($handler, $handlerMethodName))) {
					throw new Exception ("No se encuentra el método {$handlerMethodName} en la clase gestora {$handlerClassName}");
				}

				$actionParameters = $action->getParameters ();
				if (!empty ($actionParameters)) {
					$parameters              = array ();
					$selectedParameterValues = array ();
					foreach ($actionParameters as $actionParameter) {
						if ($actionParameter->showExpanded ()) {
							$types           = $actionParameter->getType ();
							$formulas        = $actionParameter->getValueFormula ();
							$parameterValues = array ();
							foreach ($formulas as $expandedKey => $formula) {
								$type                             = $types [ $expandedKey ];
								$parameterValues [ $expandedKey ] = $this->evaluateParameterFormula ($type, $formula, $selectedParameterValues, $dataSourceValues);
							}
							$actionParameter->setValue ($parameterValues);
						} else {
							$parameterValue = $this->evaluateParameterFormula ($actionParameter->getType (), $actionParameter->getValueFormula (), $selectedParameterValues, $dataSourceValues);
							$actionParameter->setValue ($parameterValue);
							$selectedParameterValues [ $actionParameter->getName () ] = $parameterValue;
						}
					}
					$action->setParameters ($parameters);
				}

				$logger->emit ('INFO', "Ejecutando el método {$handlerMethodName} de la clase {$handlerClassName}");
				$this->output [ $action->getName () ] = call_user_func_array (array ($handler, $handlerMethodName), array ($action));
			}
		}

		/**
		 * @param BackgroundTask $task
		 *
		 * @throws BackgroundTaskActionException
		 */
		private function saveActions ($task) {
			$taskId = $task->getId ();
			$this->adb->pquery ('DELETE FROM vtiger_bgtasks_data_parameters WHERE taskid=?', array ($taskId));
			$this->adb->pquery ('DELETE FROM vtiger_bgtasks_data_actions WHERE taskid=?', array ($taskId));

			$actions = $task->getActions ();
			if (empty ($actions)) {
				return;
			}

			foreach ($actions as $index => $action) {
				$actionName = $action->getName ();
				$action->setTaskId ($taskId)
					->setSequence ($index + 1);
				$this->validateAction ($action);

				$this->adb->pquery (
					'INSERT INTO vtiger_bgtasks_data_actions (taskid, actionname, actiontype, actionorder) VALUES (?, ?, ?, ?)',
					array ($taskId, $actionName, $action->getType (), $action->getSequence ())
				);
				$this->saveParameters ($action);
			}
		}

		/**
		 * @param BackgroundTask $task
		 */
		private function saveFilterGroups ($task) {
			BackgroundTaskFilterManager::getInstance ($this->adb)->saveFilterGroups ($task->getId (), $task->getFilterGroups ());
		}

		/**
		 * @param BackgroundTaskAction $action
		 *
		 * @throws BackgroundTaskParameterException
		 */
		private function saveParameters ($action) {
			$parameters = $action->getParameters ();
			if (empty ($parameters)) {
				return;
			}

			$taskId     = $action->getTaskId ();
			$actionName = $action->getName ();
			foreach ($parameters as $index => $parameter) {
				$parameterName = $parameter->getName ();
				$showExpanded  = $parameter->showExpanded ();
				$types         = $parameter->getType ();
				$valueFormulas = $parameter->getValueFormula ();
				$parameter->setTaskId ($taskId)
					->setActionName ($actionName)
					->setActionType ($action->getType ())
					->setSequence ($index);
				$this->validateParameter ($parameter);

				if (!$showExpanded) {
					$types         = array ('' => $types);
					$valueFormulas = array ('' => $valueFormulas);
				}

				if (!is_array ($types)) {
					continue;
				}

				foreach ($types as $expandedKey => $type) {
					$valueFormula = isset ($valueFormulas [ $expandedKey ]) ? $valueFormulas [ $expandedKey ] : null;
					$this->adb->pquery (
						'INSERT INTO vtiger_bgtasks_data_parameters (taskid, actionname, parametername, expandedkey, actiontype, parametertype, parameterformula) VALUES (?, ?, ?, ?, ?, ?, ?)',
						array ($taskId, $actionName, $parameterName, $expandedKey, $parameter->getActionType (), $type, $valueFormula)
					);
				}
			}
		}

		/**
		 * @param string $formula
		 * @param array $selectedParameters
		 * @param array $dataSourceValues
		 *
		 * @return mixed
		 */
		private function substituteVariables ($formula, $selectedParameters, $dataSourceValues) {
			$availableVariables = SystemVariables::getAvailableVariableValues ($this->adb, $dataSourceValues);
			foreach ($availableVariables as $variableName => $variableValue) {
				$formula = str_replace ('{' . $variableName . '}', $variableValue, $formula);
			}

			if (!empty ($selectedParameters)) {
				foreach ($selectedParameters as $parameterName => $parameterValue) {
					$formula = str_replace ("[{$parameterName}]", $parameterValue, $formula);
				}
			}

			if (!empty ($dataSourceValues)) {
				$moduleName = isset ($dataSourceValues ['record_module']) ? $dataSourceValues ['record_module'] : null;
				if (empty ($moduleName) && !empty ($dataSourceValues ['record_id'])) {
					$result = $this->adb->pquery ('SELECT setype FROM vtiger_crmentity WHERE crmid=? AND deleted=0', array ($dataSourceValues ['record_id']));
					if ($this->adb->num_rows ($result) > 0) {
						$row        = $this->adb->fetchByAssoc ($result, -1, false);
						$moduleName = $row ['setype'];
					}
					DatabaseUtils::closeResult ($result);
				}
				foreach ($dataSourceValues as $parameterName => $parameterValue) {
					if (strpos ($formula, "|{$parameterName}|") !== false) {
						$resolvedValue = $this->evaluateSourceField ($moduleName, $parameterName, $parameterValue);
						$formula = str_replace ("|{$parameterName}|", $resolvedValue, $formula);
					}
				}
			}

			return $formula;
		}

		/**
		 * Evalúa fórmulas de cálculo de fecha
		 * Formato soportado: |campo_fecha| +/- X días
		 * Ejemplos: |date_start| + 7 días, |due_date| - 30 días
		 *
		 * @param string $formula
		 * @param array $dataSourceValues
		 *
		 * @return string|null
		 */
		private function evaluateDateFormula ($formula, $dataSourceValues) {
			// Primero intentar sustituir variables
			$formula = $this->substituteVariables ($formula, array (), $dataSourceValues);
			
			// Buscar patrón: |campo_fecha| +/- X días (o days)
			if (preg_match('/\|(\w+)\|\s*([+-])\s*(\d+)\s*(días|days)/i', $formula, $matches)) {
				$fieldName = $matches[1];
				$operator = $matches[2];
				$days = intval($matches[3]);
				
				// Verificar si el campo existe en los datos origen
				if (isset ($dataSourceValues [$fieldName])) {
					$dateValue = $dataSourceValues [$fieldName];
					if (!empty ($dateValue)) {
						$timestamp = strtotime ($dateValue);
						if ($timestamp !== false) {
							if ($operator === '+') {
								$timestamp += ($days * 86400); // días a segundos
							} else {
								$timestamp -= ($days * 86400);
							}
							return date ('Y-m-d', $timestamp);
						}
					}
				}
			}
			
			// Si no es fórmula de fecha reconocida, devolver la fórmula tal cual
			return $formula;
		}

		/**
		 * @param string $parameterFormula
		 *
		 * @return null|integer
		 * @throws Exception
		 */
		private function getModulesLastId ($parameterFormula) {
			$dummy = explode ('@', $parameterFormula);
			$value = null;
			$result = $this->adb->query ("SELECT MAX(crmid) FROM vtiger_crmentity WHERE deleted = 0  AND setype = '{$dummy[0]}'");
			if ($this->adb->num_rows ($result) > 0) {
				$row = $this->adb->fetchByAssoc ($result, -1, false);
				$value  = $row ['max(crmid)'];
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $value;
		}

		/**
		 * @param BackgroundTask $task
		 *
		 * @throws BackgroundTaskException
		 */
		private function validate ($task) {
			if ((empty ($task)) || (!($task instanceof BackgroundTask))) {
				throw new BackgroundTaskException (BackgroundTaskException::ERROR_BACKGROUND_TASK_EMPTY);
			}

			$task->validate ();

			$moduleName = $task->getModuleName ();
			if ((!empty ($moduleName)) && ($moduleName != '--NONE--')) {
				$result = $this->adb->pquery ('SELECT * FROM vtiger_tab WHERE name=?', array ($moduleName));
				if ($this->adb->num_rows ($result) == 0) {
					DatabaseUtils::closeResult ($result);
					$result = null;
					throw new BackgroundTaskException (BackgroundTaskException::ERROR_BACKGROUND_TASK_INVALID_MODULE_NAME);
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
			}

			$event = $task->getEvent ();
			if (!empty ($event)) {
				$result = $this->adb->pquery ('SELECT * FROM vtiger_bgtasks_cfg_events WHERE eventname=? AND scope=?', array ($event, $task->getScope ()));
				if ($this->adb->num_rows ($result) == 0) {
					DatabaseUtils::closeResult ($result);
					$result = null;
					throw new BackgroundTaskException (BackgroundTaskException::ERROR_BACKGROUND_TASK_INVALID_EVENT);
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_bgtasks_data WHERE taskname=?', array ($task->getName ()));
			if ($this->adb->num_rows ($result) == 0) {
				DatabaseUtils::closeResult ($result);
				$result = null;
				return;
			}

			$row = $this->adb->fetchByAssoc ($result, -1, false);
			DatabaseUtils::closeResult ($result);
			$result = null;
			$taskId = $task->getId ();
			if ((empty ($taskId)) || ($row ['taskid'] != $taskId)) {
				throw new BackgroundTaskException (BackgroundTaskException::ERROR_BACKGROUND_TASK_DUPLICATE_NAME);
			}
		}

		/**
		 * @param BackgroundTaskAction $action
		 *
		 * @throws BackgroundTaskActionException
		 */
		private function validateAction ($action) {
			if ((empty ($action)) || (!($action instanceof BackgroundTaskAction))) {
				throw new BackgroundTaskActionException (BackgroundTaskActionException::ERROR_BACKGROUND_TASK_ACTION_EMPTY);
			}

			$action->validate ();

			$type   = $action->getType ();
			$result = $this->adb->pquery ('SELECT * FROM vtiger_bgtasks_cfg_actions WHERE actiontype=? AND scope=?', array ($type, $action->getScope ()));
			if ($this->adb->num_rows ($result) == 0) {
				$e = new BackgroundTaskActionException (BackgroundTaskActionException::ERROR_BACKGROUND_TASK_ACTION_INVALID_TYPE);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			if (isset ($e)) {
				throw $e;
			}
		}

		/**
		 * @param BackgroundTaskParameter $parameter
		 *
		 * @throws BackgroundTaskParameterException
		 */
		private function validateParameter ($parameter) {
			if ((empty ($parameter)) || (!($parameter instanceof BackgroundTaskParameter))) {
				throw new BackgroundTaskParameterException (BackgroundTaskParameterException::ERROR_BACKGROUND_TASK_PARAMETER_EMPTY);
			}

			$parameter->validate ();

			$actionType    = $parameter->getActionType ();
			$parameterName = $parameter->getName ();
			$result        = $this->adb->pquery ('SELECT * FROM vtiger_bgtasks_cfg_parameters WHERE actiontype=? AND parametername=?', array ($actionType, $parameterName));
			if ($this->adb->num_rows ($result) == 0) {
				$e = new BackgroundTaskParameterException (BackgroundTaskParameterException::ERROR_BACKGROUND_TASK_PARAMETER_INVALID);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			if (isset ($e)) {
				throw $e;
			}
		}

		/**
		 * @param BackgroundTask $task
		 */
		public function deleteTask ($task) {
			if ((empty ($task)) || (!($task instanceof BackgroundTask))) {
				return;
			}

			$taskId = $task->getId ();
			if (empty ($taskId)) {
				return;
			}

			$moduleName = $task->getModuleName ();
			$identifier = $task->getName ();
			$this->adb->startTransaction ();
			if ((!empty ($moduleName)) && (!empty ($identifier))) {
				$this->adb->pquery ('DELETE FROM vtiger_deletedelements WHERE elementtype=? AND modulename=? AND identifier=?', array ('backgroundtask', $moduleName, $identifier));
				$this->adb->pquery ('INSERT INTO vtiger_deletedelements (elementtype, modulename, identifier, deletedon, serializedobject) VALUES (?, ?, ?, ?, ?)', array ('backgroundtask', $moduleName, $identifier, date ('Y-m-d h:i:s'), serialize ($task)));
			}
			$this->adb->pquery ('DELETE FROM vtiger_bgtasks_data_filters WHERE taskid=?', array ($taskId));
			$this->adb->pquery ('DELETE FROM vtiger_bgtasks_data_filtergroups WHERE taskid=?', array ($taskId));
			$this->adb->pquery ('DELETE FROM vtiger_bgtasks_data_parameters WHERE taskid=?', array ($taskId));
			$this->adb->pquery ('DELETE FROM vtiger_bgtasks_data_actions WHERE taskid=?', array ($taskId));
			$this->adb->pquery ('DELETE FROM vtiger_bgtasks_data WHERE taskid=?', array ($taskId));
			$this->adb->completeTransaction ();
		}

		/**
		 * @param string $moduleName
		 * @param boolean $ignoreLock
		 */
		public function deleteTasks ($moduleName, $ignoreLock = true) {
			if ((empty ($moduleName)) || (!DatabaseUtils::checkIfTableExists ($this->adb, 'vtiger_bgtasks_data'))) {
				return;
			}

			if (!$ignoreLock) {
				$whereClause = 'AND locked=0';
			} else {
				$whereClause = '';
			}

			$this->adb->startTransaction ();
			$this->adb->pquery ("DELETE FROM vtiger_bgtasks_data_filters WHERE taskid IN (SELECT taskid FROM vtiger_bgtasks_data WHERE modulename=? {$whereClause})", array ($moduleName));
			$this->adb->pquery ("DELETE FROM vtiger_bgtasks_data_filtergroups WHERE taskid IN (SELECT taskid FROM vtiger_bgtasks_data WHERE modulename=? {$whereClause})", array ($moduleName));
			$this->adb->pquery ("DELETE FROM vtiger_bgtasks_data_parameters WHERE taskid IN (SELECT taskid FROM vtiger_bgtasks_data WHERE modulename=? {$whereClause})", array ($moduleName));
			$this->adb->pquery ("DELETE FROM vtiger_bgtasks_data_actions WHERE taskid IN (SELECT taskid FROM vtiger_bgtasks_data WHERE modulename=? {$whereClause})", array ($moduleName));
			$this->adb->pquery ("DELETE FROM vtiger_bgtasks_data WHERE modulename=? {$whereClause}", array ($moduleName));
			$this->adb->completeTransaction ();
		}

		/**
		 * @param array $selectedParameterValues
		 *
		 * @return BackgroundTaskAction[]|null
		 */
		public function fetchAvailableActions ($selectedParameterValues) {
			$result = $this->adb->query ('SELECT * FROM vtiger_bgtasks_cfg_actions');
			if ($this->adb->num_rows ($result) > 0) {
				$actions = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$actions [ $row ['actiontype'] ] = BackgroundTaskAction::getInstance ()
						->setHandlerClass ($row ['handlerclass'])
						->setHandlerMethod ($row ['handlermethod'])
						->setParameters ($this->fetchAvailableParameters ($row ['actiontype'], $row ['handlerclass'], $selectedParameterValues))
						->setScope ($row ['scope'])
						->setType ($row ['actiontype']);
				}
				uasort (
					$actions,
					function (BackgroundTaskAction $actionA, BackgroundTaskAction $actionB) {
						$labelA = getTranslatedString ($actionA->getType (), 'backgroundtasks');
						$labelB = getTranslatedString ($actionB->getType (), 'backgroundtasks');
						return strcmp ($labelA, $labelB);
					}
				);
			} else {
				$actions = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $actions;
		}

		/**
		 * @return string[]|null
		 */
		public function fetchAvailableCategories () {
			$result = $this->adb->query ('SELECT * FROM vtiger_bgtasks_cfg_categories ORDER BY categoryname');
			if ($this->adb->num_rows ($result) > 0) {
				$categories = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$categories [] = $row;
				}
			} else {
				$categories = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $categories;
		}

		/**
		 * @return string[]|null
		 */
		public function fetchAvailableEvents () {
			$result = $this->adb->query ('SELECT * FROM vtiger_bgtasks_cfg_events');
			if ($this->adb->num_rows ($result) > 0) {
				$events = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$row ['label']                 = getTranslatedString ($row ['eventname'], 'backgroundtasks');
					$events [ $row ['eventname'] ] = $row;
				}
				uasort (
					$events,
					function ($eventA, $eventB) {
						return strcmp ($eventA ['label'], $eventB ['label']);
					}
				);
			} else {
				$events = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $events;
		}

		/**
		 * @param string $actionType
		 * @param array $selectedParameterValues
		 *
		 * @return BackgroundTaskAction|null
		 */
		public function fetchEmptyAction ($actionType, $selectedParameterValues) {
			if (empty ($actionType)) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_bgtasks_cfg_actions WHERE actiontype=?', array ($actionType));
			if ($this->adb->num_rows ($result) > 0) {
				$row    = $this->adb->fetchByAssoc ($result, -1, false);
				$action = BackgroundTaskAction::getInstance ()
					->setHandlerClass ($row ['handlerclass'])
					->setHandlerMethod ($row ['handlermethod'])
					->setParameters ($this->fetchAvailableParameters ($row ['actiontype'], $row ['handlerclass'], $selectedParameterValues))
					->setScope ($row ['scope'])
					->setType ($row ['actiontype']);
			} else {
				$action = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $action;
		}

		/**
		 * @param string $actionType
		 * @param string $parameterName
		 *
		 * @return BackgroundTaskParameter|null
		 */
		public function fetchEmptyParameter ($actionType, $parameterName) {
			if ((empty ($actionType)) || (empty ($parameterName))) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_bgtasks_cfg_parameters WHERE actiontype=? AND parametername=?', array ($actionType, $parameterName));
			if ($this->adb->num_rows ($result) > 0) {
				$row       = $this->adb->fetchByAssoc ($result, -1, false);
				$parameter = BackgroundTaskParameter::getInstance ()
					->setActionType ($row ['actiontype'])
					->setAvailableTypes ($this->fetchParameterAvailableTypes ($actionType, $parameterName))
					->setDefaultOptionsType ($row ['defaultoptionstype'])
					->setDefaultOptionsFormula ($row ['defaultoptionsformula'])
					->setIsMandatory ($row ['ismandatory'] == 1 ? true : false)
					->setIsMultiValued ($row ['ismultivalued'] == 1 ? true : false)
					->setName ($row ['parametername'])
					->setRefreshOnChanges ($row ['refreshonchanges'] == 1 ? true : false)
					->setSequence (intval ($row ['parameterorder']))
					->setShowExpanded ($row ['showexpanded'] == 1 ? true : false)
					->setTranslationModule ($row ['translationmodule']);
			} else {
				$parameter = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $parameter;
		}

		/**
		 * @param string $moduleName
		 * @param string $event
		 * @param string $eventInstant
		 * @param string $status
		 * @param boolean $headersOnly
		 *
		 * @return BackgroundTask[]|null
		 */
		public function fetchEventTriggeredTasks ($moduleName, $event, $eventInstant, $status = null, $headersOnly = false) {
			$whereClauses = array ('modulename=?', '`trigger`=?', 'event=?', 'eventinstant=?');
			$arguments    = array ($moduleName, BackgroundTaskInterface::TRIGGER_EVENT, $event, $eventInstant);
			if (!empty ($status)) {
				$whereClauses [] = 'taskstatus=?';
				$arguments []    = $status;
			}
			$whereClauses = join (' AND ', $whereClauses);

			$result = $this->adb->pquery ("SELECT * FROM vtiger_bgtasks_data WHERE {$whereClauses} ORDER BY taskid", $arguments);
			if ($this->adb->num_rows ($result) > 0) {
				$tasks = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$tasks [] = $this->fetchTaskById ($row ['taskid'], $headersOnly);
				}
			} else {
				$tasks = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $tasks;
		}

		/**
		 * @return BackgroundTask[]|null
		 */
		public function fetchRunnableScheduledTasks () {
			$today  = date ('Y-m-d');
			$now    = date ('Y-m-d H:i:s');
			$result = $this->adb->pquery (
				"SELECT
					*
				FROM
					vtiger_bgtasks_data
				WHERE
					taskstatus=? AND
					((
						`trigger`=? AND
						STR_TO_DATE(CONCAT(?, ' ', SEC_TO_TIME(frequency)), '%Y-%m-%d %H:%i:%s')<=? AND
						ADDTIME(STR_TO_DATE(CONCAT(?, ' ', SEC_TO_TIME(frequency)), '%Y-%m-%d %H:%i:%s'), '0:59:59')>=? AND
						(lastexecutedon IS NULL OR ADDTIME(lastexecutedon, '0:59:59') <= STR_TO_DATE(CONCAT(?, ' ', SEC_TO_TIME(frequency)), '%Y-%m-%d %H:%i:%s'))
					) OR (
						`trigger`=? AND
						(lastexecutedon IS NULL OR DATE_ADD(lastexecutedon, INTERVAL frequency SECOND)<=?)
					))",
				array (BackgroundTaskInterface::STATUS_ENABLED, BackgroundTaskInterface::TRIGGER_DAILY_SCHEDULE, $today, $now, $today, $now, $today, BackgroundTaskInterface::TRIGGER_TIMED_SCHEDULE, $now)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$tasks = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$tasks [] = $this->fetchTaskById ($row ['taskid']);
				}
			} else {
				$tasks = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $tasks;
		}

		/**
		 * @param integer $taskId
		 * @param boolean $headersOnly
		 *
		 * @return BackgroundTask|null
		 */
		public function fetchTaskById ($taskId, $headersOnly = false) {
			if (empty ($taskId)) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_bgtasks_data WHERE taskid=?', array ($taskId));
			if ($this->adb->num_rows ($result) > 0) {
				$row    = $this->adb->fetchByAssoc ($result, -1, false);
				$taskId = intval ($row ['taskid']);
				if (in_array ($row ['trigger'], array (BackgroundTaskInterface::TRIGGER_DAILY_SCHEDULE, BackgroundTaskInterface::TRIGGER_TIMED_SCHEDULE))) {
					$frequency = intval ($row ['frequency']);
				} else {
					$frequency = null;
				}

				$task = BackgroundTask::getInstance ()
					->setId ($taskId)
					->setActions (!$headersOnly ? $this->fetchActions ($taskId) : null)
					->setCategory ($row ['category'])
					->setDescription ($row ['description'])
					->setEvent ($row ['event'])
					->setEventInstant ($row ['eventinstant'])
					->setFilterGroups (!$headersOnly ? $this->fetchFilterGroups ($taskId) : null)
					->setFrequency ($frequency)
					->setLastExecutedOn ($row ['lastexecutedon'])
					->setLocked ($row ['locked'] == 1)
					->setModuleName ($row ['modulename'])
					->setName ($row ['taskname'])
					->setProtected ($row ['protected'] == 1)
					->setScope ($row ['scope'])
					->setStatus ($row ['taskstatus'])
					->setTrigger ($row ['trigger'])
					->setUrlVideo ($row ['videourl']);
			} else {
				$task = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $task;
		}

		/**
		 * @param string $taskName
		 * @param boolean $headersOnly
		 *
		 * @return BackgroundTask|null
		 */
		public function fetchTaskByName ($taskName, $headersOnly = false) {
			if (empty ($taskName)) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_bgtasks_data WHERE taskname=?', array ($taskName));
			if ($this->adb->num_rows ($result) == 0) {
				DatabaseUtils::closeResult ($result);
				$result = null;
				$result = $this->adb->pquery ('SELECT * FROM vtiger_bgtasks_data WHERE SHA1(taskname)=?', array ($taskName));
				if ($this->adb->num_rows ($result) == 0) {
					DatabaseUtils::closeResult ($result);
					$result = null;
					return null;
				}
			}

			$row    = $this->adb->fetchByAssoc ($result, -1, false);
			$taskId = intval ($row ['taskid']);
			if (in_array ($row ['trigger'], array (BackgroundTaskInterface::TRIGGER_DAILY_SCHEDULE, BackgroundTaskInterface::TRIGGER_TIMED_SCHEDULE))) {
				$frequency = intval ($row ['frequency']);
			} else {
				$frequency = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return BackgroundTask::getInstance ()
				->setId ($taskId)
				->setActions (!$headersOnly ? $this->fetchActions ($taskId) : null)
				->setCategory ($row ['category'])
				->setDescription ($row ['description'])
				->setEvent ($row ['event'])
				->setEventInstant ($row ['eventinstant'])
				->setFilterGroups (!$headersOnly ? $this->fetchFilterGroups ($taskId) : null)
				->setFrequency ($frequency)
				->setLastExecutedOn ($row ['lastexecutedon'])
				->setLocked ($row ['locked'] == 1)
				->setModuleName ($row ['modulename'])
				->setName ($row ['taskname'])
				->setProtected ($row ['protected'] == 1)
				->setScope ($row ['scope'])
				->setStatus ($row ['taskstatus'])
				->setTrigger ($row ['trigger'])
				->setUrlVideo ($row ['videourl']);
		}

		/**
		 * @param string $moduleName
		 * @param string $scope
		 * @param boolean $includeDeleted
		 * @param boolean $enabledOnly
		 *
		 * @return BackgroundTask[]|null
		 */
		public function fetchTasks ($moduleName = null, $scope = null, $includeDeleted = false, $enabledOnly = false) {
			$whereClauses = array ();
			$arguments    = array ();
			if (!empty ($moduleName)) {
				$whereClauses [] = 'modulename=?';
				$arguments []    = $moduleName;
			}
			if (!empty ($scope)) {
				$whereClauses [] = 'scope=?';
				$arguments []    = $scope;
			}
			if ($enabledOnly) {
				$whereClauses [] = 'taskstatus=?';
				$arguments []    = BackgroundTaskInterface::STATUS_ENABLED;
			}
			if (!empty ($whereClauses)) {
				$whereClause = ' AND ' . join (' AND ', $whereClauses);
			} else {
				$whereClause = '';
			}

			$result = $this->adb->pquery (
				"SELECT bt.* FROM vtiger_bgtasks_data bt WHERE modulename IS NOT NULL {$whereClause} ORDER BY bt.taskstatus DESC, bt.taskname",
				$arguments
			);
			if ($this->adb->num_rows ($result) > 0) {
				$tasks = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$tasks [] = $this->fetchTaskById ($row ['taskid']);
				}

				if ($includeDeleted) {
					$deletedTasks = $this->fetchDeletedTasks ($moduleName);
				} else {
					$deletedTasks = array ();
				}

				$tasks = array_merge ($tasks, $deletedTasks);
			} else {
				$tasks = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $tasks;
		}

		/**
		 * @param string[] $moduleNames
		 * @param string $scope
		 * @param boolean $headersOnly
		 *
		 * @return BackgroundTask[]|null
		 */
		public function fetchTasksClassifiedByScope ($moduleNames = null, $scope = null, $headersOnly = false) {
			$whereClauses = array ();
			$arguments    = array ();
			if (!empty ($moduleNames)) {
				$questionMarks   = str_repeat ('?, ', (count ($moduleNames) - 1)) . '?';
				$whereClauses [] = "modulename IN ({$questionMarks})";
				$arguments       = $moduleNames;
			}
			if (!empty ($scope)) {
				$whereClauses [] = 'scope=?';
				$arguments []    = $scope;
			}
			if (!empty ($whereClauses)) {
				$whereClause = 'WHERE ' . join (' AND ', $whereClauses);
			} else {
				$whereClause = '';
			}

			$result = $this->adb->pquery (
				"SELECT bt.* FROM vtiger_bgtasks_data bt {$whereClause} ORDER BY bt.taskstatus DESC, bt.taskname",
				$arguments
			);
			if ($this->adb->num_rows ($result) > 0) {
				$tasks = array (
					BackgroundTaskInterface::SCOPE_SYSTEM => array (),
					BackgroundTaskInterface::SCOPE_USER   => array (),
				);
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$scope              = $row ['scope'];
					$tasks [ $scope ][] = $this->fetchTaskById ($row ['taskid'], $headersOnly);
				}
			} else {
				$tasks = array (
					BackgroundTaskInterface::SCOPE_SYSTEM => null,
					BackgroundTaskInterface::SCOPE_USER   => null,
				);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $tasks;
		}

		/**
		 * @param BackgroundTask $task
		 * @param string $platform
		 * @param CRMEntity $sourceEntity
		 *
		 * @throws Exception
		 */
		public function runTask ($task, $platform, $sourceEntity = null) {
			if ((empty ($task)) || (!($task instanceof BackgroundTask))) {
				return;
			}

			$taskName = $task->getName ();
			$logger   = new Logger ('BackgroundTasksRunner', array ('appender' => array ('File' => "{$platform}/logs/backgroundtasks/{$taskName}.log")));
			$taskId   = $task->getId ();
			if (in_array ($taskId, self::$RUNNING_TASKS_IDS [ $this->adb->dbName ])) {
				return;
			}
			self::$RUNNING_TASKS_IDS [ $this->adb->dbName ][] = $taskId;

			$this->output  = array ();
			$oldDieOnError = $this->adb->dieOnError;
			$this->adb->setDieOnError (false);

			$taskScope  = $task->getScope ();
			$taskStatus = $task->getStatus ();
			try {
				$this->adb->pquery ('UPDATE vtiger_bgtasks_data SET lastexecutedon=? WHERE taskid=?', array (date ('Y-m-d H:i:s'), $taskId));
				$logger->emit ('INFO', str_repeat ('-', 120));
				$logger->emit ('INFO', "Iniciando ejecución de la tarea {$taskId}. {$taskName}");
				$this->validate ($task);
				if ($taskStatus == BackgroundTaskInterface::STATUS_DISABLED) {
					throw new Exception ('La tarea está deshabilitada');
				}

				$logger->emit ('INFO', 'Obteniendo fuente de datos');
				$dataSources = $this->getDataSources ($task, $sourceEntity);
				if ((empty ($dataSources)) && ($taskScope != BackgroundTask::SCOPE_SYSTEM)) {
					throw new Exception ('La fuente de datos suministrada no cumple con los criterios configurados', -1);
				}

				$logger->emit ('INFO', 'Ejecutando acciones');
				$actions = $task->getActions ();
				if (!empty ($dataSources)) {
					$exceptionMessages = array ();
					/** @var CRMEntity|stdClass $dataSource */
					foreach ($dataSources as $dataSource) {
						try {
							if (!isset ($dataSource->column_fields ['record_id'])) {
								$dataSource->column_fields ['record_id'] = $dataSource->id;
							}
							$this->runTaskActions ($logger, $actions, $dataSource->column_fields, $platform);
						} catch (Exception $ie) {
							$exceptionMessages [] = "{$dataSource->id} - {$ie->getMessage ()}";
						}
					}
					if (!empty ($exceptionMessages)) {
						throw new Exception (join ("\n", $exceptionMessages));
					}
				} else {
					$this->runTaskActions ($logger, $actions, null, $platform);
				}
			} catch (Exception $e) {
				if ($e->getCode () == -1) {
					$logger->emit ('INFO', $e->getMessage ());
				} else {
					$logger->emit ('ERROR', "Imposible ejecutar la tarea: {$e->getMessage ()}");
				}
			}
			$logger->emit ('INFO', 'Finalizando ejecución de la tarea');
			$this->adb->setDieOnError ($oldDieOnError);

			$key = array_search ($taskId, self::$RUNNING_TASKS_IDS [ $this->adb->dbName ]);
			if ($key !== false) {
				unset (self::$RUNNING_TASKS_IDS [ $this->adb->dbName ] [ $key ]);
			}
		}

		/**
		 * @param BackgroundTask $task
		 * @param boolean $ignoreLock
		 *
		 * @return BackgroundTask
		 * @throws BackgroundTaskException
		 */
		public function saveTask ($task, $ignoreLock = true) {
			$this->validate ($task);

			$isDeleted = $task->isDeleted ();
			if ($isDeleted) {
				return $task;
			}

			$task     = $this->fixTask ($task);
			$taskId   = $task->getId ();
			$category = $task->getCategory ();
			$data     = $this->fetchTaskData ($taskId);
			if (!empty ($data)) {
				$isLocked = ($data ['locked'] == 1);
			} else {
				$isLocked = false;
				$taskId   = null;
			}

			$scope      = $task->getScope ();
			$moduleName = ($scope == BackgroundTask::SCOPE_SYSTEM) && ($task->getModuleName () == '--NONE--') ? null : $task->getModuleName ();
			$this->adb->startTransaction ();
			if (empty ($taskId)) {
				$this->adb->pquery (
					'INSERT INTO vtiger_bgtasks_data (taskname, description, videourl, category, scope, modulename, `trigger`, event, eventinstant, taskstatus, frequency, locked, protected) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
					array ($task->getName (), $task->getDescription (), $task->getUrlVideo (), !empty ($category) ? $category : null, $task->getScope (), $moduleName, $task->getTrigger (), $task->getEvent (), $task->getEventInstant (), $task->getStatus (), $task->getFrequency (), $task->isLocked (), $task->isProtected ())
				);
				$taskId = intval ($this->adb->getLastInsertID ());
				$task->setId ($taskId);
			} else if (($ignoreLock) || (!$isLocked)) {
				$this->adb->pquery (
					'UPDATE vtiger_bgtasks_data SET taskname=?, description=?, videourl=?, category=?, scope=?, modulename=?, `trigger`=?, event=?, eventinstant=?, taskstatus=?, frequency=?, locked=?, protected=? WHERE taskid=?',
					array ($task->getName (), $task->getDescription (),$task->getUrlVideo (), !empty ($category) ? $category : null, $task->getScope (), $moduleName, $task->getTrigger (), $task->getEvent (), $task->getEventInstant (), $task->getStatus (), $task->getFrequency (), $task->isLocked (), $task->isProtected (), $taskId)
				);
			}

			if (($ignoreLock) || (!$isLocked)) {
				$this->saveActions ($task);
				$this->saveFilterGroups ($task);
			}
			$this->adb->completeTransaction ();
			return $task;
		}

		/**
		 * @param string $moduleName
		 * @param BackgroundTask[] $tasks
		 * @param boolean $ignoreLock
		 */
		public function saveTasks ($moduleName, $tasks, $ignoreLock = true) {
			if (empty ($moduleName)) {
				return;
			}

			$this->adb->startTransaction ();
			if (empty ($tasks)) {
				$this->deleteTasks ($moduleName, $ignoreLock);
				return;
			}

			$processedTaskIds = array ();
			foreach ($tasks as $task) {
				if ($task->isDeleted ()) {
					continue;
				}

				$task->setModuleName ($moduleName);
				$this->saveTask ($task, $ignoreLock);
				$processedTaskIds [] = $task->getId ();
			}
			if (empty ($processedTaskIds)) {
				return;
			}

			if (!$ignoreLock) {
				$whereClause = 'AND locked=0';
			} else {
				$whereClause = '';
			}

			$questionMarks = str_repeat ('?, ', (count ($processedTaskIds) - 1)) . '?';
			$this->adb->pquery (
				"DELETE FROM vtiger_bgtasks_data_filters WHERE taskid IN (SELECT taskid FROM vtiger_bgtasks_data WHERE modulename=? {$whereClause} AND taskid NOT IN ({$questionMarks}))",
				array_merge (array ($moduleName), $processedTaskIds)
			);
			$this->adb->pquery (
				"DELETE FROM vtiger_bgtasks_data_filtergroups WHERE taskid IN (SELECT taskid FROM vtiger_bgtasks_data WHERE modulename=? {$whereClause} AND taskid NOT IN ({$questionMarks}))",
				array_merge (array ($moduleName), $processedTaskIds)
			);
			$this->adb->pquery (
				"DELETE FROM vtiger_bgtasks_data_parameters WHERE taskid IN (SELECT taskid FROM vtiger_bgtasks_data WHERE modulename=? {$whereClause} AND taskid NOT IN ({$questionMarks}))",
				array_merge (array ($moduleName), $processedTaskIds)
			);
			$this->adb->pquery (
				"DELETE FROM vtiger_bgtasks_data_actions WHERE taskid IN (SELECT taskid FROM vtiger_bgtasks_data WHERE modulename=? {$whereClause} AND taskid NOT IN ({$questionMarks}))",
				array_merge (array ($moduleName), $processedTaskIds)
			);
			$this->adb->pquery ("DELETE FROM vtiger_bgtasks_data WHERE modulename=? {$whereClause} AND taskid NOT IN ({$questionMarks})", array_merge (array ($moduleName), $processedTaskIds));
			$this->adb->completeTransaction ();
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return BackgroundTaskManager
		 */
		public static function getInstance (PearDatabase $adb) {
			if (self::$RUNNING_TASKS_IDS === null) {
				self::$RUNNING_TASKS_IDS = array ();
			}
			if (!isset (self::$RUNNING_TASKS_IDS [ $adb->dbName ])) {
				self::$RUNNING_TASKS_IDS [ $adb->dbName ] = array ();
			}
			if (self::$INSTANCES === null) {
				self::$INSTANCES = array ();
			}
			if (!isset (self::$INSTANCES [ $adb->dbName ])) {
				self::$INSTANCES [ $adb->dbName ] = new self ($adb);
			}
			return self::$INSTANCES [ $adb->dbName ];
		}

	}
