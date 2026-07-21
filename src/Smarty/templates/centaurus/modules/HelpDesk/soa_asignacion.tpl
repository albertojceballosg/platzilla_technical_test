<script type="text/javascript" src="include/js/comunesTareas.js"></script>
<script type="text/javascript" src="include/jquery/jquery-1.7.1.min.js"></script>
<script>jQuery.noConflict();</script>
<span class="lvtHeaderText">Definici&oacute;n de Ordenes de Trabajo:</span> <br>
<hr noshade="" size="1">
{literal}
<script>

function validateForm(){

//Se determina la fecha menor y la fecha mayor y se asigna
bFecha = true;
i = 1;
while (bFecha) {
	fecha = document.getElementById('jscal_field_dateinicio'+i);
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

		var url = "index.php?action=ActivityAjax&function=ACTUALIZAR_TIPO_OTS&module=HelpDesk";

		jQuery.getJSON(
		url,
		{
			'type':jQuery('#fld_type').val()
		},
		function( data ) {
			i = 1;
			while (jQuery("#tipoot"+i).length > 0) {
				jQuery("#tipoot"+i).empty();
				jQuery('#tipoot'+i).append(new Option('--Seleccione--', ''));
				jQuery.each( data, function( key, value ) {
					jQuery('#tipoot'+i).append(new Option(value.typeot, value.otadminid));
				});
				i++;
			}
		});

		if (requiere_validacion_cliente == 'false') {
			jQuery('#ots').show();
		}
	});
}

function onClickValidacion() {
	if (jQuery('#reqvalidacion').is(':checked')) {
		jQuery('#ots').hide();
	}else {
		jQuery('#ots').show();
	}
}

jQuery( document ).ready(function(  ) {
  onChangeType();
});
</script>
{/literal}
<form onsubmit="return validateForm();"  style="margin:0px;" enctype="multipart/form-data" method="post" class="formDefault" action="index.php" id="crearRegistro" name="EditView">
<input type="hidden" name="module" id="module" value="{$MODULE}" />
<input type="hidden" name="action" id="action" value="soa_asignacion" />
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
<table width="99%">
<tbody>
<tr>
<td valign="top">
<table cellspacing="0" cellpadding="5" border="0" width="100%" class="small">
<tbody>
<tr>
<td align="" colspan="4" class="detailedViewHeader">Datos del pedido:</td>
</tr>
<tr>
<td width="180" class="dvtCellLabel">
<font color="red">*</font>Titulo:
</td>
<td width="320" class="dvtCellInfo">
<input class="detailedViewTextBox" type="text" onkeypress="" style="" value="{$TITLE}" maxlength="40" size="64" name="titulo" id="titulo">
</td>
<td width="180" class="dvtCellLabel">
<font color="red">*</font>Cuenta:
</td>
<td width="320" class="dvtCellInfo">{$ACCOUNTNAME}
</td>
</tr>
<tr>
<td width="180" class="dvtCellLabel">
<font color="red"></font>Proceso:
</td>
<td width="320" class="dvtCellInfo">
{$TYPE}
</td>
<td width="180" class="dvtCellLabel">
<font color="red">*</font>Documentacion:
</td>
<td width="320" class="dvtCellInfo" >
{$DOCUMENTACION}
</td>
</tr>
<tr id="row_proyecto">
<td width="180" class="dvtCellLabel">
Proyecto:
</td>
<td width="320" class="dvtCellInfo">
{$PROJECT}
</td>
<td width="180" class="dvtCellLabel">
Hito:
</td>
<td width="320" class="dvtCellInfo">
{$HITO}
</td>
</tr>

{if $VIDEOVAL neq ''}
<tr id="row_video">
<td width="180" class="dvtCellLabel" colspan="2">
Video asociado:
</td>
<td width="320" class="dvtCellInfo" colspan="2">
{$VIDEOVAL}
</td>
</tr>
{/if}
{if $DOCREQUISITOS neq ''}
<tr id="row_docrequisitos">
<td width="180" class="dvtCellLabel" colspan="4">
Requisitos definidos:
</td>
</tr>
<tr>
<td width="320" class="dvtCellInfo" colspan="4">
	{$DOCREQUISITOS}
</td>
</tr>
{/if}

</table>
<br>
<div id="ots">
<div>
	<br><input type='button' name='agregar' id='agregar' class='crmbutton small create' onClick='agregarExpress()' value='Agregar'>
</div>

<table width="100%" cellspacing="0" cellpadding="5" border="0" align="left" id="testingasoc" name="testingasoc" class="small">
<tbody>
<tr>
	<td class="dvtCellLabel">Subproceso</td>
	<td class="dvtCellLabel">Desarrollador Asignado</td>
	<td class="dvtCellLabel">Descripcion</td>
    <td style="display:none" class="dvtCellLabel">Turno </td>
	<td class="dvtCellLabel">Fecha de Inicio</td>
	<td class="dvtCellLabel">Fecha de Fin</td>
</tr>
<tr>
	<td class="dvtCellInfo">
		<select id="tipoot1" name="tipoot1" class="small">
		<option value="">--Seleccione--</option>
		{foreach item=option from=$TIPOS_OTS}
			<option value="{$option.otadminid}"
			{if $fldlabel.selected == $option}selected{/if}>
			{$option.typeot|@getTranslatedString:$option.typeot}
			</option>
		{/foreach}
		</select>
	</td>
	<td class="dvtCellInfo">
		<select id="desarrollador1" name="desarrollador1" class="small">
		<option value="">--Seleccione--</option>
		{foreach item=option from=$DESARROLLADORES}
			<option value="{$option.id}"
			{if $fldlabel.selected == $option}selected{/if}>
			{$option.name|@getTranslatedString:$option.name}
			</option>
		{/foreach}
		</select>
	</td>
	<td class="dvtCellInfo">
		<textarea id="descrip1" name="descrip1"></textarea>
	</td>
	<td style="display:none" class="dvtCellInfo">
		<select id="turno1" name="turno1">
		<option value="man">Mañana</option>
		<option value="tarde">Tarde</option>
		</select>
	</td>
	<td class="dvtCellInfo">
		<input type="text" maxlength="10" size="11" value="" tabindex="" style="border: 1px solid rgb(186, 186, 186);" id="jscal_field_dateinicio1" name="dateinicio1">
		<img src="themes/softed/images/btnL3Calendar.gif" id="jscal_trigger_dateinicio1">
{literal}
		<script type="text/javascript" id="massedit_calendar_dateinicio1">

			Calendar.setup ({

				inputField : 'jscal_field_dateinicio1', ifFormat : '%d-%m-%Y', showsTime : false, button : 'jscal_trigger_dateinicio1', singleClick : true, step : 1

			})

		</script>
{/literal}
	</td>
	<td class="dvtCellInfo">
		<input type="text" maxlength="10" size="11" value="" tabindex="" style="border: 1px solid rgb(186, 186, 186);" id="jscal_field_datefin1" name="datefin1">
		<img src="themes/softed/images/btnL3Calendar.gif" id="jscal_trigger_datefin1">
{literal}
		<script type="text/javascript" id="massedit_calendar_datefin1">

			Calendar.setup ({

				inputField : 'jscal_field_datefin1', ifFormat : '%d-%m-%Y', showsTime : false, button : 'jscal_trigger_datefin1', singleClick : true, step : 1

			})

		</script>
{/literal}
	</td>

    </tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
<table cellspacing="0" align="center" cellpadding="5" border="0" width="100%" class="small">
<tr>
<td align="center" colspan="4" class="FormButton">
<input type="submit" value="Enviar" name="enviar" id="enviar" class="crmbutton small edit" style="">
</td>
</tr>
</tbody>
</table>
</div>
</form>