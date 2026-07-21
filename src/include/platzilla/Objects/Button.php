<?php
	require_once ('include/platzilla/Exceptions/ButtonException.php');
	require_once ('include/platzilla/Objects/ButtonInterface.php');

	/**
	 * Class Button
	 *
	 * En esta clase se define el objeto "Boton Customizado" el cual hace referencia a los botones definidos a traves de la funcionalidad custom buttons.
	 */
	class Button implements ButtonInterface {

		/** @var integer */
		private $id;

		/** @var string */
		private $action;

		/**
		 *
		 * @var array
		 */
		private $arrayVisibility;

		/** @var boolean */
		private $deleted;

		/** @var string */
		private $description;

		/** @var boolean */
		private $isActive;

		/** @var string */
		private $label;

		/** @var string */
		private $location;

		/** @var boolean */
		private $locked;

		/** @var string */
		private $moduleName;

		/** @var boolean */
		private $runInNewWindow;

		/**
		 *
		 * @var string
		 */
		private $sqlVisibility;

		/** @var string */
		private $style;

		/** @var string */
		private $type;

		/** @var string */
		private $faIcon;

		/**
		 * Button constructor.
		 */
		public function __construct () {
			$this->deleted        = false;
			$this->isActive       = true;
			$this->locked         = false;
			$this->runInNewWindow = true;
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
		public function getAction () {
			return $this->action;
		}

		/**
		 *
		 * @return array
		 */
		public function getArrayVisibility () {
			return $this->arrayVisibility;
		}

		/**
		 * @return string
		 */
		public function getDescription () {
			return $this->description;
		}

		/**
		 * @return boolean
		 */
		public function getIsActive () {
			return $this->isActive;
		}

		/**
		 * @return string
		 */
		public function getLabel () {
			return $this->label;
		}

		/**
		 * @return string
		 */
		public function getLocation () {
			return $this->location;
		}

		/**
		 * @return string
		 */
		public function getModuleName () {
			return $this->moduleName;
		}

		/**
		 * @return boolean
		 */
		public function getRunInNewWindow () {
			return $this->runInNewWindow;
		}

		/**
		 *
		 * @return string
		 */
		public function getSqlVisibility () {
			return $this->sqlVisibility;
		}

		/**
		 * @return string
		 */
		public function getStyle () {
			return $this->style;
		}

		/**
		 * @return string
		 */
		public function getType () {
			return $this->type;
		}

		/**
		 * @return string
		 */
		public function getFaIcon () {
			return $this->faIcon;
		}

		/**
		 * @return boolean
		 */
		public function isDeleted () {
			return $this->deleted;
		}

		/**
		 * @return boolean
		 */
		public function isLocked () {
			return $this->locked;
		}

		/**
		 * @param integer $id
		 *
		 * @return Button
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}

		/**
		 * @param string $action
		 *
		 * @return Button
		 */
		public function setAction ($action) {
			$this->action = $action;
			return $this;
		}

		/**
		 * @param array $sqlArray
		 *
		 * @return Button
		 */
		public function setArrayVisibility ($sqlArray) {
			if (!empty ($sqlArray)) {
				$this->arrayVisibility = json_encode ($sqlArray, (JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT));
			}
			return $this;
		}

		/**
		 * @param boolean $deleted
		 *
		 * @return Button
		 */
		public function setDeleted ($deleted) {
			if (is_bool ($deleted)) {
				$this->deleted = $deleted;
			}
			return $this;
		}

		/**
		 * @param string $description
		 *
		 * @return Button
		 */
		public function setDescription ($description) {
			$this->description = $description;
			return $this;
		}

		/**
		 * @param boolean $isActive
		 *
		 * @return Button
		 */
		public function setIsActive ($isActive) {
			if (is_bool ($isActive)) {
				$this->isActive = $isActive;
			}
			return $this;
		}

		/**
		 * @param string $label
		 *
		 * @return Button
		 */
		public function setLabel ($label) {
			$this->label = $label;
			return $this;
		}

		/**
		 * @param string $location
		 *
		 * @return Button
		 */
		public function setLocation ($location) {
			if (in_array ($location, array (self::LOCATION_ACTION_BUTTON, self::LOCATION_DETAIL_VIEW, self::LOCATION_EDIT_VIEW, self::LOCATION_LIST_VIEW))) {
				$this->location = $location;
			}
			return $this;
		}

		/**
		 * @param boolean $locked
		 *
		 * @return Button
		 */
		public function setLocked ($locked) {
			if (is_bool ($locked)) {
				$this->locked = $locked;
			}
			return $this;
		}

		/**
		 * @param string $moduleName
		 *
		 * @return Button
		 */
		public function setModuleName ($moduleName) {
			$this->moduleName = $moduleName;
			return $this;
		}

		/**
		 * @param boolean $runInNewWindow
		 *
		 * @return Button
		 */
		public function setRunInNewWindow ($runInNewWindow) {
			if (is_bool ($runInNewWindow)) {
				$this->runInNewWindow = $runInNewWindow;
			}
			return $this;
		}

		/**
		 * @param string $sql
		 *
		 * @return Button
		 */
		public function setSqlVisibility ($sql) {
			if (!empty($sql)) {
				$this->sqlVisibility = json_encode ($sql);
			}
			return $this;
		}

		/**
		 * @param string $style
		 *
		 * @return Button
		 */
		public function setStyle ($style) {
			$this->style = $style;
			return $this;
		}

		/**
		 * @param string $type
		 *
		 * @return Button
		 */
		public function setType ($type) {
			if (in_array ($type, array (self::TYPE_JAVASCRIPT, self::TYPE_LINK))) {
				$this->type = $type;
			}
			return $this;
		}

		/**
		 * @param string $faIcon
		 *
		 * @return Button
		 */
		public function setFaIcon ($faIcon) {
			$this->faIcon = $faIcon;
			return $this;
		}

		/**
		 * @param Button $button
		 */
		public function copyValuesFrom ($button) {
			if ((empty ($button)) || (!($button instanceof Button))) {
				return;
			}

			$this->action          = $button->getAction ();
			$this->arrayVisibility = $button->getArrayVisibility ();
			$this->description     = $button->getDescription ();
			$this->isActive        = $button->getIsActive ();
			$this->label           = $button->getLabel ();
			$this->location        = $button->getLocation ();
			$this->moduleName      = $button->getModuleName ();
			$this->runInNewWindow  = $button->getRunInNewWindow ();
			$this->sqlVisibility   = $button->getSqlVisibility ();
			$this->style           = $button->getStyle ();
			$this->type            = $button->getType ();
			$this->faIcon          = $button->getFaIcon ();
		}

		/**
		 * @param integer $newButtonId
		 *
		 * @return Button
		 * @throws ButtonException
		 */
		public function duplicate ($newButtonId = null) {
			$this->validate ();

			$object = new self ();
			return $object->setId ($newButtonId)
				->setAction ($this->action)
				->setArrayVisibility ($this->arrayVisibility)
				->setDescription ($this->description)
				->setIsActive ($this->isActive)
				->setLabel ($this->label)
				->setLocation ($this->location)
				->setModuleName ($this->moduleName)
				->setRunInNewWindow ($this->runInNewWindow)
				->setSqlVisibility ($this->sqlVisibility)
				->setStyle ($this->style)
				->setType ($this->type)
				->setFaIcon ($this->faIcon);
		}

		/**
		 * @param Button $button
		 *
		 * @return boolean
		 */
		public function isEqualTo ($button) {
			if (
				(empty ($button)) ||
				(!($button instanceof Button)) ||
				($this->action != $button->getAction ()) ||
				(!MiscellaneousUtils::areArrayValuesEqual ($this->arrayVisibility, $button->getArrayVisibility ())) ||
				($this->description != $button->getDescription ()) ||
				($this->isActive != $button->getIsActive ()) ||
				($this->label != $button->getLabel ()) ||
				($this->location != $button->getLocation ()) ||
				($this->moduleName != $button->getModuleName ()) ||
				($this->runInNewWindow != $button->getRunInNewWindow ()) ||
				($this->sqlVisibility != $button->getSqlVisibility ()) ||
				($this->style != $button->getStyle ()) ||
				($this->type != $button->getType ()) ||
				($this->faIcon != $button->getFaIcon ())
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * @throws ButtonException
		 */
		public function validate () {
			if ($this->deleted) {
				return;
			} else if (empty ($this->action)) {
				throw new ButtonException (ButtonException::ERROR_BUTTON_EMPTY_ACTION);
			} else if (empty ($this->label)) {
				throw new ButtonException (ButtonException::ERROR_BUTTON_EMPTY_LABEL);
			} else if (empty ($this->location)) {
				throw new ButtonException (ButtonException::ERROR_BUTTON_EMPTY_LOCATION);
			} else if (empty ($this->style)) {
				throw new ButtonException (ButtonException::ERROR_BUTTON_EMPTY_STYLE);
			} else if (empty ($this->type)) {
				throw new ButtonException (ButtonException::ERROR_BUTTON_EMPTY_TYPE);
			}
		}

		/**
		 * @return Button
		 */
		public static function getInstance () {
			return new self ();
		}

	}
