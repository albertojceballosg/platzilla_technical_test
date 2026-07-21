{extends file='modules/report_rails/Base/SummaryReportFormLayout.tpl'}
{if $PERFORMANCE neq NULL}
        {assign var='color' value=$PERFORMANCE->getIndexColor ()}
        {assign var='content' value=$PERFORMANCE->getDescription ()}
        {assign var='iconPath' value=$PERFORMANCE->getIconPath ()}
        {assign var='indexColor' value=$PERFORMANCE->getIndexColor ()}
        {assign var='performance' value=$PERFORMANCE->getPerformanceName ()}
        {assign var='record' value=$PERFORMANCE->getPerformanceId()}
        {*assign var='report' value=$PERFORMANCE*}
        {assign var='status' value=$PERFORMANCE->getPerformanceStatus ()}
    {else}
        {assign var='color' value=null}
        {assign var='content' value=null}
        {assign var='iconPath' value='&#65;'}
        {assign var='indexColor' value='#FFFFFF'}
        {assign var='performance' value=null}
        {assign var='record' value=null}
        {assign var='report' value=null}
        {assign var='status' value=null}
    {/if}
{block name="css"}
    <link rel="stylesheet" href="include/colorpicker/css/colorpicker.css" type="text/css"/>
    <link rel="stylesheet" href="themes/centaurus/css/libs/datepicker.css" type="text/css"/>
    <link rel="stylesheet" href="themes/centaurus/css/libs/bootstrap-timepicker.css" type="text/css"/>
    <link rel="stylesheet" type="text/css" href="modules/report_rails/report_rails-utils.css"/>
{/block}
{block name="form_name"}performace-{$idSummaryReport}{/block}
{block name="actionFile"}SavePerformace{/block}
{block name="isAjax"}<input type="hidden" name="Ajax" value="true"/>{/block}
{block name="tabName"}Rendimiento{/block}
{block name="selectedTab"}&tab=PERFORMANCE{/block}
{block name="saveJsAction"}
    onclick="ReportRailesUtils.savePerformance(this, '{$idSummaryReport}')"
{/block}
{block name="mainBoxHeader"}
    <header class="main-box-header clearfix">
        <h2 class="pull-left">Información general del rendimiento</h2>
    </header>
{/block}
{block name="mainBoxBody"}
    {* Linea uno *}
    {* Linea dos *}
    <div class="row">
        {* Indice de Rendimiento *}
        <div class="col-md-6">
            <div class="label-input" style="text-align: left;">
                <label for="performance-from">Indice de Rendimiento&nbsp;<span class="required">*</span></label>
            </div>
            <div id="p-div-performance_index" class="form-group field-container">
                <input type="text" id="performance_index_{$idSummaryReport}" name="performance_index" value="{$performance}"
                       title="indice de rendimiento"
                       maxlength="255" class="form-control"/>
                <span id="p-performance_index" class="help-block"></span>
            </div>

        </div>
        {* Estado *}
        <div class="col-md-6">
            <div class="label-input" style="text-align: left;">
                <label for="news-from">Estado&nbsp;<span class="required">*</span></label>
            </div>
            <div id="p-div-performance_status" class="form-group field-container">
                <select class="form-control" name="performance_status" title="Estado del rendimiento"
                        id="performance_status_{$idSummaryReport}">
                    {foreach $PERFORMANCES_STATUS as $statusValue => $statusName}
                        <option value="{$statusValue}"
                                {if $statusValue eq $status}selected{/if} >{$statusName}</option>
                    {/foreach}
                </select>
                <span id="p-performance_status" class="help-block"></span>
            </div>
        </div>
    </div>
    {* Linea tres *}
    <div class="row">
        {* Color de Rendimiento *}
        <div class="col-md-4">
            <div class="label-input" style="text-align: left;">
                <label for="performance-from">Color&nbsp;<span class="required">*</span></label>
            </div>
            <div id="p-div-performance_color" class="form-group field-container">

                <input type="text" id="performance_color" name="performance_color" value="{$indexColor}" class="color"
                       title="El color del indice de rendimiento"
                       readonly="readonly" size="6" style="background-color: {if $color eq NULL}#FF7A59; color: #FF7A59;{else}{$color};color: {$color}; {/if}">
                <span id="p-performance_color" class="help-block"></span>
            </div>

        </div>
        {* Icom del indice *}
        <div class="col-md-4">
            <div class="label-input" style="text-align: left;">
                <label for="performance-from">Index de gestión&nbsp;<span class="required">*</span></label>
            </div>
            <div id="p-div-performance_iconpath" class="form-group field-container">
                <input type="text" id="performance_iconpath" name="performance_iconpath" value="{$iconPath}"
                       title="indice de rendimiento"
                       onkeyup="ReportRailesUtils.selectPerformance(this)"
                       maxlength="5" class="form-control"/>
                <span id="p-performance_iconpath" class="help-block"></span>
            </div>
        </div>
        <div class="col-md-4" style="padding-top: 30px">
            {*  <div class="label-input" style="text-align: left;">
                  <label for="performance-from">Icom&nbsp;<span class="required">*</span></label>
              </div> *}
            <div id="p-div-performance_iconpath" class="form-group field-container">
                <div id="iconPath" class="center" {if $color neq NULL}style="background-color:{$color}"{/if}>
                    <p>{$iconPath}</p>
                </div>
            </div>
        </div>
    </div>
    {* Linea cuatro *}
    <div class="row">
        {* Comentarios del estado de rendimiento *}
        <div class="col-xs-12">
            <div class="label-input" style="text-align: left;">
                <label for="performance-content">Contenido del rendimiento <span class="required">*</span></label>
            </div>
            <div class="form-group field-container">
                <textarea id="performace-content" name="performace_content" class="form-control">{$content}</textarea>
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
        ReportRailesUtils.initPerformance();
    </script>
{/block}