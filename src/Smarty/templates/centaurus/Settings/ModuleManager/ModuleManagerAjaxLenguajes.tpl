{foreach key=langprefix item=langinfo from=$TOGGLE_LANGINFO}
					{if $langprefix neq 'en_us'}

					{assign var="totalCustomModules" value=$totalCustomModules+1}
					<tr>
						<td class="cellText small"><i class="fa fa-keyboard-o"></i></td>
						<td class="cellLabel small">{$langinfo.label}</td>
						<td class="cellText small" width="15px" align=center>
							<a href="index.php?module=Settings&action=ModuleManager&module_update=Step1&src_module={$langprefix}&parenttab=Settings"><i class="fa fa-external-link" alt="{$MOD.LBL_UPGRADE} {$langinfo.label}" title="{$MOD.LBL_UPGRADE} {$langinfo.label}"></i></a>
						</td>
						<td class="cellText small" width="15px" align=center>
						{if $langinfo.active eq 1}
							<a href="javascript:void(0);" onclick="vtlib_toggleModule('{$langprefix}', 'module_disable', 'language');"><i class="fa fa-square-o" alt="{$MOD.LBL_DISABLE} Language {$langinfo.label}" title="{$MOD.LBL_DISABLE} Language {$langinfo.label}"></i></a>
						{else}
							<a href="javascript:void(0);" onclick="vtlib_toggleModule('{$langprefix}', 'module_enable', 'language');"><i class="fa fa-square" alt="{$MOD.LBL_ENABLE} Language {$langinfo.label}" title="{$MOD.LBL_ENABLE} Language {$langinfo.label}"></i></a>
						{/if}
						</td>
						<td class="cellText small" width="15px" align=center colspan=2>&nbsp;</td>
						
					</tr>
					{/if}
				{/foreach}