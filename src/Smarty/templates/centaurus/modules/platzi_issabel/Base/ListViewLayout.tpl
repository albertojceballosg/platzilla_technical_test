{strip}
    {if (isset($ISSABEL_ID)) && ($ISSABEL_ID neq NULL)}
        {assign var="idPlatziIsabel" value=$ISSABEL_ID}
    {else}
        {math equation= rand() assign= "idPlatziIsabel"}
    {/if}

    {block name="css"}{/block}
    <div class="row module-buttons">
        <div class="col-lg-12" style="padding-right: 10px; padding-bottom: 0">
            <div class="pull-left">
                <h1 style="margin-left: -3px;font-weight: bold">
                    Centralita
                </h1>
            </div>
            <div class="pull-right">
            </div>
        </div>
    </div>
    {if (!empty ($MESSAGE))}
        <div class="alert alert-{if ($IS_ERROR)}danger{else}success{/if} fade in">
            <strong>{if ($IS_ERROR)}Error!{else}Listo!{/if}</strong> {$MESSAGE}
        </div>
    {/if}
    {block name="page_header"}{/block}
    <div class="container-fluid base-list-container" style="margin-top: -6px!important;">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-xs-12">
                <div class="main-box clearfix">
                    <div id="list-view-header-columns-{$idPlatziIsabel}" class="row">
                        {block name = "header_title"}{/block}
                    </div>
                    <div class="{block name="main_box_class"}{/block}">
                        <div class="row">
                            <div class="col-lg-12 col-md-12 col-xs-12">
                                <div class="table-responsive" style="overflow-y: visible;font-weight: bold">
                                    <table id="table_list" class="table {block name="table_type"}{/block}">
                                        <thead>
                                        <tr class="table-title">
                                            {block name = "table_header"}{/block}
                                        </tr>
                                        </thead>
                                        <tbody id="platzi-issabel-{$idPlatziIsabel}">
                                        {block name = "table_body"}{/block}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <header class="btn-footer main-box-header clearfix">
                        <div class="row">
                            <div id="show-records-{$idPlatziIsabel}"
                                 class="filter-block col-md-6 pull-left">{block name="page-data-platzi-issabel"}{/block}</div>
                            <div class="col-md-6" style="margin-top: -.25em; margin-right:-.2em;">
                                {block name = "pager-platzi-issabel"}{/block}
                            </div>
                        </div>
                    </header>
                </div>
            </div>
        </div>
    </div>
    {block name="modal_detalview"}{/block}
    {block name="js"}{/block}
{/strip}