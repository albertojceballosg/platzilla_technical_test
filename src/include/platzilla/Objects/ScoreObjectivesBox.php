<?php

	class ScoreObjectivesBox {

		/** @var string */
		private $dateEnd;

		/** @var string */
		private $dateFrom;

		/** @var string */
		private $fulfillment;

		/** @var integer */
		private $id;

		/** @var string */
		private $monthApli;

		/** @var string */
		private $objective;

		/** @var string */
		private $operator;

		/** @var integer */
		private $scoringDataBoxId;

		/** @var ScoringDataCompBox[] */
		private $scoringDataCompBox;

		/** @var integer */
		private $weekApli;
		
		/**
		 * @param ScoringDataCompBox[] $scoreDataCompBoxes
		 *
		 * @return array|null
		 */
		private function copyScoringDataCompBox ($scoreDataCompBoxes) {
			if (empty ($scoreDataCompBoxes)) {
				return null;
			}
			$scoringDataComp = array ();
			foreach ($scoreDataCompBoxes as $scoreDataCompBox) {
				$scoringDataComp [] = $scoreDataCompBox->duplicate ($scoreDataCompBox->getId());
			}
			return $scoringDataComp;
		}

		/**
		 * @param ScoringDataCompBox[] $scoreDataCompBoxes
		 *
		 * @return array
		 */
		private function duplicateFromScoringDataCompBox ($scoreDataCompBoxes) {
			$scoringDataComp = array ();
			foreach ($scoreDataCompBoxes as $scoreDataCompBox) {
				$scoringDataComp [] = $scoreDataCompBox->duplicate ($scoreDataCompBox->getId ());
			}
			return $scoringDataComp;
		}

		/**
		 * @param ScoringDataCompBox[] $theseScoreDataComp
		 * @param ScoringDataCompBox[] $thoseScoreDataComp
		 *
		 * @return boolean
		 */
		private function isScoreDataCompEqualTo ($theseScoreDataComp, $thoseScoreDataComp) {
			$totalScoreDataComp = count ($theseScoreDataComp);
			$equals              = false;
			if ($totalScoreDataComp != count ($thoseScoreDataComp)) {
				return false;
			}

			for ($k = 0; $k < $totalScoreDataComp; $k++) {
				if ($theseScoreDataComp [ $k ]->isEqualTo ($thoseScoreDataComp [ $k ])) {
					$equals = true;
					$k      = ($totalScoreDataComp + 1);
				}
			}
			return $equals;
		}

		/**
		 * @param string $date
		 * @param string $format
		 *
		 * @return boolean
		 */
		private function validateDate ($date, $format = 'Y-m-d') {
			$objectDate = DateTime::createFromFormat ($format, $date);
			return $objectDate && $objectDate->format ($format) == $date;
		}

		/**
		 * @param ScoreObjectivesBox $scoreObjective
		 */
		public function copyValuesFrom ($scoreObjective) {
			if ((empty ($scoreObjective)) || (!($scoreObjective instanceof ScoreObjectivesBox))) {
				return;
			}
			$this->dateEnd            = $scoreObjective->getDateEnd ();
			$this->dateFrom           = $scoreObjective->getDateFrom ();
			$this->fulfillment        = $scoreObjective->getFulfillment ();
			$this->monthApli          = $scoreObjective->getMonthApli ();
			$this->objective          = $scoreObjective->getObjective ();
			$this->operator           = $scoreObjective->getOperator ();
			$this->scoringDataBoxId   = $scoreObjective->getScoringDataBoxId ();
			$this->scoringDataCompBox = $this->copyScoringDataCompBox ($scoreObjective->getScoringDataCompBox ());
		}

		/**
		 * @param integer $newScoreObjectiveBoxId
		 *
		 * @return ScoreObjectivesBox
		 */
		public function duplicate ($newScoreObjectiveBoxId = null) {
			$object = new self ();
			return $object->setId ($newScoreObjectiveBoxId)
				->setDateEnd ($this->dateEnd)
				->setDateFrom ($this->dateFrom)
				->setFulfillment ($this->fulfillment)
				->setMonthApli ($this->monthApli)
				->setObjective ($this->objective)
				->setOperator ($this->operator)
				->setScoringDataBoxId ($this->scoringDataBoxId)
				->setScoringDataCompBox ($this->duplicateFromScoringDataCompBox ($this->scoringDataCompBox));
		}

		/**
		 * @return string
		 */
		public function getDateFrom () {
			return $this->dateFrom;
		}

		/**
		 * @return string
		 */
		public function getDateEnd () {
			return $this->dateEnd;
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
		public function getId() {
			return $this->id;
		}

		/**
		 * @return string
		 */
		public function getMonthApli () {
			return $this->monthApli;
		}

		/**
		 * @return string
		 */
		public function getObjective () {
			return $this->objective;
		}

		/**
		 * @return string
		 */
		public function getOperator () {
			return $this->operator;
		}

		/**
		 * @return integer
		 */
		public function getScoringDataBoxId () {
			return $this->scoringDataBoxId;
		}

		/**
		 * @return ScoringDataCompBox[]
		 */
		public function getScoringDataCompBox() {
			return $this->scoringDataCompBox;
		}
		
		/**
		 * @return integer
		 */
		public function getWeekApli () 	{
			return $this->weekApli;
		}
		
		/**
		 * @param ScoreObjectivesBox $scoreObjective
		 *
		 * @return boolean
		 */
		public function isEqualTo ($scoreObjective) {
			if (
				(empty ($scoreObjective)) ||
				(!($scoreObjective instanceof ScoreObjectivesBox)) ||
				($this->dateEnd != $scoreObjective->getDateEnd ()) ||
				($this->dateFrom != $scoreObjective->getDateFrom ()) ||
				($this->fulfillment != $scoreObjective->getFulfillment ()) ||
				($this->monthApli != $scoreObjective->getMonthApli ()) ||
				($this->objective != $scoreObjective->getObjective ()) ||
				($this->operator != $scoreObjective->getOperator ()) ||
				($this->isScoreDataCompEqualTo ($this->scoringDataCompBox, $scoreObjective->getScoringDataCompBox ()))
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * @param string $dateEnd
		 *
		 * @return ScoreObjectivesBox
		 */
		public function setDateEnd($dateEnd) {
			if ($this->validateDate ($dateEnd)) {
				$this->dateEnd = $dateEnd;
			} else {
				$this->dateEnd = null;
			}
			return $this;
		}

		/**
		 * @param string $dateFrom
		 *
		 * @return ScoreObjectivesBox
		 */
		public function setDateFrom ($dateFrom) {
			if ($this->validateDate ($dateFrom)) {
				$this->dateFrom = $dateFrom;
			} else {
				$this->dateFrom = null;
			}
			return $this;
		}

		/**
		 * @param string $fulfillment
		 *
		 * @return ScoreObjectivesBox
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
		 * @return ScoreObjectivesBox
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
		 * @param string $monthApli
		 *
		 * @return ScoreObjectivesBox
		 */
		public function setMonthApli($monthApli) {
			if (is_scalar ($monthApli)) {
				$this->monthApli = $monthApli;
			} else {
				$this->monthApli = null;
			}
			return $this;
		}

		/**
		 * @param string $objective
		 *
		 * @return ScoreObjectivesBox
		 */
		public function setObjective ($objective) {
			if (is_scalar (objective)) {
				$this->objective = $objective;
			} else {
				$this->objective = null;
			}
			return $this;
		}

		/**
		 * @param string $operator
		 *
		 * @return ScoreObjectivesBox
		 */
		public function setOperator ($operator) {
			if (is_scalar ($operator)) {
				$this->operator = $operator;
			} else {
				$this->operator = null;
			}
			return $this;
		}

		/**
		 * @param integer $scoringDataBoxId
		 *
		 * @return ScoreObjectivesBox
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
		 * @param ScoringDataCompBox[] $scoringDataCompBox
		 *
		 * @return ScoreObjectivesBox
		 */
		public function setScoringDataCompBox($scoringDataCompBox) {
			foreach ($scoringDataCompBox as $scoringDataComp) {
				if ($scoringDataComp == null || (($scoringDataComp instanceof ScoringDataCompBox)) && (!empty ($scoringDataComp))) {
					$this->scoringDataCompBox [] = $scoringDataComp;
				}
			}
			return $this;
		}
		
		/**
		 * @param integer $weekApli
		 *
		 * @return ScoreObjectivesBox
		 */
		public function setWeekApli ($weekApli) {
			$this->weekApli = $weekApli;
			return $this;
		}
		
		/**
		 * @return ScoreObjectivesBox
		 */
		public static function getInstance () {
			return new self ();
		}

	}
