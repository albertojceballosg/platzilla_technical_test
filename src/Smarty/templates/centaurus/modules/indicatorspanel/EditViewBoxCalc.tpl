{strip}
	<style type="text/css">
		th {
			text-align: center;
		}
		/* Important part */
		.modal-dialog{
			overflow-y: initial !important
		}
		.modal-body{
			height: 300px;
			overflow-y: auto;
		}
	</style>
	<script type="text/javascript">
	{literal}
		jQuery (document).ready (function () {
			var count = {/literal}{if ($MODE == 'edit') && (count ($CALCULATION['boxscore_data_id']) > 2)}{$CALCULATION['boxscore_data_id']|@count}{else}0{/if}{literal};
			jQuery ('#addoperation').click (function () {
				count = count + 1;
				var html = '<div class="row" id="count' + count + '">' +
								'<br />' +
								'<div class="col-md-12">' +
									'<div class="box-header">' +
										'<h2 class="box-title">' +
											'<a href="javascript:void(0)" class="input-group-addon deleteitem" id="delete-count' + count + '" countval="' + count + '" style="color:red; border-top-width:0px !important; cursor: pointer;" title={/literal}{$MOD.LBL_DELETE_OPERATION}{literal}>' +
												'<i class="fa fa-trash-o"></i>' +
											'</a>' +
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


	{/literal}
	</script>
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true" onclick="jQuery ('#addCalcules').removeClass ('md-show'); jQuery ('.md-overlay').css ({ldelim} opacity: 0.0, visibility: 'hidden' {rdelim}); jQuery ('#addCalcules').html(''); return false;">×</button>
				<h4 class="modal-title">{if ($MODE != 'edit')}{$MOD.LBL_ADD_BS_CALC}{else}{$MOD.LBL_EDIT_BS_CALC}{/if}</h4>
			</div>
			<form role="form" name="{$MODULE}" id="{$MODULE}" action="index.php" method="post">
				<div class="modal-body">
					<input type="hidden" name="type" value="{$TYPE}">
					<input type="hidden" name="totalbs" value="{count($BOX_SCORE->boxs)}">
					<input type="hidden" name="boxscoreid" value="{$ACCOUNT_ID}">
					<input type="hidden" name="modeView" value="{$MODE}">
					<input type="hidden" name="operationid" value="{$RECORD}">
					<input type="hidden" name="monthsearch" value="{$MONTH_SEARCH}">
					<input type="hidden" id="viewScale" name="viewScale" value="{$VIEW_SEARCH}">
					<input type="hidden" id="app" name="app" value="{$CODE_APP}">
					<input type="hidden" id="module" name="module" value="{$MODULE}">
					<input type="hidden" id="action" name="action" value="SaveBoxCalc">
					<div class="box-body" id="content-body">
						<div class="form-group">
							<label for="box_score">{$MOD.LBL_TITLE_INDICATOR}</label>
							<select class="form-control selectboxscore" name="boxscoreArray[]" title="">
								<option value="">--</option>
								{foreach $BOX_SCORE->boxs as $data}
									<option value="{$data.box_score_dataid}"{if ($MODE == 'edit') && ($CALCULATION) && ($CALCULATION['boxscore_data_id'][0] == $data.box_score_dataid)} selected="selected"{/if}>{$data.box_score}</option>
								{/foreach}
							</select>
						</div>
						<div class="form-group">
							<label for="box_score">{$MOD.LBL_OPERATION}</label>
							<select class="form-control selectoperation" name="operation[]" title="">
								<option value="">{$MOD.LBL_SELECT_OPERATION}</option>
								{if ($MODE == 'edit') && ($CALCULATION)}
									<option value="+"{if ($CALCULATION['operators_list'][0] == '+')} selected="selected"{/if}>{$MOD.LBL_SUM}</option>
									<option value="-"{if ($CALCULATION['operators_list'][0] == '-')} selected="selected"{/if}>{$MOD.LBL_REST}</option>
								{else}
									<option value="+">{$MOD.LBL_SUM}</option>
									<option value="-">{$MOD.LBL_REST}</option>
								{/if}
							</select>
						</div>
						<div class="form-group">
							<label for="box_score">{$MOD.LBL_TITLE_INDICATOR}</label>
							<select class="form-control selectboxscore" name="boxscoreArray[]" title="">
								<option value="">--</option>
								{foreach $BOX_SCORE->boxs as $data}
									<option value="{$data.box_score_dataid}"{if ($MODE == 'edit') && ($CALCULATION) && ($CALCULATION['boxscore_data_id'][1] == $data.box_score_dataid)} selected="selected"{/if}>{$data.box_score}</option>
								{/foreach}
							</select>
						</div>
						{if ($MODE == 'edit') && (count ($CALCULATION['boxscore_data_id']) > 2)}
							{for $i = 2; $i < count ($CALCULATION['boxscore_data_id']); $i++}
								<div class="row" id="count{$i - 2}">
									<br />
									<div class="col-md-12">
										<div class="box-header">
											<h2 class="box-title">
												<a href="javascript:void(0)" class="input-group-addon deleteitem" id="delete-count{$i - 2}" countval="{$i - 2}" style="color:red; border-top-width:0px !important; cursor: pointer;"><i class="fa fa-trash-o"></i></a>
											</h2>
										</div>
										<div class="form-group">
											<label for="operation{$i-2}">{$MOD.LBL_OPERATION}</label>
											<select id="operation{$i-2}" class="form-control selectoperation" name="operation[]">
												<option value="">{$MOD.LBL_SELECT_OPERATION}</option>
												<option value="+"{if ($CALCULATION['operators_list'][$i-1] == '+')} selected="selected"{/if}>{$MOD.LBL_SUM}</option>
												<option value="-"{if ($CALCULATION['operators_list'][$i-1] == '-')} selected="selected"{/if}>{$MOD.LBL_REST}</option>
											</select>
										</div>
										<div class="form-group">
											<label for="box_score{$i-2}">{$MOD.LBL_TITLE_INDICATOR}</label>
											<select id="box_score{$i-2}" class="form-control selectboxscore" name="boxscoreArray[]">
												<option value="">--</option>
												{foreach $EDITABLE_BOX_SCORE->boxs as $data}
													<option value="{$data.box_score_dataid}"{if ($data.box_score_dataid == $CALCULATION['boxscore_data_id'][$i])} selected="selected"{/if}>{$data.box_score}</option>
												{/foreach}
											</select>
										</div>
									</div>
								</div>
							{/for}
						{/if}
					</div>
				</div>
				<div class="modal-footer">
					<button class="btn btn-warning" id="btnclose" onclick="jQuery ('#addCalcules').removeClass ('md-show'); jQuery ('.md-overlay').css ({ldelim} opacity: 0.0, visibility: 'hidden' {rdelim}); jQuery ('#addCalcules').html(''); return false;">{$MOD.LBL_BACK}</button>
					<button type="button" name="addoperation" id="addoperation" class="btn btn-success">{$MOD.LBL_ADD_DIV_OP}</button>
					<button type="submit" name="btnSubmitCalculate" id="btnSubmitCalculate" class="btn btn-primary" onclick="return validateCalculate()">{$MOD.LBL_SAVE}</button>
				</div>
			</form>
		</div>
	</div>
{/strip}
