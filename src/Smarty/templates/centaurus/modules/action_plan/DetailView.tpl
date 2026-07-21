{strip}
{math equation= rand() assign= "idActionPlanView"}
<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/libs/emojionearea.min.css"/>
<link type="text/css" rel="stylesheet" href="modules/instancesdatasharing/instancesdatasharing.css"/>
<link type="text/css" rel="stylesheet" href="themes/centaurus/css/compiled/pipeline.css"/>
<link type="text/css" rel="stylesheet" href="themes/centaurus/css/compiled/detailview.css?v1.0.0"/>
<link type="text/css" rel="stylesheet" href="themes/centaurus/css/compiled/platzilla-detailview.css"/>
<link type="text/css" rel="stylesheet" href="themes/centaurus/css/bootstrap/bootstrap-editable.css"/>
<link rel="stylesheet" type="text/css" href="themes/centaurus/css/bootstrap/nifty-component.css"/>
<link rel="stylesheet" type="text/css" href="themes/centaurus/css/bootstrap/bootstrap-cards.css"/>
<link rel="stylesheet" type="text/css" href="modules/grid_view/grid-view.css"/>
<style>
    #card-view-container {
        margin-top: 20px;
    }
    #card-view-register-container {
        max-height: 110%;
        overflow-y: auto;
        padding: 0 !important;
        overflow-x: hidden;
        z-index: 10000;
        scrollbar-width: thin;
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
        margin-left: 0!important;
        padding-left: 0!important;
    }
    .rounded {
        border-radius:.75rem!important
    }
    @media (min-width: 1280px) and (max-width: 1300px) {
        .platzilla-card-header p {
            font-size: 0.85em;
            margin-left: 0!important;
            padding-left: 0!important;
        }
    }
    @media (min-width: 1400px) and (max-width: 1580px) {
        .platzilla-card-header p {
            font-size: 0.9em;
            margin-left: 0!important;
            padding-left: 0!important;
        }
    }
    @media (min-width: 1600px) and (max-width: 1800px) {
        .platzilla-card-header p {
            font-size: 1.05em;
            margin-left: 0!important;
            padding-left: 0!important;
        }
    }
</style>
{include file="modules/action_plan/Buttons_List.tpl"}
{if (!empty ($NOTIFICATIONS)) && (!$IS_MODAL)}
    {foreach $NOTIFICATIONS as $index => $notification}
        {if $index >= 1}
            {$notification->getContents ()|regex_replace:"/__ID__/":$notification->getId ()|regex_replace:"/__COLLAPSE_IN__/":'collapse'|regex_replace:"/__HIDDEN__/":'hidden'|unescape:"html"}
        {else}
            {$notification->getContents ()|regex_replace:"/__ID__/":$notification->getId ()|regex_replace:"/__COLLAPSE_IN__/":'collapse'|regex_replace:"/__HIDDEN__/":''|unescape:"html"}
        {/if}
    {/foreach}
    <script type="text/javascript">
        (function (jQuery) {
            jQuery('.notification').on('closed.bs.alert', function () {
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
                }).done(function () {
                    jQuery('.notification.hidden:first').removeClass('hidden');
                });
            });
        }(jQuery));
    </script>
{/if}
{if (isset ($MESSAGE))}
    <div class="alert alert-{if (!$IS_ERROR)}success{else}danger{/if}">
        <i class="fa fa-{if (!$IS_ERROR)}check{else}times{/if}-circle fa-fw fa-lg"></i>
        <strong>{if (!$IS_ERROR)}Listo{else}Error{/if}!</strong> {$MESSAGE}
    </div>
{/if}
<div class="container-fluid" {if !$IS_MODAL}
    style="background-color: transparent!important;" {/if}>
    <div class="tabs-wrapper row" style="background-color: transparent!important;">
        <div class="col-lg-12 col-md-12 col-xs-12">
            <div class="tab-content">
                {* Action Plan Detail View *}
                <div id="tab-detail-{$idActionPlanView}"
                     class="tab-pane fade in{if ($SELECTED_TAB == 'detail') || ($SELECTED_TAB eq NULL)} active{/if}">
                    {if $IS_MODAL}
                        <form action="index.php" method="post" name="DetailView" id="form">
                            {include file='DetailViewHidden.tpl'}
                            {if $MODULE eq 'formacion_cursos'}
                                <div>{include file="modules/formacion_cursos/DetailViewCursos.tpl"}</div>
                            {/if}
                            {assign var="countCol" value=0}
                            {assign var="totalBlocks" value=(1700 - (($BLOCKS|count) * 150))}
                            {foreach key=header item=detail from=$BLOCKS name=mainBlock}
                                {assign var="keyArray" value=key($detail[0])}
                                {assign var="uitype" value=$detail[0][$keyArray]['ui']}
                                {assign var="idField" value=$detail[0][$keyArray]['fldid']}
                                {assign var="fieldName" value=$detail[0][$keyArray]['fldname']}
                                {assign var="isEmpty" value=true}
                                {assign var=detailD value=$detail}
                                {if $uitype neq 2202}
                                    {foreach item=detail from=$detailD}
                                        {foreach key=label item=data from=$detail}
                                            {if ((!empty ($data.value) && $uitype neq 2202) || ($FIELD_ATTACHMENTS[$fieldName] neq NULL && $uitype eq 4096)) }
                                                {assign var="isEmpty" value=false}
                                            {/if}
                                        {/foreach}
                                    {/foreach}
                                    {if ($isEmpty)}
                                        {continue}
                                    {/if}
                                {/if}
                                {if $header eq $MOD.LBL_COMMENTS || $header eq $MOD.LBL_COMMENT_INFORMATION}
                                    <div class="row">
                                        <div class="main-box"
                                             {if $smarty.foreach.mainBlock.index > 0}style="border-top:1px solid #D8D8D8!important;{if $IS_MODAL}height: 100%!important;;{/if}"
                                             {else}style="height: 100%!important;" {/if}>
                                            <header class="title-section main-box-header clearfix">
                                                <h2>{$MOD.LBL_COMMENT_INFORMATION} {$idField}</h2>
                                            </header>
                                            <div class="main-box-body clearfix"
                                                 id="tbl{$header|replace:' ':''}">{$COMMENT_BLOCK}</div>
                                        </div>
                                    </div>
                                {else}
                                    <div class="row">
                                        <div class="main-box"
                                             {if $smarty.foreach.mainBlock.index > 0}style="border-top: 1px solid #D8D8D8 !important;" {/if}>
                                            <header class="title-section main-box-header clearfix">
                                                <h2>{$header}</h2>
                                            </header>
                                            <div class="main-box-body clearfix" id="tbl{$header|replace:' ':''}">
                                                {if $uitype eq 2202}
                                                    {$CAMPOS_TIPO_GRID[$idField]}
                                                {/if}
                                                {assign var=detailD value=$detail}

                                                {foreach item=detail from=$detailD}
                                                    {foreach key=label item=data from=$detail  name=detailBlock}
                                                        {assign var=keyid value=$data.ui}
                                                        {assign var=keyval value=$data.value}
                                                        {assign var=keytblname value=$data.tablename}
                                                        {assign var=keyfldname value=$data.fldname}
                                                        {assign var=keyfldid value=$data.fldid}
                                                        {assign var=keyoptions value=$data.options}
                                                        {assign var=keysecid value=$data.secid}
                                                        {assign var=keyseclink value=$data.link}
                                                        {assign var=keycursymb value=$data.cursymb}
                                                        {assign var=keysalut value=$data.salut}
                                                        {assign var=keyaccess value=$data.notaccess}
                                                        {assign var=keycntimage value=$data.cntimage}
                                                        {assign var=keyadmin value=$data.isadmin}
                                                        {assign var=display_type value=$data.displaytype}
                                                        {assign var=_readonly value=$data.readonly}
                                                        {if $label ne ''}
                                                            {if $keycntimage ne ''}
                                                                <input type="hidden" id="hdtxt_IsAdmin"
                                                                       value={$keyadmin} />{$keycntimage}
                                                            {elseif $keyid eq '14'}
                                                                <input type="hidden" id="hdtxt_IsAdmin"
                                                                       value={$keyadmin}/>
                                                            {/if}
                                                            {include file="DetailViewUI.tpl"}
                                                        {/if}

                                                    {/foreach}
                                                {/foreach}
                                            </div>
                                        </div>
                                    </div>
                                {/if}
                            {/foreach}
                            {if $CAMPOS_TIPO_GRID}
                                <script type="text/javascript" src="include/js/gridFormValidate.js"></script>
                            {/if}
                            {block name="content-after-blocks"}{/block}
                        </form>
                    {else}
                        {*assign var='btnPrinter' value=','|explode:"presupuestos_cotizacion,facturas"*}
                        {* new detailView here *}
                        <div class="row">
                            {* Detail record *}
                            <div class="{if ($gridPosition eq 'SIDE') ||($gridPosition eq MULL)}col-md-8{else}col-md-12{/if}">
                                <div id="card-view-register-container"> {*div with scroll*}
                                    <div class="row">
                                        <div class="col-md-12">
                                            {* Tarjeta aquí *}
                                            <div class="card rounded">
                                                <div class="card-header platzilla-card-header rounded">
                                                    <div class="row">
                                                        <div class="col-md-5">
                                                            <p class="text-center pull-left"
                                                               style="font-weight: bold">Detalle del registro</p>
                                                        </div>
                                                        <div class="col-md-7">
                                                            <div class="pull-right">
                                                                {* printer button *}
                                                                {if in_array($MODULE, $btnPrinter)}
                                                                    <button type="button"
                                                                            id="pinterButton"
                                                                            class="btn btn-info btn-circle btn-xs"
                                                                            title="Imprime el documento actual"
                                                                            onclick="print_invoice('{$MODULE}')">
                                                                        <i class="fa fa-print" aria-hidden="true"></i>
                                                                    </button>
                                                                {/if}
                                                                {* /printer button *}
                                                                {* share button *}
                                                                <a class="btn btn-success btn-circle btn-xs"
                                                                   id="shareButton"
                                                                   style="margin-left:.5em; margin-right:.5em; ;"
                                                                   href="javascript:;"
                                                                   onclick="DataSharingUtils.openSharingModal ('{$MODULE}', '{$ID}');"><i
                                                                            class="fa fa-share"></i></a>
                                                                {if ($EDIT_DUPLICATE == 'permitted')}
                                                                    {* duplicate button *}
                                                                    <a class="btn btn-warning btn-circle btn-xs"
                                                                       id="duplicateButton"
                                                                       href="javascript:void(0)"
                                                                       onclick="DetailView.return_module.value='{$MODULE}'; DetailView.return_action.value='DetailView'; DetailView.isDuplicate.value='true';DetailView.module.value='{$MODULE}'; submitFormForAction('DetailView','EditView');"><i
                                                                                class="fa fa-files-o"></i></a>
                                                                {/if}
                                                                {if ($EDIT_PERMISSION == 'yes')}
                                                                    {* edit button*}
                                                                    <a href="javascript:void(0)"
                                                                       onclick="DetailView.return_module.value='{$MODULE}'; DetailView.return_action.value='DetailView'; DetailView.return_id.value='{$ID}';DetailView.module.value='{$MODULE}';submitFormForAction('DetailView','EditView');"
                                                                       id="editButton"
                                                                       class="btn btn-default btn-circle btn-xs"
                                                                       style="margin-left:.5em; margin-right: 0;">
                                                                        <span class="fa fa-pencil"></span>
                                                                    </a>
                                                                {/if}
                                                                {if ($DELETE == 'permitted')}
                                                                    {* delete button *}
                                                                    <a class="btn btn-danger btn-circle btn-xs"
                                                                       href="javascript:void(0)"
                                                                       id="deleteButton"
                                                                       tagModule="{$MODULE}"
                                                                       onclick="DetailView.return_module.value='{$MODULE}'; DetailView.return_action.value='index'; var confirmMsg = '{$APP.NTC_DELETE_CONFIRMATION}'; submitFormForActionWithConfirmation('DetailView', 'Delete', confirmMsg);"
                                                                       style="margin-left:.5em; margin-right: 0;"><span
                                                                                class="fa fa-trash-o"></span></a>
                                                                {/if}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    {* Register *}
                                                    <form action="index.php" method="post" name="DetailView"
                                                          id="form">
                                                        {include file='DetailViewHidden.tpl'}
                                                        {if $MODULE eq 'formacion_cursos'}
                                                            <div>{include file="modules/formacion_cursos/DetailViewCursos.tpl"}</div>
                                                        {/if}
                                                        {assign var="countCol" value=0}
                                                        {assign var="totalBlocks" value=(1700 - (($BLOCKS|count) * 150))}
                                                        {foreach key=header item=detail from=$BLOCKS name=mainBlock}
                                                            {assign var="keyArray" value=key($detail[0])}
                                                            {assign var="uitype" value=$detail[0][$keyArray]['ui']}
                                                            {assign var="idField" value=$detail[0][$keyArray]['fldid']}
                                                            {assign var="fieldName" value=$detail[0][$keyArray]['fldname']}
                                                            {assign var="isEmpty" value=true}
                                                            {assign var=detailD value=$detail}
                                                            {if $uitype neq 2202}
                                                                {foreach item=detail from=$detailD}
                                                                    {foreach key=label item=data from=$detail}
                                                                        {if ((!empty ($data.value) && $uitype neq 2202) || ($FIELD_ATTACHMENTS[$fieldName] neq NULL && $uitype eq 4096)) }
                                                                            {assign var="isEmpty" value=false}
                                                                        {/if}
                                                                    {/foreach}
                                                                {/foreach}
                                                                {if ($isEmpty)}
                                                                    {continue}
                                                                {/if}
                                                            {/if}
                                                            {if $header eq $MOD.LBL_COMMENTS || $header eq $MOD.LBL_COMMENT_INFORMATION}
                                                                <div class="row">
                                                                    <div class="main-box"
                                                                         {if $smarty.foreach.mainBlock.index > -1}style="border:1px solid #D8D8D8!important;margin-top: 12px{if $IS_MODAL}height: 100%!important;;{/if}"
                                                                         {else}style="height: 100%!important;" {/if}>
                                                                        <header class="title-section main-box-header clearfix">
                                                                            <h2>{$MOD.LBL_COMMENT_INFORMATION} {$idField}</h2>
                                                                        </header>
                                                                        <div class="main-box-body clearfix"
                                                                             id="tbl{$header|replace:' ':''}">{$COMMENT_BLOCK} </div>
                                                                    </div>
                                                                </div>
                                                            {else}
                                                                <div class="row" style="margin-top: -4px">
                                                                    {if $smarty.foreach.mainBlock.index > 0}
                                                                        <div class="col-md-12 border-bottom"
                                                                             style="margin-bottom: 6px;margin-top: 16px;">
                                                                            <p class="text-left"
                                                                               style="font-weight: bold;">{*$header*}</p>
                                                                        </div>
                                                                    {/if}
                                                                    {* main-box *}
                                                                    <div class="main-box"
                                                                            {*if $smarty.foreach.mainBlock.index > -1}style="border:    1px solid #D8D8D8 !important;margin-top: 20px;" {/if*}>
                                                                        {*
                                                                        <header class="title-section main-box-header clearfix">
                                                                            <h2>$header</h2>
                                                                        </header> *}

                                                                        {* /body *}
                                                                        <div class="main-box-body clearfix"
                                                                             style="padding: 0 2px;!important;"
                                                                             id="tbl{$header|replace:' ':''}">
                                                                            {if $uitype eq 2202}
                                                                                {$CAMPOS_TIPO_GRID[$idField]}
                                                                            {/if}
                                                                            {assign var=detailD value=$detail}
                                                                            {foreach item=detail from=$detailD}
                                                                                {foreach key=label item=data from=$detail  name=detailBlock}
                                                                                    {assign var=keyid value=$data.ui}
                                                                                    {assign var=keyval value=$data.value}
                                                                                    {assign var=keytblname value=$data.tablename}
                                                                                    {assign var=keyfldname value=$data.fldname}
                                                                                    {assign var=keyfldid value=$data.fldid}
                                                                                    {assign var=keyoptions value=$data.options}
                                                                                    {assign var=keysecid value=$data.secid}
                                                                                    {assign var=keyseclink value=$data.link}
                                                                                    {assign var=keycursymb value=$data.cursymb}
                                                                                    {assign var=keysalut value=$data.salut}
                                                                                    {assign var=keyaccess value=$data.notaccess}
                                                                                    {assign var=keycntimage value=$data.cntimage}
                                                                                    {assign var=keyadmin value=$data.isadmin}
                                                                                    {assign var=display_type value=$data.displaytype}
                                                                                    {assign var=_readonly value=$data.readonly}
                                                                                    {if $label neq ''}
                                                                                        {if $keycntimage neq ''}
                                                                                            <input type="hidden"
                                                                                                   id="hdtxt_IsAdmin"
                                                                                                   value={$keyadmin} />{$keycntimage}
                                                                                        {elseif $keyid eq '14'}
                                                                                            <input type="hidden"
                                                                                                   id="hdtxt_IsAdmin"
                                                                                                   value={$keyadmin}/>
                                                                                        {/if}
                                                                                        {*$smarty.foreach.detailBlock.iteration|var_dump*}
                                                                                        {include file="DetailViewUI.tpl"}
                                                                                    {/if}

                                                                                {/foreach}
                                                                            {/foreach}
                                                                        </div>{*/ body *}
                                                                    </div>

                                                                </div>
                                                            {/if}
                                                        {/foreach}
                                                        {if $CAMPOS_TIPO_GRID}
                                                            <script type="text/javascript"
                                                                    src="include/js/gridFormValidate.js"></script>
                                                        {/if}
                                                        {block name="content-after-blocks"}{/block}
                                                    </form>
                                                    {if ($EDIT_PERMISSION == 'yes')}
                                                        <script type="text/javascript"
                                                                src="themes/centaurus/js/bootstrap-editable.js"></script>
                                                        {*loadEditableFiels arrayBlocs=$BLOCKS*}
                                                    {/if}
                                                    {* /Register *}
                                                </div> {* card body main-box *}
                                            </div> {*card *}
                                            {* Tarjeta aquí *}
                                        </div>
                                        <div class="col-md-12" style="margin-top: 20px">
                                            {*  related information *}
                                            {*include file='modules/grid_view/RelatedListCardView.tpl'*}
                                            {*  /related information *}
                                        </div>
                                    </div>
                                </div>{*div with scroll*}
                            </div>
                            {* cards gridPosition *}
                            <div class="{if ($gridPosition eq 'SIDE') ||($gridPosition eq MULL)}col-md-4{else}col-md-12{/if}">
                                {include file='modules/grid_view/DetailCardView.tpl'}
                            </div>
                            {* /cards *}
                        </div>
                        {* new detailView here *}
                    {/if}
                </div>
                {* Summary Action Plan *}
                <div id="tab-summary-plan-{$idActionPlanView}"
                     class="tab-pane fade in{if ($SELECTED_TAB == 'summary_plan')} active{/if}">
                    {include file='utils/HTMLPageLoanding.tpl'}
                </div>
                {* strategies and initiatives  *}
                <div id="tab-strategies-initiatives-{$idActionPlanView}"
                     class="tab-pane fade in{if ($SELECTED_TAB == 'strategies-initiatives')} active{/if}">
                    {include file='utils/HTMLPageLoanding.tpl'}
                </div>
                {* progress-plan *}
                <div id="tab-progress-plan-view-{$idActionPlanView}"
                     class="tab-pane fade in{if ($SELECTED_TAB == 'related_list')} active{/if}">
                    {include file='utils/HTMLPageLoanding.tpl'}
                </div>
            </div>
        </div>
    </div>
</div>
    </script>
{if (!$IS_MODAL)}
        <script type="text/javascript" src="modules/instancesdatasharing/data-sharing.js"></script>
    <script type="text/javascript" src="modules/notification_center/parleyScript.js?v=1.0.1"></script>
    <script type="text/javascript" src="webmail/program/js/common.min.js"></script>
    <script type="text/javascript" src="modules/webmail/webmail-utils.js?v=1.0.6"></script>
    <script type="text/javascript" src="themes/centaurus/js/classie.js"></script>
    <script type="text/javascript" src="themes/centaurus/js/modalEffects.js"></script>
    <script type="text/javascript" src="include/js/RelatedLists.js"></script>
    {elseif ($EDIT_PERMISSION == 'yes')}
    <script type="text/javascript" src="themes/centaurus/js/bootstrap-editable.js"></script>
    {*loadEditableFiels arrayBlocs=$BLOCKS*}
{/if}
{/strip}
