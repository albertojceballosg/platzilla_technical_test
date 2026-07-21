{strip}
<script type="text/javascript">
{literal}
	function onlyNumbers (evt) {
		evt = (evt) ? evt : window.event;
		var charCode = (evt.which) ? evt.which : evt.keyCode;
		return !(charCode > 31 && (charCode < 48 || charCode > 57));
	}
{/literal}
</script>
<div class="form-group col-lg-6" id="td_minvalue">
	<label for="minvalue">{$MOD.LBL_INITIAL_VALUE}</label>
	<div class="input-group" style="width: 100%;">
		<input name="minvalue" id="minvalue" type="text" class="form-control pull-right"  size="5" maxlength="56" onKeyPress="return onlyNumbers (event)" value="" />
	</div>
</div>
<div class="form-group col-lg-6" id="td_maxvalue">
	<label for="maxvalue">{$MOD.LBL_MAXIMUM_VALUE}</label>
	<div class="input-group" style="width: 100%;">
		<input name="maxvalue" id="maxvalue" type="text" class="form-control pull-right"  size="5" maxlength="56" onKeyPress="return onlyNumbers (event)" value="" />
	</div>
</div>
<div class="form-group col-lg-6" id="td_negativenumbre">
	<label for="negativenumbers">{$MOD.LBL_NEGATIVE_NUMBER}</label>
	<input name="negativenumbers" id="negativenumbers" type="checkbox" />
</div>
{/strip}