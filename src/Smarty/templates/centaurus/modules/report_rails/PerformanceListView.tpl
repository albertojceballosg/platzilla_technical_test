{extends file='modules/report_rails/Base/ListViewLayout.tpl'}
{strip}
    {block name="css"}
        <link rel="stylesheet" type="text/css" href="modules/report_rails/report_rails-utils.css" /></link>
        <style type="text/css">
                .col-fullname {
                    width: 20%;
                    vertical-align: top!important;
                }

                .col-status {
                    width: 6%;
                }
                .dataLabel {
                    font-weight: bold;
                    text-align: center;
                }
                .col-modulename {
                       width: 15em;
                   }

                   .col-field {
                       width: 15em;
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
            </style>
    {/block}
    {block name="url_created_file"}index.php?module=report_rails&action=PerformanceEditView&parenttab=Settings&master_report={$MASTER_REPORT->getId()}{/block}
    {block name="lbl_created_btn"}&nbsp;Crear index de rendimiento{/block}
    {block name="tab_description"}Gestionar de index de rendimiento.{/block}
    {block name="extraHiddenFields"}
        <input type="hidden" id="master_report" name="master_report" value="{$MASTER_REPORT->getId()}"/>
    {/block}
    {block name="table_header"}
        <th class="col-fullname">Tipo</th>
        <th class="col-status">Simbolo</th>
        {*<th class="col-fullname">Valor</th>*}
        <th class="col-fullname">Comentarios</th>
        <th class="col-status">Estado</th>
        <th class="col-actions">Acciones</th>
    {/block}
     {block name="table_body"}
         {if $AVAILABLE_PERFORMANCES neq NULL}
             {assign var='activeData' value='ACTIVE'}
             {assign var='functionDalete' value='DELETE_PERFORMANCE'}
             {assign var='functionUpdate' value='UPDATE_PERFORMANCE'}
             {assign var='urlBase' value='index.php?module=report_rails&action=PerformanceEditView&parenttab=Settings&master_report='|cat:$MASTER_REPORT->getId()}
        {foreach $AVAILABLE_PERFORMANCES as $performance}
            <tr class="">
                <td class="col-fullname">{$performance->getPerformanceName()}</td>
                <td class="col-status" style="text-align:center; color: #FFFFFF; background-color:{$performance->getIndexColor()} ">{$performance->getIconPath()}</td>
                <td class="col-fullname">
                    {$performance->getDescription()|truncate:60|cat:'...'}
                </td>
                <td class="col-status">
                    <span class="label label-{if $performance->getPerformanceStatus () eq $activeData}success{else}danger{/if}">
                        {$PERFORMANCES_STATUS[$performance->getPerformanceStatus ()]}
                    </span>
                </td>
                <td class="col-actions">
                    {assign var='recordId' value=$performance->getPerformanceId()}
                    {assign var='rowName' value=$performance->getPerformanceName()|truncate:20|cat:'...'}
                    {assign var='rowStatus' value=$performance->getPerformanceStatus ()}
                    {assign var='urlEdit' value=$urlBase|cat:'&record='|cat:$performance->getPerformanceId()}
                    {include file='modules/report_rails/Base/ListiViewActionButtom.tpl'}
                </td>
            </tr>
        {/foreach}
         {else}
             <tr>
                 <td colspan="6" class="dataLabel" >No hay index de rendimietos definidos</td>
             </tr>
         {/if}
     {/block}
    {block name="js"}
        <script type="text/javascript" src="modules/report_rails/report_rails-utils.js"></script>
        <script type="text/javascript">
            deleteRow = function (label) {
                return confirm ('¿Estás seguro de eliminar: "' + label + '"?')
            }
            changeStatusRow = function (label) {
                return confirm ('¿Estás seguro de cambiar el estado: "' + label + '"?')
            }
        </script>
    {/block}
{/strip}