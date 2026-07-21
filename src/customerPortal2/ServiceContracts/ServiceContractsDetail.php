<?php
global $result;
global $client;
global $Server_Path;

$customerid = $_SESSION['customer_id'];
$sessionid = $_SESSION['customer_sessionid'];
echo '<!-- BEGIN ServiceContracts/ServiceContractsDetail.php -->';	
if($id != '')
{
	$params = array('id' => "$id", 'block'=>"$block", 'contactid'=>"$customerid",'sessionid'=>"$sessionid");
	$result = $client->call('get_servicecontracts_detail', $params, $Server_Path, $Server_Path);
	if (count($result) == 1 && $result[0] == "#NOT AUTHORIZED#") {
		echo '<tr><td colspan="6" align="center"><b>'.getTranslatedString('LBL_NOT_AUTHORISED').'</b></td></tr>';
		die();
	}
	$invinfo0 = $result[0][$block];
	$invinfo1 = $result[1][$block];
	$invinfo2 = $result[2][$block];

	// var_dump($invinfo);die;
	
	echo '<tr><td><input class="crmbutton small cancel" type="button" value="'.getTranslatedString('LBL_BACK_BUTTON').'" onclick="window.history.back();"/></td></tr>';

	echo getblock_fieldlist($invinfo0);

	echo getblock_fieldlist($invinfo1);

	echo getblock_fieldlist($invinfo2);
	
}
echo '<!-- END ServiceContracts/ServiceContractsDetail.php -->';	
?>
