{strip}
    <tr id="tr-row-{$idRow}" data-row-id="{$idRow}" class="tabla-field-row">
        <td style="{$colStyle1}">
            <div class="input-group" style="width: 100%;">
                <span id="step_id-{$idRow}"
                      class="form-control   b-left"
                      style="overflow-x: hidden;width: 100%">
                    {if $processStep->getStepId() neq Null}
                        <a href="?module=process_steps&parenttab=&action=DetailView&record={$processStep->getStepId()}"
                           target="_blank" title="Paso: {$processStep->getStepName()}">
                        {$processStep->getSequence()}: {$processStep->getStepCode()}</a>
                    {else}
                        {$processStep->getStepCode()}
                    {/if}
                </span>
            </div>
        </td>
        <td style="{$colStyle2}">
            <div id="input-step_name-{$idRow}" class="input-group" style="width: 100%;">
                <span id="step_name-{$idRow}">
                    {if $processStep->getStepName() neq NULL}{$processStep->getStepName()}{/if}
                </span>
            </div>
        </td>
        <td style="{$colStyle3}">
            <div id="input-step_responsible_role-{$idRow}" class="input-group" style="width: 100%;">
                <span id="step_responsible_role-{$idRow}">
                    {if $processStep->getStepResponsibleRole() neq NULL}{$processStep->getStepResponsibleRole()}{/if}
                </span>
            </div>
        </td>
        <td style="{$colStyle4}">
            <div id="input-related_module-{$idRow}" class="input-group" style="width: 100%;">
               <span id="srelated_module-{$idRow}">
                   {if $processStep->getRelatedTab() neq NULL}{$processStep->getRelatedTab()|module_label: $ADB}{/if}
               </span>
            </div>
        </td>
        <td style="{$colStyle5}">
            <div id="input-action-on-{$idRow}" class="input-group" style="width: 100%;">
                <span id="action_on-{$idRow}">
                    {if $processStep->getActionOnStep() neq NULL}
                        {if $processStep->getStepType() neq 'MANUAL'}
                            {assign var="index" value=1}
                            {foreach $PROCESS_STEPS as $key => $steps}
                                {assign var="accion" value=$index|cat:'-'|cat:$steps->getStepId()}
                                {if $processStep->getActionOnStep() eq $accion}
                                    {$index}-{$steps->getStepCode()}
                                {/if}
                                {assign var="index" value=$index+1}
                            {/foreach}
                        {else}
                            {$processStep->getSequence()}-{$processStep->getStepCode()}
                        {/if}
                    {/if}
                </span>
            </div>
        </td>
        <td style="{$colStyle6}">
            <div id="input-step_state-{$idRow}" class="input-group" style="width: 100%;">
                <span id="action_on-{$idRow}">
                    {if $STEPS_TYPE[$processStep->getStepType()] neq NULL}{$STEPS_TYPE[$processStep->getStepType()]}{/if}
                </span>
            </div>
            {if ($processStep->getStepType() eq 'AUTOMATIC') && ($processStep->getActionOnTask() neq NULL)}
                <div id="input-action-on-task-{$idRow}" class="input-group border" style="width: 100%;margin-top: 2px;background-color: #f9f8f7">
                    <span id="action_on-task-{$idRow}" class="" >
                        {assign var="indexTask" value=1}
                        {foreach $PROCESS_STEPS as $key => $steps}
                            {assign var="accionTask" value=$indexTask|cat:'-'|cat:$steps->getStepId()}
                            {if $processStep->getActionOnTask() eq $accionTask}
                                        {$indexTask}-{$steps->getStepCode()}
                            {/if}
                            {assign var="indexTask" value=$indexTask+1}
                        {/foreach}
                    </span>
                </div>
            {/if}

        </td>
        <td style="{$colStyle7}">
            <div id="input-step_state-{$idRow}" class="input-group" style="width: 100%;">
                <span id="step_state-{$idRow}">
                    {if $processStep->getStepState() neq NULL}{$processStep->getStepState()}{/if}
                </span>
            </div>
        </td>
        {*<td style="{$colStyle8}">&nbsp;</td>*}
    </tr>
{/strip}