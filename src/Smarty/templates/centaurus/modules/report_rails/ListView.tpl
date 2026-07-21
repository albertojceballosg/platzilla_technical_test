{extends file='modules/report_rails/Base/SummaryReportPanelLayout.tpl'}
{strip}
    {block name="css"}
        <link rel="stylesheet" type="text/css" href="modules/report_rails/report_rails-utils.css"/>
        <style type="text/css">
            .summary-header {
                padding-bottom: 0;
            }
            .summary-body {
                padding-bottom: 0;
            }
        </style>
    {/block}
    {block name="config_url"}index.php?module=Settings&action=index&parenttab=Settings{/block}
    {block name="panel_title"}{$MOD['LBL_REPORT_RAILS_DESCRIPTION']}{/block}
    {block name="nav_tab"}{/block}
    {block name="body_content"}
        {include file='modules/report_rails/MaterReportListView.tpl'}
    {/block}
    {block name="summary_report"}{/block}
    {block name="context"}{/block}
    {block name="objetives_operational"}{/block}
    {block name="continuous_improvement"}{/block}
    {block name="tracking"}{/block}
    {block name="performance"}{/block}
    {block name="js"}
        <script type="text/javascript" src="modules/report_rails/report_rails-utils.js"></script>
        <script type="text/html" id="master-report-modal-template">
            {include file='modules/report_rails/Objects/MasterReportModal.tpl'}
        </script>
    {/block}
{/strip}