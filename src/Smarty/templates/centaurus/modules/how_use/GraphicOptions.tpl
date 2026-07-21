{assign var='swNoOption' value=true}
    {assign var='applicationCodes' value=array_keys ($APPLICATIONS)}
    {foreach $applicationCodes as $applicationCode}
        {if (empty ($GRAPHS.applications[$applicationCode]))}
            {continue}
        {/if}
        {assign var='swNoOption' value=false}
        <optgroup label="{$APPLICATIONS[$applicationCode].app_name}">
        {foreach $GRAPHS.applications[$applicationCode] as $graph}
            <option value="{$graph.graficoid}">{$graph.title}</option>
        {/foreach}
        </optgroup>
    {/foreach}
{if $swNoOption}
    <optgroup label="Gráficos">
        <option value="">No hay vistas graficos disponibles</option>
    </optgroup>
{/if}
