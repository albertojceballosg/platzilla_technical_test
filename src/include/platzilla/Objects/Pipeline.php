<?php
	require_once ('include/platzilla/Exceptions/PipelineException.php');

	class Pipeline {
		/** @var integer */
		protected $id;

		/** @var string */
		protected $fieldName;

		/** @var string */
		protected $moduleName;

		/** @var string[] */
		protected $values;

		/**
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}

		/**
		 * @return string
		 */
		public function getFieldName () {
			return $this->fieldName;
		}

		/**
		 * @return string
		 */
		public function getModuleName () {
			return $this->moduleName;
		}

		/**
		 * @return string[]
		 */
		public function getValues () {
			return $this->values;
		}

		/**
		 * @param integer $id
		 *
		 * @return Pipeline
		 */
		public function setId ($id) {
			if ((is_numeric ($id)) && ($id > 0) && (intval ($id) == $id)) {
				$this->id = $id;
			} else {
				$this->id = null;
			}
			return $this;
		}

		/**
		 * @param string $fieldName
		 *
		 * @return Pipeline
		 */
		public function setFieldName ($fieldName) {
			if (is_scalar ($fieldName)) {
				$this->fieldName = $fieldName;
			} else {
				$this->fieldName = null;
			}
			return $this;
		}

		/**
		 * @param string $moduleName
		 *
		 * @return Pipeline
		 */
		public function setModuleName ($moduleName) {
			if (is_scalar ($moduleName)) {
				$this->moduleName = $moduleName;
			} else {
				$this->moduleName = null;
			}
			return $this;
		}

		/**
		 * @param string[] $values
		 *
		 * @return Pipeline
		 */
		public function setValues ($values) {
			if ((is_array ($values)) && (!empty ($values))) {
				$this->values = $values;
			} else {
				$this->values = null;
			}
			return $this;
		}

		/**
		 * @param Pipeline $pipeline
		 */
		public function copyValuesFrom ($pipeline) {
			if ((empty ($pipeline)) || (!($pipeline instanceof self))) {
				return;
			}

			$this->fieldName  = $pipeline->getFieldName ();
			$this->moduleName = $pipeline->getModuleName ();
			$this->values     = $pipeline->getValues ();
		}

		/**
		 * @return Pipeline
		 */
		public function duplicate () {
			$this->validate ();

			$object = new self ();
			return $object->setFieldName ($this->fieldName)
				->setModuleName ($this->moduleName)
				->setValues ($this->values);
		}

		/**
		 * @param Pipeline $pipeline
		 *
		 * @return boolean
		 */
		public function isEqualTo ($pipeline) {
			if (
				(empty ($pipeline)) ||
				(!($pipeline instanceof self)) ||
				($this->fieldName != $pipeline->getFieldName ()) ||
				($this->moduleName != $pipeline->getModuleName ()) ||
				(!MiscellaneousUtils::areArrayValuesEqual ($this->values, $pipeline->getValues ()))
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * @throws PipelineException
		 */
		public function validate () {
			if (empty ($this->fieldName)) {
				throw new PipelineException (PipelineException::ERROR_PIPELINE_EMPTY_FIELD_NAME);
			} else if (empty ($this->moduleName)) {
				throw new PipelineException (PipelineException::ERROR_PIPELINE_EMPTY_MODULE_NAME);
			} else if ((empty ($this->values)) || (!is_array ($this->values))) {
				throw new PipelineException (PipelineException::ERROR_PIPELINE_EMPTY_VALUES);
			}
		}

		/**
		 * @return Pipeline
		 */
		public static function getInstance () {
			return new self ();
		}

	}
