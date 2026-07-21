{extends file="platzilla_layout.tpl"}

{block name="action_css"}
    <link rel="stylesheet" type="text/css" href="themes/centaurus/css/compiled/emojionearea.min.css" />
    <link rel="stylesheet" type="text/css" href="themes/centaurus/css/compiled/detailview.css?v1.0.0" />
    <link rel="stylesheet" type="text/css" href="themes/centaurus/css/compiled/platzilla-detailview.css" />
    <link rel="stylesheet" type="text/css" href="themes/centaurus/css/detailview-custom.css" />
    <link rel="stylesheet" href="themes/centaurus/css/compiled/pipeline.min.css">
    <link type="text/css" rel="stylesheet" href="modules/instancesdatasharing/instancesdatasharing.css" />
    <link type="text/css" rel="stylesheet" href="themes/centaurus/css/bootstrap/bootstrap-editable.css" />
    <link rel="stylesheet" type="text/css" href="themes/centaurus/css/bootstrap/nifty-component.css" />
    <link rel="stylesheet" type="text/css" href="themes/centaurus/css/bootstrap/bootstrap-cards.css" />
    <link type="text/css" rel="stylesheet" href="themes/centaurus/css/messageBox.min.css" />
    <style>
        #card-view-container {
            margin-top: 20px;
            /* max-height: 650px;
                    overflow-y: auto;
                    padding:    0 20px !important;
                    scrollbar-width: thin; */
        }

        #card-view-register-container {
            /*margin-top: 20px;*/
            /*max-height: 110%;*/
            overflow-y: auto;
            padding: 0 !important;
            overflow-x: hidden;
            z-index: 10000;
            scrollbar-width: thin;
            scrollbar-color: #e0dfde #F9F8F7;
            /*thumb background*/
        }

        .btn-circle.btn-xl {
            width: 70px;
            height: 70px;
            padding: 10px 16px;
            border-radius: 35px;
            font-size: 24px;
            line-height: 1.33;
        }

        .btn-circle {
            width: 30px;
            height: 30px;
            padding: 6px 0px;
            border-radius: 15px;
            text-align: center;
            font-size: 12px;
            line-height: 1.42857;
        }

        .platzilla-card-header {
            background-color: #FFFFFF;
            border-bottom-color: #FFFFFF;
            font-family: helvetica, arial, sans-serif;
            font-size: 1.5em
        }

        .platzilla-card-header p {
            font-size: 1.05em;
            margin-left: 0 !important;
            padding-left: 0 !important;
        }

        .rounded {
            border-radius: .75rem !important
        }

        @media (min-width: 1280px) and (max-width: 1300px) {
            .platzilla-card-header p {
                font-size: 0.85em;
                margin-left: 0 !important;
                padding-left: 0 !important;
            }
        }

        @media (min-width: 1400px) and (max-width: 1580px) {
            .platzilla-card-header p {
                font-size: 0.9em;
                margin-left: 0 !important;
                padding-left: 0 !important;
            }
        }

        @media (min-width: 1600px) and (max-width: 1800px) {
            .platzilla-card-header p {
                font-size: 1.05em;
                margin-left: 0 !important;
                padding-left: 0 !important;
            }
        }
    </style>
{/block}

{block name="action_js"}
    <!-- SMARTY_RENDER: Smarty/templates/centaurus/Detailview.tpl -->
    {if !$DETAILVIEW_SCRIPT_INCLUDED}
        <script src="themes/centaurus/js/detailview.js"></script>
        {assign var="DETAILVIEW_SCRIPT_INCLUDED" value=true}
    {/if}
    <script type="text/javascript" src="include/ckeditor/ckeditor.js"></script>
    <script type="text/javascript" src="include/js/dtlviewajax.js"></script>
    <script type="text/javascript" src="include/js/attachments-utils.js"></script>
    <script type="text/javascript" src="themes/centaurus/js/compiled/emojionearea.min.js"></script>
    <script type="text/javascript" src="themes/centaurus/js/bootstrap-typeahead.min.js"></script>
    <script type="text/javascript" src="themes/centaurus/js/comments.js"></script>
    <script type="text/javascript" src="themes/centaurus/js/jquery.messagebox.min.js"></script>
    <script type="text/javascript" src="include/js/general.js"></script>
    <script>
        function loadScriptIfNotPresent(src) {
            if (!document.querySelector('script[src="' + src + '"]')) {
                var s = document.createElement('script');
                s.src = src;
                s.type = 'text/javascript';
                document.head.appendChild(s);
            }
        }
        // loadScriptIfNotPresent('include/ckeditor/ckeditor.js');
    </script>
    {if !isset($PROCESS_CASES_UTILS_LOADED) || !$PROCESS_CASES_UTILS_LOADED}
        <script type="text/javascript" src="themes/centaurus/js/process_cases_utils.js"></script>
        {assign var="PROCESS_CASES_UTILS_LOADED" value=true}
    {/if}

    <!-- Inicialización de Tooltip para campos de área de texto -->
    <script type="text/javascript">
        (function($) {
            'use strict';

            var hideTimeout = null;
            var isOverTooltip = false;

            function initTextAreaPopovers() {
                var elements = $('.protip[data-pt-title]');

                elements.each(function() {
                    var $element = $(this);

                    // Evitar inicialización duplicada
                    if ($element.data('protip-initialized')) {
                        return;
                    }
                    $element.data('protip-initialized', true);

                    var content = $element.attr('data-pt-title');
                    var width = $element.attr('data-pt-width') || 400;

                    if (content && content.length > 0) {
                        $element.on('mouseenter.protip', function(e) {
                            // No mostrar si el editor inline está abierto
                            if ($(this).closest('.editable-open').length > 0) {
                                return;
                            }

                            // Cancelar cualquier timeout de ocultamiento pendiente
                            if (hideTimeout) {
                                clearTimeout(hideTimeout);
                                hideTimeout = null;
                            }

                            var $tooltip = $('#protip-tooltip');
                            if ($tooltip.length === 0) {
                                $tooltip = $('<div id="protip-tooltip"></div>').appendTo('body');

                                // Eventos para mantener el tooltip visible cuando el cursor está sobre él
                                $tooltip.on('mouseenter', function() {
                                    isOverTooltip = true;
                                    if (hideTimeout) {
                                        clearTimeout(hideTimeout);
                                        hideTimeout = null;
                                    }
                                }).on('mouseleave', function() {
                                    isOverTooltip = false;
                                    hideTimeout = setTimeout(function() {
                                        $('#protip-tooltip').hide();
                                    }, 100);
                                });
                            }

                            $tooltip.html(content.replace(/\n/g, '<br>'))
                                .css({
                                    'position': 'absolute',
                                    'max-width': width + 'px',
                                    'max-height': '350px',
                                    'overflow-y': 'auto',
                                    'background': '#333',
                                    'color': '#fff',
                                    'padding': '12px 16px',
                                    'border-radius': '6px',
                                    'font-size': '13px',
                                    'line-height': '1.5',
                                    'z-index': '99999',
                                    'box-shadow': '0 4px 12px rgba(0,0,0,0.3)',
                                    'white-space': 'pre-wrap',
                                    'word-wrap': 'break-word',
                                    'scrollbar-width': 'thin',
                                    'scrollbar-color': '#666 #333'
                                })
                                .show();

                            var offset = $(this).offset();
                            var tooltipHeight = $tooltip.outerHeight();
                            var tooltipWidth = $tooltip.outerWidth();
                            var top = offset.top - tooltipHeight - 5;
                            var left = offset.left;

                            // Si no cabe arriba, mostrar abajo
                            if (top < $(window).scrollTop()) {
                                top = offset.top + $(this).outerHeight() + 5;
                            }
                            if (left + tooltipWidth > $(window).width()) {
                                left = $(window).width() - tooltipWidth - 10;
                            }

                            $tooltip.css({ top: top, left: left });

                        }).on('mouseleave.protip', function() {
                            // Delay para permitir mover el cursor al tooltip
                            hideTimeout = setTimeout(function() {
                                if (!isOverTooltip) {
                                    $('#protip-tooltip').hide();
                                }
                            }, 150);
                        });
                    }
                });
            }

            $(document).ready(function() {
                setTimeout(initTextAreaPopovers, 500);
            });

            $(document).ajaxComplete(function() {
                setTimeout(initTextAreaPopovers, 300);
            });

        })(jQuery);
    </script>

{/block}

{block name="action_content"}

    {* SOLO contenido específico de la vista, sin extends ni estructura global *}

    {* Display ALERT notifications first *}
    {if (!empty ($ALERTS))}
        {foreach $ALERTS as $index => $alert}
            {if $index >= 1}
                {$alert->getContents ()|regex_replace:"/__ID__/":$alert->getId ()|regex_replace:"/__COLLAPSE_IN__/":'collapse'|regex_replace:"/__HIDDEN__/":'hidden'|unescape:"html"}
            {else}
                {$alert->getContents ()|regex_replace:"/__ID__/":$alert->getId ()|regex_replace:"/__COLLAPSE_IN__/":'collapse'|regex_replace:"/__HIDDEN__/":''|unescape:"html"}
            {/if}
        {/foreach}
    {/if}
    {* Display NOTIFY notifications *}
    {if (!empty ($NOTIFICATIONS))}
        <div class="notifications-container" style="position: relative; z-index: 1050;">
            {foreach $NOTIFICATIONS as $index => $notification}
                <div style="margin-bottom: 10px;">
                    {$notification->getContents ()|regex_replace:"/__ID__/":$notification->getId ()|regex_replace:"/__COLLAPSE_IN__/":'collapse'|regex_replace:"/__HIDDEN__/":''|unescape:"html"}
                </div>
            {/foreach}
        </div>
        <script type="text/javascript">
            (function(jQuery) {
                jQuery('.notification').on('closed.bs.alert', function() {
                    jQuery('.notification.hidden:first').removeClass('hidden');
                    var notificationId = jQuery(this).attr('data-id'),
                        arguments = [
                            'module=notifications',
                            'action=Disable',
                            'record=' + encodeURIComponent(notificationId),
                            'Ajax=true'
                        ];
                    jQuery.ajax('index.php', {
                        data: arguments.join('&'),
                        dataType: 'text',
                        method: 'post'
                    }).done(function(responseText) {
                        jQuery('.notification.hidden:first').removeClass('hidden');
                    });
                });
            }(jQuery));
        </script>
    {/if}

    <div class="container-fluid">
        <style type="text/css">
            /* Darker backdrop for notification modals */
            .ekko-lightbox .modal-backdrop,
            .modal-backdrop {
                background-color: #000000 !important;
                opacity: 0.85 !important;
            }

            .modal-backdrop.in {
                opacity: 0.85 !important;
            }
        </style>
        <script type="text/javascript">
            (function(jQuery) {
                // Prevent duplicate modal execution
                if (window.notificationModalTriggered) {
                    return;
                }

                var idModal = '{$ID_NOTIFICATION_MODAL|escape:"javascript"}',
                record  = '{$ID|escape:"javascript"}',
                module  = '{$MODULE|escape:"javascript"}';

                if (idModal && idModal !== '' && idModal !== '0' && idModal !== 'null') {
                    window.notificationModalTriggered = true;
                    var href = 'modules/notifications/NotificationsModal.php?notificationId=' + encodeURIComponent(
                        idModal) + '&record=' + encodeURIComponent(record) + '&formodule=' + encodeURIComponent(
                        module);

                    // Wait for DOM and jQuery to be ready, then trigger via delegated event handler
                    jQuery(document).ready(function() {
                        // Create anchor element and append to body (hidden)
                        var anchor = jQuery('<a/>', {
                            href: href,
                            'data-toggle': 'lightbox',
                            'data-max-width': '800',
                            'data-title': 'Notificación',
                            style: 'display:none;'
                        }).appendTo('body');

                        // Trigger click after a short delay to ensure ekkoLightbox is initialized
                        setTimeout(function() {
                            anchor.trigger('click');
                        }, 500);
                    });
                }
            }(jQuery));
        </script>
        {strip}
            {math equation= rand() assign= "idDetailView"}
            {assign var='btnPrinter' value=','|explode:"presupuestos_cotizacion,facturas"}
            {if $GRID_VIEW neq NULL}
                {assign var='gridPosition' value=$GRID_VIEW->getPosition()}
            {else}
                {assign var='gridPosition' value=null}
            {/if}

            {if $MODULE eq 'action_plan'}
                {include file="modules/action_plan/Buttons_List.tpl"}
            {elseif $MODULE eq 'diagnostic_report'}
                {include file="modules/diagnostic_report/Buttons_List.tpl"}
            {else}
                {include file="Buttons_List.tpl"}
            {/if}
            {* NOTIFICATIONS are already rendered at the top of action_content block (lines 134-163) *}
            {* Removed duplicate rendering here to fix double notification display *}
            {if (isset ($MESSAGE))}
                <div class="alert alert-{if (!$IS_ERROR)}success{else}danger{/if}">
                    <i class="fa fa-{if (!$IS_ERROR)}check{else}times{/if}-circle fa-fw fa-lg"></i>
                    <strong>{if (!$IS_ERROR)}Listo{else}Error{/if}!</strong> {$MESSAGE}
                </div>
            {/if}
            <div class="tabs-wrapper row" style="background-color: transparent!important;">
                {* New DetailView *}
                <div class="col-md-12">
                    {if (!empty ($ACTIVE_APPLICATIONS)) && (count ($ACTIVE_APPLICATIONS) > 1) && ($APPLICATION_VIEWS_ENABLED)}
                        <div class="row block-container">
                            <div class="col-xs-12">
                                <div class="main-box" style="margin-bottom: 0;height: 100%!important">
                                    <div class="main-box-body clearfix">
                                        <form action="index.php" method="get" class="form">
                                            <input type="hidden" name="module" value="{$MODULE}" />
                                            <input type="hidden" name="action" value="DetailView" />
                                            {if (isset ($CREATEMODE))}
                                                <input type="hidden" name="createmode" value="{$CREATEMODE}" />
                                            {/if}
                                            {if (isset ($DUPLICATE))}
                                                <input type="hidden" name="isDuplicate" value="{$DUPLICATE}" />
                                            {/if}
                                            {if (isset ($MODE))}
                                                <input type="hidden" name="mode" value="{$MODE}" />
                                            {/if}
                                            {if (isset ($ID))}
                                                <input type="hidden" name="record" value="{$ID}" />
                                            {/if}
                                            {if (isset ($RETURN_ACTION))}
                                                <input type="hidden" name="return_action" value="{$RETURN_ACTION}" />
                                            {/if}
                                            {if (isset ($RETURN_ID))}
                                                <input type="hidden" name="return_id" value="{$RETURN_ID}" />
                                            {/if}
                                            {if (isset ($RETURN_MODULE))}
                                                <input type="hidden" name="return_module" value="{$RETURN_MODULE}" />
                                            {/if}
                                            {if (isset ($RETURN_MODULE))}
                                                <input type="hidden" name="return_viewname" value="{$RETURN_VIEWNAME}" />
                                            {/if}
                                            <div class="form-group">
                                                <div class="col-xs-12">
                                                    <select id="profileids" name="profileids" class="form-control"
                                                        onchange="this.form.submit ();" title="Vista por aplicación">
                                                        <option value="">Vista por aplicación</option>
                                                        {foreach $ACTIVE_APPLICATIONS as $application}
                                                            <option value="{$application.app_profile}"
                                                                {if (!empty ($PROFILE_IDS)) && (in_array ($application.app_profile, $PROFILE_IDS))}
                                                                selected="selected" {/if}>{$application.app_name}</option>
                                                        {/foreach}
                                                    </select>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    {/if}
                    {* Tab - Contenet *}
                    <div class="tab-content">
                        <div id="tab-detail-{$idDetailView}"
                            class="tab-pane fade in{if ($SELECTED_TAB == 'detail') || ($SELECTED_TAB eq NULL)} active{/if}">
                            {include file={$TAB_DETAIL}}
                        </div>
                        {* Panel de Control *}
                        {if ($GRAPHS neq NULL)}
                            <div id="tab-control_panel-{$idDetailView}"
                                class="tab-pane fade in{if ($SELECTED_TAB == 'control_panel')} active{/if}">
                                <div class="main-box clearfix"
                                    style="background-color: transparent!important;padding: 0!important;border-top: 1px solid #D8D8D8 !important;">
                                    <div class="main-box-body clearfix">
                                        <div class="tab-content">
                                            <div id="tab-metrics-graphics-{$idDetailView}" class="tab-pane fade in active"
                                                style="padding-top: 12px">
                                                {include file='modules/graficosgenerales/TabsContenet/GraphsModuleTabs.tpl'}
                                            </div>
                                            <div id="tab-metrics-reports-{$idDetailView}" class="tab-pane fade in"
                                                style="padding-top: 12px">
                                                {include file='utils/HTMLPageLoanding.tpl'}
                                            </div>
                                        </div>
                                    </div>
                                    <div style="height: 400px"></div>
                                </div>
                            </div>
                        {/if}
                        {* /Panel de Control *}
                        {* Related List *}
                        <div id="tab-related-list-{$idDetailView}"
                            class="tab-pane fade in{if ($SELECTED_TAB == 'related_list')} active{/if}">
                            {include file='modules/grid_view/RelatedListCardView.tpl'}
                        </div>
                        {* Related List *}
                        {*jobs view *}
                        <div id="tab-jobs-list-{$idDetailView}"
                            class="tab-pane fade in{if ($SELECTED_TAB == 'jobs-list')} active{/if}">
                            <div id="jobs-list-{$idDetailView}">
                                {include file='utils/HTMLPageLoanding.tpl'}
                            </div>
                        </div>
                        {*/jobs view *}
                        {* task view *}
                        <div id="tab-task-list-{$idDetailView}"
                            class="tab-pane fade in{if ($SELECTED_TAB == 'task-list')} active{/if}">
                            <div id="task-list-{$idDetailView}">
                                {include file='utils/HTMLPageLoanding.tpl'}
                            </div>
                        </div>
                        {* Summary Action Plan *}
                        <div id="tab-summary-plan-{$idDetailView}"
                            class="tab-pane fade in{if ($SELECTED_TAB == 'summary-plan')} active{/if}">
                            <div id="summary-plan-{$idDetailView}">
                                {include file='utils/HTMLPageLoanding.tpl'}
                            </div>
                        </div>
                        {* okr view *}
                        <div id="tab-okr-plan-{$idDetailView}"
                            class="tab-pane fade in{if ($SELECTED_TAB == 'okr-plan')} active{/if}">
                            <div id="okr-plan-{$idDetailView}">
                                {include file='utils/HTMLPageLoanding.tpl'}
                            </div>
                        </div>
                        {* strategies and initiatives  *}
                        <div id="tab-strategies-initiatives-{$idDetailView}"
                            class="tab-pane fade in{if ($SELECTED_TAB == 'strategies-initiatives')} active{/if}">
                            <div id="strategies-initiatives-{$idDetailView}">
                                {include file='utils/HTMLPageLoanding.tpl'}
                            </div>
                        </div>
                        {* progress-plan *}
                        <div id="tab-progress-plan-view-{$idDetailView}"
                            class="tab-pane fade in{if ($SELECTED_TAB == 'progress-plan')} active{/if}">
                            <div id="progress-plan-{$idDetailView}">
                                {include file='utils/HTMLPageLoanding.tpl'}
                            </div>
                        </div>
                        {* Diagnostic Destination  tab*}
                        <div id="tab-diagnostic-destination-view-{$idDetailView}"
                            class="tab-pane fade in{if ($SELECTED_TAB == 'diagnostic-destination')} active{/if}">
                            <div id="diagnostic-destination-{$idDetailView}">
                                {include file='utils/HTMLPageLoanding.tpl'}
                            </div>
                        </div>
                        {* Diagnostic Action Plan  tab*}
                        <div id="tab-diagnostic-plan-view-{$idDetailView}"
                            class="tab-pane fade in{if ($SELECTED_TAB == 'diagnostic-plan')} active{/if}">
                            <div id="diagnostic-plan-{$idDetailView}">
                                {include file='utils/HTMLPageLoanding.tpl'}
                            </div>
                        </div>
                        {* Diagnostic Evolution  tab*}
                        <div id="tab-diagnostic-evolution-view-{$idDetailView}"
                            class="tab-pane fade in{if ($SELECTED_TAB == 'diagnostic-evolution')} active{/if}">
                            <div id="diagnostic-evolution-{$idDetailView}">
                                {include file='utils/HTMLPageLoanding.tpl'}
                            </div>
                        </div>
                    </div>
					<div id="add_spaces_pre_footer" style="height:40px"> </div>
                    {* /Tab - Contenet *}
                </div>
                {if (!$IS_MODAL)}
                    {include file='CreateTaskWizard.tpl'}
                    <div class="main-box clearfix" style="height: 100% !important;background-color: transparent!important;">
                        <div style="height: {$totalBlocks}px"></div>
                    </div>
                {/if}
            </div>
            {if (!$IS_MODAL)}
                {math equation= rand() assign= "idModalDetalView"}
                <div class="md-modal md-effect-7-2" id="modal-detail-row">
                    <div class="md-content">
                        <div class="modal-header">
                            <button class="md-close close">&times;</button>
                            <h4 class="modal-title">{$ENTITY_IDENTIFIER_VALUE|truncate:40}</h4>
                        </div>
                        <div id="modal-detail-body-{$idModalDetalView}" data-status="0" class="modal-body">
                            <div class="row">
                                <div class="coll-md-12">
                                    {* botones aquí *}
                                    <div class="pull-right">
                                        {* INCLUYENDO CUSTOM BUTTONS *}
                                        {assign var = action value=$ACTION}
                                        {if ($action == 'DetailView') || ($action == 'index') || ($action == 'ListView')}
                                            {include file='customButtons.tpl'}
                                        {/if}

                                        {if (($action == 'DetailView' || $action == 'RecordHistory' || $action == 'CallRelatedList')) && (false)}
                                            <button class="btn btn-info" style="margin-left: 5px;"
                                                onclick="CalendarWizard.open ('{$MODULE}', '{$ID}', '{$ENTITY_IDENTIFIER_VALUE}')">
                                                <i class="fa fa-plus fa-lg" title="Crear Tarea" style="padding-right: 0.2em;"></i>Crear
                                                Tarea
                                            </button>
                                        {/if}

                                        {if ($MODULE != 'emailssent') && ($MODULE != 'emailsreceived') && ($CAN_CREATE_RECORDS)}
                                            <a href="index.php?module={$MODULE}&action=EditView&return_action=DetailView&parenttab={$CATEGORY}{if $smarty.request.frontendsid}&frontendsid={$smarty.request.frontendsid}{/if}&mode=create"
                                                class="btn btn-success" style="margin-left:.5em; margin-right: 0;">
                                                <i class="fa fa-plus fa-lg"
                                                    title="{$APP.LBL_CREATE_BUTTON_LABEL} {$SINGLE_MOD|getTranslatedString:$MODULE}"
                                                    style="padding-right: 0.2em;"></i> {$APP.LBL_CREATE_BUTTON_LABEL}
                                                {$SINGLE_MOD|getTranslatedString:$MODULE}
                                            </a>
                                        {/if}
                                        {* Información asociada *}
                                        {if (($action == 'DetailView') || (($action == 'RecordHistory') && (!$IS_MODAL)))}
                                            <a class="btn btn-info"
                                                href="index.php?action=CallRelatedList&module={$MODULE}&record={$ID}&editpermission={$EDIT_PERMISSION}"
                                                style="margin-left:.5em; margin-right: 0;"
                                                {if $IS_MODAL}onclick="ModalDetailViewUtils.getRelatedList (this, event)" {/if}
                                                title="Información asociada"><i class="fa fa-cogs"></i>&nbsp;Información asociada</a>
                                            {* compartir *}
                                            <a class="btn btn-success" style="margin-left:.5em; margin-right: 0;" href="javascript:;"
                                                onclick="DataSharingUtils.openSharingModal ('{$MODULE}', '{$ID}');"><i
                                                    class="fa fa-share"></i>Compartir</a>
                                            {* / compartir *}
                                        {elseif ($IS_MODAL) && (($action eq 'CallRelatedList') || ($action eq 'RecordHistory'))}
                                            <a class="btn btn-info"
                                                href="index.php?action=DetailView&module={$MODULE}&record={$ID}&tab=detail"
                                                style="margin-left:.5em; margin-right: 0;"
                                                {if $IS_MODAL}onclick="ModalDetailViewUtils.getRelatedList (this, event)" {/if}
                                                title="Información asociada"><i class="fa fa-home"></i>&nbsp;Ver detalle</a>
                                        {/if}
                                        {* /Información asociada *}
                                        {if ($EDIT_PERMISSION == 'yes')}
                                            <a href="javascript:void(0)"
                                                onclick="DetailView.return_module.value='{$MODULE}'; DetailView.return_action.value='DetailView'; DetailView.return_id.value='{$ID}';DetailView.module.value='{$MODULE}';submitFormForAction('DetailView','EditView');"
                                                class="btn btn-warning" style="margin-left:.5em; margin-right: 0;">
                                                <span class="fa fa-pencil"></span> {$APP.LBL_EDIT_BUTTON_LABEL}
                                            </a>
                                        {/if}
                                        {if ($DELETE == 'permitted')}
                                            <a href="javascript:void(0)" id="deleteButton" tagModule="{$MODULE}"
                                                onclick="DetailView.return_module.value='{$MODULE}'; DetailView.return_action.value='index'; var confirmMsg = '{$APP.NTC_DELETE_CONFIRMATION}'; submitFormForActionWithConfirmation('DetailView', 'Delete', confirmMsg);"
                                                class="btn btn-default" style="margin-left:.5em; margin-right: 0;">
                                                <span class="fa fa-trash-o"></span> {$APP.LBL_DELETE_BUTTON_LABEL}
                                            </a>
                                        {/if}
                                        <div class="btn-group" style="margin-left: 5px;">
                                            {if ($MODULE == 'Calendar')}
                                                <a href="index.php?module=Calendar&action=EditView&activity_mode=Events&return_module=Calendar&return_action=ListView"
                                                    class="btn btn-info" style="margin-left: 5px;">Crear Tarea</a>
                                                <a href="index.php?module=Calendar&action=index" class="btn btn-warning"
                                                    style="margin-left: 5px;">Ir al calendario</a>
                                            {else}
                                                <button type="button" class="btn btn-info dropdown-toggle dropdown-toggle-ext"
                                                    data-toggle="dropdown">
                                                    <i class="fa">&nbsp;...&nbsp;</i>
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu pull-right" role="menu">
                                                    {if (in_array ($action, array ('CallRelatedList', 'EditView')))}
                                                        <li>
                                                            <a
                                                                href="index.php?module={$MODULE}&action=EditView&return_action=DetailView&parenttab={$CATEGORY}{if $smarty.request.frontendsid}&frontendsid={$smarty.request.frontendsid}{/if}&mode=create">
                                                                <i class="fa fa-plus"
                                                                    title="{$APP.LBL_CREATE_BUTTON_LABEL} {$SINGLE_MOD|getTranslatedString:$MODULE}"></i>
                                                                {$APP.LBL_CREATE_BUTTON_LABEL} {$SINGLE_MOD|getTranslatedString:$MODULE}
                                                            </a>
                                                        </li>
                                                    {/if}
                                                    {if ($EDIT_DUPLICATE == 'permitted') && ($MODULE != 'Documents') && ($CAN_CREATE_RECORDS)}
                                                        <li>
                                                            <a href="javascript:void(0)"
                                                                onclick="DetailView.return_module.value='{$MODULE}'; DetailView.return_action.value='DetailView'; DetailView.isDuplicate.value='true';DetailView.module.value='{$MODULE}'; submitFormForAction('DetailView','EditView');">
                                                                <i class="fa fa-files-o"></i> {$APP.LBL_DUPLICATE_BUTTON_LABEL}
                                                            </a>
                                                        <li>
                                                        {/if}
                                                        {if (!empty ($TOTAL_SYNCS))}
                                                        <li>
                                                            <a href="javascript:;" onclick="DataSharingUtils.openSyncsModal ('{$MODULE}');">
                                                                <i class="fa fa-exchange"></i>Histórico de compartir <span
                                                                    class="label label-primary"
                                                                    style="margin-left: 0.75em;">{$TOTAL_SYNCS}</span>
                                                            </a>
                                                        </li>
                                                    {/if}
                                                    {if ($IS_ADMIN)}
                                                        <li>
                                                            <a
                                                                href="index.php?module=Settings&action=LayoutBlockList&parenttab=Settings&formodule={$MODULE}&return_module={$MODULE}">
                                                                <i class="fa fa-cog"></i>{$APP.LBL_FIELDS_LAYOUT}
                                                                {$MODULE|getTranslatedString:$MODULE}
                                                            </a>
                                                        </li>
                                                    {/if}
                                                    {if ($action == 'DetailView' || $action == 'RecordHistory' || $action == 'CallRelatedList')}
                                                        {*<li>
                                                        <a href="javascript:;"
                                                           onclick="DataSharingUtils.openSharingModal ('{$MODULE}', '{$ID}');"><i
                                                                    class="fa fa-share"></i>Compartir</a>
                                                    </li>
                                                    *}
                                                        {if (!empty ($TOTAL_SYNCS))}
                                                            <li>
                                                                <a href="javascript:;"
                                                                    onclick="DataSharingUtils.openSyncsModal ('{$MODULE}', '{$ID}');"><i
                                                                        class="fa fa-exchange"></i>Histórico de compartir
                                                                    <span class="label label-primary"
                                                                        style="margin-left: 0.75em;">{$TOTAL_SYNCS}</span></a>
                                                            </li>
                                                        {/if}
                                                    {/if}
                                                    {if ($IS_ADMIN) && (!empty ($ACTIVE_APPLICATIONS)) && (count ($ACTIVE_APPLICATIONS) > 1)}
                                                        <li>
                                                            <a
                                                                href="index.php?module=Settings&action=ToggleApplicationViewsAvailability&Ajax=true&returnmodule={$MODULE}&returnaction={$action}&returnrecord={$RECORD}"><i
                                                                    class="fa fa-{if ($APPLICATION_VIEWS_ENABLED)}eye-slash{else}eye{/if}"></i>{if ($APPLICATION_VIEWS_ENABLED)}Desactivar{else}Activar{/if}
                                                                vistas por aplicación</a>
                                                        </li>
                                                    {/if}
                                                    <li>
                                                        <a href="index.php?action=RecordHistory&module=historymanager&record={$ID}&parenttab={$CATEGORY}&formodule={$MODULE}&editpermission={$EDIT_PERMISSION}"
                                                            {if $IS_MODAL}onclick="ModalDetailViewUtils.getRecordHistory (this, event)"
                                                            {/if} title="Histórico de Cambios"><i
                                                                class="fa fa-archive"></i>&nbsp;Histórico
                                                            de Cambios</a>
                                                    </li>
                                                    {if $GRAPHS neq NULL}
                                                        <li{if ($SELECTED_TAB == 'control_panel')} class="active" {/if}>
                                                            <a title="{$APP.LBL_CONTROL_PANEL}"
                                                                href="index.php?action=DetailView&module={$MODULE}&record={$ID}&tab=control_panel"
                                                                {if $IS_MODAL}onclick="ModalDetailViewUtils.getRecordHistory (this, event)"
                                                                {/if}>
                                                                <i class="fa fa-bar-chart-o"></i>Graficos favoritos</a>
                                                            </li>
                                                        {/if}
                                                </ul>
                                            {/if}
                                        </div>
                                    </div>
                                </div>
                                {* botones aquí *}
                            </div>
                        </div>
                        {* Register here *}
                    </div>{* modal body *}
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary md-close" data-status="0">Cerrar</button>
                    </div>
                </div>
            </div>{* /Modal registro *}
            <div class="md-overlay"></div>
        {/if}
    {/strip}
    </div>
    {block name="content-extra"}{/block}
    {if (!$IS_MODAL)}
        {include file='modules/instancesdatasharing/SyncsModal.tpl'}
        <script type="text/javascript" src="modules/instancesdatasharing/data-sharing.js"></script>
        <script type="text/javascript" src="webmail/program/js/common.min.js"></script>
        <script type="text/javascript" src="modules/webmail/webmail-utils.js?v=1.0.6"></script>
        <script type="text/javascript" src="themes/centaurus/js/classie.js"></script>
        <script type="text/javascript" src="themes/centaurus/js/modalEffects.js"></script>
        <script type="text/javascript" src="include/js/RelatedLists.js"></script>
        <script type="text/javascript" src="modules/notification_center/parleyScript.js?v=1.0.1"></script>
    {elseif ($EDIT_PERMISSION == 'yes')}
        <script type="text/javascript" src="themes/centaurus/js/bootstrap-editable.js"></script>
        {loadEditableFiels arrayBlocs=$BLOCKS}
    {/if}
    {$DLG_DETALLE_NOTIFICACION}
    {$DLG_NUEVA_NOTIFICACION}

    {* Script para ajustar tooltip de work_situation según costo o unidades *}
    {if $MODULE eq 'orden_de_trabajo'}
        <script type="text/javascript" src="include/js/related-module-modal.js"></script>
        <script type="text/javascript" src="themes/centaurus/js/work-situation-tooltip.js"></script>
        <script type="text/javascript" src="themes/centaurus/js/ActivityEditUtils.js"></script>
    {/if}

{/block}