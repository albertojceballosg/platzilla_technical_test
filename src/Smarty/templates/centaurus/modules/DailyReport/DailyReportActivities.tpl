{extends file="modules/DailyReport/base/DailyReportMainLayout.tpl"}
{block name="css"}
    <link rel="stylesheet" type="text/css" href="modules/grid_view/grid-view.css"/>
    <style>
        .daily-report:visited {
            color: white !important;
            font-weight: bold;
        }

        .daily-report:link {
            color: white !important;
            font-weight: bold;
        }

        .daily-report:hover {
            color: white !important;
            font-weight: bold;
        }
    </style>
{/block}

{* Logros del día *}
{block name="achievements-of-the-day"}
    {if $VIEW eq NULL}
        {include file='modules/DailyReport/AchievementsEditView.tpl'}
    {else}
        {include file='modules/DailyReport/AchievementsDetailView.tpl'}
    {/if}
{/block}

{* Work progress reports *}
{block name="progress-reports-on-work"}
    {if $VIEW eq NULL}
        {include file='modules/DailyReport/ProgressReportsWorkEditView.tpl'}
    {else}
        {include file='modules/DailyReport/ProgressReportsWorkDetailView.tpl'}
    {/if}
{/block}

{* Reportes de avances en acciones *}
{block name="progress-report-on-actions"}
    {if $VIEW eq NULL}
        {include file='modules/DailyReport/ProgressReportActionEditView.tpl'}
    {else}
        {include file='modules/DailyReport/ProgressReportActionDetailView.tpl'}
    {/if}
{/block}

{* Otra información *}
{block name="other-information"}
    {if $VIEW eq NULL}
        {include file='modules/DailyReport/OtherInformationEditView.tpl'}
    {else}
        {include file='modules/DailyReport/OtherInformationDetailView.tpl'}
    {/if}
{/block}

{block name="script"}
    <script src="https://cdn.tiny.cloud/1/890v9nqmb6w8aw4ibgargqluwbbu1lj05sfyklk6rqef1idd/tinymce/6/tinymce.min.js"
            referrerpolicy="origin"></script>
    <script type="text/javascript" src="themes/centaurus/js/jquery.nicescroll.js"></script>
    <script type="text/javascript">
        jQuery(".daily-report-scroll").niceScroll();
    </script>
{/block}

{block name="script_template"}{/block}