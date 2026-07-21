{extends file='Home/ActionTabs/Base/MainActionTabsLayout.tpl'}
{strip}
    {* Action in progress module Work *}
    {block name="orden_de_trabajo"}
        <div class="main-box clearfix" style="margin-top: 0">
            <div class="main-box-header clearfix">
                <div id="list-view-header-columns-{$WORK_TAB_ID}" class="row">
                    {block name = "action_buttons-orden_de_trabajo"}{/block}
                </div>
            </div>
            <div class="main-box-body clearfix" style="padding-bottom: 0">
                <div class="table-responsive" id="main-box-action-in-progress-{$WORK_TAB_ID}" style="overflow-y: visible;">
                    <table id="table_list" class="table" style="padding-bottom: 0;margin-top: 0">
                        <thead>
                        <tr class="table-title">
                            {block name = "table_header-orden_de_trabajo"}{/block}
                        </tr>
                        </thead>
                        <tbody id="action-in-progress-{$WORK_TAB_ID}">
                        {block name = "table_body-orden_de_trabajo"}{/block}
                        </tbody>
                    </table>
                </div>
            </div>
            <header class="btn-footer main-box-header clearfix" style="margin-top: 0;padding-top: 0">
                <div class="row" style="margin-top: 4px">
                    <div id="show-records-{$WORK_TAB_ID}"
                         class="filter-block col-md-6 pull-left">{block name="page-data-orden_de_trabajo"}{/block}</div>
                    <div class="col-md-6"
                         style="padding-right: 20px" {*style="margin-top: -.25em; margin-right:-.2em;*}>
                        {block name = "pager-orden_de_trabajo"}{/block}
                    </div>
                </div>
            </header>
        </div>
    {/block}
    {* Action in progress module Project *}
    {block name="proyecto"}
        <div class="main-box clearfix" style="margin-top: 0">
            <div class="main-box-header clearfix">
                <div id="list-view-header-columns-{$PROJECT_TAB_ID}" class="row">
                    {block name = "action_buttons-proyecto"}{/block}
                </div>
            </div>
            <div class="main-box-body clearfix">
                <div class="table-responsive" id="main-box-action-in-progress-{$PROJECT_TAB_ID}" style="overflow-y: visible;font-weight: bold">
                    <table id="table_list" class="table">
                        <thead>
                        <tr class="table-title">
                            {block name = "table_header-proyecto"}{/block}
                        </tr>
                        </thead>
                        <tbody id="action-in-progress-{$PROJECT_TAB_ID}">
                        {block name = "table_body-proyecto"}{/block}
                        </tbody>
                    </table>
                </div>
            </div>
            <header class="btn-footer main-box-header clearfix">
                <div class="row">
                    <div id="show-records-{$PROJECT_TAB_ID}"
                         class="filter-block col-md-6 pull-left">{block name="page-data-proyecto"}{/block}</div>
                    <div class="col-md-6" style="margin-top: -.25em; margin-right:-.2em;">
                        {block name = "pager-proyecto"}{/block}
                    </div>
                </div>
            </header>
        </div>
    {/block}
    {* To be process module Orders *}
    {block name="pedidos"}
        <div class="main-box clearfix" style="margin-top: 0">
            <div class="main-box-header clearfix">
                <div id="list-view-header-columns-{$ORDERS_TAB_ID}" class="row">
                    {block name = "action_buttons-pedidos"}{/block}
                </div>
            </div>
            <div class="main-box-body clearfix">
                <div class="table-responsive" id="main-box-action-in-progress-{$ORDERS_TAB_ID}" style="overflow-y: visible;">
                    <table id="table_list" class="table">
                        <thead>
                        <tr class="table-title">
                            {block name = "table_header-pedidos"}{/block}
                        </tr>
                        </thead>
                        <tbody id="action-in-progress-{$ORDERS_TAB_ID}">
                        {block name = "table_body-pedidos"}{/block}
                        </tbody>
                    </table>
                </div>
            </div>
            <header class="btn-footer main-box-header clearfix">
                <div class="row">
                    <div id="show-records-{$ORDERS_TAB_ID}"
                         class="filter-block col-md-6 pull-left">{block name="page-data-pedidos"}{/block}</div>
                    <div class="col-md-6" style="margin-top: -.25em; margin-right:-.2em;">
                        {block name = "pager-pedidos"}{/block}
                    </div>
                </div>
            </header>
        </div>
    {/block}
    {* To be process module Issues *}
    {block name="incidencias"}
        <div class="main-box clearfix" style="margin-top: 0">
            <div class="main-box-header clearfix">
                <div id="list-view-header-columns-{$ISSUES_TAB_ID}" class="row">
                    {block name = "action_buttons-incidencias"}{/block}
                </div>
            </div>
            <div class="main-box-body clearfix">
                <div class="table-responsive" id="main-box-action-in-progress-{$ISSUES_TAB_ID}" style="overflow-y: visible;">
                    <table id="table_list" class="table">
                        <thead>
                        <tr class="table-title">
                            {block name = "table_header-incidencias"}{/block}
                        </tr>
                        </thead>
                        <tbody id="action-in-progress-{$ISSUES_TAB_ID}">
                        {block name = "table_body-incidencias"}{/block}
                        </tbody>
                    </table>
                </div>
            </div>
            <header class="btn-footer main-box-header clearfix">
                <div class="row">
                    <div id="show-records-{$ISSUES_TAB_ID}"
                         class="filter-block col-md-6 pull-left">{block name="page-data-incidencias"}{/block}</div>
                    <div class="col-md-6" style="margin-top: -.25em; margin-right:-.2em;">
                        {block name = "pager-incidencias"}{/block}
                    </div>
                </div>
            </header>
        </div>
    {/block}
    {* To be process module Opportunities *}
    {block name="oportunidades"}
        <div class="main-box clearfix" style="margin-top: 0">
            <div class="main-box-header clearfix">
                <div id="list-view-header-columns-{$OPPORTUNITIES_TAB_ID}" class="row">
                    {block name = "action_buttons-oportunidades"}{/block}
                </div>
            </div>
            <div class="main-box-body clearfix">
                <div class="table-responsive" id="main-box-action-in-progress-{$OPPORTUNITIES_TAB_ID}" style="overflow-y: visible;">
                    <table id="table_list" class="table">
                        <thead>
                        <tr class="table-title">
                            {block name = "table_header-oportunidades"}{/block}
                        </tr>
                        </thead>
                        <tbody id="action-in-progress-{$OPPORTUNITIES_TAB_ID}">
                        {block name = "table_body-oportunidades"}{/block}
                        </tbody>
                    </table>
                </div>
            </div>
            <header class="btn-footer main-box-header clearfix">
                <div class="row">
                    <div id="show-records-{$OPPORTUNITIES_TAB_ID}"
                         class="filter-block col-md-6 pull-left">{block name="page-data-oportunidades"}{/block}</div>
                    <div class="col-md-6" style="margin-top: -.25em; margin-right:-.2em;">
                        {block name = "pager-oportunidades"}{/block}
                    </div>
                </div>
            </header>
        </div>
    {/block}
    {* To be process module Asiones *}
    {block name="Calender"}
        <div class="main-box clearfix" style="margin-top: 0">
            <div class="main-box-header clearfix">
                <div id="list-view-header-columns-{$ACTION_TAB_ID}" class="row">
                    {block name = "action_buttons-calendar"}{/block}
                </div>
            </div>
            <div class="main-box-body clearfix">
                <div class="table-responsive" id="main-box-action-in-progress-{$ACTION_TAB_ID}" style="overflow-y: visible;font-weight: bold">
                    <table id="table_list" class="table">
                        <thead>
                        <tr class="table-title">
                            {block name = "table_header-action"}{/block}
                        </tr>
                        </thead>
                        <tbody id="action-in-progress-{$ACTION_TAB_ID}">
                        {block name = "table_body-action"}{/block}
                        </tbody>
                    </table>
                </div>
            </div>
            <header class="btn-footer main-box-header clearfix">
                <div class="row">
                    <div id="show-records-{$ACTION_TAB_ID}"
                         class="filter-block col-md-6 pull-left">{block name="page-data-action"}{/block}</div>
                    <div class="col-md-6" style="margin-top: -.25em; margin-right:-.2em;">
                        {block name = "pager-action"}{/block}
                    </div>
                </div>
            </header>
        </div>
    {/block}
    {* Work by Supplier module *}
    {block name="por_proveedor"}
        <div class="main-box clearfix" style="margin-top: 0">
            <div class="main-box-header clearfix">
                <div id="list-view-header-columns-{$SUPPLIER_WORK_TAB_ID}" class="row">
                    {block name = "action_buttons-por_proveedor"}{/block}
                </div>
            </div>
            <div class="main-box-body clearfix" style="padding-bottom: 0">
                <div class="table-responsive" id="main-box-supplier-work-{$SUPPLIER_WORK_TAB_ID}" style="overflow-y: visible;">
                    <table id="table_list" class="table" style="padding-bottom: 0;margin-top: 0">
                        <thead>
                        <tr class="table-title">
                            {block name = "table_header-por_proveedor"}{/block}
                        </tr>
                        </thead>
                        <tbody id="supplier-work-{$SUPPLIER_WORK_TAB_ID}">
                        {block name = "table_body-por_proveedor"}{/block}
                        </tbody>
                    </table>
                </div>
            </div>
            <header class="btn-footer main-box-header clearfix" style="margin-top: 0;padding-top: 0">
                <div class="row" style="margin-top: 4px">
                    <div id="show-records-{$SUPPLIER_WORK_TAB_ID}"
                         class="filter-block col-md-6 pull-left">{block name="page-data-por_proveedor"}{/block}</div>
                    <div class="col-md-6" style="padding-right: 20px">
                        {block name = "pager-por_proveedor"}{/block}
                    </div>
                </div>
            </header>
        </div>
    {/block}
{/strip}