<?php
@include("../PortalConfig.php");
if(!isset($_SESSION['customer_id']) || $_SESSION['customer_id'] == '')
{
	@header("Location: $Authenticate_Path/login.php");
	exit;
}
?>

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
		global $client;
		$sessionid = $_SESSION['customer_sessionid'];
		$customerid = $_SESSION['customer_id'];
		$servicetasks_id =$_REQUEST['id'];
		$block= 'ServiceTasks';

		echo '<tr><td><span class="lvtHeaderText">'.getTranslatedString("LBL_SERVICE_TASKS").'</span></td></tr>';
		echo '<tr><td colspan="2"><hr noshade="noshade" size="1" width="100%" align="left">'.
			      '<table width="95%"  border="0" cellspacing="0" cellpadding="5" align="center">';
				  
		if ($customerid != '' ) {
			$params = array('id' => "$customerid", 'block'=>"$block",'sessionid'=>$sessionid,'onlymine'=>false);
			$result = $client->call('get_list_values', $params, $Server_Path, $Server_Path);
			echo getblock_fieldlistview($result,$block);
		}

		echo '</table></td></tr>';
							
?>
			</table>
</td></tr>
</table>

