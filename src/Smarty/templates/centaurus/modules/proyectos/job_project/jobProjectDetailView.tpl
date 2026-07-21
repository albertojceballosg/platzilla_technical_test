<tr class="tabla-field-row" valign="middle" id="tr-row-{$idRow}">
    <td style="vertical-align: middle;">
        <div class="row" style="padding-right: 1px;margin-right: 1px">
            <div class="col-md-12">
                <div class="form-group field-container" id="td_job_{$idRow}">
                    <div class="input-group col-xs-12" style="width: 100%;">
                        <span id="job-id-{$idRow}" class="form-control b-left" style="overflow-x: hidden;width: 100%">
                            <a href="index.php?module=orden_de_trabajo&action=DetailView&record={$relatedJob->getCrmIdJob ()}"
                                target="_blank" title="Trabajo para el proyecto">{$relatedJob->getJobName ()}</a>
                        </span>
                    </div>
                </div>

            </div>
        </div>
    </td>
    <td style="vertical-align: middle; text-align: center;">
        <div class="input-group" style="width: 100%;">
            <span id="start_date-{$idRow}" class="form-control" style="border: none; background: transparent;">
                {if $relatedJob->getStartDate () neq NULL}{$relatedJob->getStartDate ()}{/if}
            </span>
        </div>
    </td>
    <td style="vertical-align: middle; text-align: center;">
        <div class="input-group" style="width: 100%;">
            <span id="due_date-{$idRow}" class="form-control" style="border: none; background: transparent;">
                {if $relatedJob->getEstimatedDueDate () neq NULL}{$relatedJob->getEstimatedDueDate ()}{/if}
            </span>
        </div>
    </td>
    <td style="vertical-align: middle; text-align: center;">
        {if $AVAILABLE_SYSTEM_USERS neq NULL}
            <div class="input-group" style="width: 100%;">
                <span id="assigned-{$idRow}" class="form-control" style="border: none; background: transparent;">
                    {foreach $AVAILABLE_SYSTEM_USERS as $systemUser}
                        {if $systemUser->getId() eq $relatedJob->getResponsibleJob ()}
                            {if $systemUser->getImageUri() neq NULL}
                                <figure class="center-block" style="border-radius: 50%; height: 40px; overflow: hidden; width: 40px;">
                                    <img class="img-responsive img-circle" alt="{$systemUser->getFirstName()}"
                                        title="{$systemUser->getFirstName()} {$systemUser->getLastName()}"
                                        src="{$systemUser->getImageUri()}">
                                </figure>
                            {else}
                                {$systemUser->getFirstName()} {$systemUser->getLastName()}
                            {/if}
                        {/if}
                    {/foreach}
                </span>
            {else}
                <span class="form-control" style="border: none; background: transparent;">&nbsp;</span>
            {/if}
    </td>
    <td style="vertical-align: middle; text-align: right;">
        <div class="input-group" style="width: 100%;">
            <span id="due_date-{$idRow}" class="form-control" style="border: none; background: transparent;">
                {if $relatedJob->getJobContributionFactor () neq NULL}{$relatedJob->getJobContributionFactor ()}{/if}
            </span>
        </div>
    </td>
    <td style="vertical-align: middle; text-align: right;">
        <div class="input-group" style="width: 100%;">
            <span id="duration-{$idRow}" class="form-control" style="border: none; background: transparent;">
                {if $relatedJob->getPercentageCompletion () neq NULL}{$relatedJob->getPercentageCompletion ()}{/if}
            </span>
        </div>
    </td>
    <td style="vertical-align: middle; text-align: right;">
        <div class="input-group" style="width: 100%;">
            <span id="advance-{$idRow}" class="form-control" style="border: none; background: transparent;">
                {if $relatedJob->getProjectProgress () neq NULL}{$relatedJob->getProjectProgress ()}{/if}
            </span>
        </div>
    </td>
    <td style="vertical-align: middle; text-align: right;">
        <div class="input-group" style="width: 100%;">
            <span id="work_estimated_cost-{$idRow}" class="form-control" style="border: none; background: transparent;">
                {if $relatedJob->getWorkEstimatedCost () neq NULL}{$relatedJob->getWorkEstimatedCost ()}{/if}
            </span>
        </div>
    </td>
    <td style="vertical-align: middle; text-align: right;">
        <div class="input-group" style="width: 100%;">
            <span id="cost_work_performed-{$idRow}" class="form-control"
                style="border: none; background: transparent; text-align: right;">
                {if $relatedJob->getCostWorkPerformed () neq NULL}{$relatedJob->getCostWorkPerformed ()}{/if}
            </span>
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
            <span id="work_situation-{$idRow}" class="form-control"
                style="border: none; background-color: {$bgColor}; color: white !important; {if $bgColor neq 'transparent'}font-weight: bold; border: 2px solid {$bgColor};{/if}"
                {if $tooltipText neq ''}title="{$tooltipText}" data-toggle="tooltip" data-placement="top" {/if}>
                {if $relatedJob->getWorkSituation () neq NULL}{$relatedJob->getWorkSituation ()}{/if}
            </span>
        </div>
    </td>
</tr>