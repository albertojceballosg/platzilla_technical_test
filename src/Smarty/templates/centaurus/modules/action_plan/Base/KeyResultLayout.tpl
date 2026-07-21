{strip}
    <div class="table-responsive field-container">
        <table id="kr-business_objective-table-{$ID_TABLE}" class="table table-bordered tablegridvalidate">
            <thead>
            <tr>
                <td {block name="colspan_header"}{/block}
                        style="text-align: left; background-color:#f9f8f7">{block name="header_title"}{/block}</td>
            </tr>
            <tr>
                {block name="header_column"}{/block}
            </tr>
            </thead>
            <tbody id="tbody-kr-business_objective-{$ID_TABLE}" rowtotal="0">
            {block name="tbody_kr-business_objective"}{/block}
            </tbody>
            <tfoot id="tfoot-{$ID_TABLE}" data-field-name="" data-summary-row=""
                   data-operation-row="">
            {block name="summaryRow"}{/block}
            {block name="addRow"}{/block}
            </tfoot>
        </table>
    </div>
{/strip}