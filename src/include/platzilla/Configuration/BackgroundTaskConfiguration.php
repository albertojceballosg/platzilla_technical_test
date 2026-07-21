<?php
	require_once ('include/platzilla/Configuration/BackgroundTaskActionConfiguration.php');
	require_once ('include/platzilla/Configuration/BackgroundTaskCategory.php');
	require_once ('include/platzilla/Configuration/BackgroundTaskEvent.php');
	require_once ('include/platzilla/Exceptions/BackgroundTaskConfigurationException.php');
	require_once ('include/platzilla/Utils/MiscellaneousUtils.php');

	class BackgroundTaskConfiguration {
		/** @var BackgroundTaskActionConfiguration[] */
		private $actions;

		/** @var BackgroundTaskCategory[] */
		private $categories;

		/** @var BackgroundTaskEvent[] */
		private $events;

		/**
		 * @param BackgroundTaskActionConfiguration[] $sourceActions
		 */
		private function copyActions ($sourceActions) {
			$actions = array ();
			foreach ($sourceActions as $sourceAction) {
				$found = false;
				foreach ($this->actions as $targetAction) {
					if ($sourceAction->getType () != $targetAction->getType ()) {
						continue;
					} else if (!$targetAction->isEqualTo ($sourceAction)) {
						$targetAction->copyValuesFrom ($sourceAction);
					}
					$actions [] = $targetAction;
					$found      = true;
					break;
				}
				if (!$found) {
					$actions [] = $sourceAction->duplicate ();
				}
			}
			$this->actions = $actions;
		}

		/**
		 * @param BackgroundTaskConfiguration $configuration
		 */
		private function copyActionsFrom ($configuration) {
			$sourceActions = $configuration->getActions ();
			if ((empty ($sourceActions)) && (empty ($this->actions))) {
				return;
			}

			if (empty ($sourceActions)) {
				$this->actions = null;
			} else if (empty ($this->actions)) {
				$actions = array ();
				foreach ($sourceActions as $sourceAction) {
					$actions [] = $sourceAction->duplicate ();
				}
				$this->actions = $actions;
			} else {
				$this->copyActions ($sourceActions);
			}
		}

		/**
		 * @return BackgroundTaskActionConfiguration[]|null
		 */
		private function duplicateActions () {
			if (empty ($this->actions)) {
				return null;
			}
			$actions = array ();
			foreach ($this->actions as $action) {
				$actions [] = $action->duplicate ();
			}
			return $actions;
		}

		/**
		 * @throws BackgroundTaskActionConfigurationException
		 * @throws BackgroundTaskConfigurationException
		 */
		private function validateActions () {
			if (empty ($this->actions)) {
				throw new BackgroundTaskConfigurationException (BackgroundTaskConfigurationException::ERROR_BACKGROUND_TASK_CONFIGURATION_EMPTY_ACTIONS);
			}

			foreach ($this->actions as $action) {
				if (!($action instanceof BackgroundTaskActionConfiguration)) {
					throw new BackgroundTaskConfigurationException (BackgroundTaskConfigurationException::ERROR_BACKGROUND_TASK_CONFIGURATION_INVALID_ACTION);
				} else {
					$action->validate ();
				}
			}
		}

		/**
		 * @param BackgroundTaskCategory[] $sourceCategories
		 */
		private function copyCategories ($sourceCategories) {
			$categories = array ();
			foreach ($sourceCategories as $sourceCategory) {
				$found = false;
				foreach ($this->categories as $targetCategory) {
					if ($sourceCategory->getName () != $targetCategory->getName ()) {
						continue;
					} else if (!$targetCategory->isEqualTo ($sourceCategory)) {
						$targetCategory->copyValuesFrom ($sourceCategory);
					}
					$categories [] = $targetCategory;
					$found         = true;
					break;
				}
				if (!$found) {
					$categories [] = $sourceCategory->duplicate ();
				}
			}
			$this->categories = $categories;
		}

		/**
		 * @param BackgroundTaskConfiguration $configuration
		 */
		private function copyCategoriesFrom ($configuration) {
			$sourceCategories = $configuration->getCategories ();
			if ((empty ($sourceCategories)) && (empty ($this->categories))) {
				return;
			}

			if (empty ($sourceCategories)) {
				$this->categories = null;
			} else if (empty ($this->categories)) {
				$categories = array ();
				foreach ($sourceCategories as $sourceCategory) {
					$categories [] = $sourceCategory->duplicate ();
				}
				$this->categories = $categories;
			} else {
				$this->copyCategories ($sourceCategories);
			}
		}

		/**
		 * @return BackgroundTaskCategory[]|null
		 */
		private function duplicateCategories () {
			if (empty ($this->categories)) {
				return null;
			}
			$categories = array ();
			foreach ($this->categories as $category) {
				$categories [] = $category->duplicate ();
			}
			return $categories;
		}

		/**
		 * @throws BackgroundTaskCategoryException
		 * @throws BackgroundTaskConfigurationException
		 */
		private function validateCategories () {
			if (empty ($this->categories)) {
				return;
			}

			foreach ($this->categories as $category) {
				if (!($category instanceof BackgroundTaskCategory)) {
					throw new BackgroundTaskConfigurationException (BackgroundTaskConfigurationException::ERROR_BACKGROUND_TASK_CONFIGURATION_INVALID_CATEGORY);
				} else {
					$category->validate ();
				}
			}
		}

		/**
		 * @param BackgroundTaskEvent[] $sourceEvents
		 */
		private function copyEvents ($sourceEvents) {
			$events = array ();
			foreach ($sourceEvents as $sourceEvent) {
				$found = false;
				foreach ($this->events as $targetEvent) {
					if ($sourceEvent->getName () != $targetEvent->getName ()) {
						continue;
					} else if (!$targetEvent->isEqualTo ($sourceEvent)) {
						$targetEvent->copyValuesFrom ($sourceEvent);
					}
					$events [] = $targetEvent;
					$found     = true;
					break;
				}
				if (!$found) {
					$events [] = $sourceEvent->duplicate ();
				}
			}
			$this->events = $events;
		}

		/**
		 * @param BackgroundTaskConfiguration $configuration
		 */
		private function copyEventsFrom ($configuration) {
			$sourceEvents = $configuration->getEvents ();
			if ((empty ($sourceEvents)) && (empty ($this->events))) {
				return;
			}

			if (empty ($sourceEvents)) {
				$this->events = null;
			} else if (empty ($this->events)) {
				$events = array ();
				foreach ($sourceEvents as $sourceEvent) {
					$events [] = $sourceEvent->duplicate ();
				}
				$this->events = $events;
			} else {
				$this->copyEvents ($sourceEvents);
			}
		}

		/**
		 * @return BackgroundTaskEvent[]|null
		 */
		private function duplicateEvents () {
			if (empty ($this->events)) {
				return null;
			}
			$events = array ();
			foreach ($this->events as $event) {
				$events [] = $event->duplicate ();
			}
			return $events;
		}

		/**
		 * @throws BackgroundTaskConfigurationException
		 * @throws BackgroundTaskEventException
		 */
		private function validateEvents () {
			if (empty ($this->events)) {
				throw new BackgroundTaskConfigurationException (BackgroundTaskConfigurationException::ERROR_BACKGROUND_TASK_CONFIGURATION_EMPTY_EVENTS);
			}

			foreach ($this->events as $event) {
				if (!($event instanceof BackgroundTaskEvent)) {
					throw new BackgroundTaskConfigurationException (BackgroundTaskConfigurationException::ERROR_BACKGROUND_TASK_CONFIGURATION_INVALID_EVENT);
				} else {
					$event->validate ();
				}
			}
		}

		/**
		 * @return BackgroundTaskActionConfiguration[]
		 */
		public function getActions () {
			return $this->actions;
		}

		/**
		 * @return BackgroundTaskCategory[]
		 */
		public function getCategories () {
			return $this->categories;
		}

		/**
		 * @return BackgroundTaskEvent[]
		 */
		public function getEvents () {
			return $this->events;
		}

		/**
		 * @param BackgroundTaskActionConfiguration[] $actions
		 *
		 * @return BackgroundTaskConfiguration
		 */
		public function setActions ($actions) {
			if (($actions === null) || ((is_array ($actions)) && (!empty ($actions)))) {
				$this->actions = $actions;
			}
			return $this;
		}

		/**
		 * @param BackgroundTaskCategory[] $categories
		 *
		 * @return BackgroundTaskConfiguration
		 */
		public function setCategories ($categories) {
			if (($categories === null) || ((is_array ($categories)) && (!empty ($categories)))) {
				$this->categories = $categories;
			}
			return $this;
		}

		/**
		 * @param BackgroundTaskEvent[] $events
		 *
		 * @return BackgroundTaskConfiguration
		 */
		public function setEvents ($events) {
			if (($events === null) || ((is_array ($events)) && (!empty ($events)))) {
				$this->events = $events;
			}
			return $this;
		}

		/**
		 * @param BackgroundTaskConfiguration $configuration
		 */
		public function copyValuesFrom ($configuration) {
			if ((empty ($configuration)) || (!($configuration instanceof BackgroundTaskConfiguration))) {
				return;
			}

			$this->copyActionsFrom ($configuration);
			$this->copyCategoriesFrom ($configuration);
			$this->copyEventsFrom ($configuration);
		}

		/**
		 * @return BackgroundTaskConfiguration
		 * @throws BackgroundTaskConfigurationException
		 */
		public function duplicate () {
			$this->validate ();

			$object = new self ();
			return $object->setActions ($this->duplicateActions ())
				->setCategories ($this->duplicateCategories ())
				->setEvents ($this->duplicateEvents ());
		}

		/**
		 * @param BackgroundTaskConfiguration $configuration
		 *
		 * @return boolean
		 */
		public function isEqualTo ($configuration) {
			if (
				(empty ($configuration)) ||
				(!($configuration instanceof BackgroundTaskConfiguration)) ||
				(!MiscellaneousUtils::areObjectArraysEqual ($this->actions, $configuration->getActions ())) ||
				(!MiscellaneousUtils::areObjectArraysEqual ($this->categories, $configuration->getCategories ())) ||
				(!MiscellaneousUtils::areObjectArraysEqual ($this->events, $configuration->getEvents ()))
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * @throws BackgroundTaskConfigurationException
		 */
		public function validate () {
			$this->validateActions ();
			$this->validateCategories ();
			$this->validateEvents ();
		}

		/**
		 * @return BackgroundTaskConfiguration
		 */
		public static function getInstance () {
			return new self ();
		}

	}
