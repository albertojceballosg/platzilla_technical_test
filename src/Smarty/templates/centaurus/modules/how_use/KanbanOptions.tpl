<optgroup label="Kanban">
    {if $KANBAN_LIST neq NULL}
        {foreach $KANBAN_LIST as $kanban}
            <option value="{$kanban.kanbanviewid}">{$kanban.label}</option>
        {/foreach}
    {else}
        <option value="">No hay vistas Kanban disponibles</option>
    {/if}
</optgroup>