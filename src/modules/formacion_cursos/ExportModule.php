<?php

require_once('config.php');
require_once('include/logging.php');
require_once('include/database/PearDatabase.php');
require_once('modules/Accounts/Accounts.php');
require_once('modules/Contacts/Contacts.php');
require_once('modules/Contacts/Contacts.php');
require_once('modules/Calendar/Activity.php');
require_once('modules/Documents/Documents.php');
require_once('modules/Potentials/Potentials.php');
require_once('modules/Users/Users.php');
require_once('modules/HelpDesk/HelpDesk.php');
require_once('include/utils/UserInfoUtil.php');
require_once('modules/CustomView/CustomView.php');
require_once 'modules/PickList/PickListUtils.php';

// Set the current language and the language strings, if not already set.
setCurrentLanguage();

global $allow_exports,$app_strings;

session_start();
$current_user = new Users();

if (isset($_SESSION['authenticated_user_id'])) {
	$result = $current_user->retrieveCurrentUserInfoFromFile($_SESSION['authenticated_user_id']);
	if ($result == null) {
		session_destroy();
		header('Location: index.php?action=Login&module=Users');
		exit;
	}
}

// Security Check
if (isPermitted($_REQUEST['module'],'Export') == 'no') {
	$allow_exports='none';
}
if ($allow_exports=='none' || ($allow_exports=='admin' && ! is_admin($current_user))) {
// @codingStandardsIgnoreStart
?>
	<script type='text/javascript'>
		alert("<?php echo $app_strings['NOT_PERMITTED_TO_EXPORT']?>");
		window.location="index.php?module=<?php echo vtlib_purify($_REQUEST['module']) ?>&action=index";
	</script>
	
	<?php exit; ?>
<?php
// @codingStandardsIgnoreEnd
}

/**
 * Function convert line breaks to space in description during export
 *
 * @param string $str
 *
 * @return string
 */
// @codingStandardsIgnoreStart
function br2nl_vt($str) {
	// @codingStandardsIgnoreEnd
	global $log;
	$log->debug('Entering br2nl_vt('.$str.') method ...');
	$str = preg_replace('/(\r\n)/', ' ', $str);
	$log->debug('Exiting br2nl_vt method ...');
	return $str;
}

/**
 * This function exports all the data for a given module
 *
 * @param  $type
 *
 * @return array
 */
function export($type) {
	global $log,$list_max_entries_per_page, $adb;
	$log->debug('Entering export('.$type.') method ...');
	$focus = 0;

	if ($type != '') {
		// vtlib customization: Hook to dynamically include required module file.
		// Refer to the logic in setting $currentModule in index.php
		$focus = CRMEntity::getInstance($type);
	}
	$log = LoggerManager::getLogger('export_'.$type);
	$adb = PearDatabase::getInstance();

	$oCustomView = new CustomView('$type');
	$viewid = $oCustomView->getViewId('$type');
	$sorder = $focus->getSortOrder();
	$order_by = $focus->getOrderBy();

	$search_type = $_REQUEST['search_type'];
	$export_data = $_REQUEST['export_data'];

	$where='';
	if (isset($_SESSION['export_where']) && $_SESSION['export_where']!='' && $search_type == 'includesearch') {
		$where =$_SESSION['export_where'];
	}

	$query = $focus->create_export_query($where);
	$query .= searchTypeAux($search_type, $type, $viewid);
	$params = paramAux($search_type, $export_data, $type);
	$query .= orderByAux($order_by, $sorder, $type);

	if ($export_data == 'currentpage') {
		$current_page = ListViewSession::getCurrentPage($type,$viewid);
		$limit_start_rec = (($current_page - 1) * $list_max_entries_per_page);
		if ($limit_start_rec < 0) {
			$limit_start_rec = 0;
		}
		$query .= ' LIMIT '.$limit_start_rec.','.$list_max_entries_per_page;
	}

	$resultFull = array();
	$resultFull[0] = $query;
	$resultFull[1] = $params;

	$log->debug('Exiting export method ...');
	return $resultFull;
}

function searchTypeAux($search_type, $type, $viewid) {
	global $oCustomView;
	$query = '';
	if($search_type != 'includesearch' && $type != 'Calendar') {
		$stdfiltersql = $oCustomView->getCVStdFilterSQL($viewid);
		$advfiltersql = $oCustomView->getCVAdvFilterSQL($viewid);
		if (isset($stdfiltersql) && $stdfiltersql != '') {
			$query .= ' and '.$stdfiltersql;
		}
		if (isset($advfiltersql) && $advfiltersql != '') {
			$query .= ' and '.$advfiltersql;
		}
	}
	return $query;
}

function paramAux($search_type, $export_data, $type) {
	$params = array();
	if (($search_type == 'withoutsearch' || $search_type == 'includesearch') && $export_data == 'selecteddata') {
		$params = searchParamsAux($type);
	}
	return $params;
}

function searchParamsAux($type) {
	global $query;
	$params = array();
	$idstring = explode(';', $_REQUEST['idstring']);
	if ($type == 'Accounts' && count($idstring) > 0) {
		$query .= ' and vtiger_account.accountid in ('. generateQuestionMarks($idstring) .')';
		array_push($params, $idstring);
	} else if ($type == 'Contacts' && count($idstring) > 0) {
		$query .= ' and vtiger_contactdetails.contactid in ('. generateQuestionMarks($idstring) .')';
		array_push($params, $idstring);
	} else if ($type == 'Potentials' && count($idstring) > 0) {
		$query .= ' and vtiger_potential.potentialid in ('. generateQuestionMarks($idstring) .')';
		array_push($params, $idstring);
	} else if ($type == 'Leads' && count($idstring) > 0) {
		$query .= ' and vtiger_leaddetails.leadid in ('. generateQuestionMarks($idstring) .')';
		array_push($params, $idstring);
	} else if ($type == 'Products' && count($idstring) > 0) {
		$query .= ' and vtiger_products.productid in ('. generateQuestionMarks($idstring) .')';
		array_push($params, $idstring);
	} else if($type == 'Documents' && count($idstring) > 0) {
		$query .= ' and vtiger_notes.notesid in ('. generateQuestionMarks($idstring) .')';
		array_push($params, $idstring);
	} else if($type == 'HelpDesk' && count($idstring) > 0) {
		$query .= ' and vtiger_troubletickets.ticketid in ('. generateQuestionMarks($idstring) .')';
		array_push($params, $idstring);
	} else if($type == 'Vendors' && count($idstring) > 0) {
		$query .= ' and vtiger_vendor.vendorid in ('. generateQuestionMarks($idstring) .')';
		array_push($params, $idstring);
	} else if(count($idstring) > 0) {
		// vtlib customization: Hook to make the export feature available for custom modules.
		$query .= ' and $focus->table_name.$focus->table_index in (' . generateQuestionMarks($idstring) . ')';
		array_push($params, $idstring);
		// END
	}
	return $params;
}

function orderByAux($order_by, $sorder, $type) {
	global $adb;
	$query = '';
	if (isset($order_by) && $order_by != '') {
		if ($order_by == 'smownerid') {
			$query .= ' ORDER BY user_name ' . $sorder;
		} else if ($order_by == 'lastname' && $type == 'Documents') {
			$query .= ' ORDER BY vtiger_contactdetails.lastname  ' . $sorder;
		} else if ($order_by == 'crmid' && $type == 'HelpDesk') {
			$query .= ' ORDER BY vtiger_troubletickets.ticketid  ' . $sorder;
		} else {
			$tablename = getTableNameForField($type, $order_by);
			$tablename = (($tablename != '') ? ($tablename . '.') : '');
			if ($adb->dbType == 'pgsql') {
				$query .= ' GROUP BY ' . $tablename . $order_by;
			}
			$query .= ' ORDER BY ' . $tablename . $order_by . ' ' . $sorder;
		}
	}
	return $query;
}

global $adb;

// Send the output header and invoke function for contents output
$moduleName = $_REQUEST['activemod'];
$moduleName = getTranslatedString($moduleName, $moduleName);
$moduleName = str_replace(' ','_',$moduleName);

// Include PHPExcel
require_once('modules/Import/PHPExcel-1.8/Classes/PHPExcel.php');
// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set document properties
$objPHPExcel->getProperties()->setCreator('TimeManagement_');
$objPHPExcel->getProperties()->setLastModifiedBy('TimeManagement_');
$objPHPExcel->getProperties()->setTitle('Office 2007 XLSX Test Document');
$objPHPExcel->getProperties()->setSubject('Office 2007 XLSX Test Document');
$objPHPExcel->getProperties()->setDescription('Test document for Office 2007 XLSX, generated using PHP classes.');
$objPHPExcel->getProperties()->setKeywords('office 2007 openxml php');
$objPHPExcel->getProperties()->setCategory('Test result file');

$type = vtlib_purify($_REQUEST['activemod']);
$resultFull = export(vtlib_purify($_REQUEST['activemod']));
$result = $adb->pquery($resultFull[0], $resultFull[1], true, "Error exporting $type: <BR>$query");
$fields_array = $adb->getFieldsArray($result);
$fields_array = array_diff($fields_array,array('user_name'));
$__processor = new ExportUtils($type, $fields_array);
	
// Translated the field names based on the language used.
$translated_fields_array = array();
$contador = count($fields_array);
for ($i=0; $i < $contador; $i++) {
	$translated_fields_array[$i] = getTranslatedString($fields_array[$i],$type);
}

$arr_val = array();
$arr_val = $translated_fields_array;
$columFinal = array();
$activa = 0;
if ($arr_val) {
	$lastColumn = $objPHPExcel->getActiveSheet()->getHighestColumn();
	$head = 1;
	$lastColumn++;
	$i=0;
	for ($column = 'A'; $column != $lastColumn; $column++) {
		$objPHPExcel->setActiveSheetIndex($activa)->setCellValue($column.$head, $arr_val[$i]);
		$columFinal[$i] = $column;
		$lastColumn++;
		$i++;
		if ($i>=count($arr_val)) {
			break;
		}
	}

	if ($type != '') {
		// vtlib customization: Hook to dynamically include required module file.
		// Refer to the logic in setting $currentModule in index.php
		$focus = CRMEntity::getInstance($type);
	}

	$i = 2;
	while ($val = $adb->fetchByAssoc($result, -1, false)) {
		$new_arr = array();
		$val = $__processor->sanitizeValues($val);
		
		foreach ($val as $key => $value){
			if ($type == 'Documents' && $key == 'description') {
				$value = strip_tags($value);
				$value = str_replace('&nbsp;','',$value);
				array_push($new_arr,$value);
			} else if ($key != 'user_name') {
				// Let us provide the module to transform the value before we save it to CSV file
				$value = $focus->transform_export_value($key, $value);
				if (strstr($value, '::::')) {
					$exp = explode('::::', $value);
					$value = $exp[1];
				}
				array_push($new_arr, preg_replace('/\'/','\'\'',$value));
			}
		}

		$contador = count($columFinal);
		for ($j=0; $j<$contador; $j++) {
	    	$objPHPExcel->setActiveSheetIndex($activa)->setCellValue($columFinal[$j].$i, $new_arr[$j]);
	    }
		$i++;
	}
}

// Rename worksheet
$objPHPExcel->getActiveSheet()->setTitle($type);
// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex($activa);
// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header("Content-Disposition: attachment;filename=$moduleName.xls");
header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');
// If you're serving to IE over SSL, then the following may be needed
header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header ('Pragma: public'); // HTTP/1.0

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');

/**
 * This class will provide utility functions to process the export data.
 * this is to make sure that the data is sanitized before sending for export
 */
class ExportUtils {

	public $fieldsArr = array();
	public $picklistValues = array();

	// @codingStandardsIgnoreStart
	public function ExportUtils($module, $fields_array) {
		// @codingStandardsIgnoreEnd
		self::__init($module, $fields_array);
	}

	// @codingStandardsIgnoreStart
	public function __init($module, $fields_array) {
		// @codingStandardsIgnoreEnd
		$infoArr = self::getInformationArray($module);
		
		//attach extra fields related information to the fields_array; this will be useful for processing the export data
		foreach ($infoArr as $fieldname => $fieldinfo) {
			if (in_array($fieldinfo['fieldlabel'], $fields_array)) {
				$this->fieldsArr[$fieldname] = $fieldinfo;
			}
		}
	}

	public function sanitizeValues($arr) {
		global $current_user, $adb;
		$roleid = fetchUserRole($current_user->id);
		
		foreach ($arr as $fieldlabel => &$value) {
			$fieldInfo = $this->fieldsArr[$fieldlabel];
			
			$uitype = $fieldInfo['uitype'];
			$fieldname = $fieldInfo['fieldname'];
			if ($uitype == 15 || $uitype == 16 || $uitype == 33) {
				//picklists
				if (empty($this->picklistValues[$fieldname])) {
					$this->picklistValues[$fieldname] = getAssignedPicklistValues($fieldname, $roleid, $adb);
				}
				$value = trim($value);
			} else if ($uitype == 10 || $uitype == 404) {
				//have to handle uitype 10
				$value = trim($value);
				if (!empty($value)) {
					$parent_module = getSalesEntityType($value);
					$displayValueArray = getEntityName($parent_module, $value);
					if (!empty($displayValueArray)) {
						foreach ($displayValueArray as $v) {
							$displayValue = $v;
						}
					}
					if (!empty($parent_module) && !empty($displayValue)) {
						$value = $parent_module.'::::'.$displayValue;
					} else {
						$value = '';
					}
				} else {
					$value = '';
				}
			}
		}
		return $arr;
	}
	
	/**
	 * This function takes in a module name and returns the field information for it
	 *
	 * @param $module
	 *
	 * @return array
	 */
	public function getInformationArray($module) {
		require_once 'include/utils/utils.php';
		global $adb;
		$tabid = getTabid($module);
		
		$result = $adb->pquery('SELECT * FROM vtiger_field WHERE tabid=?', array($tabid));
		$count = $adb->num_rows($result);
		$arr = array();
		$data = array();
		
		for ($i=0; $i<$count; $i++) {
			$arr['uitype'] = $adb->query_result($result, $i, 'uitype');
			$arr['fieldname'] = $adb->query_result($result, $i, 'fieldname');
			$arr['columnname'] = $adb->query_result($result, $i, 'columnname');
			$arr['tablename'] = $adb->query_result($result, $i, 'tablename');
			$arr['fieldlabel'] = $adb->query_result($result, $i, 'fieldlabel');
			$fieldlabel = strtolower($arr['fieldlabel']);
			$data[$fieldlabel] = $arr;
		}
		return $data;
	}

}
