<script type="text/javascript" language="JavaScript">
	
	jQuery(document).ready(function() {ldelim}
		
		jQuery('#boxscoreid').change(function(){ldelim}
			console.log('entrando change boxscoreid formparametrosLSSI.tpl')
			var boxscoreid = jQuery('#boxscoreid').val();
			var url = "index.php?module=alertas&action=ActivityAjax&funcion=getindicadors&boxscoreid="+boxscoreid;
			jQuery.getJSON(
			url,
			{ldelim}
				'boxscoreid':jQuery('#boxscoreid').val()
			{rdelim},
			function( data ) {ldelim}
				jQuery('#indicador').empty();
				console.log(data)
			  var toAppend = '';
			  toAppend += '<option value="">-</option>';
			  jQuery.each( data, function( key, value ) {ldelim}
				toAppend += '<option value="'+key+'">'+value+'</option>';
			  {rdelim});
			  jQuery('#indicador').append(toAppend);
			{rdelim});
		{rdelim});	
		
	{rdelim});
	


function ValidateBS() {ldelim}
	
	document.getElementById('parametroBS').value = 	document.getElementById('parametroBS').value.replace(/^\s*|\s*$/g,"");
	
	if (!jQuery('#tituloBS').val()) {ldelim} 
		alert('Debe colocar el nombre/identificador de la alerta');
		return false; 
	{rdelim}

	if (!jQuery('#boxscoreid').val()) {ldelim} 
		alert('Seleccione el boxscore asociado a la alerta');
		return false; 
	{rdelim}

	if (!jQuery('#indicador').val()) {ldelim} 
		alert('Seleccione el indicador asociado a la alerta');
		return false; 
	{rdelim}

	if (!jQuery('#parametroBS').val()) {ldelim} 
		alert('El campo "Valor" es numerico y obligatorio!'); 
		return false; 
	{rdelim}
	
	
	
	{* Validando responsable de alerta *}
	if(jQuery('#createNC').is(':checked') || jQuery('#sendEmail').is(':checked') ) {ldelim}

		if (!jQuery('#emailsid').val()) {ldelim} 
			alert('Debe selecccionar un responsable de la Alerta'); 
			return false; 
		{rdelim}
	    
	    {*
	    if( jQuery('#emailsid').has('option').length > 0 ) {ldelim}

	    	var selectedEmailsCounter = 0;
    		jQuery( "#emailsid option" ).each(function() {ldelim}
			    if (jQuery(this).is(':selected')){ldelim}
			    	selectedEmailsCounter++;
			    {rdelim}
		    {rdelim});

		    if (selectedEmailsCounter == 0){ldelim}
		    	alert('Debe selecccionar un responsable de la Alerta');
		    	return false;
		    {rdelim}

	    {rdelim}

	    *}

	{rdelim}

	var  url = 'module={$MODULE}&action={$MODULE}Ajax&file=validateAlerts&ajax=true&mode=create&boxscoreid='+jQuery('#boxscoreid').val()+'&indicador='+jQuery('#indicador').val();

	new Ajax.Request(
		'index.php',
		{ldelim}asynchronous : false,
			cache: false,
			queue: {ldelim}position: 'end', scope: 'command'},
			method: 'post',
			postBody:url,
			onComplete: function(response) {ldelim}
				if ( response.responseText != 'ok' ) {ldelim}
						alert('Ya existe una alerta con el mismo Boxscore e Indicador');
	    				return false;
				{rdelim}else{ldelim}
					jQuery('#tablaform').submit();
				{rdelim}
			{rdelim}
		{rdelim}
	);

	
	return false;
{rdelim}


</script>







<form action="index.php"  method="post" name="tablaform" id="tablaform" autocomplete=off  onsubmit="" style="width:100%;">
	<input type="hidden" name="module" value="{$MODULE}">
	<input type="hidden" name="action" value="Save">

	<div class="row">
		<div class="form-group col-md-6">
			<label for="tituloBS">{$MOD.LBL_TITLE}</label>
			<input class="form-control" name="tituloBS" id="tituloBS" size="50" placeholder="{$MOD.LBL_TITLE}" type="text">
		</div>
		
		<div class="form-group col-md-6">
			<label for="boxscoreid">{$MOD.SELECT_BOXSCORE}</label>
			<select name="boxscoreid" id="boxscoreid" class="form-control">
				<option value="" >-</option>
				{foreach key=keyBS item=valBS from=$LISTA_BOXSCORE}
					<option value="{$keyBS}" >
						{$valBS}
					</option>
				{/foreach}
			</select>
		</div>

		<div class="form-group col-md-6">
			<label for="indicador">{$MOD.SELECT_INDICADOR}</label>
			<select name="indicador" id="indicador" class="form-control">
			</select>
		</div>

		
		<div class="form-group col-md-6">
			<label for="periodicidadBS">{$MOD.LBL_PERIODICIDAD_ANALISIS} ({$MOD.LBL_EVALUACION_AL_FINAL_DE_CADA_PERIODO}):</label>
			<select name="periodicidadBS" id="periodicidadBS" class="form-control">
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
			<label for="comparacionBS">{$MOD.LBL_COMPARACION}</label>
			<select name="comparacionBS" id="comparacionBS" class="form-control">
				<option value=">=" >>=</option>
				<option value=">" >></option>
				<option value="=" >=</option>
				<option value="!=" >!=</option>
				<option value="<" ><</option>
				<option value="<=" ><=</option>	
			</select>
		</div>

		<div class="form-group col-md-6">
			<label for="parametroBS">{$MOD.LBL_VALOR}</label>
			<input id="parametroBS" name="parametroBS" type="text" value="" maxlength="20" class="form-control" />
		</div>

		<div class="form-group col-md-6">

			<div class="checkbox checkbox-nice">
				<input id="createNC" name="createNC"  type="checkbox">
				<label for="createNC">
					{$MOD.LBL_CREATE_NC}
				</label>
			</div>

			<div class="checkbox checkbox-nice">
				<input id="sendEmail" name="sendEmail" type="checkbox">
				<label for="sendEmail">
					{$MOD.LBL_SEND_EMAIL}
				</label>
			</div>
		</div>

		<div class="form-group col-md-6">
			<label for="emailsid">{$MOD.LBL_USUARIO_RESPONSABLE}</label>
			{*<select name="emailsid[]" size="3" multiple="multiple" id="emailsid" class="form-control">*}
			<select name="emailsid" id="emailsid" class="form-control">
			{foreach item=arr from=$LISTA_USUARIOS}
				<option value="{$arr[0]}" >
					{$arr[1]} ({$arr[2]})
				</option>
			{/foreach}
			</select>
		</div>
	</div>




	<div class="row">
		<div class="form-group col-md-12 text-center">
			<input type="hidden" name="submitSaveBS" value="1"/> 
			<input name="submitSaveBS" type="button" style="" class="btn btn-primary btn_1" onClick="javascript:ValidateBS();" value="Guardar{$APP.LBL_SAVE_LABEL}" />
			<button type="button" class="btn btn-default md-close" data-dismiss="modal">Cerrar</button>
		</div>
	</div>


		
</form>
	






































<script>

var rutaDefecto = new Array();
rutaDefecto[0] = '';
		
Event.observe(window, 'load', function() {ldelim}
	//agregaCombo('indicador1','55',0,'',rutaDefecto);
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
					if (respuesta.indicadors.length > 0) {ldelim}
						var select = document.createElement('select');
						select.name = contenedor+'[]';
						select.id = contenedor+'_'+nivel;
						agregaOpcion(select,'-Selecionar-');
						for (i=0;i<respuesta.indicadors.length;i++)
							agregaOpcion(select,respuesta.indicadors[i].nombre);
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