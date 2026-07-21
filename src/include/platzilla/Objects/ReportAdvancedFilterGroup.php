<?php
	require_once ('include/platzilla/Exceptions/ReportAdvancedFilterGroupException.php');
	require_once ('include/platzilla/Objects/ReportAdvancedFilter.php');

	class ReportAdvancedFilterGroup {
		/** @var ReportAdvancedFilter[] */
		private $filters;

		/** @var string */
		private $operator;

		/** @var integer */
		private $reportId;

		/** @var integer */
		private $sequence;

		public function __construct () {
			$this->operator = '';
		}

		/**
		 * @param ReportAdvancedFilter[] $filters
		 *
		 * @return boolean
		 */
		private function areFiltersEqual ($filters) {
			if ((empty ($this->filters)) && (empty ($filters))) {
				return true;
			} else if (
				(empty ($this->filters) !== empty ($filters)) ||
				(!is_array ($filters)) ||
				(count ($this->filters) != count ($filters))
			) {
				return false;
			} else {
				foreach ($this->filters as $thisFilter) {
					$equals = false;
					foreach ($filters as $filter) {
						if ($filter->isEqualTo ($thisFilter)) {
							$equals = true;
							break;
						}
					}
					if (!$equals) {
						return false;
					}
				}
				return true;
			}
		}

		/**
		 * @param integer $reportId
		 * @param ReportAdvancedFilter[] $sourceFilters
		 */
		private function copyAdvancedFilters ($reportId, $sourceFilters) {
			$filters = array ();
			foreach ($sourceFilters as $sourceFilter) {
				$found = false;
				foreach ($this->filters as $targetFilter) {
					if ($sourceFilter->getSequence () != $targetFilter->getSequence ()) {
						continue;
					} else if (!$targetFilter->isEqualTo ($sourceFilter)) {
						$targetFilter->copyValuesFrom ($sourceFilter);
					}
					$filters [] = $targetFilter;
					$found      = true;
					break;
				}
				if (!$found) {
					$targetFilter = $sourceFilter->duplicate ($reportId, null);
					$filters []   = $targetFilter;
				}
			}
			$this->filters = $filters;
		}

		/**
		 * @param ReportAdvancedFilterGroup $group
		 */
		private function copyAdvancedFiltersFrom ($group) {
			$sourceFilters = $group->getFilters ();
			if ((empty ($sourceFilters)) && (empty ($this->filters))) {
				return;
			}

			if (empty ($sourceFilters)) {
				$this->filters = null;
			} else if (empty ($this->filters)) {
				$filters = array ();
				foreach ($sourceFilters as $sourceFilter) {
					$filters [] = $sourceFilter->duplicate ($this->reportId, $this->sequence);
				}
				$this->filters = $filters;
			} else {
				$this->copyAdvancedFilters ($this->reportId, $sourceFilters);
			}
		}

		/**
		 * @return ReportAdvancedFilter[]
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
		 * @return integer
		 */
		public function getReportId () {
			return $this->reportId;
		}

		/**
		 * @return integer
		 */
		public function getSequence () {
			return $this->sequence;
		}

		/**
		 * @param ReportAdvancedFilter[] $filters
		 *
		 * @return ReportAdvancedFilterGroup
		 */
		public function setFilters ($filters) {
			$this->filters = $filters;
			return $this;
		}

		/**
		 * @param string $moduleName
		 */
		public function setModuleName ($moduleName) {
			if (empty ($this->filters)) {
				return;
			}

			$n = count ($this->filters);
			for ($i = 0; $i < $n; $i++) {
				$this->filters [$i]->setModuleName ($moduleName);
			}
		}

		/**
		 * @param string $operator
		 *
		 * @return ReportAdvancedFilterGroup
		 */
		public function setOperator ($operator) {
			if (in_array ($operator, array ('', ReportAdvancedFilterInterface::OPERATOR_AND, ReportAdvancedFilterInterface::OPERATOR_OR))) {
				$this->operator = $operator;
			}
			return $this;
		}

		/**
		 * @param integer $reportId
		 *
		 * @return ReportAdvancedFilterGroup
		 */
		public function setReportId ($reportId) {
			$this->reportId = $reportId;
			return $this;
		}

		/**
		 * @param integer $sequence
		 *
		 * @return ReportAdvancedFilterGroup
		 */
		public function setSequence ($sequence) {
			$this->sequence = $sequence;
			return $this;
		}

		/**
		 * @param ReportAdvancedFilterGroup $group
		 */
		public function copyValuesFrom ($group) {
			if ((empty ($group)) || (!($group instanceof ReportAdvancedFilterGroup))) {
				return;
			}

			$this->operator = $group->getOperator ();
			$this->copyAdvancedFiltersFrom ($group);
		}

		/**
		 * @param integer $newReportId
		 * @param integer $newGroupId
		 * @param string $oldCodeFieldName
		 * @param string $newCodeFieldName
		 *
		 * @return ReportAdvancedFilterGroup
		 * @throws ReportAdvancedFilterException
		 * @throws ReportAdvancedFilterGroupException
		 */
		public function duplicate ($newReportId, $newGroupId, $oldCodeFieldName = null, $newCodeFieldName = null) {
			$this->validate ();

			$clonedFilters = array ();
			if (!empty ($this->filters)) {
				foreach ($this->filters as $filter) {
					$clonedFilters [] = $filter->duplicate ($newReportId, $newGroupId, $oldCodeFieldName, $newCodeFieldName);
				}
			}

			$object = new self ();
			return $object->setFilters ($clonedFilters)
				->setOperator ($this->operator)
				->setReportId ($newReportId)
				->setSequence ($this->sequence);
		}

		/**
		 * @param ReportAdvancedFilterGroup $group
		 * @param boolean $deepCheck
		 *
		 * @return boolean
		 */
		public function isEqualTo ($group, $deepCheck = true) {
			if (
				(empty ($group)) ||
				(!($group instanceof ReportAdvancedFilterGroup)) ||
				($this->operator != $group->getOperator ()) ||
				($this->sequence != $group->getSequence ()) ||
				(($deepCheck) && (!$this->areFiltersEqual ($group->getFilters ())))
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * @param string $oldTableName
		 * @param string $newTableName
		 */
		public function setTableName ($oldTableName, $newTableName) {
			if (empty ($this->filters)) {
				return;
			}

			$n = count ($this->filters);
			for ($i = 0; $i < $n; $i++) {
				if ($this->filters [ $i ]->getTableName () == $oldTableName) {
					$this->filters [ $i ]->setTableName ($newTableName);
				}
			}
		}

		/**
		 * @throws ReportAdvancedFilterException
		 * @throws ReportAdvancedFilterGroupException
		 */
		public function validate () {
			if (!isset ($this->sequence)) {
				throw new ReportAdvancedFilterGroupException (ReportAdvancedFilterGroupException::ERROR_REPORT_ADVANCED_FILTER_GROUP_EMPTY_SEQUENCE);
			} else if ((!empty ($this->filters)) && (!is_array ($this->filters))) {
				throw new ReportAdvancedFilterGroupException (ReportAdvancedFilterGroupException::ERROR_REPORT_ADVANCED_FILTER_GROUP_EMPTY_FILTERS);
			} else if (!empty ($this->filters)) {
				foreach ($this->filters as $filter) {
					if (!($filter instanceof ReportAdvancedFilter)) {
						throw new ReportAdvancedFilterException (ReportAdvancedFilterException::ERROR_REPORT_ADVANCED_FILTER_INVALID_FILTER);
					} else {
						$filter->validate ();
					}
				}
			}
		}

		/**
		 * @return ReportAdvancedFilterGroup
		 */
		public static function getInstance () {
			return new self ();
		}

	}
