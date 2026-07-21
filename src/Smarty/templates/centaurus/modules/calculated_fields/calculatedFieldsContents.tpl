{strip}
<table class="table table-striped table-hover">
	<thead>
	<tr>
		<th class="text-center">#</th>
		<th class="text-center">{$MOD.LBL_CALCULATED_FIELDS_TITLE}</th>
		<th class="text-center">{$MOD.LBL_CALCULATED_FIELDS_MODULE}</th>
		<th class="text-center">{$MOD.LBL_CALCULATED_FIELDS_NAME}</th>
        <th class="text-center">{$MOD.LBL_CALCULATED_FIELDS_OPERATION}</th>
        <th class="text-center">{$MOD.LBL_CALCULATED_FIELDS_RESULTS}</th>
        <th class="text-center">{$MOD.LBL_CALCULATED_FIELDS_UPDATED}</th>
		<th class="text-center">{$MOD.LBL_CALCULATED_FIELDS_ACTIVE}</th>
		<th class="text-center">{$MOD.LBL_CALCULATED_FIELDS_PROCESS}</th>

	</tr>
	</thead>
	<tbody>
{foreach $ACF as $row}
	<tr id="field_{$row->getId ()}">
		<td class="text-center">{$row->getId ()}</td>
		<td title="{$row->getDescription ()}">{$row->getName ()}</td>
		<td>{$row->getTabLabel ()}</td>
		<td>{$row->getFieldLabel ()}</td>
        <td>{$MOD.CALCULATED_FIELDS_OPERATIONS[$row->getOperationName ()]}</td>
        <td>{$row->getResult ()}</td>
        <td>{$row->getUpdatedDate ()}</td>
		<td class="text-center">
            {if $row->getStatus () eq CalculationElementInterface::STATUS_ACTIVE}
                {assign var='textLabel' value = 'success'}
            {else}
                {assign var='textLabel' value = 'danger'}
            {/if}
			<span class="label label-{$textLabel}">{$MOD.CALCULATED_SYSTEM_STATUS[$row->getStatus ()]}</span>
		</td>
		<td>
			<a class="md-trigger table-link" title="Editar" href="index.php?module=calculated_fields&action=addCalculatedFields&record={$row->getId ()}&parenttab=Settings">
				<span class="fa-stack">
					<i class="fa fa-square fa-stack-2x"></i>
					<i class="fa fa-pencil fa-stack-1x fa-inverse"></i>
				</span>
			</a>

			<a class="table-link danger delete_field" title="Borrar"  rel="{$row->getId ()}@{$row->getName ()}"  href="#">
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