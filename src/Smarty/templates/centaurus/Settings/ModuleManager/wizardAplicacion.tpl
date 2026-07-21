<script>
	function validarDatos() {ldelim}
		alerta = '';
		var lstFields = ["domain","company","email","address","city","state","country","phone","email","password"];
		var lstLabels = ["{$MOD.LBL_NOMBRE_CODIGO}","{$MOD.LBL_ORGANIZATION_NAME}","{$MOD.LBL_MAIL_ORGANIZATION}","{$MOD.LBL_ORGANIZATION_ADDRESS}","{$MOD.LBL_ORGANIZATION_CITY}","{$MOD.LBL_ORGANIZATION_STATE}","{$MOD.LBL_ORGANIZATION_COUNTRY}","{$MOD.LBL_ORGANIZATION_PHONE}","{$MOD.LBL_ORGANIZATION_EMAIL}","{$MOD.LBL_ORGANIZATION_PASS}"];
		
		for(i=0;i < lstFields.length;i++) {ldelim}
			field = jQuery('#'+lstFields[i]);
			if (field.val() == '') {ldelim}
				alerta+= lstLabels[i]+"\n";
			{rdelim}
		{rdelim}
		
		if (alerta != '') {ldelim}
			alert('{$MOD.LBL_MANDATORY_FIELD_MISSING}' + "\n" + alerta);
			return false;
		{rdelim}
		
		return true;
	{rdelim}
</script>
<!-- 
	Template: wizardPlataforma.tpl
	Objetivo: Presentar el dialogo donde se indica el nombre código de la plataforma
	Fecha: 2013-05-14
	Desarrollador: Leonardo Castillo Lacruz (LCL)
	
-->

<form method="post" action="index.php"  ENCTYPE="multipart/form-data" onsubmit="return validarDatos();">
<input type="hidden" name="module" value="{$MODULE}" />
<input type="hidden" name="action" value="crearAplicacion" />
<table class="table">
	<tr>
		<td colspan="2">
		<b>{$MOD.LBL_COMPANY_DETAILS}</b>
		</td>
	</tr>
	<tr>
		<td>Todas las aplicaciones</td>
		<td>
			<div class="checkbox-nice">
				<input type="checkbox" id="all_aplications" name="all_aplications" value="1" checked="checked" onclick="moduleobj.enableDisbleApp(parseInt(jQuery('#all_aplications').prop('checked')*1))">
				<label for="all_aplications">&nbsp;</label>
			</div>
		</td>
	</tr>
	<tr>
		<td>{$MOD.LBL_TIPO_APLICACION}</td>
		<td>
				<select name="instanceapps" id="instanceapps" class="form-control" disabled="disabled">
				{foreach item=arr from=$tipoapps}
					<option value="{$arr[0]}" {$arr[2]}>{$arr[1]}</option>	
				{/foreach}
			   </select>
		</td>
	</tr>
	<tr>
		<td>{$MOD.LBL_PLANTILLA_DE_WEBSITE}</td>
		<td>
				<select name="tipotemplateweb" id="tipotemplateweb" class="form-control">
				<option value="" >{$MOD.LBL_SIN_SITIO_WEB}</option>	
			   	{foreach item=arr from=$tipotemplatewebs}
					<option value="{$arr[0]}" {$arr[2]}>
						{$arr[1]}
					</option>	
				{/foreach}
			   </select>
		</td>
	</tr>
	<tr>
		<td>{$MOD.LBL_NOMBRE_CODIGO}</td>
		<td>
			<input class="form-control" type="text" id="name" name="domain" maxlength='100' value="{$NOMBRE_CODIGO}"></input>
		</td>
	</tr>
	<tr>
		<td>{$MOD.LBL_ORGANIZATION_NAME}</td>
		<td>
			<input class="form-control" type="text" id="name_organization" name="company" maxlength='100' value="{$NOMBRE_ORGANIZACION}"></input>
		</td>
	</tr>
	<tr>
		<td>{$MOD.LBL_MAIL_ORGANIZATION}</td>
		<td>
			<input class="form-control" type="text" id="email" name="email" maxlength='100' value="{$MAIL_ORGANIZATION}"></input>
		</td>
	</tr>
	<tr>
		<td>{$MOD.LBL_ORGANIZATION_ADDRESS}</td>
		<td>
			<input class="form-control" type="text" id="address" name="address" maxlength='100' value="{$ADDRESS}"></input>
		</td>
	</tr>
	<tr>
		<td>{$MOD.LBL_ORGANIZATION_CITY}</td>
		<td>
			<input class="form-control" type="text" id="city" name="city" maxlength='100' value="{$CITY}"></input>
		</td>
	</tr>
	<tr>
		<td>{$MOD.LBL_ORGANIZATION_STATE}</td>
		<td>
			<input class="form-control" type="text" id="state" name="state" maxlength='100' value="{$STATE}"></input>
		</td>
	</tr>
	<tr>
		<td>{$MOD.LBL_ORGANIZATION_COUNTRY}</td>
		<td>
			<input class="form-control" type="text" id="country" name="country" maxlength='100' value="{$COUNTRY}"></input>
		</td>
	</tr>
	<tr>
		<td>{$MOD.LBL_ORGANIZATION_CODE}</td>
		<td>
			<input class="form-control" type="text" id="code" name="code" maxlength='100' value="{$CODE}"></input>
		</td>
	</tr>
	<tr>
		<td>{$MOD.LBL_ORGANIZATION_PHONE}</td>
		<td>
			<input class="form-control" type="text" id="phone" name="phone" maxlength='100' value="{$PHONE}"></input>
		</td>
	</tr>
	<tr>
		<td>{$MOD.LBL_ORGANIZATION_FAX}</td>
		<td>
			<input class="form-control" type="text" id="fax" name="fax" maxlength='100' value="{$FAX}"></input>
		</td>
	</tr>
	<tr>
		<td>{$MOD.LBL_ORGANIZATION_WEBSITE}</td>
		<td>
			<input class="form-control" type="text" id="txtbox_website" name="website" maxlength='100' value="{$WEBSITE}"></input>
		</td>
	</tr>
	<tr>
		<td>{$MOD.LBL_ORGANIZATION_LOGO}</td>
		<td>
			<input name="logo" id="logo" value="" tabindex="" style="" type="file">
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<button class="btn btn-primary" onclick="irPaso2(document.wizardPaso1);">{$MOD.LBL_SIGUIENTE}</button>
		</td>
	</tr>
</table>
<br/>

</form>