{strip}
<script type="text/javascript">
{literal}
	function validatesFormFields () {
		var eventName = jQuery ('#eventname');
		if (eventName.val () == '') {
			alert ('Campo vacío');
			eventName.focus ();
			return false;
		}
		return true;
	}
{/literal}
</script>
<form method="post" action="index.php" onsubmit="return false;" name="relatedEvent">
	<input type="hidden" name="module" value="{$MODULE}" />
	<input type="hidden" name="fldmodule" value="{$FLD_MODULE}" />
	<input type="hidden" name="action" id="action" value="" />
	<input type="hidden" name="Ajax" value="true" />
	<input type="hidden" name="varid" value="{$VAR_ID}" />
	<div class="md-content">
		<div class="modal-header">
			<h4 class="modal-title" id="labelDiv">{$MOD.LBL_EVENT_TASK_RECORD}</h4>
		</div>
		<div class="modal-body" style="height: 256px;overflow-Y: scroll;">
			<table width="95%" cellspacing="2" cellpadding="2" border="0" class="layerHeadingULine">
				<tr>
					<td width="30%">
						<span style="color: red;">*</span> {$MOD.LBL_EVENT_NAME}
					</td>
					<td width="65%" align="center">
						<input type="text" class="form-control" id="eventname" value="{$EVENT_NAME}" name="eventName" placeholder="" />
					</td>
				</tr>
				<tr>
					<td width="30%">
						{$MOD.LBL_EVENT_DESCRIPTION}
					</td>
					<td width="65%" align="center">
						<textarea class="form-control" rows="3" name="description" id="description" placeholder="">{$EVENT_DESCRIPTION}</textarea>
					</td>
				</tr>
				<tr>
					<td width="30%">
						{$MOD.LBL_EVENT_STATUS}
					</td>
					<td width="65%" align="center">
						<select class="form-control" name="status" id="event_status" title="">
{foreach $EVENT_STATUSES as $status}
							<option value="{$status.value}"{if (isset ($status.selected)) && ($status.selected)} selected="selected"{/if}>{$status.text}</option>
{/foreach}
						</select>
					</td>
				</tr>
				<tr>
					<td width="30%">
						{$MOD.LBL_EVENT_TYPE}
					</td>
					<td width="65%" align="center">
						<select class="form-control" name="eventType" id="event_type" title="">
{foreach $EVENT_TYPES as $type}
							<option value="{$type.value}"{if (isset ($type.selected)) && ($type.selected)} selected="selected"{/if}>{$type.text}</option>
{/foreach}
						</select>
					</td>
				</tr>
				<tr>
					<td width="30%">
						{$MOD.LBL_EVENT_START_TIME}
					</td>
					<td width="65%" class="row">
						<div class="form-group col-xs-4" align="left">
							<input type="hidden" class="form-control" id="workflow_time" value="" name="startTime" />
							<select class="form-control" id="h_workflow_time" name="h_startTime" title="">
{foreach $START_HOURS as $hour}
								<option value="{$hour.value}"{if (isset ($hour.selected)) && ($hour.selected)} selected="selected"{/if}>{$hour.text}</option>
{/foreach}
							</select>
						</div>
						<div class="form-group col-xs-4" align="center">
							<select class="form-control" id="m_workflow_time" name="m_startTime" title="">
{foreach $START_MINUTES as $minute}
								<option value="{$minute.value}"{if (isset ($minute.selected)) && ($minute.selected)} selected="selected"{/if}>{$minute.text}</option>
{/foreach}
							</select>
						</div>
						<div class="form-group col-xs-4" align="right">
							<select class="form-control" id="p_workflow_time" name="p_startTime" title="">
{foreach $START_AMPMS as $ampm}
								<option value="{$ampm.value}"{if (isset ($ampm.selected)) && ($ampm.selected)} selected="selected"{/if}>{$ampm.text}</option>
{/foreach}
							</select>
						</div>
					</td>
				</tr>
				<tr>
					<td width="30%">
						{$MOD.LBL_EVENT_START_DATE}
					</td>
					<td width="65%" class="row">
						<div class="form-group col-xs-2" align="left">
							<input type="text" class="form-control" id="start_days" value="{$START_DAYS}" name="startDays" placeholder="" />
						</div>
						<div class="form-group col-xs-1" align="left">
							{$MOD.LBL_DAYS}
						</div>
						<div class="form-group col-xs-4" align="center">
							<select class="form-control" name="startDirection" title="">
{foreach $START_DIRECTIONS as $direction}
								<option value="{$direction.value}"{if (isset ($direction.selected)) && ($direction.selected)} selected="selected"{/if}>{$direction.text}</option>
{/foreach}
							</select>
						</div>
						<div class="form-group col-xs-5" align="right">
							<select class="form-control" name="startDatefield" title="">
{foreach $START_DATE_FIELDS as $dateField}
								<option value="{$dateField.value}"{if (isset ($dateField.selected)) && ($dateField.selected)} selected="selected"{/if}>{$dateField.text}</option>
{/foreach}
							</select>
						</div>
					</td>
				</tr>
				<tr>
					<td width="30%">
						{$MOD.LBL_EVENT_END_TIME}
					</td>
					<td width="65%" align="center" class="row">
						<div class="form-group col-xs-4" align="left">
							<input type="hidden" class="form-control" id="workflow_time" value="" name="endTime" />
							<select class="form-control" id="h_workflow_time" name="h_endTime" title="">
{foreach $END_HOURS as $hour}
								<option value="{$hour.value}"{if (isset ($hour.selected)) && ($hour.selected)} selected="selected"{/if}>{$hour.text}</option>
{/foreach}
							</select>
						</div>
						<div class="form-group col-xs-4" align="center">
							<select class="form-control" id="m_workflow_time" name="m_endTime" title="">
{foreach $END_MINUTES as $minute}
								<option value="{$minute.value}"{if (isset ($minute.selected)) && ($minute.selected)} selected="selected"{/if}>{$minute.text}</option>
{/foreach}
							</select>
						</div>
						<div class="form-group col-xs-4" align="right">
							<select class="form-control" id="p_workflow_time" name="p_endTime" title="">
{foreach $END_AMPMS as $ampm}
								<option value="{$ampm.value}"{if (isset ($ampm.selected)) && ($ampm.selected)} selected="selected"{/if}>{$ampm.text}</option>
{/foreach}
							</select>
						</div>
					</td>
				</tr>
				<tr>
					<td width="30%">
						{$MOD.LBL_EVENT_END_DATE}
					</td>
					<td width="65%" class="row" align="left">
						<div class="form-group col-xs-2">
							<input type="text" class="form-control" id="end_days" value="{$END_DAYS}" name="endDays" placeholder="" />
						</div>
						<div class="form-group col-xs-1" align="left">
							{$MOD.LBL_DAYS}
						</div>
						<div class="form-group col-xs-4" align="center">
							<select class="form-control" name="endDirection" title="">
{foreach $END_DIRECTIONS as $direction}
								<option value="{$direction.value}"{if (isset ($direction.selected)) && ($direction.selected)} selected="selected"{/if}>{$direction.text}</option>
{/foreach}
							</select>
						</div>
						<div class="form-group col-xs-5" align="right">
							<select class="form-control" name="endDatefield" title="">
{foreach $END_DATE_FIELDS as $dateField}
								<option value="{$dateField.value}"{if (isset ($dateField.selected)) && ($dateField.selected)} selected="selected"{/if}>{$dateField.text}</option>
{/foreach}
							</select>
						</div>
					</td>
				</tr>
			</table>
		</div>
		<div class="modal-footer">
			<button class="btn btn-primary" onclick="if (validatesFormFields ()) {ldelim} irPaso (document.relatedEvent,'recordRelatedEvent'); {rdelim}">{$MOD.LBL_CREATE_EDIT}</button>
		</div>
	</div>
</form>
{/strip}