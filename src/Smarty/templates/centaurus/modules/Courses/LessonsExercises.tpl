{math equation= rand() assign= "idExercise"}
<div class="col-xs-12 col-md-12 col-lg-12" style="margin-top: 15px">
    <input type="hidden" name="lessons[{$INDEX}][exercises_id]" value="{$exerciseId}" class="" />
    <h2>Ejercicio práctico</h2>
    <div class="col-xs-8 col-md-8 col-lg-8">
        <div class="form-group exercise-name-group">
            <label for="exercise-name-{$INDEX}">Nombre:</label>
            <input type="text" id="exercise-name-{$INDEX}" name="lessons[{$INDEX}][exercise_name]"
                   value="{$exerciseName}" class="form-control exercise-name" maxlength="255"/>
        </div>
        <div class="form-group exercise-description-group">
            <label for="exercise-description-{$INDEX}">Descripción  para el cursante:</label>
            <textarea id="exercise-description-{$INDEX}" name="lessons[{$INDEX}][exercis_description]"
                      class="form-control exercise-description">{$exerciseDescription}</textarea>
        </div>
    </div>
    <div class="col-xs-4 col-md-4 col-lg-4">
        <div class="form-group exercise-has-questions-group">
            <label for="exercise-has-test-{$INDEX}">¿Ejercicio con evaluación?</label>
            <select id="exercise-has-test-{$INDEX}"
                    name="lessons[{$INDEX}][exercis_hastest]"
                    class="form-control exercise-has-test">
                <option value="1" {if $exerciseTest eq '1'} selected="selected"{/if} >Si</option>
                <option value="0" {if $exerciseTest eq '0'} selected="selected"{/if} >No</option>
            </select>
        </div>
        <div class="form-group exercise-minimum-score-group">
            <label for="exercise-minimum-score-{$INDEX}">Puntuación aprobatoria (%): <span class="required">*</span></label>
            <input type="number" id="exercise-minimum-score-{$INDEX}"
                   name="lessons[{$INDEX}][minimum_score]" value="{$minimumScore}"
                   class="form-control exercise-minimum-score" min="0" max="100" step="1"/>
        </div>
        <div class="form-group resources-group">
            <label>Recursos:
                <button type="button" class="btn btn-default btn-icon"
                        onclick="CourseUtils.addResource (this, 'YES');"><i class="fa fa-plus"></i></button>
            </label>
            <ul class="resources" data-maximum-file-size="{$UPLOAD_MAXSIZE / (1024 * 1024)}">
                {if (!empty ($lessonResources))}
                    {foreach $lessonResources as $index => $lessonResource}
                        {if $lessonResource->getHasExercise () eq 'NO'}{continue}{/if}
                        {include file='modules/Courses/Resource.tpl' RESOURCE=$lessonResource LESSON_INDEX=$INDEX RESOURCE_INDEX=$index}
                    {/foreach}
                {/if}
            </ul>
        </div>
    </div>
</div>
