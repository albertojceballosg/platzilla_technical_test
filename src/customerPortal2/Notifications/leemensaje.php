<?php 
 //VAR_DUMP($_REQUEST);
 
    $idcaso=$_REQUEST['numero'];
 
$output='	';
 
 
  $conex= mysql_connect((getenv('NOTIF_DB_HOST')?:'127.0.0.1:3306'),(getenv('NOTIF_DB_USER')?:'timeuser'),getenv('NOTIF_DB_PASSWORD'),true) or die("Error al intentar establecer la conexiÃ³n.");
    $dbp = mysql_select_db('plat_gestiontime2',$conex);
    $sql="SELECT first_name, last_name, subject, sent_body, sent_date FROM `tmm_notifications` inner join vtiger_crmentity on (vtiger_crmentity.crmid = tmm_notifications.notificationid and vtiger_crmentity.deleted = 0 )
inner join vtiger_users on (vtiger_users.id = tmm_notifications.userid ) 
WHERE `enviadopor` LIKE 'cliente' and tmm_notifications.notificationid='".$idcaso."'"; 
 
	      $query=mysql_query($sql,$conex);
	      while ($row = mysql_fetch_array($query, MYSQL_NUM)) {
 
     	$vtiger_user = $row[0];
		$vtiger_user.= ' '; 		
		$vtiger_user.=  $row[1];
		$subjet =  $row[2];
		$body = $row[3];
		$fecha =  $row[4];}
?>
 
<form name="index" method="POST" action="nuevomail.php">
	<input type="hidden" name="module" value="Notifications">
	<input type="hidden" name="action" value="index">
	<input type="hidden" name="fun" value="savenot">
	<table  cellpadding="5" cellspacing="0" width="100%" border="0" align="center" class="dvtContentSpace">
<tr><td colspan="2" background="bg_folder_title.gif" align="left " nowrap="" height="21" style="font-family: Arial,Helvetica,sans-serif; font-size: 12px;  "><span style="white-space: nowrap;"> 
<a style="text-decoration:none" target="right" ><font color="#000000"> 
<b>Mensaje enviado:</b>
  </font></a></span> 
<br>
         <small>&nbsp; 
 

</small></td>
</tr>


	<tr>
		<td style="font-family: Verdana,Arial,Helvetica,sans-serif;
    font-size: 12px;"   align="left"><b>Asunto:&nbsp;&nbsp;&nbsp;  &nbsp; &nbsp;  &nbsp;</b><?php echo($subjet); ?></td>
              <td class="dvtCellInfo"  ></td>
	</tr>
	<tr>
		<td style="font-family: Verdana,Arial,Helvetica,sans-serif;
    font-size: 12px;"     align="left"><b>Dirigido A:  &nbsp; </b>Soporte</td>
              <td class="dvtCellInfo"  >
 
	      </td>
	</tr>
	</table>
	<table>
	<!--<tr>
		<td style="font-family: Verdana,Arial,Helvetica,sans-serif;
    font-size: 12px;"    align="left"><b>Mensaje:</b></td></tr>-->
				<tr>
 <td style="font-family: Verdana,Arial,Helvetica,sans-serif;
    font-size: 12px;"    align="left"><b>Mensaje:</b></td>
              <td colspan="4" style="  "    ><textarea readonly="readonly" name="mensaje" wrap="hard" cols="135" rows="8" style="font-family: Verdana,Arial,Helvetica,sans-serif;
    font-size: 12px; width: 1007px; height: 334px;"   wrap="OFF" >    <?php echo($body); ?></textarea></td>
	</tr>
 </tr>
<tr><td></td><td background="bg_folder_title.gif" align="left " nowrap="" height="21" style="font-family: Arial,Helvetica,sans-serif; font-size: 12px;  " colspan="2"></td>
</tr>
	</table>
	</form>