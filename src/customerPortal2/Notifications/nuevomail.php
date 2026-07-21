<?php
	@include("../include.php");
	@include("../version.php");
	@include("../PortalConfig.php");
	include_once('../include/utils/utils.php');
	global $default_language;
	setPortalCurrentLanguage();
	$default_language = getPortalCurrentLanguage();
	require_once("../language/".$default_language.".lang.php");
	
$output='	';
$conex= mysql_connect('127.0.0.1:3306','timeuser','Eceptu.2011',true) or die("Error al intentar establecer la conexiÃ³n.");
    $dbp = mysql_select_db('plat_gestiontime2',$conex);
echo($output);

if(isset($_REQUEST['caso'])){
    $sql="SELECT first_name, last_name, subject, sent_body, sent_date, respuesta, fecha_resp, tmm_notif_resp.check as check2, accountid, 	to_contactid   FROM `tmm_notifications` inner join vtiger_crmentity on (vtiger_crmentity.crmid = tmm_notifications.notificationid and vtiger_crmentity.deleted = 0 )
inner join vtiger_users on (vtiger_users.id = tmm_notifications.userid ) 
inner join tmm_notif_resp on  ( tmm_notif_resp.id_notificacion = tmm_notifications.notificationid)
WHERE `enviadopor` LIKE 'cliente' and tmm_notifications.notificationid='".$_REQUEST['caso']."'

";     
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
}


if($_REQUEST['fun']=='savenot'){
    
    $userid=$_REQUEST['usuario'];
    
    if(isset($_REQUEST['caso'])){
     
    $id=$_REQUEST['caso'];
    
    $id_contact=$_SESSION['customer_id'];
    $sq='SELECT MAX(id_respuesta) AS id_respuesta FROM tmm_notif_resp WHERE id_notificacion='.$id;
    $query=mysql_query($sq,$conex);
    $cant=mysql_num_rows($query);
    $r=mysql_fetch_assoc($query);
    extract($r);

    if($cant>0 and $id_respuesta!=NULL){
      $idmensaje=$id_respuesta;
    } else {
      $idmensaje=0;
    }
    
    $sql3='INSERT INTO tmm_notif_resp (id_respuesta,id_contact,id_notificacion,fecha_resp,respuesta,id_mensaje) VALUES (NULL,'.$_REQUEST['user'].','.$id.',NOW(),"'.$_REQUEST['mensaje'].'",'.$idmensaje.')';
    //echo $sql3;exit;
    $rs3=mysql_query($sql3, $conex);
 
    $ss='SELECT userid FROM tmm_notifications WHERE notificationid='.$id;
    $rr=mysql_query($ss, $conex);
    $rww=mysql_fetch_assoc($rr);
    extract($rww);    

    $sq='SELECT email1,first_name,last_name FROM vtiger_users WHERE id='.$userid		;
    $rs=mysql_query($sq, $conex);
    $rw1=mysql_fetch_assoc($rs);
    extract($rw1);
    
    $nombre=$first_name." ".$last_name;
    $asunto= 'Nueva Notificacion';
    $detalle =cuerpomail($nombre);

	$headers = "MIME-Version: 1.0\r\n";
	$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
	$headers .= "From:'Time Management'<notificaciones@timemanagement.es>\r\n";
	
	mail($email1,$asunto,$detalle,$headers);
	    
    }else{
    $sql="select id from vtiger_crmentity_seq";
    $query=mysql_query($sql,$conex);
    $id=mysql_fetch_array($query);
    $idcrm=$id['id'];
    $idcrm++;
    $sql_cuenta1="INSERT INTO vtiger_crmentity (crmid, smcreatorid, smownerid, modifiedby, setype, description, createdtime, modifiedtime, viewedtime, status, version, presence, deleted) VALUES (
    ".$idcrm.", '".$userid."', '".$userid."', '".$userid."', 'Notifications', '' , NOW(), NOW(), NOW(), '' , '0', '1', '0');";
 
      $r=mysql_query($sql_cuenta1,$conex);

    $upd="update vtiger_crmentity_seq set id=".$idcrm;
    mysql_query($upd,$conex);
    $to_contact=$_SESSION['customer_id'];
 
    $sql='INSERT INTO tmm_notifications (notificationid,userid,accountid,to_contactid,subject,sent_date,notstatus,sent_body,enviadopor) VALUES ('.$idcrm.','.$userid.','.$_REQUEST['cuenta'].','.$_REQUEST['user'].' ,"'.$_REQUEST['asunto'].'",NOW(),"Enviado","'.$_REQUEST['mensaje'].'","cliente")';

    $r2=mysql_query($sql,$conex);

    $sq='SELECT email1,first_name,last_name FROM vtiger_users WHERE id='.$userid;
    $rs=mysql_query($sq, $conex);
    $rw1=mysql_fetch_assoc($rs);
    extract($rw1);
    
    $nombre=$first_name." ".$last_name;
    $asunto= 'Nueva Notificacion';
    $detalle =cuerpomail($nombre);

	$headers = "MIME-Version: 1.0\r\n";
	$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
	$headers .= "From:'Time Management'<notificaciones@timemanagement.es>\r\n";
	 
	mail($email1,$asunto,$detalle,$headers);
	
    }

//	$sessidhash = base64_encode(base64_encode($_SESSION['customer_account_id'].":".$idcrm.":$customerid:CP"));  //  CP = CustomerPortal   �  PM = ProcessMaker
  ?>
  <script>
    document.location="mails.php?user=<?=$_REQUEST['user']?>&cuenta=<?=$_REQUEST['cuenta']?>";
  </script>


  
  
  <?php
	//header('Location:  index.php?module=Notifications&action=index2');
  }
?>

<?php
function cuerpomail($contact){
$cuerpo=' 
<html>
    <head>
        <title></title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    </head>
    <body>
        <table width="700" cellspacing="0" cellpadding="0" border="0" align="center" style="font-family: Arial,Helvetica,sans-serif; font-size: 12px; font-weight: normal; text-decoration: none; ">
        <tr>
            <td> </td>
            <td> </td>
            <td> </td>
        </tr>
        <tr>
            <td> </td>
            <td> </td>
            <td> </td>
        </tr>
        <tr>
            <td> </td>
            <td> </td>
            <td> </td>
        </tr>
        <tr>
            <td width="50"> </td>
            <td>
            <table width="100%" cellspacing="0" cellpadding="0" border="0">
                    <tr>
                        <td>
                        <table width="100%" cellspacing="0" cellpadding="0" border="0" style="font-family: Arial,Helvetica,sans-serif; font-size: 14px; color: rgb(255, 255, 255); font-weight: normal; line-height: 25px;">
                                <tr>
                                    <td align="left" rowspan="4"><img src="https://gestion.timemanagement.es/include/images/logo.jpg" alt="Timemanagement"</td>
                                    <td align="center"> </td>
                                </tr>
                                <tr>
                                    <td align="left" style=" font-family: Arial,Helvetica,sans-serif; font-size: 24px; color: rgb(255, 255, 255); font-weight: bolder; line-height: 35px;"><br /> </td>
                                </tr>
                                <tr>
                                    <td align="right" style="padding-right: 100px;"> </td>
                                </tr>
                                <tr>
                                    <td> </td>
                                </tr>
                        </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                        <table width="100%" cellspacing="0" cellpadding="0" border="0" style="font-family: Arial,Helvetica,sans-serif; font-size: 12px; font-weight: normal; color: rgb(0, 0, 0); background-color: rgb(255, 255, 255);">
                                <tr>
                                    <td valign="top">
                                    <table width="100%" cellspacing="0" cellpadding="5" border="0">
                                            <tr>
                                                <td align="right" style="font-family: Arial,Helvetica,sans-serif; font-size: 12px; font-weight: bolder; text-decoration: none; color: rgb(66, 66, 253);"> </td>
                                            </tr>
                                            <tr>
                                                <td> </td>
                                            </tr>
                                            <tr>
                                                <td style="font-family: Arial,Helvetica,sans-serif; font-size: 14px; color: rgb(22, 72, 134); font-weight: bolder; line-height: 15px;">
						    Estimado '.$contact.', </td>
                                            </tr>
                                            <tr>
                                                <td style="font-family: Arial,Helvetica,sans-serif; font-size: 12px; color: rgb(0, 0, 0); font-weight: normal; text-align: justify; line-height: 20px;">  
						   Le informamos que tiene una nueva notificacion en la plataforma de Timemanagement.<br />
						    Para acceder al mismo debe seguir el siguiente enlace:<br /><br />
							<a href="https://gestion.timemanagement.es/index.php">https://gestion.timemanagement.es/index.php</a></td>
                                            </tr>
                                            <tr>
                                                <td style="font-family: Arial,Helvetica,sans-serif; font-size: 12px; color: rgb(0, 0, 0); font-weight: normal; text-align: justify; line-height: 20px;">
                                                
                                                <br />  <br />  Gracias <br />  <br /> <br />  <br /> </td>
                                            </tr>
                                            
                                    </table>
                                    </td>
                                    <td width="1%" valign="top"> </td>
                                </tr>
                        </table>
                        </td>
                    </tr>
 
            </table>
            </td>
            <td width="50"> </td>
        </tr>
        <tr>
            <td> </td>
            <td> </td>
            <td> </td>
        </tr>
        <tr>
            <td> </td>
            <td> </td>
            <td> </td>
        </tr>
        <tr>
            <td> </td>
            <td> </td>
            <td> </td>
        </tr>
</table>
    </body>
</html>';
return $cuerpo;
}
?>
 
<form name="index" method="POST" action="nuevomail.php">
	<input type="hidden" name="module" value="Notifications">
	<input type="hidden" name="action" value="index">
	<input type="hidden" name="fun" value="savenot">
	<table  cellpadding="5" cellspacing="0" width="100%" border="0" align="center" class="dvtContentSpace">
<tr><td colspan="2" background="bg_folder_title.gif" align="left " nowrap="" height="21" style="font-family: Arial,Helvetica,sans-serif; font-size: 12px;  "><span style="white-space: nowrap;"> 
<a style="text-decoration:none" target="right"  ><font color="#000000"> &nbsp;   </font></a></span> 
<br>
         <small>&nbsp; 
 

</small></td>
</tr>
<tr>
<td background="bg_folder_title.gif" align="left" colspan="4">
<small>
<b><?php echo getTranslatedString('Ingrese la Nueva Notificación')?></b>
</small>
</td>
</tr>

	<tr>
		<td class="dvtCellLabel"  align="left"><b><?php echo getTranslatedString('Asunto')?>:</b></td>
              <td class="dvtCellInfo"  ><input name="asunto" type ="text" size="166" maxlength="166" value='<?php echo($subjet);?>' /></td>
	</tr>
	<tr>
		<td class="dvtCellLabel"   align="left"><b><?php echo getTranslatedString('Dirigido A')?>:</b></td>
              <td class="dvtCellInfo"  >
	      <select  name="usuario" id="usuario">
		<option value="4764"><?php echo getTranslatedString('Soporte')?></option>
	      </select>
	      </td>
	</tr>
	</table><table>
	<tr>
		<td class="dvtCellLabel"   align="left"><b><?php echo getTranslatedString('Mensaje')?>:</b></td> 
 
              <td colspan="4" class="dvtCellInfo"  ><textarea name="mensaje" wrap="hard" cols="135" rows="8" style="width: 1034px; height: 287px;" wrap="OFF"  >

<?php if(isset($_REQUEST['caso'])){?>
             

_______________________________________________________________________________________________________________________________________  
De <?php echo getTranslatedString('Soporte')?>: <?php echo($resp); ?> 
_______________________________________________________________________________________________________________________________________  
<?php echo getTranslatedString('Dirigido a Soporte')?>: <?php echo($body); ?>    

<?php }?>          
              </textarea></td>
	</tr>
	<tr><td></td><td align="center" colspan="4" background="bg_folder_title.gif">
<?php if(isset($_REQUEST['caso'])){ echo('<input type="hidden" name="caso" id="caso" value="'.$_REQUEST['caso'].'"/>');	}?>
	<input type="hidden" name="user" id="user" value='<?php echo($_REQUEST['user']);?>'/><input type="hidden" name="cuenta" id="cuenta" value='<?php echo($_REQUEST['cuenta']);?>'/><input class="crmbutton small cancel" name="Submitnot" type='submit' value="  Enviar  "  /></td></tr>
	</table>
	</form>