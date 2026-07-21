<?php
	require_once ('include/platzilla/Objects/DefaultView.php');
	require_once ('include/platzilla/Objects/HowUseView.php');

	/**
	 * Class HowToUse
	 */
	class HowToUse implements HowToUseInterface {

		/** @var DefaultView */
		private $defaultView;

		/** @var string */
		private $description;

		/** @var string */
		private $howUseName;

		/** @var HowUseView[] */
		private $howUseView;

		/** @var integer */
		private $id;

		/** @var boolean */
		private $isDefault;

		/** @var string */
		private $name;

		/** @var string */
		private $status;

		/** @var string */
		private $tabName;

		/**
		 * @param DefaultView $defaultView
		 *
		 * @return boolean
		 */
		private function isEqualDefaultView ($defaultView) {
			if (
				(!empty ($defaultView) && empty($this->defaultView)) ||
				(empty ($defaultView) && !empty($this->defaultView))
			) {
				return false;
			} else if ($this->defaultView->isEqualTo ($defaultView)) {
				return true;
			}
			return false;
		}

		/**
		 * @param HowUseView[] $howUseViews
		 *
		 * @return boolean
		 */
		private function isEqualHowUseView ($howUseViews) {
			if (!empty ($this->getHowUseView()) && !empty ($howUseViews)) {
				if (count($this->getHowUseView()) != count($howUseViews)) {
					return false;
				} else {
					$isEqual = true;
					foreach ($this->getHowUseView() as $thisHowUseView) {
						$isFound = false;
						foreach ($howUseViews as $thatHowUseView) {
							if ($thisHowUseView->getMasterView()->getName() != $thatHowUseView->getMasterView()->getName()) {
								continue;
							}
							$isFound = true;
							if (!$thisHowUseView->isEqualTo($thatHowUseView)) {
								$isEqual = false;
							}
						}
						if(!$isFound) {
							return false;
						} else if (!$isEqual) {
							return false;
						}
					}
				}
			} else if (empty ($this->getHowUseView()) && empty ($howUseViews)) {
				return true;
			}
			return false;
		}

		/**
		 * @param HowToUse $howToUse
		 */
		public function copyValuesFrom ($howToUse) {
			if (empty ($howToUse) || !$howToUse instanceof HowToUse) {
				return;
			}
			$this->defaultView = $howToUse->getDefaultView ();
			$this->description = $howToUse->getDescription ();
			$this->howUseName  = $howToUse->getHowUseName ();
			$this->howUseView  = $howToUse->getHowUseView ();
			$this->id          = $howToUse->getId ();
			$this->isDefault   = $howToUse->getDefaultView ();
			$this->name        = $howToUse->getName ();
			$this->status      = $howToUse->getStatus ();
			$this->tabName     = $howToUse->getTabName ();
		}

		/**
		 * @param null $howToUseId
		 *
		 * @return HowToUse
		 */
		public function duplicate ($howToUseId = null) {
			$object = new self ();
			return $object->setId ((!empty ($howToUseId)) ? $howToUseId : $this->id)
				->setHowUseName ($this->howUseName)
				->setDefaultView ($this->defaultView)
				->setDescription ($this->description)
				->setHowUseView ($this->howUseView)
				->setDefault ($this->isDefault)
				->setName ($this->name)
				->setStatus ($this->status)
				->setTabName ($this->tabName);
		}

		/**
		 * @return DefaultView
		 */
		public function getDefaultView () {
			return $this->defaultView;
		}

		/**
		 * @return string
		 */
		public function getDescription () {
			return $this->description;
		}

		/**
		 * @return string
		 */
		public function getHowUseName () {
			return $this->howUseName;
		}

		/**
		 * @return HowUseView[]
		 */
		public function getHowUseView () {
			return $this->howUseView;
		}

		/**
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}

		/**
		 * @return boolean
		 */
		public function isDefault () {
			return $this->isDefault;
		}

		/**
		 * @param HowToUse $howToUse
		 *
		 * @return boolean
		 */
		public function isEqualTo ($howToUse) {
			if (
				empty($howToUse) ||
				($this->howUseName != $howToUse->getHowUseName ()) ||
				(!$this->isEqualDefaultView ($howToUse->getDefaulTView())) ||
				($this->description != $howToUse->getDescription()) ||
				(!$this->isEqualHowUseView ($howToUse->getHowUseView())) ||
				($this->isDefault != $howToUse->isDefault()) ||
				($this->name != $howToUse->getName()) ||
				($this->tabName != $howToUse->getTabName())
			) {
				return false;
			} else {
				return true;
			}
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
		public function getStatus() {
			return $this->status;
		}

		/**
		 * @return string
		 */
		public function getTabName () {
			return $this->tabName;
		}

		/**
		 * @param DefaultView $defaultView
		 *
		 * @return HowToUse
		 */
		public function setDefaultView ($defaultView) {
			$this->defaultView = $defaultView;
			return $this;
		}

		/**
		 * @param boolean $default
		 *
		 * @return HowToUse
		 */
		public function setDefault ($default) {
			$this->isDefault = $default;
			return $this;
		}

		/**
		 * @param string $description
		 *
		 * @return HowToUse
		 */
		public function setDescription ($description) {
			$this->description = $description;
			return $this;
		}

		/**
		 * @param $howUseName
		 *
		 * @return HowToUse
		 */
		public function setHowUseName ($howUseName) {
			$this->howUseName = $howUseName;
			return $this;
		}

		/**
		 * @param HowUseView[] $howUseView
		 *
		 * @return HowToUse
		 */
		public function setHowUseView ($howUseView) {
			$this->howUseView = $howUseView;
			return $this;
		}

		/**
		 * @param integer $id
		 *
		 * @return HowToUse
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}

		/**
		 * @param string $name
		 *
		 * @return HowToUse
		 */
		public function setName ($name) {
			$this->name = $name;
			return $this;
		}

		/**
		 * @param string $status
		 *
		 * @return HowToUse
		 */
		public function setStatus ($status) {
			$this->status = $status;
			return $this;
		}

		/**
		 * @param string $tabName
		 *
		 * @return HowToUse
		 */
		public function setTabName ($tabName) {
			$this->tabName = $tabName;
			return $this;
		}

		/**
		 * @throws HowToUseException
		 */
		public function validate () {
			if (empty($this->tabName)) {
				throw new HowToUseException (HowToUseException::ERROR_VIEW_EMPTY_DEFAULT_VIEW_MODULE);
			} else if (empty($this->howUseName)) {
				throw new HowToUseException (HowToUseException::ERROR_VIEW_EMPTY_INDEX_VIEW_NAME);
			} else if (empty($this->name)) {
				throw new HowToUseException (HowToUseException::ERROR_VIEW_EMPTY_HOW_USE_NAME);
			}
		}

		/**
		 * @return HowToUse
		 */
		public static function getInstance () {
			return new self ();
		}
		
	}
