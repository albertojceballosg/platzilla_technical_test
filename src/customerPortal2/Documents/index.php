<?php
@include("../PortalConfig.php");
if(!isset($_SESSION['customer_id']) || $_SESSION['customer_id'] == '')
{
	@header("Location: $Authenticate_Path/login.php");
	exit;
}
?>

<!-- BEGIN documents/index.php -->
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
		$sessionid = $_SESSION['customer_sessionid'];
		$customerid = $_SESSION['customer_id'];
		$note_id =$_REQUEST['id'];
		$block= 'Documents';

		if( $note_id == '')	include("DocumentsList.php");
							else		include("DocumentDetail.php");
							
?>
			</table>
</td></tr>
</table>
<!-- END documents/index.php -->

