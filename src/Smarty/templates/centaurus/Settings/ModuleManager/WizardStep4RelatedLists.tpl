{strip}
	<tr valign="top" class="lvtColData">
		<td class="dvtCellInfo">
			<input type="text" name="labelModulos[]" value="{$SELECTED_LABEL}" class="form-control related-label" placeholder="" />
		</td>
		<td class="dvtCellInfo">
			<select name="listaModulos[]" style="display: inline;" class="form-control related-name" title="">
				<option value="">{$MOD.LBL_SELECCIONAR}</option>
	{foreach $MODULES as $module}
				<option value="{$module.value}"{if ($module.value == $SELECTED_MODULE)} selected="selected"{/if}>{$module.text}</option>
	{/foreach}
			</select>
		</td>
		<td class="dvtCellInfo">
			<div class="action">
				<label>
					<input type="hidden" name="listaAccionAdd[]" value="{if ($SELECTED_INSERT)}{true}{else}{false}{/if}" class="related-action-add" />
					<input type="checkbox" class="related-action-add"{if ($SELECTED_INSERT)} checked="checked"{/if} onclick="WizardUtils.updateRelatedHiddenField (this);" />
					<span>{$MOD.LBL_INSERTAR}</span>
				</label>
			</div>
			<div class="action">
				<label>
					<input type="hidden" name="listaAccionSelect[]" value="{if ($SELECTED_SELECT)}{true}{else}{false}{/if}" class="related-action-select" />
					<input type="checkbox" class="related-action-select"{if ($SELECTED_SELECT)} checked="checked"{/if} onclick="WizardUtils.updateRelatedHiddenField (this);" />
					<span>{$MOD.LBL_SELECCIONAR}</span>
				</label>
			</div>
		</td>
		<td width="5%" align="left" class="dvtCellInfo">
			<input type="image" width="16" height="16" title="Delete" src="themes/images/remove.png" onclick="WizardUtils.deleteRelatedList (this); return false;" />
		</td>
	</tr>
{/strip}