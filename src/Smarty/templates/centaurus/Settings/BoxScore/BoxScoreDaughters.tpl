{extends file='Settings/BoxScore/Base/ListViewContentLayOut.tpl'}
{strip}
    {block name='page_header'}
        {if !$IS_INSTANCE}
        <header class="main-box-header clearfix">
            <div class="row">
                <div class="col-xs-6 text-left">
                    <select class="form-control" name="instances" title="Selecionar instancia"
                            onchange="BoxScoreInventoryUtils.getInstance(this)"
                            data-instance="{$SELECTED_INSTANCE}">
                        {if $INSTANCES neq NULL}
                            <option value="">Seleccionar instancia</option>
                            {foreach $INSTANCES as $instance}
                                <option value="{$instance['code']}" {if $instance['code'] eq $SELECTED_INSTANCE} selected {/if}>{$instance['code']}
                                    : {$instance['name']} </option>
                            {/foreach}
                        {else}
                            <option value="0">No hay instancias</option>
                        {/if}
                    </select>
                </div>
                <div class="col-xs-6">&nbsp;</div>
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
            <th class="column-action">Acciones</th>
        </tr>
    {/block}
    {block name='table_body_id'}table_body_daughter-{$idBoxScore}{/block}
    {block name='table_body'}
        {if $DAUGHTERS neq NULL}
            {foreach $DAUGHTERS as $key => $boxScore}
                {if !is_numeric($key)}{continue}{/if}
                <tr id="tabble-row-{$boxScore->getId()}-{$idBoxScore}">
                    <td id="bs-title-{$boxScore->getId()}-{$idBoxScore}"
                        class="column-title">{$boxScore->getBoxScore()}</td>
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
                    <td class="column-action">
                        {include file="Settings/BoxScore/objets/ActionButton.tpl" boxScore = $boxScore dataInstance = $SELECTED_INSTANCE}
                    </td>
                </tr>
            {/foreach}
        {else}
            <tr>
                <td colspan="6" class="dataLabel text-center">No hay indicadores registrados</td>
            </tr>
        {/if}
    {/block}
{/strip}