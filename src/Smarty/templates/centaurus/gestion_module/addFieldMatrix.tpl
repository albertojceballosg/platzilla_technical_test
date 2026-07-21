<!-- 
	Template: ModuloDefinicion.tpl
	Objetivo: Presentar el paso inicial de definición de datos para construir un nuevo Módulo
	Fecha: 2013-04-02
	Desarrollador: Leonardo Castillo Lacruz (LCL)
	
-->
<script type="text/javascript">
function irPaso(form,action)
{ldelim}
	form.action.value = action;
		new Ajax.Request('index.php', {ldelim}
			method: form.method,
			postBody: Form.serialize(form),
			onComplete: function(response) {ldelim}
								window.location.reload();
						{rdelim}
		{rdelim});
{rdelim}

</script>

<form method="post" action="index.php" onsubmit="return false;" name="wizardPaso3">
<input type="hidden" name="module" value="{$MODULE}" />
<input type="hidden" name="fldmodule" value="{$_FLD_MODULE}" />
<input type="hidden" name="action" id="action" value="" />
<input type="hidden" name="Ajax" value="true" />
<div class="md-content">
	<div class="modal-header">
		<h4 class="modal-title" id="labelDiv">{$MOD.LBL_DEFINICION_CAMPO_MATRIZ}</h4>
	</div>
	<div class="modal-body">
		<table width="100%" border="0" cellpadding="3" cellspacing="0" class="layerHeadingULine" >
			<tr>
				<td width="50%">
					{$MOD.LBL_NOMBRE_CAMPO}
				</td>
				<td width="50%" align="right">
					<input type="text" name="nombreMatrix" id="nombreMatrix" maxlength='100' value="" class="form-control"></input>
				</td>
			</tr>
			<tr>
				<td width="45%">
					{$MOD.LBL_ETIQUETA_CAMPO}
				</td>
				<td width="45%" align="right">
					<input type="text" name="etiquetaMatrix" id="etiquetaMatrix" maxlength='100' value="" class="form-control"></input>
				</td>
			</tr>
			<tr>
				<td width="50%">
					{$MOD.LBL_NOMBRE_CAMPO_FILAS}
				</td>
				<td width="50%" align="right">
					<select id="field_rows" name="field_rows"  class="form-control">
						{foreach item=arr from=$picklist}
							<option value="{$arr[1]}" {$arr[2]}>{$arr[0]}</option>
						{/foreach}
					</select>
				</td>
			</tr>
			<tr>
				<td width="50%">
					{$MOD.LBL_NOMBRE_CAMPO_COLUMNAS}
				</td>
				<td width="50%" align="right">
					<select id="field_cols" name="field_cols" class="form-control">
						{foreach item=arr from=$picklist}
							<option value="{$arr[1]}" {$arr[2]}>{$arr[0]}</option>
						{/foreach}
					</select>
				</td>
			</tr>
		</table>
	</div>
	<div class="modal-footer">
		<input type="button" name="save" value=" {$APP.LBL_SAVE_BUTTON_LABEL}" class="btn btn-primary"  onclick="irPaso(document.wizardPaso3,'addFieldMatrix');" />&nbsp;
		<button class="btn btn-danger md-close" id="btnclose" onclick="jQuery('#modal').removeClass('md-show');jQuery('.md-overlay').css({ldelim}opacity: 0.0, visibility: 'hidden'{rdelim});">{$APP.LBL_CANCEL_BUTTON_LABEL}</button>
	</div>	
</div>
</form>