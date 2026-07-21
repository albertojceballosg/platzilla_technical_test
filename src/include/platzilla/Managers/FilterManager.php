<?php
	require_once ('include/platzilla/Managers/FilterManagerInterface.php');
	require_once ('include/platzilla/Objects/FilterGroup.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');

	abstract class FilterManager implements FilterManagerInterface {
		/** @var PearDatabase */
		protected $adb;

		/** @var string */
		protected $entityFiltersTableName;

		/** @var string */
		protected $entityIdColumnName;

		protected function __construct (PearDatabase $adb, $entityFiltersTableName, $entityIdColumnName) {
			$this->adb                    = $adb;
			$this->entityFiltersTableName = $entityFiltersTableName;
			$this->entityIdColumnName     = $entityIdColumnName;
		}

		/**
		 * @param array $sourceValue
		 * @param string $comparator
		 * @param string $targetValue
		 *
		 * @return boolean
		 *
		 * NOTA: PHP Code Sniffer reporta una violación de la complejidad ciclomática (12). La cantidad de operadores exige que el switch sea así de complejo.
		 * Intentar refactorizar la función dificultaría la lectura de la misma
		 * @codingStandardsIgnoreStart
		 */
		protected function evaluateCondition ($sourceValue, $comparator, $targetValue) {
			switch ($comparator) {
				case FilterInterface::COMPARATOR_CONTAINS:
					$result = (strpos ($sourceValue, $targetValue) !== false);
					break;
				case FilterInterface::COMPARATOR_DAYS_AFTER:
					$today      = date_create ()->setTime (0, 0, 0);
					$sourceDate = date_create ($sourceValue);
					if ($sourceDate === false) {
						$result = false;
					} else {
						$sourceDate = $sourceDate->setTime (0, 0, 0);
						$result     = intval ($today->diff ($sourceDate)->format ('%a')) >= intval ($targetValue);
					}
					break;
				case FilterInterface::COMPARATOR_DAYS_AFTER_EXACT:
					$today      = date_create ()->setTime (0, 0, 0);
					$sourceDate = date_create ($sourceValue);
					if ($sourceDate === false) {
						$result = false;
					} else {
						$sourceDate = $sourceDate->setTime (0, 0, 0);
						$result     = intval ($today->diff ($sourceDate)->format ('%a')) == intval ($targetValue);
					}
					break;
				case FilterInterface::COMPARATOR_DAYS_BEFORE:
					$today      = date_create ()->setTime (0, 0, 0);
					$sourceDate = date_create ($sourceValue);
					if ($sourceDate === false) {
						$result = false;
					} else {
						$sourceDate = $sourceDate->setTime (0, 0, 0);
						$result     = intval ($sourceDate->diff ($today)->format ('%a')) >= intval ($targetValue);
					}
					break;
				case FilterInterface::COMPARATOR_DAYS_BEFORE_EXACT:
					$today      = date_create ()->setTime (0, 0, 0);
					$sourceDate = date_create ($sourceValue);
					if ($sourceDate === false) {
						$result = false;
					} else {
						$sourceDate = $sourceDate->setTime (0, 0, 0);
						$result     = intval ($sourceDate->diff ($today)->format ('%a')) == intval ($targetValue);
					}
					break;
				case FilterInterface::COMPARATOR_DOES_NOT_CONTAIN:
					$result = (strpos ($sourceValue, $targetValue) === false);
					break;
				case FilterInterface::COMPARATOR_ENDS_WITH:
					$length = strlen ($targetValue);
					$result = (substr ($sourceValue, 0, $length) === $targetValue);
					break;
				case FilterInterface::COMPARATOR_EQUALS:
					if ($targetValue == 'NULL') {
						$result = (empty(trim ($sourceValue)));
					} else {
						$result = ($sourceValue == $targetValue);
					}
					break;
				case FilterInterface::COMPARATOR_GREATER:
					$result = ($sourceValue > $targetValue);
					break;
				case FilterInterface::COMPARATOR_GREATER_OR_EQUALS:
					$result = ($sourceValue >= $targetValue);
					break;
				case FilterInterface::COMPARATOR_LESS:
					$result = ($sourceValue < $targetValue);
					break;
				case FilterInterface::COMPARATOR_LESS_OR_EQUALS:
					$result = ($sourceValue <= $targetValue);
					break;
				case FilterInterface::COMPARATOR_NOT_EQUALS:
					if ($targetValue == 'NULL') {
						$result = (!empty(trim ($sourceValue)));
					} else {
						$result = ($sourceValue != $targetValue);
					}
					break;
				case FilterInterface::COMPARATOR_STARTS_WITH:
					$length = strlen ($targetValue);
					$result = ($length === 0) || (substr ($sourceValue, -$length) === $targetValue);
					break;
				default:
					$result = false;
					break;
			}
			return $result;
		}
		// @codingStandardsIgnoreEnd

		/**
		 * @param Filter $filter
		 * @param CRMEntity|stdClass $entity
		 * @param string $moduleName
		 *
		 * @return boolean
		 */
		protected function evaluateFilter ($filter, $entity, $moduleName) {
			if ($filter->getModuleName () != $moduleName) {
				return false;
			}

			$sourceValues = $entity->column_fields;
			$fieldName    = $filter->getFieldName ();
			$comparator   = $filter->getComparator ();
			$targetValue  = $filter->getValue ();
			$sourceValue  = isset ($sourceValues [ $fieldName ]) ? $sourceValues [ $fieldName ] : '';
			return $this->evaluateCondition ($sourceValue, $comparator, $targetValue);
		}

		/**
		 * @param FilterGroup $group
		 * @param CRMEntity $entity
		 * @param string $moduleName
		 *
		 * @return boolean
		 */
		protected function evaluateFilterGroup ($group, $entity, $moduleName) {
			$filters = $group->getFilters ();
			if (empty ($filters)) {
				return true;
			}

			$result   = null;
			$operator = null;
			foreach ($filters as $filter) {
				if ($result === null) {
					$result = $this->evaluateFilter ($filter, $entity, $moduleName);
				} else {
					if ($operator == FilterInterface::OPERATOR_AND) {
						$result = ($result) && ($this->evaluateFilter ($filter, $entity, $moduleName));
					} else {
						$result = ($result) || ($this->evaluateFilter ($filter, $entity, $moduleName));
					}
				}
				$operator = $filter->getOperator ();
			}
			return $result;
		}

		/**
		 * @param FilterGroup[] $groups
		 * @param CRMEntity $entity
		 * @param string $moduleName
		 *
		 * @return boolean
		 */
		protected function evaluateFilterGroups ($groups, $entity, $moduleName) {
			if (empty ($groups)) {
				return true;
			} else if (get_class ($entity) != $moduleName) {
				return false;
			}

			$result   = null;
			$operator = null;
			foreach ($groups as $group) {
				if ($result === null) {
					$result = $this->evaluateFilterGroup ($group, $entity, $moduleName);
				} else {
					if ($operator == FilterInterface::OPERATOR_AND) {
						$result = ($result) && ($this->evaluateFilterGroup ($group, $entity, $moduleName));
					} else {
						$result = ($result) || ($this->evaluateFilterGroup ($group, $entity, $moduleName));
					}
				}
				$operator = $group->getOperator ();
			}
			return $result;
		}

		/**
		 * @param FilterGroupInterface $filterGroupClassName
		 * @param FilterInterface $filterClassName
		 * @param integer $entityId
		 *
		 * @return FilterGroup[]|null
		 */
		protected function fetchFilterGroupsByEntityId ($filterGroupClassName, $filterClassName, $entityId) {
			$result = $this->adb->pquery ("SELECT * FROM {$this->entityFiltersTableName}_filtergroups WHERE {$this->entityIdColumnName}=? ORDER BY groupid", array ($entityId));
			if ($this->adb->num_rows ($result) > 0) {
				$groups = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$groupId   = intval ($row ['groupid']);
					$groups [] = $filterGroupClassName::getInstance ()
						->setId ($groupId)
						->setFilters ($this->fetchFiltersByGroupId ($filterClassName, $entityId, $groupId))
						->setOperator ($row ['operator']);
				}
			} else {
				$groups = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $groups;
		}

		/**
		 * @param FilterInterface $className
		 * @param integer $entityId
		 * @param integer $groupId
		 *
		 * @return Filter[]|null
		 */
		protected function fetchFiltersByGroupId ($className, $entityId, $groupId) {
			$result = $this->adb->pquery ("SELECT * FROM {$this->entityFiltersTableName}_filters WHERE {$this->entityIdColumnName}=? AND groupid=? ORDER BY sequence", array ($entityId, $groupId));
			if ($this->adb->num_rows ($result) > 0) {
				$filters = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$filters [] = $className::getInstance ()
						->setComparator ($row ['comparator'])
						->setFieldName ($row ['fieldname'])
						->setGroupId ($groupId)
						->setLabel ($row ['label'])
						->setModuleName ($row ['modulename'])
						->setOperator ($row ['operator'])
						->setSequence (intval ($row ['sequence']))
						->setValue ($row ['value']);
				}
			} else {
				$filters = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $filters;
		}

		/**
		 * @param integer $entityId
		 * @param FilterGroup $group
		 *
		 * @throws FilterException
		 */
		protected function saveFilters ($entityId, $group) {
			$groupId = $group->getId ();
			$filters = $group->getFilters ();
			if (empty ($filters)) {
				$this->adb->pquery ("DELETE FROM {$this->entityFiltersTableName}_filters WHERE {$this->entityIdColumnName}=? AND groupid=?", array ($entityId, $groupId));
				return;
			}

			$processedSequences = array ();
			foreach ($filters as $filter) {
				if ($filter->getGroupId () === null) {
					$filter->setGroupId ($groupId);
				}
				$this->validateFilter ($filter);

				$sequence = $filter->getSequence ();
				$result   = $this->adb->pquery ("SELECT * FROM {$this->entityFiltersTableName}_filters WHERE {$this->entityIdColumnName}=? AND groupid=? AND sequence=?", array ($entityId, $groupId, $sequence));
				if ($this->adb->num_rows ($result) == 0) {
					$this->adb->pquery (
						"INSERT INTO {$this->entityFiltersTableName}_filters ({$this->entityIdColumnName}, groupid, sequence, modulename, fieldname, label, comparator, value, operator) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
						array ($entityId, $groupId, $sequence, $filter->getModuleName (), $filter->getFieldName (), $filter->getLabel (), $filter->getComparator (), $filter->getValue (), $filter->getOperator ())
					);
				} else {
					$this->adb->pquery (
						"UPDATE {$this->entityFiltersTableName}_filters SET modulename=?, fieldname=?, label=?, comparator=?, value=?, operator=? WHERE {$this->entityIdColumnName}=? AND groupid=? AND sequence=?",
						array ($filter->getModuleName (), $filter->getFieldName (), $filter->getLabel (), $filter->getComparator (), $filter->getValue (), $filter->getOperator (), $entityId, $groupId, $sequence)
					);
				}
				DatabaseUtils::closeResult ($result);
				$result                = null;
				$processedSequences [] = $sequence;
			}

			$questionMarks = str_repeat ('?, ', (count ($processedSequences) - 1)) . '?';
			$this->adb->pquery ("DELETE FROM {$this->entityFiltersTableName}_filters WHERE {$this->entityIdColumnName}=? AND groupid=? AND sequence NOT IN ({$questionMarks})", array_merge (array ($entityId, $groupId), $processedSequences));
		}

		/**
		 * @param Filter $filter
		 *
		 * @throws FilterException
		 */
		protected function validateFilter ($filter) {
			$groupId    = $filter->getGroupId ();
			$moduleName = $filter->getModuleName ();
			if (empty ($moduleName)) {
				throw new FilterException (FilterException::ERROR_FILTER_EMPTY_MODULE_NAME);
			} else if (!isset ($groupId)) {
				throw new FilterException (FilterException::ERROR_FILTER_EMPTY_GROUP_ID);
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_tab WHERE name=?', array ($moduleName));
			if ($this->adb->num_rows ($result) == 0) {
				$e = new FilterException (FilterException::ERROR_FILTER_INVALID_MODULE_NAME);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			if (isset ($e)) {
				throw $e;
			}
		}

		/**
		 * @param FilterGroup[] $groups
		 *
		 * @throws FilterException
		 * @throws FilterGroupException
		 */
		protected function validateFilterGroupSequenceNumbers ($groups) {
			// Validar los números de secuencia de grupos y filtros
			$processedGroupIds        = array ();
			$processedFilterSequences = array ();
			foreach ($groups as $group) {
				$groupId = $group->getId ();
				if (in_array ($groupId, $processedGroupIds)) {
					throw new FilterGroupException (FilterGroupException::ERROR_FILTER_GROUP_ID_ALREADY_TAKEN);
				}

				$filters = $group->getFilters ();
				if (!empty ($filters)) {
					foreach ($filters as $filter) {
						$sequence = $filter->getSequence ();
						if (in_array ($sequence, $processedFilterSequences)) {
							throw new FilterException (FilterException::ERROR_FILTER_SEQUENCE_ALREADY_TAKEN);
						}
						$processedFilterSequences [] = $sequence;
					}
				}
				$processedFilterSequences = array ();
				$processedGroupIds [] = $groupId;
			}
		}

		/**
		 * @param string $moduleName
		 * @param string $fieldName
		 */
		public function deleteFieldFromFilters ($moduleName, $fieldName) {
			$this->adb->pquery ("DELETE FROM {$this->entityFiltersTableName}_filters WHERE modulename=? AND fieldname=?", array ($moduleName, $fieldName));
		}

		/**
		 * @param integer $entityId
		 * @param FilterGroup[] $groups
		 *
		 * @throws FilterException
		 * @throws FilterGroupException
		 */
		public function saveFilterGroups ($entityId, $groups) {
			if (empty ($groups)) {
				$this->adb->pquery ("DELETE FROM {$this->entityFiltersTableName}_filters WHERE {$this->entityIdColumnName}=?", array ($entityId));
				$this->adb->pquery ("DELETE FROM {$this->entityFiltersTableName}_filtergroups WHERE {$this->entityIdColumnName}=?", array ($entityId));
				return;
			}

			$this->validateFilterGroupSequenceNumbers ($groups);

			$processedGroupIds = array ();
			foreach ($groups as $group) {
				$filters = $group->getFilters ();
				if (empty ($filters)) {
					continue;
				}

				$groupId = $group->getId ();
				$result  = $this->adb->pquery ("SELECT * FROM {$this->entityFiltersTableName}_filtergroups WHERE {$this->entityIdColumnName}=? AND groupid=?", array ($entityId, $groupId));
				if ((!$result) || ($this->adb->num_rows ($result) == 0)) {
					$this->adb->pquery (
						"INSERT INTO {$this->entityFiltersTableName}_filtergroups ({$this->entityIdColumnName}, groupid, operator) VALUES (?, ?, ?)",
						array ($entityId, $groupId, $group->getOperator ())
					);
				} else {
					$this->adb->pquery (
						"UPDATE {$this->entityFiltersTableName}_filtergroups SET operator=? WHERE {$this->entityIdColumnName}=? AND groupid=?",
						array ($group->getOperator (), $entityId, $groupId)
					);
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
				$this->saveFilters ($entityId, $group);
				$processedGroupIds [] = $groupId;
			}

			if (!empty ($processedGroupIds)) {
				$questionMarks = str_repeat ('?, ', (count ($processedGroupIds) - 1)) . '?';
				$this->adb->pquery ("DELETE FROM {$this->entityFiltersTableName}_filtergroups WHERE {$this->entityIdColumnName}=? AND groupid NOT IN ({$questionMarks})", array_merge (array ($entityId), $processedGroupIds));
			}
		}

		/**
		 * @param string $entityFiltersTableName
		 * @param string $entityIdColumnName
		 *
		 * @return string[]
		 */
		protected static function buildSqlStatementsToCreateTables ($entityFiltersTableName, $entityIdColumnName) {
			/** @noinspection SqlResolve */
			return array (
				"CREATE TABLE `{$entityFiltersTableName}_filtergroups` (
					`{$entityIdColumnName}` INT(11) NOT NULL,
					`groupid` INT(11) NOT NULL,
					`operator` VARCHAR(15) NULL DEFAULT NULL,
					PRIMARY KEY (`{$entityIdColumnName}`, `groupid`),
					CONSTRAINT `FK_{$entityFiltersTableName}_filtergroups_{$entityFiltersTableName}` FOREIGN KEY (`{$entityIdColumnName}`) REFERENCES `{$entityFiltersTableName}` (`{$entityIdColumnName}`) ON UPDATE CASCADE ON DELETE CASCADE
				) ENGINE=InnoDB;",
				"CREATE TABLE `{$entityFiltersTableName}_filters` (
					`{$entityIdColumnName}` INT(11) NOT NULL,
					`groupid` INT(11) NOT NULL,
					`sequence` INT(11) NOT NULL,
					`modulename` VARCHAR(25) NOT NULL,
					`fieldname` VARCHAR(50) NOT NULL,
					`label` VARCHAR(255) NOT NULL,
					`comparator` VARCHAR(25) NOT NULL,
					`value` VARCHAR(255) NULL DEFAULT NULL,
					`operator` VARCHAR(3) NULL DEFAULT NULL,
					PRIMARY KEY (`{$entityIdColumnName}`, `groupid`, `sequence`),
					INDEX `modulename_fieldname` (`modulename`, `fieldname`),
					CONSTRAINT `FK_{$entityFiltersTableName}_filters_filtergroups` FOREIGN KEY (`{$entityIdColumnName}`, `groupid`) REFERENCES `{$entityFiltersTableName}_filtergroups` (`{$entityIdColumnName}`, `groupid`) ON UPDATE CASCADE ON DELETE CASCADE
				) ENGINE=InnoDB;",
			);
		}

	}
