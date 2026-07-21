<form onsubmit="return validateForm();"  style="margin:0px;" enctype="multipart/form-data" method="post" class="formDefault" action="index.php" id="crearRegistro" name="EditView">
<input type="hidden" name="module" id="module" value="HelpDesk" />
<input type="hidden" name="action" id="action" value="soa_coordinador" />
<input type="hidden" name="idcrm" id="idcrm" value="" />
<input type="hidden" name="Ajax" id="Ajax" value="true" />
<input type="hidden" name="idregistro" id="idregistro" value="" />
<div style="padding-left:0; padding-right:0; border-width:1;" class="borderForm">
<div style="height:100%;" class="content">
<table width="99%">
<tbody>
<tr id="row_coordinador">
<td width="180" class="dvtCellLabel" colspan="2">
Pedido:
</td>
<td width="320" class="dvtCellInfo" colspan="2"> 
	<input class="detailedViewTextBox" type="text" onkeypress="" style="" value="" name="title" id="title" readonly="readonly">
</td>
</tr>
<tr id="row_coordinador">
<td width="180" class="dvtCellLabel" colspan="2">
Coordinador asignado:
</td>
<td width="320" class="dvtCellInfo" colspan="2"> 
	<select id="coordinador" name="coordinador" class="small">
		<option value="">--Seleccione--</option>
		{foreach item=option from=$DESARROLLADORES}
			<option value="{$option.id}"
			{if $COORDINADOR == $option.id}selected{/if}>
			{$option.name|@getTranslatedString:$option.name}
			</option>
		{/foreach}
	</select>
</td>
</tr>
</table>
</div>
</div>
<table cellspacing="0" align="center" cellpadding="5" border="0" width="100%" class="small">
<tr>
<td align="center" colspan="4" class="FormButton">
<input type="submit" value="Enviar" name="enviar" id="enviar" class="crmbutton small edit" style="">
</td>
</tr>
</tbody>
</table>
</form>