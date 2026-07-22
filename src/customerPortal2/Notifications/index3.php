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
$conex= mysql_connect((getenv('NOTIF_DB_HOST')?:'127.0.0.1:3306'),(getenv('NOTIF_DB_USER')?:'timeuser'),getenv('NOTIF_DB_PASSWORD'),true) or die("Error al intentar establecer la conexiÃ³n.");
    $dbp = mysql_select_db('plat_gestiontime2',$conex);
  
  if(!$_REQUEST['fun']){
		global $result;
		$sessionid = $_SESSION['customer_sessionid'];
		$customerid = $_SESSION['customer_id'];
		$notificationid =$_REQUEST['id'];
		$block= 'Notifications';
		if($notificationid == '')	include("NotificationsList.php");
						else	include("NotificationsDetail.php");
  }else if($_REQUEST['fun']=='search'){ 
    global $result;
    global $client;
    $sessionid = $_SESSION['customer_sessionid'];
    $customerid = $_SESSION['customer_id'];
    $notificationid =$_REQUEST['id'];
    $block= 'Notifications';
    echo '<tr><td><span class="lvtHeaderText">'.getTranslatedString("LBL_NOTIFICATIONS").'</span></td></tr>';
    echo '<tr><td align="right">
    <input class="crmbutton small cancel" type="button" onclick="document.location=\'index.php?module=Notifications&action=index&fun=newnot\'" value="Nueva notificacion" name="newnotificacion"></form>
    <input class="crmbutton small cancel" name="srch" type="button" value="Buscar" onClick="showSearchFormNow(\'tabSrch\',\'Notifications\');"> </td></tr>';
    echo '<tr><td colspan="2"><hr noshade="noshade" size="1" width="100%" align="left">'.
		  '<table width="95%"  border="0" cellspacing="0" cellpadding="5" align="center">';
    echo '<tr><td class="mnu">Notificaciones Nuevas/Pendientes</td></tr>';
    //var_dump($_REQUEST);
    $block2=$block.'-'.$_REQUEST['search_title'];
    //echo $block2;
	if ($customerid != '' ) {
		$params = array('id' => "$customerid", 'block'=>"$block2",'sessionid'=>$sessionid,'onlymine'=>false);
		$result = $client->call('get_list_values', $params, $Server_Path, $Server_Path);
		echo getblock_fieldlistview($result,$block);
		
	}
    echo '</table></td></tr>';

  }else if($_REQUEST['fun']=='newnot'){
  ?>
      </form>
<tr><td align="center">
	<form name="index" method="POST" action="index.php">
	<input type="hidden" name="module" value="Notifications">
	<input type="hidden" name="action" value="index">
	<input type="hidden" name="fun" value="savenot">
	<table  cellpadding="5" cellspacing="0" width="100%" border="0" align="center" class="dvtContentSpace">
	<tr><td colspan="4" class="detailedViewHeader"><b>Ingrese la Nueva Notificaci&oacute;n</b></td></tr>

	<tr>
		<td class="dvtCellLabel" width="20%" align="right">Asunto:</td>
              <td class="dvtCellInfo" width="80%"><input name="asunto" type ="text" size="15" maxlength="64" value='' /></td>
	</tr>
	<tr>
		<td class="dvtCellLabel" width="20%" align="right">Dirigido A:</td>
              <td class="dvtCellInfo" width="80%">
	      <select  name="usuario" id="usuario">
		<option value="4722">Soporte</option>
	      </select>
	      </td>
	</tr>
	<tr>
		<td class="dvtCellLabel" width="20%" align="right">Mensaje:</td>
              <td class="dvtCellInfo" width="80%"><textarea name="mensaje" wrap="hard" cols="60" rows="8" style="" wrap="OFF"  ></textarea></td>
	</tr>
	<tr><td class="dvtCellLabel" >&nbsp;</td class="dvtCellLabel" ><td align="center"><input class="crmbutton small cancel" name="Submitnot" type='submit' value="  Enviar  "  /></td></tr>
	</table>
	</form>

</td></tr>

<?php  
  } else if($_REQUEST['fun']=='savenot'){
    
    $userid=$_REQUEST['usuario'];
    $sql="select id from vtiger_crmentity_seq";
    $query=mysql_query($sql,$conex);
    $id=mysql_fetch_array($query);
    $idcrm=$id['id'];
    $idcrm++;
    $sql_cuenta1="INSERT INTO vtiger_crmentity (crmid, smcreatorid, smownerid, modifiedby, setype, description, createdtime, modifiedtime, viewedtime, status, version, presence, deleted) VALUES (
    ".$idcrm.", '".$userid."', '".$userid."', '".$userid."', 'Notifications', '' , NOW(), NOW(), NOW(), '' , '0', '1', '0');";
    //echo $sql_cuenta1;exit;
      $r=mysql_query($sql_cuenta1,$conex);

    $upd="update vtiger_crmentity_seq set id=".$idcrm;
    mysql_query($upd,$conex);
    $to_contact=$_SESSION['customer_id'];
  /*me falta el accountid */
    $sql='INSERT INTO tmm_notifications (notificationid,userid,accountid,to_contactid,subject,sent_date,notstatus,sent_body,enviadopor) VALUES ('.$idcrm.','.$userid.','.$_SESSION["customer_account_id"].',"'.$to_contact.'","'.$_REQUEST['asunto'].'",NOW(),"Enviado","'.$_REQUEST['mensaje'].'","cliente")';
    
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
		//ini_set(sendmail_from,'difusion@espaciopampa.com');
	mail($email1,$asunto,$detalle,$headers);
	
    

	$sessidhash = base64_encode(base64_encode($_SESSION['customer_account_id'].":".$idcrm.":$customerid:CP"));  //  CP = CustomerPortal   �  PM = ProcessMaker
  ?>
  <script>
    document.location='index.php?module=Notifications&action=index&id=<?=$idcrm?>';
  </script>
  <?php
	
  } else if($_REQUEST['fun']=='resp'){
    
?>
            </form>
    <tr><td align="center">
	    <form name="index" method="POST" action="index.php">
	    <input type="hidden" name="module" value="Notifications">
	    <input type="hidden" name="action" value="index">
	    <input type="hidden" name="fun" value="savresp">
	    <input type="hidden" name="id" value="<?=$_REQUEST['id']?>">
	    <table  cellpadding="5" cellspacing="0" width="100%" border="0" align="center" class="dvtContentSpace">
	    <tr>
		    <td class="dvtCellLabel" width="20%" align="right">Mensaje:</td>
		  <td class="dvtCellInfo" width="80%"><textarea name="mensaje" wrap="hard" cols="60" rows="8" style="" wrap="OFF"  ></textarea></td>
	    </tr>
	    <tr><td class="dvtCellLabel" >&nbsp;</td class="dvtCellLabel" ><td align="center"><input class="crmbutton small cancel" name="Submitnot" type='submit' value="  Enviar  "  /></td></tr>
	    </table>
	    </form>

    </td></tr>
<?php
  } else if($_REQUEST['fun']=='savresp'){
    $id=$_REQUEST['id'];
    
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
    
    $sql3='INSERT INTO tmm_notif_resp (id_respuesta,id_contact,id_notificacion,fecha_resp,respuesta,id_mensaje) VALUES (NULL,'.$id_contact.','.$id.',NOW(),"'.$_REQUEST['mensaje'].'",'.$idmensaje.')';
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
	
?>
  <script>
    document.location='index.php?module=Notifications&action=index&id=<?=$id?>';
  </script>
<?php

  }
?>
			</table>
</td></tr>
</table>

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
                    <tr>
                        <td>
                        <table width="100%" cellspacing="0" cellpadding="5" border="0" style="border: 2px solid rgb(180, 180, 179);font-family: Arial,Helvetica,sans-serif; font-size: 12px; color: rgb(0, 0, 0); font-weight: normal; line-height: 15px; background-color: rgb(226,226, 225);">
                                <tr>
                                    <td align="center"></td>
                                </tr>
                                <tr>
                                    <td align="center">Telf: +34 902.88.32.94     -     Fax: +34 91.567.11.71  </td>
                                </tr>
                                <tr>
                                    <td align="center">Email Id: <a style="font-family: Arial,Helvetica,sans-serif; font-size: 12px; font-weight: bolder; text-decoration: none; color: rgb(0, 0, 0);" href="mailto:support@vtiger.com">ltramontini@timemanagement.es</a></td>
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
