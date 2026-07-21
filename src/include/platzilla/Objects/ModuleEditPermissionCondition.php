<?php
	require_once ('include/platzilla/Objects/Filter.php');

	/**
	 * Class ModuleEditPermissionCondition
	 *
	 * @codingStandardsIgnoreStart
	 * @method ModuleEditPermissionCondition setComparator ($comparator)
	 * @method ModuleEditPermissionCondition setFieldName ($fieldName)
	 * @method ModuleEditPermissionCondition setGroupId ($groupId)
	 * @method ModuleEditPermissionCondition setLabel ($label)
	 * @method ModuleEditPermissionCondition setModuleName ($moduleName)
	 * @method ModuleEditPermissionCondition setOperator ($operator)
	 * @method ModuleEditPermissionCondition setSequence ($sequence)
	 * @method ModuleEditPermissionCondition setValue ($value)
	 * @method ModuleEditPermissionCondition copyValuesFrom ($filter)
	 * @codingStandardsIgnoreEnd
	 */
	class ModuleEditPermissionCondition extends Filter {

		/**
		 * @param integer $newGroupId
		 *
		 * @return ModuleEditPermissionCondition
		 * @throws FilterException
		 */
		public function duplicate ($newGroupId) {
			$this->validate ();
			return self::getInstance ()
				->setComparator ($this->comparator)
				->setFieldName ($this->fieldName)
				->setGroupId ($newGroupId)
				->setLabel ($this->label)
				->setModuleName ($this->moduleName)
				->setOperator ($this->operator)
				->setSequence ($this->sequence)
				->setValue ($this->value);
		}

		/**
		 * @return ModuleEditPermissionCondition
		 */
		public static function getInstance () {
			return new self ();
		}

	}
