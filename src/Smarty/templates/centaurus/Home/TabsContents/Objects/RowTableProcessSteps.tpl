{strip}
    <tr valign="top" id="">
        <td style="text-align: center; width: 7%">
            <i class="fa fa-circle fa-2x" aria-hidden="true"
               style="color: {$CONTROL_BANDS[$step['step_state']]}"></i>
        </td>
        <td class="step-no-manual" style="vertical-align: top">
            <a href="index.php?module=process_steps&action=DetailView&record={$step['step']['process_stepsid']}"
               target="_blank"
               title="ver detalles del paso">
                <span>{$step['step']['step_name']}</span>
            </a>
        </td>
        <td style="vertical-align: top"><span>{$STEPS_TYPE[$step['step']['step_type']]}</span></td>
        <td class="step-no-manual" style="vertical-align: top">
            {if $step['step']['step_type_module'] eq NULL}
                <span>&nbsp;-&nbsp;</span>
            {else}
                <a href="index.php?module={$step['step']['step_type_module']}&action=DetailView&record={*$step['crm_id']*}{$step['step']['step_type_module']|crmentity_id:$CASE_NUMBER:$ADB}"
                   target="_blank"
                   title="">
                    <span>{$step['step']['step_type_module']|module_label: $ADB}</span>
                </a>
            {/if}

        </td>
        <td class="step-no-manual text-left" style="vertical-align: top">
            <span>{($step['start_date']|cat:' '|cat:$step['start_time'])|date_es_format}</span>
        </td>
        <td class="step-no-manual text-left" style="vertical-align: top">
            <span>{($step['due_date']|cat:' '|cat:$step['end_time'])|date_es_format}</span>
        </td>
        <td class="step-no-manual" style="text-align: center;vertical-align: top">
            <span>{$step['step_exec_time']}</span>
        </td>
        <td class="step-no-manual" style="vertical-align:top"><span>{$step['quality_valuation']}</span></td>
        <td class="step-no-manual text-left" style=";vertical-align: top;">
            <span id="step_comments-16885" class=""
                  style="overflow-y:auto;width: 100%;vertical-align: top;max-height: 220px;line-height: 1.35em!important">
                {$step['comment']}
                <span style="display: inline-block">{$step['reason_valuation']}</span>
                {if $step['documents'] neq NULL}
                    <span style="display: inline-block">
                        <b>Documentos Adjuntos:</b>
                        <ul>
                            {foreach $step['documents'] as $doc}
                                <li>{$doc['name']}&nbsp;<span style="font-size: 0.85em">({$doc['createdtime']})</span></li>
                            {/foreach}
                        </ul>
                    </span>
                {/if}
            </span>
        </td>
    </tr>
{/strip}