{extends file='modules/report_rails/Base/SummaryReportPanelLayout.tpl'}
{strip}
    {block name="css"}
        <link rel="stylesheet" type="text/css" href="modules/report_rails/report_rails-utils.css"/>
        <style type="text/css">
            .summary-header {
                padding-bottom: 20px;
            }

            .summary-body {
                padding-bottom: 20px;
            }
        </style>
    {/block}
    {block name="config_url"}index.php?module=report_rails&action=index&parenttab=Settings{/block}
    {block name="panel_title"}Reporte de: {$MASTER_REPORT->getAgent()->getName()} para la Instancia: {$MASTER_REPORT->getCodeInstance()} Semana desde: {$MASTER_REPORT->getDateStart()|date_es_format} hasta {$MASTER_REPORT->getDueDate()|date_es_format}{/block}
    {block name="master_report"}{/block}
    {block name="nav_tab"}
        <ul class="nav nav-tabs" id="MAIN-TAB">
            {* Summary report *}
            <li {if ($SELECTED_TAB eq 'SUMMARY_REPORT')} class="active main-tab" {else} class="main-tab" {/if}>
                <a data-toggle="tab" href="#SUMMARY_REPORT-{$idSummaryReport}">{$MOD['LBL_SUMMARY_REPORT']}</a>
            </li>
            {* Performace config panel *}
            <li {if ($SELECTED_TAB eq 'PERFORMANCE')}class="active main-tab" {else} class="main-tab" {/if}>
                <a data-toggle="tab" href="#PERFORMANCE-{$idSummaryReport}">{$MOD['LBL_PERFORMANCE']}</a>
            </li>
            {* AGreements *}
            <li {if ($SELECTED_TAB eq 'AGREEMENTS')}class="active main-tab" {else} class="main-tab" {/if}>
                <a data-toggle="tab" href="#AGREEMENTS-{$idSummaryReport}">{$MOD['LBL_AGREEMENTS']}</a>
            </li>
            {* context *}
            <li class="dropdown">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                    {$MOD['LBL_CONTEXT']} <span class="caret"></span></a>
                <ul class="dropdown-menu" id="PERFORMANCE">
                    {* Operational objetives *}
                    <li {if ($SELECTED_TAB eq 'PLANNING_COMPLIANCE')} class="active" {else} class="" {/if}>
                        <a data-toggle="tab" rel="{$MASTER_REPORT->getWeeklyReportId()}" data-loading="FALSE"
                           onclick="ReportRailesUtils.getWeeklyReport(this,'{$idSummaryReport}')"
                           href="#PLANNING_COMPLIANCE-{$idSummaryReport}">{$MOD['LBL_CONTEXT_THIS_WEEK']}</a>
                    </li>
                    {* Continuos Improvement *}
                    <li {if ($SELECTED_TAB eq 'NEXT_WEEK')} class="active" {else} class=""{/if}>
                        <a data-toggle="tab" rel="{$MASTER_REPORT->getUpcomingReportId()}" data-loading="FALSE"
                           onclick="ReportRailesUtils.getUpcomingReport(this,'{$idSummaryReport}')"
                           href="#NEXT_WEEK-{$idSummaryReport}">{$MOD['LBL_CONTEXT_NEXT_WEEK']}</a>
                    </li>
                    {* Box Score *}
                    <li {if ($SELECTED_TAB eq 'BOX_SCORE')} class="active" {else} class=""{/if}>
                        <a data-toggle="tab" rel="{$MASTER_REPORT->getId()}"
                           data-loading="FALSE" data-module="report_rails" data-period=""
                           onclick="ReportRailesUtils.getBoxScoreReport(this,'{$idSummaryReport}')"
                           href="#BOX_SCORE-{$idSummaryReport}">{$MOD['LBL_CONTEXT_BOX_SCORE']}</a>
                    </li>

                </ul>
            </li>
            {* Objetives *}
            <li class="dropdown">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                    {$MOD['LBL_OBJETIVES']} <span class="caret"></span>
                </a>
                <ul class="dropdown-menu" id="OBJETIVES">
                    {* Operational objetives *}
                    <li {if ($SELECTED_TAB eq 'OBJETIVES_OPERATIONAL')} class="active" {else} class="" {/if}>
                        <a data-toggle="tab"
                           href="#OBJETIVES_OPERATIONAL-{$idSummaryReport}">{$MOD['LBL_OBJETIVES_OPERATIONAL']}</a>
                    </li>
                    {* Continuos Improvement *}
                    <li {if ($SELECTED_TAB eq 'CONTINUOUS_IMPROVEMENT')} class="active" {else} class="" {/if}>
                        <a data-toggle="tab"
                           href="#CONTINUOUS_IMPROVEMENT-{$idSummaryReport}">{$MOD['LBL_CONTINUOUS_IMPROVEMENT']}</a>
                    </li>
                    {* traking *}
                    <li {if ($SELECTED_TAB eq 'TRACKING')} class="active" {else} class="" {/if}>
                        <a data-toggle="tab" href="#TRACKING-{$idSummaryReport}">{$MOD['LBL_TRACKING']}</a>
                    </li>
                </ul>
            </li>
        </ul>
    {/block}
    {block name="body_content"}
        {include file='modules/report_rails/Objects/SummaryReportTabEstructure.tpl'}
    {/block}
    {* Tabs Contenet*}
    {block name="summary_report"}
        {include file='modules/report_rails/ReportStatusEditView.tpl'}
    {/block}
    {block name="performance"}
        {include file='modules/report_rails/PerformanceListView.tpl'}
    {/block}
    {block name="agreements"}
        {include file='modules/report_rails/AgreementsListView.tpl'}
    {/block}
    {block name="planning_compliance"}
        {include file="utils/HTMLPageLoanding.tpl"}
    {/block}
    {block name="next_week"}
        {include file="utils/HTMLPageLoanding.tpl"}
    {/block}
    {block name="box_score"}
        {include file="utils/HTMLPageLoanding.tpl"}
    {/block}
    {block name="objetives_operational"}{/block}
    {block name="continuous_improvement"}{/block}
    {block name="tracking"}{/block}

    {block name="js"}
        <script type="text/javascript" src="modules/report_rails/report_rails-utils.js"></script>
    {/block}
{/strip}