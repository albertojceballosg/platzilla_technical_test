{strip}
{if (isset ($RESOURCE))}
	{assign var='resourceId' value=$RESOURCE->getId ()}
	{assign var='resourceFileContents' value=$RESOURCE->getFileContents ()}
	{assign var='resourceName' value=$RESOURCE->getName ()}
	{assign var='resourceType' value=$RESOURCE->getType ()}
	{assign var='resourceUrl' value=$RESOURCE->getUrl ()}
	{assign var='exerciseId' value=$RESOURCE->getExerciseId ()}
	{assign var='HAS_EXERCISES' value=$RESOURCE->getHasExercise()}
{else}
	{assign var='resourceId' value=null}
	{assign var='resourceFileContents' value=null}
	{assign var='resourceName' value=null}
	{assign var='resourceType' value=null}
	{assign var='resourceUrl' value=null}
	{assign var='exerciseId' value=null}
	{assign var='exerciseId' value=null}
	{assign var='HAS_EXERCISES' value='NO'}
{/if}
<li class="resource" data-index="{$RESOURCE_INDEX}">
	<input type="hidden" name="lessons[{$LESSON_INDEX}][resources][{$RESOURCE_INDEX}][resourceid]" value="{$resourceId}" class="resource-id" />
	<input type="hidden" name="lessons[{$LESSON_INDEX}][resources][{$RESOURCE_INDEX}][exerciseid]" value="{$exerciseId}" class="exercise-id" />
	<input type="hidden" name="lessons[{$LESSON_INDEX}][resources][{$RESOURCE_INDEX}][has_exercise]" value="{$HAS_EXERCISES}" class="has_exercise-id"/>
	<div class="row">
		<div class="form-group col-xs-12 col-md-5">
			<input type="text" name="lessons[{$LESSON_INDEX}][resources][{$RESOURCE_INDEX}][resourcename]" value="{$resourceName}" class="form-control resource-name" placeholder="Nombre" />
		</div>
		<div class="form-group col-xs-12 col-md-2">
{if (empty ($resourceId))}
			<select name="lessons[{$LESSON_INDEX}][resources][{$RESOURCE_INDEX}][resourcetype]" class="form-control resource-type" onchange="CourseUtils.setResourceType (this);" title="Tipo">
				<option value="{CourseResource::TYPE_ATTACHMENT}"{if ($resourceType == CourseResource::TYPE_ATTACHMENT)} selected="selected"{/if}>Anexo</option>
				<option value="{CourseResource::TYPE_URL}"{if ($resourceType == CourseResource::TYPE_URL)} selected="selected"{/if}>URL</option>
			</select>
{else}
			<input type="hidden" name="lessons[{$LESSON_INDEX}][resources][{$RESOURCE_INDEX}][resourcetype]" value="{$resourceType}" class="resource-type" />
			<input type="text" value="{if ($resourceType == CourseResource::TYPE_ATTACHMENT)}Anexo{elseif ($resourceType == CourseResource::TYPE_URL)}URL{/if}" class="form-control" placeholder="" disabled="disabled" />
{/if}
		</div>
		<div class="form-group col-xs-12 col-md-4">
			<input type="text" name="lessons[{$LESSON_INDEX}][resources][{$RESOURCE_INDEX}][url]" value="{if ($resourceType == CourseResource::TYPE_URL)}{$resourceUrl}{/if}" class="form-control resource-url" placeholder="URL"{if (empty ($resourceType)) || ($resourceType != CourseResource::TYPE_URL)} style="display: none;" disabled="disabled"{/if} />
			<div class="drop-zone"{if (!empty ($resourceType)) && ($resourceType != CourseResource::TYPE_ATTACHMENT)} style="display: none;" disabled="disabled"{/if}>
{if (empty ($resourceId))}
				<input type="hidden" name="lessons[{$LESSON_INDEX}][resources][{$RESOURCE_INDEX}][filedata]" value="{$resourceFileContents}" class="resource-data" />
				<input type="file" multiple="multiple" onchange="CourseUtils.addAttachment (event || window.event);" data-maximum-file-size="{$UPLOAD_MAXSIZE / (1024 * 1024)}" />
				<input type="text" class="form-control title" placeholder="{if (!empty ($resourceName))}{$resourceName}{else}Pincha aquí (Máx {$UPLOAD_MAXSIZE / (1024 * 1024)} MB){/if}" readonly="readonly">
{else}
				<a href="index.php?module=Courses&action=DownloadAttachment&record={$resourceId}&Ajax=true" class="attachment-link" target="_blank">Descargar</a>
{/if}
			</div>
		</div>
		<div class="form-group col-xs-12 col-md-1">
			<button type="button" class="btn btn-default" onclick="CourseUtils.deleteResource (this);"><i class="fa fa-trash-o"></i></button>
		</div>
	</div>
</li>
{/strip}