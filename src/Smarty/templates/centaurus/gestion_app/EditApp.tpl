{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is:  vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
  *
 ********************************************************************************/
-->*}
<script language="JAVASCRIPT" type="text/javascript" src="include/js/smoothscroll.js"></script>
<script language="JavaScript" type="text/javascript" src="include/js/menu.js"></script>
<script language="JavaScript" type="text/javascript" src="include/js/menu.js"></script>
<script language="JavaScript" type="text/javascript" src="themes/centaurus/js/jquery.nestable.maxDepth.js"></script>
<script language="JavaScript" type="text/javascript" src="modules/gestion_app/gestion_app.js"></script>

{literal}
<style>
DIV.fixedLay{
	border:3px solid #CCCCCC;
	background-color:#FFFFFF;
	width:500px;
	position:fixed;
	left:250px;
	top:200px;
	display:block;
}
</style>
{/literal}
{literal}


{/literal}


<form action="index.php?module=gestion_app&action=SaveEditApps"  id="SaveEditApps" method="post" name="index" enctype="multipart/form-data" onsubmit="">


<div class="row">
  <div class="col-lg-12">
  		<div class="col-lg-9 pull-left">
      		<h1><a href="index.php?module=gestion_app&action=ConfigApps&parenttab=gestion_app">{$MOD.LBL_TITLE_MODAL_EDIT_APP} </a></h1>
      	</div>
      	<div class="col-lg-3 pull-right text-right">
      		<button class="btn btn-primary" type="button" id="btnsave" onclick="if (validateForm() == true ){ldelim} validateRepeatData(); {rdelim} ">{$MOD.LBL_SAVE}</button>
      		<a class="btn btn-warning" type="submit" href="index.php?module=gestion_app&action=ConfigApps">{$MOD.LBL_CANCEL_BUTTON}</a>
      	</div>
  </div>
</div>


<div class="row">
  <div class="col-lg-12"> 
    <div class="main-box no-header">
      <div class="main-box-body clearfix" id="">

			<!--form role="form"-->


			
				<div class="form-group">
					<label for="app_code" id="label_app_code">{$MOD.LBL_CONFIG_APPS_CODE}</label>&nbsp;<font color="red">*</font>
					<input type="text" placeholder="" id="app_code" name="code" value="{$CONFIGAPPLICATION.app_code}" class="form-control" tite="Identificador de la aplicación">
				</div>
				<div class="form-group"  >
					<label for="app_name" id="label_app_name">{$MOD.LBL_CONFIG_APPS_NAME}</label>&nbsp;<font color="red">*</font>
					<input type="text" placeholder="" id="app_name" name="name" value="{$CONFIGAPPLICATION.app_name}" class="form-control">
				</div>
				<!-- [ TT11276 ] Ajustar precio de las aplicaciones - Jesus A - 15/08/2016 - El campo se configuro no editable y se elimino la obligatoriedad -->
				<div class="form-group"  >
					<label for="app_price" id="label_app_name">{$MOD.LBL_CONFIG_APPS_PRICE}</label>&nbsp;
					<input type="text" placeholder="" id="app_price" name="price" class="form-control" readonly>
				</div>		
				<div class="form-group"  >
					<label for="app_url" id="label_app_url">{$MOD.LBL_CONFIG_APPS_URL}</label>&nbsp;<font color="red">*</font>
					<input type="text" placeholder="" id="app_url" name="url" value="{$CONFIGAPPLICATION.app_url}" class="form-control">
				</div>				
				<div class="form-group">
					<label for="app_descripcion" id="label_app_descripcion">{$MOD.LBL_CONFIG_APPS_DESCRIPTION_LIST}</label>
					<textarea rows="3" id="app_descripcion" name="descripcion" class="form-control">{$CONFIGAPPLICATION.app_description}</textarea>
				</div>
				<div class="form-group">
					<label for="app_status">{$MOD.LBL_CONFIG_APPS_STATUS}</label>
					<select id="app_status" name="status" class="form-control">
						<option value="Activa" {if $CONFIGAPPLICATION.status eq 'Activa'} selected="selected" {/if} >Activa</option>
						<option value="Inactiva" {if $CONFIGAPPLICATION.status eq 'Inactiva'} selected="selected" {/if} >Inactiva</option>
					</select>
				</div>
				<div class="form-group">
					<label for="app_category">{$MOD.LBL_CATEGORYAPPS_LABEL} </label>
					<select id="app_category" name="category[]" multiple="multiple" class="form-control">
						<option value="" >-</option>
						{foreach key=keyC item=category from=$CATEGORIES}
							<!--option value="{$category.catappid}"  {if $CONFIGAPPLICATION.app_category eq $category.catappid } selected="selected" {/if} >{$category.name}</option-->
							<option value="{$category.catappid}"  {if in_array($category.catappid,$CONFIGAPPLICATION.app_category)} selected="selected" {/if} >{$category.name}</option>
						{/foreach}
						
					</select>
				</div>

				<div class="form-group">
					<label for="app_status">{$MOD.LBL_IMAGE_APPS}</label>
					<br>
					<input TYPE="hidden" NAME="MAX_FILE_SIZE" VALUE="800000">
	    			<input TYPE="hidden" NAME="PREV_FILE" VALUE="">	 
	    			<input type="button" name="binFileButton" id="binFileButton" class="btn btn-primary btn-sm" value="{if $CONFIGAPPLICATION.app_image eq 1} {$CONFIGAPPLICATION.app_code}.png {else}  Seleccionar Archivo {/if}"  onclick="jQuery('#binFile').trigger('click');"> 
	            	<label id="displaySize"></label>
	            	<input type="file" accept="image/png" name="binFile" id="binFile" class=""  style="display:none" value="" onchange="validatePngFilenameImage(this,0.8);if(this.value!=''){ldelim} jQuery('#binFileButton').val(this.value);{rdelim}">[{if $CONFIGAPPLICATION.app_image eq 1} {$CONFIGAPPLICATION.app_code}.png {/if} ]
	            	<input type="hidden" name="binFile_hidden" value="{if $CONFIGAPPLICATION.app_image eq 1} {$CONFIGAPPLICATION.app_code} {/if} " />

				</div>	


				


				<div class="form-group">
					<textarea id="nestable-output" name="nestable-output" style="display:none"></textarea>
					<input type="hidden" id="record" name="record" value="{$RECORD}" />
					<input type="hidden" id="mode" name="mode" value="edit" />


					<label for="">{$MOD.LBL_ASIG_MODULES}</label>&nbsp;<font color="red">*</font>
					<div class="row cf nestable-lists">

						<div class="table-responsive">
							<table class="table" width="100%" cellpadding="5" cellspacing="0">
								<tr>
									<th width="50%" class="text-center">{$MOD.LBL_FREE}</th>
									<th width="50%" class="text-center" >{$MOD.LBL_ASIG}</th>
								</tr>
								<tr>
									<td>
										<div id="nestable2" class="dd" style="vertical-align: top; width:100%; height:250px; overflow:auto;">
											<ul class="dd-list">
												{foreach item=module from=$CONFIGAPPLICATION.modulesFree}	
													<li class="dd-item " data-id="{$module.tabid}">
														<div class="dd-handle">{$module.tablabel}</div>
													</li>
												{/foreach}
											</ul>	
										</div>
									</td>
									<td>
										<div id="nestable" class="dd" style="vertical-align: top; width:100%; height:250px; overflow:auto;">
											<ul class="dd-list">
												 {* <!--div class="dd-empty"></div-->  *}
												
												{foreach item=module from=$CONFIGAPPLICATION.modules}	
													<li class="dd-item" data-id="{$module.tabid}">
														<div class="dd-handle">{$module.tablabel}</div>
													</li>
												{/foreach}
												
											</ul>
										</div>
									</td>
									
								</tr>
							</table>
						</div>						
					</div>
				</div>
			




		</div>
    </div>
  </div>
</div>


<div class="row">
  <div class="col-lg-12">
  		<div class="col-lg-9 pull-left">
      	</div>
      	<div class="col-lg-3 pull-right text-right">
      		<button class="btn btn-primary" type="button" id="btnsave" onclick="if (validateForm() == true ){ldelim} validateRepeatData(); {rdelim} ">{$MOD.LBL_SAVE}</button>
      		<a class="btn btn-warning" type="submit" href="index.php?module=gestion_app&action=ConfigApps">{$MOD.LBL_CANCEL_BUTTON}</a>
      	</div>
  </div>
</div>



</form>




<div id="editdiv" style="display:none;position:absolute;width:400px;"></div>

<div class="md-overlay"></div><!-- the overlay element -->
	

<script>

jQuery(document).ready(function() {ldelim}



		var updateOutput = function(e){ldelim}
	
			var list   = e.length ? e : jQuery(e.target),
			output = list.data('output');
			/*[ TT11276 ] Re-Open Ajustar precio de las aplicaciones - Jesus A - 22/09/2016 */
			var modules = new Array();
			//console.log( list.nestable('serialize'));
			//console.log( output);
			//console.log(list[0]['children'][0]['children'])
			var elementos = list[0]['children'][0]['children'];
			for (var i = elementos.length - 1; i >= 0; i--) {ldelim}
				//console.log(elementos[i]['dataset']['id'])
				//modules[i] = elementos[i]['dataset']['id']
				var obj = {ldelim} {rdelim};
				obj['id'] = elementos[i]['dataset']['id'];
				modules.push(obj);
				//modules.push({ 'id' : elementos[i]['dataset']['id']  });
			{rdelim};

			//console.log( modules);
			//console.log(window.JSON.stringify(modules))
			/*[ TT11276 ] Re-Open Ajustar precio de las aplicaciones - Jesus A - 22/09/2016 */
			if (modules != "")
				jQuery('#nestable-output').html(window.JSON.stringify(modules))
			else
				jQuery('#nestable-output').html(window.JSON.stringify(list.nestable('serialize')))
	
			/*[ TT11276 ] Ajustar precio de las aplicaciones - Jesus A - 15/08/2016 */
			/* Se construye un arraglo para contabilizar las Apps que se hayan seleccionado*/
			var applications = JSON.parse(output.val());
			/*Se le pasan la cantidad de aplicaciones al la función updatedPriceValueOfApps para calcular el precio total*/
			updatedPriceValueOfApps(applications.length);
			
		{rdelim};


		jQuery('#nestable').nestable({ldelim}
			group: 1
		{rdelim}).on('change', updateOutput);

		jQuery('#nestable2').nestable({ldelim}
			group: 1
		{rdelim});

		jQuery('#app_code').keyup(function(){ldelim}
			validField('app_code');
		{rdelim});

		jQuery('#app_price').keyup(function(){ldelim}
			validateDecimal('app_price');
		{rdelim});

		updateOutput(jQuery('#nestable').data('output', jQuery('#nestable-output')));


		//jQuery('#nestable').trigger('change');

	{rdelim});





	function validateRepeatData(){ldelim}

		var param = 'validation=norepeatnameappEdit&app_code='+jQuery('#app_code').val()+'&app_name='+jQuery('#app_name').val()+'&appid={$CONFIGAPPLICATION.id}';

		new Ajax.Request(
	        	'index.php',
	        	
	        	{ldelim}queue: {ldelim}position: 'end', scope: 'command'{rdelim},
	                method: 'post',
	                postBody: 'action=gestion_appAjax&module=gestion_app&file=validateSaveEditApps&'+param,
	                onComplete: function(response) {ldelim}
	                	console.log(response.responseText);
	                	if(response.responseText == 'repeated'){ldelim}
	                		alert('La aplicaci\u00F3n Ya Existe');
	                		return false
	                	{rdelim}else {ldelim}
	                		console.log("enviando a validateInfo")
	                		return validateInfo()
	                	{rdelim}
	                {rdelim}
	        	{rdelim}	
			);

	{rdelim}

	function validateInfo(){ldelim}

		if (!jQuery('#app_price').val()){ldelim}
			alert('Especifique el precio de la Aplicación');
			return false;
		{rdelim}
		if (jQuery('#app_price').val() <= 0 ){ldelim}
			alert('Especifique el precio de la Aplicación');
			return false;
		{rdelim}
		if (jQuery('#app_url').val() == '' ){ldelim}
			alert('Especifique la url de la Aplicación');
			return false;
		{rdelim}
		if (jQuery('#binFileButton').val() == 'Seleccionar Archivo'){ldelim}
			alert('Elija una imagen para la Aplicación');
			return false;
		{rdelim}
		if (jQuery('#nestable-output').val() == '[]'){ldelim}
			alert('Agregue al menos un módulo a esta Aplicación');
			return false;
		{rdelim}


		jQuery('#SaveEditApps').submit();

	{rdelim}


	function validateForm(){ldelim}

		if (!jQuery('#app_code').val()){ldelim}
			alert('Especifique el código de la Aplicación');
			return false;
		{rdelim}
		if (!jQuery('#app_name').val()){ldelim}
			alert('Especifique el nombre de la Aplicación');
			return false;
		{rdelim}
		
		return true;

	{rdelim}
	
	/*[ TT11276 ] Ajustar precio de las aplicaciones - Jesus A - 15/08/2016 */
	/* Función que consulta el precio de la aplicación via Ajax en la tabla vtiger_variables_instancias y
	calcula el monto total para colocarlo en el campo precio de la forma*/
	
	function updatedPriceValueOfApps(quantity){ldelim}
		
		var param = 'sub_mode=priceQueryApps';

		new Ajax.Request(
	        	'index.php',
	        	
	        	{ldelim}queue: {ldelim}position: 'end', scope: 'command'{rdelim},
	                method: 'post',
	                postBody: 'action=gestion_appAjax&module=gestion_app&file=priceApps&'+param,
	                onComplete: function(response) {ldelim}
	                	//console.log(response.responseText);
	                	if(response.responseText != ""){ldelim}
	                		//alert(response.responseText);
							var priceApp = 0;
							priceApp = response.responseText;
							var totalPrice = priceApp * quantity;
							jQuery('#app_price').val(totalPrice);
	                	{rdelim}
	                {rdelim}
	        	{rdelim}	
			);
		
	{rdelim}

</script>

