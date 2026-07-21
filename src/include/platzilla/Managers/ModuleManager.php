<?php
	require_once ('include/platzilla/Managers/BackgroundTaskManager.php');
	require_once ('include/platzilla/Managers/BlockManager.php');
	require_once ('include/platzilla/Managers/ButtonManager.php');
	require_once ('include/platzilla/Managers/CalendarViewManager.php');
	require_once ('include/platzilla/Managers/ChartManager.php');
	require_once ('include/platzilla/Managers/EditableFieldsManager.php');
	require_once ('include/platzilla/Managers/GridViewManager.php');
	require_once ('include/platzilla/Managers/HowToUseManager.php');
	require_once ('include/platzilla/Managers/CalculationManager.php');
	require_once ('include/platzilla/Managers/KanbanViewManager.php');
	require_once ('include/platzilla/Managers/ModuleProfileManager.php');
	require_once ('include/platzilla/Managers/ModuleRelationshipManager.php');
	require_once ('include/platzilla/Managers/NotificationManager.php');
	require_once ('include/platzilla/Managers/PicklistRelationshipManager.php');
	require_once ('include/platzilla/Managers/ProfileManager.php');
	require_once ('include/platzilla/Managers/ReportsManager.php');
	require_once ('include/platzilla/Managers/ReportTemplateManager.php');
	require_once ('include/platzilla/Managers/ScoringBoxManager.php');
	require_once ('include/platzilla/Managers/ViewManager.php');
	require_once ('include/platzilla/Objects/Module.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/platzilla/Utils/ModuleFilesUtils.php');
	require_once ('include/platzilla/Utils/VtigerUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	/**
	 * Class ModuleManager
	 *
	 * Gestiona los módulos de Platzilla
	 *
	 * @codingStandardsIgnoreStart
	 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
	 * NOTA: PHP Mess Detector reporta una cantidad excesiva (47) de propiedades y métodos públicos, pero resulta imposible reducirlos de momento, pues las funcionalidades
	 * programadas hasta el momento lo requieren. En un futuro se podrán reducir aquellos que hacen búsquedas en la base de datos.
	 * @codingStandardsIgnoreEnd
	 */
	class ModuleManager {

		/** @var ModuleManager[]|null */
		private static $INSTANCES = null;

		/** @var PearDatabase */
		private $adb;

		/**
		 * ModuleManager constructor.
		 *
		 * @param PearDatabase $adb
		 */
		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
		}

		/**
		 * Añadir campos de auditoría (fecha de creación, fecha de modificación, dueño) si el módulo no los incluye)
		 *
		 * @param Module $module
		 */
		private function addAuditingFields ($module) {
			if ((!$module->getIsEntityType ()) || (in_array ($module->getName (), array ('Users', 'formacion_preguntas', 'formacion_pruebas', 'formacion_lecciones', 'formacion_cursos')))) {
				return;
			}

			$blocks     = $module->getBlocks ();
			$firstBlock = $blocks [0];
			$fields     = $module->getFields ();

			$foundOwnerField        = false;
			$foundCreatedTimeField  = false;
			$foundModifiedTimeField = false;

			foreach ($fields as $field) {
				switch ($field->getName ()) {
					case 'assigned_user_id':
						$foundOwnerField = true;
						break;
					case 'createdtime':
						$foundCreatedTimeField = true;
						break;
					case 'modifiedtime':
						$foundModifiedTimeField = true;
						break;
					default:
						// Do nothing
						break;
				}
			}

			$firstBlockFields = $firstBlock->getFields ();
			if (!$foundOwnerField) {
				$firstBlockFields [] = Field::getInstance ()
					->setBlockId ($firstBlock->getId ())
					->setColumnName ('smownerid')
					->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)
					->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)
					->setLabel ('Assigned To')
					->setMandatory (true)
					->setMassEditable (FieldInterface::MASS_EDITABLE_ENABLED)
					->setModuleName ($firstBlock->getModuleName ())
					->setName ('assigned_user_id')
					->setPresence (FieldInterface::PRESENCE_USER_DEFINED)
					->setQuickCreate (FieldInterface::QUICK_CREATE_UNKNOWN)
					->setReadOnly (FieldInterface::READ_WRITE)
					->setTableName ('vtiger_crmentity')
					->setUiType (FieldInterface::UI_TYPE_OWNER);
			}
			if (!$foundCreatedTimeField) {
				$firstBlockFields [] = Field::getInstance ()
					->setBlockId ($firstBlock->getId ())
					->setColumnName ('createdtime')
					->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)
					->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)
					->setLabel ('Created Time')
					->setMandatory (false)
					->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)
					->setModuleName ($firstBlock->getModuleName ())
					->setName ('createdtime')
					->setPresence (FieldInterface::PRESENCE_USER_DEFINED)
					->setQuickCreate (FieldInterface::QUICK_CREATE_UNKNOWN)
					->setReadOnly (FieldInterface::READ_WRITE)
					->setTableName ('vtiger_crmentity')
					->setUiType (FieldInterface::UI_TYPE_CREATED_TIME);
			}
			if (!$foundModifiedTimeField) {
				$firstBlockFields [] = Field::getInstance ()
					->setBlockId ($firstBlock->getId ())
					->setColumnName ('modifiedtime')
					->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)
					->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)
					->setLabel ('Modified Time')
					->setMandatory (false)
					->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)
					->setModuleName ($firstBlock->getModuleName ())
					->setName ('modifiedtime')
					->setPresence (FieldInterface::PRESENCE_USER_DEFINED)
					->setQuickCreate (FieldInterface::QUICK_CREATE_UNKNOWN)
					->setReadOnly (FieldInterface::READ_WRITE)
					->setTableName ('vtiger_crmentity')
					->setUiType (FieldInterface::UI_TYPE_CREATED_TIME);
			}
			$firstBlock->setFields ($firstBlockFields);
		}

		/**
		 * Crear los perfiles por defecto del módulo
		 *
		 * @param Module $module
		 */
		private function createDefaultModuleProfiles ($module) {
			ModuleProfileManager::getInstance ($this->adb)->createDefaultProfiles ($module->getName ());
		}

		/**
		 * Crear los archivos y las carpetas del módulo
		 *
		 * @param Module $module
		 */
		private function createFiles ($module) {
			$rootFolderPath = PlatzillaUtils::getPlatzillaRootFolderPath ();
			if ($module->getIsEntityType ()) {
				ModuleFilesUtils::createFieldsModuleFiles ($module, $rootFolderPath);
			} else {
				ModuleFilesUtils::createSimpleModuleFiles ($module, $rootFolderPath);
			}
		}

		/**
		 * Crear las tablas del módulo
		 *
		 * @param array $moduleTables
		 *
		 * @throws DatabaseException
		 */
		private function createTables ($moduleTables) {
			DatabaseUtils::createModuleTableIfNotExists ($this->adb, $moduleTables ['maintable']['name'], $moduleTables ['maintable']['idcolumn']);
			foreach ($moduleTables ['extratables'] as $table) {
				DatabaseUtils::createModuleTableIfNotExists ($this->adb, $table ['name'], $table ['idcolumn']);
			}
		}

		/**
		 * Eliminar la información de entidad del módulo
		 *
		 * @param Module $module
		 */
		private function deleteEntityName ($module) {
			$this->adb->pquery ('DELETE FROM vtiger_entityname WHERE modulename=?', array ($module->getName ()));
		}

		/**
		 * Eliminar la información de secuencia (prefijo, número de secuencia) del módulo
		 *
		 * @param Module $module
		 */
		private function deleteEntitySequence ($module) {
			$this->adb->pquery ('DELETE FROM vtiger_modentity_num WHERE semodule=?', array ($module->getName ()));
		}

		/**
		 * Eliminar archivos del módulo
		 *
		 * @param Module $module
		 */
		private function deleteFiles ($module) {
			$rootFolderPath   = PlatzillaUtils::getPlatzillaRootFolderPath ();
			$moduleFolderPath = "{$rootFolderPath}/modules/{$module->getName ()}";
			FileSystemUtils::deleteFolder ($moduleFolderPath);
		}

		/**
		 * Eliminar del menú el módulo
		 *
		 * @param Module $module
		 */
		private function deleteMenu ($module) {
			$this->adb->pquery ('DELETE FROM vtiger_parenttabrel WHERE tabid=?', array ($module->getId ()));
		}

		/**
		 * Eliminar las tablas del módulo
		 *
		 * @param array $moduleTables
		 *
		 * @throws DatabaseException
		 */
		private function deleteTables ($moduleTables) {
			foreach ($moduleTables ['extratables'] as $table) {
				DatabaseUtils::deleteTableIfExists ($this->adb, $table ['name']);
			}
			DatabaseUtils::deleteTableIfExists ($this->adb, $moduleTables ['maintable']['name']);
		}

		/**
		 * Elimina el bloque del módulo de la sección de configuración
		 *
		 * @param Module $module
		 */
		private function deleteSettingsBlock ($module) {
			$this->adb->pquery (
				'DELETE FROM
					vtiger_settings_field
				WHERE
					blockid IN (SELECT blockid FROM vtiger_settings_blocks WHERE label=?) AND
					name=?',
				array ('LBL_APPLICATIONS_SETTINGS', $module->getName ())
			);
		}

		/**
		 * Duplicar el lenguaje del archivo
		 *
		 * @param string $oldModuleName
		 * @param string $oldModuleLabel
		 * @param string $newModuleName
		 * @param string $newModuleLabel
		 * @param string $sourceFilePath
		 * @param string $targetFilePath
		 */
		private function duplicateLanguageFile ($oldModuleName, $oldModuleLabel, $newModuleName, $newModuleLabel, $sourceFilePath, $targetFilePath) {
			if ((!$sourceFilePath) || (!file_exists ($sourceFilePath)) || (!is_file ($sourceFilePath))) {
				return;
			}

			$contents = file_get_contents ($sourceFilePath);
			$contents = str_replace ("'ModuleName'", "'{$newModuleName}'", $contents);
			$contents = str_replace ("'SINGLE_ModuleName'", "'SINGLE_{$newModuleName}'", $contents);
			$contents = str_replace ("'Module Name'", "'{$newModuleLabel}'", $contents);
			$contents = str_replace ("'ModuleName ID'", "'{$newModuleName} ID'", $contents);
			$contents = str_replace ("'Module Name ID'", "'{$newModuleLabel} ID'", $contents);

			$contents = str_replace ("'{$oldModuleName}'", "'{$newModuleName}'", $contents);
			$contents = str_replace ("'SINGLE_{$oldModuleName}'", "'SINGLE_{$newModuleName}'", $contents);
			$contents = str_replace ("'{$oldModuleLabel}'", "'{$newModuleLabel}'", $contents);
			$contents = str_replace ("'{$oldModuleName} ID'", "'{$newModuleName} ID'", $contents);
			$contents = str_replace ("'{$oldModuleLabel} ID'", "'{$newModuleLabel}'", $contents);

			file_put_contents ($targetFilePath, $contents);
		}

		/**
		 * Duplicar archivos del módulo
		 *
		 * @param string $oldModuleName
		 * @param string $oldModuleLabel
		 * @param string $newModuleName
		 * @param string $newModuleLabel
		 */
		private function duplicateFiles ($oldModuleName, $oldModuleLabel, $newModuleName, $newModuleLabel) {
			$oldModuleFolderPath = "modules/{$oldModuleName}";
			$oldModuleFileNames  = array_diff (scandir ($oldModuleFolderPath), array ('.', '..'));
			if (empty ($oldModuleFileNames)) {
				return;
			}

			$rootFolderPath      = PlatzillaUtils::getPlatzillaRootFolderPath ();
			$newModuleFolderPath = "{$rootFolderPath}/modules/{$newModuleName}";

			PlatzillaUtils::deleteFolder ($newModuleFolderPath);
			if (!is_dir ("{$newModuleFolderPath}/language")) {
				$oldumask = umask (0);
				mkdir ("{$newModuleFolderPath}/language", 0777, true);
				umask ($oldumask);
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_entityname WHERE tabid IN (SELECT tabid FROM vtiger_tab WHERE name=?)', array ($oldModuleName));
			if ($this->adb->num_rows ($result) > 0) {
				$row           = $this->adb->fetchByAssoc ($result, -1, false);
				$entityIdField = $row ['entityidfield'];
			} else {
				$entityIdField = "{$oldModuleName}id";
			}
			DatabaseUtils::closeResult ($result);
			$result                 = null;
			$newModuleEntityIdField = str_replace ($oldModuleName, $newModuleName, $entityIdField);

			foreach ($oldModuleFileNames as $oldModuleFileName) {
				if (is_dir ("{$oldModuleFolderPath}/{$oldModuleFileName}")) {
					continue;
				} else if ($oldModuleFileName == "{$oldModuleName}.php") {
					$newModuleFileName = "{$newModuleName}.php";
				} else if ($oldModuleFileName == "{$oldModuleName}Ajax.php") {
					$newModuleFileName = "{$newModuleName}Ajax.php";
				} else if ($oldModuleFileName == "{$oldModuleName}.js") {
					$newModuleFileName = "{$newModuleName}.js";
				} else {
					$newModuleFileName = $oldModuleFileName;
				}

				$contents = file_get_contents ("{$oldModuleFolderPath}/{$oldModuleFileName}");
				$contents = str_replace ($entityIdField, '{$_ENTITY_ID_FIELD}', $contents);
				$contents = str_replace ($oldModuleName, $newModuleName, $contents);
				$contents = str_replace ('{$_ENTITY_ID_FIELD}', $newModuleEntityIdField, $contents);
				file_put_contents ("{$newModuleFolderPath}/{$newModuleFileName}", $contents);
			}

			$languageFileNames = array ('de_de.lang.php', 'en_us.lang.php', 'es_es.lang.php', 'pt_br.lang.php');
			foreach ($languageFileNames as $languageFileName) {
				$this->duplicateLanguageFile ($oldModuleName, $oldModuleLabel, $newModuleName, $newModuleLabel, "{$oldModuleFolderPath}/language/{$languageFileName}", "{$newModuleFolderPath}/language/{$languageFileName}");
			}
		}

		/**
		 * Obtener la información de entidad del módulo
		 *
		 * @param string $moduleName
		 *
		 * @return array|null
		 */
		private function fetchEntityIdentifier ($moduleName) {
			$result = $this->adb->pquery ('SELECT * FROM vtiger_entityname WHERE modulename=?', array ($moduleName));
			if ($this->adb->num_rows ($result) > 0) {
				$row              = $this->adb->fetchByAssoc ($result, -1, false);
				$entityIdentifier = array (
					'entityidcolumn'  => $row ['entityidcolumn'],
					'entityidfield'   => $row ['entityidfield'],
					'fieldname'       => $row ['fieldname'],
					'tablename'       => $row ['tablename'],
					'fieldidentifier' => $row ['fieldidentifier'],
				);
			} else {
				$entityIdentifier = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $entityIdentifier;
		}

		/**
		 * Obtener la información de secuencia (prefijo, número de secuencia) del módulo
		 *
		 * @param string $moduleName
		 *
		 * @return array|null
		 */
		private function fetchEntitySequence ($moduleName) {
			$result = $this->adb->pquery ('SELECT * FROM vtiger_modentity_num WHERE semodule=? AND active=1', array ($moduleName));
			if ($this->adb->num_rows ($result) > 0) {
				$row            = $this->adb->fetchByAssoc ($result, -1, false);
				$entitySequence = array (
					'currentid' => $row ['cur_id'],
					'prefix'    => $row ['prefix'],
					'startid'   => $row ['start_id'],
				);
			} else {
				$entitySequence = array (
					'currentid' => null,
					'prefix'    => null,
					'startid'   => null,
				);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $entitySequence;
		}

		/**
		 * Poblar el módulo, con las funcionalidades
		 *
		 * @param array $row
		 * @param boolean $headersOnly
		 * @param boolean $forAnInstance
		 *
		 * @return Module
		 * @throws Exception
		 */
		private function fillModule ($row, $headersOnly, $forAnInstance) {
			$entitySequence          = $this->fetchEntitySequence ($row ['name']);
			$entitySequencePrefix    = $entitySequence ['prefix'];
			$entitySequenceInitialId = $entitySequence ['startid'];
			$entitySequenceCurrentId = $entitySequence ['currentid'];
			$entityIdentifier        = $this->fetchEntityIdentifier ($row ['name']);
			if (!$headersOnly) {
				$backgroundTasks  = BackgroundTaskManager::getInstance ($this->adb)->fetchTasks ($row ['name'], $forAnInstance ? BackgroundTaskInterface::SCOPE_USER : null, $forAnInstance);
				$blocks           = BlockManager::getInstance ($this->adb)->fetchBlocks ($row ['name'], $forAnInstance);
				$buttons          = ButtonManager::getInstance ($this->adb)->fetchButtons ($row ['name'], $forAnInstance);
				$calculations     = CalculationManager::getInstance ($this->adb)->fetchCalculations ($row ['name'], $forAnInstance);
				$calendarViews    = CalendarViewManager::getInstance ($this->adb)->fetchViews ($row ['name'], $forAnInstance);
				$charts           = ChartManager::getInstance ($this->adb)->fetchCharts ($row ['name'], $forAnInstance);
				$eFieldsButton    = EditableFieldsManager::getInstance($this->adb)->fetchEditableButtonsByModule($row ['name']);
				$gridViews        = GridViewManager::getInstance($this->adb)->fetchGridViews($row ['name']);
				$howToUse         = HowToUseManager::getInstance($this->adb)->fetchHowToUse ($row ['name']);
				$elements         = CalculationManager::getInstance ($this->adb)->fetchElements ($row ['name']);
				$kanbanViews      = kanbanViewManager::getInstance ($this->adb)->fetchKanbanViews ($row ['name']);
				$notifications    = NotificationManager::getInstance ($this->adb)->fetchNotifications ($row ['name'], $forAnInstance);
				$pickRelationship = PicklistRelationshipManager::getInstance($this->adb)->fetchPicklistRelationshipByModule($row['name']);
				$reports          = ReportsManager::getInstance ($this->adb)->fetchReports ($row ['name'], $forAnInstance);
				$reportTemplates  = ReportTemplateManager::getInstance ($this->adb)->fetchTemplates ($row ['name'], $forAnInstance);
				$scoringBox       = ScoringBoxManager::getInstance ($this->adb)->fetchScoringBoxes ($row ['name']);
				$views            = ViewManager::getInstance ($this->adb)->fetchViews ($row ['name'], $forAnInstance);
			} else {
				$backgroundTasks  = null;
				$blocks           = null;
				$buttons          = null;
				$calculations     = null;
				$calendarViews    = null;
				$elements         = null;
				$charts           = null;
				$eFieldsButton    = null;
				$gridViews        = null;
				$howToUse         = null;
				$kanbanViews      = null;
				$notifications    = null;
				$pickRelationship = null;
				$reports          = null;
				$reportTemplates  = null;
				$scoringBox       = null;
				$views            = null;
			}

			return Module::getInstance ($row ['isentitytype'] == 1, $entitySequencePrefix, $entitySequenceInitialId, $entitySequenceCurrentId)
				->setId (intval ($row ['tabid']))
				->setBackgroundTasks ($backgroundTasks)
				->setBlocks ($blocks)
				->setButtons ($buttons)
				->setCalculatedElements ($elements)
				->setCalculationsSystem ($calculations)
				->setCalendarViews ($calendarViews)
				->setCharts ($charts)
				->setEditableFieldsButtons ($eFieldsButton)
				->setGridViewes ($gridViews)
				->setHowToUse ($howToUse)
				->setEntityIdColumnName (isset ($entityIdentifier ['entityidcolumn']) ? $entityIdentifier ['entityidcolumn'] : null)
				->setEntityIdentifier (isset ($entityIdentifier ['fieldname']) ? $entityIdentifier ['fieldname'] : null)
				->setFieldIdentifier (isset ($entityIdentifier ['fieldidentifier']) ? $entityIdentifier ['fieldidentifier'] : (($entityIdentifier ['fieldname']) ? $entityIdentifier ['fieldname'] : null))
				->setKanbanView ($kanbanViews)
				->setLabel ($row ['tablabel'])
				->setMenuLabel ($row ['menulabel'])
				->setName ($row ['name'])
				->setPicklistRelationship ($pickRelationship)
				->setNotifications ($notifications)
				->setPresence (intval ($row ['presence']))
				->setRelSequence (isset($row['relsequence']) ? intval($row['relsequence']) : 0)
				->setReports ($reports)
				->setReportTemplates ($reportTemplates)
				->setScoringBox ($scoringBox)
				->setSequence (intval ($row ['tabsequence']))
				->setShowInAdminConsole ($row ['isvisibleinadmin'] == 1 ? true : false)
				->setShowInSettings ($this->isShownOnSettings ($row ['name']))
				->setTableName ($entityIdentifier ['tablename'])
				->setType (intval ($row ['customized']))
				->setViews ($views);
		}

		/**
		 * Ejecutar el proceso de post instalación definido en el archivo de clase del módulo
		 *
		 * @param Module $module
		 * @param boolean $isNewModule
		 */
		private function firePostInstallEvent ($module, $isNewModule) {
			if (($isNewModule) && (!in_array ($module->getName (), array ('Reports')))) {
				VtigerUtils::firePostInstallEvent ($this->adb, $module->getName ());
			}

			$moduleFilePath = __DIR__ . "/../../../modules/{$module->getName ()}/{$module->getName ()}.php";
			if (!file_exists ($moduleFilePath)) {
				return;
			}

			require_once ($moduleFilePath);
			if (is_callable (array ($module->getName (), 'runPostInstallTasks'))) {
				call_user_func ("{$module->getName ()}::runPostInstallTasks", $this->adb, false);
			}
		}

		/**
		 * Ejecutar el proceso de pre desinstalación definido en el archivo de clase del módulo
		 *
		 * @param Module $module
		 */
		private function firePreUninstallEvent ($module) {
			VtigerUtils::firePreUninstallEvent ($this->adb, $module->getName ());

			$moduleFilePath = __DIR__ . "/../../../modules/{$module->getName ()}/{$module->getName ()}.php";
			if (!file_exists ($moduleFilePath)) {
				return;
			}

			require_once ($moduleFilePath);
			if (is_callable (array ($module->getName (), 'runPreUninstallTasks'))) {
				call_user_func ("{$module->getName ()}::runPreUninstallTasks", $this->adb, false);
			}
		}

		/**
		 * Arreglar los datos del módulo:
		 * + Si el nombre del menú suministrado no existe, colocarlo en NULL
		 *
		 * @param Module $module
		 */
		private function fixModuleData ($module) {
			$menuLabel = $module->getMenuLabel ();
			if (!empty ($menuLabel)) {
				$result = $this->adb->pquery ('SELECT * FROM vtiger_parenttab WHERE parenttab_label=?', array ($menuLabel));
			} else {
				$result = null;
			}

			if ($this->adb->num_rows ($result) == 0) {
				$menuLabel = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			$module->setMenuLabel ($menuLabel);
		}

		/**
		 * Obtiene el nombre del menú donde estará ubicado el módulo
		 *
		 * @param Module $module
		 *
		 * @return string|null El nombre del menú o <code>null</code> si es un módulo en configuración
		 */
		private function getMenuLabel ($module) {
			return $module->getShowInSettings () ? null : $module->getMenuLabel ();
		}

		/**
		 * Obtener el próximo número de la secuencia del menu
		 *
		 * @param Module $module
		 *
		 * @return integer
		 */
		private function getNextMenuSequenceNumber ($module) {
			$result = $this->adb->pquery (
				'SELECT
					MAX(ptr.sequence) AS maxsequence
				FROM
					vtiger_parenttabrel ptr
					INNER JOIN vtiger_parenttab pt ON pt.parenttabid=ptr.parenttabid AND pt.parenttab_label=?
				GROUP BY
					pt.parenttab_label',
				array ($module->getMenuLabel ())
			);
			if ($this->adb->num_rows ($result) > 0) {
				$row      = $this->adb->fetchByAssoc ($result, -1, false);
				$sequence = intval ($row ['maxsequence']);
			} else {
				$sequence = 1;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $sequence;
		}

		/**
		 * Obtener el próximo id del modulo
		 *
		 * @param Module $module
		 *
		 * @return integer
		 */
		private function getNextModuleId ($module) {
			$moduleId = isset ($module) ? $module->getId () : null;
			if (!empty ($moduleId)) {
				return $moduleId;
			}

			$result = $this->adb->query ('SELECT MAX(tabid) AS maxid FROM vtiger_tab');
			if ($this->adb->num_rows ($result) > 0) {
				$row      = $this->adb->fetchByAssoc ($result, -1, false);
				$moduleId = (intval ($row ['maxid']) + 1);
			} else {
				$moduleId = 1;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $moduleId;
		}

		/**
		 * Obtener el próximo número de secuencia del módulo
		 *
		 * @param Module $module
		 *
		 * @return integer
		 */
		private function getNextSequenceNumber ($module) {
			$menuLabel = isset ($module) ? $module->getMenuLabel () : null;
			$sequence  = isset ($module) ? $module->getSequence () : null;
			if (!empty ($sequence)) {
				return $sequence;
			} else if (empty ($menuLabel)) {
				return -1;
			}

			$result = $this->adb->pquery ('SELECT MAX(tabsequence) AS maxsequence FROM vtiger_tab WHERE parent=? GROUP BY parent', array ($module->getMenuLabel ()));
			if ($this->adb->num_rows ($result) > 0) {
				$row      = $this->adb->fetchByAssoc ($result, -1, false);
				$sequence = (intval ($row ['maxsequence']) + 1);
			} else {
				$sequence = 1;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $sequence;
		}

		/**
		 * Obtiene el siguiente número de bloque de configuración
		 *
		 * @return integer
		 */
		private function getNextSettingsBlockSequenceNumber () {
			$result = $this->adb->pquery (
				'SELECT
					MAX(sequence) AS sequence
				FROM
					vtiger_settings_field
				WHERE
					blockid IN (SELECT blockid FROM vtiger_settings_blocks WHERE label=?)',
				array ('LBL_APPLICATIONS_SETTINGS')
			);
			if ($this->adb->num_rows ($result) > 0) {
				$row          = $this->adb->fetchByAssoc ($result, -1, false);
				$nextSequence = (intval ($row ['sequence']) + 1);
			} else {
				$nextSequence = 1;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $nextSequence;
		}

		/**
		 * Obtiene la visibilidad del módulo basado en el nombre y el tipo
		 *
		 * @param Module $module
		 *
		 * @return integer
		 */
		private function getPresence ($module) {
			$moduleName = $module->getName ();
			$type       = $module->getType ();
			if ((in_array ($moduleName, array ('Dashboard', 'Events'))) || ($type != Module::TYPE_TOOL)) {
				return $module->getPresence ();
			} else if (($type == Module::TYPE_TOOL) && ($module->getPresence () == Module::PRESENCE_ALWAYS_HIDDEN)) {
				return Module::PRESENCE_ALWAYS_HIDDEN;
			} else {
				return Module::PRESENCE_VISIBLE;
			}
		}

		/**
		 * Determina si un módulo forma parte del core de Platzilla
		 *
		 * @param string $moduleType
		 *
		 * @return integer
		 */
		private function isPlatzilla ($moduleType) {
			return $moduleType == ModuleInterface::TYPE_USER ? 0 : 1;
		}

		/**
		 * Determina si el módulo está en la sección de configuración
		 *
		 * @param string $moduleName
		 *
		 * @return boolean
		 */
		private function isShownOnSettings ($moduleName) {
			if (empty ($moduleName)) {
				return false;
			}

			$result            = $this->adb->pquery (
				'SELECT
					f.*
				FROM
					vtiger_settings_field f
					INNER JOIN vtiger_settings_blocks b ON b.blockid=f.blockid AND b.label=?
				WHERE
					f.name=?',
				array ('LBL_APPLICATIONS_SETTINGS', $moduleName)
			);
			$isShownOnSettings = $this->adb->num_rows ($result) > 0;
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $isShownOnSettings;
		}

		/**
		 * Guardar la información de entidad del módulo (nombre del campo identificador, nombre de la tabla principal, nombre de la columna identificadora)
		 *
		 * @param Module $module
		 * @param string $tableName
		 * @param string $idColumnName
		 */
		private function saveEntityName ($module, $tableName, $idColumnName) {
			$entityIdentifier = $module->getEntityIdentifier ();
			if (empty ($entityIdentifier)) {
				return;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_entityname WHERE modulename=?', array ($module->getName ()));
			if ($this->adb->num_rows ($result) == 0) {
				$this->adb->pquery (
					'INSERT INTO vtiger_entityname (tabid, modulename, tablename, fieldname, entityidfield, entityidcolumn) VALUES (?, ?, ?, ?, ?, ?)',
					array ($module->getId (), $module->getName (), $tableName, $module->getEntityIdentifier (), $idColumnName, $idColumnName)
				);
			} else {
				$this->adb->pquery (
					'UPDATE vtiger_entityname SET tablename=?, fieldname=?, entityidfield=?, entityidcolumn=? WHERE modulename=?',
					array ($tableName, $module->getEntityIdentifier (), $idColumnName, $idColumnName, $module->getName ())
				);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
		}

		/**
		 * Guardar la información de secuencia (prefijo, número de secuencia) del módulo
		 *
		 * @param Module $module
		 */
		private function saveEntitySequence ($module) {
			$prefix   = $module->getEntityPrefix ();
			$sequence = $module->getEntityInitialSequence ();
			if ((empty ($prefix)) || (empty ($sequence))) {
				return;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_modentity_num WHERE semodule=?', array ($module->getName ()));
			if ($this->adb->num_rows ($result) == 0) {
				$numId = $this->adb->getUniqueId ('vtiger_modentity_num');
				$this->adb->pquery (
					'INSERT INTO vtiger_modentity_num (num_id, semodule, prefix, start_id, cur_id, active) VALUES (?, ?, ?, ?, ?, ?)',
					array ($numId, $module->getName (), $prefix, $sequence, $sequence, 1)
				);
			} else {
				$row = $this->adb->fetchByAssoc ($result, -1, false);
				if (($row ['prefix'] != $prefix) || ($row ['start_id'] != $sequence)) {
					$this->adb->pquery (
						'UPDATE vtiger_modentity_num SET prefix=?, start_id=?, active=? WHERE semodule=?',
						array ($prefix, $sequence, 1, $module->getName ())
					);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
		}

		/**
		 * Guardar las dependencias de los campos del módulo
		 *
		 * @param Module $module
		 */
		private function saveFieldDependencies ($module) {
			$fields = $module->getFields ();
			if (empty ($fields)) {
				return;
			}

			foreach ($fields as $field) {
				$dependencies = $field->getDependencies ();
				if (empty ($dependencies)) {
					continue;
				}

				foreach ($dependencies as $dependency) {
					$dependency->setModuleName ($module->getName ());
					FieldDependencyManager::getInstance ($this->adb)->saveDependency ($dependency);
				}
			}
		}

		/**
		 * Guardar la ubicación en el menu del módulo
		 *
		 * @param Module $module
		 */
		private function saveMenu ($module) {
			$menuLabel = $module->getMenuLabel ();
			if ((empty ($menuLabel)) || ($module->getShowInSettings ())) {
				return;
			}
			$this->deleteSettingsBlock ($module);

			$moduleId  = $module->getId ();
			$menuLabel = $module->getMenuLabel ();
			$sequence  = $module->getRelSequence ();
			$result    = $this->adb->pquery (
				'SELECT ptr.*, pt.parenttab_label FROM vtiger_parenttabrel ptr INNER JOIN vtiger_parenttab pt ON pt.parenttabid=ptr.parenttabid WHERE ptr.tabid=?',
				array ($moduleId)
			);
			if ($this->adb->num_rows ($result) == 0) {
				$this->adb->pquery (
					'INSERT INTO vtiger_parenttabrel (parenttabid, tabid, sequence) SELECT parenttabid, ?, ? FROM vtiger_parenttab WHERE parenttab_label=?',
					array ($moduleId, $sequence, $menuLabel)
				);
			} else {
				$row = $this->adb->fetchByAssoc ($result, -1, false);
				if ($row ['parenttab_label'] != $menuLabel) {
					$this->adb->pquery (
						'UPDATE vtiger_parenttabrel SET parenttabid=(SELECT parenttabid FROM vtiger_parenttab WHERE parenttab_label=?), sequence=? WHERE tabid=?',
						array ($menuLabel, $sequence, $moduleId)
					);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
		}

		/**
		 * Guardar la ubicación del módulo en la pestaña de aplicaciones del menú de configuración
		 *
		 * @param Module $module
		 */
		private function saveSettingsBlock ($module) {
			if (!$module->getShowInSettings ()) {
				return;
			}
			$this->deleteMenu ($module);

			$result = $this->adb->pquery (
				'SELECT
					*
				FROM
					vtiger_settings_field
				WHERE
					blockid IN (SELECT blockid FROM vtiger_settings_blocks WHERE label=?) AND
					name=?',
				array ('LBL_APPLICATIONS_SETTINGS', $module->getName ())
			);
			if ($this->adb->num_rows ($result) == 0) {
				$blockId  = $this->adb->getUniqueID ('vtiger_settings_field');
				$sequence = $this->getNextSettingsBlockSequenceNumber ();
				$this->adb->pquery (
					'INSERT INTO vtiger_settings_field (fieldid, blockid, tab, name, iconpath, description, linkto, sequence, active)
					VALUES (?, (SELECT blockid FROM vtiger_settings_blocks WHERE label=? LIMIT 1), ?, ?, ?, ?, ?, ?, ?)',
					array ($blockId, 'LBL_APPLICATIONS_SETTINGS', null, $module->getName (), 'fa fa-cogs yellow-bg', '', "index.php?module={$module->getName ()}&action=index&parenttab=Settings", $sequence, 0)
				);
			} else {
				$row = $this->adb->fetchByAssoc ($result, -1, false);
				$this->adb->pquery (
					'UPDATE vtiger_settings_field SET iconpath=?, description=?, linkto=?, active=? WHERE fieldid=?',
					array ('fa fa-cogs yellow-bg', '', "index.php?module={$module->getName ()}&action=index&parenttab=Settings", 0, $row ['fieldid'])
				);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
		}

		/**
		 * Cuestiones propias de vTiger, que seguramente serán migradas o desaparecerán en un futuro no muy lejano
		 *
		 * @param Module $module
		 */
		private function setUpVtigerRelatedStuff ($module) {
			VtigerUtils::setUpWebServices ($this->adb, $module->getName ());
			$moduleId = $module->getId ();
			$result   = $this->adb->pquery ('SELECT * FROM vtiger_def_org_share WHERE tabid=?', array ($moduleId));
			if ($this->adb->num_rows ($result) == 0) {
				$ruleId = $this->adb->getUniqueID ('vtiger_def_org_share');
				$this->adb->pquery (
					'INSERT INTO vtiger_def_org_share (ruleid, tabid, permission, editstatus) VALUES(?, ?, ?, ?)',
					array ($ruleId, $moduleId, 3, 0)
				);
			} else {
				$row = $this->adb->fetchByAssoc ($result, -1, false);
				$this->adb->pquery ('UPDATE vtiger_def_org_share SET permission=? WHERE ruleid=?', array (3, $row ['ruleid']));
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
		}

		/**
		 * Cuestiones propias de vTiger, que seguramente serán migradas o desaparecerán en un futuro no muy lejano
		 *
		 * @param Module $module
		 */
		private function tearDownVtigerRelatedStuff ($module) {
			$this->adb->pquery ('DELETE FROM vtiger_def_org_share WHERE tabid=?', array ($module->getId ()));
			VtigerUtils::tearDownWebServices ($this->adb, $module->getName ());
		}

		/**
		 * Validar la información del módulo. Adiciuonalmente:
		 * + Validar que el nombre del módulo no esté en uso
		 *
		 * @param Module $module
		 *
		 * @throws ModuleException
		 */
		private function validate ($module) {
			if ((empty ($module)) || (!($module instanceof Module))) {
				throw new ModuleException (ModuleException::ERROR_MODULE_EMPTY);
			}

			$module->validate ();
			$this->fixModuleData ($module);

			$result = $this->adb->pquery ('SELECT * FROM vtiger_tab WHERE name=?', array ($module->getName ()));
			if ($this->adb->num_rows ($result) > 0) {
				$row      = $this->adb->fetchByAssoc ($result, -1, false);
				$moduleId = $module->getId ();
				if ((empty ($moduleId)) || ($row ['tabid'] != $moduleId)) {
					$e = new ModuleException (ModuleException::ERROR_MODULE_DUPLICATE_NAME);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			if (isset ($e)) {
				throw $e;
			}
		}

		/**
		 * Elimina un campo del módulo. Si el campo a eliminar es el identificador de entidad, se elige un nuevo campo para tal fin
		 *
		 * @param string $moduleName
		 * @param integer $fieldId
		 */
		public function deleteFieldById ($moduleName, $fieldId) {
			if ((empty ($moduleName)) || (empty ($fieldId))) {
				return;
			}

			$module = $this->fetchModule ($moduleName);
			if (empty ($module)) {
				return;
			}

			$fm    = FieldManager::getInstance ($this->adb);
			$field = $fm->fetchFieldById ($fieldId);
			if (empty ($field)) {
				return;
			}

			$fieldName = $field->getName ();
			if (($module->getIsEntityType ()) && ($fieldName == $module->getEntityIdentifier ())) {
				$entityIdentifierFields = $fm->fetchFieldsByUiType ($moduleName, FieldInterface::UI_TYPE_CODE);
				if (!empty ($entityIdentifierFields)) {
					$entityIdentifierField = $entityIdentifierFields [0];
				} else {
					$fields                = $module->getFields ();
					if ($fields [0]->getName () != $fieldName) {
						$entityIdentifierField = $fields [0];
					} else {
						$entityIdentifierField = $fields [1];
					}
				}
				$module->setEntityIdentifier ($entityIdentifierField->getName ());
				$this->saveModule ($module);
			}
			$fm->deleteField ($field);
		}

		/**
		 * Eliminar el módulo. Opcionalmente, eliminar los archivos del mismo
		 *
		 * @param Module $module
		 * @param boolean $deleteFiles
		 *
		 * @throws VtigerUtilsException
		 */
		public function deleteModule ($module, $deleteFiles = false) {
			$moduleId   = isset ($module) ? $module->getId () : null;
			$moduleName = isset ($module) ? $module->getName () : null;
			if ((empty ($module)) || (!($module instanceof Module)) || (empty ($moduleId)) || (empty ($moduleName))) {
				return;
			}

			$this->adb->startTransaction ();
			$this->firePreUninstallEvent ($module);
			ModuleRelationshipManager::getInstance ($this->adb)->deleteRelationships ($module->getName ());
			if ($module->getIsEntityType ()) {
				$this->tearDownVtigerRelatedStuff ($module);
				ReportTemplateManager::getInstance ($this->adb)->deleteTemplates ($module->getName ());
				ReportsManager::getInstance ($this->adb)->deleteReports ($module->getName ());
				CalendarViewManager::getInstance ($this->adb)->deleteViews ($module->getName ());
				ChartManager::getInstance ($this->adb)->deleteCharts ($module->getName ());
				ViewManager::getInstance ($this->adb)->deleteViews ($module->getName ());
				$this->deleteEntitySequence ($module);
				$this->deleteEntityName ($module);
				BlockManager::getInstance ($this->adb)->deleteBlocks ($module->getName ());
				$moduleTables = VtigerUtils::parseModuleFile ($this->adb, $module->getName ());
				$this->deleteTables ($moduleTables);
			}
			ButtonManager::getInstance ($this->adb)->deleteButtons ($module->getName ());
			BackgroundTaskManager::getInstance ($this->adb)->deleteTasks ($module->getName ());
			$this->deleteSettingsBlock ($module);
			$this->deleteMenu ($module);
			ModuleProfileManager::getInstance ($this->adb)->deleteProfiles ($module->getName ());
			NotificationManager::getInstance ($this->adb)->deleteNotifications ($module->getName ());
			$this->adb->pquery ('DELETE FROM vtiger_tab WHERE name=?', array ($module->getName ()));
			if ($deleteFiles) {
				$this->deleteFiles ($module);
			}
			$this->adb->completeTransaction ();
		}

		/**
		 * Duplica el módulo
		 *
		 * @param Module $oldModule
		 * @param string $newModuleName
		 * @param string $newModuleLabel
		 * @param string $newMenuLabel
		 *
		 * @return Module
		 *
		 * @throws Exception
		 * @throws ModuleException
		 */
		public function duplicateModule ($oldModule, $newModuleName, $newModuleLabel, $newMenuLabel) {
			$this->validate ($oldModule);

			$newModule = $oldModule->duplicate (true, false, false, $newModuleName)
				->setLabel ($newModuleLabel)
				->setMenuLabel ($newMenuLabel)
				->setSequence (null);
			$this->validate ($newModule);

			try {
				$this->duplicateFiles ($oldModule->getName (), $oldModule->getLabel (), $newModuleName, $newModuleLabel);
				if ($oldModule->getIsEntityType ()) {
					$oldModuleTables = VtigerUtils::parseModuleFile ($this->adb, $oldModule->getName ());
					$oldTableName    = $oldModuleTables ['maintable']['name'];
					$newModuleTables = VtigerUtils::parseModuleFile ($this->adb, $newModuleName);
					$newTableName    = $newModuleTables ['maintable']['name'];
					$newModule->changeTableName ($oldTableName, $newTableName);
				}
				return $this->saveModule ($newModule);
			} catch (Exception $e) {
				$this->deleteModule ($newModule, true);
				throw $e;
			}
		}

		/**
		 * Obtener el módulo por el nombre código asociado
		 *
		 * @param string $moduleName
		 * @param boolean $headersOnly
		 * @param boolean $forAnInstance
		 *
		 * @return Module|null
		 */
		public function fetchModule ($moduleName, $headersOnly = false, $forAnInstance = false) {
			if (empty ($moduleName)) {
				return null;
			}

			$result = $this->adb->pquery (
				'SELECT
					t.*,
					pt.parenttab_label AS menulabel,
					ptr.sequence AS relsequence
				FROM
					vtiger_tab t
					LEFT JOIN vtiger_parenttabrel ptr ON ptr.tabid=t.tabid
					LEFT JOIN vtiger_parenttab pt ON pt.parenttabid=ptr.parenttabid
				WHERE
					t.name=?',
				array ($moduleName)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$row    = $this->adb->fetchByAssoc ($result, -1, false);
				$module = $this->fillModule ($row, $headersOnly, $forAnInstance);
			} else {
				$module = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $module;
		}

		/**
		 * Obtener el módulo por el id
		 *
		 * @param integer $moduleId
		 * @param boolean $headersOnly
		 *
		 * @return Module|null
		 */
		public function fetchModuleById ($moduleId, $headersOnly = false) {
			if (empty ($moduleId)) {
				return null;
			}

			$result = $this->adb->pquery (
				'SELECT
					t.*,
					pt.parenttab_label AS menulabel,
					ptr.sequence AS relsequence
				FROM
					vtiger_tab t
					LEFT JOIN vtiger_parenttabrel ptr ON ptr.tabid=t.tabid
					LEFT JOIN vtiger_parenttab pt ON pt.parenttabid=ptr.parenttabid
				WHERE
					t.tabid=?',
				array ($moduleId)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$row    = $this->adb->fetchByAssoc ($result, -1, false);
				$module = $this->fillModule ($row, $headersOnly, false);
			} else {
				$module = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $module;
		}

		/**
		 * Obtener los módulos
		 *
		 * @param boolean $headersOnly
		 * @param string[]|null $excludedModuleNames
		 * @param boolean $forAnInstance
		 *
		 * @return Module[]|null
		 */
		public function fetchModules ($headersOnly = false, $excludedModuleNames = null, $forAnInstance = false) {
			if (!empty ($excludedModuleNames)) {
				$questionMarks = str_repeat ('?, ', (count ($excludedModuleNames) - 1)) . '?';
				$whereClause   = "WHERE name NOT IN ({$questionMarks})";
				$arguments     = $excludedModuleNames;
			} else {
				$whereClause = '';
				$arguments   = array ();
			}

			$result = $this->adb->pquery (
				"SELECT
					t.*,
					pt.parenttab_label AS menulabel,
					ptr.sequence AS relsequence
				FROM
					vtiger_tab t
					LEFT JOIN vtiger_parenttabrel ptr ON ptr.tabid=t.tabid
					LEFT JOIN vtiger_parenttab pt ON pt.parenttabid=ptr.parenttabid
				{$whereClause}",
				$arguments
			);
			if ($this->adb->num_rows ($result) > 0) {
				$modules = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$modules [] = $this->fillModule ($row, $headersOnly, $forAnInstance);
				}
			} else {
				$modules = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $modules;
		}

		/**
		 * Obtener los módulos
		 *
		 * @param string $type
		 * @param boolean $headersOnly
		 * @param boolean $forAnInstance
		 *
		 * @return Module[]|null
		 */
		public function fetchModulesByType ($type, $headersOnly = false, $forAnInstance = false) {
			if (!in_array ($type, array (Module::TYPE_ADMIN, Module::TYPE_TOOL, Module::TYPE_USER))) {
				return null;
			}

			$result = $this->adb->pquery (
				'SELECT
					t.*,
					pt.parenttab_label AS menulabel
				FROM
					vtiger_tab t
					LEFT JOIN vtiger_parenttabrel ptr ON ptr.tabid=t.tabid
					LEFT JOIN vtiger_parenttab pt ON pt.parenttabid=ptr.parenttabid
				WHERE
					t.customized=?
				ORDER BY t.tablabel ASC',
				array ($type)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$modules = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$modules [] = $this->fillModule ($row, $headersOnly, $forAnInstance);
				}
			} else {
				$modules = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $modules;
		}

		/**
		 * Guardar el módulo
		 *
		 * @param Module $module
		 * @param boolean $createFiles
		 * @param boolean $ignoreLock
		 *
		 * @return Module
		 * @throws CalculationElementException
		 * @throws CalculationSystemException
		 * @throws DatabaseException
		 * @throws ModuleException
		 * @throws NotificationException
		 * @throws VtigerUtilsException
		 * @throws NotificationException
		 * @throws Exception
		 */
		public function saveModule ($module, $createFiles = false, $ignoreLock = true) {
			$this->validate ($module);

			$moduleId    = $module->getId ();
			$isNewModule = empty ($moduleId) ? true : false;
			$moduleName  = $module->getName ();
			$type        = $module->getType ();
			$sequence    = $this->getNextSequenceNumber ($module);
			$isPlatzilla = $this->isPlatzilla ($type);
			$menuLabel   = $this->getMenuLabel ($module);

			$module->setPresence ($this->getPresence ($module));
			$this->adb->startTransaction ();
			if ($isNewModule) {
				$moduleId = $this->getNextModuleId ($module);
				$this->adb->pquery (
					'INSERT INTO vtiger_tab (tabid, name, presence, tabsequence, tablabel, customized, isentitytype, parent, isplatzilla, isvisibleinadmin) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
					array ($moduleId, $module->getName (), $module->getPresence (), $sequence, $module->getLabel (), $module->getType (), $module->getIsEntityType (), $menuLabel, $isPlatzilla, $module->getShowInAdminConsole ())
				);
				$this->createDefaultModuleProfiles ($module);
				$module->setId ($moduleId);
			} else {
				$this->adb->pquery (
					'UPDATE vtiger_tab SET name=?, presence=?, tabsequence=?, tablabel=?, customized=?, isentitytype=?, parent=?, isplatzilla=?, isvisibleinadmin=? WHERE tabid=?',
					array ($module->getName (), $module->getPresence (), $sequence, $module->getLabel (), $module->getType (), $module->getIsEntityType (), $menuLabel, $isPlatzilla, $module->getShowInAdminConsole (), $moduleId)
				);
			}
			if (!$module->getShowInSettings ()) {
				$this->saveMenu ($module);
			} else {
				$this->saveSettingsBlock ($module);
			}
			ButtonManager::getInstance ($this->adb)->saveButtons ($module->getName (), $module->getButtons (), $ignoreLock);
			BackgroundTaskManager::getInstance ($this->adb)->saveTasks ($module->getName (), $module->getBackgroundTasks (), $ignoreLock);
			if ($createFiles) {
				$this->createFiles ($module);
			}
			if (($module->getIsEntityType ()) || (in_array ($moduleName, array ('Users')))) {
				$moduleTables      = VtigerUtils::parseModuleFile ($this->adb, $moduleName);
				$mainTableName     = $moduleTables ['maintable']['name'];
				$mainTableIdColumn = $moduleTables ['maintable']['idcolumn'];
				$this->addAuditingFields ($module);
				$this->createTables ($moduleTables);
				BlockManager::getInstance ($this->adb)->saveBlocks ($module->getName (), $module->getBlocks (), $mainTableName, $ignoreLock);
				$this->saveFieldDependencies ($module);
				$this->saveEntityName ($module, $mainTableName, $mainTableIdColumn);
				$this->saveEntitySequence ($module);
				$this->adb->pquery ('INSERT IGNORE INTO vtiger_module_report (tabid, reportavailable) VALUES (?, ?)', array ($module->getId (), 1));
				ViewManager::getInstance ($this->adb)->saveViews ($module->getName (), $module->getViews (), $mainTableName, $ignoreLock);
				CalendarViewManager::getInstance ($this->adb)->saveViews ($module->getName (), $module->getCalendarViews (), $ignoreLock);
				EditableFieldsManager::getInstance($this->adb)->saveEditableFieldsButtons($module, $ignoreLock);
				GridViewManager::getInstance ($this->adb)->saveGridViews ($module, $ignoreLock);
				KanbanViewManager::getInstance ($this->adb)->saveKanbanViews ($module->getName (), $module->getKanbanViews (), $ignoreLock);
				NotificationManager::getInstance ($this->adb)->saveNotifications ($module->getName (), $module->getNotifications (), $ignoreLock);
				PicklistRelationshipManager::getInstance($this->adb)->saveRelationshipPicklits ($module, $ignoreLock);
				ReportsManager::getInstance ($this->adb)->saveReports ($module->getName (), $module->getReports (), $mainTableName, $ignoreLock);
				ReportTemplateManager::getInstance ($this->adb)->saveTemplates ($module->getName (), $module->getReportTemplates ());
				HowToUseManager::getInstance ($this->adb)->saveHowUseModes($module);
				$this->setUpVtigerRelatedStuff ($module);
			}
			// TODO: El módulo calculated_fields no está diseñado como los demás. En lugar de que cada módulo tenga asociados sus cálculos, todos los cálculos están asociados a dicho módulo. Esto hay que cambiarlo
			// TODO: El módulo indicatorspanel - boxScore, los indicadores no se asocian a ningún módulo por tal motivo se actualiza por acá
			if (in_array ($moduleName, array ('calculated_fields', 'indicatorspanel', 'graficosgenerales'))) {
				CalculationManager::getInstance ($this->adb)->saveCalculationsSystem ($module, $ignoreLock);
				ScoringBoxManager::getInstance ($this->adb)->saveScoringBoxes ($module, $ignoreLock);
				ChartManager::getInstance ($this->adb)->saveCharts ($module, $ignoreLock);
				HowToUseManager::getInstance ($this->adb)->updateHowToUsesViewsIds ($module);
			}
			if ($isNewModule) {
				$this->firePostInstallEvent ($module, $isNewModule);
			}
			$this->adb->completeTransaction ();
			return $module;
		}

		/**
		 * Actualizar la información básica del módulo, sin alterar los objetos asociados
		 *
		 * @param Module $module
		 *
		 * @return Module
		 *
		 * @throws ModuleException
		 */
		public function updateModuleHeader ($module) {
			if ((empty ($module)) || (!($module instanceof Module))) {
				throw new ModuleException (ModuleException::ERROR_MODULE_EMPTY);
			}

			$moduleId = $module->getId ();
			if (empty ($moduleId)) {
				return $module;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_tab WHERE tabid=?', array ($moduleId));
			if ($this->adb->num_rows ($result) > 0) {
				$type        = $module->getType ();
				$sequence    = $this->getNextSequenceNumber ($module);
				$isPlatzilla = $type == ModuleInterface::TYPE_USER ? 0 : 1;
				$this->adb->pquery (
					'UPDATE vtiger_tab SET presence=?, tabsequence=?, tablabel=?, parent=?, isplatzilla=?, isvisibleinadmin=? WHERE tabid=?',
					array ($module->getPresence (), $sequence, $module->getLabel (), $module->getMenuLabel (), $isPlatzilla, $module->getShowInAdminConsole (), $moduleId)
				);
				if (!$module->getShowInSettings ()) {
					$this->saveMenu ($module);
				} else {
					$this->saveSettingsBlock ($module);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $module;
		}

		/**
		 * Actualiza la visibilidad de los módulos de acuerdo a si forman parte de aplicaciones
		 */
		public function updateModulesPresence () {
			$this->adb->pquery (
				'UPDATE vtiger_tab SET presence=? WHERE presence=? AND customized=? AND tabid IN (
					SELECT DISTINCT tabid FROM vtiger_configapps_tab WHERE tabid NOT IN (
						SELECT tabid FROM vtiger_disabled_tab
					)
				) ',
				array (0, -1, 1)
			);
			$this->adb->pquery (
				'UPDATE vtiger_tab SET presence=? WHERE presence=? AND customized=? AND tabid IN (
					SELECT tabid FROM vtiger_disabled_tab
				)',
				array (-1, 0, 1)
			);
		}

		/**
		 * Hace visibles los módulos tipo herramientas, excluyendo los módulos Dashboard y Events
		 */
		public function updateToolsPresence () {
			$this->adb->pquery (
				'UPDATE vtiger_tab SET presence=? WHERE customized=? AND name NOT IN (?, ?)',
				array (Module::PRESENCE_VISIBLE, Module::TYPE_TOOL, 'Dashboard', 'Events')
			);
			// TODO: cuando se repare el maldito kanban y el panel de indicadores, quitar
			$this->adb->pquery (
				'UPDATE vtiger_tab SET presence=? WHERE customized=? AND name IN (?)',
				array (Module::PRESENCE_ALWAYS_HIDDEN, Module::TYPE_TOOL, 'systemalerts')
			);
		}

		/**
		 * Validar el módulo
		 *
		 * @param $module
		 *
		 * @throws ModuleException
		 */
		public function validateModule ($module) {
			$this->validate ($module);
		}

		/**
		 * Obtiene una instancia de la clase
		 *
		 * @param PearDatabase $adb
		 *
		 * @return ModuleManager
		 */
		public static function getInstance (PearDatabase $adb) {
			if (self::$INSTANCES === null) {
				self::$INSTANCES = array ();
			}
			if (!isset (self::$INSTANCES [ $adb->dbName ])) {
				self::$INSTANCES [ $adb->dbName ] = new self ($adb);
			}
			return self::$INSTANCES [ $adb->dbName ];
		}

	}
