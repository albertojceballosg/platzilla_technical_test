<?php
/*
if (strstr($_SERVER['HTTP_HOST'],'gen.timelocal.es')) {
	$nameplat = explode('.',$_SERVER['HTTP_HOST']);
	session_name($nameplat[0]);
}

session_start();
chdir("../../../");
*/
include_once('include/utils/utils.php');
include_once('include/utils/comunesTareas.php');

function calcularTiempoPunto($vendorid,$puntoid,$hoy){
	global $adb;
	$tiempo = 0;
	$sql  ="SELECT inicio , fin
			FROM vtiger_log_tiempo_ticket
			WHERE
			 puntoid = $puntoid
			and desarrollador_id = $vendorid
			and inicio >= '$hoy 00:00:01' and inicio <= '$hoy 23:59:59'


	";
 
	$result = $adb->query($sql);
	while ($reg = $adb->fetch_array($result)){
		$enddate = $reg['fin'];
		if ($enddate == '0000-00-00 00:00:00'){
		$enddate=date('Y-m-d H:i:s');
		}
		$diferencia = diferenciaEntreFecha($reg['inicio'],$enddate);
		$tiempo = $tiempo+$diferencia;
	}
	if ($tiempo > 0){
		return formatearSegundos($tiempo, $pad_hrs = FALSE);
	}
	else{
		return ' - ' ;
	}

}
function listarTareas($ticketid)
{
	global $adb;
    $sql ="SELECT a.activityid, a.date_start,a.due_date, v.vendorname
	
			FROM vtiger_ticketcf tcf
			left join vtiger_seactivityrel rel on rel.crmid = tcf.ticketid
			left join vtiger_activity a on a.activityid = rel.activityid
			left join vtiger_crmentity crm on tcf.ticketid=crm.crmid
                        left join vtiger_vendor v on a.desarrollador_id=v.vendorid
			where crm.deleted=0

			
			and tcf.ticketid = $ticketid and a.activityid!='' and v.vendorid=".$_REQUEST['selectedVendor']." and v.vendorname!=''
	order by a.date_start asc";
    
	$result = $adb->query($sql);
	    while ($reg = $adb->fetch_array($result)){
		$activityid = $reg['activityid'];
		 $selected='';
                if($activityid==$_POST['tarea'])
                {
                    $selected=' selected ';
                } 
             if(!empty($activityid))
                $html.='<option value="'.$activityid.'"    '.$selected.'>'.substr($reg['date_start'],8,2).'-'.substr($reg['date_start'],5,2).'-'.substr($reg['date_start'],0,4).' / '.substr($reg['due_date'],8,2).'-'.substr($reg['due_date'],5,2).'-'.substr($reg['due_date'],0,4).'-'.$reg['vendorname'].'</option>';
	}
    return $html;
    
}
function obtenerPuntosTrabajados($vendor,$ticket,$hoy){
	global $adb;
	$sql = " SELECT p.pointid , p.description
			FROM vtiger_ticketpuntos p
			INNER JOIN vtiger_diarynotes_desarrolladores l on l.puntoid=p.pointid
			WHERE l.date >= '$hoy' and l.date <='$hoy'
			and l.desarrollador_id = $vendor
			and l.ticketid = $ticket
			GROUP BY p.pointid ";
	$result = $adb->query($sql);
	$i=0;
	while ($reg = $adb->fetch_array($result)){
		$return[$i]['desc']=$reg['description'];
		$return[$i]['tiempo']= calcularTiempoPunto($vendor,$reg['pointid'],$hoy);
		$i++;
	}
	return $return;
}

function getUserIdFromVendor ($vendorid) {
	$data = 0;
        global $adb;
	$sql = "SELECT  v.user_id
			FROM vtiger_vendor v
			left join vtiger_crmentity crm on v.vendorid=crm.crmid
			where crm.deleted=0
			and  v.vendorid  = $vendorid

		 ";
	$result = $adb->query($sql);
	while ($reg = $adb->fetch_array($result)){
		$data = $reg['user_id'];
	}
	return $data;
}




function desFormatearFecha($fecha){
	$data = substr($fecha,6,4).'-'.substr($fecha,3,2).'-'.substr($fecha,0,2);
	return $data;

}
function obtenerSubordinados ($h8Id,$tipo) {
    global $adb;
	$vendorType = utf8_encode(html_entity_decode(obtenerValorVariable('TASK_VENDOR_TYPE','Vendors')));
	if ($tipo == 'H8'){
		$sql = "SELECT  vendorname,v.vendorid,vendortype,u.id
				FROM vtiger_users u
				left join vtiger_vendor v on u.id = v.user_id
				left join vtiger_crmentity crm on v.vendorid=crm.crmid
				left join vtiger_vendorcf vcf on vcf.vendorid=v.vendorid
				left join vtiger_user2rel tr on tr.id_subordinado = u.id
				where crm.deleted=0
				and tr.id_jefe =$h8Id
				and (vendortype='$vendorType')
				order by vendorname
			 ";
	}
	elseif ($tipo == 'H2'){
		$sql = "SELECT  vendorname,v.vendorid,vendortype,u.id
				FROM vtiger_users u
				left join vtiger_vendor v on u.id = v.user_id
				left join vtiger_crmentity crm on v.vendorid=crm.crmid
				left join vtiger_vendorcf vcf on vcf.vendorid=v.vendorid
				where crm.deleted=0
				and (vendortype='$vendorType')
				order by vendorname
			 ";
	}
	if (!empty($sql)){
		$result = $adb->query($sql);
		$i = 0;
		while ($reg = $adb->fetch_array($result)){
			$data[$i]['nombre'] = $reg['vendorname'];
			$data[$i]['vendorid'] = $reg['vendorid'];
			$data[$i]['tipo'] = $reg['vendortype'];
			$data[$i]['user_id'] = $reg['id'];
			$i++;
		}
	}
	return $data;
}
function esTestFuncional($ticketid){
    global $adb;
    $sql="select cf_689 from vtiger_ticketcf where ticketid=".$ticketid;
    $respuesta='no';
    $row=$adb->fetch_array($adb->query($sql));
    $tipo=$row['cf_689'];
    if($tipo=='Testing de Desarrollo')
        $respuesta='si';
    return $respuesta;
}
function obtenerRolUser($idvendor){
    global $adb;
    $sql="select roleid from vtiger_vendor vendor inner join vtiger_user2role user on vendor.user_id=user.userid where vendor.vendorid=".$idvendor;
    $row=$adb->fetch_array($adb->query($sql));
    $rol=$row['roleid'];
    return $rol;
}

function panelH8 ($userId,$fecha,$selectedVendor,$tipo) {
	$alguno = false;
	$bufferSalida = '';
	if ($tipo <> FALSE) {
		$users = obtenerSubordinados($userId,$tipo);
		if (!empty($users)){
			$bufferSalida.= '<div align="center" style="width:98%;margin:10px;"><table width="0%"><tr><td>';
			foreach ($users as $key => $value) {
				$aCerrar = obtenerTicketsACerrar ($value['vendorid'],$fecha);
				if (!empty($aCerrar)){
					if ($selectedVendor == $value['vendorid']){
						$bColor="background-color:#FC0;";
					}
					else{
						$bColor="";
					}
					$alguno = true;
					$rol=obtenerRolUser($userId);
                                      
                                            
					$bufferSalida.=  '<div style="'.$bColor.' text-align:center;padding:3px; float:left; width:200px; border:thin; border-style:solid; border-color:#666"><b>
					<a href="index.php?module=Calendar&action=panel_jefe_desarrollo&Popup=true&selectedVendor='.$value['vendorid'].'" >'.$value['nombre'].'</a></b></div>';
				}

			}
			if ($alguno == false){
				$bufferSalida.=  '<font size="+2" color="red">No hay informes diarios por cerrar</font>';
			}
			$bufferSalida.=  '</div><br><div style="clear:both" ></td></tr></table></div>';
		}

	}
	return $bufferSalida;
}

function esSubordinadoDe($vendorid,$userId,$tipo) {
	$return = FALSE;
        global $adb;
	if ($tipo == 'H2'){
		$return = TRUE;
	}
		elseif ($tipo == 'H8'){
			$subordinadoId = getUserIdFromVendor ($vendorid);
			$sql = "SELECT *
					FROM vtiger_user2rel
					WHERE id_subordinado = 	$subordinadoId
					and id_jefe = $userId
					";
			$result = $adb->query($sql);
			while($reg = $adb->fetch_array($result)){
				$return = TRUE;
			}
		}

	return $return;
}


function obtenerHorasDedicadasTicket($date,$ticketid,$vendorid) {
	global $adb;
	
	/*
	$sql = "SELECT horas_dedicadas, coment FROM vtiger_diarynotes_desarrolladores
				WHERE date LIKE '".$date."%' AND desarrollador_id = ".$vendorid." AND ticketid = ".$ticketid;
	*/
	$sql = "SELECT horas FROM vtiger_ordentrabajo_informes
				WHERE fecha = ? AND vendorid = ? AND ordentrabajoid = ?";
				
	$result = $adb->pquery($sql,array($date,$ticketid,$vendorid));
	
	if ($result) {
		$row = $adb->fetch_array($result);
		return $row;
	}

	return;
}

function getUserData($vendorId) {
	global $adb;
	$vendorType = obtenerValorVariable('TASK_VENDOR_TYPE','Vendors');
	$sql = "SELECT  vendorname,v.vendorid,vendortype,u.first_name , u.last_name
			FROM vtiger_users u
			left join vtiger_vendor v on u.id = v.user_id
			left join vtiger_crmentity crm on v.vendorid=crm.crmid
			left join vtiger_vendorcf vcf on vcf.vendorid=v.vendorid
			where crm.deleted=0
			and v.vendorid = $vendorId
			and (vendortype = '$vendorType')
			order by vendorname
		 ";
	$result = $adb->query($sql);
	while ($reg = $adb->fetch_array($result)){
		$data['Nombre'] = $reg['vendorname'];
		$data['vendorid'] = $reg['vendorid'];
		$data['first_name'] = $reg['first_name'];
		$data['last_name'] = $reg['last_name'];
		$data['tipo'] = $reg['vendortype'];
		$data['user_id'] = $userId;
	}
	if (empty($data)){
		$data = FALSE;
	}
	return $data;
}

function obtenerPuntosHoy($ticketId,$vendorId,$fecha){
        global $adb;
	$sql ="SELECT description , porcentaje ,date ,pointid

			FROM vtiger_ticketpuntos

			where ticketid = $ticketId
			and desarrollador_id = $vendorId
			and  porcentaje < 100
			and date BETWEEN '$fecha' and '$fecha'
			order by pointid

	";
	$result = $adb->query($sql);
	$i = 0;
	while ($reg = $adb->fetch_array($result)){
		$data[$i]['description'] = $reg['description'];
		$data[$i]['porcentaje'] = $reg['porcentaje'];
		$data[$i]['date'] = $reg['date'];
		$data[$i]['pointid'] = $reg['pointid'];
		$i++;
	}
	return $data;
}

function obtenerPuntosPendientes($ticketId,$vendorId,$fecha){
        global $adb;
	$sql ="SELECT description , porcentaje ,date ,pointid

			FROM vtiger_ticketpuntos

			where ticketid = $ticketId
			and desarrollador_id = $vendorId
			and  porcentaje < 100
			and date > '$fecha'
			order by pointid
	";
	$result = $adb->query($sql);
	$i = 0;
	while ($reg = $adb->fetch_array($result)){
		$data[$i]['description'] = $reg['description'];
		$data[$i]['porcentaje'] = $reg['porcentaje'];
		$data[$i]['date'] = $reg['date'];
		$data[$i]['pointid'] = $reg['pointid'];
		$i++;
	}
	return $data;
}

function obtenerPuntosRetrasados($ticketId,$vendorId,$fecha){
        global $adb;
	$sql ="SELECT description , porcentaje ,date ,pointid

			FROM vtiger_ticketpuntos

			where ticketid = $ticketId
			and desarrollador_id = $vendorId
			and  porcentaje < 100
			and date < '$fecha'
			order by pointid
	";
	$result = $adb->query($sql);
	$i = 0;
	while ($reg = $adb->fetch_array($result)){
		$data[$i]['description'] = $reg['description'];
		$data[$i]['porcentaje'] = $reg['porcentaje'];
		$data[$i]['date'] = $reg['date'];
		$data[$i]['pointid'] = $reg['pointid'];
		$i++;
	}
	return $data;
}

function obtenerPuntosCerrados($ticketId,$vendorId,$fecha){
        global $adb;
	$sql ="SELECT description , porcentaje ,date ,pointid, enddate

			FROM vtiger_ticketpuntos

			where ticketid = $ticketId
			and desarrollador_id = $vendorId
			and  porcentaje = 100
			order by pointid
	";
	$result = $adb->query($sql);
	$i = 0;
	while ($reg = $adb->fetch_array($result)){
		$data[$i]['description'] = $reg['description'];
		$data[$i]['porcentaje'] = $reg['porcentaje'];
		$data[$i]['horas_dedicadas'] = $reg['horas_dedicadas'];
		$data[$i]['date'] = $reg['date'];
		$data[$i]['enddate'] = $reg['enddate'];
		$data[$i]['pointid'] = $reg['pointid'];
		$i++;
	}
	return $data;
}
 function finalizarProcesoExpress($ticketid,$vendorId){

	global $adb;

	$queryt="select * from vtiger_ticketcf where ticketid=".$ticketid;
	$rowt=$adb->fetch_array($adb->query($queryt));
	$guid=$rowt['guid'];
	$up="update vtiger_troubletickets set status='".TICKET_PENDING_CONFIRMATION_OF_CUSTOMER."' where ticketid=".$ticketid;
	$adb->query($up);
    	enviaNotificacionCambioEstado($ticketid);
 }
	function finalizarProcesoDoc($ticketid,$vendorId){

		global $adb;

		$queryt="select * from vtiger_ticketcf where ticketid=".$ticketid;
		$rowt=$adb->fetch_array($adb->query($queryt));
		$guid=$rowt['cf_689'];
		$sql="update vtiger_reldesa set estado='Finalizado' where idticket=".$ticketid." and idvendor=".$vendorId." and rol='Documentacion'";
		$adb->query($sql);
		$sql="select estado from vtiger_reldesa where idticket=".$ticketid." and rol='Documentacion'";
		$result=$adb->query($sql);
		$fin='si';
		while($row=$adb->fetch_array($result)){
			if($row['estado']!='Finalizado')
				$fin='no';
		}
		if($fin=='si') {
			$sql2="select comentario_responsable_cuenta from vtiger_troubletickets where ticketid=".$ticketid;
			$result2=  $adb->query($sql2);
			$row2=$adb->fetch_array($result2);
			$respuesta2=$row2['comentario_responsable_cuenta'];
			if($respuesta2=='')
				$notifico='no';
			else
				$notifico='si';
			if($notifico=='si')
				$up="update vtiger_troubletickets set status='13. Dev validado por cliente' where ticketid=".$ticketid;
			if($notifico=='no')
				$up="update vtiger_troubletickets set status='".TICKET_PENDING_CONFIRMATION_OF_CUSTOMER."' where ticketid=".$ticketid;
			$adb->query($up);
			enviaNotificacionCambioEstado($ticketid);
		}
	}
 function notificarMail($idcrm){
            global $adb;
            
           
            if($origen=='Plataforma'){
            $sql="select email1 ,first_name,vtiger_troubletickets.title,ticket_no,contacto_solicitante  from vtiger_troubletickets inner join vtiger_ticketcf on vtiger_ticketcf.ticketid=vtiger_troubletickets.ticketid inner join vtiger_users on vtiger_users.user_name=vtiger_troubletickets.contacto_solicitante where vtiger_troubletickets.ticketid=".$idcrm;
            
            $re=$adb->query($sql);
            $row=$adb->fetch_array($re);
            $email=$row['email1'];
 
            $contacto=$row['firstname'];
            $codigo=$row['ticket_no'];
            $titulo=$row['title'];
            }
            else{
                $sql="select firstname,vtiger_troubletickets.title,ticket_no,contacto_solicitante from vtiger_troubletickets inner join vtiger_ticketcf on vtiger_ticketcf.ticketid=vtiger_troubletickets.ticketid inner join vtiger_contactdetails on vtiger_contactdetails.email=vtiger_troubletickets.contacto_solicitante where vtiger_troubletickets.ticketid=".$idcrm;
            $re=$adb->query($sql);
            $row=$adb->fetch_array($re);
            $email=$row['contacto_solicitante'];
            $contacto=$row['firstname'];
            $titulo=$row['title'];
            $codigo=$row['ticket_no'];
            }
            $remitente="admin@timemanagement.es";
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
            $headers .= "From:'timemanagement.es'<admin@timemanagement.es>\r\n";
            $cuerpo='Estimado '.$contacto.':<br> Hemos finalizado el pedido '.$titulo.' c&oacute;digo '.$codigo.', el mismo ya se encuentra en la plataforma.<br>';
           
            
            $cuerpo.='<br><br>Ante cualquier consulta, podra realizar el pedido desde el portal de cliente en la pestaña Notificaciones .<br> Nos encontramos a su disposici&oacute;n. <br><br>
                Le Saluda Atte. <br> El equipo de Time Management<br>';
            $cuerpo.='<img src="vtiger-crm.jpg">';
            $re= mail('mgatti@timemanagement.es','Petición Finalizada', $cuerpo,$headers);
            exit;
        }
function finalizaProcesoTestFuncional($ticketid,$vendorid){
    global $adb;
    $sql2="select necesita_documentacion from vtiger_troubletickets where ticketid=".$ticketid;
    $row2=$adb->fetch_array($adb->query($sql2));
    $ndoc=$row2['necesita_documentacion'];
    //Proximo estado segun si necesita documentacion o no , siempre que el Testing este correcto
    if($ndoc=='si')
        $nuevoEstado='10. Dev terminado y testado. En espera de validación';
    else{
        $nuevoEstado='13. Dev validado por cliente. Pdte de evaluación interna';
    }
    $qu="select MAX(vtiger_solicitudtesting.solicitudTestingid) as maximo from vtiger_solicitudtesting where ticketid=".$ticketid;
    $res=$adb->fetch_array($adb->query($qu));
    $idtest=$res['maximo'];
    $sql="select * from vtiger_solicitudtesting inner join vtiger_puntosTesting on vtiger_solicitudtesting.solicitudTestingid=vtiger_puntosTesting.testingid where ticketid=".$ticketid. " and testingid=".$idtest;
    $result=$adb->query($sql);
    
    $estado='';
    while($row=  $adb->fetch_array($result)){
        $guid=$row['guid'];
        $estado=$row['estado'];
        if($estado=='incorrecto'){
            $nuevoEstado='5. Asignar Recursos';
        }
    }
   $up="update vtiger_troubletickets set status='".$nuevoEstado."' where ticketid=".$ticketid;
   $adb->query($up);
            if($nuevoEstado!='5. Asignar Recursos'){
                notificarMail($ticketid);
            }
                
}

  function finalizarProceso($ticketid,$vendorId){

            global $adb;
			
			if (!isset($ticketid) || $ticketid == '' 
				|| isset($vendorId) || $vendorId == '')
				return;
			

            $query="select user_name,id_jefe from vtiger_vendor inner join vtiger_users on vtiger_users.id=vtiger_vendor.user_id inner join vtiger_user2rel on vtiger_user2rel.id_subordinado=vtiger_users.id where vendorid=".$vendorId;
            $result=$adb->query($query);
            $row=$adb->fetch_array($result);
            $user_name=$row['user_name'];
            $idJefe=$row['id_jefe'];

            $queryn="select user_name from vtiger_users where id=".$idJefe;
            $name=$adb->fetch_array($adb->query($queryn));
            $name=$name['user_name'];


            
            $queryt="select * from vtiger_desarrolloxguid where ticketid=".$ticketid." and vendorid=".$vendorId." and tipo='Desarrollo'";
            $rowt=$adb->fetch_array($adb->query($queryt));
            $guid=$rowt['guid'];
           $sql="update vtiger_reldesa set estado='Finalizado' where idticket=".$ticketid." and idvendor=".$vendorId." and rol='Desarrollo'";
           $adb->query($sql);
           $sql="select estado from vtiger_reldesa where idticket=".$ticketid." and rol='Desarrollo'";
                $result=$adb->query($sql);
                $fin='si';
               while($row=$adb->fetch_array($result)){
                    if($row['estado']!='Finalizado')
                        $fin='no';
                }
                if($fin=='si')
                {
                    $estado=TICKET_TO_VALIDATE;
                    $up="update vtiger_troubletickets set status='".$estado."' where ticketid=".$ticketid;
                    $adb->query($up);
                    enviaNotificacionCambioEstado($ticketid);
                }
				
            


  }
 function finalizarProcesoTest($ticketid,$vendorId){

            global $conex/*_login*/;

         

            $queryn="select cf_769 ,vtiger_solicitudtesting.solicitudTestingid from vtiger_solicitudtesting inner join vtiger_solicitudtestingcf on vtiger_solicitudtesting.solicitudTestingid=vtiger_solicitudtestingcf.solicitudTestingid where ticketid=".$ticketid;
            $name=$adb->fetch_array($adb->query($queryn));
            
            $idcrm=$name['solicitudTestingid'];
            $name=$name['cf_769'];
            
            $sql="select user_name from vtiger_vendor inner join vtiger_users on vtiger_vendor.user_id=vtiger_users.id where vendorname='".$name."'";
            $name=$adb->fetch_array($adb->query($sql));
            $name=$name['user_name'];
            
            $sql="select user_name from vtiger_vendor inner join vtiger_users on vtiger_vendor.user_id=vtiger_users.id where vendorid=".$vendorId;
            $row=$adb->fetch_array($adb->query($sql));
            $user_name=$row['user_name'];
              $fecha=date('Y-m-d');
            $sql="update vtiger_solicitudtestingcf set cf_748='Finalizado',cf_746='".$fecha."' where solicitudTestingid=".$idcrm;
            $adb->query($sql);
           
            /*
            $conex_proceso = mysql_connect('localhost:3306','timeuser','Eceptu.2011',true) or die("Error al intentar establecer la conexion de login.");
            $db_pros = mysql_select_db('wf_workflow2'_proceso);
            $sqluid="select USR_UID from USERS where USR_USERNAME='".$name."'";
            $uidUSER=$adb->fetch_array($adb->query($sqluid_proceso));
            $iduser=$uidUSER['USR_UID'];
            
            $pass="select USR_PASSWORD from USERS where USR_USERNAME='".$row['user_name']."'";

            $rowpass=$adb->fetch_array($adb->query($pass_proceso));
            $pass=$rowpass['USR_PASSWORD'];


            $pass='md5:'.$pass;
            $queryt="select * from vtiger_solicitudtesting where ticketid=".$ticketid;
            $rowt=$adb->fetch_array($adb->query($queryt));
            $guid=$rowt['guid'];

            $conex_login = mysql_connect('localhost:3306','timeuser','Eceptu.2011',true) or die("Error al intentar establecer la conexion de login.");
            $db_login = mysql_select_db('app_autologin'_login);
            $query2="select password from processos where username='".$user_name."'";
            $result2=$adb->query($query2_login);
            $row2=$adb->fetch_array($result2);

            $password=$row2['password'];

           
            if($user_name=='lperez' || $user_name=='lforte')
            $pass = 'md5:' . md5($password);






			$sessionId = "";
			class variableStruct {
					  public $name;
					  public $value;
			}

			ini_set("soap.wsdl_cache_enabled", "0");
                        $client = new SoapClient('http://pm.timemanagement.es/sysworkflow/en/green/services/wsdl2');

			//$pass = 'md5:' . md5($password);
                        //v
                       // var_dump($pass);
			$params = array(array('userid'=>$user_name, 'password'=>$pass));
			$result = $client->__SoapCall('login', $params);
  if($result->message=='Wrong password'){
       $pass = 'md5:' . md5($password);
       $params = array(array('userid'=>$user_name, 'password'=>$pass));
			$result = $client->__SoapCall('login', $params);
  }
 
			if ($result->status_code == 0) {
						$sessionId = $result->message;




													$PMprocessID ='1019954654e60aa50525933016712321';
													//$PMcaseID = '7295733364cc185fcef73c7039713835';
													$PMcaseID=$guid;
                                                                                                        $PMdelIndex = '';

													$params = array(array('sessionId'=>$sessionId));
													$resultx = $client->__SoapCall('caseList', $params);
													$casesArray = $resultx->cases;

													if (count($casesArray) == 1) {

													if ($PMcaseID == $casesArray->guid)  {

													$PMdelIndex = $casesArray->delIndex;
													}
													} else {

														foreach ($casesArray as $key) {
													if ($PMcaseID == $key->guid)  {

													$PMdelIndex = $key->delIndex;
													}
													}
												}
													if ( ($PMcaseID) and ($PMdelIndex) ) {
													$aVars = array();

																 $obj = new variableStruct();
																 $obj->name = 'SYS_NEXT_USER_TO_BE_ASSIGNED';
																 $obj->value = $iduser;
																 $aVars[] = $obj;

													$params = array(array('sessionId'=>$sessionId, 'caseId'=>$PMcaseID, 'variables'=>$aVars));

													$resultsoap = $client->__SoapCall('sendVariables', $params);

													if ($resultsoap->status_code == 0) {
																$params = array(array('sessionId'=>$sessionId,  'caseId'=>$PMcaseID, 'delIndex'=>$PMdelIndex));
																$resultsoap1 = $client->__SoapCall('routeCase', $params);

													}
}

									} else {
											print "Unable to login to ProcessMaker.<br>Error Number: $result->status_code<br>Error Message: $result->message<br>";
									}

*/

								}

function finalizarProcesoInterno($ticketid,$vendorId){

            global $adb;

            $query="select iduser from vtiger_procesointerno where ticketid=".$ticketid;
            $row=$adb->fetch_array($adb->query($query));
            $iduser=$row['iduser'];

            $queryn="select user_name from vtiger_users where id=".$idJefe;
            $name=$adb->fetch_array($adb->query($queryn));
            $name=$name['user_name'];


            $query2="select user_name from vtiger_vendor inner join vtiger_users on vtiger_users.id=vtiger_vendor.user_id where vendorid=".$vendorId;
            $result2=$adb->query($query2);
            $row2=$adb->fetch_array($result2);
            $user_name=$row2['user_name'];

			/*
            $conex_proceso = mysql_connect('localhost:3306','timeuser','Eceptu.2011',true) or die("Error al intentar establecer la conexion de login.");
            $db_pros = mysql_select_db('wf_workflow2'_proceso);
            $pass="select USR_PASSWORD from USERS where USR_USERNAME='".$row2['user_name']."'";

            $rowpass=$adb->fetch_array($adb->query($pass_proceso));
            $pass=$rowpass['USR_PASSWORD'];
            $pass='md5:'.$pass;



            $queryt="select * from vtiger_ticketcf inner join vtiger_procesoap on vtiger_procesoap.labelProceso=vtiger_ticketcf.cf_689 where ticketid=".$ticketid;
            $rowt=$adb->fetch_array($adb->query($queryt));
            $guid=$rowt['guid'];
            $idprocesso=$rowt['idProceso'];
            //$password = 'md5:' . md5($password);

			




			$sessionId = "";
			class variableStruct {
					  public $name;
					  public $value;
			}

			ini_set("soap.wsdl_cache_enabled", "0");
                        $client = new SoapClient('http://pm.timemanagement.es/sysworkflow/en/green/services/wsdl2');

			//$pass = 'md5:' . md5($password);
                        //v
                       // var_dump($pass);
			$params = array(array('userid'=>$user_name, 'password'=>$pass));
			$result = $client->__SoapCall('login', $params);
			if ($result->status_code == 0) {
						$sessionId = $result->message;




													$PMprocessID = $idprocesso;
													//$PMcaseID = '7295733364cc185fcef73c7039713835';
													$PMcaseID=$guid;
                                                                                                        $PMdelIndex = '';

													$params = array(array('sessionId'=>$sessionId));
													$resultx = $client->__SoapCall('caseList', $params);
													$casesArray = $resultx->cases;

													if (count($casesArray) == 1) {

													if ($PMcaseID == $casesArray->guid)  {

													$PMdelIndex = $casesArray->delIndex;
													}
													} else {

														foreach ($casesArray as $key) {
													if ($PMcaseID == $key->guid)  {

													$PMdelIndex = $key->delIndex;
													}
													}
												}
													if ( ($PMcaseID) and ($PMdelIndex) ) {
													$aVars = array();

																 $obj = new variableStruct();
																 $obj->name = 'SYS_NEXT_USER_TO_BE_ASSIGNED';
																 $obj->value = $iduser;
																 $aVars[] = $obj;

													$params = array(array('sessionId'=>$sessionId, 'caseId'=>$PMcaseID, 'variables'=>$aVars));

													$resultsoap = $client->__SoapCall('sendVariables', $params);

													if ($resultsoap->status_code == 0) {
																$params = array(array('sessionId'=>$sessionId,  'caseId'=>$PMcaseID, 'delIndex'=>$PMdelIndex));
																$resultsoap1 = $client->__SoapCall('routeCase', $params);

													}
}

									} else {
											print "Unable to login to ProcessMaker.<br>Error Number: $result->status_code<br>Error Message: $result->message<br>";
									}
					*/


								}

function comprobarSistemaInterno($ticketid){
    global $adb;
    $query="select * from vtiger_procesointerno where ticketid=".$ticketid;
    $result=$adb->query($query);
    $cant=$adb->num_rows($result);
    if($cant>0)
        $interno='si';
    else
        $interno='no';
    return $interno;
}
function esTesting($ticket,$vendor){
    global $adb;
    $rol=obtenerRolUser($vendor);
    $respuesta="no";
    if($rol=='H28'){
        $sql="select * from vtiger_solicitudtesting where ticketid=".$ticket;
        $resul=$adb->query($sql);
        $cant=$adb->num_rows($resul);
    
    if($cant>0)
        $respuesta="si";
    }

    return $respuesta;
}
function esExpress($idcrm){
    global $adb;
    $sql="select cf_689 from vtiger_ticketcf where ticketid=".$idcrm;
    $cf=$adb->fetch_array($adb->query($sql));
    $express=$cf['cf_689'];
    $respuesta='no';
    if($express=='Incidencia Express')
        $respuesta='si';
    return $respuesta;
}
function esDoc($idcrm){
    global $adb;
    $sql="select cf_689 from vtiger_ticketcf where ticketid=".$idcrm;
    $cf=$adb->fetch_array($adb->query($sql));
    $express=$cf['cf_689'];
    $respuesta='no';
    if($express=='Documentacion')
        $respuesta='si';
    return $respuesta;
}

function procesarDatos($datos) {
	global $adb;
	$ticketid = $datos['ticketid'];
	$puntos = explode(',',$datos['puntosTicket']);
	if (!empty($datos["coment_ticket_$ticketid"]) and (isset($datos["nota_ticket_$ticketid"])) and !empty($datos['postVendorid']) and !empty($ticketid)) {
		$vendorid = $datos['postVendorid'];
		$nota = $datos["nota_ticket_$ticketid"];


		$hoy = date('Y-m-d');
		$hora= date('H');

		if($hora>=0 and $hora<=5)
		{
			$hoy = date( "Y-m-d", strtotime( "-1 day", strtotime( $hoy ) ) );
		}

        $user_id=$_SESSION['authenticated_user_id'];

		$comentario = $datos["coment_ticket_$ticketid"];
		$sql ="INSERT INTO vtiger_diarynotes ( date , coment , note , ticketid , desarrollador_id,user_evaluador)

				VALUES ( '$hoy','$comentario', $nota , $ticketid , $vendorid ,$user_id)

				";
		$result = $adb->query($sql);
                $fintarea='si';
				

		foreach ($puntos as $key => $value) {
			if (isset($datos["descripcion_punto_$value"])  and isset($datos["date_punto_$value"])  and isset($datos["porcentaje_punto_$value"]) ){
				if ($datos["porcentaje_punto_$value"] == 100) {


					$extraSet = ", enddate = '".date('Y-m-d')."' , state = 'Finalizado'";
				}
				else{
                                    $fintarea='no';
					$extraSet ="";
				}

				guardarLogsPorcentaje ($datos["porcentaje_punto_".$value."_old"] , $datos["porcentaje_punto_$value"] , $value);
				$modFecha=desFormatearFecha($datos["date_punto_$value"]);
				$sql ="UPDATE vtiger_ticketpuntos
						SET description= '".$datos["descripcion_punto_$value"]."' , porcentaje=".$datos["porcentaje_punto_$value"]." , date='".$modFecha."' $extraSet
						WHERE
						pointid =$value";
				$result = $adb->query($sql);

				$timeFecha = strtotime($modFecha);
				if ($mayorFecha < $timeFecha){
					$mayorFecha = $timeFecha;
				}

			}

		}

                if($fintarea=='si'){


                                        if($interno=='si')
                                        finalizarProcesoInterno($ticketid,$vendorid);
                                        else{
                                            
                                        $test=esTesting($ticketid,$vendorid);
   
                                       if($test=="si")
                                        {
                                         $testFuncional=esTestFuncional($ticketid);
                                         if($testFuncional=='si')
                                            
                                             finalizaProcesoTestFuncional($ticketid,$vendorid);
                                         else
                                         finalizarProcesoTest($ticketid,$vendorid);   
                                        }   
                                        else{
                                        $express=esExpress($ticketid);
                                        $doc=esDoc($ticketid); 
                                        if($express=='si')
                                         finalizarProcesoExpress($ticketid,$vendorid);   
                                        elseif($doc=='si')
                                         finalizarProcesoDoc($ticketid,$vendorid); 
                                        else
                                        finalizarProceso($ticketid,$vendorid);
                                        }
                                        }
                }

										

		updateFechasTicket(date('Y-m-d',$mayorFecha) , $ticketid);
		
								


	}
	else	{
		echo '<div align="center"><font color="red">ERROR: Los campos Nota final del dia y Comentario son requeridos.</font></div>';
	}

}


function guardarLogsPorcentaje ($porcentaje1 , $porcentaje2 , $puntoid) {
	$hoy = date('Y-m-d');
        global $adb;
	if ($porcentaje1 < $porcentaje2) {
		$sql="INSERT INTO vtiger_log_cierre_diario ( puntoid , porcentaje_inicio , porcentaje_fin , date)

				VALUES ( $puntoid , $porcentaje1 , $porcentaje2 , '$hoy' )

				";
		$query = $adb->query($sql);

	}
}


function calcularTiempoTicket($vendorid,$ticketid,$hoy){
	$tiempo = 0;
        global $adb;
	$sql  ="SELECT inicio , fin
			FROM vtiger_log_tiempo_ticket
			WHERE
			 ticketid = $ticketid
			and desarrollador_id = $vendorid
			and inicio BETWEEN '$hoy 00:00:01' and '$hoy 23:59:59'


	";
	$result = $adb->query($sql);
	while ($reg = $adb->fetch_array($result)){
		$enddate = $reg['fin'];
		if ($enddate == '0000-00-00 00:00:00'){
		$enddate=date('Y-m-d H:i:s');
		}
		$diferencia = diferenciaEntreFecha($reg['inicio'],$enddate);
		$tiempo = $tiempo+$diferencia;
	}
	if ($tiempo > 0){
		return formatearSegundos($tiempo, $pad_hrs = FALSE);
	}
	else{
		return ' - ' ;
	}

}

function formatearSegundos($seconds, $pad_hrs = FALSE) {
$o = '';
$hrs = intval(intval($seconds) / 3600);
$o .= ($pad_hrs) ? str_pad($hrs, 2, '0', STR_PAD_LEFT) : $hrs;
$o .= ':';
$mns = intval(($seconds / 60) % 60);
$o .= str_pad($mns, 2, '0', STR_PAD_LEFT);
$o .= ':';
$secs = intval($seconds % 60);
$o .= str_pad($secs, 2, '0', STR_PAD_LEFT);
return $o;
}


function diferenciaEntreFecha ($primera,$segunda) {
$start = strtotime($primera);
$end = strtotime($segunda);

$data = $end - $start;
return $data;
}

function obtenerTicketsACerrarDesarrollador ($vendorId,$fecha) { 
	global $adb;
	
	if (!empty($vendorId)) {
		$year = date('Y');
		$month = date('m');
		$day = date('d');
		$dateSemanaAtras = date('Y-m-d',mktime(0,0,0,$month,$day,$year)-(7*24*60*60));
		$sql = "SELECT rel.idticket

				FROM vtiger_vendor v
				left join vtiger_reldesa rel on  rel.idvendor=v.vendorid
				left join vtiger_ticketpuntos pts on pts.ticketid=rel.idticket
				left join vtiger_crmentity crm on rel.idticket=crm.crmid
								
				WHERE v.vendorid = $vendorId
				and crm.deleted=0
				and pts.desarrollador_id = v.vendorid
				and (pts.date BETWEEN '$fecha' and '$fecha')
				group by rel.idticket

				UNION
				SELECT rel.idticket

				FROM vtiger_vendor v
				left join vtiger_reldesa rel on  rel.idvendor=v.vendorid
				left join vtiger_ticketpuntos pts on pts.ticketid=rel.idticket
				left join vtiger_crmentity crm on rel.idticket=crm.crmid
								
				WHERE v.vendorid = $vendorId
				and crm.deleted=0
				and pts.desarrollador_id = v.vendorid
				and pts.date > '".$dateSemanaAtras."' and pts.date < '$fecha' and pts.porcentaje < 100
				group by rel.idticket
			 ";
		 
       
		$result = $adb->query($sql);
		$i = 0;
		if ($result && $adb->num_rows($result) > 0) {
			while ($reg = $adb->fetch_array($result)){
				$retornar[$i] = $reg['idticket'];
				$i++;
			}
		}
	}
	return $retornar;
}

function obtenerIDdesarrolladorFromUserID($userid) {
	
	global $adb;

	$sql = "SELECT vendorid, vendorname  FROM vtiger_users left join vtiger_vendor on user_id=id
	where id='$userid' ";

	$result = $adb->query($sql);

	if ($result) {
		$rowVendor = $adb->fetch_array($result);
		return $rowVendor;
	}
}

function obtenerDatosControlDiario($ticketid,$vendorid,$fecha) {
	global $adb;
	
	$sql = "SELECT * FROM vtiger_diarynotes_desarrolladores 
				WHERE date = '".$fecha."' AND ticketid = ".$ticketid." AND desarrollador_id = ".$vendorid;
				
	$result = $adb->query($sql);

	if ($result) {
		$rowControlDiario = $adb->fetch_array($result);
		return $rowControlDiario;
	}
}

	function informacionPuntosPendientes($ticketid,$vendorId,$tipo,$j) {
		$displayPuntos='none';
		global $adb;
		$bufferSalida = '';
        //$tipo=$_REQUEST['tipo_tarea'];
		$sql = "SELECT * FROM vtiger_ticketpuntos WHERE porcentaje != 100
													AND ticketid = ".$ticketid."
													AND desarrollador_id = ".$vendorId."
													ORDER BY date	asc ";

		$result = $adb->query($sql);
		$noofrows = $adb->num_rows($result);

		$bufferSalida = '
		<table width="100%" cellspacing="0" cellpadding="0" border="0" class="small">
		<tbody>
		<tr style="height: 25px;" >
		<td class="dvtCellInfo" style="background-color:#DCDCDC;">No.</td>
		<td class="dvtCellInfo"  width="50%" style="background-color:#DCDCDC;">Descripci&oacute;n</td>
		<td class="dvtCellInfo" style="background-color:#DCDCDC;">% Avance</td>
		<!--
		<td class="dvtCellInfo" style="background-color:#DCDCDC;">Horas dedicadas</td>
		-->
		<td class="dvtCellInfo" style="background-color:#DCDCDC;">Fecha estimada</td>
		<!--
		<td class="dvtCellInfo" style="background-color:#DCDCDC;">Estado</td>
		-->

		</tr>
		';
		$puntosJavascript=array();
		for($i=0;$i<$noofrows;$i++) {

			$_row = $adb->fetch_array($result);
			$pointid = $_row["pointid"];
			array_push($puntosJavascript,$pointid);
			$description = $_row["description"];
			$date = $_row["date"];

			$porcentaje = $_row["porcentaje"];
			//$horas_dedicadas = $_row["horas_dedicadas"];
			$horas_dedicadas = 0;
			$styleColor = '';
			$bufferSalida.= '
			<tr style="height: 25px;">
				<td class="dvtCellInfo"  '.$styleColor.'>'.($i+1).'<input type="hidden" value="'.$pointid.'" name="pointid'.$j.'[]"/></td>
				<td class="dvtCellInfo" width="50%"  '.$styleColor.'>'.utf8_encode($description).'</td>
				<td class="dvtCellInfo"  '.$styleColor.'>'.escribeComboPorcentaje(number_format($porcentaje,0),$j).'</td>
				<td class="dvtCellInfo"  '.$styleColor.'>'.escribeEntradaFecha($pointid,'date',$date).'</td>';
				/*
				<td class="dvtCellInfo" align="center"  '.$styleColor.'>';
				// aca van los botones
				
				$estadoPunto = estadoPunto($pointid,$_REQUEST['desarrollador']);
				$bufferSalida.="<img style=\"display:none;\" id=\"gif_$pointid\" src=\"http://lh3.googleusercontent.com/-tNTz7LGT6Ro/TnMySc6ayrI/AAAAAAAABlU/ApkZKIWnX5o/Loading.gif\" alt=\"Cargando\" width=\"20\" height=\"20\" />";
				if ($estadoPunto ==1){
					$displayPuntos = 'block';
					$bufferSalida.= "<input class=\"crmbutton small delete\" type=\"button\" value=\"  Detener  \" name=\"estado_$pointid\" id=\"estado_$pointid\" onClick=\"cambiarEstado('0','$pointid');\">";

				}else{
					$bufferSalida.= "<input class=\"crmbutton small edit\" type=\"button\" value=\"Comenzar\" name=\"estado_$pointid\" id=\"estado_$pointid\" onClick=\"cambiarEstado('1','$pointid');\">";
				}
				
				$bufferSalida.= '</td>';
				*/
				$bufferSalida.= '
			</tr>
			';
		}
		$bufferSalida.= '
				</tbody>
				</table>
			';

		$return['html']=$bufferSalida;
		$return['puntosJavascript']=$puntosJavascript;
		$return['estado'] = $displayPuntos;
		return $return;
	}
	
	function escribeComboPorcentaje($valor,$id = '') {



          if( empty($valor)){
                $valor=110;
          }                           
									

		$bufferSalida = '
		<select id="porcentaje'.$id.'" name="porcentaje'.$id.'[]"   class="small">
		<option  selected="selected"  value="-">-</option>';


		for($i=0;$i<=100;$i+=10) {
			$selected = '';
			if ($valor == $i)
				$selected = 'selected="selected"';

			$bufferSalida.= '<option value="'.$i.'" '.$selected.'>'.$i.'</option>';
		}

		$bufferSalida.= "</select>";
                                    
		return $bufferSalida;
	}
	
	function escribeComboHoras($valor,$noPlanificado = false,$parametros = '',$limit=18) {

		$sufijoCampo = '';
		
		if ($noPlanificado)
			$sufijoCampo = '_noplat';
			
		$bufferSalida = '<select id="horas_dedicadas[]" name="horas_dedicadas'.$sufijoCampo.'[]"   class="small" '.$parametros.'>';
		if(empty($valor))
		{
			$selected = 'selected="selected"';
			$valor=24;
		}	
		$bufferSalida.= '<option value="-" '.$selected.'  >-</option>';

		for($i=0;$i<=$limit;$i+=0.25) {
			$selected = '';
			if ($valor == $i)
			{ 
				
				$selected = 'selected="selected"';
			}
			
			$bufferSalida.= '<option value="'.$i.'" '.$selected.'>'.number_format($i,2).'</option>';
		}

		$bufferSalida.= "</select>";
                                    
		return $bufferSalida;
	}
	
	function escribeEntradaFecha($id,$name,$valor) {
		if (!empty($valor)) {
			list($y,$m,$d) = explode("-",$valor);
			$valorFormateado = date("d-m-Y",mktime(0,0,0,$m,$d,$y));
		}



	//	$disabled='disabled="disabled"';
		if($rol=="H2" or $rol=="H8")
		{
			$disabled='';
		}
		$bufferSalida = $valorFormateado;
                                   /* $bufferSalida ='$valorFormateado
		<input type="text" '.$disabled.'  value="'.$valorFormateado.'" maxlength="10" disabled size="11" style="border: 1px solid rgb(186, 186, 186);" id="jscal_field_'.$name.'_'.$id.'" tabindex="" name="'.$name.'[]">
		<img id="jscal_trigger_'.$name.'_'.$id.'" src="../../themes/softed/images/btnL3Calendar.gif">
		<script id="massedit_calendar_date_'.$id.'" type="text/javascript">
			Calendar.setup ({
				inputField : "jscal_field_'.$name.'_'.$id.'", ifFormat : "%d-%m-%Y", showsTime : false, button : "jscal_trigger_'.$name.'_'.$id.'", singleClick : true, step : 1
			})
		</script>
		';
                                      */
		return $bufferSalida;
	}
	
	
	
	function obtenerIDUserFromDesarrollador($desarrolladorID) {
			global $adb;

			$sql = "SELECT user_id  FROM vtiger_vendor WHERE vendorid = '$desarrolladorID' ";

			$result = $adb->query($sql);

			if ($result) {
				$rowVendor = $adb->fetch_array($result);
				return $rowVendor['user_id'];
			}

	}
	
	function determinarSiHorasRegistradasMayorHorasTrabajadas($date,$ticketid,$desarrolladorid) {
		$horaInicial = obtenerHoraPrimerLogueo($desarrolladorid,true);
		$horaFinal = mktime(date('H'),date('i'),date('s'),date('m'),date('d'),date('Y'));
		
		$horas = ceil(($horaFinal - $horaInicial)/(60*60));
		
		$horasRegistradas = determinarHorasDedicadas($date,$desarrolladorid);
		$row = existeRegistroDesarrollador($date,$ticketid,$desarrolladorid);
		
		if ($row) {
			$horasRegistradas-= $row['horas_dedicadas'];
			
			for($i = 0;$i < count($_REQUEST['horas_dedicadas']);$i++)
				$horasRegistradas+= $_REQUEST['horas_dedicadas'][$i];
				
			for($i = 0;$i < count($_REQUEST['horas_dedicadas_noplat']);$i++)
				$horasRegistradas+= $_REQUEST['horas_dedicadas_noplat'][$i];
		}
		
		
		
		if ($horas > $horasRegistradas)
			return false;
			
		return true;
	}
	
	function determinarHorasDedicadas($date,$desarrolladorid) {
		global $adb;
		
		$query = "SELECT SUM(horas_dedicadas) as horas_dedicadas FROM vtiger_diarynotes_desarrolladores WHERE date = '".$date."' AND desarrollador_id = ".$desarrolladorid;
		
		$result = $adb->query($query);
		
		if ($result) {
			$row = $adb->fetch_array($result);
			
			return $row['horas_dedicadas'];
		}
		return 0;
	}

	function existeRegistroDesarrollador($date,$ticketid,$desarrolladorid) {
		global $adb;
		
		$sql = "SELECT * FROM vtiger_diarynotes_desarrolladores WHERE date = '".$date."' AND ticketid = ".$ticketid." AND desarrollador_id = ".$desarrolladorid;
		
		$result = $adb->query($sql);
		
		if ($result) {
			$row = $adb->fetch_array($result);
			
			return $row;
		}
		return false;
	}
	
	function guardarRegistroTrabajoNoPlanificado($user,$horas_dedicadas,$descripcion,$titulo,
													$parent_id,$prioridad,$nivel,$confirmada,$coment_desarrollador){
        
		global $adb;
		
        $modifiedtime = date("Y-m-d H:i:s");

        $sql3="select id from vtiger_crmentity_seq";
        $id=$adb->fetch_array($adb->query($sql3));
        $id=$id['id'];
        $id++;

        $sql2 = "UPDATE vtiger_crmentity_seq SET id = ".$id;
		$re2=$adb->query($sql2);
		
        $sql4="SELECT prefix,cur_id FROM vtiger_modentity_num where num_id=6";
		$result = $adb->query($sql4);
		$fila = $adb->fetchByAssoc($result);

        $sql5="UPDATE vtiger_modentity_num SET cur_id=cur_id+1 where num_id=6";
		$result = $adb->query($sql5);
        $ticket_no=$fila['prefix'].($fila['cur_id']+1);

        $sql = "INSERT INTO vtiger_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime)
						VALUES(".$id.",".$user.",".$user.",'HelpDesk','".utf8_decode(mysql_real_escape_string($descripcion))."','".$modifiedtime."','".$modifiedtime."')";
						
		$re2=$adb->query($sql);
		
		//Se cambia el estado de la tarea finalizada por el desarrollador a TICKET_TO_VALIDATE
		
        //$sql_t="insert into vtiger_troubletickets(ticketid,ticket_no,start_date,parent_id,status,title,prioridad,nivel,end_estimated_date,confirmada,type,customerdescription) 
		//		VALUES (".$id.",'".$ticket_no."','".date('Y-m-d')."','".$parent_id."','".TICKET_PENDING_CONFIRMATION_OF_CUSTOMER."','".$titulo."','".$prioridad."','".$nivel."','".date('Y-m-d')."','".$confirmada."','".$_REQUEST['tipo']."','".$descripcion."')";
	
		$sql_t="INSERT INTO vtiger_troubletickets(ticketid,ticket_no,start_date,parent_id,status,title,prioridad,nivel,end_estimated_date,confirmada,type,customerdescription) 
				VALUES (".$id.",'".$ticket_no."','".date('Y-m-d')."','".$parent_id."','".TICKET_TO_VALIDATE."','".$titulo."','".$prioridad."','".$nivel."','".date('Y-m-d')."','".$confirmada."','".$_REQUEST['tipo']."','".$descripcion."')";
				
		$re2=$adb->query($sql_t);
		
        $sql_cf="insert into vtiger_ticketcf (ticketid,cf_689,preticket) values(".$id.",'Incidencia Express',0)";
		$re=$adb->query($sql_cf);
		
		//Se obtiene el id del usuario como desarrollador
		$sql_desarrollador = "SELECT vendorid FROM vtiger_vendor WHERE user_id = ".$user;
		$result = $adb->query($sql_desarrollador);
		
		if ($result) {
			$row = $adb->fetch_array($result);
			
			$desarrolladorid = $row['vendorid'];
		}
        
		$sql_puntos = "INSERT INTO vtiger_ticketpuntos (pointid,ticketid,description,date,enddate,porcentaje,state,desarrollador_id,horasest,tipo)
						VALUES (NULL,$id,'Realizar lo indicado','".date('Y-m-d')."','".date('Y-m-d')."',100,'Finalizado',$desarrolladorid,0,2)";
				
		$result = $adb->query($sql_puntos);
		
		//$pointid = mysql_insert_id($conex);
		$pointid = $adb->database->Insert_ID();
		
		$sql_control_diario = "INSERT INTO vtiger_diarynotes_desarrolladores (diarynoteid,date,coment,ticketid,desarrollador_id,horas_dedicadas)
								VALUES (NULL,'".date('Y-m-d')."','".nl2br(($coment_desarrollador))."',$id,$desarrolladorid,$horas_dedicadas)";
								
		$result = $adb->query($sql_control_diario);
		
		//Se incorpora el registro en el calendario
		$sql3="SELECT id FROM vtiger_crmentity_seq";
        $idcrmentity=$adb->fetch_array($adb->query($sql3));
        $idcrmentity=$idcrmentity['id'];
        $idcrmentity++;

        $sql2 = "UPDATE vtiger_crmentity_seq SET id = ".$idcrmentity;
		$re2=$adb->query($sql2);
		
		$fechad = date('Y-m-d h:i:s');

		$sql = "insert into vtiger_crmentity  (crmid,smcreatorid,smownerid,modifiedby,setype,createdtime,modifiedtime,presence,deleted)
		values('$idcrmentity','$user','$user','$user','Calendar','$fechad','$fechad','1','0');";

		$result = $adb->query($sql);

		$fecha = date('Y-m-d');
		$hora_ini='08:00';
		$hora_fin='08:59';

		$sql = "insert into vtiger_activity  (activityid,subject,activitytype,date_start,due_date,time_start,time_end,status,duration_hours,duration_minutes,eventstatus,priority,visibility,desarrollador_id,tipo_tarea)
			values('$idcrmentity','$titulo','Meeting','$fecha','$fecha','$hora_ini','$hora_fin','Not Started',0,59,'Planned','High','all','$desarrolladorid',2);";

		$result = $adb->query($sql);
							
		$sql = "insert into vtiger_activitycf   (activityid) values ($idcrmentity)";
		$result = $adb->query($sql);

		$sql = "insert into vtiger_seactivityrel  (crmid,activityid)
			values('$id','$idcrmentity');";

		$result = $adb->query($sql);
		
		if (empty($pointid))
			$pointid = -1;
		
		//Se ingresa informacion en el log de tiempo
		$sql = "INSERT INTO vtiger_log_tiempo_ticket (id,ticketid,inicio,fin,desarrollador_id,puntoid) 
					VALUES (NULL,$id,'".date('Y-m-d H:i:s')."','".date('Y-m-d H:i:s')."',$desarrolladorid,$pointid);";
		
		$result = $adb->query($sql);
		
		//Se indica el desarrollador
		$sql = "INSERT INTO vtiger_reldesa (idticket,idvendor,rol) VALUES(".$id.",".$desarrolladorid.",'Desarrollo')";
		$result = $adb->query($sql);
		
	}
	
	
function obtenerOrdenTrabajoAbiertas($vendorId,$tipoOTsIncluidos = '',$tipoOTsExcluidos = '',$regini = 0) { 
	global $adb;
	
	if (!empty($vendorId)) {
		$userid = getUserIdByVendorId($vendorId);
		$condicionTiposOTs = '';
		
		if (!empty($tipoOTsIncluidos)) {
			$condicionTiposOTs = " AND vtiger_ordentrabajo.otadminid IN (".$tipoOTsIncluidos.")";
		} else if (!empty($tipoOTsExcluidos)) {
			$condicionTiposOTs = " AND vtiger_ordentrabajo.otadminid NOT IN (".$tipoOTsExcluidos.")";
		} 
		
		$sql = "SELECT vtiger_ordentrabajo.ordentrabajoid,vtiger_troubletickets.title as pedidotitle, vtiger_troubletickets.ticketid, vtiger_ordentrabajo.description, vtiger_ordentrabajo_informes.horas,
					vtiger_ordentrabajo_informes.comentario, vtiger_account.accountname FROM vtiger_ordentrabajo INNER JOIN vtiger_crmentity 
					ON (vtiger_ordentrabajo.ordentrabajoid = vtiger_crmentity.crmid AND vtiger_crmentity.deleted = 0)
					LEFT JOIN vtiger_troubletickets 
					ON (vtiger_ordentrabajo.ticketid = vtiger_troubletickets.ticketid)
					LEFT JOIN vtiger_crmentity as crm2
					ON (vtiger_troubletickets.ticketid = crm2.crmid)
					LEFT JOIN vtiger_account
					ON (vtiger_troubletickets.parent_id = vtiger_account.accountid)
					LEFT JOIN vtiger_ordentrabajo_informes 
					ON (vtiger_ordentrabajo.ordentrabajoid = vtiger_ordentrabajo_informes.ordentrabajoid AND vtiger_ordentrabajo_informes.fecha = ?)
					WHERE vtiger_troubletickets.status NOT IN (?,?) AND (crm2.deleted=0 or crm2.deleted is null) AND vtiger_crmentity.smownerid = ? AND (vtiger_ordentrabajo.statusot = ? OR vtiger_ordentrabajo.statusot = '-') AND vtiger_ordentrabajo.date <= ?
					".$condicionTiposOTs;
       
		$result = $adb->pquery($sql,array(date('Y-m-d'),TICKET_ACCEPTED,TICKET_PENDING_CONFIRMATION_OF_CUSTOMER,$userid,'En curso',date('Y-m-d')));
		$i = 0;
		if ($result && $adb->num_rows($result) > 0) {
			while ($reg = $adb->fetch_array($result)){
				$reg['i'] = $i+$regini;
				$reg['pendingtask'] = getPendingTask($reg['ordentrabajoid']);
				$reg['comentario'] = decode_html($reg['comentario']); 
				$reg['comentario'] = str_replace( '<br/>', "\n", $reg['comentario'] ); 
				
				$retornar[] = $reg;
				$i++;
			}
		}
	}
	return $retornar;
}

function obtenerOrdenTrabajoCerradas($vendorId,$date = 'Y-m-d') { 
	global $adb;
	
	if (!empty($vendorId)) {
		$userid = getUserIdByVendorId($vendorId);
		
		$sql = "SELECT vtiger_ordentrabajo.ordentrabajoid,vtiger_troubletickets.title as pedidotitle, vtiger_troubletickets.ticketid, vtiger_ordentrabajo.description, vtiger_ordentrabajo_informes.horas,
					vtiger_ordentrabajo_informes.comentario, vtiger_account.accountname FROM vtiger_ordentrabajo INNER JOIN vtiger_crmentity 
					ON (vtiger_ordentrabajo.ordentrabajoid = vtiger_crmentity.crmid AND vtiger_crmentity.deleted = 0)
					INNER JOIN vtiger_troubletickets 
					ON (vtiger_ordentrabajo.ticketid = vtiger_troubletickets.ticketid)
					INNER JOIN vtiger_account
					ON (vtiger_troubletickets.parent_id = vtiger_account.accountid)
					LEFT JOIN vtiger_ordentrabajo_informes 
					ON (vtiger_ordentrabajo.ordentrabajoid = vtiger_ordentrabajo_informes.ordentrabajoid)
					WHERE vtiger_crmentity.smownerid = ? AND vtiger_ordentrabajo.statusot = ? AND vtiger_ordentrabajo_informes.fecha = ?";
       
		$result = $adb->pquery($sql,array($userid,'Terminado',date('Y-m-d')));
		$i = 0;
		if ($result && $adb->num_rows($result) > 0) {
			while ($reg = $adb->fetch_array($result)){
				$reg['i'] = $i;
				$retornar[] = $reg;
				$i++;
			}
		}
	}
	return $retornar;
}

function getPendingTask($id) {
	global $adb;
	
	$sql = "SELECT title, description, todotasksid FROM vtiger_crmentityrel 
				INNER JOIN vtiger_todotasks ON (vtiger_crmentityrel.crmid = vtiger_todotasks.todotasksid AND executed = 0)
				WHERE relcrmid = ? AND (module = 'todotasks' OR relmodule = 'todotasks')";
				
	$result = $adb->pquery($sql,array($id));
	
	$i = 0;
	if ($result && $adb->num_rows($result) > 0) {
		while ($reg = $adb->fetch_array($result)){
			$reg['description'] = html_entity_decode($reg['description']);
			$reg['title'] = html_entity_decode($reg['title']);
			$retornar[] = $reg;
		}
	}
	return $retornar;
}

function actualizar_estado($ticketid)
{
    global $adb;
	//Se cambia el estado de la tarea finalizada por el desarrollador a TICKET_TO_VALIDATE
	
    //$sql = "UPDATE vtiger_troubletickets SET status = '".TICKET_PENDING_CONFIRMATION_OF_CUSTOMER."'
	$sql = "UPDATE vtiger_troubletickets SET status = '".TICKET_TO_VALIDATE."'
						WHERE ticketid = $ticketid ";
						
	$result = $adb->query($sql,$conex);
	
	enviaNotificacionCambioEstado($ticketid);
	enviaNotificacionCooordinador($ticketid);
}

function actualizar_horas_trabajo($ticketid,$horas)
{
    global $adb;
	 if ($ticketid && $horas != '-') {
	 	$sql = "UPDATE vtiger_troubletickets
						SET work_hours = work_hours+$horas
						WHERE ticketid = $ticketid ";
		$result = $adb->query($sql,$conex);
	 }

}

function determinarTipoRegistro($ticketid){
	global $adb;
	$query="SELECT  type FROM vtiger_troubletickets where ticketid=".$ticketid;
	$result=$adb->query($query,$conex);

	if ($result) {
		$row=$adb->fetch_array($result);
		return $row['type'];
	}
	return;
}

function procesarDatosOTs($datos,$fecha) {
	global $current_user,$adb;
	$ticketid = $datos['ticketid'];
	$ordentrabajoid = $datos['ordentrabajoid'];
	$coordinadorid = getVendorId($current_user->id);
	if (!empty($datos["coment_ticket_$ordentrabajoid"]) and (isset($datos["nota_ticket_$ordentrabajoid"])) and !empty($datos['postVendorid']) and !empty($ticketid)) {
		$sql = "UPDATE vtiger_ordentrabajo_informes SET nota = ?, coordinadorid = ?, comentario_coordinador = ? WHERE ordentrabajoid = ? AND fecha = ?";
		
		$adb->pquery($sql,array($datos["nota_ticket_$ordentrabajoid"],$coordinadorid,$datos["coment_ticket_$ordentrabajoid"],$ordentrabajoid,$fecha));
	}
	else	{
		echo '<div align="center"><font color="red">ERROR: Los campos Nota final del dia y Comentario son requeridos.</font></div>';
	}

}

?>