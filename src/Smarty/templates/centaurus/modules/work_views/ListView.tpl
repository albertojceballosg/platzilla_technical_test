{strip}
<style type="text/css">
    .col-modulename {
        width: 15em;
    }

    .col-field {
        width: 15em;
    }

    .col-actions {
        width: 7em;
    }

    .action {
        display: inline-block;
        list-style: none;
    }

    .action .btn {
        font-size: 14px;
        height: 27px;
        line-height: 27px;
        margin: 0 5px 0 0;
        padding: 0;
        text-align: center;
        width: 27px;
    }
</style>
<div id="email-box" class="clearfix">
    <table class="table" width="100%" cellspacing="0" cellpadding="5" border="0">
        <tbody>
        <tr>
            <td rowspan="2" valign="top">
                <div class="infographic-box" style="width: 30px; padding: 0;">
                    <i class="fa fa-th yellow-bg"></i>
                </div>
            </td>
            <td class="heading2" valign="bottom">
                <ol class="breadcrumb">
                    <li>
                        <a href="index.php?module=Settings&action=index&parenttab=Settings">CONFIGURACIÓN</a>
                    </li>
                    <li class="active">{$MOD.LBL_work_views|upper}</li>
                </ol>
            </td>
        </tr>
        <tr>
            <td class="small" valign="top">{$MOD.LBL_work_views_DESCRIPTION}</td>
        </tr>
        </tbody>
    </table>
    {if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
        <div class="row">
            <div class="alert {if (isset ($IS_ERROR)) && ($IS_ERROR)}alert-danger{else}alert-success{/if}">
                <strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
            </div>
        </div>
    {/if}
    <div class="main-box clearfix">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-xs-12" style="margin-bottom: 12px">
                <header class="main-box-header clearfix">
                    <div class="col-xs-6">&nbsp;
                    </div>
                    <div class="col-xs-6 text-right">
                        <a href="index.php?module=work_views&action=EditView&parenttab=Settings" class="btn btn-primary">
                            <i class="fa fa-plus-circle"></i> Activar vista de tareas
                        </a>
                    </div>
                </header>
                <div class="main-box-body clearfix" id="ListViewContents">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                            <tr>
                                <th class="col-label" style="width: 40%"><b>Vista</b></th>
                                <th class="col-label" style="width: 15%"><b>Estado de la vista</b></th>
                                <th class="col-actions" style="width: 30%;float: right">Acciones</th>
                            </tr>
                            </thead>
                            <tbody>
                            {if $WORKS_VIEWS neq NULL}
                                {foreach  $WORKS_VIEWS as $workView}
                                    <tr>
                                        <td class="text-left">{$workView->getView()}</td>
                                        <td>{if $workView->getViewStatus() eq  'VISIBLE'}<span class="label label-success">Visible</span>
                                            {else}
                                                <span class="label label-danger">Oculta</span>
                                            {/if}
                                        </td>
                                        <td>
                                            <ul class="actions" style="float: right">
                                                <li class="action">
                                                    <a href="index.php?module=work_views&action=EditView&parenttab=Settings&record={$workView->getId()}&parenttab=Settings" class="btn btn-primary" title="Editar">
                                                        <i class="fa fa-pencil"></i>
                                                    </a>
                                                </li>
                                                <li class="action">
                                                    <form method="post" action="index.php" onsubmit="return deleteView ('{$workView->getView()}');">
                                                        <input type="hidden" name="module" value="work_views" />
                                                        <input type="hidden" name="action" value="Delete" />
                                                        <input type="hidden" name="record" value="{$workView->getId()}" />
                                                        <input type="hidden" name="Ajax" value="true" />
                                                        <button class="btn btn-danger" type="submit" title="Eliminar">
                                                            <i class="fa fa-trash-o"></i>
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </td>
                                    </tr>
                                {/foreach}
                            {else}
                                <tr>
                                    <td colspan="3">
                                        <div class="alert alert-info">No se han activiado vistas de trabajo</div>
                                    </td>
                                </tr>
                            {/if}

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    <script type="text/javascript">
        deleteView = function (label) {
            return confirm ('¿Estás seguro de borrar la vista "' + label + '"?')
        }
    </script>
{/strip}