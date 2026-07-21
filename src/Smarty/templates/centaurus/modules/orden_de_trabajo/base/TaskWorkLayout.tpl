{strip}
        {math equation= rand() assign= "idTaskProject"}
        {assign var="TASK_EXECUTOR_LABEL" value="{'LBL_TASK_EXECUTOR'|@getTranslatedString:'orden_de_trabajo'}"}

        {block name="css"}{/block}
        <div class="col-md-12" {block name="table_margin"}{/block}>
                {block name="card_header"}{/block}
                <div class="table-responsive task-work-container field-container" style="overflow-x: hidden;">
						<div class="label-input">
							<label style="line-height: 1.25em !important;font-weight: bold;">{'LBL_TASKS'|@getTranslatedString:'orden_de_trabajo'}:</label>
						</div>
                        <table id="task-project-table-{$idTaskProject}"
                                class="table table-bordered tablegridvalidate task-work-table">
                                <thead>
                                        <tr>
                                                <th {block name="col_0"}{/block} style="width:10%;vertical-align: middle;"><span
                                                                style="">{'LBL_TASK_SUBJECT'|@getTranslatedString:'orden_de_trabajo'}</span>
                                                </th>
                                                <th {block name="col_1"}{/block} style="width:17%;vertical-align: middle;"><span
                                                                style="">{'LBL_TASK_DESCRIPTION'|@getTranslatedString:'orden_de_trabajo'}</span>
                                                <h>
                                                <th {block name="col_3"}{/block} style="width:5.50%;vertical-align: middle;">
                                                        <span
                                                                style="">{'LBL_TASK_START_DATE'|@getTranslatedString:'orden_de_trabajo'}</span>
                                                </th>
                                                <th {block name="col_4"}{/block} style="width:5.5%;vertical-align: middle;">
                                                        <span
                                                                style="">{'LBL_TASK_DUE_DATE'|@getTranslatedString:'orden_de_trabajo'}</span>
                                                </th>
                                                <th {block name="col_status"}{/block}
                                                        style="width:4.5%;vertical-align: middle;">
                                                        <span
                                                                style="">{'LBL_TASK_STATUS'|@getTranslatedString:'orden_de_trabajo'}</span>
                                                </th>
                                                <th {block name="col_6" }{/block} style="width:5%;vertical-align: middle;"><span
                                                                style="">{'LBL_TASK_ASSIGNED'|@getTranslatedString:'orden_de_trabajo'}</span>
                                                </th>
                                                <th {block name="col_supplier"}{/block}
                                                        style="width:6%;vertical-align: middle;"><span
                                                                style="">{$TASK_EXECUTOR_LABEL}</span></th>
                                                <th {block name="col_2b"}{/block} style="width:4.5%;vertical-align: middle;"><span
                                                                style="">{'LBL_TASK_UNIT_TYPE'|@getTranslatedString:'orden_de_trabajo'}</span>
                                                </th>
                                                <th {block name="col_5"}{/block} style="width:4.5%;vertical-align: middle;"><span
                                                                style="">{'LBL_TASK_ESTIMATED_UNITS'|@getTranslatedString:'orden_de_trabajo'}</span>
                                                </th>
                                                <th {block name="col_5b" }{/block} style="width:6%;vertical-align: middle;">
                                                        <span
                                                                style="">{'LBL_TASK_ESTIMATED_COST'|@getTranslatedString:'orden_de_trabajo'}</span>
                                                </th>
                                                <th {block name="col_pwf"}{/block}
                                                        style="width:5%;vertical-align: middle;text-align:center;"><span
                                                                style="">{'LBL_TASK_WEIGHTING'|@getTranslatedString:'orden_de_trabajo'}</span>
                                                </th>
                                                <th {block name="col_reported"}{/block}
                                                        style="width:4.5%;vertical-align: middle;background-color: #e0dfde;">
                                                        <span
                                                                style="">{'LBL_TASK_EXECUTED_UNITS'|@getTranslatedString:'orden_de_trabajo'}</span>
                                                </th>
                                                <th {block name="col_costreported"}{/block}
                                                        style="width:6%;vertical-align: middle;background-color: #e0dfde;">
                                                        <span
                                                                style="">{'LBL_TASK_EXECUTED_COST'|@getTranslatedString:'orden_de_trabajo'}</span>
                                                </th>
                                                <th {block name="col_7"}{/block}
                                                        style="width:4.5%;vertical-align: middle;background-color: #e0dfde;">
                                                        <span>{'LBL_TASK_PROGRESS'|@getTranslatedString:'orden_de_trabajo'}</span>
                                                </th>
                                                <th {block name="col_work_progress"}{/block}
                                                        style="width:4.5%;vertical-align: middle;text-align:center;background-color: #e0dfde;">
                                                        <span
                                                                style="">{'LBL_TASK_WORK_PROGRESS'|@getTranslatedString:'orden_de_trabajo'}</span>
                                                </th>
                                                <th {block name="col_situation"}{/block}
                                                        style="width:6%;vertical-align: middle;text-align:center;background-color: #e0dfde;">
                                                        <span
                                                                style="">{'LBL_TASK_SITUATION'|@getTranslatedString:'orden_de_trabajo'}</span>
                                                </th>
                                        </tr>
                                </thead>
                                <tbody id="tbody-task-project-{$idTaskProject}" rowtotal="0"
                                        data-num-format="{$NUMBERING_FORMAT}">
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