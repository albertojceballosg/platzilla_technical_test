<?php
@include("../PortalConfig.php");
if(!isset($_SESSION['customer_id']) || $_SESSION['customer_id'] == '')
{
	@header("Location: $Authenticate_Path/login.php");
	exit;
}
setPortalCurrentLanguage();
$default_language = getPortalCurrentLanguage();
require_once("language/$default_language.lang.php");
?>

<!-- BEGIN ServiceContracts/index.php -->
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
		$block = "ServiceContracts";
		include("ServiceContractsDetail.php");
	} else {
		include("ServiceContractsList.php");
	}
	

?>
			</table>
</td></tr>
</table>
<!-- END ServiceContracts/index.php -->
	