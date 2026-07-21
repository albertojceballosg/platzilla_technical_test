{strip}
<li{if ($INDEX === 0)} class="active"{/if}>
	<a data-toggle="tab" href="#lesson-{$INDEX}" class="tab-link">{$INDEX + 1}</a>
	<button type="button" class="btn" onclick="CourseUtils.deleteLesson (this);"><i class="fa fa-trash-o"></i></button>
</li>
{/strip}