{strip}
    {assign var="action" value=$smarty.request.action}
    {assign var="moduleRequest" value=$smarty.request.module}
    {assign var='customButtonsPermission' value=','|explode:"index,ListView,DetailView"}
    {*DetailView it will stay out while resolve how to do this *}
    {assign var='emailButtonsPermission' value=','|explode:"emailssent,emailsreceived"}
    {assign var='btnsListView' value=','|explode:"ListView,index"}
    {assign var='statusButtons' value=$STATUS_BUTTONS|custom_serialize}
    {if $MODULE neq 'video'}
        <script type="text/javascript" src="{$DIR_PLAT}modules/{$MODULE}/{$MODULE}.js"></script>
    {/if}
    {if ($MODULE eq 'Calendar') && ($action eq 'ListView')}
        {assign var="MODULELABEL" value='Tareas'}
    {elseif !isset($MODULELABEL)}
        {assign var="MODULELABEL" value=$MODULE|@getTranslatedString:$MODULE}
    {/if}
    {include file='utils/CSSPlatzillaTabs.tpl'}
    <div class="row module-buttons">
        <div class="col-lg-12" style="padding-right: 20px;height: 3.5em!important;">
            {* page title and add record *}
            <div class="pull-left">
                {if (in_array ($action, array ('ListView', 'index')))}
                    {if $AVAILABLE_MODES neq NULL}
                        <table>
                            <tr>
                                <td>
                                    <h1 style="margin-left: 10px">
                                        Gestionando&nbsp;
                                        <a href="index.php?module={$MODULE}&action={$action}"
                                            style="text-decoration: none; font-weight: bold">{$MODULELABEL|module_pluralize}</a>&nbsp;para&nbsp;
                                        {if (!empty ($TOTAL_SYNCS))}
                                            <i class="fa fa-exchange" style="font-size: 0.5em; line-height: 1.1em; margin-left: 0.75em;"
                                                title="Hay {$TOTAL_SYNCS} registro(s) compartido(s)"></i>
                                        {/if}
                                    </h1>
                                </td>
                                <td style="vertical-align: top">
                                    <form action="index.php" id="how-to-use-form" class="form-inline" method="get">
                                        <input type="hidden" name="module" value="{$MODULE}" />
                                        <input type="hidden" name="action" value="index" />
                                        <select class="form-control input-sm pull-left" id="how-to-use" name="howusename"
                                            onchange="ListViewTabUtils.changeUseMode (this)">
                                            {foreach $AVAILABLE_MODES as $modes}
                                                <option value="{$modes->getId()}" {if $HOW_USE_ID eq $modes->getId()} selected{/if}>
                                                    {$modes->getName()}</option>
                                            {/foreach}
                                        </select>
                                    </form>
                                </td>
                            </tr>
                        </table>
                    {else}
                        <h1 style="margin-left: 0">
                            {if ($CAN_CREATE_RECORDS) && $MODULE neq 'daily_report'}
                                <a href="index.php?module={$MODULE}&action=EditView&return_action=DetailView&parenttab={$CATEGORY}{if $smarty.request.frontendsid}&frontendsid={$smarty.request.frontendsid}{/if}&mode=create"
                                    class="">
                                    <i class="fa fa-plus-circle "
                                        title="{$APP.LBL_CREATE_BUTTON_LABEL} {$SINGLE_MOD|@getTranslatedString:$MODULE|module_singularize}"
                                        style="padding-right: 0.2em;"></i></a>
                            {/if}
                            <a href="index.php?module={$MODULE}&action={$action}"
                                style="text-decoration: none; font-weight: bold">{$MODULELABEL|module_pluralize}</a>
                            {if (!empty ($TOTAL_SYNCS))}
                                <i class="fa fa-exchange" style="font-size: 0.5em; line-height: 1.1em; {*margin-left: 0.05em;*}"
                                    title="Hay {$TOTAL_SYNCS} registro(s) compartido(s)"></i>
                            {/if}
                        {/if}
                    </h1>
                {elseif (in_array ($action, array ('DetailView', 'EditView'))) && (!empty ($ENTITY_IDENTIFIER_VALUE))}
                    {if (!$IS_MODAL)}
                        <h1>
                            {* DetailView Header label *}
                            {if $MODULE eq 'daily_report'}
                                {* Botón especial para daily_report con menú de fechas *}
                                {math equation= rand() assign= "idDailyBtn"}
                                <div class="btn-group" style="display: inline-block; vertical-align: middle;">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"
                                        style="text-decoration: none; background-color: #337ab7; color: white; padding: 2px 10px; border-radius: 40%; margin-right: 6px; font-size: 0.85em;">
                                        <i class="fa fa-plus" title="Crear informe diario" style="color: white;"></i>&nbsp;<span
                                            class="caret" style="color: white;"></span>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-left" role="menu">
                                        <li>
                                            <a href="index.php?module=daily_report&action=EditView&return_module=daily_report&return_action=DetailView&parenttab=&afp={$REPORT_TODAY}"
                                                data-date="{$HEADER_TODAY}"
                                                onclick="DailyReportNavBar.goReportDate(this, '{$idDailyBtn}', event)">Para hoy</a>
                                        </li>
                                        <li>
                                            <a href="index.php?module=daily_report&action=EditView&return_module=daily_report&return_action=DetailView&parenttab=&afp={$REPORT_YESTERDAY}"
                                                data-date="{$HEADER_YESTERDAY}"
                                                onclick="DailyReportNavBar.goReportDate(this, '{$idDailyBtn}', event)">Para ayer</a>
                                        </li>
                                        <li>
                                            <a id="other-date-{$idDailyBtn}" href="#" rel="{$CURRENT_USER_ID}" data-date=""
                                                onclick="DailyReportNavBar.createReportDate(this, '{$idDailyBtn}', event)">Otra
                                                fecha</a>
                                        </li>
                                        <li id="other-date-input-{$idDailyBtn}" class="hide other-date">
                                            <input rel="{$CURRENT_USER_ID}"
                                                class="form-control pull-right input-readonly b-left col-md-3"
                                                placeholder="Seleccione fecha"
                                                onclick="DailyReportNavBar.createReportDate(this, '{$idDailyBtn}', event)" value=""
                                                type="text" id="report-date-{$idDailyBtn}" readonly="readonly">
                                        </li>
                                    </ul>
                                    <input type="hidden" id="reported_day-{$idDailyBtn}"
                                        value="{if $REPORTED_DAYS neq NULL}{$REPORTED_DAYS}{/if}">
                                </div>
                            {else}
                                <a href="index.php?module={$MODULE}&action=EditView&return_action=DetailView&parenttab={$CATEGORY}{if $smarty.request.frontendsid}&frontendsid={$smarty.request.frontendsid}{/if}&mode=create"
                                    class="{*btn btn-success*}" style="margin-left:.5em; margin-right: 0;">
                                    <i class="fa fa-plus-circle "
                                        title="{$APP.LBL_CREATE_BUTTON_LABEL} {$SINGLE_MOD|@getTranslatedString:$MODULE|module_singularize}"
                                        style="padding-right: 0.2em;"></i></a>
                            {/if}
                            <a href="index.php?module={$MODULE}&action=ListView&parenttab={$CATEGORY}"
                                title="Listado de {$SINGLE_MOD|@getTranslatedString:$MODULE|module_pluralize}"
                                style="text-decoration: none"><strong>{$SINGLE_MOD|@getTranslatedString:$MODULE|module_singularize}</strong><span
                                    style="color: #777777;font-size: 0.8em;font-weight: bold">&nbsp;&gt;</span></a>
                            <small {if $ENTITY_IDENTIFIER_VALUE|strlen gte 38}class="protip"
                                data-pt-title="{$ENTITY_IDENTIFIER_VALUE}" {/if} style="font-weight: bold">
                                {$ENTITY_IDENTIFIER_VALUE|truncate:38}</small>
                            {if (!empty ($TOTAL_SYNCS))}
                                <i class="fa fa-exchange" style="font-size: 0.5em; line-height: 1.1em; margin-left: 0.75em;"
                                    title="Compartido"></i>
                            {/if}
                        </h1>
                    {/if}
                {elseif (in_array ($action, array ('CallRelatedList', 'RecordHistory')) ||($SELECTED_TAB eq 'control_panel')) && (!empty ($ENTITY_IDENTIFIER_VALUE))}
                    {if (!$IS_MODAL)}
                        <h1>
                            <a href="index.php?module={$MODULE}&action=ListView&parenttab={$CATEGORY}"
                                title="Listado de {$SINGLE_MOD|@getTranslatedString:$MODULE}"
                                style="text-decoration: none">{$SINGLE_MOD|@getTranslatedString:$MODULE}<span
                                    style="color: #000">&nbsp;&gt;</span></a>
                            <small {if $ENTITY_IDENTIFIER_VALUE|strlen gte 28}class="protip"
                                data-pt-title="{$ENTITY_IDENTIFIER_VALUE}" {/if} style="font-weight: bold">
                                {$ENTITY_IDENTIFIER_VALUE|truncate:28}</small> <!-- wa 13/3/21 -->
                            {if (!empty ($TOTAL_SYNCS))}
                                <i class="fa fa-exchange" style="font-size: 0.5em; line-height: 1.1em; margin-left: 0.75em;"
                                    title="Compartido"></i>
                            {/if}
                        </h1>
                    {/if}
                {else}
                    <h1>
                        <a
                            href="index.php?module={$MODULE}&action=ListView&parenttab={$CATEGORY}">{$SINGLE_MOD|@getTranslatedString:$MODULE}</a>
                        {if (!empty ($TOTAL_SYNCS))}
                            <i class="fa fa-exchange" style="font-size: 0.5em; line-height: 1.1em; margin-left: 0.75em;"
                                title="Compartido"></i>
                        {/if}
                    </h1>
                {/if}
            </div>
            {* /page title and add record *}
            {* button group *}
            <div class="pull-right row" style="display: inline">
                {* Task create - no display for now- *}
                {if (($action eq 'DetailView' || $action eq 'RecordHistory' || $action eq 'CallRelatedList')) && (false)}
                    <button class="btn btn-info" style="margin-left: 5px;"
                        onclick="CalendarWizard.open ('{$MODULE}', '{$ID}', '{$ENTITY_IDENTIFIER_VALUE}')">
                        <i class="fa fa-plus fa-lg" title="Crear Tarea" style="padding-right: 0.2em;"></i>Crear Tarea
                    </button>
                {/if}
                {* /Task create - no display for now- *}
                {* modal control buttons*}
                {if $IS_MODAL} {* yes, it´s  modal page on ListView *}
                    {* Información asociada *}
                    {if ($action eq 'DetailView')}
                        <a class="btn btn-info" title="{$APP.LBL_REGISTER}"
                            href="index.php?action=DetailView&module={$MODULE}&record={$ID}&tab=detail"
                            style="margin-left:.5em; margin-right: 0;"
                            {if ($SELECTED_TAB neq 'detail')}onclick="ModalDetailViewUtils.getRelatedList (this, event)" {/if}><i
                                class="fa fa-home"></i>&nbsp;Ver detalle</a>
                    {elseif (($action eq 'CallRelatedList') || ($action eq 'RecordHistory'))}
                        <a class="btn btn-info" href="index.php?action=DetailView&module={$MODULE}&record={$ID}&tab=detail"
                            style="margin-left:.5em; margin-right: 0;" onclick="ModalDetailViewUtils.getRelatedList (this, event)"
                            title="Información asociada"><i class="fa fa-home"></i>&nbsp;Ver detalle</a>
                    {/if}{* /Información asociada *}

                    {* is hidden all buttons *}
                    {if !$HIDDEN_GNL_BUTTON}
                        {if ($EDIT_PERMISSION eq 'yes')}
                            <a href="javascript:void(0)"
                                onclick="DetailView.return_module.value='{$MODULE}'; DetailView.return_action.value='DetailView'; DetailView.return_id.value='{$ID}';DetailView.module.value='{$MODULE}';submitFormForAction('DetailView','EditView');"
                                class="btn btn-default" style="margin-left:.5em; margin-right: 0;">
                                <span class="fa fa-pencil"></span> {$APP.LBL_EDIT_BUTTON_LABEL}
                            </a>
                        {/if}
                        {if ($DELETE eq 'permitted')}
                            {* Delete button *}
                            <a href="javascript:void(0)" id="deleteButton" tagModule="{$MODULE}"
                                onclick="DetailView.return_module.value='{$MODULE}'; DetailView.return_action.value='index'; var confirmMsg = '{$APP.NTC_DELETE_CONFIRMATION}'; submitFormForActionWithConfirmation('DetailView', 'Delete', confirmMsg);"
                                class="btn btn-danger" style="margin-left:.5em; margin-right: 0;">
                                <span class="fa fa-trash-o"></span> {$APP.LBL_DELETE_BUTTON_LABEL}
                            </a>
                        {/if}
                    {/if}
                    {*/ is hidden all buttons *}
                    {* /multi function button *}
                    <div class="btn-group" style="margin-left: 5px;">
                        {if ($MODULE eq 'Calendar')}
                            <a href="index.php?module=Calendar&action=EditView&activity_mode=Events&return_module=Calendar&return_action=ListView"
                                class="btn btn-info" style="margin-left: 5px;">Crear Tarea</a>
                            <a href="index.php?module=Calendar&action=index" class="btn btn-warning" style="margin-left: 5px;">Ir al
                                calendario</a>
                        {elseif (($action eq 'DetailView' || $action eq 'RecordHistory' || $action eq 'CallRelatedList'))}
                            <button type="button" class="btn btn-primary btn-xs dropdown-toggle dropdown-toggle-ext"
                                data-toggle="dropdown">
                                <i class="fa">&nbsp;<i class="fa fa-bar-chart-o"></i>&nbsp;</i>
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu pull-right" role="menu">
                                <li>
                                    <a href="index.php?action=RecordHistory&module=historymanager&record={$ID}&parenttab={$CATEGORY}&formodule={$MODULE}&editpermission={$EDIT_PERMISSION}"
                                        onclick="ModalDetailViewUtils.getRecordHistory (this, event)" title="Histórico de Cambios">
                                        <i class="fa fa-archive"></i>&nbsp;Histórico de Cambios</a>
                                </li>
                                {if $GRAPHS neq NULL}
                                    <li{if ($SELECTED_TAB eq 'control_panel')} class="active" {/if}>
                                        <a title="{$APP.LBL_CONTROL_PANEL}"
                                            href="index.php?action=DetailView&module={$MODULE}&record={$ID}&tab=control_panel"
                                            onclick="ModalDetailViewUtils.getRecordHistory (this, event)">
                                            <i class="fa fa-bar-chart-o"></i>Graficos favoritos</a>
                                        </li>
                                    {/if}
                            </ul>
                        {/if}
                    </div>
                    {* /multi function button *}
                {else} {* no, it is not  modal page on ListView, it is a detailView page *}
                    {* The module has permission to create record  this button had been moved on left side*}
                    {if ($CAN_CREATE_RECORDS) && false}
                        {* Button create new register, it was moved to header label pag on detailView *}
                        {if (in_array($MODULE, $emailButtonsPermission) && (in_array($action, $btnsListView)))}
                            <a href="index.php?module={$MODULE}&action=EditView&return_action=DetailView&parenttab={$CATEGORY}{if $smarty.request.frontendsid}&frontendsid={$smarty.request.frontendsid}{/if}&mode=create"
                                class="btn btn-success" style="margin-left:.5em; margin-right: 0;">
                                <i class="fa fa-plus fa-lg"
                                    title="{$APP.LBL_CREATE_BUTTON_LABEL} {$SINGLE_MOD|getTranslatedString:$MODULE}"
                                    style="padding-right: 0.2em;"></i> {$APP.LBL_CREATE_BUTTON_LABEL}
                                {$SINGLE_MOD|getTranslatedString:$MODULE}
                            </a>
                        {elseif (in_array($action, $btnsListView))}
                            <a href="index.php?module={$MODULE}&action=EditView&return_action=DetailView&parenttab={$CATEGORY}{if $smarty.request.frontendsid}&frontendsid={$smarty.request.frontendsid}{/if}&mode=create"
                                class="btn btn-success" style="margin-left:.5em; margin-right: 0;">
                                <i class="fa fa-plus fa-lg"
                                    title="{$APP.LBL_CREATE_BUTTON_LABEL} {$SINGLE_MOD|getTranslatedString:$MODULE}"
                                    style="padding-right: 0.2em;"></i> {$APP.LBL_CREATE_BUTTON_LABEL}
                                {$SINGLE_MOD|getTranslatedString:$MODULE}
                            </a>
                        {/if}
                    {/if}{* /The module has permission to create record *}
                    {* config button on listView and others*}
                    {if (in_array ($action, $btnsListView)) && ($MODULE neq 'Calendar')}
                        <ul class="nav nav-tabs nav-platzilla" style="margin-top: 0!important;">
                            <li class="active">
                                <a id="listview-view-{$idListView}" data-toggle="tab" href="#ListViewContents" title="Vistas"
                                    onclick="ListViewTabUtils.activeViewTab (event, this)">Vistas</a>
                            </li>
                            {if ($STATUS_BUTTONS['report']) || $STATUS_BUTTONS['graphic']}
                                <li>
                                    <!-- {$LIST_VIEW_TAB} {$STATUS_BUTTONS['report']}  {$STATUS_BUTTONS['graphic']}-->
                                    <a id="listview-metrics-{$idListView}" data-toggle="tab"
                                        {if  ($STATUS_BUTTONS['graphic'] && ($LIST_VIEW_TAB eq 'graphic' || $LIST_VIEW_TAB eq 'list' || empty($LIST_VIEW_TAB eq 'list')))}
                                            href="#LIST-VIEW-GRAPHIC"
                                        {elseif ($STATUS_BUTTONS['report'] && ($LIST_VIEW_TAB eq 'report') || $LIST_VIEW_TAB eq 'list' || empty($LIST_VIEW_TAB eq 'list'))}
                                        href="#LIST-VIEW-REPORT" {/if} title="Métricas"
                                        onclick="ListViewTabUtils.activeMetricsTab (event, this)">Métricas</a>
                                </li>
                            {/if}
                            <li style="display: none">
                                <a data-toggle="tab" href="#listview-history-{$idListView}">Historico</a>
                            </li>
                            <li>
                                {* <div class="btn-group" id="congig-btn-tab"><!-- tuerca wa -->
                                    <button type="button"
                                            style="margin-left: .5em"
                                            class="btn btn-primary btn-xs dropdown-toggle dropdown-toggle-ext"
                                            data-toggle="dropdown">
                                        &nbsp;<i class="fa fa-cog"></i>&nbsp;
                                        <span class="caret"></span>
                                    </button> *}
                                <a id="listview-option-{$idListView}" data-toggle="dropdown" href="#" style="">
                                    <i class="fa fa-cog"></i>&nbsp;
                                    <span class="caret"></span></a>
                                <ul class="dropdown-menu pull-right" role="menu">
                                    {if ($IS_ADMIN)}
                                        <li>
                                            <a
                                                href="index.php?module=Settings&action=LayoutBlockList&parenttab=Settings&formodule={$MODULE}&return_module={$MODULE}">
                                                <i class="fa fa-cog"></i>{$APP.LBL_FIELDS_LAYOUT} {$MODULE|getTranslatedString:$MODULE}
                                            </a>
                                        </li>
                                    {/if}
                                    {*
                                        <li>
                                            <!-- wa 1/3  -->
                                            <a href="index.php?module=notifications&action=NotificationsModal&Ajax=true&notificationId=0&notificationName=AUTOMATED_ACTIVITIES_FIRST_TIME&record={$ID}&formodule={$MODULE}"
                                               data-process="NO" data-toggle="lightbox" data-max-width="600">
                                                <i class="fa fa-cog"></i>Tareas automatizadas
                                            </a>
                                        </li> *}

                                    {* select a default view by module*}
                                    {if ($statusButtons neq 'list') && ($AVAILABLE_MODES eq NULL)}
                                        <li>
                                            <a href="index.php?module={$MODULE}&action=AjaxListViewUtils&Ajax=true&function=SET-DEFAULT-VIEW&record={$ID}&buttons={$statusButtons}"
                                                data-process="NO" data-toggle="lightbox" data-max-width="600"
                                                data-title="Vistas del módulo">
                                                <i class="fa fa-cog"></i>vistas del módulo
                                            </a>
                                        </li>
                                    {/if}
                                    {* select a default view by module*}
                                    {* import records *}
                                    {if $CHECK.Import eq 'yes' && $MODULE neq 'Documents' && $MODULE neq 'Reports'}
                                        <li>
                                            <a
                                                href="index.php?module={$MODULE}&action=Import&step=1&mode=&return_module={$MODULE}&return_action=index&parenttab={$CATEGORY}">
                                                <i class="fa fa-cog"></i>Importar registros
                                            </a>
                                        </li>
                                    {/if}
                                    {* export records *}
                                </ul>
                                {* </div> *}
                            </li>
                        </ul>

                    {/if} {* /config button on listView and others*}
                    {* share button *}
                    {if $HIDDEN_GNL_BUTTON}
                        <div class="btn-group">
                            <ul class="nav nav-tabs nav-platzilla" id="detal-view-group-tab" data-tab="{$SELECTED_TAB}">
                                {if (((in_array ($action, $customButtonsPermission)) || (($action eq 'DetailView') && ($IS_MODAL))) && !in_array($MODULE, $btnPrinter))}
                                    {include file='customButtonsDetailView.tpl'}
                                {/if}
                                {* Related list *}
                                {if ($action eq 'DetailView')}
                                    <li {if ($SELECTED_TAB eq NULL) || ($SELECTED_TAB eq '')}class="active" {/if}>
                                        <a data-toggle="tab" href="#tab-detail-{$idDetailView}" style=" font-size: 15px!important;"
                                            id="detail-view-btn-tab" title="Detalle del registro"
                                            onclick="DetailViewTabUtils.activeDetailViewTab (event)">
                                            Información
                                        </a>
                                    </li>
                                    {* works view*}
                                    {if  $MODULE eq 'proyectos'}
                                        <li {if ($SELECTED_TAB eq 'jobs-list')}class="active" {/if}>
                                            <a data-toggle="tab" href="#tab-jobs-list-{$idDetailView}" style=" font-size: 15px!important;"
                                                id="jobs-list-btn-tab" title="Tareas"
                                                onclick="DetailViewTabUtils.activeJobTab (event, '{$MODULE}','{$idDetailView}', '{$ID}')">
                                                Vista de trabajos
                                            </a>
                                        </li>
                                    {/if}
                                    {* task view*}
                                    {if $VIEW_TASK neq 'HIDDEN'}
                                        <li {if ($SELECTED_TAB eq 'task-list')}class="active" {/if}>
                                            <a data-toggle="tab" href="#tab-task-list-{$idDetailView}" style=" font-size: 15px!important;"
                                                id="task-list-btn-tab" title="Tareas"
                                                onclick="DetailViewTabUtils.activeTaskTab (event, '{$MODULE}','{$idDetailView}', '{$ID}')">
                                                {if $MODULE eq 'orden_de_trabajo'}Vista de tareas
                                                {else}Acciones
                                                {/if}
                                            </a>
                                        </li>
                                    {/if}
                                    {* task view*}
                                    {if $IS_REL_LIST && is_array($IS_REL_LIST) && count($IS_REL_LIST) > 0}
                                        <li {if ($SELECTED_TAB eq 'related_list')}class="active" {/if}>
                                            <a data-toggle="tab" href="#tab-related-list-{$idDetailView}"
                                                style=" font-size: 15px!important;" id="related-list-btn-tab"
                                                title="Información relacionada" onclick="DetailViewTabUtils.activeRelatedListTab (event)">
                                                Relaciones
                                            </a>
                                        </li>
                                    {/if}
                                    {if $GRAPHS neq NULL}
                                        <li {if ($SELECTED_TAB eq 'control_panel')}class="active" {/if}>
                                            <a data-toggle="tab" href="#tab-control_panel-{$idDetailView}" style="font-size: 15px"
                                                id="graphic-view-btn-tab" title="{$APP.LBL_CONTROL_PANEL}"
                                                onclick="DetailViewTabUtils.activeGraphicTab (event)">
                                                Métricas
                                            </a>
                                        </li>
                                    {/if}
                                    <li>
                                        <a href="index.php?action=RecordHistory&module=historymanager&record={$ID}&parenttab={$CATEGORY}&formodule={$MODULE}&editpermission={$EDIT_PERMISSION}"
                                            style="font-size: 15px" id="history-view-btn-tab"
                                            onclick="DetailViewTabUtils.activeHistoryTab (event)"
                                            title="Histórico de Cambios">{*<i class="fa fa-archive"></i>Histórico de Cambios*}
                                            Histórico
                                        </a>
                                    </li>
                                {/if}
                                {* / Related list *}
                                {if ($MODULE neq 'Calendar') && false}
                                    <div class="btn-group" id="congig-btn-tab">
                                        <button type="button" style="font-size: 14px"
                                            class="btn btn-default btn-xs dropdown-toggle dropdown-toggle-ext" data-toggle="dropdown">
                                            &nbsp;<i class="fa fa-cog"></i>&nbsp;
                                            <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu pull-right" role="menu">
                                            {if ($IS_ADMIN)}
                                                <li>
                                                    <a
                                                        href="index.php?module=Settings&action=LayoutBlockList&parenttab=Settings&formodule={$MODULE}&return_module={$MODULE}">
                                                        <i class="fa fa-cog"></i>{$APP.LBL_FIELDS_LAYOUT}
                                                        {$MODULE|getTranslatedString:$MODULE}
                                                    </a>
                                                </li>
                                            {/if}
                                            {*
                                        <li><!-- wa 2/3  -->
                                            <a href="index.php?module=notifications&action=NotificationsModal&Ajax=true&notificationId=0&notificationName=AUTOMATED_ACTIVITIES_FIRST_TIME&record={$ID}&formodule={$MODULE}"
                                               data-process="NO" data-toggle="lightbox" data-max-width="600">
                                                <i class="fa fa-cog"></i>Tareas automatizadas
                                            </a>
                                        </li>
                                        *}
                                        </ul>
                                    </div>
                                {/if}
                            </ul>
                        </div>
                        {* expediente button moved to card-header in StandardDetailView.tpl *}
                    {else}
                        {if ($action eq 'DetailView')}
                            {* share register *}
                            <a class="btn btn-success" style="margin-left:.5em; margin-right:.5em; ;" href="javascript:;"
                                onclick="DataSharingUtils.openSharingModal ('{$MODULE}', '{$ID}');"><i
                                    class="fa fa-share"></i>&nbsp;Compartir</a>
                        {elseif (($action eq 'RecordHistory'))}
                            <ul class="nav nav-tabs nav-platzilla" id="detal-view-group-tab" style=" margin-right: 0!important;">
                                {* Related list *}
                                <li>
                                    <a href="index.php?action=DetailView&module={$MODULE}&record={$ID}"
                                        style=" font-size: 15px!important;" id="detail-view-btn-tab" title="Detalle del registro">
                                        Información
                                    </a>
                                </li>
                                {if $VIEW_TASK neq 'HIDDEN'}
                                    <li>
                                        <a href="index.php?action=DetailView&module={$MODULE}&record={$ID}&tab=task-list"
                                            style=" font-size: 15px!important;" id="task-list-btn-tab" title="Tareas">
                                            Tareas
                                        </a>
                                    </li>
                                {/if}
                                {if $IS_REL_LIST && is_array($IS_REL_LIST) && count($IS_REL_LIST) > 0}
                                    <li>
                                        <a href="index.php?action=DetailView&module={$MODULE}&record={$ID}&tab=related_list"
                                            style=" font-size: 15px!important;" id="related-list-btn-tab" title="Información relacionada">
                                            Relaciones
                                        </a>
                                    </li>
                                {/if}
                                {if $GRAPHS neq NULL}
                                    <li>
                                        <a href="index.php?action=DetailView&module={$MODULE}&record={$ID}&tab=control_panel"
                                            style="font-size: 15px" id="graphic-view-btn-tab" title="{$APP.LBL_CONTROL_PANEL}">
                                            Métricas
                                        </a>
                                    </li>
                                {/if}
                                <li class="active">
                                    <a href="#" style=" font-size: 15px!important;" id="history-view-btn-tab"
                                        title="Histórico de cambios">
                                        {*<i class="fa fa-home"></i>&nbsp;Detalle del registro*}
                                        Histórico
                                    </a>
                                </li>
                                {if ($MODULE neq 'Calendar') && false}
                                    <div class="btn-group" id="congig-btn-tab">
                                        <button type="button" style="font-size: 14px"
                                            class="btn btn-default btn-xs dropdown-toggle dropdown-toggle-ext" data-toggle="dropdown">
                                            &nbsp;<i class="fa fa-cog"></i>&nbsp;
                                            <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu pull-right" role="menu">
                                            {if ($IS_ADMIN)}
                                                <li>
                                                    <a
                                                        href="index.php?module=Settings&action=LayoutBlockList&parenttab=Settings&formodule={$MODULE}&return_module={$MODULE}">
                                                        <i class="fa fa-cog"></i>{$APP.LBL_FIELDS_LAYOUT} {$MODULE|getTranslatedString:$MODULE}
                                                    </a>
                                                </li>
                                            {/if}
                                            {*
                                            <li><!-- wa 3/3  -->
                                                <a href="index.php?module=notifications&action=NotificationsModal&Ajax=true&notificationId=0&notificationName=AUTOMATED_ACTIVITIES_FIRST_TIME&record={$ID}&formodule={$MODULE}"
                                                   data-process="NO" data-toggle="lightbox" data-max-width="600">
                                                    <i class="fa fa-cog"></i>Tareas automatizadas
                                                </a>
                                            </li>
                                            *}
                                        </ul>
                                    </div>
                                {/if}
                            </ul>
                            {*</div> *}
                        {/if}{* share button *}
                        {* duplicate  button *}
                    {*if ($EDIT_DUPLICATE eq 'permitted') && ($MODULE neq 'Documents') && ($CAN_CREATE_RECORDS)}
                            <a class="btn btn-warning" href="javascript:void(0)"
                               onclick="DetailView.return_module.value='{$MODULE}'; DetailView.return_action.value='DetailView'; DetailView.isDuplicate.value='true';DetailView.module.value='{$MODULE}'; submitFormForAction('DetailView','EditView');">
                                <i class="fa fa-files-o"></i> {$APP.LBL_DUPLICATE_BUTTON_LABEL}
                            </a>
                        {/if} duplicate  button *}
                {/if}
            {/if}
            {* /modal control buttons *}

            {* /multi function button *}
            <div class="btn-group" style="margin-left: 5px;">
                {if ($MODULE eq 'Calendar')}
                    <a href="index.php?module=Calendar&action=EditView&activity_mode=Events&return_module=Calendar&return_action=ListView"
                        class="btn btn-info" style="margin-left: 5px;">Crear Tarea</a>
                    <a href="index.php?module=Calendar&action=index" class="btn btn-warning" style="margin-left: 5px;">Ir al
                        calendario</a>
                {/if}
            </div>
        </div>
        {* /button group *}
    </div>
    {* DeailView Widget *}
    {if ($DETAILWIDGET|count) > 0}
        <div class="col-xs-12">
            {foreach $DETAILWIDGET as $widget}
                <div class="col-lg-3">
                    <div class="main-box">
                        <div class="col-md-10">
                            <div class="main-box infographic-box">
                                <i class="{$widget.icono} {$widget.color}"></i>
                                <span class="value {$widget.colorValue}"
                                    style="text-align: left;">{if $widget.valor.variablegraficar eq NULL}0{else}{$widget.valor.variablegraficar}{/if}</span>
                                <span class="headline" style="text-align: left; font-size: 1em;">{$widget.texto}</span>
                                <a class="md-trigger table-link" title="Editar"
                                    href="index.php?module=admin_widgets&action=EditWidgets&record={$widget.widgetid}">
                                    <span class="subinfo" style="text-align: right;">
                                        <span class="fa fa-cog {$widget.colorValue}" style="right: auto"></span>
                                    </span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            {/foreach}
        </div>{* /DeailView Widget *}
    {/if}
</div>
{/strip}