<script type="text/javascript" src="include/js/comunesTareas.js"></script>
<script type="text/javascript" src="include/jquery/jquery-1.7.1.min.js"></script>
<script>jQuery.noConflict();</script>
{literal}
<script>
function asignarValores(idbtn) {

			if (idbtn == 'REOPEN') {
				jQuery('#btn_accepted').hide();
				jQuery('#tablaobservacion').show();
				jQuery('#status').val('TICKET_OPEN');
			} else if (idbtn == 'ACCEPTED') {
				jQuery('#btn_reopen').hide();
				jQuery('#tablaobservacion').show();
				jQuery('#status').val('TICKET_VALIDATED_CUSTOMER');
			}
			jQuery('#btnvalidacion').show();
		}
</script>
{/literal}
<span class="lvtHeaderText">Validación de peticiones por parte del cliente:</span> <br>
<hr noshade="" size="1">
<form style="margin:0px;" enctype="multipart/form-data" method="post" class="formDefault" action="index.php" id="crearRegistro" name="EditView">
<input type="hidden" name="module" id="module" value="{$MODULE}" />
<input type="hidden" name="action" id="action" value="soa_validacion_cliente" />
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
{$TITLE}
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
<td width="320" class="dvtCellInfo">'
{$TYPE}
</td>
<td width="180" class="dvtCellLabel">
<font color="red">*</font>Documentacion:
</td>
<td width="320" class="dvtCellInfo" >
{$DOCUMENTACION}
</td>
</tr>
{if $TYPE eq 'Peticion'}
<tr id="row_proyecto">
<td width="180" class="dvtCellLabel" readonly="readonly">
Proyecto:
</td>
<td width="320" class="dvtCellInfo">
<select  name="proyecto" id="proyecto" onchange="loadHito('hito',this.value)" class="small">
<option value="0">--Seleccione--</option>
{$PROJECTS}
</select>
</td>
<td width="180" class="dvtCellLabel">
Hito:
</td>
<td width="320" class="dvtCellInfo">
<select  name="hito" id="hito" class="small" readonly="readonly">
<option value="">--Seleccione--</option>
</select>
</td>
</tr>
{/if}
<tr>
<td align="" colspan="4" class="detailedViewHeader">
Validación de Cliente:
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
<br/>
<table cellspacing="0" cellpadding="5" width="100%" border="0" class="small">
<tbody>
<tr>
  <td align="" class="detailedViewHeader" colspan="4">Resultado de Validación del Cliente</td>
 </tr>
<tr style="height:25px">
<td width="33%" align="center" id="tdinfo_accepted" class="dvtCellLabel">
<input type="button" onclick="asignarValores('ACCEPTED')" class="crmbutton small edit" id="btn_accepted" name="enviar" value="Ticket Validado">
</td>
<td width="33%" align="center" id="tdinfo_reopen" class="dvtCellInfo">
<input type="button" onclick="asignarValores('REOPEN')" class="crmbutton small delete" id="btn_reopen" name="enviar" value="Ticket NO Valido">
</td>
</tr>
</tbody>
</table>

<div style="display:none" id="tablaobservacion">
<table cellspacing="0" cellpadding="5" width="100%" border="0" id="dataValidacion" class="small">
<tbody>
<tr>
  <td align="" class="detailedViewHeader" colspan="4"><b>Observaciones a la Validación:</b></td>
</tr>
<tr style="height:25px">
<td align="left" id="tdinfo_texto_val_cliente" class="dvtCellInfo">
<textarea rows="2" onblur="this.className='detailedViewTextBox'" onfocus="this.className='detailedViewTextBoxOn'" class="detailedViewTextBox" tabindex="" name="texto_val_cliente" value=""></textarea>
</td>
</tr>
</tbody>
</table>
</div>
<br/>
<div style="text-align:center; display:none;"  id="btnvalidacion">
	<input type="submit" class="crmbutton small edit" name="aceptar" value="  Enviar  ">
</div>
</form>