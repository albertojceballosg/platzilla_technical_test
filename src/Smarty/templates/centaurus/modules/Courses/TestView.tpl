{strip}
	{*$TEST|var_dump*}
{assign var='lessonId' value=$TEST->getLessonId ()}
{assign var='testDescription' value=$TEST->getDescription ()}
{assign var='testMinimumScore' value=$TEST->getMinimumScore ()}
{assign var='testQuestions' value=$TEST->getQuestions ()}
{assign var='testTotalQuestions' value=$TEST->getTotalQuestionsPerTest ()}
<link type="text/css" href="modules/Courses/Courses.css" />
<div class="row">
	<div class="col-xs-12">
		<h1 class="pull-left"><a href="index.php?module=Courses&action=LessonView&record={$lessonId}&course={$COURSE_ID}">Evaluación y/o Diagnóstico</a></h1>
		<div class="pull-right">
		<div class="btn-group">
		<a href="index.php?module=Courses&action=LessonView&record={$lessonId}&course={$COURSE_ID}" class="btn btn-info" title="ir a la Lección" style="margin-right: 25px;">
			<i class="fa fa-backward" aria-hidden="true"></i>
		</a>
		<a href="index.php?module=Courses&action=CourseView&record={$COURSE_ID}" class="btn btn-info" title="lecciones" style="margin-right: 15px;">
			<i class="fa fa-list-ul" aria-hidden="true"></i>
		</a>
		</div>
		</div>
	</div>
</div>
{if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
<div class="row">
	<div class="alert alert-{if (isset ($IS_ERROR)) && ($IS_ERROR)}danger{else}success{/if}">
		<strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
	</div>
</div>
{/if}
<form method="post" action="index.php" onsubmit="return confirm ('Estás enviando tu evaluación y/o diagnóstico en este momento. ¿Estás seguro?');">
	<input type="hidden" name="module" value="Courses" />
	<input type="hidden" name="action" value="EvaluateTestAnswers" />
	<input type="hidden" name="record" value="{$lessonId}" />
	<input type="hidden" name="course" value="{$COURSE_ID}" />
	{*<input type="hidden" name="Ajax" value="true" />*}
	<div class="main-box no-header">
		<div class="main-box-body">
			<div class="row">
				<div class="col-xs-12 col-md-9">
					<h4>{$testDescription}</h4>
				</div>
				<div class="col-xs-12 col-md-3">
					<h5>Total de preguntas: {$testTotalQuestions}</h5>
					{* EB - 20200908 se deja comentado esta linea para que no se reflejen, dado que los cursos no tendrán nota de examen de acuerdo con lo indicado por Golfredo *}
					{* <h5>Total preguntas correctas para aprobar: {round($testTotalQuestions * $testMinimumScore / 100)}</h5> *}
				</div>
			</div>
{for $testQuestionIndex=0 to ($testTotalQuestions - 1)}
	{assign var='testQuestion' value=$testQuestions[$testQuestionIndex]}
			<div class="row test-question">
				<input type="hidden" name="questions[{$testQuestionIndex}][id]" value="{$testQuestion->getId ()}" />
				<div class="col-xs-12 question">
					<h5>{$testQuestionIndex + 1}. {$testQuestion->getStatement ()}</h5>
	{assign var='testQuestionType' value=$testQuestion->getType ()}
	{assign var='testAnswers' value=$testQuestion->getAnswers ()}
	{foreach $testAnswers as $testAnswerIndex => $testAnswer}
					<div class="checkbox-nice test-answer">
		{if ($testQuestionType == CourseTestQuestion::TYPE_MULTIPLE_CHOICE)}
						<input type="checkbox" id="answer-{$testQuestionIndex}-{$testAnswerIndex}" name="questions[{$testQuestionIndex}][answers][]" value="{$testAnswer->getId ()}" />
		{else}
						<input type="radio" id="answer-{$testQuestionIndex}-{$testAnswerIndex}" name="questions[{$testQuestionIndex}][answers][]" value="{$testAnswer->getId ()}" />
		{/if}
						<label for="answer-{$testQuestionIndex}-{$testAnswerIndex}">{$testAnswer->getStatement ()}</label>
					</div>
	{/foreach}
				</div>
			</div>
{/for}
			<div class="test-button text-center">
				<button type="submit" class="btn btn-success">Terminar</button>
			</div>
		</div>
	</div>
</form>
{/strip}