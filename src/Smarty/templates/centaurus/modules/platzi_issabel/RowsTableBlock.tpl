{strip}
    {if $ISSABEL_MONITORING neq NULL}
        {foreach $ISSABEL_MONITORING as $isabel}
            <tr>
                <td class="text-center">
                    <a href="index.php?module=platzi_issabel&parenttab=&action=DetailView&record=&uniqueid={$isabel->getUniqueId()}"
                       title="Detalle de la grabación del {$isabel->getDate()}">{$isabel->getDate()}</a>
                </td>
                <td class="text-center">{$isabel->getTime()}</td>
                <td class="text-center">{$isabel->getOrigin()}</td>
                <td class="text-center">{$isabel->getDestination()}</td>
                <td class="text-center">{$isabel->getDuration()}</td>
                <td class="text-center">{$MOD[$isabel->getType()]}</td>
                <td class="text-center">
                {if $isabel->getMessage() neq NULL}
                    <a data-width="650" data-toggle="lightbox" data-parent="" data-gallery="remoteload" data-title=""
                       href="index.php?module=platzi_issabel&action=AjaxPlatziIssabelUtils&function=AUDIO_MONITORING&uniqueid={$isabel->getUniqueId()}&Ajax=true"
                       title="Reproducir audio de la grabación"><i class="fa fa-bullhorn" aria-hidden="true"></i>
                    </a>
                {/if}
                </td>
            </tr>
        {/foreach}
    {else}
        <tr class="">
            <td colspan="7" class="text-center">No se encontró datos de grabación</td>
        </tr>
    {/if}
{/strip}