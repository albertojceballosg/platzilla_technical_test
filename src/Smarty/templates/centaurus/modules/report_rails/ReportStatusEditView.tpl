{extends file='modules/report_rails/Base/SummaryReportFormLayout.tpl'}
{if $MASTER_REPORT neq NULL}
    {assign var='content' value=$MASTER_REPORT->getReportOfStatus ()}
    {assign var='MASTER_REPORT_ID' value=$MASTER_REPORT->getId ()}
    {assign var='record' value=$MASTER_REPORT->getId ()}
{else}
    {assign var='content' value=null}
    {assign var='MASTER_REPORT_ID' value=null}
    {assign var='record' value=null}
{/if}
{block name="css"}
    <link rel="stylesheet" href="include/colorpicker/css/colorpicker.css" type="text/css"/>
    <link rel="stylesheet" href="themes/centaurus/css/libs/datepicker.css" type="text/css"/>
    <link rel="stylesheet" href="themes/centaurus/css/libs/bootstrap-timepicker.css" type="text/css"/>
    <link rel="stylesheet" type="text/css" href="modules/report_rails/report_rails-utils.css"/>
{/block}
{block name="form_name"}master_status-{$idSummaryReport}{/block}
{block name="actionFile"}SaveMasterStatus{/block}
{block name="isAjax"}<input type="hidden" name="Ajax" value="true"/>{/block}
{block name="tabName"}Resumen{/block}
{block name="selectedTab"}&tab=SUMMARY_REPORT{/block}
{block name="saveJsAction"}
    onclick="ReportRailesUtils.saveMasterReportStatus(this, '{$idSummaryReport}')"
{/block}
{block name="mainBoxHeader"}{/block}
{block name="mainBoxBody"}
    <div class="row">
        {* Comentarios del estado de rendimiento *}
        <div class="col-xs-12">
            <div class="label-input" style="text-align: left;">
                <label for="report_content">Estado actual: <span class="required">*</span></label>
            </div>
            <div class="form-group field-container">
                <textarea id="report_content" name="report_content" class="form-control">{$content}</textarea>
            </div>
        </div>
    </div>
{/block}
{block name="js"}
    <script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.js"></script>
    <script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.es.js"></script>
    <script type="text/javascript" src="themes/centaurus/js/bootstrap-timepicker.min.js"></script>
    <script type="text/javascript" src="include/ckeditor/ckeditor.js"></script>
    <script type="text/javascript" src="include/colorpicker/js/colorpicker.js"></script>
    <script type="text/javascript" src="modules/report_rails/report_rails-utils.js"></script>
    <script type="text/javascript">
        ReportRailesUtils.initMasterReportStatus();
    </script>
{/block}