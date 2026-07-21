{foreach $PIPELINE_VALUES as $pipeValue}
    <option value="{$pipeValue}" {if $VALUE eq $pipeValue} selected {/if}>{$pipeValue}</option>
{/foreach}