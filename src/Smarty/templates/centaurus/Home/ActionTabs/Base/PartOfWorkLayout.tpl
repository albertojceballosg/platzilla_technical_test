{strip}
    {math equation= rand() assign= "idModalDetalView"}
    {block name="css"}{/block}
    {if (!empty ($MESSAGE))}
        <div class="alert alert-{if ($IS_ERROR)}danger{else}success{/if} fade in">
            <strong>{if ($IS_ERROR)}Error!{else}Listo!{/if}</strong> {$MESSAGE}
        </div>
    {/if}
    {block name="page_header"}{/block}
    <div class="container-fluid base-list-container" style="margin-top: -6px!important;">
        <div class="{block name="main_box_class"}{/block}" style="margin-top: 0">
            <div class="main-box-header clearfix">
                <div id="list-view-header-columns-{$PART_WORK_TAB_ID}" class="row">
                    {block name = "header_title"}{/block}
                </div>
            </div>
            <div class="{block name="main_box_class"}{/block}">
                <div class="table-responsive" style="overflow-y: visible;font-weight: bold">
                    <table id="table_list" class="table {block name="table_type"}{/block}">
                        <thead>
                        <tr class="table-title">
                            {block name = "table_header"}{/block}
                        </tr>
                        </thead>
                        <tbody id="part-work-{$PART_WORK_TAB_ID}">
                        {block name = "table_body"}{/block}
                        </tbody>
                    </table>
                </div>
            </div>
            <header class="btn-footer main-box-header clearfix">
                <div class="row">
                    <div id="show-records-{$PART_WORK_TAB_ID}"
                         class="filter-block col-md-6 pull-left">{block name="page-data-proyecto"}{/block}</div>
                    <div class="col-md-6" style="margin-top: -.25em; margin-right:-.2em;">
                        {block name = "pager-part-work"}{/block}
                    </div>
                </div>
            </header>
        </div>
    </div>
    {block name="modal_detalview"}{/block}
    {block name="js"}{/block}
{/strip}