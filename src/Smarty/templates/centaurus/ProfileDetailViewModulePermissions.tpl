{strip}
{foreach $TAB_PRIV as $tabid => $elements}
	<div class="panel panel-default">
		<div class="panel-heading">
			<h4 class="panel-title">
				<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#{if ($tabid == 0)}collapseOne{else}{$tabid}{/if}">
					{$TAB_PRIV[$tabid][2]}
				</a>
			</h4>
		</div>
		<div id="{if ($tabid == 0)}collapseOne{else}{$tabid}{/if}" class="panel-collapse collapse{if ($tabid == 0)} in{/if}">
			<div class="panel-body">
				<table class="table table-bordered">
					<thead>
					<tr>
						<th>Flange</th>
						<th>{$CMOD.LBL_TAB_MESG_OPTION}</th>
						<th>{$CMOD.LBL_CREATE_EDIT}</th>
						<th>{$CMOD.LBL_VIEW}</th>
						<th>{$CMOD.LBL_DELETE}</th>
						<th>{$CMOD.LBL_FIELDS_AND_TOOLS_SETTINGS}</th>
					</tr>
					</thead>
					<tbody>
	{if ($TAB_PRIV[$tabid][2] != '') && ($TAB_PRIV[$tabid][2] != 'Primer')}
		{assign var=modulename value=$TAB_PRIV[$tabid][0]}
		{assign var=tabid2 value=$TAB_PRIV[$tabid][3]}
					<tr>
						<td>{$TAB_PRIV[$tabid][2]} </td>
						<td>{$TAB_PRIV[$tabid][1]} {$TAB_PRIV[$tabid][0]} </td>
						<td>{$STANDARD_PRIV[$tabid2][1]}</td>
						<td>{$STANDARD_PRIV[$tabid2][3]}</td>
						<td>{$STANDARD_PRIV[$tabid2][2]}</td>
						<td>
		{if ($FIELD_PRIVILEGES[$tabid2] != NULL)}
							<img src="{'showDown.gif'|@vtiger_imageurl:$THEME}" alt="{$APP.LBL_EXPAND_COLLAPSE}" title="{$APP.LBL_EXPAND_COLLAPSE}" onclick="fnToggleVIew('{$modulename}_view')" border="0" height="16" width="40">
		{/if}
						</td>
					</tr>
					<tr class="hideTable table" id="{$modulename}_view">
						<td colspan="6">
							<table class="table" border="0" cellpadding="0" cellspacing="0" width="100%" style="background: #DAE4EB; border-radius: 5px">
								<tbody>
		{if ($FIELD_PRIVILEGES[$tabid2] != '')}
								<tr>
			{if ($modulename == 'Calendar')}
									<td class="mini-products" colspan="6" valign="top">
										<b>{$CMOD.LBL_FIELDS_TO_BE_SHOWN} ({$APP.Tasks})</b>
									</td>
			{else}
									<td class="mini-products" colspan="6" valign="top">
										<b>{$CMOD.LBL_FIELDS_TO_BE_SHOWN}</b>
									</td>
			{/if}
								</tr>
		{/if}
		{foreach $FIELD_PRIVILEGES[$tabid2] as $row_values}
								<tr>
			{foreach $row_values as $element}
									<td valign="top">{$element.1}</td>
									<td>{$element.0}</td>
			{/foreach}
								</tr>
		{/foreach}
		{if ($modulename == 'Calendar')}
								<tr>
									<td class="mini-products" colspan="6" valign="top">{$CMOD.LBL_FIELDS_TO_BE_SHOWN} ({$APP.Events})</td>
								</tr>
			{foreach $FIELD_PRIVILEGES[16] as $row_values}
								<tr>
				{foreach $row_values as $element}
									<td valign="top">{$element.1}</td>
									<td>{$element.0}</td>
				{/foreach}
								</tr>
			{/foreach}
		{/if}
		{if ($UTILITIES_PRIV[$tabid2] != '')}
								<tr>
									<td colspan="6" class="mini-products" valign="top">{$CMOD.LBL_TOOLS_TO_BE_SHOWN} </td>
								</tr>
		{/if}
		{foreach $UTILITIES_PRIV[$tabid2] as $util_value}
								<tr>
			{foreach $util_value as $util_elements}
									<td valign="top">{$util_elements.1}</td>
									<td>{$APP[$util_elements.0]}</td>
			{/foreach}
								</tr>
		{/foreach}
								</tbody>
							</table>
						</td>
					</tr>
	{/if}
	{if ($TAB_PRIV[$tabid][2] != $TAB_PRIV[$tabid+1][2])}
					</tbody>
				</table>
			</div>
		</div>
	</div>
	{/if}
{/foreach}
{/strip}