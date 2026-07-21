<?php
  global $result;
  global $client;

  echo '<tr><td><span class="lvtHeaderText">'.getTranslatedString("LBL_NOTIFICATIONS").'</span></td></tr>';
  echo '<tr><td align="right">
  <input class="crmbutton small cancel" type="button" onclick="document.location=\'index.php?module=Notifications&action=index&fun=newnot\'" value="Nueva notificacion" name="newnotificacion"></form>
  <input class="crmbutton small cancel" name="srch" type="button" value="Buscar" onClick="showSearchFormNow(\'tabSrch\',\'Notifications\');"> </td></tr>';
  echo '<tr><td colspan="2"><hr noshade="noshade" size="1" width="100%" align="left">'.
		'<table width="95%"  border="0" cellspacing="0" cellpadding="5" align="center">';
  echo '<tr><td class="mnu">Notificaciones Nuevas/Pendientes</td></tr>';
 $usuario=$_SESSION["customer_id"];
  $block2=$block.'#'.$usuario;
  if ($customerid != '' ) {
	  $params = array('id' => "$customerid", 'block'=>"$block2",'sessionid'=>$sessionid,'onlymine'=>false);
	  $result = $client->call('get_list_values', $params, $Server_Path, $Server_Path);
	 //var_dump($result);exit;
	  echo getblock_fieldlistview($result,$block);
	  
  }
  echo '<tr><td class="mnu">Notificaciones Respondidas/Cerradas</td></tr>';		  
  if ($customerid != '' ) {
	  $params = array('id' => "$customerid", 'block'=>"$block",'sessionid'=>$sessionid,'onlymine'=>false);
	  $result = $client->call('get_list_values', $params, $Server_Path, $Server_Path);
	  echo getblock_fieldlistview($result,$block);
  }

echo '</table></td></tr>';

?>
