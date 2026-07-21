{math equation= rand() assign= "idQuestion"}
<link rel="stylesheet" type="text/css" href="modules/questionnaire/question.css"/>
<div id="question-{$idQuestion}" data-sequence="{$TOTAL_QUESTION}">
    {if isset($ASKING_FOR) && !empty($ASKING_FOR)}
        <div class="col-md-6" style="margin-bottom: 10px">
            <div class="col-md-4">
                <div class="label-input">
                    <label for=asking-for" >
                        <span id="for=asking-for"></span>&nbsp;Filtrar preguntas</label>
                </div>
            </div>
            <div class="form-group col-md-8 field-container" style="margin-bottom: 0!important;">
                <select id="question-filter"  class="form-control for-filter border"
                        tabindex="" onchange="QuestionUtils.questionFilter (this);">
                    <option value="" disabled="disabled">Seleccionar pregunta</option>
                    <option value=""  selected>Todas las preguntas</option>
                    {foreach $ASKING_FOR as $askingFor}
                        <option value="ROW-{$askingFor->idQuestionRow}">{$askingFor->getSequence()}&nbsp;-&nbsp;{$askingFor->getQuestion()|substr:0:80|cat:'...'}</option>
                    {/foreach}

                </select>
            </div>
        </div>
        <div class="col-md-6" style="margin-bottom: 10px">
            <div class="col-md-4">
                <div class="label-input">
                    <label for="entiy-row" >
                        <span id="entiy-row"></span>&nbsp;</label>
                </div>
            </div>
            <div class="form-group col-md-8" style="margin-bottom: 0!important;">
                {if $VIEW neq NULL}
                <button type="button" class="btn  btn-primary" data-record="{$QUESTIONNAIRE_ID}" onclick="QuestionUtils.changeNavi(this, event)"
                        title="Cambiar sequencia de las preguntas"><i class="fa fa-puzzle-piece" aria-hidden="true"></i>&nbsp;Secuencia</button>&nbsp;
                    <button type="button" class="btn  btn-primary" data-record="{$QUESTIONNAIRE_ID}" onclick="QuestionUtils.changeRanges(this, event)"
                            title="Rango de evaluación"><i class="fa fa-exchange" aria-hidden="true"></i>&nbsp;Rangos</button>
                {/if}
            </div>
        </div>
        {foreach $ASKING_FOR as $askingFor}
            {*math equation= rand() assign= "idRowQuestion"*}
            {assign var="idRowQuestion" value= $askingFor->idQuestionRow}
            {if $VIEW eq NULL}
                {include file='modules/Questionnaires/Questions/QuestionEdittemplate.tpl'}
            {else}
                {include file='modules/Questionnaires/QuestionDetailView.tpl'}
            {/if}
        {/foreach}
    {/if}
</div>
<div class="col-md-12 text-center field-container" style="margin-top: 4px">
    {if $VIEW eq NULL}
        <button type="button" class="btn btn-info" onclick="QuestionUtils.addQuetionGroup (this, '{$idQuestion}');"
                title="Agregar pregunta"><i class="fa fa-plus"></i>
        </button>
    {/if}
</div>
<script type="text/javascript" src="/modules/questionnaire/question-utils.js"></script>
<script type="text/html" id="question-template-{$idQuestion}">
    {include file='modules/Questionnaires/Questions/Question-template.tpl'}
</script>