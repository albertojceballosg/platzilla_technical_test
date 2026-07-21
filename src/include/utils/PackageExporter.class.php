<?php
	require_once ('vtlib/Vtiger/Module.php');
	require_once ('vtlib/Vtiger/PackageExport.php');

	class PackageExporter extends Vtiger_PackageExport {

		public function __construct () {
			parent::Vtiger_PackageExport ();
			$this->_export_tmpdir = sys_get_temp_dir () . '/vtlib';
			if ((is_dir ($this->_export_tmpdir)) && (is_writable ($this->_export_tmpdir))) {
				return;
			}

			if (is_dir ($this->_export_tmpdir)) {
				$i = 1;
				do {
					$this->_export_tmpdir = sys_get_temp_dir () . "/vtlib_{$i}";
					$i++;
				} while ((is_dir ($this->_export_tmpdir)) && (!is_writable ($this->_export_tmpdir)));
			}

			if (!is_dir ($this->_export_tmpdir)) {
				mkdir ($this->_export_tmpdir);
			}

			if ((!is_dir ($this->_export_tmpdir)) || (!is_writable ($this->_export_tmpdir))) {
				throw new Exception ("Imposible crear el directorio {$this->_export_tmpdir} o no tiene permiso de escritura");
			}
		}

		private function exportEntityIdentifier ($fieldName, $entityFieldName, $entityFieldId, $entityIdColumn) {
			if ($fieldName != $entityFieldName) {
				return;
			}
			$this->openNode ('entityidentifier');
			$this->outputNode ($entityFieldId, 'entityidfield');
			$this->outputNode ($entityIdColumn, 'entityidcolumn');
			$this->closeNode ('entityidentifier');
		}

		private function exportField (array $fieldData) {
			$fieldName  = $fieldData ['fieldname'];
			$columnName = $fieldData ['columnname'];
			$tableName  = $fieldData ['tablename'];
			$uiType     = $fieldData ['uitype'];

			$this->outputNode ($fieldName, 'fieldname');
			$this->outputNode ($uiType, 'uitype');
			$this->outputNode ($columnName, 'columnname');
			$this->outputNode ($tableName, 'tablename');
			$this->outputNode ($fieldData ['generatedtype'], 'generatedtype');
			$this->outputNode ($fieldData ['fieldlabel'], 'fieldlabel');
			$this->outputNode ($fieldData ['readonly'], 'readonly');
			$this->outputNode ($fieldData ['presence'], 'presence');
			$this->outputNode ($fieldData ['defaultvalue'], 'defaultvalue');
			$this->outputNode ($fieldData ['sequence'], 'sequence');
			$this->outputNode ($fieldData ['maximumlength'], 'maximumlength');
			$this->outputNode ($fieldData ['typeofdata'], 'typeofdata');
			$this->outputNode ($fieldData ['quickcreate'], 'quickcreate');
			$this->outputNode ($fieldData ['quickcreatesequence'], 'quickcreatesequence');
			$this->outputNode ($fieldData ['displaytype'], 'displaytype');
			$this->outputNode ($fieldData ['info_type'], 'info_type');
			$this->outputNode ("<![CDATA[{$fieldData ['helpinfo']}]]>", 'helpinfo');
			if (isset ($fieldData ['masseditable'])) {
				$this->outputNode ($fieldData ['masseditable'], 'masseditable');
			}
		}

		private function exportFieldsDependencies ($moduleInstance) {
			global $adb;
			$result = $adb->pquery (
				'SELECT * FROM vtiger_fielddependencies fd WHERE fd.modulename=? ORDER BY fd.sourcefieldname',
				array ($moduleInstance->name)
			);
			if ($adb->num_rows ($result) == 0) {
				return;
			}
			$this->openNode ('fielddependencies');
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$this->openNode ('fielddependency');
				$this->outputNode ($row ['modulename'], 'modulename');
				$this->outputNode ($row ['sourcefieldname'], 'sourcefieldname');
				$this->outputNode ($row ['sourcefieldvalue'], 'sourcefieldvalue');
				$this->outputNode ($row ['targetfieldname'], 'targetfieldname');
				$this->outputNode ($row ['targetfieldvisibility'], 'targetfieldvisibility');
				$this->closeNode ('fielddependency');
			}
			$this->closeNode ('fielddependencies');
		}

		private function exportFieldPickListValues (array $fieldData) {
			global $adb;
			$fieldName = $fieldData ['fieldname'];
			$uiType    = $fieldData ['uitype'];
			if (
				(!in_array ($uiType, array ('15', '16', '33', '55', '111'))) ||
				(($uiType == '55') && ($fieldData ['sequence'] == 2)) ||
				(!Vtiger_Utils::CheckTable ("vtiger_{$adb->sql_escape_string ($fieldName)}"))
			) {
				return;
			}
			$pickListValues = vtlib_getPicklistValues ($fieldName);
			$this->openNode ('picklistvalues');
			foreach ($pickListValues as $pickListValue) {
				$this->outputNode ($pickListValue, 'picklistvalue');
			}
			$this->closeNode ('picklistvalues');
		}

		private function exportFieldToModuleRelations (array $fieldData) {
			global $adb;
			$fieldId = $fieldData ['fieldid'];
			$uiType  = $fieldData ['uitype'];
			if ($uiType != '10') {
				return;
			}
			$result = $adb->pquery ('SELECT * FROM vtiger_fieldmodulerel WHERE fieldid=?', array ($fieldId), true);
			$n      = $adb->num_rows ($result);
			if ($n == 0) {
				return;
			}
			$this->openNode ('relatedmodules');
			for ($i = 0; $i < $n; ++$i) {
				$this->outputNode ($adb->query_result ($result, $i, 'relmodule'), 'relatedmodule');
			}
			$this->closeNode ('relatedmodules');
		}

		private function exportGraphs ($moduleInstance) {
			global $adb;
			$result = $adb->pquery ('SELECT * FROM vtiger_graficos WHERE fld_module=?', array ($moduleInstance->name));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return;
			}
			$this->openNode ('graphs');
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$this->openNode ('graph');
				$this->outputNode (htmlentities ($moduleInstance->name, ENT_QUOTES, 'UTF-8'), 'modulename');
				$this->outputNode (htmlentities ($row ['fieldoperation'], ENT_QUOTES, 'UTF-8'), 'field');
				$this->outputNode (htmlentities ($row ['operation'], ENT_QUOTES, 'UTF-8'), 'operation');
				$this->outputNode (htmlentities ($row ['tipografico'], ENT_QUOTES, 'UTF-8'), 'type');
				$this->outputNode (htmlentities ($row ['title'], ENT_QUOTES, 'UTF-8'), 'title');
				$this->outputNode (htmlentities ($row ['roles_grafico'], ENT_QUOTES, 'UTF-8'), 'roles');
				$this->outputNode (htmlentities ($row ['sqlprimarioreporte'], ENT_QUOTES, 'UTF-8'), 'sql');
				$this->outputNode (htmlentities ($row ['varreporte'], ENT_QUOTES, 'UTF-8'), 'variables');
				$this->outputNode (htmlentities ($row ['reporteavanzado'], ENT_QUOTES, 'UTF-8'), 'isadvanced');
				$this->outputNode (htmlentities ($row ['comparar'], ENT_QUOTES, 'UTF-8'), 'compare');
				$this->outputNode (htmlentities ($row ['ishome'], ENT_QUOTES, 'UTF-8'), 'ishome');
				$this->outputNode (htmlentities ($row ['fieldgrouping'], ENT_QUOTES, 'UTF-8'), 'fieldgrouping');
				$this->outputNode (htmlentities ($row ['dategrouping'], ENT_QUOTES, 'UTF-8'), 'dategrouping');
				$this->closeNode ('graph');
			}
			$this->closeNode ('graphs');
		}

		/**
		 * @param CRMEntity $entity
		 * @param array $alreadyExportedTables
		 *
		 * @return array
		 */
		private function exportGridTables (CRMEntity $entity, array $alreadyExportedTables) {
			$gridTables = $entity->gridTables ();
			if (!$gridTables) {
				return array ();
			}
			$exportedTables = array ();
			foreach ($gridTables as $gridTable) {
				if ((in_array ($gridTable, $alreadyExportedTables)) || (in_array ($gridTable, $exportedTables))) {
					continue;
				}
				$sqlTable = Vtiger_Utils::CreateTableSql ($gridTable);
				$this->openNode ('table');
				$this->outputNode ($gridTable, 'name');
				$this->outputNode ("<![CDATA[$sqlTable]]>", 'sql');
				$this->closeNode ('table');
				$exportedTables [] = $gridTable;

				if (!VTiger_Utils::CheckTable ("{$gridTable}_values")) {
					continue;
				}
				$sqlTable = Vtiger_Utils::CreateTableSql ("{$gridTable}_values");
				$this->openNode ('table');
				$this->outputNode ("{$gridTable}_values", 'name');
				$this->outputNode ("<![CDATA[$sqlTable]]>", 'sql');
				$this->closeNode ('table');
				$exportedTables [] = "{$gridTable}_values";
			}
			return $exportedTables;
		}

		/**
		 * @param CRMEntity|stdClass $entity
		 * @param array $alreadyExportedTables
		 *
		 * @return array
		 */
		private function exportRegularTables (CRMEntity $entity, array $alreadyExportedTables) {
			$tables = array ();
			if ((isset ($entity->tables_name)) && (is_array ($entity->tables_name))) {
				$tables = $entity->tables_name;
			}
			if (isset ($entity->table_name)) {
				$tables = array ($entity->table_name);
			}
			if ((isset ($entity->tab_name)) && (is_array ($entity->tab_name))) {
				$tables = array_merge ($tables, $entity->tab_name);
			}
			if (!empty ($entity->groupTable)) {
				$tables [] = $entity->groupTable [0];
			}
			if (!empty ($entity->customFieldTable)) {
				$tables [] = $entity->customFieldTable [0];
			}
			$tables         = array_unique (array_diff ($tables, array ('vtiger_crmentity')));
			$exportedTables = array ();
			foreach ($tables as $table) {
				if ((in_array ($table, $alreadyExportedTables)) || (in_array ($table, $exportedTables))) {
					continue;
				}
				$sqlTable = Vtiger_Utils::CreateTableSql ($table);
				$this->openNode ('table');
				$this->outputNode ($table, 'name');
				$this->outputNode ("<![CDATA[$sqlTable]]>", 'sql');
				$this->closeNode ('table');
				$exportedTables [] = $table;
			}
			return $exportedTables;
		}

		private function exportReportAvailability (Vtiger_Module $moduleInstance) {
			global $adb;
			if (!$moduleInstance->isentitytype) {
				return;
			}

			$result = $adb->pquery ('SELECT * FROM vtiger_module_report WHERE tabid=?', array ($moduleInstance->id));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				$isAvailableForReport = 0;
			} else {
				$row = $adb->fetchByAssoc ($result, -1, false);
				$isAvailableForReport = intval ($row ['reportavailable']);
			}
			$this->outputNode ($isAvailableForReport, 'reportavailability');
		}

		private function exportRules ($cvid, $cvcolumnname) {
			global $adb;
			$cvcolumnruleres   = $adb->pquery ('SELECT * FROM vtiger_cvadvfilter WHERE cvid=? AND columnname=?', array ($cvid, $cvcolumnname));
			$cvcolumnrulecount = $adb->num_rows ($cvcolumnruleres);
			$this->openNode ('rules');
			for ($i = 0; $i < $cvcolumnrulecount; ++$i) {
				$cvcolumnruleindex = $adb->query_result ($cvcolumnruleres, $i, 'columnindex');
				$cvcolumnrulecomp  = $adb->query_result ($cvcolumnruleres, $i, 'comparator');
				$cvcolumnrulevalue = $adb->query_result ($cvcolumnruleres, $i, 'value');
				$cvcolumnrulecomp  = Vtiger_Filter::translateComparator ($cvcolumnrulecomp, true);

				$this->openNode ('rule');
				$this->outputNode ($cvcolumnruleindex, 'columnindex');
				$this->outputNode ($cvcolumnrulecomp, 'comparator');
				$this->outputNode ($cvcolumnrulevalue, 'value');
				$this->closeNode ('rule');
			}
			$this->closeNode ('rules');
		}

		private function exportSchemaTables ($moduleName, array $alreadyExportedTables) {
			if (file_exists (__DIR__ . "/../../modules/$moduleName/schema.xml")) {
				$schemaFile = __DIR__ . "/../../modules/$moduleName/schema.xml";
			} else {
				$schemaFile = __DIR__ . "/../../{$_SESSION ['plat']}/modules/$moduleName/schema.xml";
			}
			if (!file_exists ($schemaFile)) {
				return;
			}
			$schema = simplexml_load_file ($schemaFile, null, LIBXML_NOCDATA);
			if ((empty ($schema->tables)) || (empty ($schema->tables->table))) {
				return;
			}
			$exportedTables = array ();
			foreach ($schema->tables->table as $tableNode) {
				$table    = trim ($tableNode->name);
				$sqlTable = Vtiger_Utils::CreateTableSql ($table);
				if ((in_array ($table, $alreadyExportedTables)) || (in_array ($table, $exportedTables))) {
					continue;
				}
				$this->openNode ('table');
				$this->outputNode ($table, 'name');
				$this->outputNode ("<![CDATA[$sqlTable]]>", 'sql');
				$this->closeNode ('table');
				$exportedTables [] = $table;
			}
		}

		private function initExport () {
			$this->_export_modulexml_file = fopen ($this->__getManifestFilePath (), 'w');
			$this->__write ("<?xml version='1.0'?>\n");
		}

		public function export ($moduleInstance) {
			$moduleName = $moduleInstance->name;
			$this->initExport ();
			$this->export_Module ($moduleInstance, $moduleName);
			$this->__finishExport ();

			$zipFileName = "{$this->_export_tmpdir}/{$moduleName}.zip";
			$zip         = new Vtiger_Zip ($zipFileName);
			$zip->addFile ($this->__getManifestFilePath (), 'manifest.xml');
			$zip->save ();

			$this->__cleanupExport ();
		}

		public function export_Blocks ($moduleInstance) {
			global $adb;
			$result = $adb->pquery ('SELECT * FROM vtiger_blocks WHERE tabid=?', array ($moduleInstance->id));
			$n      = $adb->num_rows ($result);
			if ((!$result) || ($n == 0)) {
				return;
			}

			$this->openNode ('blocks');
			while ($row = $adb->fetch_array ($result)) {
				$this->openNode ('block');
				$this->outputNode ($row ['blocklabel'], 'label');
				$this->outputNode ($row ['create_view'], 'createview');
				$this->outputNode ($row ['detail_view'], 'detailview');
				$this->outputNode ($row ['display_status'], 'displaystatus');
				$this->outputNode ($row ['edit_view'], 'editview');
				$this->outputNode ($row ['sequence'], 'sequence');
				$this->outputNode ($row ['show_title'], 'showtitle');
				$this->outputNode ($row ['visible'], 'visible');
				$this->export_Fields ($moduleInstance, $row ['blockid']);
				$this->closeNode ('block');
			}
			$this->closeNode ('blocks');
		}

		public function export_CustomViews ($moduleInstance) {
			global $adb;

			$customviewres   = $adb->pquery ('SELECT * FROM vtiger_customview WHERE entitytype=?', array ($moduleInstance->name));
			$customviewcount = $adb->num_rows ($customviewres);

			if (empty($customviewcount)) {
				return;
			}

			$this->openNode ('customviews');
			for ($cvindex = 0; $cvindex < $customviewcount; ++$cvindex) {
				$cvid = $adb->query_result ($customviewres, $cvindex, 'cvid');

				$cvcolumnres   = $adb->query ("SELECT * FROM vtiger_cvcolumnlist WHERE cvid=$cvid");
				$cvcolumncount = $adb->num_rows ($cvcolumnres);

				$this->openNode ('customview');

				$setdefault = $adb->query_result ($customviewres, $cvindex, 'setdefault') == 1 ? 'true' : 'false';

				$setmetrics = $adb->query_result ($customviewres, $cvindex, 'setmetrics') == 1 ? 'true' : 'false';

				$this->outputNode ($adb->query_result ($customviewres, $cvindex, 'viewname'), 'viewname');
				$this->outputNode ($setdefault, 'setdefault');
				$this->outputNode ($setmetrics, 'setmetrics');

				$this->openNode ('fields');
				for ($index = 0; $index < $cvcolumncount; ++$index) {
					$cvcolumnindex = $adb->query_result ($cvcolumnres, $index, 'columnindex');
					$cvcolumnname  = $adb->query_result ($cvcolumnres, $index, 'columnname');
					$cvcolumnnames = explode (':', $cvcolumnname);
					$cvfieldname   = $cvcolumnnames[2];

					$columnname = $cvfieldname;
					$this->openNode ('field');
					$this->outputNode ($columnname, 'fieldname');
					$this->outputNode ($cvcolumnindex, 'columnindex');
					$this->exportRules ($cvid, $cvcolumnname);
					$this->closeNode ('field');
				}
				$this->closeNode ('fields');
				$this->closeNode ('customview');
			}
			$this->closeNode ('customviews');
		}

		public function export_Fields ($moduleInstance, $blockid) {
			global $adb;

			$fieldResult = $adb->pquery ('SELECT * FROM vtiger_field WHERE tabid=? AND block=?', array ($moduleInstance->id, $blockid), true);
			$fieldCount  = $adb->num_rows ($fieldResult);
			if (empty ($fieldCount)) {
				return;
			}

			$entityResult    = $adb->pquery ('SELECT * FROM vtiger_entityname WHERE tabid=?', array ($moduleInstance->id), true);
			$entityFieldName = $adb->query_result ($entityResult, 0, 'fieldname');
			$entityFieldId   = $adb->query_result ($entityResult, 0, 'entityidfield');
			$entityIdColumn  = $adb->query_result ($entityResult, 0, 'entityidcolumn');

			$this->openNode ('fields');
			for ($i = 0; $i < $fieldCount; ++$i) {
				$this->openNode ('field');
				$fieldResultRow = $adb->fetch_row ($fieldResult);
				$this->exportField ($fieldResultRow);
				$this->exportEntityIdentifier ($fieldResultRow ['fieldname'], $entityFieldName, $entityFieldId, $entityIdColumn);
				$this->exportFieldPickListValues ($fieldResultRow);
				$this->exportFieldToModuleRelations ($fieldResultRow);
				$this->closeNode ('field');
			}
			$this->closeNode ('fields');
		}

		public function export_Module ($moduleInstance, $nameExport = '') {
			global $adb;

			$moduleId    = $moduleInstance->id;
			$result      = $adb->pquery ('SELECT * FROM vtiger_parenttabrel WHERE tabid=?', array ($moduleId));
			$parentTabId = $adb->query_result ($result, 0, 'parenttabid');
			if (!empty ($parentTabId)) {
				$menu      = Vtiger_Menu::getInstance ($parentTabId);
				$menuLabel = $menu->label;
			} else {
				$menuLabel = '';
			}

			$result = $adb->pquery ('SELECT * FROM vtiger_tab WHERE tabid=?', array ($moduleId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return;
			}

			$row     = $adb->fetch_array ($result);
			$version = isset ($row ['version']) ? $row ['version'] : false;

			$this->openNode ('module');
			$this->outputNode (date ('Y-m-d H:i:s'), 'exporttime');
			$this->outputNode ($nameExport, 'name');
			$this->outputNode ($row ['tablabel'], 'label');
			$this->outputNode ($menuLabel, 'parent');
			if (!$moduleInstance->isentitytype) {
				$this->outputNode ('extension', 'type');
			}
			if ($version) {
				$this->outputNode ($version, 'version');
			}
			$this->exportReportAvailability ($moduleInstance);
			$this->export_Dependencies ($moduleInstance);
			$this->export_Tables ($moduleInstance);
			$this->export_Blocks ($moduleInstance);
			$this->exportFieldsDependencies ($moduleInstance);
			$this->export_CustomViews ($moduleInstance);
			$this->export_SharingAccess ($moduleInstance);
			$this->export_Events ($moduleInstance);
			$this->export_Actions ($moduleInstance);
			$this->export_RelatedLists ($moduleInstance);
			$this->export_CustomLinks ($moduleInstance);
			$this->export_CronTasks ($moduleInstance);
			$this->export_Grids ($moduleInstance);
			$this->export_IncrementalCode ($moduleInstance);
			$this->exportGraphs ($moduleInstance);
			$this->closeNode ('module');
		}

		public function export_Tables ($moduleInstance) {
			$alreadyExportedTables = array ();
			$moduleName            = $moduleInstance->name;
			$this->openNode ('tables');
			if (($moduleInstance->isentitytype) && (file_exists (__DIR__ . "/../../modules/$moduleName/$moduleName.php"))) {
				/** @var CRMEntity|stdClass $entity */
				$entity = CRMEntity::getInstance ($moduleName);
				vtlib_setup_modulevars ($moduleName, $entity);
				$alreadyExportedTables = array_merge ($alreadyExportedTables, $this->exportRegularTables ($entity, $alreadyExportedTables));
				$alreadyExportedTables = array_merge ($alreadyExportedTables, $this->exportGridTables ($entity, $alreadyExportedTables));
			}
			$this->exportSchemaTables ($moduleName, $alreadyExportedTables);
			$this->closeNode ('tables');
		}

		public function getExportFolder () {
			return $this->_export_tmpdir;
		}

		public function outputNode ($value, $node = '') {
			if ($node != '') {
				$this->openNode ($node, '');
			}
			$value = html_entity_decode ($value, ENT_QUOTES, 'UTF-8');
			$value = str_replace ('&', '&amp;', $value);
			if ((strpos ($value, '<![CDATA[') === false) && (strpos ($value, '<') !== false)) {
				$value = str_replace ('<', '&lt;', $value);
			}
			if ((strpos ($value, ']]>') === false) && (strpos ($value, '>') !== false)) {
				$value = str_replace ('>', '&gt;', $value);
			}
			$this->__write ($value);
			if ($node != '') {
				$this->closeNode ($node);
			}
		}

	}
