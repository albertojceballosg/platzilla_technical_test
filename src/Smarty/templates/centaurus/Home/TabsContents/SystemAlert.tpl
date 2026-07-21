{block name="scripts" append}
    <link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/libs/datepicker.css"/>
    <script type="text/javascript" src="themes/{$THEME}/js/bootstrap-datepicker.js"></script>
    <script type="text/javascript" src="themes/{$THEME}/js/bootstrap-datepicker.es.js"></script>
    <script src="modules/{$MODULE}/{$MODULE}.js" type="text/javascript"></script>
    <script src="modules/{$MODULE}/system-alert-utils.js" type="text/javascript"></script>
    <script src="themes/centaurus/js/protip.min.js"></script>
    <style type="text/css">
        {literal}
        .alert-grey {
            background-color: #eee;
            text-align: center !important;
        }
        .lft {
            text-align: left !important;
        }
        .ctr {
            text-align: center !important;
        }
        td {
            font-size: 0.875em !important;
        }
        .main-box-body button {
            font-size: 12px !important;
        }
        {/literal}
    </style>
{/block}
{block name="content"}
    {math equation= rand() assign= "idAlertListView"}
    <div class="row" style="padding: 6px 20px">
        <div class="col-lg-9 col-md-9 col-xs-9" style="margin-bottom: 10px">
            <div class="btn-group">
                {if isset($TAB_ACTIVE) }
                    {assign var='hasActiveTab' value=true}
                {else}
                    {assign var='hasActiveTab' value=false}
                {/if}
                {if (!empty ($ALERT_APPLICATIONS))}
                    {foreach $ALERT_APPLICATIONS as $key => $value}
                        {if $TAB_ACTIVE eq $key}
                            {assign var='hasActiveTab' value=false}
                        {/if}
                        <a href="#tab-{$key}"
                           class="btn {if (!$hasActiveTab)}btn-primary{else}btn-default{/if}"
                           id="li--{$key}" onclick="SystemAlertUtils.loadAlerts(this, '{$idAlertListView}')"
                           data-toggle="tab">{$value}</a>
                        {assign var='hasActiveTab' value=true}
                    {/foreach}
                {/if}
            </div>
        </div>
        <div class="col-md-3">
            <div class="pull-right " {*style="padding-left: 15px;vertical-align: bottom;margin-top: 13px;" align="center"*}>
                <button type="button" class="md-trigger btn btn-primary" data-modal="createAlert"
                        onclick="SystemAlertUtils.openModalAlert('create', '', '', '{$idAlertListView}')"> {$MODSTRING.LBL_CREATE_ALERT}</button>{*createAlert*}
            </div>
        </div>
    </div>
    <div class="main-box-body">
        <div class="tabs-wrapper">
            <div class="tab-content" style="padding-left: 0!important;padding-right: 0!important;">
                {if isset($TAB_ACTIVE) }
                    {assign var='hasActiveTab' value=true}
                {else}
                    {assign var='hasActiveTab' value=false}
                {/if}
                {if (!empty ($ALERT_APPLICATIONS))}
                    <input type="hidden" name="newblock" id="newblock" value="">
                    <input type="hidden" name="dinamicViewScale" id="dinamicViewScale" value="">
                    {assign var='applicationCodes' value=array_keys ($ALERT_APPLICATIONS)}
                    {foreach from=$applicationCodes item=applicationCode name=applicationCodes}
                        {if $TAB_ACTIVE eq $applicationCode}
                            {assign var='hasActiveTab' value=false}
                        {/if}
                        <div id="tab-{$applicationCode}"
                             class="tab-pane fade in{if (!$hasActiveTab)} active{/if} loadAlertstabs">
                            {if $smarty.foreach.applicationCodes.iteration eq 1}
                                {include file="modules/systemalerts/DetailViewAllAlerts.tpl"}
                            {else}
                                {include file="modules/systemalerts/DetailViewAlerts.tpl"}
                            {/if}
                        </div>
                        {assign var='hasActiveTab' value=true}
                    {/foreach}
                {/if}
            </div>
        </div>
    </div>
    <div class="md-modal md-effect-1" id="viewModules"></div>
    <div class="md-modal md-effect-1" id="viewIndicators"></div>
    <div class="md-modal md-effect-1" id="createAlert"></div>
    {include file="modules/systemalerts/Wizard/AlertWizard.tpl"}
{/block}