<div class="row" id="ROW-__ID_ROW__" style="margin-bottom: 4px;padding-bottom: 12px">
    <input type="hidden" id="question-id-__ID_ROW__" name="question[__ID__][questionid]" value="">
    <div class="col-md-6">
        <div class="col-md-4">
            <div class="label-input">
                <label for="status" class="animate__animated ">
                    <span id="questionannairestagesid"></span>&nbsp;Grupo</label>
            </div>
        </div>
        <div class="form-group col-md-8 field-container" id="td_status">
            <input type="text" tabindex="" name="question[__ID__][group]" id="questionannairestagesid" value="" class="form-control">
            {*
            <select id="questionannairestagesid" name="question[__ID__][group]" class="form-control for-filter"
                    tabindex="" >
                <option value="" disabled="disabled" selected="selected">Grupo</option>
                {if (!empty ($QUESTION_GROUP))}
                    {foreach $QUESTION_GROUP as $qgroup}
                        <option value="{$qgroup->getId ()}">{$qgroup->getName ()}</option>
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
            <input type="text" tabindex="" name="question[__ID__][stages]" id="questiongroupid" value="" class="form-control">
            {*
            <select id="questiongroupid" name="question[__ID__][stages]" class="form-control for-filter"
                    tabindex="">
                <option value="" disabled="disabled" selected="selected">Etapa</option>
                {if (!empty ($STAGES))}
                    {foreach $STAGES as $stage}
                        <option value="{$stage->getId ()}">{$stage->getName ()}</option>
                    {/foreach}
                {/if}
            </select>
            *}
        </div>
    </div>
    <div class="col-md-6">
        <div class="col-md-4">
            <div class="label-input">
                <label for="question_title" class="animate__animated ">Pregunta</label>
            </div>
        </div>
        <div class="form-group col-md-8 field-container" id="td_question_title">
            <textarea name="question[__ID__][title]" id="question_title" class="form-control" tabindex=""
                      rows="2"></textarea>
        </div>
    </div>
    <div class="col-md-6">
        <div class="col-md-4">
            <div class="label-input">
                <label for="question_descrption" class="animate__animated">Descripción</label>
            </div>
        </div>
        <div class="form-group col-md-8 field-container" id="td_question_descrption">
        <textarea id="question_descrption" name="question[__ID__][description]" class="form-control" tabindex=""
                  rows="2"></textarea>
        </div>
    </div>
    <div class="col-md-6">
        <div class="col-md-4">
            <div class="label-input">
                <label for="question_help" class="animate__animated">Ayuda</label>
            </div>
        </div>
        <div class="form-group col-md-8 field-container" id="td_question_help">
            <textarea id="question_help" name="question[__ID__][help]" class="form-control" tabindex=""
                      rows="2"></textarea>
        </div>
    </div>
    <div class="col-md-6">&nbsp;;
        {*  not necessary in the question
        <div class="col-md-4">
            <div class="label-input">
                <label for="question_feedback" class="animate__animated">Feed back</label>
            </div>
        </div>
        <div class="form-group col-md-8 field-container" id="td_question_feedback">
            <textarea id="question_feedback" name="question[__ID__][feedback]" class="form-control" tabindex=""
                      rows="2"></textarea>
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
                <select id="calculationtype-__ID_ROW__" name="question[__ID__][calculation_type]"
                        class="form-control for-filter"  tabindex="">
                    <option value="">Tipo de cálculo</option>
                    {if (!empty ($CALCULATION_TYPE))}
                        {foreach $CALCULATION_TYPE as $key => $ctype}
                            <option value="{$key}">{$ctype}</option>
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
                <input type="text" id="video_url-__ID_ROW__" name="question[__ID__][video_url]" value="" class="form-control" tabindex="" onkeyup="validateUrl('video_url');">
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
            <select id="questionform-__ID_ROW__" name="question[__ID__][form]" class="form-control for-filter"
                    tabindex="" onchange="QuestionUtils.quetionForm (this, '__ID_ROW__');">
                <option value="">Tipo de pregunta</option>
                {if (!empty ($QUESTION_FORM))}
                    {foreach $QUESTION_FORM as $key => $gtype}
                        <option value="{$key}">{$gtype}</option>
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
            <select id="answereoption-__ID_ROW__" name="question[__ID__][answere]" class="form-control for-filter"
                    tabindex=""
                    onchange="QuestionUtils.quetionType (this, '__ID_ROW__', '__ID__');">
                <option value="" disabled="disabled" selected="selected">Opciones de respuesta</option>
                {if (!empty ($QUESTION_FORM))}
                    {foreach $QUESTION_FORM as $key => $gtype}
                        {if (!empty ($ANSWERS_OPTIONS))}
                            {foreach $ANSWERS_OPTIONS[$key] as $answarekey => $answare}
                                <option value="{$answarekey}" class="hide" data-type="{$key}">{$answare}</option>
                            {/foreach}
                        {/if}
                    {/foreach}
                {/if}
            </select>
        </div>
    </div>
    <div class="col-md-12" id="question-type-__ID_ROW__"></div>
    <div class="col-md-6">
        <div class="col-md-4">
            <div class="label-input">
                <label for="question_points" class="animate__animated">
                Puntos</label>
            </div>
        </div>
        <div class="form-group col-md-8 field-container" id="td_question_points">
            <input type="text" tabindex="" name="question[__ID__][points]" id="question_points" value="" class="form-control">
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
            <input type="text" tabindex="" name="question[__ID__][weight]" id="question_weight" value="" class="form-control">
        </div>
    </div>
    <div class="col-md-12 text-right border-bottom">
        <button type="button" style="margin-bottom: 4px" class="btn btn-danger" onclick="QuestionUtils.delQuetionGroup (this, '__ID_ROW__');"
                title="Eliminar pregunta"><i class="fa fa-trash-o"></i>
        </button>
    </div>
</div>