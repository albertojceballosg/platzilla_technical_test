<tr class="tabla-field-row" valign="middle" id="tr-row-{$idRow}" data-num-format="{$NUMBERING_FORMAT}">
    <td style="vertical-align: middle">
        {if $PROJECT_STAGES neq NULL}
            <div class="input-group" style="width: 100%;">
                <select id="stage-{$idRow}" name="projec_job[stage][]" onchange="" class="form-control">
                    {foreach $PROJECT_STAGES as $projectStage}
                        <option value="{$projectStage->id}" {if $relatedJob->getStageId () eq $projectStage->id}selected{/if}>
                            {$projectStage->stage}</option>
                    {/foreach}
                </select>
            </div>
        {else}
            <span style="">No se han definido las etpas del proyecto</span>
        {/if}
    </td>
    <td style="vertical-align: middle;padding-top:1.8em !important; padding-bottom:0; margin-bottom:0;">
        <div class="row" style="padding-right: 0;margin-right: 0;vertical-align: middle;">
            <div class="col-md-12" style="vertical-align: middle">
                <div class="form-group field-container" id="td_select_job-{$idRow}" style="vertical-align: middle">
                    <input type="hidden" id="select_job-{$idRow}" name="projec_job[crmid_job][]"
                        value="{$relatedJob->getCrmIdJob ()}" class="small">
                    <div class="input-group" style="width: 100%;vertical-align: middle;">
                        <input type="text" id="edit_seleccione_job-{$idRow}_display" name="projec_job[job_name][]"
                            value="{$relatedJob->getJobName ()}" class="form-control input-readonly b-right"
                            readonly="readonly" placeholder="">
                        <div class="input-group-addon" data-current-module="proyectos"
                            data-display-field-id="edit_seleccione_job-{$idRow}_display"
                            data-field-id="select_job-{$idRow}" data-referenced-module="orden_de_trabajo"
                            data-title="Seleccione trabajo" onclick="RelatedModuleModalUtils.openModal (this);">
                            <i class="fa fa-plus-circle"></i>
                        </div>
                        <div class="input-group-addon"
                            onclick="var fieldContainer = jQuery (this).closest ('.field-container'); fieldContainer.find ('#edit_seleccione_job-{$idRow}_display').val (''); fieldContainer.find ('#seleccione_vent').val (''); return false;">
                            <i class="fa fa-eraser"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </td>
    <td style="vertical-align: middle; text-align: center;">
        <div class="input-group" style="width: 100%;">
            <input type="text" id="job-start_date-{$idRow}" placeholder="Fecha estimada de inicio"
                name="projec_job[start_date][]" value="{$relatedJob->getStartDate ()}"
                class="form-control" readonly="readonly" style="background-color: #b8bcc4; cursor: not-allowed;" title="La fecha se obtiene del trabajo">
        </div>
    </td>
    <td style="vertical-align: middle; text-align: center;">
        <div class="input-group" style="width: 100%;">
            <input type="text" id="job-due_date-{$idRow}" placeholder="Fecha estimada de cierre"
                name="projec_job[due_date][]" value="{$relatedJob->getEstimatedDueDate ()}"
                class="form-control" readonly="readonly" style="background-color: #b8bcc4; cursor: not-allowed;" title="La fecha se obtiene del trabajo">
        </div>
    </td>
    <td style="vertical-align: middle; text-align: center;">
        <div class="input-group" style="width: 100%;">
            <input type="text" tabindex="" id="name_responsible_job-{$idRow}"
                value="{$relatedJob->getResponsibleJobName ()}" class="form-control" readonly="readonly" style="background-color: #b8bcc4;">
            <input type="hidden" tabindex="" name="projec_job[responsible_job][]" id="responsible_job-{$idRow}"
                value="{$relatedJob->getResponsibleJob ()}" class="form-control">
        </div>
    </td>
    <td style="vertical-align: middle; text-align: right;">
        <div class="input-group" style="width: 100%;">
            <input type="text" id="job_contribution_factor-{$idRow}" placeholder="99%"
                name="projec_job[job_contribution_factor][]" value="{$relatedJob->getJobContributionFactor ()}"
                class="form-control" onkeyup="TaskProjectUtls.updateNumFields(this, '{$idRow}')">
        </div>
    </td>
    <td style="vertical-align: middle; text-align: right;">
        <div class="input-group" style="width: 100%;">
            <input type="text" id="percentage_completion-{$idRow}" placeholder="99%"
                name="projec_job[percentage_completion][]" value="{$relatedJob->getPercentageCompletion ()}"
                class="form-control" readonly="readonly" style="background-color: #b8bcc4;">
        </div>
    </td>
    <td style="vertical-align: middle; text-align: right;">
        <div class="input-group" style="width: 100%;">
            <input type="text" id="project_progress-{$idRow}" placeholder="99%" name="projec_job[project_progress][]"
                value="{$relatedJob->getProjectProgress ()}" class="form-control"
                readonly="readonly" style="background-color: #a0a4ac; font-weight: bold;">
        </div>
    </td>
    <td style="vertical-align: middle; text-align: right;">
        <div class="input-group" style="width: 100%;">
            <input type="text" id="work_estimated_cost-{$idRow}" placeholder="0,00"
                value="{$relatedJob->getWorkEstimatedCost ()}" class="form-control" readonly="readonly" style="background-color: #b8bcc4;">
        </div>
    </td>
    <td style="vertical-align: middle; text-align: right;">
        <div class="input-group" style="width: 100%;">
            <input type="text" id="cost_work_performed-{$idRow}" placeholder="0,00"
                value="{$relatedJob->getCostWorkPerformed ()}" class="form-control" readonly="readonly"
                style="text-align: right; background-color: #b8bcc4;">
        </div>
    </td>
    <td style="vertical-align: middle; text-align: center;">
        <div class="input-group" style="width: 100%;">
            {assign var=workSitVal value=$relatedJob->getWorkSituation()|trim}
            {assign var=bgColor value='transparent'}
            {assign var=textColor value='inherit'}
            {assign var=tooltipText value=''}
            {if $workSitVal eq 'Óptima'}
                {assign var=bgColor value='#2E7D32'}
                {assign var=textColor value='white'}
                {assign var=tooltipText value='El trabajo progresa rápido gastando menos o lo justo.'}
            {elseif $workSitVal eq 'En control'}
                {assign var=bgColor value='#8BC34A'}
                {assign var=textColor value='white'}
                {assign var=tooltipText value='El trabajo cumple el cronograma y el presupuesto.'}
            {elseif $workSitVal eq 'Alerta de eficiencia'}
                {assign var=bgColor value='#1976D2'}
                {assign var=textColor value='white'}
                {assign var=tooltipText value='Se está cumpliendo el tiempo, pero a un costo mayor (poca rentabilidad).'}
            {elseif $workSitVal eq 'Retraso operativo'}
                {assign var=bgColor value='#FF9800'}
                {assign var=textColor value='white'}
                {assign var=tooltipText value='Estamos lentos, pero aún no nos hemos pasado del presupuesto.'}
            {elseif $workSitVal eq 'Crítica'}
                {assign var=bgColor value='#D32F2F'}
                {assign var=tooltipText value='El peor escenario: vamos tarde y ya gastamos más de lo previsto.'}
            {/if}
            <input type="text" id="work_situation-{$idRow}" value="{$relatedJob->getWorkSituation ()}"
                class="form-control" readonly="readonly"
                style="text-align: center; background-color: {$bgColor} !important; color:#FFFFFF; font-weight: bold; border: 2px solid {$bgColor};"
                {if $tooltipText neq ''}title="{$tooltipText}" data-toggle="tooltip" data-placement="top" {/if}>
        </div>
    </td>
    <td class="text-center project-actions-cell" style="vertical-align: middle">
        <button type="button" class="btn btn-primary btn-xs"
            onclick="TaskProjectUtls.moveRowUp (this, 'tr-row-{$idRow}')">
            <i class="fa fa-arrow-up" aria-hidden="true"></i>
        </button>
        <button type="button" class="btn btn-danger btn-xs"
            onclick="TaskProjectUtls.moveRowDown (this, 'tr-row-{$idRow}')">
            <i class="fa fa-arrow-down" aria-hidden="true"></i>
        </button>
        <button type="button" class="btn btn-danger btn-icon delete-value-button"
            onclick="TaskProjectUtls.delRowToTable (this, 'tr-row-{$idRow}', '{$idTaskProject}');">
            <i class="fa fa-trash-o"></i>
        </button>
    </td>
</tr>