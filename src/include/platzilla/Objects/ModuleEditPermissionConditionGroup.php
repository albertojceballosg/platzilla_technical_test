<?php
	require_once ('include/platzilla/Objects/ModuleEditPermissionCondition.php');
	require_once ('include/platzilla/Objects/FilterGroup.php');

	/**
	 * Class ModuleEditPermissionConditionGroup
	 *
	 * @codingStandardsIgnoreStart
	 * @property ModuleEditPermissionCondition[] $filters
	 * @method ModuleEditPermissionCondition[] getFilters ()
	 * @method ModuleEditPermissionConditionGroup setId ($id)
	 * @method ModuleEditPermissionConditionGroup setFilters ($filters)
	 * @method ModuleEditPermissionConditionGroup setOperator ($operator)
	 * @codingStandardsIgnoreEnd
	 */
	class ModuleEditPermissionConditionGroup extends FilterGroup {
		/** @var string */
		private $moduleName;

		/**
		 * @param ModuleEditPermissionCondition[] $sourceFilters
		 */
		private function copyFilters ($sourceFilters) {
			$filters = array ();
			foreach ($sourceFilters as $sourceFilter) {
				$found = false;
				foreach ($this->filters as $targetFilter) {
					if ($sourceFilter->getModuleName () != $targetFilter->getModuleName ()) {
						continue;
					} else if (!$targetFilter->isEqualTo ($sourceFilter)) {
						$targetFilter->copyValuesFrom ($sourceFilter);
					}
					$filters [] = $targetFilter;
					$found      = true;
					break;
				}
				if (!$found) {
					$targetFilter = $sourceFilter->duplicate (null);
					$filters []   = $targetFilter;
				}
			}
			$this->filters = $filters;
		}

		/**
		 * @param ModuleEditPermissionConditionGroup $group
		 */
		private function copyFiltersFrom ($group) {
			$sourceFilters = $group->getFilters ();
			if ((empty ($sourceFilters)) && (empty ($this->filters))) {
				return;
			}

			if (empty ($sourceFilters)) {
				$this->filters = null;
			} else if (empty ($this->filters)) {
				$filters = array ();
				foreach ($sourceFilters as $sourceFilter) {
					$filters [] = $sourceFilter->duplicate ($this->id);
				}
				$this->filters = $filters;
			} else {
				$this->copyFilters ($sourceFilters);
			}
		}

		/**
		 * @return string
		 */
		public function getModuleName () {
			return $this->moduleName;
		}

		/**
		 * @param string $moduleName
		 *
		 * @return ModuleEditPermissionCondition
		 */
		public function setModuleName ($moduleName) {
			if (is_scalar ($moduleName)) {
				$this->moduleName = $moduleName;
			} else {
				$this->moduleName = null;
			}
			return $this;
		}

		/**
		 * @param ModuleEditPermissionConditionGroup $group
		 */
		public function copyValuesFrom ($group) {
			if ((empty ($group)) || (!($group instanceof ModuleEditPermissionConditionGroup))) {
				return;
			}

			$this->operator = $group->getOperator ();
			$this->copyFiltersFrom ($group);
		}

		/**
		 * @param integer $newGroupId
		 *
		 * @return ModuleEditPermissionConditionGroup
		 * @throws FilterException
		 * @throws FilterGroupException
		 */
		public function duplicate ($newGroupId) {
			$this->validate ();

			$clonedFilters = array ();
			if (!empty ($this->filters)) {
				foreach ($this->filters as $filter) {
					$clonedFilters [] = $filter->duplicate ($newGroupId);
				}
			}

			return self::getInstance ()
				->setId ($this->id)
				->setFilters ($clonedFilters)
				->setOperator ($this->operator);
		}

		/**
		 * @return ModuleEditPermissionConditionGroup
		 */
		public static function getInstance () {
			return new self ();
		}

	}
