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
<!--[if lte IE 6]>
<STYLE type=text/css>
DIV.fixedLay {
	POSITION: absolute;
}
</STYLE>
<![endif]-->




{/literal}


<div id="createApps" class="md-modal md-effect-1">
	<div class="md-content" style="width:100%; height:500px;  overflow:auto;">
		<div class="modal-header">
			<button class="md-close close">×</button>
			<h4 class="modal-title" id="modal-title">{$MOD.LBL_TITLE_MODAL_CREATE_APP}</h4>
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
				<div class="form-group"  >
					<label for="app_price" id="label_app_name">{$MOD.LBL_CONFIG_APPS_PRICE}</label>&nbsp;<font color="red">*</font>
					<input type="text" placeholder="" id="app_price" class="form-control">
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
				<div class="form-group">
					<label for="app_descripcion">{$MOD.LBL_ASIG_MODULES}</label>&nbsp;<font color="red">*</font>
					<div class="row cf nestable-lists">
						<div class="table-responsive">
							<table class="table" width="100%" cellpadding="5" cellspacing="0">
								<tr>
									<th width="50%" class="text-center" >{$MOD.LBL_ASIG}</th>
									<th width="50%" class="text-center">{$MOD.LBL_FREE}</th>
								</tr>
								<tr>
									<td>
										<div id="nestable" class="dd" style="vertical-align: top; width:100%; height:250px; overflow:auto;">
											<ul class="dd-list">
												<div class="dd-empty"></div>
											</ul>
										</div>
									</td>
									<td>
										<div id="nestable2" class="dd" style="vertical-align: top; width:100%; height:250px; overflow:auto;">
											<ul class="dd-list">
												{foreach item=module from=$MODULESFREE}	
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
			</form>

		</div>
		<div class="modal-footer">
			<button class="md-close btn btn-default" type="button" id="btnclose" onclick="jQuery('#createApps').removeClass('md-show');jQuery('.md-overlay').css({ldelim}opacity: 0.0, visibility: 'hidden'{rdelim});return false;">{$MOD.LBL_CANCEL}</button>
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


{foreach name=configAppTotal item=elementsA from=$CONFIGAPPLICATION}
	<div id="editApps_{$elementsA.id}" class="md-modal md-effect-1">
		<div class="md-content" style="width:100%; height:500px;  overflow:auto;">
			<div class="modal-header">
				<button class="md-close close">×</button>
				<h4 class="modal-title" id="modal-title">{$MOD.LBL_TITLE_MODAL_EDIT_APP}</h4>
			</div>
			<div class="modal-body">
				<form role="form">
					<input type="hidden" id="nestable-output_{$elementsA.id}" value="">
					<div class="form-group">
						<label for="app_code" id="label_app_code">{$MOD.LBL_CONFIG_APPS_CODE}</label>&nbsp;<font color="red">*</font>
						<input type="text" placeholder="" id="app_code_{$elementsA.id}" class="form-control" tite="Identificador de la aplicación" value='{$elementsA.app_code}'>
					</div>
					<div class="form-group"  >
						<label for="app_name" id="label_app_name">{$MOD.LBL_CONFIG_APPS_NAME}</label>&nbsp;<font color="red">*</font>
						<input type="text" placeholder="" id="app_name_{$elementsA.id}" class="form-control" value='{$elementsA.app_name}'>
					</div>
					<div class="form-group"  >
						<label for="app_price" id="label_app_name">{$MOD.LBL_CONFIG_APPS_PRICE}</label>&nbsp;<font color="red">*</font>
						<input type="text" placeholder="" id="app_price_{$elementsA.id}" class="form-control" value='{$elementsA.app_price}'>
					</div>					
					<div class="form-group">
						<label for="app_descripcion" id="label_app_descripcion">{$MOD.LBL_CONFIG_APPS_DESCRIPTION_LIST}</label>
						<textarea rows="3" id="app_descripcion_{$elementsA.id}" class="form-control">{$elementsA.app_descripcion}</textarea>
					</div>
					<div class="form-group">
						<label for="app_status">{$MOD.LBL_CONFIG_APPS_STATUS}</label>
						<select id="app_status_{$elementsA.id}" class="form-control">
							<option {if $elementsA.app_status eq 'Activa'}selected{/if} value="Activa">Activa</option>
							<option {if $elementsA.app_status eq 'Inactiva'}selected{/if} value="Inactiva">Inactiva</option>
						</select>
					</div>					
					<div class="form-group">
						<label for="app_descripcion">{$MOD.LBL_ASIG_MODULES}</label>&nbsp;<font color="red">*</font>
						<div class="row cf nestable-lists">
							<div class="table-responsive">
								<table class="table" width="100%" cellpadding="5" cellspacing="0">
									<tr>
										<th width="50%" class="text-center" >{$MOD.LBL_ASIG}</th>
										<th width="50%" class="text-center">{$MOD.LBL_FREE}</th>
									</tr>
									<tr>
										<td>
											<div id="nestable_{$elementsA.id}" class="dd" style="vertical-align: top; width:100%; height:250px; overflow:auto;">
												<ol class="dd-list">
													{foreach item=moduleA from=$elementsA.modules}	
														<li data-id="{$moduleA.tabid}" class="dd-item">
															<div class="dd-handle">{$moduleA.tablabel}</div>
														</li>
													{/foreach}
												</ol>
											</div>
										</td>
										<td>
											<div id="nestable2_{$elementsA.id}" class="dd" style="vertical-align: top; width:100%; height:250px; overflow:auto;">
												<ul class="dd-list">
													{foreach item=moduleB from=$elementsA.modulesFree}
														<li class="dd-item" data-id="{$moduleB.tabid}">
															<div class="dd-handle">{$moduleB.tablabel}</div>
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
				</form>

			</div>
			<div class="modal-footer">
				<button class="md-close btn btn-default" type="button" id="btnclose" onclick="jQuery('#editApps_{$elementsA.id}').removeClass('md-show');jQuery('.md-overlay').css({ldelim}opacity: 0.0, visibility: 'hidden'{rdelim});return false;">{$MOD.LBL_CANCEL}</button>
				<button class="btn btn-primary" type="button" onclick="saveApp('edit','{$elementsA.id}')" id="btnsave">{$MOD.LBL_SAVE}</button>
			</div>
		</div>
		
	</div>
{/foreach}


<div id="email-box" class="clearfix">
	<div class="col-lg-12">	
			<table class="table" border="0" cellpadding="5" cellspacing="0" width="100%">
			<tr>
				<td rowspan="2" valign="top">
					<div class="infographic-box" style="width:30px;padding:0px;">
					<i class="fa fa-cubes purple-bg"></i>
					</div>
				</td>
				<td class="heading2" valign="bottom">
					<ol class="breadcrumb">				
						<li><h1><a href="index.php?module=gestion_app&action=ConfigApps">{$MOD.CONFIG_APPS} </a><h1></li>				
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
	<div class="row">	
		<div class="col-lg-12">
			<div class="main-box clearfix">
				<br/>
				<div class="pull-right" style="margin-right: 20px;">
						<a class="btn btn-info" href="index.php?module=gestion_app&action=CategoryApps">
							{$MOD.LBL_CATEGORYAPPS_BUTTON_LABEL}
						</a>
						<a class="btn btn-primary" href="index.php?module=gestion_app&action=CreateApp">
							{$MOD.LBL_CREATE_BUTTON_LABEL}
						</a>
				</div>				
				<br/>
				<div class="main-box-body clearfix">
					<table width="100%" border="0" cellpadding="5" cellspacing="0" class="table-responsive">
	                	<tr>
	                    	<th><h2>{$MOD.CONFIG_APPS_TITLE}</h2></th>
	                    		<td align="right">&nbsp;</td>
	                 	 </tr>
				  	</table>
				  	<br/>
				  	<div id="appscontents">
						{include file='gestion_app/ConfigAppsContents.tpl'}
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



		var updateOutput = function(e){ldelim}
			var list   = e.length ? e : jQuery(e.target),
				output = list.data('output');
			if (window.JSON) {ldelim}
				output.val(window.JSON.stringify(list.nestable('serialize')));
			{rdelim} 
			else {ldelim}
				output.val('Contacte al administrador');
			{rdelim}
		{rdelim};


		jQuery('#nestable').nestable({ldelim}
			group: 1,
			maxDepth: 0,
		{rdelim}).on('change', updateOutput);

		jQuery('#nestable2').nestable({ldelim}
			group: 1,
			maxDepth: 0,
		{rdelim});

		jQuery('#app_code').keyup(function(){ldelim}
			validField('app_code');
		{rdelim});

		jQuery('#app_price').keyup(function(){ldelim}
			validateDecimal('app_price');
		{rdelim});

		updateOutput(jQuery('#nestable').data('output', jQuery('#nestable-output')));


		{foreach item=entries key=id from=$CONFIGAPPLICATION name=outer}
			{assign var=count value=$smarty.foreach.outer.iteration}

			jQuery('#nestable_{$entries.id}').nestable({ldelim}
				group: 1,
				maxDepth: 0,
			{rdelim}).on('change', updateOutput);

			jQuery('#nestable2_{$entries.id}').nestable({ldelim}
				group: 1,
				maxDepth: 0,
			{rdelim});

			jQuery('#app_code_{$entries.id}').keyup(function(){ldelim}
				validField('app_code_{$entries.id}');
			{rdelim});

			jQuery('#app_price_{$entries.id}').keyup(function(){ldelim}
				validateDecimal('app_price_{$entries.id}');
			{rdelim});			

			updateOutput(jQuery('#nestable_{$entries.id}').data('output', jQuery('#nestable-output_{$entries.id}')));

		{/foreach}


	});


</script>

{literal}
<script>	

function saveApp(mode,id){

	if(mode == 'create'){

		if(validateConfigApps(id)){

			jQuery('#createApps').hide();
			var code = jQuery('#app_code').val();
			var name = jQuery('#app_name').val();
			var price = jQuery('#app_price').val();
			var description = jQuery('#app_descripcion').val();
			var status = jQuery('#app_status').val();
			var modules = jQuery('#nestable-output').val();
			var param= 'code=' + code + '&name=' + name  + '&price=' + price + '&description=' + description + '&status=' + status + '&modules=' + modules + '&mode=' + mode;



	        new Ajax.Request(
	        	'index.php',
	        	{queue: {position: 'end', scope: 'command'},
	                method: 'post',
	                postBody: 'action=gestion_appAjax&module=gestion_app&file=SaveEditApps&'+param,
	                onComplete: function(response) {
	                	console.log(response.responseText);
	                	jQuery('#NoteApps').hide();
	                	jQuery('#createApps').removeClass('md-show');
	                	if(response.responseText == 'CONFIG_SUCCESS'){
	                		alert('La aplicaci\u00F3n se ha creado correctamente');
	                		location.reload();
	                	}else{
	                		alert('Ha ocurrido un error. Por favor contacte al administrador');
	                		return false;
	                	}
	                    
	                }
	        	}	
			);
	
		}

	}else if(mode == 'edit'){

		if(validateConfigApps(id)){

			jQuery('#editApps_'+id).hide();
			var code = jQuery('#app_code_'+id).val();
			var name = jQuery('#app_name_'+id).val();
			var price = jQuery('#app_price_'+id).val();
			var description = jQuery('#app_descripcion_'+id).val();
			var status = jQuery('#app_status_'+id).val();
			var modules = jQuery('#nestable-output_'+id).val();
			var param= 'code=' + code + '&name=' + name  + '&price=' + price + '&description=' + description + '&status=' + status + '&modules=' + modules + '&mode=' + mode + '&id=' + id;

	        new Ajax.Request(
	        	'index.php',
	        	{queue: {position: 'end', scope: 'command'},
	                method: 'post',
	                postBody: 'action=gestion_appAjax&module=gestion_app&file=SaveEditApps&'+param,
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
	                postBody: 'action=gestion_appAjax&module=gestion_app&file=SaveEditApps&'+param,
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

