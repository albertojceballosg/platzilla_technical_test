<?php
	require_once ('modules/okrs/Objects/OkrsInterface.php');
	
	class KeyResults implements OkrsInterface {
		
		/** @var string */
		private $companyArea;
		
		/** @var string */
		private $description;
		
		/** @var string */
		private $frequency;
		
		/** @var integer */
		private $goalValue;
		
		/** @var integer */
		private $id;
		
		/** @var integer */
		private $objectiveId;
		
		/** @var string */
		private $status;
		
		/** @var integer */
		private $valueActual;
		
		private function timeSince ($original) {
			$chunks = array (
				array (60 * 60 * 24 * 365, 'año'),
				array (60 * 60 * 24 * 30, 'mes'),
				array (60 * 60 * 24 * 7, 'sem'),
				array (60 * 60 * 24, 'día'),
				array (60 * 60, 'h'),
				array (60, 'min'),
			);
			$today  = time ();
			$since  = ($today - intval ($original));
			if ($since < 0) {
				return null;
			}
			$j = count ($chunks);
			for ($i = 0; $i < $j; $i++) {
				$seconds = $chunks[ $i ][0];
				$name    = $chunks[ $i ][1];
				$count   = floor ($since / $seconds);
				if ($count != 0) {
					$print = ($count == 1) ? '1 ' . $name : (($name == 'mes') ? "$count {$name}es" : "$count {$name}s");
					if (($i + 1) < $j) {
						$secondsTwo = $chunks[ ($i + 1) ][0];
						$nameTwo    = $chunks[ ($i + 1) ][1];
						$countTwo   = floor (($since - ($seconds * $count)) / $secondsTwo);
						if ($countTwo != 0) {
							$print .= ($countTwo == 1) ? ', 1 ' . $nameTwo : ", $countTwo {$nameTwo}s";
						}
					}
					break;
				}
			}
			return isset ($print) ? $print : null;
		}
		
		/**
		 * @return integer
		 */
		public function getCompanyArea () {
			return $this->companyArea;
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
		public function getFrequency () {
			return $this->frequency;
		}
		
		/**
		 * @return integer
		 */
		public function getGoalValue () {
			return $this->goalValue;
		}
		
		/**
		 * @return integer
		 */
		public function getId () 	{
			return $this->id;
		}
		
		/**
		 * @return integer
		 */
		public function getObjectiveId () {
			return $this->objectiveId;
		}
		
		/**
		 * @return string
		 */
		public function getStatus () {
			return $this->status;
		}
		
		/**
		 * @return integer
		 */
		public function getValueActual () {
			return $this->valueActual;
		}
		
		/**
		 * @param string $companyArea
		 *
		 * @return KeyResults
		 */
		public function setCompanyArea ($companyArea) {
			if (in_array ($companyArea, self::OKRS_COMPANY_AREA)) {
				$this->companyArea = $companyArea;
			} else {
				$this->companyArea = null;
			}
			return $this;
		}
		
		/**
		 * @param string $description
		 *
		 * @return KeyResults
		 */
		public function setDescription ($description) {
			$this->description = $description;
			return $this;
		}
		
		/**
		 * @param string $frequency
		 *
		 * @return KeyResults
		 */
		public function setFrequency ($frequency) {
			if (in_array ($frequency, self::OKRS_FREQUENCY)) {
				$this->frequency = $frequency;
			} else {
				$this->frequency = null;
			}
			return $this;
		}
		
		/**
		 * @param integer $goalValue
		 *
		 * @return KeyResults
		 */
		public function setGoalValue ($goalValue) {
			if (is_integer ($goalValue)) {
				$this->goalValue = $goalValue;
			} else {
				$this->goalValue = 0;
			}
			return $this;
		}
		
		/**
		 * @param integer $id
		 *
		 * @return KeyResults
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}
		
		/**
		 * @param integer $objectiveId
		 *
		 * @return KeyResults
		 */
		public function setObjectiveId ($objectiveId) {
			$this->objectiveId = $objectiveId;
			return $this;
		}
		
		/**
		 * @param string $status
		 *
		 * @return KeyResults
		 */
		public function setStatus ($status) {
			if (in_array ($status, self::OKRS_STATUS)) {
				$this->status = $status;
			} else {
				$this->status = null;
			}
			return $this;
		}
		
		/**
		 * @param integer $valueActual
		 *
		 * @return KeyResults
		 */
		public function setValueActual ($valueActual) {
			if (is_integer ($valueActual)) {
				$this->valueActual = $valueActual;
			} else {
				$this->valueActual = 0;
			}
			return $this;
		}
		
		/**
		 * @throws OkrsException
		 */
		public function validate () {
			if (empty ($this->objectiveId)) {
				throw new OkrsException (OkrsException::ERROR_OKRS_EMPTY_OBJECTIVE_ID);
			} else if (empty($this->goalValue)) {
				throw new OkrsException (OkrsException::ERROR_OKRS_EMPTY_GOAL_VALUE);
			} else if (empty($this->valueActual)) {
				throw new OkrsException (OkrsException::ERROR_OKRS_EMPTY_ACTUAL_VALUE);
			}
		}
		
		/**
		 * @return KeyResults
		 */
		public static function getInstance () {
			return new self ();
		}
		
	}
