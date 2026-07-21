<?php
	require_once ('include/platzilla/Exceptions/ViewAdvancedFilterGroupException.php');
	require_once ('include/platzilla/Objects/ViewAdvancedFilter.php');

	/**
	 * Class ViewAdvancedFilterGroup
	 *
	 * La clase "Vista Filtro Avanzado Grupo" hace referencia a las vista que controla el aspecto visual de la lista de registros
	 * consultados a través de los filtros avanzados, en la "Plataforma" y/o "Instancia".
	 * La clase está asociada al objeto "Vista Filtro Avanzado".
	 */
	class ViewAdvancedFilterGroup {
		/** @var ViewAdvancedFilter[] */
		private $filters;

		/** @var string */
		private $operator;

		/** @var integer */
		private $sequence;

		/** @var integer */
		private $viewId;

		/**
		 * ViewAdvancedFilterGroup constructor.
		 */
		public function __construct () {
			$this->operator = '';
		}

		/**
		 * Compara si el filtro es igual a otro
		 *
		 * @param ViewAdvancedFilter[] $filters
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
		 * Para realizar copia avanzada de la vista del filtro y las fuentes de sus parametros
		 *
		 * @param integer $viewId
		 * @param ViewAdvancedFilter[] $sourceFilters
		 */
		private function copyAdvancedFilters ($viewId, $sourceFilters) {
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
					$targetFilter = $sourceFilter->duplicate ($viewId, null);
					$filters []   = $targetFilter;
				}
			}
			$this->filters = $filters;
		}

		/**
		 * Para realizar copia avanzada de la vista del filtro y las fuentes de sus parametros desde otro filtro
		 *
		 * @param ViewAdvancedFilterGroup $group
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
					$filters [] = $sourceFilter->duplicate ($this->viewId, $this->sequence);
				}
				$this->filters = $filters;
			} else {
				$this->copyAdvancedFilters ($this->viewId, $sourceFilters);
			}
		}

		/**
		 * Para obtener la vista del filtro
		 *
		 * @return ViewAdvancedFilter[]
		 */
		public function getFilters () {
			return $this->filters;
		}

		/**
		 * Para obtener el operador que controlara el filtro
		 *
		 * @return string
		 */
		public function getOperator () {
			return $this->operator;
		}

		/**
		 * Para obtener la secuencia del filtro
		 *
		 * @return integer
		 */
		public function getSequence () {
			return $this->sequence;
		}

		/**
		 * Para obtener el ID de la vista del filtro
		 *
		 * @return integer
		 */
		public function getViewId () {
			return $this->viewId;
		}

		/**
		 * Establece la vista del filtro
		 *
		 * @param ViewAdvancedFilter[] $filters
		 *
		 * @return ViewAdvancedFilterGroup
		 */
		public function setFilters ($filters) {
			$this->filters = $filters;
			return $this;
		}

		/**
		 * Establece el nombre del modulo que se empleara para la vista avanzada del filtro
		 *
		 * @param string $moduleName
		 */
		public function setModuleName ($moduleName) {
			if (empty ($this->filters)) {
				return;
			}

			$n = count ($this->filters);
			for ($i = 0; $i < $n; $i++) {
				$this->filters [ $i ]->setModuleName ($moduleName);
			}
		}

		/**
		 * Establece el operador que controlara el filtro
		 *
		 * @param string $operator
		 *
		 * @return ViewAdvancedFilterGroup
		 */
		public function setOperator ($operator) {
			if (in_array ($operator, array ('', ViewAdvancedFilterInterface::OPERATOR_AND, ViewAdvancedFilterInterface::OPERATOR_OR))) {
				$this->operator = $operator;
			}
			return $this;
		}

		/**
		 * Establece la secuencia del filtro
		 *
		 * @param integer $sequence
		 *
		 * @return ViewAdvancedFilterGroup
		 */
		public function setSequence ($sequence) {
			$this->sequence = $sequence;
			return $this;
		}

		/**
		 * Establece el ID de la vista del filtro
		 *
		 * @param integer $viewId
		 *
		 * @return ViewAdvancedFilterGroup
		 */
		public function setViewId ($viewId) {
			$this->viewId = $viewId;
			return $this;
		}

		/**
		 * Realiza copia de los valores de la vista del filtro avanzada desde otra vista
		 *
		 * @param ViewAdvancedFilterGroup $group
		 */
		public function copyValuesFrom ($group) {
			if ((empty ($group)) || (!($group instanceof ViewAdvancedFilterGroup))) {
				return;
			}

			$this->operator = $group->getOperator ();
			$this->copyAdvancedFiltersFrom ($group);
		}

		/**
		 * Duplica los atributos (id, idGrupo, nombre codigo de los campos) de la vista avanzada del filtro
		 *
		 * @param integer $newViewId
		 * @param integer $newGroupId
		 * @param string $oldCodeFieldName
		 * @param string $newCodeFieldName
		 *
		 * @return ViewAdvancedFilterGroup
		 * @throws ViewAdvancedFilterException
		 * @throws ViewAdvancedFilterGroupException
		 */
		public function duplicate ($newViewId, $newGroupId, $oldCodeFieldName = null, $newCodeFieldName = null) {
			$this->validate ();

			$clonedFilters = array ();
			if (!empty ($this->filters)) {
				foreach ($this->filters as $filter) {
					$clonedFilters [] = $filter->duplicate ($newViewId, $newGroupId, $oldCodeFieldName, $newCodeFieldName);
				}
			}

			$object = new self ();
			return $object->setFilters ($clonedFilters)
				->setOperator ($this->operator)
				->setSequence ($this->sequence)
				->setViewId ($newViewId);
		}

		/**
		 * Para comparar si la vista avanzada del filtro es igual a otra
		 *
		 * @param ViewAdvancedFilterGroup $group
		 * @param boolean $deepCheck
		 *
		 * @return boolean
		 */
		public function isEqualTo ($group, $deepCheck = true) {
			if (
				(empty ($group)) ||
				(!($group instanceof ViewAdvancedFilterGroup)) ||
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
		 * Establece el nombre de la tabla donde se almacenara el filtro avanzado
		 *
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
		 * Valida que los atributos/valores (secuencia, los valores no esten vacios) de la vista esten correctos
		 *
		 * @throws ViewAdvancedFilterException
		 * @throws ViewAdvancedFilterGroupException
		 */
		public function validate () {
			if (!isset ($this->sequence)) {
				throw new ViewAdvancedFilterGroupException (ViewAdvancedFilterGroupException::ERROR_VIEW_ADVANCED_FILTER_GROUP_EMPTY_SEQUENCE);
			} else if ((!empty ($this->filters)) && (!is_array ($this->filters))) {
				throw new ViewAdvancedFilterGroupException (ViewAdvancedFilterGroupException::ERROR_VIEW_ADVANCED_FILTER_GROUP_EMPTY_FILTERS);
			} else if (!empty ($this->filters)) {
				foreach ($this->filters as $filter) {
					if (!($filter instanceof ViewAdvancedFilter)) {
						throw new ViewAdvancedFilterException (ViewAdvancedFilterException::ERROR_VIEW_ADVANCED_FILTER_INVALID_FILTER);
					} else {
						$filter->validate ();
					}
				}
			}
		}

		/**
		 * Instanciación de la clase ViewAdvancedFilterGroup. Se obtiene un objeto ViewAdvancedFilterGroup con los valores de la clase
		 *
		 * @return ViewAdvancedFilterGroup
		 */
		public static function getInstance () {
			return new self ();
		}

	}
