<tr class="tabla-field-row" valign="top" id="tr-row-{$idRow}" data-num-format="{$NUMBERING_FORMAT}"
    data-combined-condition="{$relatedTask['combined_condition']}">
    {* Titulo, asunto   *}
    {assign var="stringLength" value=($relatedTask['task']|strlen)/45}
    <td class="step-no-manual text-left" {block name="col_0"}{/block}>
        <a href="javascript:void(0)"
                onclick="if (window.WorkTaskActivityModal && typeof window.WorkTaskActivityModal.openView === 'function') { window.WorkTaskActivityModal.openView ({$relatedTask['taskId']}); } return false;"
                title="{if $relatedTask['types'] eq 'Job'}Ver reportes de avance del trabajo{else}Ver información de la tarea{/if}">{$relatedTask['task_title']}</a>
    </td>
    {* Descripción   *}

    <td class="step-no-manual text-left" {block name="col_1"}{/block}>
        {if $relatedTask['types'] eq 'Job'}
            {if $relatedTask['task']|strlen > 255}
                {$relatedTask['task']|truncate:255:"..."}
            {else}
                {$relatedTask['task']}
            {/if}
        {else}
            <a href="javascript:void(0)"
                onclick="if (window.WorkTaskActivityModal && typeof window.WorkTaskActivityModal.openView === 'function') { window.WorkTaskActivityModal.openView ({$relatedTask['taskId']}); } return false;"
                title="Ver información de la tarea">
                {if $relatedTask['task']|strlen > 255}
                    {$relatedTask['task']|truncate:255:"..."}
                {else}
                    {$relatedTask['task']}
                {/if}
            </a>
        {/if}
        <input type="hidden" name="projec_task[taskId][]" value="{$relatedTask['taskId']}">
    </td>
    <td {block name="col_3"}{/block}>
        <div class="input-group" style="width: 100%;">
            <span id="start_date-{$idRow}">
                {if $relatedTask['start_date'] neq NULL}{$relatedTask['start_date']}{/if}
            </span>
        </div>
    </td>
    <td {block name="col_4"}{/block}>
        <div class="input-group" style="width: 100%;">
            <span id="due_date-{$idRow}">
                {if $relatedTask['due_date'] neq NULL}{$relatedTask['due_date']}{/if}
            </span>
        </div>
    </td>
    <td {block name="col_status"}{/block}>
        <div class="input-group" style="width: 100%;">
            <span id="status-{$idRow}">
                {if $relatedTask['status'] neq NULL}
                    {foreach $AVAILABLE_EVENT_STATUSES as $key => $status}
                        {if $key eq $relatedTask['status']} {$status} {/if}
                    {/foreach}
                {/if}
            </span>
        </div>
    </td>
    <td {block name="col_6"}{/block} style="text-align: center; vertical-align: middle;">
        {if $AVAILABLE_SYSTEM_USERS neq NULL}
            <div class="input-group"
                style="width: 100%; display: flex; justify-content: center; align-items: center; height: 100%;">
                <span id="assigned-{$idRow}">
                    {foreach $AVAILABLE_SYSTEM_USERS as $systemUser}
                        {if $systemUser->getId() eq $relatedTask['assigned']}
                            {if $systemUser->getImageUri() neq NULL}
                                <figure class="center-block"
                                    style="border-radius: 50%; height: 40px; overflow: hidden; width: 40px; margin: 0 auto;">
                                    <img class="img-responsive img-circle" alt="{$systemUser->getFirstName()}"
                                        title="{$systemUser->getFirstName()} {$systemUser->getLastName()}"
                                        src="{$systemUser->getImageUri()}">
                                </figure>
                            {else}
                                <div style="text-align: center; line-height: 40px;">{$systemUser->getFirstName()}
                                    {$systemUser->getLastName()}</div>
                            {/if}
                        {/if}
                    {/foreach}
                </span>
            {else}
                <span style="">&nbsp;</span>
            {/if}
    </td>
    <td {block name="col_supplier"}{/block}>
        <div class="input-group" style="width: 100%;">
            <span id="supplier-{$idRow}">
                {if $relatedTask['supplierName'] neq NULL}
                    <a href="index.php?module=proveedores&action=DetailView&record={$relatedTask['supplierId']}"
                        title="Ver proveedor ejecutor">
                        {$relatedTask['supplierName']}
                    </a>
                {else}
                    <span class="text-muted">--</span>
                {/if}
            </span>
        </div>
    </td>
    <td {block name="col_2b"}{/block}>
        <div class="input-group" style="width: 100%;">
            <span id="unit-{$idRow}">
                {if $relatedTask['estimated_time_unit'] neq NULL}{$relatedTask['estimated_time_unit']}{else}Hora{/if}
            </span>
        </div>
    </td>
    <td {block name="col_5"}{/block}>
        <div class="input-group" style="width: 100%; text-align:right">
            <span id="duration-{$idRow}" style="display:block; width:100%; text-align:right;">
                {if $relatedTask['duration'] neq NULL}{$relatedTask['duration']}{/if}
            </span>
        </div>
    </td>
    <td {block name="col_5b"}{/block}>
        <div class="input-group" style="width: 100%; text-align:right">
            <span id="estimated-cost-{$idRow}" style="display:block; width:100%; text-align:right;">
                {if $relatedTask['estimated_cost'] neq NULL}{$relatedTask['estimated_cost']}{/if}
            </span>
        </div>
    </td>
    <td {block name="col_pwf"}{/block}>
        <div class="input-group" style="width: 100%; text-align:center;">
            <span id="progress-weighting-factor-{$idRow}" style="display:block; width:100%; text-align:center;">
                {if $relatedTask['progress_weighting_factor'] neq NULL}{if $NUMBERING_FORMAT eq 'EUROPEAN_FORMAT'}{$relatedTask['progress_weighting_factor']|number_format:2:',':'.'}{else}{$relatedTask['progress_weighting_factor']|number_format:2:'.':','}{/if}{/if}
            </span>
        </div>
    </td>
    <td {block name="col_reported"}{/block} class="task-progress-cell" data-field="reported_hours">
        <div class="input-group" style="width: 100%; text-align:right;">
            <span id="reported-{$idRow}" style="display:block; width:100%; text-align:right;">
                {if $relatedTask['reported_hours'] neq NULL}{$relatedTask['reported_hours']}{else}0{/if}
            </span>
        </div>
    </td>
    <td {block name="col_costreported"}{/block} class="task-progress-cell" data-field="reported_cost">
        <div class="input-group" style="width: 100%; text-align:right">
            <span id="reported-cost-{$idRow}" style="display:block; width:100%; text-align:right;">
                {if $relatedTask['reported_cost'] neq NULL}{$relatedTask['reported_cost']}{else}0{/if}
            </span>
        </div>
    </td>
    <td {block name="col_7"}{/block} class="task-progress-cell" data-field="progress">
        <div class="input-group" style="width: 100%; text-align:right;">
            <span id="progress-{$idRow}" style="display:block; width:100%; text-align:right;">
                {if $relatedTask['progress'] neq NULL}{$relatedTask['progress']}{/if}
            </span>
        </div>
    </td>
    <td {block name="col_work_progress"}{/block} class="task-progress-cell" data-field="work_progress">
        <div class="input-group" style="width: 100%; text-align:center;">
            <span id="work-progress-{$idRow}" style="display:block; width:100%; text-align:center;">
                {assign var="pwf_raw" value=$relatedTask['progress_weighting_factor']|default:0}
                {assign var="progress_raw" value=$relatedTask['progress']|default:0}
                {assign var="pwf_value" value=$pwf_raw|replace:',':'.'}
                {assign var="progress_value" value=$progress_raw|replace:',':'.'}
                {math equation="(pwf * progress) / 100" pwf=$pwf_value progress=$progress_value assign="work_progress"}
                {if $NUMBERING_FORMAT eq 'EUROPEAN_FORMAT'}{$work_progress|number_format:2:',':'.'}{else}{$work_progress|number_format:2:'.':','}{/if}
            </span>
        </div>
    </td>
    {assign var=combinedConditionVal value=$relatedTask['combined_condition']|trim|default:''}
    {assign var=bgColor value='transparent'}
    {assign var=textColor value='inherit'}
    {assign var=tooltipText value=''}
    {assign var=displayValue value='--'}

    {* Si hay un valor, procesarlo *}
    {if $combinedConditionVal neq ''}
        {* Si es una clave de traducción (PICK_ACTIVITY_*), traducirla *}
        {if $combinedConditionVal|substr:0:14 eq 'PICK_ACTIVITY_'}
            {assign var=displayValue value=$combinedConditionVal|@getTranslatedString:'Calendar'}
        {else}
            {assign var=displayValue value=$combinedConditionVal}
        {/if}
    {/if}

    {if $combinedConditionVal eq 'PICK_ACTIVITY_ON_TIME_ON_BUDGET'}
        {assign var=bgColor value='#388E3C'}
        {assign var=textColor value='white'}
        {assign var=tooltipText value='El trabajo cumple el cronograma y el presupuesto.'}
    {elseif $combinedConditionVal eq 'PICK_ACTIVITY_ON_TIME_OVER_BUDGET'}
        {assign var=bgColor value='#7B1FA2'}
        {assign var=textColor value='white'}
        {assign var=tooltipText value='Se está cumpliendo el tiempo, pero a un costo mayor.'}
    {elseif $combinedConditionVal eq 'PICK_ACTIVITY_DELAYED_ON_BUDGET'}
        {assign var=bgColor value='#F57C00'}
        {assign var=textColor value='white'}
        {assign var=tooltipText value='Estamos lentos, pero aún no nos hemos pasado del presupuesto.'}
    {elseif $combinedConditionVal eq 'PICK_ACTIVITY_DELAYED_OVER_BUDGET'}
        {assign var=bgColor value='#D32F2F'}
        {assign var=textColor value='white'}
        {assign var=tooltipText value='El peor escenario: vamos tarde y ya gastamos más de lo previsto.'}
    {/if}
    <td {block name="col_situation"}{/block} class="task-situation-cell" data-field="combined_condition"
        style="text-align:center; {if $bgColor neq 'transparent'}background-color: {$bgColor};{/if}">
        <div class="input-group">
            <span id="combined-condition-{$idRow}"
                style="{if $bgColor neq 'transparent'}color: {$textColor} !important;{/if}"
                {if $tooltipText neq ''}title="{$tooltipText}" data-toggle="tooltip" data-placement="top" {/if}>
                {$displayValue|trim|default:'--'}
            </span>
        </div>
    </td>
    <!--<td {block name="col_help"}{/block}>
        {if $relatedTask['howToId'] neq NULL}
            <a class="btn btn-link btn-xs" data-width="950" data-toggle="lightbox" data-parent="" data-gallery="remoteload"
                data-title="¡Aprende como!"
                href="index.php?module=Calendar&action=AjaxDetailViewUtils&record={$relatedTask['taskId']}&function=GET-HOW-TO&Ajax=true"
                title="¡Aprende como!"><i class="bi bi-question-square"></i></a>
        {/if}
    </td>-->
</tr>