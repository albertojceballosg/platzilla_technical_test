{strip}
    {if ($OBJECTIVE neq NULL)}
        {assign var='id' value=$OBJECTIVE->getId()}
        {assign var='companyarea' value=$OBJECTIVE->getCompanyArea()}
        {assign var='companyphase' value=$OBJECTIVE->getCompanyPhases()}
        {assign var='companytype' value=$OBJECTIVE->getCompanyTypes()}
        {assign var='frequency' value=$OBJECTIVE->getFrequency()}
        {assign var='howtodo' value=$OBJECTIVE->getHowToDo()}
        {assign var='todo' value=$OBJECTIVE->getToDo()}
        {assign var="onboarding" value=$OBJECTIVE->isOnBoarding()}
        {assign var='status' value=$OBJECTIVE->getStatus()}
    {else}
        {assign var='id' value=null}
        {assign var='companyarea' value=null}
        {assign var='companyphase' value=array()}
        {assign var='companytype' value=array()}
        {assign var='frequency' value=array()}
        {assign var='howtodo' value=null}
        {assign var='todo' value=null}
        {assign var='status' value=null}
        {assign var="onboarding" value=null}
    {/if}
    <style>
        .row-how-use {
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -ms-flex-wrap: wrap;
            flex-wrap: wrap;
            margin-right: -15px;
            margin-left: -15px
        }

        .justify-content-center {
            -webkit-box-pack: center !important;
            -ms-flex-pack: center !important;
            justify-content: center !important
        }

        .no-gutters > .col,
        .no-gutters > [class*=col-] {
            padding-right: 1px;
            padding-left: 1px;
        }
    </style>
    <link rel="stylesheet" href="themes/centaurus/css/libs/bootstrap-timepicker.css" type="text/css"/>
    <link rel="stylesheet" type="text/css" href="modules/News/News.css"/>
    <form class="form-horizontal" name="howToUse-form" role="form" method="post" action="index.php"
          onsubmit="return OKRsUtils.validateProfileForm (this);">
        <input type="hidden" name="module" value="okrs"/>
        <input type="hidden" name="action" value="SaveObjective"/>
        <input type="hidden" name="record" value="{$id}"/>
        <input type="hidden" name="return_action" value="{$RETURN_ACTION}"/>
        <input type="hidden" name="return_module" value="{$RETURN_MODULE}"/>
        {* <input type="hidden" name="Ajax" value="true"/> *}
        <div class="row">
            <div class="col-xs-12">
                <h1 class="pull-left">
                    <a href="index.php?module=okrs&action=ListView&tab={$SELECTED_TAB}&parenttab=Settings">{$MOD['okrs']}</a>
                </h1>
                <div class="action-bar pull-right">
                    <button type="submit" class="btn btn-info">{$MOD['BTN_SAVE_OBJECTIVE']}</button>
                    <a href="index.php?module=okrs&action=ListView&tab={$SELECTED_TAB}&parenttab=Settings"
                       class="btn btn-warning"
                       style="margin-left: 5px;">Cancelar</a>
                </div>
            </div>
        </div>
        {if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
            <div class="row">
                <div class="alert alert-{if (isset ($IS_ERROR)) && ($IS_ERROR)}danger{else}success{/if}">
                    <strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
                </div>
            </div>
        {/if}
        <div class="row">
            <div class="col-xs-12">
                <div class="main-box">
                    <header class="main-box-header clearfix">
                        <h2 class="pull-left">{$MOD['TITLE_OBJECTIVE_GRAL_INFORMATION']}</h2>
                    </header>
                    <div class="main-box-body">
                        <div class="row">
                            {* Type *}
                            <div class="form-group">
                                <label for="okr_objective_companytype" class="col-md-3 control-label">{$MOD['OBJECTIVE_LABEL_COMPAY_TYPE']}</label>
                                <div id="obj-div-are" class="col-md-7">
                                    <select multiple class="form-control" name="companytype[]" id="companytype"
                                            title="{$MOD['OBJECTIVE_TITLE_COMPAY_TYPE']}">
                                        {if $COMPANY_TYPE neq NULL}
                                            {foreach $COMPANY_TYPE as $type}
                                                <option value="{$type}"
                                                        {if in_array($type,$companytype)}selected {/if}>
                                                    {$MOD[$type]}</option>
                                            {/foreach}
                                        {/if}
                                    </select>
                                    <span id="obj-companytype" class="help-block"></span>
                                </div>
                            </div>

                            {* Phase *}
                            <div class="form-group">
                                <label for="okr_objective_companyphase" class="col-md-3 control-label">{$MOD['OBJECTIVE_LABEL_COMPAY_PHASE']}</label>
                                <div id="obj-div-are" class="col-md-7">
                                    <select multiple class="form-control" name="companyphase[]" id="companyphase"
                                            title="{$MOD['OBJECTIVE_TITLE_COMPAY_PHASE']}">
                                        {if $COMPANY_PHASE neq NULL}
                                            {foreach $COMPANY_PHASE as $phase}
                                                <option value="{$phase}"
                                                        {if in_array($phase, $companyphase)}selected {/if}>
                                                    {$MOD[$phase]}</option>
                                            {/foreach}
                                        {/if}
                                    </select>
                                    <span id="obj-companyphase" class="help-block"></span>
                                </div>
                            </div>

                            {* Area *}
                            <div class="form-group">
                                <label for="okr_objective_companyarea" class="col-md-3 control-label">{$MOD['OBJECTIVE_LABEL_COMPAY_AREA']}</label>
                                <div id="obj-div-are" class="col-md-7">
                                    <select class="form-control" name="companyarea" id="companyarea"
                                            title="{$MOD['OBJECTIVE_TITLE_COMPAY_AREA']}">
                                        {if $COMPANY_AREAS neq NULL}
                                            {foreach $COMPANY_AREAS as $area}
                                                <option value="{$area}"
                                                        {if $companyarea eq $area}selected {/if}>
                                                    {$MOD[$area]}</option>
                                            {/foreach}
                                        {/if}
                                    </select>
                                    <span id="obj-companyarea" class="help-block"></span>
                                </div>
                            </div>
                            {* To do *}
                            <div class="form-group">
                                <label for="okr_objective_todo" class="col-md-3 control-label">{$MOD['OBJECTIVE_LABEL_TODO']}</label>
                                <div id="obj-div-name" class="col-md-7">
                                    <input type="text" class="form-control" id="okr_objective_todo" name="todo"
                                           value="{$todo}"
                                           title="{$MOD['OBJECTIVE_TITLE_TODO']}"
                                           placeholder="{$MOD['OBJECTIVE_PLACEHOLDER_TODO']}">
                                    <span id="obj-todo" class="help-block"></span>
                                </div>
                            </div>
                            {* Profile howtodo *}
                            <div class="form-group">
                                <label for="okr_objective_howtodo"
                                       class="col-md-3 control-label">{$MOD['OBJECTIVE_LABEL_HOWTODO']}</label>
                                <div id="obj-div-howtodo" class="col-md-7">
                                    <textarea class="form-control" name="howtodo" id="okr_objective_howtodo"
                                              rows="3"
                                              placeholder="{$MOD['OBJECTIVE_PLACEHOLDER_HOWTODO']}">{$howtodo}</textarea>
                                    <span id="obj-howtodo" class="help-block"></span>
                                </div>
                            </div>
                            {* company Sector *}
                            <div class="form-group">
                                <label for="okr_objective_frequency" class="col-md-3 control-label">{$MOD['OBJECTIVE_LABEL_FREQUENCY']}</label>
                                <div id="obj-div-frequency" class="col-md-7">
                                    <select class="form-control" name="frequency" id="frequency"
                                            title="{$MOD['OBJECTIVE_TITLE_FREQUENCY']}">
                                        {foreach $FREQUENCY as $freq}
                                            <option value="{$freq}"
                                                    {if $frequency eq $freq}selected{/if} >{$MOD[$freq]}</option>
                                        {/foreach}
                                    </select>
                                    <span id="obj-frequency" class="help-block"></span>
                                </div>
                            </div>
                            {* status *}
                            <div class="form-group">
                                <label for="okr_objective_staus" class="col-md-3 control-label">{$MOD['OBJECTIVE_LABEL_STATUS']}</label>
                                <div class="col-md-7">
                                    <select class="form-control" name="status" id="status_profile" title="{$MOD['OBJECTIVE_TITLE_STATUS']}">
                                        {foreach $AVAILABLE_STATUS as $keyStatus}
                                            <option value="{$keyStatus}"
                                                    {if $keyStatus eq $status}selected{/if} >{$MOD[$keyStatus]}</option>
                                        {/foreach}
                                    </select>
                                </div>
                            </div>
                            {* on bording *}
                            <div class="form-group">
                                <label for="okr_objective_onboarding" class="col-md-3 control-label">{$MOD['OBJECTIVE_LABEL_ON_BORDING']}</label>
                                <div class="col-md-7">
                                    <select class="form-control" name="onboarding" id="onboarding title="{$MOD['OBJECTIVE_TITLE_ON_BORDING']}">
                                        {foreach $IS_ON_BOARDIMG as $isOnBoarding}
                                            <option value="{$isOnBoarding}"
                                                    {if $isOnBoarding eq $onboarding}selected{/if} >{$MOD[$isOnBoarding]}</option>
                                        {/foreach}
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.js"></script>
    <script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.es.js"></script>
    <script type="text/javascript" src="themes/centaurus/js/bootstrap-timepicker.min.js"></script>
    <script type="text/javascript" src="include/ckeditor/ckeditor.js"></script>
    <script type="text/javascript" src="modules/okrs/okrs-utils.js"></script>
    {if $PROFILES_HOW_USE neq NULL}
        <script type="text/javascript">
            {literal}
            OKRsUtils.reloadProfile({/literal}{$PROFILES_HOW_USE|json_encode}{literal});
            {/literal}
        </script>
    {/if}
{/strip}