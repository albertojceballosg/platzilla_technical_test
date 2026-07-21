{strip}
	<style type="text/css">
	{literal}
		.alert-grey {
			background-color: #eee;
			text-align: center;
		}
		.rgt {
			text-align: right;
		}
		th, .ctr {
			text-align: center;
		}
		.lft {
			text-align: left;
		}
		/* Important part*/
		.modal-dialog{
			width: 800px;
			overflow-y: initial !important;
			overflow-x: initial !important;
		}
		.modal-body{
			height: 400px;
			overflow-y: auto;
			overflow-x: auto;
		}
	{/literal}
	</style>
	<script src="modules/{$MODULE}/{$MODULE}.js" type="text/javascript"></script>

	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true" onclick="jQuery ('#addValues').removeClass ('md-show'); jQuery ('.md-overlay').css ({ldelim} opacity: 0.0, visibility: 'hidden' {rdelim}); jQuery ('#addValues').html(''); return false;">×</button>
				<h4 class="modal-title">{$MODSTRING.LBL_EDIT_VALUES_INDICATOR}</h4>
			</div>
			<form name="EditViewBoxValues" id="EditViewBoxValues" method="post" action="index.php">
				<div class="modal-body">
					<input type="hidden" name="type" value="{$TYPE}">
					<input type="hidden" name="date_from" value="{$FROM}">
					<input type="hidden" name="date_to" value="{$TO}">
					<input type="hidden" name="boxscoreid" value="{$RECORD}">
					<input type="hidden" name="monthsearch" value="{$MONTH_SEARCH}">
					<input type="hidden" id="action" name="action" value="SaveBox">
					<input type="hidden" id="module" name="module" value="{$MODULE}">
					<input type="hidden" id="app" name="app" value="{$APPCODE}">
					<input type="hidden" id="edit_values_indicator" name="edit_values_indicator" value="">
					<input type="hidden" id="viewScale" name="viewScale" value="{$VIEW_SEARCH}">
                    {if $IS_HOME neq NULL}
						<input type="hidden" name="is_home" value="1">
                    {/if}
					<div class="row">
						<div class="col-xs-12 connectedSortable table-responsive">
							<table class="table">
								<tr>
									<th class="lft" style="width: 180px;">{$MODSTRING.LBL_INDICATORS}</th>
                                    {assign var='countdate' value=1}
                                    {foreach $BOX_SCORE->dates as $date}
										<th>{if ($BOX_SCORE->scale == 'Week')} SEM {$date.week}/{$YEAR_DATE}{*$countdate*} {else} {assign var='month' value=$date.date|date_format: 'M'} {$MODSTRING.MONTHS[$month]}-{$YEAR_DATE} {* $date.date|date_format: 'M'*} {/if}</th>
                                        {assign var='countdate' value=$countdate + 1}
                                    {/foreach}
								</tr>
								{for $i=0; $i<count($BLOCKS); $i++}
									{foreach $BOX_SCORE->boxs as $boxScoreData}
										<tr id="row-{$boxScoreData.box_score_dataid}">
											<td class="show-tools" style="color: #566573; background-color: {$boxScoreData.colorbase};">
												<span class="text">
												{if ($boxScoreData.defaultplatzilla == 1)}
													<i class="fa fa-lock" style="color: #00a65a;"></i>
												{/if}
													{$boxScoreData.box_score}
												</span>
											</td>
											{foreach $BOX_SCORE->dates as $date}
												{assign var='value' value=''}
												{if ($boxScoreData.scale == 'Week') && (isset ($boxScoreData.weekly[$date.week].value)) && ($date.year == $YEAR_DATE)}
													{assign var='value' value=$boxScoreData.weekly[$date.week].value}
												{else}
													{foreach $boxScoreData.weekly as $key => $data}
                                                        {assign var='dummy' value=$boxScoreData.weekly.$key.date|date_format:"%m"}
														{if ($dummy == $date.month) && ($date.year == $YEAR_DATE)}
															{assign var='value' value=$boxScoreData.weekly.$key.value}
															{break}
														{/if}
													{/foreach}
												{/if}
												<td style="background-color: {if ($date@index % 2 == 0)}{$boxScoreData.colordegrade}{else}{$boxScoreData.colorbase}{/if}" class="rgt show-tools">
													{if ($boxScoreData.defaultplatzilla == 1)}
														{$value}
													{else}
														<input name="date[{$boxScoreData.box_score_dataid}][{$date.week}]" type="hidden" value="{$date.date}">
														<input name="objective" type="hidden" value="{if (strpos ($boxScoreData.objective, '%') !== false)}%{/if}">
														<input name="weeklyid[{$boxScoreData.box_score_dataid}][{$date.week}]" type="hidden" value="{$boxScoreData.weekly[$date.week]['weeklyid']}">
														<input class="form-control input-sm" onkeyup="validateDecimal32General('value[{$boxScoreData.box_score_dataid}][{$date.week}]')" name="value[{$boxScoreData.box_score_dataid}][{$date.week}]" type="text" value="{$value}" objective="{if (strpos ($boxScoreData.objective, '%') !== false)}%{/if}" placeholder="" {if $date.year != $YEAR_DATE} readonly {/if}>
													{/if}
												</td>
											{/foreach}
										</tr>
									{/foreach}
								{/for}
							</table>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<a class="btn btn-warning" onclick="jQuery ('#addValues').removeClass ('md-show'); jQuery ('.md-overlay').css ({ldelim} opacity: 0.0, visibility: 'hidden' {rdelim}); jQuery ('#addValues').html(''); return false;">{$APP.LBL_CANCEL_BUTTON_LABEL}</a>
					<input class="btn btn-success" id="submit_boxscore" name="submit_boxscore" type="submit" onclick="return validateIndicatorValues()" value="{$APP.LBL_SAVE_BUTTON_LABEL}">
				</div>
			</form>
		</div>
	</div>
{/strip}