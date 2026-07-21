{extends file='modules/DailyReport/base/AchievementsLayout.tpl'}
{assign var='totalInformation' value=0}

{block name="id_tbody_table"}tbody-other_information-{/block}

{block name="header_achievements_day_table"}
    <tr style="vertical-align: top">
        <td style="width: 13.5%">
            <span style="">Tipo de información</span>
        </td>
        <td style="width: 31.5%">
            <span style="">Información</span>
        </td>
        <td style="width: 45%">
            <span style="">Descripción</span>
        </td>
        <td class="text-center" style="width: 10%">Acciones</td>
    </tr>
{/block}

{block name="body_achievements_day_table"}
    {if $OTHER_INFORMATION neq NULL}
        {assign var='totalInformation' value=$OTHER_INFORMATION|@count}
        {foreach $OTHER_INFORMATION as $otherInformation}
            {math equation= rand() assign= "idRow"}
            {include file='modules/DailyReport/Objects/row_other_information_edit.tpl'}
            {assign var="hasOtherInformation" value='YES'}
        {/foreach}
    {else}
        <tr>
            <td colspan="4" style="text-align: center"><p class="text-center">No hay información adicional</p></td>
        </tr>
        {if $hasOtherInformation neq 'NO'}
            <tr>
                <td colspan="4" style="text-align: center"><p class="text-center">&nbsp;</p></td>
            </tr>
        {/if}
    {/if}
{/block}

{block name="add-achievements_day-row"}
    <td colspan="4" class="text-center">
        <button type="button"
                data-sequence="{$totalInformation}"
                data-template="other_information-template-{$idAchievements}"
                data-id-linkage="{$idAchievements}" class="btn btn-primary"
                onclick="DailyReportUtils.addRowToTable (this, 'tbody-other_information-{$idAchievements}', '{$idAchievements}');">
            <i class="fa fa-plus"></i>
        </button>
    </td>
{/block}

{block name="script_template"}
    <script type="text/html" id="other_information-template-{$idAchievements}">
        {include file='modules/DailyReport/Objects/other_information_edit.tpl'}
    </script>
    <script type="text/html" id="other_information-colspan-template-{$idAchievements}">
        <tr>
            <td colspan="4" style="text-align: center"></td>
        </tr>
    </script>
{/block}