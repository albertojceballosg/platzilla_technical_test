{math equation= rand() assign= "idProcessCase"}
<div class="row">
    {*$CASE_DETAILS|var_dump*}
    <div class="col-md-12">
        <div class="row">
            <div class="col-md-5">
                <p class="text-center pull-left" style="font-weight: bold;margin-left: 6px">Tabla resumen del caso</p>
            </div>
            <div class="col-md-7"></div>
            <div class="col-md-12">
                <div class="table-responsive field-container" style="padding-left: 6px">
                    {if $CASE_DETAILS neq NULL}
                        <table id="step-type-table-{$idProcessCase}"
                               class="table table-bordered tablegridvalidate">
                            <thead>
                            <tr>
                                <td colspan="8" style="text-align: left; background-color:#f9f8f7">
                                    <strong>Proceso:</strong>&nbsp;{$CASE_DETAILS[0]['process']['process_title']}
                                </td>
                            </tr>
                            <tr style="vertical-align: top" id="">
                                <td style="width:22%"><span style="">Pasos</span></td>
                                <td class="step-no-manual" style="width:8%"><span >Tipo</span></td>
                                <td class="step-no-manual" style="width:11%"><span >Módulo</span></td>
                                <td class="step-no-manual" style="width:10%"><span >Fecha inicio</span></td>
                                <td class="step-no-manual" style="width:10%"><span >Fecha fin</span></td>
                                <td class="step-no-manual" style="text-align: center; width:8%">
                                    <span >Tiempo(Hrs)</span></td>
                                <td class="step-no-manual" style="width:9%!important;"><span>Calidad</span></td>
                                <td class="step-no-manual" style="width:22%"><span>Comentarios</span></td>
                            </tr>
                            </thead>
                            <tbody id="step-type-tbody-{$idProcessCase}" rowtotal="0">
                            {foreach $CASE_DETAILS as $key => $step}
                                {if $step['step']['process_stepsid'] eq NULL}{continue}{/if}
                                <tr style="vertical-align: top" id="">
                                    <td class="step-no-manual" style="vertical-align:top; width:22%">
                                        <a href="index.php?module=process_steps&action=DetailView&record={$step['step']['process_stepsid']}"
                                           target="_blank"
                                           title="ver detalles del paso">
                                        <span >{$step['step']['step_name']}</span>
                                        </a>
                                    </td>
                                    <td style="vertical-align:top; width:8%"><span >{$STEPS_TYPE[$step['step']['step_type']]}</span></td>
                                    <td class="step-no-manual" style="vertical-align:top; width:11%">
                                        {if $step['step']['step_type_module'] eq NULL}
                                            <span >&nbsp;-&nbsp;</span>
                                        {else}
                                        <a href="index.php?module={$step['step']['step_type_module']}&action=DetailView&record={*$step['crm_id']*}{$step['step']['step_type_module']|crmentity_id:$CASE_NUMBER:$ADB}"
                                           target="_blank"
                                           title="">
                                            <span >{$step['step']['step_type_module']|module_label: $ADB}</span>
                                        </a>
                                        {/if}

                                    </td>
                                    <td class="step-no-manual text-left" style="vertical-align:top; width:10%">
                                        <span >{($step['start_date']|cat:' '|cat:$step['start_time'])|date_es_format}</span>
                                    </td>
                                    <td class="step-no-manual text-left" style="vertical-align:top; width:10%">
                                        <span>{($step['due_date']|cat:' '|cat:$step['end_time'])|date_es_format}</span>
                                    </td>
                                    <td class="step-no-manual" style="text-align:center;vertical-align:top; width:8%">
                                        <span >{$step['step_exec_time']}</span>
                                    </td>
                                    <td class="step-no-manual" style="width:9%!important;vertical-align:top"><span>{$step['quality_valuation']}</span></td>
                                    <td class="step-no-manual text-left"
                                        style="vertical-align:top; width:22%!important">
                                        <span id="step_comments-16885"
                                              style="overflow-y:auto;
                                              vertical-align: top;max-height: 220px;line-height:1.35em!important">
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
                            {/foreach}
                            </tbody>

                            <tfoot id="tfoot-{$idProcessCase}">
                            <tr>
                                <td colspan="5" class="text-right">
                                    <span >Total tiempo de ejecución del proceso:</span>
                                </td>
                                <td class="step-no-manual" style="text-align: center">
                                    <span >{$CASE_DETAILS['summary_time']}</span></td>
                                <td>&nbsp;</td>
                                <td class="step-no-manual" style="text-align: justify; width:22%!important">&nbsp;</td>
                            </tr>
                            </tfoot>
                        </table>
                    {/if}
                </div>
            </div>
        </div>
    </div>
</div>
