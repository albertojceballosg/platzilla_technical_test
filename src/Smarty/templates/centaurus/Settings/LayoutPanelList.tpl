{strip}
<script type="text/javascript">
{literal}
	function changePosition (pos, prevpos) {
		jQuery.ajax ({
			type: 'POST',
			url: 'index.php',
			data: {
				panelid: {/literal}'{$entries.panelid}'{literal},
				module: 'Settings',
				fld_module: {/literal}'{$MODULE}'{literal},
				action: 'LayoutPanelList',
				'function': 'changePosition',
				Ajax: 'true',
				pos: pos,
				prevpos: prevpos
			}
		}).done (function (html) {
			jQuery ('#cfList').html (html);
		});
	}

	function changeColumnPosition (panelid, pos, prevpos) {
		jQuery.ajax ({
			type: 'POST',
			url: 'index.php',
			data: {
				panelid: panelid,
				module: 'Settings',
				fld_module: {/literal}'{$MODULE}'{literal},
				action: 'LayoutPanelList',
				'function': 'changeColumnPosition',
				Ajax: 'true',
				pos: pos,
				prevpos: prevpos
			}
		}).done (function (html) {
			jQuery ('#textodlgPanelGraphProperties').html (html);
		});
	}

	function deletePanelOrGraph (pos) {
		jQuery.ajax ({
			type: 'POST',
			url: 'index.php',
			data: {
				panelid: {/literal}'{$entries.panelid}'{literal},
				module: 'Settings',
				fld_module: {/literal}'{$MODULE}'{literal},
				action: 'LayoutPanelList',
				'function': 'deletePanelOrGraph',
				Ajax: 'true',
				pos: pos
			}
		}).done (function (html) {
			jQuery ('#cfList').html (html);
		});
	}
{/literal}
</script>
<table align="center" border="0" cellpadding="0" cellspacing="0" width="98%">
	<tr>
		<td valign="top"><img src="{'showPanelTopLeft.gif'|@vtiger_imageurl:$THEME}"></td>
		<td class="showPanelBg" style="padding: 10px;" valign="top" width="100%">
			<br />
			<table class="settingsSelUITopLine" border="0" cellpadding="5" cellspacing="0" width="100%">
				<tr>
					<td rowspan="2" valign="top" width="50">
						<img src="{'orgshar.gif'|@vtiger_imageurl:$THEME}" alt="Users" title="Users" border="0" height="48" width="48">
					</td>
					<td class="heading2" valign="bottom" width="100%">
						<b>
							<a href="index.php?module=Settings&action=ModuleManager&parenttab=Settings">{$MOD.VTLIB_LBL_MODULE_MANAGER}</a>&gt;
							<a href="index.php?module=Settings&action=LayoutBlockList&parenttab=Settings&formodule={$MODULE}">{if ($APP.$MODULE)}{$APP.$MODULE}{elseif ($MOD.$MODULE)}{$MOD.$MODULE}{else}{$MODULE}{/if}</a>&gt;
							{$MOD.LBL_LAYOUT_EDITOR}
						</b>
					</td>
				</tr>
				<tr>
					<td align="right" width="8%">
						<input
							type="button"
							class="crmButton create small"
							onclick="
								jQuery.ajax ({ldelim}
									type: 'POST',
									url: 'index.php',
									data: {ldelim}
										panelid: '{$entries.panelid}',
										module: 'Settings',
										fld_module: '{$MODULE}',
										action: 'LayoutPanelList',
										'function': 'newPanel',
										Ajax: 'true'
									{rdelim}
								{rdelim}).done (function (html) {ldelim}
									jQuery ('#texto{$ID_DLG_PANEL_GRAPH_PROPERTIES}').html (html);
								{rdelim});
								jQuery ('#{$ID_DLG_PANEL_GRAPH_PROPERTIES}').slideDown (function () {ldelim}
									OpenClosecortina ();
								{rdelim});"
							alt="{$MOD.LBL_ADD_PANEL_OR_GRAPH}"
							title="{$MOD.LBL_ADD_PANEL_OR_GRAPH}"
							value="{$MOD.LBL_ADD_PANEL_OR_GRAPH}"
						/>
						&nbsp;
						<img src="{'vtbusy.gif'|@vtiger_imageurl:$THEME}" id="vtbusy_info" style="display:none;position:absolute;top:180px;right:100px;" border="0" />
					</td>
				</tr>
			</table>
			<div id="cfList">
{include file="Settings/LayoutPanelEntries.tpl"}
			</div>
			<table border="0" cellpadding="5" cellspacing="0" width="100%">
				<tr>
					<td class="small" align="right" nowrap="nowrap"><a href="#top">{$MOD.LBL_SCROLL}</a></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
{/strip}