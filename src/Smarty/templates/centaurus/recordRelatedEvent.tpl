	<form method="post" action="index.php" onsubmit="return false;" name="relatedEvent">
	<input type="hidden" name="module" value="{$MODULE}" />
	<input type="hidden" name="fldmodule" value="{$_FLD_MODULE}" />
	<input type="hidden" name="action" id="action" value="" />
	<input type="hidden" name="Ajax" value="true" />
	<input type="hidden" name="varid" value="{$VAR_ID}" />
	<table width="100%" cellspacing="0" cellpadding="5" border="0" class="tableHeading">
		<tbody><tr>
			<td nowrap="nowrap" class="big">
				<strong>{$MOD.LBL_EVENT_TASK_RECORD}</strong>
			</td>
		</tr>
		</tbody>
	</table>
		
	<table width="100%" cellspacing="0" cellpadding="5" border="0">
	<tbody><tr valign="top">
		<td width="15%" align="right" nowrap="nowrap"><b><font color="red">*</font>{$MOD.LBL_EVENT_NAME}</b></td>
		<td class="dvtCellInfo"><input type="text" class="form_input" id="workflow_eventname" value="{$EVENT_NAME}" name="eventName"></td>
	</tr>
	<tr valign="top">
		<td width="15%" align="right" nowrap="nowrap" class="dvtCellLabel"><b>{$MOD.LBL_EVENT_DESCRIPTION}</b></td>
		<td class="dvtCellInfo"><textarea class="detailedViewTextBox" cols="40" rows="5" name="description">{$EVENT_DESCRIPTION}</textarea></td>
	</tr>
	<tr valign="top">
		<td width="15%" align="right" nowrap="nowrap" class="dvtCellLabel"><b>{$MOD.LBL_EVENT_STATUS}</b></td>
		<td class="dvtCellInfo">
			<select style="" class="small" name="status" value="" id="event_status">
				{$EVENT_STATUS}
			</select>
		</td>
	</tr> 
	<tr valign="top">
		<td width="15%" align="right" nowrap="nowrap" class="dvtCellLabel"><b>{$MOD.LBL_EVENT_TYPE}</b></td>
		<td class="dvtCellInfo">
			<select style="" class="small" name="eventType" value="" id="event_type">
				{$EVENT_TYPE}
			</select>
		</td>
	</tr>
	<tr>
		<td align="right"><b>{$MOD.LBL_EVENT_START_TIME}</b></td>
		<td><input type="hidden" class="time_field" style="width:60px" id="workflow_time" value="" name="startTime">
			<select class="small" id="h_workflow_time" name="h_startTime">
				{$STARTTIME}
			</select>
			<select class="small" id="m_workflow_time" name="m_startTime">
				{$STARTMINTIME}
			</select>
			<select class="small" id="p_workflow_time" name="p_startTime" >
				{$STARTPTIME}
			</select>
			</td>
	</tr>
	<tr>
		<td align="right"><b>{$MOD.LBL_EVENT_START_DATE}</b></td>
		<td>
			<input type="text" class="small" style="width:30px" id="start_days" value="{$STARTDAYS}" name="startDays"> {$MOD.LBL_DAYS} 
			<select class="small" value="" name="startDirection">
				{$STARTDIRECTION}
				
			</select>
			<select class="small" value="" name="startDatefield">
				{$STARTDATE_FIELDS}
			</select>
		</td>
	</tr>
	<tr>
		<td align="right"><b>{$MOD.LBL_EVENT_END_TIME}</b></td>
		<td><input type="hidden" class="time_field" style="width:60px" id="workflow_time" value="" name="endTime">
			<select class="small" id="h_workflow_time" name="h_endTime">
				{$ENDTIME}
			</select>
			<select class="small" id="m_workflow_time" name="m_endTime">
				{$ENDMINTIME}
			</select>
			<select class="small" id="p_workflow_time" name="p_endTime">
				{$ENDPTIME}
			</select>
			</td>
	</tr>
	<tr>
		<td align="right"><b>{$MOD.LBL_EVENT_END_DATE}</b></td>
		<td>
			<input type="text" class="small" style="width:30px" id="end_days" value="{$ENDDAYS}" name="endDays"> {$MOD.LBL_DAYS} 
			<select class="small" value="" name="endDirection">
				{$ENDDIRECTION}
			</select>
			<select class="small" value="" name="endDatefield">
				{$ENDDATE_FIELDS}
			</select>
		</td>
	</tr>
	</tbody>
	</table>
	<button class="btn btn-primary" onclick="irPaso(document.relatedEvent,'recordRelatedEvent');">{$MOD.LBL_SIGUIENTE}</button>
	</form>