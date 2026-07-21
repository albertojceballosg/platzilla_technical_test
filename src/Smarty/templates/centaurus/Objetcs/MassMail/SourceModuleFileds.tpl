{strip}
    {if $FIELDS neq NULL}
        <option value="">Seleccionar</option>
        {foreach $FIELDS as $name => $label}
            <option value="{$name}">{$label}</option>
        {/foreach}
    {else}
        <option value=""> - No hay campos -</option>
    {/if}
{/strip}