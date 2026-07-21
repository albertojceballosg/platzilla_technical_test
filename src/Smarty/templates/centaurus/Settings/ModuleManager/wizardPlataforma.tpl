<script>
	function validarDatos() {ldelim}
		alerta = '';
		var lstFields = ["domain","company","address","city","state","country","phone","email","password"];
		var lstLabels = ["{$MOD.LBL_NOMBRE_CODIGO}","{$MOD.LBL_ORGANIZATION_NAME}","{$MOD.LBL_ORGANIZATION_ADDRESS}","{$MOD.LBL_ORGANIZATION_CITY}","{$MOD.LBL_ORGANIZATION_STATE}","{$MOD.LBL_ORGANIZATION_COUNTRY}","{$MOD.LBL_ORGANIZATION_PHONE}","{$MOD.LBL_ORGANIZATION_EMAIL}","{$MOD.LBL_ORGANIZATION_PASS}"];
		
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

<form method="post" action="index.php"  ENCTYPE="multipart/form-data" id="formWizardPlataforma" onsubmit="return moduleobj.validaPaso(this);">
<input type="hidden" name="module" value="{$MODULE}" />
<input type="hidden" name="action" value="crearPlataforma" />
<table width="100%" cellspacing="0" cellpadding="0" border="0" class="small">
	<tr>
		<td class="detailedViewHeader" colspan="2">
		<b>{$MOD.LBL_COMPANY_DETAILS}</b>
		</td>
	</tr>
	<tr>
		<td class="dvtCellLabel">{$MOD.LBL_NOMBRE_CODIGO}</td>
		<td class="dvtCellInfo">
			<input class="detailedViewTextBox" onFocus="this.className='detailedViewTextBoxOn'" onBlur="this.className='detailedViewTextBox'" type="text" id="txtbox_name" name="domain" maxlength='100' value="{$NOMBRE_CODIGO}" required_type="textnowhitespaces"  field_label="LBL_NOMBRE_CODIGO"></input>
		</td>
	</tr>
	<tr>
		<td class="dvtCellLabel">{$MOD.LBL_ORGANIZATION_NAME}</td>
		<td class="dvtCellInfo">
			<input class="detailedViewTextBox" onFocus="this.className='detailedViewTextBoxOn'" onBlur="this.className='detailedViewTextBox'" type="text" id="txtbox_name_organization" name="company" maxlength='100' value="{$NOMBRE_ORGANIZACION}" required_type="text"  field_label="LBL_ORGANIZATION_NAME"></input>
		</td>
	</tr>
	<tr>
		<td class="dvtCellLabel">{$MOD.LBL_ORGANIZATION_ADDRESS}</td>
		<td class="dvtCellInfo">
			<input class="detailedViewTextBox" onFocus="this.className='detailedViewTextBoxOn'" onBlur="this.className='detailedViewTextBox'" type="text" id="txtbox_address" name="address" maxlength='100' value="{$ADDRESS}"></input>
		</td>
	</tr>
	<tr>
		<td class="dvtCellLabel">{$MOD.LBL_ORGANIZATION_CITY}</td>
		<td class="dvtCellInfo">
			<input class="detailedViewTextBox" onFocus="this.className='detailedViewTextBoxOn'" onBlur="this.className='detailedViewTextBox'" type="text" id="txtbox_city" name="city" maxlength='100' value="{$CITY}"></input>
		</td>
	</tr>
	<tr>
		<td class="dvtCellLabel">{$MOD.LBL_ORGANIZATION_STATE}</td>
		<td class="dvtCellInfo">
			<input class="detailedViewTextBox" onFocus="this.className='detailedViewTextBoxOn'" onBlur="this.className='detailedViewTextBox'" type="text" id="txtbox_state" name="state" maxlength='100' value="{$STATE}"></input>
		</td>
	</tr>
	<tr>
		<td class="dvtCellLabel">{$MOD.LBL_ORGANIZATION_COUNTRY}</td>
		<td class="dvtCellInfo">
			<input class="detailedViewTextBox" onFocus="this.className='detailedViewTextBoxOn'" onBlur="this.className='detailedViewTextBox'" type="text" id="txtbox_country" name="country" maxlength='100' value="{$COUNTRY}"></input>
		</td>
	</tr>
	<tr>
		<td class="dvtCellLabel">{$MOD.LBL_ORGANIZATION_CODE}</td>
		<td class="dvtCellInfo">
			<input class="detailedViewTextBox" onFocus="this.className='detailedViewTextBoxOn'" onBlur="this.className='detailedViewTextBox'" type="text" id="txtbox_code" name="code" maxlength='100' value="{$CODE}"></input>
		</td>
	</tr>
	<tr>
		<td class="dvtCellLabel">{$MOD.LBL_ORGANIZATION_PHONE}</td>
		<td class="dvtCellInfo">
			<input class="detailedViewTextBox" onFocus="this.className='detailedViewTextBoxOn'" onBlur="this.className='detailedViewTextBox'" type="text" id="txtbox_phone" name="phone" maxlength='100' value="{$PHONE}"></input>
		</td>
	</tr>
	<tr>
		<td class="dvtCellLabel">{$MOD.LBL_ORGANIZATION_FAX}</td>
		<td class="dvtCellInfo">
			<input class="detailedViewTextBox" onFocus="this.className='detailedViewTextBoxOn'" onBlur="this.className='detailedViewTextBox'" type="text" id="txtbox_fax" name="fax" maxlength='100' value="{$FAX}"></input>
		</td>
	</tr>
	<tr>
		<td class="dvtCellLabel">{$MOD.LBL_ORGANIZATION_WEBSITE}</td>
		<td class="dvtCellInfo">
			<input class="detailedViewTextBox" onFocus="this.className='detailedViewTextBoxOn'" onBlur="this.className='detailedViewTextBox'" type="text" id="txtbox_website" name="website" maxlength='100' value="{$WEBSITE}"></input>
		</td>
	</tr>
	<tr>
		<td class="dvtCellLabel">{$MOD.LBL_ORGANIZATION_LOGO}</td>
		<td class="dvtCellInfo">
			<input name="logo" id="logo" value="" tabindex="" style="" type="file" required_type="text" field_label="LBL_ORGANIZATION_LOGO">
		</td>
	</tr>
	<tr>
		<td class="dvtCellLabel">{$MOD.LBL_CHOOSE_COMPLETE_PLATFORM}</td>
		<td class="dvtCellInfo">
			<label for="all_aplications" style="cursor:pointer">
				<input type="radio" id="all_aplications" name="all_aplications" value="1" style=" vertical-align: bottom; " onclick="moduleobj.enableDisbleApp(this.value)"/>&nbsp;{$MOD.LBL_YES}
			</label>
			&nbsp;&nbsp;
			<label for="all_aplications_no"  style="cursor:pointer">
				<input type="radio" id="all_aplications_no" name="all_aplications" value="0" style=" vertical-align: bottom; " checked  onclick="moduleobj.enableDisbleApp(this.value)"/>&nbsp;{$MOD.LBL_NO}
			</label>
		</td>
	</tr>
	<tr>
		<td class="dvtCellLabel">{$MOD.LBL_TIPO_APLICACION}</td>
		<td class="dvtCellInfo">
				<select multiple name="instanceapps[]" id="instanceapps" class="small" required_type="select" dependency="all_aplications" field_label="LBL_TIPO_APLICACION">
				{foreach item=arr from=$tipoapps}
					<option value="{$arr[0]}" {$arr[2]}>{$arr[1]}</option>	
				{/foreach}
			   </select>
		</td>
	</tr>
</table>
<br/>
<input type="submit" class="crmbutton small edit" value='{$MOD.LBL_SIGUIENTE}' title='{$MOD.LBL_SIGUIENTE}' onclick="irPaso2(document.wizardPaso1);" />
</form>