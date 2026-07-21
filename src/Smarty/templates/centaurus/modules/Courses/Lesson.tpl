{strip}
    {*$LESSON|var_dump*}
    {if (isset ($LESSON))}
        {assign var='lessonId' value=$LESSON->getId ()}
        {assign var='lessonDescription' value=$LESSON->getDescription ()}
        {assign var='hasTest' value=$LESSON->getHasTest()}
        {assign var='lessonName' value=$LESSON->getName ()}
        {assign var='lessonResources' value=$LESSON->getResources ()}
        {assign var='publishStatus' value=$LESSON->getStatus ()}
        {assign var='lessonTest' value=$LESSON->getTest ()}
        {assign var='lessonTipeVideo' value=$LESSON->getTypeVideo ()}
        {assign var='lessonVideoUrl' value=$LESSON->getVideoUrl ()}
        {if $lessonTipeVideo eq 'VIMEO'}
            {assign var='lessonVimeoUrl' value=$LESSON->getVideoUrl ()}
        {else}
            {assign var='lessonYoutobeUrl' value=$LESSON->getVideoUrl ()}
        {/if}
        {if ($LESSON->getLessonExercise() neq NULL)}
            {assign var='exerciseId' value=$LESSON->getLessonExercise ()->getId()}
            {assign var='exerciseName' value=$LESSON->getLessonExercise ()->getName()}
            {assign var='exerciseDescription' value=$LESSON->getLessonExercise ()->getDescription()}
            {assign var='exerciseTest' value=$LESSON->getLessonExercise ()->getHasTest()}
            {assign var='minimumScore' value=$LESSON->getLessonExercise ()->getPassingScore()}
        {else}
            {assign var='exerciseId' value=null}
            {assign var='exerciseName' value=null}
            {assign var='exerciseDescription' value=null}
            {assign var='exerciseTest' value='0'}
            {assign var='minimumScore' value=null}
        {/if}
    {else}
        {assign var='lessonId' value=null}
        {assign var='lessonDescription' value=null}
        {assign var='lessonName' value=null}
        {assign var='lessonResources' value=null}
        {assign var='lessonTest' value=null}
        {assign var='lessonTipeVideo' value='VIMEO'}
        {assign var='publishStatus' value=1}
        {assign var='lessonVimeoUrl' value=null}
        {assign var='lessonYoutobeUrl' value=null}
        {assign var='hasTest' value='1'}
    {/if}
    {if (isset ($lessonTest))}
        {assign var='testDescription' value=$lessonTest->getDescription ()}
        {assign var='testFeedback' value=$lessonTest->getFeedback ()}
        {assign var='feedbackNotApproved' value=$lessonTest->getFeedbackNotApproved()}
        {assign var='testMinimumScore' value=$lessonTest->getMinimumScore ()}
        {assign var='testQuestions' value=$lessonTest->getQuestions ()}
        {assign var='testTotalQuestions' value=$lessonTest->getTotalQuestionsPerTest ()}
    {else}
        {assign var='testDescription' value=null}
        {assign var='testFeedback' value=null}
        {assign var='feedbackNotApproved' value=null}
        {assign var='testMinimumScore' value=null}
        {assign var='testQuestions' value=null}
        {assign var='testTotalQuestions' value=null}
    {/if}
    <div id="lesson-{$INDEX}"
         class="tab-pane fade{if ($INDEX === 0)} in active{/if}"{if (isset ($INDEX))} data-index="{$INDEX}"{/if}>
        <div class="row lesson">
            <input type="hidden" name="lessons[{$INDEX}][lessonid]" class="lesson-id" value="{$lessonId}"/>
            <div class="col-xs-12">
                    {* vimeo *}
                    <div class="col-xs-12 col-md-6 {if $lessonTipeVideo neq 'VIMEO'} hide{/if}"">
                        <div id="video-{$INDEX}"
                             class="embed-responsive embed-responsive-16by9 video"{if (isset ($lessonVimeoUrl))} data-vimeo-url="{$lessonVimeoUrl}"{/if}></div>
                    </div>
                    {* /vimeo *}
                    {* youtube *}
                    <div class="col-xs-12 col-md-6{if $lessonTipeVideo neq 'YOUTUBE'} hide{/if}" data-index="{$INDEX}">
                        <iframe id="video-{$INDEX}" class="youtube-video" width="560" height="410" src="{$lessonYoutobeUrl}" frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen="allowfullscreen">
                        </iframe>
                    </div>
                    {* /youtube *}
                <div class="col-xs-12 col-md-6">
                    <div class="form-group lesson-name-group">
                        <label for="lesson-name-{$INDEX}">Nombre: <span class="required">*</span></label>
                        <input type="text" id="lesson-name-{$INDEX}" name="lessons[{$INDEX}][lessonname]"
                               value="{$lessonName}" class="form-control lesson-name" maxlength="255"/>
                    </div>
                    {* Estado del la lección *}
                    <div class="form-group lesson-status-group">
                        <label for="lesson-status-{$INDEX}">Estado de la lección:</label>
                        <select id="lesson-status-{$INDEX}" name="lessons[{$INDEX}][lesson_status]" class="form-control lesson-status">
                            {if (!empty ($LESSON_STATUS))}
                                <option value="">Seleccionar estado de la lección</option>
                                {foreach $LESSON_STATUS as $kStatus => $lessonStatus}
                                    <option value="{$kStatus}"{if ($kStatus eq $publishStatus)} selected="selected"{/if}>{$lessonStatus}</option>
                                {/foreach}
                            {else}
                                <option value="1" selected="selected">Publicada</option>
                            {/if}
                        </select>
                    </div>
                    {* Tipo de video *}
                    <div class="form-group video-url-type-group">
                        <label for="video-type-{$INDEX}">Tipo de vídeo:</label>
                        <select id="video-type-{$INDEX}" name="lessons[{$INDEX}][videotype]" class="form-control lesson-video-type" onchange="CourseUtils.selectVideo (this);">
                            {if (!empty ($TYPE_VIDEO))}
                                <option value="">Seleccionar tipo de video</option>
                                {foreach $TYPE_VIDEO as $typeVideo}
                                    <option value="{$typeVideo}"{if ($typeVideo == $lessonTipeVideo)} selected="selected"{/if}>{$typeVideo|strtolower|ucfirst}</option>
                                {/foreach}
                                {else}
                                <option value="VIMEO" selected="selected">Vimeo</option>
                            {/if}
                        </select>
                    </div>
                    <div class="form-group video-url-group">
                        <label for="video-url-{$INDEX}">URL:</label>
                        <input type="url" id="video-url-{$INDEX}" name="lessons[{$INDEX}][videourl]"
                               value="{$lessonVideoUrl}" class="form-control lesson-video-url" maxlength="2048"
                               onchange="CourseUtils.showVideo (this);"/>
                    </div>
                    <div class="form-group resources-group">
                        <label>Recursos:
                            <button type="button" class="btn btn-default btn-icon"
                                    onclick="CourseUtils.addResource (this, 'NO');"><i class="fa fa-plus"></i></button>
                        </label>
                        <ul class="resources" data-maximum-file-size="{$UPLOAD_MAXSIZE / (1024 * 1024)}">
                            {if (!empty ($lessonResources))}
                                {foreach $lessonResources as $index => $lessonResource}
                                    {if $lessonResource->getHasExercise () eq 'YES'}{continue}{/if}
                                    {include file='modules/Courses/Resource.tpl' RESOURCE=$lessonResource LESSON_INDEX=$INDEX RESOURCE_INDEX=$index}
                                {/foreach}
                            {/if}
                        </ul>
                    </div>
                </div>
                <div class="col-xs-12">
                    <div class="form-group lesson-description-group">
                        <label for="lesson-description-{$INDEX}">Descripción: <span class="required">*</span></label>
                        <textarea id="lesson-description-{$INDEX}" name="lessons[{$INDEX}][description]"
                                  class="form-control lesson-description">{$lessonDescription}</textarea>
                    </div>
                </div>
                <div class="col-xs-12 test">
                    <h2>Evaluación</h2>
                    {math equation= rand() assign= "idQuestion"}
                    <div class="col-xs-12 col-md-10">
                        <div class="form-group test-description-group">
                            <label for="test-description-{$INDEX}">Descripción: <span class="required">*</span></label>
                            <textarea id="test-description-{$INDEX}" name="lessons[{$INDEX}][test][description]"
                                      class="form-control test-description" {if $hasTest eq '0'}readonly="readonly"{/if}>{if $hasTest neq '0'}{$testDescription}{/if}</textarea>
                        </div>
                        <div id="feedback-{$idQuestion}" class="form-group test-feedback-group">
                            <label for="test-feedback-{$INDEX}">Feedback: Evaluación aprobada<span class="required">*</span></label>
                            <textarea id="test-feedback-{$INDEX}" name="lessons[{$INDEX}][test][feedback]"
                                      class="form-control test-feedback" >{if $hasTest neq '0'}{$testFeedback}{/if}</textarea>
                        </div>
                        <div id="feedback-{$idQuestion}" class="form-group test-feedback_no_approved-group">
                            <label for="test-feedback_no_approved-{$INDEX}">Feedback: Evaluación no aprobada<span class="required">*</span></label>
                            <textarea id="test-feedback_no_approved-{$INDEX}" name="lessons[{$INDEX}][test][feedback_not_approved]"
                                      class="form-control test-feedback" >{if $hasTest neq '0'}{$feedbackNotApproved}{/if}</textarea>
                        </div>
                    </div>
                    <div class="col-xs-12 col-md-2">
                        <div class="form-group test-has-questions-group">
                            <label for="test-has-test-{$INDEX}">¿Lección con evaluación?</label>
                            <select id="test-has-test-{$INDEX}"
                                    data-idQuestion="{$idQuestion}"
                                    name="lessons[{$INDEX}][hastest]"
                                    class="form-control lesson-has-test"
                                    onchange="CourseUtils.hasTest(this, {$idQuestion}, {$INDEX})">
                                <option value="1" {if $hasTest eq '1'} selected="selected"{/if} >Si</option>
                                <option value="0" {if $hasTest eq '0'} selected="selected"{/if} >No</option>
                            </select>
                        </div>
                        <div class="form-group test-minimum-score-group">
                            <label for="test-minimum-score-{$INDEX}">Puntuación aprobatoria (%): <span class="required">*</span></label>
                            <input type="number" id="test-minimum-score-{$INDEX}"
                                   name="lessons[{$INDEX}][test][minimumscore]" value="{if $hasTest eq '1'}{$testMinimumScore}{/if}"
                                   {if $hasTest eq '0'}readonly="readonly"{/if}
                                   class="form-control test-minimum-score" min="0" max="100" step="1"/>
                        </div>
                        <div class="form-group test-total-questions-group">
                            <label for="test-total-questions-{$INDEX}">Preguntas por evaluación: <span class="required">*</span></label>
                            <input type="number" id="test-total-questions-{$INDEX}"
                                   {if $hasTest eq '0'}readonly="readonly"{/if}
                                   name="lessons[{$INDEX}][test][totalquestionspertest]" value="{if $hasTest eq '1'}{$testTotalQuestions}{/if}"
                                   class="form-control test-total-questions" min="1" step="1"/>
                        </div>
                    </div>
                    <div class="col-xs-12 test-questions">
                        <h4>Preguntas
                            <button id="addquestion-{$idQuestion}"
                                    type="button" class="btn {if $hasTest eq '0'}btn-danger{else}btn-default{/if}"
                                    onclick="CourseUtils.addQuestion (this);"
                                    {if $hasTest eq '0'}disabled="disabled"{/if}>
                                <i class="fa fa-plus"></i>
                            </button>
                        </h4>
                        <div  id="questions-{$idQuestion}" class="questions">
                            {if (!empty ($testQuestions))}
                                {foreach $testQuestions as $index => $question}
                                    {include file='modules/Courses/Question.tpl' QUESTION=$question LESSON_INDEX=$INDEX QUESTION_INDEX=$index}
                                {/foreach}
                            {elseif ($hasTest eq '1')}
                                {for $index=0 to 1}
                                    {include file='modules/Courses/Question.tpl' QUESTION=null LESSON_INDEX=$INDEX QUESTION_INDEX=$index}
                                {/for}
                            {/if}
                        </div>
                    </div>
                </div>
            </div>
        <div class="col-xs-12 lessons-exercises" style="padding-top: 25px">
           {include file="modules/Courses/LessonsExercises.tpl"}
        </div>
        </div>
    </div>
{/strip}