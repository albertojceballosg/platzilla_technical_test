{strip}
<table class="table table-striped table-hover">
	<thead>
		<tr>
			<th class="text-center">#</th>
			<th class="text-center"></th>
			<th class="text-left">{$MOD.LBL_CONFIG_APPS_NAME}</th>
			<th class="text-left">{$MOD.LBL_CONFIG_APPS_STATUS}</th>
			<th class="text-left">{$MOD.LBL_CATEGORYAPPS_LABEL}</th>
			<th>{$MOD.LBL_CONFIG_APPS_ACTION}</th>
		</tr>
	</thead>
	<tbody>
{foreach $APPLICATIONS as $application}
	<tr>
		<td class="text-center">{$application@iteration}</td>
		<td>{if ($application.image == 1)}<img src="{$IMAGES_PATH}/{$application.code}.png?{$TIMESTAMP}" width="60" />{/if}</td>
		<td>
			{$application.name} ({$MOD.LBL_CONFIG_APPS_CODE}: {$application.code})<br />
			{$application.description}
		</td>
		<td>{if ($application.status == 'Inactiva')}{$MOD.LBL_INACTIVE}{else}{$MOD.LBL_ACTIVE}{/if}</td>
		<td>{$application.category|join: '<br />'}
		</td>
		<td>
			<a class="md-trigger table-link" title="Editar" href="index.php?module=Settings&action=EditApps&record={$application.id}&parenttab=Settings" style="margin-right: 0;">
				<span class="fa-stack">
					<i class="fa fa-square fa-stack-2x"></i>
					<i class="fa fa-pencil fa-stack-1x fa-inverse"></i>
				</span>
			</a>
			<a class="table-link" title="Visibilidad" href="index.php?module=Settings&action=EditApplicationProfile&record={$application.id}&parenttab=Settings" style="margin-right: 0;">
				<span class="fa-stack">
					<i class="fa fa-square fa-stack-2x"></i>
					<i class="fa fa-eye fa-stack-1x fa-inverse"></i>
				</span>
			</a>
	{if ($application.id != 1)}
			<a class="table-link danger" title="Borrar" href="javascript: confirmdelete ('index.php?module=Settings&action=AppDelete&record={$application.id}&parenttab=Settings')" style="margin-right: 0;">
				<span class="fa-stack">
					<i class="fa fa-square fa-stack-2x"></i>
					<i class="fa fa-trash-o fa-stack-1x fa-inverse"></i>
				</span>
			</a>
	{/if}
		</td>
	</tr>
{/foreach}
	</tbody>
</table>
{/strip}