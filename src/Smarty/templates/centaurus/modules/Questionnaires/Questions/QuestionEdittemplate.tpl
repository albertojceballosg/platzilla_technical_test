<div class="row" id="ROW-{$idRowQuestion}" style="margin-bottom: 4px;padding-bottom: 12px">
    <input type="hidden" id="question-id-{$idRowQuestion}" name="question[{$askingFor->getSequence()}][questionid]" value="{$askingFor->getId()}">
    <div class="col-md-6">
        <div class="col-md-4">
            <div class="label-input">
                <label for="status" class="animate__animated ">
                    <span id="questionannairestagesid"></span>&nbsp;Grupo</label>
            </div>
        </div>
        <div class="form-group col-md-8 field-container" id="td_status">
            <input type="text" tabindex="" name="question[{$askingFor->getSequence()}][group]" id="questionannairestagesid" value="{$askingFor->getQuestionGroupId()}" class="form-control">
{*            <select id="questionannairestagesid" name="question[{$askingFor->getSequence()}][group]" class="form-control for-filter"
                    tabindex="" >
                <option value="" disabled="disabled">Fundamento</option>
                {if (!empty ($QUESTION_GROUP))}
                    {foreach $QUESTION_GROUP as $qgroup}
                        <option value="{$qgroup->getId ()}" {if $qgroup->getId () eq $askingFor->getQuestionGroupId()}selected="selected"{/if}>{$qgroup->getName ()}</option>
                    {/foreach}
                {/if}
            </select> *}
        </div>
    </div>
    <div class="col-md-6">
        <div class="col-md-4">
            <div class="label-input">
                <label for="status" class="animate__animated ">
                    <span id="questiongroupid"></span>&nbsp;Tema</label>
            </div>
        </div>
        <div class="form-group col-md-8 field-container" id="td_status">
            <input type="text" tabindex="" name="question[{$askingFor->getSequence()}][stages]" id="questiongroupid" value="{$askingFor->getQuestionStageId()}" class="form-control">
{*            <select id="questiongroupid" name="question[{$askingFor->getSequence()}][stages]" class="form-control for-filter"
                    tabindex="">
                <option value="" disabled="disabled">Etapa</option>
                {if (!empty ($STAGES))}
                    {foreach $STAGES as $stage}
                        <option value="{$stage->getId ()}" {if $stage->getId () eq $askingFor->getQuestionStageId()}selected="selected"{/if}>{$stage->getName ()}</option>
                    {/foreach}
                {/if}
            </select> *}
        </div>
    </div>
    <div class="col-md-6">
        <div class="col-md-4">
            <div class="label-input">
                <label for="question_title" class="animate__animated ">Pregunta</label>
            </div>
        </div>
        <div class="form-group col-md-8 field-container" id="td_question_title">
            <textarea name="question[{$askingFor->getSequence()}][title]" id="question_title" class="form-control" tabindex=""
                      rows="2">{$askingFor->getQuestion()}</textarea>
        </div>
    </div>
    <div class="col-md-6">
        <div class="col-md-4">
            <div class="label-input">
                <label for="question_descrption" class="animate__animated">Descripción</label>
            </div>
        </div>
        <div class="form-group col-md-8 field-container" id="td_question_descrption">
        <textarea id="question_descrption" name="question[{$askingFor->getSequence()}][description]" class="form-control" tabindex=""
                  rows="2">{$askingFor->getDescription()}</textarea>
        </div>
    </div>
    <div class="col-md-6">
        <div class="col-md-4">
            <div class="label-input">
                <label for="question_help" class="animate__animated">Ayuda</label>
            </div>
        </div>
        <div class="form-group col-md-8 field-container" id="td_question_help">
            <textarea id="question_help" name="question[{$askingFor->getSequence()}][help]" class="form-control" tabindex=""
                      rows="2">{$askingFor->getHelp()}</textarea>
        </div>
    </div>
    <div class="col-md-6">&nbsp;
        {* not necessary in the question
        <div class="col-md-4">
            <div class="label-input">
                <label for="question_feedback" class="animate__animated">Feed back</label>
            </div>
        </div>
        <div class="form-group col-md-8 field-container" id="td_question_feedback">
            <textarea id="question_feedback" name="question[{$askingFor->getSequence()}][feedback]" class="form-control" tabindex=""
                      rows="2">{$askingFor->getFeedBack()}</textarea>
        </div>
        *}
    </div>
    <div class="col-md-12">
        <div class="col-md-6">
            <div class="col-md-4">
                <div class="label-input">
                    <label for="status" class="animate__animated ">
                        <span id="calculationtype"></span>&nbsp;Tipo de cálculo</label>
                </div>
            </div>
            <div class="form-group col-md-8 field-container" id="td_calculationtype">
                <select id="calculationtype-{$idRowQuestion}" name="question[{$askingFor->getSequence()}][calculation_type]"
                        class="form-control for-filter"  tabindex="">
                    <option value="">Tipo de cálculo</option>
                    {if (!empty ($CALCULATION_TYPE))}
                        {foreach $CALCULATION_TYPE as $key => $ctype}
                            <option value="{$key}" {if $key eq $askingFor->getCalculationType()}selected="selected"{/if}>{$ctype}</option>
                        {/foreach}
                    {/if}
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="col-md-4">
                <div class="label-input">
                    <label for="video_url" class="animate__animated ">
                        Url video</label>
                </div>
            </div>
            <div class="form-group col-md-8 field-container" id="td_video_url">
                <div class="input-group" style="width: 100%;">
                    <span class="input-group-addon" style="cursor: default; background-color: #eee;"><i class="fa fa-wordpress"></i></span>
                    <input type="text" id="video_url-{$idRowQuestion}" name="question[{$askingFor->getSequence()}][video_url]" value="{$askingFor->getUrlVideo()}" class="form-control" tabindex="" onkeyup="validateUrl('video_url');">
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="col-md-4">
            <div class="label-input">
                <label for="status" class="animate__animated ">
                    <span id="questionannairestagesid"></span>&nbsp;Tipo de pregunta</label>
            </div>
        </div>
        <div class="form-group col-md-8 field-container" id="td_status">
            <select id="questionform-{$idRowQuestion}" name="question[{$askingFor->getSequence()}][form]" class="form-control for-filter"
                    tabindex="" onchange="QuestionUtils.quetionForm (this, '{$idRowQuestion}');">
                <option value="">Tipo de pregunta</option>
                {if (!empty ($QUESTION_FORM))}
                    {foreach $QUESTION_FORM as $key => $gtype}
                        <option value="{$key}" {if $key eq $askingFor->getQuestionForm()}selected="selected"{/if}>{$gtype}</option>
                    {/foreach}
                {/if}
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="col-md-4">
            <div class="label-input">
                <label for="status" class="animate__animated ">
                    <span id="questiongroupid"></span>&nbsp;Tipo de respuesta</label>
            </div>
        </div>
        <div class="form-group col-md-8 field-container" id="td_status">
            <select id="answereoption-{$idRowQuestion}" name="question[{$askingFor->getSequence()}][answere]" class="form-control for-filter"
                    tabindex=""
                    onchange="QuestionUtils.quetionType (this, '{$idRowQuestion}', '{$askingFor->getSequence()}');">
                <option value="" disabled="disabled" selected="selected">Opciones de respuesta</option>
                {if (!empty ($QUESTION_FORM))}
                    {foreach $QUESTION_FORM as $key => $gtype}
                        {if (!empty ($ANSWERS_OPTIONS))}
                            {foreach $ANSWERS_OPTIONS[$key] as $answarekey => $answare}
                                <option value="{$answarekey}" {if $answarekey eq $askingFor->getQuestionType()}selected="selected"{/if} class="{if $askingFor->getQuestionForm() neq $key}hide{/if}" data-type="{$key}">{$answare}</option>
                            {/foreach}
                        {/if}
                    {/foreach}
                {/if}
            </select>
        </div>
    </div>
    <div class="col-md-12" id="question-type-{$idRowQuestion}">
        {$askingFor->getHtmlResponse()}
    </div>
    <div class="col-md-6">
        <div class="col-md-4">
            <div class="label-input">
                <label for="question_points" class="animate__animated">
                    Puntos</label>
            </div>
        </div>
        <div class="form-group col-md-8 field-container" id="td_question_points">
            <input type="text" tabindex="" name="question[{$askingFor->getSequence()}][points]" id="question_points" value="{$askingFor->getPuctuation()}" class="form-control">
        </div>
    </div>
    <div class="col-md-6">
        <div class="col-md-4">
            <div class="label-input">
                <label for="question_points" class="animate__animated">
                    Ponderación</label>
            </div>
        </div>
        <div class="form-group col-md-8 field-container" id="td_question_weight">
            <input type="text" tabindex="" name="question[{$askingFor->getSequence()}][weight]" id="question_weight" value="{$askingFor->getWeighing()}" class="form-control">
        </div>
    </div>
    <div class="col-md-12 text-right border-bottom">
        <button type="button" style="margin-bottom: 4px" class="btn btn-danger" onclick="QuestionUtils.delQuetionGroup (this, '{$idRowQuestion}');"
                title="Eliminar pregunta"><i class="fa fa-trash-o"></i>
        </button>
    </div>
</div>