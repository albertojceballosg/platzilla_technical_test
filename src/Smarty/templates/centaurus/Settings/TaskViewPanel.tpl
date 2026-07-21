{extends file='Settings/Objects/ViewPanel.tpl'}
{assign var='faClass' value='fa fa-cogs emerald-bg'}
{* css and js statement *}
{block name="css"}{/block}
{block name="js"}
    <script type="text/javascript" src="modules/Settings/view-panel-utils.js"></script>
{/block}
{* header info *}
{block name="fa_class"}{$faClass}{/block}
{block name="panel_name"}{$MOD['LBL_PANEL_VIEW_TASK']}{/block}
{block name="panel_descripction"}{$MOD['LBL_VIEW_TASK_DESCRIPTION']}{/block}
{* panel content *}
{block name="hidden-data"}{/block}
{block name="nav_tabs"}{/block}
{block name="content"}
    <div class="table-responsive">
        {*$MODULES|var_dump*}
        <table class="table table-striped table-hover">
            <thead>
            <tr>
                <th class="col-to" style="width: 4%">id</th>
                <th class="col-to">Nombre</th>
                <th class="col-title" style="width: 30%">Módulo</th>
                <th class="col-to" style="width: 10%">Estado</th>
                <th class="col-actions" style="width: 6%">Acciones</th>
            </tr>
            </thead>
            <tbody id="how-use-table">
            {if ($MODULES neq NULL) }
                {foreach $MODULES as $thisModule}
                    <tr id="row-{$thisModule['tabid']}-{$thisModule['name']}">
                        <td class="col-title">
                            {$thisModule['tabid']}
                        </td>
                        <td class="col-to">{$thisModule['tablabel']}</td>
                        <td class="col-from">{$thisModule['name']}</td>
                        <td id="tab-status-{$thisModule['tabid']}">
                        {if $thisModule['status'] eq 'SHOW'}
                            <span title="Pestaña de tareas: visible" class="label label-success">Visible</span>
                        {else}
                            <span title="Pestaña de tareas: oculta" class="label label-danger">Oculta</span>
                        {/if}
                        </td>
                        <td class="col-actions">
                            <form action="index.php" class="form-inline" method="post"
                                  onclick="return confirm ('¿Estás seguro que quieres {$VIEW_STATUS[$thisModule['status']]} la pestaña en el modulo {$thisModule['tablabel']}?');">
                                <input type="hidden" name="module" value="Settings"/>
                                <input type="hidden" name="action" value="ViewPanelAjaxUtils"/>
                                <input type="hidden" name="fl_module" value="{$thisModule['name']}"/>
                                <input type="hidden" name="module_status" value="{$thisModule['status']}"/>
                                <input type="hidden" name="Ajax" value="true"/>
                                <button type="submit" class="btn btn-icon {if $thisModule['status'] neq 'SHOW'}btn-danger{else}btn-success{/if}"
                                        title="Cambiar a {$VIEW_STATUS[$thisModule['status']]} la pestaña en el modulo {$thisModule['tablabel']}">
                                    {if $thisModule['status'] eq 'SHOW'}
                                        <i class="fa fa-check-square-o" aria-hidden="true"></i>
                                    {else}
                                       <i class="fa fa-check-square-o" aria-hidden="true"></i>
                                    {/if}
                                </button>
                            </form>
                        </td>
                    </tr>
                {/foreach}
            {else}
                <tr class="lvtColData">
                    <td colspan="4" class="text-center">No hay modulos disponibles</td>
                </tr>
            {/if}
            </tbody>
        </table>
    </div>
{/block}