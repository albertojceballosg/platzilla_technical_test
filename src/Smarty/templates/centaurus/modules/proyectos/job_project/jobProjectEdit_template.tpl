<tr class="tabla-field-row" valign="middle" id="tr-row-__ID__" data-num-format="{$NUMBERING_FORMAT}">
    <td style="vertical-align: middle">
        {if $PROJECT_STAGES neq NULL}
            <div class="input-group" style="width: 100%;vertical-align: top">
                <select id="stage-__ID__" name="projec_job[stage][]" onchange="" class="form-control">
                    {foreach $PROJECT_STAGES as $projectStage}
                        <option value="{$projectStage->id}" {if $relatedTask['stage'] eq $projectStage->id}selected{/if}>
                            {$projectStage->stage}</option>
                    {/foreach}
                </select>
            </div>
        {else}
            <span style="">No se han definido las etapas del proyecto</span>
        {/if}
    </td>
    <td style="vertical-align: middle">
        <div class="row" style="padding-right: 0;margin-right: 0;vertical-align: middle">
            <div class="col-md-12">
                <div class="form-group field-container" id="td_select_job-__ID__" style="">
                    <input type="hidden" id="select_job-__ID__" name="projec_job[crmid_job][]" value=" " class="small">
                    <div class="input-group" style="width: 100%;">
                        <input type="text" id="edit_seleccione_job-__ID___display" name="projec_job[job_name][]"
                            value="" class="form-control input-readonly b-right" readonly="readonly" placeholder="">
                        <div class="input-group-addon" data-current-module="proyectos"
                            data-display-field-id="edit_seleccione_job-__ID___display" data-field-id="select_job-__ID__"
                            data-referenced-module="orden_de_trabajo" data-title="Seleccione trabajo"
                            onclick="RelatedModuleModalUtils.openModal (this);">
                            <i class="fa fa-plus-circle"></i>
                        </div>
                        <div class="input-group-addon"
                            onclick="var fieldContainer = jQuery (this).closest ('.field-container'); fieldContainer.find ('#edit_seleccione_job-__ID___display').val (''); fieldContainer.find ('#seleccione_vent').val (''); return false;">
                            <i class="fa fa-eraser"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </td>
    <td style="vertical-align: middle; text-align: center;">
        <div class="input-group" style="width: 100%;vertical-align: top">
            <input type="text" id="job-start_date-__ID__" placeholder="Fecha estimada de inicio"
                name="projec_job[start_date][]" value="" class="form-control" readonly="readonly" style="background-color: #b8bcc4; cursor: not-allowed;">
        </div>
    </td>
    <td style="vertical-align: middle; text-align: center;">
        <div class="input-group" style="width: 100%;vertical-align: top">
            <input type="text" id="job-due_date-__ID__" placeholder="Fecha estimada de cierre"
                name="projec_job[due_date][]" value="" class="form-control" readonly="readonly" style="background-color: #b8bcc4; cursor: not-allowed;" title="La fecha se obtiene del trabajo">
        </div>
    </td>
    <td style="vertical-align: middle; text-align: center;">
        <div class="input-group" style="width: 100%;vertical-align: top">
            <input type="text" tabindex="" id="name_responsible_job-__ID__" value="" class="form-control" readonly="readonly" style="background-color: #b8bcc4;">
            <input type="hidden" tabindex="" name="projec_job[responsible_job][]" id="responsible_job-__ID__" value=""
                class="form-control">
        </div>
    </td>
    <td style="vertical-align: middle; text-align: right;">
        <div class="input-group" style="width: 100%;vertical-align: top">
            <input type="text" id="job_contribution_factor-__ID__" placeholder="99%"
                name="projec_job[job_contribution_factor][]" value="" class="form-control"
                onkeyup="TaskProjectUtls.updateNumFields(this, '__ID__')">
        </div>
    </td>
    <td style="vertical-align: middle; text-align: right;">
        <div class="input-group" style="width: 100%;vertical-align: top">
            <input type="text" id="percentage_completion-__ID__" placeholder="99%"
                name="projec_job[percentage_completion][]" value="" class="form-control"
                readonly="readonly" style="background-color: #b8bcc4;">
        </div>
    </td>
    <td style="vertical-align: middle; text-align: right;">
        <div class="input-group" style="width: 100%;">
            <input type="text" id="project_progress-__ID__" placeholder="99%" name="projec_job[project_progress][]"
                value="" class="form-control" readonly="readonly" style="background-color: #a0a4ac; font-weight: bold;">
        </div>
    </td>
    <td style="vertical-align: middle; text-align: right;">
        <div class="input-group" style="width: 100%;">
            <input type="text" id="work_estimated_cost-__ID__" placeholder="0,00" value="" class="form-control"
                readonly="readonly" style="background-color: #b8bcc4;">
        </div>
    </td>
	<td style="vertical-align: middle; text-align: right;">
        <div class="input-group" style="width: 100%;">
            <input type="text" id="cost_work_performed-__ID__" placeholder="0,00"
                value="{if $relatedJob neq NULL}{$relatedJob->getCostWorkPerformed ()}{else}0,00{/if}" class="form-control" readonly="readonly"
                style="text-align: right; background-color: #b8bcc4;">
        </div>
    </td>
	<td style="vertical-align: middle; text-align: center;">
        <div class="input-group" style="width: 100%;">
            <input type="text" id="work_situation-__ID__" placeholder="Situación del trabajo"
                value="{if $relatedJob neq NULL}{$relatedJob->getWorkSituation ()}{else}{/if}" class="form-control" readonly="readonly"
                style="text-align: center; background-color: #b8bcc4;">
        </div>
    </td>
    <td class="text-center project-actions-cell" style="vertical-align: middle">
        <button type="button" class="btn btn-primary btn-xs"
            onclick="TaskProjectUtls.moveRowUp (this, 'tr-row-__ID__')">
            <i class="fa fa-arrow-up" aria-hidden="true"></i>
        </button>
        <button type="button" class="btn btn-danger btn-xs"
            onclick="TaskProjectUtls.moveRowDown (this, 'tr-row-__ID__')">
            <i class="fa fa-arrow-down" aria-hidden="true"></i>
        </button>
        <button type="button" class="btn btn-danger btn-icon delete-value-button"
            onclick="TaskProjectUtls.delRowToTable (this, 'tr-row-__ID__', '{$idTaskProject}');">
            <i class="fa fa-trash-o"></i>
        </button>

    </td>
</tr>