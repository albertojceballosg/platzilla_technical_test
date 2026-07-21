{strip}
{* Función para formatear números según configuración del usuario *}
{function name=formatNumber value=$fieldValue}
    {* Convertir a número si es string numérico (puede tener punto decimal de BD) *}
    {assign var="numericValue" value=$value|replace:',':''}
    {if $numericValue !== '' && $numericValue !== null && is_numeric($numericValue)}
        {if $current_user->numbering_format == 'EUROPEAN_FORMAT'}
            {$numericValue|floatval|number_format:2:',':'.'}
        {else}
            {$numericValue|floatval|number_format:2:'.':','}
        {/if}
    {else}
        {$value}
    {/if}
{/function}

    <td width="{$v.proportional_width}%" {if $v.uitype eq 99 } align="center"{/if} class="grid-cell-compact {if $calculatedClass neq ""}{$fieldname}_observed{/if}" id="td_{$fieldname}_{$v.name}_Campo{$numrowtr}">
        {if $v.uitype eq 1}
            {if $swDetailView}
                <div class="grid-input-compact grid-text-display" title="{$fieldValue}">
                    {if is_numeric($fieldValue)}
                        {call formatNumber value=$fieldValue}
                    {else}
                        {$fieldValue}
                    {/if}
                </div>
            {else}
                <input autocomplete="off" numrow="{$numrowtr}" value="{$fieldValue}" name="{$v.name}[]" id="{$v.name}{$numrowtr}" {if $v.filter_field neq '' } oninput="{$fieldname}_getFilter(this)" {/if} class="grid-input-compact" type="text">
            {/if}
        {elseif $v.uitype eq 7 }
            {if $swDetailView}
                <div class="grid-input-compact grid-text-display numericvalidate" title="{$fieldValue}" style="text-align: right;">
                    {call formatNumber value=$fieldValue}
                </div>
            {else}
                {* Formatear el valor inicial para mostrar con separadores de miles *}
                {if $fieldValue neq '' && is_numeric($fieldValue)}
                    {if $current_user->numbering_format eq 'EUROPEAN_FORMAT'}
                        {assign var="displayValue" value=$fieldValue|number_format:2:',':'.'}
                    {else}
                        {assign var="displayValue" value=$fieldValue|number_format:2:'.':','}
                    {/if}
                {else}
                    {assign var="displayValue" value=$fieldValue}
                {/if}
                <input autocomplete="off" onkeyup="validateDecimal32Generalsinporcentaje('{$v.name}{$numrowtr}')" {if $v.filter_field neq '' } oninput="{$fieldname}_getFilter(this)" {/if} numrow="{$numrowtr}" value="{$displayValue}" name="{$v.name}[]" id="{$v.name}{$numrowtr}" class="grid-input-compact numericvalidate" type="text" placeholder="{if $current_user->numbering_format eq 'EUROPEAN_FORMAT'}9.999,99{else}9,999.99{/if}" data-number-format="{$current_user->numbering_format|default:'AMERICAN_FORMAT'}" style="text-align: right;">
            {/if}
        {elseif $v.uitype eq 9 }
            {if $swDetailView}
                <div class="grid-input-compact grid-text-display percentvalidate" title="{$fieldValue}" style="text-align: right;">
                    {call formatNumber value=$fieldValue}
                </div>
            {else}
                {* Formatear el valor inicial para porcentajes *}
                {if $fieldValue neq '' && is_numeric($fieldValue)}
                    {if $current_user->numbering_format eq 'EUROPEAN_FORMAT'}
                        {assign var="displayValue" value=$fieldValue|number_format:2:',':'.'}
                    {else}
                        {assign var="displayValue" value=$fieldValue|number_format:2:'.':','}
                    {/if}
                {else}
                    {assign var="displayValue" value=$fieldValue}
                {/if}
                <input autocomplete="off" onkeyup="validateDecimal22General('{$v.name}{$numrowtr}')" {if $v.filter_field neq '' } oninput="{$fieldname}_getFilter(this)" {/if} numrow="{$numrowtr}" value="{$displayValue}" name="{$v.name}[]" id="{$v.name}{$numrowtr}" class="grid-input-compact percentvalidate" type="text" placeholder="{if $current_user->numbering_format eq 'EUROPEAN_FORMAT'}99,99%{else}99.99%{/if}" data-number-format="{$current_user->numbering_format|default:'AMERICAN_FORMAT'}" style="text-align: right;">
            {/if}
        {elseif $v.uitype eq 71 }
            {if $swDetailView}
                <div class="grid-input-compact grid-text-display currencyvalidate" title="{$fieldValue}" style="text-align: right;">
                    {call formatNumber value=$fieldValue}
                </div>
            {else}
                {* Formatear el valor inicial para moneda *}
                {if $fieldValue neq '' && is_numeric($fieldValue)}
                    {if $current_user->numbering_format eq 'EUROPEAN_FORMAT'}
                        {assign var="displayValue" value=$fieldValue|number_format:2:',':'.'}
                    {else}
                        {assign var="displayValue" value=$fieldValue|number_format:2:'.':','}
                    {/if}
                {else}
                    {assign var="displayValue" value=$fieldValue}
                {/if}
                <input autocomplete="off" numrow="{$numrowtr}" value="{$displayValue}" name="{$v.name}[]" id="{$v.name}{$numrowtr}" class="grid-input-compact currencyvalidate" type="text" placeholder="{if $current_user->numbering_format eq 'EUROPEAN_FORMAT'}9.999,99{else}9,999.99{/if}" data-number-format="{$current_user->numbering_format|default:'AMERICAN_FORMAT'}" style="text-align: right;">
            {/if}
        {elseif $v.uitype eq 72 }
            {if $swDetailView}
                <div class="grid-input-compact grid-text-display currencyvalidate" title="{$fieldValue}" style="text-align: right;">
                    {call formatNumber value=$fieldValue}
                </div>
            {else}
                {* Formatear el valor inicial para moneda *}
                {if $fieldValue neq '' && is_numeric($fieldValue)}
                    {if $current_user->numbering_format eq 'EUROPEAN_FORMAT'}
                        {assign var="displayValue" value=$fieldValue|number_format:2:',':'.'}
                    {else}
                        {assign var="displayValue" value=$fieldValue|number_format:2:'.':','}
                    {/if}
                {else}
                    {assign var="displayValue" value=$fieldValue}
                {/if}
                <input autocomplete="off" numrow="{$numrowtr}" value="{$displayValue}" name="{$v.name}[]" id="{$v.name}{$numrowtr}" class="grid-input-compact currencyvalidate" type="text" placeholder="{if $current_user->numbering_format eq 'EUROPEAN_FORMAT'}9.999,99{else}9,999.99{/if}" data-number-format="{$current_user->numbering_format|default:'AMERICAN_FORMAT'}" style="text-align: right;">
            {/if}
        {elseif $v.uitype eq 17 }
            {if $swDetailView}
                <div class="grid-input-compact grid-text-display" title="{$fieldValue}">{$fieldValue}</div>
            {else}
                <input autocomplete="off" numrow="{$numrowtr}" value="{$fieldValue}" name="{$v.name}[]" id="{$v.name}{$numrowtr}" class="grid-input-compact urlvalidate" type="text">
            {/if}
        {elseif $v.uitype eq 5 }
            <div class="grid-date-compact">
                {* Determinar el formato de fecha según preferencias del usuario *}
                {if $current_user->date_format eq 'dd-mm-yyyy'}
                    {assign var="smartyDateFormat" value="%d-%m-%Y"}
                    {assign var="dateFormatPlaceholder" value="DD-MM-AAAA"}
                {elseif $current_user->date_format eq 'dd/mm/yyyy'}
                    {assign var="smartyDateFormat" value="%d/%m/%Y"}
                    {assign var="dateFormatPlaceholder" value="DD/MM/AAAA"}
                {elseif $current_user->date_format eq 'mm-dd-yyyy'}
                    {assign var="smartyDateFormat" value="%m-%d-%Y"}
                    {assign var="dateFormatPlaceholder" value="MM-DD-AAAA"}
                {elseif $current_user->date_format eq 'mm/dd/yyyy'}
                    {assign var="smartyDateFormat" value="%m/%d/%Y"}
                    {assign var="dateFormatPlaceholder" value="MM/DD/AAAA"}
                {elseif $current_user->date_format eq 'yyyy/mm/dd'}
                    {assign var="smartyDateFormat" value="%Y/%m/%d"}
                    {assign var="dateFormatPlaceholder" value="AAAA/MM/DD"}
                {else}
                    {assign var="smartyDateFormat" value="%Y-%m-%d"}
                    {assign var="dateFormatPlaceholder" value="AAAA-MM-DD"}
                {/if}
                <input type="text" id="jscal_field_{$v.name}{$numrowtr}" name="{$v.name}[]" value="{if ($fieldValue eq 'create') || ($fieldValue eq 'edit') }{$smarty.now|date_format:$smartyDateFormat}{else}{$fieldValue}{/if}" class="grid-input-compact grid-date-input" readonly="readonly" placeholder="{$dateFormatPlaceholder}" />
                <i class="fa fa-calendar grid-date-icon" id="jscal_trigger_{$v.name}{$numrowtr}"></i>
                {if (!$swDetailView) && ($v.defaultvalue neq 'create') }
                <script type="text/javascript">
                    jQuery ('#jscal_field_{$v.name}{$numrowtr}').datepicker ( {literal}{ format: (typeof gUserDateFormat !== 'undefined') ? gUserDateFormat : 'yyyy-mm-dd', language: 'es', weekStart: 1 } {/literal});
                </script>
                {/if}
                <input type="hidden" name="dateFormat" value="{$dateFormat}" class="grid-input-compact" />
            </div>

        {elseif $v.uitype eq 10 }

        {if $v.relmodule|strpos:"@" !== false}
            {assign var=relateModuleValues value="@"|explode:$v.relmodule}
            {assign var="title" value=$relateModuleValues[2]}
            {assign var="module" value=$relateModuleValues[2]}
            {assign var="btnTitle" value=$relateModuleValues[1]}
            {assign var="relatedModule" value=$relateModuleValues[0]}
            {assign var="lista" value='lista'}
            {assign var="modulo" value='modulo'}
            {assign var="hideToMe" value=""}
        {else}
            {assign var="title" value=$v.relmodule}
            {assign var="module" value=$v.relmodule}
            {assign var="hideToMe" value="hide"}
        {/if}
        {if $fieldValue|strpos:"@" !== false}
            {assign var=relatedEditValues value="@"|explode:$fieldValue}
            {assign var="moduloValue" value=$relatedEditValues[0]}
        {else}
            {assign var="moduloValue" value=$fieldValue}
        {/if}
        {if !($fieldValue|is_array)}
            <div class="grid-reference-compact">
                {if $swDetailView }
                    <div class="grid-input-compact grid-text-display" title="{$moduloValue}">{$moduloValue}</div>
                {else}
                    <input type="hidden" id="{$v.name}{$numrowtr}" value="{$moduloValue}" class="for-filter" />
                    <input  type="text" id="edit_{$v.name}{$numrowtr}_display"  name="{$v.name}[]" value="{$moduloValue}" class="grid-input-compact grid-reference-input" readonly="readonly" />
                    <button type="button" class="grid-reference-btn" data-current-module="" data-display-field-id="edit_{$v.name}{$numrowtr}_display" data-field-id="{$v.name}{$numrowtr}" data-referenced-module="{$module}" data-title="{$title}" onclick="RelatedModuleModalUtils.openModal (this);">
                        <i class="fa fa-plus-circle"></i>
                    </button>
                    <button type="button" class="grid-reference-clear" onClick="var fieldContainer = jQuery (this).closest ('.grid-reference-compact'); fieldContainer.find ('.grid-reference-input').val (''); return false;">
                        <i class="fa fa-eraser"></i>
                    </button>
                {/if}
                {if isset($btnTitle) && !$swDetailView}
                    <button type="button" class="btn btn-success btn-xs" title="Añadir {$btnTitle}" data-current-module="" data-display-field-id="{$v.name}{$numrowtr}_{$btnTitle}_display" data-field-id="{$v.name}{$numrowtr}_{$btnTitle}" data-referenced-module="{$relatedModule}" data-title="{$btnTitle}"  onclick="RelatedModuleModalUtils.openModal (this);" >
                        <i class="fa fa-plus" style="padding-right: 0.2em;"></i> {$btnTitle}
                    </button>
                {/if}
            </div>
        {/if}
        {if isset($btnTitle)}
                {if isset($relatedEditValues)}
                        {section name=listId start=1 loop=$relatedEditValues}
                            <div class="grid-reference-compact" style="margin-top: 2px;">
                                <input  type="text"  name="{$v.name}[][{$btnTitle}]" value="{$relatedEditValues[listId]}" title="{$relatedEditValues[listId]}" class="grid-input-compact"  readonly />
                                {if ! $swDetailView }
                                <button type="button" class="btn btn-danger btn-sm removeListElemet" onclick="removeListFromEdit(this)" ><i class="fa fa-minus" aria-hidden="true"></i>
                                </button>
                                {/if}
                            </div>
                        {/section}
                {/if}
                <div id="related-list-{$btnTitle}_template_{$numrowtr}" class="grid-reference-compact hide" style="margin-top: 1px">
                    <input type="hidden" id="{$v.name}{$numrowtr}_{$btnTitle}"  value="" class="for-filter" />
                    <input  type="text" id="{$v.name}{$numrowtr}_{$btnTitle}_display" name="{$v.name}[][{$btnTitle}]" value="" class="grid-input-compact"  readonly />
                    <button type="button" class="btn btn-danger btn-sm removeButton"><i class="fa fa-minus" aria-hidden="true"></i>
                    </button>
                </div>
        {/if}
        {elseif $v.uitype eq 15 }
            {if $swDetailView }
                {if $fieldValue eq ''}
                    {assign var="fieldValue" value= '0'}
                {/if}
                {foreach from=$v.values key=k item=v}
                    {if $fieldValue == $k }
                     <div class="grid-input-compact grid-text-display" title="{if $v eq 'Seleccionar'}deshabilitado{else}{$v}{/if}">{if $v eq 'Seleccionar'}deshabilitado{else}{$v}{/if}</div>
                    {/if}
                {/foreach}
            {else}
                <select name="{$v.name}[]" id="{$v.name}{$numrowtr}" select-action='{$v.action_field}'  {if $swDetailView } disabled {/if} class="grid-input-compact action_grid">
                    {html_options options=$v.values selected=$fieldValue }
                </select>
            {/if}

        {elseif $v.uitype eq 33 }
            {if $swDetailView}
                <div class="grid-input-compact grid-text-display" title="{$fieldValue}">{$fieldValue}</div>
            {else}
                {assign var=custOptions value=","|explode:$v.values}
                <select select multiple name="{$v.name}[]" id="{$v.name}{$numrowtr}" class="grid-input-compact">
                    {html_options options=$v.values selected=$fieldValue }
                </select>
            {/if}
        {elseif $v.uitype eq 6 }
            {if $swDetailView}
                <div class="grid-input-compact grid-text-display" title="{$fieldValue}">{$fieldValue}</div>
            {else}
                <input name="{$v.name}[]" id="jscal_field_{$v.name}_Campo{$numrowtr}" type="text" class="grid-input-compact" value="{$fieldValue}">
                <script type="text/javascript" id='massedit_calendar_{$v.name}_Campo{$numrowtr}'>
                    Calendar.setup ({
                        inputField : "jscal_field_{$v.name}_Campo{$numrowtr}", ifFormat : "'.parse_calendardate($app_strings['NTC_DATE_FORMAT']).'", showsTime : false, button : "jscal_trigger_{$v.name}_Campo{$numrowtr}", singleClick : true, step : 1
                    })
                </script>
            {/if}
        {elseif $v.uitype eq 21 }
            {if $swDetailView}
                <div class="grid-input-compact grid-text-display" title="{$fieldValue}">{$fieldValue}</div>
            {else}
                <textarea name="{$v.name}[]" id="{$v.name}{$numrowtr}" {if $v.filter_field neq '' } oninput="{$fieldname}_getFilter(this)" {/if} class="grid-input-compact" rows="2">{$fieldValue}</textarea>
            {/if}
        {elseif $v.uitype eq 56 }
        {if $swDetailView }
            <div class="grid-checkbox-compact">
                <span class="grid-input-compact">{if $fieldValue eq 'Si'}<i class="fa fa-check-square"></i>{else}<i class="fa fa-minus-square"></i>{/if} {$fieldValue}</span>
            </div>
        {else}
            <input type="checkbox" value ="{if $fieldValue neq ''}{$fieldValue}{else}1{/if}" name="chk_{$v.name}[]" id="{$v.name}{$numrowtr}"  class="grid-input-compact action_chk"  data-action={$v.action_field}     {if $fieldValue eq 'Si'} checked="checked"  {/if}/>
            <input type="hidden" value ="{if $fieldValue neq ''}{$fieldValue}{else}No{/if}" name="{$v.name}[]" id="chk_{$v.name}{$numrowtr}" />
         {/if}
        {elseif $v.uitype eq 2204 }
            {if $swDetailView}
                <div class="grid-input-compact grid-text-display" title="{$fieldValue}" style="text-align: right;">
                    {call formatNumber value=$fieldValue}
                </div>
            {else}
                {* Formatear el valor inicial del campo calculado con separadores de miles *}
                {if $fieldValue neq '' && is_numeric($fieldValue)}
                    {if $current_user->numbering_format eq 'EUROPEAN_FORMAT'}
                        {assign var="displayValue" value=$fieldValue|number_format:2:',':'.'}
                    {else}
                        {assign var="displayValue" value=$fieldValue|number_format:2:'.':','}
                    {/if}
                {else}
                    {assign var="displayValue" value=$fieldValue}
                {/if}
                <input autocomplete="off" numrow="{$numrowtr}" value="{$displayValue}" name="{$v.name}[]" id="{$v.name}{$numrowtr}"
                       class="grid-input-compact calculated-{$v.name}-table" data-number-format="{$current_user->numbering_format|default:'AMERICAN_FORMAT'}" readonly type="text" style="text-align: right;">
            {/if}
        {elseif $v.uitype eq 99 }
            {if ! $swDetailView }
                <button class="btn btn-danger btn-sm" type="button" onclick="deleteRow{$fieldname}(this);return false;"><i class="fa fa-trash-o"></i></button>
            {/if}
        {elseif $v.uitype eq 2202 }
           <p class="grid-input-compact">{$v.data_field[$j]}</p>
        {elseif $v.uitype eq 4096 }
            <div class="attachments-field grid-attachments-compact">
                {if ! isset($ATTACHMENTS) && ! $swDetailView}
                    <div class="grid-attachment-zone">
                        <input type="file" id="{$v.name}{$numrowtr}"  name="{$v.name}[]"  multiple="multiple" onchange="AttachmentsUtils.addAttachments (event || window.event);" class="grid-file-input" />
                        <span class="grid-attachment-label">Adjunto</span>
                        <ul class="attachments-container grid-attachment-list" data-field-name="{$v.name}[]" data-maximum-file-size="2">
                        </ul>
                    </div>
                {else}
                    <div class="grid-attachment-zone">
                        <input type="file" id="{$v.name}{$numrowtr}"  name="{$v.name}[]" multiple="multiple" onchange="AttachmentsUtils.addAttachments (event || window.event);" class="grid-file-input {if $swDetailView } hide {/if}" />
                        <span class="grid-attachment-label {if $swDetailView } hide {/if}">Adjunto</span>
                        <ul class="attachments-container grid-attachment-list" data-field-name="{$v.name}[]" data-maximum-file-size="2">
                            {if (!empty ($ATTACHMENTS))}
                                {foreach $ATTACHMENTS_DATA as $row}
                                    {if $row.tableRow eq ($numrowtr)}
                                    {foreach $ATTACHMENTS as $attachment}
                                       {if $row.filename eq  $attachment.name}
                                        <li class="attachment grid-attachment-item">
                                            <button type="button" class="btn btn-close {if $swDetailView } hide {/if}" onclick="AttachmentsUtils.deleteAttachment (this);">X</button>
                                            <div class="attachment-container">
                                                <a href="{$attachment.uri}" title="{$attachment.name}" target="_blank">
                                                    <span class="attachment-name">{$attachment.name}</span><span class="attachment-size"> ({number_format ($attachment.size, 2, '.', '')} KB)</span>
                                                </a>
                                            </div>
                                            <input type="hidden" name="{$v.name}[][data]" value="{$row.data}" class="attachment-data" />
                                            <input type="hidden" name="{$v.name}[][filename]" value="{$row.tableRow}@{$row.filename}" class="attachment-filename" />
                                            {$row.filename = 'nul'}
                                        </li>
                                       {/if}
                                    {/foreach}
                                    {/if}
                                {/foreach}
                            {/if}
                        </ul>
                    </div>
                {/if}
            </div>
        {/if}
    </td>
{/strip}
