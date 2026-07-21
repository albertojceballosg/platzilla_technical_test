<?php
	require_once ('include/platzilla/Objects/BackgroundTaskFilter.php');
	require_once ('include/platzilla/Objects/FilterGroup.php');

	/**
	 * Class BackgroundTaskFilterGroup
	 *
	 * La clase "Filtro Grupo Tarea Oculta" hace referencia a los filtros que controlan las "Tareas Ocultas" en la "Plataforma" y/o "Instancia".
	 * La clase está asociada al objeto "Filtro Tarea Oculta".
	 *
	 * @codingStandardsIgnoreStart
	 * @property BackgroundTaskFilter[] $filters
	 * @method BackgroundTaskFilter[] getFilters ()
	 * @method BackgroundTaskFilterGroup setId ($id)
	 * @method BackgroundTaskFilterGroup setFilters ($filters)
	 * @method BackgroundTaskFilterGroup setModuleName ($moduleName)
	 * @method BackgroundTaskFilterGroup setOperator ($operator)
	 * @codingStandardsIgnoreEnd
	 */
	class BackgroundTaskFilterGroup extends FilterGroup {
		/** @var integer */
		private $taskId;

		/**
		 * Realiza copia de los grupos de filtros posea la tarea oculta
		 *
		 * @param integer $taskId
		 * @param BackgroundTaskFilter[] $sourceFilters
		 */
		private function copyFilters ($taskId, $sourceFilters) {
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
					$targetFilter = $sourceFilter->duplicate ($taskId, null);
					$filters []   = $targetFilter;
				}
			}
			$this->filters = $filters;
		}

		/**
		 * Realiza copia de los grupos de filtros de la tarea oculta desde otra tarea
		 *
		 * @param BackgroundTaskFilterGroup $group
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
					$filters [] = $sourceFilter->duplicate ($this->taskId, $this->id);
				}
				$this->filters = $filters;
			} else {
				$this->copyFilters ($this->taskId, $sourceFilters);
			}
		}

		/**
		 * Para obtener el id de la tarea oculta
		 *
		 * @return integer
		 */
		public function getTaskId () {
			return $this->taskId;
		}

		/**
		 * Establece el id de la tarea oculta
		 *
		 * @param integer $taskId
		 *
		 * @return BackgroundTaskFilter
		 */
		public function setTaskId ($taskId) {
			$this->taskId = $taskId;
			return $this;
		}

		/**
		 * Realiza copia de los valores/parametros de los grupos de filtro de la tarea oculta desde otra tarea
		 *
		 * @param BackgroundTaskFilterGroup $group
		 */
		public function copyValuesFrom ($group) {
			if ((empty ($group)) || (!($group instanceof BackgroundTaskFilterGroup))) {
				return;
			}

			$this->operator = $group->getOperator ();
			$this->copyFiltersFrom ($group);
		}

		/**
		 * Duplica los atributos/valores de los grupos de filtro que posea la tarea oculta
		 *
		 * @param integer $newTaskId
		 * @param integer $newGroupId
		 *
		 * @return BackgroundTaskFilterGroup
		 * @throws FilterException
		 * @throws FilterGroupException
		 */
		public function duplicate ($newTaskId, $newGroupId) {
			$this->validate ();

			$clonedFilters = array ();
			if (!empty ($this->filters)) {
				foreach ($this->filters as $filter) {
					$clonedFilters [] = $filter->duplicate ($newTaskId, $newGroupId);
				}
			}

			return self::getInstance ()
				->setId ($this->id)
				->setFilters ($clonedFilters)
				->setOperator ($this->operator);
		}

		/**
		 * Instanciación de la clase BackgroundTaskFilterGroup. Se obtiene un objeto BackgroundTaskFilterGroup con los valores de la clase
		 *
		 * @return BackgroundTaskFilterGroup
		 */
		public static function getInstance () {
			return new self ();
		}

	}
