{strip}
    {math equation= rand() assign= "idProcessSteps"}
    {block name="css"}{/block}
    <div class="col-md-12" {block name="table_margin"}{/block}>
        {block name="card_header"}{/block}
        <div class="table-responsive field-container">
                <table id="process-steps-table-{$idProcessSteps}" class="table table-bordered tablegridvalidate">
                    <thead>
                    <tr>
                        <td {block name="colspan_header"}{/block} style="text-align: left; background-color:#f9f8f7"><strong>Pasos del proceso:</strong></td>
                    </tr>
                    <tr>
                        <td {block name="col_1"}{/block}>Paso del proceso</td>
                        <td {block name="col_2"}{/block}>Nombre del paso</td>
                        <td {block name="col_3"}{/block}>Rol responsable</td>
                        <td {block name="col_4"}{/block}>Módulo</td>
                        <td {block name="col_5"}{/block}>Acción en el paso
                        <span id="help-action-{$idProcessSteps}" class="help-block" style="color: red"></span>
                        </td>
                        <td {block name="col_6"}{/block}>Tipo</td>
                        <td {block name="col_7"}{/block}>Estado</td>
                        {block name="col_action"}{/block}
                    </tr>
                    </thead>
                    <tbody id="tbody-process-steps-{$idProcessSteps}" rowtotal="0">
                    {block name="tbody_process_steps"}{/block}
                    </tbody>
                    <tfoot id="tfoot-{$idProcessSteps}" data-field-name="" data-summary-row=""
                           data-operation-row="">
                        {block name="summaryRow"}{/block}
                        {block name="addRow"}{/block}
                    </tfoot>
                </table>
            {block name="global_task"}{/block}
        </div>
    </div>
    {block name="script"}{/block}
    {block name="script_template"}{/block}
{/strip}