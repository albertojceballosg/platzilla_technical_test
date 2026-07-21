{strip}
    <tr id="tr-row-{$idRow}" data-row-id="{$idRow}" class="tabla-field-row">
        <td style="{$colStyle1}">
            <div class="input-group" style="width: 100%;">
                <input type="hidden" id="step_id-{$idRow}_type" name="step_id_type"
                       value="process_steps" class="small"/>
                <input type="hidden" id="step_id-{$idRow}"
                       data-row-ids="{$idProcessSteps}@{$idRow}"
                       name="app_process[step_id][]" value="{if $processStep->getStepId() neq Null}{$processStep->getStepId()}{/if}"
                       onchange="ProcessStepsUtls.relatedModule(this, '', 'process')"
                       class="for-filter module-reference"/>
                <input type="text" id="edit_step_id-{$idRow}_display"
                       name="app_process[step_code][]" value="{if $processStep->getStepCode() neq Null}{$processStep->getStepCode()}{/if}"
                       class="form-control input-readonly b-right process-step-code"
                       data-Tableid="{$idProcessSteps}"
                       readonly="readonly" placeholder=""/>
                <div class="input-group-addon" data-current-module="process"
                     data-display-field-id="edit_step_id-{$idRow}_display"
                     data-field-id="step_id-{$idRow}"
                     data-referenced-module="process_steps"
                     data-title="Código"
                     onclick="RelatedModuleModalUtils.openModal (this);">
                    <i class="fa fa-plus-circle"></i>
                </div>
                <div class="input-group-addon"
                     onClick="var fieldContainer = jQuery (this).closest ('.field-container'); fieldContainer.find ('#edit_{$actionField['fieldname']}_display').val (''); fieldContainer.find ('#{$actionField['fieldname']}').val (''); return false;">
                    <i class="fa fa-eraser"></i>
                </div>
            </div>
        </td>
        <td style="{$colStyle2}">
            <div id="input-step_name-{$idRow}" class="input-group" style="width: 100%;">
                <input type="text" id="step_name-{$idRow}"
                       readonly="readonly"
                       name="app_process[step_name][]"
                       value="{$processStep->getStepName()}"
                       class="form-control">
            </div>
        </td>
        <td style="{$colStyle3}">
            <div id="input-step_responsible_role-{$idRow}" class="input-group" style="width: 100%;">
                <input type="text" id="step_responsible_role-{$idRow}"
                       name="app_process[step_responsible_role][]"
                       value="{$processStep->getStepResponsibleRole()}"
                       class="form-control">
            </div>
        </td>
        <td style="{$colStyle4}">
            <div id="input-related_module-{$idRow}" class="input-group" style="width: 100%;">
                <input type="text" id="related_module-{$idRow}"
                       readonly="readonly"
                       name="app_process[related_module][]"
                       value="{$processStep->getRelatedTab()}"
                       class="form-control">
            </div>
        </td>
        <td style="{$colStyle5}">
            <div id="input-action-on-{$idRow}" class="input-group" style="width: 100%;">
                <select id="action_on-{$idRow}"
                        name="app_process[action_on][step][]"
                        class="form-control action-on-step">
                    {if $processStep->getStepType() neq 'MANUAL'}
                        {assign var="index" value=1}
                        {foreach $PROCESS_STEPS as $key => $steps}
                            {assign var="accion" value=$index|cat:'-'|cat:$steps->getStepId()}
                            <option value="{$accion}"
                            {if $processStep->getActionOnStep() eq $accion}selected{/if} >
                                {$index}-{$steps->getStepCode()}</option>
                            {assign var="index" value=$index+1}
                        {/foreach}
                    {else}
                        <option value="{$processStep->getActionOnStep()}">{$processStep->getSequence()}-{$processStep->getStepCode()}</option>
                    {/if}

                </select>
                <input type="hidden" id="step_type-{$idRow}"
                       name="app_process[step_type][]"
                       value="{$processStep->getStepType()}" class=""/>
            </div>
        </td>
        <td style="{$colStyle6}">
            <div id="input-step_state-{$idRow}" class="input-group" style="width: 100%;">
                <input type="text" id="step_type_view-{$idRow}"
                       readonly="readonly"
                       name=""
                       value="{$STEPS_TYPE[$processStep->getStepType()]}"
                       class="form-control">
            </div>
            <div id="input-action_task_on-{$idRow}" class="input-group {if $processStep->getStepType() neq 'AUTOMATIC'}hidden{/if}" style="width: 100%;margin-top: 2px">
                <select id="action_task_on-{$idRow}"
                        name="app_process[action_on][task][]"
                        class="form-control action-on-step">
                    {if $processStep->getStepType() eq 'AUTOMATIC'}
                        {assign var="indexTask" value=1}
                        {foreach $PROCESS_STEPS as $key => $steps}
                            {assign var="accionTask" value=$indexTask|cat:'-'|cat:$steps->getStepId()}
                            <option value="{$accionTask}" {if $processStep->getActionOnTask() eq $accionTask}selected{/if} >
                                {$indexTask}-{$steps->getStepCode()}</option>
                            {assign var="indexTask" value=$indexTask+1}
                        {/foreach}
                    {else}
                    <option value="">Paso del proceso</option>
                    {/if}
                </select>
            </div>
        </td>
        <td style="{$colStyle7}">
            <div id="input-step_state-{$idRow}" class="input-group" style="width: 100%;">
                <input type="text" id="step_state-{$idRow}"
                       readonly="readonly"
                       name="app_process[step_state][]"
                       value="{$processStep->getStepState()}"
                       class="form-control">
            </div>
        </td>
        <td style="{$colStyle8}">
            <button type="button" class="btn btn-primary btn-xs"
                    onclick="ProcessStepsUtls.moveRowUp (this, 'tr-row-{$idRow}')"><i class="fa fa-arrow-up" aria-hidden="true"></i>
            </button>&nbsp;
            <button type="button" class="btn btn-danger btn-xs"
                    onclick="ProcessStepsUtls.moveRowDown (this, 'tr-row-{$idRow}')"><i class="fa fa-arrow-down" aria-hidden="true"></i>
            </button>&nbsp;
            <button type="button" class="btn btn-danger btn-icon delete-value-button"
                    onclick="ProcessStepsUtls.delRowToTable (this, 'tr-row-{$idRow}', '{$idProcessSteps}');"><i class="fa fa-trash-o"></i></button>
        </td>
    </tr>
{/strip}