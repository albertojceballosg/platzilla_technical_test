{strip}
<div class="row">
	<div class="col-lg-12" style="background-color: #ffffff;">
		<table class="table" border="0" cellpadding="5" cellspacing="0" width="100%">
			<tr>
				<td rowspan="2" valign="top">
					<div class="infographic-box" style="width: 30px; padding: 0;">
						<i class="fa fa-eye red-bg"></i>
					</div>
				</td>
				<td class="heading2" valign="bottom">
					<ol class="breadcrumb">
						<li><a href="index.php?module=Settings&action=index&parenttab=Settings">{$MOD.LBL_SETTINGS}</a></li>
						<li><a href="index.php?module=Settings&action=ConfigApps&parenttab=Settings">{$MOD.CONFIG_APPS}</a></li>
						<li>Visibilidad</li>
						<li class="active">{$APPLICATION.app_name}</li>
					</ol>
				</td>
			</tr>
			<tr>
				<td class="small" colspan="3" valign="top">Configurar la visibilidad de los campos de los módulos que componen la aplicación</td>
			</tr>
		</table>
	</div>
	<form action="index.php" method="post" name="form" onsubmit="VtigerJS_DialogBox.block ();">
		<input type="hidden" name="module" value="Settings" />
		<input type="hidden" name="parenttab" value="Settings" />
		<input type="hidden" name="action" value="SaveApplicationProfile" />
		<input type="hidden" name="record" value="{$APPLICATION.config_applicationsid}" />
		<div class="col-lg-12" style="background-color: #ffffff;">
{if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
			<div class="alert {if (isset ($IS_ERROR)) && ($IS_ERROR)}alert-danger{else}alert-success{/if}">
				<strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
			</div>
{/if}
			<div class="col-lg-10 pull-left">
				<h1>{$APPLICATION.app_name}</h1>
			</div>
			<div class="col-lg-2 pull-right">
				<button type="submit" class="btn btn-success">{$APP.LBL_SAVE_LABEL}</button>
				&nbsp;
				<a href="index.php?module={$RETURN_MODULE}&action={$RETURN_ACTION}&parenttab=Settings" class="btn btn-warning">{$APP.LBL_CANCEL_BUTTON_LABEL}</a>
			</div>
		</div>
		<div class="main-box clearfix col-lg-12" style="margin-bottom: 0;">
			<div class="main-box-body clearfix">
				<div class="panel-group" id="profile">
{foreach $PROFILE_DATA.modules as $module}
					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title">
								<a data-toggle="collapse" data-parent="#profile" href="#{$module.name}">{$module.tablabel|@getTranslatedString:$module.name} ({$module.name})</a>
							</h4>
						</div>
						<div id="{$module.name}" class="panel-collapse collapse{if ($module@first)} in{/if}">
							<div class="panel-body">
	{foreach $module.blocks as $block}
								<div class="row block-container" id="block_{$block.blockid}">
									<div class="col-xs-12">
										<div class="main-box">
											<header class="title-section main-box-header clearfix">
												<h2>{$block.blocklabel|@getTranslatedString:$module.name}</h2>
											</header>
											<div class="main-box-body clearfix">
		{foreach $block.fields as $field}
			{$typeofdata=explode ('~', $field.typeofdata)}
												<div class="col-xs-6">
													<div class="col-md-4">
														<div class="label-input">
															<h4 style="margin: 0;"><label for="{$field.fieldname}" style="font-weight: 300; margin: 0;">{$field.fieldlabel|@getTranslatedString:$module.name}{if ($typeofdata[1] == 'M')} <span style="color: #FF0000;">*</span>{/if}</label></h4>
														</div>
													</div>
													<div class="form-group col-md-8 field-container">
			{if ($typeofdata[1] == 'M') || ($field.uitype == 4)}
														<input type="hidden" name="fieldids[{$module.tabid}][]" value="{$field.fieldid}" />
														<input type="checkbox" id="{$field.fieldname}" disabled="disabled" checked="checked" />
			{else}
														<input type="checkbox" id="{$field.fieldname}" name="fieldids[{$module.tabid}][]" value="{$field.fieldid}"{if ($field.visible == 0)} checked="checked"{/if} />
			{/if}
													</div>
												</div>
		{/foreach}
											</div>
										</div>
									</div>
								</div>
	{/foreach}
								<div class="row block-container" id="customview_{$block.blockid}">
									<div class="col-xs-12">
										<div class="main-box">
											<header class="title-section main-box-header clearfix">
												<h2>Vistas</h2>
											</header>
											<div class="main-box-body clearfix">
												<div class="row">
													<div class="col-xs-6">
														<div class="col-md-4">
															<div class="label-input">
																<h4 style="margin: 0;">
																	<label for="default-{$customView.cvid}" style="font-weight: 300; margin: 0;">Vista por defecto <span style="color: #FF0000;">*</span></label>
																</h4>
															</div>
														</div>
														<div class="form-group col-md-8 field-container">
															<select id="default-{$customView.cvid}" name="cvids[{$module.tabid}][defaultcvid]" class="form-control">
	{assign var='hasSelectedDefaultView' value=false}
	{foreach $module.customviews as $customView}
																<option value="{$customView.cvid}"{if ($customView.profiledefault == 1) && (!$hasSelectedDefaultView)} selected="selected"{/if}>{if ($customView.viewname == 'All')}{$APP.COMBO_ALL}{else}{$customView.viewname}{/if}</option>
		{if ($customView.profiledefault == 1) && (!$hasSelectedDefaultView)}
			{assign var='hasSelectedDefaultView' value=true}
		{/if}
	{/foreach}
															</select>
														</div>
													</div>
												</div>
	{foreach $module.customviews as $customView}
												<div class="col-xs-6">
													<div class="col-md-4">
														<div class="label-input">
															<h4 style="margin: 0;">
																<label for="view-{$customView.cvid}" style="font-weight: 300; margin: 0;">{if ($customView.viewname == 'All')}{$APP.COMBO_ALL}{else}{$customView.viewname}{/if}</label>
															</h4>
														</div>
													</div>
													<div class="form-group col-md-8 field-container">
														<input type="checkbox" id="view-{$customView.cvid}" name="cvids[{$module.tabid}][cvids][]" value="{$customView.cvid}"{if ($customView.permissions == 0)} checked="checked"{/if} />
													</div>
												</div>
	{/foreach}
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
{/foreach}
				</div>
			</div>
		</div>
		<div class="col-lg-12" style="background-color: #ffffff; padding: 15px 0;">
			<div class="col-lg-2 pull-right">
				<button type="submit" class="btn btn-success">{$APP.LBL_SAVE_LABEL}</button>
				&nbsp;
				<a href="index.php?module={$RETURN_MODULE}&action={$RETURN_ACTION}&parenttab=Settings" class="btn btn-warning">{$APP.LBL_CANCEL_BUTTON_LABEL}</a>
			</div>
		</div>
<script type="text/javascript">
{literal}
{/literal}
</script>
{/strip}