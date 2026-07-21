<?php 
 
    $idcaso=$_REQUEST['numero'];
    $nummen=$_REQUEST['nummen'];
$output='	';
 
echo($output);
 
  $conex= mysql_connect('127.0.0.1:3306','timeuser','Eceptu.2011',true) or die("Error al intentar establecer la conexiÃ³n.");
    $dbp = mysql_select_db('plat_gestiontime2',$conex);
    $sql="SELECT first_name, last_name, subject, sent_body, sent_date, respuesta, fecha_resp, tmm_notif_resp.check as check2, accountid, 	to_contactid   FROM `tmm_notifications` inner join vtiger_crmentity on (vtiger_crmentity.crmid = tmm_notifications.notificationid and vtiger_crmentity.deleted = 0 )
inner join vtiger_users on (vtiger_users.id = tmm_notifications.userid ) 
left join tmm_notif_resp on  ( tmm_notif_resp.id_notificacion = tmm_notifications.notificationid)
WHERE  tmm_notifications.notificationid='".$idcaso."'";  
//WHERE `enviadopor` LIKE 'cliente' and tmm_notifications.notificationid='".$idcaso."'   and tmm_notif_resp.id_respuesta = '".$nummen."'";  

   
	      $query=mysql_query($sql,$conex);
	      while ($row = mysql_fetch_array($query, MYSQL_NUM)) {
 
     	$vtiger_user = $row[0];
		$vtiger_user.= ' '; 		
		$vtiger_user.=  $row[1];
		$subjet =  $row[2];
		$body = $row[3];
		$fecha =  $row[4];
		$resp =  $row[5];
		$fec_resp =  $row[6];
		$check =  $row[7];
		$cuenta =  $row[8];
		$user =  $row[9];}
		
		if($check !=1) {
   $sql="UPDATE `tmm_notif_resp` SET `check` = '1' WHERE `tmm_notif_resp`.`id_notificacion` ='".$idcaso."'

";    
	      $query=mysql_query($sql,$conex);			
 
			
			}
?>
 
<form name="index" method="POST" action="nuevomail.php">
	<input type="hidden" name="module" value="Notifications">
	<input type="hidden" name="action" value="index">
	<input type="hidden" name="fun" value="savenot">
	<table  cellpadding="5" cellspacing="0" width="100%" border="0" align="center" class="dvtContentSpace">
<tr><td colspan="2" background="bg_folder_title.gif" align="left " nowrap="" height="21" style="font-family: Arial,Helvetica,sans-serif; font-size: 12px;  "><span style="white-space: nowrap;"> 
<a style="text-decoration:none" target="right" ><font color="#000000" style="font-family: Verdana,Arial,Helvetica,sans-serif; font-size: 12px;" >  <b>Respuesta:</b>  </font></a></span> 
<br>
         <small>&nbsp; 
 

</small></td>
</tr>
 

	<tr>
		<td style="font-family: Verdana,Arial,Helvetica,sans-serif;
    font-size: 12px;"  align="left"><b>Asunto:</b>&nbsp; &nbsp;
              <?php echo($subjet); ?> </td><td></td>
	</tr>
	<tr>
		<td style="font-family: Verdana,Arial,Helvetica,sans-serif; font-size: 12px;"    align="left"><b>De: &nbsp;&nbsp;&nbsp;  &nbsp; &nbsp;  &nbsp;</b>Soporte </td>
              <td class="dvtCellInfo"  >
 
	      </td>
	</tr>
	<tr>
		<!--<td style="font-family: Verdana,Arial,Helvetica,sans-serif;
    font-size: 12px;"   align="left"><b>Mensaje:</b></td>--></tr>
			</table><table>
			<tr>
<td style="font-family: Verdana,Arial,Helvetica,sans-serif;
    font-size: 12px;"   align="left"><b>Mensaje:</b></td>
              <td colspan="4" style="font-family: Verdana,Arial,Helvetica,sans-serif;
    font-size: 12px;"   ><textarea readonly="readonly" name="mensaje" wrap="hard" cols="135" rows="8"  style="width: 1007px; height: 334px;" wrap="OFF" ><?php echo($resp); ?> 
 __________________________________________________________________________________________________________________________  
   <?php echo($body); ?>
</textarea></td>
	</tr>
			 
<tr><td></td><td align="center"  background="bg_folder_title.gif" colspan="4">
<input type="hidden" value="<?php echo($user); ?>" id="user" name="user">
<input type="hidden" value="<?php echo($cuenta); ?>" id="cuenta" name="cuenta">
<input type="hidden" value="<?php echo($idcaso); ?>" id="caso" name="caso">
<a style="text-decoration:none" target="right" href="nuevomail.php?user=<?php echo($user); ?>&cuenta=<?php echo($cuenta); ?>&caso=<?php echo($idcaso); ?>">
<input type="button" value="Responder" name="responder" class="crmbutton small cancel" onClick="parent.location='nuevomail.php?user=<?php echo($user); ?>&cuenta=<?php echo($cuenta); ?>'" >
</a></td></tr> 
	</table>
	</form>