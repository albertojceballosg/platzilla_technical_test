<?php


include_once("include/utils/utils.php");
include_once('include/utils/comunesTareas.php');
	
global $conex;
global $adb;

function confirmarTicket($ticketId){ // cambia el estado desarrollando del ticket
	
	global $adb;
	$sql = "UPDATE vtiger_troubletickets
					SET confirmada = 'Si'
					WHERE ticketid = $ticketId ";
					
	$result = $adb->query($sql);
	$sql = "SELECT confirmada
				FROM vtiger_troubletickets
				WHERE  ticketid = $ticketId";
	$result = $adb->query($sql);
	
	if ($row=$adb->fetch_array($result)){
		if ($row['confirmada'] == 'Si'){
				return TRUE;
			}
	}
	return FALSE;
}
/*
function getVendorName ($vendorid) {	
	global $conex;
	$data = 0;
	$sql = "SELECT  v.vendorname 
			FROM vtiger_vendor v
			left join vtiger_crmentity crm on v.vendorid=crm.crmid
			where crm.deleted=0
			and v.vendorid = $vendorid  
		
		 ";
	$result = mysql_query($sql);
	while ($reg = mysql_fetch_array($result)){
		$vendorname = $reg['vendorname'];
	}
	return $vendorname;
}*/



function getTicketTitle($ticketId) {	
	global $conex;
	$data = 0;
	$sql = "SELECT  title 
			FROM vtiger_troubletickets 
			where ticketid = $ticketId  
		
		 ";
	$result = mysql_query($sql);
	while ($reg = mysql_fetch_array($result)){
		$title = $reg['title'];
	}
	return $title;
}


///////////////////////// GRABA RESPUESTAS DEL POPUP INICIAL /////////////////////////////
$parametros = explode("|",$_POST['parametros']);

$vendorId = $parametros[0];
$hoy = date('Y-m-d',time());
for($i=2;$i<count($parametros);$i+=2) {
	$ticketId = $parametros[$i];
	$respuesta = mysql_real_escape_string($parametros[($i+1)]);
	
	$sql = "INSERT INTO vtiger_diarynotes_desarrolladores (diarynoteid, date, coment, ticketid, desarrollador_id)  VALUES (NULL,'$hoy','$respuesta','$ticketId','$vendorId');";
	 $result = mysql_query($sql);
	 
	 //////////////////Envia Mail a David Polo////////////////////////

	if(!empty($respuesta))
	{
				$desarrollador = getVendorName($vendorId);
				$ticketTitle = getTicketTitle($ticketId);
				$mail ='dpolo@timemanagement.es';
				$asunto= 'Notificacion de comentario Desarrollador';
				$detalle = $desarrollador.' ha respondido en el informe diario de la tarea <a href="http://time.platzilla.com/time/modules/HelpDesk/control_diario.php?tipo_tarea=&record='.$ticketId.'&desarrollador='.$vendorId.'" ><b>'.$ticketTitle.'</b></a>:<br>'.$respuesta;
				$headers = "MIME-Version: 1.0\r\n";
				$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
				$headers .= "From:'timemanagement.es'<soporte@timemanagement.es>\r\n";
				mail($mail,$asunto,$detalle,$headers);

	}
}
// $return[parametros] = $sql;
$return[parametros] = $_POST['parametros'];




///////////////////////// GRABA RESPUESTAS DEL POPUP INICIAL /////////////////////////////



$return['success']=false;
$tipo = tipoUsuario($_SESSION["authenticated_user_id"]);
if (!empty($_REQUEST['t']) and ($tipo = 'H2' or $tipo = 'H26')){
	$ticketid= $_REQUEST['t'];
	$return['t'] = $_REQUEST['t'];
	$booleano = confirmarTicket($ticketid);
	if ($booleano == TRUE){
		$return['success']=true;
	}
}elseif (!empty($_REQUEST['div'])){
	$_SESSION['mostrarDiv97']=1;
	$return['success']=true;
}
echo json_encode($return);
die();
?>