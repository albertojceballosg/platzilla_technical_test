{strip}
<table class="table table-striped table-hover">
	<thead>
		<tr>
			<th>{$MOD.LBL_CALCULATED_FIELDS_MODULE}</th>
			<th>{$MOD.LBL_CALCULATED_FIELDS_TABLA}</th>
			<th>{$MOD.LBL_CALCULATED_FIELDS_FIELD}</th>
			<th>&nbsp;</th>
		</tr>
	</thead>
	<tbody>
	{if $GRID_WITH_CALCULATED_FIELDS}
		{foreach $GRID_WITH_CALCULATED_FIELDS as $row}
			<tr id="field_{$row.subfieldsid}">
				<td>{$row.tablabel}</td>
				<td>{$row.fieldlabel}</td>
				<td>{$row.label}</td>
				<td>
					<a class="md-trigger table-link" title="Editar" href="index.php?module=calculated_fields&action=addGridCalculatedField&record={$row.fieldid}&subRecord={$row.subfieldsid}&parenttab=Settings">
						<span class="fa-stack">
							<i class="fa fa-square fa-stack-2x"></i>
							<i class="fa fa-pencil fa-stack-1x fa-inverse"></i>
						</span>
					</a>

				</td>
			</tr>
		{/foreach}
    {/if}
	</tbody>
</table>
{/strip}