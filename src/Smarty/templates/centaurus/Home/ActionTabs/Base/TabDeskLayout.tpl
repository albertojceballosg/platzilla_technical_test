{strip}
    {*$ROWS|var_dump*}
    <table id="table_list" class="table">
        <thead>
        <tr class="table-title">
            {block name = "table_header"}{/block}
        </tr>
        </thead>
        <tbody id="action-in-progress-{$ACTION_TAB_ID}">
        {block name = "table_body"}{/block}
        </tbody>
    </table>
{/strip}