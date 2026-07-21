<?php
	require_once ('modules/operating_modes/Exceptions/OperatingModesException.php');
	require_once ('modules/operating_modes/Objects/OperatingModesInterface.php');

	class TabTabs implements OperatingModesInterface {

		/** @var integer */
		private $id;

		/** @var string */
		private $iconPath;

		/** @var ModesContent */
		private $modesContent;

		/** @var string */
		private $moduleName;

		/** @var string */
		private $operatingModeName;

		/** @var integer */
		private $presence;

		/** @var integer */
		private $sequence;

		/** @var integer */
		private $tabId;
		
		/**
		 * @return TabTabs
		 */
		public function duplicate () {
			$object = new self ();
			return $object->setId ($this->id)
				->setIconPath ($this->iconPath)
				->setModesContent ($this->modesContent)
				->setModuleName ($this->moduleName)
				->setOperatingModeName ($this->operatingModeName)
				->setPresence ($this->presence)
				->setSequence (intval ($this->sequence) + 1)
				->setTabId ($this->tabId);
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
		public function getIconPath () {
			return $this->iconPath;
		}

		/**
		 * @return ModesContent
		 */
		public function getModesContent () {
			return $this->modesContent;
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
		public function getOperatingModeName () {
			return $this->operatingModeName;
		}

		/**
		 * @return integer
		 */
		public function getPresence () {
			return $this->presence;
		}

		/**
		 * @return integer
		 */
		public function getSequence () {
			return $this->sequence;
		}

		/**
		 * @return integer
		 */
		public function getTabId () {
			return $this->tabId;
		}

		/**
		 * @param integer $id
		 *
		 * @return TabTabs
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}

		/**
		 * @param string $iconPath
		 *
		 * @return TabTabs
		 */
		public function setIconPath ($iconPath) {
			$this->iconPath = $iconPath;
			return $this;
		}

		/**
		 * @param ModesContent $modesContent
		 *
		 * @return TabTabs
		 */
		public function setModesContent ($modesContent) {
			if (!empty ($modesContent) && $modesContent instanceof ModesContent) {
				$this->modesContent = $modesContent;
			} else {
				$this->modesContent = null;
			}
			return $this;
		}

		/**
		 * @param string $moduleName
		 *
		 * @return TabTabs
		 */
		public function setModuleName ($moduleName) {
			$this->moduleName = $moduleName;
			return $this;
		}

		/**
		 * @param string $operatingModeName
		 *
		 * @return TabTabs
		 */
		public function setOperatingModeName ($operatingModeName) {
			$this->operatingModeName = $operatingModeName;
			return $this;
		}

		/**
		 * @param integer $presence
		 *
		 * @return TabTabs
		 */
		public function setPresence ($presence) {
			$this->presence = $presence;
			return $this;
		}

		/**
		 * @param integer $sequence
		 *
		 * @return TabTabs
		 */
		public function setSequence ($sequence) {
			$this->sequence = $sequence;
			return $this;
		}

		/**
		 * @param integer $tabId
		 *
		 * @return TabTabs
		 */
		public function setTabId ($tabId) {
			$this->tabId = $tabId;
			return $this;
		}

		/**
		 * @return TabTabs
		 */
		public static function getInstance () {
			return new self ();
		}

	}
