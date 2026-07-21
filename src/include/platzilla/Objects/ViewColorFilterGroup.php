<?php
	require_once ('include/platzilla/Exceptions/ViewColorFilterGroupException.php');
	require_once ('include/platzilla/Objects/ViewColorFilter.php');

	/**
	 * Class ViewColorFilterGroup
	 *
	 * Esta clase "Vista Filtro Color Grupo" hace referencia a las vista que controla el color en el aspecto visual de los registros
	 * consultados a través de los filtros, en la "Plataforma" y/o "Instancia". La clase está asociada al objeto "Vista Filtro Color".
	 */
	class ViewColorFilterGroup {
		/** @var ViewColorFilter[] */
		private $filters;

		/** @var string */
		private $color;

		/** @var integer */
		private $sequence;

		/** @var integer */
		private $viewId;

		/**
		 * ViewColorFilterGroup constructor.
		 */
		public function __construct () {
			$this->color = '';
		}

		/**
		 * Compara si el filtro de color es igual a otro
		 *
		 * @param ViewColorFilter[] $filters
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
		 * Para realizar copia de la vista del filtro de color y las fuentes de sus parametros
		 *
		 * @param integer $viewId
		 * @param ViewColorFilter[] $sourceFilters
		 */
		private function copyColorFilters ($viewId, $sourceFilters) {
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
		 * Para realizar copia del grupo del filtro de color y las fuentes de sus parametros
		 *
		 * @param ViewColorFilterGroup $group
		 */
		private function copyColorFiltersFrom ($group) {
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
				$this->copyColorFilters ($this->viewId, $sourceFilters);
			}
		}

		/**
		 * Para obtener la vista del filtro de color
		 *
		 * @return ViewColorFilter[]
		 */
		public function getFilters () {
			return $this->filters;
		}

		/**
		 * Para obtener el color del filtro
		 *
		 * @return string
		 */
		public function getColor () {
			return $this->color;
		}

		/**
		 * Para obtener la secuencia del filtro del color
		 *
		 * @return integer
		 */
		public function getSequence () {
			return $this->sequence;
		}

		/**
		 * Para obtener el ID de la vista del filtro de color
		 *
		 * @return integer
		 */
		public function getViewId () {
			return $this->viewId;
		}

		/**
		 * Establece la vista del filtro de color
		 *
		 * @param ViewColorFilter[] $filters
		 *
		 * @return ViewColorFilterGroup
		 */
		public function setFilters ($filters) {
			$this->filters = $filters;
			return $this;
		}

		/**
		 *  Establece el nombre del modulo que se empleara para la vista del filtro de color
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
		 * Establece el color del filtro
		 *
		 * @param string $color
		 *
		 * @return ViewColorFilterGroup
		 */
		public function setColor ($color) {
			$this->color = $color;
			return $this;
		}

		/**
		 * Establece la secuencia del filtro del color
		 *
		 * @param integer $sequence
		 *
		 * @return ViewColorFilterGroup
		 */
		public function setSequence ($sequence) {
			$this->sequence = $sequence;
			return $this;
		}

		/**
		 * Establece el ID de la vista del filtro de color
		 *
		 * @param integer $viewId
		 *
		 * @return ViewColorFilterGroup
		 */
		public function setViewId ($viewId) {
			$this->viewId = $viewId;
			return $this;
		}

		/**
		 * Realiza copia de los valores de la vista del filtro de color desde otra vista
		 *
		 * @param ViewColorFilterGroup $group
		 */
		public function copyValuesFrom ($group) {
			if ((empty ($group)) || (!($group instanceof ViewColorFilterGroup))) {
				return;
			}

			$this->color = $group->getColor ();
			$this->copyColorFiltersFrom ($group);
		}

		/**
		 * Duplica los atributos (id, idGrupo, nombre codigo de los campos) de la vista del filtro de color
		 *
		 * @param integer $newViewId
		 * @param integer $newGroupId
		 * @param string $oldCodeFieldName
		 * @param string $newCodeFieldName
		 *
		 * @return ViewColorFilterGroup
		 * @throws ViewColorFilterException
		 * @throws ViewColorFilterGroupException
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
				->setColor ($this->color)
				->setSequence ($this->sequence)
				->setViewId ($newViewId);
		}

		/**
		 * Para comparar si la vista del filtro de color es igual a otra
		 *
		 * @param ViewColorFilterGroup $group
		 * @param boolean $deepCheck
		 *
		 * @return boolean
		 */
		public function isEqualTo ($group, $deepCheck = true) {
			if (
				(empty ($group)) ||
				(!($group instanceof ViewColorFilterGroup)) ||
				($this->color != $group->getColor ()) ||
				($this->sequence != $group->getSequence ()) ||
				(($deepCheck) && (!$this->areFiltersEqual ($group->getFilters ())))
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * Establece el nombre de la tabla donde se almacenara el filtro de color
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
		 * Valida que los atributos/valores (secuencia, los valores no esten vacios) de la vista del filtro de color esten correctos
		 *
		 * @throws ViewColorFilterException
		 * @throws ViewColorFilterGroupException
		 */
		public function validate () {
			if (!isset ($this->sequence)) {
				throw new ViewColorFilterGroupException (ViewColorFilterGroupException::ERROR_VIEW_COLOR_FILTER_GROUP_EMPTY_SEQUENCE);
			} else if ((!empty ($this->filters)) && (!is_array ($this->filters))) {
				throw new ViewColorFilterGroupException (ViewColorFilterGroupException::ERROR_VIEW_COLOR_FILTER_GROUP_EMPTY_FILTERS);
			} else if (!empty ($this->filters)) {
				foreach ($this->filters as $filter) {
					if (!($filter instanceof ViewColorFilter)) {
						throw new ViewColorFilterException (ViewColorFilterException::ERROR_VIEW_COLOR_FILTER_INVALID_FILTER);
					} else {
						$filter->validate ();
					}
				}
			}
		}

		/**
		 * Instanciación de la clase ViewColorFilterGroup. Se obtiene un objeto ViewColorFilterGroup con los valores de la clase
		 *
		 * @return ViewColorFilterGroup
		 */
		public static function getInstance () {
			return new self ();
		}

	}
