<?php
	require_once ('modules/okrs/Objects/OkrsException.php');
	require_once ('modules/okrs/Objects/KeyResults.php');
	require_once ('modules/okrs/Objects/OkrsInterface.php');
	class OkrsObjectives implements OkrsInterface {
		
		/** @var string */
		private $companyArea;
		
		/** @var array */
		private $companyPhases;
		
		/** @var array */
		private $companyTypes;
		
		/** @var DateTime */
		private $dueDate;
		
		/** @var string */
		private $frequency;
		
		/** @var string */
		private $howToDo;
		
		/** @var integer */
		private $id;
		
		/** @var string */
		private $isOnBoarding;
		
		/** @var KeyResults[] */
		private $keyResults;
		
		/** @var string */
		private $listPhases;
		
		/** @var string */
		private $listTypes;
		
		/** @var DateTime */
		private $startDate;
		
		/** @var string */
		private $status;
		
		/** @var string */
		private $toDo;
		
		/**
		 * @return integer
		 */
		public function getCompanyArea () {
			return $this->companyArea;
		}
		
		/**
		 * @return array
		 */
		public function getCompanyPhases () {
			return $this->companyPhases;
		}
		
		/**
		 * @return array
		 */
		public function getCompanyTypes () {
			return $this->companyTypes;
		}
		
		/**
		 * @return DateTime
		 */
		public function getDueDate () {
			return $this->dueDate;
		}
		
		/**
		 * @return string
		 */
		public function getFrequency () {
			return $this->frequency;
		}
		
		/**
		 * @return string
		 */
		public function getHowToDo () {
			return $this->howToDo;
		}
		
		/**
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}
		
		/**
		 * @return KeyResults[]
		 */
		public function getKeyResults () {
			return $this->keyResults;
		}
		
		/**
		 * @return string
		 */
		public function getListPhases () {
			return $this->listPhases;
		}
		
		/**
		 * @return string
		 */
		public function getListTypes () {
			return $this->listTypes;
		}
		
		/**
		 * @return DateTime
		 */
		public function getStartDate () {
			return $this->startDate;
		}
		
		/**
		 * @return string
		 */
		public function getStatus () {
			return $this->status;
		}
		
		/**
		 * @return string
		 */
		public function getToDo () 	{
			return $this->toDo;
		}
		
		/**
		 * @return string
		 */
		public function isOnBoarding () {
			return $this->isOnBoarding;
		}
		
		/**
		 * @param string $companyArea
		 *
		 * @return OkrsObjectives
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
		 * @param array $companyPhases
		 *
		 * @return OkrsObjectives
		 */
		public function setCompanyPhases ($companyPhases) {
			if (count ($companyPhases)) {
				foreach ($companyPhases as $phase) {
					if (!in_array ($phase, self::OKRS_COMPANY_PHASE)) {
						continue;
					}
					$this->companyPhases [] = $phase;
				}
				
			} else {
				$this->companyPhases = $companyPhases;
			}
			return $this;
		}
		
		/**
		 * @param array $companyTypes
		 *
		 * @return OkrsObjectives
		 */
		public function setCompanyTypes ($companyTypes) {
			if (count ($companyTypes)) {
				foreach ($companyTypes as $type) {
					if (!in_array ($type, self::OKRS_COMPANY_TYPE)) {
						continue;
					}
					$this->companyTypes [] = $type;
				}
				
			} else {
				$this->companyTypes = $companyTypes;
			}
			
			return $this;
		}
		
		/**
		 * @param DateTime $dueDate
		 *
		 * @return OkrsObjectives
		 */
		public function setDueDate ($dueDate) {
			if (!empty ($dueDate)) {
				$this->dueDate = DateTime::createFromFormat ('Y-m-d', $dueDate);
			} else {
				$this->createDate = null;
			}
			return $this;
		}
		
		/**
		 * @param string $frequency
		 *
		 * @return OkrsObjectives
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
		 * @param string $howToDo
		 *
		 * @return OkrsObjectives
		 */
		public function setHowToDo ($howToDo) {
			$this->howToDo = $howToDo;
			return $this;
		}
		
		/**
		 * @param integer $id
		 *
		 * @return OkrsObjectives
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}
		
		/**
		 * @param string $isOnBoarding
		 *
		 * @return OkrsObjectives
		 */
		public function setIsOnBoarding ($isOnBoarding) {
			$this->isOnBoarding = $isOnBoarding;
			return $this;
		}
		
		/**
		 * @param KeyResults[] $keyResults
		 *
		 * @return OkrsObjectives
		 */
		public function setKeyResults ($keyResults) {
			if (!empty ($keyResults)) {
				foreach ($keyResults as $keyResult) {
					if (!$keyResult instanceof KeyResults) {
						continue;
					}
					$this->keyResults [] = $keyResult;
				}
			} else {
				$this->keyResults = array ();
			}
			return $this;
		}
		
		/**
		 * @param string $listPhases
		 */
		public function setListPhases ($listPhases) {
			$this->listPhases = $listPhases;
		}
		
		/**
		 * @param string $listTypes
		 */
		public function setListTypes ($listTypes) {
			$this->listTypes = $listTypes;
		}
		
		/**
		 * @param DateTime $startDate
		 *
		 * @return OkrsObjectives
		 */
		public function setStartDate ($startDate) {
			if (!empty ($startDate)) {
				$this->startDate = DateTime::createFromFormat ('Y-m-d', $startDate);
			} else {
				$this->startDate = null;
			}
			return $this;
		}
		
		/**
		 * @param $status
		 *
		 * @return OkrsObjectives
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
		 * @param $toDo
		 *
		 * @return OkrsObjectives
		 */
		public function setToDo ($toDo) {
			$this->toDo = $toDo;
			return $this;
		}
		
		/**
		 * @throws OkrsException
		 */
		public function validate () {
			if (empty($this->companyArea)) {
				throw new OkrsException (OkrsException::ERROR_OKRS_EMPTY_COMPANY_AREA);
			} else if (!count ($this->companyPhases)) {
				throw new OkrsException (OkrsException::ERROR_OKRS_EMPTY_COMPANY_PHASE);
			} else if (!count ($this->companyTypes)) {
				throw new OkrsException (OkrsException::ERROR_OKRS_EMPTY_COMPANY_TYPE);
			} else if (empty($this->frequency)) {
				throw new OkrsException (OkrsException::ERROR_OKRS_EMPTY_FREQUENCY);
			} else if (empty($this->toDo)) {
				throw new OkrsException (OkrsException::ERROR_OKRS_EMPTY_TODO_OBJECTIVE);
			}
		}
		
		/**
		 * @return OkrsObjectives
		 */
		public static function getInstance () {
			return new self ();
		}
		
	}
