{strip}
<table class="listTable" border="0" cellpadding="3" cellspacing="0" width="100%">
	<tr>
		<td>{$MOD.LBL_LABEL}</td>
		<td>{$MOD.LBL_TYPE}</td>
		<td>{$MOD.LBL_SUBTYPE}</td>
		<td>{$MOD.LBL_RELATED_MODULE}</td>
		<td>{$MOD.LBL_ACTION}</td>
	</tr>
{foreach $CFENTRIES as $entry}
	<tr>
		<td>{$entry.label}</td>
		<td>{$entry.type}</td>
		<td>{$entry.subtype}</td>
		<td>{$entry.relatedmodule}</td>
		<td>
			<table width="350px">
				<tr>
					<td width="200px">
						<input type="button" class="crmButton create small" onclick="jQuery.ajax ({ldelim} type: 'POST', url: 'index.php', data: {ldelim} panelid: '{$entry.panelid}', type: '{$entry.type}', module: '{$CURRENT_MODULE}', related_module: '{$entry.relatedmodulename}', fld_module: '{$MODULE}', action: 'LayoutPanelList', 'function': 'panelProperties', Ajax: 'true' {rdelim} {rdelim}).done (function (html) {ldelim} jQuery ('#textodlgPanelGraphProperties').html (html); {rdelim}); jQuery('#dlgPanelGraphProperties').slideDown (function (){ldelim} OpenClosecortina (); {rdelim});" alt="{'LBL_EDIT_PANEL_GRAPH_PROPERTIES'|@getTranslatedString}'" title="{'LBL_EDIT_PANEL_GRAPH_PROPERTIES'|@getTranslatedString}" value="{'LBL_EDIT_PANEL_GRAPH_PROPERTIES'|@getTranslatedString}" />
					</td>
					<td width="50px">
	{if ($entry@iteration > 1)}
						<img src="themes/softed/images/arrow_up.png" border="0" style="cursor: pointer;" onclick="changePosition ('{($entry@iteration - 1)}', '{$entry@iteration}')" />
	{else}
						&nbsp;
	{/if}
					</td>
					<td width="50px">
	{if ($entry@iteration < count ($CFENTRIES))}
						<img src="themes/softed/images/arrow_down.png" border="0" style="cursor: pointer;" onclick="changePosition ('{($entry@iteration + 1)}', '{$entry@iteration}')" />
	{else}
						&nbsp;
	{/if}
					</td>
					<td width="50px">
						<img src="themes/images/delete.gif" align="absmiddle" title="Borrar..." border="0" onclick="deletePanelOrGraph ('{$entry.panelid}')">
					</td>
				</tr>
			</table>
		</td>
	</tr>
{/foreach}
</table>
{$DLG_PANEL_GRAPH_PROPERTIES}
{/strip}