{extends file='modules/indicatorspanel/Base/IndicatorPanelLayOut.tpl'}
{block name="css"}
    <style type="text/css">
        /* Important part */
        .modal-open {
            overflow: scroll !important;
            padding-right: 0px !important;
        }

        .main-box {
            box-shadow: 0px 0px 0px 0 #FFFFFF !important;
            border-radius: 0px !important;
        }

        .base-list-container {
            background-color: #ffffff;
            margin: 0px -13px !important;
            border-top: 1px solid #D8D8D8 !important;
            height: auto;
            min-height: 1150px !important;
        }

        .nav-platzilla {
            margin-bottom: -3px !important;
        }

        .nav-platzilla > li > a {
            font-weight: bold !important;
        }

        .nav-platzilla > li.active {
            background-color: #FFFFFF;
            margin-bottom: -3px !important;
            height: 46px;
        }
        /* Important part Detail View */
            /* Important part Detail View */
        .alert-grey {
                background-color: #eee;
                text-align: center !important;
            }

            .rgt {
                text-align: right;
            }

            th, .ctr {
                text-align: center !important;
            }

            .lft {
                text-align: left !important;
            }

            .pi-tools {
                display: block !important;
                float: right !important;
                color: #f56954;

            }

            .show-tools:hover .pi-tools {
                display: inline-block;
            }

            .show-tools {
                color: #566573 !important;
            }

            .main-box-body button {
                font-size: 12px !important;
            }

            .isPiDisabled > a {
                color: currentColor;
                display: inline-block; /* For IE11/ MS Edge bug */
                pointer-events: none;
                text-decoration: none;
            }
            .railes_red {
                color: red !important;
                background-color: red;
            }

            .railes_green {
                color: darkgreen !important;
                background-color: green !important;
            }
            .railes_red:hover {
                color: greenyellow !important;
                background-color: green !important;
            }
            .railes_green:hover {
                color: red !important;
                background-color: red !important;
            }
    </style>
{/block}
{block name="js"}
    <script src="modules/{$MODULE}/{$MODULE}.js" type="text/javascript"></script>
    <script src="modules/indicatorspanel/indicatorspanel-utils.js" type="text/javascript"></script>
    <script>
        {literal}
        jQuery(document).ready(function () {
            var appcode = {/literal}{if (isset ($TAB_ACTIVE))}'{$TAB_ACTIVE}'
            {else}''{/if}{literal};
            var monthSelect = {/literal}{if (isset ($MONTH_SEARCH))}'{$MONTH_SEARCH}'
            {else}''{/if}{literal};

            var monthSearch = jQuery('#monthsearch');
            if (monthSearch.val() == '' && monthSelect == '') {
                var date = new Date();
                var m;
                m = date.getMonth() + 1;
                if (m < 10) {
                    monthSearch.val('0' + m);
                } else {
                    monthSearch.val(m);
                }
            } else if (monthSelect != '') {
                monthSearch.val(monthSelect);
            }
            var view = jQuery('#viewScale');
            if (view.val() == '') {
                view.val('Month');
            }

            if (appcode != '') {
                jQuery('#newblock').val('reload');
                jQuery('#dinamicMonthsearch').val(jQuery('#monthsearch').val());
                jQuery('#dinamicViewScale').val(jQuery('#viewScale').val());
                var obj = jQuery('#li--' + appcode);
                obj.click();
            }

        });
        {/literal}
    </script>
{/block}
{block name="nav_tab"}
    <ul class="nav nav-tabs nav-platzilla">
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
                <li class="{if (!$hasActiveTab)} active {/if} loadIndicators" id="li--{$applicationCode}"
                    onclick="loadIndicators(this)">
                    <a href="#tab-{$applicationCode}" data-toggle="tab">{$APPLICATIONS[$applicationCode].app_name}</a>
                </li>
                {assign var='hasActiveTab' value=true}
            {/foreach}
        {/if}
    </ul>
{/block}
{block name="body_content"}
    <div class="tab-content">
        {if isset($TAB_ACTIVE) }
            {assign var='hasActiveTab' value=true}
        {else}
            {assign var='hasActiveTab' value=false}
        {/if}
        {if (!empty ($APPLICATIONS))}
            <input type="hidden" name="newblock" id="newblock" value="">
            <input type="hidden" name="dinamicMonthsearch" id="dinamicMonthsearch" value="">
            <input type="hidden" name="dinamicViewScale" id="dinamicViewScale" value="">
            {assign var='applicationCodes' value=array_keys ($APPLICATIONS)}
            {foreach from=$applicationCodes item=applicationCode name=applicationCodes}
                {if $TAB_ACTIVE eq $applicationCode}
                    {assign var='hasActiveTab' value=false}
                {/if}
                <div id="tab-{$applicationCode}"
                     class="tab-pane fade in{if (!$hasActiveTab)} active{/if} loadIndicatorstabs">
                    {if $smarty.foreach.applicationCodes.iteration eq 1}
                        {include file="modules/indicatorspanel/AllAppDetailView.tpl"}
                    {else}
                        {include file="modules/indicatorspanel/DetailView.tpl"}
                    {/if}
                </div>
                {assign var='hasActiveTab' value=true}
            {/foreach}
        {/if}
    </div>
{/block}
{block name="indicators_modal"}
    <div class="md-modal md-effect-1" id="addIndicators"></div>
    <div class="md-modal md-effect-1" id="addValues"></div>
    <div class="md-modal md-effect-1" id="addCalcules"></div>
{/block}