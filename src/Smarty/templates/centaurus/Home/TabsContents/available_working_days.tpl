<option value="">Seleccionar tipo de jornada {if !$ONLY_ENNABLED}a modificar{/if} </option>
{if $AVAILABLE_WORKING_DAYS neq NULL}
    {foreach $AVAILABLE_WORKING_DAYS as $dayWorking}
        {if $dayWorking->getWorkingDayStatus() eq 'DISABLED' && $ONLY_ENNABLED}{continue}{/if}
        <option value="{$dayWorking->getId ()}"
                {if $USER_WORKING_DAY neq NULL}
                    {if $USER_WORKING_DAY->getId eq $ID}
                        selected="selected"
                    {/if}
                {/if}
        >{$dayWorking->getWorkingDayName()}</option>
    {/foreach}
{/if}