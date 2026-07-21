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
<script language="JavaScript" type="text/javascript" src="modules/Settings/Settings.js"></script>

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


<div id="createCatApp" class="md-modal md-effect-1">
	<div class="md-content" style="width:100%; height:530px;  overflow:auto;">
		<div class="modal-header">
			<button class="md-close close">×</button>
			<h4 class="modal-title" id="modal-title">{$MOD.LBL_CREATE_CATEGORYAPPS_BUTTON_LABEL}</h4>
		</div>
		<div class="modal-body">
			<form role="form">
				<input type="hidden" id="nestable-output" value="">
				<div class="form-group">
					<label for="app_code" id="label_app_code">{$MOD.LBL_CONFIG_APPS_CODE}</label>&nbsp;<font color="red">*</font>
					<input type="text" placeholder="" id="app_code" class="form-control" tite="Identificador de la aplicación">
				</div>
				<div class="form-group"  >
					<label for="app_name" id="label_app_name">{$MOD.LBL_CONFIG_APPS_NAME}</label>&nbsp;<font color="red">*</font>
					<input type="text" placeholder="" id="app_name" class="form-control">
				</div>
				<div class="form-group">
					<label for="app_descripcion" id="label_app_descripcion">{$MOD.LBL_CONFIG_APPS_DESCRIPTION_LIST}</label>
					<textarea rows="3" id="app_descripcion" class="form-control"></textarea>
				</div>
				<div class="form-group">
					<label for="app_status">{$MOD.LBL_CONFIG_APPS_STATUS}</label>
					<select id="app_status" class="form-control">
						<option value="Activa">Activa</option>
						<option value="Inactiva">Inactiva</option>
					</select>
				</div>

			</form>

		</div>
		<div class="modal-footer">
			<button class="md-close btn btn-default" type="button" id="btnclose" onclick="jQuery('#createCatApp').removeClass('md-show');jQuery('.md-overlay').css({ldelim}opacity: 0.0, visibility: 'hidden'{rdelim});return false;">{$MOD.LBL_CANCEL}</button>
			<button class="btn btn-primary" type="button" onclick="saveApp('create', '0'); return true;" id="btnsave">{$MOD.LBL_SAVE}</button>
		</div>
	</div>

</div>


<div id="NoteApps" class="modal fade" role="dialog">
	<div class="md-content" style="width:100%; height:500px;  overflow:auto;">
		<div class="modal-header">
			<h4 class="modal-title" id="modal-title">"La aplicación se está creando. Por favor espere..."</h4>
		</div>
	</div>
</div>




<div id="email-box" class="clearfix">
	<div class="col-lg-12">
			<table class="table" border="0" cellpadding="5" cellspacing="0" width="100%">
			<tr>
				<td rowspan="2" valign="top">
					<div class="infographic-box" style="width:30px;padding:0px;">
					<i class="fa fa-cubes green-bg"></i>
					</div>
				</td>
				<td class="heading2" valign="bottom">
					<ol class="breadcrumb">
						<li><a href="index.php?module=Settings&action=index&parenttab=Settings">{$MOD.LBL_SETTINGS}</a></li>
						<li>{$MOD.LBL_CATEGORYAPPS_BUTTON_LABEL} </li>
					</ol>
				</td>
			</tr>
			<tr>
				<td class="small" colspan="3" valign="top">{$MOD.LBL_CONFIG_APPS_DESCRIPTION}</td>
			</tr>
			</table>
	</div>
	<br/>
	<br/>


	{if $MSG_ERROR neq ''}
	<div class="col-lg-12">
		<div class="alert alert-danger fade in">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
			<i class="fa fa-times-circle fa-fw fa-lg"></i>
			<strong>ERROR!</strong> {$MSG_ERROR}.
		</div>
	</div>
	{/if}

	<div class="row">
		<div class="col-lg-12">
			<div class="main-box clearfix">
				<br/>
				<div class="pull-right" style="margin-right: 20px;">

						<a class="btn btn-info" href="index.php?module=Settings&action=ConfigApps">
							{$MOD.CONFIG_APPS}
						</a>
						<button data-modal="createCatApp" class="md-trigger btn btn-primary">
							<span class="fa fa-plus"></span> {$MOD.LBL_CREATE_CATEGORYAPPS_BUTTON_LABEL}
						</button>
				</div>
				<br/>
				<div class="main-box-body clearfix">
					<table width="100%" border="0" cellpadding="5" cellspacing="0" class="table-responsive">
	                	<tr>
	                    	<th><h2>{$MOD.LBL_CATEGORYAPPS_BUTTON_LABEL}</h2></th>
	                    		<td align="right">&nbsp;</td>
	                 	 </tr>
				  	</table>
				  	<br/>
				  	<div id="appscontents">
						{include file='Settings/CatAppsContents.tpl'}
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div id="editdiv" style="display:none;position:absolute;width:400px;"></div>

<div class="md-overlay"></div><!-- the overlay element -->


<script>

jQuery(document).ready(function() {ldelim}


		jQuery('#app_code').keyup(function(){ldelim}
			validField('app_code');
		{rdelim});



	});


</script>

{literal}
<script>






function saveApp(mode,id){


	if(mode == 'create'){
		// Validando vacios
		if (!jQuery('#app_code').val()){
			alert('Especifique el código de la Categoría');
			return false;
		}

		if (!jQuery('#app_name').val()){
			alert('Especifique el nombre de la Categoría');
			return false;
		}


		//jQuery('#createCatApp').hide();
		var code = jQuery('#app_code').val();
		var name = jQuery('#app_name').val();
		var description = jQuery('#app_descripcion').val();
		var status = jQuery('#app_status').val();
		var param= 'code=' + code + '&name=' + name  + '&description=' + description + '&status=' + status + '&mode=' + mode;


        new Ajax.Request(
        	'index.php',
        	{queue: {position: 'end', scope: 'command'},
                method: 'post',
                postBody: 'action=SettingsAjax&module=Settings&file=SaveEditCatApps&'+param,
                onComplete: function(response) {
                	//jQuery('#NoteApps').hide();
                	//jQuery('#createCatApp').removeClass('md-show');
                	if(response.responseText == 'success'){
                		alert('La categoría se ha creado correctamente');
                		location.reload();

                	}else if(response.responseText == 'exist'){
                		alert('Valores repetidos! Verifique los datos.');
                		return false;

                	}else{
                		alert('Ha ocurrido un error. Por favor contacte al administrador');
                		return false;
                	}

                }
        	}
		);



	}else if(mode == 'edit'){

		if(validateConfigApps(id)){

			jQuery('#editApps_'+id).hide();
			var code = jQuery('#app_code_'+id).val();
			var name = jQuery('#app_name_'+id).val();
			var description = jQuery('#app_descripcion_'+id).val();
			var status = jQuery('#app_status_'+id).val();
			var param= 'code=' + code + '&name=' + name  + '&description=' + description + '&status=' + status + '&mode=' + mode + '&id=' + id;

	        new Ajax.Request(
	        	'index.php',
	        	{queue: {position: 'end', scope: 'command'},
	                method: 'post',
	                postBody: 'action=SettingsAjax&module=Settings&file=SaveEditCatApps&'+param,
	                onComplete: function(response) {
	                	console.log(response.responseText);
	                	jQuery('#editApps_'+id).removeClass('md-show');
	                	if(response.responseText == 'CONFIG_SUCCESS'){
	                		alert('La aplicaci\u00F3n se ha editado correctamente');
	                		location.reload();
	                	}else{
	                		alert('Ha ocurrido un error. Por favor contacte al administrador');
	                		return false;
	                	}

	                }
	        	}
			);

		}

	}else if(mode == 'delete'){

		jQuery(this).addClass('md-show');

		if(confirm("¿Est\u00E1 seguro que dese eliminar la aplicaci\u00F3n?")){

			var param = 'mode=' + mode + '&id=' + id;

	        new Ajax.Request(
	        	'index.php',
	        	{queue: {position: 'end', scope: 'command'},
	                method: 'post',
	                postBody: 'action=SettingsAjax&module=Settings&file=DeleteCatApps&'+param,
	                onComplete: function(response) {
	                	console.log(response.responseText);
	                	jQuery(this).removeClass('md-show');
	                	if(response.responseText == 'CONFIG_SUCCESS'){
	                		alert('La aplicaci\u00F3n se ha eliminado correctamente');
	                		location.reload();
	                	}else{
	                		alert('Ha ocurrido un error. Por favor contacte al administrador');
	                		return false;
	                	}

	                }
	        	}
			);

		}

	}


}


</script>
{/literal}

