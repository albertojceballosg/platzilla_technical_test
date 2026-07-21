{extends file='modules/DailyReport/base/AchievementsLayout.tpl'}
{assign var='totalAchievements' value=0}
{assign var="hasAchievements" value='NO'}

{block name="id_tbody_table"}tbody-achievements_day-{/block}

{block name="header_achievements_day_table"}
    <tr style="vertical-align: top;">
        <td style="vertical-align:middle; width: 36%">
            <span style="">Título</span>
        </td>
        <td style="vertical-align:middle;width: 54%;">
            <span style="">Descripción</span>
        </td>
        <td class="text-center;" style="width: 10%">Acciones</td>
    </tr>
{/block}

{block name="body_achievements_day_table"}
    {if $ACHIEVEMENTS neq NULL}
        {assign var='totalAchievements' value=$ACHIEVEMENTS|@count}
        {foreach $ACHIEVEMENTS as $achievements_day}
            {math equation= rand() assign= "idRow"}
            {include file='modules/DailyReport/Objects/row_achievements_day_edit.tpl'}
            {assign var="hasAchievements" value='YES'}
        {/foreach}
    {else}
        <tr>
            <td colspan="3" style="text-align: center"><p class="text-center">No hay logros reportados</p></td>
        </tr>
        {if $hasAchievements neq 'NO'}
            <tr>
                <td colspan="3" style="text-align: center"><p class="text-center">&nbsp;</p></td>
            </tr>
        {/if}
    {/if}
{/block}

{block name="add-achievements_day-row"}
    <td colspan="3" class="text-center">
        <button type="button"
                data-sequence="{$totalAchievements}"
                data-template="achievements_day-template-{$idAchievements}"
                data-id-linkage="{$idAchievements}" class="btn btn-primary"
                onclick="DailyReportUtils.addRowToTable (this, 'tbody-achievements_day-{$idAchievements}', '{$idAchievements}');">
            <i class="fa fa-plus"></i></button>
    </td>
{/block}

{block name="script_template"}
    <script type="text/html" id="achievements_day-template-{$idAchievements}">
        {include file='modules/DailyReport/Objects/achievements_day_edit.tpl'}
    </script>
    <script type="text/html" id="chievements_day-colspan-template-{$idAchievements}">
        <tr>
            <td colspan="3" style="text-align: center"></td>
        </tr>
    </script>
{/block}