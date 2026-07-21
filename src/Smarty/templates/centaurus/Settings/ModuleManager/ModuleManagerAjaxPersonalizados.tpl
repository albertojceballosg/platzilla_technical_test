{assign var="totalCustomModules" value="0"}
					<tr>
						<td colspan="6">
							<select class="form-control" title="Filtrar por aplicación..." style="display: inline-block;" onchange="filterByApplication (this);">
								<option value="">Filtrar por aplicación...</option>
{foreach $APPLICATIONS_MODULE_NAMES as $applicationId => $applicationData}
								<option value="{$applicationId}">{$applicationData.name}</option>
{/foreach}
								<option value="-1">Sin aplicación</option>
							</select>
						</td>
					</tr>
{foreach key=modulename item=modinfo from=$MODULOS_PERSONALIZADOS}
	{if $modinfo.customized eq true}
		{assign var="totalCustomModules" value=$totalCustomModules+1}
		{assign var="modulelabel" value=$modulename}
		{if isset($APP.$modulename)}
			{assign var="modulelabel" value=$APP.$modulename}
		{/if}
					<tr id="row-{$modulename}" class="module">
						<td class="cellText small" width="20px"><i class="fa fa-external-link"></i></td>
						<td class="cellLabel small" width="30px">{$modulelabel}</td>
						<td class="application-logo-cell">
		{foreach $APPLICATIONS_MODULE_NAMES as $applicationId => $applicationData}
			{if (!empty ($applicationData.modulenames)) && (in_array ($modulename, $applicationData.modulenames))}
							<figure class="application-logo">
								<img src="../../../../storage/appsimages/{$applicationData.code}.png" alt="{$applicationData.name}" title="{$applicationData.name}" />
							</figure>
			{/if}
		{/foreach}
						</td>
						<td class="cellText small" width="15px" align=center>
		{if $modinfo.presence eq 0}
							<a href="javascript:void(0);" onclick="vtlib_toggleModule('{$modulename}', 'module_disable');" title="Deshabilitar">
								<i class="fa fa-check-circle fa-fw fa-lg green"></i>
							</a>
		{else}
							<a href="javascript:void(0);" onclick="vtlib_toggleModule('{$modulename}', 'module_enable');" title="Habilitar">
								<i class="fa fa-times-circle fa-fw fa-lg red"></i>
							</a>
		{/if}
						</td>
						<td class="cellText small" width="15px" align=center>
		{if $modinfo.presence eq 0 && $modinfo.hassettings}
							<a href="index.php?module=Settings&action=ModuleManager&module_settings=true&formodule={$modulename}&parenttab=Settings" title="Configuración">
								<i class="fa fa-gears fa-fw fa-lg emerald"></i>
							</a>
		{elseif $modinfo.hassettings eq false}
							&nbsp;
		{/if}
						</td>
						<td class="cellText small" width="15px" align=center>
		{if $ESINSTANCIA eq 1 }
			{if $modinfo.presence eq 0 and $modinfo.isplatzilla eq 0 }
							<a href="index.php?module=Settings&action=eliminarModulo&module_settings=true&formodule={$modulename}&parenttab=Settings&return_module=Settings" title="Eliminar">
								<i class="fa fa-trash-o fa-fw fa-lg red"></i>
							</a>
			{/if}
		{elseif $modinfo.presence eq 0 and $modinfo.presenciaappsplatzilla eq 0 and $modinfo.presenciainstanciasclientes eq 0}
							<a href="index.php?module=Settings&action=eliminarModulo&module_settings=true&formodule={$modulename}&parenttab=Settings&return_module=Settings" title="Eliminar">
								<i class="fa fa-trash-o fa-fw fa-lg red"></i>
							</a>
		{/if}
						</td>
					</tr>
	{/if}
{/foreach}
{if $totalCustomModules eq 0}
					<tr>
						<td class="cellLabel small" colspan="6"><b>{$MOD.VTLIB_LBL_MODULE_MANAGER_NOMODULES}</b></td>
					</tr>
{/if}