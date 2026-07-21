{*
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
*
 ********************************************************************************/ *}
<link rel="stylesheet" media="all" type="text/css" href="include/colorpicker/css/colorpicker.css">
<link rel="stylesheet" type="text/css" href="css/libs/nifty-component.css"/>

<style>
.dd-links {ldelim}
    background: #f6f6f8 none repeat scroll 0 0 padding-box;
    border: 1px solid #e1e1e1;
    border-radius: 0;
    box-sizing: border-box;
    color: #000000;
    display: block;
    font-size: 0.875em;
    font-weight: 700;
    height: 30px;
    margin: 5px 0;
    padding: 5px 10px;
    text-decoration: none;
{rdelim}

.form-group {ldelim}
    margin-bottom: 5px !important;
{rdelim}

input[type=checkbox][disabled]  {ldelim}
    color: #ccc;
{rdelim}
</style>

<script language="JavaScript" type="text/javascript" src="include/colorpicker/js/colorpicker.js"></script>

<script>
	function asignaValorModales(label) {ldelim}
		jQuery('#modal-title').html(label);
	{rdelim}
	
	// Modificado por Johana Romero pedido [TT11132] Fallas Editor Disposición - Platzilla 
	// Evalua los valores y activa o desactiva los checks dependiendo de la info del field
	function asignaValorModalesEdit(label,field,module,defaultvalue,mandatory,quickcreate,presence,massedit,typedata,columname,uitype) {ldelim}

		jQuery('#modal-title').html(label);
		jQuery('#fieldid').val(field);
		jQuery('#fldmodule').val(module);
		jQuery('#typedata').val(typedata);

		jQuery('#columname').val(columname);
		jQuery('#uitype').val(uitype);
		
		alert(mandatory);
		if (mandatory == 2){ldelim}
			jQuery("#mandatory_check").prop("checked",true);
		{rdelim}else if(mandatory == 0){ldelim}
			jQuery("#mandatory_check").prop("checked",true).attr('disabled','');
		{rdelim}else {ldelim}
			jQuery("#mandatory_check").prop("checked",false);
		{rdelim}
		jQuery("#presence_check").prop("checked",(presence == 2 || presence == 0 ? true : false));
		jQuery("#defaultvalue_check").prop("checked",(defaultvalue != '' ? true : false));
		jQuery("#defaultvalue").val(defaultvalue)
		jQuery("#massedit_check").prop("checked",(massedit == 1 ? true : false));
		jQuery("#quickcreate_check").prop("checked",(quickcreate == 2 ? true : false));

	{rdelim}

	function saveMandatory(campo) {ldelim}
		new Ajax.Request(
    	'index.php',
         {ldelim} queue:  {ldelim}position: 'end', scope: 'command' {rdelim},
        	method: 'post',
            postBody: 'module=gestion_module&action=gestion_moduleAjax&file=saveMandatory&ajax=true&campo=' + campo + '&fldmodule={$MODULE}',
            onComplete: function(response)  {ldelim}
				if (response.responseText == 1){ldelim}
					alert("Actualizado con Éxito");
				if ( jQuery('#mandatory_check_'+campo).is( ":checked" ) )
					jQuery('#mandatory_check_'+campo).prop( "checked", false );
				else
					jQuery('#mandatory_check_'+campo).prop( "checked", true );
				{rdelim}
             {rdelim}
         {rdelim}
    );
	{rdelim}

	/* [TT11215] Prioridades avanzadas en campos Editor Disposición - 14/07/16 - Johana R - Valores iniciales del modal propiedades avanzadas */
	function asignaValorModalOpcA(label,uitype,columname,field,module) {ldelim}
		jQuery('#nombreCampo').html(label);
		jQuery('#fieldid').val(field);			
		jQuery('#divPick').empty()	
		//Si es lista muestra el select, sino lo oculta
		if (uitype == '15')	{ldelim}
			jQuery('#divSelect').show();
			jQuery('#listValues').empty();
			new Ajax.Request(
		    	'index.php',
		        {ldelim} queue:  {ldelim}position: 'end', scope: 'command' {rdelim},
		        	method: 'post',		        	
		            postBody: 'module=gestion_module&action=gestion_moduleAjax&file=LayoutBlockList&ajax=true&sub_mode=getList&fieldname='+columname,
		            onComplete: function(response)  {ldelim}							
						jQuery('#listValues').html(response.responseText);						
						var fieldid = jQuery('#listValues').val();
						new Ajax.Request(
					    	'index.php',
					        {ldelim} queue:  {ldelim}position: 'end', scope: 'command' {rdelim},
					        	method: 'post',
					            postBody: 'module=gestion_module&action=gestion_moduleAjax&file=LayoutBlockList&ajax=true&sub_mode=getFieldsPick&field='+fieldid+'&columnname='+columname+'&fld_module='+module,
					            onComplete: function(response)  {ldelim}	
									jQuery('#divPick').html(response.responseText)				
					            {rdelim}
					        {rdelim}
					    );	   	
		            {rdelim}
		        {rdelim}
		    );	

		{rdelim}else{ldelim}
			jQuery('#divSelect').hide();	
			getPickList(field)
		{rdelim}
		//console.log(fieldvalue)	
		//Coloca los fields en los picklist
		
	    
	{rdelim}

	function getPickList(value) {ldelim}
		var module = '{$MODULE}';
		//Coloca los fields en los picklist
		new Ajax.Request(
	    	'index.php',
	        {ldelim} queue:  {ldelim}position: 'end', scope: 'command' {rdelim},
	        	method: 'post',
	            postBody: 'module=gestion_module&action=gestion_moduleAjax&file=LayoutBlockList&ajax=true&sub_mode=getFieldsPick&field='+value+'&fld_module='+module,
	            onComplete: function(response)  {ldelim}							
					jQuery('#divPick').html(response.responseText)					
	            {rdelim}
	        {rdelim}
	    );	    
	    
	{rdelim}


	/* [TT11215] Prioridades avanzadas en campos Editor Disposición - 14/07/16 - Johana R - Agregar field a picklist ocultar campos */
	function addColumn() {ldelim}   
		var selectedOpts = jQuery('#availList option:selected');
        if (selectedOpts.length == 0) {ldelim}
            alert("Seleccione un campo");
        {rdelim}

        jQuery('#notAvailList').append(jQuery(selectedOpts).clone());
        jQuery(selectedOpts).remove();        
    {rdelim}

    /* [TT11215] Prioridades avanzadas en campos Editor Disposición - 14/07/16 - Johana R - Agregar field a picklist mostrar campos */
    function delColumn() {ldelim}
    	var selectedOpts = jQuery('#notAvailList option:selected');
        if (selectedOpts.length == 0) {ldelim}
            alert("Seleccione un campo");
        {rdelim}

        jQuery('#availList').append(jQuery(selectedOpts).clone());
        jQuery(selectedOpts).remove();        
    {rdelim}

    /* [TT11215] Prioridades avanzadas en campos Editor Disposición - 14/07/16 - Johana R - Ajax para guardar las propiedades avanzadas */
    function saveOpcionesAvanzadas(){ldelim}
    	var opcionesVisibles = []; 
    	var opcionesNoVisibles = []; 
    	var field = jQuery('#fieldid').val();
    	var listvalue = (jQuery('#divSelect').is(':visible') ? jQuery('#listValues').val() : '');
    	
    	var urlstring = '';

		jQuery('#availList option').each(function() {ldelim}
		    opcionesVisibles.push(jQuery(this).val());
		{rdelim});

		jQuery('#notAvailList option').each(function() {ldelim}
		    opcionesNoVisibles.push(jQuery(this).val());
		{rdelim});
    	
    	urlstring = '&field='+field+'&visibles='+opcionesVisibles+'&novisibles='+opcionesNoVisibles+'&listvalue='+listvalue;

    	new Ajax.Request(
	    	'index.php',
	        {ldelim} queue:  {ldelim}position: 'end', scope: 'command' {rdelim},
	        	method: 'post',
	            postBody: 'module=gestion_module&action=gestion_moduleAjax&file=LayoutBlockList&ajax=true&sub_mode=saveOpcionesAvanzadas'+urlstring,
	            onComplete: function(response){ldelim}							
					location.reload();
	            {rdelim}
	        {rdelim}
	    );
    {rdelim}


</script>
	
	<div class="md-modal md-effect-1" id="dlg_properties" style="min-width:320px;width:40%px;min-height:240px;overflow:auto;background-color:#FFFFFF;">
		<div class="modal-content" style="background-color:#FFFFFF">
			
			<div class="modal-header">				
				<button aria-hidden="true" data-dismiss="modal" class="md-close close" type="button">×</button>
				<h4 class="modal-title" id="modal-title"></h4>
			</div>
			<form role="form">
				<input type="hidden" id="fieldid" name="fieldid"/>
				<input type="hidden" id="columname" name="columname"/>
				<input type="hidden" id="uitype" name="uitype"/>

				<input type="hidden" name="fldmodule" value="{$MODULE}" id="fldmodule">
				<input type="hidden" name="typedata" id="typedata"/>

			<div class="modal-body form-group col-xs-12" style="background-color:#FFFFFF">
				<div class="layerPopup" style="position:relative; display:block">			
				<!-- Modificado por Johana Romero pedido [TT11132] Fallas Editor Disposición - Platzilla  
					 Ocultar campos innecesarios y quitar checks html
				-->
					<div class="form-group col-xs-4 checkbox-nice">
						<input id="mandatory_check" type="checkbox" >
						<label for="mandatory_check">{$MOD.LBL_MANDATORY_FIELD}</label>
					</div>
					{$INFOFIELD}
					<div class="form-group col-xs-4 checkbox-nice">
						<input id="presence_check" type="checkbox" >
						<label for="presence_check">{$MOD.LBL_ACTIVE}</label>
					</div>

					{* etiqueta valor por defecto *}
					<div class="form-group col-xs-4 checkbox-nice">
						<input id="defaultvalue_check" type="checkbox" >
						<label for="defaultvalue_check">{$MOD.LBL_DEFAULT_VALUE}</label>
						{* <input type="text" id="defaultvalue" class="form-control"> *}
					</div>
					
					<div class="form-group col-xs-4 checkbox-nice">
						<input id="massedit_check" type="checkbox" >
						<label for="massedit_check">{$MOD.LBL_MASS_EDIT}</label>
					</div>
					


					<div class="form-group col-xs-4 checkbox-nice">
						<input id="quickcreate_check" type="checkbox" >
						<label for="quickcreate_check">{$MOD.LBL_QUICK_CREATE}</label>
					</div>

					{* input valor por defecto *}
					<div class="form-group col-xs-4 checkbox-nice">
						<input type="text" id="defaultvalue" class="form-control">
					</div>
					
					<!--<div class="form-group col-xs-12">
					<header class="main-box-header clearfix">
					<h4>{$MOD.LBL_FORMAT_FIELD}</h4>
					</header>
					</div>
					<div class="form-group col-xs-12">
					<label>{$MOD.LBL_FIELD}</label>
					<select class="form-control" id="condicional_field" name="condicional_field">
						<option value="">Ninguno</option>
						{foreach item=bloques key=key from=$CFENTRIES}
							<optgroup label="{$bloques.blocklabel}">
								{foreach item=campos key=keyf from=$bloques.field}
									<option value="{$campos.fieldselect}" {if $value.first_fieldid eq $campos.fieldselect}selected{/if}>{$campos.label}</option>
								{/foreach}
							</optgroup>
						{/foreach}
					</select>
					<label>{$MOD.LBL_CONDITION}</label>
					<select class="form-control" name="condicional_condition" id="condicional_condition">
						<option value="">-</option>
						<optgroup label="Texto">
							<option value="empty">Campo vac&iacute;o</option>
							<option value="not empty">Campo no vac&iacute;o</option>
							<option value="like">Contiene el texto</option>
							<option value="not like">No Contiene el texto</option>
							<option value="equal">Exactamente el texto</option>
						</optgroup>
						<optgroup label="Fechas">
							<option value="date">La fecha es</option>
							<option value="before date">La fecha es anterior</option>
							<option value="after date">La fecha es posterior</option>
						</optgroup>
						<optgroup label="Numeros">
							<option value=">" >Mayor que</option>
							<option value=">=" >Mayor o igual que</option>
							<option value="<" >Menor que</option>
							<option value="<=" >Menor o igual que</option>
							<option value="==" >Es igual a</option>
							<option value="!=" >No es igual a</option>
							<option value="between" >Est&aacute; entre</option>
							<option value="not between" >No est&aacute; entre</option>
						</optgroup>
					</select>
					<label>{$MOD.LBL_OTHER_FIELD}</label>
					<select class="form-control" name="condicional_field2" onChange="if(this.value=='valor_fijo'){ldelim}jQuery('#valor_fijo').show(){rdelim}else{ldelim}jQuery('#valor_fijo').hide(){rdelim}" id="condicional_field2">
						<option value="">Ninguno</option>
						<option value="valor_fijo" {if $value.value}selected{/if}>Valor fijo</option>
						{foreach item=bloques key=key from=$CFENTRIES}
							<optgroup label="{$bloques.blocklabel}">
								{foreach item=campos key=keyf from=$bloques.field}
									<option value="{$campos.fieldselect}" {if $value.second_fieldid eq $campos.fieldselect}selected{/if}>{$campos.label}</option>
								{/foreach}
							</optgroup>
						{/foreach}
					</select>
					<label>{$MOD.LBL_FIXED_VALUE}</label>
					<input type="text" id="condicional_value" class="form-control">
					<label>{$MOD.LBL_BACKGROUND_COLOR}</label>
					<div style="float:right;width:80%;text-align:right;">
					<span style="cursor:pointer;" id="icp" name="icp" ><img src="include/colorpicker/images/color.png" style="border:0;margin:0 0 0 3px" align="absmiddle"></span>
					<script>
						jQuery('#icp').ColorPicker({ldelim}
							color: '#{$value.color}',
							onChange: function (hsb, hex, rgb) {ldelim}
								jQuery('#condicional_color').css('backgroundColor', '#' + hex);
								jQuery('#condicional_color').val(hex);
							{rdelim}
						{rdelim});
					</script>
					</div>
					<input type="text" name="condicional_color" id="condicional_color" class="form-control"/>
					<label>{$MOD.LBL_ADD_STYLE}</label>
					<input type="text"  name="condicional_style" id="condicional_style" class="form-control"/>
					</div>-->
				</div>							 		
			</div>
			<div class="form-group col-xs-12"  style="background-color:#FFFFFF">
			<div class="modal-footer">
				<button class="btn btn-default" type="button" onclick="saveFieldInfo('{$MODULE}')">{$APP.LBL_SAVE_BUTTON_LABEL}</button>
				<button class="btn btn-primary" type="button" onclick="deleteCustomField()">{$APP.LBL_DELETE_BUTTON_LABEL}</button>
			</div>
			</div>
			</form>
		</div>
	</div>







{*   Inicio de popup mandatory  *}


<div class="md-modal md-effect-1" id="dlg_properties_reducido" style="min-width:320px;width:40%px;min-height:240px;overflow:auto;background-color:#FFFFFF;">
		<div class="modal-content" style="background-color:#FFFFFF">
			<div class="modal-header">
				<button aria-hidden="true" data-dismiss="modal" class="md-close close" type="button">×</button>
				<h4 class="modal-title" id="modal-title"></h4>
			</div>
			<form role="form">
			<input type="hidden" id="fieldid" name="fieldid"/>
			<div class="modal-body form-group col-xs-12" style="background-color:#FFFFFF">
				<div class="layerPopup" style="position:relative; display:block">
					<div class="form-group col-xs-4 checkbox-nice">
						<input id="mandatory_check" type="checkbox" checked="">
						<label for="mandatory_check">{$MOD.LBL_MANDATORY_FIELD}</label>
					</div>
					</header>
				</div>
					
					
			</div>
			<div class="form-group col-xs-12"  style="background-color:#FFFFFF">
			<div class="modal-footer">
				<button class="btn btn-default" type="button" onclick="saveFieldInfo()">{$APP.LBL_SAVE_BUTTON_LABEL}</button>
				<button class="btn btn-primary" type="button" onclick="deleteCustomField()">{$APP.LBL_DELETE_BUTTON_LABEL}</button>
			</div>
			</div>
			</form>
		</div>
	</div>



{*   Fin de popup mandatory *}

<!-- [TT11215] Prioridades avanzadas en campos Editor Disposición - 14/07/16 - Johana R -->
{* Modal Opciones Avanzadas *}
<div class="md-modal md-effect-1" id="dlg_opcadvanced">
	<div class="md-content">
		<div class="modal-header">
			<button aria-hidden="true" data-dismiss="modal" class="md-close close" type="button">×</button>
			<h4 class="modal-title">{$MOD.LBL_ADVANCED_PROPERTIES}</h4>
		</div>
		<div class="modal-body">
			<input type="hidden" name="fieldid" id="fieldid">			
			<div class="row">
				<div class="col-md-12">
					<h5 class="control-label" id="nombreCampo"></h5>
				</div>
			</div>
			<div class="row" id="divSelect" style="display: none">
				{include file='gestion_module/showListValues.tpl'}
			</div>
			<div class="row">
				<div class="form-group">				
					{include file='gestion_module/showFieldsPicklist.tpl'}					
				</div>			
			</div>
		</div>
		<div class="modal-footer">			
			<button type="button" class="btn btn-primary" onclick="saveOpcionesAvanzadas()">{$APP.LBL_SAVE_BUTTON_LABEL}</button>
			<button class="btn btn-danger md-close" id="btnclose" onclick="jQuery('#modal').removeClass('md-show');jQuery('.md-overlay').css({ldelim}opacity: 0.0, visibility: 'hidden'{rdelim});return false;">{$APP.LBL_CANCEL_BUTTON_LABEL}</button>
		</div>	
	</div>
</div>
{* Fin Modal Opciones Avanzadas *}













			<form action="index.php" method="post" name="form" onsubmit="VtigerJS_DialogBox.block();">
				<input type="hidden" name="fld_module" value="{$MODULE}">
				<input type="hidden" name="module" value="gestion_module">
				<input type="hidden" name="parenttab" value="gestion_module">
				<input type="hidden" name="mode">
				<script language="JavaScript" type="text/javascript" src="include/js/customview.js"></script>
				<script language="JavaScript" type="text/javascript" src="include/js/general.js"></script>
				<script language="JavaScript" type="text/javascript" src="include/js/settings_custom.js"></script>
				<script language="JavaScript" type="text/javascript" src="modules/gestion_module/gestion_module.js"></script>
				
				{foreach item=entries key=id from=$CFENTRIES name=outer}
					{assign var=count value=$smarty.foreach.outer.iteration}
					{assign var=hidden_count value=$entries.hidden_count}
					
					{include file='gestion_module/LayoutBlockListAddField.tpl'}
					
					<div id='blockid_{$entries.blockid}' class="main-box clearfix" >
						<header class="main-box-header clearfix">
							<h2 class="col-lg-8">{$entries.blocklabel}</h2>
							<div class="col-lg-4 pull-right" >
								<div class="btn-group pull-right">
									<button type="button" class="btn btn-primary"><i class="fa fa-plus"></i></button>
									<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
										<span class="caret"></span>
									</button>
									<ul class="dropdown-menu" role="menu">
										
										<li><a href="javascript:void(0)" class="md-trigger" data-modal="addfield_{$entries.blockid}" onclick="document.getElementById('labelDiv').innerHTML = '{$MOD.LBL_ADD_FIELD2}';document.getElementById('fieldname_{$entries.blockid}').style.display = ''; "><i class="fa fa-plus"></i>A&ntilde;adir Campo</a></li>
										<!--li><a href="javascript:void(0)" class="md-trigger" data-modal="addfield_{$entries.blockid}" onclick="document.getElementById('labelDiv').innerHTML = '{$MOD.LBL_ADD_FIELD}';document.getElementById('fieldname_{$entries.blockid}').style.display = 'none';"><i class="fa fa-plus"></i>A&ntilde;adir campo Custom [cf_]</a></li-->
										
										{if $entries.iscustom == 1 }
											<li><a href="javascript:void(0)" onclick="deleteCustomBlock('{$MODULE}','{$entries.blockid}','{$entries.no}')">
											<i class="fa fa-trash-o"></i>Eliminar Bloque</a></li>
										{/if}
										{if $smarty.foreach.outer.first}
											<li><a href="javascript:void(0)" onclick="changeBlockorder('block_down','{$entries.tabid}','{$entries.blockid}','{$MODULE}') " alt="{$MOD.DOWN}"><i class="fa fa-hand-o-down"></i>Bajar Bloque</a></li>
										{elseif $smarty.foreach.outer.last}
											<li><a href="javascript:void(0)" onclick="changeBlockorder('block_up','{$entries.tabid}','{$entries.blockid}','{$MODULE}') " alt="{$MOD.UP}" alt="{$MOD.UP}"><i class="fa fa-hand-o-up"></i>Subir Bloque</a></li>
										{else}
											<li><a href="javascript:void(0)" onclick="changeBlockorder('block_down','{$entries.tabid}','{$entries.blockid}','{$MODULE}') " alt="{$MOD.DOWN}"><i class="fa fa-hand-o-down"></i>Bajar Bloque</a></li>
											<li><a href="javascript:void(0)" onclick="changeBlockorder('block_up','{$entries.tabid}','{$entries.blockid}','{$MODULE}') " alt="{$MOD.UP}" alt="{$MOD.UP}"><i class="fa fa-hand-o-up"></i>Subir Bloque</a></li>
										{/if}

									</ul>
								</div>
								
							</div>
						</header>
						
						<div class="main-box-body clearfix">
							<div class="row cf nestable-lists">
								<div class="col-md-6 dd" id="nestable{$count}" style="width:100%">
									<ul class="dd-list">							
									{foreach name=inner item=value from=$entries.field name=fields}
										{assign var=countfields value=$smarty.foreach.fields.iteration}
										<li class="dd-item" data-id="{$value.fieldselect}" blockid='{$entries.blockid}' id="{$value.fieldselect}">
											<div class="dd-handle" style="width:90%;float:none;display:inline-block;">
												{$value.label}
											</div>

											<div class="dd-links" style="width:10%;float:right;display:inline-block;">
												<button identity = "setField" class="md-trigger btn btn-primary mrg-b-lg" style="height:22px;padding: 3px;" type="button" data-modal="dlg_properties" onclick="asignaValorModalesEdit('{$value.label}','{$value.fieldselect}','{$MODULE}','{$value.defaultvalue.value}','{$value.mandatory}','{$value.quickcreate}','{$value.presence}','{$value.massedit}','{$value.typeofdata}','{$value.columnname}','{$value.uitype}')" dataField = "{$value.fieldselect}">
												<span class="fa fa-pencil"></span>
												</button>										

												<!-- [TT11215] Prioridades avanzadas en campos Editor Disposición - 14/07/16 - Johana R Boton propiedades avanzadas (tipos checkbox y listas) -->
												{if $value.uitype eq '15' || $value.uitype eq '56'}
												<button class="md-trigger btn btn-warning mrg-b-lg" style="height:22px;padding: 3px;" type="button" data-modal="dlg_opcadvanced" data-toggle="tooltip" title="Opciones avanzadas" onclick="asignaValorModalOpcA('{$value.label}','{$value.uitype}','{$value.columnname}',{$value.fieldselect},'{$MODULE}')">
												<span class="fa fa-gears"></span>
												</button>
												{/if}

											</div>

										</li>
									{/foreach}
									
									{if $hidden_count gt 0}

									{foreach name=inner item=value from=$entries.hiddenfield name=fields}	
										{assign var=countfields value=$smarty.foreach.fields.iteration}
										<li class="dd-item" data-id="{$value.fieldselect}" blockid='{$entries.blockid}' id="{$value.fieldselect}">
											<div class="dd-handle" style="width:90%;float:none;display:inline-block;">
												{$value.label}
											</div>
											
											<div class="dd-links" style="width:10%;float:right;display:inline-block;">
												<button identity = "setField" class="md-trigger btn btn-primary mrg-b-lg" style="height:22px;padding: 3px;" type="button" data-modal="dlg_properties" onclick="asignaValorModalesEdit('{$value.label}','{$value.fieldselect}','{$MODULE}','{$value.defaultvalue.value}','{$value.mandatory}','{$value.quickcreate}','{$value.presence}','{$value.massedit}','{$value.typeofdata}','{$value.columnname}','{$value.uitype}')" dataField = "{$value.fieldselect}">
												<span class="fa fa-pencil"></span>
												</button>
											</div>

										</li>
									{/foreach}

									{/if}

									</ul>
								</div>
							</div>
						</div>
					</div>
				{/foreach}
				
				<div class="md-modal md-effect-1" id="addblock">
					<div class="md-content">
						<div class="modal-header">
							<h4 class="modal-title" id="labelDiv">{$MOD.LBL_ADD_BLOCK}</h4>
						</div>
						<div class="modal-body">
							<table width="100%" border="0" cellpadding="5" cellspacing="0" class="table">
								<tr>
									<td class="dataLabel" nowrap="nowrap" align="right" width="30%"><b>{$MOD.LBL_BLOCK_NAME}</b></td>
									<td align="left" width="70%">
									<input id="blocklabel" value="" type="text" class="txtBox">
									</td>
								</tr>
								<tr>
									<td class="dataLabel" align="right" width="30%"><b>{$MOD.AFTER}</b></td>
									<td align="left" width="70%">
									<select id="after_blockid" name="after_blockid">
										{foreach key=blockid item=blockname from=$BLOCKS}
										<option value = {$blockid}> {$blockname} </option>
										{/foreach}
									</select>				
									</td>
								</tr>
								<tr>
									<td class="dataLabel" align="right" width="30%"><b>{"LBL_TYPE"|getTranslatedString}</b></td>
									<td align="left" width="70%">
									<select id="block_type" name="block_type" onchange="updateBlockProperties(this.value, '{$MODULE}');">
										{foreach key=blocktype item=blocktypelbl from=$BLOCK_TYPES}
										<option value ="{$blocktype}">{$blocktypelbl|getTranslatedString}</option>
										{/foreach}
									</select>
									
									</td>
								</tr>
								<tr style="display:none">
									<td class="dataLabel" align="right" width="30%"><b>{"LBL_RELATED_MODULE"|getTranslatedString}</b></td>
									<td align="left" width="70%">
									<select id="relmodule" name="relmodule" onchange="fillRelFieldsModulePickList(this.value)">
									</select>
									</td>
								</tr>
								<tr style="display:none">
									<td class="dataLabel" align="right" width="30%"><b>{"LBL_RELATED_FIELD"|getTranslatedString}</b></td>
									<td align="left" width="70%">
									<select id="relfieldname" name="relfieldname">
									</select>
									</td>
								</tr>
								<tr style="display:none">
									<td class="dataLabel" align="right" width="30%"><b>{"LBL_UPDATE_PARENT_FIELD"|getTranslatedString}</b></td>
									<td align="left" width="70%">
									<select id="update_parentfield" name="update_parentfield">
										<option value="">{"-Select-"|getTranslatedString}</option>
										{foreach key=c item=field from=$MODULE_FIELDS}
										<option value="{$field.fieldname}">{$field.fieldlabel|getTranslatedString}</option>
										{/foreach}
									</select>
									</td>
								</tr>
								<tr style="display:none">
									<td class="dataLabel" align="right" width="30%"><b>{"LBL_ON_COMPLETE_VALUE"|getTranslatedString}</b></td>
									<td align="left" width="70%">
									<input type="text" id="oncomplete_value" name="oncomplete_value" class="txtBox"/>
									</td>
								</tr>
								<tr style="display:none">
									<td class="dataLabel" align="right" width="30%"><b>{"LBL_ON_PROGRESS_VALUE"|getTranslatedString}</b></td>
									<td align="left" width="70%">
									<input type="text" id="onprogress_value" name="onprogress_value" class="txtBox" />
									</td>
								</tr>
							</table>
						</div>
						<div class="modal-footer">
							<!-- Modificado por Johana Romero Pedido [TT11132] Fallas Editor Disposición - Platzilla 							
                            type="button" -->
							<button type="button" class="btn btn-primary" onclick="getCreateCustomBlockForm('{$MODULE}','add') ">{$APP.LBL_SAVE_BUTTON_LABEL}</button>
							<button class="btn btn-danger md-close" id="btnclose" onclick="jQuery('#modal').removeClass('md-show');jQuery('.md-overlay').css({ldelim}opacity: 0.0, visibility: 'hidden'{rdelim});return false;">{$APP.LBL_CANCEL_BUTTON_LABEL}</button>
						</div>	
					</div>
				</div>
			</form>

				
<!-- this page specific scripts -->
<script src="themes/{$THEME}/js/jquery.nestable.js"></script>

<!-- this page specific inline scripts -->
<script>
jQuery(document).ready(function() {ldelim}

	/* Modificado por Johana Romero pedido [TT11132] Fallas Editor Disposición - Platzilla  
		Cada vez que se cambia la posicion del field, este llama al ajax-post en donde se actualiza la secuencia. El 'id' es el valor del fieldid (esta en el data-id de cada dd-item)
	*/
	var updateOutput = function(e){ldelim}	
		var list   = e.length ? e : jQuery(e.target),
		output = list.data('output');
		var fields = [];	
		var idd = '';					
		var elementos = list[0]['children'][0]['children'];		
		for (var i = 0; i < elementos.length; i++) {ldelim}			
			var obj = {ldelim} {rdelim};
			obj['id'] = elementos[i]['dataset']['id'];
			idd = elementos[i]['dataset']['id'];
			fields.push(obj);			
		{rdelim};

		var blockid = jQuery('#'+idd).attr('blockid');
		//console.log(window.JSON.stringify(fields))

		var param = 'jsonFields='+window.JSON.stringify(fields);
		new Ajax.Request(
        	'index.php',
        	
        	{ldelim}queue: {ldelim}position: 'end', scope: 'command'{rdelim},
                method: 'post',
                postBody: 'action=gestion_moduleAjax&module=gestion_module&file=SaveSequenceFields&'+param+'&blockid='+blockid,
                onComplete: function(response) {ldelim}
                	
                {rdelim}
        	{rdelim}	
		);
	{rdelim};


	{foreach item=entries key=id from=$CFENTRIES name=outer}
		{assign var=count value=$smarty.foreach.outer.iteration}
		// activate Nestable for list 1
		jQuery('#nestable{$count}').nestable({ldelim}
			group: {$count}
		{rdelim}).on('change', updateOutput);		
	{/foreach}

{rdelim});
</script>
<div class="md-overlay"></div><!-- the overlay element -->
<!-- this page specific scripts -->
<script src="themes/{$THEME}/js/modernizr.custom.js"></script>
<script src="themes/{$THEME}/js/classie.js"></script>
<script src="themes/{$THEME}/js/modalEffects.js"></script>