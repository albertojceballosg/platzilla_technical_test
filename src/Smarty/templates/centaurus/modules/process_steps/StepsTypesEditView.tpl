{extends file='modules/process_steps/StepTypeView.tpl'}
{assign var='stepType' value=$STEPS}
{block name="js"}
    <script type="text/javascript" src="modules/process_steps/process_steps.js"></script>
{/block}
{block name="css"}
    <link rel="stylesheet" type="text/css" href="modules/grid_view/grid-view.css"/>
{/block}

{block name="step_type_body"}
    {if $VIEW neq NULL}
        {if ($stepType neq NULL) && ($stepType->stepType eq 'MANUAL')}
            <tr>
                <td style="vertical-align: top">
                    <div class="input-group" style="width: 100%;;vertical-align: top!important;">
                        <span id="step_type-{$idStepType}" class="form-control text-left"
                              style="width: 100%;vertical-align: middle;height: 20px;line-height: 1.35em !important;padding-top: 2px;">
                            {if $stepType->stepType neq NULL}{$STEPS_TYPE[$stepType->stepType]}{/if}
                        </span>
                    </div>
                </td>
                <td class="step-manual {if (($stepType eq NULL)) || ($stepType neq NULL && $stepType->stepType neq 'MANUAL')}hidden{/if}">
                    <div class="form-group field-container" id="td_conditions_to_carry_out_step">
                        <textarea id="step_comments-{$idStepType}" name="step_comments"
                                  readonly
                                  class="form-control" tabindex=""
                                  rows="4">{if $stepType->stepComments neq NULL}{$stepType->stepComments}{/if}</textarea>
                    </div>
                </td>
            </tr>
        {else}
            <tr>
                <td style="vertical-align: top">
                    <div class="input-group" style="width: 100%;;vertical-align: top!important;">
                        <span id="step_type-{$idStepType}" class="form-control text-left"
                              style="width: 100%;vertical-align: middle;height: 20px;line-height: 1.35em !important;padding-top: 2px;">
                            {if $stepType->stepType neq NULL}{$STEPS_TYPE[$stepType->stepType]}{/if}
                        </span>
                    </div>
                </td>
                <td class="step-no-manual{if ($stepType neq NULL) && ($stepType->stepType eq 'MANUAL')}hidden{/if}" style="vertical-align: top">
                    <div class="input-group" style="width: 100%;;vertical-align: top!important;">
                        <span id="step_module-{$idStepType}" class="form-control text-left"
                              style="width: 100%;vertical-align: middle;height: 20px;line-height: 1.35em !important;padding-top: 2px;">
                            {if $stepType->stepModule neq NULL}{$stepType->stepModule|module_label: $ADB}{/if}
                        </span>
                    </div>
                </td>
                <td class="step-no-manual{if ($stepType neq NULL) && ($stepType->stepType eq 'MANUAL')}hidden{/if}" style="vertical-align: top">
                    <div class="input-group" style="width: 100%;;vertical-align: top!important;">
                        <span id="step_module-{$idStepType}" class="form-control text-left"
                              style="width: 100%;vertical-align: middle;height: 20px;line-height: 1.35em !important;padding-top: 2px;">
                            {if $stepType->stepView neq NULL}{$VIEW_END[$stepType->stepView]}{/if}
                        </span>
                    </div>
                </td>
                <td class="step-no-manual {if ($stepType neq NULL) && ($stepType->stepType eq 'MANUAL')}hidden{/if}" style="vertical-align: top">
                    <div class="input-group step-no-automatic {if ($stepType neq NULL) && ($stepType->stepType neq 'AUTOMATIC')}hidden{/if}"
                         style="width: 100%;">
                        {if $BACKGROUND_TASK neq NULL}
                            <textarea id="step_task-{$idStepType}"
                                      readonly
                                      class="form-control" tabindex=""
                                      rows="2">{foreach $BACKGROUND_TASK as $bgTask}{if (($stepType->stepTask eq $bgTask->getId()))}{$bgTask->getName()}{/if}{/foreach}</textarea>
                        {else}
                            <span id="step_module-{$idStepType}" class="form-control text-center"
                                  style=";overflow-x: hidden;width: 100%;vertical-align: middle;height: 20px;line-height: 1.35em !important;padding-top: 10px;">
                                No hay tareas automatizadas disponibles!
                                </span>
                        {/if}
                    </div>
                </td>
            </tr>
        {/if}
    {else}
        <tr>
            <td style="vertical-align: top">
                <div class="input-group" style="width: 100%;;vertical-align: top!important;">
                    <select id="step_type-{$idStepType}" name="type" class="form-control for-filter" tabindex=""
                            onchange="StepTypeUtls.stepTypeSelected(this, '{$idStepType}')">
                        <option value="" {if $stepType eq NULL} selected="selected" {/if}>Tipo de paso</option>
                        {foreach $STEPS_TYPE as $type => $name}
                            <option value="{$type}"
                                    {if (($stepType neq NULL) && ($stepType->stepType == $type))}selected{/if}>
                                {$name}
                            </option>
                        {/foreach}
                    </select>
                </div>
            </td>
            <td class="step-no-manual {if ($stepType neq NULL) && ($stepType->stepType eq 'MANUAL')}hidden{/if}" style="vertical-align: top">
                <div class="input-group" style="width: 100%">
                    <select id="step_module-{$idStepType}" name="step_type" class="form-control for-filter"
                            tabindex=""
                            onchange="StepTypeUtls.moduleSelected(this, '{$idStepType}')">
                        <option value="" {if $stepType eq NULL} selected="selected" {/if}>Modulo</option>
                        {foreach $AVAILABLE_MODULES as $modle}
                            <option value="{$modle->getName()}"
                            {$type}" {if (($stepType neq NULL) && ($stepType->stepModule eq $modle->getName()))}selected{/if}>
                            {$modle->getLabel()}
                            </option>
                        {/foreach}
                    </select>
                </div>
            </td>
            <td class="step-no-manual {if ($stepType neq NULL) && ($stepType->stepType eq 'MANUAL')}hidden{/if}" style="vertical-align: top">
                <div class="input-group" style="width: 100%;;vertical-align: top!important;">
                    <select id="step_view-{$idStepType}" name="step_view" class="form-control for-filter" tabindex="">
                        {foreach $VIEW_END as $key => $value}
                            <option value="{$key}" {if (($key neq NULL) && ($stepType->stepView == $key))}selected{/if}>
                                {$value}
                            </option>
                        {/foreach}
                    </select>
                </div>
            </td>
            <td class="step-no-manual {if ($stepType neq NULL) && ($stepType->stepType eq 'MANUAL')}hidden{/if}">
                <div class="input-group step-no-automatic {if ($stepType neq NULL) && ($stepType->stepType neq 'AUTOMATIC')}hidden{/if}"
                     style="width: 100%;">
                    {if $BACKGROUND_TASK neq NULL}
                        <select id="step_task-{$idStepType}" name="step_task" class="form-control for-filter"
                                tabindex=""
                                {*onchange="StepTypeUtls.stepTypeSelected(this, '{$idStepType}')" *}>
                            <option value="" {if $stepType eq NULL} selected="selected" {/if}>Tarea automatizada
                            </option>
                            {foreach $BACKGROUND_TASK as $bgTask}
                                <option value="{$bgTask->getId()}"
                                        data-module="{$bgTask->getModuleName()}"
                                        {if ($stepType neq NULL) && ($stepType->stepType eq 'AUTOMATIC')}
                                    {if ($stepType->stepModule neq $bgTask->getModuleName())}style="display: none;"{/if}
                                    {if (($stepType->stepTask eq $bgTask->getId()))}selected{/if}
                                        {/if}>
                                    {$bgTask->getName()}
                                </option>
                            {/foreach}
                        </select>
                    {else}
                        <span id="step_module-{$idStepType}" class="form-control text-center"
                              style=";overflow-x: hidden;width: 100%;vertical-align: middle;height: 40px;line-height: 1.35em !important;padding-top: 10px;">
                    No hay tareas automatizadas disponibles!
                    </span>
                    {/if}
                </div>
            </td>
            <td class="step-manual {if (($stepType eq NULL)) || ($stepType neq NULL && $stepType->stepType neq 'MANUAL')}hidden{/if}">
                <div class="form-group field-container" id="td_conditions_to_carry_out_step">
                <textarea id="step_comments-{$idStepType}" name="step_comments" class="form-control" tabindex=""
                          rows="4">{if ($stepType neq NULL) && ($stepType->stepComments neq NULL)}{$stepType->stepComments}{/if}</textarea>
                </div>
            </td>
        </tr>
    {/if}
{/block}