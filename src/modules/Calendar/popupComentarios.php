<?php
include_once("include/utils/utils.php");

global $adb;
$ticketId=$_REQUEST['t'];
$vendorId=$_REQUEST['v'];
function listarComentariosLocal($ticketId,$vendorId){
	$comentarios = obtenerComentariosLocal ($ticketId,$vendorId);
	foreach ($comentarios as $fecha => $coment){
		if (isset($coment['desarrollador'])){
			$buffer.='<tr>
			<td height="19" width="20%" class="dvtCellLabel">
			<strong>Desarrollador '.($coment['vendorname']).' del '.$fecha.':</strong>
			<br>
			</td>
			<td height="19" class="dvtCellInfo">
			'.html_entity_decode($coment['desarrollador']).'<br>
			</td>
			</tr>';
		}		if (isset($coment['admin'])){
			$buffer.='<tr>
			<td height="19" width="20%" class="dvtCellLabel">
			<strong>Jefe de Desarrollo '.$fecha.':</strong>
			<br>
			</td>
			<td height="19" class="dvtCellInfo"><div style="width:80%; float:left">
			'.$coment['admin'].'</div><b>Nota: '.$coment['note'].'</b><br>
			</td>
			</tr>';
		}

	}
echo  $buffer;

}

function obtenerComentariosLocal ($ticketId,$vendorId) {
		global $adb;
		$punteroDia = date( 'Y-m-d', $date );
		/*$sql = "SELECT coment , note , date, vendorname
				FROM vtiger_diarynotes
				LEFT JOIN vtiger_vendor v ON v.vendorid = desarrollador_id
				WHERE desarrollador_id = $vendorId
				and ticketid = $ticketId order by diarynoteid 	desc

			 ";*/
		$sql = "SELECT comentario_coordinador as coment, nota as note, fecha as date, vendorname
				FROM vtiger_ordentrabajo_informes
				INNER JOIN vtiger_ordentrabajo ON (vtiger_ordentrabajo_informes.ordentrabajoid = vtiger_ordentrabajo.ordentrabajoid)
				LEFT JOIN vtiger_vendor v ON v.vendorid = vtiger_ordentrabajo_informes.vendorid
				WHERE vtiger_ordentrabajo_informes.vendorid = $vendorId AND LENGTH(comentario_coordinador) > 0
				and ticketid = $ticketId order by informesid desc";

		$result = $adb->query($sql);
		while ($reg = $adb->fetch_array($result)){
			$punteroFecha=$reg['date'];
			$data[$punteroFecha]['admin'] = decode_html($reg['coment']);
			$data[$punteroFecha]['note'] = $reg['note'];
			$data[$punteroFecha]['vendorname'] = $reg['vendorname'];

		}

		$sql = "SELECT comentario as coment, fecha as date, vendorname
				FROM vtiger_ordentrabajo_informes
				INNER JOIN vtiger_ordentrabajo ON (vtiger_ordentrabajo_informes.ordentrabajoid = vtiger_ordentrabajo.ordentrabajoid)
				LEFT JOIN vtiger_vendor v ON v.vendorid = vtiger_ordentrabajo_informes.vendorid
				WHERE vtiger_ordentrabajo_informes.vendorid = $vendorId AND LENGTH(comentario) > 0
				and ticketid = $ticketId order by informesid desc";



		$result = $adb->query($sql);
		while ($reg = $adb->fetch_array($result)){
			$punteroFecha=$reg['date'];
			$data[$punteroFecha]['desarrollador'] = decode_html($reg['coment']);
			$data[$punteroFecha]['jefe'] = $reg['vendorname'];
		}
		if (is_array($data))
		   krsort($data);
		return $data;
}

?><html>

<head>

<style type="text/css">@import url("../../themes/softed/style.css");</style>
</head>

<body>
<table cellpadding="0" cellspacing="0" width="100%" class="small">
	<tr>
	<td class="dvInnerHeader" colspan="2">
	<div style="text-align:center">
	<b>Comentarios Anteriores</b>
	</div>
	</td>
	</tr>
	<? listarComentariosLocal($ticketId,$vendorId);?>
   </table>
</body>
</html>