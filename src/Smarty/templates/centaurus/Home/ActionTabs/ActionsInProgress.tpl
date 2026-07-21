{extends file='Home/ActionTabs/Base/ActionTabsLayout.tpl'}
{strip}
    {block name="css"}
        <link type="text/css" rel="stylesheet" href="themes/centaurus/css/libs/datepicker.css"/>
        <link type="text/css" rel="stylesheet" href="themes/centaurus/css/compiled/list-view.css?v=1.1"/>
        <link type="text/css" rel="stylesheet" href="themes/centaurus/css/compiled/pipeline.css?v=1.0"/>
        <link type="text/css" rel="stylesheet" href="modules/instancesdatasharing/instancesdatasharing.css?v=1.0"/>
    {/block}
    {* Actiosn Calendar module*}
    {block name = "action_buttons-calendar"}
        {assign var="fromModule" value='Calendar'}
        {assign var="totalRecords" value=$ACTION_TOTAL_ROWS}
        {assign var="page" value=$START_RECORD}
        {assign var='actionId' value=$ACTION_TAB_ID}
        {assign var='ajaxFuntion' value='ACTONS_IN_PROGRESS'}
        {assign var='ajaxFile' value='AjaxDeskUtils'}
        {assign var="hasOtherButton" value='YES'}
        {assign var="hasThirdButton" value='NO'}
        {assign var="otherButtonTitle" value=''}
        {assign var="otherButtonToltip" value='Buscar en el calendario'}
        {assign var="otherButtonAction" value='DataViewUtils.showInCalendar'}
        {assign var="otherButtonClass" value='btn btn-primary'}
        {assign var="otherButtonIcon" value='fa-calendar'}
        {include file='Home/ActionTabs/Base/ActionButtonsBlock.tpl'}
    {/block}
    {block name = "table_header-action"}
        {foreach $ACTION_TABLE_HEADER as  $label => $data}
            <th class="{$data.class}"
                style="width:{$data.width}%;vertical-align: top;text-align: {$data.text_align}"
                colspan="{$data.colspan}">
                <div style="display: inline-flex;">
                    <div class="title-overflow">
                        <a href="#" title="{$label}" class="title-link" {*onclick=""*}>
                            <span>{$label}</span>
                                    {*<i class="fa fa-caret-up" aria-hidden="true" style="margin-left:.5em;"></i>*}
                        </a>
                    </div>
                </div>
            </th>
        {/foreach}
    {/block}

    {block name = "table_body-action"}
        {if $ACTION_IN_PROGRESS neq NULL}
            {foreach $ACTION_IN_PROGRESS as $action}
                <tr>
                    {html_row_table fields=$ACTION_TABLE_ROW row_data=$action url_avatar=$URL_AVATARS}
                </tr>
            {/foreach}
        {/if}
    {/block}

    {block name="page-data-action"}
        <span>Mostrando registros&nbsp;{$START_RECORD} - {$RECORDS_PER_PAGE}&nbsp;de&nbsp;{$ACTION_TOTAL_ROWS}</span>
    {/block}
    {block name = "pager-action"}
        <div id="pager-dv" class="text-right selection-stuff" style="display: block;">
            <ul id="pager-{$ACTION_TAB_ID}" class="pagination">
                {if $ACTION_PAGER neq NULL}
                    {$ACTION_PAGER}
                {else}
                    <li class="Pages"><a href="#"><strong>1</strong></a></li>
                {/if}

            </ul>
        </div>
    {/block}
    {* Work module *}
    {block name = "action_buttons-orden_de_trabajo"}
        {assign var="fromModule" value='orden_de_trabajo'}
        {assign var="totalRecords" value=$WORK_TOTAL_ROWS}
        {assign var="page" value=$START_RECORD}
        {assign var='actionId' value=$WORK_TAB_ID}
        {assign var='ajaxFuntion' value='WORK_IN_PROGRESS'}
        {assign var='ajaxFile' value='AjaxDeskUtils'}
        {assign var="hasOtherButton" value='YES'}
        {assign var="otherButtonTitle" value=''}
        {assign var="otherButtonToltip" value='Calendario de tareas de trabajo'}
        {assign var="otherButtonAction" value='DataViewUtils.showInCalendar'}
        {assign var="otherButtonClass" value='btn btn-primary'}
        {assign var="otherButtonIcon" value='fa-calendar'}
        {assign var="hasThirdButton" value='YES'}
        {assign var="otherButtonTitle" value=''}
        {assign var="thirdButtonToltip" value='Calendario de trabajos'}
        {assign var="thirdButtonAction" value='DataViewUtils.showInCalendar'}
        {assign var="thirdButtonData" value='WORK_TO_PROCESSED'}
        {assign var="thirdButtonClass" value='btn btn-primary'}
        {assign var="thirdButtonIcon" value='fa-calendar-o'}
        {include file='Home/ActionTabs/Base/ActionButtonsBlock.tpl'}
    {/block}

    {* Works module *}
    {block name = "table_header-orden_de_trabajo"}
        {foreach $WORK_TABLE_HEADER as  $label => $data}
            <th class="{$data.class}"
                style="width:{$data.width}%;vertical-align: top;text-align: {$data.text_align}"
                colspan="{$data.colspan}">
                <div style="display: inline-flex;">
                    <div class="title-overflow">
                        <a href="#" title="{$label}" class="title-link" {*onclick=""*}>
                            <span>{$label}</span>
                            {*<i class="fa fa-caret-up" aria-hidden="true" style="margin-left:.5em;"></i>*}
                        </a>
                    </div>
                </div>
            </th>
        {/foreach}
    {/block}
    {block name = "table_body-orden_de_trabajo"}
        {if $WORK_IN_PROGRESS neq NULL}
            {foreach $WORK_IN_PROGRESS as $work}
                <tr>
                    {html_row_table fields=$WORK_TABLE_ROW row_data=$work url_avatar=$URL_AVATARS}
                </tr>
            {/foreach}
        {/if}
    {/block}
    {block name="page-data-orden_de_trabajo"}
        <span>Mostrando registros&nbsp;{$START_RECORD} - {$RECORDS_PER_PAGE}&nbsp;de&nbsp;{$WORK_TOTAL_ROWS}</span>
    {/block}
    {block name = "pager-orden_de_trabajo"}
        <div id="pager-dv" class="text-right selection-stuff" style="display: block;">
            <ul id="pager-{$WORK_TAB_ID}" class="pagination">
                {if $WORK_PAGER neq NULL}
                    {$WORK_PAGER}
                {else}
                    <li class="Pages"><a href="#"><strong>1</strong></a></li>
                {/if}

            </ul>
        </div>
    {/block}
    {* Project module *}
    {block name = "action_buttons-proyecto"}
        {assign var="fromModule" value='proyecto'}
        {assign var="totalRecords" value=$PROJECT_TOTAL_ROWS}
        {assign var="page" value=$START_RECORD}
        {assign var='actionId' value=$PROJECT_TAB_ID}
        {assign var='ajaxFuntion' value='PROJECT_IN_PROGRESS'}
        {assign var='ajaxFile' value='AjaxDeskUtils'}
        {assign var="hasOtherButton" value='YES'}
        {assign var="hasThirdButton" value='NO'}
        {include file='Home/ActionTabs/Base/ActionButtonsBlock.tpl'}
    {/block}
    {block name = "table_header-proyecto"}
        {foreach $PROJECT_TABLE_HEADER as  $label => $data}
            <th class="{$data.class}"
                style="width:{$data.width}%;vertical-align: top;text-align: {$data.text_align};"
                colspan="{$data.colspan}">
                <div style="display: inline-flex;">
                    <div class="title-overflow">
                        <a href="#" title="{$label}" class="title-link" {*onclick=""*}>
                            <span>{$label}</span>
                            {*<i class="fa fa-caret-up" aria-hidden="true" style="margin-left:.5em;"></i>*}
                        </a>
                    </div>
                </div>
            </th>
        {/foreach}
    {/block}
    {block name = "table_body-proyecto"}
        {if $PROJECT_IN_PROGRESS neq NULL}
            {foreach $PROJECT_IN_PROGRESS as $project}
                <tr>
                    {html_row_table fields=$PROJECT_TABLE_ROW row_data=$project url_avatar=$URL_AVATARS}
                </tr>
            {/foreach}
        {/if}
    {/block}
    {block name="page-data-proyecto"}
        <span>Mostrando registros&nbsp;{$START_RECORD} - {$RECORDS_PER_PAGE}&nbsp;de&nbsp;{$PROJECT_TOTAL_ROWS}</span>
    {/block}
    {block name = "pager-proyecto"}
        <div id="pager-dv" class="text-right selection-stuff" style="display: block;">
            <ul id="pager-{$PROJECT_TAB_ID}" class="pagination">
                {if $PROJECT_PAGER neq NULL}
                    {$PROJECT_PAGER}
                {else}
                    <li class="Pages"><a href="#"><strong>1</strong></a></li>
                {/if}

            </ul>
        </div>
    {/block}
    {* Work by Supplier module *}
    {block name = "action_buttons-por_proveedor"}
        <div class="main-box-header clearfix" style="padding: 0 1.2em">
            <div class="row" style="margin-top: 1.5em">
                <form role="form" method="post" id="supplier_work_form-{$SUPPLIER_WORK_TAB_ID}">
                    <input type="hidden" name="module" value="{$MODULE}">
                    <input type="hidden" name="flmodule" value="por_proveedor">
                    <input type="hidden" id="function-name-{$SUPPLIER_WORK_TAB_ID}" name="function" value="WORK_BY_SUPPLIER">
                    <input type="hidden" name="action" value="AjaxDeskUtils">
                    <input type="hidden" name="Ajax" value="true">
                    <input type="hidden" name="total_records" value="{$SUPPLIER_WORK_TOTAL_ROWS}">
                    <input type="hidden" name="page" value="{$START_RECORD}">
                    <input type="hidden" name="hometabid" value="{$SUPPLIER_WORK_TAB_ID}">
                    <input type="hidden" name="supplierid" id="supplierid-{$SUPPLIER_WORK_TAB_ID}" value="{$SELECTED_SUPPLIER}">
                    <div class="col-md-12 col-sm-12 col-xs-12" style="margin-top: 0">
                        <div class="btn-toolbar" role="toolbar">
                            {* Selector de Proveedor *}
                            <div class="btn-group" style="margin-left: 0.125em!important;margin-right: 2px">
                                <button id="btn-group-supplier-{$SUPPLIER_WORK_TAB_ID}" type="button"
                                        class="btn btn-primary dropdown-toggle"
                                        title="{$APP.LBL_FILTER_BY_SUPPLIER|default:'Filtrar por proveedor'}"
                                        style="font-size: 15px!important;margin-left: 0.1em"
                                        data-toggle="dropdown">
                                    <i class="fa fa-truck" aria-hidden="true"></i>
                                    &nbsp;{$APP.LBL_FILTER_BY_SUPPLIER|default:'Filtrar por proveedor'}&nbsp;
                                    <span class="caret"></span>
                                </button>
                                <ul id="supplier-menu-{$SUPPLIER_WORK_TAB_ID}" class="dropdown-menu scroll-user-menu" role="menu" style="max-height: 300px; overflow-y: auto;">
                                    {if $AVAILABLE_SUPPLIERS neq NULL}
                                        {foreach $AVAILABLE_SUPPLIERS as $supplier}
                                            <li {if $supplier.id eq $SELECTED_SUPPLIER}class="active"{/if}>
                                                <a href="#" title="{$supplier.name}" rel="{$supplier.id}"
                                                   onclick="SupplierWorkUtils.selectedSupplier(event, this, '{$SUPPLIER_WORK_TAB_ID}')">
                                                    <i class="fa fa-building-o"></i>&nbsp;
                                                    {$supplier.name}
                                                </a>
                                            </li>
                                        {/foreach}
                                    {else}
                                        <li class="disabled">
                                            <a href="#" style="color: #999; cursor: default;">
                                                <i class="fa fa-info-circle"></i>&nbsp;{$APP.LBL_NO_SUPPLIERS_REGISTERED|default:'No hay proveedores registrados'}
                                            </a>
                                        </li>
                                    {/if}
                                </ul>
                            </div>
                            {* Selector de Periodo *}
                            <div class="col-lg-3 col-md-3 col-xs-3 btn-group date-time-{$SUPPLIER_WORK_TAB_ID}" style="margin-bottom: 4px; margin-right: 0!important;">
                                <div class="input-group">
                                    <div class="input-group-addon">
                                        <i class="fa fa-clock-o"></i>
                                    </div>
                                    <select id="period-dates-{$SUPPLIER_WORK_TAB_ID}"
                                            onchange="SupplierWorkUtils.selectedPeriod(this, '{$SUPPLIER_WORK_TAB_ID}')"
                                            name="periodtask" class="form-control" title="Seleccionar periodo">
                                        {if $PERIOD_DATES neq NULL}
                                            <option value="">Seleccionar periodo</option>
                                            {foreach $PERIOD_DATES as $period => $periodName}
                                                <option value="{$period}" {if $period eq $PERIOD_SELECTED}selected{/if}>
                                                    {$periodName}
                                                </option>
                                            {/foreach}
                                        {/if}
                                    </select>
                                </div>
                            </div>
                            {* Fecha Desde - oculto por defecto *}
                            <div id="date-from-container-{$SUPPLIER_WORK_TAB_ID}" class="btn-group col-lg-2 col-md-2 col-xs-2 hide" style="margin-bottom: 4px; margin-left: 2px!important;">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input id="start-date-{$SUPPLIER_WORK_TAB_ID}" type="text" name="datestart"
                                           readonly="readonly"
                                           class="form-control supplier-date-{$SUPPLIER_WORK_TAB_ID} date start-date"
                                           value="" style="margin: 0!important;" placeholder="Desde"/>
                                </div>
                            </div>
                            {* Fecha Hasta - oculto por defecto *}
                            <div id="date-to-container-{$SUPPLIER_WORK_TAB_ID}" class="btn-group col-lg-2 col-md-2 col-xs-2 hide" style="margin-bottom: 4px; margin-right: 0!important;">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input id="end-date-{$SUPPLIER_WORK_TAB_ID}" type="text" name="duedate"
                                           readonly="readonly"
                                           class="form-control supplier-date-{$SUPPLIER_WORK_TAB_ID} date end-date"
                                           value="" style="margin: 0!important;" placeholder="Hasta"/>
                                </div>
                            </div>
                            {* Botón de Búsqueda *}
                            <div class="pull-left" style="margin-left: 2px">
                                <div class="btn-group">
                                    <button name="submitSearch" class="btn btn-primary" title="Buscar"
                                            data-pagination-page="{$START_RECORD}"
                                            onclick="SupplierWorkUtils.goToPage(event, this, '{$SUPPLIER_WORK_TAB_ID}')" type="button">
                                        <i class="fa fa-search" aria-hidden="true"></i>
                                    </button>
                                    {* Botón Parte de Trabajo *}
                                    <button type="button" class="btn btn-default" style="margin-left: 2px!important;"
                                            title="Ver Parte de Trabajo del Proveedor"
                                            onclick="SupplierWorkUtils.goToPartWork(event, this, '{$SUPPLIER_WORK_TAB_ID}')">
                                        <i class="fa fa-file-text-o" aria-hidden="true"></i>&nbsp;Parte de trabajo
                                    </button>
                                    {* Botón Gantt *}
                                    <button type="button" class="btn btn-default" style="margin-left: 2px!important;"
                                            title="Ver Diagrama Gantt de Tareas del Proveedor"
                                            onclick="SupplierWorkUtils.showGantt(event, this, '{$SUPPLIER_WORK_TAB_ID}')">
                                        <span class="glyphicon glyphicon-indent-left" aria-hidden="true"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <span id="help-supplier-{$SUPPLIER_WORK_TAB_ID}" class="help-block"
                      style="color: #17a2b8; display: inline-block!important;margin: 0 0.9em">
                    {if $AVAILABLE_SUPPLIERS neq NULL}
                        <b>{$APP.LBL_SUPPLIER|default:'Proveedor'}:</b>&nbsp;<span id="supplier-name-{$SUPPLIER_WORK_TAB_ID}">{$AVAILABLE_SUPPLIERS[0].name}</span>
                    {else}
                        <span id="supplier-name-{$SUPPLIER_WORK_TAB_ID}" style="color: #d9534f;">{$APP.LBL_NO_SUPPLIERS_AVAILABLE|default:'No hay proveedores disponibles'}</span>
                    {/if}
                </span>
            </div>
        </div>
    {/block}
    {block name = "table_header-por_proveedor"}
        {foreach $SUPPLIER_WORK_TABLE_HEADER as $label => $data}
            <th class="{$data.class}"
                style="width:{$data.width}%;vertical-align: top;text-align: {$data.text_align}"
                colspan="{$data.colspan}">
                <div style="display: inline-flex;">
                    <div class="title-overflow">
                        <span>{$label}</span>
                    </div>
                </div>
            </th>
        {/foreach}
    {/block}
    {block name = "table_body-por_proveedor"}
        {if $SUPPLIER_WORK_DATA neq NULL}
            {foreach $SUPPLIER_WORK_DATA as $work}
                <tr>
                    {html_row_table fields=$SUPPLIER_WORK_TABLE_ROW row_data=$work url_avatar=$URL_AVATARS}
                </tr>
            {/foreach}
        {else}
            <tr>
                <td colspan="{$SUPPLIER_WORK_TABLE_HEADER|@count}" class="text-center" style="padding: 20px; color: #d9534f;">
                    {$APP.LBL_NO_TASKS_FOR_SUPPLIER|default:'No hay información disponible para el proveedor'}
                </td>
            </tr>
        {/if}
    {/block}
    {block name="page-data-por_proveedor"}
        <span>Mostrando registros&nbsp;{$START_RECORD} - {$RECORDS_PER_PAGE}&nbsp;de&nbsp;{$SUPPLIER_WORK_TOTAL_ROWS}</span>
    {/block}
    {block name = "pager-por_proveedor"}
        <div id="pager-dv" class="text-right selection-stuff" style="display: block;">
            <ul id="pager-{$SUPPLIER_WORK_TAB_ID}" class="pagination">
                {if $SUPPLIER_WORK_PAGER neq NULL}
                    {$SUPPLIER_WORK_PAGER}
                {else}
                    <li class="Pages"><a href="#"><strong>1</strong></a></li>
                {/if}
            </ul>
        </div>
    {/block}
    {block name="js"}
        <script type="text/javascript" src="themes/centaurus/js/modernizr.custom.js"></script>
        <script type="text/javascript" src="themes/centaurus/js/snap.svg-min.js"></script>
        <script type="text/javascript" src="themes/centaurus/js/classie.js"></script>
        <script type="text/javascript" src="themes/centaurus/js/notificationFx.js"></script>
        <script type="text/javascript" src="include/jquery/jquery-ui-1.10.3.custom.min.js"></script>
        <script type="text/javascript" src="include/js/list-view.js?v=1.1"></script>
        <script type="text/javascript" src="include/js/mass-actions-utils.js?v=1.1"></script>
        <script type="text/javascript" src="modules/instancesdatasharing/data-sharing.js?v=1.0"></script>
        <script type="text/javascript" src="include/js/supplier-work-utils.js?v=1.0"></script>
    {/block}
{/strip}