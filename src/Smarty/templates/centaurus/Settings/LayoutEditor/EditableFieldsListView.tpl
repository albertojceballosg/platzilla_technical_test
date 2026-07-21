{if $EDITABLE_BUTTOM neq NULL}
    <table id="ec-list-table" class="table table-striped table-hover">
        <thead>
        <tr>
            <th width="6">&nbsp;</th>
            <th class="text-center">{$MOD.LBL_EDITABLE_FIELDS_TITLE}</th>
            <th class="text-center">{$MOD.LBL_EDITABLE_FIELDS_MODULE}</th>
            <th class="text-center">{$MOD.LBL_EDITABLE_FIELDS_DESCRIPCION}</th>
            <th class="text-center">{$MOD.LBL_CONFIG_APPS_STATUS}</th>
            <th class="text-center">{$MOD.LBL_CONFIG_APPS_ACTION}</th>
        </tr>
        </thead>
        <tbody>
        {foreach $EDITABLE_BUTTOM as $elements}
            <tr>
                <td><button type="button" class="btn btn-success btn-xs hide" onclick="EditableFieldsUtils.unlockEditableButton(this)"><i class="fa fa-unlock" aria-hidden="true"></i>
                    </button></td>
                <td>{$elements->getLabel ()}</td>
                <td>{$elements->getModuleName ()}</td>
                <td>{$elements->getDescription()}</td>
                <td class="text-center"><span
                            class="text-center label label-{if $elements->isStatus ()}success{else}danger{/if}">{if $elements->isStatus ()}{$MOD.LBL_ACTIVE}{else}{$MOD.LBL_INACTIVE}{/if}</span>
                </td>
                <td>
                    <a class="md-trigger table-link" rel="{$elements->getName ()}" title="Editar" alt="Editar" href="#"
                       onclick="EditableFieldsUtils.editEditableButton(this)">
					<span class=" fa-stack">
                    <i class="fa fa-square fa-stack-2x"></i>
                    <i class="fa fa-pencil fa-stack-1x fa-inverse"></i>
                    </span>
                    </a>

                    <a class="table-link danger" alt="Eliminar" rel="{$elements->getName ()}" title="Borrar"
                       align="absmiddle" href="#" onclick="EditableFieldsUtils.delEditableButton(this)">
					<span class=" fa-stack">
                    <i class="fa fa-square fa-stack-2x"></i>
                    <i class="fa fa-trash-o fa-stack-1x fa-inverse"></i>
                    </span>
                    </a>
                </td>
            </tr>
        {/foreach}
        </tbody>
    </table>
{else}
    <div class="row">
        <div class="col-xs-2">&nbsp;</div>
        <div class="col-xs-8">
            <div class="alert alert-info">No se ha creado ningun botón para editar campos!</div>
        </div>
        <div class="col-xs-2">&nbsp;</div>
    </div>
{/if}

