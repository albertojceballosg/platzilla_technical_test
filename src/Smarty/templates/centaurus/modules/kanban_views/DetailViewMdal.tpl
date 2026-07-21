{if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
    <div class="row">
        <div class="alert alert-danger">
            <strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
        </div>
    </div>
{/if}
<div class="row">
    {foreach key=label item=value from=$CARD_DATA}
    <div class="col-md-12">
        <p class="text-left small" style="margin-bottom: 1px; padding-bottom: 1px; font-weight: bold">{$label}:</p>
        <p class="text-justify small">{$value}</p>
    </div>
    {/foreach}
</div>