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
			height: 250px;
			overflow-y: auto;
		}
	</style>
	<!--
	<script src="modules/{$MODULE}/{$MODULE}.js" type="text/javascript"></script>
	-->
    {math equation= rand() assign= "viewAlertId"}
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true" onclick="jQuery ('#viewIndicators').removeClass ('md-show'); jQuery ('.md-overlay').css ({ldelim} opacity: 0.0, visibility: 'hidden' {rdelim}); jQuery ('#viewIndicators').html(''); return false;">×</button>
				<h4 class="modal-title">{$MODSTRING.DETAIL_OCURRENCE}</h4>
			</div>
			<form role="form"  id="form-view-{$viewAlertId}" method="post">
				<div class="modal-body">
					<input type="hidden" name="module" id="module" value="systemalerts">
					<input type="hidden" name="action" id="action" value="AjaxSystemAlertsUtils">
					<input type="hidden" name="function" id="function" value="LOOK-ALERT">
					<input type="hidden" id="Ajax" name="Ajax" value="true">
					<input type="hidden" name="record" id="record" value="{$RECORD}">
					<input type="hidden" name="date_from" value="{$DATE_FROM}">
					<input type="hidden" name="date_to" value="{$DATE_TO}">
					<input type="hidden" id="app" name="app" value="{$APP}">
					<input type="hidden" id="sourceAlert" name="sourceAlert" value="{$SOURCE_ALERT}">
					<div {*class="modal-body"*}>
						<div class="row">
							<div class="col-xs-12 connectedSortable table-responsive">
								{*$DETAIL_ALERT|var_dump*}
								{*$FLAGGED_ALERTS|var_dump*}
								<table class="table table-striped table-hover">
									{if $SOURCE_ALERT == 'Indicators'}
										<thead>
											<tr>
												<th>{$MODSTRING.OBJECTIVE}</th>
												{*<th>{$MODSTRING.CONDITION}</th>*}
												<th>{$MODSTRING.LBL_ALERT_ENTITY_VALUE}</th>
												<th>{$MODSTRING.DATE_ALERT}</th>
												<th>{$MODSTRING.NUM_ALERTS}</th>

											</tr>
										</thead>
										<tbody>
											{foreach $DETAIL_ALERT as $ocurrence}
												<tr>
													<td>{$ocurrence.objective}</td>
													{*<td>{$ocurrence.condition_alert}</td>*}
													<td>{$ocurrence.value_alert}</td>
													<td>{$ocurrence.date_alert}</td>
													<td style="text-align: center">{$ocurrence.count_alert}</td>
												</tr>
											{/foreach}
										</tbody>
									{else}
										<thead>
										<tr>
											<th>{$MODSTRING.DATE_ALERT_MODULE}</th>
											{*<th>{$MODSTRING.PERIOD_ALERT}</th>*}
											<th style="text-align: center">{$MODSTRING.NUM_ALERTS}</th>
											<th style="text-align: center">{$MODSTRING.LBL_ALERT_STATUS}</th>
											<th style="text-align: right;width: 20%"></th>
										</tr>
										</thead>
										<tbody>
										{foreach $DETAIL_ALERT as $ocurrence}
                                            {assign var='occurrenceStatus' value='PENDING'}
											{if $FLAGGED_ALERTS neq NULL}
                                                {foreach $FLAGGED_ALERTS as $status}
													{if $status.systemalerts_ocurrence_id eq $ocurrence.systemalerts_ocurrence_id}
                                                        {assign var='occurrenceStatus' value=$status.status_occurrence}
														{break}
													{/if}
                                                {/foreach}
											{/if}
											<tr id="ocurrence-{$ocurrence.systemalerts_ocurrence_id}-{$viewAlertId}">
												<td>{$ocurrence.date_alert}</td>
												{*<td>{$ocurrence.from_period} - {$ocurrence.to_period}</td>*}
												<td style="text-align: center;">{$ocurrence.count_alert}</td>
												<td style="text-align: center;">{$MODSTRING[$occurrenceStatus]}</td>
												<td style="text-align: right">
													<div class="btn-group">
														<button type="button" class="btn btn-sm btn-sm btn-primary"
                                                                {if $occurrenceStatus eq 'PROCESSED'}
																	disabled="disabled"
                                                                {/if}
																data-ocurrence="{$ocurrence.systemalerts_ocurrence_id}"
																data-modal-id="{$viewAlertId}"
																title="Tramitar alerta">
															<i class="fa fa-exchange"></i>
														</button>
															<button type="button" class="btn btn-sm btn-danger"
                                                                    {if $occurrenceStatus eq 'DISCARDED'}
																	disabled="disabled"
																	{/if}
																	data-ocurrence="{$ocurrence.systemalerts_ocurrence_id}"
																	data-modal-id="{$viewAlertId}"
																	onclick="SystemAlertUtils.setlookAlert(this,)"
																	title="Descartar Alerta">
																<i class="fa fa-eye-slash" aria-hidden="true"></i>
															</button>
													</div>
												</td>
											</tr>
										{/foreach}
										</tbody>
									{/if}
								</table>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<a class="btn btn-default" onclick="jQuery ('#viewIndicators').removeClass ('md-show'); jQuery ('.md-overlay').css ({ldelim} opacity: 0.0, visibility: 'hidden' {rdelim}); return false;">{$MODSTRING.LBL_CLOSE_BUTTON_LABEL}</a>
					</div>
			</form>
		</div>
	</div>
{/strip}