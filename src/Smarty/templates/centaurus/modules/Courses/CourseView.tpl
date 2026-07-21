{strip}
    {*$COURSE|var_dump*}
    {assign var='courseId' value=$COURSE->getId ()}
    {assign var='courseCategoryId' value=$COURSE->getCategoryId ()}
    {assign var='courseDescription' value=$COURSE->getDescription ()}
    {assign var='courseLessons' value=$COURSE->getLessons ()}
    {assign var='courseLevel' value=$COURSE->getLevel ()}
    {assign var='courseName' value=$COURSE->getName ()}
    {assign var='coursePrice' value=$COURSE->getPrice ()}
    {assign var='paidLesson' value=$COURSE->getLessonIndex ()}
    {assign var='courseStatus' value=$COURSE->getStatus ()}
    {assign var='isPaid' value=$COURSE->isPaid ()}
    {assign var='courseFirstLesson' value=$courseLessons[0]}
    {assign var='courseTargetAudience' value=$COURSE->getTargetAudience ()}
    {assign var='courseImage' value=$COURSE->getImageCourse ()}
    {assign var='courseImageType' value=$COURSE->getImageType ()}
    {assign var='courseVideo' value=$COURSE->getVideoCourse ()}
    {assign var='courseVideoType' value=$COURSE->getVideoType ()}
    {assign var='forumName' value=$COURSE->getForumName ()}
    {assign var='forumUrl' value=$COURSE->getForumUrl ()}
	{assign var='lessontopay' value=$COURSE->getLessonToPay ()}
	{assign var='userHasPaid' value=$USERHASPAID}
    <link type="text/css" href="modules/Courses/Courses.css"/>
    {if $MESSAGE neq NULL && false}
    <div class="alert alert-{if ($IS_ERROR)}danger{else}success{/if} fade in">
    	<strong>{if ($IS_ERROR)}Error!{else}Listo!{/if}</strong> {$MESSAGE}
    </div>
    {/if}
    <div class="row">
        <div class="col-xs-12">
            <h1 class="pull-left"><a href="index.php?module=Home&action=index&tab=TRAINING">Curso: {$courseName}</a>
            </h1>
            {if $courseLessons neq NULL}
                <div class="action-bar pull-right">
                    {if (($coursePrice > 0) && ($lessontopay > 0) && ($userHasPaid === 0))}
                        <form action="index.php" method="post" style="display: inline-block;">
                            <input type="hidden" name="module" value="Courses"/>
                            <input type="hidden" name="remodule" value="Courses"/>
                            <input type="hidden" name="reaction"
                                   value="action=CourseView&record={$courseId}"/>
                            <input type="hidden" name="action" value="AddPaymentCourse"/>
                            <input type="hidden" name="record" value="{$courseId}"/>
                            <button type="submit" class="btn btn-info">Adquirir</button>
                        </form>
                    {/if}
                    <!--<a href="index.php?module=Courses&action=LessonView&course={$courseId}&record={$courseFirstLesson->getId ()}"
                       class="btn btn-success">Comenzar</a>-->
                </div>
            {/if}
        </div>
    </div>
    {if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
        <div class="row">
            <div class="alert alert-{if (isset ($IS_ERROR)) && ($IS_ERROR)}danger{else}success{/if}">
                <strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
            </div>
        </div>
    {/if}
    <div class="row">
        <div class="main-box no-header">
            <div class="main-box-body">
                <div class="row">
                    <div class="col-xs-12 col-md-12">
                            {if (!empty ($courseVideo))}
                                {if $courseVideoType eq 'VIMEO'}
								<!--2025-01-20/GGC/Cambios en los estilos para ajustar tamaño de presentación del video-->
									<div id="video-{$courseId}" style="width:55vw; max-width:60vw; height:auto; aspect-ratio: 16 / 9;margin:0 auto; border-style:solid; border-width:1px; border-color:{$UI_COLORS.BORDER_GRAY};" class="embed-responsive" video"{if (null !== $courseVideo)} data-vimeo-url="{$courseVideo}"{/if}></div>
                                {else}
                                    <div style="text-align: center">
									<!--2025-01-20/GGC/Cambios en los estilos para ajustar tamaño de presentación del video-->
                                    <iframe id="video-{$courseId}" class="youtube-video" style="width:55vw; max-width:60vw; height:auto; aspect-ratio: 16 / 9; margin:0 auto; border-style:solid; border-width:1px; border-color:{$UI_COLORS.BORDER_GRAY};" src="{if (null !== $courseVideo)}{$courseVideo}{/if}" frameborder="0"
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                            allowfullscreen="allowfullscreen">
                                    </iframe>
                                    </div>
                                {/if}
                            {else}
                                {if !empty ($courseImage)}
                                    <img id="course-photo-{$courseId}"
                                         src="data:{$courseImageType}; base64, {$courseImage}"
                                         class="img-responsive center-block">
                                {else}
                                    <img class="img-responsive center-block"
                                         src='data:image/png;base64,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J8ZWdS9yndTeELylGssvoWClW3nsz7JetW2C750FhxIe9GB3oiPzfd/rXthuCpmp2TspKegINQStjdk7c54x1KX3hHeS/q3AEBfGSVyi/8LgVlioSSMVvq43LwoeFwHM/5u2zp2JR/9uTpa0QMFeDikmZHZwSWhydHJMUHl4qP4UJtJex4NyMgW0XrScdK5w7afP4RQembpqbia2tIDBSzglImR8shccVa0TKqIMYpA2b5Erv/5pMcVRGFFi8o3Ws8IB217bU3saciIXX0uuvpadJF4uUWdGJskcvJ8qTpKZYdahCKF73ucJgEDVkIL08KFrISv5+oE2XKKOX2pUk56c/W2LAEK8LAAYGUXrVYs/USPkXouAAVA7GqW2sTExMTExMTExMTExMTExMTExMTExMTExMTExMTExMTExMTExMTExMTExMTExMTExMTExMTExMTExMTExMTkMvL/AWR5YRIIW6wZAAAAJXRFWHRkYXRlOmNyZWF0ZQAyMDIxLTAyLTA4VDA5OjU5OjQzKzAwOjAwo2YCiwAAACV0RVh0ZGF0ZTptb2RpZnkAMjAyMS0wMi0wOFQwOTo1OTo0MyswMDowMNI7ujcAAAAASUVORK5CYII='/>
                                {/if}
                            {/if}
                        <div class="course-description">{$courseDescription}</div>
                    </div>
                    <div class="col-xs-12 col-md-12">
                        <h2>Lecciones</h2>

                        <ul class="lesson-portraits">
							{foreach $courseLessons as $index => $courseLesson}
								<li class="lesson">
									<span>{$courseLesson->getName ()}</span>
									{if $index >= ($lessontopay-1) && ($userHasPaid == 0 && $coursePrice > 0)}
										<a href="javascript:void(0)"
										   onclick="lessonNoPublish(event)"
										   title="Lección no está disponible o requiere pago"
										   style="background-color: #ccc; width:2em; height:2em;text-align:center; vertical-align:middle;"
										   class="btn pull-right">
											<i class="fa fa-lock" style="color: {$UI_COLORS.TEXT_WHITE};font-size:0.8em;text-align:center; vertical-align:middle;padding-bottom:0.5em;margin:0px;margin-left:-0.1em;"></i>
										</a>
									{else}
										<a href="index.php?module=Courses&action=LessonView&course={$courseId}&record={$courseLesson->getId ()}"
										   title="{$STATUS_TITLE[$courseLesson->getUserLessonStatus()]}"
										   style="background-color: {$STATUS_COLOR[$courseLesson->getUserLessonStatus()]}; width:2em; height:2em;text-align:center; vertical-align:middle;"
										   class="btn pull-right">
											<i class="fa fa-play" style="color: {$UI_COLORS.TEXT_WHITE};font-size:0.8em;text-align:center; vertical-align:middle;padding-bottom:0.5em;margin:0px;margin-left:-0.1em;"></i>
										</a>
									{/if}
								</li>
							{/foreach}

                        </ul>
                    </div>
                    {* Forum *}
                    {if $forumUrl neq NULL}
                    <div class="col-xs-12 col-md-12" style="margin-top: 25px">
                        <div class="course-description well well-sm">
                            <p style="color: rgb(52, 70, 68); font-family: Arial; margin-bottom: 0;">
                                <span style="color: rgb(0, 0, 0); font-size: 15px; font-weight: 700;">Foro para el tema del curso: </span>
                                <a href="{$forumUrl}" target="_blank">{if $forumName neq NULL}{$forumName}{else}Foro General{/if}</a>
                            </p>
                        </div>
                    </div>
                    {/if}
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript" src="https://player.vimeo.com/api/player.js"></script>
    <script type='text/javascript'>
        function lessonNoPublish (event) {
            alert('¡Ups! La lección no esta disponible.');
            event.stopPropagation();
        }
        {literal}
        jQuery(document).ready(function () {
            window.addEventListener('beforeunload', function (event) {
                var data = new FormData();
                data.append('Ajax','true');
                data.append('function','TRACK-COURSE');
                data.append('track_id',{/literal} {$TRACK_COURSE_ID} {literal});
                navigator.sendBeacon ('index.php?module=Courses&action=AjaxCourseUtils', data);
            });
        });

        {/literal}
    </script>
{/strip}