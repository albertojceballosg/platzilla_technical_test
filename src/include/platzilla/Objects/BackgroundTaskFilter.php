<?php
	require_once ('include/platzilla/Objects/Filter.php');

	/**
	 * Class BackgroundTaskFilter
	 *
	 * La clase "Filtro Tarea Oculta" hace referencia a los filtros que controlan las "Tareas Ocultas" en la "Plataforma" y/o "Instancia".
	 * La clase esta asociada al objeto "Filtro".
	 *
	 * @codingStandardsIgnoreStart
	 * @method BackgroundTaskFilter setComparator ($comparator)
	 * @method BackgroundTaskFilter setFieldName ($fieldName)
	 * @method BackgroundTaskFilter setGroupId ($groupId)
	 * @method BackgroundTaskFilter setLabel ($label)
	 * @method BackgroundTaskFilter setModuleName ($moduleName)
	 * @method BackgroundTaskFilter setOperator ($operator)
	 * @method BackgroundTaskFilter setSequence ($sequence)
	 * @method BackgroundTaskFilter setValue ($value)
	 * @method BackgroundTaskFilter copyValuesFrom ($filter)
	 * @codingStandardsIgnoreEnd
	 */
	class BackgroundTaskFilter extends Filter {
		/** @var integer */
		private $taskId;

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
		 * Duplica los atributos/valores de los filtros que controlan las tareas ocultas
		 *
		 * @param integer $newTaskId
		 * @param integer $newGroupId
		 *
		 * @return BackgroundTaskFilter
		 * @throws FilterException
		 */
		public function duplicate ($newTaskId, $newGroupId) {
			$this->validate ();
			return self::getInstance ()
				->setComparator ($this->comparator)
				->setFieldName ($this->fieldName)
				->setGroupId ($newGroupId)
				->setLabel ($this->label)
				->setModuleName ($this->moduleName)
				->setOperator ($this->operator)
				->setSequence ($this->sequence)
				->setValue ($this->value)
				->setTaskId ($newTaskId);
		}

		/**
		 * Instanciación de la clase BackgroundTaskFilter. Se obtiene un objeto BackgroundTaskFilter con los valores de la clase
		 *
		 * @return BackgroundTaskFilter
		 */
		public static function getInstance () {
			return new self ();
		}

	}
