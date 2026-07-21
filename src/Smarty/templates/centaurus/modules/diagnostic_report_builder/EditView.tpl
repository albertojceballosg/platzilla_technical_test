{extends file='modules/diagnostic_report_builder/blocks/layout.tpl'}
{block name="css"}
    <link type="text/css" rel="stylesheet" href="modules/diagnostic_report_builder/diagnostic-report-builder.css"/>
    <script type="text/javascript" src="include/ckeditor/ckeditor.js"></script>
{/block}
{block name="js"}
    <script type="text/javascript" src="modules/diagnostic_report_builder/diagnostic-report-builder-utils.js"></script>
    {if $isEdit && ($DINAMIC_TEXT_IDS neq NULL)}
    <script type="text/javascript">
        {literal}
        window.onload = function(){
            DiagnosticRerportBuilderUtls.initDinamicText ('{/literal}{json_encode($DINAMIC_TEXT_IDS)|escape: 'htmlall'}{literal}');
        };
        {/literal}
    </script>
    {/if}
{/block}
{block name="panel_url"}index.php?module=diagnostic_report_builder&action=index&parenttab=Settings{/block}
{block name="view_title"}{$MOD.LBL_DIAGNOSTIC_REPORT_MANAGER|upper}{/block}
{block name="form_parameter"}method="post" action="index.php" enctype="multipart/form-data" id="REPORT-BUILDER-{$idReportBuilder}" name="report-builder"{/block}
{block name="from_hidden"}
    <input type="hidden" name="module" value="diagnostic_report_builder"/>
    <input type="hidden" name="action" value="Save"/>
    <input type="hidden" name="record" id="record" value="{$ID}"/>
    {*<input type="hidden" name="Ajax" value="true">*}
{/block}
{block name="main-box-body"}
    {*$reportsToAnswer|var_dump*}
    <div class="row">
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-4 text-right">
                    <label for="fromfieldname">Diagnostico</label>
                </div>
                <div id="div-drb-name" class="form-group col-md-6 field-container">
                    <input type="text" tabindex=""
                           name="diagnosticname"
                           title="Nombre del diagnostico"
                           id="diagnostic-name-{$idReportBuilder}"
                           placeholder="Nombre del diagnostico"
                           value="{$drbName}" class="form-control">
                    <span id="help-field-name-{$idReportBuilder}"  class="help-block" style="color: red"></span>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-4 text-right">
                    <label for="fromfieldname">Cuestionario</label>
                </div>
                <div id="div-drb-questionnaire" class="form-group col-md-6 field-container">
                    <select class="form-control" id="questionnaire-{$idReportBuilder}" name="questionnaire"
                            onchange="DiagnosticRerportBuilderUtls.getQuestionnaireData(this, '{$idReportBuilder}')"
                            title="El Cuestionario">
                        {if isset($QUESTIONNAIRE)}
                            <option value="" {if $viewId neq NULL}disabled=""{/if}>Seleccione ...</option>
                            {foreach $QUESTIONNAIRE as $row}
                                <option value="{$row['questionnaireid']}"
                                        {if $questionnaireId eq $row['questionnaireid']}selected{/if}>{$row['name']}</option>
                            {/foreach}
                        {else}
                            <option value="">Seleccione ...</option>
                        {/if}
                    </select>
                    <span id="help-field-{$idReportBuilder}"  class="help-block" style="color: red"></span>
                </div>
            </div>
        </div>
    </div>
    {*  Filters *}
    <div class="row">
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-4 text-right">
                    <label for="fromfieldname">Bloque del informe</label>
                </div>
                <div id="div-drb-name" class="form-group col-md-6 field-container">
                    <select class="form-control" id="block-filter"
                    onchange="DiagnosticRerportBuilderUtls.filterByBlock(this, '{$idReportBuilder}')">
                        {if isset($REPORT_BLOCKS)}
                            <option value="" >Filtrar por ...</option>
                            {foreach $REPORT_BLOCKS as $key => $block}
                                <option value="{$key}">{$block}</option>
                            {/foreach}
                        {else}
                            <option value="">Filtrar por ...</option>
                        {/if}
                    </select>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-4 text-right">
                    <label for="fromfieldname">Elemento del Informe</label>
                </div>
                <div id="div-drb-questionnaire" class="form-group col-md-6 field-container">
                    <select class="form-control" id="element-filter"
                            onchange="DiagnosticRerportBuilderUtls.filterByElement(this, '{$idReportBuilder}')">
                        {if isset($ELEMENT_TYPE)}
                            <option value="" >Filtrar por ...</option>
                            {foreach $ELEMENT_TYPE as $key => $element}
                                <option value="{$key}">{$element}</option>
                            {/foreach}
                        {else}
                            <option value="">Filtrar por ....</option>
                        {/if}
                    </select>
                    <span id="help-field-{$idReportBuilder}"  class="help-block" style="color: red"></span>
                </div>
            </div>
        </div>
    </div>
    {*  Filters *}
    <div id="questionnaire-data-{$idReportBuilder}">
        {if $isEdit && ($reportsToAnswer neq NULL)}
            {foreach $reportsToAnswer as $objIndex => $reportToAnswer}
                {if $reportToAnswer->getElementType() eq NULL}
                    {continue}
                {/if}
                {include file="modules/diagnostic_report_builder/blocks/QuestionMainBlock.tpl"}
            {/foreach}
        {/if}
    </div>
    <div class="row-drb justify-content-center">
        <div id="question-block-{$idReportBuilder}"  class="col-lg-8 col-md-8  col-sm-8 {if !$isEdit} hide{/if}" style="text-align: center; margin-top: 20px">
            <span class="help-block"></span>
            <button type="button" class="btn btn-primary"
                    data-sequence="{($key + 1)}"
                    onclick="DiagnosticRerportBuilderUtls.addBlockToReportBuilder (this, '{$idReportBuilder}');">
                <i class="fa fa-plus"></i></button>
        </div>
    </div>
{/block}