<?php
	require_once ('include/platzilla/Exceptions/ViewException.php');
	require_once ('include/platzilla/Objects/ViewAdvancedFilterGroup.php');
	require_once ('include/platzilla/Objects/ViewColorFilterGroup.php');
	require_once ('include/platzilla/Objects/ViewColumn.php');
	require_once ('include/platzilla/Objects/ViewGroup.php');
	require_once ('include/platzilla/Objects/ViewInterface.php');
	require_once ('include/platzilla/Objects/ViewStandardFilter.php');

	/**
	 * Class View
	 *
	 * Esta clase define el objeto "Vista" el cual hace referencia a las vistas que controlan las controlan el aspecto visual de los registros en la "Plataforma" y/o "Instancia".
	 * La clase está asociada a los objetos "Vista Filtro Estándar", "Vista Filtro Avanzado Grupo", "Vista Filtro Color Grupo" y "Vista Columna".
	 */
	class View implements ViewInterface {

		/** @var integer */
		private $id;

		/** @var ViewAdvancedFilterGroup[] */
		private $advancedFilterGroups;

		/** @var ViewColorFilterGroup[] */
		private $colorFilterGroups;

		/** @var ViewColumn[] */
		private $columns;

		/** @var integer */
		private $default;

		/** @var boolean */
		private $deleted;
		
		/** @var integer */
		private $deskView;

		/** @var boolean */
		private $locked;

		/** @var string */
		private $moduleName;

		/** @var string */
		private $name;

		/** @var integer */
		private $owner;

		/** @var integer */
		private $searchView;

		/** @var integer */
		private $showCountInMenu;

		/** @var ViewStandardFilter */
		private $standardFilter;

		/** @var integer */
		private $status;

		/** @var ViewGroup */
		private $viewGroup;

		/**
		 * View constructor.
		 */
		public function __construct () {
			$this->default         = self::DEFAULT_NO;
			$this->searchView      = self::SEARCH_NO;
			$this->deleted         = false;
			$this->deskView        = self::SHOW_ON_DESK_NO;
			$this->locked          = false;
			$this->showCountInMenu = self::SHOW_COUNT_YES;
			$this->status          = self::STATUS_PRIVATE;
		}

		/**
		 * Compara si las columnas de la vista son iguales
		 *
		 * @param ViewColumn[] $columns
		 *
		 * @return boolean <code>true</code> si son iguales las columnas. <code>false</code> caso contrario
		 */
		private function areColumnsEqual ($columns) {
			if ((empty ($this->columns)) && (empty ($columns))) {
				return true;
			} else if (
				(empty ($this->columns) !== empty ($columns)) ||
				(!is_array ($columns)) ||
				(count ($this->columns) != count ($columns))
			) {
				return false;
			} else {
				foreach ($this->columns as $thisColumn) {
					$equals = false;
					foreach ($columns as $column) {
						if ($column->isEqualTo ($thisColumn)) {
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
		 * Compara si el filtro estandar de la vista es igual a otro
		 *
		 * @param ViewStandardFilter $filter
		 *
		 * @return boolean <code>true</code> si los filtros son iguales. <code>false</code> caso contrario
		 */
		private function areStandardFiltersEqual ($filter) {
			if ((empty ($this->standardFilter)) && (empty ($filter))) {
				return true;
			} else if (empty ($this->standardFilter) !== empty ($filter)) {
				return false;
			} else {
				return $this->standardFilter->isEqualTo ($filter);
			}
		}

		/**
		 * Para cambiar el nombre del modulo que se emplea en la vista
		 *
		 * @param ViewAdvancedFilterGroup[]|ViewColorFilterGroup[]|ViewColumn[]|ViewStandardFilter $elements
		 * @param string $oldModuleName
		 * @param string $newModuleName
		 */
		private function changeModuleName ($elements, $oldModuleName, $newModuleName) {
			if ((empty ($elements)) || ($oldModuleName == $newModuleName)) {
				return;
			}

			if (!is_array ($elements)) {
				$elements = array ($elements);
			}

			$n = count ($elements);
			for ($i = 0; $i < $n; $i++) {
				if (
					(is_object ($elements [ $i ])) &&
					(is_callable (array ($elements [ $i ], 'getModuleName'))) &&
					(is_callable (array ($elements [ $i ], 'setModuleName'))) &&
					($oldModuleName == $elements [ $i ]->getModuleName ())
				) {
					$elements [ $i ]->setModuleName ($newModuleName);
				}
			}
		}

		/**
		 * Para cambiar el nombre de la tabla de los filtros avanzados
		 *
		 * @param string $oldTableName
		 * @param string $newTableName
		 */
		private function changeAdvancedFiltersTableName ($oldTableName, $newTableName) {
			if (empty ($this->advancedFilterGroups)) {
				return;
			}

			$n = count ($this->advancedFilterGroups);
			for ($i = 0; $i < $n; $i++) {
				$this->advancedFilterGroups [ $i ]->setTableName ($oldTableName, $newTableName);
			}
		}

		/**
		 * Para cambiar el nombre de la tabla de los filtros de color
		 *
		 * @param string $oldTableName
		 * @param string $newTableName
		 */
		private function changeColorFiltersTableName ($oldTableName, $newTableName) {
			if (empty ($this->colorFilterGroups)) {
				return;
			}

			$n = count ($this->colorFilterGroups);
			for ($i = 0; $i < $n; $i++) {
				$this->colorFilterGroups [ $i ]->setTableName ($oldTableName, $newTableName);
			}
		}

		/**
		 * Cambia el nombre de la tabla para la vista
		 *
		 * @param ViewColumn[]|ViewStandardFilter $elements
		 * @param string $oldTableName
		 * @param string $newTableName
		 */
		private function changeTableName ($elements, $oldTableName, $newTableName) {
			if (empty ($elements)) {
				return;
			}

			if (is_array ($elements)) {
				$n = count ($elements);
				for ($i = 0; $i < $n; $i++) {
					if (
						(is_object ($elements [ $i ])) &&
						(is_callable (array ($elements [ $i ], 'setTableName'))) &&
						(is_callable (array ($elements [ $i ], 'getTableName'))) &&
						($elements [ $i ]->getTableName () == $oldTableName)
					) {
						$elements [ $i ]->setTableName ($newTableName);
					}
				}
			} else if (
				(is_object ($elements)) &&
				(is_callable (array ($elements, 'setTableName'))) &&
				(is_callable (array ($elements, 'getTableName'))) &&
				($elements->getTableName () == $oldTableName)
			) {
				$elements->setTableName ($newTableName);
			}
		}

		/**
		 * Realiza copia de los grupos de filtros avanzados esten configurados en la vista
		 *
		 * @param integer $viewId
		 * @param ViewAdvancedFilterGroup[] $sourceGroups
		 */
		private function copyAdvancedFilterGroups ($viewId, $sourceGroups) {
			$groups = array ();
			foreach ($sourceGroups as $sourceGroup) {
				$found = false;
				foreach ($this->advancedFilterGroups as $targetGroup) {
					if ($sourceGroup->getSequence () != $targetGroup->getSequence ()) {
						continue;
					} else if (!$targetGroup->isEqualTo ($sourceGroup)) {
						$targetGroup->copyValuesFrom ($sourceGroup);
					}
					$groups [] = $targetGroup;
					$found     = true;
					break;
				}
				if (!$found) {
					$groups [] = $sourceGroup->duplicate ($viewId, null);
				}
			}
			$this->advancedFilterGroups = $groups;
		}

		/**
		 * Realiza copia de los grupos de filtros avanzados esten configurados en la vista cuya fuente sea otra vista
		 *
		 * @param View $view
		 */
		private function copyAdvancedFilterGroupsFrom ($view) {
			$sourceGroups = $view->getAdvancedFilterGroups ();
			if ((empty ($sourceGroups)) && (empty ($this->advancedFilterGroups))) {
				return;
			}

			if (empty ($sourceGroups)) {
				$this->advancedFilterGroups = null;
			} else if (empty ($this->advancedFilterGroups)) {
				$groups = array ();
				foreach ($sourceGroups as $sourceGroup) {
					$groups [] = $sourceGroup->duplicate ($this->id, $sourceGroup->getSequence ());
				}
				$this->advancedFilterGroups = $groups;
			} else {
				$this->copyAdvancedFilterGroups ($view->getId (), $sourceGroups);
			}
		}

		/**
		 * Realiza copia de los grupos de filtro de color configurados en la vista
		 *
		 * @param integer $viewId
		 * @param ViewColorFilterGroup[] $sourceGroups
		 */
		private function copyColorFilterGroups ($viewId, $sourceGroups) {
			$groups = array ();
			foreach ($sourceGroups as $sourceGroup) {
				$found = false;
				foreach ($this->colorFilterGroups as $targetGroup) {
					if ($sourceGroup->getSequence () != $targetGroup->getSequence ()) {
						continue;
					} else if (!$targetGroup->isEqualTo ($sourceGroup)) {
						$targetGroup->copyValuesFrom ($sourceGroup);
					}
					$groups [] = $targetGroup;
					$found     = true;
					break;
				}
				if (!$found) {
					$groups [] = $sourceGroup->duplicate ($viewId, null);
				}
			}
			$this->colorFilterGroups = $groups;
		}

		/**
		 * Realiza copia de los grupos de filtro de color configurados en la vista, cuya fuente sea otra vista
		 *
		 * @param View $view
		 */
		private function copyColorFilterGroupsFrom ($view) {
			$sourceGroups = $view->getColorFilterGroups ();
			if ((empty ($sourceGroups)) && (empty ($this->colorFilterGroups))) {
				return;
			}

			if (empty ($sourceGroups)) {
				$this->colorFilterGroups = null;
			} else if (empty ($this->colorFilterGroups)) {
				$groups = array ();
				foreach ($sourceGroups as $sourceGroup) {
					$groups [] = $sourceGroup->duplicate ($this->id, $sourceGroup->getSequence ());
				}
				$this->colorFilterGroups = $groups;
			} else {
				$this->copyColorFilterGroups ($view->getId (), $sourceGroups);
			}
		}

		/**
		 * Realiza copia de las columnas configuradas en la vista
		 *
		 * @param integer $viewId
		 * @param ViewColumn[] $sourceColumns
		 */
		private function copyColumns ($viewId, $sourceColumns) {
			$columns = array ();
			foreach ($sourceColumns as $sourceColumn) {
				$found = false;
				foreach ($this->columns as $targetColumn) {
					if ($sourceColumn->getSequence () != $targetColumn->getSequence ()) {
						continue;
					} else if (!$targetColumn->isEqualTo ($sourceColumn)) {
						$targetColumn->copyValuesFrom ($sourceColumn);
					}
					$columns [] = $targetColumn;
					$found      = true;
					break;
				}
				if (!$found) {
					$columns [] = $sourceColumn->duplicate ($viewId);
				}
			}
			$this->columns = $columns;
		}

		/**
		 * Realiza copia de las columnas configuradas en la vista, cuya fuente sea otra vista
		 *
		 * @param View $view
		 */
		private function copyColumnsFrom ($view) {
			$sourceColumns = $view->getColumns ();
			if ((empty ($sourceColumns)) && (empty ($this->columns))) {
				return;
			}

			if (empty ($sourceColumns)) {
				$this->columns = null;
			} else if (empty ($this->columns)) {
				$columns = array ();
				foreach ($sourceColumns as $sourceColumn) {
					$columns [] = $sourceColumn->duplicate ($view->getId ());
				}
				$this->columns = $columns;
			} else {
				$this->copyColumns ($view->getId (), $sourceColumns);
			}
		}

		/**
		 * Realiza copia de las columna configuradas en la vista, cuya fuente sea otra vista de un grupo
		 *
		 * @param View $view
		 */
		private function copyViewGroupFrom ($view) {
			if (empty ($this->getViewGroup()) && !empty ($view->getViewGroup())) {
				$this->viewGroup = $view->viewGroup->duplicate ($view->getViewGroup()->getId());
			} else if (!empty($this->getViewGroup())){
				$this->getViewGroup()->copyValuesFrom($view->getViewGroup());
			} else {
				$this->viewGroup = null;
			}
		}

		/**
		 * Realiza copia estandar de los filtros
		 *
		 * @param View $view
		 */
		private function copyStandardFilterFrom ($view) {
			$sourceStandardFilter = $view->getStandardFilter ();
			if ((empty ($sourceStandardFilter)) && (empty ($this->standardFilter))) {
				return;
			}

			if (empty ($sourceStandardFilter)) {
				$this->standardFilter = null;
			} else if (empty ($this->standardFilter)) {
				$this->standardFilter = $sourceStandardFilter->duplicate ($this->id);
			} else {
				$this->standardFilter->copyValuesFrom ($sourceStandardFilter);
			}
		}

		/**
		 * @param View $view
		 *
		 * @return boolean
		 */
		private function isEqualViewGroup ($view) {
			if (!empty ($this->getViewGroup())) {
				return $this->viewGroup->isEqualTo($view->getViewGroup());
			} else if (empty($view->getViewGroup())){
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Para validar que los grupos de filtros avanzados esten correctos
		 *
		 * @throws ViewAdvancedFilterException
		 * @throws ViewAdvancedFilterGroupException
		 * @throws ViewException
		 */
		private function validateAdvancedFilterGroups () {
			if (empty ($this->advancedFilterGroups)) {
				return;
			}

			if (!is_array ($this->advancedFilterGroups)) {
				throw new ViewException (ViewException::ERROR_VIEW_INVALID_ADVANCED_FILTER_GROUPS);
			}

			foreach ($this->advancedFilterGroups as $group) {
				if (!($group instanceof ViewAdvancedFilterGroup)) {
					throw new ViewException (ViewException::ERROR_VIEW_INVALID_ADVANCED_FILTER_GROUP);
				} else {
					$group->validate ();
				}
			}
		}

		/**
		 * Para validar que los grupos de filtros de colo esten correctos
		 *
		 * @throws ViewColorFilterException
		 * @throws ViewColorFilterGroupException
		 * @throws ViewException
		 */
		private function validateColorFilterGroups () {
			if (empty ($this->colorFilterGroups)) {
				return;
			}

			if (!is_array ($this->colorFilterGroups)) {
				throw new ViewException (ViewException::ERROR_VIEW_INVALID_COLOR_FILTER_GROUPS);
			}

			foreach ($this->colorFilterGroups as $group) {
				if (!($group instanceof ViewColorFilterGroup)) {
					throw new ViewException (ViewException::ERROR_VIEW_INVALID_COLOR_FILTER_GROUP);
				} else {
					$group->validate ();
				}
			}
		}

		/**
		 * Para validar que las columnas de las vistas esten correctas
		 *
		 * @throws ViewColumnException
		 * @throws ViewException
		 */
		private function validateColumns () {
			if ((empty ($this->columns)) || (!is_array ($this->columns))) {
				throw new ViewException (ViewException::ERROR_VIEW_INVALID_COLUMNS);
			}

			foreach ($this->columns as $column) {
				if (!($column instanceof ViewColumn)) {
					throw new ViewException (ViewException::ERROR_VIEW_INVALID_COLUMN);
				} else {
					$column->validate ();
				}
			}
		}

		/**
		 * Para validar que el filtro estandar de la vista este correcto
		 *
		 * @throws ViewException
		 * @throws ViewStandardFilterException
		 */
		private function validateStandardFilter () {
			if (empty ($this->standardFilter)) {
				return;
			}

			if (!($this->standardFilter instanceof ViewStandardFilter)) {
				throw new ViewException (ViewException::ERROR_VIEW_INVALID_STANDARD_FILTER);
			} else {
				$this->standardFilter->validate ();
			}
		}

		/**
		 * Para obtener el id de la vista
		 *
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}

		/**
		 * Para obtener el grupo de filtros avanzados de la vista
		 *
		 * @return ViewAdvancedFilterGroup[]
		 */
		public function getAdvancedFilterGroups () {
			return $this->advancedFilterGroups;
		}

		/**
		 * Para obtener los grupos de filtros de colores de la vista
		 *
		 * @return ViewColorFilterGroup[]
		 */
		public function getColorFilterGroups () {
			return $this->colorFilterGroups;
		}

		/**
		 * para obtener las columnas de la vista
		 *
		 * @return ViewColumn[]
		 */
		public function getColumns () {
			return $this->columns;
		}

		/**
		 * Para obtener la vista por defecto
		 *
		 * @return integer
		 */
		public function getDefault () {
			return $this->default;
		}

		/**
		 * Para obtener el nombre del modulo de la vista
		 *
		 * @return string
		 */
		public function getModuleName () {
			return $this->moduleName;
		}

		/**
		 * Para obtener el nombre de la vista
		 *
		 * @return string
		 */
		public function getName () {
			return $this->name;
		}

		/**
		 * Para obtener el propietario de la vista
		 *
		 * @return integer
		 */
		public function getOwner () {
			return $this->owner;
		}

		/**
		 * @return integer
		 */
		public function getSearchView() {
			return $this->searchView;
		}

		/**
		 * Para mostrar el recuento de la vista en el menu
		 *
		 * @return integer
		 */
		public function getShowCountInMenu () {
			return $this->showCountInMenu;
		}

		/**
		 * Para obtener el filtro estandar de la vista
		 *
		 * @return ViewStandardFilter
		 */
		public function getStandardFilter () {
			return $this->standardFilter;
		}

		/**
		 * Para obtener el estatus de la vista
		 *
		 * @return integer
		 */
		public function getStatus () {
			return $this->status;
		}

		/**
		 * @return ViewGroup
		 */
		public function getViewGroup () {
			return $this->viewGroup;
		}

		/**
		 * Para validar si la vista esta eliminada
		 *
		 * @return boolean
		 */
		public function isDeleted () {
			return $this->deleted;
		}
		
		/**
		 * @return integer
		 */
		public function getDeskView () {
			return $this->deskView;
		}
		
		/**
		 * Para validar si la vista esta bloqueada
		 *
		 * @return boolean
		 */
		public function isLocked () {
			return $this->locked;
		}

		/**
		 * Establece el id de la vista
		 *
		 * @param integer $id
		 *
		 * @return View
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}

		/**
		 * Establece el grupo de filtros avanzados de la vista
		 *
		 * @param ViewAdvancedFilterGroup[] $advancedFilterGroups
		 *
		 * @return View
		 */
		public function setAdvancedFilterGroups ($advancedFilterGroups) {
			$this->advancedFilterGroups = $advancedFilterGroups;
			return $this;
		}

		/**
		 * Establece los grupos de filtros de colores de la vista
		 *
		 * @param ViewColorFilterGroup[] $colorFilterGroups
		 *
		 * @return View
		 */
		public function setColorFilterGroups ($colorFilterGroups) {
			$this->colorFilterGroups = $colorFilterGroups;
			return $this;
		}

		/**
		 * Establece las columnas de la vista
		 *
		 * @param ViewColumn[] $columns
		 *
		 * @return View
		 */
		public function setColumns ($columns) {
			$this->columns = $columns;
			return $this;
		}

		/**
		 * Establece la vista por defecto
		 *
		 * @param integer $default
		 *
		 * @return View
		 */
		public function setDefault ($default) {
			if (in_array ($default, array (self::DEFAULT_NO, self::DEFAULT_YES))) {
				$this->default = $default;
			}
			return $this;
		}

		/**
		 * Establece si la vista esta eliminada
		 *
		 * @param boolean $deleted
		 *
		 * @return View
		 */
		public function setDeleted ($deleted) {
			if (is_bool ($deleted)) {
				$this->deleted = $deleted;
			}
			return $this;
		}
		
		/**
		 * @param $deskView
		 *
		 * @return View
		 */
		public function setDeskView ($deskView) {
			$this->deskView = $deskView;
			return $this;
		}
		
		/**
		 * Establece si la vista esta bloqueada
		 *
		 * @param boolean $locked
		 *
		 * @return View
		 */
		public function setLocked ($locked) {
			if (is_bool ($locked)) {
				$this->locked = $locked;
			}
			return $this;
		}

		/**
		 * Establece el nombre del modulo de la vista
		 *
		 * @param string $moduleName
		 *
		 * @return View
		 */
		public function setModuleName ($moduleName) {
			$this->changeModuleName ($this->advancedFilterGroups, $this->moduleName, $moduleName);
			$this->changeModuleName ($this->colorFilterGroups, $this->moduleName, $moduleName);
			$this->changeModuleName ($this->columns, $this->moduleName, $moduleName);
			$this->changeModuleName ($this->standardFilter, $this->moduleName, $moduleName);
			$this->moduleName = $moduleName;
			return $this;
		}

		/**
		 * Establece el nombre de la vista
		 *
		 * @param string $name
		 *
		 * @return View
		 */
		public function setName ($name) {
			$this->name = $name;
			return $this;
		}

		/**
		 * Establece el propietario de la vista
		 *
		 * @param integer $owner
		 *
		 * @return View
		 */
		public function setOwner ($owner) {
			$this->owner = $owner;
			return $this;
		}

		/**
		 * @param integer $searchView
		 *
		 * @return View
		 */
		public function setSearchView ($searchView) {
			if (in_array ($searchView, array (self::SEARCH_NO, self::SEARCH_YES))) {
				$this->searchView = $searchView;
			}
			return $this;
		}

		/**
		 * Establece el recuento de la vista en el menu
		 *
		 * @param integer $showCountInMenu
		 *
		 * @return View
		 */
		public function setShowCountInMenu ($showCountInMenu) {
			if (in_array ($showCountInMenu, array (self::SHOW_COUNT_NO, self::SHOW_COUNT_YES))) {
				$this->showCountInMenu = $showCountInMenu;
			}
			return $this;
		}

		/**
		 * Establece el filtro estandar de la vista
		 *
		 * @param ViewStandardFilter $standardFilter
		 *
		 * @return View
		 */
		public function setStandardFilter ($standardFilter) {
			$this->standardFilter = $standardFilter;
			return $this;
		}

		/**
		 * Establece el estatus de la vista
		 *
		 * @param integer $status
		 *
		 * @return View
		 */
		public function setStatus ($status) {
			if (in_array ($status, array (self::STATUS_APPROVED, self::STATUS_PENDING, self::STATUS_PRIVATE, self::STATUS_PUBLIC))) {
				$this->status = $status;
			}
			return $this;
		}

		/**
		 * @param $viewGroup
		 *
		 * @return View
		 */
		public function setViewGroup ($viewGroup) {
			$this->viewGroup = $viewGroup;
			return $this;
		}

		/**
		 * Para realizar copia de los valores/atributos de la vista
		 *
		 * @param View $view
		 */
		public function copyValuesFrom ($view) {
			if ((empty ($view)) || (!($view instanceof View))) {
				return;
			}

			$this->default         = $view->getDefault ();
			$this->deskView        = $view->getDeskView ();
			$this->moduleName      = $view->getModuleName ();
			$this->name            = $view->getName ();
			$this->searchView      = $view->getSearchView ();
			$this->showCountInMenu = $view->getShowCountInMenu ();
			$this->status          = $view->getStatus ();
			$this->copyViewGroupFrom ($view);
			$this->copyAdvancedFilterGroupsFrom ($view);
			$this->copyColorFilterGroupsFrom ($view);
			$this->copyColumnsFrom ($view);
			$this->copyStandardFilterFrom ($view);
		}

		/**
		 * Duplica los atributos (id, propietario, nombre codigo de los campos) de la vista
		 *
		 * @param integer $newViewId
		 * @param integer $newOwnerId
		 * @param string $oldCodeFieldName
		 * @param string $newCodeFieldName
		 *
		 * @return View
		 * @throws Exception
		 * @throws ViewException
		 */
		public function duplicate ($newViewId, $newOwnerId, $oldCodeFieldName = null, $newCodeFieldName = null) {
			$this->validate ();

			$columns = array ();
			foreach ($this->columns as $column) {
				$columns [] = $column->duplicate ($newViewId, $oldCodeFieldName, $newCodeFieldName);
			}

			if (!empty ($this->advancedFilterGroups)) {
				$advancedFilterGroups = array ();
				foreach ($this->advancedFilterGroups as $group) {
					$advancedFilterGroups [] = $group->duplicate ($newViewId, $group->getSequence (), $oldCodeFieldName, $newCodeFieldName);
				}
			} else {
				$advancedFilterGroups = null;
			}

			if (!empty ($this->colorFilterGroups)) {
				$colorFilterGroups = array ();
				foreach ($this->colorFilterGroups as $group) {
					$colorFilterGroups [] = $group->duplicate ($newViewId, $group->getSequence (), $oldCodeFieldName, $newCodeFieldName);
				}
			} else {
				$colorFilterGroups = null;
			}

			$object = new self ();
			return $object->setId ($newViewId)
				->setAdvancedFilterGroups ($advancedFilterGroups)
				->setColorFilterGroups ($colorFilterGroups)
				->setColumns ($columns)
				->setDefault ($this->default)
				->setDeskView ($this->deskView)
				->setModuleName ($this->moduleName)
				->setName ($this->name)
				->setOwner ($newOwnerId)
				->setSearchView ($this->searchView)
				->setShowCountInMenu ($this->showCountInMenu)
				->setStandardFilter (!empty ($this->standardFilter) ? $this->standardFilter->duplicate ($newViewId) : null)
				->setStatus ($this->status)
				->setViewGroup (!empty($this->getViewGroup()) ? $this->getViewGroup()->duplicate() : null);
		}

		/**
		 * Para comparar si una vista es igual a otra
		 *
		 * @param View $view
		 * @param boolean $deepCheck
		 *
		 * @return boolean
		 */
		public function isEqualTo ($view, $deepCheck = true) {
			if (
				(empty ($view)) ||
				(!($view instanceof View)) ||
				($this->default != $view->getDefault ()) ||
				($this->moduleName != $view->getModuleName ()) ||
				($this->name != $view->getName ()) ||
				($this->showCountInMenu != $view->getShowCountInMenu ()) ||
				($this->status != $view->getStatus ()) ||
				(!$this->isEqualViewGroup ($view)) ||
				(($deepCheck) && ((!$this->areColumnsEqual ($view->getColumns ())) || (!$this->areStandardFiltersEqual ($view->getStandardFilter ()))))
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * Establece el nombre de la tabla que almacenara la vista
		 *
		 * @param string $oldTableName
		 * @param string $newTableName
		 */
		public function setTableName ($oldTableName, $newTableName) {
			$this->changeAdvancedFiltersTableName ($oldTableName, $newTableName);
			$this->changeColorFiltersTableName ($oldTableName, $newTableName);
			$this->changeTableName ($this->columns, $oldTableName, $newTableName);
			$this->changeTableName ($this->standardFilter, $oldTableName, $newTableName);
		}

		/**
		 * Valida que los atributos/valores (nombre y propietario) de la vista esten correctos
		 *
		 * @throws Exception
		 * @throws ViewException
		 */
		public function validate () {
			if ($this->deleted) {
				return;
			} else if (empty ($this->name)) {
				throw new ViewException (ViewException::ERROR_VIEW_EMPTY_NAME);
			} else if (empty ($this->owner)) {
				throw new ViewException (ViewException::ERROR_VIEW_EMPTY_OWNER);
			}
			$this->validateColumns ();
			$this->validateStandardFilter ();
			$this->validateAdvancedFilterGroups ();
			$this->validateColorFilterGroups ();
		}

		/**
		 * Instanciación de la clase View. Se obtiene un objeto View con los valores de la clase
		 *
		 * @return View
		 */
		public static function getInstance () {
			return new self ();
		}

	}
