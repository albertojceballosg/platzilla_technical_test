<?php
	require_once ('modules/operating_modes/Exceptions/OperatingModesException.php');
	require_once ('modules/operating_modes/Objects/OperatingModesInterface.php');

	class OperatingModes implements OperatingModesInterface {

		/** @var array */
		private $attributes;

		/** @var integer */
		private $id;

		/** @var string */
		private $label;

		/** @var string */
		private $operatingModeName;

		/** @var string */
		private $status;

		/** @var TabTabs[] */
		private $tabTabs;

		/**
		 * @return array
		 */
		public function getAttributes () {
			return $this->attributes;
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
		public function getLabel () {
			return $this->label;
		}

		/**
		 * @return string
		 */
		public function getOperatingModeName () {
			return $this->operatingModeName;
		}

		/**
		 * @return string
		 */
		public function getStatus () {
			return $this->status;
		}

		/**
		 * @return TabTabs[]
		 */
		public function getTabTabs() {
			return $this->tabTabs;
		}

		/**
		 * @param array $attributes
		 *
		 * @return OperatingModes
		 */
		public function setAttributes ($attributes) {
			$this->attributes = $attributes;
			return $this;
		}

		/**
		 * @param integer $id
		 *
		 * @return OperatingModes
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}

		/**
		 * @param string $label
		 *
		 * @return OperatingModes
		 */
		public function setLabel ($label) {
			$this->label = $label;
			return $this;
		}

		/**
		 * @param string $operatingModeName
		 *
		 * @return OperatingModes
		 */
		public function setOperatingModeName ($operatingModeName) {
			$this->operatingModeName = $operatingModeName;
			return $this;
		}

		/**
		 * @param string $status
		 *
		 * @return OperatingModes
		 */
		public function setStatus ($status) {
			$this->status = $status;
			return $this;
		}

		/**
		 * @param TabTabs[] $tabTabs
		 *
		 * @return OperatingModes
		 */
		public function setTabTabs ($tabTabs) {
			if (!empty ($tabTabs)) {
				foreach ($tabTabs as $tabTab) {
					if (!$tabTab instanceof TabTabs) {
						continue;
					}
					$this->tabTabs [] = $tabTab;
				}
			} else {
				$this->tabTabs = array ();
			}
			return $this;
		}

		/**
		 * @return OperatingModes
		 */
		public static function getInstance () {
			return new self ();
		}

	}
