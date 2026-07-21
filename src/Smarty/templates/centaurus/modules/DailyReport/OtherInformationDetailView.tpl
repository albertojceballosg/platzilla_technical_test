{extends file='modules/DailyReport/base/AchievementsLayout.tpl'}
{assign var='hasOtherInformation' value='NO'}

{block name="id_tbody_table"}tbody-other_information-{/block}

{block name="header_achievements_day_table"}
    <tr style="vertical-align: top">
        <td style="width: 15.5%">
            <span style="">Tipo de información</span>
        </td>
        <td style="width: 33.5%">
            <span style="">Información</span>
        </td>
        <td style="width: 47%">
            <span style="">Descripción</span>
        </td>
        <td class="text-center" style="width: 4%">Acciones</td>
    </tr>
{/block}

{block name="body_achievements_day_table"}
    {if $OTHER_INFORMATION neq NULL}
            {foreach $OTHER_INFORMATION as $otherInformation}
                {math equation= rand() assign= "idRow"}
                {include file='modules/DailyReport/Objects/other_information_view.tpl'}
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