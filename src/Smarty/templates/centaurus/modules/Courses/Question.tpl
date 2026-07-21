{strip}
{if (isset ($QUESTION))}
	{assign var='questionId' value=$QUESTION->getId ()}
	{assign var='questionAnswers' value=$QUESTION->getAnswers ()}
	{assign var='questionStatement' value=$QUESTION->getStatement ()}
	{assign var='questionType' value=$QUESTION->getType ()}
{else}
	{assign var='questionId' value=null}
	{assign var='questionAnswers' value=null}
	{assign var='questionStatement' value=null}
	{assign var='questionType' value=null}
{/if}
<div id="question-{$QUESTION_INDEX}" class="col-xs-6 col-md-6 col-lg-6 question" data-index="{$QUESTION_INDEX}">
	<input type="hidden" name="lessons[{$LESSON_INDEX}][test][questions][{$QUESTION_INDEX}][questionid]" value="{$questionId}" class="question-id" />
	<div class="form-group question-type-group">
		<label for="question-type-{$LESSON_INDEX}-{$QUESTION_INDEX}">Tipo: <span class="required">*</span></label>
		<select id="question-type-{$LESSON_INDEX}-{$QUESTION_INDEX}" name="lessons[{$LESSON_INDEX}][test][questions][{$QUESTION_INDEX}][questiontype]" class="form-control question-type" onchange="CourseUtils.setQuestionType (this);">
			<option value="{CourseTestQuestion::TYPE_SINGLE_CHOICE}"{if ($questionType == CourseTestQuestion::TYPE_SINGLE_CHOICE)} selected="selected"{/if}>Selección simple</option>
			<option value="{CourseTestQuestion::TYPE_MULTIPLE_CHOICE}"{if ($questionType == CourseTestQuestion::TYPE_MULTIPLE_CHOICE)} selected="selected"{/if}>Selección múltiple</option>
		</select>
	</div>
	<div class="form-group question-statement-group">
		<label for="question-statement-{$LESSON_INDEX}-{$QUESTION_INDEX}">Planteamiento: <span class="required">*</span></label>
		<textarea id="question-statement-{$LESSON_INDEX}-{$QUESTION_INDEX}" name="lessons[{$LESSON_INDEX}][test][questions][{$QUESTION_INDEX}][statement]" class="form-control question-statement">{$questionStatement}</textarea>
	</div>
	<div class="form-group question-answers-group">
		<label>Respuestas: <button type="button" class="btn btn-default btn-icon" onclick="CourseUtils.addAnswer (this);"><i class="fa fa-plus"></i></button></label>
		<div class="table-responsive">
			<table class="table">
				<thead>
				<tr>
					<th>Planteamiento</th>
					<th>¿Correcta?</th>
					<th>Acciones</th>
					<th>Fedback</th>
				</tr>
				</thead>
				<tbody class="answers">
{if (!empty ($questionAnswers))}
	{foreach $questionAnswers as $index => $questionAnswer}
		{include file='modules/Courses/Answer.tpl' ANSWER=$questionAnswer LESSON_INDEX=$LESSON_INDEX QUESTION_INDEX=$QUESTION_INDEX ANSWER_INDEX=$index}
	{/foreach}
{else}
	{for $index=0 to 3}
		{include file='modules/Courses/Answer.tpl' ANSWER=null LESSON_INDEX=$LESSON_INDEX QUESTION_INDEX=$QUESTION_INDEX ANSWER_INDEX=$index}
	{/for}
{/if}
				</tbody>
			</table>
		</div>
	</div>
	<div class="text-center">
		<button type="button" class="btn btn-default" onclick="CourseUtils.deleteQuestion (this);"><i class="fa fa-trash-o"></i></button>
	</div>
</div>
{/strip}