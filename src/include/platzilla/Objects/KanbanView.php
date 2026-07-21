<?php
	require_once ('include/platzilla/Exceptions/ViewException.php');
	require_once ('include/platzilla/Objects/ViewAdvancedFilterGroup.php');
	require_once ('include/platzilla/Objects/ViewInterface.php');
	require_once ('include/platzilla/Objects/ViewStandardFilter.php');
	require_once ('include/platzilla/Exceptions/KanbanViewException.php');
	require_once ('include/platzilla/Objects/KanbanViewInterface.php');

	class KanbanView implements KanbanViewInterface, Serializable {

		/** @var integer */
		private $idKanban;

		/** @var ViewAdvancedFilterGroup[] */
		private $advancedFilterGroups;

		/** @var string */
		private $codeApplication;

		/** @var datetime */
		private $creationDate;

		/** @var string */
		private $fieldName;

		/** @var integer */
		private $idField;

		/** @var integer */
		private $inListView;

		/** @var integer */
		private $isDefaultView;

		/** @var KanbanCardConfig[] */
		private $kanbanCards;

		/** @var KanbanFieldConfig[] */
		private $kanbanFields;

		/** @var string */
		private $kanbanName;

		/** @var string */
		private $label;

		/** @var integer */
		private $idTabModule;

		/** @var @var string */
		private $moduleName;

		/** @var boolean */
		private $locked;

		/** @var ViewStandardFilter */
		private $standardFilter;

		/**
		 * @param array $theseElements
		 * @param array $thoseElements
		 *
		 * @return boolean
		 */
		private function areEqual ($theseElements, $thoseElements) {
			if ((empty ($theseElements)) && (empty ($thoseElements))) {
				return true;
			} else if (
				(empty ($theseElements) !== empty ($thoseElements)) ||
				(!is_array ($thoseElements)) ||
				(count ($theseElements) != count ($thoseElements))
			) {
				return false;
			} else {
				foreach ($theseElements as $thisElement) {
					$equals = false;
					foreach ($thoseElements as $thatElement) {
						/** @noinspection PhpUndefinedMethodInspection */
						if ($thisElement->isEqualTo ($thatElement)) {
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
		 * @param ViewAdvancedFilterGroup[]|ViewColorFilterGroup[]|ViewStandardFilter $elements
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
		 * @param ViewStandardFilter $elements
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
		 * @param integer $viewId
		 * @param ViewAdvancedFilterGroup[] $sourceGroups
		 *
		 * @throws Exception
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
		 * @param KanbanView $view
		 *
		 * @throws Exception
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
					$groups [] = $sourceGroup->duplicate ($this->idKanban, $sourceGroup->getSequence ());
				}
				$this->advancedFilterGroups = $groups;
			} else {
				$this->copyAdvancedFilterGroups ($view->getIdKanban (), $sourceGroups);
			}
		}

		/**
		 * @param KanbanCardConfig[] $kambaCards
		 *
		 * @return array|null
		 */
		private function copyKanbanCard ($kambaCards) {
			if ((empty ($kambaCards)) && (empty ($this->kanbanCards))) {
				return null;
			}
			$cards = array ();
			foreach ($kambaCards as $card) {
				if ((empty ($card)) || (!($card instanceof KanbanCardConfig))) {
					continue;
				}
				$cards [] = $card->duplicate ($card);
			}
			return $cards;
		}

		/**
		 * @param KanbanFieldConfig[] $kambaFields
		 *
		 * @return array|null
		 */
		private function copyKanbanField ($kambaFields) {
			if ((empty ($kambaFields)) && (empty ($this->kanbanFields))) {
				return null;
			}
			$fields = array ();
			foreach ($kambaFields as $field) {
				if ((empty ($field)) || (!($field instanceof KanbanFieldConfig))) {
					continue;
				}
				$fields [] = $field->duplicate ($field);
			}
			return $fields;
		}

		/**
		 * @param integer|null $newViewId
		 *
		 * @return KanbanCardConfig[]|null
		 */
		private function duplicateKanbanCard ($newViewId) {
			if (empty ($this->kanbanCards)) {
				return null;
			}
			$cards = array ();
			foreach ($this->kanbanCards as $card) {
				$newCardId = !empty ($newViewId) ? $newViewId : null;
				$cards []  = $card->duplicate ($newCardId);
			}
			return $cards;
		}

		/**
		 * @param KanbanView $view
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
		 * @param integer|null $newViewId
		 *
		 * @return KanbanFieldConfig[]|null
		 */
		private function duplicateKanbanFiled ($newViewId) {
			if (empty ($this->kanbanFields)) {
				return null;
			}
			$fields = array ();
			foreach ($this->kanbanFields as $kanbanField) {
				$newCardId = !empty ($newViewId) ? $newViewId : null;
				$fields [] = $kanbanField->duplicate ($newCardId);
			}
			return $fields;
		}

		/**
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
		 * @param datetime $date
		 * @param string $format
		 *
		 * @return boolean
		 */
		private function validateDate ($date, $format = 'Y-m-d H:m:s') {
			$objectDate = DateTime::createFromFormat ($format, $date);
			return $objectDate && $objectDate->format ($format) == $date;
		}

		/**
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
		 * @param $kanban
		 *
		 * @throws Exception
		 */
		public function copyValuesFrom ($kanban) {
			if ((empty ($kanban)) || (!($kanban instanceof KanbanView))) {
				return;
			}
			$this->codeApplication = $kanban->getCodeApplication ();
			$this->fieldName       = $kanban->getFieldName ();
			$this->idField         = $kanban->getIdField ();
			$this->inListView      = $kanban->getInListView ();
			$this->isDefaultView   = $kanban->getIsDefaultView ();
			$this->kanbanCards     = $this->copyKanbanCard ($kanban->getKanbanCards ());
			$this->kanbanFields    = $this->copyKanbanField ($kanban->getKanbanField ());
			$this->kanbanName      = $kanban->getKanbaName ();
			$this->label           = $kanban->getLabel ();
			$this->idTabModule     = $kanban->getIdTabModule ();
			$this->moduleName      = $kanban->getModuleName ();
			$this->locked          = $kanban->isLocked ();
			$this->copyStandardFilterFrom ($kanban);
			$this->copyAdvancedFilterGroupsFrom ($kanban);
		}

		/**
		 * @param integer $newKanbanViewId
		 * @param string $oldCodeFieldName
		 * @param string $newCodeFieldName
		 *
		 * @return KanbanView
		 * @throws Exception
		 */
		public function duplicate ($newKanbanViewId = null, $oldCodeFieldName = null, $newCodeFieldName = null) {
			if (!empty ($this->advancedFilterGroups)) {
				$advancedFilterGroups = array ();
				foreach ($this->advancedFilterGroups as $group) {
					$advancedFilterGroups [] = $group->duplicate ($newKanbanViewId, $group->getSequence (), $oldCodeFieldName, $newCodeFieldName);
				}
			} else {
				$advancedFilterGroups = null;
			}
			$object = new self ();
			return $object->setIdKanban (!empty ($newKanbanViewId) ? $newKanbanViewId : null)
				->setAdvancedFilterGroups ($advancedFilterGroups)
				->setCodeApplication ($this->codeApplication)
				->setFieldName ($this->fieldName)
				->setIdField ($this->idField)
				->setInListView ($this->inListView)
				->setDefaultView ($this->isDefaultView)
				->setKanbanCard ($this->duplicateKanbanCard (!empty ($newKanbanViewId) ? $newKanbanViewId : null))
				->setKanbanField ($this->duplicateKanbanFiled (!empty ($newKanbanViewId) ? $newKanbanViewId : null))
				->setKanbanName ($this->kanbanName)
				->setLabel ($this->label)
				->setIdTabModule ($this->idTabModule)
				->setModuleName ($this->moduleName)
				->setStandardFilter (!empty ($this->standardFilter) ? $this->standardFilter->duplicate ($newKanbanViewId) : null)
				->setLocked ($this->locked);
		}

		/**
		 * @return integer
		 */
		public function getIdKanban () {
			return $this->idKanban;
		}

		/**
		 * @return ViewAdvancedFilterGroup[]
		 */
		public function getAdvancedFilterGroups () {
			return $this->advancedFilterGroups;
		}

		/**
		 * @return string
		 */
		public function getCodeApplication () {
			return $this->codeApplication;
		}

		/**
		 * @return datetime
		 */
		public function getCreationDate () {
			return $this->creationDate;
		}

		/**
		 * @return string
		 */
		public function getFieldName () {
			return $this->fieldName;
		}

		/**
		 * @return integer
		 */
		public function getIdField () {
			return $this->idField;
		}

		/**
		 * @return integer
		 */
		public function getInListView () {
			return $this->inListView;
		}

		/**
		 * @return integer
		 */
		public function getIsDefaultView () {
			return $this->isDefaultView;
		}

		/**
		 * @return KanbanCardConfig[]
		 */
		public function getKanbanCards () {
			return $this->kanbanCards;
		}

		/**
		 * @return KanbanFieldConfig[]
		 */
		public function getKanbanField () {
			return $this->kanbanFields;
		}

		/**
		 * @return string
		 */
		public function getKanbaName () {
			return $this->kanbanName;
		}

		/**
		 * @return string
		 */
		public function getLabel () {
			return $this->label;
		}

		/**
		 * @return integer
		 */
		public function getIdTabModule () {
			return $this->idTabModule;
		}

		/**
		 * @return string
		 */
		public function getModuleName () {
			return $this->moduleName;
		}

		/**
		 * @return ViewStandardFilter
		 */
		public function getStandardFilter () {
			return $this->standardFilter;
		}

		/**
		 * @param KanbanView $kanban
		 * @param boolean $deepCheck
		 *
		 * @return boolean
		 */
		public function isEqualTo ($kanban, $deepCheck = true) {
			if (
				(empty ($kanban)) ||
				(!($kanban instanceof KanbanView)) ||
				($this->codeApplication != $kanban->getCodeApplication ()) ||
				($this->fieldName != $kanban->getFieldName ()) ||
				($this->areEqual ($this->kanbanCards, $kanban->getKanbanCards ())) ||
				($this->areEqual ($this->kanbanFields, $kanban->getKanbanField ())) ||
				($this->kanbanName != $kanban->getKanbaName ()) ||
				($this->label != $kanban->getLabel ()) ||
				($this->moduleName != $kanban->getModuleName ()) ||
				(($deepCheck) && (!$this->areStandardFiltersEqual ($kanban->getStandardFilter ())))
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * @return boolean
		 */
		public function isLocked () {
			return $this->locked;
		}

		/**
		 * @param integer $id
		 *
		 * @return KanbanView
		 */
		public function setIdKanban ($id) {
			if ((is_numeric ($id)) && ($id > 0) && (intval ($id) == $id)) {
				$this->idKanban = $id;
			} else {
				$this->idKanban = null;
			}
			return $this;
		}

		/**
		 * @param ViewAdvancedFilterGroup[] $advancedFilterGroups
		 *
		 * @return KanbanView
		 */
		public function setAdvancedFilterGroups ($advancedFilterGroups) {
			$this->advancedFilterGroups = $advancedFilterGroups;
			return $this;
		}

		/**
		 * @param $appCode
		 *
		 * @return KanbanView
		 */
		public function setCodeApplication ($appCode) {
			if (is_scalar ($appCode)) {
				$this->codeApplication = $appCode;
			} else {
				$this->codeApplication = null;
			}
			return $this;
		}

		/**
		 * @param datetime $date
		 *
		 * @return KanbanView
		 */
		public function setCreationDate ($date) {
			if ($this->validateDate ($date)) {
				$this->creationDate = $date;
			} else {
				$this->creationDate = null;
			}
			return $this;
		}

		/**
		 * @param $fieldName
		 *
		 * @return KanbanView
		 */
		public function setFieldName ($fieldName) {
			if (is_scalar ($fieldName)) {
				$this->fieldName = $fieldName;
			} else {
				$this->fieldName = null;
			}
			return $this;
		}

		/**
		 * @param integer $id
		 *
		 * @return KanbanView
		 */
		public function setIdField ($id) {
			if ((is_numeric ($id)) && ($id > 0) && (intval ($id) == $id)) {
				$this->idField = $id;
			} else {
				$this->idField = null;
			}
			return $this;
		}

		/**
		 * @param integer $isVisibleInList
		 *
		 * @return KanbanView
		 */
		public function setInListView ($isVisibleInList) {
			if ($isVisibleInList) {
				$this->inListView = 1;
			} else {
				$this->inListView = 0;
			}
			return $this;
		}

		/**
		 * @param integer $isDefaultView
		 *
		 * @return KanbanView
		 */
		public function setDefaultView ($isDefaultView) {
			if ($isDefaultView) {
				$this->isDefaultView = 1;
			} else {
				$this->isDefaultView = 0;
			}
			return $this;
		}

		/**
		 * @param KanbanCardConfig[] $kanbans
		 *
		 * @return KanbanView
		 */
		public function setKanbanCard ($kanbans) {
			foreach ($kanbans as $kanban) {
				if (($kanban == null) || ($kanban instanceof KanbanCardConfig) && (!empty ($kanban))) {
					$this->kanbanCards[] = $kanban;
				}
			}
			return $this;
		}

		/**
		 * @param KanbanFieldConfig[] $kanbans
		 *
		 * @return KanbanView
		 */
		public function setKanbanField ($kanbans) {
			foreach ($kanbans as $kanban) {
				if (($kanban == null) || ($kanban instanceof KanbanFieldConfig) && (!empty ($kanban))) {
					$this->kanbanFields[] = $kanban;
				}
			}
			return $this;
		}

		/**
		 * @param $kanbanName
		 *
		 * @return $this
		 */
		public function setKanbanName ($kanbanName) {
			if (is_scalar ($kanbanName)) {
				$this->kanbanName = $kanbanName;
			} else {
				$this->kanbanName = null;
			}
			return $this;
		}

		/**
		 * @param $label
		 *
		 * @return KanbanView
		 */
		public function setLabel ($label) {
			if (is_scalar ($label)) {
				$this->label = $label;
			} else {
				$this->label = null;
			}
			return $this;
		}

		/**
		 * @param integer $id
		 *
		 * @return KanbanView
		 */
		public function setIdTabModule ($id) {
			if ((is_numeric ($id)) && ($id > 0) && (intval ($id) == $id)) {
				$this->idTabModule = $id;
			} else {
				$this->idTabModule = null;
			}
			return $this;
		}

		/**
		 * @param $moduleName
		 *
		 * @return KanbanView
		 */
		public function setModuleName ($moduleName) {
			if (is_scalar ($moduleName)) {
				$this->moduleName = $moduleName;
				$this->changeModuleName ($this->advancedFilterGroups, $this->moduleName, $moduleName);
				$this->changeModuleName ($this->standardFilter, $this->moduleName, $moduleName);
			} else {
				$this->moduleName = null;
			}
			return $this;
		}

		/**
		 * @param ViewStandardFilter $standardFilter
		 *
		 * @return KanbanView
		 */
		public function setStandardFilter ($standardFilter) {
			$this->standardFilter = $standardFilter;
			return $this;
		}

		/**
		 * @return string
		 */
		public function serialize () {
			return serialize (
				array (
					$this->idKanban,
					$this->codeApplication,
					$this->fieldName,
					$this->idField,
					$this->inListView,
					$this->isDefaultView,
					$this->kanbanCards,
					$this->kanbanFields,
					$this->kanbanName,
					$this->label,
					$this->idTabModule,
					$this->moduleName,
				)
			);
		}

		/**
		 * @param boolean $locked
		 *
		 * @return KanbanView
		 */
		public function setLocked ($locked) {
			if ($locked) {
				$this->locked = 1;
			} else {
				$this->locked = 0;
			}
			return $this;
		}

		/**
		 * @param string $serialized
		 */
		public function unserialize ($serialized) {
			list (
				$this->idKanban,
				$this->codeApplication,
				$this->fieldName,
				$this->idField,
				$this->inListView,
				$this->isDefaultView,
				$this->kanbanCards,
				$this->kanbanFields,
				$this->kanbanName,
				$this->label,
				$this->idTabModule,
				$this->moduleName,
				) = unserialize ($serialized);
		}

		public function setTableName ($oldTableName, $newTableName) {
			$this->changeAdvancedFiltersTableName ($oldTableName, $newTableName);
			$this->changeTableName ($this->standardFilter, $oldTableName, $newTableName);
		}

		/**
		 * @throws KanbanViewException
		 * @throws Exception
		 */
		public function validate () {
			if (empty ($this->codeApplication)) {
				throw new KanbanViewException (KanbanViewException::ERROR_KANABAN_VIEW_EMPTY_APPLICATION_CODE);
			} else if (empty ($this->fieldName)) {
				throw new KanbanViewException (KanbanViewException::ERROR_KANABAN_VIEW_EMPTY_FIELD_NAME);
			} else if (empty ($this->label)) {
				throw new KanbanViewException (KanbanViewException::ERROR_KANABAN_VIEW_EMPTY_LABEL);
			} else if (empty ($this->idField)) {
				throw new KanbanViewException (KanbanViewException::ERROR_KANABAN_VIEW_EMPTY_FIELD_ID);
			} else if (empty ($this->idTabModule)) {
				throw new KanbanViewException (KanbanViewException::ERROR_KANABAN_VIEW_EMPTY_MODULE_TAB_ID);
			} else if (empty ($this->moduleName)) {
				throw new KanbanViewException (KanbanViewException::ERROR_KANABAN_VIEW_EMPTY_MODULE_NAME);
			}
			$this->validateAdvancedFilterGroups ();
			$this->validateStandardFilter ();
		}

		/**
		 * @return KanbanView
		 */
		public static function getInstance () {
			return new self ();
		}

	}
