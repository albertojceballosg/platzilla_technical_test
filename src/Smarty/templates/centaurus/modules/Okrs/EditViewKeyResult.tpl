{strip}
    {if ($KEY_RESULT neq NULL)}
        {assign var='id' value=$KEY_RESULT->getId()}
        {assign var='description' value=$KEY_RESULT->getDescription()}
        {assign var='goal_value' value=$KEY_RESULT->getGoalValue()}
        {assign var='objectiveid' value=$KEY_RESULT->getObjectiveId()}
        {assign var='value_actual' value=$KEY_RESULT->getValueActual()}
        {assign var='status' value=$KEY_RESULT->getStatus()}
    {else}
        {assign var='id' value=null}
        {assign var='description' value=null}
        {assign var='goal_value' value=null}
        {assign var='objectiveid' value=null}
        {assign var='value_actual' value=null}
        {assign var='status' value=null}
    {/if}
    {if ($OBJECTIVE neq NULL)}
        {assign var='idObjective' value=$OBJECTIVE->getId()}
        {assign var='companyarea' value=$OBJECTIVE->getCompanyArea()}
        {assign var='frequency' value=$OBJECTIVE->getFrequency()}
        {assign var='howtodo' value=$OBJECTIVE->getHowToDo()}
        {assign var='todo' value=$OBJECTIVE->getToDo()}
        {assign var='status' value=$OBJECTIVE->getStatus()}
    {else}
        {assign var='idObjective' value=null}
        {assign var='companyarea' value=null}
        {assign var='frequency' value=null}
        {assign var='howtodo' value=null}
        {assign var='todo' value=null}
        {assign var='status' value=null}
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
        <input type="hidden" name="action" value="SaveKeyResult"/>
        <input type="hidden" name="record" value="{$id}"/>
        <input type="hidden" name="tab" value="key_results"/>
        <input type="hidden" name="return_action" value="{$RETURN_ACTION}"/>
        <input type="hidden" name="return_module" value="{$RETURN_MODULE}"/>
        <div class="row">
            <div class="col-xs-12">
                <h1 class="pull-left">
                    <a href="index.php?module=okrs&action=ListView&tab={$SELECTED_TAB}&parenttab=Settings">{$MOD['okrs']}</a>
                </h1>
                <div class="action-bar pull-right">
                    <button type="submit" class="btn btn-info">{$MOD['BTN_SAVE_KEY_RESULT']}</button>
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
                        <h2 class="pull-left">{$MOD['TITLE_KEY_RESULT_GRAL_INFORMATION']}</h2>
                    </header>
                    <div class="main-box-body">
                        <div class="row">
                            {* Objective *}
                            <div class="form-group">
                                <label for="okr_key_result_objectiveid" class="col-md-3 control-label">{$MOD['KEY_RESULT_LABEL_OBJECTIVE']}</label>
                                <div id="obj-div-are" class="col-md-7">
                                    <select class="form-control" name="objectiveid" id="objectiveid"
                                            title="{$MOD['KEY_RESULT_TITLE_OBJECTIVE']}">
                                        {if $OBJECTIVES neq NULL}
                                            {foreach $OBJECTIVES as $objective}
                                                <option value="{$objective->getId()}"
                                                        {if ($objective->getId() eq $objectiveid) || ($objective->getId() eq $OBJECTIVE_ID)}selected {/if}>
                                                    {$objective->getToDo()}</option>
                                            {/foreach}
                                        {/if}
                                    </select>
                                    <span id="obj-objectiveid" class="help-block"></span>
                                </div>
                            </div>
                            {* Descripction *}
                            <div class="form-group">
                                <label for="okr_key_result_description"
                                       class="col-md-3 control-label">{$MOD['KEY_RESULT_LABEL_DESCRIPTION']}</label>
                                <div id="obj-div-description" class="col-md-7">
                                    <textarea class="form-control" name="description" id="okr_key_result_description"
                                              rows="3"
                                              title="{$MOD['KEY_RESULT_TITLE_DESCRIPTION']}"
                                              placeholder="{$MOD['KEY_RESULT_PLACEHOLDER_DESCRIPTION']}">{$description}</textarea>
                                    <span id="obj-description" class="help-block"></span>
                                </div>
                            </div>
                            {* Goasl value *}
                            <div class="form-group">
                                <label for="okr_key_result_goal_value" class="col-md-3 control-label">{$MOD['KEY_RESULT_LABEL_GOAL_VALUE']}</label>
                                <div id="obj-div-name" class="col-md-7">
                                    <input type="number" class="form-control" id="okr_key_result_goal_value" name="goal_value"
                                           value="{$goal_value}"
                                           title="{$MOD['KEY_RESULT_TITLE_GOAL_VALUE']}"
                                           placeholder="{$MOD['KEY_RESULT_PLACEHOLDER_GOAL_VALUE']}">
                                    <span id="obj-goal_value" class="help-block"></span>
                                </div>
                            </div>

                            {* company Sector *}
                            <div class="form-group">
                                <label for="okr_key_result_frequency" class="col-md-3 control-label">{$MOD['KEY_RESULT_LABEL_FREQUENCY']}</label>
                                <div id="obj-div-frequency" class="col-md-7">
                                    <select class="form-control" name="frequency" id="frequency"
                                            title="{$MOD['KEY_RESULT_TITLE_FREQUENCY']}">
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
                                <label for="okr_key_result_staus" class="col-md-3 control-label">{$MOD['KEY_RESULT_LABEL_STATUS']}</label>
                                <div class="col-md-7">
                                    <select class="form-control" name="status" id="status_profile" title="{$MOD['KEY_RESULT_TITLE_STATUS']}">
                                        {foreach $AVAILABLE_STATUS as $keyStatus}
                                            <option value="{$keyStatus}"
                                                    {if $keyStatus eq $status}selected{/if} >{$MOD[$keyStatus]}</option>
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