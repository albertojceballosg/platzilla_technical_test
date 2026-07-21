{strip}
    {math equation= rand() assign= "idProgressJob"}
    {block name="css"}{/block}
    <div class="col-md-12" {if $VIEW neq NULL}style="margin-top: 20px"{/if}>
        <div class="table-responsive">
            <table id="planned_activities-table-{$idProgressJob}" class="table table-bordered tablegridvalidate">
                <thead>
                <tr>
                    <td colspan="7" style="text-align: left; background-color:#f9f8f7">
                        <strong>Reporte de avance global de trabajo</strong></td>
                </tr>
                <tr valign="top" class="border">
                    <td {block name="col_1"}{/block}><span style="">Trabajo que se reporta</span></td>
                    <td {block name="col_2"}{/block}><span style="">Tiempo estimado total del trabajo</span></td>
                    <td {block name="col_3"}{/block}><span style="">(%) de avance anterior</span></td>
                    <td {block name="col_4"}{/block}><span style="">Reporte de avance del trabajo</span></td>
                    <td {block name="col_5"}{/block}><span style="">(hrs) Tiempo usado para el avance reportado</span></td>
                    <td {block name="col_6"}{/block}><span style="">% avance total alcanzado con el reporte</span></td>
                    <td class="text-center"
                        {if $VIEW eq NULL}width="12%"{/if}>{if $VIEW eq NULL}Acciones{else}&nbsp;{/if}</td>
                </tr>
                </thead>
                <tbody id="tbody-job-report-{$idProgressJob}" rowtotal="0">
                {block name="tbodyJobReport"}{/block}
                </tbody>
                <tfoot id="tfoot-{$idProgressJob}" data-summary-row="" data-operation-row="">
                {block name="summaryRow"}{/block}
                {block name="addRow"}{/block}
                </tfoot>
            </table>
        </div>
    </div>
    {block name="script"}{/block}
    {block name="script_template"}{/block}
{/strip}