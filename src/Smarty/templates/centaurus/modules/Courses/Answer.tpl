{strip}
{if (isset ($ANSWER))}
	{assign var='answerId' value=$ANSWER->getId ()}
	{assign var='answerCorrect' value=$ANSWER->isCorrect ()}
	{assign var='answerFeedBack' value=$ANSWER->getFeedback ()}
	{assign var='answerStatement' value=$ANSWER->getStatement ()}
{else}
	{assign var='answerId' value=null}
	{assign var='answerCorrect' value=null}
	{assign var='answerFeedBack' value=null}
	{assign var='answerStatement' value=null}
{/if}
<tr class="answer" data-index="{$ANSWER_INDEX}">
	<td>
		<input type="hidden" name="lessons[{$LESSON_INDEX}][test][questions][{$QUESTION_INDEX}][answers][{$ANSWER_INDEX}][answerid]" value="{$answerId}" class="answer-id" />
		<textarea name="lessons[{$LESSON_INDEX}][test][questions][{$QUESTION_INDEX}][answers][{$ANSWER_INDEX}][statement]" class="form-control answer-statement" placeholder="Respuesta">{$answerStatement}</textarea>
	</td>
	<td>
		<input type="checkbox" name="lessons[{$LESSON_INDEX}][test][questions][{$QUESTION_INDEX}][answers][{$ANSWER_INDEX}][correct]" value="1" class="form-control answer-correct" placeholder=""{if ($answerCorrect)} checked="checked"{/if} onclick="CourseUtils.setAnswerCorrect (this);" />
	</td>
	<td>
		<button type="button" class="btn btn-default" onclick="CourseUtils.deleteAnswer (this);"><i class="fa fa-trash-o"></i></button>
	</td>
	<td>
		<textarea name="lessons[{$LESSON_INDEX}][test][questions][{$QUESTION_INDEX}][answers][{$ANSWER_INDEX}][feedback]" class="form-control answer-feedback" placeholder="Feedback">{$answerFeedBack}</textarea>
	</td>
</tr>
{/strip}