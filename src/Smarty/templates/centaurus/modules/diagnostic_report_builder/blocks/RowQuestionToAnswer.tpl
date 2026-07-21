{math equation= rand() assign= "idRow"}
<div class="row" id="row-question-answer-{$idRow}" style="margin-top: 5px">
    <div id="div-drb-question-{$idRow}" class="col-lg-4 col-md-4 col-sm-4">
        <select class="form-control question" id="question-{$idRow}" name="block[{$idRowBuilder}][question][]"
                onchange="DiagnosticRerportBuilderUtls.getAnswerOption(this, '{$idRow}')"
                title="Preguntas">
            {if isset($QUESTIONS)}
                <option value="">Seleccione un pregunta</option>
                {foreach $QUESTIONS as $question}
                    <option value="{$question->getId()}"
                            {if $reportToAnswerRow->getQuestionId() eq $question->getId()}
                                selected
                            {/if}
                    >{$question->getQuestion()}</option>
                {/foreach}
            {else}
                <option value="">Upoo! no hay preguntas</option>
            {/if}
        </select>
        <span id="question-field-{$idRow}" class="help-block" style="color: red;"></span>
    </div>
    <div id="div-drb-answer-{$idRow}" class="col-lg-4 col-md-4 col-sm-4">
        <select class="form-control answer" id="answer-{$idRow}" name="block[{$idRowBuilder}][answer][]"
                title="Opción de respuesta">
            {if isset($QUESTIONS)}
                <option value="">Seleccione una respuesta</option>
                {foreach $QUESTIONS as $question}
                    <optgroup label="{$question->getQuestion()}">
                        {foreach $question->getResponseOption() as $response}
                            {if $question->getQuestionForm() eq 'OPEN_QUESTION'}
                                {assign var="label" value='Pregunta abierta'}
                            {else}
                                {if $response->getSecondLabel eq NULL}
                                    {assign var="label" value=$response->getMainLabel()}
                                {else}
                                    {assign var="label" value=$response->getMainLabel()|cat:' - '|cat:$response->getSecondLabel()}
                                {/if}
                            {/if}
                            <option value="{$response->getName()}" data-question="{$question->getId()}"
                                    {if $reportToAnswerRow->getAnswerName()  eq $response->getName()}
                                        selected
                                    {elseif $question->getId() neq  $reportToAnswerRow->getQuestionId()}
                                        disabled
                                    {/if}
                                    >{$label}</option>
                        {/foreach}
                    </optgroup>
                {/foreach}
            {else}
                <option value="">Upoo! no hay preguntas</option>
            {/if}
        </select>
        <span id="answer-field-{$idRow}" class="help-block help-drb" style="color: red"></span>
    </div>
    <div class="col-lg-4 col-md-4 col-sm-4">
        <div class=" col-lg-10 col-md-10 col-sm-10">
            <select class="form-control element" id="element-{$idRow}" name="block[{$idRowBuilder}][join][]">
                {if isset($JOIN_CONDITIONS)}
                    <option value="">Seleccionar..</option>
                    {foreach $JOIN_CONDITIONS as $key => $type}
                        <option value="{$key}"
                                {if $reportToAnswerRow->getJoinType() eq $key}
                                    selected
                                {/if}
                        >{$type}</option>
                    {/foreach}
                {else}
                    <option value="">Upoo! no hay elementos</option>
                {/if}
            </select>
        </div>
        <div class="pull-right">
            <button type="button"
                    onclick="DiagnosticRerportBuilderUtls.delQuestion(this, '{$idRow}', '{$idRowBuilder}')"
                    title="Eliminar esta Pregunta - Respuesta"
                    class="btn btn-danger delete-question-row">
                <i class="fa fa-trash-o"></i>
            </button>
        </div>
    </div>
</div>