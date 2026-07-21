<html>
<body>
<script type="text/javascript" src="include/js/FieldDependencies.js"></script>
<script type="text/javascript" src="include/js/comunesTareas.js"></script>
<script type="text/javascript" src="include/jquery/jquery-1.7.1.min.js"></script>
<script>jQuery.noConflict();</script>
{literal}
<script type="text/javascript">
	jQuery(document).ready(function() { (new FieldDependencies({"tipoht":{"Instancias":{"subtipoht":["-"]},"__DEFAULT__":{"subtipoht":["Datos de identificacion","Bloques de campos","Campos","Relaciones con otros modulos","Filtros","Vista de lista","Vista de detalle","Vista de registros relacionados","Vista de edicion","-"]},"JQuery+Javascripts":{"subtipoht":["-"]},"Smarty Templates":{"subtipoht":["-"]},"Modulo a nivel de datos":{"subtipoht":["Datos de identificacion","Bloques de campos","Campos","Relaciones con otros modulos","Filtros"]},"Modulo a nivel de interfaz":{"subtipoht":["Vista de lista","Vista de detalle","Vista de registros relacionados","Vista de edicion"]},"Usuarios":{"subtipoht":["-"]},"Idioma-Lenguaje":{"subtipoht":["-"]},"Importacion-Exportacion":{"subtipoht":["-"]},"ETLs":{"subtipoht":["-"]},"CronJobs":{"subtipoht":["-"]},"Front-ends":{"subtipoht":["-"]},"Utilidades":{"subtipoht":["-"]},"Otra":{"subtipoht":["-"]},"":{"subtipoht":["-"]}}})).init() });
</script>

<script type="text/javascript">

</script>
{/literal}
<script>
var muestra_ph='{$MUESTRA_PROYECTOS_HITOS}';
var requiere_validacion_cliente = '{$REQUIERE_VALIDACION_CLIENTE}';
{literal}
function validateForm(){

//Se determina la fecha menor y la fecha mayor y se asigna
bFecha = true;
i = 1;
while (jQuery('#desarrollador'+i).length) {
	/*fecha = document.getElementById('jscal_field_dateinicio'+i);
	fechafin = document.getElementById('jscal_field_datefin'+i);
	if (fecha && fecha.value != '' &&
	    fechafin && fechafin.value != '') {
		if (fecha.value > document.getElementById("jscal_field_date_punto").value || document.getElementById("jscal_field_date_punto").value == '') {
			document.getElementById("jscal_field_date_punto").value = fecha.value;
		}
		if (fecha.value < document.getElementById("jscal_field_date_puntofin").value || document.getElementById("jscal_field_date_puntofin").value == '') {
			document.getElementById("jscal_field_date_puntofin").value = fecha.value;
		}
	} else {
		bFecha = false;
	}*/
	if (jQuery('#row_coordinador').is(":visible")) {

		if (jQuery('#coordinador').val() == jQuery('#desarrollador'+i).val()) {
			alert('El coordinador no puede uno de los desarrolladores asignados');
			return false;
		}
	}
	i++;
}


var titulo=document.getElementById("titulo").value;

var turno=document.getElementById("turno1").value;
var mm=document.getElementById("jscal_field_dateinicio1").value;
var descri=document.getElementById("descrip1").value;
var desa=document.getElementById("desarrollador1").value;
var tipoot=document.getElementById("tipoot1").value;

if (jQuery('#reqvalidacion').is(':checked')) //Si se requiere validación de cliente no se valida grid de asignaciones
	return true;

if((titulo=="")||(turno=="")||(mm=="") || (descri=="") || (desa=="")||(tipoot == "")){
	alert("Debe completar todos los campos solicitados!");
	return false;
}
else
	return true;
}

function onChangeType() {

	var url = "index.php?action=ActivityAjax&function=ACTUALIZAR_PAR_OTS&module=HelpDesk";

	jQuery.getJSON(
	url,
	{
		'type':jQuery('#fld_type').val()
	},
	function( data ) {
		if (data) {

			if (data.video) {
				jQuery('#row_video').show();
			} else {
				jQuery('#row_video').hide();
			}
			if (data.proyecto) {
				jQuery('#row_proyecto').show();
			} else {
				jQuery('#row_proyecto').hide();
			}
			if (data.requisitos) {
				jQuery('#row_docrequisitos').show();
			} else {
				jQuery('#row_docrequisitos').hide();
			}

			if (data.coordinador) {
				jQuery('#row_coordinador').show();
			} else {
				jQuery('#row_coordinador').hide();
			}
			if (data.prioridad) {
				jQuery('#row_prioridad').show();
			} else {
				jQuery('#row_prioridad').hide();
			}
			if (data.appdesarrollo) {
				jQuery('#row_appdesarrollo').show();
			} else {
				jQuery('#row_appdesarrollo').hide();
			}
			if (data.howto) {
				jQuery('#row_howto').hide();
			} else {
				jQuery('#row_howto').hide();
			}
		} else {
			if (jQuery('#fld_type').val() == 'Peticion' || muestra_ph == 'true') {
				jQuery('#row_proyecto').show();
				jQuery('#row_docrequisitos').show();
				jQuery('#row_video').hide();
				jQuery('#reqvalidacion').prop('checked', true);
				jQuery('#ots').hide();
				jQuery('#row_prioridad').hide();
			}else if (jQuery('#fld_type').val() == 'Incidencia') {
				jQuery('#row_proyecto').hide();
				jQuery('#row_docrequisitos').hide();
				jQuery('#row_video').show();
				jQuery('#row_prioridad').show();
			}else {
				jQuery('#row_proyecto').hide();
				jQuery('#row_docrequisitos').show();
				jQuery('#row_video').show();
				jQuery('#reqvalidacion').prop('checked', true);
				jQuery('#ots').hide();
				jQuery('#row_prioridad').hide();
			}
		}
	});
}

function agregarPaso1Express(){
	alert('entra aca');
	   var tableName = document.getElementById('testingasoc');
	   document.getElementById('cantidadFilas').value=document.getElementById('cantidadFilas').value+1;
		var prev = tableName.rows.length;
	    	var count = eval(prev)-1;//As the table has two headers, we should reduce the count
	    	var row = tableName.insertRow(prev);
	        var count2=count+1;
			row.id = "row"+count2;
			row.style.verticalAlign = "top";

			var colzero = row.insertCell(0);
	        colzero.className='dvtCellInfo';
			var colone = row.insertCell(1);
	        colone.className='dvtCellInfo';
	        var coltwo = row.insertCell(2);
	        coltwo.className='dvtCellInfo';
	        var colthree = row.insertCell(3);
	        colthree.className='dvtCellInfo';

	        var valore='';
	        var texto='';

	        var count=(document.getElementById('testingasoc').getElementsByTagName('tr').length)-1;
	        var boton='<img src="../../../themes/images/delete.gif" border="0" name="eliminar'+count+'" id="eliminar'+count+'" onClick=eliminarExpress('+count+'); value="Eliminar">';
			//alert(ruta);
	        var selector0="<select name='tipoot"+count+"' id='tipoot"+count+"' class='small' onChange=' var ruta=\"index.php?module=howtos&action=DetailView&record=\"; document.getElementById(\"tiempo_est"+count+"\").value=this.options[this.selectedIndex].value.split(\",\")[1]; document.getElementById(\"ref_howto"+count+"\").href=ruta.concat(this.options[this.selectedIndex].value.split(\",\")[2]);'>";
	        for(var i=0;i<document.getElementById('tipoot1').length;i++){
	            valore=document.getElementById("tipoot1").options[i].value;
	            texto=document.getElementById("tipoot1").options[i].text;
	            selector0+='<option value="'+valore+'">'+texto+'</option>';
	        }
	        selector0+='</select>';
	        selector0=boton+selector0;
	        colzero.innerHTML=selector0;


	        colone.innerHTML='<input type="text" id="tiempo_est'+count+'"  name="tiempo_est'+count+'" class="small" disabled="disabled" />';
	        coltwo.innerHTML='<a id="ref_howto'+count+'" href="#" onClick="window.open(this.href, this.target, \'width=800,height=800\'); return false;">How To</a>';
	        colthree.innerHTML='<textarea id="descri'+count+'" name="descrip'+count+'"></textarea>';







	}



function asignarValores(idbtn) {
	if (idbtn == 'TICKET_TO_VALIDATE_CUSTOMER' || idbtn == 'REOPEN') {
		jQuery('#tablanovalidate').show();
		jQuery('#btn_accepted').hide();
		if (idbtn == 'TICKET_TO_VALIDATE_CUSTOMER') {
			jQuery('#status').val('TICKET_TO_VALIDATE_CUSTOMER');
			jQuery('#btn_reopen').hide();
			jQuery('#btnvalidacion').show();
		} else {
			jQuery('#status').val('OPEN');
			jQuery('#btn_val_customer').hide();
			jQuery('#btnvalidacion').show();
		}
	} else if (idbtn == 'TICKET_VALIDATE_COORDINATOR') {
		jQuery('#btn_reopen').hide();
		jQuery('#btn_val_customer').hide();

		//jQuery('#tablavalidate').show();
		jQuery('#ots').show();
		jQuery('#status').val('TICKET_VALIDATE_COORDINATOR');
		jQuery('#btnvalidacion').show();
	}

}

jQuery( document ).ready(function(  ) {
  onChangeType();
});

function actualizaListaHT() {
	var url = "index.php?action=ActivityAjax&function=ACTUALIZAR_LISTA_HT&module=HelpDesk&tipoht="+jQuery('[name="tipoht"]').val()+"&subtipoht="+jQuery('[name="subtipoht"]').val();

	jQuery.getJSON(
	url,
	function( data ) {
		if (data) {
			jQuery('#howtosid option[value!="0"]').remove();
			jQuery.each(data,function( key, value ) {
				var x = document.getElementById("howtosid");
				var option = document.createElement("option");
				option.text = value;
				option.value = key;
				x.add(option);
			});
		}
	});
}









</script>

{/literal}
<form onsubmit="return validateForm();"  style="margin:0px;" enctype="multipart/form-data" method="post" action="index.php" name="EditView">
<input type="hidden" name="module" id="module" value="{$MODULE}" />
<input type="hidden" name="action" id="action" value="soa_validacion_coordinador" />
<input type="hidden" name="funcion" id="funcion" value="Crear Caso" />
<input type="hidden" name="registro" id="registro" value="Crear Proceso Express" />

<input type="hidden" name="userid" id="userid" value="'.$user_id.'" />

<input type="hidden" name="cantidadFilas" id="cantidadFilas" value="1">
<input type="hidden" name="idcrm" id="idcrm" value="{$RECORD}" />
<input type="hidden" name="idregistro" id="idregistro" value="{$RECORD}" />
<input type="hidden" name="status" id="status" value="{$STATUS}" />

<input type="hidden" name="id_cuenta" id="id_cuenta" value="{$ACCOUNTID}" />
<div style="padding-left:0; padding-right:0; border-width:1;" class="borderForm">
<div style="height:100%;" class="content">
<input type="hidden" name="tipo" id="tipo" value="'.$_REQUEST['tipo'].'" />
	<div style="padding-left:0; padding-right:0; border-width:1;" class="borderForm">
	<div style="height:100%;" class="content">
	<div class="col-lg-12">
		<div class="main-box">
			<header class="main-box-header clearfix">
				<h1>Definición de Ordenes de Trabajo</h1>
			</header>
			<div class="main-box-body clearfix">
				<h2>Datos de la incidencia</h2>
				<div class="row">
					<div class="form-group col-lg-6">
						<label for="titulo">Referencia/Título</label>
						<input type="text" class="form-control" id="titulo" name="titulo" maxlength="64" value="{$TITLE}" readonly="readonly">
					</div>
					<div class="form-group col-lg-6">
						<label for="titulo">Cliente</label>
						<input type="text" class="form-control" value="{$ACCOUNTNAME}" readonly="readonly">
					</div>
				</div>
				<div class="row">
					<div class="form-group col-lg-6">
						<label for="titulo">Proceso</label>
						{$TYPE}
					</div>
					<div class="form-group col-lg-6">
						<label for="titulo">Documentación</label><br/>
						{$DOCUMENTACION}
					</div>
				</div>
				<div class="row>
				<label for="titulo">Requiere Testing?</label><br/>
				<input type="checkbox" name="testing"  id="testing" />
				</div>
				{if $COMENTARIOS_TESTER neq ''}
				<div class="row">
					<div class="form-group col-lg-6">
						<label for="titulo">Comentarios Tester:</label>
						{$COMENTARIOS_TESTER}
					</div>
				</div>
				{/if}
			</div>
		</div>

		<div class="main-box">
			<header class="main-box-header clearfix">
				<h1>Validación del resultado del Coordinador</h1>
			</header>
			<div class="main-box-body clearfix" style="text-align:center;">
				<button type="button" onclick="asignarValores('TICKET_VALIDATE_COORDINATOR')" class="btn btn-primary" id="btn_accepted" name="enviar" style="margin-left:20px;margin-right:20px">Ticket Validado</button>
				<button type="button" onclick="asignarValores('TICKET_TO_VALIDATE_CUSTOMER')" class="btn btn-warning" id="btn_val_customer" name="enviar" style="margin-left:20px;margin-right:20px">Ticket a Validar por cliente</button>
				<button type="button" onclick="asignarValores('REOPEN')" class="btn btn-default" id="btn_reopen" name="enviar" style="margin-left:20px;margin-right:20px">Ticket NO Valido</button>
			</div>
		</div>


		<div class="main-box" style="display:none" id="tablavalidate">
			<div class="main-box-body clearfix">
				<div class="table-responsive">

				<table class="table" id="dataValidacion">
				<tr id="row_prioridad" style="display:none">
				<td width="180" class="dvtCellLabel" colspan="2">
				Prioridad:
				</td>
				<td width="320" class="dvtCellInfo" colspan="2">
					{$PRIORIDAD}
				</td>
				</tr>
				<tr id="row_appdesarrollo" style="display:none">
				<td width="180" class="dvtCellLabel" colspan="2">
				Desarrollo aplicado en:
				</td>
				<td width="320" class="dvtCellInfo" colspan="2">
					{$APPDESARROLLO}
				</td>
				</tr>
				<tr id="row_howto" style="display:none">
				<td width="180" class="dvtCellLabel" colspan="2">
				How To:
				</td>
				<td width="320" class="dvtCellInfo" colspan="2">
				{if $HOWTO_NAME eq ''}
				<table class="small" style="width:100%">
				<tr>
				<td style="width:100px" class="dvtCellLabel">Categoria:</td>
				<td style="width:300px" class="dvtCellInfo">
				{$TIPO_HT}
				</td>
				</tr>
				<tr>
				<td style="width:100px" class="dvtCellLabel">SubCategoria:</td>
				<td style="width:300px" class="dvtCellInfo">
				{$SUBTIPO_HT}
				</td>
				</tr>
				</table>
				<select name="howtosid" id="howtosid" class="small" style="width:400px">
				<option value="0">--Seleccione--</option>
				</select>
				{else}
				{$HOWTO_NAME}
				<input name="howtosid" type="hidden" value="{$HOWTOSID}"/>
				{/if}
				</td>
				</tr>
				</table>
				</div>
			</div>
		</div>

		<div class="main-box" id="ots" {$STYLE_OTS} style="display:none">
			<div class="main-box-body clearfix">
				<div>
					<br><input type='button' name='agregar' id='agregar' class='crmbutton small create' onClick='agregarPaso1Express()' value='Agregar Paso'>
				</div>

				<div class="table-responsive">
					<table width="100%" cellspacing="0" cellpadding="5" border="0" align="left" id="testingasoc" name="testingasoc" class="table">
					<tbody>
					<tr>
						<td class="dvtCellLabel">Paso</td>


						<td class="dvtCellLabel">Tiempo Estimado</td>
						<td class="dvtCellLabel">Como hacerlo (How to)</td>
						<td class="dvtCellLabel">Comentario Coordinador</td>


					</tr>
					<tr>
					{foreach item=option from=$PASOS_PEDIDO}
								<input type="hidden" id="{$option.item_pedidoid}" name="{$option.item_pedidoid}" value="{$option.horas_estimadas}" >
							{/foreach}
						<td class="dvtCellInfo">
							<select id="tipoot1" name="tipoot1" class="small" onChange="document.getElementById('tiempo_est1').value=this.options[this.selectedIndex].value.split(',')[1]; document.getElementById('ref_howto1').href ='index.php?module=howtos&action=DetailView&record='.concat(this.options[this.selectedIndex].value.split(',')[2]); ">
							<option value="">--Seleccione--</option>
							{foreach item=option from=$PASOS_PEDIDO}
								<option value="{$option.item_pedidoid},{$option.horas_est},{$option.how_to}"
								{if $fldlabel.selected == $option}selected{/if} >
								{$option.descripcion_brev|@getTranslatedString:$option.descripcion_brev}
								</option>
							{/foreach}
							</select>
						</td>
						<td class="dvtCellInfo">

							<input type="text" id="tiempo_est1"  name="tiempo_est1" class="small" disabled="disabled" />

						</td>
						<td class="dvtCellInfo">

							<a id="ref_howto1" href="#" onClick="window.open(this.href, this.target, 'width=800,height=800'); return false;">How To</a>
						</td>
						<td class="dvtCellInfo">
							<textarea id="descrip1" name="descrip1"></textarea>
						</td>




						</tr>
					</tbody>
					</table>

					<div style="text-align:center; display:none;"  id="btnvalidacion">
						<input type="submit" class="crmbutton small edit" name="aceptar" value="Enviar">
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!--
<div style="display:none" id="tablanovalidate">
<table cellspacing="0" cellpadding="5" width="100%" border="0" id="dataNoValidacion" class="small">
<tbody>
<tr>
  <td align="" class="detailedViewHeader" colspan="4"><b>Observaciones a la Validación:</b></td>
</tr>
<tr style="height:25px">
<td align="left" id="tdinfo_texto_val_cliente" class="dvtCellInfo">
<textarea rows="2" onblur="this.className='detailedViewTextBox'" onfocus="this.className='detailedViewTextBoxOn'" class="detailedViewTextBox" tabindex="" name="texto_val_coordinador" value=""></textarea>
</td>
</tr>
</tbody>
</table>
</div>-->
<div style="display:none" id="tablanovalidate">
{$FORMA_NOTIFICACION}
</div>
<br/>

<!--grid pasos-->
<div id="ots" {$STYLE_OTS} style="display:none">
<div>
	<br><input type='button' name='agregar' id='agregar' class='crmbutton small create' onClick='agregarPaso1Express()' value='Agregar Paso'>
</div>

<table width="100%" cellspacing="0" cellpadding="5" border="0" align="left" id="testingasoc" name="testingasoc" class="small">
<tbody>
<tr>
	<td class="dvtCellLabel">Paso</td>


	<td class="dvtCellLabel">Tiempo Estimado</td>
	<td class="dvtCellLabel">Como hacerlo (How to)</td>
	<td class="dvtCellLabel">Comentario Coordinador</td>


</tr>
<tr>
{foreach item=option from=$PASOS_PEDIDO}
			<input type="hidden" id="{$option.item_pedidoid}" name="{$option.item_pedidoid}" value="{$option.horas_estimadas}" >
		{/foreach}
	<td class="dvtCellInfo">
		<select id="tipoot1" name="tipoot1" class="small" onChange="document.getElementById('tiempo_est1').value=this.options[this.selectedIndex].value.split(',')[1]; document.getElementById('ref_howto1').href ='index.php?module=howtos&action=DetailView&record='.concat(this.options[this.selectedIndex].value.split(',')[2]); ">
		<option value="">--Seleccione--</option>
		{foreach item=option from=$PASOS_PEDIDO}
			<option value="{$option.item_pedidoid},{$option.horas_est},{$option.how_to}"
			{if $fldlabel.selected == $option}selected{/if} >
			{$option.descripcion_brev|@getTranslatedString:$option.descripcion_brev}
			</option>
		{/foreach}
		</select>
	</td>
	<td class="dvtCellInfo">

		<input type="text" id="tiempo_est1"  name="tiempo_est1" class="small" disabled="disabled" />

	</td>
	<td class="dvtCellInfo">

		<a id="ref_howto1" href="#" onClick="window.open(this.href, this.target, 'width=800,height=800'); return false;">How To</a>
	</td>
	<td class="dvtCellInfo">
		<textarea id="descrip1" name="descrip1"></textarea>
	</td>




    </tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
<table cellspacing="0" align="center" cellpadding="5" border="0" width="100%" class="small">

</tbody>
</table>

<div style="text-align:center; display:none;"  id="btnvalidacion">
	<input type="submit" class="crmbutton small edit" name="aceptar" value="Enviar">
</div>
</div>

<!--fin grid pasos-->



</form>
</body>
</html>