{strip}
<select name="column{$ROW}" id="column{$ROW}" onChange="checkDuplicate ();" class="small" title="">
	<option value="">{$MOD.LBL_NONE}</option>
{foreach $COLUMN as $label => $filteroption}
	<optgroup label="{$label}" class="select" style="border: none;">
	{foreach $filteroption as $text}
		{assign var=option_values value=$text.text}
		<option {$text.selected} value={$text.value}>
		{if ($MOD.$option_values != '')}
			{if ($DATATYPE[0].$option_values == 'M')}
				{$MOD.$option_values} {$APP.LBL_REQUIRED_SYMBOL}
			{else}
				{$MOD.$option_values}
			{/if}
		{elseif ($APP.$option_values != '')}
			{if ($DATATYPE[0].$option_values == 'M')}
				{$APP.$option_values} {$APP.LBL_REQUIRED_SYMBOL}
			{else}
				{$APP.$option_values}
			{/if}
		{else}
			{if ($DATATYPE[0].$option_values == 'M')}
				{$option_values} {$APP.LBL_REQUIRED_SYMBOL}
			{else}
				{$option_values}
			{/if}
		{/if}
		</option>
	{/foreach}
	</optgroup>
{/foreach}
	{$COLUMN}
</select>
{/strip}