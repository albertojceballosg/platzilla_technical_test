<table class="table table-striped">
	<tr>
		<td align="left">
{if ($LIST_ENTRIES != '')}
			{$RECORD_COUNTS}
{/if}
		</td>
		{$NAVIGATION}
	</tr>
</table>
<table width="90%" class="table table-hover table-striped">
	<thead>
	<tr>
{foreach $LIST_HEADER as $header}
		<th>{$header}</th>
{/foreach}
	</tr>
	</thead>
	<tbody>
{foreach $LIST_ENTRIES as $entity}
	<tr>
	{foreach $entity as $data}
		<td>{$data}</td>
	{/foreach}
	</tr>
{foreachelse}
	{assign var="colspan" value=count ($LIST_HEADER)}
	<tr>
		<td colspan="{$colspan}" height="300px" align="center"><span style="font-size: 6px; font-weight: bold;">{$MOD.LBL_NO_DATA}</span></td>
	</tr>
{/foreach}
	</tbody>
</table>