<?php
@include("../PortalConfig.php");
if(!isset($_SESSION['customer_id']) || $_SESSION['customer_id'] == '')
{
	@header("Location: $Authenticate_Path/login.php");
	exit;
}
?>

<!-- BEGIN Invoice/index.php -->
<script language="JavaScript" src="js/general.js"></script>
<table class="dvtContentSpace" border="0" cellpadding="0" cellspacing="0" width="100%">
<tr><td align="left">
		<table border="0" cellpadding="0" cellspacing="0" width="100%">
		  <form name="index" method="post" action="index.php">
		  <input type="hidden" name="module">
		  <input type="hidden" name="action">
		  <input type="hidden" name="fun">
<?php

	global $result;
	$customerid = $_SESSION['customer_id'];
	$sessionid = $_SESSION['customer_sessionid'];

	if($_REQUEST['id'] != '') {
		$id=$_REQUEST['id'];
		$status =$_REQUEST['status'];
		$block = "Invoice";
		if($status != true)	{
			$params = array('id' => "$id", 'block'=>"$block", 'contactid'=>"$customerid",'sessionid'=>"$sessionid");
			$filecontent = $client->call('get_pdf', $params, $Server_Path, $Server_Path);
			
			if($filecontent != 'failure') {
				$filename=$Server_Path."/test/product/".$id."_Invoice.pdf";
				header("Content-type: text/pdf");
				header("Cache-Control: private");
				header("Content-Disposition: attachment; filename=$filename");
				header("Content-Description: PHP Generated Data");
				echo base64_decode($filecontent);
				exit;
			}  else {
				echo getTranslatedString('LBL_PDF_CANNOT_GENERATE');   //We have to show the error message like "PDF output cannot be generated. Please contact admin"
			}
		} 	else {
				include("InvoiceDetail.php");
		}	
	} else {
		include("InvoiceList.php");
	}
	

?>
			</table>
</td></tr>
</table>
<!-- END Invoice/index.php -->
	