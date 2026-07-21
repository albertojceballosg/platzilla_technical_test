{foreach key=modulename item=modinfo from=$MODULOS_PLATZILLA}
	{assign var="modulelabel" value=$modulename}
	{if isset($APP.$modulename)}
		{assign var="modulelabel" value=$APP.$modulename}
	{/if}
	<tr>
		<td class="cellText small" width="20px"><i class="fa fa-external-link"></i></td>
		<td class="cellLabel small">{$modulelabel}</td>
		<td class="cellText small" width="15px" align=center>
	{if $modinfo.presence eq 0 && $modinfo.hassettings}
			<a href="index.php?module=Settings&action=ModuleManager&module_settings=true&formodule={$modulename}&parenttab=Settings" title="Configuración">
				<i class="fa fa-gears fa-fw fa-lg emerald"></i>
			</a>
	{elseif $modinfo.hassettings eq false}
		&nbsp;
	{/if}
		</td>
	</tr>
{/foreach}

