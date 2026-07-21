{strip}
    {math equation= rand() assign= "idKeyObjResult"}
    {block name="css"}{/block}
    <div class="col-md-12" {block name="table_margin"}{/block}>
        {block name="card_header"}{/block}
        <div class="table-responsive field-container">
            <table id="okr-action-plan-table-{$idKeyObjResult}" class="table table-bordered tablegridvalidate">
                <thead>
                    <tr>
                        <td {block name="colspan_header"}{/block} style="text-align: left; background-color:#f9f8f7">{block name="header_title"}{/block}</td>
                    </tr>
                    <tr>
                        {block name="header_column"}{/block}
                    </tr>
                </thead>
                <tbody id="tbody-okr-action-plan-{$idKeyObjResult}" rowtotal="0">
                    {block name="tbody_okr_action_plan"}{/block}
                </tbody>
                <tfoot id="tfoot-{$idKeyObjResult}" data-field-name="" data-summary-row=""
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