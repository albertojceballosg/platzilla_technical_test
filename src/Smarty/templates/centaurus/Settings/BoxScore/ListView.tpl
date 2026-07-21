{extends file='Settings/BoxScore/Base/ListViewLayout.tpl'}
{strip}
    {block name="css"}
        {*<link rel="stylesheet" type="text/css" href="modules/report_rails/report_rails-utils.css"/>*}
        <style type="text/css">
            .summary-header {
                padding-bottom: 0;
            }
            .summary-body {
                padding-bottom: 0;
            }
            .bs-table {
                font-family: 'Roboto Light, Roboto, sans-serif', serif;
                font-size: medium;
                margin-top: 20px;

            }
            .bs-table th, td {
                padding:        4px 3px!important;
                line-height:    1.3 !important;
                vertical-align: top !important;
            }
            .column-title {
                text-align: left;3
                width:      15%;
            }
            .column-description {
                text-align: left;
                width:      20%;
            }
            .column-field {
                text-align: left;
                width:      14%;
            }
            .column-action {
                text-align: center!important;
                width:      16%;
            }
            .column-general-center {
                text-align: center!important;
                width:      10%;
            }
            .column-general-left {
                text-align: left;
                width:      10%;
            }
            .column-status {
                text-align: center!important;
                width:      8%;
            }
            .instance-list {
                list-style-type: square !important;
                max-height: 160px;
                overflow-y: auto;
                overflow-x: hidden;
            }
            #share_box_score-{$idBoxScore} .modal-backdrop {
                opacity: 0.7 !important;
            }

        </style>
    {/block}
    {block name="fa_icon"}<i class="fa fa-cogs yellow-bg"></i>{/block}
    {block name="config_url"}index.php?module=Settings&action=index&parenttab=Settings{/block}
    {block name="panel_title"}{$MOD_STRINGS['LBL_BOX_SCORE_TITLE']}{/block}
    {block name="nav_tab"}
        <ul class="nav nav-tabs">
            {* Box Score Mother *}
            <li {if ($SELECTED_TAB eq 'BOX_SCORE_MOTHER')} class="active"{/if}>
                <a data-toggle="tab" href="#BOX_SCORE_MOTHER-{$idBoxScore}">{$MOD_STRINGS['LBL_BOX_SCORE_MOTHER']}</a>
            </li>
            {* Box Score Douthers *}
            <li {if ($SELECTED_TAB eq 'BOX_SCORE_DAUGHTERS')} class="active"{/if}>
                <a data-toggle="tab" href="#BOX_SCORE_DAUGHTERS-{$idBoxScore}">{if !$IS_INSTANCE}{$MOD_STRINGS['LBL_BOX_SCORE_DAUGHTERS']}{else}{$MOD_STRINGS['LBL_BOX_SCORE_DAUGHTER']}{/if}</a>
            </li>
        </ul>
    {/block}
    {block name="body_content"}
        <div class="tab-content">
            {* Box Score Mother *}
            <div id="BOX_SCORE_MOTHER-{$idBoxScore}" style="padding-top: 15px"
                 class="tab-pane fade {if ($SELECTED_TAB eq 'BOX_SCORE_MOTHER')}active in{/if}">
                {include file='Settings/BoxScore/BoxScoreMother.tpl'}
            </div>
            {* Box Score Douthers *}
            <div id="BOX_SCORE_DAUGHTERS-{$idBoxScore}"
                 class="tab-pane fade {if ($SELECTED_TAB eq 'BOX_SCORE_DAUGHTERS')}active in{/if}">
                {include file='Settings/BoxScore/BoxScoreDaughters.tpl'}
            </div>
        </div>
    {/block}
    {block name="js"}
        <script type="text/javascript" src="modules/Settings/boxscore-inventory-utils.js"></script>
    {/block}
    {block name="indicators_modal"}
        <div class="modal fade" id="share_box_score-{$idBoxScore}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content"></div>
            </div>
        </div>
    {/block}
{/strip}