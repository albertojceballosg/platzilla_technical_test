{extends file='Home/TabsContents/Base/ProcessPanelLayOut.tpl'}
{strip}
    {block name="css"}
        <link type="text/css" rel="stylesheet" href="themes/centaurus/css/libs/datepicker.css"/>
        <link rel="stylesheet" type="text/css" href="modules/Home/daily_matrix.css"/>
    {/block}
    {block name="panel_content"}
        {include file='Home/TabsContents/Objects/PanelContent.tpl'}
    {/block}
    {block name="quality_content"}
        {include file='Home/TabsContents/Objects/QualityContent.tpl'}
    {/block}
    {block name="js"}
        <script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.js"></script>
        <script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.es.js"></script>
        <script type="text/javascript" src="themes/centaurus/js/charts/loader.js"></script>
        <script type="text/javascript" src="modules/Home/process-cases-utils.js"></script>
    {/block}
{/strip}