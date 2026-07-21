{strip}
    {math equation= rand() assign= "idQuestion"}
    <link rel="stylesheet" type="text/css" href="modules/questionnaire/question.css"/>
    <link rel="stylesheet" type="text/css" href="modules/grid_view/grid-view.css">
    {if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
        <div class="row">
            <div class="alert alert-{if (isset ($IS_ERROR)) && ($IS_ERROR)}danger{else}success{/if}">
                <strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
            </div>
        </div>
    {/if}
    {if isset($ASKING_FOR) && !empty($ASKING_FOR)}
        <div class="row-grid-view" style="max-height: 500px; overflow-y: auto; overflow-x: hidden">
            <div class="col-lg-12 col-md-12 col-xs-12 col-sm-12">
                <div class="row" id="question-{$idQuestion}">

                    <div class="col-md-6" style="margin-bottom: 10px">
                        <div class="col-md-4">
                            <div class="label-input">
                                <label for=asking-for">
                                    <span id="for=asking-for"></span>&nbsp;Filtrar preguntas</label>
                            </div>
                        </div>
                        <div class="form-group col-md-8 field-container" style="margin-bottom: 0!important;">
                            <select id="question-filter" class="border form-control for-filter"
                                    tabindex="" onchange="QuestionUtils.navFilter (this, 'ROW-NAV');">
                                <option value="" disabled="disabled">Seleccionar pregunta</option>
                                <option value="" selected>Todas las preguntas</option>
                                {foreach $ASKING_FOR as $key => $askingFor}
                                    {if $key >= $TOTAL_QUESTION}{continue}{/if}
                                    <option value="ROW-NAV-{$askingFor->getId()}">{$askingFor->getSequence()}
                                        &nbsp;-&nbsp;{$askingFor->getQuestion()|substr:0:80|cat:'...'}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6" style="margin-bottom: 10px">
                        <div class="col-md-4">
                            <div class="label-input">
                                <label for="entiy-row">
                                    <span id="entiy-row"></span>&nbsp;</label>
                            </div>
                        </div>
                        <div class="form-group col-md-8" style="margin-bottom: 0!important;">&nbsp;</div>
                    </div>

                </div>
                <form id="form-{$idQuestion}" name="form-navi-{$idQuestion}" method="post" enctype="multipart/form-data" action="index.php">
                    <input type="hidden" name="module" value="questionnaire"/>
                    <input type="hidden" name="action" value="AjaxQuestionUtils"/>
                    <input type="hidden" name="Ajax" value="true"/>
                    <input type="hidden" name="function" value="SAVE_SURVEY_NAV"/>
                    <input type="hidden" name="record" value="{$QUESTIONNAIRE_ID}"/>
                    {foreach $ASKING_FOR as $key => $askingFor}
                        {if $key >= $TOTAL_QUESTION}{continue}{/if}
                        {assign var="setQuestion" value="yes"}
                        <div id="ROW-NAV-{$askingFor->getId()}" class="row">
                            <div class="col-md-12">
                                <p class="text-left border" style="padding: 2px 4px">
                                    <span style="font-weight: bold;">Pregunta:&nbsp;</span>{$askingFor->getQuestion()}<span
                                            style="margin-left: 25px; font-style: italic">{$ANSWERS_OPTIONS[$askingFor->getQuestionForm()][$askingFor->getQuestionType()]}</span>
                                </p>
                            </div>
                            <div class="col-md-12">
                                <div class="row-grid-view">
                                    <div class="col-md-11 justify-content-center">
                                        <div class="col-md-6"><p class="text-left"
                                                                 style="font-weight: bold; padding: 2px 4px">Opciones de respuestas:</p></div>
                                        <div class="col-md-6"><p class="text-left"
                                                                 style="font-weight: bold; padding: 2px 4px">Pegunta siguiente:</p></div>
                                    </div>
                                </div>
                                <div class="row-grid-view">
                                    <div class="col-md-11 justify-content-center">
                                        {foreach $askingFor->getResponseOption () as $response}
                                            <div class="col-md-6">
                                                {if $askingFor->getQuestionForm() eq 'OPEN_QUESTION'}
                                                    {$response->getValue()|substr:0:160|cat:'...'}
                                                {else}
                                                    {$response->getMainLabel()|substr:0:160|cat:'...'}
                                                {/if}
                                                {if $response->getSurveyNav () neq NULL}
                                                    {assign var="slectedAsk" value= $response->getSurveyNav ()->getQuestionId()}
                                                {else}
                                                    {assign var="slectedAsk" value=null}
                                                {/if}
                                            </div>
                                            <div id="div-sn-{$response->getName()}"   class="col-md-6">
                                                {if $setQuestion eq 'yes'}
                                                <select id="question-filter" class="border form-control for-filter"
                                                        name="nav[{$response->getName()}]"
                                                        title="La Pregunta"
                                                        tabindex="" {*style="margin-bottom: 2px"*}>
                                                    <option value="" >Seleccionar pregunta</option>
                                                    {foreach $ASKING_FOR as $keyOp => $asking}
                                                        {if ($keyOp eq 0) || $keyOp <= $key }{continue}{/if}
                                                        <option value="{$asking->getId()}" {if $slectedAsk eq $asking->getId()}selected{/if}>{$asking->getSequence()}
                                                            &nbsp;-&nbsp;{$asking->getQuestion()|substr:0:80|cat:'...'}</option>
                                                    {/foreach}
                                                </select>
                                                <span id="sn-{$response->getName()}" class="help-block" style="color: red;font-size: small"></span>
                                                {else}
                                                    <div class="border form-control" style="width: 100%;margin-bottom: 2px">&nbsp;</div>
                                                {/if}
                                            </div>
                                            {if in_array($askingFor->getQuestionForm(), $ONLY_OPTIONS)}
                                                {assign var="setQuestion" value="no"}
                                            {/if}
                                        {/foreach}
                                    </div>
                                </div>
                            </div>
                        </div>
                    {/foreach}
                </form>
            </div>
        </div>
        <div class="row-grid-view justify-content-center">
            <div class="col-md-4" style="margin-top: 12px">
                <div class="btn-group center-block" style="text-align: center">
                    <button type="button" onclick="QuestionUtils.saveSurveyNav (this, '{$idQuestion}');" class="btn btn-success">Guardar</button>
                    <button type="button"data-dismiss="modal" aria-hidden="true" class="btn btn-warning">Cancelar</button>
                </div>
            </div>
        </div>
    {/if}
    <script type="text/javascript" src="/modules/questionnaire/question-utils.js"></script>
{/strip}