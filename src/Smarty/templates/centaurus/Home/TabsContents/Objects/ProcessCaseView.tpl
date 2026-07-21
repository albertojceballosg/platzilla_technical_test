{extends file='modules/process/base/ProcessCaseViewLayout.tpl'}
{assign var="process" value=null}
{assign var="initWidth" value=-10}
{assign var="isClose" value= 0}
{block name="css"}
    <style>
        .wrap-platzilla {
            position: relative;
            margin: 5px 10px;
        }

        .line {
            width: 450px;
            height: 2px;
            background: #9B9B9B;
            /*background: #000000;*/
            position: absolute;
            left: 0;
            top: 0;
        }

        .step {
            width: 0;
            height: 5px;
            background: #34BC9D;
            position: absolute;
            left: 0;
            top: 0;
            margin: 0;
            padding: 0;
        }

        .flag {
            display: inline-block;
            width: 30px;
            height: 30px;
            line-height: 26px;
            border-radius: 50%;
            text-align: center;
            vertical-align: center;
            font-weight: bold;
            /*border: 2px solid #DA4453;*/
            border: 2px solid #9B9B9B;
            position: absolute;
            top: -16px;
            background: #fafafa;
            z-index: 10;
            cursor: pointer;
        }

        .active-step {
            background: #ffdc89 !important;
            color: #fff !important;
        }

        .closed-step {
            background: #34BC9D !important;
            color: #fff !important;
        }

        .open-step {
            background: #ffff7f !important;
            color: #6e797a !important;
        }

        .flag:hover {
            background: #fafafa;
            border: 3px solid #000000;
        }

        .isDisabled > a {
            color: currentColor;
            /*display: inline-block;  For IE11/ MS Edge bug */
            pointer-events: none;
            text-decoration: none;
        }
    </style>
{/block}

{block name="process_steps"}
    {foreach $CASES_PROCESS  as $key => $process}
        {assign var="myStep" value=$key+1}
        {if $process.related_module neq NULL}
            {assign var="myStep" value=$process.related_module|substr:0:1|upper}
        {else}
            {assign var="myStep" value="M"}
        {/if}
        {if $key eq 0}
            {assign var="myWidth" value=$initWidth}
            {assign var="mennuWidth" value=-2}
        {elseif $key eq $NUMBER_OF_STEPS}
            {assign var="myWidth" value=($LINE_WIDTH + $initWidth)}
            {assign var="mennuWidth" value=($LINE_WIDTH - 150)}
        {else}
            {assign var="myWidth" value=($myWidth + $SPACE_WIDTH)}
            {assign var="mennuWidth" value=$mennuWidth + $SPACE_WIDTH}
        {/if}
        <a href="#"
           class="dropdown-toggle"
           title="{$process.step_name}">
            <span id="step-{$process.step_id}" class="flag
                             {if ($process.case neq NULL) && ($process.case.step_exec_time neq '0')}
                                    closed-step
                                  {elseif ($process.is_active) && ($process.case['crm_id'] eq $ID)}active-step
                                    {elseif ($process.case neq NULL) && ($process.case.step_exec_time eq '0')}open-step{/if}"
                                      style="left: {$myWidth}px;">
                                    {$myStep}</span>
        </a>
        <ul id="flag-{$idProcessDetailView}-{$key}" class="dropdown-menu"
            style="left: {$mennuWidth}px;margin-top: 12px">
            {if !$IS_FINISH_PROCESS && (($process.is_active && $process.case['due_step_date'] eq NULL) ||
            ($process.step_type eq 'MANUAL' && $process.case['due_step_date'] eq NULL) ||
            ($process.case['due_step_date'] eq NULL))}
            <li>
                <a href="#"
                   id="close-link-{$process.step_id}"
                   class="{if ($process.case eq NULL)}isDisabled{/if}"
                   title="{if ($process.step_type eq 'MANUAL')}Cerrar paso{else}Ejecutar acción de cierre del paso{/if}"
                   data-name="{$process.step_name}"
                   data-step-type="{$process.step_type}"
                   data-record-id="{if $process.related_module neq NULL && $process.related_module eq $MODULE}{$RECORD_ID}{/if}"
                   rel="{$process.processtfid}@{$process.step_id}@{if $process.action_on_task neq NULL}{$process.action_on_task[1]}{else}0{/if}">
                    <i class="fa fa-power-off"></i>{if ($process.step_type eq 'MANUAL')}Cerrar paso{else}Ejecutar acción de cierre del paso{/if}
                </a>
            </li>
            {/if}
            {if ($process.step_description neq NULL) || ($process.step_comments neq NULL)}
                <li><a href="#"
                       class=""
                       title="info sobre el paso"
                       rel="{if $process.step_comments neq NULL}{$process.step_comments}{else}{$process.step_description}{/if}"
                       data-name="{$process.step_name}"
                       data-step-type="{$process.step_type}"
                       data-module="{$process.related_module}">
                        <i class="fa fa-comment-o" aria-hidden="true"></i>
                        Info. sobre el paso</a></li>
            {/if}
            <li><a href="#" data-test="{$process.case['crm_id']}-{$ID}"
                   class="{if ($process.case eq NULL) && $process.related_module neq NULL}isDisabled{/if}"
                   title="Editar nota sobre lo realizado"
                   rel="{$process.processtfid}@{$process.step_id}"
                   data-name="{$process.step_name}"
                   data-module="{$process.related_module}"
                   data-step-type="{$process.step_type}"
                   data-status="{if $process.case eq NULL}NO_OPEN{/if}">
                    <i class="fa fa-pencil-square-o" aria-hidden="true"></i>Nota sobre lo realizado</a>
            </li>
            {if !$IS_FINISH_PROCESS && $process.step_type neq 'MANUAL'}
                <li role="separator" class="divider"></li>
                <li><a href="#"
                       class=""
                       title="Finalizar el proceso">
                        <i class="fa fa-lock" aria-hidden="true"></i>Finalizar proceso</a>
                </li>
            {/if}
            {if ($process.step_type neq 'MANUAL')  && ($process.case['crm_id'] neq $ID)}
                <li role="separator" class="divider"></li>
                <li>
                    <a href=""
                       class="{if ($process.case['due_step_date'] neq NULL && $process.step_view eq 'EditView')}hide{/if}"
                       title="{if $IS_FINISH_PROCESS}El proceso ha sido finalizado manualmente{else}Ver detalle, editar o crear {$process.related_module}{/if}"
                       data-fl-module="{$process.related_module}"
                       data-step-type="{$process.step_type}"
                       data-step-view="{$process.step_view}"
                       data-seq="{if $process.action_on_step[0] eq $key}{$process.case['crm_id']}{else}{$CASES_PROCESS[$process.action_on_step[0]]['case']['crm_id']}{/if}"                                                                            "
                       data-step-id="{$process.processtfid}@{$process.step_id}"

                       rel="{if $CASES_PROCESS[$process.action_on_step[0]]['case'] neq NULL}{$CASES_PROCESS[$process.action_on_step[0]]['case']['case_number']}{/if}">
                        <i class="fa fa-cog"></i>Ir al paso</a>
                </li>
            {/if}
        </ul>
        {assign var="isClose" value= 0}
    {/foreach}
{/block}
{block name="js_script"}{/block}