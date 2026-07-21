<?php 
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
date_default_timezone_set('UTC');


/** Include PHPExcel */
require_once('modules/Import/PHPExcel-1.8/Classes/PHPExcel.php');
// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set document properties
$objPHPExcel->getProperties()->setCreator("TimeManagement_")
							 ->setLastModifiedBy("TimeManagement_")
							 ->setTitle("Office 2007 XLSX Test Document")
							 ->setSubject("Office 2007 XLSX Test Document")
							 ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
							 ->setKeywords("office 2007 openxml php")
							 ->setCategory("Test result file");

$module1 = $_REQUEST['activemod'];
$type = $_REQUEST['typeImport'];

$arr_val = GenerateReport($module1, $type);

if($arr_val){

	$activa = 0;
	$lastColumn = $objPHPExcel->getActiveSheet()->getHighestColumn();
	$head = 1;
	$lastColumn++;
	$i=0;
	for ($column = 'A'; $column != $lastColumn; $column++) {
    	$objPHPExcel->setActiveSheetIndex($activa)->setCellValue($column.$head, $arr_val[$i]);
    	$lastColumn++;
    	$i++;
    	if($i>=count($arr_val)){
    		break;

    	}
    }

}

// Rename worksheet
$objPHPExcel->getActiveSheet()->setTitle('Projects');


// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex($activa);

// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="template.xls"');
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

//echo 'SUCESS';









function GenerateReport($moduleActive,$type)
{
global $adb,$current_user,$php_max_execution_time;
	global $modules,$app_strings;
	global $mod_strings,$current_language,$currentModule;
	require('user_privileges/user_privileges_'.$current_user->id.'.php');
	
	$modules_selected = array();
	$modules_selected[0] = $currentModule;
	$module = $currentModule;

	// Update Reference fields list list
	$sql = "SELECT vtiger_field.fieldlabel, vtiger_field.fieldname, vtiger_field.columnname,  vtiger_field.tablename, vtiger_field.uitype
									from vtiger_field 
									inner join vtiger_blocks on vtiger_blocks.blockid = vtiger_field.block 
									WHERE vtiger_field.tabid = (select tabid from vtiger_tab where name='".$moduleActive."') 
									and vtiger_field.presence = 0 and vtiger_blocks.visible = 0 and vtiger_field.columnname <>'createdtime' ";

	if(isset($type) && $type=='standar'){
		$sql .= "and vtiger_blocks.blockid = '160'";
	}
	$result = $adb->pquery($sql, array());
	if($result)
	{
		$arraylists = Array();
		$noofrows = $adb->num_rows($result);
		for ($i=0; $i<$noofrows; $i++)
		{
			$arraylists[$i] = getTranslatedString($adb->query_result($result, $i, 'fieldlabel'), $moduleActive);
		}


	}
		return $arraylists;
}













?>