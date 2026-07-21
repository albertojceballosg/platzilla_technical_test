<?php
global $result;
global $client;
global $Server_Path;

$customerid = $_SESSION['customer_id'];
$sessionid = $_SESSION['customer_sessionid'];
if($notificationid != '')
{
	$params = array('id' => "$notificationid", 'block'=>"$block", 'contactid'=>"$customerid",'sessionid'=>"$sessionid");
	$result = $client->call('get_notifications_detail', $params, $Server_Path, $Server_Path);
	if (count($result) == 1 && $result[0] == "#NOT AUTHORIZED#") {
		echo '<tr><td colspan="6" align="center"><b>'.getTranslatedString('LBL_NOT_AUTHORISED').'</b></td></tr>';
		die();
	}
	echo '<tr><td><input class="crmbutton small cancel" type="button" value="'.getTranslatedString('LBL_BACK_BUTTON').'" onclick="window.history.back();"/></td></tr>';
	echo getblock_fieldlist($result);
	$conex1= mysql_connect('127.0.0.1:3306','timeuser','Eceptu.2011',true) or die("Error al intentar establecer la conexiÃ³n.");
	$dbp = mysql_select_db('plat_gestiontime2',$conex1);
	$sq='SELECT enviadopor, notstatus FROM tmm_notifications WHERE notificationid='.$notificationid;
	
	$rs=mysql_query($sq,$conex1);
	$env=mysql_fetch_array($rs);
	$sq='SELECT id_respuesta FROM tmm_notif_resp WHERE id_notificacion='.$notificationid;
	$query=mysql_query($sq,$conex1);
	$cant=mysql_num_rows($query);

	$enviadopor=$env['enviadopor'];
	$estado=$env['notstatus'];
	   
	if($enviadopor!='cliente' and $cant==0 and $estado!="Cerrado"){
	echo '<tr><td colspan="2" align="right">
	      <input class="crmbutton small cancel" type="button" onclick="document.location=\'index.php?module=Notifications&action=index&fun=resp&id='.$notificationid.'\'" value="Responder" name="respuesta">
	      </td></tr>';
	}
	
	$sq2='SELECT * FROM tmm_notif_resp WHERE id_notificacion='.$notificationid.' ORDER BY id_respuesta ASC';
	$rs2=mysql_query($sq2,$conex1);
	$ca=mysql_num_rows($rs2);
	$rw=mysql_fetch_assoc($rs2);
	if($ca>0){
	
	echo '<tr><td class="detailedViewHeader" colspan="4" align="left"><b>Respuestas</b></td></tr><tr><td colspan="4"><table width="100%">';
	$usuario=$_SESSION['customer_id'];
	
	for($j=0;$j<$ca;$j++){
	    
	    extract($rw);
	   // echo $id_respuesta;
	    /*$id_respuesta=$adb->query_result($rs,$i,'id_respuesta');
	    $id_contact=$adb->query_result($rs,$i,'id_contact');
	    $fecha_resp=$adb->query_result($rs,$i,'fecha_resp');
	    $respuesta=$adb->query_result($rs,$i,'respuesta');*/
	    $sql='SELECT firstname,lastname FROM vtiger_contactdetails WHERE contactid='.$id_contact;
	    $result=mysql_query($sql,$conex1);
	    if(mysql_num_rows($result)>0){
		$r=mysql_fetch_array($result);
		$nombre=$r['firstname'];
		$apellido=$r['lastname'];
	    } else {
		$squ='SELECT first_name, last_name FROM vtiger_users WHERE id='.$id_contact;
		//echo $squ;
		$resu=mysql_query($squ,$conex1);
		$r=mysql_fetch_array($resu);
		$nombre=$r['first_name'];
		$apellido=$r['last_name'];
	    }
	      echo '<tr><td style="width:40px" class="dvtCellInfo"><img src="../flecha.jpg"></td>
			  <td style="width:90px" class="dvtCellInfo">'.$fecha_resp.'</td>
			  <td style="width:100%" class="dvtCellInfo"><b>De: '.$nombre.' '.$apellido.'</b><br>'.$respuesta.'</td>
			  <td style="width:50px" class="dvtCellInfo">';
	      $sqq='SELECT * FROM tmm_notif_resp WHERE id_mensaje='.$id_respuesta;
	      $rr=mysql_query($sqq,$conex1);
	      $cantidad=mysql_num_rows($rr);
	
	      if($id_contact!=$usuario and $cantidad==0 and $estado!="Cerrado")
		  echo '<input class="crmbutton small cancel" type="button" onclick="document.location=\'index.php?module=Notifications&action=index&fun=resp&id='.$notificationid.'\'" value="Responder" name="respuesta">
	      ';
	      echo '</td></tr>';
	  
	  $rw=mysql_fetch_assoc($rs2);
	    
	}
	echo '</table></td></tr>'; 
	}
}

function serespondio($id){
   
  $sqq='SELECT * FROM tmm_notif_resp WHERE id_mensaje='.$id;
  $rr=mysql_query($sqq,$conex1);
  $cantidad=mysql_num_rows($rr);

  if($cantidad>0)
      return true;
  else
      return false;
}
?>
