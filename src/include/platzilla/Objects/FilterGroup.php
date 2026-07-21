<?php
	require_once ('include/platzilla/Exceptions/FilterGroupException.php');
	require_once ('include/platzilla/Objects/Filter.php');
	require_once ('include/platzilla/Objects/FilterGroupInterface.php');
	require_once ('include/platzilla/Utils/MiscellaneousUtils.php');

	abstract class FilterGroup implements FilterGroupInterface {
		/** @var integer */
		protected $id;

		/** @var Filter[] */
		protected $filters;

		/** @var string */
		protected $operator;

		/**
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}

		/**
		 * @return Filter[]
		 */
		public function getFilters () {
			return $this->filters;
		}

		/**
		 * @return string
		 */
		public function getOperator () {
			return $this->operator;
		}

		/**
		 * @param integer $id
		 *
		 * @return FilterGroup
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}

		/**
		 * @param Filter[] $filters
		 *
		 * @return FilterGroup
		 */
		public function setFilters ($filters) {
			$this->filters = $filters;
			return $this;
		}

		/**
		 * @param string $moduleName
		 *
		 * @return FilterGroup
		 */
		public function setModuleName ($moduleName) {
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
		 * @return FilterGroup
		 */
		public function setOperator ($operator) {
			if (in_array ($operator, array ('', FilterInterface::OPERATOR_AND, FilterInterface::OPERATOR_OR))) {
				$this->operator = $operator;
			}
			return $this;
		}

		/**
		 * @param FilterGroup $group
		 * @param boolean $deepCheck
		 *
		 * @return boolean
		 */
		public function isEqualTo ($group, $deepCheck = true) {
			if (
				(empty ($group)) ||
				(!($group instanceof FilterGroup)) ||
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
					if (!($filter instanceof Filter)) {
						throw new FilterException (FilterException::ERROR_FILTER_INVALID_FILTER);
					} else {
						$filter->validate ();
					}
				}
			}
		}

	}
