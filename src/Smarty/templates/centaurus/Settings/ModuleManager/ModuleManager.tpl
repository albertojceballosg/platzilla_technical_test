{strip}
<style type="text/css">
	.main-box-header .btn {
		margin-left: 0.5em;
	}
	ul#legend {
		margin: 1em 0;
	}
	ul#legend > li {
		display: inline-block;
		padding-right: 1em;
	}
	.application-logo-container {
		line-height: 1em;
		padding: 0;
	}
	.application-logo {
		display: inline-block;
		height: 30px;
		margin-right: 1em;
		overflow: hidden;
		width:  30px;
	}
	.application-logo > img {
		border-radius: 50%;
		max-width: 100%;
	}
	tr.module .module-label {
		width: 25%;
	}
	.action {
		text-align: center;
		width: 3em;
	}
	.action .btn.btn-icon {
		background-color: transparent;
		font-size:   14px;
		height:      27px;
		line-height: 27px;
		margin:      0 5px 0 0;
		padding:     0;
		text-align:  center;
		width:       27px;
	}
	.action .btn:active,
	.action .btn.active {
		box-shadow: none;
	}
</style>
<div id="email-box" class="clearfix">
	<table class="table" border="0" cellpadding="5" cellspacing="0" width="100%">
		<tr>
			<td rowspan="2" valign="top">
				<div class="infographic-box"><i class="fa fa-list-alt emerald-bg"></i></div>
			</td>
			<td class="heading2" valign="bottom">
				<ol class="breadcrumb">
					<li><a href="index.php?module=Settings&action=index&parenttab=Settings">{$MOD.LBL_SETTINGS} </a></li>
					<li class="active">{$MOD.VTLIB_LBL_MODULE_MANAGER|upper}</li>
				</ol>
			</td>
		</tr>
		<tr>
			<td class="small" valign="top">{$MOD.VTLIB_LBL_MODULE_MANAGER_DESCRIPTION}</td>
		</tr>
	</table>
{if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
	<div class="row">
		<div class="alert {if (isset ($IS_ERROR)) && ($IS_ERROR)}alert-danger{else}alert-success{/if}">
			<strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
		</div>
	</div>
{/if}
	<div class="main-box clearfix">
		<header class="main-box-header clearfix text-right">
			<div class="pull-right">
				<button class="btn btn-primary" onclick="ModuleManager.openModuleCreatorModal ();">{$APP.LBL_CREATE_MODULE}</button>
				<button class="btn btn-primary">{$APP.LBL_DUPLICATE_MODULE}</button>
			</div>
		</header>
		<div class="main-box-body clearfix">
			<div class="tabs-wrapper">
				<ul class="nav nav-tabs">
					<li class="active"><a href="#tab-user" data-toggle="tab">Módulos de aplicaciones</a></li>
					<li><a href="#tab-tools" data-toggle="tab">Módulos de Platzilla</a></li>
					<li><a href="#tab-admin" data-toggle="tab">Módulos administrativos</a></li>
				</ul>
				<div class="tab-content">
					<div id="tab-user" class="tab-pane fade in active">
						<ul id="legend">
							<li><i class="fa fa-check-circle fa-fw fa-lg green"></i> Módulo Activo</li>
							<li><i class="fa fa-times-circle fa-fw fa-lg red"></i> Módulo Inactivo</li>
							<li><i class="fa fa-gears fa-fw fa-lg emerald"></i> Configuración</li>
							<li><i class="fa fa-trash-o fa-fw fa-lg red"></i> Eliminar</li>
						</ul>
						<table class="table">
							<tr>
								<td colspan="5">
									<select class="form-control" title="Filtrar por aplicación..." style="display: inline-block;" onchange="ModuleManager.filterByApplication (this);">
										<option value="">(Todas las aplicaciones)</option>
										<option value="-1">(Sin aplicación)</option>
{foreach $APPLICATIONS as $application}
										<option value="{$application->getCode ()}">{$application->getName ()}</option>
{/foreach}
									</select>
								</td>
							</tr>
{if (!empty ($USER_MODULES))}
	{foreach $USER_MODULES as $module}
		{if (!empty ($MODULE_APPLICATIONS[$module->getName ()]))}
			{assign var='moduleApplications' value=$MODULE_APPLICATIONS[$module->getName ()]}
			{assign var='moduleApplicationCodes' value=array_keys ($moduleApplications)}
		{else}
			{assign var='moduleApplications' value=null}
			{assign var='moduleApplicationCodes' value=null}
		{/if}
							<tr id="row-{$module->getName ()}" class="module" data-applications="{if (!empty ($moduleApplicationCodes))}['{implode ('\', \'', $moduleApplicationCodes)}']{else}null{/if}">
								<td class="module-label">
									<p style="margin: 0;">{$module->getLabel ()}</p>
									<p style="font-size: 0.85em; font-style: italic; margin: 0;">({$module->getName ()})</p>
								</td>
								<td class="application-logo-container">
		{if (!empty ($moduleApplications))}
			{foreach $moduleApplications as $applicationCode => $applicationName}
									<figure class="application-logo">
										<img src="storage/appsimages/{$applicationCode}.png" alt="{$applicationName}" title="{$applicationName}" />
									</figure>
			{/foreach}
		{/if}
								</td>
								<td class="action">
		{if (in_array ($module->getPresence (), array (Module::PRESENCE_USER_DEFINED, Module::PRESENCE_VISIBLE)))}
									<form action="index.php" method="post" onsubmit="return confirm ('¿Estás seguro que quieres deshabilitar el módulo {$module->getLabel ()}?');">
										<input type="hidden" name="module" value="Settings" />
										<input type="hidden" name="action" value="DisableModule" />
										<input type="hidden" name="modulename" value="{$module->getName ()}" />
										<input type="hidden" name="Ajax" value="true" />
										<button type="submit" class="btn btn-icon" title="Deshabilitar"><i class="fa fa-check-circle fa-fw fa-lg green"></i></button>
									</form>
		{else}
									<form action="index.php" method="post" onsubmit="return confirm ('¿Estás seguro que quieres habilitar el módulo {$module->getLabel ()}?');">
										<input type="hidden" name="module" value="Settings" />
										<input type="hidden" name="action" value="EnableModule" />
										<input type="hidden" name="modulename" value="{$module->getName ()}" />
										<input type="hidden" name="Ajax" value="true" />
										<button type="submit" class="btn btn-icon" title="Deshabilitar"><i class="fa fa-times-circle fa-fw fa-lg red"></i></button>
									</form>
		{/if}
								</td>
								<td class="action">
		{if (file_exists ("{$ROOT_FOLDER_PATH}/modules/{$module->getName ()}/Settings.php"))}
									<a href="index.php?module=Settings&action=LayoutBlockList&parenttab=Settings&formodule={$module->getName ()}" class="btn btn-icon" title="Configuración"><i class="fa fa-gears fa-fw fa-lg emerald"></i></a>
		{/if}
								</td>
								<td class="action">
		{if (in_array ($module->getPresence (), array (Module::PRESENCE_USER_DEFINED, Module::PRESENCE_VISIBLE))) && (empty ($moduleApplications))}
									<form action="index.php" method="post" onsubmit="return confirm ('¿Estás seguro que quieres eliminar el módulo {$module->getLabel ()}?');">
										<input type="hidden" name="module" value="Settings" />
										<input type="hidden" name="action" value="DeleteModule" />
										<input type="hidden" name="modulename" value="{$module->getName ()}" />
										<input type="hidden" name="Ajax" value="true" />
										<button type="submit" class="btn btn-icon" title="Eliminar"><i class="fa fa-trash-o fa-fw fa-lg red"></i></button>
									</form>
		{/if}
								</td>
							</tr>
	{/foreach}
{else}
							<tr>
								<td colspan="5" class="text-center">{$MOD.VTLIB_LBL_MODULE_MANAGER_NOMODULES}</td>
							</tr>
{/if}
						</table>
					</div>
					<div id="tab-tools" class="tab-pane fade">
						<table class="table">
{if (!empty ($TOOL_MODULES))}
	{foreach $TOOL_MODULES as $module}
							<tr>
								<td class="module-label">
									<p style="margin: 0;">{$module->getLabel ()}</p>
									<p style="font-size: 0.85em; font-style: italic; margin: 0;">({$module->getName ()})</p>
								</td>
								<td class="action">
		{if (file_exists ("{$ROOT_FOLDER_PATH}/modules/{$module->getName ()}/Settings.php"))}
									<a href="index.php?module=Settings&action=LayoutBlockList&parenttab=Settings&formodule={$module->getName ()}" class="btn btn-icon" title="Configuración"><i class="fa fa-gears fa-fw fa-lg emerald"></i></a>
		{/if}
								</td>
							</tr>
	{/foreach}
{else}
							<tr>
								<td colspan="3" class="text-center">{$MOD.VTLIB_LBL_MODULE_MANAGER_NOMODULES}</td>
							</tr>
{/if}
						</table>
					</div>
					<div id="tab-admin" class="tab-pane fade">
						<table class="table">
{if (!empty ($ADMIN_MODULES))}
	{foreach $ADMIN_MODULES as $module}
							<tr>
								<td class="module-label">
									<p style="margin: 0;">{$module->getLabel ()}</p>
									<p style="font-size: 0.85em; font-style: italic; margin: 0;">({$module->getName ()})</p>
								</td>
								<td class="action">
		{if (file_exists ("{$ROOT_FOLDER_PATH}/modules/{$module->getName ()}/Settings.php"))}
									<a href="index.php?module=Settings&action=LayoutBlockList&parenttab=Settings&formodule={$module->getName ()}" class="btn btn-icon" title="Configuración"><i class="fa fa-gears fa-fw fa-lg emerald"></i></a>
		{/if}
								</td>
							</tr>
	{/foreach}
{else}
							<tr>
								<td colspan="3" class="text-center">{$MOD.VTLIB_LBL_MODULE_MANAGER_NOMODULES}</td>
							</tr>
{/if}
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
{include file='Settings/ModuleManager/ModuleCreatorWizard.tpl'}
<script type="text/javascript" src="modules/Settings/module-manager.js"></script>
{/strip}