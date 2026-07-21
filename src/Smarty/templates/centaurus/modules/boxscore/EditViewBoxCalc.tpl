{strip}
<link href="{$TEMPLATE_PATH}/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
<link href="{$TEMPLATE_PATH}/css/ionicons.min.css" rel="stylesheet" type="text/css" />
<link href="{$TEMPLATE_PATH}/css/morris/morris.css" rel="stylesheet" type="text/css" />
<link href="{$TEMPLATE_PATH}/css/jvectormap/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />
<link href="{$TEMPLATE_PATH}/css/fullcalendar/fullcalendar.css" rel="stylesheet" type="text/css" />
<link href="{$TEMPLATE_PATH}/css/daterangepicker/daterangepicker-bs3.css" rel="stylesheet" type="text/css" />
<link href="{$TEMPLATE_PATH}/css/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css" rel="stylesheet" type="text/css" />
<link href="{$TEMPLATE_PATH}/css/AdminLTE.css" rel="stylesheet" type="text/css" />
<link href="{$TEMPLATE_PATH}/css/iCheck/all.css" rel="stylesheet" type="text/css" />
<link href="{$TEMPLATE_PATH}/css/colorpicker/bootstrap-colorpicker.min.css" rel="stylesheet" />
<link href="{$TEMPLATE_PATH}/css/timepicker/bootstrap-timepicker.min.css" rel="stylesheet" />
<script src="{$TEMPLATE_PATH}/js/bootstrap.min.js" type="text/javascript"></script>
<script src="{$TEMPLATE_PATH}/js/plugins/input-mask/jquery.inputmask.js" type="text/javascript"></script>
<script src="{$TEMPLATE_PATH}/js/plugins/input-mask/jquery.inputmask.date.extensions.js" type="text/javascript"></script>
<script src="{$TEMPLATE_PATH}/js/plugins/input-mask/jquery.inputmask.extensions.js" type="text/javascript"></script>
<script src="{$TEMPLATE_PATH}/js/plugins/daterangepicker/daterangepicker.js" type="text/javascript"></script>
<style type="text/css">
	th {
		text-align: center;
	}
</style>
<script type="text/javascript">
{literal}
	jQuery (document).ready (function () {
		var count = {/literal}{if ($MODE == 'edit') && (count ($CALCULATION[0]['boxscore_data_id']) > 2)}{$CALCULATION[0]['boxscore_data_id']|@count}{else}0{/if}{literal};
		jQuery ('#addoperation').click (function () {
			count = count + 1;
			var html = '<div class="row" id="count' + count + '">' +
							'<br />' +
							'<div class="col-md-12">' +
								'<div class="box-header">' +
									'<h2 class="box-title">' +
										'<button class="input-group-addon deleteitem" id="delete-count' + count + '" countval="' + count + '" type="button">' +
											'<i class="fa fa-trash-o"></i>' +
										'</button>' +
									'</h2>' +
								'</div>' +
								'<div class="form-group">' +
									'<label for="box_score">{/literal}{$MOD.LBL_OPERATION}{literal}</label>' +
									'<select class="form-control selectoperation" name="operation[]">' +
										'<option value="">{/literal}{$MOD.LBL_SELECT_OPERATION}{literal}</option>' +
										'<option value="+">{/literal}{$MOD.LBL_SUM}{literal}</option>' +
										'<option value="-">{/literal}{$MOD.LBL_REST}{literal}</option>' +
									'</select>' +
								'</div>' +
								'<div class="form-group">' +
									'<label for="box_score">Box Score</label>' +
									'<select class="form-control selectboxscore" name="boxscoreArray[]">' +
										'<option value="">--</option>' +
{/literal}
{foreach $BOX_SCORE->boxs as $data}
					   					'<option value="{$data.box_score_dataid}">{$data.box_score}</option>' +
{/foreach}
{literal}
									'</select>' +
								'</div>' +
							'</div>' +
						'</div>';
			jQuery ('#content-body').append (html);
		});

		jQuery ('#content-body').on ('click', '.deleteitem', function () {
			var idelement = jQuery (this).attr ('countval');
			if (count > 0) {
				jQuery ('#count' + idelement).remove ();
				count = count - 1;
			} else {
				jQuery ('#count' + idelement).remove ();
			}
		});

		jQuery ('#back').click (function () {
			window.history.back ();
		});
	});

	function submitdata () {
		var validate = true,
			contentBody = jQuery ("#content-body");
		contentBody.find(".selectboxscore").each (function () {
			if (jQuery (this).val () == '' || jQuery (this).val () == 'undefined') {
				alert (alert_arr.SELECT_ELEMENT_boxscore);
				jQuery (this).focus ();
				validate = false;
				return false;
			}
		});

		if (validate == false) {
			return false;
		}

		contentBody.find(".selectoperation").each (function () {
			if (jQuery (this).val () == '' || jQuery (this).val () == 'undefined') {
				alert (alert_arr.SELECT_ELEMENT_OPERATION);
				jQuery (this).focus ();
				validate = false;
				return false;
			}
		});

		return validate != false;
	}
{/literal}
</script>
<div id="dashboard_box_score">
	<section class="content">
		<div class="row">
			<div class="col-md-6">
				<div class="box box-primary">
					<div class="box-header">
						<h3 class="box-title">{if ($MODE != 'edit')}{$MOD.LBL_ADD_BS_CALC}{else}{$MOD.LBL_EDIT_BS_CALC}{/if}</h3>
					</div>
					<form role="form" name="boxscore" id="boxscore" action="index.php?module=boxscore&action=SaveBoxCalc" method="post" onsubmit="return submitdata();">
						<input type="hidden" name="tipo" value="{$TYPE}">
						<input type="hidden" name="totalbs" value="{$BOX_SCORE->boxs}">
						<input type="hidden" name="boxscoreid" value="{$ACCOUNT_ID}">
						<input type="hidden" name="modeView" value="{$MODE}">
						<input type="hidden" name="operationid" value="{$RECORD}">
						<div class="box-body" id="content-body">
							<div class="form-group">
								<label for="box_score">Box Score</label>
								<select class="form-control selectboxscore" name="boxscoreArray[]" title="">
									<option value="">--</option>
{foreach $BOX_SCORE->boxs as $data}
									<option value="{$data.box_score_dataid}"{if ($MODE == 'edit') && ($CALCULATION) && ($CALCULATION[0]['boxscore_data_id'][0] == $data.box_score_dataid)} selected="selected"{/if}>{$data.box_score}</option>
{/foreach}
								</select>
							</div>
							<div class="form-group">
								<label for="box_score">{$MOD.LBL_OPERATION}</label>
								<select class="form-control selectoperation" name="operation[]" title="">
									<option value="">{$MOD.LBL_SELECT_OPERATION}</option>
{if ($MODE == 'edit') && ($CALCULATION)}
									<option value="+"{if ($CALCULATION[0]['operators_list'][0] == '+')} selected="selected"{/if}>{$MOD.LBL_SUM}</option>
									<option value="-"{if ($CALCULATION[0]['operators_list'][0] == '-')} selected="selected"{/if}>{$MOD.LBL_REST}</option>
{/if}
								</select>
							</div>
							<div class="form-group">
								<label for="box_score">Box Score</label>
								<select class="form-control selectboxscore" name="boxscoreArray[]" title="">
									<option value="">--</option>
{foreach $BOX_SCORE->boxs as $data}
									<option value="{$data.box_score_dataid}"{if ($MODE == 'edit') && ($CALCULATION) && ($CALCULATION[0]['boxscore_data_id'][1] == $data.box_score_dataid)} selected="selected"{/if}>{$data.box_score}</option>
{/foreach}
								</select>
							</div>
{if ($MODE == 'edit') && (count ($CALCULATION[0]['boxscore_data_id']) > 2)}
	{for $i = 2; $i < count ($CALCULATION[0]['boxscore_data_id']); $i++}
							<div class="row" id="count{$i - 2}">
								<br />
								<div class="col-md-12">
									<div class="box-header">
										<h2 class="box-title">
											<button class="input-group-addon deleteitem" id="delete-count{$i - 2}" countval="{$i - 2}" type="button"><i class="fa fa-trash-o"></i></button>
										</h2>
									</div>
									<div class="form-group">
										<label for="operation{$i-2}">{$MOD.LBL_OPERATION}</label>
										<select id="operation{$i-2}" class="form-control selectoperation" name="operation[]">
											<option value="">{$MOD.LBL_SELECT_OPERATION}</option>
											<option value="+"{if ($CALCULATION[0]['operators_list'][$i-1] == '+')} selected="selected"{/if}>{$MOD.LBL_SUM}</option>
											<option value="-"{if ($CALCULATION[0]['operators_list'][$i-1] == '-')} selected="selected"{/if}>{$MOD.LBL_REST}</option>
										</select>
									</div>
									<div class="form-group">
										<label for="box_score{$i-2}">Box Score</label>
										<select id="box_score{$i-2}" class="form-control selectboxscore" name="boxscoreArray[]">
											<option value="">--</option>
		{foreach $EDITABLE_BOX_SCORE->boxs as $data}
											<option value="{$data.box_score_dataid}"{if ($data.box_score_dataid == $CALCULATION[0]['boxscore_data_id'][$i])} selected="selected"{/if}>{$data.box_score}</option>
		{/foreach}
										</select>
									</div>
								</div>
							</div>
	{/for}
{/if}
						</div>
						<div class="box-footer">
							<button type="button" name="addoperation" id="addoperation" class="btn btn-success">{$MOD.LBL_ADD_DIV_OP}</button>
							<button type="submit" name="submit" id="submit" class="btn btn-primary">{$MOD.LBL_SAVE_BUTTON_LABEL}</button>
							<button type="button" name="back" id="back" class="btn btn-warning">{$MOD.LBL_BACK}</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</section>
</div>
{/strip}