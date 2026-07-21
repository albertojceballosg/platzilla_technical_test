{strip}
<style type="text/css">
	label {
		font-size: 1em;
	}
</style>
<div class="row">
	<div class="col-xs-12">
		<h1 class="pull-left"><a href="index.php?module={$MODULE}&action=ListView&parenttab={$CATEGORY}">Tarea</a></h1>
		<div class="pull-right">
			<form action="index.php" method="post" name="DetailView" id="form" onsubmit="VtigerJS_DialogBox.block();">
{include file='DetailViewHidden.tpl'}
{if ($EDIT_DUPLICATE == 'permitted')}
				<a href="index.php?module=Calendar&action=EditView&record={$ID}&return_module=Calendar&return_action=DetailView&return_id={$ID}" accessKey="{$APP.LBL_EDIT_BUTTON_KEY}" class="btn btn-primary">{$APP.LBL_EDIT_BUTTON_LABEL}</a>&nbsp;
{/if}
{if ($EDIT_DUPLICATE == 'permitted')}
				<input title="{$APP.LBL_DUPLICATE_BUTTON_TITLE}" accessKey="{$APP.LBL_DUPLICATE_BUTTON_KEY}" class="btn btn-info" onclick="DetailView.return_module.value='{$MODULE}'; DetailView.return_action.value='DetailView'; DetailView.isDuplicate.value='true';DetailView.module.value='{$MODULE}'; submitFormForAction('DetailView','EditView');" type="button" name="Duplicate" value="{$APP.LBL_DUPLICATE_BUTTON_LABEL}" />&nbsp;
{/if}
{if ($DELETE == 'permitted')}
				<input title="{$APP.LBL_DELETE_BUTTON_TITLE}" accessKey="{$APP.LBL_DELETE_BUTTON_KEY}" class="btn btn-danger" onclick="DetailView.return_module.value='{$MODULE}'; {if $VIEWTYPE eq 'calendar'} DetailView.return_action.value='index'; {else} DetailView.return_action.value='ListView'; {/if}  submitFormForActionWithConfirmation('DetailView', 'Delete', '{$APP.NTC_DELETE_CONFIRMATION}');" type="button" name="Delete" value="{$APP.LBL_DELETE_BUTTON_LABEL}" />&nbsp;
{/if}
			</form>
		</div>
	</div>
</div>
{if (isset ($MESSAGE))}
<div class="alert alert-{if (!$IS_ERROR)}success{else}danger{/if}">
	<i class="fa fa-{if (!$IS_ERROR)}check{else}times{/if}-circle fa-fw fa-lg"></i>
	<strong>{if (!$IS_ERROR)}Listo{else}Error{/if}!</strong> {$MESSAGE}
</div>
{/if}
<div class="main-box clearfix">
	<header class="main-box-header clearfix"><h2 class="pull-left">{$header}</h2></header>
	<div class="main-box-body clearfix">
		<div class="row">
			<div class="form-group col-md-6">
				<label for="activitytype">{$MOD.LBL_EVENTTYPE}</label>
				<input type="text" id="activitytype" class="form-control" value="{$MOD[$ACTIVITYDATA.activitytype]}" readonly="readonly" />
			</div>
			<div class="form-group col-md-6">
				<label for="subject">{$MOD.LBL_EVENTNAME}</label>
				<input type="text" id="subject" class="form-control" value="{$ACTIVITYDATA.subject}" readonly="readonly" />
			</div>
		</div>
		<div class="row">
			<div class="form-group col-md-6">
				<label for="description">{$MOD.LBL_APP_DESCRIPTION}</label>
				<textarea id="description" class="form-control" readonly="readonly">{$ACTIVITYDATA.description}</textarea>
			</div>
			<div class="form-group col-md-6">
				<label for="progress">{$MOD.LBL_PROGRESS}: <span id="progress-display">{intval($ACTIVITYDATA.progress)}</span> %</label>
				<div class="slidecontainer">
					<input type="range" id="progress" name="progress" value="{intval($ACTIVITYDATA.progress)}" class="slider" min="1" max="100" disabled="disabled" />
				</div>
			</div>
		</div>
		<div class="row">
			<div class="form-group col-md-6">
				<label for="location">{$MOD.LBL_APP_LOCATION}</label>
				<input type="text" id="location" class="form-control" value="{$ACTIVITYDATA.location}" readonly="readonly" />
			</div>
			<div class="form-group col-md-6">
				<label for="eventstatus">{$MOD.LBL_LIST_STATUS}</label>
				<input type="text" id="eventstatus" class="form-control" value="{$MOD[$ACTIVITYDATA.eventstatus]}" readonly="readonly" />
			</div>
		</div>
		<div class="row">
			<div class="form-group col-md-6">
				<label for="assigned_user_id" style="margin-right: 0.5em;">{$MOD['Assigned To']}</label>
				<input type="text" id="assigned_user_id" class="form-control" value="{$ACTIVITYDATA.assigned_user_id}" readonly="readonly" />
			</div>
			<div class="form-group col-md-6">
				<label for="taskpriority">{$MOD['Priority']}</label>
				<input type="text" id="taskpriority" class="form-control" value="{$ACTIVITYDATA.taskpriority}" readonly="readonly" />
			</div>
		</div>
		<div class="row">
			<div class="form-group col-md-6">
				<label for="startdate">{$MOD.LBL_EVENTSTAT}</label>
				<div class="input-group">
					<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
					<input type="text" id="startdate" class="form-control" value="{$ACTIVITYDATA.date_start} {$ACTIVITYDATA.time_start}" readonly="readonly" />
				</div>
			</div>
			<div class="form-group col-lg-6">
				<label for="enddate">{$MOD.LBL_EVENTEDAT}</label>
				<div class="input-group">
					<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
					<input type="text" id="enddate" class="form-control" value="{$ACTIVITYDATA.due_date} {$ACTIVITYDATA.time_end}" readonly="readonly" />
				</div>
			</div>
		</div>
{if (!empty ($INVITEDUSERS))}
		<div class="row">
			<div class="form-group col-lg-6">
				<label for="selectedusers">Usuarios invitados</label>
				<textarea id="selectedusers" class="form-control" readonly="readonly">{join('\n', $INVITEDUSERS)}</textarea>
			</div>
		</div>
{/if}
	</div>
</div>
{if (!empty ($RELATED))}
<div class="row">
	<div class="col-xs-12">
		<div class="main-box clearfix">
			<header class="main-box-header clearfix"><h2 class="pull-left">{$MOD.LBL_LIST_RELATED_TO}</h2></header>
			<div class="main-box-body clearfix">
				<div class="table-responsive">
					<table class="table">
						<thead>
						<tr>
							<th>{$MOD.LBL_NAME_ENTITY}</th>
							<th>{$MOD.LBL_ENTITY}</th>
						</tr>
						</thead>
						<tbody>
	{foreach item=rel from=$RELATED}
						<tr>
							<td width="30%" align=left>{$rel.name}</td>
							<td width="30%" align=left>{$rel.label_entity}</td>
						</tr>
	{/foreach}
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
{/if}
{/strip}