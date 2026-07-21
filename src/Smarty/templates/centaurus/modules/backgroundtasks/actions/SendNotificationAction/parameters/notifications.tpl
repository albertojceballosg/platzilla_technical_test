{if $NOTIFICATIONS neq NULL}
    {foreach $NOTIFICATIONS as $notify}
        {if $notify->getView() eq 'LIST_VIEW'} {continue}{/if}
        {assign var='optionValue' value=$notify->getName()|cat:'@'|cat:$notify->getView()}
        {assign var='optionText' value=$MOD[$notify->getView()]|cat:': '|cat:$notify->getName()}
        <option value="{$optionValue}" {if $optionValue eq $NOTIFICATION_SELECTED}
            selected
            {$NOTIFICATION_SELECTED = NULL}
        {/if} >{$optionText}</option>
    {/foreach}
{else}
    <option value="">{$MOD['NO_NOTIFICATIONS']}</option>
{/if}