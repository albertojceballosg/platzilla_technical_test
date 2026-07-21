{strip}
<div class="row">
	<div class="form-group col-xs-12 col-md-12">
		<label for="taskname">Nombre <span class="required">*</span></label>
		<input type="text" id="taskname" name="taskname" value="{$taskName}" maxlength="50" class="form-control taskname" />
	</div>
</div>
<div class="row">
	<div class="form-group col-xs-12 col-md-12">
		<label for="description">Descripción</label>
		<textarea id="description" name="description" class="form-control">{$taskDescription}</textarea>
	</div>
</div>
<div class="row">
	<div class="form-group col-xs-12 col-md-12">
		<label for="taskname">URL vídeo</label>
		<input type="url" id="taskvideo" name="taskvideo" value="{$taskVideo}" placeholder="https://player.vimeo.com/..." class="form-control" />
	</div>
</div>
<div class="row">
	<div class="form-group col-xs-12 col-md-6">
		<label for="category">Categoría</label>
		<select id="category" name="category" class="form-control">
			<option value=""></option>
{if (!empty ($AVAILABLE_CATEGORIES))}
	{foreach $AVAILABLE_CATEGORIES as $category}
			<option value="{$category.categoryname}"{if ($taskCategory == $category.categoryname)} selected="selected"{/if}>{$category.categoryname}</option>
	{/foreach}
{/if}
		</select>
	</div>
{if (!$IS_INSTANCE)}
	<div class="form-group col-xs-12 col-md-6">
		<label for="scope">Ámbito <span class="required">*</span></label>
		<select id="scope" name="scope" class="form-control scope" onchange="BackgroundTasksUtils.setScope (this);">
			<option value="USER"{if ($taskScope == BackgroundTask::SCOPE_USER)} selected="selected"{/if}>Tarea de módulos de usuario</option>
			<option value="SYSTEM"{if ($taskScope == BackgroundTask::SCOPE_SYSTEM)} selected="selected"{/if}>Tarea del sistema</option>
		</select>
	</div>
{else}
	<input type="hidden" id="scope" name="scope" class="form-control scope" readonly="readonly" value="USER" />
{/if}
	<div class="form-group col-xs-12 col-md-6">
		<label for="taskstatus">Status <span class="required">*</span></label>
		<select id="taskstatus" name="taskstatus" class="form-control taskstatus">
			<option value=""></option>
			{foreach $AVAILABLE_STATUSES as $status}
				<option value="{$status}"{if ($taskStatus == $status)} selected="selected"{/if}>{$MOD[$status]}</option>
			{/foreach}
		</select>
	</div>
</div>
<script type="text/javascript" src="include/ckeditor/ckeditor.js"></script>
{/strip}