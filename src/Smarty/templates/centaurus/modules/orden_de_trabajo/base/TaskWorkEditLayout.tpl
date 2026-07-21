{strip}
        {math equation= rand() assign= "idTaskProject"}
        {assign var="TASK_EXECUTOR_LABEL" value="{'LBL_TASK_EXECUTOR'|@getTranslatedString:'orden_de_trabajo'}"}

        {block name="css"}{/block}
        <div class="col-md-12" {block name="table_margin"}{/block}>
                {block name="card_header"}{/block}
                <div class="table-responsive field-container">
                        <table id="task-project-table-{$idTaskProject}"
                                class="table table-bordered tablegridvalidate task-work-table">
                                <thead>
                                        <tr>
                                                <td {block name="colspan_header"}{/block}
                                                        style="text-align: left; background-color:#f9f8f7">
                                                        <strong>{'LBL_TASKS'|@getTranslatedString:'orden_de_trabajo'}:</strong>
                                                </td>
                                        </tr>
                                        <tr style="background-color: #f9f8f7;">
                                                <td {block name="col_0"}{/block} style="width:11%;vertical-align:middle;"><span
                                                                style="">{'LBL_TASK_SUBJECT'|@getTranslatedString:'orden_de_trabajo'}</span>
                                                </td>
                                                <td {block name="col_1"}{/block}
                                                        style="width:14%;vertical-align:middle;background-color: #f9f8f7;"><span
                                                                style="">{'LBL_TASK_DESCRIPTION'|@getTranslatedString:'orden_de_trabajo'}</span>
                                                </td>
                                                <td {block name="col_2"}{/block}
                                                        style="width:6%;vertical-align:middle;background-color: #f9f8f7;"><span
                                                                style="">Tipo</span>
                                                </td>
                                                <td {block name="col_3"}{/block}
                                                        style="width:6%;vertical-align:middle;background-color: #f9f8f7;"><span
                                                                style="">{'LBL_TASK_START_DATE'|@getTranslatedString:'orden_de_trabajo'}</span>
                                                </td>
                                                <td {block name="col_4"}{/block}
                                                        style="width:6.5%;vertical-align:middle;background-color: #f9f8f7;">
                                                        <span
                                                                style="">{'LBL_TASK_DUE_DATE'|@getTranslatedString:'orden_de_trabajo'}</span>
                                                </td>
                                                <td {block name="col_status"}{/block}
                                                        style="width:6%;vertical-align:middle;background-color: #f9f8f7;">
                                                        <span
                                                                style="">{'LBL_TASK_STATUS'|@getTranslatedString:'orden_de_trabajo'}</span>
                                                </td>
                                                <td {block name="col_6" }{/block}
                                                        style="width:6%;vertical-align:middle;background-color: #f9f8f7;"><span
                                                                style="">{'LBL_TASK_ASSIGNED'|@getTranslatedString:'orden_de_trabajo'}</span>
                                                </td>
                                                <td {block name="col_supplier"}{/block}
                                                        style="width:13%;vertical-align: middle;background-color: #f9f8f7;">
                                                        <span style="">{$TASK_EXECUTOR_LABEL}</span>
                                                </td>
                                                <td {block name="col_4b"}{/block}
                                                        style="width:4.5%;vertical-align:middle;background-color: #f9f8f7;">
                                                        <span
                                                                style="">{'LBL_TASK_UNIT_TYPE'|@getTranslatedString:'orden_de_trabajo'}</span>
                                                </td>
                                                <td {block name="col_5"}{/block}
                                                        style="width:5.5%;vertical-align:middle;background-color: #f9f8f7;"><span
                                                                style="">{'LBL_TASK_ESTIMATED_UNITS'|@getTranslatedString:'orden_de_trabajo'}</span>
                                                </td>
                                                <td {block name="col_5b" }{/block}
                                                        style="width:7.5%;vertical-align:middle;background-color: #f9f8f7;">
                                                        <span
                                                                style="">{'LBL_TASK_ESTIMATED_COST'|@getTranslatedString:'orden_de_trabajo'}</span>
                                                </td>
                                                <td {block name="col_2b"}{/block}
                                                        style="width:4%;vertical-align:middle;background-color: #f9f8f7;text-align:center;">
                                                        <span
                                                                style="">{'LBL_TASK_WEIGHTING'|@getTranslatedString:'orden_de_trabajo'}</span>
                                                </td>
                                                <td {block name="col_8"}{/block}
                                                        style="width:7%;vertical-align:middle;background-color: #f9f8f7;text-align:center;">
                                                        {block name="col_action"}{/block}</td>
                                        </tr>
                                </thead>
                                <tbody id="tbody-task-project-{$idTaskProject}" rowtotal="0"
                                        data-num-format="{$NUMBERING_FORMAT}" data-work-unit="{$WORK_UNIT_OF_MEASURE}">
                                        {block name="tbody_task_project"}{/block}
                                </tbody>
                                <tfoot id="tfoot-{$idTaskProject}" data-field-name="planned_activities" data-summary-row=""
                                        data-operation-row="">
                                        {block name="summaryRow"}{/block}
                                        {block name="addRow"}{/block}
                                </tfoot>
                        </table>
                        {block name="global_task"}{/block}
                </div>
        </div>
        {block name="modal"}{/block}
        {block name="script"}{/block}
        {block name="script_template"}{/block}
{/strip}