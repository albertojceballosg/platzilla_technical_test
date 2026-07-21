{*$ASKING_FOR|var_dump*}
{strip}
{if $ASKING_FOR neq NULL}
    {assign var='askingForId' value=$ASKING_FOR->getId ()}
    {assign var='askingForQuestionnaireId' value=$ASKING_FOR->getQuestionnaireId ()}
    {assign var='askingForQuestion' value=$ASKING_FOR->getQuestion ()}
    {assign var='askingForSecuence' value=$ASKING_FOR->getSequence ()}
    {assign var='askingForTotal' value=$ASKING_FOR->getSurveyTotal ()}
    {assign var='askingForalculation' value=$ASKING_FOR->getCalculationResult ()}
    {assign var='askingForResponseOption' value=$ASKING_FOR->getResponseOption ()}
{/if}
    <style>
        .answer-export {
            min-width: 95px!important;
        }

        .answer-export > li > a {
            padding-left: 5px!important;
            padding-right: 5px!important;

        }
    </style>
    <div class="row">
        <div class="col-lg-12 col-md-12 col-xs-12">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-xs-12">
                    <div class="col-lg-6 col-md-6 col-xs-6">
                        <div class="platzilla-card-header" style="padding-left: 10px">
                            <p class="text-center pull-left" style="font-weight: bold">Evaluación de respuestas</p>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-xs-6">
                        <div class="btn-group pull-right" style="z-index: 100000">
                            <div class="btn-group">
                                <button type="button" class="btn btn-primary btn-xs dropdown-toggle"
                                        data-toggle="dropdown">
                                    <i class="fa fa-file-excel-o" aria-hidden="true"></i>&nbsp;Exportar&nbsp;
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu pull-left answer-export" role="menu">
                                    <li><a data-questionnaire="{$askingForQuestionnaireId}"
                                           data-asking-for="{$askingForId}"
                                           href="index.php?module=answers&action=AjaxAnswersUtils&questionnaire={$askingForQuestionnaireId}&askingfor={$askingForId}&function=EXPORT_RECORD&Ajax=true">
                                            Registro actual</a>
                                    </li>
                                    <li><a  data-questionnaire="{$askingForQuestionnaireId}"
                                            data-asking-for="{$askingForId}"
                                            href="index.php?module=answers&action=AjaxAnswersUtils&questionnaire={$askingForQuestionnaireId}&function=EXPORT_QUESTION&Ajax=true">
                                            Todo el cuestionario</a>
                                    </li>
                                </ul>
                            </div>
                            <button type="button"
                                    onclick="ResponseEvaluationUtils.exportPdf({$RECORD_ID}, {$askingForQuestionnaireId}, 'template_answeres')"
                                    class="btn btn-success btn-xs"><i class="fa fa-file-pdf-o" aria-hidden="true"></i></button>
                        </div>
                    </div>
                </div>
                <div class="pull-left col-lg-12 col-md-1 col-xs-12">
                    <p style="margin-left: 15px;width: 100%">{$askingForSecuence}.&nbsp;{$askingForQuestion}</p>
                </div>
                {*<div class="col-lg-6 col-md-6 col-xs-6"></div> *}
            </div>
        </div>
        <div class="col-lg-12 col-md-12 col-xs-12">
            <div class="col-lg-4 col-md-4 col-xs-4">
                <div class="col-lg-12 col-md-12 col-xs-12">
                    <p style="font-weight: bold">Número de participantes:&nbsp;{$askingForTotal}</p>
                    <p style="font-style: italic">{$QUESTION_TYPE}</p>
                </div>
                <div class="col-lg-12 col-md-12 col-xs-12">
                {if $askingForResponseOption neq NULL}
                <ul class="list-unstyled">
                    {foreach $askingForResponseOption as $responseOption}
                    <li>{$responseOption->getSurveyTotal()}&nbsp;({$responseOption->getSuveyPorcent()} %):&nbsp;{if $responseOption->getMainLabel() neq NULL}{$responseOption->getMainLabel()}{else}{$responseOption->getValue()}{/if}  </li>
                    {/foreach}
                </ul>
                </div>
                {/if}
                {if $askingForalculation neq NULL}
                <div class="col-lg-12 col-md-12 col-xs-12">
                    <p class="vorlage" style="float: left">{$CALCULATION}:&nbsp;<strong>{$askingForalculation}<strong></p>
                </div>
                {/if}
            </div>
            <div class="col-lg-8 col-md-8 col-xs-8 text-left">
                <div id="piechart_3d" style="margin-top: -5px"></div>
            </div>
        </div>
    </div>
    <script type="text/javascript" src="themes/centaurus/js/charts/loader.js"></script>
    <script type="text/javascript" src="modules/answers/response-evaluation-utils.js"></script>
    <script type="text/javascript" src="include/js/html2canvas.min.js"></script>
    <script type="text/javascript">
        ResponseEvaluationUtils.init('{$GRAPHIC_DATA}', '{$askingForQuestion}');
    </script>
{/strip}