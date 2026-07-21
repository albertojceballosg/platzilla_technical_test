<?php
	require_once ('include/platzilla/Exceptions/ReportTemplateException.php');

	class ReportTemplate implements Serializable {
		/** @var integer */
		private $id;

		/** @var boolean */
		private $active;

		/** @var string */
		private $code;

		/** @var boolean */
		private $deleted;

		/** @var boolean */
		private $hasInventory;

		/** @var string */
		private $moduleName;

		/** @var string */
		private $name;

		public function __construct () {
			$this->deleted = false;
		}

		/**
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}

		/**
		 * @return string
		 */
		public function getCode () {
			return $this->code;
		}

		/**
		 * @return string
		 */
		public function getModuleName () {
			return $this->moduleName;
		}

		/**
		 * @return string
		 */
		public function getName () {
			return $this->name;
		}

		/**
		 * @return boolean
		 */
		public function hasInventory () {
			return $this->hasInventory;
		}

		/**
		 * @return boolean
		 */
		public function isActive () {
			return $this->active;
		}

		/**
		 * @return boolean
		 */
		public function isDeleted () {
			return $this->deleted;
		}

		/**
		 * @param integer $id
		 *
		 * @return ReportTemplate
		 */
		public function setId ($id) {
			if ((is_numeric ($id)) && ($id > 0) && (intval ($id) == $id)) {
				$this->id = intval ($id);
			} else {
				$this->id = null;
			}
			return $this;
		}

		/**
		 * @param boolean $active
		 *
		 * @return ReportTemplate
		 */
		public function setActive ($active) {
			if (is_bool ($active)) {
				$this->active = $active;
			} else {
				$this->active = null;
			}
			return $this;
		}

		/**
		 * @param string $code
		 *
		 * @return ReportTemplate
		 */
		public function setCode ($code) {
			if (is_scalar ($code)) {
				$this->code = $code;
			} else {
				$this->code = null;
			}
			return $this;
		}

		/**
		 * @param boolean $deleted
		 *
		 * @return ReportTemplate
		 */
		public function setDeleted ($deleted) {
			if (is_bool ($deleted)) {
				$this->deleted = $deleted;
			} else {
				$this->deleted = false;
			}
			return $this;
		}

		/**
		 * @param boolean $hasInventory
		 *
		 * @return ReportTemplate
		 */
		public function setHasInventory ($hasInventory) {
			if (is_bool ($hasInventory)) {
				$this->hasInventory = $hasInventory;
			} else {
				$this->hasInventory = null;
			}
			return $this;
		}

		/**
		 * @param string $moduleName
		 *
		 * @return ReportTemplate
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
		 * @param string $name
		 *
		 * @return ReportTemplate
		 */
		public function setName ($name) {
			if (is_scalar ($name)) {
				$this->name = $name;
			} else {
				$this->name = null;
			}
			return $this;
		}

		/**
		 * @param ReportTemplate $template
		 */
		public function copyValuesFrom ($template) {
			if ((empty ($template)) || (!($template instanceof self))) {
				return;
			}

			$this->active       = $template->isActive ();
			$this->code         = $template->getCode ();
			$this->hasInventory = $template->hasInventory ();
			$this->moduleName   = $template->getModuleName ();
			$this->name         = $template->getName ();
		}

		/**
		 * @param integer $newTemplateId
		 *
		 * @return ReportTemplate
		 */
		public function duplicate ($newTemplateId = null) {
			$this->validate ();

			$object = new self ();
			return $object->setId ($newTemplateId)
				->setActive ($this->active)
				->setCode ($this->code)
				->setHasInventory ($this->hasInventory)
				->setModuleName ($this->moduleName)
				->setName ($this->name);
		}

		/**
		 * @param ReportTemplate $template
		 *
		 * @return boolean
		 */
		public function isEqualTo ($template) {
			if (
				(empty ($template)) ||
				(!($template instanceof self)) ||
				($this->active != $template->isActive ()) ||
				($this->code != $template->getCode ()) ||
				($this->hasInventory != $template->hasInventory ()) ||
				($this->moduleName != $template->getModuleName ()) ||
				($this->name != $template->getName ())
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * @throws ReportTemplateException
		 */
		public function validate () {
			if ($this->active === null) {
				throw new ReportTemplateException (ReportTemplateException::ERROR_REPORT_TEMPLATE_EMPTY_IS_ACTIVE);
			} else if (empty ($this->code)) {
				throw new ReportTemplateException (ReportTemplateException::ERROR_REPORT_TEMPLATE_EMPTY_CODE);
			} else if ($this->hasInventory === null) {
				throw new ReportTemplateException (ReportTemplateException::ERROR_REPORT_TEMPLATE_EMPTY_HAS_INVENTORY);
			} else if (empty ($this->moduleName)) {
				throw new ReportTemplateException (ReportTemplateException::ERROR_REPORT_TEMPLATE_EMPTY_MODULE_NAME);
			} else if (empty ($this->name)) {
				throw new ReportTemplateException (ReportTemplateException::ERROR_REPORT_TEMPLATE_EMPTY_NAME);
			}
		}

		/**
		 * @return string
		 */
		public function serialize () {
			return serialize (
				array (
					$this->id,
					$this->active,
					$this->code,
					$this->hasInventory,
					$this->moduleName,
					$this->name,
				)
			);
		}

		/**
		 * @param string $serialized
		 */
		public function unserialize ($serialized) {
			list (
				$this->id,
				$this->active,
				$this->code,
				$this->hasInventory,
				$this->moduleName,
				$this->name,
				) = unserialize ($serialized);
		}

		/**
		 * @return ReportTemplate
		 */
		public static function getInstance () {
			return new self ();
		}

	}
