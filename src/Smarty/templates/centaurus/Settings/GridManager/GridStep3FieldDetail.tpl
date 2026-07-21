{strip}
<tr  class="MoveableRow"   valign="top"{if (!$VISIBLE)} style="display: none;"{/if}>
	<td>
		<input type="hidden" name="numeroBloqueCampo[]" value="{$BLOCK_NUMBER}" class="block-number" />
        <input type="hidden" name="fieldImportId[]" value="0" class="import-code" />
		<input type="text" name="nombreCampo[]" value="{$FIELD_NAME}" maxlength="16" placeholder="" title="{$MOD.LBL_AYUDA_NOMBRE_CAMPO}" class="form-control field-name hideToMe" readonly="readonly" data-toggle="tooltip" data-placement="top" />
	</td>
	<td>
		<button type="button" class="btn btn-primary btn-xs" onclick="AddGridFieldsUtils.rowUp(this)"><i class="fa fa-arrow-up" aria-hidden="true"></i></button>&nbsp;
		<button type="button" class="btn btn-danger btn-xs" onclick="AddGridFieldsUtils.rowDown(this)"><i class="fa fa-arrow-down" aria-hidden="true"></i></button>

	</td>
	<td>
		<input type="text" name="etiquetaCampo[]" value="{$FIELD_LABEL}" maxlength="30" placeholder="" class="form-control field-label" onkeyup="AddGridFieldsUtils.gridNormalizedFieldName (this);" onblur="WizardUtils.copyNormalizedFieldName (this);" />
	</td>
	<td align="left" class="crmTableRow small lineOnTop" >
		<select name="tipoCampo[]" title="" class="form-control field-type" onchange="AddGridFieldsUtils.changeGridFieldPropertiesUI (this);">
{foreach $FIELD_GRID_OPTIONS as $type}
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
	</td>
	<td align="left" class="crmTableRow small lineOnTop">
        <button id="btn-delete"  type="button" class="btn btn-danger btn-xs" onclick="AddGridFieldsUtils.deleteRowOfGrid (this); return false;"><i class="fa fa-minus" aria-hidden="true"></i></i></button>
	</td>
</tr>
{/strip}