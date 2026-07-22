<?php

require_once('Smarty_setup.php');
include_once("include/utils/utils.php");
include_once('include/utils/comunesTareas.php');
global $adb,$theme;


function separarPalabra($string){// si una palabra es mas larga de 12 caracteres la corta cada 11 caracteres y le agrega un guion
	$leng=strlen($string);
	if ($leng > 12)
		$return =substr($string,0,11).'- '.separarPalabra(substr($string,12,$leng - 11));
	else
		$return = $string;
	return $return;
}


function botonAdjuntar($idcrm,$cuenta,$user_id){
global $adb;
$sql="select preticket from vtiger_ticketcf where ticketid=".$idcrm;

$row=$adb->fetch_array($adb->query($sql));
$preticket=$row['preticket'];
$sessidhash = base64_encode(base64_encode("$cuenta:".$preticket.":$user_id:PM"));

$salida='<input  type="button" name="documentos" id="documentos" class="crmbutton small create"  value="Documentaci&oacute;n" onclick="window.open(\'http://documentos.timemanagement.es/index.php?sessid='.$sessidhash.'\',\'Documentos\',\'width=400,height=500,status=no,toolbar=no,menubar=no,location=no\')";>';
return $salida;

}

function cortarPalabras($string){
	$palabras = explode(' ',$string);
	foreach ($palabras as $key => $value){
		$temp = trim($value,' ');
		if (!empty($value)){
			$return.=separarPalabra($temp).' ';
		}
	}

return $return;
}
function diaSemana($dia) { // devuelve array con la fecha de hoy y la de ayer (en caso de lunes ayer es viernes)s
	if (empty($dia)){
		$dia = date('Y-m-d');
	}
	$fecha= strtotime($dia);
	$dias = array('0', '1', '2', '3', '4', '5', '6');
	$numero=date("w", $fecha);
	$numero2=$fecha;
	switch ($numero)	{
		case 6:
		$eldia['segundo']=$numero2-(86400*1);
		$eldia['primero']=$numero2+(86400*2);
		break;
		case 5:
		$eldia['segundo']=$numero2-(86400*1);
		$eldia['primero']=$numero2+(86400*3);
		break;
		case 1:
		$eldia['segundo']=$numero2-(86400*3);
		$eldia['primero']=$numero2+(86400*1);
		break;
		case 0:
		$eldia['segundo']=$numero2-(86400*2);
		$eldia['primero']=$numero2+(86400*1);
		break;
		default:
		$eldia['segundo']=$numero2-(86400*1);
		$eldia['primero']=$numero2+(86400*1);
		break;
	}
	$eldia['hoy'] = $dia;
	$eldia['ayer'] = date('Y-m-d',$eldia['segundo']);
	$eldia['manana'] = date('Y-m-d',$eldia['primero']);
	return $eldia;

}

function esCEO($userId){
	$sql = "SELECT  count(roleid) as cant
			FROM vtiger_user2role r
			left join vtiger_users u on r.userid=u.id
			left join vtiger_crmentity crm on u.id=crm.crmid

			where crm.deleted=0
			and roleid like 'H2'
			and u.id = $userId
		 ";
	$result = $adb->query($sql);
$reg = $adb->fetch_array($result);
	if ($reg['cant'] > 0){
		$data = TRUE;
	}
	else {
		$data = FALSE;
	}
	return $data;
}


function calcularTiempoTicket($vendorid,$ticketid,$hoy){
	global $adb;
	$tiempo = 0;
	$sql  ="SELECT horas_dedicadas FROM vtiger_diarynotes_desarrolladores
			WHERE ticketid = $ticketid AND desarrollador_id = $vendorid
			AND date = '$hoy'
	";
	
	
	$result = $adb->query($sql);
	if ($reg = $adb->fetch_array($result)){
		$tiempo = $reg['horas_dedicadas']*60*60;
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



function obtenerIDdesarrollador($username) {
		global $adb;

		$sql = "SELECT vendorid  FROM vtiger_users
		left join vtiger_vendor on user_id=id

		where user_name='$username' ";

		$result = $adb->query($sql);

		if ($result) {
			$rowVendor = $adb->fetch_array($result);
			return $rowVendor['vendorid'];
		}

}


	function obtenerNombreDesarrollador($fecha = '') {
		global $adb;
		
		$vendorType = obtenerValorVariable('TASK_VENDOR_TYPE','Vendors');

		$sql = "SELECT v.vendorid,v.vendorname,color FROM vtiger_vendor v

					left join vtiger_crmentity on v.vendorid=crmid
					left join vtiger_vendorcf vcf on vcf.vendorid=v.vendorid
					 where
					vendortype='$vendorType' and
					deleted=0 order by vendorname ";
					
		
		$result = $adb->query($sql);
		$i=0;
		while ($rowVendor =$adb->fetch_array($result)) {
			$Vendor[$i]['id']=$rowVendor['vendorid'];
			$Vendor[$i]['nombre']=$rowVendor['vendorname'];
			$Vendor[$i]['color']=$rowVendor['color'];
			
			$registros = obtenerTicketsCerrados ($Vendor[$i]['id'],$fecha);
			
			if (is_array(($registros))) {
				foreach ($registros as $key => $value) {
					$Vendor[$i]['registros'][] = obtenerDatosOT($value,$fecha);
				} 
			}
			
			$i++;

		}
		$Vendor['cantidad']=$i-1;
		return $Vendor;
	}









	function informacionPuntosPendientes($idregistro,$iddesarrollador) {
		global $adb;
		$bufferSalida = '';
		$sql = "SELECT * FROM vtiger_ticketpuntos WHERE porcentaje != 100
													AND ticketid = $idregistro
													AND desarrollador_id =$iddesarrollador
													ORDER BY date	asc ";

		$result = $adb->query($sql);
		$noofrows = $adb->num_rows($result);

		$bufferSalida = '
		<table width="100%" cellspacing="0" cellpadding="0" border="0" class="small">
		<tbody>
		<tr style="height: 25px;" >
		<td class="dvtCellInfo" style="background-color:#DCDCDC;">No.</td>
		<td class="dvtCellInfo"  width="60%" style="background-color:#DCDCDC;">Descripci&oacute;n</td>
		<td class="dvtCellInfo" style="background-color:#DCDCDC;">% Avance</td>
		<td class="dvtCellInfo" style="background-color:#DCDCDC;">Fecha estimada</td>

		</tr>
		';
		for($i=0;$i<$noofrows;$i++) {
			$_row = $adb->fetch_array($result);
			$pointid = $_row["pointid"];
			$description = $_row["description"];
			$date = $_row["date"];

			$porcentaje = $_row["porcentaje"];
			$styleColor = '';
			$bufferSalida.= '
			<tr style="height: 25px;">
				<td class="dvtCellInfo"  '.$styleColor.'>'.($i+1).'<input type="hidden" value="'.$pointid.'" name="pointid[]"/></td>
				<td class="dvtCellInfo" width="60%"  '.$styleColor.'>'.mb_convert_encoding($description, 'UTF-8', 'ISO-8859-1').'</td>
				<td class="dvtCellInfo"  '.$styleColor.'>'.(number_format($porcentaje,0)).'</td>
				<td class="dvtCellInfo"  '.$styleColor.'>'.substr($date,8,2).'-'.substr($date,5,2).'-'.substr($date,0,4).'</td>

			</tr>
			';
		}
		$bufferSalida.= '
				</tbody>
				</table>
			';
		return $bufferSalida;
	}



	function informacionPuntosRealizados($idregistro,$iddesarrollador) {
		global $adb;
		$bufferSalida = '';
		$sql = "SELECT * FROM vtiger_ticketpuntos WHERE porcentaje = 100
													AND ticketid = $idregistro
													AND desarrollador_id = $iddesarrollador
													ORDER BY date	asc ";

		$result = $adb->query($sql);
		$noofrows = $adb->num_rows($result);

		$bufferSalida = '
		<table width="100%" cellspacing="0" cellpadding="0" border="0" class="small">
		<tbody>
		<tr style="height: 25px;" >
		<td class="dvtCellInfo" style="background-color:#DCDCDC;">No.</td>
		<td class="dvtCellInfo" style="background-color:#DCDCDC;">Descripci&oacute;n</td>
		<td class="dvtCellInfo" style="background-color:#DCDCDC;">% Avance</td>
		<td class="dvtCellInfo" style="background-color:#DCDCDC;">Fecha estimada</td>
		<td class="dvtCellInfo" style="background-color:#DCDCDC;">Fecha de realizaci&oacute;n</td>

		</tr>
		';
		for($i=0;$i<$noofrows;$i++) {
			$_row = $adb->fetch_array($result);
			$pointid = $_row["pointid"];
			$description = $_row["description"];
			$date = $_row["date"];
			$enddate = $_row["enddate"];
			$porcentaje = $_row["porcentaje"];
			$styleColor = '';
			$bufferSalida.= '
			<tr style="height: 25px;">
				<td class="dvtCellInfo"  '.$styleColor.'>'.$pointid.'</td>
				<td class="dvtCellInfo"  '.$styleColor.'>'.mb_convert_encoding($description, 'UTF-8', 'ISO-8859-1').'</td>
				<td class="dvtCellInfo"  '.$styleColor.'>'.number_format($porcentaje,0).'</td>
				<td class="dvtCellInfo"  '.$styleColor.'>'.substr($date,8,2).'-'.substr($date,5,2).'-'.substr($date,0,4).'</td>
				<td class="dvtCellInfo"  '.$styleColor.'>'.substr($enddate,8,2).'-'.substr($enddate,5,2).'-'.substr($enddate,0,4).'</td>

			</tr>
			';
		}
		$bufferSalida.= '
				</tbody>
				</table>
			';
		return $bufferSalida;
	}


	function informacionComentarioyNota($idregistro,$iddesarrollador) {
		global $adb;
		$bufferSalida = '';
		$sql = "SELECT *

		FROM vtiger_diarynotes

		where

		ticketid=$idregistro  and desarrollador_id=$iddesarrollador ";

		$result = $adb->query($sql);
		$noofrows = $adb->num_rows($result);

		$bufferSalida = '
		<table width="100%" cellspacing="0" cellpadding="0" border="0" class="small">
		<tbody>
		<tr style="height: 25px;" >
		<td class="dvtCellInfo" style="background-color:#DCDCDC;">Fecha</td>
		<td class="dvtCellInfo" style="background-color:#DCDCDC;">Comentario</td>
		<td class="dvtCellInfo" style="background-color:#DCDCDC;">Nota</td>

		</tr>
		';
		for($i=0;$i<$noofrows;$i++) {

			$_row = $adb->fetch_array($result);
			$fecha = substr($_row["date"],8,2).'-'.substr($_row["date"],5,2).'-'.substr($_row["date"],0,4);
			$description = $_row["coment"];

			$porcentaje = $_row["note"];
			$styleColor = '';
			$bufferSalida.= '
			<tr style="height: 25px;">
				<td class="dvtCellInfo"  '.$styleColor.'>'.$fecha.'</td>
				<td class="dvtCellInfo"  '.$styleColor.'>'.mb_convert_encoding($description, 'UTF-8', 'ISO-8859-1').'</td>
				<td class="dvtCellInfo"  '.$styleColor.'>'.$porcentaje.'</td>


			</tr>
			';
		}
		$bufferSalida.= '
				</tbody>
				</table>
			';
		return $bufferSalida;
	}
   function informacionComentario_desarrollador($idregistro,$iddesarrollador) {
		global $adb;
		$bufferSalida = '';
		$sql = "SELECT *

		FROM vtiger_diarynotes_desarrolladores

		where

		ticketid=$idregistro  and desarrollador_id=$iddesarrollador ";

		$result = $adb->query($sql);
		$noofrows = $adb->num_rows($result);

		$bufferSalida = '
		<table width="100%" cellspacing="0" cellpadding="0" border="0" class="small">
		<tbody>
		<tr style="height: 25px;" >
		<td class="dvtCellInfo" style="background-color:#DCDCDC;" width="6%">Fecha</td>
		<td class="dvtCellInfo" style="background-color:#DCDCDC;">Comentario</td>


		</tr>
		';
		for($i=0;$i<$noofrows;$i++) {

			$_row = $adb->fetch_array($result);
			$fecha = substr($_row["date"],8,2).'-'.substr($_row["date"],5,2).'-'.substr($_row["date"],0,4);
			$description = $_row["coment"];


			$styleColor = '';
			$bufferSalida.= '
			<tr style="height: 25px;">
				<td class="dvtCellInfo"  '.$styleColor.'>'.$fecha.'</td>
				<td class="dvtCellInfo"  '.$styleColor.'>'.mb_convert_encoding($description, 'UTF-8', 'ISO-8859-1').'</td>



			</tr>
			';
		}
		$bufferSalida.= '
				</tbody>
				</table>
			';
		return $bufferSalida;
	}

	global $adb;




function ObtenerRegistroDeTrabajo($desarrolladorid,$hoy)
{
	$ayer =$hoy;
	global $adb;
	$date=$hoy;
	if ($hoy == date('Y-m-d') or strtotime($hoy) < time()){
			$sqlInterno = "  	SELECT DISTINCT ticketid
								FROM  vtiger_diarynotes_desarrolladores
								WHERE date >= '$hoy 00:00:00' and date <='$hoy 23:59:59'
								and desarrollador_id = $desarrolladorid



			 ";
	}
	else{
		$sqlInterno = " SELECT DISTINCT ticketid
								FROM vtiger_ticketpuntos


								WHERE date BETWEEN '$hoy' and '$hoy'
								and desarrollador_id = $desarrolladorid
			";
	}
$sqlpor="SELECT t.ticketid,t.title,a.accountname,e.description,a.accountid

FROM vtiger_troubletickets t

left join vtiger_reldesa  r on r.idticket=t.ticketid
left join vtiger_crmentity e on e.crmid=t.ticketid
left join vtiger_ticketcf tcf on tcf.ticketid=t.ticketid
left join vtiger_account a on a.accountid=t.parent_id

where

r.idvendor=$desarrolladorid

and e.deleted=0

and t.ticketid IN( $sqlInterno )
";




	$resultpor=$adb->query($sqlpor);
//	$row = $adb->fetchByAssoc($resultpor);

return $resultpor;


}

function ObtenerOTs($vendorid,$hoy){
	global $adb;
	$ayer = $hoy;
	

	$sql = "SELECT t.ticketid,t.title,a.accountname,date_closed cierre,t.start_date  inicio,t.end_estimated_date  estimada, nota, horas

	FROM vtiger_troubletickets t
	INNER join vtiger_crmentity e on (e.crmid=t.ticketid AND e.deleted = 0)
	INNER join vtiger_ordentrabajo o on (t.ticketid = o.ticketid)
	INNER join vtiger_ordentrabajo_informes oi on (o.ordentrabajoid = oi.ordentrabajoid)
	left join vtiger_ticketcf tcf on tcf.ticketid=t.ticketid
	left join vtiger_account a on a.accountid=t.parent_id
	

	where
	oi.vendorid=$vendorid
	AND fecha = '$hoy'
	";
		
	$result = $adb->query($sql);
	
	return $result;
}


function listarTipoIncidencia($estado,$hoy)
{
	global $adb;
	$ayer =$hoy;

	$sql = "SELECT type

		FROM vtiger_type
where type !='-'
		 ";

		$result = $adb->query($sql);
		$noofrows = $adb->num_rows($result);
	if($estado=='Abierto')
	{
		$where="and createdtime BETWEEN '$ayer 00:00:01' and '$ayer 23:59:59' ";
	}
	else
	{
		$where="and date_closed BETWEEN '$ayer' and '$ayer' ";
	}



	for($i=0;$i<$noofrows;$i++) {

			$_row = $adb->fetch_array($result);

			$type = $_row["type"];

				$sql_ticket = "SELECT count(*) cantidad

								FROM vtiger_troubletickets t

								left join vtiger_crmentity crm on crmid= t.ticketid
								left join vtiger_ticketcf cf on cf.ticketid=t.ticketid

								where

								crm.deleted=0
								and type='$type'
								$where";

		$result_ticket = $adb->query($sql_ticket);
					$cantidad_ticket = $adb->fetch_array($result_ticket);
				$bufferSalida.= '
			<tr style="height: 25px;">';




			$bufferSalida.= '

				<td class="dvtCellInfo" width="60%" ><b>'.$type.'</b></td>
				<td   class="dvtCellInfo" align="center"  >'.$cantidad_ticket['cantidad'].'</td>
				 	<td   class="dvtCellInfo" align="center" width="6%" ><a href="javascript:void(0)" onclick="window.open(\'ver_incidencia.php?tipo='.$type.'&dia='.$hoy.'&estado='.$estado.'\',\'venta\',\'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=658, height=465, top=85, left=140\'); "><img src="'.vtiger_imageurl('lupa.png', $theme).'" border="0"></a></td>
			';

				$bufferSalida.= '</tr>';



		}

	return $bufferSalida;

}

function listarDesarrollador($hoy)
{
	global $adb;
	$ayer =$hoy;
	$vendorType = mb_convert_encoding(html_entity_decode(obtenerValorVariable('TASK_VENDOR_TYPE','Vendors')), 'UTF-8', 'ISO-8859-1');
	
	$sql = "SELECT  vendorname,v.vendorid,vendortype FROM vtiger_vendor v

		INNER JOIN vtiger_crmentity crm on (v.vendorid=crm.crmid AND deleted = 0)
		INNER JOIN vtiger_vendorcf vcf on vcf.vendorid=v.vendorid
		where vendortype=? order by vendorname
	 ";
	

	$result = $adb->pquery($sql,array($vendorType));
	$noofrows = $adb->num_rows($result);

	$where="and date_closed BETWEEN '$ayer' and '$ayer' ";

	for($i=0;$i<$noofrows;$i++) {

			$_row = $adb->fetch_array($result);

			$vendor_id = $_row["vendorid"];




				$bufferSalida.= '
			<tr style="height: 35px;">';




			$bufferSalida.= '

				<td class="dvtCellInfo" width="10%" ><b><a href="#tableDesarro'.$i.'" onclick="openDiv'."('divDesarro".$i."')".'">'.$_row["vendorname"].'</a></b></td>';

		$desarrollos=OTsDesarrollo($vendor_id,$hoy);

		$bufferSalida.= '<td width="83%"><table  cellpadding="0" cellspacing="0" width="100%" class="small">';
		$miniTabla = '';
		if (isset($desarrollos)) {
			foreach ($desarrollos as $key => $value) {
				$fechaCierre = formatearFecha($value['cierre']);
				if (!empty($fechaCierre)){
					$fechaCierre='<br><b>C:</b><font color="#093">'.$fechaCierre.'</font>';
				}
				$miniTabla.= '	<tr style="height: 40px;">
								<td width="10%"  class="dvtCellInfo" align="center"  >'.htmlentities(cortarPalabras($value['title'])).'<br>
								<a href="javascript:void(0)" onclick="window.open(\'index.php?module=Calendar&action=popupComentarios&Popup=true&v='.$vendor_id.'&t='.$value['ticketid'].'\',\'Comentarios\',\'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=658, height=465, top=85, left=140\'); "><img src="'.vtiger_imageurl('lupa.png', $theme).'" border="0"></a>

								</td>
								<td width="10%"  class="dvtCellInfo" align="center"  >'.htmlentities(cortarPalabras($value['accountname'])).'</td>
								<td width="46%"    >
								<table  cellpadding="0" cellspacing="0" width="100%" class="small">
											';
											
				$miniTabla.= tablaPuntos($value['ticketid'],$vendor_id,$hoy);
				
				if($value['nota']==0)
				{
					$value['nota']='-';
				}
				$miniTabla.= '
										</table>
								</td>
								<td width="8%"  class="dvtCellInfo" align="center"  >'.round($value['porcentaje'],0).'%</td>
								<td width="8%"  class="dvtCellInfo" align="center"  >'.round($value['horas'],2)/*calcularTiempoTicket($vendor_id,$value['ticketid'],$hoy)*/.'</td>
								<td width="10%"  class="dvtCellInfo" align="center"  ><b>I:</b>'.formatearFecha($value['inicio']).'<br><b>E:</b>'.formatearFecha($value['estimada']).$fechaCierre.'</td>
								<td width="8%"  class="dvtCellInfo" align="center"  >'.$value['nota'].'</td>
								</tr>

				';
			}
			}
			if ($miniTabla == ''){
				$miniTabla = '	<tr style="height: 40px;">
								<td width="10%"  class="dvtCellInfo" align="center"  > - </td>
								<td width="10%"  class="dvtCellInfo" align="center"  > - </td>
								<td width="46%"   >
								<table  cellpadding="0" cellspacing="0" width="100%" class="small">
											<tr style="height: 45px;">
												<td width="50%" class="dvtCellInfo" align="center"  ><b> - </b></td>
												<td width="25%" class="dvtCellInfo" align="center"  ><b> - </b></td>
												<td width="25%" class="dvtCellInfo" align="center"  ><b> - </b></td>
											</tr>
										</table>
								</td>
								<td width="8%"  class="dvtCellInfo" align="center"  > - </td>
								<td width="8%"  class="dvtCellInfo" align="center"  > - </td>
								<td width="10%"  class="dvtCellInfo" align="center"  > - </td>
								<td width="8%"  class="dvtCellInfo" align="center"  > - </td>
								</tr>';
			}
			$bufferSalida.= $miniTabla.'</table></td>';
			$bufferSalida.= '
					</tr>';

			


		}

	return $bufferSalida;

}

function tablaPuntos($ticketId,$vendor_id,$hoy){//acaestaelquery
	global $adb;
	$ayer = $hoy;
	if ($hoy == date('Y-m-d') or strtotime($hoy) < time()){
			$sql = " SELECT pointid , description , porcentaje , p.date , enddate

					FROM vtiger_ticketpuntos p

					where ticketid = $ticketId
					and desarrollador_id = $vendor_id
					and
						ticketid IN (
										SELECT ticketid
										FROM vtiger_diarynotes_desarrolladores l
										WHERE date >= '$hoy 00:00:00' and date <='$hoy 23:59:59'
										and desarrollador_id = $vendor_id
										and ticketid = $ticketId




										)



					order by date	";
	}
	else{
			$sql ="	SELECT pointid , description , porcentaje , date , enddate

			FROM vtiger_ticketpuntos


			where ticketid = $ticketId
			and date BETWEEN '$hoy' and '$hoy'
			and desarrollador_id = $vendor_id
			order by date

	";
	}


	$result = $adb->query($sql);
	$trHeight = 50;
	if ($adb->num_rows($result) > 1){
		$trHeight = 35;
	}
	while ($reg = $adb->fetch_array($result)){
	$porcentajeHecho = round(calcularTrabajoTarea ($reg['pointid'],$hoy),0).'%';
	if ($porcentajeHecho == 100 ){
		$estiloTd = ';color:#093';
		if ($reg['enddate'] <> '0000-00-00'){
			$porcentajeHecho=formatearFecha($reg['enddate']);
		}

	}
	else{
		$estiloTd ='';
	}
	if (strlen ($reg['description']) > 220) {
		$descripcionTarea= substr ( $reg['description'] , 0 , 220 ).' ...';
	}
	else {
		$descripcionTarea=$reg['description'];
	}
	$buffer .= '	<tr style="height: '.$trHeight.'px; '.$estiloTd.'">
                                    		<td width="50%" class="dvtCellInfo" align="center"  ><b>'.htmlentities($descripcionTarea).'</b></td>
                                   			<td width="25%" class="dvtCellInfo" align="center"  ><b>'.$porcentajeHecho.'</b></td>
                                   			<td width="25%" class="dvtCellInfo" align="center"  ><b>'.formatearFecha($reg['date']).'</b></td>
                                     	</tr>';
	}
	if (empty($buffer)){
	$buffer = '	<tr style="height: 50px;">
                                    		<td width="50%" class="dvtCellInfo" align="center"  ><b> - </b></td>
                                   			<td width="25%" class="dvtCellInfo" align="center"  ><b> - </b></td>
                                   			<td width="25%" class="dvtCellInfo" align="center"  ><b> - </b></td>
                                     	</tr>';
	}
	return $buffer;
}


function calcularTrabajoTarea ($pointid,$hoy) {
	global $adb;
	$ayer = $hoy;

	$sql ="	SELECT  porcentaje_fin

			FROM vtiger_log_cierre_diario

			WHERE puntoid=$pointid
			and date BETWEEN '$ayer' and '$ayer'

	";
	$result = $adb->query($sql);
	while ($reg = $adb->fetch_array($result)){

			$return = $reg['porcentaje_fin'] ;
	}
	if ($return > 0 and $return <= 101){
		return $return;
	}
	else{
		return 0;
	}


}

function OTsDesarrollo($vendorid,$hoy){
	global $adb;
	$ayer = $hoy;
	

	$sql = "SELECT t.ticketid,t.title,a.accountname,date_closed cierre,t.start_date  inicio,t.end_estimated_date  estimada, nota, horas

	FROM vtiger_troubletickets t
	INNER join vtiger_crmentity e on (e.crmid=t.ticketid AND e.deleted = 0)
	INNER join vtiger_ordentrabajo o on (t.ticketid = o.ticketid)
	INNER join vtiger_ordentrabajo_informes oi on (o.ordentrabajoid = oi.ordentrabajoid)
	left join vtiger_ticketcf tcf on tcf.ticketid=t.ticketid
	left join vtiger_account a on a.accountid=t.parent_id
	

	where
	oi.vendorid=$vendorid
	AND fecha = '$hoy'
	group by t.ticketid
			 ";
		
	$result = $adb->query($sql);
	$i = 0;

	while ($reg = $adb->fetch_array($result)){

		$_resultado[$i]['ticketid']=$reg['ticketid'];
		$_resultado[$i]['cierre']=$reg['cierre'];
		$_resultado[$i]['inicio']=$reg['inicio'];
		$_resultado[$i]['estimada']=$reg['estimada'];
		$_resultado[$i]['title']=$reg['title'];
		$_resultado[$i]['accountname']=$reg['accountname'];
		$_resultado[$i]['nota']	= $reg['nota'];
		$_resultado[$i]['horas'] = $reg['horas'];
		$i++;
	}

	return $_resultado;
}

function RegistroDesarrollo($vendorid,$hoy){
	global $adb;
	$ayer = $hoy;
	if ($hoy == date('Y-m-d') or strtotime($hoy) < time()){
			$sqlInterno = "  	SELECT DISTINCT ticketid
								FROM vtiger_diarynotes_desarrolladores
								WHERE date >= '$hoy 00:00:00' and date <='$hoy 23:59:59'
								and desarrollador_id = $vendorid



			 ";
	}
	else{
		$sqlInterno = " SELECT DISTINCT ticketid
								FROM vtiger_ticketpuntos


								WHERE date BETWEEN '$hoy' and '$hoy'
								and desarrollador_id = $vendorid
			";
	}


	$sql = "SELECT t.ticketid,t.title,a.accountname,AVG(p.porcentaje) porcentaje,date_closed cierre,t.start_date  inicio,t.end_estimated_date  estimada

	FROM vtiger_troubletickets t
	left join vtiger_crmentity e on e.crmid=t.ticketid
	left join vtiger_ticketcf tcf on tcf.ticketid=t.ticketid
	left join vtiger_account a on a.accountid=t.parent_id
	left join vtiger_ticketpuntos p on t.ticketid = p.ticketid


	where
	 e.deleted=0
	and p.desarrollador_id=$vendorid
	and p.ticketid = t.ticketid
	and t.ticketid IN (

						$sqlInterno

					)
	group by t.ticketid
			 ";
	$result = $adb->query($sql);
	$i = 0;

	while ($reg = $adb->fetch_array($result)){

			$_resultado[$i]['ticketid']=$reg['ticketid'];
			$_resultado[$i]['cierre']=$reg['cierre'];
			$_resultado[$i]['inicio']=$reg['inicio'];
			$_resultado[$i]['estimada']=$reg['estimada'];
			$_resultado[$i]['title']=$reg['title'];
			$_resultado[$i]['accountname']=$reg['accountname'];
			$_resultado[$i]['porcentaje']=$reg['porcentaje'];
			$_resultado[$i]['nota']	=	obtenerNota($reg['ticketid'],$vendorid,$hoy);
			$i++;
	}

	return $_resultado;
}
function obtenerNota($ticketid,$vendorid,$day){
	global $adb;
	$sql = "SELECT note from vtiger_diarynotes
			where desarrollador_id = $vendorid
			and ticketid = $ticketid
			and date BETWEEN '$day' and '$day'
			 ";
	$result = $adb->query($sql);
	while ($reg = $adb->fetch_array($result)){
		$note = $reg['note'];
	}
	if (empty($note)){
		$note = '-';
	}
	return $note;
}
if (!empty($_REQUEST['day'])){
	 $hoy = $_REQUEST['day'];
}
else {
	$hoy = date('Y-m-d');
}
$date = $hoy;
$calcular_dia=diaSemana($hoy);
$ayer =$calcular_dia['ayer'];
$manana = $calcular_dia['manana'];

$smarty = new vtigerCRM_Smarty;
$smarty->assign("MOD", $mod_strings);
$smarty->assign("APP", $app_strings);

$smarty->assign("THEME", $theme);
$smarty->assign("IMAGE_PATH", $image_path);
$smarty->assign("MODULE", $currentModule);

$sql="select * from vtiger_organizationdetails";
$result = $adb->pquery($sql, array());
$organization_logo = decode_html($adb->query_result($result,0,'logoname'));
$smarty->assign("LOGO",$organization_logo);
$smarty->assign("HEADER_IMAGE",vtiger_imageurl('header-bg.png', $theme));
$smarty->assign("HOY",formatearFecha($hoy));
$smarty->assign("LISTA_INCIDENCIAS_ABIERTAS",listarTipoIncidencia('Abierto',$hoy));
$smarty->assign("LISTA_INCIDENCIAS_CERRADAS",listarTipoIncidencia('Cerrado',$hoy));
$smarty->assign("AYER",$ayer);
$smarty->assign("MANANA",$manana);
$smarty->assign("IMG_PREVIA",vtiger_imageurl('cal_prev_nav.gif', $theme));
$smarty->assign("IMG_SIGUIENTE",vtiger_imageurl('cal_next_nav.gif', $theme));
$smarty->assign("IMG_ABRIR",vtiger_imageurl('33.png', $theme));
$smarty->assign("IMG_LUPA",vtiger_imageurl('lupa.png', $theme));
$smarty->assign("LISTA_DESARROLLADORES",listarDesarrollador($hoy));
$smarty->assign("RECORDID",$_REQUEST['record']);
$smarty->assign("DESARROLLADORES",obtenerNombreDesarrollador($hoy));

$smarty->display('modules/'.$currentModule.'/informe_del_dia.tpl');

?>