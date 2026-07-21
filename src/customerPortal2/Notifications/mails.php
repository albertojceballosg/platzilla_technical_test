<?php 
	@include("../include.php");
	@include("../version.php");
	@include("../PortalConfig.php");
	include_once('../include/utils/utils.php');
	global $default_language;
	setPortalCurrentLanguage();
	$default_language = getPortalCurrentLanguage();
	require_once("../language/".$default_language.".lang.php");
	
	session_start();
	
	global $result;
	global $client;
  
	$user = $_REQUEST['user'];
	$customerid=$_REQUEST['user'];
	$block='Notifications#';
	$sessionid='q6jixnp1';
  	//$params = array('id' => "$customerid", 'block'=>"$block",'sessionid'=>$sessionid,'onlymine'=>false);
	//$result = $client->call('get_list_values', $params, $Server_Path, $Server_Path);
	//var_dump($result);
	//echo getblock_fieldlistview($result,$block);



	$conex= mysql_connect('127.0.0.1:3306','timeuser','Eceptu.2011',true) or die("Error al intentar establecer la conexiÃ³n.");
    $dbp = mysql_select_db('plat_gestiontime2',$conex);
    mysql_query("SET NAMES 'utf8'");
   /* $sql="SELECT first_name, last_name, subject, sent_body, DATE_FORMAT(sent_date,'%d/%m/%Y') as sent_date, respuesta, DATE_FORMAT(fecha_resp,'%d/%m/%Y') as fecha_resp,  tmm_notifications.notificationid as numero, tmm_notif_resp.check as check2 , id_respuesta, fecha_resp as acomoda 
		FROM `tmm_notifications` inner join vtiger_crmentity on (vtiger_crmentity.crmid = tmm_notifications.notificationid and vtiger_crmentity.deleted = 0 )
		inner join vtiger_users on (vtiger_users.id = tmm_notifications.userid ) 
		inner join tmm_notif_resp on  ( tmm_notif_resp.id_notificacion = tmm_notifications.notificationid)
		WHERE `to_contactid` LIKE '".$user."' AND `enviadopor` LIKE 'cliente' and id_contact 	 not like '".$user."'";
	if(isset($_REQUEST['noleido'])){
	$sql.=" and tmm_notif_resp.check is null";
	}*/
	$sql = "SELECT first_name, last_name, subject, sent_body, DATE_FORMAT( sent_date, '%d/%m/%Y' ) AS sent_date, tmm_notifications.notificationid AS numero
		FROM `tmm_notifications`
		INNER JOIN vtiger_crmentity ON ( vtiger_crmentity.crmid = tmm_notifications.notificationid
		AND vtiger_crmentity.deleted =0 )
		INNER JOIN vtiger_users ON ( vtiger_users.id = tmm_notifications.userid )
		WHERE `to_contactid` LIKE '%".$user."%'";
  
	$groupde=0;
	$groupasun =0;
	$groufecha =0;
	/*
	if(isset($_REQUEST['groupde'])){
		$groupde=$_REQUEST['groupde'];

	$sql.=" ORDER BY  first_name  "; if( $groupde <0){ $sql.=" DESC"; }
} elseif(isset($_REQUEST['groupasun'])){
	$groupasun =$_REQUEST['groupasun'];

$sql.=" ORDER BY  subject  "; if( $groupasun <0){ $sql.=" DESC"; }
} elseif(isset($_REQUEST['groufecha'])){
	$groufecha = $_REQUEST['groufecha'];
$sql.=" ORDER BY acomoda  "; if( $groufecha <0){ $sql.=" DESC"; }
} else {$sql.=" ORDER BY acomoda  DESC"; $groufecha = -1;}*/

		$query=mysql_query($sql,$conex);
?>
 


<td align="left">
			<table width="100%" cellspacing="0" cellpadding="0" border="0">
			
			
			
			


 

<tbody><tr><td height="5" class="toggle_table"></td></tr><tr><td></td></tr><tr>

<td align="left">
<table width="100%" cellspacing="0" cellpadding="1" border="0">
<tbody><tr><td nowrap="" align="left " background="bg_folder_title.gif" align="center" height="26" style="font-family: Arial,Helvetica,sans-serif; font-size: 12px;  "><span style="white-space: nowrap;"> 
<a href="nuevomail.php?user=<?php echo($user);?>&cuenta=<?php echo($_REQUEST['cuenta']);?>&login_language=<?php echo $default_language;?>" target="right" style="text-decoration:none"><font color="#000000" style="    color: #000000;     font-family: Verdana,Arial,Helvetica,sans-serif;     font-size: 12px;     font-weight: normal;     text-decoration: none;"  > <img width="26" vspace="1" border="0" align="absbottom" hspace="1" height="26" src="http://images-2.findicons.com/files/icons/1730/smoothgnome/48/evolution_mail.png"><?php echo getTranslatedString('Nuevo')?></font></a></span> 

<!--          &nbsp; 
<a style="text-decoration:none;color:black" href="mails.php?user=<?php echo($user);?>&cuenta=<?php echo($_REQUEST['cuenta']);?>" style="text-decoration:none">&nbsp; &nbsp;Todos</a> &nbsp; &nbsp;&nbsp; &nbsp;|&nbsp; &nbsp;&nbsp; &nbsp;
<a style="text-decoration:none;color:black" href="mails.php?user=<?php echo($user);?>&cuenta=<?php echo($_REQUEST['cuenta']);?>&noleido=1" style="text-decoration:none">No Leidos</a>
-->

</td></tr><tr>
<td nowrap="" align="left " background="bg_folder_title.gif" align="center" height="20" style="font-family: Arial,Helvetica,sans-serif; font-size: 11px;  ">
           &nbsp; 
<a style="text-decoration:none;color:black" href="mails.php?user=<?php echo($user);?>&cuenta=<?php echo($_REQUEST['cuenta']);?>" style="text-decoration:none">&nbsp; &nbsp;Todos</a> &nbsp; &nbsp;&nbsp; &nbsp;|&nbsp; &nbsp;&nbsp; &nbsp;
<a style="text-decoration:none;color:black" href="mails.php?user=<?php echo($user);?>&cuenta=<?php echo($_REQUEST['cuenta']);?>&noleido=1" style="text-decoration:none"><?php echo getTranslatedString('No Leido')?></a>


</td>
</tr></tbody></table><table width="100%" cellspacing="0" cellpadding="1" border="0" bgcolor="#F1F0F0" class="mvfwdl">
<tbody><tr>
</tr><tr>

 

</td>
   </tr>
</tbody></table></td></tr></tbody>
         <tbody><tr><td>       <table width="100%" cellspacing="0" cellpadding="1" border="0" align="center" class="messages_table"><tbody><tr><td></td></tr><tr bgcolor="#808080" background="bg_folder_title.gif" align="center">
<td width="1" class="current_folder" style="font-family: Arial,Helvetica,sans-serif; font-size: 12px; "><div align="right"></div></td>
<td width="1" class="current_folder" style="font-family: Arial,Helvetica,sans-serif; font-size: 12px; "><div align="right"></div></td><td width="48%" align="left" class="current_folder" style="font-family: Arial,Helvetica,sans-serif; font-size: 12px; ">
<b><?php echo getTranslatedString('Asunto')?></b> <a style="text-decoration:none;color:black" href="mails.php?user=<?php echo($user);?>&cuenta=<?php echo($_REQUEST['cuenta']);?>&groupasun=<?php if($groupasun!=1){echo('1');}else{echo('-1');}?>"><img width="12" border="0" height="10" src="down_pointer.png" alt="sort"></a></td>
<td width="25%" align="left" class="current_folder" style="font-family: Arial,Helvetica,sans-serif; font-size: 12px; ">
<b><?php echo getTranslatedString('De')?></b> <a  style="text-decoration:none;color:black" href="mails.php?user=<?php echo($user);?>&cuenta=<?php echo($_REQUEST['cuenta']);?>&groupde=<?php if($groupde !=1){echo('1');}else{echo('-1');}?>"><img width="12" border="0" height="10" src="down_pointer.png" alt="sort"></a></td>
<td width="25%" nowrap="" align="center" class="current_folder" style="font-family: Arial,Helvetica,sans-serif; font-size: 12px; ">
<b><?php echo getTranslatedString('Fecha')?></b> <a style="text-decoration:none;color:black" href="mails.php?user=<?php echo($user);?>&cuenta=<?php echo($_REQUEST['cuenta']);?>&groufecha=<?php if($groufecha!=1){echo('1');}else{echo('-1');}?>"><img width="12" border="0" height="10" src="down_pointer.png" alt="sort"></a></td>

</tr>

<?php
$cont=0;
$color = '#F1F0F0';
while ($row = mysql_fetch_array($query, MYSQL_NUM)) {
  
     	$vtiger_user = $row[0];
		$vtiger_user.= ' '; 		
		$vtiger_user.=  $row[1];
		$subjet =  $row[2];
		$body = $row[3];
		$fecha =  $row[4];
		//$resp =  $row[5];
		//$fec_resp =  $row[6];
		$numero =  $row[5];
		//$check =  $row[8];
		$id_respuesta =  $row[9];
	 
	 if($color == '#F1F0F0'){ $color = '#FFFFFF';}else{ $color = '#F1F0F0';}
		if($check=='1'){
$registro='<tr valign="middle">
<td style="font-family: Arial,Helvetica,sans-serif; font-size: 12px;  " bgcolor="'.$color.'" align="center"><!--<input type="checkbox" name="msg['.$cont.']" value="103">--></td>
<td style="font-family: Arial,Helvetica,sans-serif; font-size: 12px;  " bgcolor="'.$color.'" align="right"><b><small><img width="6" border="0" height="10" src="transparent.gif"><img width="18" border="0" height="12" src="msg_read.gif" alt="This message is Read" title="This message is Read"></small></b>&nbsp;</td>
<td style="font-family: Arial,Helvetica,sans-serif; font-size: 12px;  " bgcolor="'.$color.'" align="left"><a style="text-decoration:none;color:black" href="leemensaje2.php?numero='.$numero.'&nummen='.$id_respuesta.'">'.$subjet.'</a></td>

<td style="font-family: Arial,Helvetica,sans-serif; font-size: 12px;  " bgcolor="'.$color.'" align="left" title="'.$vtiger_user.'">'.$vtiger_user.'</td>
<td style="font-family: Arial,Helvetica,sans-serif; font-size: 12px;  " bgcolor="'.$color.'" align="center">'.$fec_resp.'</td>
</tr>';			
			
			
			}else{ $registro='<tr valign="middle">

<td style="font-family: Arial,Helvetica,sans-serif; font-size: 12px; font-weight: bolder; text-decoration: none; " bgcolor="'.$color.'" align="center"><!--<input type="checkbox" name="msg['.$cont.']" value="103">--></td>
<td style="font-family: Arial,Helvetica,sans-serif; font-size: 12px; font-weight: bolder; text-decoration: none; " nowrap="" bgcolor="'.$color.'" align="right"><b><small><img width="6" border="0" height="10" src="transparent.gif"><img width="18" border="0" height="12" src="msg_new.gif" alt="This message is Read" title="This message is Read"></small></b>&nbsp;</td>
<td style="font-family: Arial,Helvetica,sans-serif; font-size: 12px; font-weight: bolder; text-decoration: none; " bgcolor="'.$color.'" align="left"><a style="text-decoration:none;color:black" href="leemensaje2.php?numero='.$numero.'&nummen='.$id_respuesta.'">'.$subjet.'</a></td>

<td style="font-family: Arial,Helvetica,sans-serif; font-size: 12px; font-weight: bolder; text-decoration: none; " bgcolor="'.$color.'" align="left" title="'.$vtiger_user.'">'.$vtiger_user.'</td>
<td style="font-family: Arial,Helvetica,sans-serif; font-size: 12px; font-weight: bolder; text-decoration: none; "  nowrap="" bgcolor="'.$color.'" align="center">'.$fec_resp.'</td>
</tr>'; }
echo($registro);
 		$cont++;
} ?>







 <!--
<tr valign="middle">

<td  style="font-family: Arial,Helvetica,sans-serif; font-size: 12px; font-weight: bolder; text-decoration: none; "  bgcolor="#F1F0F0" align="center"><input type="checkbox" name="msg[1]" value="104"></td>
<td  style="font-family: Arial,Helvetica,sans-serif; font-size: 12px; font-weight: bolder; text-decoration: none; " bgcolor="#F1F0F0" align="left" title="ltramontini@timemanagement.es">Lionel Tramontini</td>

<td  style="font-family: Arial,Helvetica,sans-serif; font-size: 12px; font-weight: bolder; text-decoration: none; " nowrap="" bgcolor="#F1F0F0" align="right"><b><small><img width="6" border="0" height="10" src="transparent.gif"><img width="18" border="0" height="12" src="msg_read.gif" alt="This message is Read" title="This message is Read"></small></b>&nbsp;</td>
<td  style="font-family: Arial,Helvetica,sans-serif; font-size: 12px; font-weight: bolder; text-decoration: none; " bgcolor="#F1F0F0" align="left"><a href="index.php?module=Notifications&action=index&id=18993">Prueba</a></td>
<td  style="font-family: Arial,Helvetica,sans-serif; font-size: 12px; font-weight: bolder; text-decoration: none; " nowrap="" bgcolor="#F1F0F0" align="center">2011-09-26</td>
</tr>
 -->
<tr valign="middle">

</tbody></table></td></tr></tbody><tbody><tr><td><table width="100%" cellspacing="0" cellpadding="5" border="0" bgcolor="#F1F0F0" class="toggle_table"><tbody> 
</tbody></table>
</td>
</tr>
</tbody>



</table>
</td>