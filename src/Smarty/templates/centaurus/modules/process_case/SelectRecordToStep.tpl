{assign var="PROCESS_CASES_UTILS_LOADED" value=false}
{math equation= rand() assign= "idProcessCase"}
{* process_cases_utils.js se carga desde boilerplate.tpl cuando $INCLUDE_PROCESS_JS está activo *}
<div class="row">
    {if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
        <div class="col-md-12">
            <div class="alert alert-danger">
                <strong>Error:&nbsp;</strong> {$MESSAGE}
            </div>
        </div>
    {/if}
    <div class="col-md-12" style="margin-bottom: 12px">
        <div class="row-grid-view justify-content-center">
            <div class="table-responsive field-container"
                 style="max-height: 350px;overflow-x: hidden; overflow-y: auto">
                <table id="step-table-{$idProcessCase}" class="table table-bordered tablegridvalidate"
                       style="min-width: 680px; width: 100%">
                    <thead>
                    <tr>
                        <td colspan="2" style="text-align: left; background-color:#f9f8f7">
                            <strong>Modulo:&nbsp;{$FLMODULE|module_label: $ADB}</strong>
                        </td>
                    </tr>
                    <tr valign="top" id="">
                        <td style="" width="20%"><span style="">Código</span></td>
                        <td style="" width="80%"><span style="">Registro</span></td>
                    </tr>
                    </thead>
                    <tbody id="step-tbody-{$idProcessCase}" rowtotal="0">
                    {if $ENTITYS neq NULL}
                        {foreach $ENTITYS as $type => $entity}
                            <tr valign="top" id="{$entity['crmid']}">
                                <td style="" width="20%"><span style="">
                                        <a href="#"
                                           class=""
                                           title="Editar notas"
                                           rel="{$RECORDS}"
                                           data-fl-module="{$FLMODULE}"
                                           data-module="{$MODULE}"
                                           data-case-number="{$CASE_NUMBER}"
                                           onclick="ProcessCaseUtils.joinRecordToCase(this, '{$entity['crmid']}',event)">
                                        {$entity['cod']}</span>
                                </td>
                                <td style="" width="80%"><span style="">
                                        <a class=""
                                           title="Ver detalles del registro"
                                           href="index.php?module={$FLMODULE}&parenttab=&action=DetailView&record={$entity['crmid']}"
                                           target="_blank">
                                            {if $entity['fieldname'] neq NULL}
                                            {$entity['fieldname']}
                                            {else}
                                                {$entity['crmid']}
                                            {/if}
                                        </a>
                                    </span>
                                </td>
                            </tr>
                        {/foreach}
                    {else}
                        <td colspan="2" style="text-align: left; background-color:#f9f8f7">
                            No se encontrarón registros
                        </td>
                    {/if}
                    </tbody>
                    <tfoot id="tfoot-{$idProcessCase}"
                           class=""
                           data-field-name=""
                           data-summary-row=""
                           data-operation-row="">
                    <tr>
                        <td colspan="2" class="text-center">&nbsp;</td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
{*</div>*}