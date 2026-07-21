{math equation= rand() assign= "idProcessCase"}
{strip}
    <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12" style="margin-top: 10px">
            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                <div class="table-responsive">
                    {block name="processes_name"}{/block}
                </div>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                <div class="table-responsive">
                    {block name="case_name"}{/block}
                </div>
            </div>
        </div>
        <div class="col-md-12 col-sm-12 col-xs-12" style="margin-top: 10px">
            {block name="flow_steps"}{/block}
        </div>
        <div class="col-md-12 col-sm-12 col-xs-12" style="margin-top: 10px">
            <table id="step-type-table-{$idProcessCase}"
                   class="table table-bordered tablegridvalidate">
                <thead>
                <tr>
                    <td colspan="9" style="text-align: left; background-color:#f9f8f7">
                        <strong>Proceso:</strong>&nbsp;{$CASE_DETAILS[0]['process']['process_title']}
                    </td>
                </tr>
                <tr valign="top" id="">
                    <td style="text-align: center; width: 7%">&nbsp;</td>
                    <td style="width: 19%" ><span style="">Pasos</span></td>
                    <td class="step-no-manual" style="width: 8%"><span style="">Tipo</span></td>
                    <td class="step-no-manual" style="width: 11%"><span style="">Módulo</span></td>
                    <td class="step-no-manual" style="width: 9%"><span style="">Fecha inicio</span></td>
                    <td class="step-no-manual" style="width: 9%"><span style="">Fecha fin</span></td>
                    <td class="step-no-manual" style="text-align: center;width: 8%">
                        <spanstyle="">Tiempo(Hrs)</span>
                    </td>
                    <td class="step-no-manual" style="width: 9%"><span style="">Calidad</span></td>
                    <td class="step-no-manual" style="width: 20%"><span style="">Comentarios</span></td>
                </tr>
                </thead>
                <tbody id="step-type-tbody-{$idProcessCase}" rowtotal="0">
                {block name="row_steps"}{/block}
                </tbody>

                <tfoot id="tfoot-{$idProcessCase}">
                <tr>
                    <td colspan="6" class="text-right">
                        <span style="">Total tiempo de ejecución del proceso:</span>
                    </td>
                    <td class="step-no-manual" style="text-align: center; width: 8%">
                        <span style="">{block name="steps_total_time"}{/block}</span></td>
                    <td class="step-no-manual" style="width: 9%">&nbsp;</td>
                    <td class="step-no-manual" style="text-align: justify;width: 20%">&nbsp;</td>
                </tr>
                </tfoot>
            </table>
        </div>
        <div class="col-md-12 col-sm-12 col-xs-12" style="margin-top: 10px">
            {block name="page_loading"}{/block}
            {block name="chart_steps"}{/block}
        </div>
    </div>
    {block name="script"}{/block}
{/strip}