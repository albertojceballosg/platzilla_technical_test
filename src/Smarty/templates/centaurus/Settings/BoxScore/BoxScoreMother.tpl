{extends file='Settings/BoxScore/Base/ListViewContentLayOut.tpl'}
{strip}
    {block name='page_header'}
        {if !$IS_INSTANCE}
        <header class="main-box-header clearfix">
        <div class="col-xs-12 text-right">
            <a href="index.php?module=indicatorspanel&action=index"
               target="_blank"
               title="Ver panel de indicadores"
               class="btn btn-primary">
                <i class="fa fa-edit"></i> Añadir Indicador
            </a>
        </div>
        </header>
        {/if}
    {/block}
    {block name='table_header'}
        <tr>
            <th class="column-title">Nombre del indicador</th>
            <th class="column-description">Descripción</th>
            <th class="column-field">Campo involucrado</th>
            <th class="column-general-left">F. Creación</th>
            <th class="column-status">Estado</th>
            {if !$IS_INSTANCE}
                <th class="column-title">Compartido con:</th>
                <th class="column-action">Acciones</th>
            {/if}
        </tr>
    {/block}
    {block name='table_body_id'}table_body-{$idBoxScore}{/block}
    {block name='table_body'}
        {if $MOTHER neq NULL}
            {foreach $MOTHER as $boxScore}
                <tr id="tabble-row-{$boxScore->getId()}-{$idBoxScore}">
                    <td id="bs-title-{$boxScore->getId()}-{$idBoxScore}" class="column-title">{$boxScore->getBoxScore()}</td>
                    <td class="column-description">{$boxScore->getDescription()}</td>
                    <td class="column-field">
                        {include file="Settings/BoxScore/objets/FieldInvolved.tpl" boxScore = $boxScore}
                    </td>
                    <td class="column-general-left">{$boxScore->getCreatedDate()|date_es_format}</td>
                    <td class="column-status">
                        <span class="label {if $boxScore->getStatus() eq 'ENABLED'}label-success{else}label-danger{/if}"
                              id="bsd-status-{$boxScore->getId()}-{$idBoxScore}">
                            {$MOD_STRINGS[$boxScore->getStatus()]}
                        </span>
                    </td>
                    {if !$IS_INSTANCE}
                        <td class="column-title">
                            {include file="Settings/BoxScore/objets/ListOfInstances .tpl" instancesList = $boxScore->sharedOn}
                        </td>
                    {/if}
                    {if !$IS_INSTANCE}
                    <td class="column-action">
                        {include file="Settings/BoxScore/objets/ActionButton.tpl" boxScore = $boxScore dataInstance = 'MOTHER'}
                    </td>
                    {/if}
                </tr>
            {/foreach}
        {else}
            <tr>
                <td colspan="7" class="dataLabel text-center">No hay indicadores registrados</td>
            </tr>
        {/if}
    {/block}
{/strip}