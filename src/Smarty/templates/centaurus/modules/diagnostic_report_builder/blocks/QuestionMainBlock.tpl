{if $isEdit}
    {assign var="idRowBuilder" value=$reportToAnswer->getIdQuestionBlock()}
{else}
    {math equation= rand() assign= "idRowBuilder"}
{/if}

<div class="row-drb justify-content-center">
    <div class="col-lg-8 col-md-8 col-sm-8 border rounded main-block-{$idReportBuilder}" style="padding: 4px; background-color:#f9f8f7;margin-top: 5px">
        <div class="row" id="block-{$idRowBuilder}">
            <div class="col-lg-12 col-md-12 col-sm-12">
                {* Question - answer block *}
                <div class="row" id="row-question-answer-{$idRowBuilder}">
                    {* Question *}
                    <div id="div-drb-question-{$idRowBuilder}" class="col-lg-4 col-md-4 col-sm-4">
                        <select class="form-control question" id="question-{$idRowBuilder}" name="block[{$idRowBuilder}][question][]"
                                onchange="DiagnosticRerportBuilderUtls.getAnswerOption(this, '{$idRowBuilder}')"
                                title="Preguntas">
                            {if isset($QUESTIONS)}
                                <option value="">Seleccione un pregunta o tema</option>
                                {if $TOPICS neq NULL}
                                    <option value="{if $SELECTED_TOPIC eq NULL}TOPICS{else}{$SELECTED_TOPIC}{/if}"
                                    {if $isEdit && ($reportToAnswer->getQuestionId() eq 0) && (($reportToAnswer->getElementType() eq 'MANAGEMENT_LEVEL') || ($SELECTED_TOPIC neq NULL))}
                                      selected
                                    {/if}>Temas</option>
                                {/if}
                                {foreach $QUESTIONS as $question}
                                    <option value="{$question->getId()}"
                                            {if $isEdit}
                                                {if $reportToAnswer->getQuestionId() eq $question->getId()}
                                                    selected
                                                {/if}
                                            {/if}
                                    >{$question->getQuestion()}</option>
                                {/foreach}

                            {else}
                                <option value="">Upoo! no hay preguntas</option>
                            {/if}
                        </select>
                        <span id="question-field-{$idRowBuilder}"  class="help-block" style="color: red;"></span>
                    </div>
                    {* Answere *}
                    <div id="div-drb-answer-{$idRowBuilder}" class="col-lg-4 col-md-4 col-sm-4">
                        <select class="form-control answer" id="answer-{$idRowBuilder}" name="block[{$idRowBuilder}][answer][]"
                                title="Opción de respuesta">
                            {if isset($QUESTIONS)}
                                <option value="">Seleccione una respuesta</option>
                                {if $TOPICS neq NULL}
                                    <optgroup label="Temas">
                                        {if $isEdit && ($QUESTIONNAIRE_TOPICS neq NULL) && ($reportToAnswer->getElementType() eq 'MANAGEMENT_LEVEL')}
                                            {foreach $QUESTIONNAIRE_TOPICS as $topic}
                                                <option value="{$topic}"
                                                        {if $isEdit}
                                                            {if $reportToAnswer->getAnswerName()  eq $topic}
                                                                selected
                                                            {/if}
                                                        {else}
                                                            disabled
                                                        {/if}
                                                        data-question="{if $SELECTED_TOPIC eq NULL}TOPICS{else}{$SELECTED_TOPIC}{/if}">{$topic}</option>
                                            {/foreach}
                                        {else}
                                            {foreach $TOPICS as $topic}
                                                <option value="{$topic}"
                                                        {if $isEdit}
                                                            {if $reportToAnswer->getAnswerName()  eq $topic}
                                                                selected
                                                            {/if}
                                                        {else}
                                                            disabled
                                                        {/if}
                                                        data-question="{if $SELECTED_TOPIC eq NULL}TOPICS{else}{$SELECTED_TOPIC}{/if}">{$topic}</option>
                                            {/foreach}
                                        {/if}
                                    </optgroup>
                                {/if}
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
                                                    {if $isEdit}
                                                        {if $reportToAnswer->getAnswerName()  eq $response->getName()}
                                                            selected
                                                        {elseif $question->getId() neq  $reportToAnswer->getQuestionId()}
                                                            disabled
                                                        {/if}
                                                    {else}
                                                        disabled
                                                    {/if}
                                            >{$label}</option>
                                        {/foreach}
                                    </optgroup>
                                {/foreach}
                            {else}
                                <option value="">No hay respuestas</option>
                            {/if}

                        </select>
                        <span id="answer-field-{$idRowBuilder}"  class="help-block help-drb" style="color: red"></span>
                    </div>
                    {* Join question - answere*}
                    <div class="col-lg-4 col-md-4 col-sm-4">
                        <div class=" col-lg-10 col-md-10 col-sm-10">
                            <select class="form-control element" id="join-question-{$idRowBuilder}"
                                    name="block[{$idRowBuilder}][join][]">
                                {if isset($JOIN_CONDITIONS)}
                                    <option value="">Sin mas condiciones</option>
                                    {foreach $JOIN_CONDITIONS as $key => $type}
                                        <option value="{$key}"
                                                {if $isEdit}
                                                    {if $reportToAnswer->getJoinType() eq $key}
                                                        selected="selected"
                                                    {/if}
                                                {/if}
                                        >{$type}</option>
                                    {/foreach}
                                {else}
                                    <option value="">Upoo! no hay condiciones</option>
                                {/if}
                            </select>
                        </div>
                        <div class="pull-right">
                            <button type="button"
                                    onclick="DiagnosticRerportBuilderUtls.delQuestion(this, '{$idRowBuilder}', '{$idRowBuilder}')"
                                    title="Eliminar esta Pregunta - Respuesta"
                                    class="btn btn-danger delete-question-row">
                                <i class="fa fa-trash-o"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12 col-md-12 col-sm-12" id="question-row-{$idRowBuilder}">
                {* row - Question - answer - join conditions*}
                {if $isEdit}
                    {foreach $reportsToAnswer as $reportToAnswerRow}
                        {if ($reportToAnswerRow->getElementType() eq NULL) && ($reportToAnswerRow->getId() > $reportToAnswer->getId())}
                            {include file="modules/diagnostic_report_builder/blocks/RowQuestionToAnswer.tpl"}
                        {/if}
                        {if ($reportToAnswerRow->getElementType() neq NULL)  && ($reportToAnswerRow->getId() > $reportToAnswer->getId())}
                            {break}
                        {/if}
                    {/foreach}
                {/if}
            </div>
            <div class="col-lg-12 col-md-12 col-sm-12" id="element-object-{$idRowBuilder}" >
                {* Element to build report *}
                {if $isEdit && ($reportToAnswer->getHtmlblock() neq NULL)}
                    {$reportToAnswer->getHtmlblock()}
                {/if}
            </div>
            <div class="col-lg-12 col-md-12 col-sm-12" style="margin-top: 15px">
                {* Diagnostic Report Part *}
                <div class="row">
                    {* Elemnts *}
                    <div id="div-drb-element-{$idRowBuilder}" class="col-lg-4 col-md-4 col-sm-4">
                        {*$TOPICS|var_dump*}
                        <select class="form-control element"
                                id="element-{$idRowBuilder}"
                                name="block[{$idRowBuilder}][element][]"
                                onchange="DiagnosticRerportBuilderUtls.getElementToReportBuilder(this, '{$idRowBuilder}','{if $isEdit}{$idReportBuilder}{else}{$ID}{/if}')"
                                title="Elemento de Diagnostico">
                            {if isset($ELEMENT_TYPE)}
                                <option value="">Elemento de informe</option>
                                {foreach $ELEMENT_TYPE as $key => $type}
                                    <option value="{$key}"
                                            {if $isEdit}
                                                {if $reportToAnswer->getElementType() eq $key}
                                                    selected
                                                {/if}
                                            {/if}

                                        {if (($key eq 'MANAGEMENT_LEVEL') && ($SELECTED_TOPIC neq NULL) && $isEdit && ($reportToAnswer->getElementType() neq $key)) || (!$isEdit && $SELECTED_TOPIC neq NULL && ($key eq 'MANAGEMENT_LEVEL'))}
                                            disabled
                                        {/if}
                                        >{$type}</option>
                                {/foreach}
                            {else}
                                <option value="">Upoos! no hay elementos</option>
                            {/if}
                        </select>
                        <span id="element-field-{$idRowBuilder}"
                              class="help-block help-drb" style="color: red;margin: 0!important;"></span>
                    </div>
                    {* Report tab*}
                    <div class="col-lg-4 col-md-4 col-sm-4">
                        <select class="form-control element"
                                id="report-tab-{$idRowBuilder}"
                                name="block[{$idRowBuilder}][report-tab][]"
                                title="El bloque de informe">
                            {if isset($REPORT_BLOCKS)}
                                <option value="">Bloque del informe</option>
                                {foreach $REPORT_BLOCKS as $key => $type}
                                    <option value="{$key}"
                                            {if $isEdit}
                                                {if $reportToAnswer->getReportBlock() eq $key}
                                                    selected
                                                {/if}
                                            {/if}
                                    >{$type}</option>
                                {/foreach}
                            {else}
                                <option value="">Upoo! no hay bloques del informe</option>
                            {/if}
                        </select>
                        <span id="element-field-{$idRowBuilder}"
                              class="help-block help-drb" style="color: red;margin: 0!important;"></span>
                    </div>
                    {* Tab section*}
                    <div class="col-lg-4 col-md-4 col-sm-4">
                        <div class=" col-lg-9 col-md-9 col-sm-9">&nbsp;</div>
                        <div class="pull-right">
                            <button type="button"
                                    onclick="DiagnosticRerportBuilderUtls.addQuestion(this, '{$idRowBuilder}', '{$ID}')"
                                    title="Inserta Pregunta - Respuesta"
                                    class="btn btn-primary">
                                <i class="fa fa-plus" aria-hidden="true"></i>
                            </button>&nbsp;
                            <button type="button"
                                    onclick="DiagnosticRerportBuilderUtls.delQuestionBlock(this, '{$idRowBuilder}', '{$ID}')"
                                    title="Eliminar el bloque de Preguntas - Respuestas"
                                    class="btn btn-danger">
                                <i class="fa fa-trash-o"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/html" id="row-template-{$idRowBuilder}">
    <div class="row" id="row-question-answer-__ID__" style="margin-top: 5px">
        <div id="div-drb-question-__ID__" class="col-lg-4 col-md-4 col-sm-4">
            <select class="form-control question" id="question-__ID__" name="block[{$idRowBuilder}][question][]"
                    onchange="DiagnosticRerportBuilderUtls.getAnswerOption(this, '__ID__')"
                    title="Preguntas">
                {if isset($QUESTIONS)}
                    <option value="">Seleccione un pregunta</option>
                    {foreach $QUESTIONS as $question}
                        <option value="{$question->getId()}">{$question->getQuestion()}</option>
                    {/foreach}
                {else}
                    <option value="">Upoo! no hay preguntas</option>
                {/if}
            </select>
            <span id="question-field-__ID__"  class="help-block" style="color: red;"></span>
        </div>
        <div id="div-drb-answer-__ID__"  class="col-lg-4 col-md-4 col-sm-4">
            <select class="form-control answer" id="answer-__ID__" name="block[{$idRowBuilder}][answer][]"
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
                                        disabled>{$label}</option>
                            {/foreach}
                        </optgroup>
                    {/foreach}
                {else}
                    <option value="">Upoo! no hay preguntas</option>
                {/if}
            </select>
            <span id="answer-field-__ID__"  class="help-block help-drb" style="color: red"></span>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-4">
            <div class=" col-lg-10 col-md-10 col-sm-10">
                <select class="form-control element" id="element-__ID__" name="block[{$idRowBuilder}][join][]">
                    {if isset($JOIN_CONDITIONS)}
                        <option value="">Seleccionar..</option>
                        {foreach $JOIN_CONDITIONS as $key => $type}
                            <option value="{$key}">{$type}</option>
                        {/foreach}
                    {else}
                        <option value="">Upoo! no hay elementos</option>
                    {/if}
                </select>
            </div>
            <div class="pull-right">
                <button type="button"
                        onclick="DiagnosticRerportBuilderUtls.delQuestion(this, '__ID__', '{$idRowBuilder}')"
                        title="Eliminar esta Pregunta - Respuesta"
                        class="btn btn-danger delete-question-row">
                    <i class="fa fa-trash-o"></i>
                </button>
            </div>
        </div>
    </div>
</script>