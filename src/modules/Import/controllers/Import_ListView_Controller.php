<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

	require_once 'modules/Import/resources/Utils.php';
	require_once 'modules/Import/ui/Viewer.php';
	require_once 'include/QueryGenerator/QueryGenerator.php';

	class Import_ListView_Controller {
		var $user;
		var $module;
		static $_cached_module_meta;
	
		public function  __construct() {
		}
		
		private static function getFiledLabels ($adb, $tabId) {
			if (empty ($tabId)) {
				return null;
			}
			$result = $adb->pquery('SELECT columnname, fieldlabel FROM vtiger_field WHERE tabid=?', array ($tabId));
			if (($result) && ($adb->num_rows ($result))) {
				$fieldLabels ['status'] = 'Descartado por';
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$fieldLabels [$row['columnname']] = $row ['fieldlabel'];
				}
			}
			return (isset ($fieldLabels)) ? $fieldLabels : null;
		}
		
		public static function discardedRecords ($userInputObject, $user) {
			global $list_max_entries_per_page;
			$adb       = PearDatabase::getInstance();
			$viewer    = new Import_UI_Viewer();
			$ownerId   = $userInputObject->get('foruser');
			$owner     = new Users();
			$owner->id = $ownerId;
			$owner->retrieve_entity_info($ownerId, 'Users');
			if (!is_admin($user) && $user->id != $owner->id) {
				$viewer->display('OperationNotPermitted.tpl', 'Vtiger');
				exit;
			}
			$userDBTableName = Import_Utils::getDbTableName($owner);
			$isPDF           = $userInputObject->get('pdf');
			$slectedModule   = ($isPDF == 'no') ? 'module' : 'for_module';
			$moduleName      = $userInputObject->get($slectedModule);
			$tabId           = getTabid ($moduleName);
			$dataHeader      = self::getFiledLabels ($adb, $tabId);
			$recordStatus    = array (1 => 'CREADO', 2 => 'OMITIDO',3 => 'ACTUALIZADO',4 => 'FUSIONADA',5 => ' FALLÓ');
			$noFound         = array();
			$result = $adb->query('SELECT * FROM '.$userDBTableName.' WHERE recordid IS NULL');
			
			if (($result) && ($adb->num_rows ($result)) && count ($dataHeader)) {
				$dataRow = array();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					foreach ($dataHeader as $key => $value) {
						if ($key == 'status') {
							$dataRow[$key] = "<b>{$recordStatus [$row[ $key ]]}</b>";
							continue;
						}
						if (isset ($row [$key]) && !empty ($row[$key]) && !is_numeric($row[$key])) {
							$dataRow[$key] = $row[ $key ];
						} else {
							$noFound [] = $key;
						}
						
					}
					$rows [] = $dataRow;
				}
				foreach ($noFound as $key) {
					unset ($dataHeader[$key]);
				}
			}
			
			$viewer->assign('MODULE', $moduleName);
			$viewer->assign('MODULE_LABEL', getTabname ($tabId));
			$viewer->assign('IS_PDF', $isPDF);
			$viewer->assign('LISTHEADER', $dataHeader);
			$viewer->assign('LISTENTITY', isset($rows) ? $rows : null);
			$viewer->assign('FOR_MODULE', $moduleName);
			$viewer->assign('FOR_USER', $ownerId);
			$viewer->assign('PAGINATION', array('total' => 1, 'start' => 1, 'end' => 1, 'next' => 1, 'prev' => 1));
			if ($isPDF == 'yes') {
				$_SESSION ['pdf_html'] = $viewer->fetch('ListViewDiscardedRecords.tpl');
			} else {
				echo $viewer->fetch('ListViewDiscardedRecords.tpl');
				
			}
			
		}
		
		public static function getModuleMeta($moduleName, $user) {
			if(empty(self::$_cached_module_meta[$moduleName][$user->id])) {
				$moduleHandler = vtws_getModuleHandlerFromName($moduleName, $user);
				self::$_cached_module_meta[$moduleName][$user->id] = $moduleHandler->getMeta();
			}
			return self::$_cached_module_meta[$moduleName][$user->id];
		}
	
		public static function render($userInputObject, $user) {
			global $list_max_entries_per_page;
			$adb = PearDatabase::getInstance();
	
			$viewer = new Import_UI_Viewer();
	
			$ownerId = $userInputObject->get('foruser');
			$owner = new Users();
			$owner->id = $ownerId;
			$owner->retrieve_entity_info($ownerId, 'Users');
			if(!is_admin($user) && $user->id != $owner->id) {
				$viewer->display('OperationNotPermitted.tpl', 'Vtiger');
				exit;
			}
			$userDBTableName = Import_Utils::getDbTableName($owner);
	
			$moduleName = $userInputObject->get('module');
			$moduleMeta = self::getModuleMeta($moduleName, $user);
	
			$result = $adb->query('SELECT recordid FROM '.$userDBTableName.' WHERE status is NOT NULL AND recordid IS NOT NULL');
			$noOfRecords = $adb->num_rows($result);
	
			$importedRecordIds = array();
			for($i=0; $i<$noOfRecords; ++$i) {
				$importedRecordIds[] = $adb->query_result($result, $i, 'recordid');
			}
			if(count($importedRecordIds) == 0) $importedRecordIds[] = 0;
	
	
			$focus = CRMEntity::getInstance($moduleName);
			$queryGenerator = new QueryGenerator($moduleName, $user);
			$customView = new CustomView($moduleName);
			$viewId = $customView->getViewIdByName('All', $moduleName);
			$queryGenerator->initForCustomViewById($viewId);
			$list_query = $queryGenerator->getQuery();
	
			// Fetch only last imported records
			$list_query .= ' AND '.$focus->table_name.'.'.$focus->table_index.' IN ('. implode(',', $importedRecordIds).')';
	
			if(PerformancePrefs::getBoolean('LISTVIEW_COMPUTE_PAGE_COUNT', false) === true){
				$count_result = $adb->query( mkCountQuery( $list_query));
				$noofrows = $adb->query_result($count_result,0,"count");
			}else{
				$noofrows = null;
			}
	
			$start = ListViewSession::getRequestCurrentPage($moduleName, $list_query, $viewId, false);
	
			$navigation_array = VT_getSimpleNavigationValues($start,$list_max_entries_per_page,$noofrows);
	
			$limit_start_rec = ($start-1) * $list_max_entries_per_page;
	
			if( $adb->dbType == "pgsql")
				$list_result = $adb->pquery($list_query. " OFFSET $limit_start_rec LIMIT $list_max_entries_per_page", array());
			else
				$list_result = $adb->pquery($list_query. " LIMIT $limit_start_rec, $list_max_entries_per_page", array());
	
			$recordListRangeMsg = getRecordRangeMessage($list_result, $limit_start_rec,$noofrows);
			$viewer->assign('recordListRange',$recordListRangeMsg);
	
			$controller = new ListViewController($adb, $user, $queryGenerator);
			$listview_header = $controller->getListViewHeader($focus,$moduleName,$url_string,$sorder,$order_by,true);
			$listview_entries = $controller->getListViewEntries($focus,$moduleName,$list_result,$navigation_array,true);
	
			$viewer->assign('CURRENT_PAGE', $start);
			$viewer->assign('LISTHEADER', $listview_header);
			$viewer->assign('LISTENTITY', $listview_entries);
	
			$viewer->assign('FOR_MODULE', $moduleName);
			$viewer->assign('FOR_USER', $ownerId);
	
			$isAjax = $userInputObject->get('ajax');
			if(!empty($isAjax)) {
				echo $viewer->fetch('ListViewEntries.tpl');
			} else {
				$viewer->display('ImportListView.tpl');
			}
		}
	}
	
	?>
