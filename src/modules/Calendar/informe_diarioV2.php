<?php
include_once("modules/Calendar/funciones_panel_jefe_desarrollo.php");
include_once('include/utils/comunesTareas.php');

$rol=substr($_REQUEST['CRM'],2,-2);


if(!(isset($_SESSION["authenticated_user_id"]) && (isset($_SESSION["app_unique_key"]) ))){
	header("index.php?action=Login&module=Users");
	exit();
}

$criterio = '';

if($rol=="H19" or $rol=="H8" or $rol=="H26" or $rol=="H28" or $rol=="H2" or $rol=="H22") {
	if($rol=="H2"){
		$criterio='';
	}else if($rol=='H28'){ 
		$criterio='-*Se cumple Fecha estimada Finalizaci&oacute;n:\n-*Comentarios del desarrollo:\n';
	}else if($rol=='H8'){ 
		$criterio='-*Detalles T&eacute;cnicos:\n-*Producci&oacute;n[si/no]:\n-*Se cumple Fecha estimada Finalizaci&oacute;n:\n-*Comentarios del desarrollo:\n';
	}else if($rol=='H26'){ 		
		$criterio='-*Detalles T&eacute;cnicos:\n-*Producci&oacute;n[si/no]:\n-*Se cumple Fecha estimada Finalizaci&oacute;n:\n';		
	}else if($rol=='H22'){ 		
	   $criterio='-*Se cumple Fecha estimada Finalizaci&oacute;n:\n-*Comentarios del desarrollo:\n';
	}
}

$datosVendedor = obtenerIDdesarrolladorFromUserID($_SESSION["authenticated_user_id"]);
$vendorId = $datosVendedor['vendorid'];
$vendorName = $datosVendedor['vendorname'];
$tickets = obtenerTicketsACerrarDesarrollador ($vendorId,date('Y-m-d'));

$bufferSalida = '
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<style type="text/css">@import url("../../../themes/softed/style.css");</style>
<script>
	function escribirFormaRegistroNoPlanificado() {
		ctrlTable = document.getElementById(\'actividadesTable\');
		
		var x = ctrlTable.insertRow(ctrlTable.rows.length);
		var cantidad = ctrlTable.rows.length;
		var x1=x.insertCell(0);
		var x2=x.insertCell(1);
		var x3=x.insertCell(2);
		var x4=x.insertCell(3);
		var x5=x.insertCell(4);
		var x6=x.insertCell(5);
		x1.className = \'dvtCellInfo\';
		x2.className = \'dvtCellInfo\';
		x3.className = \'dvtCellInfo\';
		x4.className = \'dvtCellInfo\';
		x5.className = \'dvtCellInfo\';
		x6.className = \'dvtCellInfo\';		
		x1.innerHTML = \'<textarea class="detailedViewTextBox" style="width:150px" name="title_noplat[]"></textarea>\';
		x2.innerHTML = \''.obtenerValoresFiltro('parent_id',true).'\';
		x3.innerHTML = \'<textarea class="detailedViewTextBox" style="width:150px" name="incidencia_noplat[]"></textarea>\';
		x4.innerHTML = \'&nbsp;\';
		x5.innerHTML = \''.escribeComboHoras(0,true,'onChange="actualizarHorasTrabajadasYHorasRegistradas()"').'\';
		x6.innerHTML = \'<textarea class="detailedViewTextBox" style="width:300px;height:120px" name="coment_desarrollador_noplat[]">'.$criterio.'</textarea>\';
	}
	
	function obtenerHorasTrabajadas() {
		horaInicial = new Date('.obtenerHoraPrimerLogueo($vendorId).');
		horaFinal = new Date('.date('Y,m,d,H,i,s').');
		
		tiempoTotal = parseFloat((horaFinal.getTime() - horaInicial.getTime())/(1000*60*60)/0.25); //Por son cuartos de horas
		
		tiempoTotal = Math.ceil(tiempoTotal)*0.25;
		return tiempoTotal;
	}

	function obtenerHorasRegistradas() {
		horasPlanificadas = document.getElementsByName(\'horas_dedicadas[]\');
		horasNoPlanificadas = document.getElementsByName(\'horas_dedicadas_noplat[]\');
		totalHoras = 0;
		
		for(i = 0;i < horasPlanificadas.length;i++) {
			if (horasPlanificadas[i].value != \'-\')
				totalHoras+= parseFloat(horasPlanificadas[i].value);
		}
		
		for(i = 0;i < horasNoPlanificadas.length;i++) {
			if (horasNoPlanificadas[i].value != \'-\')
				totalHoras+= parseFloat(horasNoPlanificadas[i].value);
		}
		
		return totalHoras;
	}
	
	function actualizarHorasTrabajadasYHorasRegistradas() {
		ctrlHorasTrabajadas = document.getElementById(\'HorasTrabajadas\');
		ctrlHorasRegistradas = document.getElementById(\'HorasReportadas\');
		
		if (ctrlHorasTrabajadas) {
			ctrlHorasTrabajadas.innerHTML = \'Horas trabajadas: \'+obtenerHorasTrabajadas();
		}
		
		if (ctrlHorasRegistradas) {
			ctrlHorasRegistradas.innerHTML = \'Horas registradas: \'+obtenerHorasRegistradas();
		}
	}
	
	function verificar_desarrollador(form){

		horasTrabajadas = obtenerHorasTrabajadas();
		horasRegistradas = obtenerHorasRegistradas();
	
		if (horasTrabajadas < horasRegistradas) {
			alert(\'Esta registrando más horas de las que realmente ha estado conectado\nPor favor verifique\');
			return false;
		}
		/******
		Comento la validación del llenado forzado de todas las actividades 
		*******/
		/*
		cantidad=document.getElementById(\'countTicket\');
			
		comentarios = document.getElementsByName(\'coment_desarrollador[]\');
		horas = document.getElementsByName(\'horas_dedicadas[]\');
		for(i = 0;i < cantidad.value;i++) {
			if (document.getElementById(\'porcentaje\'+i)) {
				var posicion=document.getElementById(\'porcentaje\'+i).options.selectedIndex; //posicion
				if(document.getElementById(\'porcentaje\'+i).options[posicion].text=="-"  )	
				{
					punto=i+1;
					alert(\'Por favor complete el valor del porcentaje en el punto numero \'+punto);
					return false;
			
				}
				if(horas[i].value=="-")	
				{
					punto=i+1;
					alert(\'Por favor complete la cantidad de horas utilizadas en el punto numero \'+punto);
					return false;
			
				}
				if(comentarios[i].value=="")	
				{
					punto=i+1;
					alert(\'Por favor complete el comentario en el punto numero \'+punto);
					return false;
			
				}
			}
		
		
		}
		*/
		form.submit();
	}
	
	function actualizarEstado(id) {
		ctrl = document.getElementById(id);
		
		if (ctrl) {
			ctrl.value = "1";
		}
	}
	
	if (window.addEventListener)
		window.addEventListener(\'load\',actualizarHorasTrabajadasYHorasRegistradas,false);
	else
		window.attachEvent(\'onload\',actualizarHorasTrabajadasYHorasRegistradas,false);
</script>
</head>
<body>
<div style="style:overflow:scroll">
<form name="" method="POST" action="index.php">
	<input type="hidden" name="module" value="Calendar" />
	<input type="hidden" name="action" value="saveComentariosV2" />
	<input type="hidden" name="Ajax" value="true" />
	<input type="hidden" name="vendorid" value="'.$vendorId.'" />
	<input type="hidden" id="countTicket" name="countTicket" value="'.count($tickets).'">
	<div style="float:left;text-align:center;" class="small"> 
	<h2>Informe Diario de:'.$vendorName.'</h2>
	</div>
	<div style="float:right" class="small"> 
		<input  class="crmbutton small delete" type="button" id="boton_noplafinicado"   value="  Registrar Actividad No Planificada  " onClick="escribirFormaRegistroNoPlanificado();">
		<br/>
		<div id="HorasTrabajadas">
		Horas trabajadas: 0.00
		</div><br/>
		<div id="HorasReportadas">
		Horas reportadas: 0.00
		</div><br/>
	</div>
	<br/>
	<table cellpadding="0" cellspacing="0" width="100%" style="background-image:url(../../../themes/softed/images/header-bg.png); background-repeat:repeat-x;" class="small" id="actividadesTable">
		<tr style="background-color:gray">
			<th class="detailedViewHeader">Registro</th>
			<th class="detailedViewHeader">Cuenta</th>
			<th class="detailedViewHeader">Incidencia</th>
			<th class="detailedViewHeader">Puntos pendientes</th>
			<th class="detailedViewHeader">Horas dedicadas</th>
			<th class="detailedViewHeader">Comentarios</th>
		</tr>';
for($i = 0;$i < count($tickets);$i++) {
	//Se lee la información del ticket
	$ticket = obtenerDatosTicket($tickets[$i]);
	$controlDiario = obtenerDatosControlDiario($tickets[$i],$vendorId,date('Y-m-d'));
	$puntosPendientes = informacionPuntosPendientes($tickets[$i],$vendorId,$ticket['tipo'],$i);
	$informeDiario = obtenerHorasDedicadasTicket(date('Y-m-d'),$tickets[$i],$vendorId);
	
	if($rol=="H2" or $rol=="H8" or $rol=="H22") {
		$onclick="verificar_desarrollador";
	}
	else{
		$onclick="verificar_desarrollador";
	}

	if (!isset($informeDiario['coment']))
		$informeDiario['coment'] = $criterio;
	$hours_limit=18;
	if($ticket['hours_limit']!='' && $ticket['hours_limit']>0){
		$hours_limit=$ticket['hours_limit'];
	}
	$informeDiario['coment'] = str_replace('\n',"\n",$informeDiario['coment']);
	$informeDiario['coment'] = str_replace('<br/>',"",$informeDiario['coment']);
	
	$bufferSalida.= '
		<tr style="background-color:'.$color.'">
			<td width="10%" class="dvtCellInfo">
				<input type="hidden" name="ticketid_actualizados[]" value="0" id="ticketid_actualizados'.$tickets[$i].'">
				<input type="hidden" name="ticketid[]" value="'.$tickets[$i].'">
				'.utf8_encode($ticket['title']).'
			</td>
			<td width="10%" class="dvtCellInfo"><a href="index.php?module=Accounts&action=DetailView&record='.$ticket['accountid'].'">'.$ticket['accountname'].'</a></td>
			<td width="20%" class="dvtCellInfo">'.(substr(utf8_encode($ticket['description']),0,200)).'</td>
			<td width="35%" class="dvtCellInfo" valign="top">'.$puntosPendientes['html'].'</td>
			<td width="5%"  class="dvtCellInfo">'.escribeComboHoras($informeDiario['horas_dedicadas'],false,'onChange="actualizarHorasTrabajadasYHorasRegistradas()"',$hours_limit).'</td>
			<td width="20%"  class="dvtCellInfo">
				<textarea style="width:300px; height:120px;" class="detailedViewTextBox" name="coment_desarrollador[]" onchange="actualizarEstado(\'ticketid_actualizados'.$tickets[$i].'\');">'.str_replace("<br/>","\n",utf8_encode(html_entity_decode(($informeDiario['coment'])))).'</textarea>
			</td>
		</tr>';
}
$bufferSalida.= '
	</table>
	<div align="center">
		<input class="crmbutton small edit" type="button" value="Finalizar" name="comentar" id="comentar" onClick="return verificar_desarrollador(this.form)">
		<input  class="crmbutton small delete" type="button" value="Cerrar" onClick="window.close()">
	</div>
</form>
</div>
</body>
</html>
';

echo $bufferSalida;
?>