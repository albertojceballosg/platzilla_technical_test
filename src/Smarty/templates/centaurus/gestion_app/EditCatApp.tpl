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


<br>

<form action="index.php?module=gestion_app&action=SaveEditCatApps" method="post" name="index" onsubmit="return validateForm()">
<input type="hidden" placeholder="" id="mode" name="mode" value="edit" class="form-control">
<input type="hidden" placeholder="" id="record" name="record" value="{$RECORD}" class="form-control">


<div class="row">
  <div class="col-lg-12">
  		<div class="col-lg-9 pull-left">
      		<h1><a href="index.php?module=gestion_app&action=CategoryApps&parenttab=gestion_app">{if $RECORD neq '' }{$MOD.LBL_EDIT_CATEGORYAPPS_BUTTON_LABEL} {else}{$MOD.LBL_CREATE_CATEGORYAPPS_BUTTON_LABEL} {/if}</a></h1>
      	</div>
      	<div class="col-lg-3 pull-right text-right">
      		<button class="btn btn-primary btn-sm" type="submit" id="btnsave">{$MOD.LBL_SAVE}</button>
      		<a class="btn btn-warning btn-sm" type="submit" href="index.php?module=gestion_app&action=CategoryApps">{$MOD.LBL_CANCEL}</a>
      	</div>
  </div>
</div>


<div class="row">
  <div class="col-lg-12"> 
    <div class="main-box no-header">
      <div class="main-box-body clearfix" id="">

				<div class="form-group">
					<label for="app_code" id="label_app_code">{$MOD.LBL_CONFIG_APPS_CODE}</label>&nbsp;<font color="red">*</font>
					<input type="text" placeholder="" id="app_code" name="code" value="{$CONFIGAPPLICATION.code}" class="form-control" tite="Identificador de la aplicación">
				</div>
				<div class="form-group"  >
					<label for="app_name" id="label_app_name">{$MOD.LBL_CONFIG_APPS_NAME}</label>&nbsp;<font color="red">*</font>
					<input type="text" placeholder="" id="app_name" name="name" value="{$CONFIGAPPLICATION.name}" class="form-control">
				</div>			
				<div class="form-group">
					<label for="app_descripcion" id="label_app_descripcion">{$MOD.LBL_CONFIG_APPS_DESCRIPTION_LIST}</label>
					<textarea rows="3" id="app_descripcion" name="descripcion" class="form-control">{$CONFIGAPPLICATION.description}</textarea>
				</div>
				<div class="form-group">
					<label for="app_status">{$MOD.LBL_CONFIG_APPS_STATUS}</label>
					<select id="app_status" name="status" class="form-control">
						<option value="Activa" {if $CONFIGAPPLICATION.status eq 'Activa'} selected="selected" {/if} >Activa</option>
						<option value="Inactiva" {if $CONFIGAPPLICATION.status eq 'Inactiva'} selected="selected" {/if} >Inactiva</option>
					</select>
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
      		<button class="btn btn-primary btn-sm" type="submit" id="btnsave">{$MOD.LBL_SAVE}</button>
      		<a class="btn btn-warning btn-sm" type="submit" href="index.php?module=gestion_app&action=CategoryApps">{$MOD.LBL_CANCEL}</a>
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
				var modules = [];
			//console.log( list.nestable('serialize'));
			console.log( output);
			console.log(list[0]['children'][0]['children'])
			var elementos = list[0]['children'][0]['children'];
			for (var i = elementos.length - 1; i >= 0; i--) {ldelim}
				console.log(elementos[i]['dataset']['id'])
				//modules[i] = elementos[i]['dataset']['id']
				var obj = {ldelim} {rdelim};
				obj['id'] = elementos[i]['dataset']['id'];
				modules.push(obj);
				//modules.push({ 'id' : elementos[i]['dataset']['id']  });
			{rdelim};

			console.log( modules);
			console.log(window.JSON.stringify(modules))
			jQuery('#nestable-output').html(window.JSON.stringify(modules))

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


	function validateForm(){ldelim}


		if (!jQuery('#app_code').val()){ldelim}
			alert('Especifique el código de la Categoría');
			return false;
		{rdelim}
		if (!jQuery('#app_name').val()){ldelim}
			alert('Especifique el nombre de la Categoría');
			return false;
		{rdelim}
		
		return true;

	{rdelim}



</script>

