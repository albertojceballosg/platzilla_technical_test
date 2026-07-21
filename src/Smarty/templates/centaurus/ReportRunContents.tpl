{strip}
<div class="main-box-body clearfix">
	<div class="table-responsive" style="border-bottom: 1px solid rgb(231, 235, 238); border-top: 1px solid rgb(231, 235, 238); overflow-y: visible;">
{if $DIRECT_OUTPUT eq true}
	{if isset($__REPORT_RUN_INSTANCE)}
		{php}
			global $list_report_form;
			$__oReportRun = $list_report_form->getVariable('__REPORT_RUN_INSTANCE')->value;
			$__filterSql = $list_report_form->getVariable('__REPORT_RUN_FILTER_SQL')->value;
			$__oReportRunReturnValue = $__oReportRun->GenerateReport("HTML", $__filterSql, true);
		{/php}
	{/if}
{elseif $ERROR_MSG eq ''}
	{$REPORTHTML.0}
{else}
	{$ERROR_MSG}
{/if}
	</div>
</div>
{if $SHOWCHARTS eq 'true'}
<div class="col-lg-12">
	<div class="main-box clearfix">
		<div name="viewcharts" id="viewcharts">
			<table style="border: 1px solid rgb(0, 0, 0);" align="center" cellpadding="0" cellspacing="0" width="100%">
				<tbody>
				<tr>
					<td>
						<table border=0 cellspacing=1 cellpadding=0 width="100%" class="lvtBg">
							<tr>
								<td> {$PIECHART} </td>
								<td> {$BARCHART} </td>
							</tr>
						</table>
					</td>
				</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>
{/if}
{/strip}