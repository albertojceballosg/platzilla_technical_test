<?php
	require_once ('include/platzilla/Exceptions/BackgroundTaskCategoryException.php');

	class BackgroundTaskCategory {
		/** @var string */
		private $description;

		/** @var string */
		private $name;

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
		 * @param string $description
		 *
		 * @return BackgroundTaskCategory
		 */
		public function setDescription ($description) {
			$this->description = $description;
			return $this;
		}

		/**
		 * @param string $name
		 *
		 * @return BackgroundTaskCategory
		 */
		public function setName ($name) {
			$this->name = $name;
			return $this;
		}

		/**
		 * @param BackgroundTaskCategory $category
		 */
		public function copyValuesFrom ($category) {
			if ((empty ($category)) || (!($category instanceof BackgroundTaskCategory))) {
				return;
			}

			$this->description = $category->getDescription ();
			$this->name        = $category->getName ();
		}

		/**
		 * @return BackgroundTaskCategory
		 */
		public function duplicate () {
			$this->validate ();

			$object = new self ();
			return $object->setDescription ($this->description)
				->setName ($this->name);
		}

		/**
		 * @param BackgroundTaskCategory $category
		 *
		 * @return boolean
		 */
		public function isEqualTo ($category) {
			if (
				(empty ($category)) ||
				(!($category instanceof BackgroundTaskCategory)) ||
				($this->description != $category->getDescription ()) ||
				($this->name != $category->getName ())
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * @throws BackgroundTaskCategoryException
		 */
		public function validate () {
			if (empty ($this->description)) {
				throw new BackgroundTaskCategoryException (BackgroundTaskCategoryException::ERROR_BACKGROUND_TASK_CATEGORY_EMPTY_DESCRIPTION);
			} else if (empty ($this->name)) {
				throw new BackgroundTaskCategoryException (BackgroundTaskCategoryException::ERROR_BACKGROUND_TASK_CATEGORY_EMPTY_NAME);
			}
		}

		/**
		 * @return BackgroundTaskCategory
		 */
		public static function getInstance () {
			return new self ();
		}

	}
