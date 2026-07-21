<?php
	require_once ('config.inc.php');
	require_once ('include/database/PearDatabase.php');
	require_once ('include/platzilla/Managers/PlatformManager.php');
	require_once ('include/utils/AdbManager.class.php');

	/**
	 * Prueba funcional de la clase PlatformManager
	 *
	 * @codingStandardsIgnoreStart
	 * @SuppressWarnings(PHPMD)
	 */
	class PlatformManagerTest extends PHPUnit_Framework_TestCase {
		/** @var PearDatabase */
		private static $adb;

		public static function setUpBeforeClass () {
			parent::setUpBeforeClass ();
			self::$adb = AdbManager::getInstance ()->getMasterAdb ();
		}

		public static function tearDownAfterClass () {
			parent::tearDownAfterClass ();
			global $dbconfig;

			$instanceCode = self::fetchLastInstanceCode ();
			InstanceDatabaseUtils::deleteInstanceDatabase ($dbconfig ['db_serverForNewDB'], $dbconfig ['db_username'], $dbconfig ['db_password'], $instanceCode);
			InstanceDatabaseUtils::deleteInstanceUser ($dbconfig ['db_serverForNewDB'], $dbconfig ['db_username'], $dbconfig ['db_password'], $dbconfig ['db_serverForNewUsers'], $instanceCode);

			self::$adb->pquery (
				'DELETE FROM vtiger_crmentity WHERE crmid IN (SELECT contactid FROM vtiger_contactdetails WHERE accountid IN (SELECT accountid FROM vtiger_instances WHERE code=?))',
				array ($instanceCode)
			);
			self::$adb->pquery ('DELETE FROM vtiger_crmentity WHERE crmid IN (SELECT accountid FROM vtiger_instances WHERE code=?)', array ($instanceCode));
			self::$adb->pquery ('DELETE FROM vtiger_crmentity WHERE crmid IN (SELECT instanceid FROM vtiger_instances WHERE code=?)', array ($instanceCode));
			self::deleteToolModule ($instanceCode);
			self::$adb->disconnect ();
		}

		private static function deleteToolModule ($instanceCode) {
			$mm     = ModuleManager::getInstance (self::$adb);
			$module = $mm->fetchModule ('my_tool_module');
			ModuleManager::getInstance (self::$adb)->deleteModule ($module);

			$adb = AdbManager::getInstance ()->getTargetInstanceAdb ($instanceCode);
			$mm = ModuleManager::getInstance ($adb);
			$module = $mm->fetchModule ('my_tool_module');
			ModuleManager::getInstance ($adb)->deleteModule ($module);
		}

		private static function fetchLastInstanceCode () {
			$result   = self::$adb->pquery ('SELECT * FROM vtiger_variables_instancias WHERE varname IN (?, ?)', array ('prefixinstances', 'codeseq'), true);
			$prefix   = null;
			$sequence = null;
			while ($row = self::$adb->fetchByAssoc ($result, -1, false)) {
				if ($row ['varname'] == 'prefixinstances') {
					$prefix = $row ['varvalue'];
				} else {
					$sequence = intval ($row ['varvalue']) - 1;
				}
			}
			return "{$prefix}{$sequence}";
		}

		/**
		 * @param array $row
		 * @param Application $application
		 * @param boolean $forInstance
		 */
		private function checkApplicationBasicProperties ($row, $application, $forInstance = false) {
			$this->assertNotEmpty ($application->getProfile (), 'Application profile should not be empty');
			$this->assertEquals ($row ['app_code'], $application->getCode (), 'Codes do not match');
			$this->assertEquals ($row ['app_name'], $application->getName (), 'Names do not match');
			$this->assertEquals ($row ['app_descripcion'], $application->getDescription (), 'Descriptions do not match');
			$this->assertEquals ($row ['app_status'], $application->getStatus (), 'Statuses do not match');
			$this->assertEquals ($row ['app_category'], $application->getCategoryId (), 'Category IDs do not match');
			$this->assertEquals ($row ['app_url'], $application->getUrl (), 'URLs do not match');

			if (!$forInstance) {
				$this->assertEquals ($row ['config_applicationsid'], $application->getId (), 'IDs do not match');
				$this->assertEquals ($row ['app_profile'], $application->getProfile ()->getId (), 'Profile IDs do not match');
				$this->assertEquals ($row ['app_price'], $application->getPrice (), 'Prices do not match');
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param Application $application
		 */
		private function checkApplicationModules (PearDatabase $adb, $application) {
			$applicationName = $application->getName ();
			$modules         = $application->getModules ();
			$expectedTotal   = !empty ($modules) ? count ($modules) : 0;
			$result          = $adb->pquery (
				'SELECT cat.*, t.name AS modulename FROM vtiger_configapps_tab cat INNER JOIN vtiger_tab t ON t.tabid=cat.tabid WHERE cat.config_applicationsid IN (SELECT config_applicationsid FROM vtiger_config_applications WHERE app_code=?)',
				array ($application->getCode ())
			);
			$this->assertEquals ($expectedTotal, $adb->num_rows ($result), "{$applicationName} modules count rules do not match");

			if ($adb->num_rows ($result) == 0) {
				return;
			}

			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$selectedModule = null;
				foreach ($modules as $module) {
					if ($module->getName () == $row ['modulename']) {
						$selectedModule = $module;
						break;
					}
				}

				if (empty ($selectedModule)) {
					$this->fail ("{$applicationName} module {$row ['modulename']} not found in platform");
				}
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param CalendarView $view
		 */
		private function checkCalendarViewRules (PearDatabase $adb, $view) {
			$moduleName    = $view->getModuleName ();
			$viewId        = $view->getId ();
			$viewLabel     = $view->getLabel ();
			$rules         = $view->getRules ();
			$expectedTotal = !empty ($rules) ? count ($rules) : 0;
			$result        = $adb->pquery ('SELECT * FROM vtiger_calendarviews_rules WHERE calendarviewid=?', array ($viewId));
			$this->assertEquals ($expectedTotal, $adb->num_rows ($result), "Calendar view rules do not match for {$viewLabel} ({$moduleName})");

			if ($adb->num_rows ($result) == 0) {
				return;
			}

			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$selectedRule = null;
				foreach ($rules as $rule) {
					if (
						($rule->getFieldName () == $row ['fieldname']) &&
						($rule->getOperator () == $row ['operator']) &&
						($rule->getValue () == $row ['value']) &&
						($rule->getBackgroundColor () == $row ['backgroundcolor'])
					) {
						$selectedRule = $rule;
						break;
					}
				}

				if (empty ($selectedRule)) {
					$this->fail ("Rule {$row ['ruleid']} - {$viewLabel} ({$moduleName}) not found in platform");
				}
			}
		}

		/**
		 * @param Field $field
		 */
		private function checkFieldDependencies ($field) {
			$moduleName    = $field->getModuleName ();
			$fieldName     = $field->getName ();
			$dependencies  = $field->getDependencies ();
			$expectedTotal = !empty ($dependencies) ? count ($dependencies) : 0;
			$result        = self::$adb->pquery ('SELECT fd.*, f.fieldname AS targetfieldname FROM vtiger_field_dependency fd INNER JOIN vtiger_field f ON f.fieldid=fd.field WHERE nameparent=?', array ($field->getName ()));
			$this->assertEquals ($expectedTotal, self::$adb->num_rows ($result), "Field dependencies count do not match for {$fieldName} ({$moduleName})");

			if (self::$adb->num_rows ($result) == 0) {
				return;
			}

			while ($row = self::$adb->fetchByAssoc ($result, -1, false)) {
				$selectedDependency = null;
				foreach ($dependencies as $dependency) {
					if ($dependency->getTargetFieldName () == $row ['targetfieldname']) {
						$selectedDependency = $dependency;
						break;
					}
				}

				if (empty ($selectedDependency)) {
					$this->fail ("Field dependency {$fieldName} - {$moduleName} - {$row ['targetfieldname']} not found in platform");
				} else {
					$this->assertEquals ($row ['visible'], $selectedDependency->getTargetFieldVisibility (), "Field dependency {$fieldName} - {$moduleName} - {$row ['targetfieldname']} target field visibilities do not match");
				}
			}
		}

		/**
		 * @param Field $field
		 */
		private function checkFieldModuleReferences ($field) {
			$moduleName    = $field->getModuleName ();
			$fieldName     = $field->getName ();
			$references    = $field->getModuleReferences ();
			$expectedTotal = !empty ($references) ? count ($references) : 0;
			$result        = self::$adb->pquery ('SELECT * FROM vtiger_fieldmodulerel WHERE fieldid=? AND module=?', array ($field->getId (), $moduleName));
			$this->assertEquals ($expectedTotal, self::$adb->num_rows ($result), "Field references count do not match for {$fieldName} ({$moduleName})");

			if (self::$adb->num_rows ($result) == 0) {
				return;
			}

			while ($row = self::$adb->fetchByAssoc ($result, -1, false)) {
				$selectedReference = null;
				foreach ($references as $reference) {
					if ($reference->getReferencedModuleName () == $row ['relmodule']) {
						$selectedReference = $reference;
						break;
					}
				}

				if (empty ($selectedReference)) {
					$this->fail ("Field reference {$fieldName} - {$moduleName} - {$row ['relmodule']} not found in platform");
				} else {
					$this->assertEquals ($row ['status'], $selectedReference->getStatus (), "Field reference {$fieldName} - {$moduleName} - {$row ['relmodule']} statuses do not match");
					$this->assertEquals ($row ['sequence'], $selectedReference->getSequence (), "Field reference {$fieldName} - {$moduleName} - {$row ['relmodule']} sequences do not match");
				}
			}
		}

		/**
		 * @param Field $field
		 */
		private function checkFieldPicklist ($field) {
			$moduleName    = $field->getModuleName ();
			$fieldName     = $field->getName ();
			$picklist      = $field->getPicklist ();
			$expectedTotal = !empty ($picklist) ? 1 : 0;
			$result        = self::$adb->pquery ('SELECT * FROM vtiger_picklist WHERE name=?', array ($fieldName));
			$this->assertEquals ($expectedTotal, self::$adb->num_rows ($result), "Picklists count do not match for {$fieldName} ({$moduleName})");

			if (self::$adb->num_rows ($result) == 0) {
				return;
			}

			$values        = $picklist->getValues ();
			$expectedTotal = !empty ($values) ? count ($values) : 0;
			/** @noinspection SqlResolve */
			$result = self::$adb->query ("SELECT * FROM vtiger_{$picklist->getName ()}");
			$this->assertEquals ($expectedTotal, self::$adb->num_rows ($result), "Picklist values count do not match for {$fieldName} ({$moduleName})");
		}

		/**
		 * @param array $row
		 * @param Module $module
		 * @param boolean $forInstance
		 */
		private function checkModuleBasicProperties ($row, $module, $forInstance = false) {
			$moduleName = $module->getName ();
			$this->assertEquals ($row ['isentitytype'] == 1 ? true : false, $module->getIsEntityType (), "{$moduleName} entity types do not match");
			$this->assertEquals ($row ['tablabel'], $module->getLabel (), "{$moduleName} labels do not match");
			$this->assertEquals ($row ['tabsequence'], $module->getSequence (), "{$moduleName} sequences do not match");
			$this->assertEquals ($row ['customized'], $module->getType (), "{$moduleName} types do not match");
			if (!$forInstance) {
				$this->assertEquals ($row ['tabid'], $module->getId (), "{$moduleName} IDs do not match");
				$this->assertEquals ($row ['presence'], $module->getPresence (), "{$moduleName} presences do not match");
				$this->assertEquals ($row ['isvisibleinadmin'] == 1 ? true : false, $module->getShowInAdminConsole (), "{$moduleName} ShowInAdmin properties do not match");
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param Module $module
		 * @param boolean $forInstance
		 */
		private function checkModuleBlocks (PearDatabase $adb, $module, $forInstance = false) {
			$moduleName = $module->getName ();
			$blocks     = $module->getBlocks ();
			$result     = $adb->pquery ('SELECT * FROM vtiger_blocks WHERE tabid IN (SELECT tabid FROM vtiger_tab WHERE name=?)', array ($moduleName));
			$this->assertEquals ($adb->num_rows ($result), count ($blocks), "Blocks count do not match for {$moduleName}");

			if ($adb->num_rows ($result) == 0) {
				return;
			}

			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$selectedBlock = null;
				foreach ($blocks as $block) {
					if (($block->getLabel () == $row ['blocklabel']) && ($block->getSequence () == $row ['sequence'])) {
						$selectedBlock = $block;
						break;
					}
				}

				if (empty ($selectedBlock)) {
					$this->fail ("Block {$row ['blocklabel']} ({$moduleName}) not found in platform");
				} else {
					$this->assertEquals ($row ['show_title'], $selectedBlock->getShowTitle (), "Block {$row ['blocklabel']} ({$moduleName}) ShowTitle properties do not match");
					$this->assertEquals ($row ['visible'], $selectedBlock->getVisibility (), "Block {$row ['blocklabel']} ({$moduleName}) visibility do not match");
					$this->assertEquals ($row ['create_view'], $selectedBlock->getVisibilityInCreateView (), "Block {$row ['blocklabel']} ({$moduleName}) visibility in create view do not match");
					$this->assertEquals ($row ['edit_view'], $selectedBlock->getVisibilityInEditView (), "Block {$row ['blocklabel']} ({$moduleName}) visibility in edit view do not match");
					$this->assertEquals ($row ['detail_view'], $selectedBlock->getVisibilityInDetailView (), "Block {$row ['blocklabel']} ({$moduleName}) visibility in detail view do not match");
					$this->assertEquals ($row ['display_status'], $selectedBlock->getDisplayStatus (), "Block {$row ['blocklabel']} ({$moduleName}) display statuses do not match");
					$this->assertEquals ($row ['iscustom'], $selectedBlock->getIsCustom (), "Block {$row ['blocklabel']} ({$moduleName}) IsCustom properties do not match");
				}
				if (!$forInstance) {
					$this->assertEquals ($row ['blockid'], $selectedBlock->getId (), "Block {$row ['blocklabel']} ({$moduleName}) IDs do not match");
				}
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param Module $module
		 */
		private function checkModuleButtons (PearDatabase $adb, $module) {
			$moduleName    = $module->getName ();
			$buttons       = $module->getButtons ();
			$expectedTotal = !empty ($buttons) ? count ($buttons) : 0;
			$result        = $adb->pquery ('SELECT * FROM vtiger_custombuttons WHERE module=?', array ($moduleName));
			$this->assertEquals ($adb->num_rows ($result), $expectedTotal, "Buttons count do not match for {$moduleName}");

			if ($adb->num_rows ($result) == 0) {
				return;
			}

			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$selectedButton = null;
				foreach ($buttons as $button) {
					if ($button->getLabel () == $row ['label']) {
						$selectedButton = $button;
						break;
					}
				}

				if (empty ($selectedButton)) {
					$this->fail ("Button {$row ['label']} ({$moduleName}) not found in platform");
				} else {
					$onClick = $selectedButton->getType () == ButtonInterface::TYPE_JAVASCRIPT ? $selectedButton->getAction () : null;
					$link    = $selectedButton->getType () == ButtonInterface::TYPE_LINK ? $selectedButton->getAction () : null;
					$this->assertEquals ($row ['action'], $selectedButton->getLocation (), "Button {$row ['label']} ({$moduleName}) locations do not match");
					$this->assertEquals ($row ['style'], $selectedButton->getStyle (), "Button {$row ['label']} ({$moduleName}) styles do not match");
					$this->assertEquals ($row ['label'], $selectedButton->getLabel (), "Button {$row ['label']} ({$moduleName}) labels do not match");
					$this->assertEquals ($row ['onclick'], $onClick, "Button {$row ['label']} ({$moduleName}) OnClick properties do not match");
					$this->assertEquals ($row ['link'], $link, "Button {$row ['label']} ({$moduleName}) Link properties do not match");
					$this->assertEquals ($row ['type'], $selectedButton->getType (), "Button {$row ['label']} ({$moduleName}) types do not match");
					$this->assertEquals ($row ['description'], $selectedButton->getDescription (), "Button {$row ['label']} ({$moduleName}) descriptions do not match");
					$this->assertEquals ($row ['active'], $selectedButton->getIsActive (), "Button {$row ['label']} ({$moduleName}) IsActive properties do not match");
					$this->assertEquals ($row ['runinnewwindow'], $selectedButton->getRunInNewWindow () == true ? 1 : 0, "Button {$row ['label']} ({$moduleName}) RunInNewWindow properties do not match");
				}
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param Module $module
		 * @param boolean $forInstance
		 */
		private function checkModuleCalendarViews (PearDatabase $adb, $module, $forInstance = false) {
			$moduleName    = $module->getName ();
			$views         = $module->getCalendarViews ();
			$expectedTotal = !empty ($views) ? count ($views) : 0;
			$result        = $adb->pquery ('SELECT * FROM vtiger_calendarviews WHERE modulename=?', array ($moduleName));
			$this->assertEquals ($expectedTotal, $adb->num_rows ($result), "Calendar views count do not match for {$moduleName}");

			if ($adb->num_rows ($result) == 0) {
				return;
			}

			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$selectedView = null;
				foreach ($views as $view) {
					if ($view->getLabel () == $row ['label']) {
						$selectedView = $view;
						break;
					}
				}

				if (empty ($selectedView)) {
					$this->fail ("View {$row ['label']} ({$moduleName}) not found in platform");
				} else {
					$this->assertEquals ($row ['label'], $selectedView->getLabel (), "View {$row ['label']} ({$moduleName}) labels do not match");
					$this->assertEquals ($row ['titlemodulename'], $selectedView->getTitleModuleName (), "View {$row ['label']} ({$moduleName}) title module names do not match");
					$this->assertEquals ($row ['titlefieldname'], $selectedView->getTitleFieldName (), "View {$row ['label']} ({$moduleName}) title field names do not match");
					$this->assertEquals ($row ['frommodulename'], $selectedView->getFromModuleName (), "View {$row ['label']} ({$moduleName}) from module names do not match");
					$this->assertEquals ($row ['fromfieldname'], $selectedView->getFromFieldName (), "View {$row ['label']} ({$moduleName}) from field names do not match");
					$this->assertEquals ($row ['tomodulename'], $selectedView->getToModuleName (), "View {$row ['label']} ({$moduleName}) to module names do not match");
					$this->assertEquals ($row ['tofieldname'], $selectedView->getToFieldName (), "View {$row ['label']} ({$moduleName}) to field names do not match");
					$this->assertEquals ($row ['backgroundcolor'], $selectedView->getBackgroundColor (), "View {$row ['label']} ({$moduleName}) background colors do not match");
					$this->checkCalendarViewRules ($adb, $selectedView);
				}

				if (!$forInstance) {
					$this->assertEquals ($row ['calendarviewid'], $selectedView->getId (), "View {$row ['label']} ({$moduleName}) IDs do not match");
				}
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param Module $module
		 * @param boolean $forInstance
		 */
		private function checkModuleCharts (PearDatabase $adb, $module, $forInstance = false) {
			$moduleName    = $module->getName ();
			$charts        = $module->getCharts ();
			$expectedTotal = !empty ($charts) ? count ($charts) : 0;
			$result        = $adb->pquery ('SELECT * FROM vtiger_graficos WHERE fld_module=?', array ($moduleName));
			$this->assertEquals ($adb->num_rows ($result), $expectedTotal, "Charts count do not match for {$moduleName}");

			if ($adb->num_rows ($result) == 0) {
				return;
			}

			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$selectedChart = null;
				foreach ($charts as $chart) {
					if (($chart->getTitle () == $row ['title']) && ($chart->getType () == $row ['tipografico'])) {
						$selectedChart = $chart;
						break;
					}
				}

				if (empty ($selectedChart)) {
					$this->fail ("Chart {$row ['title']} ({$moduleName}) not found in platform");
				} else {
					$applicationCodes = !empty ($selectedChart->getApplicationCodes ()) ? json_encode ($selectedChart->getApplicationCodes ()) : null;
					$this->assertEquals ($row ['fieldgrouping'], $selectedChart->getGroupBy (), "Chart {$row ['title']} ({$moduleName}) GroupBy properties do not match");
					$this->assertEquals ($row ['fieldoperation'], $selectedChart->getFieldName (), "Chart {$row ['title']} ({$moduleName}) field names do not match");
					$this->assertEquals ($row ['operation'], $selectedChart->getOperation (), "Chart {$row ['title']} ({$moduleName}) operations do not match");
					$this->assertEquals ($row ['tipografico'], $selectedChart->getType (), "Chart {$row ['title']} ({$moduleName}) types do not match");
					$this->assertEquals ($row ['dategrouping'], $selectedChart->getDateGrouping (), "Chart {$row ['title']} ({$moduleName}) DateGrouping properties do not match");
					$this->assertEquals ($row ['title'], $selectedChart->getTitle (), "Chart {$row ['title']} ({$moduleName}) titles do not match");
					$this->assertEquals ($row ['applicationcodes'], $applicationCodes, "Chart {$row ['title']} ({$moduleName}) application codes do not match");
					$this->assertEquals ($row ['sqlprimarioreporte'], $selectedChart->getSqlQuery (), "Chart {$row ['title']} ({$moduleName}) SQL queries do not match");
					$this->assertEquals ($row ['varreporte'], $selectedChart->getVariables (), "Chart {$row ['title']} ({$moduleName}) variables do not match");
					$this->assertEquals ($row ['reporteavanzado'], $selectedChart->getAdvanced (), "Chart {$row ['title']} ({$moduleName}) advanced properties do not match");
					$this->assertEquals ($row ['comparar'], $selectedChart->getCompare () == true ? 1 : 0, "Chart {$row ['title']} ({$moduleName}) Compare properties do not match");
				}

				if (!$forInstance) {
					$this->assertEquals ($row ['graficoid'], $selectedChart->getId (), "Chart {$row ['title']} ({$moduleName}) IDs do not match");
				}
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param Module $module
		 * @param boolean $forInstance
		 */
		private function checkModuleFields (PearDatabase $adb, $module, $forInstance = false) {
			$moduleName = $module->getName ();
			$fields     = $module->getFields ();
			$result     = $adb->pquery ('SELECT * FROM vtiger_field WHERE tabid IN (SELECT tabid FROM vtiger_tab WHERE name=?)', array ($moduleName));
			$this->assertEquals ($adb->num_rows ($result), count ($fields), "Fields count do not match for {$moduleName}");

			if ($adb->num_rows ($result) == 0) {
				return;
			}

			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$selectedField = null;
				foreach ($fields as $field) {
					if ($field->getName () == $row ['fieldname']) {
						$selectedField = $field;
						break;
					}
				}

				if (empty ($selectedField)) {
					$this->fail ("Field {$row ['fieldname']} ({$moduleName}) not found in platform");
				} else {
					$this->assertEquals ($row ['columnname'], $selectedField->getColumnName (), "Field {$row ['fieldname']} ({$moduleName}) column names do not match");
					$this->assertEquals ($row ['tablename'], $selectedField->getTableName (), "Field {$row ['fieldname']} ({$moduleName}) table names do not match");
					$this->assertEquals ($row ['generatedtype'], $selectedField->getGeneratedType (), "Field {$row ['generatedtype']} ({$moduleName}) GeneratedType properties do not match");
					$this->assertEquals ($row ['uitype'], $selectedField->getUiType (), "Field {$row ['fieldname']} ({$moduleName}) uitypes do not match");
					$this->assertEquals ($row ['fieldname'], $selectedField->getName (), "Field {$row ['fieldname']} ({$moduleName}) field names do not match");
					$this->assertEquals ($row ['fieldlabel'], $selectedField->getLabel (), "Field {$row ['fieldname']} ({$moduleName}) labels do not match");
					$this->assertEquals ($row ['readonly'], $selectedField->getReadOnly (), "Field {$row ['fieldname']} ({$moduleName}) ReadOnly properties do not match");
					$this->assertEquals ($row ['presence'], $selectedField->getPresence (), "Field {$row ['fieldname']} ({$moduleName}) presences do not match");
					$this->assertEquals ($row ['defaultvalue'], $selectedField->getDefaultValue (), "Field {$row ['fieldname']} ({$moduleName}) default values do not match");
					$this->assertEquals ($row ['sequence'], $selectedField->getSequence (), "Field {$row ['fieldname']} ({$moduleName}) sequences do not match");
					$this->assertEquals ($row ['displaytype'], $selectedField->getDisplayType (), "Field {$row ['fieldname']} ({$moduleName}) display types do not match");
					$this->assertEquals ($row ['masseditable'], $selectedField->getMassEditable (), "Field {$row ['fieldname']} ({$moduleName}) MassEditable properties do not match");

					$uiType = $selectedField->getUiType ();
					if ($uiType == FieldInterface::UI_TYPE_MODULE_REFERENCE) {
						$this->checkFieldModuleReferences ($selectedField);
					} else if (in_array ($uiType, array (FieldInterface::UI_TYPE_MULTI_SELECT, FieldInterface::UI_TYPE_PICKLIST))) {
						$this->checkFieldDependencies ($selectedField);
						$this->checkFieldPicklist ($selectedField);
					}
				}

				if (!$forInstance) {
					$this->assertEquals ($row ['fieldid'], $selectedField->getId (), "Field {$row ['fieldname']} ({$moduleName}) IDs do not match");
					$this->assertEquals ($row ['block'], $selectedField->getBlockId (), "Field {$row ['fieldname']} ({$moduleName}) block IDs do not match");
					$this->assertEquals ($row ['quickcreate'], $selectedField->getQuickCreate (), "Field {$row ['fieldname']} ({$moduleName}) QuickCreate properties do not match");
					$this->assertEquals ($row ['quickcreatesequence'], $selectedField->getQuickCreateSequence (), "Field {$row ['fieldname']} ({$moduleName}) QuickCreate sequences do not match");
				}
			}
		}

		/**
		 * @param string $moduleName
		 * @param ViewAdvancedFilterGroup $group
		 */
		private function checkViewAdvancedFilters ($moduleName, $group) {
			$viewId        = $group->getViewId ();
			$filters       = $group->getFilters ();
			$expectedTotal = !empty ($filters) ? count ($filters) : 0;
			$result        = self::$adb->pquery ('SELECT * FROM vtiger_cvadvfilter WHERE cvid=? AND groupid=?', array ($viewId, $group->getSequence ()));
			$this->assertEquals ($expectedTotal, self::$adb->num_rows ($result), "Advanced filters count do not match for {$viewId} ({$moduleName})");

			if (self::$adb->num_rows ($result) == 0) {
				return;
			}

			while ($row = self::$adb->fetchByAssoc ($result, -1, false)) {
				$selectedFilter = null;
				foreach ($filters as $filter) {
					if ($filter->getSequence () == $row ['columnindex']) {
						$selectedFilter = $filter;
						break;
					}
				}

				if (empty ($selectedFilter)) {
					$this->fail ("Advanced filter {$row ['columnname']} - {$viewId} not found in platform");
				} else {
					$vTigerColumnName = "{$selectedFilter->getTableName ()}:{$selectedFilter->getColumnName ()}:{$selectedFilter->getFieldName ()}:{$moduleName}";
					$this->assertStringStartsWith ($vTigerColumnName, $row ['columnname'], "Advanced filter {$row ['columnname']} - {$row ['columnindex']} ({$viewId} - {$moduleName}) column names do not match");
					$this->assertEquals ($row ['comparator'], $selectedFilter->getComparator (), "Advanced filter {$row ['columnname']} - {$row ['columnindex']} ({$viewId} - {$moduleName}) comparators do not match");
					$this->assertEquals ($row ['value'], $selectedFilter->getValue (), "Advanced filter {$row ['columnname']} - {$row ['columnindex']} ({$viewId} - {$moduleName}) values do not match");
					$this->assertEquals ($row ['column_condition'], $selectedFilter->getOperator (), "Advanced filter {$row ['columnname']} - {$row ['columnindex']} ({$viewId} - {$moduleName}) operators do not match");
				}
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param View $view
		 * @param boolean $forInstance
		 */
		private function checkViewAdvancedFilterGroups (PearDatabase $adb, $view, $forInstance = false) {
			$moduleName    = $view->getModuleName ();
			$viewName      = $view->getName ();
			$groups        = $view->getAdvancedFilterGroups ();
			$expectedTotal = !empty ($groups) ? count ($groups) : 0;
			$result        = $adb->pquery ('SELECT * FROM vtiger_cvadvfilter_grouping WHERE cvid IN (SELECT cvid FROM vtiger_customview WHERE viewname=? AND entitytype=?)', array ($viewName, $moduleName));
			if ((!$forInstance) || (count ($groups) > 1)) {
				$this->assertEquals ($expectedTotal, $adb->num_rows ($result), "Advanced filter groups count do not match for {$viewName} ({$moduleName})");
			}

			if ($adb->num_rows ($result) == 0) {
				return;
			}

			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$selectedGroup = null;
				foreach ($groups as $group) {
					if ($group->getSequence () == $row ['groupid']) {
						$selectedGroup = $group;
						break;
					}
				}

				if (empty ($selectedGroup)) {
					$this->fail ("Advanced filter group {$row ['groupid']} - {$viewName} - {$moduleName} not found in platform");
				} else {
					$this->assertEquals ($row ['group_condition'], $selectedGroup->getOperator (), "Advanced filter group {$row ['groupid']} - {$viewName} - {$moduleName} operators do not match");
					$this->checkViewAdvancedFilters ($moduleName, $selectedGroup);
				}
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param View $view
		 */
		private function checkViewColumns (PearDatabase $adb, $view) {
			$moduleName    = $view->getModuleName ();
			$viewId        = $view->getId ();
			$viewName      = $view->getName ();
			$columns       = $view->getColumns ();
			$expectedTotal = !empty ($columns) ? count ($columns) : 0;
			$result        = $adb->pquery ('SELECT * FROM vtiger_cvcolumnlist WHERE cvid IN (SELECT cvid FROM vtiger_customview WHERE viewname=? AND entitytype=?)', array ($viewName, $moduleName));
			$this->assertEquals ($expectedTotal, $adb->num_rows ($result), "Columns count do not match for {$viewName} ({$moduleName})");

			if ($adb->num_rows ($result) == 0) {
				return;
			}

			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$selectedColumn = null;
				foreach ($columns as $column) {
					if ($column->getSequence () == $row ['columnindex']) {
						$selectedColumn = $column;
						break;
					}
				}

				if (empty ($selectedColumn)) {
					$this->fail ("Column {$row ['columnname']} - {$row ['columnindex']} ({$moduleName}) not found in platform");
				} else {
					$vTigerColumnName = "{$selectedColumn->getTableName ()}:{$selectedColumn->getColumnName ()}:{$selectedColumn->getFieldName ()}:{$moduleName}";
					$this->assertStringStartsWith ($vTigerColumnName, $row ['columnname'], "Column {$row ['columnname']} - {$row ['columnindex']} ({$viewId} - {$moduleName}) column names do not match");
				}
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param View $view
		 */
		private function checkViewStandardFilter (PearDatabase $adb, $view) {
			$moduleName    = $view->getModuleName ();
			$viewName      = $view->getName ();
			$filter        = $view->getStandardFilter ();
			$expectedTotal = !empty ($filter) ? 1 : 0;
			$result        = $adb->pquery ('SELECT * FROM vtiger_cvstdfilter WHERE cvid IN (SELECT cvid FROM vtiger_customview WHERE viewname=? AND entitytype=?)', array ($viewName, $moduleName));
			$this->assertEquals ($expectedTotal, $adb->num_rows ($result), "Standard filters count do not match for {$viewName} ({$moduleName})");

			if ($adb->num_rows ($result) == 0) {
				return;
			}

			$row              = $adb->fetchByAssoc ($result, -1, false);
			$vTigerColumnName = "{$filter->getTableName ()}:{$filter->getColumnName ()}:{$filter->getFieldName ()}:{$moduleName}";
			$this->assertStringStartsWith ($vTigerColumnName, $row ['columnname'], "View filter {$row ['viewname']} ({$moduleName}) column names do not match");
			$this->assertEquals ($row ['stdfilter'], $filter->getPeriod (), "View filter {$row ['viewname']} ({$moduleName}) periods do not match");

			$startDate = !empty ($filter->getStartDate ()) ? $filter->getStartDate ()->format ('Y-m-d') : null;
			$this->assertEquals ($row ['startdate'], $startDate, "View filter {$row ['viewname']} ({$moduleName}) start dates do not match");
			$endDate = !empty ($filter->getEndDate ()) ? $filter->getEndDate ()->format ('Y-m-d') : null;
			$this->assertEquals ($row ['enddate'], $endDate, "View filter {$row ['viewname']} ({$moduleName}) end dates do not match");
		}

		/**
		 * @param PearDatabase $adb
		 * @param Module $module
		 * @param boolean $forInstance
		 */
		private function checkModuleViews (PearDatabase $adb, $module, $forInstance = false) {
			$moduleName = $module->getName ();
			$views      = $module->getViews ();
			$result     = $adb->pquery ('SELECT * FROM vtiger_customview WHERE entitytype=?', array ($moduleName));
			$this->assertEquals ($adb->num_rows ($result), count ($views), "Views count do not match for {$moduleName}");

			if ($adb->num_rows ($result) == 0) {
				return;
			}

			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$selectedView = null;
				foreach ($views as $view) {
					if ($view->getName () == $row ['viewname']) {
						$selectedView = $view;
						break;
					}
				}

				if (empty ($selectedView)) {
					$this->fail ("View {$row ['viewname']} ({$moduleName}) not found in platform");
				} else {
					$this->assertEquals ($row ['viewname'], $selectedView->getName (), "View {$row ['viewname']} ({$moduleName}) names do not match");
					$this->assertEquals ($row ['setdefault'], $selectedView->getDefault (), "View {$row ['viewname']} ({$moduleName}) Default properties do not match");
					$this->assertEquals ($row ['setmetrics'], $selectedView->getShowCountInMenu (), "View {$row ['viewname']} ({$moduleName}) ShowCountInMenu properties do not match");
					$this->assertEquals ($row ['status'], $selectedView->getStatus (), "View {$row ['viewname']} ({$moduleName}) statuses do not match");

					$this->checkViewStandardFilter ($adb, $selectedView);
					$this->checkViewColumns ($adb, $selectedView);
					$this->checkViewAdvancedFilterGroups ($adb, $selectedView, $forInstance);
				}
			}
		}

		/**
		 * Validar que toda la información estructural de la plataforma se pueda obtener correctamente
		 */
		public function testFetchPlatform () {
			$platform = PlatformManager::getInstance (self::$adb)->fetchPlatform ();

			$modules = $platform->getModules ();
			$this->assertNotNull ($modules, 'Modules should not be null');

			$result = self::$adb->query ('SELECT * FROM vtiger_tab');
			while ($row = self::$adb->fetchByAssoc ($result, -1, false)) {
				$selectedModule = null;
				foreach ($modules as $module) {
					if ($module->getName () == $row ['name']) {
						$selectedModule = $module;
						break;
					}
				}

				if (empty ($selectedModule)) {
					$this->fail ("Module {$row ['name']} not found in platform");
				} else {
					$this->checkModuleBasicProperties ($row, $selectedModule);
					$this->checkModuleBlocks (self::$adb, $selectedModule);
					$this->checkModuleButtons (self::$adb, $selectedModule);
					$this->checkModuleCalendarViews (self::$adb, $selectedModule);
					$this->checkModuleCharts (self::$adb, $selectedModule);
					$this->checkModuleFields (self::$adb, $selectedModule);
					$this->checkModuleViews (self::$adb, $selectedModule);
				}
			}

			$applications = $platform->getApplications ();
			$this->assertNotNull ($applications, 'Applications should not be null');
			$result = self::$adb->query ('SELECT * FROM vtiger_config_applications');
			while ($row = self::$adb->fetchByAssoc ($result, -1, false)) {
				$selectedApplication = null;
				foreach ($applications as $application) {
					if ($application->getCode () == $row ['app_code']) {
						$selectedApplication = $application;
						break;
					}
				}

				if (empty ($selectedApplication)) {
					$this->fail ("Application {$row ['app_name']} not found in platform");
				} else {
					$this->checkApplicationBasicProperties ($row, $selectedApplication);
					$this->checkApplicationModules (self::$adb, $selectedApplication);
				}
			}

			$relationships = $platform->getModuleRelationships ();
			$expectedTotal = !empty ($relationships) ? count ($relationships) : 0;
			$result        = self::$adb->query ('SELECT rl.*, tm.name AS modulename, trm.name AS relatedmodulename FROM vtiger_relatedlists rl INNER JOIN vtiger_tab tm ON tm.tabid=rl.tabid INNER JOIN vtiger_tab trm ON trm.tabid=rl.related_tabid');
			$this->assertEquals ($expectedTotal, self::$adb->num_rows ($result), 'Relationships count do not match');

			if ((!$result) || (self::$adb->num_rows ($result))) {
				return;
			}

			while ($row = self::$adb->fetchByAssoc ($result, -1, false)) {
				$selectedRelationship = null;
				foreach ($relationships as $relationship) {
					if (($relationship->getModuleName () == $row ['modulename']) && ($relationship->getRelatedModuleName () == $row ['relatedmodulename'])) {
						$selectedRelationship = $relationship;
						break;
					}
				}

				if (empty ($selectedRelationship)) {
					$this->fail ("Relationship {$row ['modulename']} - {$row ['relatedmodulename']} not found in platform");
				} else {
					$this->assertEquals ($row ['name'], $selectedRelationship->getFunction (), "Relationship {$row ['modulename']} - {$row ['relatedmodulename']} functions do not match");
					$this->assertEquals ($row ['sequence'], $selectedRelationship->getSequence (), "Relationship {$row ['modulename']} - {$row ['relatedmodulename']} sequences do not match");
					$this->assertEquals ($row ['label'], $selectedRelationship->getSequence (), "Relationship {$row ['modulename']} - {$row ['relatedmodulename']} labels do not match");
					$this->assertEquals ($row ['presence'], $selectedRelationship->getPresence (), "Relationship {$row ['modulename']} - {$row ['relatedmodulename']} presences do not match");
					$this->assertEquals (strtoupper ($row ['actions']), join (',', $selectedRelationship->getActions ()), "Relationship {$row ['modulename']} - {$row ['relatedmodulename']} actions do not match");
				}
			}
		}
	}
	// @codingStandardsIgnoreEnd
