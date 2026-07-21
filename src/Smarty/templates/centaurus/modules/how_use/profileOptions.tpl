{if (!empty ($PROFILES_USE))}
    <option value="" selected>Seleccionar</option>
    {foreach $PROFILES_USE as $profile}
        <option value="{$profile['profileId']}" data-code="{$profile['code']}">{$profile['profileName']}</option>
    {/foreach}
{else}
    <option value="">No se encontro un perfile adecuado</option>
{/if}