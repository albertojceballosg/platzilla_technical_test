{extends file='modules/Courses/Base/testResultLayOut.tpl'}
{if $TEST_STATUS eq 'TEST_PASSED'}
    {assign var='feedback' value=$TEST->getFeedback()}
{else}
    {assign var='feedback' value=$TEST->getFeedbackNotApproved()}
{/if}
{assign var='lastQuestion' value=null}
{assign var='lastQuestionFb' value=null}
{assign var='businessType' value=null}
{assign var='createdDate' value=null}

{block name="css"}
    <link type="text/css" href="modules/Courses/Courses.css"/>
{/block}
{block name="link_title"}
    <a href="index.php?module=Courses&action=LessonView&record={$LESSON_ID}&course={$COURSE_ID}">Resultados de la
        evaluación y/o diagnóstico</a>
{/block}
{block name="navi_page"}
    <a href="index.php?module=Courses&action=LessonView&record={$LESSON_ID}&course={$COURSE_ID}"
       class="btn btn-info" title="ir a la Lección" style="margin-right: 25px;">
        <i class="fa fa-backward" aria-hidden="true"></i>
    </a>
    <a href="index.php?module=Courses&action=CourseView&record={$COURSE_ID}" class="btn btn-info"
       title="lecciones" style="margin-right: 15px;">
        <i class="fa fa-list-ul" aria-hidden="true"></i>
    </a>
{/block}
{block name="feedback_content"}
    {if $feedback neq NULL}
        <div class="col-lg-12 col-md-12 col-xs-12 text-justify" style="margin-top: 1em; margin-left: 1em;">
            {str_replace('<br />', "", str_replace('<br>', "", $feedback))}
        </div>
    {else}
        <div class="alert alert-info">¡Gracias por participar!</div>
    {/if}
{/block}
{block name="test_result"}
    <div class="col-lg-12 col-md-12 col-xs-12 text-justify" style="margin-top: 1em;">
		<!-- Ajustes en estilos por GGC/20250123-->
        <div class="col-lg-10 col-md-10 col-xs-10 text-left" style="float:left; max-width:65vw;">
            <h2><strong>El resultado según las respuesta que ha dado es:</strong></h2>
        </div>
        <div style="float:right"> <!-- class="col-lg-2 col-md-2 col-xs-2 text-center">-->
            <span class="label {if $TEST_STATUS eq 'TEST_PASSED'}label-success{else}label-danger{/if}"
                  style="padding: 5px 10px; font-size: 1.0em;">
                {$MOD[$TEST_STATUS]}</span>
        </div>
    </div>
{/block}
{* QUESTION_RESULTS *}
{block name="question_result"}
{if $QUESTION_RESULTS neq NULL}
<div class="table-responsive" style="margin-top: 15px">
    <table class="table table-hover table-responsive">
        <tbody id="task-panel-table">
        {foreach $QUESTION_RESULTS as $idex => $questionResult}
            {if $questionResult['id'] neq $lastQuestion}
                {assign var='lastQuestion' value=$questionResult['id']}
                <tr style="background-color: {$UI_COLORS.BACKGROUND_LIGHT_GRAY}">
                    <td style="width: 20%">
                        <div class="col-lg-12 col-md-12 col-xs-12 text-left">
                            <strong>Pregunta:</strong>
                        </div>
                    </td>
                    <td style="width: 80%">
                        <div class="col-lg-12 col-md-12 col-xs-12 text-justify">
                            {$questionResult['statement']}
                        </div>
                    </td>
                </tr>
            {/if}
            <tr>
                <td style="width: 20%">
                    <div class="col-lg-12 col-md-12 col-xs-12 text-left">
                        <strong>Respuesta recibida:</strong>
                    </div>
                </td>
                <td style="width: 80%">
                    <div class="row">
                        <div class="col-lg-10 col-md-10 col-xs-10 text-justify">
                            {$questionResult['answer']}
                        </div>
                        <div class="col-lg-2 col-md-2 col-xs-2 text-center ">
                                <span class="label {if $questionResult['evaluated'] eq 'TO_BE_PASSED'}label-danger{else}label-success{/if}"
                                      style="padding:  5px 10px; font-size: 0.95em">
                                    {$MOD[$questionResult['evaluated']]}</span>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td style="width: 20%">
                    <div class="col-lg-12 col-md-12 col-xs-12 text-left">
                        <strong>Feedback:</strong>
                    </div>
                </td>
                <td style="width: 80%">
                    <div class="col-lg-12 col-md-12 col-xs-12 text-justify">
                       {$questionResult['feedback']}
                    </div>
                </td>
            </tr>
        {/foreach}
        </tbody>
    </table>
    {/if}
{/block}
{*QUESTION_FEEDBACKS*}
{block name="question_result_feedback"}
    {if $QUESTION_FEEDBACKS neq NULL}
    <div class="table-responsive" style="margin-top: 15px">
        <table class="table table-hover table-responsive">
            <tbody id="task-panel-table">
            {foreach $QUESTION_FEEDBACKS as $idex => $questionResult}
                {if $questionResult['questionid'] neq $lastQuestionFb}
                    {assign var='lastQuestion' value=$questionResult['questionid']}
                    <tr style="background-color: {$UI_COLORS.BACKGROUND_LIGHT_GRAY}">
                        <td style="width: 20%">
                            <div class="col-lg-12 col-md-12 col-xs-12 text-left">
                                <strong>Pregunta:</strong>
                            </div>
                        </td>
                        <td style="width: 80%">
                            <div class="col-lg-12 col-md-12 col-xs-12 text-justify">
                                {$questionResult['statement']}
                            </div>
                        </td>
                    </tr>
                {/if}
                <tr>
                    <td style="width: 20%">
                        <div class="col-lg-12 col-md-12 col-xs-12 text-left">
                            <strong>Respuesta recibida:</strong>
                        </div>
                    </td>
                    <td style="width: 80%">
                        <div class="row">
                            <div class="col-lg-10 col-md-10 col-xs-10 text-justify">
                                {$questionResult['answer']}
                            </div>
                            <div class="col-lg-2 col-md-2 col-xs-2 text-center ">
                                <span class="label {if $questionResult['status'] eq 'TO_BE_PASSED'}label-danger{else}label-success{/if}"
                                      style="padding:  5px 10px; font-size: 0.95em">
                                    {$MOD[$questionResult['status']]}</span>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="width: 20%">
                        <div class="col-lg-12 col-md-12 col-xs-12 text-left">
                            <strong>Feedback:</strong>
                        </div>
                    </td>
                    <td style="width: 80%">
                        <div class="col-lg-12 col-md-12 col-xs-12 text-justify">
                            {$questionResult['feedback']}
                        </div>
                    </td>
                </tr>
            {/foreach}
            </tbody>
        </table>
        {/if}
{/block}
