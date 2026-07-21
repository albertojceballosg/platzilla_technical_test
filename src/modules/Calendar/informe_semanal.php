<?php

require_once('Smarty_setup.php');
include_once("include/utils/utils.php");
include_once('include/utils/comunesTareas.php');
global $adb,$theme;	
	
/*	
function obtenerNombreDesarrollador() {
	global $adb;
		
	if (obtenerValorVariable('TASK_VENDOR_TYPE','Vendors') != '') {
		$condicionTipoProveedor = " AND vendortype = '".utf8_encode(html_entity_decode(obtenerValorVariable('TASK_VENDOR_TYPE','Vendors')))."'";
	}


		$sql = "SELECT v.vendorid,v.vendorname,color FROM vtiger_vendor v

					left join vtiger_crmentity on v.vendorid=crmid
					left join vtiger_vendorcf vcf on vcf.vendorid=v.vendorid
					 where deleted = 0
					 $condicionTipoProveedor
					 order by vendorname ";

		$result = $adb->query($sql);
		$i=0;
		while ($rowVendor =$adb->fetch_array($result)) {
			$Vendor[$i]['id']=$rowVendor['vendorid'];
			$Vendor[$i]['nombre']=$rowVendor['vendorname'];
			$Vendor[$i]['color']=$rowVendor['color'];


			$i++;

		}
		$Vendor['cantidad']=$i-1;
		return $Vendor;
	}
*/

	function obtenerNombreDesarrollador($fechaini,$fechafin) {
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
			
			$registros = obtenerTicketsCerrados ($Vendor[$i]['id'],$fechaini,$fechafin);
			
			if (is_array(($registros))) {
				foreach ($registros as $key => $value) {
					$Vendor[$i]['registros'][] = obtenerDatosOT($value,$fechaini,$fechafin);
				} 
			}
			
			$i++;

		}
		$Vendor['cantidad']=$i-1;
		return $Vendor;
	}
	
function ObtenerRegistroDeTrabajo($desarrolladorid,$inicio,$fin)
{
	$ayer =$hoy;
	global $adb;
	$date=$hoy;
	$sqlpor="SELECT t.ticketid,t.title,a.accountname,e.description

FROM vtiger_troubletickets t

left join vtiger_reldesa  r on r.idticket=t.ticketid
left join vtiger_crmentity e on e.crmid=t.ticketid
left join vtiger_ticketcf tcf on tcf.ticketid=t.ticketid
left join vtiger_account a on a.accountid=t.parent_id

where

 r.idvendor=$desarrolladorid

and e.deleted=0

and  date_closed BETWEEN '$inicio' and '$fin' 
";
$resultpor=$adb->query($sqlpor);
//	$row = $adb->fetchByAssoc($resultpor);
return $resultpor;


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
				<td class="dvtCellInfo"  '.$styleColor.'>'.utf8_encode($description).'</td>



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
				<td class="dvtCellInfo"  '.$styleColor.'>'.utf8_encode($description).'</td>
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
				<td class="dvtCellInfo"  '.$styleColor.'>'.utf8_encode($description).'</td>
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
				<td class="dvtCellInfo" width="60%"  '.$styleColor.'>'.utf8_encode($description).'</td>
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



function diaSemana() {
	$fecha= strtotime( '-1 week' );
	$dias = array('7', '1', '2', '3', '4', '5', '6');
	$numero=date("w", $fecha);
	$numero2=$fecha;
	switch ($numero)	{
		case 1:
		$eldia['primero']=$numero2;
		$eldia['segundo']=$numero2+(86400*4);
		break;
		case 2:
		$eldia['primero']=$numero2-(86400*1);
		$eldia['segundo']=$numero2+(86400*3);
		break;
		case 3:
		$eldia['primero']=$numero2-(86400*2);
		$eldia['segundo']=$numero2+(86400*2);
		break;
		case 4:
		$eldia['primero']=$numero2-(86400*3);
		$eldia['segundo']=$numero2+(86400);
		break;
		case 5:
		$eldia['primero']=$numero2-(86400*4);
		$eldia['segundo']=$numero2;
		break;
		case 6:
		$eldia['primero']=$numero2-(86400*5);
		$eldia['segundo']=$numero2-(86400*1);
		break;
		case 7:
		$eldia['primero']=$numero2-(86400*6);
		$eldia['segundo']=$numero2-(86400*2);
		break;
	}
	$eldia['primero'] = date('Y-m-d',$eldia['primero']);
	$eldia['segundo'] = date('Y-m-d',$eldia['segundo']);
	return $eldia;

}



function  obtenerEstadisticas () { // devuelve array con las cantitades de registros
	global $adb;
	
	$diasSemana = diaSemana();
	$lunes = $diasSemana['primero'];
	$viernes = $diasSemana['segundo'];

	$sql = "SELECT count(*) cantidad

								FROM vtiger_troubletickets t

								left join vtiger_crmentity crm on crmid= t.ticketid
								left join vtiger_ticketcf cf on cf.ticketid=t.ticketid

								where

								crm.deleted=0
								-- and planificado like 'Si'
								and date_closed BETWEEN '$lunes' and '$viernes' ";
	$result = $adb->query($sql);
	 $si = $adb->fetch_array($result);
	$data['si'] = $si['cantidad'] ; // cantidad de planificados cerrados

	$sql = "SELECT count(*) cantidad

								FROM vtiger_troubletickets t

								left join vtiger_crmentity crm on crmid= t.ticketid
								left join vtiger_ticketcf cf on cf.ticketid=t.ticketid

								where

								crm.deleted=0
								-- and planificado not like 'Si'
								and date_closed BETWEEN '$lunes' and '$viernes' ";
	$result = $adb->query($sql);
	 $no = $adb->fetch_array($result);
	$data['no'] = $no['cantidad'] ; //cantidad de no planificados cerrados

	$sql = "SELECT count(*) cantidad

								FROM vtiger_troubletickets t

								left join vtiger_crmentity crm on crmid= t.ticketid
								left join vtiger_ticketcf cf on cf.ticketid=t.ticketid

								where

								crm.deleted=0
								and date_closed BETWEEN '$lunes' and '$viernes'
								and end_estimated_date > date_closed
								";
	$result = $adb->query($sql);
	$antes = $adb->fetch_array($result);
	$data['antes'] = $antes['cantidad'] ; // cantidad que se cerraron antes de la fecha estimada

	$sql = "SELECT count(*) cantidad

								FROM vtiger_troubletickets t

								left join vtiger_crmentity crm on crmid= t.ticketid
								left join vtiger_ticketcf cf on cf.ticketid=t.ticketid

								where

								crm.deleted=0
								and date_closed BETWEEN '$lunes' and '$viernes'
								and end_estimated_date < date_closed
								";
	$result = $adb->query($sql);
	$despues = $adb->fetch_array($result);
	$data['despues'] = $despues['cantidad'] ; // cantidad que se cerraron despues de la fecha estimada
	$sql = "SELECT count(*) cantidad

								FROM vtiger_troubletickets t

								left join vtiger_crmentity crm on crmid= t.ticketid
								left join vtiger_ticketcf cf on cf.ticketid=t.ticketid

								where

								crm.deleted=0
								and date_closed BETWEEN '$lunes' and '$viernes'
								and end_estimated_date = date_closed
								";
	$result = $adb->query($sql);
	$atiempo = $adb->fetch_array($result);
	$data['atiempo'] = $atiempo['cantidad'] ;  // cantidad que se cerraron el dia que se estimaba



	return $data;

}

function listarEstadisticasClientes (){
	global $adb;
	$bufferSalida = '';
	
	$sql = "SELECT  a.accountname as nombre , a.accountid as id FROM vtiger_accountscf ac
		left join vtiger_account a on  ac.accountid = a.accountid
		left join vtiger_crmentity crm on crmid= a.accountid

		WHERE iscustomer = 1
		and crm.deleted=0
		order by a.accountname asc
						";
	$result = $adb->query($sql);
	while ($reg=$adb->fetch_array($result)) {
		$temp =  obtenerRegistroCliente ($reg['id']);
		$cerrado = $temp['cerrado'];
		$abierto = $temp['abierto'];
		$nombre = $reg['nombre'];
		if ($cerrado > 0 or $abierto >0) {
			$bufferSalida.= '<tr style="height: 25px;">



						<td class="dvtCellInfo" width="60%" ><b>'.htmlentities($nombre).'</b></td>

						<td   class="dvtCellInfo" width="20%" align="center"  >'.$cerrado.'</td>

						<td   class="dvtCellInfo" width="20%" align="center"  >'.$abierto.'</td>


					</tr>';
		}
	}
	return $bufferSalida;
}


function obtenerRegistroCliente ($id) {
	global $adb;
	$diasSemana = diaSemana();
	$lunes = $diasSemana['primero'];
	$viernes = $diasSemana['segundo'];



	$sql = "SELECT count(*) as cantidad

								FROM     vtiger_troubletickets t
								left join vtiger_crmentity crm on crmid= t.ticketid
								left join vtiger_ticketcf cf on cf.ticketid=t.ticketid

								where
								t.parent_id = $id
								and crm.deleted=0
								and date_closed BETWEEN '$lunes' and '$viernes'
								";
	$result = $adb->query($sql);
	$res = $adb->fetch_array($result);
	$data['cerrado'] = $res['cantidad'] ; // cantidad de abiertos por cliente

	$sql = "SELECT count(*) as cantidad

								FROM     vtiger_troubletickets t
								left join vtiger_crmentity crm on crmid= t.ticketid
								left join vtiger_ticketcf cf on cf.ticketid=t.ticketid

								where
								t.parent_id = $id
								and crm.deleted=0
								and crm.createdtime BETWEEN '$lunes 00:00:01' and '$viernes 23:59:59'
								";
	$result = $adb->query($sql);
	$res = $adb->fetch_array($result);
	$data['abierto'] = $res['cantidad'] ;
	return $data;

}



function listarTipoIncidencia($estado)
{
	global $adb;
	$diasSemana = diaSemana();
	$lunes = $diasSemana['primero'];
	$viernes = $diasSemana['segundo'];

	$sql = "SELECT type

		FROM vtiger_type
where type !='-'
		 ";

		$result = $adb->query($sql);
		$noofrows = $adb->num_rows($result);
	if($estado=='Abierto')
	{
		$where="and createdtime BETWEEN '$lunes 00:00:01' and '$viernes 23:59:59' ";
	}
	else
	{
		$where="and date_closed BETWEEN '$lunes' and '$viernes' ";
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
				 	<td   class="dvtCellInfo" align="center" width="6%" ><a href="javascript:void(0)" onclick="window.open(\'ver_incidencia_semanal.php?tipo='.$type.'&estado='.$estado.'\',\'venta\',\'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=658, height=465, top=85, left=140\'); "><img src="'.vtiger_imageurl('lupa.png', $theme).'" border="0"></a></td>
			';

				$bufferSalida.= '</tr>';



		}

	return $bufferSalida;

}

function listarDesarrollador()
{
	global $adb;
	$diasSemana = diaSemana();
	$lunes = $diasSemana['primero'];
	$viernes = $diasSemana['segundo'];
	
	$bufferSalida = '';
	
	if (obtenerValorVariable('TASK_VENDOR_TYPE','Vendors') != '') {
		$condicionTipoProveedor = " AND vendortype = '".utf8_encode(html_entity_decode(obtenerValorVariable('TASK_VENDOR_TYPE','Vendors')))."'";
	}

	$sql = "SELECT  vendorname,v.vendorid,vendortype FROM vtiger_vendor v

			left join vtiger_crmentity crm on v.vendorid=crm.crmid
			left join vtiger_vendorcf vcf on vcf.vendorid=v.vendorid
			where deleted=0
			$condicionTipoProveedor
			order by vendorname
		 ";
	$i = 0;
	$result = $adb->query($sql);
	$where="and date_closed BETWEEN '$lunes' and '$viernes' ";
	while($reg = $adb->fetch_array($result)) {
			$vendor_id = $reg["vendorid"];
			$sql_ticket = "SELECT avg(note) as prom FROM vtiger_diarynotes

								where desarrollador_id=$vendor_id

								and date BETWEEN '$lunes' and '$viernes' ";

			$result_ticket = $adb->query($sql_ticket);
			$cantidad_ticket = $adb->fetch_array($result_ticket);


			if (empty($cantidad_ticket['prom'])){
				$prom = '-';
			}
			else{
				$prom =  round($cantidad_ticket['prom'],2);
			}

			$sql = "SELECT count(*) as cantidad

								FROM     vtiger_reldesa rel
								left join vtiger_troubletickets t on rel.idticket = t.ticketid
								left join vtiger_crmentity crm on crmid= t.ticketid
								left join vtiger_ticketcf cf on cf.ticketid=t.ticketid

								where
								rel.idvendor = $vendor_id
								and crm.deleted=0
								and date_closed BETWEEN '$lunes' and '$viernes'
								";
			$cant = $adb->query($sql);
			$cantidad = $adb->fetch_array($cant);


			$bufferSalida.= '<tr style="height: 25px;">

				<td   class="dvtCellInfo" width="40%" ><b><a href="#tableDesarro'.$reg['vendorid'].'" onclick="openDiv'."('divDesarro".$reg['vendorid']."')".'">'.$reg["vendorname"].'</a></b></td>
				<td   class="dvtCellInfo" align="center" width="30%"  >'.$reg['vendortype'].'</td>
				<td   class="dvtCellInfo" align="center"  width="15%">'.$prom.'</td>
				<td   class="dvtCellInfo" align="center"  width="15%">'.$cantidad['cantidad'].'</td>
				 </tr>
			';
			





		}


	return $bufferSalida;
}


$diasSemana = diaSemana();
$lunes = $diasSemana['primero'];
$viernes = $diasSemana['segundo'];
$dataEst = obtenerEstadisticas ();


$smarty = new vtigerCRM_Smarty;
$smarty->assign("MOD", $mod_strings);
$smarty->assign("APP", $app_strings);

$smarty->assign("THEME", $theme);
$smarty->assign("IMAGE_PATH", $image_path);
$smarty->assign("MODULE", $currentModule);
$sql="select * from vtiger_organizationdetails";
$result = $adb->query($sql);
$organization_logo = decode_html($adb->query_result($result,0,'logoname'));
$smarty->assign("LOGO",$organization_logo);

$smarty->assign("LISTA_INCIDENCIAS_ABIERTAS",listarTipoIncidencia('Abierto'));
$smarty->assign("LISTA_INCIDENCIAS_CERRADAS",listarTipoIncidencia('Cerrado'));
$smarty->assign("ESTADISTICAS_CLIENTES",listarEstadisticasClientes());
$smarty->assign("LISTA_DESARROLLADORES",listarDesarrollador());
$smarty->assign("DATAEST",$dataEst);
$smarty->assign("DESARROLLADORES",obtenerNombreDesarrollador($lunes,$viernes));
$smarty->assign("IMG_PREVIA",vtiger_imageurl('cal_prev_nav.gif', $theme));
$smarty->assign("IMG_SIGUIENTE",vtiger_imageurl('cal_next_nav.gif', $theme));
$smarty->assign("IMG_ABRIR",vtiger_imageurl('33.png', $theme));
$smarty->assign("IMG_LUPA",vtiger_imageurl('lupa.png', $theme));
$smarty->assign("LUNES",formatearFecha($lunes));
$smarty->assign("VIERNES",formatearFecha($viernes));

$smarty->display('modules/'.$currentModule.'/informe_semanal.tpl');


?>