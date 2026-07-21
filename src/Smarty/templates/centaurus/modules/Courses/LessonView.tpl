{strip}
    {*$IS_PAID_COURSE*}
    {*$LESSON|var_dump*}
    {assign var='courseId' value=$LESSON->getCourseId ()}
    {assign var='lessonId' value=$LESSON->getId ()}
    {assign var='lessonDescription' value=$LESSON->getDescription ()}
    {assign var='lessonName' value=$LESSON->getName ()}
    {assign var='hasTest' value=$LESSON->getHasTest()}
    {assign var='lessonResources' value=$LESSON->getResources ()}
    {assign var='lessonTest' value=$LESSON->getTest ()}
    {assign var='lessonVideoType' value=$LESSON->getTypeVideo ()}
    {assign var='lessonVideoUrl' value=$LESSON->getVideoUrl ()}
    {assign var='lessonUserStatus' value=$LESSON->getUserLessonStatus ()}
    {assign var='lessonExercise' value=$LESSON->getlessonExercise()}
	{assign var='userHasPaid' value=$USERHASPAID}
	{assign var='lessontopay' value=$LESSONTOPAY}
	{assign var='price' value=$PRICE}
	{assign var='currentLesson' value=$CURRENTLESSON}

    {if $lessonExercise neq NULL}
        {assign var='exerciseId' value=$lessonExercise->getId()}
    {else}
        {assign var='exerciseId' value=0}
    {/if}

    <link type="text/css" href="modules/Courses/Courses.css"/>
    <div class="row">
        <div class="col-xs-12">
            <h1 class="pull-left"><a
                        href="index.php?module=Courses&action=CourseView&record={$courseId}">Lección: {$lessonName}</a>
            </h1>
            <div class="pull-right">
                {if $userHasPaid == 0 && $lessontopay > 0}
                    <form class="pull-right" action="index.php" method="post" style="display: inline-block;">
                        <input type="hidden" name="module" value="Courses"/>
                        <input type="hidden" name="remodule" value="Courses"/>
                        <input type="hidden" name="reaction"
                               value="action=CourseView&record={$courseId}"/>
                        <input type="hidden" name="action" value="AddPaymentCourse"/>
                        <input type="hidden" name="record" value="{$courseId}"/>
                        <button type="submit" class="btn btn-info" style="background-color=red !important; margin-left:1em;">Adquirir</button>
                    </form>
                {/if}
                <div class="btn-group">
                    <a href="{if $PREV_LESSON eq NULL}index.php?module=Courses&action=CourseView&record={$COURSE_ID}{else}index.php?module=Courses&action=LessonView&course={$COURSE_ID}&record={$PREV_LESSON}{/if}"
                       class="btn btn-info" title="{if $PREV_LESSON eq NULL}Volver al curso{else}Lección anterior{/if}"
                       style="margin-right: 25px;"><i class="fa {if $PREV_LESSON eq NULL}fa-list{else}fa-backward{/if}" aria-hidden="true"></i></a>

                    {if $NEXT_LESSON eq NULL}
                        <a href="index.php?module=Courses&action=CourseView&record={$COURSE_ID}"
                           class="btn btn-info" title="Volver al curso">
                            <i class="fa fa-list" aria-hidden="true"></i>
                        </a>
                    {elseif $currentLesson >= ($lessontopay-1) && ($userHasPaid == 0 && $price >0)}
                        <a href="javascript:void(0)"
                           onclick="lessonNoPublish(event)"
                           class="btn btn-info"
                           title="La siguiente lección requiere pago"
                           style="background-color: #ccc;">
                            <i class="fa fa-lock" style="color: {$UI_COLORS.TEXT_WHITE};font-size:0.8em;text-align:center; vertical-align:middle;padding-bottom:0.5em;margin:0px;margin-left:-0.1em;width:1.3em;"></i>
                        </a>
                    {else}
                        <a href="index.php?module=Courses&action=LessonView&course={$COURSE_ID}&record={$NEXT_LESSON}"
                           class="btn btn-info" title="Siguiente lección">
                            <i class="fa fa-forward" aria-hidden="true"></i>
                        </a>
                    {/if}
                </div>
            </div>
        </div>
    </div>
    {if $IS_PAID_COURSE}
        <div class="alert alert-success  course-no-payed">
            <button class="close" data-dismiss="alert" type="button">×</button>
            <strong style="padding-left: 60px;">¡Muy bien!</strong>
            <p style="padding-left: 60px;">
                Esperamos que lo que hayas visto hasta ahora sea de tu agrado.&nbsp;Para continuar, sin embargo&nbsp;
                necesitamos que contrates el curso.<br>Si estás interesado pincha en el botón de tu derecha</p>
        </div>
    {/if}
    {if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
        <div class="row">
            <div class="alert alert-{if (isset ($IS_ERROR)) && ($IS_ERROR)}danger{else}success{/if}">
                <strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
            </div>
        </div>
    {/if}
    <div class="main-box no-header">
        <div class="main-box-body">
            {if !$IS_PAID_COURSE}
                <div class="row">
                    {if (!empty ($lessonVideoUrl))}
                        <div class="col-xs-12 col-md-10 col-md-push-1 col-lg-8 col-lg-push-2">
                        {if $lessonVideoType eq 'VIMEO'}
							<!--2025-01-20/GGC/Cambios en los estilos para ajustar tamaño de presentación del video-->
                            <div class="embed-responsive" style="width:55vw; max-width:60vw; height:auto; aspect-ratio: 16 / 9; margin:0 auto;border-style:solid; border-width:1px; border-color:#EAEAEA;" data-vimeo-url="{$lessonVideoUrl}">
                            </div>
                        {else}
                            <div style="text-align: center">
								<!--2025-01-20/GGC/Cambios en los estilos para ajustar tamaño de presentación del video-->
                                <iframe id="video-0" class="youtube-video" style="width:55vw; max-width:60vw; height:auto; aspect-ratio: 16 / 9; margin:0 auto; border-style:solid; border-width:1px; border-color:#EAEAEA;" src="{$lessonVideoUrl}" frameborder="0"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                        allowfullscreen="allowfullscreen">
                                </iframe>
                            </div>
                        {/if}
                        </div>
                    {/if}
                    <div class="col-xs-12">
                        <h2>Descripción</h2>
                        {$lessonDescription}
                    </div>
                    {if (!empty ($lessonResources) && $SHOW_RESOURCE)}
                        <div class="col-xs-12">
                            <h2>Recursos</h2>
                            <ul class="resources">
                                {foreach $lessonResources as $lessonResource}
                                    {assign var='resourceName' value=$lessonResource->getName ()}
                                    {assign var='resourceType' value=$lessonResource->getType ()}
                                    <li class="resource">
                                        {if ($resourceType == CourseResource::TYPE_ATTACHMENT)}
                                            <a href="index.php?module=Courses&action=DownloadAttachment&record={$lessonResource->getId ()}&Ajax=true"
                                               target="_blank">{$resourceName}</a>
                                        {else}
                                            <a href="{$lessonResource->getUrl ()}" target="_blank">{$resourceName}</a>
                                        {/if}
                                    </li>
                                {/foreach}
                            </ul>
                        </div>
                    {/if}
                    <div class="col-xs-12 text-center">
                        {* Debug info *}
                        {if isset($DEBUG_VALUES)}
                            <div style="display:none">
                                Debug Values:
                                evaluationStatus: {$DEBUG_VALUES.evaluationStatus}
                                prevLessonStatus: {$DEBUG_VALUES.prevLessonStatus}
                                hasTest: {$DEBUG_VALUES.hasTest}
                                lessonUserStatus: {$DEBUG_VALUES.lessonUserStatus}
                            </div>
                        {/if}
                        
                        {* Botón de evaluación - Solo se muestra si la lección tiene evaluación *}
                        {if $hasTest}
                            <div style="position: relative;">
                                <div style="display: inline-block;">
                                    <a {if $PREV_LESSON_STATUS eq 'LESSON_PASSED'}
                                            href="index.php?module=Courses&action=TakeTest&record={$LESSON_ID}&course={$COURSE_ID}"
                                       {/if}
                                       style="background-color: {if $PREV_LESSON_STATUS neq 'LESSON_PASSED'}{$EVALUATION_COLORS.DISABLED}
                                                              {elseif $EVALUATION_STATUS eq 'TEST_PASSED'}{$EVALUATION_COLORS.PASSED}
                                                              {elseif $EVALUATION_STATUS eq 'TEST_NOT_PASSED'}{$EVALUATION_COLORS.NOT_PASSED}
                                                              {else}{$EVALUATION_COLORS.DEFAULT}{/if};
                                              color: {if $PREV_LESSON_STATUS neq 'LESSON_PASSED'}#f2f3f2
                                                    {else}#FFFFFF{/if};
                                              min-width: 360px;
                                              padding: 10px 20px;"
                                       class="btn test-button"
                                       {if $PREV_LESSON_STATUS neq 'LESSON_PASSED'}
                                          title="Esta evaluación estará disponible una vez hayas aprobado la lección anterior"
                                       {/if}
                                       >
                                        <span style="display: inline-block">{$MOD.LBL_EVALUATION_BUTTON}</span>
                                    </a>
                                </div>

                                {* Botón de feedback - Solo se muestra cuando la evaluación está en TEST_PASSED *}
                                {if $PREV_LESSON_STATUS eq 'LESSON_PASSED' && $EVALUATION_STATUS eq 'TEST_PASSED'}
                                    <div style="position: absolute; right: 0; top: 50%; transform: translateY(-50%);">
                                        <a href="index.php?module=Courses&action=FeddbackView&course={$COURSE_ID}&record={$LESSON_ID}"
                                           class="btn btn-success">Ver feedback</a>
                                    </div>
                                {/if}
                            </div>
                        {/if}
                    </div>
                    <div class="col-xs-12 text-center" style="margin-top: 10px;">
                        {* Botón de ejercicio práctico - Solo se muestra si la lección tiene ejercicio *}
                        {if $lessonExercise neq NULL}
                            <div style="position: relative;">
                                <div style="display: inline-block;">
                                    <a {if $PREV_LESSON_STATUS eq 'LESSON_PASSED' && 
                                          ($hasTest neq '1' || 
                                           $EVALUATION_STATUS eq 'TEST_PASSED' || 
                                           $LESSON_USER_STATUS eq 'LESSON_PASSED')}
                                            href="index.php?module=Courses&action=LessonExerciseView&record={$exerciseId}&course={$COURSE_ID}&lesson={$lessonId}"
                                       {/if}
                                       style="background-color: {if $PREV_LESSON_STATUS neq 'LESSON_PASSED'}{$EXERCISE_COLORS.DISABLED}
                                                              {elseif $EXERCISE_STATUS eq 'EXERCISE_DONE'}{$EXERCISE_COLORS.DONE}
                                                              {elseif $EXERCISE_STATUS eq 'EXERCISE_VISITED'}{$EXERCISE_COLORS.VISITED}
                                                              {elseif $hasTest eq '1' && $EVALUATION_STATUS neq 'TEST_PASSED'}{$EXERCISE_COLORS.DISABLED}
                                                              {else}{$EXERCISE_COLORS.READY}{/if};
                                              color: #FFFFFF;
                                              min-width: 360px;
                                              padding: 10px 20px;"
                                       class="btn exercise-button">
                                        <span style="display: inline-block">{$MOD.LBL_PRACTICAL_EXERCISE_BUTTON}</span>
                                    </a>
                                </div>
                            </div>
                        {/if}

                        {* Botón "Listo, he terminado la lección" - Solo se muestra si la lección no tiene ni evaluación ni ejercicio *}
                        {if !$hasTest && $lessonExercise eq NULL}
                            <a {if $PREV_LESSON_STATUS eq 'LESSON_PASSED'}
                                    href="javascript:void(0);"
                                    onclick="handleLessonFinished('{$LESSON_ID}', '{$COURSE_ID}')"
                               {/if}
                               style="background-color: {if $PREV_LESSON_STATUS neq 'LESSON_PASSED'}#7E8F7E
                                                      {elseif $LESSON_USER_STATUS eq 'LESSON_PASSED'}#2ECC71
                                                      {else}#76A2D4{/if};
                                      color: #FFFFFF;
                                      min-width: 350px;
                                      padding: 10px 20px;"
                               class="btn test-button">
                                <span style="display: inline-block">Listo, he terminado la lección</span>
                            </a>
                        {/if}
                    </div>

                    <div class="col-xs-12 text-right" style="margin-top: 20px;">
                        <div class="btn-group">
                            <a href="{if $PREV_LESSON eq NULL}index.php?module=Courses&action=CourseView&record={$COURSE_ID}{else}index.php?module=Courses&action=LessonView&course={$COURSE_ID}&record={$PREV_LESSON}{/if}"
                               class="btn btn-info" title="{if $PREV_LESSON eq NULL}Volver al curso{else}Lección anterior{/if}"
                               style="margin-right: 25px;"><i class="fa {if $PREV_LESSON eq NULL}fa-list{else}fa-backward{/if}" aria-hidden="true"></i></a>{if $NEXT_LESSON eq NULL}
							<a href="index.php?module=Courses&action=CourseView&record={$COURSE_ID}"
							   class="btn btn-info" title="Volver al curso">
								<i class="fa fa-list" aria-hidden="true"></i>
							</a>
							{elseif $currentLesson >= ($lessontopay-1) && ($userHasPaid == 0 && $price >0)}
								<a href="javascript:void(0)"
								   onclick="lessonNoPublish(event)"
								   class="btn btn-info"
								   title="La siguiente lección requiere pago"
								   style="background-color: #ccc;">
									<i class="fa fa-lock" style="color: {$UI_COLORS.TEXT_WHITE};font-size:0.8em;text-align:center; vertical-align:middle;padding-bottom:0.5em;margin:0px;margin-left:-0.1em;width:1.3em;"></i>
								</a>
							{else}
								<a href="index.php?module=Courses&action=LessonView&course={$COURSE_ID}&record={$NEXT_LESSON}"
								   class="btn btn-info" title="Siguiente lección">
									<i class="fa fa-forward" aria-hidden="true"></i>
								</a>
							{/if}

                        </div>
                    </div>
                </div>
            {else}
                <div class="row">
                    <div class="col-xs-12">
                        <h2>Descripción:
                            <small>Resumen del contenido</small>
                        </h2>
                        {$lessonDescription}
                    </div>
                </div>
            {/if}

        </div>
    </div>
    {if (!empty ($lessonVideoUrl))}
        <script type="text/javascript" src="https://player.vimeo.com/api/player.js"></script>
    {/if}
    <script type='text/javascript'>
        {literal}
            jQuery(document).ready(function () {
                window.addEventListener('beforeunload', function (event) {
                    var data = new FormData();
                    data.append('Ajax','true');
                    data.append('function','TRACK-LESSON');
                    data.append('track_id',{/literal} {$TRACK_LESSON_ID} {literal});

                    navigator.sendBeacon ('index.php?module=Courses&action=AjaxCourseUtils', data);

                });
            });
        {/literal}
    </script>
    <script type='text/javascript'>
        function handleLessonFinished(lessonId, courseId) {
            jQuery.ajax({
                url: 'index.php',
                type: 'GET',
                data: {
                    module: 'Courses',
                    action: 'AjaxCourseUtils',
                    function: 'LESSON_PASSED',
                    record: lessonId,
                    course: courseId
                },
                success: function(response) {
                    // Redirigir a la vista del curso
                    window.location.href = 'index.php?module=Courses&action=CourseView&record=' + courseId;
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                }
            });
        }
    </script>
{/strip}