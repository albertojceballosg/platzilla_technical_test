<script type="text/javascript" language="JavaScript">
	
	jQuery(document).ready(function() {ldelim}
		
		jQuery('#tabid').change(function(){ldelim}
			alert("getVariables");
			var url = "index.php?action=ActivityAjax&funcion=getVariables&module={$MODULE}";
			jQuery.getJSON(
			url,
			{ldelim}
				'tabid':jQuery('#tabid').val()
			{rdelim},
			function( data ) {ldelim}
				
			  var toAppend = '';
			  toAppend += '<option value="">-</option>';
			  jQuery.each( data, function( key, value ) {ldelim}
				toAppend += '<option value="'+key+'">'+value+'</option>';
			  {rdelim});
			  jQuery('#variable').append(toAppend);
			{rdelim});
		{rdelim});	
		
		jQuery('#variable').change(function(){ldelim}
			alert("getParameters");
			var url = "index.php?action=ActivityAjax&funcion=getParameters&module={$MODULE}";
			jQuery.getJSON(
			url,
			{ldelim}
				'variablesid':jQuery('#variable').val()
			{rdelim},
			function( data ) {ldelim}
				jQuery("#tabParameters").find("tr:gt(0)").remove();
				jQuery.each( data, function( key, value ) {ldelim}
					 jQuery('#tabParameters tr').last().after('<tr><td class="small cellLabel">'+key+'</td><td class="small cellText">'+value+'</td></tr>');  
				{rdelim});
				jQuery("#parameters").show();
			{rdelim});
		{rdelim});	
	{rdelim});
	
function ActivarAvisos(op){ldelim}
	var select = document.getElementById("idcopiasavisos");
	if (op <= 1) {ldelim}
		for(i=0;i<select.options.length;i++)         {ldelim}
				select.options[i].selected=false;
		{rdelim}
		for(i=0;i<select2.options.length;i++)         {ldelim}
			select2.options[i].selected=false;
		{rdelim}
		select.disabled = true;
	{rdelim} else {ldelim}
		select.disabled = false;
	{rdelim}
{rdelim}

function Validate() {ldelim}
	document.getElementById('parametro').value = 	document.getElementById('parametro').value.replace(/^\s*|\s*$/g,"");
	
	if (!document.getElementById('parametro').value) {ldelim} alert('El campo "parametro" es numerico y obligatorio!'); return false; {rdelim}
	
	if (!document.getElementById('titulo').value) {ldelim}
		alert('Debe colocar el nombre/identificador de la alerta');
		return false;
	{rdelim}
	
	return true;
{rdelim}

function probarAlerta(id, btn) {ldelim}
	jQuery(btn).attr('disabled', true);
	jQuery(btn).val('Probando..');
	
	jQuery.get('/intranet/index.php?module=alertas&action=alertasAjax&file=cronalertas&forzado=1&ida='+id,
	function(response) {ldelim}
		jQuery(btn).hide();
		alert("Alerta enviada com sucesso!");
	{rdelim});
{rdelim}
</script>







<form action="index.php"  method="post" name="tablaform" id="tablaform" autocomplete=off  onsubmit="return Validate()" style="width:100%;">
	<input type="hidden" name="module" value="{$MODULE}">
	<input type="hidden" name="action" value="Save">

	<div class="row">
		<div class="form-group col-md-6">
			<label for="titulo">Título</label>
			<input class="form-control" name="titulo" id="titulo" size="50" placeholder="Título" type="text">
		</div>
		
		<div class="form-group col-md-6">
			<label for="tabid">{$MOD.SELECT_MODULE}</label>
			<select name="tabid" id="tabid" class="form-control">
				<option value="" >-</option>
				{foreach item=arr from=$LISTA_MODULOS}
					<option value="{$arr[0]}" >
						{$arr[1]}
					</option>
				{/foreach}
			</select>
		</div>

		<div class="form-group col-md-6">
			<label for="variable">{$MOD.SELECT_VARIABLES}</label>
			<select name="variable" id="variable" class="form-control">
			</select>
		</div>

		<div class="form-group col-md-6">
			<div id="parameters" style="display:none">
				<table width="80%" cellspacing="0" cellpadding="5" border="0" class="tableHeading" id="tabParameters" style="margin-left:auto;margin-right:auto;">
				<tr>
				<td class="big" colspan="2"><strong>{$MOD.PARAMETERS}</strong></td>
				</tr>
				</table>
			</div>
		</div>

		<div class="form-group col-md-6">
			<label for="essixsigma">{$MOD.LBL_ES_CALCULADA_CON_SIXSIGMA}</label>
			<input type="checkbox" name="essixsigma" id="essixsigma">
			<br><br>
		</div>


		<div class="form-group col-md-6">
			<label for="periodicidad">{$MOD.LBL_PERIODICIDAD_ANALISIS} ({$MOD.LBL_EVALUACION_AL_FINAL_DE_CADA_PERIODO}):</label>
			<select name="periodicidad" id="periodicidad" class="form-control">
				<option value="Diario" >{$MOD.LBL_DIARIO}</option>
				<option value="Semanal" >{$MOD.LBL_SEMANAL}</option>
				<option value="Quincenal" >{$MOD.LBL_QUINCENAL}</option>
				<option value="Mensual" selected>{$MOD.LBL_MENSUAL}</option>
				<option value="Trimestral" >{$MOD.LBL_TRIMESTRAL}</option>
				<option value="Semestral" >{$MOD.LBL_SEMESTRAL}</option>
				<option value="Anual" >{$MOD.LBL_ANUAL}</option>
			</select>
		</div>

		<div class="form-group col-md-6">
			<label for="comparacion">{$MOD.LBL_COMPARACION}</label>
			<select name="comparacion" id="comparacion" class="form-control">
				<option value=">=" >>=</option>
				<option value=">" >></option>
				<option value="=" >=</option>
				<option value="!=" >!=</option>
				<option value="<" ><</option>
				<option value="<=" ><=</option>	
			</select>
		</div>

		<div class="form-group col-md-6">
			<label for="comparacion">{$MOD.LBL_VALOR}</label>
			<input id="parametro" name="parametro" type="text" value="" maxlength="20" class="form-control" />
		</div>

		<div class="form-group col-md-6">
			<label for="emails">{$MOD.LBL_EMAIL}</label>
			<select name="emails" onChange="ActivarAvisos(this.options[this.selectedIndex].value);" class="form-control">
				<option value="2" >{$MOD.LBL_SI_ENVIAR_ALERTA_SI_ENVIAR_AVISOS}</option>
				<option value="1" >{$MOD.LBL_SI_ENVIAR_ALERTA_NO_ENVIAR_AVISOS}</option>
				<option value="0" >{$MOD.LBL_NO_ENVIAR_ALERTA_NO_ENVIAR_AVISOS}</option>
			</select> 
		</div>

		<div class="form-group col-md-6">
			<label for="emailsid">{$MOD.LBL_ENVIAR_COPIA_CON_AVISO}</label>
			<select name="emailsid[]" size="3" multiple="multiple" id="emailsid" class="form-control">
			{foreach item=arr from=$LISTA_USUARIOS}
				<option value="{$arr[0]}" >
					{$arr[1]}({$arr[2]})
				</option>
			{/foreach}
			</select>
		</div>
	</div>




	<div class="row">
		<div class="form-group col-md-12 text-center">
			<input name="submitSAVE" type="submit" style="" class="btn btn-primary btn_1" value="Guardar{$APP.LBL_SAVE_LABEL}" />
		</div>
	</div>


		
</form>
	








































{*


<form action="index.php"  method="post" name="tablaform" id="tablaform" autocomplete=off  onsubmit="return Validate()" style="width:100%;">
		<input type="hidden" name="module" value="{$MODULE}">
		<input type="hidden" name="action" value="Save">
		<table border="0" align="center" width="100%" class="small">
		<tr>
			<td colspan="2" class="settingsTabHeader">
				Nueva Alerta
			</td>
		</tr>
		<tr>
			<td class="small" width="50%" nowrap="">
			<label for="titulo">Titulo</label>
			</td>
			<td class="small" width="50%" nowrap="">
			<input type="text" name="titulo" id="titulo" size="50">
			<img src="themes/images/info.jpg" width="15" title="Mais informa&ccedil;&atilde;o" style="mouse:hand" onclick="alert('');">
			</td>
		</tr>
		<tr class="dvtCellLabel">
			<td class="small" width="50%" nowrap="">
				{$MOD.SELECT_MODULE}&nbsp;
			</td>
			<td class="small" width="50%" nowrap="">
				<select name="tabid" id="tabid" class="small">
					<option value="" >-</option>
				{foreach item=arr from=$LISTA_MODULOS}
					<option value="{$arr[0]}" >
						{$arr[1]}
					</option>
				{/foreach}
				</select>
			</td>
		</tr>
		<tr class="dvtCellInfo">
			<td class="small" width="50%" nowrap="">
				{$MOD.SELECT_VARIABLES}
			</td>
			<td class="small" width="50%" nowrap="">
				<select name="variable" id="variable" class="small">
				</select>
			</td>
		</tr>
		<tr class="dvtCellInfo">
		<td colspan="2">
		<div id="parameters" style="display:none">
			<table width="80%" cellspacing="0" cellpadding="5" border="0" class="tableHeading" id="tabParameters" style="margin-left:auto;margin-right:auto;">
			<tr>
			<td class="big" colspan="2"><strong>{$MOD.PARAMETERS}</strong></td>
			</tr>
			</table>
		</div>
		</td>
		</tr>
		<tr class="dvtCellLabel">
			<td class="small" width="50%" nowrap="">
				<label class="labelClass">{$MOD.LBL_ES_CALCULADA_CON_SIXSIGMA}</label>
			</td>
			<td class="small" width="50%" nowrap="">
				<input type="checkbox" name="essixsigma" id="essixsigma">
			</td>
		</tr>
		<tr class="dvtCellInfo">
			<td width="50%" nowrap="">
				<label class="labelClass">{$MOD.LBL_PERIODICIDAD_ANALISIS}</label> <br />{$MOD.LBL_EVALUACION_AL_FINAL_DE_CADA_PERIODO}:<br />
			</td>
			<td class="small" width="50%" nowrap="">
				<select name="periodicidad" id="periodicidad" >
					<option value="Diario" >{$MOD.LBL_DIARIO}</option>
					<option value="Semanal" >{$MOD.LBL_SEMANAL}</option>
					<option value="Quincenal" >{$MOD.LBL_QUINCENAL}</option>
					<option value="Mensual" selected>{$MOD.LBL_MENSUAL}</option>
					<option value="Trimestral" >{$MOD.LBL_TRIMESTRAL}</option>
					<option value="Semestral" >{$MOD.LBL_SEMESTRAL}</option>
					<option value="Anual" >{$MOD.LBL_ANUAL}</option>
				</select>
			</td>
		</tr>
		<tr class="dvtCellLabel">
			<td class="small" width="50%" nowrap="">
				<label class="labelClass">{$MOD.LBL_COMPARACION}</label>
			</td>
			<td class="small" width="50%" nowrap="">
				<select name="comparacion" >
					<option value=">=" >>=</option>
					<option value=">" >></option>
					<option value="=" >=</option>
					<option value="!=" >!=</option>
					<option value="<" ><</option>
					<option value="<=" ><=</option>		
				</select>
			</td>
		</tr>
		<tr class="dvtCellInfo">
			<td class="small" width="50%" nowrap="">
				<label class="labelClass">{$MOD.LBL_VALOR}</label>
			</td>
			<td class="small" width="50%" nowrap="">
				<input  id="parametro" name="parametro" type="text" value="" size="20" maxlength="20"  />
			</td>
		</tr>
		<tr class="dvtCellLabel">
			<td class="small" width="50%" nowrap="">
			<label>{$MOD.LBL_EMAIL}<label>
			</td>
			<td class="small" width="50%" nowrap="">
			<select name="emails" onChange="ActivarAvisos(this.options[this.selectedIndex].value);">
				<option value="2" >{$MOD.LBL_SI_ENVIAR_ALERTA_SI_ENVIAR_AVISOS}</option>
				<option value="1" >{$MOD.LBL_SI_ENVIAR_ALERTA_NO_ENVIAR_AVISOS}</option>
				<option value="0" >{$MOD.LBL_NO_ENVIAR_ALERTA_NO_ENVIAR_AVISOS}</option>
			</select> 
			</td>
		</tr> 
		<tr class="dvtCellInfo">
			<td class="small" width="50%" nowrap="">
				<label>{$MOD.LBL_ENVIAR_COPIA_CON_AVISO}</label>
			</td>
			<td class="small" width="50%" nowrap="">
				<select name="emailsid[]" size="6" multiple="multiple" id="emailsid">
				{foreach item=arr from=$LISTA_USUARIOS}
					<option value="{$arr[0]}" >
						{$arr[1]}({$arr[2]})
					</option>
				{/foreach}
				</select>
			</td>
		</tr>
		<tr>
			<td colspan="2" align="center" >
				<input name="submitSAVE" type="submit" style="background:#F68121; color:#FFFFFF;border-radius:5px;font-weight: bolder;" class="btn_1" value="{$APP.LBL_SAVE_LABEL}" />
			</td>
		</tr>
		</table>
	</form>
	<script>


*}






<script>

var rutaDefecto = new Array();
rutaDefecto[0] = '';
		
Event.observe(window, 'load', function() {ldelim}
	//agregaCombo('variable1','55',0,'',rutaDefecto);
{rdelim});
function agregaCombo(contenedor,dashboard,nivel,ruta,rutaDefecto) {ldelim}
	var sFiltros = obtieneFiltros();
	var sUrl = 'dashboard_ajax='+dashboard+'&ruta='+ruta+sFiltros+'&periodicidad='+document.getElementById('periodicidad').value;
	var span = document.createElement('span');
	span.innerHTML = "<img src=\"themes/images/ajax-loader.gif\">";
	document.getElementById(contenedor).appendChild(span);
	new Ajax.Request(
		'../../include/apiDashBoardsVT.php',
		{ldelim}queue: {ldelim}position: 'end', scope: 'command'{rdelim},
					method: 'post',
			postBody: sUrl,
			onComplete: function(response) {ldelim}
				var json_data = response.responseText;
				document.getElementById(contenedor).removeChild(span);
				var respuesta = eval("(" + json_data + ")");
				if (typeof respuesta == "object") {ldelim}
					if (respuesta.variables.length > 0) {ldelim}
						var select = document.createElement('select');
						select.name = contenedor+'[]';
						select.id = contenedor+'_'+nivel;
						agregaOpcion(select,'-Selecionar-');
						for (i=0;i<respuesta.variables.length;i++)
							agregaOpcion(select,respuesta.variables[i].nombre);
						document.getElementById(contenedor).appendChild(select);
						agregaEscuchaActualizacionCombo(select.id,contenedor,nivel,rutaDefecto);	// permite agregar el proximo combo
						
						if(rutaDefecto && rutaDefecto[nivel]) {ldelim}
							jQuery(select).val(rutaDefecto[nivel]);
							jQuery('#'+select.id).trigger('change');
						{rdelim}
					{rdelim}
				{rdelim} else {ldelim}
					var spanvalor = document.createElement('span');
					spanvalor.id = contenedor+'_valor';
					spanvalor.innerHTML = respuesta;
					document.getElementById(contenedor).appendChild(spanvalor);
				{rdelim}
			{rdelim}
		{rdelim}
	);
{rdelim}

function agregaEscuchaActualizacionCombo(sId,contenedor,nivel, rutaDefecto) {ldelim}
	jQuery('#'+sId).change(function() {ldelim}
		var combos = document.getElementsByName(contenedor+'[]');
		var ruta = '';
		var i;
		for (i=0;i<combos.length;i++) {ldelim}
			ruta += combos[i].options[combos[i].selectedIndex].text+';';
			if (i==nivel)
				break;
		{rdelim}
		i = nivel+1;
		while (i<combos.length) {ldelim}	// borrado de los combos siguientes si hubo un cambio en el combo actual
			document.getElementById(contenedor).removeChild(combos[i]);
		{rdelim}
		if (document.getElementById(contenedor+'_valor'))
			document.getElementById(contenedor).removeChild(document.getElementById(contenedor+'_valor'));
		if (combos[nivel].selectedIndex > 0)	// se agrega el proximo combo segun la selección en el actual
			agregaCombo(contenedor,'55',nivel+1,ruta,rutaDefecto);
	{rdelim});
{rdelim}

function agregaOpcion(select,texto) {ldelim}
	var option=document.createElement("option");
	option.text=texto;
	option.value=texto;
	try
	  {ldelim}
	  // for IE earlier than version 8
	  select.add(option,x.options[null]);
	  {rdelim}
	catch (e)
	  {ldelim}
	  select.add(option,null);
	  {rdelim}
{rdelim}

function obtieneFiltros() {ldelim}
	var vFiltros=document.getElementsByName('varFiltros1[]');
	var cmbFiltros=document.getElementsByName('cmbFiltros1[]');
	var sFiltro = '';
	for (i=0;(vFiltros && i<vFiltros.length);i++) {ldelim}
		sFiltro += '&'+vFiltros[i].value+'='+cmbFiltros[i].options[cmbFiltros[i].selectedIndex].value;
	{rdelim}
	return sFiltro;
{rdelim}

function limpiaCombos(contenedor) {ldelim}
	var combos = document.getElementsByName(contenedor+'[]');
	var i = 0;
	while (i<combos.length) {ldelim}
		document.getElementById(contenedor).removeChild(combos[i]);
	{rdelim}
	if (document.getElementById(contenedor+'_valor'))
		document.getElementById(contenedor).removeChild(document.getElementById(contenedor+'_valor'));
{rdelim}
</script>