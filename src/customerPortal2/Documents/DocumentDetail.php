<?php
global $result;
global $client;
global $Server_Path;

$customerid = $_SESSION['customer_id'];
$sessionid = $_SESSION['customer_sessionid'];
echo '<!-- BEGIN documents/DocumentsDetail.php -->';
if($note_id != '')
{

	$params = array('id' => "$note_id", 'block'=>"$block",'contactid'=>$customerid,'sessionid'=>"$sessionid");
	$result = $client->call('get_details', $params, $Server_Path, $Server_Path);
	if (count($result) == 1 && $result[0] == "#NOT AUTHORIZED#") {
		echo '<tr><td colspan="6" align="center"><b>'.getTranslatedString('LBL_NOT_AUTHORISED').'</b></td></tr>';
		die();
	}
	$noteinfo = $result[0][$block];
	
    echo '<tr><td><input class="crmbutton small cancel" type="button" value="'.getTranslatedString('LBL_BACK_BUTTON').'" onclick="window.history.back();"/></td></tr>';
	echo getblock_fieldlist($noteinfo);
	
}
echo '<!-- END documents/DocumentsDetail.php -->';
?>
