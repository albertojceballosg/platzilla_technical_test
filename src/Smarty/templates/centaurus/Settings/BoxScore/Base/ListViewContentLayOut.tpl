{strip}
    {block name='page_header'}{/block}
    <div class="main-box-body clearfix" id="ListViewContents-{$idBoxScore}">
        <div class="table-responsive">
            <table class="table table-striped table-hover {*bs-table*}" id="bs_table-{$idBoxScore}">
                <thead>
                {block name='table_header'}{/block}
                </thead>
                <tbody id="{block name='table_body_id'}{/block}">
                {block name='table_body'}{/block}
                </tbody>
            </table>
        </div>
    </div>
{/strip}