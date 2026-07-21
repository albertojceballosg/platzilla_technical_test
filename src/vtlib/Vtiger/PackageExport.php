<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
include_once('vtlib/Vtiger/Module.php');
include_once('vtlib/Vtiger/Menu.php');
include_once('vtlib/Vtiger/Event.php');
include_once('vtlib/Vtiger/Zip.php');
include_once('vtlib/Vtiger/Cron.php');
/**
 * Provides API to package vtiger CRM module and associated files.
 * @package vtlib
 */
class Vtiger_PackageExport {
	var $_export_tmpdir = 'test/vtlib';
	var $_export_modulexml_filename = null;
	var $_export_modulexml_file = null;

	/**
	 * Constructor
	 */
	function Vtiger_PackageExport() {
		if(is_dir($this->_export_tmpdir) === FALSE) {
			mkdir($this->_export_tmpdir);
		}
	}

	/** Output Handlers */

	/** @access private */
	function openNode($node,$delimiter="\n") {
		$this->__write("<$node>$delimiter");
	}
	/** @access private */
	function closeNode($node,$delimiter="\n") {
		$this->__write("</$node>$delimiter");
	}
	/** @access private */
	function outputNode($value, $node='') {
		if($node != '') $this->openNode($node,'');
		$value = html_entity_decode($value);
		$value = str_replace('&','&amp;',$value);
		$this->__write(utf8_encode($value));
		if($node != '') $this->closeNode($node);
	}
	/** @access private */
	function __write($value) {
		fwrite($this->_export_modulexml_file, $value);
	}

	/**
	 * Set the module.xml file path for this export and
	 * return its temporary path.
	 * @access private
	 */
	function __getManifestFilePath() {
		if(empty($this->_export_modulexml_filename)) {
			// Set the module xml filename to be written for exporting.
			$this->_export_modulexml_filename = "manifest-".time().".xml";
		}
		return "$this->_export_tmpdir/$this->_export_modulexml_filename";
	}

	/**
	 * Initialize Export
	 * @access private
	 */
	function __initExport($module, $moduleInstance) {
		if($moduleInstance->isentitytype) {
			// We will be including the file, so do a security check.
			if (file_exists($_SESSION['plat']."/modules/$module/$module.php"))
				Vtiger_Utils::checkFileAccessForInclusion($_SESSION['plat']."/modules/$module/$module.php");
			else if (file_exists("/modules/$module/$module.php"))
				Vtiger_Utils::checkFileAccessForInclusion("modules/$module/$module.php");
		}
		$this->_export_modulexml_file = fopen($this->__getManifestFilePath(), 'w');
		$this->__write("<?xml version='1.0'?>\n");
	}

	/**
	 * Post export work.
	 * @access private
	 */
	function __finishExport() {
		if(!empty($this->_export_modulexml_file)) {
			fclose($this->_export_modulexml_file);
			$this->_export_modulexml_file = null;
		}
	}

    /**
	 * Clean up the temporary files created.
	 * @access private
     */
	function __cleanupExport() {
		if(!empty($this->_export_modulexml_filename)) {
			unlink($this->__getManifestFilePath());
		}
	}

	/**
	 * Export Module as a zip file.
	 * @param Vtiger_Module Instance of module
	 * @param Path Output directory path
	 * @param String Zipfilename to use
	 * @param Boolean True for sending the output as download
	 */
	function export($moduleInstance, $todir='', $zipfilename='', $directDownload=false, $nameExport = '') {

		$module = $moduleInstance->name;

		if (empty($nameExport))
			$nameExport = $module;


		$this->__initExport($module, $moduleInstance);

		// Call module export function
		$this->export_Module($moduleInstance,$nameExport);

		$this->__finishExport();

		// Export as Zip

		if($zipfilename == '') $zipfilename = "$module-" . date('YmdHis') . ".zip";
		$zipfilename = "$this->_export_tmpdir/$zipfilename";

		if (file_exists($_SESSION['plat']."/modules/$module/$module.php"))
			$this->full_copy($_SESSION['plat']."/modules/$module","export/modules/$nameExport");
		else
			$this->full_copy("modules/$module","export/modules/$nameExport");

		$this->full_rename($module,$nameExport,"export/modules/$nameExport/");

		$this->full_copy("Smarty/templates/centaurus/modules/$module","export/Smarty/templates/centaurus/modules/$nameExport");
		$this->full_copy("cron/modules/$module","export/cron/modules/$nameExport");

		//Al archivo de la clase se le hacen las modificaciones respectivas
		$this->full_rename_code($module,$nameExport,"export/modules/$nameExport/");

		$zip = new Vtiger_Zip($zipfilename);
		// Add manifest file
		$zip->addFile($this->__getManifestFilePath(), "manifest.xml");
		// Copy module directory
		$zip->copyDirectoryFromDisk("export/modules/$nameExport","modules/$nameExport",null,null,$module,$nameExport);
		// Copy templates directory of the module (if any)
		if(is_dir("export/Smarty/templates/centaurus/modules/$nameExport"))
			$zip->copyDirectoryFromDisk("export/Smarty/templates/centaurus/modules/$nameExport", "templates",null,null,$module,$nameExport);
		// Copy cron files of the module (if any)
		if(is_dir("export/cron/modules/$nameExport"))
			$zip->copyDirectoryFromDisk("export/cron/modules/$nameExport", "cron",null,null,$module,$nameExport);

		$zip->save();

		$this->eliminarDir("export");

		if($directDownload) {
			$zip->forceDownload($zipfilename);
			unlink($zipfilename);
		}
		$this->__cleanupExport();
	}

	/**
	 * Export vtiger dependencies
	 * @access private
	 */
	function export_Dependencies($moduleInstance) {
		global $vtiger_current_version, $adb;
		$moduleid = $moduleInstance->id;

		$sqlresult = $adb->query("SELECT * FROM vtiger_tab_info WHERE tabid = $moduleid");
		$vtigerMinVersion = $vtiger_current_version;
		$vtigerMaxVersion = false;
		$noOfPreferences = $adb->num_rows($sqlresult);
		for($i=0; $i<$noOfPreferences; ++$i) {
			$prefName = $adb->query_result($sqlresult,$i,'prefname');
			$prefValue = $adb->query_result($sqlresult,$i,'prefvalue');
			if($prefName == 'vtiger_min_version') {
				$vtigerMinVersion = $prefValue;
			}
			if($prefName == 'vtiger_max_version') {
				$vtigerMaxVersion = $prefValue;
			}

		}

		$this->openNode('dependencies');
		$this->outputNode($vtigerMinVersion, 'vtiger_version');
		if($vtigerMaxVersion !== false)	$this->outputNode($vtigerMaxVersion, 'vtiger_max_version');
		$this->closeNode('dependencies');
	}

	/**
	 * Export Module Handler
	 * @access private
	 */
	function export_Module($moduleInstance,$nameExport = '') {
		global $adb;

		$moduleid = $moduleInstance->id;

		$sqlresult = $adb->query("SELECT * FROM vtiger_parenttabrel WHERE tabid = $moduleid");
		$parenttabid = $adb->query_result($sqlresult, 0, 'parenttabid');
		$menu = Vtiger_Menu::getInstance($parenttabid);
		$parent_name = $menu->label;

		$sqlresult = $adb->query("SELECT * FROM vtiger_tab WHERE tabid = $moduleid");
		$tabresultrow = $adb->fetch_array($sqlresult);

		$tabname = $tabresultrow['name'];
		$tablabel= $tabresultrow['tablabel'];
		$tabversion = isset($tabresultrow['version'])? $tabresultrow['version'] : false;

		$this->openNode('module');
		$this->outputNode(date('Y-m-d H:i:s'),'exporttime');
		//$this->outputNode($tabname, 'name');
		$this->outputNode($nameExport, 'name');
		$this->outputNode($tablabel, 'label');
		$this->outputNode($parent_name, 'parent');

		if(!$moduleInstance->isentitytype) {
			$this->outputNode('extension', 'type');
		}

		if($tabversion) {
			$this->outputNode($tabversion, 'version');
		}

		// Export dependency information
		$this->export_Dependencies($moduleInstance);

		// Export module tables
		$this->export_Tables($moduleInstance,$nameExport);

		// Export module blocks
		$this->export_Blocks($moduleInstance,$nameExport);

		// Export module filters
		$this->export_CustomViews($moduleInstance,$nameExport);

		// Export Sharing Access
		$this->export_SharingAccess($moduleInstance);

		// Export Events
		$this->export_Events($moduleInstance);

		// Export Actions
		$this->export_Actions($moduleInstance);

		// Export Related Lists
		$this->export_RelatedLists($moduleInstance);

		// Export Custom Links
		$this->export_CustomLinks($moduleInstance);

		//Export cronTasks
      $this->export_CronTasks($moduleInstance);

		// Grids fields confoguration
		$this->export_Grids($moduleInstance);

		// Code config
		$this->export_IncrementalCode($moduleInstance);

      $this->closeNode('module');
	}

	/**
	 * Export module base and related tables
	 * @access private
	 */
	function export_Tables($moduleInstance,$nameExport = '') {


		$_exportedTables = Array();

		$modulename = $moduleInstance->name;

		$this->openNode('tables');

		if($moduleInstance->isentitytype) {
			$focus = CRMEntity::getInstance($modulename);

			// Setup required module variables which is need for vtlib API's
			vtlib_setup_modulevars($modulename, $focus);

			if (isset($focus->tables_name))
				$tables = $focus->tables_name;
			else
				$tables = Array ($focus->table_name);
			if(!empty($focus->groupTable)) $tables[] = $focus->groupTable[0];
			if(!empty($focus->customFieldTable)) $tables[] = $focus->customFieldTable[0];

			$tables = array_merge($tables, $focus->gridTables());

			foreach($tables as $table) {
				//Se reemplaza el nombre codigo del modulo por el nuevo nombre c�digo
				$sqlTableName = Vtiger_Utils::CreateTableSql($table);
				$sqlTable = str_replace(strtolower($modulename),strtolower($nameExport),$sqlTableName);
				$tableName = $table;
				$table = str_replace(strtolower($modulename),strtolower($nameExport),$tableName);
				$this->openNode('table');
				$this->outputNode($table, 'name');
				$this->outputNode('<![CDATA['.$sqlTable.']]>', 'sql');
				$this->closeNode('table');

				$_exportedTables[] = $table;
			}

		}

		// Now export table information recorded in schema file
		if (file_exists($_SESSION['plat']."/modules/$modulename/schema.xml"))
			$schemaFile = $_SESSION['plat']."/modules/$modulename/schema.xml";
		else
			$schemaFile = "modules/$modulename/schema.xml";
		if(file_exists($schemaFile)) {
			$schema = simplexml_load_file("modules/$modulename/schema.xml");

			if(!empty($schema->tables) && !empty($schema->tables->table)) {
				foreach($schema->tables->table as $tablenode) {
					$sqlTable = Vtiger_Utils::CreateTableSql($table);
					$sqlTable = str_replace(strtolower($modulename),strtolower($nameExport),$sqlTable);
					$table = trim($tablenode->name);
					$table = str_replace(strtolower($modulename),strtolower($nameExport),$table);
					if(!in_array($table,$_exportedTables)) {
						$this->openNode('table');
						$this->outputNode($table, 'name');
						$this->outputNode('<![CDATA['.$sqlTable.']]>', 'sql');
						$this->closeNode('table');

						$_exportedTables[] = $table;
					}
				}
			}
		}
		$this->closeNode('tables');
	}

	/**
	 * Export module blocks with its related fields
	 * @access private
	 */
	function export_Blocks($moduleInstance,$nameExport = '') {
		global $adb;
		$sqlresult = $adb->pquery("SELECT * FROM vtiger_blocks WHERE tabid = ?", Array($moduleInstance->id));
		$resultrows= $adb->num_rows($sqlresult);

		if(empty($resultrows)) return;

		$this->openNode('blocks');
		for($index = 0; $index < $resultrows; ++$index) {
			$blockid    = $adb->query_result($sqlresult, $index, 'blockid');
			$blocklabel = $adb->query_result($sqlresult, $index, 'blocklabel');

			$this->openNode('block');
			$this->outputNode($blocklabel, 'label');
			// Export fields associated with the block
			$this->export_Fields($moduleInstance, $blockid, $nameExport);
			$this->closeNode('block');
		}
		$this->closeNode('blocks');
	}

	/**
	 * Export fields related to a module block
	 * @access private
	 */
	function export_Fields($moduleInstance, $blockid, $nameExport = '') {
		global $adb;

		$fieldresult = $adb->pquery("SELECT * FROM vtiger_field WHERE tabid=? AND block=?", Array($moduleInstance->id, $blockid));
		$fieldcount = $adb->num_rows($fieldresult);
		$modulename = $moduleInstance->name;

		if(empty($fieldcount)) return;

		$entityresult = $adb->pquery("SELECT * FROM vtiger_entityname WHERE tabid=?", Array($moduleInstance->id));
		$entity_fieldname = $adb->query_result($entityresult, 0, 'fieldname');

		$this->openNode('fields');
		for($index = 0; $index < $fieldcount; ++$index) {
			$this->openNode('field');
			$fieldresultrow = $adb->fetch_row($fieldresult);

			$fieldname = $fieldresultrow['fieldname'];
			$fieldnameAct = str_replace(strtolower($modulename),strtolower($nameExport),$fieldname);
			$columnname = $fieldresultrow['columnname'];
			$columnnameAct = str_replace(strtolower($modulename),strtolower($nameExport),$columnname);

			$tablename = $fieldresultrow['tablename'];
			$tablenameAct = str_replace(strtolower($modulename),strtolower($nameExport),$tablename);
			$uitype = $fieldresultrow['uitype'];
			$fieldid = $fieldresultrow['fieldid'];

			$this->outputNode($fieldnameAct, 'fieldname');
			$this->outputNode($uitype,    'uitype');
			$this->outputNode($columnnameAct,'columnname');
			$this->outputNode($tablenameAct,     'tablename');
			$this->outputNode($fieldresultrow['generatedtype'], 'generatedtype');
			$this->outputNode($fieldresultrow['fieldlabel'],    'fieldlabel');
			$this->outputNode($fieldresultrow['readonly'],      'readonly');
			$this->outputNode($fieldresultrow['presence'],      'presence');
			$this->outputNode($fieldresultrow['defaultvalue'],  'defaultvalue');
			$this->outputNode($fieldresultrow['sequence'],      'sequence');
			$this->outputNode($fieldresultrow['maximumlength'], 'maximumlength');
			$this->outputNode($fieldresultrow['typeofdata'],    'typeofdata');
			$this->outputNode($fieldresultrow['quickcreate'],   'quickcreate');
			$this->outputNode($fieldresultrow['quickcreatesequence'],   'quickcreatesequence');
			$this->outputNode($fieldresultrow['displaytype'],   'displaytype');
			$this->outputNode($fieldresultrow['info_type'],     'info_type');
			$this->outputNode('<![CDATA['.$fieldresultrow['helpinfo'].']]>', 'helpinfo');
			if(isset($fieldresultrow['masseditable'])) {
				$this->outputNode($fieldresultrow['masseditable'], 'masseditable');
			}

			// Export Entity Identifier Information
			if($fieldname == $entity_fieldname) {
				$this->openNode('entityidentifier');
				$entityfieldid = $adb->query_result($entityresult, 0, 'entityidfield');
				$entityidcolumn = $adb->query_result($entityresult, 0, 'entityidcolumn');

				$entityfieldidAct = str_replace(strtolower($modulename),strtolower($nameExport),$entityfieldid);
				$entityidcolumnAct = str_replace(strtolower($modulename),strtolower($nameExport),$entityidcolumn);
				$this->outputNode($entityfieldidAct,    'entityidfield');
				$this->outputNode($entityidcolumnAct, 'entityidcolumn');
				$this->closeNode('entityidentifier');
			}

			// Export picklist values for picklist fields
			if($uitype == '15' || $uitype == '16' || $uitype == '111' || $uitype == '33' || $uitype == '55') {

				if($uitype == '16') {
					$picklistvalues = vtlib_getPicklistValues($fieldname);
				} else {
					$picklistvalues = vtlib_getPicklistValues_AccessibleToAll($fieldname);
				}
				$this->openNode('picklistvalues');
				foreach($picklistvalues as $picklistvalue) {
					$this->outputNode($picklistvalue, 'picklistvalue');
				}
				$this->closeNode('picklistvalues');
			}

			// Export field to module relations
			if($uitype == '10') {
				$relatedmodres = $adb->pquery("SELECT * FROM vtiger_fieldmodulerel WHERE fieldid=?", Array($fieldid));
				$relatedmodcount = $adb->num_rows($relatedmodres);
				if($relatedmodcount) {
					$this->openNode('relatedmodules');
					for($relmodidx = 0; $relmodidx < $relatedmodcount; ++$relmodidx) {
						$this->outputNode($adb->query_result($relatedmodres, $relmodidx, 'relmodule'), 'relatedmodule');
					}
					$this->closeNode('relatedmodules');
				}
			}

			$this->closeNode('field');

		}
		$this->closeNode('fields');
	}

	/**
	 * Export Custom views of the module
	 * @access private
	 */
	function export_CustomViews($moduleInstance,$nameExport = '') {
		global $adb;

		$customviewres = $adb->pquery("SELECT * FROM vtiger_customview WHERE entitytype = ?", Array($moduleInstance->name));
		$customviewcount=$adb->num_rows($customviewres);

		if(empty($customviewcount)) return;

		$this->openNode('customviews');
		for($cvindex = 0; $cvindex < $customviewcount; ++$cvindex) {

			$cvid = $adb->query_result($customviewres, $cvindex, 'cvid');

			$cvcolumnres = $adb->query("SELECT * FROM vtiger_cvcolumnlist WHERE cvid=$cvid");
			$cvcolumncount=$adb->num_rows($cvcolumnres);

			$this->openNode('customview');

			$setdefault = $adb->query_result($customviewres, $cvindex, 'setdefault');
			$setdefault = ($setdefault == 1)? 'true' : 'false';

			$setmetrics = $adb->query_result($customviewres, $cvindex, 'setmetrics');
			$setmetrics = ($setmetrics == 1)? 'true' : 'false';

			$this->outputNode($adb->query_result($customviewres, $cvindex, 'viewname'),   'viewname');
			$this->outputNode($setdefault, 'setdefault');
			$this->outputNode($setmetrics, 'setmetrics');

			$this->openNode('fields');
			for($index = 0; $index < $cvcolumncount; ++$index) {
				$cvcolumnindex = $adb->query_result($cvcolumnres, $index, 'columnindex');
				$cvcolumnname = $adb->query_result($cvcolumnres, $index, 'columnname');
				$cvcolumnnames= explode(':', $cvcolumnname);
				$cvfieldname = $cvcolumnnames[2];

				$columnname = $cvfieldname;
				$columnnameAct = str_replace(strtolower($moduleInstance->name),strtolower($nameExport),$columnname);
				$this->openNode('field');
				$this->outputNode($columnnameAct, 'fieldname');
				$this->outputNode($cvcolumnindex,'columnindex');

				$cvcolumnruleres = $adb->pquery("SELECT * FROM vtiger_cvadvfilter WHERE cvid=? AND columnname=?",
					Array($cvid, $cvcolumnname));
				$cvcolumnrulecount = $adb->num_rows($cvcolumnruleres);

				if($cvcolumnrulecount) {
					$this->openNode('rules');
					for($rindex = 0; $rindex < $cvcolumnrulecount; ++$rindex) {
						$cvcolumnruleindex = $adb->query_result($cvcolumnruleres, $rindex, 'columnindex');
						$cvcolumnrulecomp  = $adb->query_result($cvcolumnruleres, $rindex, 'comparator');
						$cvcolumnrulevalue = $adb->query_result($cvcolumnruleres, $rindex, 'value');
						$cvcolumnrulecomp  = Vtiger_Filter::translateComparator($cvcolumnrulecomp, true);

						$this->openNode('rule');
						$this->outputNode($cvcolumnruleindex, 'columnindex');
						$this->outputNode($cvcolumnrulecomp, 'comparator');
						$this->outputNode($cvcolumnrulevalue, 'value');
						$this->closeNode('rule');

					}
					$this->closeNode('rules');
				}

				$this->closeNode('field');
			}
			$this->closeNode('fields');

			$this->closeNode('customview');
		}
		$this->closeNode('customviews');
	}

	/**
	 * Export Sharing Access of the module
	 * @access private
	 */
	function export_SharingAccess($moduleInstance) {
		global $adb;

		$deforgshare = $adb->pquery("SELECT * FROM vtiger_def_org_share WHERE tabid=?", Array($moduleInstance->id));
		$deforgshareCount = $adb->num_rows($deforgshare);

		if(empty($deforgshareCount)) return;

		$this->openNode('sharingaccess');
		if($deforgshareCount) {
			for($index = 0; $index < $deforgshareCount; ++$index) {
				$permission = $adb->query_result($deforgshare, $index, 'permission');
				$permissiontext = '';
				if($permission == '0') $permissiontext = 'public_readonly';
				if($permission == '1') $permissiontext = 'public_readwrite';
				if($permission == '2') $permissiontext = 'public_readwritedelete';
				if($permission == '3') $permissiontext = 'private';

				$this->outputNode($permissiontext, 'default');
			}
		}
		$this->closeNode('sharingaccess');
	}

	/**
	 * Export Events of the module
	 * @access private
	 */
	function export_Events($moduleInstance) {
		$events = Vtiger_Event::getAll($moduleInstance);
		if(!$events) return;

		$this->openNode('events');
		foreach($events as $event) {
			$this->openNode('event');
			$this->outputNode($event->eventname, 'eventname');
			$this->outputNode('<![CDATA['.$event->classname.']]>', 'classname');
			$this->outputNode('<![CDATA['.$event->filename.']]>', 'filename');
			$this->outputNode('<![CDATA['.$event->condition.']]>', 'condition');
			$this->closeNode('event');
		}
		$this->closeNode('events');
	}

	/**
	 * Export actions (tools) associated with module.
	 * TODO: Need to pickup values based on status for all user (profile)
	 * @access private
	 */
	function export_Actions($moduleInstance) {

		if(!$moduleInstance->isentitytype) return;

		global $adb;
		$result = $adb->pquery('SELECT distinct(actionname) FROM vtiger_profile2utility, vtiger_actionmapping
			WHERE vtiger_profile2utility.activityid=vtiger_actionmapping.actionid and tabid=?', Array($moduleInstance->id));

		if($adb->num_rows($result)) {
			$this->openNode('actions');
			while($resultrow = $adb->fetch_array($result)) {
				$this->openNode('action');
				$this->outputNode('<![CDATA['. $resultrow['actionname'] .']]>', 'name');
				$this->outputNode('enabled', 'status');
				$this->closeNode('action');
			}
			$this->closeNode('actions');
		}
	}

	/**
	 * Export related lists associated with module.
	 * @access private
	 */
	function export_RelatedLists($moduleInstance) {

		if(!$moduleInstance->isentitytype) return;

		global $adb;
		$result = $adb->pquery("SELECT * FROM vtiger_relatedlists WHERE tabid = ?", Array($moduleInstance->id));
		if($adb->num_rows($result)) {
			$this->openNode('relatedlists');

			for($index = 0; $index < $adb->num_rows($result); ++$index) {
				$row = $adb->fetch_array($result);
				$this->openNode('relatedlist');

				$this->outputNode($row['name'], 'function');
				$this->outputNode($row['label'], 'label');
				$this->outputNode($row['sequence'], 'sequence');
				$this->outputNode($row['presence'], 'presence');

				$action_text = $row['actions'];
				if(!empty($action_text)) {
					$this->openNode('actions');
					$actions = explode(',', $action_text);
					foreach($actions as $action) {
						$this->outputNode($action, 'action');
					}
					$this->closeNode('actions');
				}

				$relModuleInstance = Vtiger_Module::getInstance($row['related_tabid']);
				$this->outputNode($relModuleInstance->name, 'relatedmodule');

				$this->closeNode('relatedlist');
			}

			$this->closeNode('relatedlists');
		}
	}

	/**
	 * Export custom links of the module.
	 * @access private
	 */
	function export_CustomLinks($moduleInstance) {
		$customlinks = $moduleInstance->getLinks();
		if(!empty($customlinks)) {
			$this->openNode('customlinks');
			foreach($customlinks as $customlink) {
				$this->openNode('customlink');
				$this->outputNode($customlink->linktype, 'linktype');
				$this->outputNode($customlink->linklabel, 'linklabel');
				$this->outputNode("<![CDATA[$customlink->linkurl]]>", 'linkurl');
				$this->outputNode("<![CDATA[$customlink->linkicon]]>", 'linkicon');
				$this->outputNode($customlink->sequence, 'sequence');
				$this->outputNode("<![CDATA[$customlink->handler_path]]>", 'handler_path');
				$this->outputNode("<![CDATA[$customlink->handler_class]]>", 'handler_class');
				$this->outputNode("<![CDATA[$customlink->handler]]>", 'handler');
				$this->closeNode('customlink');
			}
			$this->closeNode('customlinks');
		}
	}

	/**
	 * Export cron tasks for the module.
	 * @access private
	 */
	function export_CronTasks($moduleInstance){
        $cronTasks = Vtiger_Cron::listAllInstancesByModule($moduleInstance->name);
        $this->openNode('crons');
        foreach($cronTasks as $cronTask){
            $this->openNode('cron');
            $this->outputNode($cronTask->getName(),'name');
            $this->outputNode($cronTask->getFrequency(),'frequency');
            $this->outputNode($cronTask->getStatus(),'status');
            $this->outputNode($cronTask->getHandlerFile(),'handler');
            $this->outputNode($cronTask->getSequence(),'sequence');
            $this->outputNode($cronTask->getDescription(),'description');
            $this->closeNode('cron');
        }
      $this->closeNode('crons');
    }

	/**
	 * Helper function to log messages
	 * @param String Message to log
	 * @param Boolean true appends linebreak, false to avoid it
	 * @access private
	 */
	static function log($message, $delim=true) {
		Vtiger_Utils::Log($message, $delim);
	}

	function full_copy( $source, $target ) {
		if ( is_dir( $source ) ) {
			@mkdir( $target, 0777, true);
			$d = dir( $source );
			while ( FALSE !== ( $entry = $d->read() ) ) {
				if ( $entry == '.' || $entry == '..' ) {
					continue;
				}
				$Entry = $source . '/' . $entry;
				if ( is_dir( $Entry ) ) {
					$this->full_copy( $Entry, $target . '/' . $entry );
					continue;
				}
				copy( $Entry, $target . '/' . $entry );
			}
			$d->close();
		}
		else {
			copy( $source, $target );
		}
	}

	function eliminarDir($carpeta) {
		foreach(glob($carpeta . "/*") as $archivos_carpeta) {
			if (is_dir($archivos_carpeta)) {
				$this->eliminarDir($archivos_carpeta);
			}
			else {
				unlink($archivos_carpeta);
			}
		}
		rmdir($carpeta);
	}

	function full_rename($source,$target,$path) {

		$midir=opendir($path);
		while($archivo=readdir($midir)){
			 if( !is_dir($archivo) && $archivo!="." && $archivo!="..") {
				$newName = str_replace($source,$target,$archivo);
				rename($path.$archivo,$path.$newName);
			}
		}
		closedir($midir);
	}

	function full_rename_code($module,$nameExport,$path) {

		$midir=opendir($path);
		while($archivo=readdir($midir)){
			if( !is_dir($archivo) && $archivo!="." && $archivo!="..") {
				$FILE = fopen($path.$archivo,'r');
				//obtenemos de una sola vez todo el contenido del fichero
				$contenido_fichero = fread($FILE, filesize($path.$archivo));
				fclose($FILE);

				$contenido_fichero_mod = str_ireplace($module,strtolower($nameExport),$contenido_fichero);
				$FILE = fopen($path.$archivo,'w');
				fwrite($FILE, $contenido_fichero_mod);
				fclose($FILE);

			}
		}
		closedir($midir);
	}

	/**
	 * Export grids fields
	 * @access private
	 */
	function export_Grids($moduleInstance,$nameExport = '') {
		global $adb;
		$sqlresult = $adb->pquery("select fieldid, fieldname, fieldlabel from vtiger_field inner join vtiger_tab using(tabid) where uitype=2202 and tabid = ?", Array($moduleInstance->id));
		$resultrows= $adb->num_rows($sqlresult);

		if(empty($resultrows)) return;

		$this->openNode('grids');
		for($index = 0; $index < $resultrows; ++$index) {
			$gridid    = $adb->query_result($sqlresult, $index, 'fieldid');
			$gridname = $adb->query_result($sqlresult, $index, 'fieldname');
			$gridlabel = $adb->query_result($sqlresult, $index, 'fieldlabel');

			$this->openNode('grid');
			$this->outputNode($gridname, 'name');
			$this->outputNode(utf8_decode($gridlabel), 'label');
			// Export subfields associated with the grid
			$this->export_Subfields($moduleInstance, $gridid, $nameExport);
			$this->closeNode('grid');
		}
		$this->closeNode('grids');
	}

	/**
	 * Export grids fields
	 * @access private
	 */
	function export_IncrementalCode($moduleInstance,$nameExport = '') {
		global $adb;
		$sqlresult = $adb->pquery("SELECT vtiger_modentity_num.* FROM `vtiger_modentity_num` inner join vtiger_tab on (semodule=name) WHERE tabid=?", Array($moduleInstance->id));
		$resultrows= $adb->num_rows($sqlresult);

		if(empty($resultrows)) return;

		$this->openNode('modulecode');
		for($index = 0; $index < $resultrows; ++$index) {
			$semodule = $adb->query_result($sqlresult, $index, 'semodule');
			$prefix = $adb->query_result($sqlresult, $index, 'prefix');
			$cur_id = $start_id = $adb->query_result($sqlresult, $index, 'start_id');
			$active = $adb->query_result($sqlresult, $index, 'active');


			$this->outputNode($semodule, 'semodule');
			$this->outputNode($prefix, 'prefix');
			$this->outputNode($start_id, 'start_id');
			$this->outputNode($active, 'active');
			$this->outputNode($cur_id, 'cur_id');
			break;
		}
		$this->closeNode('modulecode');
	}

	/**
	 * Export subfields related to a module block
	 * @access private
	 */
	function export_Subfields($moduleInstance, $blockid, $nameExport = '') {
		global $adb;

		$fieldresult = $adb->pquery("SELECT * FROM vtiger_subfields WHERE fieldid=?", Array($blockid));
		$fieldcount = $adb->num_rows($fieldresult);
		$modulename = $moduleInstance->name;

		if(empty($fieldcount)) return;

		$this->openNode('fields');
		for($index = 0; $index < $fieldcount; ++$index) {
			$this->openNode('field');
			$fieldresultrow = $adb->fetch_row($fieldresult);

			$this->outputNode($fieldresultrow['name'], 'name');
			$this->outputNode($fieldresultrow['label'], 'label');
			$this->outputNode ($fieldresultrow['sequence'], 'sequence');
			$this->outputNode($fieldresultrow['uitype'], 'uitype');
			$this->outputNode($fieldresultrow['length'], 'length');
			$this->outputNode($fieldresultrow['precision'], 'precision');
			$this->outputNode($fieldresultrow['defaultvalue'], 'defaultvalue');
			$this->outputNode($fieldresultrow['relmodule'], 'relmodule');

			$values = !empty ($fieldresultrow ['values']) ? json_decode ($fieldresultrow ['values']) : null;
			$this->openNode ('values');
			if (!empty ($values)) {
				foreach ($values as $value) {
					$this->outputNode (htmlentities ($value, null, 'UTF-8'), 'value');
				}
			}
			$this->closeNode ('values');

			$this->closeNode('field');

		}
		$this->closeNode('fields');
	}
}
?>