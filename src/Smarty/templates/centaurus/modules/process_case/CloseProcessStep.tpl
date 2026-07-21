{math equation= rand() assign= "idProcessCase"}
<div id="CloseProcessStep" class="row">
    {*$DOCUMENTS|@var_dump*}
    {if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
        <div class="col-md-12">
            <div class="alert alert-danger">
                <strong>Error:&nbsp;</strong> {$MESSAGE}
            </div>
        </div>
    {/if}
    <div class="col-md-12" style="margin-bottom: 12px">
        <div class="row-grid-view justify-content-center">
            {if $CASE_DETAILS neq NULL}
                {* Step name*}
                <div class="col-lg-6 col-md-6 col-sm-6">
                    <div class="form-group">
                        <label for="step_name" style="font-weight: bold">Paso:</label>
                        <span class="form-control border" style="overflow-x: hidden;width: 100%">
                        {$CASE_DETAILS['step']['step_name']}
                    </span>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-6">&nbsp;</div>
                {* Documents requeiered *}
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="step_name" style="font-weight: bold">Entregables, resultados:</label>
                        <span class="form-control border"
                              style="overflow-x: hidden;width: 100% resize: vertical; word-break: break-word; min-height: 50px;line-height: 1.35em !important;">
                            {$CASE_DETAILS['step']['step_deliverables']}
                        </span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="step_name" style="font-weight: bold">Número de documentos requeridos:</label>
                        <span class="form-control border" style="overflow-x: hidden;width: 100%">
                            {if $CASE_DETAILS['step']['number_doc_required'] neq NULL}
                                {$CASE_DETAILS['step']['number_doc_required']}
                            {else}0
                            {/if}
                        </span>
                    </div>
                </div>
                {* Documents uploaded *}
                <form method="post" id="form-close-step-{$idProcessCase}" name="close-step-form-{$idProcessCase}" class="col-md-12">
                <div class="col-xs-12 col-md-12" style="margin-top:0; margin-bottom: 0">
                    {*$DOCUMENTS|var_dump*}
                    <table id="" class="table table-bordered tablegridvalidate"
                           style="margin-bottom: 4px!important;">
                        <thead>
                        <tr style="vertical-align: top">
                            <td class="" style="background-color: #fcfcfc; width: 75%; text-align: left">
                                Documentos adjuntos del registro
                            </td>
                            <td class="" style="background-color: #fcfcfc; width: 25%; text-align: center">
                                Fecha en la cual se registró
                            </td>
                        </tr>
                        </thead>
                        <tbody rowtotal="0" id="tbody-{$idProcessCase}">
                        {if $DOCUMENTS neq NULL}
                            {foreach $DOCUMENTS as $item}
                                <tr>
                                    <td style="width: 75%;">
                                        <div class="checkbox" style="margin: 0">
                                            <label>
                                                <input name="document_id[]" type="checkbox"
                                                       value="{$item['attachmentsid']}">
                                                {$item['name']}
                                            </label>
                                        </div>
                                    </td>
                                    <td class="" style="width: 25%;text-align: center">
                                        {$item['createdtime']}
                                    </td>
                                </tr>
                            {/foreach}
                        {/if}
                        </tbody>
                        <tfoot>
                        <tr>
                            <td colspan="2" style="background-color: #fcfcfc; text-align: center">
                                <div class="checkbox" style="margin: 0; text-align: center">
                                    <label style="text-align: right">
                                        <input id="not-document-{$idProcessCase}"   type="checkbox" value="1">
                                        No se requiere documento adjunto en esta entrega
                                    </label>
                                </div>
                            </td>
                        </tfoot>
                    </table>
                </div>
                <div class="col-xs-12 col-md-12" style="margin-top:0; margin-bottom: 0">
                    <div class="col-xs-12 col-md-12 text-center attachments-section"
                         style="display: inline-block; margin-top: 0; margin-bottom: 0"
                         data-element-id="{$idProcessCase}"
                         data-entity-id="{$RECORD}" data-module-name="{$FL_MODULE}"
                         data-maximum-file-size="2" data-modal="3">
                        <input type="file" multiple="multiple" placeholder=""
                               onchange="AttachmentsUtils.addEntityAttachment (event || window.event);"
                               style="bottom: 0; cursor: pointer; left: 0; opacity: 0; position: absolute; top: 0; width: 100%">
                        <span class="btn btn-info" title="Anexar un nuevo documento"><i class="fa fa-plus  fa-lg"></i>&nbsp;(Máx 2 MB)</span>
                    </div>

                </div>
                <div class="col-xs-12 col-md-12" style="margin-top: 2px">
                    <label for="progress" style="font-weight: bold">&nbsp;Reporte de actividad del paso:</label>
                    <textarea id="step_notes-{$idProcessCase}" name="step_notes"
                              class="form-control border"
                              placeholder="Escriba aquí el comentarios del caso, proceso o sobre el paso en particular"
                              style="height: 100px;resize: none;">{$CASE_DETAILS['comment']}</textarea>
                </div>
                    <input type="hidden" name="module" value="{$MODULE}">
                    <input type="hidden" name="action" value="AjaxDetailViewUtils">
                    <input type="hidden" name="caseId" value="{$CASE_DETAILS['process_casesid']}">
                    <input type="hidden" name="recordModule" value="{$RECORD}">
                    <input type="hidden" name="recordsId" value="{$RECORDS}">
                    <input type="hidden" name="caseNumber" value="{$CASE_NUMBER}">
                    <input type="hidden" name="function" value="SAVE-DOC-CLOSE-STEP">
                    <input type="hidden" name="step_type" value="{$CASE_DETAILS['step']['step_type']}">
                    <input type="hidden" name="Ajax" value="true">
                </form>
                <div class="col-xs-12 col-md-12" style="margin-top: 12px">
                    <header class="main-box-header clearfix">
                        <div class="action-bar text-center">
                            <button type="button" class="btn btn-success" onclick="ProcessCaseUtils.saveDocumentToCase(this,'{$idProcessCase}','{$RECORD}')">&nbsp;Guardar&nbsp;</button>&nbsp;<button
                                    type="button"
                                    onclick="ProcessCaseUtils.unCloseStepCase (this,'{$RECORD}')"
                                    class="btn btn-primary" id="btn-cancel-{$idProcessCase}" data-dismiss="modal" aria-hidden="true">&nbsp;Cancelar&nbsp;</button>
                        </div>
                    </header>
                </div>
                <input type="hidden" id="task-process-{$RECORD}" value="{$TASK_ACTION}">
                <input type="hidden" id="cancel-task-{$RECORD}" value="1">
                <input type="hidden" id="task-name-{$RECORD}" value="{$TASK_NAME}">
                <input type="hidden" id="num-docs-{$idProcessCase}"
                       value="{if $CASE_DETAILS['step']['number_doc_required'] neq NULL}{$CASE_DETAILS['step']['number_doc_required']}{else}0{/if}">

                <script>
                    ProcessCaseUtils.updateClosedStep('{$CASE_DETAILS['process_step']}', '{$RECORD}');
                </script>
            {else}
                <div class="col-md-12" style="min-height: 120px">&nbsp;</div>
            {/if}
        </div>
    </div>
   {* <script type="text/javascript" src="themes/centaurus/js/process_cases_utils.js"></script> *}
    <script type="text/html" id="document-tr-{$idProcessCase}">
        <tr>
            <td style="width: 75%;">
                <div class="checkbox" style="margin: 0">
                    <label>
                        <input name="document_id[]" type="checkbox" checked value="__ID__">
                        __NAME__
                    </label>
                </div>
            </td>
            <td class="" style="width: 25%;text-align: center">
                {$TODAY}
            </td>
        </tr>
    </script>
</div>
</div>