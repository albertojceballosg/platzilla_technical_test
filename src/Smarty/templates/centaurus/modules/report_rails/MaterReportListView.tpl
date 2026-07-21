{extends file='modules/report_rails/Base/ListViewLayout.tpl'}
{strip}
    {block name="css"}
        <link rel="stylesheet" type="text/css" href="modules/report_rails/report_rails-utils.css" /></link>
        <style type="text/css">
                .col-fullname {
                    width: 20%;
                    vertical-align: top!important;
                }

                .col-username {
                    width: 10%;
                    vertical-align: top!important;
                }
                .col-status {
                    width: 6%;
                }

                .dataLabel {
                    font-weight: bold;
                    text-align: center;
                }
                .col-actions {
                    width: 12%;
                    text-align: right!important;
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
    {/block}
    {block name="url_created_file"}#{/block}
    {block name="lbl_created_btn"}&nbsp;Crear informe semanal{/block}
    {block name="click_created_action"}onclick="ReportRailesUtils.openCreateMasterModal(event, this)"{/block}
    {block name="tab_description"}Gestionar informe semanal.{/block}
    {block name="table_header"}
        <th class="col-fullname">Reporte</th>
        <th class="col-username">Instancia</th>
        <th class="col-username">agente</th>
        <th class="col-fullname">Fecha desde</th>
        <th class="col-fullname">Fecha hasta</th>
        <th class="col-status">Estado</th>
        <th class="col-actions">Acciones</th>
    {/block}
     {block name="table_body"}
         {if $MASTER_REPORT neq NULL}
             {assign var='activeData' value='ACTIVE'}
             {assign var='functionDalete' value='DELETE_MASTER_REPORT'}
             {assign var='functionUpdate' value='UPDATE_MASTER_REPORT'}
             {assign var='urlBase' value='index.php?module=report_rails&action=SummaryReportListView&parenttab=Settings'}
        {foreach $MASTER_REPORT as $report}
            <tr class="">
                <td class="col-fullname">
                    {$report->getDescription()|truncate:60|cat:'...'}
                </td>
                <td class="col-username">{$report->getCodeInstance()}</td>
                <td class="col-username">{$report->getAgent()->getName()}</td>
                <td class="col-fullname">{$report->getDateStart()|date_es_format}</td>
                <td class="col-fullname">{$report->getDueDate()|date_es_format}</td>
                <td class="col-status">
                    <span class="label label-{if $report->getStatus() eq $activeData}success{else}danger{/if}">
                        {$PERFORMANCES_STATUS[$report->getStatus()]}
                    </span>
                </td>
                <td class="col-actions">
                    {assign var='recordId' value=$report->getId()}
                    {assign var='rowName' value=$report->getDescription()|truncate:20|cat:'...'}
                    {assign var='rowStatus' value=$report->getStatus()}
                    {assign var='urlEdit' value=$urlBase|cat:'&master_report='|cat:$report->getId()|cat:'&instance='|cat:$report->getCodeInstance()}
                    {block name="share_report"}
                    <li class="action">
                        <form method="post" action="index.php" id="form_share_report_{$recordId}">
                            <input type="hidden" name="module" value="report_rails">
                            <input type="hidden" name="action" value="AjaxRailsUtils">
                            <input type="hidden" name="function" value="SHARE_REPORT">
                            <input type="hidden" name="record" value="{$recordId}">
                            <input type="hidden" name="Ajax" value="true">
                            <button class="btn btn-info" type="button"
                                    onclick="ReportRailesUtils.shareMasterReport('{$rowName}', {$recordId})"
                                    title="Compartie en instancia">
                                <i class="fa fa-share-alt-square"></i></button>
                        </form>
                    </li>
                    {/block}
                    {include file='modules/report_rails/Base/ListiViewActionButtom.tpl'}
                </td>
            </tr>
        {/foreach}
         {else}
             <tr>
                 <td colspan="7" class="dataLabel" >No hay reportes </td>
             </tr>
         {/if}
     {/block}
    {block name="js"}
        <script type="text/javascript" src="modules/report_rails/report_rails-utils.js"></script>
        <script type="text/javascript">
            deleteRow = function (label) {
                return confirm ('¿Estás seguro de eliminar: "' + label + '"?')
            }
            changeRow = function (label) {
                return confirm ('¿Estás seguro de cambiar el estado: "' + label + '"?')
            }
        </script>
    {/block}
{/strip}