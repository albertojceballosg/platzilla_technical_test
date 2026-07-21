{strip}
<label for="platdb">{$LBL_PLATAFORMAS}</label>
<select id="platdb" name="platdb" class="small" onchange="viewname.value=''; actualizaListaFiltros ();">
	<option value="">{$ORGANIZATION_NAME}</option>
{if (isset ($OPTIONS)) && (is_array ($OPTIONS)) && (count ($OPTIONS) > 0)}
	{foreach $OPTIONS as $option}
	<option value="{$option.value}"{if ($SELECTED_VALUE) && ($SELECTED_VALUE == $option.value)} selected="selected"{/if}>{$option.text}</option>
	{/foreach}
{/if}
</select>
{/strip}