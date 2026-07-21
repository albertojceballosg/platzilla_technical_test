<script type="text/javascript" src="include/js/comunesTareas.js"></script>
<script type="text/javascript" src="include/jquery/jquery-1.7.1.min.js"></script>
<script>jQuery.noConflict();</script>
<span class="lvtHeaderText">Definici&oacute;n de Ordenes de Trabajo:</span> <br>
<hr noshade="" size="1">
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
	fechafin = document.getElementById('jscal_field_datefin'+i);*/
	sstartDate = jQuery('#jscal_field_dateinicio'+i).val();
	sendDate = jQuery('#jscal_field_datefin'+i).val();
	var startDate = new Date(sstartDate.substr(6, 4),sstartDate.substr(3, 2),sstartDate.substr(0, 2));
	var endDate = new Date(sendDate.substr(6, 4),sendDate.substr(3, 2),sendDate.substr(0, 2));

	if (startDate > endDate){
		alert('Fecha de inicio mayor a fecha de fin en la OT No.'+i);
		return false;
	}
	/*
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

if (jQuery('#row_proyecto').is(":visible")) {
	if (jQuery('#proyecto').val() == '0') {
		alert('Todo desarrollo debe llevar un proyecto asociado. Por favor verifique');
		return false;
	}
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
			if (data.permisos) {
				jQuery('#row_permisos').show();
			} else {
				jQuery('#row_permisos').hide();
			}
			if (data.howto) {
				jQuery('#row_howto').show();
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
<input type="hidden" name="action" id="action" value="soa_analisis" />
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
<td align="" colspan="4" class="detailedViewHeader">Datos de la incidencia:</td>
</tr>
<tr>
<td width="180" class="dvtCellLabel">
<font color="red">*</font>Titulo:
</td>
<td width="320" class="dvtCellInfo">
<input class="detailedViewTextBox" type="text" onkeypress="" style="" value="{$TITLE}" maxlength="40" size="64" name="titulo" id="titulo" readonly="readonly">
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
{if $TYPEVAL neq ''}
	<input type="text" name="type" id="fld_type" value="{$TYPEVAL}" readonly="readonly" class="detailedViewTextBox"/>
{else}
	{$TYPE}
{/if}
</td>
<td width="180" class="dvtCellLabel">
<font color="red">*</font>Documentacion:
</td>
<td width="320" class="dvtCellInfo" >
{$DOCUMENTACION}
</td>
</tr>
{if $REQUIERE_VALIDACION_CLIENTE neq 'false'}
<tr >
<td width="180" class="dvtCellLabel" colspan="2">
Requiere validación de Cliente:
</td>
<td width="320" class="dvtCellInfo" colspan="2">
	<input type="checkbox" name="reqvalidacion" id="reqvalidacion" value="1" checked="checked" onClick="onClickValidacion()">
</td>
</tr>
{/if}
<tr style="display:none" id="row_proyecto">
<td width="180" class="dvtCellLabel">
Proyecto:
</td>
<td width="320" class="dvtCellInfo">
{if $PROJECT_NAME eq ''}
<select  name="proyecto" id="proyecto" onchange="loadHito('hito',this.value)" class="small">
<option value="0">--Seleccione--</option>
{$PROJECTS}
</select>
{else}
{$PROJECT_NAME}
<input name="proyecto" type="hidden" value="{$PROJECTID}"/>
{/if}
</td>
<td width="180" class="dvtCellLabel">
Hito:
</td>
<td width="320" class="dvtCellInfo">
{if $HITO_NAME eq ''}
<select  name="hito" id="hito" class="small">
<option value="">--Seleccione--</option>
</select>
{else}
{$HITO_NAME}
<input name="hito" type="hidden" value="{$HITOID}"/>
{/if}
</td>
</tr>

<tr id="row_video" style="display:none">
<td width="180" class="dvtCellLabel" colspan="2">
Video:
</td>
<td width="320" class="dvtCellInfo" colspan="2">
<input type="file" name="video" id="video">
</td>
</tr>
{if $REQUIERE_DOC_REQUISITOS neq 'false'}
<tr id="row_docrequisitos" style="display:none">
<td width="180" class="dvtCellLabel" colspan="2">
Documento de Requisitos:
</td>
<td width="320" class="dvtCellInfo" colspan="2">
	<input type='hidden' class='small' name="docrequisitos_type" id="docrequisitos_type" value="documento_requisito">
	<input id="docrequisitos" name="docrequisitos" type="hidden" value="">
	<input id="docrequisitos_display" name="docrequisitos_display" readonly="" type="text" style="border:1px solid #bababa;" value="">&nbsp;
	<img src="themes/softed/images/select.gif" tabindex="" alt="Select" title="Select" language="javascript" onclick="return window.open(&quot;index.php?module=&quot;+ document.EditView.docrequisitos_type.value +&quot;&amp;action=Popup&amp;html=Popup_picker&amp;form=vtlibPopupView&amp;forfield=docrequisitos&amp;srcmodule=HelpDesk&amp;forrecord=50&quot;,&quot;test&quot;,&quot;width=640,height=602,resizable=0,scrollbars=0,top=150,left=200&quot;);" align="absmiddle" style="cursor:hand;cursor:pointer">&nbsp;
	<input type="image" src="themes/images/clear_field.gif" alt="Clear" title="Clear" language="javascript" onclick="this.form.docrequisitos.value=''; this.form.docrequisitos_display.value=''; return false;" align="absmiddle" style="cursor:hand;cursor:pointer">&nbsp;
</td>
</tr>
{/if}
<tr id="row_coordinador" style="display:none">
<td width="180" class="dvtCellLabel" colspan="2">
Coordinador asignado:
</td>
<td width="320" class="dvtCellInfo" colspan="2">
	<select class="small" disabled="disabled">
		<option value="">--Seleccione--</option>
		{foreach item=option from=$DESARROLLADORES}
			<option value="{$option.id}"
			{if $COORDINADOR == $option.id}selected{/if}>
			{$option.name|@getTranslatedString:$option.name}
			</option>
		{/foreach}
	</select>
	<input type="hidden" id="coordinador" name="coordinador" value="{$COORDINADOR}"/>
</td>
</tr>
<tr id="row_hours">
<td width="180" class="dvtCellLabel" colspan="2">
Horas estimadas:
</td>
<td width="320" class="dvtCellInfo" colspan="2">
	<input  type="text" onkeypress="" style="" value="{$TOTAL_HORAS_EST[0][0]}" maxlength="40" size="12" name="hours" id="hours" readonly="readonly">
</td>
</tr>
<tr id="row_prioridad" style="display:none">
<td width="180" class="dvtCellLabel" colspan="2">
Prioridad:
</td>
<td width="320" class="dvtCellInfo" colspan="2">
	{$PRIORIDAD}
</td>
</tr>
<tr id="row_permisos" style="display:none">
<td width="180" class="dvtCellLabel" colspan="2">
Requiere Permisos GIT/FTP:
</td>
<td width="320" class="dvtCellInfo" colspan="2">
	<input type="checkbox" name="permisos" id="permisos" value="2" {$CHECK_PERMISOS}>
</td>
</tr>
<tr>
<td width="180" class="dvtCellLabel" colspan="2">
How To:
</td>
<td width="320" class="dvtCellInfo" colspan="2">
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

</tr>
<tr>
<td width="180" class="dvtCellLabel" colspan="1">
Fecha Inicio:
</td>
<td width="320" class="dvtCellInfo" colspan="1" >
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

	<td width="180" class="dvtCellLabel" colspan="1">
Fecha de Fin:
</td>
<td width="320" class="dvtCellInfo" colspan="1">
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
<tr>
<td width="180" class="dvtCellLabel" colspan="2">
Comentarios Generales del Coordinador:
</td>
<td  width="320" class="dvtCellInfo" colspan="2">
		<textarea id="descrip1" name="descrip1"></textarea>
	</td>
</tr>
</table>
<br>
{if $REQUIERE_VALIDACION_CLIENTE neq 'false'}
{assign var=STYLE_OTS value='style="display:none"'}
{else}
{assign var=STYLE_OTS value=''}
{/if}
<!--grid pasos-->
<div id="ots" {$STYLE_OTS} style="display:none">


<table width="100%" cellspacing="0" cellpadding="5" border="0" align="left" id="testingasoc" name="testingasoc" class="small">
<tbody>
<tr>
	<td class="dvtCellLabel">Paso</td>


	<td class="dvtCellLabel">Tiempo Estimado</td>
	<td class="dvtCellLabel">Como hacerlo (How to)</td>
	<td class="dvtCellLabel">Comentario Coordinador</td>


</tr>

{foreach item=option from=$PASOS_PED}
<tr>
			<input type="hidden" id="{$option.item_pedidoid}" name="{$option.item_pedidoid}" value="{$option.horas_estimadas}" >

	<td class="dvtCellInfo">
		<input type="text" id="desc_paso"  name="desc_paso" class="small" disabled="disabled" value="{$option.descripcion_brev}"/>
	</td>
	<td class="dvtCellInfo">

		<input type="text" id="tiempo_est1"  name="tiempo_est1" class="small" disabled="disabled" value="{$option.horas_est}"/>

	</td>
	<td class="dvtCellInfo">

		<a id="ref_howto" href="index.php?module=howtos&action=DetailView&record={$option.how_to}" onClick="window.open(this.href, this.target, 'width=800,height=800'); return false;">How To</a>
	</td>
	<td class="dvtCellInfo">
		<textarea id="coment_coor" name="coment_coor" value="{$option.observacion_coo}">{$option.observacion_coo}</textarea>
	</td>

	 </tr>
	{/foreach}


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

<!--fin grid pasos-->



</td>
</tr>
</tbody>
</table>
<table cellspacing="0" align="center" cellpadding="5" border="0" width="100%" class="small">

</tbody>
</table>

</div>
</form>