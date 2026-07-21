{assign var='swNoOption' value=true}
{foreach $APPLICATIONS as $keyApp => $itemApp}
    {assign var='BLOCKS' value=$ALL_BOX_SCORE[$keyApp][1]}
    {assign var='BOX_SCORE' value=$ALL_BOX_SCORE[$keyApp][0]}
    {if (empty ($BOX_SCORE->boxs))}
        {continue}
    {/if}
    {if count($BLOCKS) > 0}
        {assign var='swNoOption' value=false}
        <optgroup label="{$itemApp.app_name}">
            {for $i=0; $i<count($BLOCKS); $i++}
                {assign var='countbox' value=0}
                {foreach $BOX_SCORE->boxs as $boxScoreData}
                    {if ($boxScoreData.type == $BLOCKS[$i]['type'])}
                        <option value="{$boxScoreData.box_score_dataid}">{$boxScoreData.description}</option>
                        {assign var='countbox' value=$countbox + 1}
                    {/if}
                {/foreach}
            {/for}
        </optgroup>
    {/if}
{/foreach}
{if $swNoOption}
    <optgroup label="BoxScore">
        <option value="">No hay vistas Indicdores disponibles</option>
    </optgroup>
{/if}
