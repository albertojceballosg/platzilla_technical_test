<?php
	require_once ('include/platzilla/Configuration/BackgroundTaskConfiguration.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');

	class BackgroundTaskConfigurationManager {
		/** @var BackgroundTaskConfigurationManager[]|null */
		private static $INSTANCES = null;

		/** @var PearDatabase */
		private $adb;

		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
		}

		/**
		 * @param BackgroundTaskParameterConfiguration $parameter
		 */
		private function createMissingDataParameter ($parameter) {
			if (empty ($parameter)) {
				return;
			}
			$actionType    = $parameter->getActionType ();
			$parameterName = $parameter->getName ();
			$result        = $this->adb->pquery (
				'SELECT
					*
				FROM
					vtiger_bgtasks_data_actions da
				WHERE
					da.actiontype=? AND
					NOT EXISTS (SELECT * FROM vtiger_bgtasks_data_parameters dp WHERE dp.taskid=da.taskid AND dp.parametername=?)',
				array ($actionType, $parameterName)
			);
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$this->adb->pquery (
						'INSERT INTO vtiger_bgtasks_data_parameters (taskid, actionname, parametername, expandedkey, actiontype, parametertype, parameterformula) VALUES (?, ?, ?, ?, ?, ?, ?)',
						array ($row ['taskid'], $row ['actionname'], $parameterName, '', $actionType, null, null)
					);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
		}

		/**
		 * @param string $actionType
		 * @param string $parameterName
		 *
		 * @return string[]|null
		 */
		private function fetchParameterOptions ($actionType, $parameterName) {
			if ((empty ($actionType)) || (empty ($parameterName))) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_bgtasks_cfg_parameteroptions WHERE actiontype=? AND parametername=? ORDER BY parametertype', array ($actionType, $parameterName));
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
		 * @param string $actionType
		 *
		 * @return BackgroundTaskParameterConfiguration[]|null
		 */
		private function fetchParameters ($actionType) {
			if (empty ($actionType)) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_bgtasks_cfg_parameters WHERE actiontype=? ORDER BY parameterorder', array ($actionType));
			if ($this->adb->num_rows ($result) > 0) {
				$parameters = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$parameters [] = BackgroundTaskParameterConfiguration::getInstance ()
						->setActionType ($row ['actiontype'])
						->setDefaultOptionsFormula ($row ['defaultoptionsformula'])
						->setDefaultOptionsType ($row ['defaultoptionstype'])
						->setIsMandatory ($row ['ismandatory'] == 1)
						->setIsMultiValued ($row ['ismultivalued'] == 1)
						->setOptions ($this->fetchParameterOptions ($row ['actiontype'], $row ['parametername']))
						->setOrder (intval ($row ['parameterorder']))
						->setName ($row ['parametername'])
						->setRefreshOnChanges ($row ['refreshonchanges'] == 1)
						->setShowExpanded ($row ['showexpanded'] == 1)
						->setTranslationModule ($row ['translationmodule']);
				}
			} else {
				$parameters = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $parameters;
		}

		/**
		 * @param BackgroundTaskConfiguration $configuration
		 */
		private function saveActions ($configuration) {
			$actions = $configuration->getActions ();
			if (empty ($actions)) {
				return;
			}

			$processedActionTypes = array ();
			foreach ($actions as $action) {
				$this->saveAction ($action);
				$processedActionTypes [] = $action->getType ();
			}

			if (!empty ($processedActionTypes)) {
				$questionMarks = str_repeat ('?, ', (count ($processedActionTypes) - 1)) . '?';
				$this->adb->pquery (
					"DELETE FROM vtiger_bgtasks_data_parameters WHERE actiontype NOT IN ({$questionMarks})",
					$processedActionTypes
				);
				$this->adb->pquery (
					"DELETE FROM vtiger_bgtasks_data_actions WHERE actiontype NOT IN ({$questionMarks})",
					$processedActionTypes
				);
				$this->adb->query (
					'DELETE
						d
					FROM
						vtiger_bgtasks_data d
						INNER JOIN (SELECT d2.taskid FROM vtiger_bgtasks_data d2 LEFT JOIN vtiger_bgtasks_data_actions a ON a.taskid=d2.taskid GROUP BY d2.taskid HAVING COUNT(a.actionname)=0 ) AS todelete ON todelete.taskid=d.taskid'
				);

				$this->adb->pquery (
					"DELETE FROM vtiger_bgtasks_cfg_parameteroptions WHERE actiontype NOT IN ({$questionMarks})",
					$processedActionTypes
				);
				$this->adb->pquery (
					"DELETE FROM vtiger_bgtasks_cfg_parameters WHERE actiontype NOT IN ({$questionMarks})",
					$processedActionTypes
				);
				$this->adb->pquery (
					"DELETE FROM vtiger_bgtasks_cfg_actions WHERE actiontype NOT IN ({$questionMarks})",
					$processedActionTypes
				);
			}
		}

		/**
		 * @param BackgroundTaskConfiguration $configuration
		 */
		private function saveCategories ($configuration) {
			$categories = $configuration->getCategories ();
			if (empty ($categories)) {
				return;
			}

			$processedCategories = array ();
			foreach ($categories as $category) {
				$this->saveCategory ($category);
				$processedCategories [] = $category->getName ();
			}

			if (!empty ($processedCategories)) {
				$questionMarks = str_repeat ('?, ', (count ($processedCategories) - 1)) . '?';
				$this->adb->pquery (
					"DELETE FROM vtiger_bgtasks_cfg_categories WHERE categoryname NOT IN ({$questionMarks})",
					$processedCategories
				);
			}
		}

		/**
		 * @param BackgroundTaskConfiguration $configuration
		 */
		private function saveEvents ($configuration) {
			$events = $configuration->getEvents ();
			if (empty ($events)) {
				return;
			}

			$processedEvents = array ();
			foreach ($events as $event) {
				$this->saveEvent ($event);
				$processedEvents [] = $event->getName ();
			}

			if (!empty ($processedEvents)) {
				$questionMarks = str_repeat ('?, ', (count ($processedEvents) - 1)) . '?';
				$this->adb->pquery (
					"UPDATE vtiger_bgtasks_data SET event=NULL WHERE event IS NOT NULL AND event NOT IN ({$questionMarks})",
					$processedEvents
				);

				$this->adb->pquery (
					"DELETE FROM vtiger_bgtasks_cfg_events WHERE eventname NOT IN ({$questionMarks})",
					$processedEvents
				);
			}
		}

		/**
		 * @param BackgroundTaskActionConfiguration $action
		 */
		private function saveParameters ($action) {
			$parameters = $action->getParameters ();
			if (empty ($parameters)) {
				return;
			}

			$processedParameterNames = array ();
			foreach ($parameters as $parameter) {
				$this->saveParameter ($parameter);
				$processedParameterNames [] = $parameter->getName ();
			}

			if (!empty ($processedParameterNames)) {
				$questionMarks = str_repeat ('?, ', (count ($processedParameterNames) - 1)) . '?';
				$this->adb->pquery (
					"DELETE FROM vtiger_bgtasks_cfg_parameteroptions WHERE actiontype=? AND parametername NOT IN ({$questionMarks})",
					array_merge (array ($action->getType ()), $processedParameterNames)
				);
				$this->adb->pquery (
					"DELETE FROM vtiger_bgtasks_cfg_parameters WHERE actiontype=? AND parametername NOT IN ({$questionMarks})",
					array_merge (array ($action->getType ()), $processedParameterNames)
				);
			}
		}

		/**
		 * @param BackgroundTaskParameterConfiguration $parameter
		 */
		private function saveParameterOptions ($parameter) {
			if ((empty ($parameter)) || (!($parameter instanceof BackgroundTaskParameterConfiguration))) {
				return;
			}

			$actionType    = $parameter->getActionType ();
			$parameterName = $parameter->getName ();
			$options       = $parameter->getOptions ();
			if (empty ($options)) {
				$this->adb->pquery ('DELETE FROM vtiger_bgtasks_cfg_parameteroptions WHERE actiontype=? AND parametername=?', array ($actionType, $parameterName));
				return;
			}

			foreach ($options as $option) {
				$result = $this->adb->pquery ('SELECT * FROM vtiger_bgtasks_cfg_parameteroptions WHERE actiontype=? AND parametername=? AND parametertype=?', array ($actionType, $parameterName, $option));
				if ($this->adb->num_rows ($result) == 0) {
					$this->adb->pquery (
						'INSERT INTO vtiger_bgtasks_cfg_parameteroptions (actiontype, parametername, parametertype) VALUES (?, ?, ?)',
						array ($actionType, $parameterName, $option)
					);
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
			}

			$questionMarks = str_repeat ('?, ', (count ($options) - 1)) . '?';
			$this->adb->pquery (
				"DELETE FROM vtiger_bgtasks_cfg_parameteroptions WHERE actiontype=? AND parametername=? AND parametertype NOT IN ({$questionMarks})",
				array_merge (array ($actionType, $parameterName), $options)
			);
		}

		/**
		 * @param BackgroundTaskConfiguration $configuration
		 *
		 * @throws BackgroundTaskConfigurationException
		 */
		private function validate ($configuration) {
			if ((empty ($configuration)) || (!($configuration instanceof BackgroundTaskConfiguration))) {
				throw new BackgroundTaskConfigurationException (BackgroundTaskConfigurationException::ERROR_BACKGROUND_TASK_CONFIGURATION_EMPTY);
			}
			$configuration->validate ();
		}

		/**
		 * @return BackgroundTaskActionConfiguration[]|null
		 */
		public function fetchActions () {
			$result = $this->adb->query ('SELECT * FROM vtiger_bgtasks_cfg_actions ORDER BY actiontype');
			if ($this->adb->num_rows ($result) > 0) {
				$actions = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$actions [] = BackgroundTaskActionConfiguration::getInstance ()
						->setHandlerClass ($row ['handlerclass'])
						->setHandlerMethod ($row ['handlermethod'])
						->setParameters ($this->fetchParameters ($row ['actiontype']))
						->setScope ($row ['scope'])
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
		 * @return BackgroundTaskCategory[]|null
		 */
		public function fetchCategories () {
			$result = $this->adb->query ('SELECT * FROM vtiger_bgtasks_cfg_categories ORDER BY categoryname');
			if ($this->adb->num_rows ($result) > 0) {
				$categories = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$categories [] = BackgroundTaskCategory::getInstance ()
						->setDescription ($row ['description'])
						->setName ($row ['categoryname']);
				}
			} else {
				$categories = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $categories;
		}

		/**
		 * @return BackgroundTaskConfiguration
		 */
		public function fetchConfiguration () {
			return BackgroundTaskConfiguration::getInstance ()
				->setActions ($this->fetchActions ())
				->setCategories ($this->fetchCategories ())
				->setEvents ($this->fetchEvents ());
		}

		/**
		 * @return BackgroundTaskEvent[]|null
		 */
		public function fetchEvents () {
			$result = $this->adb->query ('SELECT * FROM vtiger_bgtasks_cfg_events ORDER BY eventname');
			if ($this->adb->num_rows ($result) > 0) {
				$events = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$events [] = BackgroundTaskEvent::getInstance ()
						->setDescription ($row ['description'])
						->setName ($row ['eventname'])
						->setScope ($row ['scope']);
				}
			} else {
				$events = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $events;
		}

		/**
		 * @param BackgroundTaskActionConfiguration $action
		 *
		 * @return BackgroundTaskActionConfiguration
		 */
		public function saveAction ($action) {
			if ((empty ($action)) || (!($action instanceof BackgroundTaskActionConfiguration))) {
				return null;
			}

			$action->validate ();

			$type   = $action->getType ();
			$result = $this->adb->pquery ('SELECT * FROM vtiger_bgtasks_cfg_actions WHERE actiontype=?', array ($type));
			if ($this->adb->num_rows ($result) == 0) {
				$this->adb->pquery (
					'INSERT INTO vtiger_bgtasks_cfg_actions (actiontype, scope, handlerclass, handlermethod) VALUES (?, ?, ?, ?)',
					array ($type, $action->getScope (), $action->getHandlerClass (), $action->getHandlerMethod ())
				);
			} else {
				$this->adb->pquery (
					'UPDATE vtiger_bgtasks_cfg_actions SET scope=?, handlerclass=?, handlermethod=? WHERE actiontype=?',
					array ($action->getScope (), $action->getHandlerClass (), $action->getHandlerMethod (), $type)
				);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			$this->saveParameters ($action);
			return $action;
		}

		/**
		 * @param BackgroundTaskCategory $category
		 *
		 * @return BackgroundTaskCategory|null
		 * @throws BackgroundTaskCategoryException
		 */
		public function saveCategory ($category) {
			if ((empty ($category)) || (!($category instanceof BackgroundTaskCategory))) {
				return null;
			}

			$category->validate ();

			$name   = $category->getName ();
			$result = $this->adb->pquery ('SELECT * FROM vtiger_bgtasks_cfg_categories WHERE categoryname=?', array ($name));
			if ($this->adb->num_rows ($result) == 0) {
				$this->adb->pquery ('INSERT INTO vtiger_bgtasks_cfg_categories (categoryname, description) VALUES (?, ?)', array ($name, $category->getDescription ()));
			} else {
				$this->adb->pquery ('UPDATE vtiger_bgtasks_cfg_categories SET description=? WHERE categoryname=?', array ($category->getDescription (), $name));
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $category;
		}

		/**
		 * @param BackgroundTaskEvent $event
		 *
		 * @return BackgroundTaskEvent
		 */
		public function saveEvent ($event) {
			if ((empty ($event)) || (!($event instanceof BackgroundTaskEvent))) {
				return null;
			}

			$event->validate ();

			$name   = $event->getName ();
			$result = $this->adb->pquery ('SELECT * FROM vtiger_bgtasks_cfg_events WHERE eventname=?', array ($name));
			if ($this->adb->num_rows ($result) == 0) {
				$this->adb->pquery ('INSERT INTO vtiger_bgtasks_cfg_events (eventname, description, scope) VALUES (?, ?, ?)', array ($name, $event->getDescription (), $event->getScope ()));
			} else {
				$this->adb->pquery ('UPDATE vtiger_bgtasks_cfg_events SET description=?, scope=? WHERE eventname=?', array ($event->getDescription (), $event->getScope (), $name));
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $event;
		}

		/**
		 * @param BackgroundTaskParameterConfiguration $parameter
		 */
		public function saveParameter ($parameter) {
			if ((empty ($parameter)) || (!($parameter instanceof BackgroundTaskParameterConfiguration))) {
				return;
			}

			$actionType    = $parameter->getActionType ();
			$parameterName = $parameter->getName ();
			$result        = $this->adb->pquery ('SELECT * FROM vtiger_bgtasks_cfg_parameters WHERE actiontype=? AND parametername=?', array ($actionType, $parameterName));
			if ($this->adb->num_rows ($result) == 0) {
				$this->adb->pquery (
					'INSERT INTO vtiger_bgtasks_cfg_parameters (actiontype, parametername, parameterorder, ismultivalued, ismandatory, refreshonchanges, showexpanded, defaultoptionstype, defaultoptionsformula, translationmodule) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
					array ($actionType, $parameterName, $parameter->getOrder (), $parameter->isMultiValued (), $parameter->isMandatory (), $parameter->refreshOnChanges (), $parameter->showExpanded (), $parameter->getDefaultOptionsType (), $parameter->getDefaultOptionsFormula (), $parameter->getTranslationModule ())
				);
			} else {
				$this->adb->pquery (
					'UPDATE vtiger_bgtasks_cfg_parameters SET parameterorder=?, ismultivalued=?, ismandatory=?, refreshonchanges=?, showexpanded=?, defaultoptionstype=?, defaultoptionsformula=?, translationmodule=? WHERE actiontype=? AND parametername=?',
					array ($parameter->getOrder (), $parameter->isMultiValued (), $parameter->isMandatory (), $parameter->refreshOnChanges (), $parameter->showExpanded (), $parameter->getDefaultOptionsType (), $parameter->getDefaultOptionsFormula (), $parameter->getTranslationModule (), $actionType, $parameterName)
				);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			$this->createMissingDataParameter ($parameter);
			$this->saveParameterOptions ($parameter);
		}

		/**
		 * @param BackgroundTaskConfiguration $configuration
		 *
		 * @throws BackgroundTaskConfigurationException
		 */
		public function saveConfiguration ($configuration) {
			$this->validate ($configuration);
			$this->adb->startTransaction ();
			$this->saveActions ($configuration);
			$this->saveCategories ($configuration);
			$this->saveEvents ($configuration);
			$this->adb->completeTransaction ();
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return BackgroundTaskConfigurationManager
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
