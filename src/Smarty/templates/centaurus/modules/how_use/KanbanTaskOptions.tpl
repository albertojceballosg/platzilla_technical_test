<optgroup label="Kanban tareas">
    {if $KANBAN_TASK_LIST neq NULL}
        {foreach $KANBAN_TASK_LIST as $kanban}
            {if $kanban->getStatus() eq 1}{continue}{/if}
            <option value="{$kanban->getId()}">{if $kanban->getName() eq 'All'}Filtro estándar {else}{$kanban->getName()}{/if}</option>
        {/foreach}
    {else}
        <option value="">No hay vistas disponibles</option>
    {/if}
</optgroup>