<?php
	global $result;
	global $client;

	$params = array();
	
	echo '<!-- BEGIN ServiceContracts/ServiceContractsList.php -->';	
	echo '<tr><td><span class="lvtHeaderText">'.getTranslatedString("LBL_SERVICE_CONTRACTS").'</span</td>';    

	echo '<tr><td colspan="2"><hr noshade="noshade" size="1" width="100%" align="left">'.
			 '<table width="95%"  border="0" cellspacing="0" cellpadding="5" align="center">';
	    					
if ($customerid != '' )  {
	$block = "ServiceContracts";
	$params = array('id' => "$customerid", 'block'=>"$block",'sessionid'=>$sessionid,'onlymine'=>'false');
	$result = $client->call('get_list_values', $params, $Server_Path, $Server_Path);
	echo getblock_fieldlistview($result,$block);
}

	echo '</table></td></tr>';
	echo '<!-- END ServiceContracts/ServiceContractsList.php -->';
?>