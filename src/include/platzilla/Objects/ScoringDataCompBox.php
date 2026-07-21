<?php
	class ScoringDataCompBox {

		/** @var string */
		private $fulfillment;

		/** @var integer */
		private $id;

		/** @var string */
		private $label;

		/** @var float */
		private $lowerValue;

		/** @var integer */
		private $scoringDataBoxId;

		/** @var integer */
		private $scoreObjectivesBoxId;

		/** @var float */
		private $topValue;

		/** @var string */
		private $typeDataHigher;

		/** @var string */
		private $typeDataLower;

		/** @var string */
		private $varianceType;

		/** @var float */
		private $varianceValue;

		/**
		 * @param ScoringDataCompBox $scoringDataComp
		 */
		public function copyValuesFrom ($scoringDataComp) {
			if ((empty ($scoringDataComp)) || (!($scoringDataComp instanceof ScoringDataCompBox))) {
				return;
			}
			$this->fulfillment          = $scoringDataComp->getFulfillment ();
			$this->label                = $scoringDataComp->getLabel ();
			$this->lowerValue           = $scoringDataComp->getLowerValue ();
			$this->scoringDataBoxId     = $scoringDataComp->getScoringDataBoxId ();
			$this->scoreObjectivesBoxId = $scoringDataComp->getScoreObjectivesBoxId ();
			$this->topValue             = $scoringDataComp->getTopValue ();
			$this->typeDataHigher       = $scoringDataComp->getTypeDataHigher ();
			$this->typeDataLower        = $scoringDataComp->getTypeDataLower ();
			$this->varianceType         = $scoringDataComp->getVarianceType ();
			$this->varianceValue        = $scoringDataComp->getVarianceValue ();
		}

		/**
		 * @param null $newScoringDataCompId
		 *
		 * @return ScoringDataCompBox
		 */
		public function duplicate ($newScoringDataCompId = null) {
			$object = new self ();
			return $object->setId($newScoringDataCompId)
				->setFulfillment ($this->fulfillment)
				->setLabel ($this->label)
				->setLowerValue ($this->lowerValue)
				->setScoringDataBoxId ($this->scoringDataBoxId)
				->setScoreObjectivesBoxId ($this->scoreObjectivesBoxId)
				->setTopValue ($this->topValue)
				->setTypeDataHigher ($this->typeDataHigher)
				->setTypeDataLower ($this->typeDataLower)
				->setVarianceType ($this->varianceType)
				->setVarianceValue ($this->varianceValue);
		}

		/**
		 * @return string
		 */
		public function getFulfillment () {
			return $this->fulfillment;
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
		 * @return float
		 */
		public function getLowerValue () {
			return $this->lowerValue;
		}

		/**
		 * @return integer
		 */
		public function getScoringDataBoxId () {
			return $this->scoringDataBoxId;
		}

		/**
		 * @return integer
		 */
		public function getScoreObjectivesBoxId () {
			return $this->scoreObjectivesBoxId;
		}

		/**
		 * @return float
		 */
		public function getTopValue () {
			return $this->topValue;
		}

		/**
		 * @return string
		 */
		public function getTypeDataLower () {
			return $this->typeDataLower;
		}

		/**
		 * @return string
		 */
		public function getTypeDataHigher () {
			return $this->typeDataHigher;
		}

		/**
		 * @return string
		 */
		public function getVarianceType () {
			return $this->varianceType;
		}

		/**
		 * @return float
		 */
		public function getVarianceValue () {
			return $this->varianceValue;
		}

		/**
		 * @param ScoringDataCompBox $scoringDataComp
		 *
		 * @return boolean
		 */
		public function isEqualTo ($scoringDataComp) {
			if (
				(empty ($scoringDataComp)) ||
				($this->fulfillment != $scoringDataComp->getFulfillment ()) ||
				($this->label != $scoringDataComp->getLabel ()) ||
				($this->lowerValue != $scoringDataComp->getLowerValue ()) ||
				($this->scoringDataBoxId != $scoringDataComp->getScoringDataBoxId ()) ||
				($this->scoreObjectivesBoxId != $scoringDataComp->getScoreObjectivesBoxId ()) ||
				($this->topValue != $scoringDataComp->getTopValue ()) ||
				($this->typeDataHigher != $scoringDataComp->getTypeDataHigher ()) ||
				($this->typeDataLower != $scoringDataComp->getTypeDataLower ()) ||
				($this->varianceType != $scoringDataComp->getVarianceType ()) ||
				($this->varianceValue != $scoringDataComp->getVarianceValue ())
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * @param string $fulfillment
		 *
		 * @return ScoringDataCompBox
		 */
		public function setFulfillment ($fulfillment) {
			if (is_scalar ($fulfillment)) {
				$this->fulfillment = $fulfillment;
			} else {
				$this->fulfillment = null;
			}
			return $this;
		}

		/**
		 * @param integer $id
		 *
		 * @return ScoringDataCompBox
		 */
		public function setId($id) {
			if ((is_numeric ($id)) && ($id > 0) && (intval ($id) == $id)) {
				$this->id = $id;
			} else {
				$this->id = null;
			}
			return $this;
		}

		/**
		 * @param string $label
		 *
		 * @return ScoringDataCompBox
		 */
		public function setLabel ($label) {
			if (is_scalar ($label)) {
				$this->label = $label;
			} else {
				$this->label = null;
			}
			return $this;
		}

		/**
		 * @param float $lowerValue
		 *
		 * @return ScoringDataCompBox
		 */
		public function setLowerValue ($lowerValue) {
			if (is_numeric ($lowerValue)) {
				$this->lowerValue = $lowerValue;
			} else {
				$this->lowerValue = null;
			}
			return $this;
		}

		/**
		 * @param integer $scoringDataBoxId
		 *
		 * @return ScoringDataCompBox
		 */
		public function setScoringDataBoxId ($scoringDataBoxId) {
			if ((is_numeric ($scoringDataBoxId)) && ($scoringDataBoxId > 0) && (intval ($scoringDataBoxId) == $scoringDataBoxId)) {
				$this->scoringDataBoxId = $scoringDataBoxId;
			} else {
				$this->scoringDataBoxId = null;
			}
			return $this;
		}

		/**
		 * @param integer $scoreObjectivesBoxId
		 *
		 * @return ScoringDataCompBox
		 */
		public function setScoreObjectivesBoxId ($scoreObjectivesBoxId) {
			if ((is_numeric ($scoreObjectivesBoxId)) && ($scoreObjectivesBoxId > 0) && (intval ($scoreObjectivesBoxId) == $scoreObjectivesBoxId)) {
				$this->scoreObjectivesBoxId = $scoreObjectivesBoxId;
			} else {
				$this->scoreObjectivesBoxId = null;
			}
			return $this;
		}

		/**
		 * @param float $topValue
		 *
		 * @return ScoringDataCompBox
		 */
		public function setTopValue ($topValue) {
			if (is_numeric ($topValue)) {
				$this->topValue = $topValue;
			} else {
				$this->topValue = null;
			}
			return $this;
		}

		/**
		 * @param string $typeDataHigher
		 *
		 * @return ScoringDataCompBox
		 */
		public function setTypeDataHigher ($typeDataHigher) {
			if (is_scalar ($typeDataHigher)) {
				$this->typeDataHigher = $typeDataHigher;
			} else {
				$this->typeDataHigher = null;
			}
			return $this;
		}

		/**
		 * @param string $typeDataLower
		 *
		 * @return ScoringDataCompBox
		 */
		public function setTypeDataLower ($typeDataLower) {
			if (is_scalar ($typeDataLower)) {
				$this->typeDataLower = $typeDataLower;
			} else {
				$this->typeDataLower = null;
			}
			return $this;
		}

		/**
		 * @param string $varianceType
		 *
		 * @return ScoringDataCompBox
		 */
		public function setVarianceType ($varianceType) {
			if (is_scalar ($varianceType)) {
				$this->varianceType = $varianceType;
			} else {
				$this->varianceType = null;
			}
			return $this;
		}

		/**
		 * @param float $varianceValue
		 *
		 * @return ScoringDataCompBox
		 */
		public function setVarianceValue ($varianceValue) {
			if (is_numeric ($varianceValue)) {
				$this->varianceValue = $varianceValue;
			} else {
				$this->varianceValue = null;
			}
			return $this;
		}

		/**
		 * @return ScoringDataCompBox
		 */
		public static function getInstance () {
			return new self ();
		}

	}
