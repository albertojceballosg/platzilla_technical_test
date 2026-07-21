{strip}
<tr valign="top"{if (!$VISIBLE)} style="display: none;"{/if}>
	<td>
		<input type="hidden" name="numeroBloqueCampo[]" value="{$BLOCK_NUMBER}" class="block-number" />
		<input type="text" name="nombreCampo[]" value="{$FIELD_NAME}" maxlength="16" placeholder="" title="{$MOD.LBL_AYUDA_NOMBRE_CAMPO}" class="form-control field-name hideToMe" readonly="readonly" data-toggle="tooltip" data-placement="top" />
	</td>
	<td>
		<input type="text" name="etiquetaCampo[]" value="{$FIELD_LABEL}" maxlength="30" placeholder="" class="form-control field-label" onkeyup="WizardUtils.copyNormalizedFieldName (this);" onblur="WizardUtils.copyNormalizedFieldName (this);" />
	</td>
	<td align="left" class="crmTableRow small lineOnTop" >
		<select name="tipoCampo[]" title="" class="form-control field-type" onchange="WizardUtils.changeFieldPropertiesUI (this);">
{foreach $FIELD_TYPE_OPTIONS as $type}
	{if ($VISIBLE) && ($type.value == 4)}{continue}{/if}
			<option value="{$type.value}"{if ($type.value == $FIELD_TYPE)} selected="selected"{/if}>{$type.text}</option>
{/foreach}
		</select>
	</td>
	<td class="field-main-properties">
		<input type="text" name="tamanoCampo[]" value="{$FIELD_LENGTH}" maxlength="20" placeholder="{$MOD.LBL_LONGITUD}" class="form-control field-length"{if (!in_array ($FIELD_TYPE, array (1, 7, 9, 71)))} style="display: none;"{/if} />
		<textarea name="valoresCampo[]" rows="10" placeholder="{$MOD.LBL_VALORES}" class="form-control field-values"{if (!in_array ($FIELD_TYPE, array (15, 33)))} style="display: none;"{/if}>{if (!is_array ($FIELD_VALUE))}{$FIELD_VALUE}{/if}</textarea>
		<select name="moduloCampo[]" title="" class="form-control field-modules search-list" {if (!in_array ($FIELD_TYPE, array (10, 404)))} style="display: none;"{/if}>
			<option value="-">{$MOD.LBL_SELECCIONAR}</option>
{foreach $MODULE_OPTIONS as $module}
			<option value="{$module.value}"{if ($module.value == $FIELD_MODULE)} selected="selected"{/if}>{$module.text}</option>
{/foreach}
		</select>
		<table class="field-progress-bar"{if (!in_array ($FIELD_TYPE, array (108)))} style="display: none;"{/if}>
			<tr>
				<td nowrap="nowrap" align="right">{$MOD.LBL_PROGRESS_BAR_MIN}</td>
				<td align="left">
					<input type="text" name="campoBarra[min][]" value="{if (isset ($FIELD_VALUE.min))}{$FIELD_VALUE.min}{/if}" placeholder="" class="form-control field-min" />
				</td>
			</tr>
			<tr>
				<td nowrap="nowrap" align="right">{$MOD.LBL_PROGRESS_BAR_MAX}</td>
				<td align="left">
					<input type="text" name="campoBarra[max][]" value="{if (isset ($FIELD_VALUE.max))}{$FIELD_VALUE.max}{/if}" placeholder="" class="form-control field-max" />
				</td>
			</tr>
			<tr>
				<td nowrap="nowrap" align="right">{$MOD.LBL_PROGRESS_BAR_INI}</td>
				<td align="left">
					<input type="text" name="campoBarra[ini][]" value="{if (isset ($FIELD_VALUE.ini))}{$FIELD_VALUE.ini}{/if}" placeholder="" class="form-control field-ini" />
				</td>
			</tr>
			<tr>
				<td nowrap="nowrap" align="right">{$MOD.LBL_PROGRESS_BAR_ORD}</td>
				<td align="left">
					<select name="campoBarra[ord][]" title="" class="form-control field-sort">
						<option value="desc"{if (isset ($FIELD_VALUE.ord)) && ($FIELD_VALUE.ord == 'desc')} selected="selected"{/if}>DESC</option>
						<option value="asc"{if (isset ($FIELD_VALUE.ord)) && ($FIELD_VALUE.ord == 'asc')} selected="selected"{/if}>ASC</option>
					</select>
				</td>
			</tr>
		</table>
		<input type="text" name="prefijoCampo[]" value="{$FIELD_PREFIX}" maxlength="20" placeholder="{$MOD.LBL_PREFIJO}" class="form-control field-prefix"{if (!in_array ($FIELD_TYPE, array (4)))} style="display: none;"{/if} />
		<input type="text" name="precisionCampo[]" value="{$FIELD_PRECISION}" maxlength="20" placeholder="{$MOD.LBL_PRECISION}" class="form-control field-precision"{if (!in_array ($FIELD_TYPE, array (7, 9, 71)))} style="display: none;"{/if} />
		<input type="text" name="secuenciaCampo[]" value="{$FIELD_SEQUENCE}" maxlength="20" placeholder="{$MOD.LBL_SECUENCIA}" class="form-control field-sequence"{if (!in_array ($FIELD_TYPE, array (4)))} style="display: none;"{/if} />
		<select name="globalpicklists[]" class="form-control global-picklist" title="" onchange="WizardUtils.setFieldName (this);"{if (!in_array ($FIELD_TYPE, array (16)))} style="display: none;"{/if}>
			<option value=""></option>
{if (!empty ($AVAILABLE_GLOBAL_PICKLISTS))}
	{foreach $AVAILABLE_GLOBAL_PICKLISTS as $picklist}
			<option value="{$picklist->getName ()}"{if ($GLOBAL_PICKLIST == $picklist->getName ())} selected="selected"{/if}>{$picklist->getLabel ()}</option>
	{/foreach}
{/if}
		</select>
	</td>
	<td align="left" class="crmTableRow small lineOnTop">
		<input id="btn-delete" width="16" type="image" height="16" title="Delete" src="themes/images/remove.png" onclick="WizardUtils.deleteField (this); return false;" />
	</td>
</tr>
{/strip}