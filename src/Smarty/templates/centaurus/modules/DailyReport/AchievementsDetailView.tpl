{extends file='modules/DailyReport/base/AchievementsLayout.tpl'}

{assign var="hasAchievements" value='NO'}
{block name="header_achievements_day_table"}
    <tr style="vertical-align: top;">
        <td style="vertical-align:middle; width: 39%">
            <span style="">Título</span>
        </td>
        <td style="vertical-align:middle;width: 57%;">
            <span style="">Descripción</span>
        </td>
        <td class="text-center;" style="width: 4%">&nbsp;</td>
    </tr>
{/block}

{block name="body_achievements_day_table"}
    {if $ACHIEVEMENTS neq NULL}
            {foreach $ACHIEVEMENTS as $achievements_day}
                {math equation= rand() assign= "idRow"}
                {include file='modules/DailyReport/Objects/achievements_day_view.tpl'}
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

{block name="add-achievements_day-row"}{/block}

{block name="script_template"}{/block}