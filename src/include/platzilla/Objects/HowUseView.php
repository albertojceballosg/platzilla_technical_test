<?php
	require_once ('include/platzilla/Exceptions/HowToUseException.php');
	require_once ('include/platzilla/Objects/HowToUseInterface.php');
	require_once ('include/platzilla/Objects/MasterView.php');

	/**
	 * Class HowUseView
	 */
	class HowUseView implements HowToUseInterface {

		/** @var integer */
		private $howUseId;

		/** @var string */
		private $howUseName;

		/** @var integer */
		private $id;

		/** @var MasterView */
		private $masterView;

		/** @var string */
		private $name;

		/** @var string */
		private $relatedId;

		/** @var array */
		private $relatedViews;

		/**
		 * @param MasterView $masterView
		 *
		 * @return boolean
		 */
		private function isEqualMasterView ($masterView) {
			if (
				(!empty ($masterView) && empty ($this->masterView)) ||
				(empty ($masterView) && !empty ($this->masterView))
			) {
				return false;
			} else if ($this->masterView->isEqualTo ($masterView)) {
				return true;
			}
			return false;
		}

		/**
		 * @param array $thisRelatedViews
		 * @param array $thatRelatedViews
		 *
		 * @return boolean
		 */
		private function isEqualRelatedViews ($thisRelatedViews, $thatRelatedViews) {
			foreach ($thisRelatedViews as $key => $value) {
				if (is_array ($value)) {
					if (!isset ($thatRelatedViews[$key])) {
						$difference[$key] = $value;
					} else if (!is_array ($thatRelatedViews[$key])) {
						$difference[$key] = $value;
					} else {
						$newDiff = $this->isEqualRelatedViews ($value, $thatRelatedViews[$key]);
						if ($newDiff != false) {
							$difference[$key] = $newDiff;
						}
					}
				} else if(!array_key_exists ($key, $thatRelatedViews) || $thatRelatedViews[$key] != $value) {
					$difference[$key] = $value;
				}
			}
			return !isset($difference) ? true : false;
		}

		/**
		 * @param HowUseView $howUseView
		 */
		public function copyValuesFrom ($howUseView) {
			if (empty($howUseView) || !$howUseView instanceof HowUseView) {
				return;
			}
			$this->howUseId     = $howUseView->getHowUseId ();
			$this->howUseName   = $howUseView->getHowUseName ();
			$this->id           = $howUseView->getId ();
			$this->masterView   = $howUseView->getMasterView();
			$this->name         = $howUseView->getName ();
			$this->relatedId    = $howUseView->getRelatedId ();
			$this->relatedViews = $howUseView->getRelatedViews ();
		}

		/**
		 * @param null|integer $howUseViewId
		 *
		 * @return HowUseView
		 */
		public function duplicate ($howUseViewId = null) {
			$object = new self ();
			return $object->setId ((empty($howUseViewId)) ? $this->id : $howUseViewId)
				->setHowUseName ($this->howUseName)
				->setHowUseId ($this->getHowUseId ())
				->setMasterView ($this->masterView)
				->setName ($this->name)
				->setRelatedId ($this->relatedId)
				->setRelatedViews (json_encode ($this->relatedViews));
		}

		/**
		 * @return integer
		 */
		public function getHowUseId () {
			return $this->howUseId;
		}

		/**
		 * @return string
		 */
		public function getHowUseName () {
			return $this->howUseName;
		}

		/**
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}

		/**
		 * @return MasterView
		 */
		public function getMasterView () {
			return $this->masterView;
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
		public function getRelatedId () {
			return $this->relatedId;
		}

		/**
		 * @return array
		 */
		public function getRelatedViews () {
			return $this->relatedViews;
		}

		/**
		 * @param HowUseView $howUseView
		 *
		 * @return boolean
		 */
		public function isEqualTo ($howUseView) {
			if (
				empty($howUseView) ||
				($this->howUseName != $howUseView->getHowUseName()) ||
				(!$this->isEqualMasterView ($howUseView->getMasterView ())) ||
				($this->name != $howUseView->getName ()) ||
				($this->relatedId != $howUseView->getRelatedId ()) ||
				(!$this->isEqualRelatedViews ($this->relatedViews, $howUseView->getRelatedViews()))
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * @param integer $howUseId
		 *
		 * @return HowUseView
		 */
		public function setHowUseId ($howUseId) {
			$this->howUseId = $howUseId;
			return $this;
		}

		/**
		 * @param string $howUseName
		 *
		 * @return HowUseView
		 */
		public function setHowUseName ($howUseName) {
			$this->howUseName = $howUseName;
			return $this;
		}

		/**
		 * @param integer $id
		 *
		 * @return HowUseView
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}

		/**
		 * @param MasterView $masterView
		 *
		 * @return HowUseView
		 */
		public function setMasterView ($masterView) {
			$this->masterView = $masterView;
			return $this;
		}

		/**
		 * @param string $name
		 *
		 * @return HowUseView
		 */
		public function setName ($name) {
			$this->name = $name;
			return $this;
		}

		/**
		 * @param string $relatedId
		 *
		 * @return HowUseView
		 */
		public function setRelatedId ($relatedId) {
			$this->relatedId = $relatedId;
			return $this;
		}

		/**
		 * @param string $relatedViews
		 *
		 * @return HowUseView
		 */
		public function setRelatedViews ($relatedViews) {
			if (!empty($relatedViews)) {
				$this->relatedViews = json_decode($relatedViews, true);
			} else {
				$this->relatedViews = array();
			}
			return $this;
		}

		/**
		 * @throws HowToUseException
		 */
		public function validate () {
			if  (empty($this->masterView)) {
				throw new HowToUseException(HowToUseException::ERROR_VIEW_EMPTY_MASTER_VIEW);
			}
		}

		/**
		 * @return HowUseView
		 */
		public static function getInstance () {
			return new self ();
		}

	}
