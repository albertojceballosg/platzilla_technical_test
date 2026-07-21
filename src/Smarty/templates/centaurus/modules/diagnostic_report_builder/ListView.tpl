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
                    <i class="fa fa-puzzle-piece emerald-bg"></i>
                </div>
            </td>
            <td class="heading2" valign="bottom">
                <ol class="breadcrumb">
                    <li>
                        <a href="index.php?module=Settings&action=index&parenttab=Settings">CONFIGURACIÓN</a>
                    </li>
                    <li class="active">{$MOD.LBL_DIAGNOSTIC_REPORT_MANAGER|upper}</li>
                </ol>
            </td>
        </tr>
        <tr>
            <td class="small" valign="top">{$MOD.LBL_CONFIG_DIAGNOSTIC_REPORT_MANAGER_DESCRIPTION}</td>
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
                        <a href="index.php?module=diagnostic_report_builder&action=EditView&parenttab=Settings" class="btn btn-primary">
                            <i class="fa fa-plus-circle"></i> Informe de diagnóstico
                        </a>
                    </div>
                </header>
                <div class="main-box-body clearfix" id="ListViewContents">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" style="width: 100%">
                            <thead>
                            <tr>
                                <th class="col-label" style="width: 30%"><b>Diagnostico</b></th>
                                <th class="col-label" style="width: 30%"><b>Questionario</b></th>
                                <th class="col-label" style="width: 6%;"><b>Estado</b></th>
                                <th class="col-actions" style="width: 8%;text-align: right">Acciones</th>
                            </tr>
                            </thead>
                            <tbody>
                            {if $DIAGNOSTIC_REPORT neq NULL}
                                {foreach  $DIAGNOSTIC_REPORT as $diagnosticReport}
                                    <tr>
                                        <td class="text-left">{$diagnosticReport->getName()}</td>
                                        <td class="text-left">{$diagnosticReport->getQuestionnaireName()}</td>
                                        <td>{if $diagnosticReport->getStatus() eq  'ENABLED'}<span class="label label-success">Activo</span>
                                            {else}
                                                <span class="label label-danger">No activo</span>
                                            {/if}
                                        </td>
                                        <td>
                                            <ul class="actions" style="float: right">
                                                <li class="action">
                                                    <a href="index.php?module=diagnostic_report_builder&action=EditView&parenttab=Settings&record={$diagnosticReport->getId()}&parenttab=Settings" class="btn btn-primary" title="Editar">
                                                        <i class="fa fa-pencil"></i>
                                                    </a>
                                                </li>
                                                {if $diagnosticReport->getStatus() eq  'ENABLED'}
                                                <li class="action">
                                                    <form method="post" action="index.php" onsubmit="return changeReport ('{$diagnosticReport->getName()}');">
                                                        <input type="hidden" name="module" value="diagnostic_report_builder" />
                                                        <input type="hidden" name="action" value="changeStatus" />
                                                        <input type="hidden" name="record" value="{$diagnosticReport->getId()}" />
                                                        <input type="hidden" name="status" value="{$diagnosticReport->getStatus()}" />
                                                        <input type="hidden" name="Ajax" value="true" />
                                                        <button class="btn  btn-default" type="submit" title="Desactivar">
                                                            <i class="fa fa-check-square-o" aria-hidden="true"></i>
                                                        </button>
                                                    </form>
                                                </li>
                                                {else}
                                                    <li class="action">
                                                        <form method="post" action="index.php" onsubmit="return changeReport ('{$diagnosticReport->getName()}');">
                                                            <input type="hidden" name="module" value="diagnostic_report_builder" />
                                                            <input type="hidden" name="action" value="changeStatus" />
                                                            <input type="hidden" name="record" value="{$diagnosticReport->getId()}" />
                                                            <input type="hidden" name="status" value="{$diagnosticReport->getStatus()}" />
                                                            <input type="hidden" name="Ajax" value="true" />
                                                            <button class="btn  btn-default" type="submit" title="Activar">
                                                                <i class="fa fa-square-o" aria-hidden="true"></i>
                                                            </button>
                                                        </form>
                                                    </li>
                                                {/if}
                                                <li class="action">
                                                    <form method="post" action="index.php" onsubmit="return deleteReport ('{$diagnosticReport->getName()}');">
                                                        <input type="hidden" name="module" value="diagnostic_report_builder" />
                                                        <input type="hidden" name="action" value="Delete" />
                                                        <input type="hidden" name="record" value="{$diagnosticReport->getId()}" />
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
                                        <div class="alert alert-info">No se han creado informes de diagnostico</div>
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
        deleteReport = function (label) {
            return confirm ('¿Estás seguro de borrar el informe "' + label + '"?')
        }
        changeReport = function (label) {
            return confirm ('¿Estás seguro de cambiar el estado del informe "' + label + '"?')
        }
    </script>
{/strip}