{strip}
    {assign var='colors' value=array('yellow', 'green', 'blue', 'red')}
    {assign var='audiences' value=array('Emprendedor', 'Microempresario', 'Miembro de una PYME en crecimiento', 'Miembro de una empresa grande')}
    <link type="text/css" href="modules/Courses/Courses.css"/>
    <div class="container">
        <div class="row title-content">
            <div class="col-xs-12">
                <h1 class="pull-left"><strong>Cursos</strong></h1>
                {if (!$IS_INSTANCE)}
                    <div class="action-bar pull-right">
                        <a href="index.php?module=Courses&action=EditView" class="btn btn-primary">Crear curso</a>
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
        <div class="main-box no-header clearfix">
            <div class="main-box-body clearfix">
                {foreach $audiences as $index => $audience}
                    {assign var='color' value=$colors[($index % 4)]}
                    <div class="col-xs-12 category">
                        <div class="col-xs-12 category-label border-{$color}">
                            <span class="label bg-{$color}">{$audience}</span>
                        </div>
                        <div class="col-xs-12 course-portraits">
                            {if (!empty ($COURSES[$audience]))}
                                {foreach $COURSES[$audience] as $course}
                                    {assign var='courseId' value=$course->getId ()}
                                    {assign var='courseCategoryId' value=$course->getCategoryId ()}
                                    {assign var='courseCategory' value=$course->getCategory ()}
                                    {assign var='courseLevel' value=$course->getLevel ()}
                                    {assign var='courseName' value=$course->getName ()}
                                    {assign var='coursePrice' value=$course->getPrice ()}
                                    {assign var='courseStatus' value=$course->getStatus ()}
                                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 course-portrait">
                                        <a href="index.php?module=Courses&action=CourseView&record={$courseId}">
                                            <figure class="embed-responsive embed-responsive-16by9">
                                                <img src="themes/images/aprobado.png" class="picture"/>
                                            </figure>
                                            <p class="course-name text-center">{$courseName}</p>
                                            <p class="course-category text-center">{$courseCategory->getName()}</p>
                                            <p>
                                                <span title="{if ($courseLevel == Course::LEVEL_BEGINNER)}Principiante{elseif ($courseLevel == Course::LEVEL_INTERMEDIATE)}Intermedio{elseif ($courseLevel == Course::LEVEL_ADVANCED)}Avanzado{/if}">Nivel: <i
                                                            class="fa fa-star"></i><i
                                                            class="fa fa-star{if (!in_array ($courseLevel, array (Course::LEVEL_INTERMEDIATE, Course::LEVEL_ADVANCED)))}-o{/if}"></i><i
                                                            class="fa fa-star{if ($courseLevel != Course::LEVEL_ADVANCED)}-o{/if}"></i></span>
                                                <span class="pull-right">{if ($coursePrice > 0)}{number_format($coursePrice, 2, ',', '.')} EUR{else}Gratis{/if}</span>
                                            </p>
                                        </a>
                                        {if (!$IS_INSTANCE)}
                                            <p class="pull-left">
                                                Status: {if ($courseStatus == Course::STATUS_ACTIVE)}Activo{elseif ($courseStatus == Course::STATUS_INACTIVE)}Inactivo{/if}</p>
                                            <div class="action-bar pull-right">
                                                <a href="index.php?module=Courses&action=EditView&record={$courseId}"
                                                   class="btn btn-primary"><i class="fa fa-pencil"></i></a>
                                                <form action="index.php" method="post" style="display: inline-block;"
                                                      onsubmit="return confirm ('Esta acción eliminará el curso seleccionado. ¿Estás seguro?');">
                                                    <input type="hidden" name="module" value="Courses"/>
                                                    <input type="hidden" name="action" value="DeleteCourse"/>
                                                    <input type="hidden" name="record" value="{$courseId}"/>
                                                    <button type="submit" class="btn btn-danger"><i
                                                                class="fa fa-trash-o"></i></button>
                                                </form>
                                            </div>
                                        {/if}
                                        {if ($coursePrice > 0) ||($paidLesson > 0)}
                                            <div class="action-bar pull-right">
                                                <form action="index.php" method="post" style="display: inline-block;">
                                                    <input type="hidden" name="module" value="Courses"/>
                                                    <input type="hidden" name="remodule" value="Courses"/>
                                                    <input type="hidden" name="reaction" value="action=index"/>
                                                    <input type="hidden" name="action" value="AddPaymentCourse"/>
                                                    <input type="hidden" name="record" value="{$courseId}"/>
                                                    <button title="Realizar pago del curso" type="submit"
                                                            class="btn btn-primary btn-xs"><i
                                                                class="fa fa-shopping-cart"></i></button>
                                                </form>
                                            </div>
                                        {/if}
                                    </div>
                                {/foreach}
                            {else}
                                <div class="col-xs-12 text-center course">No hay cursos en esta categoría</div>
                            {/if}
                        </div>
                    </div>
                {/foreach}
            </div>
        </div>
    </div>
{/strip}