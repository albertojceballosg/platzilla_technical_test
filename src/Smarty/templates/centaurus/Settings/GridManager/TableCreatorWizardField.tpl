{strip}
<tr id="field-0-__FIELD_ID__" class="field" data-id="__FIELD_ID__">
	<td>
		<input type="text" name="moduledata[blocks][0][fields][__FIELD_ID__][name]" class="form-control field-name" maxlength="30" placeholder="Nombre" onkeyup="TableFieldUtils.normalizeFieldContents (this);" />
	</td>
	<td>
		<input type="text" name="moduledata[blocks][0][fields][__FIELD_ID__][label]" class="form-control field-label" value="{$FIELD_LABEL}" maxlength="255" placeholder="Etiqueta" />
	</td>
	<td>
		<select name="moduledata[blocks][0][fields][__FIELD_ID__][type]" class="form-control field-type" title="Tipo" onchange="TableFieldUtils.setFieldType (this);">
{if (!empty ($AVAILABLE_FIELD_TYPES))}
			<optgroup label="Texto">
	{foreach $AVAILABLE_FIELD_TYPES.text as $type}
				<option value="{$type.value}">{$type.text}</option>
	{/foreach}
			</optgroup>
			<optgroup label="Numéricos">
	{foreach $AVAILABLE_FIELD_TYPES.number as $type}
				<option value="{$type.value}">{$type.text}</option>
	{/foreach}
			</optgroup>
			<optgroup label="Fecha">
	{foreach $AVAILABLE_FIELD_TYPES.date as $type}
				<option value="{$type.value}">{$type.text}</option>
	{/foreach}
			</optgroup>
			<optgroup label="Selección">
	{foreach $AVAILABLE_FIELD_TYPES.selection as $type}
        {if in_array($type.value, array(33))}
            {continue}
        {/if}
				<option value="{$type.value}">{$type.text}</option>
	{/foreach}
			</optgroup>
	{*
			<optgroup label="Medios">
	{foreach $AVAILABLE_FIELD_TYPES.media as $type}
				<option value="{$type.value}">{$type.text}</option>
	{/foreach}
			</optgroup>
			*}
			<optgroup label="Avanzados">
	{foreach $AVAILABLE_FIELD_TYPES.advanced as $type}
        {if in_array($type.value, array(8192,2204,2206))}
            {continue}
        {/if}
				<option value="{$type.value}">{$type.text}</option>
	{/foreach}
			</optgroup>
{/if}
		</select>
	</td>
	<td class="field-properties">
		<input type="text" name="moduledata[blocks][0][fields][__FIELD_ID__][length]" class="form-control field-length" maxlength="5" placeholder="Longitud" />
		<input type="text" name="moduledata[blocks][0][fields][__FIELD_ID__][precision]" class="form-control field-precision" maxlength="5" placeholder="Precisión" style="display: none;" />
		<textarea name="moduledata[blocks][0][fields][__FIELD_ID__][picklistvalues]" class="form-control field-picklist-values" rows="3" placeholder="Valores" style="display: none; resize: "></textarea>
		<select name="moduledata[blocks][0][fields][__FIELD_ID__][referencedmodulename]" class="form-control field-referenced-module-name" title="Módulo" style="display: none;">
			<option value="">Selecciona el módulo</option>
{if (!empty ($AVAILABLE_ENTITY_TYPE_MODULES))}
	{foreach $AVAILABLE_ENTITY_TYPE_MODULES as $module}
			<option value="{$module->getName ()}">{$module->getLabel ()}</option>
	{/foreach}
{/if}
		</select>
		<select name="moduledata[blocks][0][fields][__FIELD_ID__][globalpicklist]" class="form-control field-global-picklist" title="Campo especial" style="display: none;" onchange="TableFieldUtils.setGlobalPicklistFieldName (this);">
			<option value="">Selecciona el campo</option>
{if (!empty ($AVAILABLE_GLOBAL_PICKLISTS))}
	{foreach $AVAILABLE_GLOBAL_PICKLISTS as $picklist}
			<option value="{$picklist->getName ()}"{if ($GLOBAL_PICKLIST == $picklist->getName ())} selected="selected"{/if}>{$picklist->getLabel ()}</option>
	{/foreach}
{/if}
		</select>
	</td>
	<td class="text-center">
		<button type="button" class="btn btn-danger" onclick="TableFieldUtils.deleteField (this);"><i class="fa fa-trash-o"></i></button>
	</td>
</tr>
{/strip}