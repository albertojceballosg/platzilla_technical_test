{strip}
<script type="text/javascript" src="themes/{$THEME}/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="themes/{$THEME}/js/bootstrap-datepicker.es.js"></script>
<script type="text/javascript" src="themes/{$THEME}/js/jquery.maskedinput.min.js"></script>
<div class="form-group col-lg-6" id="td_mindate">
	<label>
		{$MOD.LBL_INITIAL_DATE}
		<font size="1"><em old="(yyyy-mm-dd)">(yyyy-mm-dd)</em></font>
	</label>
	<div class="input-group" style="width: 100%;">
		<div class="input-group-addon">
			<i class="fa fa-calendar" id="jscal_trigger_mindate"></i>
		</div>
		<input name="mindate" tabindex="" id="jscal_field_mindate" class="form-control pull-right" size="11" maxlength="18" readonly="readonly" type="text" placeholder="" />
		<script type="text/javascript">
			jQuery ("#jscal_field_mindate").datepicker ({ format: "yyyy-mm-dd", language: 'es', weekStart: 1 });
		</script>
		<script type="text/javascript" id="massedit_calendar_mindate">
			jQuery ("#jscal_field_mindate").mask ("9999-99-99");
		</script>
	</div>
</div>
<div class="form-group col-lg-6" id="td_maxdate">
	<label>
		{$MOD.LBL_MAXIM_DATE}
		<font size="1"><em old="(yyyy-mm-dd)">(yyyy-mm-dd)</em></font>
	</label>
	<div class="input-group" style="width: 100%;">
		<div class="input-group-addon">
			<i class="fa fa-calendar" id="jscal_trigger_maxdate"></i>
		</div>
		<input name="maxdate" tabindex="" id="jscal_field_maxdate" class="form-control pull-right" size="11" maxlength="18" readonly="readonly" type="text" placeholder="" />
		<script type="text/javascript">
			jQuery ("#jscal_field_maxdate").datepicker ({ format: "yyyy-mm-dd", language: 'es', weekStart: 1 });
		</script>
		<script type="text/javascript" id="massedit_calendar_mindate">
			jQuery ("#jscal_field_maxdate").mask ("9999-99-99");
		</script>
	</div>
</div>
{/strip}