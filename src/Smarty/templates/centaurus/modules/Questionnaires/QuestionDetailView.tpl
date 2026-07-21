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
            <span class="form-control {*label-readonly*}"
                  style="overflow-x: hidden;width: 100%" data-toggle="">
                {$askingFor->getQuestionGroupId()}
                {*if (!empty ($QUESTION_GROUP))}
                    {foreach $QUESTION_GROUP as $qgroup}
                        {if $qgroup->getId () eq $askingFor->getQuestionGroupId()}{$qgroup->getName ()}{/if}
                    {/foreach}
                {/if*}
            </span>
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
            <span class="form-control {*label-readonly*}"
                  style="overflow-x: hidden;width: 100%" data-toggle="">
                {$askingFor->getQuestionStageId()}
                {*foreach $STAGES as $stage}
                    {if $stage->getId () eq $askingFor->getQuestionStageId()}{$stage->getName ()}{/if}
                {/foreach*}
            </span>
        </div>
    </div>
    <div class="col-md-6">
        <div class="col-md-4">
            <div class="label-input">
                <label for="question_title" class="animate__animated ">Pregunta</label>
            </div>
        </div>
        <div class="form-group col-md-8 field-container" id="td_question_title">
            <span class="form-control {*label-readonly*}"
                  style="overflow-x: hidden;width: 100% resize: vertical; word-break: break-word;{if ($askingFor->getQuestion()|strlen) gt 51} min-height: 70px;{else} min-height: 50px;{/if}line-height: 1.35em !important;">
                {$askingFor->getQuestion()}
            </span>
        </div>
    </div>
    <div class="col-md-6">
        <div class="col-md-4">
            <div class="label-input">
                <label for="question_descrption" class="animate__animated">Descripción</label>
            </div>
        </div>
        <div class="form-group col-md-8 field-container" id="td_question_descrption">
            <span class="form-control {*label-readonly*}"
                  style="overflow-x: hidden;width: 100% resize: vertical; word-break: break-word;{if ($askingFor->getDescription()|strlen) gt 51} min-height: 70px;{else} min-height: 50px;{/if}line-height: 1.35em !important;">
                {$askingFor->getDescription()}
            </span>
        </div>
    </div>
    <div class="col-md-6">
        <div class="col-md-4">
            <div class="label-input">
                <label for="question_help" class="animate__animated">Ayuda</label>
            </div>
        </div>
        <div class="form-group col-md-8 field-container" id="td_question_help">
            <span class="form-control {*label-readonly*}"
                  style="overflow-x: hidden;width: 100% resize: vertical; word-break: break-word;{if ($askingFor->getHelp()|strlen) gt 51} min-height: 70px;{else} min-height: 50px;{/if}line-height: 1.35em !important;">
                {$askingFor->getHelp()}
            </span>
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
            <span class="form-control "
                  style="overflow-x: hidden;width: 100% resize: vertical; word-break: break-word;{if ($askingFor->getFeedBack()|strlen) gt 51} min-height: 70px;{else} min-height: 50px;{/if}line-height: 1.35em !important;">
                $askingFor->getFeedBack()}
            </span>
        </div>
        *}
    </div>
    <div class="col-md-12">
        <div class="col-md-6">
            <div class="col-md-4">
                <div class="label-input">
                    <label for="status" class="animate__animated ">
                        <span id="questionannairestagesid"></span>&nbsp;Tipo de cálculo</label>
                </div>
            </div>
            <div class="form-group col-md-8 field-container" id="td_status">
            <span class="form-control {*label-readonly*}"
                  style="overflow-x: hidden;width: 100%" data-toggle="">
                {if (!empty ($CALCULATION_TYPE))}
                    {foreach $CALCULATION_TYPE as $key => $ctype}
                        {if $key eq $askingFor->getCalculationType()}{$ctype}{/if}
                    {/foreach}
                {/if}

            </span>
            </div>
        </div>
        {if $askingFor->getUrlVideo() neq NULL}
        <div class="col-md-6">
            <div class="col-md-4">
                <div class="label-input">
                    <label for="video_url" class="animate__animated ">
                        Url video</label>
                </div>
            </div>
            <div class="form-group col-md-8 field-container" id="td_video_url">
                <span class="form-control b-left"
                      style="overflow-x: hidden;width: 100%" data-toggle="tooltip">
                    {if $askingFor->getUrlVideo() neq NULL}
                        <a href="{$askingFor->getUrlVideo()}" target="_blank">{$askingFor->getUrlVideo()}</a>
                    {else}
                        {$askingFor->getUrlVideo()}
                    {/if}
                </span>
            </div>
        </div>
        {else}
        <div class="col-md-6">
            <div class="col-md-4">&nbsp;</div>
            <div class="col-md-8">&nbsp;</div>
        </div>
        {/if}
    </div>
    <div class="col-md-6">
        <div class="col-md-4">
            <div class="label-input">
                <label for="status" class="animate__animated ">
                    <span id="questionannairestagesid"></span>&nbsp;Tipo de pregunta</label>
            </div>
        </div>
        <div class="form-group col-md-8 field-container" id="td_status">
            <span class="form-control {*label-readonly*}"
                  style="overflow-x: hidden;width: 100%" data-toggle="">
                {if (!empty ($QUESTION_FORM))}
                    {foreach $QUESTION_FORM as $key => $gtype}
                        {if $key eq $askingFor->getQuestionForm()}{$gtype}{/if}
                    {/foreach}
                {/if}
            </span>
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
            <span class="form-control {*label-readonly*}"
                  style="overflow-x: hidden;width: 100%" data-toggle="">
                {if (!empty ($QUESTION_FORM))}
                    {foreach $QUESTION_FORM as $key => $gtype}
                        {if (!empty ($ANSWERS_OPTIONS))}
                            {foreach $ANSWERS_OPTIONS[$key] as $answarekey => $answare}
                                {if $answarekey eq $askingFor->getQuestionType()}{$answare}{/if}
                            {/foreach}
                        {/if}
                {/foreach}
                {/if}
            </span>
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
            <span iclass="form-control" style=";overflow-x: hidden;width: 100%;"
                  data-toggle="">
                {if intval ($askingFor->getPuctuation()) neq 0}
                    {$askingFor->getPuctuation()}
                {else}
                    &nbsp;_&nbsp;
                {/if}
            </span>
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
            <span iclass="form-control" style=";overflow-x: hidden;width: 100%;"
                  data-toggle="">
                {if intval ($askingFor->getWeighing()) neq 0}
                    {$askingFor->getWeighing()}
                {else}
                    &nbsp;_&nbsp;
                {/if}
            </span>
        </div>
    </div>

    <div class="col-md-12 text-right border-bottom">&nbsp;
    </div>
</div>