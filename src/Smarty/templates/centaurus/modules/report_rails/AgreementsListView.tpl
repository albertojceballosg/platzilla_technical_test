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
    {block name="url_created_file"}index.php?module=report_rails&action=AgreementEditView&parenttab=Settings&master_report={$MASTER_REPORT->getId()}{/block}
    {block name="lbl_created_btn"}&nbsp;Crear un acuerdo{/block}
    {block name="tab_description"}Gestionar acuerdos.{/block}
    {block name="extraHiddenFields"}
        <input type="hidden" id="master_report" name="master_report" value="{$MASTER_REPORT->getId()}"/>
    {/block}
    {block name="table_header"}
        <th style="text-align: center; width: 4%">N°</th>
        <th style="text-align: center; width: 20%">Acuerdo</th>
        <th style="text-align: center; width: 30%">Descripción</th>
        <th style="text-align: center; width: 14%">Involucrados</th>
        <th style="text-align: center; width: 14%">Ejecución</th>
        <th class="col-status">Estado</th>
        <th class="col-actions">Acciones</th>
    {/block}
     {block name="table_body"}
         {if $AVAILABLE_AGREEMENTS neq NULL}
             {assign var='activeData' value='ACTIVE'}
             {assign var='functionDalete' value='DELETE_AGREEMENTS'}
             {assign var='functionUpdate' value='UPDATE_AGREEMENTS'}
             {assign var='urlBase' value='index.php?module=report_rails&action=AgreementEditView&parenttab=Settings&master_report='|cat:$MASTER_REPORT->getId()}
        {foreach $AVAILABLE_AGREEMENTS as $agreement}
            <tr class="">
                <td style="text-align: center; width: 4%">{$agreement->getSequence ()}</td>
                <td style="text-align: left; width: 23%">{$agreement->getAgreement ()}</td>
                <td  style="text-align: left; width: 30%">
                    {$agreement->getDescription()|truncate:60|cat:'...'}
                </td>
                <td style="text-align: left; width: 14%">
                    {*$agreement->getUsersInvolved ()|var_dump*}
                    {if $agreement->getUsersInvolved () neq NULL}
                    <ul>
                        {foreach $agreement->getUsersInvolved() as $userInvolved}
                            <li>{$userInvolved['username']}</li>
                        {/foreach}

                    </ul>
                    {/if}
                </td>
                <td style="text-align: left; width: 14%">
                    {if  $agreement->getTabName () neq NULL}
                    <ul>
                        <li>Modulo: {$agreement->getTabName ()|module_label: $ADB}</li>
                        <li>Registro: {$agreement->getExecution ()}</li>
                        <li>{$agreement->getRelatedAgreement()}</li>
                    </ul>
                    {/if}
                </td>
                <td class="col-status">
                    <span class="label label-{if $agreement->getAgreementStatus () eq $activeData}success{else}danger{/if}">
                        {$AGREEMENTS_STATUS[$agreement->getAgreementStatus ()]}
                    </span>
                </td>
                <td class="col-actions">
                    {assign var='recordId' value=$agreement->getAgreementId()}
                    {assign var='rowName' value=$agreement->getAgreement()|truncate:20|cat:'...'}
                    {assign var='rowStatus' value=$agreement->getAgreementStatus ()}
                    {assign var='urlEdit' value=$urlBase|cat:'&record='|cat:$agreement->getAgreementId ()}
                    {include file='modules/report_rails/Base/ListiViewActionButtom.tpl'}
                </td>
            </tr>
        {/foreach}
         {else}
             <tr>
                 <td colspan="7" class="dataLabel" >No hay acuerdos registrados</td>
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