{strip}
    {block name="css"}{/block}
    {math equation= rand() assign= "idStepType"}

    <div class="col-md-12" {if $VIEW neq NULL}style="margin-top: 20px"{/if}>
        {*$VIEW|var_dump*}
        {if ($VIEW neq NULL) && ($RELATED_TASK neq NULL)}
            <div class="row card-header platzilla-card-header" style="padding-left: 0!important;">

                <div class="col-md-5">
                    <p class="text-center pull-left" style="font-weight: bold">Tareas</p>
                </div>
                <div class="col-md-7">&nbsp;</div>
            </div>
        {/if}
        <div class="table-responsive field-container">
            <table id="step-type-table-{$idStepType}" class="table table-bordered tablegridvalidate">
                <thead>
                <tr>
                    <td colspan="4" style="text-align: left; background-color:#f9f8f7"><strong>Acciones ejecutadas al cerrar el paso</strong>
                    </td>
                </tr>
                <tr valign="top" id="">
                    <td style="" width="20%"><span style="">Tipo de paso</span></td>
                    {if $VIEW neq NULL}
                            {if ($stepType neq NULL) && ($stepType->stepType eq 'MANUAL')}
                                <td class="step-manual" colspan="3" style="width: 80%">
                                    <span style="">Comentario:</span>
                                </td>
                            {else}
                                <td class="step-no-manual" style="width: 15%"><span style="">Módulo donde inicia la acción</span></td>
                                <td class="step-no-manual" style="width: 15%"><span style="">Presentación del registro</span></td>
                                <td class="step-no-manual" style="width: 50%"><span style="">Tarea automática al cerrar el paso</span></td>
                            {/if}
                    {else}
                        <td class="step-no-manual {if ($stepType neq NULL) && ($stepType->stepType eq 'MANUAL')}hidden{/if}" style="width: 15%"><span style="">Módulo donde inicia la acción</span></td>
                        <td class="step-no-manual {if ($stepType neq NULL) && ($stepType->stepType eq 'MANUAL')}hidden{/if}" style="width: 15%" ><span style="">Presentación del registro</span></td>
                        <td class="step-no-manual {if ($stepType neq NULL) && ($stepType->stepType eq 'MANUAL')}hidden{/if}" style="width: 50%"><span style="">Tarea automática al cerrar el paso</span></td>
                        <td class="step-manual {if (($stepType eq NULL)) || ($stepType neq NULL && $stepType->stepType neq 'MANUAL')}hidden{/if}" {*colspan="3"*} style="width: 80%">
                            <span style="">Comentario:</span>
                        </td>
                    {/if}
                </tr>
                </thead>
                <tbody id="step-type-tbody-{$idStepType}" rowtotal="0">
                {block name="step_type_body"}{/block}
                </tbody>
                {*
                <tfoot id="tfoot-{$idStepType}"
                       class=""
                       data-field-name=""
                       data-summary-row=""
                       data-operation-row="">
                <tr>
                    <td colspan="3" class="text-center">&nbsp;</td>
                </tr>
                </tfoot> *}
            </table>
        </div>
    </div>
    {block name="js"}{/block}
{/strip}