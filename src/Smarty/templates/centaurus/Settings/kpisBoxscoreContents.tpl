{strip}
<table class="table table-striped table-hover">
	<thead>
	<tr>
		<th class="text-center">#</th>
		<th class="text-center">{$MOD.LBL_KPIS_BOXSCORE_TITLE}</th>
		<th class="text-center">{$MOD.LBL_KPIS_BOXSCORE_MODULE}</th>
		<th class="text-center">{$MOD.LBL_KPIS_BOXSCORE_DESCRIPCION}</th>
		<th class="text-center">{$MOD.LBL_KPIS_BOXSCORE_ACTIVE}</th>
	</tr>
	</thead>
	<tbody>
{foreach $KPIS as $elements}
	<tr>
		<td class="text-center">{$elements.kpisboxscoreid}</td>
		<td>{$elements.name}</td>
		<td>{$elements.modulelabel}</td>
		<td>{$elements.description}</td>
		<td class="text-center">
			<span class="label label-{if ($elements.active == 'Activa')}success{else}danger{/if}">{$elements.active}</span>
		</td>
		<td>
			<a class="md-trigger table-link" title="Editar" href="index.php?module=Settings&action=DetailKpisBoxscore&record={$elements.kpisboxscoreid}&parenttab=Settings">
				<span class="fa-stack">
					<i class="fa fa-square fa-stack-2x"></i>
					<i class="fa fa-search fa-stack-1x fa-inverse"></i>
				</span>
			</a>
			<a class="md-trigger table-link" title="Editar" href="index.php?module=Settings&action=EditKpisBoxscore&record={$elements.kpisboxscoreid}&parenttab=Settings">
				<span class="fa-stack">
					<i class="fa fa-square fa-stack-2x"></i>
					<i class="fa fa-pencil fa-stack-1x fa-inverse"></i>
				</span>
			</a>
			<a class="table-link danger" title="Borrar" href="javascript:confirmdelete ('index.php?module=Settings&action=KpisBoxscoreDelete&record={$elements.kpisboxscoreid}&parenttab=Settings')">
				<span class="fa-stack">
					<i class="fa fa-square fa-stack-2x"></i>
					<i class="fa fa-trash-o fa-stack-1x fa-inverse"></i>
				</span>
			</a>
		</td>
	</tr>
{/foreach}
	</tbody>
</table>
{/strip}