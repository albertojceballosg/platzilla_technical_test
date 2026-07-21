{extends file='modules/Courses/Base/ExerciseViewLayOut.tpl'}
{assign var='lastQuestion' value=null}
{assign var='businessType' value=null}
{assign var='createdDate' value=null}

{block name="css"}
    <link type="text/css" href="modules/Courses/Courses.css"/>
    <link type="text/css" href="modules/grid_view/grid-view.css"/>
{/block}
{block name="script"}
    <script type="text/javascript" src="include/js/attachments-utils.js"></script>
    <script type="text/html" id="attachment-template">
            <li class="col-md-3 attachment"
                style="border: 1px solid {$UI_COLORS.BORDER_DARK}; margin-bottom: 3px; position: relative; width: 100%;">
                <button type="button" class="btn btn-close" onclick="AttachmentsUtils.deleteAttachment (this);"
                        style="background-color: transparent; border: 0; bottom: 0; line-height: 1; right: 0; padding: 0 5px 2px 5px; position: absolute; text-transform: uppercase; z-index: 1000;">
                    X
                </button>
                <div class="attachment-container">
                    <span class="attachment-name"></span><span class="attachment-size"></span>
                </div>
                <input type="hidden" class="attachment-data"/>
                <input type="hidden" class="attachment-filename"/>
            </li>
        </script>
{/block}
{block name="link_title"}
    <a href="index.php?module=Courses&action=LessonView&record={$LESSON_ID}&course={$COURSE_ID}">Evaluación y/o
        diagnóstico</a>
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
{* Exercises description *}
{block name="exercise_contenet"}
    <div class="row">
        <h4 class="pull-left" style=" font-weight: bold">Ejercicio práctico:</h4>
        <div class="col-lg-12 col-md-12 col-xs-12 border" style="overflow-x: auto; max-height: 150px;margin-top: 25px">
            <div class="text-justify" style="vert-align: top">
                {str_replace('<br />', "", str_replace('<br>', "", $EXERCISE->getDescription()))}
            </div>
        </div>
    </div>
{/block}
{* Resourcelinks *}
{block name="resource_contenet"}
    {if $EXERCISE->getExercisesResources() neq NULL && $EXERCISE->getExercisesResources()|@count > 0}
        <div class="row">
            <hr style="width: 90%; text-align: center">
            <h4 class="pull-left" style="font-weight: bold">Documentos de soporte para el cursante:</h4>
            <div class="col-lg-12 col-md-12 col-xs-12 border" style="margin-top: 12px">
                {foreach $EXERCISE->getExercisesResources() as $document}
                    {assign var='docType' value=$document->getFileType()}
                    <div class="row" style="margin-bottom: 6px">
                        <div class="col-lg-1 col-md-1 col-xs-1 text-right" style="vertical-align: center">
                            {if 'pdf'|in_array:$docType}
                                <i class="fa {$FILE_ICONS.PDF.class}" style="color: {$FILE_ICONS.PDF.color};font-size:2em"></i>
                            {elseif 'txt'|in_array:$docType}
                                <i class="fa {$FILE_ICONS.TEXT.class}" style="color: {$FILE_ICONS.TEXT.color};font-size:2em"></i>
                            {elseif 'jpg'|in_array:$docType}
                                <i class="fa {$FILE_ICONS.IMAGE.class}" style="color: {$FILE_ICONS.IMAGE.color};font-size:2em"></i>
                            {elseif 'jpeg'|in_array:$docType}
                                <i class="fa {$FILE_ICONS.IMAGE.class}" style="color: {$FILE_ICONS.IMAGE.color};font-size:2em"></i>
                            {elseif 'png'|in_array:$docType}
                                <i class="fa {$FILE_ICONS.IMAGE.class}" style="color: {$FILE_ICONS.IMAGE.color};font-size:2em"></i>
                            {elseif 'doc'|in_array:$docType}
                                <i class="fa {$FILE_ICONS.WORD.class}" style="color: {$FILE_ICONS.WORD.color};font-size:2em"></i>
                            {elseif 'docx'|in_array:$docType}
                                <i class="fa {$FILE_ICONS.WORD.class}" style="color: {$FILE_ICONS.WORD.color};font-size:2em"></i>
                            {elseif 'xls'|in_array:$docType}
                                <i class="fa {$FILE_ICONS.EXCEL.class}" style="color: {$FILE_ICONS.EXCEL.color};font-size:2em"></i>
                            {elseif 'xlsx'|in_array:$docType}
                                <i class="fa {$FILE_ICONS.EXCEL.class}" style="color: {$FILE_ICONS.EXCEL.color};font-size:2em"></i>
                            {else}
                                <i class="fa {$FILE_ICONS.DEFAULT.class}" style="color: {$FILE_ICONS.DEFAULT.color};font-size:2em"></i>
                            {/if}
                        </div>
                        <div class="col-lg-11 col-md-11 col-xs-11" style="vertical-align: center">
                            <a href="index.php?module=Courses&action=DownloadAttachment&record={$document->getId()}&Ajax=true"
                               target="_blank">{$document->getName()}</a>
                        </div>
                    </div>
                {/foreach}
            </div>
        </div>
    {/if}
{/block}
{* Response description *}
{block name="response_contenet"}
    <div class="row">
        <hr style="width: 90%; text-align: center">
        <h4 class="pull-left" style="font-weight: bold">Adjuntar archivo PDF como respuesta al ejercicio. Puede agregar también, otros archivos de soporte:</h4>
        <div class="col-lg-12 col-md-12 col-xs-12 attachments-section" data-entity-id="{$RECORD}"
             data-module-name="{$MODULE}"
             data-element-id="{$RECORD}"
             data-maximum-file-size="{$UPLOAD_MAXSIZE / (1024 * 1024)}" data-modal="0">
            <div class="col-md-12 drop-zone"
                 style="background-color: {$UI_COLORS.BACKGROUND_LIGHT_GRAY}; border: 1px dashed #000000; height: 60px; line-height: 34px;margin-bottom: 1em; position: relative; text-align: center;padding-top: 15px">
                <input type="file" multiple="multiple"
                       onchange="AttachmentsUtils.addEntityAttachment (event || window.event);"
                       style="bottom: 0; cursor: pointer; left: 0; opacity: 0; position: absolute; top: 0; width: 100%;"/>
                <span class="title">Arrastra archivos o clic aquí (Máx {$UPLOAD_MAXSIZE / (1024 * 1024)}
                                MB)</span>
            </div>
            <ul class="col-xs-12 attachments-container"
                style="list-style: none; margin-bottom:0; margin-top: 3px;">
                {if $ENTITY_ATTACHMENTS neq NULL}
                {foreach $ENTITY_ATTACHMENTS as $attachment}
                    <li class="col-xs-11 attachment"
                        style="border: 1px solid {$UI_COLORS.BORDER_DARK}; margin-bottom: 3px; position: relative; width: 100%;"
                        data-attachment-id="{$attachment.attachmentsid}">
                        <button type="button" class="btn btn-close"
                                onclick="AttachmentsUtils.deleteEntityAttachment (this);"
                                style="background-color: transparent; border: 0; bottom: 0; line-height: 1; right: 0; padding: 0 5px 2px 5px; position: absolute; text-transform: uppercase; z-index: 1000;">
                            X
                        </button>
                        <div class="attachment-container">
                            <a href="{$attachment.uri}" title="{$attachment.name}"
                               target="_blank">
                                <span class="attachment-name">{$attachment.name}</span><span
                                        class="attachment-size"> ({number_format ($attachment.size, 2, '.', '')}
                                                KB)</span>
                            </a>
                        </div>
                    </li>
                {/foreach}
                {/if}
            </ul>
        </div>
    </div>
{/block}
{* Response footer *}
{block name="btn_finish"}
    <div class="test-button text-center">
        <a href="index.php?module=Courses&action=CourseView&record={$COURSE_ID}"
           class="btn btn-success"
               title="Terminar ejercicio práctico" style="margin-right: 15px;">Terminar
        </a>
    </div>
{/block}