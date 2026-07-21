<?php
	require_once ('include/platzilla/Exceptions/ModuleException.php');
	require_once ('include/platzilla/Objects/Block.php');
	require_once ('include/platzilla/Objects/HowToUse.php');
	require_once ('include/platzilla/Objects/ModuleInterface.php');
	require_once ('include/platzilla/Objects/Report.php');
	require_once ('include/platzilla/Objects/ReportTemplate.php');
	require_once ('include/platzilla/Objects/View.php');

	/**
	 * Class Module
	 *
	 * En esta clase se define el objeto "Módulo" el cual hace referencia a los módulos que forman parte de una Aplicación.
	 *
	 * @codingStandardsIgnoreStart
	 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
	 * NOTA: PHP Mess Detector reporta una cantidad excesiva (45) de propiedades y métodos públicos, pero resulta imposible reducirlos de momento, pues hasta el momento
	 * se consideran todas necesarias
	 * @codingStandardsIgnoreEnd
	 */
	class Module implements ModuleInterface {

		/** @var integer */
		private $id;

		/** @var BackgroundTask[] */
		private $backgroundTasks;

		/** @var Block[] */
		private $blocks;

		/** @var Button[] */
		private $buttons;

		/** @var CalculationElement[] */
		private $calculatedElements;

		/** @var CalculationSystem[] */
		private $calculationsSystem;

		/** @var CalendarView[] */
		private $calendarViews;

		/** @var Chart[] */
		private $charts;

		/** @var EditableFieldsButton[] */
		private $editableFieldsButtons;

		/** @var string */
		private $entityIdColumnName;

		/** @var integer */
		private $entityCurrentSequence;

		/** @var string */
		private $entityIdentifier;

		/** @var integer */
		private $entityInitialSequence;

		/** @var string */
		private $entityPrefix;

		/** @var string */
		private $fieldIdentifier;

		/** @var GridView[] */
		private $gridViewes;

		/** @var HowToUse[] */
		private $howToUse;

		/** @var boolean */
		private $isEntityType;

		/** @var KanbanView[] */
		private $kanbanViews;

		/** @var string */
		private $label;

		/** @var string */
		private $menuLabel;

		/** @var string */
		private $name;

		/** @var Notification[] */
		private $notifications;

		/** @var PicklistRelationship[] */
		private $picklistRelationship;

		/** @var integer */
		private $presence;

		/** @var integer */
		private $relSequence;

		/** @var Report[] */
		private $reports;

		/** @var ReportTemplate[] */
		private $reportTemplates;

		/** @var ScoringBox[] */
		private $scoringBox;

		/** @var integer */
		private $sequence;

		/** @var boolean */
		private $showInAdminConsole;

		/** @var boolean */
		private $showInSettings;

		/** @var string */
		private $tableName;

		/** @var integer */
		private $type;

		/** @var View[] */
		private $views;

		/**
		 * Module constructor.
		 *
		 * @param boolean $isEntityType
		 * @param string|null $entityPrefix
		 * @param string|null $entityInitialSequence
		 * @param string|null $entityCurrentSequence
		 */
		public function __construct ($isEntityType = false, $entityPrefix = null, $entityInitialSequence = null, $entityCurrentSequence = null) {
			if (is_bool ($isEntityType)) {
				$this->isEntityType = $isEntityType;
				if ($isEntityType) {
					$this->entityPrefix          = $entityPrefix;
					$this->entityInitialSequence = $entityInitialSequence;
					$this->entityCurrentSequence = $entityCurrentSequence;
				} else {
					$this->entityPrefix          = null;
					$this->entityInitialSequence = null;
					$this->entityCurrentSequence = null;
				}
			}
			$this->showInAdminConsole = false;
			$this->showInSettings     = false;
		}

		/**
		 * Compara si dos modulos son iguales
		 *
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

		/**
		 * Cambia a la vista calendario el nombre del módulo
		 *
		 * @param string $oldModuleName
		 * @param string $newModuleName
		 */
		private function changeCalendarViewsModuleName ($oldModuleName, $newModuleName) {
			if (empty ($this->calendarViews)) {
				return;
			}

			$n = count ($this->calendarViews);
			for ($i = 0; $i < $n; $i++) {
				if ($this->calendarViews [ $i ]->getModuleName () == $oldModuleName) {
					$this->calendarViews [ $i ]->setModuleName ($newModuleName);
				}
				if ($this->calendarViews [ $i ]->getFromModuleName () == $oldModuleName) {
					$this->calendarViews [ $i ]->setFromModuleName ($newModuleName);
				}
				if ($this->calendarViews [ $i ]->getTitleModuleName () == $oldModuleName) {
					$this->calendarViews [ $i ]->setTitleModuleName ($newModuleName);
				}
				if ($this->calendarViews [ $i ]->getToModuleName () == $oldModuleName) {
					$this->calendarViews [ $i ]->setToModuleName ($newModuleName);
				}
			}
		}

		/**
		 * Permite cambiar el nombre del modulo
		 *
		 * @param object|BackgroundTask[]|Block[]|Button[]|CalendarView[]|Chart[]|Report[]|View[] $elements
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
		 * Cambia el nombre de la tabla que almacena los atributos que posea el modulo
		 *
		 * @param object|Block[]|Report[]|View[] $elements
		 * @param string $oldTableName
		 * @param string $newTableName
		 */
		private function changeInnerObjectTableName ($elements, $oldTableName, $newTableName) {
			if (empty ($elements)) {
				return;
			}

			if (is_array ($elements)) {
				$n = count ($elements);
				for ($i = 0; $i < $n; $i++) {
					if ((is_object ($elements [ $i ])) && (is_callable (array ($elements [ $i ], 'setTableName')))) {
						$elements [ $i ]->setTableName ($oldTableName, $newTableName);
					}
				}
			} else if ((is_object ($elements)) && (is_callable (array ($elements, 'setTableName')))) {
				$elements->setTableName ($oldTableName, $newTableName);
			}
		}

		/**
		 * Realiza copia de las tareas en segundo plano que realizan acciones sobre los modulos
		 *
		 * @param BackgroundTask[] $sourceTasks
		 */
		private function copyBackgroundTasks ($sourceTasks) {
			$tasks = array ();
			foreach ($sourceTasks as $sourceTask) {
				$found = false;
				foreach ($this->backgroundTasks as $targetTask) {
					if ($sourceTask->getName () != $targetTask->getName ()) {
						continue;
					} else if ((!$targetTask->isDeleted ()) && (!$targetTask->isEqualTo ($sourceTask))) {
						$targetTask->copyValuesFrom ($sourceTask);
					}
					$tasks [] = $targetTask;
					$found    = true;
					break;
				}
				if (!$found) {
					$tasks [] = $sourceTask->duplicate (null);
				}
			}
			$this->backgroundTasks = !empty ($tasks) ? $tasks : null;
		}

		/**
		 * Realiza copia de las tareas de segundo plano desde otras tareas de segundo plano
		 *
		 * @param Module $module
		 */
		private function copyBackgroundTasksFrom ($module) {
			$sourceTasks = $module->getBackgroundTasks ();
			if ((empty ($sourceTasks)) && (empty ($this->backgroundTasks))) {
				return;
			}

			if (empty ($sourceTasks)) {
				$this->backgroundTasks = null;
			} else if (empty ($this->backgroundTasks)) {
				$tasks = array ();
				foreach ($sourceTasks as $sourceTask) {
					$tasks [] = $sourceTask->duplicate (null);
				}
				$this->backgroundTasks = $tasks;
			} else {
				$this->copyBackgroundTasks ($sourceTasks);
			}
		}

		/**
		 * Realiza la accion de copiar bloques asociados a modulos
		 *
		 * @param Block[] $sourceBlocks
		 */
		private function copyBlocks ($sourceBlocks) {
			$blocks = array ();
			foreach ($sourceBlocks as $sourceBlock) {
				$found = false;
				foreach ($this->blocks as $targetBlock) {
					if ($sourceBlock->getLabel () != $targetBlock->getLabel ()) {
						continue;
					} else if ((!$targetBlock->isDeleted ()) && (!$targetBlock->isEqualTo ($sourceBlock))) {
						$targetBlock->copyValuesFrom ($sourceBlock);
					}
					$blocks [] = $targetBlock;
					$found     = true;
					break;
				}
				if (!$found) {
					$blocks [] = $sourceBlock->duplicate (null);
				}
			}
			$this->blocks = !empty ($blocks) ? $blocks : null;
		}

		/**
		 * Realiza copia de bloques de modulos desde otros bloques fuentes se seleccionen
		 *
		 * @param Module $module
		 */
		private function copyBlocksFrom ($module) {
			$sourceBlocks = $module->getBlocks ();
			if ((empty ($sourceBlocks)) && (empty ($this->blocks))) {
				return;
			}

			if (empty ($sourceBlocks)) {
				$this->blocks = null;
			} else if (empty ($this->blocks)) {
				$blocks = array ();
				foreach ($sourceBlocks as $sourceBlock) {
					$blocks [] = $sourceBlock->duplicate (null);
				}
				$this->blocks = $blocks;
			} else {
				$this->copyBlocks ($sourceBlocks);
			}
		}

		/**
		 * Realiza copia de los botones de accion y sus etiquetas asociadas a un modulo
		 *
		 * @param Button[] $sourceButtons
		 */
		private function copyButtons ($sourceButtons) {
			$buttons = array ();
			foreach ($sourceButtons as $sourceButton) {
				$found = false;
				foreach ($this->buttons as $targetButton) {
					if (
						($sourceButton->getAction () != $targetButton->getAction ()) ||
						($sourceButton->getLabel () != $targetButton->getLabel ())
					) {
						continue;
					} else if ((!$targetButton->isDeleted ()) && (!$targetButton->isEqualTo ($sourceButton))) {
						$targetButton->copyValuesFrom ($sourceButton);
					}
					$buttons [] = $targetButton;
					$found      = true;
					break;
				}
				if (!$found) {
					$buttons [] = $sourceButton->duplicate (null);
				}
			}
			$this->buttons = $buttons;
		}

		/**
		 * Realiza copia de los botones de accion y sus etiquetas asociadas a un modulo desde otro modulo
		 *
		 * @param Module $module
		 */
		private function copyButtonsFrom ($module) {
			$sourceButtons = $module->getButtons ();
			if ((empty ($sourceButtons)) && (empty ($this->buttons))) {
				return;
			}

			if (empty ($sourceButtons)) {
				$this->buttons = null;
			} else if (empty ($this->buttons)) {
				$buttons = array ();
				foreach ($sourceButtons as $sourceButton) {
					$buttons [] = $sourceButton->duplicate (null);
				}
				$this->buttons = $buttons;
			} else {
				$this->copyButtons ($sourceButtons);
			}
		}

		/**
		 * Realiza copia de los elementos de calculos que se definiran para el modulo
		 *
		 * @param CalculationElement[] $sourceElements
		 */
		private function copyCalculatedElements ($sourceElements) {
			$elements = array ();
			foreach ($sourceElements as $theElement) {
				$found = false;
				foreach ($this->calculatedElements as $targetElement) {
					if ($targetElement->isEqualTo ($theElement)) {
						$found = true;
						break;
					}
				}
				if (!$found) {
					$elements [] = $theElement;
				}
			}
			$this->calculatedElements = $elements;
		}

		/**
		 * Realiza copia de los elementos de calculos desde otro modulo
		 *
		 * @param Module $module
		 */
		private function copyCalculatedElementsFrom ($module) {
			$sourceElements = $module->getCalculatedElements ();
			if ((empty ($sourceElements)) && (empty ($this->calculatedElements))) {
				return;
			}

			if (empty ($sourceElements)) {
				$this->calculatedElements = null;
			} else if (empty ($this->calculatedElements)) {
				$theElements = array ();
				foreach ($sourceElements as $sourceElement) {
					$theElements [] = $sourceElement->duplicate ();
				}
				$this->calculatedElements = $theElements;
			} else {
				$this->copyCalculatedElements ($sourceElements);
			}
		}

		/**
		 * Realiza copia de los calculos en el sistema
		 *
		 * @param CalculationSystem[] $sourceCalculations
		 */
		private function copyCalculationsSystem ($sourceCalculations) {
			if (empty ($sourceCalculations)) {
				$this->calculationsSystem = array ();
				return;
			}

			$calculations = array ();
			foreach ($sourceCalculations as $theCalculation) {
				if (empty ($this->calculationsSystem)) {
					$calculations [] = $theCalculation;
					continue;
				}

				$found = false;
				foreach ($this->calculationsSystem as $targetCalculation) {
					if ($targetCalculation->isEqualTo ($theCalculation)) {
						$found = true;
						break;
					}
				}
				if (!$found) {
					$calculations [] = $theCalculation;
				}
			}
			$this->calculationsSystem = $calculations;
		}

		/**
		 * Realiza copia de los calculos en el sistema desde una fuente determinada
		 *
		 * @param Module $module
		 */
		private function copyCalculationsSystemFrom ($module) {
			$sourceCalculations = $module->getCalculationsSystem ();
			if ((empty ($sourceCalculations)) && (empty ($this->calculatedElements))) {
				return;
			}

			if (empty ($sourceCalculations)) {
				$this->calculatedElements = null;
			} else if (empty ($this->calculatedElements)) {
				$theCalculations = array ();
				foreach ($sourceCalculations as $sourceElement) {
					$theCalculations [] = $sourceElement->duplicate ();
				}
				$this->calculationsSystem = $theCalculations;
			} else {
				$this->copyCalculationsSystem ($sourceCalculations);
			}
		}

		/**
		 * Raliza copia de las vistas calandarios de los modulos
		 *
		 * @param CalendarView[] $sourceViews
		 */
		private function copyCalendarViews ($sourceViews) {
			$views = array ();
			foreach ($sourceViews as $sourceView) {
				$found = false;
				foreach ($this->calendarViews as $targetView) {
					if ($sourceView->getLabel () != $targetView->getLabel ()) {
						continue;
					} else if ((!$targetView->isDeleted ()) && (!$targetView->isEqualTo ($sourceView))) {
						$targetView->copyValuesFrom ($sourceView);
					}
					$views [] = $targetView;
					$found    = true;
					break;
				}
				if (!$found) {
					$views [] = $sourceView->duplicate (null);
				}
			}
			$this->calendarViews = $views;
		}

		/**
		 * Realiza copia de las vistas calendarios de los modulos desde una fuente determinada
		 *
		 * @param Module $module
		 */
		private function copyCalendarViewsFrom ($module) {
			$sourceViews = $module->getCalendarViews ();
			if ((empty ($sourceViews)) && (empty ($this->calendarViews))) {
				return;
			}

			if (empty ($sourceViews)) {
				$this->calendarViews = null;
			} else if (empty ($this->calendarViews)) {
				$views = array ();
				foreach ($sourceViews as $sourceView) {
					$views [] = $sourceView->duplicate (null);
				}
				$this->calendarViews = $views;
			} else {
				$this->copyCalendarViews ($sourceViews);
			}
		}

		/**
		 * Realiza copia de los atributos de un modulo: campo, titulo y tipo
		 *
		 * @param Chart[] $sourceCharts
		 */
		private function copyCharts ($sourceCharts) {
			$charts = array ();
			foreach ($sourceCharts as $sourceChart) {
				$found = false;
				foreach ($this->charts as $targetChart) {
					if (
						(!empty (array_diff ($sourceChart->getModuleName(), $targetChart->getModuleName()))) ||
						(!empty (array_diff ($sourceChart->getFieldName (), $targetChart->getFieldName ()))) ||
						(!empty (array_diff ($sourceChart->getOperation (), $targetChart->getOperation ()))) ||
						($sourceChart->getTitle () != $targetChart->getTitle ()) ||
						($sourceChart->getType () != $targetChart->getType ())
					) {
						continue;
					} else if ((!$targetChart->isDeleted ()) && (!$targetChart->isEqualTo ($sourceChart))) {
						$targetChart->copyValuesFrom ($sourceChart);
					}
					$charts [] = $targetChart;
					$found     = true;
					break;
				}
				if (!$found) {
					$charts [] = $sourceChart->duplicate (null);
				}
			}
			$this->charts = $charts;
		}

		/**
		 * Raliza copia de los atributos de un modulo: campo, titulo y tipo, desde una fuente determinada
		 *
		 * @param Module $module
		 */
		private function copyChartsFrom ($module) {
			$sourceCharts = $module->getCharts ();
			if ((empty ($sourceCharts)) && (empty ($this->charts))) {
				return;
			}

			if (empty ($sourceCharts)) {
				$this->charts = null;
			} else if (empty ($this->charts)) {
				$charts = array ();
				foreach ($sourceCharts as $sourceChart) {
					$charts [] = $sourceChart->duplicate (null);
				}
				$this->charts = $charts;
			} else {
				$this->copyCharts ($sourceCharts);
			}
		}

		/**
		 * Copia los campos editables desde el listview del modulo
		 *
		 * @param EditableFieldsButton[] $efbs
		 */
		private function copyEditableFieldsButton ($efbs) {
			$editableFieldsButtons = array ();
			foreach ($efbs as $edfb) {
				$found = false;
				foreach ($this->editableFieldsButtons as $target) {
					if ($edfb->getName() != $target->getName()) {
						continue;
					} else if (!$target->isEqualTo ($edfb)) {
						$target->copyValuesFrom ($edfb);
					}
					$editableFieldsButtons [] = $edfb;
					$found    = true;
					break;
				}
				if (!$found) {
					$editableFieldsButtons [] = $edfb->duplicate ();
				}
			}
			$this->editableFieldsButtons = $editableFieldsButtons;
		}

		/**
		 * Copia el boton de edicion de campos disponible en el listview del
		 *
		 * @param Module $module
		 */
		private function copyEditableFieldsButtonFrom ($module) {
			$sources = $module->getEditableFieldsButtons ();
			if(empty($sources) && empty($this->editableFieldsButtons)) {
				return;
			}
			if (empty ($sources)) {
				$this->editableFieldsButtons = null;
			} else if($this->editableFieldsButtons) {
				$efbs = array ();
				foreach ($sources as $source) {
					$efbs [] = $source->duplicate();
				}
				$this->editableFieldsButtons = $efbs;
			} else {
				$this->copyEditableFieldsButton ($sources);
			}
		}

		/**
		 * Copia la vista cuadricula
		 *
		 * @param GridView[] $gridViewes
		 */
		private function copyGridView ($gridViewes) {
			if (empty ($gridViewes)) {
				return;
			}
			$theseGridViews = array ();
			foreach ($gridViewes as $gridView) {
				$found = false;
				foreach ($this->gridViewes as $target) {
					if ($gridView->getGridViewName () != $target->getGridViewName ()) {
						continue;
					} else if (!$target->isEqualTo ($gridView)) {
						$target->copyValuesFrom ($gridView);
					}
					$theseGridViews [] = $gridView;
					$found    = true;
					break;
				}
				if (!$found) {
					$theseGridViews [] = $gridView->duplicate ();
				}
			}
			$this->gridViewes = $theseGridViews;
		}

		/**
		 * Copia la vista cuadricula desde otra
		 *
		 * @param Module $module
		 */
		private function copyGridViewFrom ($module) {
			$sources = $module->getGridViewes ();
			if(empty($sources) && empty($this->gridViewes)) {
				return;
			}
			if (empty ($sources)) {
				$this->gridViewes = null;
			} else if($this->gridViewes) {
				$theseGridViews = array ();
				foreach ($sources as $source) {
					$theseGridViews [] = $source->duplicate();
				}
				$this->gridViewes = $theseGridViews;
			} else {
				$this->copyGridView ($sources);
			}
		}

		/**
		 * @param HowToUse[] $howToUses
		 */
		private function copyHowToUse ($howToUses) {
			if (empty ($howToUses)) {
				return;
			}
			$theseHowToUse = array ();
			foreach ($howToUses as $howToUse) {
				$found = false;
				foreach ($this->howToUse as $target) {
					if ($howToUse->getHowUseName() != $target->getHowUseName()) {
						continue;
					} else if (!$target->isEqualTo ($howToUse)) {
						$target->copyValuesFrom ($howToUse);
					}
					$theseHowToUse [] = $howToUse;
					$found    = true;
					break;
				}
				if (!$found) {
					$theseHowToUse [] = $howToUse->duplicate ();
				}
			}
			$this->howToUse = $theseHowToUse;
		}

		/**
		 * @param Module $module
		 */
		private function copyHowToUseFrom ($module) {
			$sources = $module->getHowToUse ();
			if(empty($sources) && empty($this->howToUse)) {
				return;
			}
			if (empty ($sources)) {
				$this->howToUse = null;
			} else if($this->howToUse) {
				$theseHowToUse = array ();
				foreach ($sources as $source) {
					$theseHowToUse [] = $source->duplicate();
				}
				$this->howToUse = $theseHowToUse;
			} else {
				$this->copyHowToUse ($sources);
			}
		}

		/**
		 * Realiza copia de la vista kanban configurada para un modulo
		 *
		 * @param KanbanView[] $sourceViews
		 */
		public function copyKanbanViews ($sourceViews) {
			$views = array ();
			foreach ($sourceViews as $sourceView) {
				$found = false;
				foreach ($this->kanbanViews as $targetView) {
					if ($sourceView->getKanbaName () != $targetView->getKanbaName ()) {
						continue;
					} else if (!$targetView->isEqualTo ($sourceView)) {
						$targetView->copyValuesFrom ($sourceView);
					}
					$views [] = $targetView;
					$found    = true;
					break;
				}
				if (!$found) {
					$views [] = $sourceView->duplicate (null);
				}
			}
			$this->kanbanViews = $views;
		}

		/**
		 * Realiza copia de la vista kanban desde otro modulo
		 *
		 * @param Module $module
		 */
		private function copyKanbanViewsFrom ($module) {
			$sourceViews = $module->getKanbanViews ();
			if ((empty ($sourceViews)) && (empty ($this->kanbanViews))) {
				return;
			}

			if (empty ($sourceViews)) {
				$this->kanbanViews = null;
			} else if (empty ($this->kanbanViews)) {
				$views = array ();
				foreach ($sourceViews as $sourceView) {
					$views [] = $sourceView->duplicate (null);
				}
				$this->kanbanViews = $views;
			} else {
				$this->copyKanbanViews ($sourceViews);
			}
		}

		/**
		 * Realiza copia de las notificaciones configuradas en el modulo
		 *
		 * @param Notification[] $sourceNotifications
		 */
		private function copyNotifications ($sourceNotifications) {
			$notifications = array ();
			foreach ($sourceNotifications as $sourceNotification) {
				$found = false;
				foreach ($this->notifications as $targetNotification) {
					if (
						($sourceNotification->getName () == $targetNotification->getName ()) ||
						($sourceNotification->getFilter ()->getModuleFilter () == $targetNotification->getFilter ()->getModuleFilter ())
					) {
						continue;
					} else if ((!$targetNotification->isEqualTo ($sourceNotification))) {
						$targetNotification->copyValuesFrom ($sourceNotification);
					}
					$notifications [] = $targetNotification;
					$found            = true;
					break;
				}
				if (!$found) {
					$notifications [] = $sourceNotification->duplicate ();
				}
			}
			$this->notifications = $notifications;
		}

		/**
		 * Realiza copia de las notificaciones desde otro modulo
		 *
		 * @param Module $module
		 */
		private function copyNotificationsFrom ($module) {
			$sourceNotifications = $module->getNotifications ();

			if ((empty ($sourceNotifications)) && (empty ($this->notifications))) {
				return;
			}

			if (empty ($sourceNotifications)) {
				$this->notifications = null;
			} else if (empty ($this->notifications)) {
				$notifications = array ();
				foreach ($sourceNotifications as $sourceNotification) {
					$notifications [] = $sourceNotification->duplicate ();
				}
				$this->notifications = $notifications;
			} else {
				$this->copyNotifications ($sourceNotifications);
			}
		}

		/**
		 * Realiza copia de los informes configurados en el modulo
		 *
		 * @param Report[] $sourceReports
		 */
		private function copyReports ($sourceReports) {
			$reports = array ();
			foreach ($sourceReports as $sourceReport) {
				$found = false;
				foreach ($this->reports as $targetReport) {
					if (($sourceReport->getName () != $targetReport->getName ()) || ($sourceReport->getModuleName () != $targetReport->getModuleName ())) {
						continue;
					} else if ((!$targetReport->isDeleted ()) && (!$targetReport->isEqualTo ($sourceReport))) {
						$targetReport->copyValuesFrom ($sourceReport);
					}
					$reports [] = $targetReport;
					$found      = true;
					break;
				}
				if (!$found) {
					$reports [] = $sourceReport->duplicate (null, 1);
				}
			}
			$this->reports = $reports;
		}

		/**
		 * Para copiar la relación del picklist
		 *
		 * @param PicklistRelationship[] $psrs
		 */
		private function copyPicklistRelationship ($psrs) {
			$relationship = array ();
			foreach ($psrs as $prs) {
				$found = false;
				foreach ($this->picklistRelationship as $target) {
					if ($prs->getRelationshipName () != $target->getRelationshipName ()) {
						continue;
					} else if (!$target->isEqualTo ($prs)) {
						$target->copyValuesFrom ($prs);
					}
					$relationship [] = $prs;
					$found    = true;
					break;
				}
				if (!$found) {
					$relationship [] = $prs->duplicate ();
				}
			}
			$this->picklistRelationship = $relationship;
		}

		/**
		 * Para copiar la relacion del picklist desde una fuente
		 *
		 * @param Module $module
		 */
		private function copyPicklistRelationshipFrom ($module) {
			$sources = $module->getPicklistRelationship ();
			if(empty($sources) && empty($this->picklistRelationship)) {
				return;
			}
			if (empty ($sources)) {
				$this->picklistRelationship = null;
			} else if($this->picklistRelationship) {
				$prs = array ();
				foreach ($sources as $source) {
					$prs [] = $source->duplicate();
				}
				$this->picklistRelationship = $prs;
			} else {
				$this->copyPicklistRelationship ($sources);
			}
		}

		/**
		 * Realiza copia de los informes desde otro modulo
		 *
		 * @param Module $module
		 *
		 * @throws Exception
		 */
		private function copyReportsFrom ($module) {
			$sourceReports = $module->getReports ();
			if ((empty ($sourceReports)) && (empty ($this->reports))) {
				return;
			}

			if (empty ($sourceReports)) {
				$this->reports = null;
			} else if (empty ($this->reports)) {
				$reports = array ();
				foreach ($sourceReports as $sourceReport) {
					$reports [] = $sourceReport->duplicate (null, 1);
				}
				$this->reports = $reports;
			} else {
				$this->copyReports ($sourceReports);
			}
		}

		/**
		 * @param ScoringBox[] $sourceScoringBox
		 */
		private function copyScoringBox ($sourceScoringBox) {
			$scoreBoxes = array ();
			foreach ($sourceScoringBox as $sourceScoreBox) {
				$found = false;
				foreach ($this->scoringBox as $targetScoreBox) {
					if (
						($sourceScoreBox->getTitle () != $targetScoreBox->getTitle ()) ||
						($sourceScoreBox->getScale () != $targetScoreBox->getScale ())
					) {
						continue;
					} else if ((!$targetScoreBox->isEqualTo ($sourceScoreBox))) {
						$targetScoreBox->copyValuesFrom ($sourceScoreBox);
					}
					$scoreBoxes [] = $targetScoreBox;
					$found         = true;
					break;
				}
				if (!$found) {
					$scoreBoxes [] = $sourceScoreBox->duplicate ();
				}
			}
			$this->scoringBox = $scoreBoxes;
		}

		/**
		 * @param Module $module
		 */
		private function copyScoringBoxFrom ($module) {
			$sourceScoringBox = $module->getScoringBox ();
			if (empty ($sourceScoringBox) && empty($this->scoringBox)) {
				return;
			}
			if (empty ($sourceScoringBox)) {
				$this->scoringBox = null;
			} else if (empty ($this->scoringBox)) {
				$scoreBoxes = array ();
				foreach ($sourceScoringBox as $sourceScoreBox) {
					$scoreBoxes [] = $sourceScoreBox->duplicate ();
				}
				$this->scoringBox = $scoreBoxes;
			} else {
				$this->copyScoringBox($sourceScoringBox);
			}
		}

		/**
		 * Realiza copia de informes plantilla
		 *
		 * @param ReportTemplate[] $sourceTemplates
		 */
		private function copyReportTemplates ($sourceTemplates) {
			$templates = array ();
			foreach ($sourceTemplates as $sourceTemplate) {
				$found = false;
				foreach ($this->reportTemplates as $targetTemplate) {
					if (($sourceTemplate->getModuleName () != $targetTemplate->getModuleName ()) ||
						($sourceTemplate->getCode () != $targetTemplate->getCode ())
					) {
						continue;
					} else if ((!$targetTemplate->isDeleted ()) && (!$targetTemplate->isEqualTo ($sourceTemplate))) {
						$targetTemplate->copyValuesFrom ($sourceTemplate);
					}
					$templates [] = $targetTemplate;
					$found        = true;
					break;
				}
				if (!$found) {
					$templates [] = $sourceTemplate->duplicate (null);
				}
			}
			$this->reportTemplates = $templates;
		}

		/**
		 * Realiza copia de informes plantilla desde otra fuente
		 *
		 * @param Module $module
		 */
		private function copyReportTemplatesFrom ($module) {
			$sourceTemplates = $module->getReportTemplates ();
			if ((empty ($sourceTemplates)) && (empty ($this->reportTemplates))) {
				return;
			}

			if (empty ($sourceTemplates)) {
				$this->reportTemplates = null;
			} else if (empty ($this->reportTemplates)) {
				$templates = array ();
				foreach ($sourceTemplates as $sourceTemplate) {
					$templates [] = $sourceTemplate->duplicate (null);
				}
				$this->reportTemplates = $templates;
			} else {
				$this->copyReportTemplates ($sourceTemplates);
			}
		}

		/**
		 * Realiza copia de las vistas de un modulo
		 *
		 * @param View[] $sourceViews
		 */
		private function copyViews ($sourceViews) {
			$views = array ();
			foreach ($sourceViews as $sourceView) {
				$found = false;
				foreach ($this->views as $targetView) {
					if ($sourceView->getName () != $targetView->getName ()) {
						continue;
					} else if ((!$targetView->isDeleted ()) && (!$targetView->isEqualTo ($sourceView))) {
						$targetView->copyValuesFrom ($sourceView);
					}
					$views [] = $targetView;
					$found    = true;
					break;
				}
				if (!$found) {
					$views [] = $sourceView->duplicate (null, 1);
				}
			}
			$this->views = $views;
		}

		/**
		 * Realiza copia de las vistas de un modulo desde otra fuente
		 *
		 * @param Module $module
		 */
		private function copyViewsFrom ($module) {
			$sourceViews = $module->getViews ();
			if ((empty ($sourceViews)) && (empty ($this->views))) {
				return;
			}

			if (empty ($sourceViews)) {
				$this->views = null;
			} else if (empty ($this->views)) {
				$views = array ();
				foreach ($sourceViews as $sourceView) {
					$views [] = $sourceView->duplicate (null, 1);
				}
				$this->views = $views;
			} else {
				$this->copyViews ($sourceViews);
			}
		}

		/**
		 * Realiza duplicacion de las tareas de segundo plano
		 *
		 * @param boolean $removeIds
		 *
		 * @return BackgroundTask[]|null
		 */
		private function duplicateBackgroundTasks ($removeIds) {
			if (empty ($this->backgroundTasks)) {
				return null;
			}

			$tasks = array ();
			foreach ($this->backgroundTasks as $task) {
				$tasks [] = $task->duplicate (!$removeIds ? $task->getId () : null);
			}
			return $tasks;
		}

		/**
		 * Realiza duplicacion de bloques
		 *
		 * @param boolean $removeIds
		 * @param string $oldCodeFieldName
		 * @param string $newCodeFieldName
		 *
		 * @return Block[]|null
		 */
		private function duplicateBlocks ($removeIds, $oldCodeFieldName, $newCodeFieldName) {
			if (empty ($this->blocks)) {
				return null;
			}
			$blocks = array ();
			foreach ($this->blocks as $block) {
				$blockModuleName = $block->getModuleName ();
				if (empty ($blockModuleName)) {
					$block->setModuleName ($this->name);
				}

				$blocks [] = $block->duplicate (!$removeIds ? $block->getId () : null, $oldCodeFieldName, $newCodeFieldName);
			}
			return $blocks;
		}

		/**
		 * Realiza duplicacion de botones
		 *
		 * @param boolean $removeIds
		 *
		 * @return Button[]|null
		 */
		private function duplicateButtons ($removeIds) {
			if (empty ($this->buttons)) {
				return null;
			}

			$buttons = array ();
			foreach ($this->buttons as $button) {
				$buttons [] = $button->duplicate (!$removeIds ? $button->getId () : null);
			}
			return $buttons;
		}

		/**
		 * Realiza duplicacion de los elementos de calculos
		 *
		 * @param boolean $removeIds
		 *
		 * @return CalculationElement[]|null
		 */
		private function duplicateCalculatedElements ($removeIds) {
			if (empty ($this->calculatedElements)) {
				return null;
			}

			$elements = array ();
			foreach ($this->calculatedElements as $theElement) {
				$elements [] = $theElement->duplicate (!$removeIds ? $theElement->getId () : null);
			}
			return $elements;
		}

		/**
		 * Realiza duplicacion de calculos del sistema
		 *
		 * @param boolean $removeIds
		 *
		 * @return CalculationSystem[]|null
		 */
		private function duplicateCalculationsSystem ($removeIds) {
			if (empty ($this->calculationsSystem)) {
				return null;
			}

			$calculations = array ();
			foreach ($this->calculationsSystem as $theCalculation) {
				$calculations [] = $theCalculation->duplicate (!$removeIds ? $theCalculation->getId () : null);
			}
			return $calculations;
		}

		/**
		 * Realiza duplicacion de la vista calendario
		 *
		 * @param boolean $removeIds
		 * @param string $oldCodeFieldName
		 * @param string $newCodeFieldName
		 *
		 * @return CalendarView[]|null
		 */
		private function duplicateCalendarViews ($removeIds, $oldCodeFieldName, $newCodeFieldName) {
			if (empty ($this->calendarViews)) {
				return null;
			}

			$views = array ();
			foreach ($this->calendarViews as $view) {
				$views [] = $view->duplicate (!$removeIds ? $view->getId () : null, $oldCodeFieldName, $newCodeFieldName);
			}
			return $views;
		}

		/**
		 * Realiza duplicacion de los atributos de un modulo (id, nombre del codigo de campo
		 *
		 * @param boolean $removeIds
		 * @param string $oldCodeFieldName
		 * @param string $newCodeFieldName
		 *
		 * @return Chart[]|null
		 */
		private function duplicateCharts ($removeIds, $oldCodeFieldName, $newCodeFieldName) {
			if (empty ($this->charts)) {
				return null;
			}

			$charts = array ();
			foreach ($this->charts as $chart) {
				$charts [] = $chart->duplicate (!$removeIds ? $chart->getId () : null, $oldCodeFieldName, $newCodeFieldName);
			}
			return $charts;
		}

		/**
		 * Realiza duplicado de los campos editables a traves del custombuttons en el listview
		 *
		 * @return EditableFieldsButton[]|null
		 */
		private function duplicateEditableFieldsButton () {
			if (empty($this->editableFieldsButtons)) {
				return null;
			}
			$efbs = array ();
			foreach ($this->editableFieldsButtons as $efb) {
				$efbs [] = $efb->duplicate ();
			}
			return $efbs;
		}

		/**
		 * Duplica la vista cuadricula
		 *
		 * @return GridView[]|null
		 */
		private function duplicateGridView () {
			if (empty($this->gridViewes)) {
				return null;
			}
			$theseGridViews = array ();
			foreach ($this->gridViewes as $thisGridView) {
				$theseGridViews [] = $thisGridView->duplicate ();
			}
			return $theseGridViews;
		}

		private function duplicateHowToUse () {
			if (empty($this->howToUse)) {
				return null;
			}
			$theseHowToUse = array ();
			foreach ($this->howToUse as $thisHowToUse) {
				$theseHowToUse[] = $thisHowToUse->duplicate();
			}
			return $theseHowToUse;
		}

		/**
		 * Realiza duplicado de las vistas kanban
		 *
		 * @param  integer $removeIds
		 *
		 * @return KanbanView[]|null
		 * @throws Exception
		 */
		private function duplicateKanbanView ($removeIds) {
			if (empty ($this->kanbanViews)) {
				return null;
			}

			$kanbans = array ();
			foreach ($this->kanbanViews as $theKanban) {
				$kanbans [] = $theKanban->duplicate (!$removeIds ? $theKanban->getIdKanban () : null);
			}
			return $kanbans;
		}

		/**
		 * Realiza duplicacion de las notificaciones
		 *
		 * @param boolean $removeIds
		 *
		 * @return Notification[]|null
		 */
		private function duplicateNotifications ($removeIds) {
			if (empty ($this->notifications)) {
				return null;
			}

			$notifications = array ();
			foreach ($this->notifications as $notify) {
				$notifications [] = $notify->duplicate (!$removeIds ? $notify->getId () : null);
			}
			return $notifications;
		}

		/**
		 * Duplica el picklist y su relacionamiento
		 *
		 * @return PicklistRelationship[]|null
		 */
		private function duplicatePicklistRelationship () {
			if (empty ($this->picklistRelationship)) {
				return null;
			}
			$psrs = array ();
			foreach ($this->picklistRelationship as $prs) {
				$psrs [] = $prs->duplicate ();
			}
			return $psrs;
		}

		/**
		 * Realiza duplicacion de informes
		 *
		 * @param boolean $removeIds
		 * @param boolean $removeOwner
		 * @param string $oldCodeFieldName
		 * @param string $newCodeFieldName
		 *
		 * @return null|Report[]
		 */
		private function duplicateReports ($removeIds, $removeOwner, $oldCodeFieldName, $newCodeFieldName) {
			if (empty ($this->reports)) {
				return null;
			}

			$reports = array ();
			foreach ($this->reports as $report) {
				$newReportId = !$removeIds ? $report->getId () : null;
				$newOwnerId  = !$removeOwner ? $report->getOwner () : 1;
				$reports []  = $report->duplicate ($newReportId, $newOwnerId, $oldCodeFieldName, $newCodeFieldName);
			}
			return $reports;
		}

		/**
		 * Realiza duplicacion de informes tipo plantillas
		 *
		 * @param boolean $removeIds
		 *
		 * @return ReportTemplate[]|null
		 */
		private function duplicateReportTemplates ($removeIds) {
			if (empty ($this->reportTemplates)) {
				return null;
			}

			$templates = array ();
			foreach ($this->reportTemplates as $template) {
				$templates [] = $template->duplicate (!$removeIds ? $template->getId () : null);
			}
			return $templates;
		}

		/**
		 * @param boolean $removeIds
		 *
		 * @return ScoringBox[]|null
		 */
		private function duplicateScoringBox ($removeIds) {
			if (empty ($this->scoringBox)) {
				return null;
			}
			$scoreBoxes = array ();
			foreach ($this->scoringBox as $scoreBox) {
				$newScoringBoxId = !$removeIds ? $scoreBox->getId () : null;
				$scoreBoxes []   = $scoreBox->duplicate ($newScoringBoxId);
			}
			return $scoreBoxes;
		}

		/**
		 * Realiza duplicacion de las vistas del modulo
		 *
		 * @param boolean $removeIds
		 * @param boolean $removeOwner
		 * @param string $oldCodeFieldName
		 * @param string $newCodeFieldName
		 *
		 * @return View[]|null
		 */
		private function duplicateViews ($removeIds, $removeOwner, $oldCodeFieldName, $newCodeFieldName) {
			if (empty ($this->views)) {
				return null;
			}

			$views = array ();
			foreach ($this->views as $view) {
				$newViewId  = !$removeIds ? $view->getId () : null;
				$newOwnerId = !$removeOwner ? $view->getOwner () : 1;
				$views []   = $view->duplicate ($newViewId, $newOwnerId, $oldCodeFieldName, $newCodeFieldName);
			}
			return $views;
		}

		/**
		 * Realiza comparacion si un modulo es igual en todos sus atributos (tareas en segundo plano, bloques, botones,
		 * elementos de calculos, calculo en el sistema, vista calendario, vista kanban, notificaciones, informes,
		 * informes tipo plantillas y vistas) a otro modulo
		 *
		 * @param Module $module
		 *
		 * @return boolean
		 */
		private function isDeeplyEqualTo ($module) {
			return (!$this->areEqual ($this->backgroundTasks, $module->getBackgroundTasks ())) ||
				   (!$this->areEqual ($this->blocks, $module->getBlocks ())) ||
				   (!$this->areEqual ($this->buttons, $module->getButtons ())) ||
				   (!$this->areEqual ($this->calculatedElements, $module->getCalculatedElements ())) ||
				   (!$this->areEqual ($this->calculationsSystem, $module->getCalculationsSystem ())) ||
				   (!$this->areEqual ($this->calendarViews, $module->getCalendarViews ())) ||
				   (!$this->areEqual ($this->charts, $module->getCharts ())) ||
				   (!$this->areEqual($this->editableFieldsButtons, $module->getEditableFieldsButtons())) ||
				   (!$this->areEqual ($this->gridViewes, $module->getGridViewes())) ||
				   (!$this->areEqual($this->howToUse, $module->getHowToUse())) ||
				   (!$this->areEqual ($this->kanbanViews, $module->getKanbanViews ())) ||
				   (!$this->areEqual ($this->notifications, $module->getNotifications ())) ||
				   (!$this->areEqual ($this->picklistRelationship, $module->getPicklistRelationship ())) ||
				   (!$this->areEqual ($this->reports, $module->getReports ())) ||
				   (!$this->areEqual ($this->scoringBox, $module->getScoringBox())) ||
				   (!$this->areEqual ($this->reportTemplates, $module->getReportTemplates ())) ||
				   (!$this->areEqual ($this->views, $module->getViews ()));
		}

		/**
		 * Valida las tareas en segundo plano del modulo
		 *
		 * @throws BackgroundTaskException
		 * @throws ModuleException
		 */
		private function validateBackgroundTasks () {
			if (empty ($this->backgroundTasks)) {
				return;
			}
			foreach ($this->backgroundTasks as $task) {
				if (empty ($task)) {
					throw new ModuleException (ModuleException::ERROR_MODULE_EMPTY_BACKGROUND_TASK);
				} else if (!($task instanceof BackgroundTask)) {
					throw new ModuleException (ModuleException::ERROR_MODULE_INVALID_BACKGROUND_TASK);
				} else {
					$task->validate ();
				}
			}
		}

		/**
		 * Valida la informacion del bloque para el modulo
		 *
		 * @throws BlockException
		 * @throws ModuleException
		 */
		private function validateBlocks () {
			if (!$this->isEntityType) {
				return;
			}

			if (empty ($this->blocks)) {
				throw new ModuleException (ModuleException::ERROR_MODULE_EMPTY_BLOCKS);
			} else if (!is_array ($this->blocks)) {
				throw new ModuleException (ModuleException::ERROR_MODULE_INVALID_BLOCKS);
			}

			foreach ($this->blocks as $block) {
				if (empty ($block)) {
					throw new ModuleException (ModuleException::ERROR_MODULE_EMPTY_BLOCK);
				} else if (!($block instanceof Block)) {
					throw new ModuleException (ModuleException::ERROR_MODULE_INVALID_BLOCK);
				} else {
					$block->validate ();
				}
			}
		}

		/**
		 * Valida los botones del modulo
		 *
		 * @throws ButtonException
		 * @throws ModuleException
		 */
		private function validateButtons () {
			if (empty ($this->buttons)) {
				return;
			}

			if (!is_array ($this->buttons)) {
				throw new ModuleException (ModuleException::ERROR_MODULE_INVALID_BUTTONS);
			}

			foreach ($this->buttons as $button) {
				if (empty ($button)) {
					throw new ModuleException (ModuleException::ERROR_MODULE_EMPTY_BUTTON);
				} else if (!($button instanceof Button)) {
					throw new ModuleException (ModuleException::ERROR_MODULE_INVALID_BUTTON);
				} else {
					$button->validate ();
				}
			}
		}

		/**
		 * Valida los elementos de calculo del modulo
		 *
		 * @throws CalculationElementException
		 * @throws ModuleException
		 */
		private function validateCalculatedElements () {
			if (empty ($this->calculatedElements)) {
				return;
			}

			if (!is_array ($this->calculatedElements)) {
				throw new ModuleException (ModuleException::ERROR_MODULE_INVALID_CALCULATED_ELEMENT);
			}

			foreach ($this->calculatedElements as $theElement) {
				if (empty ($theElement)) {
					throw new ModuleException (ModuleException::ERROR_MODULE_EMPTY_CALCULATED_ELEMENT);
				} else if (!($theElement instanceof CalculationElement)) {
					throw new ModuleException (ModuleException::ERROR_MODULE_INVALID_CALCULATED_ELEMENT);
				} else {
					$theElement->validate ();
				}
			}
		}

		/**
		 * Valida el Sistema de Calculo del Sistema para el modulo
		 *
		 * @throws CalculationSystemException
		 * @throws ModuleException
		 */
		private function validateCalculationsSystem () {
			if (empty ($this->calculationsSystem)) {
				return;
			}

			if (!is_array ($this->calculationsSystem)) {
				throw new ModuleException (ModuleException::ERROR_MODULE_INVALID_CALCULATION_SYSTEM);
			}

			foreach ($this->calculationsSystem as $theCalculation) {
				if (empty ($theCalculation)) {
					throw new ModuleException (ModuleException::ERROR_MODULE_EMPTY_CALCULATION_SYSTEM);
				} else if (!($theCalculation instanceof CalculationSystem)) {
					throw new ModuleException (ModuleException::ERROR_MODULE_INVALID_CALCULATION_SYSTEM);
				} else {
					$theCalculation->validate ();
				}
			}
		}

		/**
		 * Valida la vista calendario del modulo
		 *
		 * @throws CalendarViewRuleException
		 * @throws CalendarViewException
		 * @throws ModuleException
		 */
		private function validateCalendarViews () {
			if (empty ($this->calendarViews)) {
				return;
			}

			if (!is_array ($this->calendarViews)) {
				throw new ModuleException (ModuleException::ERROR_MODULE_INVALID_CALENDAR_VIEWS);
			}

			foreach ($this->calendarViews as $view) {
				if (empty ($view)) {
					throw new ModuleException (ModuleException::ERROR_MODULE_EMPTY_CALENDAR_VIEW);
				} else if (!($view instanceof CalendarView)) {
					throw new ModuleException (ModuleException::ERROR_MODULE_INVALID_CALENDAR_VIEW);
				} else {
					$view->validate ();
				}
			}
		}

		/**
		 * Valida los atributos del modulo
		 *
		 * @throws ChartException
		 * @throws ModuleException
		 */
		private function validateCharts () {
			if (empty ($this->charts)) {
				return;
			}

			if (!is_array ($this->charts)) {
				throw new ModuleException (ModuleException::ERROR_MODULE_INVALID_CHARTS);
			}

			foreach ($this->charts as $chart) {
				if (empty ($chart)) {
					throw new ModuleException (ModuleException::ERROR_MODULE_EMPTY_CHART);
				} else if (!($chart instanceof Chart)) {
					throw new ModuleException (ModuleException::ERROR_MODULE_INVALID_CHART);
				} else {
					$chart->validate ();
				}
			}
		}

		/**
		 * Valida los campos editables a traves del custombuttons del listview
		 *
		 * @throws EditableFieldsException
		 * @throws ModuleException
		 */
		private function validateEditableFieldsButton() {
			if (empty ($this->editableFieldsButtons)) {
				return;
			}

			if (!is_array ($this->editableFieldsButtons)) {
				throw new ModuleException (ModuleException::ERROR_MODULE_INVALID_EDITABLE_FIELDS_BUTTON);
			}
			foreach ($this->editableFieldsButtons as $efb) {
				$efb->validate();
			}
		}

		/**
		 * Para validar que la vista cuadricula tenga los parametros correctos
		 *
		 * @throws GridViewException
		 * @throws ModuleException
		 */
		private function validateGridView () {
			if (empty ($this->gridViewes)) {
				return;
			}

			if (!is_array ($this->gridViewes)) {
				throw new ModuleException (ModuleException::ERROR_MODULE_INVALID_GRID_VIEW);
			}
			foreach ($this->gridViewes as $gridView) {
				$gridView->validate ();
			}
		}

		/**
		 * @throws HowToUseException
		 * @throws ModuleException
		 */
		private function validateHowToUse () {
			if (empty($this->howToUse)) {
				return;
			}
			if (!is_array($this->howToUse)) {
				throw new ModuleException(ModuleException::ERROR_MODULE_INVALID_HOW_TO_USE);
			}
			foreach ($this->howToUse as $howToUse) {
				$howToUse->validate();
			}
		}

		/**
		 * Valida el codigo de los campos del modulo
		 *
		 * @throws ModuleException
		 */
		private function validateCodeFields () {
			if (!$this->isEntityType) {
				return;
			}

			$allowedCodeFieldsTotal = 1;
			$totalCodeFields        = 0;
			$fields                 = $this->getFields ();
			foreach ($fields as $field) {
				if ($field->isDeleted ()) {
					continue;
				}
				if ($field->getUiType () == FieldInterface::UI_TYPE_CODE) {
					$totalCodeFields++;
				}
				if ($totalCodeFields > $allowedCodeFieldsTotal) {
					throw new ModuleException (ModuleException::ERROR_MODULE_INVALID_CODE_FIELD);
				}
			}
		}

		/**
		 * Valida el identificador de la entidad para el modulo
		 *
		 * @throws ModuleException
		 */
		private function validateEntityIdentifier () {
			if ((!$this->isEntityType) || (empty ($this->entityIdentifier))) {
				return;
			}

			$entityIdentifiers = explode (',', $this->entityIdentifier);
			if (count ($entityIdentifiers) > 1) {
				return;
			}

			$found  = false;
			$fields = $this->getFields ();
			foreach ($fields as $field) {
				if ($field->getColumnName () == $this->entityIdentifier) {
					$found = true;
					break;
				}
			}

			if (!$found) {
				throw new ModuleException (ModuleException::ERROR_MODULE_INVALID_ENTITY_IDENTIFIER);
			}
		}

		/**
		 * Valida el tipo de entidad del modulo
		 *
		 * @throws ModuleException
		 */
		private function validateEntityType () {
			if (!$this->isEntityType) {
				return;
			}

			if ((!empty ($this->entityInitialSequence)) && (!is_numeric ($this->entityInitialSequence))) {
				throw new ModuleException (ModuleException::ERROR_MODULE_INVALID_ENTITY_SEQUENCE);
			}
		}

		/**
		 * Valida las propiedades obligatorias para el modulo (etiquetas, nombre, visibilidad, secuencia y el tipo)
		 *
		 * @throws ModuleException
		 */
		private function validateMandatoryProperties () {
			if (empty ($this->label)) {
				throw new ModuleException (ModuleException::ERROR_MODULE_EMPTY_LABEL);
			} else if (empty ($this->name)) {
				throw new ModuleException (ModuleException::ERROR_MODULE_EMPTY_NAME);
			} else if (!preg_match ('/^([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)$/', $this->name)) {
				throw new ModuleException (ModuleException::ERROR_MODULE_INVALID_NAME);
			} else if (!isset ($this->presence)) {
				throw new ModuleException (ModuleException::ERROR_MODULE_EMPTY_PRESENCE);
			} else if ((isset ($this->sequence)) && (!is_numeric ($this->sequence))) {
				throw new ModuleException (ModuleException::ERROR_MODULE_INVALID_SEQUENCE);
			} else if (!isset ($this->type)) {
				throw new ModuleException (ModuleException::ERROR_MODULE_EMPTY_TYPE);
			}
		}

		/**
		 * Valida la vista calendario del modulo
		 *
		 * @throws KanbanViewException
		 * @throws ModuleException
		 */
		private function validateKanbanViews () {
			if (empty ($this->kanbanViews)) {
				return;
			}

			foreach ($this->kanbanViews as $view) {
				if (empty ($view)) {
					throw new ModuleException (ModuleException::ERROR_MODULE_EMPTY_KANBAN_VIEW);
				} else if (!($view instanceof KanbanView)) {
					throw new ModuleException (ModuleException::ERROR_MODULE_INVALID_KANBAN_VIEW);
				} else {
					$view->validate ();
				}
			}
		}

		/**
		 * Valida las notificaciones del modulo
		 *
		 * @throws ModuleException
		 * @throws NotificationException
		 */
		private function validateNotifications () {
			if (empty ($this->notifications)) {
				return;
			}

			if (!is_array ($this->notifications)) {
				throw new ModuleException (ModuleException::ERROR_MODULE_INVALID_NOTIFICATION);
			}

			foreach ($this->notifications as $notify) {
				if (empty ($notify)) {
					throw new ModuleException (ModuleException::ERROR_MODULE_EMPTY_NOTICATION);
				} else if (!($notify instanceof Notification)) {
					throw new ModuleException (ModuleException::ERROR_MODULE_INVALID_NOTIFICATION);
				} else {
					$notify->validate ();
				}
			}
		}

		/**
		 * Valida el picklist y su relacion
		 *
		 * @throws ModuleException
		 * @throws PicklistRelationshipException
		 */
		private function validatePicklistRelationship () {
			if (empty ($this->picklistRelationship)) {
				return;
			}

			if (!is_array ($this->picklistRelationship)) {
				throw new ModuleException (ModuleException::ERROR_MODULE_INVALID_PICKLIST_RELATIONSHIP);
			}
			foreach ($this->picklistRelationship as $prs) {
				$prs->validate();
			}
		}

		/**
		 * Valida los informes tiene asociados el modulo
		 *
		 * @throws ModuleException
		 * @throws ReportException
		 */
		private function validateReports () {
			if ((!$this->isEntityType) || (empty ($this->reports))) {
				return;
			}

			if (!is_array ($this->reports)) {
				throw new ModuleException (ModuleException::ERROR_MODULE_INVALID_REPORTS);
			}

			foreach ($this->reports as $report) {
				if (empty ($report)) {
					throw new ModuleException (ModuleException::ERROR_MODULE_EMPTY_REPORT);
				} else if (!($report instanceof Report)) {
					throw new ModuleException (ModuleException::ERROR_MODULE_INVALID_REPORT);
				} else {
					$report->validate ();
				}
			}
		}

		/**
		 * Valida los informes plantilla tiene asociados el modulo
		 *
		 * @throws ModuleException
		 * @throws ReportTemplateException
		 */
		private function validateReportTemplates () {
			if (empty ($this->reportTemplates)) {
				return;
			}

			if (!is_array ($this->reportTemplates)) {
				throw new ModuleException (ModuleException::ERROR_MODULE_INVALID_REPORT_TEMPLATES);
			}

			foreach ($this->reportTemplates as $template) {
				if (empty ($template)) {
					throw new ModuleException (ModuleException::ERROR_MODULE_EMPTY_REPORT_TEMPLATE);
				} else if (!($template instanceof ReportTemplate)) {
					throw new ModuleException (ModuleException::ERROR_MODULE_INVALID_REPORT_TEMPLATE);
				} else {
					$template->validate ();
				}
			}
		}

		/**
		 * @throws ModuleException
		 */
		private function validateScoringBox () {
			if (empty($this->scoringBox)) {
				return;
			}
			if (!is_array($this->scoringBox)) {
				throw new ModuleException (ModuleException::ERROR_MODULE_INVALID_SCORING_BOX);
			}
			foreach ($this->scoringBox as $scoreBox) {
				if (empty ($scoreBox)) {
					throw new ModuleException (ModuleException::ERROR_MODULE_EMPTY_SCORING_BOX);
				} else if (!($scoreBox instanceof ScoringBox)) {
					throw new ModuleException (ModuleException::ERROR_MODULE_INVALID_SCORING_BOX);
				}
			}
		}

		/**
		 * Valida las vistas que tiene el modulo
		 *
		 * @throws ModuleException
		 * @throws ViewException
		 */
		private function validateViews () {
			if ((!$this->isEntityType) || (empty ($this->views))) {
				return;
			}

			if (!is_array ($this->views)) {
				throw new ModuleException (ModuleException::ERROR_MODULE_INVALID_VIEWS);
			}

			foreach ($this->views as $view) {
				if (empty ($view)) {
					throw new ModuleException (ModuleException::ERROR_MODULE_EMPTY_VIEW);
				} else if (!($view instanceof View)) {
					throw new ModuleException (ModuleException::ERROR_MODULE_INVALID_VIEW);
				} else {
					$view->validate ();
				}
			}
		}

		/**
		 * Para obtener el id del modulo
		 *
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}

		/**
		 * Para obtener las tareas en segundo plano asociados al modulo
		 *
		 * @return BackgroundTask[]
		 */
		public function getBackgroundTasks () {
			return $this->backgroundTasks;
		}

		/**
		 * Para obtener los bloques tiene el modulo
		 *
		 * @return Block[]
		 */
		public function getBlocks () {
			return $this->blocks;
		}

		/**
		 * Para obtener los botones del modulo
		 *
		 * @return Button[]
		 */
		public function getButtons () {
			return $this->buttons;
		}

		/**
		 * Para obtener los elementos de calculos asociados al modulo
		 *
		 * @return CalculationElement[]
		 */
		public function getCalculatedElements () {
			return $this->calculatedElements;
		}

		/**
		 * Para obtener el sistema de calculos asociados al modulo
		 *
		 * @return CalculationSystem[]
		 */
		public function getCalculationsSystem () {
			return $this->calculationsSystem;
		}

		/**
		 * Para obtener las vistas calendarios asociadas al modulo
		 *
		 * @return CalendarView[]
		 */
		public function getCalendarViews () {
			return $this->calendarViews;
		}

		/**
		 * Para obtener los atributos del modulo
		 *
		 * @return Chart[]
		 */
		public function getCharts () {
			return $this->charts;
		}

		/**
		 * Para obtener los campos editables a traves del custombutton del listview
		 *
		 * @return EditableFieldsButton[]
		 */
		public function getEditableFieldsButtons () {
			return $this->editableFieldsButtons;
		}

		/**
		 * Para obtener la secuencia actual de la entidad
		 *
		 * @return integer
		 */
		public function getEntityCurrentSequence () {
			return $this->entityCurrentSequence;
		}

		/**
		 * Para obtener el nombre de la columna para el ID de la entidad
		 *
		 * @return string
		 */
		public function getEntityIdColumnName () {
			return $this->entityIdColumnName;
		}

		/**
		 * Para obtener el identificador de la entidad
		 *
		 * @return string
		 */
		public function getEntityIdentifier () {
			return $this->entityIdentifier;
		}

		/**
		 * Para obtener la secuencia de la entidad
		 *
		 * @return integer
		 */
		public function getEntityInitialSequence () {
			return $this->entityInitialSequence;
		}

		/**
		 * Para obtener el prefijo de la entidad
		 *
		 * @return string
		 */
		public function getEntityPrefix () {
			return $this->entityPrefix;
		}

		/**
		 * Para obtener los campos del modulo
		 *
		 * @return Field[]|null
		 */
		public function getFields () {
			if (empty ($this->blocks)) {
				return null;
			}

			$moduleFields = array ();
			foreach ($this->blocks as $block) {
				$fields = $block->getFields ();
				if (empty ($fields)) {
					continue;
				}
				$moduleFields = array_merge ($moduleFields, $fields);
			}
			return $moduleFields;
		}

		/**
		 * @return string
		 */
		public function getFieldIdentifier () {
			return $this->fieldIdentifier;
		}

		/**
		 * Para obtener las vistas cuadriculas
		 *
		 * @return GridView[]
		 */
		public function getGridViewes() {
			return $this->gridViewes;
		}

		/**
		 * @return HowToUse[]
		 */
		public function getHowToUse () {
			return $this->howToUse;
		}

		/**
		 * Para obtener el tipo de entidad
		 *
		 * @return boolean
		 */
		public function getIsEntityType () {
			return $this->isEntityType;
		}

		/**
		 * Para obtener la vista kanban
		 *
		 * @return KanbanView[]
		 */
		public function getKanbanViews () {
			return $this->kanbanViews;
		}

		/**
		 * Para obtener las etiquetas del modulo
		 *
		 * @return string
		 */
		public function getLabel () {
			return $this->label;
		}

		/**
		 * Para obtener el menu donde se colocara el modulo
		 *
		 * @return string
		 */
		public function getMenuLabel () {
			return $this->menuLabel;
		}

		/**
		 * Para obtener el nombre del modulo
		 *
		 * @return string
		 */
		public function getName () {
			return $this->name;
		}

		/**
		 * Para obtener las notificaciones del modulo
		 *
		 * @return Notification[]
		 */
		public function getNotifications () {
			return $this->notifications;
		}

		/**
		 * Para obtenr el picklist y su relacion
		 *
		 * @return PicklistRelationship[]
		 */
		public function getPicklistRelationship () {
			return $this->picklistRelationship;
		}

		/**
		 * Para obtener la visibilidad del modulo
		 *
		 * @return integer
		 */
		public function getPresence () {
			return $this->presence;
		}

		/**
		 * Para obtener la secuencia de la relacion
		 *
		 * @return integer
		 */
		public function getRelSequence() {
			return $this->relSequence;
		}

		/**
		 * Para obtener la bandera si el modulo se mostrara para generar informes
		 *
		 * @return Report[]
		 */
		public function getReports () {
			return $this->reports;
		}

		/**
		 * Para obtener la bandera si el modulo se mostrara para generar informes plantilla
		 *
		 * @return ReportTemplate[]
		 */
		public function getReportTemplates () {
			return $this->reportTemplates;
		}

		/**
		 * Para obtener los boxscore o indicadores de gestión
		 *
		 * @return ScoringBox[]
		 */
		public function getScoringBox () {
			return $this->scoringBox;
		}

		/**
		 * Para obtener la secuencia del modulo
		 *
		 * @return integer
		 */
		public function getSequence () {
			return $this->sequence;
		}

		/**
		 * Para obtener la bandera si el modulo se mostrara como un modulo administrativo
		 *
		 * @return boolean
		 */
		public function getShowInAdminConsole () {
			return $this->showInAdminConsole;
		}

		/**
		 * Para obtener la bandera si el modulo se mostrara como un setting de configuracion
		 *
		 * @return boolean
		 */
		public function getShowInSettings () {
			return $this->showInSettings;
		}

		/**
		 * Para obtener el nombre de la tabla del modulo
		 *
		 * @return string
		 */
		public function getTableName () {
			return $this->tableName;
		}

		/**
		 * Para obtener el tipo
		 *
		 * @return integer
		 */
		public function getType () {
			return $this->type;
		}

		/**
		 * Para obtener las vistas del modulo
		 *
		 * @return View[]
		 */
		public function getViews () {
			return $this->views;
		}

		/**
		 * Establece el id del modulo
		 *
		 * @param integer $id
		 *
		 * @return Module
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}

		/**
		 * Establece las tareas en segundo plano asociadas al modulo
		 *
		 * @param BackgroundTask[] $backgroundTasks
		 *
		 * @return Module
		 */
		public function setBackgroundTasks ($backgroundTasks) {
			if (($backgroundTasks === null) || ((is_array ($backgroundTasks)) && (!empty ($backgroundTasks)))) {
				$this->backgroundTasks = $backgroundTasks;
			}
			return $this;
		}

		/**
		 * Establece el bloque para el modulo
		 *
		 * @param Block[] $blocks
		 *
		 * @return Module
		 */
		public function setBlocks ($blocks) {
			$this->blocks = $blocks;
			return $this;
		}

		/**
		 * Establece los botones tendra el modulo con campos al crearse
		 *
		 * @param Button[] $buttons
		 *
		 * @return Module
		 */
		public function setButtons ($buttons) {
			$this->buttons = $buttons;
			return $this;
		}

		/**
		 * Establece los elementos de calculos que estan asociados al modulo
		 *
		 * @param CalculationElement[] $elements
		 *
		 * @return $this
		 */
		public function setCalculatedElements ($elements) {
			$this->calculatedElements = $elements;
			return $this;
		}

		/**
		 * Establece el calculo en el sistema que tiene asociado el modulo
		 *
		 * @param CalculationSystem[] $calculations
		 *
		 * @return $this
		 */
		public function setCalculationsSystem ($calculations) {
			$this->calculationsSystem = $calculations;
			return $this;
		}

		/**
		 * Establece la vista calendario asociada al modulo
		 *
		 * @param CalendarView[] $calendarViews
		 *
		 * @return Module
		 */
		public function setCalendarViews ($calendarViews) {
			$this->calendarViews = $calendarViews;
			return $this;
		}

		/**
		 * Establece los atributos se definieron para el modulo
		 *
		 * @param Chart[] $charts
		 *
		 * @return Module
		 */
		public function setCharts ($charts) {
			$this->charts = $charts;
			return $this;
		}

		/**
		 * @param EditableFieldsButton[] $editableFieldsButtons
		 *
		 * @return Module
		 */
		public function setEditableFieldsButtons ($editableFieldsButtons) {
			$this->editableFieldsButtons = $editableFieldsButtons;
			return $this;
		}

		/**
		 * Establece las vistas cuadriculas
		 *
		 * @param GridView[] $gridViewes
		 *
		 * @return Module
		 */
		public function setGridViewes ($gridViewes) {
			$this->gridViewes = $gridViewes;
			return $this;
		}

		/**
		 * @param HowToUse[] $howtoUse
		 *
		 * @return Module
		 */
		public function setHowToUse ($howtoUse) {
			$this->howToUse = $howtoUse;
			return $this;
		}

		/**
		 * Establece el nombre de la columna para el id de la entidad
		 *
		 * @param string $entityIdColumnName
		 *
		 * @return Module
		 */
		public function setEntityIdColumnName ($entityIdColumnName) {
			$this->entityIdColumnName = $entityIdColumnName;
			return $this;
		}

		/**
		 * Establece el identificador de la entidad
		 *
		 * @param string $entityIdentifier
		 *
		 * @return Module
		 */
		public function setEntityIdentifier ($entityIdentifier) {
			$this->entityIdentifier = $entityIdentifier;
			return $this;
		}

		/**
		 * @param $fieldIdentifier
		 *
		 * @return Module
		 */
		public function setFieldIdentifier ($fieldIdentifier) {
			$this->fieldIdentifier = $fieldIdentifier;
			return $this;
		}

		/**
		 * Establece la vista kanban se le asociara el modulo
		 *
		 * @param KanbanView[] $kanbanViews
		 *
		 * @return Module
		 */
		public function setKanbanView ($kanbanViews) {
			$this->kanbanViews = $kanbanViews;
			return $this;
		}

		/**
		 * Establece las etiquetas del modulo
		 *
		 * @param string $label
		 *
		 * @return Module
		 */
		public function setLabel ($label) {
			$this->label = $label;
			return $this;
		}

		/**
		 * Establece la etiqueta del menu donde se colocara el modulo
		 *
		 * @param string $menuLabel
		 *
		 * @return Module
		 */
		public function setMenuLabel ($menuLabel) {
			$this->menuLabel = $menuLabel;
			return $this;
		}

		/**
		 * Establece los nombres de los atributos tendra el modulo
		 *
		 * @param string $name
		 *
		 * @return Module
		 */
		public function setName ($name) {
			$this->changeModuleName ($this->backgroundTasks, $this->name, $name);
			$this->changeModuleName ($this->blocks, $this->name, $name);
			$this->changeModuleName ($this->buttons, $this->name, $name);
			$this->changeCalendarViewsModuleName ($this->name, $name);
			$this->changeModuleName ($this->charts, $this->name, $name);
			$this->changeModuleName ($this->reports, $this->name, $name);
			$this->changeModuleName ($this->views, $this->name, $name);
			$this->name = $name;
			return $this;
		}

		/**
		 * Establece las notificaciones tendra el modulo
		 *
		 * @param Notification[] $notifications
		 *
		 * @return $this
		 */
		public function setNotifications ($notifications) {
			$this->notifications = $notifications;
			return $this;
		}

		/**
		 * Establece la visibilidad tendra el modulo
		 *
		 * @param integer $presence
		 *
		 * @return Module
		 */
		public function setPresence ($presence) {
			if (in_array ($presence, array (self::PRESENCE_ALWAYS_HIDDEN, self::PRESENCE_HIDDEN, self::PRESENCE_USER_DEFINED, self::PRESENCE_VISIBLE))) {
				$this->presence = $presence;
			}
			return $this;
		}

		/**
		 * Establece el picklist y su relacion para el modulo
		 *
		 * @param PicklistRelationship[] $picklistRelationship
		 *
		 * @return Module
		 */
		public function setPicklistRelationship ($picklistRelationship) {
			$this->picklistRelationship = $picklistRelationship;
			return $this;
		}

		/**
		 * @param integer $relSequence
		 *
		 * @return Module
		 */
		public function setRelSequence($relSequence) {
			$this->relSequence = $relSequence;
			return $this;
		}

		/**
		 * Establece si el modulo sera parte de informes
		 *
		 * @param Report[] $reports
		 *
		 * @return Module
		 */
		public function setReports ($reports) {
			$this->reports = $reports;
			return $this;
		}

		/**
		 * Establece si el modulo sera parte de informes plantillas
		 *
		 * @param ReportTemplate[] $reportTemplates
		 *
		 * @return Module
		 */
		public function setReportTemplates ($reportTemplates) {
			$this->reportTemplates = $reportTemplates;
			return $this;
		}

		/**
		 * Establece si el modulo sera parte del panel de indicadores
		 *
		 * @param ScoringBox[] $scoringBox
		 *
		 * @return Module
		 */
		public function setScoringBox ($scoringBox) {
			$this->scoringBox = $scoringBox;
			return $this;
		}

		/**
		 * Establece la secuencia del modulo
		 *
		 * @param integer $sequence
		 *
		 * @return Module
		 */
		public function setSequence ($sequence) {
			$this->sequence = $sequence;
			return $this;
		}

		/**
		 * Establece la bandera que controla si el modulo se mostrara como un modulo administrativo
		 *
		 * @param boolean $showInAdminConsole
		 *
		 * @return Module
		 */
		public function setShowInAdminConsole ($showInAdminConsole) {
			if (is_bool ($showInAdminConsole)) {
				$this->showInAdminConsole = $showInAdminConsole;
			}
			return $this;
		}

		/**
		 * Establece la bandera que controla si el modulo se mostrara como un setting de configuracion
		 *
		 * @param boolean $showInSettings
		 *
		 * @return Module
		 */
		public function setShowInSettings ($showInSettings) {
			if (is_bool ($showInSettings)) {
				$this->showInSettings = $showInSettings;
			}
			return $this;
		}

		/**
		 * Establece el nombre de la tabla para el modulo
		 *
		 * @param string $tableName
		 *
		 * @return Module
		 */
		public function setTableName ($tableName) {
			$this->tableName = $tableName;
			return $this;
		}

		/**
		 * Establece el tipo de modulo
		 *
		 * @param integer $type
		 *
		 * @return Module
		 */
		public function setType ($type) {
			if (in_array ($type, array (self::TYPE_ADMIN, self::TYPE_USER, self::TYPE_TOOL))) {
				$this->type = $type;
			}
			return $this;
		}

		/**
		 * Establece las vistas del modulo
		 *
		 * @param View[] $views
		 *
		 * @return Module
		 */
		public function setViews ($views) {
			$this->views = $views;
			return $this;
		}

		/**
		 * Cambia el nombre de la tabla del modulo por otro manteniendo los atributos del bloque, informes y vistas
		 *
		 * @param string $oldTableName
		 * @param string $newTableName
		 */
		public function changeTableName ($oldTableName, $newTableName) {
			$this->changeInnerObjectTableName ($this->blocks, $oldTableName, $newTableName);
			$this->changeInnerObjectTableName ($this->reports, $oldTableName, $newTableName);
			$this->changeInnerObjectTableName ($this->views, $oldTableName, $newTableName);
		}

		/**
		 * Para copiar los atributos del modulo
		 *
		 * @param Module $module
		 */
		public function copyValuesFrom ($module) {
			if ((empty ($module)) || (!($module instanceof Module))) {
				return;
			}

			$this->entityIdentifier      = $module->getEntityIdentifier ();
			$this->entityPrefix          = $module->getEntityPrefix ();
			$this->entityInitialSequence = $module->getEntityInitialSequence ();
			$this->fieldIdentifier       = $module->getFieldIdentifier ();
			$this->isEntityType          = $module->getIsEntityType ();
			$this->label                 = $module->getLabel ();
			$this->menuLabel             = $module->getMenuLabel ();
			$this->name                  = $module->getName ();
			$this->presence              = $module->getPresence ();
			$this->relSequence           = $module->getRelSequence ();
			$this->sequence              = $module->getSequence ();
			$this->showInSettings        = $module->getShowInSettings ();
			$this->type                  = $module->getType ();
			$this->copyBackgroundTasksFrom ($module);
			$this->copyBlocksFrom ($module);
			$this->copyButtonsFrom ($module);
			$this->copyCalculatedElementsFrom ($module);
			$this->copyCalculationsSystemFrom ($module);
			$this->copyCalendarViewsFrom ($module);
			$this->copyChartsFrom ($module);
			$this->copyEditableFieldsButtonFrom($module);
			$this->copyGridViewFrom($module);
			$this->copyHowToUseFrom ($module);
			$this->copyKanbanViewsFrom ($module);
			$this->copyNotificationsFrom ($module);
			$this->copyPicklistRelationshipFrom ($module);
			$this->copyReportsFrom ($module);
			$this->copyReportTemplatesFrom ($module);
			$this->copyScoringBoxFrom ($module);
			$this->copyViewsFrom ($module);
		}

		/**
		 * Duplica un modulo con todos sus atributos y valores
		 *
		 * @param boolean $removeIds
		 * @param boolean $removePresence
		 * @param boolean $removeOwner
		 * @param string $newModuleName
		 *
		 * @return Module
		 * @throws BackgroundTaskException
		 * @throws BlockException
		 * @throws ButtonException
		 * @throws CalculationElementException
		 * @throws CalculationSystemException
		 * @throws CalendarViewException
		 * @throws CalendarViewRuleException
		 * @throws ChartException
		 * @throws ModuleException
		 * @throws NotificationException
		 * @throws ReportException
		 * @throws ViewException
		 */
		public function duplicate ($removeIds = false, $removePresence = false, $removeOwner = false, $newModuleName = null) {
			$this->validate ();

			$oldCodeFieldName      = null;
			$newCodeFieldName      = null;
			$newEntityIdColumnName = null;
			if (!empty ($newModuleName)) {
				$moduleName = $newModuleName;
				$fields     = $this->getFields ();
				if (!empty ($fields)) {
					foreach ($fields as $field) {
						if ($field->getUiType () == FieldInterface::UI_TYPE_CODE) {
							$oldCodeFieldName = $field->getName ();
							$newCodeFieldName = "cod_{$newModuleName}";
							break;
						}
					}
					$newEntityIdColumnName = "{$newModuleName}id";
				}
			} else {
				$moduleName = $this->name;
			}

			$duplicatedModule = new self ($this->isEntityType, $this->entityPrefix, $this->entityInitialSequence);
			$duplicatedModule->setId (!$removeIds ? $this->id : null)
				->setBackgroundTasks ($this->duplicateBackgroundTasks ($removeIds))
				->setBlocks ($this->duplicateBlocks ($removeIds, $oldCodeFieldName, $newCodeFieldName))
				->setButtons ($this->duplicateButtons ($removeIds))
				->setCalculatedElements ($this->duplicateCalculatedElements ($removeIds))
				->setCalculationsSystem ($this->duplicateCalculationsSystem ($removeIds))
				->setCalendarViews ($this->duplicateCalendarViews ($removeIds, $oldCodeFieldName, $newCodeFieldName))
				->setCharts ($this->duplicateCharts ($removeIds, $oldCodeFieldName, $newCodeFieldName))
				->setEditableFieldsButtons($this->duplicateEditableFieldsButton())
				->setEntityIdColumnName (isset ($newEntityIdColumnName) ? $newEntityIdColumnName : $this->entityIdColumnName)
				->setEntityIdentifier ($this->entityIdentifier != $oldCodeFieldName ? $this->entityIdentifier : $newCodeFieldName)
				->setFieldIdentifier ($this->fieldIdentifier)
				->setGridViewes ($this->duplicateGridView ())
				->setHowToUse ($this->duplicateHowToUse())
				->setLabel ($this->label)
				->setMenuLabel ($this->menuLabel)
				->setKanbanView ($this->duplicateKanbanView ($removeIds))
				->setNotifications ($this->duplicateNotifications ($removeIds))
				->setPicklistRelationship ($this->duplicatePicklistRelationship ())
				->setPresence (!$removePresence ? $this->presence : ModuleInterface::PRESENCE_ALWAYS_HIDDEN)
				->setRelSequence ($this->relSequence)
				->setReports ($this->duplicateReports ($removeIds, $removeOwner, $oldCodeFieldName, $newCodeFieldName))
				->setReportTemplates ($this->duplicateReportTemplates ($removeIds))
				->setScoringBox ($this->duplicateScoringBox ($removeIds))
				->setSequence ($this->sequence)
				->setShowInAdminConsole ($this->showInAdminConsole)
				->setShowInSettings ($this->showInSettings)
				->setType ($this->type)
				->setViews ($this->duplicateViews ($removeIds, $removeOwner, $oldCodeFieldName, $newCodeFieldName))
				->setName ($moduleName);
			return $duplicatedModule;
		}

		/**
		 * Compara si dos modulos son iguales
		 *
		 * @param Module $module
		 * @param boolean $deepCheck
		 *
		 * @return boolean
		 */
		public function isEqualTo ($module, $deepCheck = true) {
			if (
				(empty ($module)) ||
				(!($module instanceof Module)) ||
				($this->entityIdentifier != $module->getEntityIdentifier ()) ||
				($this->entityPrefix != $module->getEntityPrefix ()) ||
				($this->entityInitialSequence != $module->getEntityInitialSequence ()) ||
				($this->fieldIdentifier != $module->getFieldIdentifier ()) ||
				($this->isEntityType != $module->getIsEntityType ()) ||
				($this->label != $module->getLabel ()) ||
				($this->menuLabel != $module->getMenuLabel ()) ||
				($this->name != $module->getName ()) ||
				($this->sequence != $module->getSequence ()) ||
				($this->presence != $module->getPresence()) ||
				($this->showInSettings != $module->getShowInSettings ()) ||
				($this->type != $module->getType ()) ||
				(($deepCheck) && ($this->isDeeplyEqualTo ($module)))
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * Valida que el modulo posea todos sus atributos
		 *
		 * @throws BackgroundTaskException
		 * @throws BlockException
		 * @throws ButtonException
		 * @throws CalculationElementException
		 * @throws CalculationSystemException
		 * @throws CalendarViewException
		 * @throws CalendarViewRuleException
		 * @throws KanbanViewException
		 * @throws ChartException
		 * @throws EditableFieldsException
		 * @throws GridViewException
		 * @throws HowToUseException
		 * @throws ModuleException
		 * @throws NotificationException
		 * @throws PicklistRelationshipException
		 * @throws ReportException
		 * @throws ScoringBoxException
		 * @throws ViewException
		 */
		public function validate () {
			if (in_array ($this->name, array ('Calendar'))) {
				return;
			}

			$this->validateMandatoryProperties ();
			$this->validateEntityType ();
			$this->validateBackgroundTasks ();
			$this->validateBlocks ();
			$this->validateCodeFields ();
			$this->validateEntityIdentifier ();
			$this->validateButtons ();
			$this->validateCalculatedElements ();
			$this->validateCalculationsSystem ();
			$this->validateCalendarViews ();
			$this->validateCharts ();
			$this->validateEditableFieldsButton();
			$this->validateGridView ();
			$this->validateHowToUse();
			$this->validateKanbanViews ();
			$this->validateNotifications ();
			$this->validatePicklistRelationship ();
			$this->validateReports ();
			$this->validateReportTemplates ();
			$this->validateScoringBox();
			$this->validateViews ();
		}

		/**
		 * Instanciación de la clase Module. Se obtiene un objeto Module con los atributos de la clase
		 *
		 * @param boolean $isEntityType
		 * @param string|null $entityPrefix
		 * @param string|null $entityInitialSequence
		 * @param string|null $entityCurrentSequence
		 *
		 * @return Module
		 */
		public static function getInstance ($isEntityType = false, $entityPrefix = null, $entityInitialSequence = null, $entityCurrentSequence = null) {
			return new self ($isEntityType, $entityPrefix, $entityInitialSequence, $entityCurrentSequence);
		}

	}
