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

<script src="modules/Settings/Settings.js"></script>	

<div class="md-modal md-effect-1" id="modal-1">
	<div class="md-content">
		<div class="modal-header">
			<button class="md-close close">×</button>
			<h4 class="modal-title">{$MOD.LBL_CHANGE_PASSWORD}</h4>
		</div>
		<div class="modal-body">
			<form name="ChangePassword" onsubmit="return validatePass();" method="POST">
			<input name='module' type='hidden' value='Users'>
			<input name='return_action' type='hidden' value='DetailView'>
			<input name='changepassword' type='hidden' value='true'>
			<input name='return_id' type='hidden' value='{$ID}'>
			<input name='record' type='hidden' value='{$ID}'>
			<input name='action' type='hidden' value='Save'>
			<div class="col-lg-12">	
				<div class="main-box">
					<div class="main-box-body clearfix">
						<div class="row">
							{if $IS_ADMIN neq 'true' && $IS_ROLESUP neq 'true'}
							<div class="form-group col-lg-12" id="td_productname">
								<font color="red">*</font>
								<label>{$MOD.LBL_OLD_PASSWORD}</label>
								<input name="old_password" id="old_password" tabindex="" value="" type="password" class="form-control" size="15">
								<input name='is_admin' type='hidden' value='1'>
							</div>
							{else}
								<input name='old_password' type='hidden'><input name='is_admin' type='hidden' value='0'>
							{/if}
							<div class="form-group col-lg-12">
								<font color="red"></font>
								<label>{$MOD.LBL_NEW_PASSWORD}</label>
								<input name="new_password" id="new_password" tabindex="" value="" type='password' class="form-control" size="15">
							</div>
							<div class="form-group col-lg-12">
								<font color="red"></font>
								<label>{$MOD.LBL_CONFIRM_PASSWORD}</label>
								<input name="confirm_new_password" id="confirm_new_password" tabindex="" value="" type='password' class="form-control" size="15">
							</div>
						</div>
					</div>
				</div>
			</div>
			<button type="submit" class="btn btn-success">{$APP.LBL_SAVE_LABEL}</button>

			</form>
		</div>
	</div>
</div>

<div class="main-box-body clearfix" style="width:100%;background-color:#FFFFFF;">
	<div class="tabs-wrapper">
		<ul class="nav nav-tabs">
			{assign var=style value=' class="active"'}
			{foreach key=BLOCKID item=BLOCKLABEL from=$BLOCKS name=blk}				
				{if $MOD.$BLOCKLABEL neq ''}
					{assign var=count value=$smarty.foreach.blk.iteration}
					<li{$style}><a href="#tab-{$count}" data-toggle="tab" onclick="hideInfo({$BLOCKID})">{$MOD.$BLOCKLABEL}</a></li>
					{if $style neq ''}
						{assign var=style value=""}
					{/if}
					
				{/if}
			{/foreach}
		</ul>
		
		<div class="tab-content">
			{assign var=style value="in active"}
			{foreach key=BLOCKID item=BLOCKLABEL from=$BLOCKS name=blk}			
			{if $MOD.$BLOCKLABEL neq ''}
				{assign var=count value=$smarty.foreach.blk.iteration}
				<div class="tab-pane fade{$style}" id="tab-{$count}">
					{foreach item=data from=$FIELDS.$BLOCKID name=itr}
						{assign var=label value=$data.name|@getTranslatedString:'Settings'}
						<div class="main-box infographic-box" style="float:left;width:280px;max-width:280px;height:150px;max-height:150px;margin-left:5px;" onclick="showInfo('{$MOD.$BLOCKLABEL}',{$count}, '{$data.link}', '{$label}');">
							<a href="{$data.link}">
							<i class="{$data.icon}"></i>
							</i>
							</a>
							<span class="headline">
							<a href="{$data.link}">
							<b>{$label}</b>
							</a>
							</span>
							{assign var=description value=$data.description|@getTranslatedString:'Settings'}	
							<span class="headline" style="font-size:80%">{$description}</span>
						</div>
					{/foreach}
				</div>
				{if $style neq ''}
					{assign var=style value=""}
				{/if}
			{/if}
			{/foreach}
			<div class="row" id="div-userinfo" >
				<div class="col-md-9">
					<div class="main-box no-header">
						<div class="main-box-body clearfix">Correo y contraseña</div>
					</div>					
					{foreach key=header name=blockforeach item=detail from=$BLOCKS_USER_INFO}
					
						{strip}		
						{assign var=detailD value=$detail}				
						<div class="tab-pane fade {if $smarty.foreach.blockforeach.iteration eq '1'}active in{/if}" id="tab-{$smarty.foreach.blockforeach.iteration}">
							<div class="table-responsive">
								<ul class="widget-users row">								
								<br><br>
								{foreach item=detail from=$detailD}								
									{foreach key=label item=data from=$detail}				
									   {assign var=keyid value=$data.ui}
									   {assign var=keyval value=$data.value}
									   {assign var=keytblname value=$data.tablename}
									   {assign var=keyfldname value=$data.fldname}
									   {assign var=keyfldid value=$data.fldid}
									   {assign var=keyoptions value=$data.options}
									   {assign var=keysecid value=$data.secid}
									   {assign var=keyseclink value=$data.link}
									   {assign var=keycursymb value=$data.cursymb}
									   {assign var=keysalut value=$data.salut}
									   {assign var=keycntimage value=$data.cntimage}
									   {assign var=keyadmin value=$data.isadmin}
									   {if $label ne ''}
									   <li class="col-md-6">
											<div class="details">
												<div class="name">
													<a href="#">{$label}</a>
												</div>
												<div class="time">
													{include file="DetailViewUI.tpl"}
												</div>
											</div>
										</li>
									   {else}
									   <li class="col-md-6"></li>
									   {/if}
									{/foreach}
								{/foreach}
								</ul>
							</div>
						</div>
						{assign var=list_numbering value=$smarty.foreach.blockforeach.iteration}
						{/strip}
						
					{/foreach}
				</div>
				<div class="col-md-3">
					<div class="panel panel-default">
						<div class="panel-heading">Otras configuraciones de perfil:</div>
						<div class="panel-body">				
							{foreach key=key item=BLOCKID from=$BLOCKS_ID name=blk}								
								{assign var=count value=$smarty.foreach.blk.iteration}
								<ul class="list-group">
									{foreach item=data from=$FIELDS.$BLOCKID name=itr}
										{if $data.name eq 'LBL_USER_INFO'} 
											{php}continue;{/php}
										{/if}

										{assign var=label value=$data.name|@getTranslatedString:'Settings'}
									{* [TT11394] AJUSTES CONFIGURACION Y MI CUENTA PLATZILLA - PARTE 1 Keyla Rodríguez 28/10/2016 *}
										{if ( ($label eq 'Imagen de usuario') || ($label eq 'Información') )}
										<li class="list-group-item" onclick="showDivMenu('{$data.link}','div-userinfo','{$label}')"><a href="#">{$label}</a></li>
										{/if}
									{/foreach}
								</ul>

							{/foreach}													
							
							{$EDIT_BUTTON}
						</div>
					</div>
				</div>
			</div>			
			<div class="row" id="div-imgusr" style="display:none">
				<div class="col-md-9">
					<div class="main-box no-header">
						<div class="main-box-body clearfix"></div>
					</div>
					{foreach key=header name=blockforeach item=detail from=$BLOCKS_IMAGE_USER}	
						{strip}												
						<div class="tab-pane fade {if $smarty.foreach.blockforeach.iteration eq '1'}active in{/if}" id="tab-{$smarty.foreach.blockforeach.iteration}">
							<div class="table-responsive">
								<ul class="widget-users row">								
								<br><br>
								{foreach item=details from=$detail}	
									{foreach key=label item=data from=$details}				
									   {assign var=keyid value=$data.ui}
									   {assign var=keyval value=$data.value}
									   {assign var=keytblname value=$data.tablename}
									   {assign var=keyfldname value=$data.fldname}
									   {assign var=keyfldid value=$data.fldid}
									   {assign var=keyoptions value=$data.options}
									   {assign var=keysecid value=$data.secid}
									   {assign var=keyseclink value=$data.link}
									   {assign var=keycursymb value=$data.cursymb}
									   {assign var=keysalut value=$data.salut}
									   {assign var=keycntimage value=$data.cntimage}
									   {assign var=keyadmin value=$data.isadmin}
									   {if $label ne ''}
									   <li class="col-md-6">
											<div class="details">
												<div class="name">
													<a href="#">{$label}</a>
														{if $CURRENT_USER_IMAGE}
															<img src="{$CURRENT_USER_IMAGE}" alt="" width="60" heigth="60"/>
														{else}							
															<img src="themes/centaurus/img/photo.png" alt="" width="60" heigth="60"/>
														{/if}
												</div>
												<div class="time">
													{include file="DetailViewUI.tpl"}
												</div>
												
											</div>
										</li>
										
									   {else}									   
									   <li class="col-md-6"></li>
									   {/if}
								
									{/foreach}
								{/foreach}
								</ul>	
								<div class="col-md-6">
									<div class="details">
										
										</div>
									</div>							
							</div>
						</div>
						{assign var=list_numbering value=$smarty.foreach.blockforeach.iteration}
						{/strip}						
					{/foreach}
				</div>
				<div class="col-md-3">
					<div class="panel panel-default">
						<div class="panel-heading">Otras configuraciones de perfil:</div>
						<div class="panel-body">				
							{foreach key=key item=BLOCKID from=$BLOCKS_ID name=blk}								
								{assign var=count value=$smarty.foreach.blk.iteration}
								<ul class="list-group">
									{foreach item=data from=$FIELDS.$BLOCKID name=itr}
										{if $data.name eq 'LBL_IMAGE_USER'} 
											{php}continue;{/php}
										{/if}		
										{assign var=label value=$data.name|@getTranslatedString:'Settings'}
									{* [TT11394] AJUSTES CONFIGURACION Y MI CUENTA PLATZILLA - PARTE 1 Keyla Rodríguez 28/10/2016 *}
										{if (($label eq 'Correo y contraseña') || ($label eq 'Información') )}
										<li class="list-group-item" onclick="showDivMenu('{$data.link}','div-imgusr','{$label}')"><a href="#">{$label}</a></li>
										{/if}
									{/foreach}
								</ul>
							{/foreach}		

							{$EDIT_BUTTON}						
						</div>
					</div>
				</div>
			</div>
			<div class="row" id="div-info" style="display:none">
				<div class="col-md-9">
					<div class="main-box no-header">
						<div class="main-box-body clearfix"></div>
					</div>	
					{foreach key=header name=blockforeach item=detail from=$BLOCKS_MORE_INFO}	
						{strip}												
						<div class="tab-pane fade {if $smarty.foreach.blockforeach.iteration eq '1'}active in{/if}" id="tab-{$smarty.foreach.blockforeach.iteration}">
							<div class="table-responsive">
								<ul class="widget-users row">								
								<br><br>
								{foreach item=details from=$detail}	
									{foreach key=label item=data from=$details}				
									   {assign var=keyid value=$data.ui}
									   {assign var=keyval value=$data.value}
									   {assign var=keytblname value=$data.tablename}
									   {assign var=keyfldname value=$data.fldname}
									   {assign var=keyfldid value=$data.fldid}
									   {assign var=keyoptions value=$data.options}
									   {assign var=keysecid value=$data.secid}
									   {assign var=keyseclink value=$data.link}
									   {assign var=keycursymb value=$data.cursymb}
									   {assign var=keysalut value=$data.salut}
									   {assign var=keycntimage value=$data.cntimage}
									   {assign var=keyadmin value=$data.isadmin}
									   {if $label ne ''}
									   <li class="col-md-6">
											<div class="details">
												<div class="name">
													<a href="#">{$label}</a>
												</div>
												<div class="time">
													{include file="DetailViewUI.tpl"}
												</div>
											</div>
										</li>
									   {else}									   
									   <li class="col-md-6"></li>
									   {/if}
									{/foreach}
								{/foreach}
								</ul>								
							</div>
						</div>
						{assign var=list_numbering value=$smarty.foreach.blockforeach.iteration}
						{/strip}						
					{/foreach}				
				</div>
				<div class="col-md-3">
					<div class="panel panel-default">
						<div class="panel-heading">Otras configuraciones de perfil:</div>
						<div class="panel-body">				
							{foreach key=key item=BLOCKID from=$BLOCKS_ID name=blk}								
								{assign var=count value=$smarty.foreach.blk.iteration}
								<ul class="list-group">
									{foreach item=data from=$FIELDS.$BLOCKID name=itr}
										{if $data.name eq 'LBL_MORE_INFO'} 
											{php}continue;{/php}
										{/if}
										{assign var=label value=$data.name|@getTranslatedString:'Settings'}
							{* [TT11394] AJUSTES CONFIGURACION Y MI CUENTA PLATZILLA - PARTE 1 Keyla Rodríguez 28/10/2016 *}			{if ( ($label eq 'Correo y contraseña') || ($label eq 'Imagen de usuario')) }
									<li class="list-group-item" onclick="showDivMenu('{$data.link}','div-info','{$label}')"><a href="#">{$label}</a></li>
								{/if}			
									{/foreach}
								</ul>
							{/foreach}								

							{$EDIT_BUTTON}
						</div>
					</div>
				</div>
			</div>	
		
		</div>
	</div>
</div>

<script type="text/javascript">
	function validatePass() {ldelim}
		if (jQuery('#new_password').val() == '') {ldelim}
			alert("{$MOD.ERR_ENTER_NEW_PASSWORD}");
			return false;
		{rdelim}
		if (jQuery('#confirm_new_password').val() == '') {ldelim}
			alert("{$MOD.ERR_ENTER_CONFIRMATION_PASSWORD}");
			return false;
		{rdelim}
		if (jQuery('#new_password').val() != jQuery('#confirm_new_password').val()) {ldelim}
			alert("{$MOD.ERR_REENTER_PASSWORDS}");
			return false;
		{rdelim}
		return true;
	{rdelim}

</script>