{strip}
    <option value="" selected>Seleccionar Instancia</option>
    {foreach $INSTANCES as $id => $instance}
        {if $instance->getAdministrator() neq NULL}
            {if $AGENT_INSTANCES neq NULL}
                {assign var="isSelected" value=null}
                {foreach $AGENT_INSTANCES as $agentInstance}
                    {if $agentInstance->getCode() eq $instance->getCode()}
                        {assign var="isSelected" value='selected'}
                    {/if}
                {/foreach}
            {else}
                {assign var="isSelected" value=null}
            {/if}
            <option value="{$instance->getCode()};{$instance->getAdministrator()->getEmail()}">
                {$instance->getAdministrator()->getFirstName()}  {$instance->getAdministrator()->getLastName()}
            </option>
        {/if}
    {/foreach}

{/strip}