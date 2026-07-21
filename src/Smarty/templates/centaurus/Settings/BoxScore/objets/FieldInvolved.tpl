{strip}
    {if $boxScore->getCalculatedSystemName() neq NULL}
        <div title="{$boxScore->getCalculatedSystem()->getDescription ()}" style="cursor: pointer">
            {$boxScore->getCalculatedSystem()->getName()}</div>
        <div style="font-size: small;">- Cálculo del sistema</div>
    {elseif $boxScore->getCalculatedName() neq NULL}
        <div title="{$boxScore->getCalculatedField()->getLabel()}" style="cursor: pointer">
            {$boxScore->getCalculatedField()->getLabel()}</div>
        <span style="font-size: small">- Campo con cálculo</span>
    {else}
        Manual
    {/if}
{/strip}