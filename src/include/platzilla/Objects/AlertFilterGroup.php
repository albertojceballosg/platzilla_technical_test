<?php
	require_once ('include/platzilla/Exceptions/FilterGroupException.php');
	require_once ('include/platzilla/Objects/AlertFilter.php');
	require_once ('include/platzilla/Objects/FilterGroupInterface.php');
	require_once ('include/platzilla/Utils/MiscellaneousUtils.php');
	
	class AlertFilterGroup {
		
		/** @var integer */
		private $alertId;
		
		/** @var integer */
		private $id;
		
		/** @var AlertFilter[] */
		private $filters;
		
		/** @var string */
		private $moduleName;
		
		/** @var string */
		private $operator;
		
		/**
		 * @return integer
		 */
		public function getAlertId () {
			return $this->alertId;
		}
		
		/**
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}
		
		/**
		 * @return AlertFilter[]
		 */
		public function getFilters () {
			return $this->filters;
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
		public function getOperator () {
			return $this->operator;
		}
		
		/**
		 * @param integer $alertId
		 *
		 * @return AlertFilterGroup
		 */
		public function setAlertId ($alertId) {
			$this->alertId = $alertId;
			return $this;
		}
		
		/**
		 * @param integer $id
		 *
		 * @return AlertFilterGroup
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}
		
		/**
		 * @param AlertFilter[] $filters
		 *
		 * @return AlertFilterGroup
		 */
		public function setFilters ($filters) {
			$this->filters = $filters;
			return $this;
		}
		
		/**
		 * @param string $moduleName
		 *
		 * @return AlertFilterGroup
		 */
		public function setModuleName ($moduleName) {
			$this->moduleName = $moduleName;
			if (!empty ($this->filters)) {
				$n = count ($this->filters);
				for ($i = 0; $i < $n; $i++) {
					$this->filters [ $i ]->setModuleName ($moduleName);
				}
			}
			return $this;
		}
		
		/**
		 * @param string $operator
		 *
		 * @return AlertFilterGroup
		 */
		public function setOperator ($operator) {
			if (in_array ($operator, array ('', FilterInterface::OPERATOR_AND, FilterInterface::OPERATOR_OR))) {
				$this->operator = $operator;
			}
			return $this;
		}
		
		/**
		 * @param AlertFilterGroup $group
		 * @param boolean $deepCheck
		 *
		 * @return boolean
		 */
		public function isEqualTo ($group, $deepCheck = true) {
			if (
				(empty ($group)) ||
				(!($group instanceof AlertFilterGroup)) ||
				($this->operator != $group->getOperator ()) ||
				($this->id != $group->getId ()) ||
				(($deepCheck) && (!MiscellaneousUtils::areObjectArraysEqual ($this->filters, $group->getFilters ())))
			) {
				return false;
			} else {
				return true;
			}
		}
		
		/**
		 * @throws FilterException
		 * @throws FilterGroupException
		 */
		public function validate () {
			if (!isset ($this->id)) {
				throw new FilterGroupException (FilterGroupException::ERROR_FILTER_GROUP_EMPTY_ID);
			} else if ((!empty ($this->filters)) && (!is_array ($this->filters))) {
				throw new FilterGroupException (FilterGroupException::ERROR_FILTER_GROUP_EMPTY_FILTERS);
			} else if (!empty ($this->filters)) {
				foreach ($this->filters as $filter) {
					if (!($filter instanceof AlertFilter)) {
						throw new FilterException (FilterException::ERROR_FILTER_INVALID_FILTER);
					} else {
						$filter->validate ();
					}
				}
			}
		}
		
		/**
		 * @return AlertFilterGroup
		 */
		public static function getInstance () {
			return new self ();
		}
	}