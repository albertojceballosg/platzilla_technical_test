{block name="scripts" append}
    <link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/libs/datepicker.css"/>
    <script type="text/javascript" src="themes/{$THEME}/js/bootstrap-datepicker.js"></script>
    <script type="text/javascript" src="themes/{$THEME}/js/bootstrap-datepicker.es.js"></script>
    <script src="modules/{$MODULE}/{$MODULE}.js" type="text/javascript"></script>
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
    <script type="text/javascript">
        {literal}
        jQuery(document).ready(function () {
            var appcode = {/literal}{if (isset ($TAB_ACTIVE))}'{$TAB_ACTIVE}'
            {else}''{/if}{literal};

            if (appcode === 'all') {
                jQuery('#newblock').val('reload');
                jQuery('#dinamicViewScale').val(jQuery('#viewPeriod').val());
                var obj = jQuery('#li--' + appcode);
                obj.click();
            }
            jQuery('#date_from').datepicker({format: 'yyyy-mm-dd', language: 'es', weekStart: 1});
            jQuery('#date_to').datepicker({format: 'yyyy-mm-dd', language: 'es', weekStart: 1});
        });
        {/literal}
    </script>
{/block}

{block name="content"}
    <div class="col-lg-12">
        <div class="row">
            <div class="col-lg-12">
                <div class="pull-left">
                    <h1 style="margin-left: -3px;font-weight: bold">
                        {$MODSTRING.systemalerts}
                    </h1>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-9 col-md-9 col-xs-9" style="margin-bottom: 25px">
                <div class="btn-group">
                    {if isset($TAB_ACTIVE) }
                        {assign var='hasActiveTab' value=true}
                    {else}
                        {assign var='hasActiveTab' value=false}
                    {/if}
                    {if (!empty ($APPLICATIONS))}
                        {assign var='applicationCodes' value=array_keys ($APPLICATIONS)}
                        {foreach from=$applicationCodes item=applicationCode name=applicationCodes}
                            {if $TAB_ACTIVE eq $applicationCode}
                                {assign var='hasActiveTab' value=false}
                            {/if}
                            <a href="#tab-{$applicationCode}"
                               class="btn {if (!$hasActiveTab)}btn-primary{else}btn-default{/if}"
                               id="li--{$applicationCode}" onclick="loadAlerts(this)"
                               data-toggle="tab">{$APPLICATIONS[$applicationCode].app_name}</a>
                            {assign var='hasActiveTab' value=true}
                        {/foreach}
                    {/if}
                </div>
            </div>
            <div class="col-md-3">
                <div class="pull-right " {*style="padding-left: 15px;vertical-align: bottom;margin-top: 13px;" align="center"*}>
                    <button type="button" class="md-trigger btn btn-primary" data-modal="createAlert"
                            onclick="callAddAlertsIndicators('create', '', '')"> {$MODSTRING.LBL_CREATE_ALERT}</button>
                </div>
            </div>
        </div>
        <div class="main-box-body" {* clearfix style="width:100%;background-color:#FFFFFF;"*}>
            <div class="tabs-wrapper">
                <div class="tab-content" style="padding-left: 0!important;padding-right: 0!important;">
                    {if isset($TAB_ACTIVE) }
                        {assign var='hasActiveTab' value=true}
                    {else}
                        {assign var='hasActiveTab' value=false}
                    {/if}
                    {if (!empty ($APPLICATIONS))}
                        <input type="hidden" name="newblock" id="newblock" value="">
                        <input type="hidden" name="dinamicViewScale" id="dinamicViewScale" value="">
                        {assign var='applicationCodes' value=array_keys ($APPLICATIONS)}
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
    </div>
    <div class="md-modal md-effect-1" id="viewModules"></div>
    <div class="md-modal md-effect-1" id="viewIndicators"></div>
    <div class="md-modal md-effect-1" id="createAlert"></div>
{/block}