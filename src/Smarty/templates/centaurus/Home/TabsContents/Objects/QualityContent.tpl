{extends file='Home/TabsContents/Base/QualityContentLayOut.tpl'}
{math equation= (88/$TOTAL_STEPS_NAME) assign= "tdWidth"}
{block name="steps_num"}{$TOTAL_STEPS_NAME}{/block}

{block name="steps_names"}
    {if $PROCESS_ACCORDING_QUALITY neq NULL}
        <td style="text-align: center;vertical-align: top; width: 12%">{$MOD['LBL_NUMBER_CASES']}</td>
        {foreach $STEPS_NAME as $stepsName}
            <td style="text-align: center;vertical-align: top; width: {$tdWidth}%">{$stepsName}</td>
        {/foreach}
    {else}
        <tr>
            <td colspan="2" style="text-align: center;vertical-align: top;">{$MOD['LBL_NO_CASES_FOUND']}</td>
        </tr>
    {/if}
{/block}
{block name="steps_data"}
    {if $PROCESS_ACCORDING_QUALITY neq NULL}
        {foreach $CASE_NUMBERS as $caseNumber => $caseId}
            <tr>
                <td style="text-align: center;vertical-align: top; width: 12%">
                    <a title="Ver detalles del caso"
                       target="_blank"
                       href="index.php?module=process_cases&action=DetailView&record={$caseId}">{$caseNumber}</a>
                </td>
                {foreach $PROCESS_ACCORDING_QUALITY as $processQuality}
                    {if $processQuality['case_number'] neq $caseNumber}{continue}{/if}
                    <td style="text-align: center;vertical-align: top; width: {$tdWidth}%">
                        {if $processQuality['due_date'] neq NULL}
                            <a
                                    {if $processQuality['related_module'] neq NULL}
                                        title="Ver detalles de la ejecución del paso"
                                        href="index.php?module={$processQuality['related_module']}&action=DetailView&record={$processQuality['related_module']|crmentity_id:$processQuality['case_number']:$ADB}"
                                    {else}
                                        title="Paso manual sin registro de datos en el sistema"
                                    {/if}
                               target="_blank">
                                <i class="fa fa-circle fa-2x"
                                   aria-hidden="true"
                                   style="color: {$STEPS_COLOR_QUALITY[$processQuality['quality_valuation']][$processQuality['quality_time']]}"></i>
                            </a>
                        {else}
                            &nbsp;
                        {/if}
                    </td>
                {/foreach}
            </tr>
        {/foreach}
    {/if}
{/block}