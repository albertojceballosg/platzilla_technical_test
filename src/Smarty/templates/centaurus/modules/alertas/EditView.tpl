




<form action="index.php"  method="post" name="tablaform" id="tablaform" autocomplete=off  onsubmit="" style="width:100%;">
	<input type="hidden" name="module" value="{$MODULE}">
	<input type="hidden" name="action" value="Save">
	<input type="hidden" name="record" id="record" value="{$ID}">
	<input type="hidden" name="submitSaveEditBS" id="submitSaveEditBS" value="1">




	<div class="row">
		<div class="col-lg-9 pull-left">
			<h1><a href="index.php?action=index&module={$MODULE}&parenttab={$CATEGORY}"> {$MOD.LBL_ALERTA}</a></h1>
		</div>
		<div class="col-lg-3 pull-right text-right" >
			<input name="submitSaveEditBS" type="button" style="" class="btn btn-primary btn_1" onClick="javascript: ValidateBS();" value="{$APP.LBL_SAVE_LABEL}" />
			<a class="btn btn-warning" href="index.php?action={$RETURNACTION}&module={$RETURNMODULE}&{if $RETURNACTION neq 'index'}record={$RETURNRECORD}&{/if}parenttab={$CATEGORY}">{$APP.LBL_CANCEL_BUTTON_LABEL}</a>
		</div>
	</div>


	<div class="row">
		<div class="col-lg-12">	
			<div class="main-box no-header">
				<div class="main-box-body clearfix">		

					<div class="row">
						<div class="form-group col-md-6">
							<label for="tituloBS">{$MOD.LBL_TITLE}</label>
							<input class="form-control" name="tituloBS" id="tituloBS" size="50" placeholder="{$MOD.LBL_TITLE}" type="text" value="{$DATAALERTA.titulo}">
						</div>
						
						<div class="form-group col-md-6">
							<label for="boxscoreid">{$MOD.SELECT_BOXSCORE}</label>
							<select name="boxscoreid" id="boxscoreid" class="form-control">
								<option value="" >-</option>
								{foreach key=keyBS item=valBS from=$LISTA_BOXSCORE}
									<option value="{$keyBS}" {if $DATAALERTA.boxscoreid eq $keyBS } selected="selected" {/if} >{$valBS}</option>
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
								<option value="Diario" {if $DATAALERTA.periodicidad eq 'Diario' } selected="selected" {/if} >{$MOD.LBL_DIARIO}</option>
								<option value="Semanal" {if $DATAALERTA.periodicidad eq 'Semanal' } selected="selected" {/if} >{$MOD.LBL_SEMANAL}</option>
								<option value="Quincenal" {if $DATAALERTA.periodicidad eq 'Quincenal' } selected="selected" {/if} >{$MOD.LBL_QUINCENAL}</option>
								<option value="Mensual" {if $DATAALERTA.periodicidad eq 'Mensual' } selected="selected" {/if} >{$MOD.LBL_MENSUAL}</option>
								<option value="Trimestral" {if $DATAALERTA.periodicidad eq 'Trimestral' } selected="selected" {/if}  >{$MOD.LBL_TRIMESTRAL}</option>
								<option value="Semestral" {if $DATAALERTA.periodicidad eq 'Semestral' } selected="selected" {/if}  >{$MOD.LBL_SEMESTRAL}</option>
								<option value="Anual" {if $DATAALERTA.periodicidad eq 'Anual' } selected="selected" {/if} >{$MOD.LBL_ANUAL}</option>
							</select>
						</div>

						<div class="form-group col-md-6">
							<label for="comparacionBS">{$MOD.LBL_COMPARACION}</label>
							<select name="comparacionBS" id="comparacionBS" class="form-control">
								<option value=">="  {if $DATAALERTA.comparacion_default eq '>=' } selected="selected" {/if} >>=</option>
								<option value=">" {if $DATAALERTA.comparacion_default eq '>' } selected="selected" {/if}>></option>
								<option value="=" {if $DATAALERTA.comparacion_default eq '=' } selected="selected" {/if}>=</option>
								<option value="!=" {if $DATAALERTA.comparacion_default eq '!=' } selected="selected" {/if}>!=</option>
								<option value="<" {if $DATAALERTA.comparacion_default eq '<' } selected="selected" {/if}><</option>
								<option value="<=" {if $DATAALERTA.comparacion_default eq '<=' } selected="selected" {/if}><=</option>	
							</select>
						</div>

						<div class="form-group col-md-6">
							<label for="parametroBS">{$MOD.LBL_VALOR}</label>
							<input id="parametroBS" name="parametroBS" type="text" maxlength="20" class="form-control" value="{$DATAALERTA.parametro_default}" />
						</div>

						<div class="form-group col-md-6">
					
							<div class="checkbox checkbox-nice">
								<input id="createNC" name="createNC" {if $DATAALERTA.crearnc eq 1}checked="checked"{/if} type="checkbox">
								<label for="createNC">
									{$MOD.LBL_CREATE_NC}
								</label>
							</div>

							<div class="checkbox checkbox-nice">
								<input id="sendEmail" name="sendEmail" {if $DATAALERTA.enviaremail eq 1}checked="checked"{/if} type="checkbox">
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
								<option value="{$arr[0]}" {if $arr[0] eq $DATAALERTA.emailsid} selected="selected" {/if} >
									{*<option value="{$arr[0]}" {if $arr[0]|in_array:$DATAALERTA.emailsid} selected="selected" {/if} >*}
									{$arr[1]} ({$arr[2]})
								</option>
							{/foreach}
							</select>
						</div>
					</div>
				</div>
			</div>
		</div>

	</div>

	<div class="row">
		
		<div class="col-lg-3 pull-right text-right" >
			<input name="submitSaveEditBS" type="button" style="" class="btn btn-primary btn_1" onClick="javascript: ValidateBS();" value="{$APP.LBL_SAVE_LABEL}" />
			<a class="btn btn-warning" href="index.php?action={$RETURNACTION}&module={$RETURNMODULE}&{if $RETURNACTION neq 'index'}record={$RETURNRECORD}&{/if}parenttab={$CATEGORY}">{$APP.LBL_CANCEL_BUTTON_LABEL}</a>
		</div>
	</div>

</form>



<script type="text/javascript" language="JavaScript">
	
	jQuery(document).ready(function() {ldelim}

		changeBoxscore();
		
		jQuery('#boxscoreid').change(function(){ldelim}
			changeBoxscore();
		{rdelim});	
		
		

	{rdelim});

	function changeBoxscore(){

		//console.log('entrando change boxscoreid EditView.tpl')
			
		var boxscoreid = jQuery('#boxscoreid').val();
		var url = "index.php?module=alertas&action=ActivityAjax&funcion=getindicadors&boxscoreid="+boxscoreid;
		jQuery.getJSON(
		url,
		{ldelim}
			'boxscoreid':jQuery('#boxscoreid').val()
		{rdelim},
		function( data ) {ldelim}
		jQuery('#indicador').empty();
		  var toAppend = '';
		  toAppend += '<option value="">-</option>';
		  jQuery.each( data, function( key, value ) {ldelim}
		  	// Mark a selected options
		  	var selected = '';
		  	if (key == {$DATAALERTA.indicadorboxscore}){ldelim}
		  		selected = 'selected="selected"';
		  	{rdelim}
			toAppend += '<option value="'+key+'" '+selected+'>'+value+'</option>';
		  {rdelim});
		  jQuery('#indicador').append(toAppend);
		{rdelim});
	}
	


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

	var  url = 'module={$MODULE}&action={$MODULE}Ajax&file=validateAlerts&ajax=true&mode=update&boxscoreid='+jQuery('#boxscoreid').val()+'&indicador='+jQuery('#indicador').val()+'&record='+jQuery('#record').val();

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

