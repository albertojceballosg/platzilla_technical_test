<tr id="job_project_footer" valign="middle">
    <td class="text-center" colspan=4 style="text-align: right;"><span style="text-align: right;">TOTALES</span></td>
    <td id="td-job_contribution_factor-{$idTaskProject}">
        <input type="text" id="summary-job_contribution_factor-{$idTaskProject}"
            name="projec_job[summaryRow][job_contribution_factor]" rel="SUM_COLUMN" value="{$totalJobFactor}"
            class="form-control" readonly="" style="text-align: right;">
    </td>
    <td>&nbsp;</td>
    <td id="td-project_progress-{$idTaskProject}" style="text-align: right; vertical-align: middle;">
        <input type="text" id="summary-project_progress-{$idTaskProject}"
            name="projec_job[summaryRow][project_progress]" rel="SUM_COLUMN" value="{$totalProjectProgress}"
            class="form-control" readonly="" style="text-align: right;">
    </td>
    <td id="td-work_estimated_cost-{$idTaskProject}" style="text-align: right; vertical-align: middle;">
        <input type="text" id="summary-work_estimated_cost-{$idTaskProject}" value="{$TOTAL_WORK_ESTIMATED_COST}"
            class="form-control" readonly="" style="text-align: right;">
    </td>
    <td id="td-cost_work_performed-{$idTaskProject}" style="text-align: right; vertical-align: middle;">
        <input type="text" id="summary-cost_work_performed-{$idTaskProject}" value="{$TOTAL_COST_WORK_PERFORMED}"
            class="form-control" readonly="" style="text-align: right;">
    </td>
	<td></td>
</tr>