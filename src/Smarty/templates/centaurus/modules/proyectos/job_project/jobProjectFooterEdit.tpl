<tr valign="middle">
    <td class="" colspan=5 style="text-align:right;"><span style="text-align:right;">TOTALES</span></td>

    <td id="td-job_contribution_factor-{$idTaskProject}" style="text-align: right;">
        <input type="text" id="summary-job_contribution_factor-{$idTaskProject}"
            name="projec_job[summaryRow][job_contribution_factor]" rel="SUM_COLUMN" value="{$totalJobFactor}"
            class="form-control" readonly="" style="text-align: right;">
    </td>
    <td>&nbsp;</td>
    <td id="td-project_progress-{$idTaskProject}" style="text-align: right;">
        <input type="text" id="summary-project_progress-{$idTaskProject}"
            name="projec_job[summaryRow][project_progress]" rel="SUM_COLUMN" value="{$totalProjectProgress}"
            class="form-control" readonly="" style="text-align: right;">
    </td>
    <td id="td-work_estimated_cost-{$idTaskProject}" style="text-align: right;">
        <input type="text" id="summary-work_estimated_cost-{$idTaskProject}"
            name="projec_job[summaryRow][work_estimated_cost]" value="{$TOTAL_WORK_ESTIMATED_COST}" class="form-control"
            readonly="" style="text-align: right;">
    </td>
    <td id="td-cost_work_performed-{$idTaskProject}" style="text-align: right;">
        <input type="text" id="summary-cost_work_performed-{$idTaskProject}"
            name="projec_job[summaryRow][cost_work_performed]" value="{$TOTAL_COST_WORK_PERFORMED}" class="form-control"
            readonly="" style="text-align: right;">
    </td>
    <td class="text-center">&nbsp;</td>
	<td class="text-center">&nbsp;</td>
</tr>
<tr id="footerJP">
    <td colspan="12" class="text-center">
        <button type="button" data-id-linkage="{$idTaskProject}" class="btn btn-primary" {if $RELATED_JOBS neq NULL}
            data-sequence="{($key + 1)}" {else} data-sequence="0" 
            {/if}
            onclick="TaskProjectUtls.addRowToTable (this, 'task-project-{$idTaskProject}', '{$idTaskProject}');">
            <i class="fa fa-plus"></i></button>
    </td>
</tr>