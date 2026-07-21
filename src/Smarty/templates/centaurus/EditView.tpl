{assign var="INCLUDE_MODULE_JS" value=$MODULE}
{extends file="platzilla_layout.tpl"}

{block name="action_css"}
    <link rel="stylesheet" type="text/css" href="/themes/centaurus/css/compiled/platzilla-editview.css" />
{/block}

{block name="action_content"}
    {assign var="MODULELABEL" value=$MODULE|@getTranslatedString: $MODULE}
    <div class="row">
        <div class="col-xs-6">
            <h1 id="title-view">
                {* vtlib customization: use translated label if available *}
                {if $APP.$SINGLE_MOD}
                    {assign var="SINGLE_MOD_LABEL" value=$APP.SINGLE_MOD}
                {else}
                    {assign var="SINGLE_MOD_LABEL" value=$SINGLE_MOD}
                {/if}
                <a title="Listado de {$SINGLE_MOD|@getTranslatedString:$MODULE}" style="text-decoration: none"
                    href="index.php?action=ListView&module={$MODULE}&parenttab={$CATEGORY}">
                    <strong>{if $MODE neq 'create'}Editando&nbsp;{else}Creando&nbsp;{/if}{$SINGLE_MOD|@getTranslatedString:$MODULE|module_singularize}</strong>
                    {if !empty($ENTITY_IDENTIFIER_VALUE)}
                    {/if}
                </a>{if $MODE neq 'create'}<span
                    style="color: #777777;font-size: 0.8em;font-weight: bold">&nbsp;&gt;</span>{/if}
                <small style="font-weight: bold">{$ENTITY_IDENTIFIER_VALUE|truncate:28}</small>
            </h1>
        </div>
        <div class="col-xs-6">
            <div class="pull-right" style="padding-right: 25px">
                {if ($IS_ADMIN)}
                    {math equation= rand() assign= "idHelp"}
                    <div class="btn-group" id="congig-btn-tab">
                        <button id="{$idHelp}-add-field" type="button" style="margin-right: 2px;display: none;"
                            title="Agregar campos" data-id-btn="{$idHelp}" data-module="{$MODULE}"
                            onclick="HelpUtils.addFields (this)" class="btn btn-primary  btn-xs animate__animated">
                            <i class="fa fa-plus" style="font-size:1.45em;text-align: center"></i>
                        </button>
                        <button id="{$idHelp}" type="button" title="Identificar y/o editar campos"
                            onclick="HelpUtils.ShowIconHelp(this)" class="btn btn-default  btn-xs">
                            <i class="fa fa-question-circle" aria-hidden="true" style="font-size:1.45em;"></i>
                        </button>
                        {if $HOW_TO_ID neq NULL}
                            <a class="btn btn-info btn-xs" data-width="950" data-toggle="lightbox" data-parent=""
                                data-gallery="remoteload" data-title="¡Aprende como!"
                                href="index.php?module={$MODULE}&action=AjaxDetailViewUtils&record={$HOW_TO_ID}&function=GET-HOW-TO&Ajax=true"
                                title="¡Aprende como!"><i class="bi bi-question-square" style="font-size:1.05em;"></i></a>
                        {/if}
                    </div>
                {/if}
            </div>
        </div>
    </div>
    {if (!empty ($ACTIVE_APPLICATIONS)) && (count ($ACTIVE_APPLICATIONS) > 1) && ($APPLICATION_VIEWS_ENABLED)}
        <div class="row">
            <div class="col-xs-12">
                <div class="main-box" style="margin-bottom: 0;">
                    <div class="main-box-body clearfix">
                        <form action="index.php" method="get" class="form">
                            <input type="hidden" name="module" value="{$MODULE}" />
                            <input type="hidden" name="action" value="EditView" />
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
                            {if (isset ($RETURN_TAB))}
                                <input type="hidden" name="tab" value="{$RETURN_TAB}" />
                            {/if}
                            {if (isset ($RETURN_VIEWNAME))}
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
    {block name="content"}
        {$ERROR_MESSAGE}
        <form action="index.php" method="post" name="EditView" enctype="multipart/form-data"
            onsubmit="VtigerJS_DialogBox.block (); if (FieldValidationUtils.validateForm (this)) { return true; } else { VtigerJS_DialogBox.unblock (); return false; }"
            role="form">
            {if $MODULE == 'Calendar'}
                <input type="hidden" name="activity_mode" value="{$ACTIVITY_MODE}" />
                <input type="hidden" name="product_id" value="{$PRODUCTID}" />
            {/if}
            <input type="hidden" name="pagenumber" value="{$smarty.request.start|@vtlib_purify}" />
            <input type="hidden" name="module" value="{$MODULE}" />
            <input type="hidden" name="action" value="Save" />
            <input type="hidden" name="record" value="{$RECORD}" />
            <input type="hidden" name="mode" value="{$MODE}" />
            {if (isset ($DUPLICATE))}
                <input type="hidden" name="isDuplicate" value="{$DUPLICATE}" />
            {/if}
            <input type="hidden" name="parenttab" value="{$CATEGORY}" />
            <input type="hidden" name="return_module" value="{$RETURN_MODULE}" />
            <input type="hidden" name="return_tab" value="{$RETURN_TAB}" />
            <input type="hidden" name="return_id" value="{$RETURN_ID}" />
            <input type="hidden" name="return_action" value="{$RETURN_ACTION}" />
            <input type="hidden" name="return_viewname" value="{$RETURN_VIEWNAME}" />
            <input type="hidden" name="createmode" value="{$CREATEMODE}" />
            <input type="hidden" name="cases_process" value="{$CASE_ID}" />
            {if $smarty.request.frontendsid}
                <input type="hidden" name="frontendsid" value="{$smarty.request.frontendsid}" />
            {/if}
            {foreach key=header item=data from=$BLOCKS name=block}
                <div class="row block-container" id="block_{$smarty.foreach.block.iteration}">
                    <div class="col-xs-12">
                        <div class="main-box">
                            <header class="title-section main-box-header clearfix">
                                <h2>{$header}</h2>
                            </header>
                            <div class="main-box-body clearfix" id="tbl{$header|replace:' ':''}">
                                {if ($smarty.foreach.block.iteration eq 1) && $CASE_ID eq NULL}
                                    {$PROCESS}
                                {/if}
                                {include file="DisplayFields.tpl"}
                            </div>
                        </div>
                    </div>
                </div>
            {/foreach}
            <div class="main-box">
                <div style="height: 400px"></div>
            </div>
            {block name="content-after-blocks"}{/block}
            <div class="clearfix" style="height: 25px; margin-bottom: 16px;"></div>
            <div class="row">
                <div id="fixed-btns-bar" style="display: block;">
                    <div class="container">
                        <div class="row">
                            <div id="buttons-editview"class="col-xs-12" style="padding: 15px; height: 55px;">
                                {block name="buttons-bar"}
                                    <button type="submit" class="btn btn-success" title="{$APP.LBL_SAVE_BUTTON_TITLE}"
                                        accessKey="{$APP.LBL_SAVE_BUTTON_KEY}"
                                        style="margin-right: 5px;">{$APP.LBL_SAVE_BUTTON_LABEL}</button>
                                    <a href="index.php?module={$MODULE}&action={if (empty ($RECORD))}ListView{else}DetailView&record={$RECORD}{/if}&mode=cancel"
                                        class="btn btn-default" title="{$APP.LBL_CANCEL_BUTTON_TITLE}"
                                        accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}">{$APP.LBL_CANCEL_BUTTON_LABEL}</a>
                                {/block}
                            </div>
							
                        </div>
                    </div>
					
                </div>
            </div>
            <input name="search_url" id="search_url" type="hidden" value="{$SEARCH}">
        </form>
    {/block}
{/block}

{block name="action_js"}
    {* JS exclusivos de EditView migrados aquí *}
    {if $CAMPOS_TIPO_GRID}
        <script type="text/javascript" src="/include/js/gridFormValidate.js"></script>
    {/if}
    {if $TABLE_FIELDS}
        <script type="text/javascript" src="/include/js/tablefields-wizard.js"></script>
    {/if}
    {if ($MODULE == 'Documents')}
        <script type="text/javascript" src="/include/ckeditor/ckeditor.js"></script>
        <script type="text/javascript">
            var textAreaName = 'notecontent';
            CKEDITOR.replace(textAreaName, {
                extraPlugins: 'uicolor',
                uiColor: '#dfdff1',
                height: '200',
                width: '800'
            });
            var oCKeditor = CKEDITOR.instances[textAreaName];
        </script>
    {/if}
    <script type="text/javascript">
        var fieldname = [{$VALIDATION_DATA_FIELDNAME}];
        var fieldlabel = [{$VALIDATION_DATA_FIELDLABEL}];
        var fielddatatype = [{$VALIDATION_DATA_FIELDDATATYPE}];
        function openPopup() {
            window.open("index.php?module=Users&action=UsersAjax&file=RolePopup&parenttab=Settings", "roles_popup_window",
                "height=425,width=640,toolbar=no,menubar=no,dependent=yes,resizable =no");
        }
    </script>
    {block name="scripts"}{/block}
    {if !empty ($PICKIST_DEPENDENCY_DATASOURCE)}
        <script type="text/javascript" src="include/js/FieldDependencies.js"></script>
        <script type="text/javascript">
            jQuery(document).ready(function() {
                (new FieldDependencies({$PICKIST_DEPENDENCY_DATASOURCE})).init();
            });
        </script>
    {/if}
    <script type="text/html" id="attachment-template">
        <li class="col-md-3 attachment"
            style="border: 1px solid #DDDDDD; margin-bottom: 3px; position: relative; width: 100%;">
            <button type="button" class="btn btn-close" onclick="AttachmentsUtils.deleteAttachment (this);"
                style="background-color: transparent; border: 0; bottom: 0; line-height: 1; right: 0; padding: 0 5px 2px 5px; position: absolute; text-transform: uppercase; z-index: 1000;">
                X
            </button>
            <div class="attachment-container">
                <span class="attachment-name"></span><span class="attachment-size"></span>
            </div>
            <input type="hidden" class="attachment-data" />
            <input type="hidden" class="attachment-filename" />
        </li>
    </script>
    <script type="text/javascript" src="include/js/attachments-utils.js"></script>
    <script type="text/javascript" src="include/js/field-dependencies.js"></script>
    <script type="text/javascript">
        FieldDependenciesUtils.init('form[name="EditView"]', {json_encode ($FIELD_DEPENDENCIES)});
    </script>
    <script type="text/javascript" src="include/js/field-validations.js"></script>
    <script type="text/javascript">
        FieldValidationUtils.init({json_encode ($FIELDS)}, {if (!empty ($RECORD))}{$RECORD}{else}null{/if});
    </script>

    {* Display notification modal if applicable *}
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
            record  = '{if (!empty ($RECORD))}{$RECORD|escape:"javascript"}{else}0{/if}',
            module  = '{$MODULE|escape:"javascript"}';
            if (idModal && idModal !== '' && idModal !== '0' && idModal !== 'null') {
                window.notificationModalTriggered = true;
                var href = 'modules/notifications/NotificationsModal.php?notificationId=' + encodeURIComponent(
                    idModal) + '&record=' + encodeURIComponent(record) + '&formodule=' + encodeURIComponent(module);
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
        })(jQuery);
    </script>
{/block}