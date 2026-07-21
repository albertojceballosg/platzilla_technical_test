{strip}
<table class="table table-striped table-hover">
	<thead>
	<tr>
		<th class="text-center">#</th>
		<th class="text-center">{$MOD.LBL_CALCULATED_SYSTEM_TITLE}</th>
		<th class="text-center">{$MOD.LBL_CALCULATED_SYSTEM_MODULE}</th>
        <th class="text-center">{$MOD.LBL_CALCULATED_SYSTEM_RESULTS}</th>
        <th class="text-center">{$MOD.LBL_CALCULATED_FIELDS_UPDATED}</th>
		<th class="text-center">{$MOD.LBL_CALCULATED_FIELDS_PROCESS}</th>

	</tr>
	</thead>
	<tbody>
{foreach $ACS as $row}
	<tr id="system_{$row->getId ()}">
		<td class="text-center">{$row->getId ()}</td>
		<td title="{$row->getDescription ()}">{$row->getName ()}</td>
		<td class="text-left">{$row->getModuleName ()}</td>
        <td>{$row->getResult ()|number_format:2:',':'.'}</td>
        <td>{$row->getUpdatedDate ()}</td>
        {if $row->getStatus () eq CalculationSystemInterface::STATUS_ACTIVE}
            {assign var='textLink' value = 'text-warning'}
            {assign var='textTitle' value = 'Desactivar'}
            {assign var='iconStatus' value = 'fa-ban'}

        {else}
            {assign var='textLink' value = 'text-success'}
            {assign var='textTitle' value = 'Activar'}
            {assign var='iconStatus' value = 'fa-check'}
        {/if}
		<td>
			<a class="table-link {$textLink} active_system " title="{$textTitle} " rel="{$row->getId ()}@{$row->getName ()}" href="#">
				<span class="fa-stack">
					<i class="fa fa-square fa-stack-2x"></i>
					<i id="status-{$row->getId ()}" class="fa {$iconStatus} fa-stack-1x fa-inverse"></i>
				</span>
			</a>
			<a class="table-link text-default register_system " title="Registro de eventos" rel="{$row->getId ()}@{$row->getName ()}" href="index.php?module=calculated_fields&action=logCalculatedSystem&calculatedSystemId={$row->getId ()}">
				<span class="fa-stack">
					<i class="fa fa-square fa-stack-2x"></i>
					<i class="fa fa-search fa-stack-1x fa-inverse"></i>
				</span>
			</a>
			<a class="table-link text-primary edit_system " title="Editar {$row->getDescription ()}" href="index.php?module=calculated_fields&action=addCalculatedSystem&record={$row->getId ()}">
				<span class="fa-stack">
					<i class="fa fa-square fa-stack-2x"></i>
					<i class="fa fa-pencil fa-stack-1x fa-inverse"></i>
				</span>
			</a>
			<a class="table-link text-primary duplicate_system " title="Duplicar" rel="{$row->getId ()}@{$row->getName ()}" href="index.php?module=calculated_fields&action=duplicateCalculatedSystem&calculatedSystemId={$row->getId ()}">
				<span class="fa-stack">
					<i class="fa fa-square fa-stack-2x"></i>
					<i class="fa fa-copy fa-stack-1x fa-inverse"></i>
				</span>
			</a>
			<a class="table-link danger delete_system " title="Borrar" rel="{$row->getId ()}@{$row->getName ()}" href="#">
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