<?php
/*[ TT11362 ] Corregir Facturas en "Mi Cuenta en Platzilla" - Se modifica ara que trabaje con el modulo myinvoice*/
require_once('Smarty_setup.php');
require_once('include/ListView/ListView.php');
require_once('modules/CustomView/CustomView.php');
require_once('include/DatabaseUtil.php');



/*
function getInvoiceCustomer() {
	global $app_strings, $mod_strings, $current_language, $currentModule, $theme, $adb;
	global $list_max_entries_per_page,$clientView,$platPrincipal,$current_user;

	$contact_id = $_SESSION['customerid'];

	$currentModule = 'myinvoice';
	$adb = conectaPlataformaHija($platPrincipal);
	$customView = new CustomView('myinvoice');
	if (isset($_REQUEST['viewname']) && !empty($_REQUEST['viewname']))
		$viewid = $_REQUEST['viewname'];
	else
		$viewid = $customView->getViewId('myinvoice');

	$queryGenerator = new QueryGenerator('myinvoice', $current_user);
	$fields = "vtiger_myinvoice.invoice_no,vtiger_myinvoice.duedate,vtiger_myinvoice.description,vtiger_myinvoice.total,vtiger_crmentity.crmid";
	$query = "SELECT ".$fields." FROM vtiger_myinvoice
						INNER JOIN vtiger_crmentity ON vtiger_myinvoice.myinvoiceid = vtiger_crmentity.crmid
							WHERE vtiger_crmentity.deleted=0 AND vtiger_myinvoice.account_id = ".$contact_id;
	if ($viewid != "0") {
		$queryGenerator->initForCustomViewById($viewid);
	} else {
		$queryGenerator->initForDefaultCustomView();
	}

	$controller = new ListViewController($adb, $current_user, $queryGenerator);

	$list_query = $queryGenerator->getQuery();
	$where = $queryGenerator->getConditionalWhere();
	$start = ListViewSession::getRequestCurrentPage($currentModule, $list_query, $viewid, $queryMode);
	$limit_start_rec = ($start-1) * $list_max_entries_per_page;
	$focus = new myinvoice();
	$focus->initSortbyField($currentModule);
	$list_result = $adb->pquery($query . " LIMIT $limit_start_rec, $list_max_entries_per_page", array());
	//echo $query. " LIMIT $limit_start_rec, $list_max_entries_per_page ";

	$field_num_rows = $adb->num_rows($list_result);


	//$listview_header = $controller->getListViewHeader($focus,'myinvoice',$url_string,$sorder,$order_by,true);
	//$listview_entries = $controller->getListViewEntries($focus,'myinvoice',$list_result,$navigation_array,true);

	$listview_header = array("Nº Factura","Fecha de pago","Descripción","Total","Factura PDF");
	$listview_entries = array();
	for($j=0;$j< $field_num_rows;$j ++)
	{
		//$invoicedate = $adb->query_result($list_result,$j,'invoicedate');
		$invoice_no = $adb->query_result($list_result,$j,'invoice_no');
		$duedate = $adb->query_result($list_result,$j,'duedate');
		$description = $adb->query_result($list_result,$j,'description');
		$total = $adb->query_result($list_result,$j,'total');
		$record = $adb->query_result($list_result,$j,'crmid');

		$listview_entries[$j] = array("invoice_no"=>$invoice_no,
								   "duedate"=>$duedate,
								   "description"=>$description,
								   "total"=>$total,
								   "record"=>$record,
								   );
	}

	$smartyInv = new vtigerCRM_Smarty();


	$smartyInv->assign('LISTHEADER', $listview_header);
	$smartyInv->assign('LISTENTITY', $listview_entries);

	//return $smartyInv->fetch("ListViewEntries.tpl");
	return $smartyInv->fetch("Home/invoiceCustomer.tpl");
}

*/

function getInvoiceCustomer() {
	global $app_strings, $mod_strings, $current_language, $currentModule, $theme;
	global $list_max_entries_per_page,$clientView,$platPrincipal,$current_user;

	$contact_id = $_SESSION['customerid'];

	$currentModule = 'myinvoice';
	$adbPrincipal = conectaPlataformaHija($platPrincipal);

	$fields = "vtiger_myinvoice.invoice_no,vtiger_myinvoice.duedate,vtiger_myinvoice.description,vtiger_myinvoice.total,vtiger_crmentity.crmid";
	$query = "SELECT ".$fields." FROM vtiger_myinvoice
						INNER JOIN vtiger_crmentity ON vtiger_myinvoice.myinvoiceid = vtiger_crmentity.crmid
							WHERE vtiger_crmentity.deleted=0 AND vtiger_myinvoice.account_id = ".$contact_id;


	$list_result = $adbPrincipal->pquery($query, array());
	//echo $query. " LIMIT $limit_start_rec, $list_max_entries_per_page ";

	$field_num_rows = $adbPrincipal->num_rows($list_result);

	$listview_header = array("Nº Factura","Fecha de pago","Descripción","Total","Factura PDF");
	$listview_entries = array();
	for($j=0;$j< $field_num_rows;$j ++)
	{
		//$invoicedate = $adb->query_result($list_result,$j,'invoicedate');
		$invoice_no = $adbPrincipal->query_result($list_result,$j,'invoice_no');
		$duedate = $adbPrincipal->query_result($list_result,$j,'duedate');
		$description = $adbPrincipal->query_result($list_result,$j,'description');
		$total = $adbPrincipal->query_result($list_result,$j,'total');
		$record = $adbPrincipal->query_result($list_result,$j,'crmid');

		$listview_entries[$j] = array("invoice_no"=>$invoice_no,
								   "duedate"=>$duedate,
								   "description"=>$description,
								   "total"=>$total,
								   "record"=>$record,
								   );
	}

	$smartyInv = new vtigerCRM_Smarty();


	$smartyInv->assign('LISTHEADER', $listview_header);
	$smartyInv->assign('LISTENTITY', $listview_entries);

	//return $smartyInv->fetch("ListViewEntries.tpl");
	return $smartyInv->fetch("Home/invoiceCustomer.tpl");
}


?>