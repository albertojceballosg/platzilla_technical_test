<?php
	require_once ('include/platzilla/Exceptions/BackgroundTaskEventException.php');
	require_once ('include/platzilla/Objects/BackgroundTaskInterface.php');

	class BackgroundTaskEvent {
		/** @var string */
		private $description;

		/** @var string */
		private $name;

		/** @var string */
		private $scope;

		/**
		 * @return string
		 */
		public function getDescription () {
			return $this->description;
		}

		/**
		 * @return string
		 */
		public function getName () {
			return $this->name;
		}

		/**
		 * @return string
		 */
		public function getScope () {
			return $this->scope;
		}

		/**
		 * @param string $description
		 *
		 * @return BackgroundTaskEvent
		 */
		public function setDescription ($description) {
			$this->description = $description;
			return $this;
		}

		/**
		 * @param string $name
		 *
		 * @return BackgroundTaskEvent
		 */
		public function setName ($name) {
			$this->name = $name;
			return $this;
		}

		/**
		 * @param string $scope
		 *
		 * @return BackgroundTaskEvent
		 */
		public function setScope ($scope) {
			if (in_array ($scope, array (BackgroundTaskInterface::SCOPE_SYSTEM, BackgroundTaskInterface::SCOPE_USER))) {
				$this->scope = $scope;
			}
			return $this;
		}

		/**
		 * @param BackgroundTaskEvent $category
		 */
		public function copyValuesFrom ($category) {
			if ((empty ($category)) || (!($category instanceof BackgroundTaskEvent))) {
				return;
			}

			$this->description = $category->getDescription ();
			$this->name        = $category->getName ();
			$this->scope       = $category->getScope ();
		}

		/**
		 * @return BackgroundTaskEvent
		 */
		public function duplicate () {
			$this->validate ();

			$object = new self ();
			return $object->setDescription ($this->description)
				->setName ($this->name)
				->setScope ($this->scope);
		}

		/**
		 * @param BackgroundTaskEvent $category
		 *
		 * @return boolean
		 */
		public function isEqualTo ($category) {
			if (
				(empty ($category)) ||
				(!($category instanceof BackgroundTaskEvent)) ||
				($this->description != $category->getDescription ()) ||
				($this->name != $category->getName ()) ||
				($this->scope != $category->getScope ())
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * @throws BackgroundTaskEventException
		 */
		public function validate () {
			if (empty ($this->description)) {
				throw new BackgroundTaskEventException (BackgroundTaskEventException::ERROR_BACKGROUND_TASK_EVENT_EMPTY_DESCRIPTION);
			} else if (empty ($this->name)) {
				throw new BackgroundTaskEventException (BackgroundTaskEventException::ERROR_BACKGROUND_TASK_EVENT_EMPTY_NAME);
			} else if (empty ($this->scope)) {
				throw new BackgroundTaskEventException (BackgroundTaskEventException::ERROR_BACKGROUND_TASK_EVENT_EMPTY_SCOPE);
			}
		}

		/**
		 * @return BackgroundTaskEvent
		 */
		public static function getInstance () {
			return new self ();
		}

	}
