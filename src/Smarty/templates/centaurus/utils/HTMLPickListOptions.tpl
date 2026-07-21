{foreach $PICKLIST_VALUES->getValues() as $picklistValue}
    {if $MOD[$picklistValue->getValue ()] neq NULL}
        {assign var='label' value=$MOD[ $picklistValue->getValue () ]}
    {else}
        {assign var='label' value=$picklistValue->getValue ()}
    {/if}
    <option value="{$picklistValue->getValue ()}" {if $VALUE eq $picklistValue->getValue ()} selected {/if} >{$label}</option>
{/foreach}