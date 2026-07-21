{extends file='modules/indicatorspanel/Base/EditViewBoxLayOut.tpl'}
{if (isset ($BOX_SCORE) && isset($BOX_SCORE->boxs[0]))}
    {assign var='allObjetives' value=$BOX_SCORE->boxs[0]['all_objetive']}
    {assign var='boxScore' value=$BOX_SCORE->boxs[0]['box_score']}
    {assign var='boxScoreName' value=$BOX_SCORE->boxs[0]['name']}
    {assign var='boxScoreObjectiveId' value=$BOX_SCORE->boxs[0]['box_score_objectiveid']}
    {assign var='boxScoreType' value=$BOX_SCORE->boxs[0]['type']}
    {assign var='dataCumpOne' value=$FULFILLMENT[0]['id']}
    {assign var='dataCumpTwo' value=$FULFILLMENT[1]['id']}
    {assign var='description' value=$BOX_SCORE->boxs[0]['description']}
    {assign var='isEditable' value=$BOX_SCORE->boxs[0]['is_editable']}
    {assign var='marginAccordingTarget' value=$FULFILLMENT[0]['value_variance']}
    {assign var='marginCloseTarget' value=$FULFILLMENT[1]['value_variance']}
    {assign var='objectiveScale' value=$BOX_SCORE->boxs[0]['objective_scale']}
    {assign var='record' value=$BOX_SCORE->boxs[0]['box_score_dataid']}
    {assign var='targetMonth' value=$BOX_SCORE->boxs[0]['target_month']}
    {assign var='totalObjetives' value=$allObjetives|@count}
    {assign var='typeValueVarianceClose' value=$FULFILLMENT[1]['type_variance']}
    {assign var='typeVarianceAccordig' value=$FULFILLMENT[0]['type_variance']}
{else}
    {assign var='allObjetives' value=null}
    {assign var='box_score' value=null}
    {assign var='boxScoreName' value=null}
    {assign var='boxScoreObjectiveId' value=null}
    {assign var='boxScoreType' value=null}
    {assign var='dataCumpOne' value=null}
    {assign var='dataCumpTwo' value=null}
    {assign var='description' value=null}
    {if $IS_MOTHER}
        {assign var='isEditable' value='YES'}
    {else}
        {assign var='isEditable' value='NO'}
    {/if}
    {assign var='marginAccordingTarget' value=null}
    {assign var='marginCloseTarget' value=null}
    {assign var='objectiveScale' value='MONTH'}
    {assign var='targetMonth' value=null}
    {assign var='record' value=null}
    {assign var='totalObjetives' value=0}
    {assign var='typeValueVarianceClose' value=null}
    {assign var='typeVarianceAccordig' value=null}
{/if}
{block name="css"}
    <link rel="stylesheet" type="text/css" href="modules/grid_view/grid-view.css"/>
    <style>
        th {
            text-align: center;
        }

        /* Important part */
        .modal-dialog {
            overflow-y: initial !important
        }

        .modal-body {
            height: 400px;
            overflow-y: auto;
        }

        .onoffswitch4 {
            position: relative;
            width: 90px;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
        }

        .onoffswitch4-checkbox {
            display: none;
        }

        .onoffswitch4-label {
            display: block;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid #27A1CA;
            border-radius: 0px;
        }

        .onoffswitch4-inner {
            display: block;
            width: 200%;
            margin-left: -100%;
            -moz-transition: margin 0.3s ease-in 0s;
            -webkit-transition: margin 0.3s ease-in 0s;
            -o-transition: margin 0.3s ease-in 0s;
            transition: margin 0.3s ease-in 0s;
        }

        .onoffswitch4-inner:before, .onoffswitch4-inner:after {
            display: block;
            float: left;
            width: 50%;
            height: 30px;
            padding: 0;
            line-height: 26px;
            font-size: 14px;
            color: white;
            font-family: Trebuchet, Arial, sans-serif;
            font-weight: bold;
            -moz-box-sizing: border-box;
            -webkit-box-sizing: border-box;
            box-sizing: border-box;
            border: 2px solid transparent;
            background-clip: padding-box;
        }

        .onoffswitch4-inner:before {
            content: "Mes";
            padding-left: 10px;
            background-color: #FFFFFF;
            color: #27A1CA;
        }

        .onoffswitch4-inner:after {
            content: "Semana";
            padding-right: 2px;
            background-color: #FFFFFF;
            color: #666666;
            text-align: right;
        }

        .onoffswitch4-switch {
            display: block;
            width: 25px;
            margin: 0px;
            background: #27A1CA;
            position: absolute;
            top: 0;
            bottom: 0;
            right: 65px;
            -moz-transition: all 0.3s ease-in 0s;
            -webkit-transition: all 0.3s ease-in 0s;
            -o-transition: all 0.3s ease-in 0s;
            transition: all 0.3s ease-in 0s;
        }

        .onoffswitch4-checkbox:checked + .onoffswitch4-label .onoffswitch4-inner {
            margin-left: 0;
        }

        .onoffswitch4-checkbox:checked + .onoffswitch4-label .onoffswitch4-switch {
            right: 0px;
        }
    </style>
{/block}
{* *}
{block name="identification"}
    <div class="form-group">
        <label for="box_score">{$MOD.LBL_TITLE_INDICATOR}</label>
        <input type="text" class="form-control" value="{$boxScore}"
               id="box_score-{$idBoxScore}" name="box_score">
    </div>
    <div class="form-group">
        <label for="description">{$MOD.LBL_DESCRIPTION}</label>
        <textarea id="description-{$idBoxScore}" class="form-control" rows="2" tabindex=""
                  name="description">{$description}</textarea>
    </div>
{/block}
{block name="selected_ranges"}
    {*$allObjetives|var_dump*}
    <div id="the-ranges-{$idBoxScore}" class="col-lg-12 col-md-12 col-sm-12">
        <div class="row" style="margin-top: 2px">
            <div class="col-lg-5 col-md-5 col-sm-5">&nbsp;</div>
            <div class="col-lg-2 col-md-2 col-sm-2">
                <div class="onoffswitch4">
                    <input type="checkbox" name="range_type"
                           class="onoffswitch4-checkbox" id="myonoffswitch4"
                           onchange="IndicatorUtils.selectedRange(this, '{$idBoxScore}')"
                           {if $objectiveScale eq 'MONTH'}checked{/if}>
                    <label class="onoffswitch4-label" for="myonoffswitch4">
                        <span class="onoffswitch4-inner"></span>
                        <span class="onoffswitch4-switch"></span>
                    </label>
                </div>
            </div>
            <div class="col-lg-5 col-md-5 col-sm-5">&nbsp;</div>
        </div>
        <div class="col-lg-12 col-md-12 col-sm-12 hide">&nbsp;</div>
        <table id="table-objetive-{$idBoxScore}"
               class="table table-hover table-bordered dataTable" role="grid">
            <thead id="header-range-{$idBoxScore}">
            <tr role="row">
                <th style="width: 50%" id="column-{$idBoxScore}">{$MOD.LBL_MONTH}</th>
                <th style="width: 15%">{$MOD.LBL_OPERATOR}</th>
                <th style="width: 30%">{$MOD.LBL_OBJECT}</th>
                <th style="width: 5%">&nbsp</th>
            </tr>
            </thead>
            <tbody id="body-range-{$idBoxScore}">
                {include file="modules/indicatorspanel/Objets/ObjetivesEditTemplate.tpl"}
            </tbody>
            <tfoot id="tfoot-{$idBoxScore}">
            <tr>
                <td colspan="4" class="text-center">
                    <button id="bs-add-row-table-{$idBoxScore}"  type="button" data-id-linkage="{$idBoxScore}"
                            class="btn btn-primary" data-sequence="{$totalObjetives}"
                            onclick="IndicatorUtils.addRowToTable (this, '{$idBoxScore}');">
                        <i class="fa fa-plus"></i></button>
                </td>
            </tr>
            </tfoot>
        </table>
    </div>
{/block}
{block name="loading_method"}
    <div class="form-group">
        <table id="table-example"
               class="table table-hover dataTable no-footer"
               role="grid" style="width: 100%;">
            <tbody>
            <tr class="odd" role="row">
                <td style=" font-size:12px; width:50%">{$MOD.PERCENT_VAR_OF_TARGET}
                    <input type="hidden" name="data_cump_one" value="{$dataCumpOne}">
                    <input type="hidden" name="fulfillment_one" value="According to the objective">
                </td>
                <td class="">
                    <select id="margin_according_target-{$idBoxScore}"  {*id="dao_inf_0"*} name="margin_according_target" class="form-control">
                        {include file="modules/indicatorspanel/Objets/TargetPercentageOptions.tpl" selectedValue=$marginAccordingTarget}
                    </select>
                    <input type="hidden" name="type_variance_according" id="type_dao_inf_0"
                           value="{$typeVarianceAccordig}">
                </td>
            </tr>
            <tr class="even" role="row">
                <td style="font-size:12px; width:50%">{$MOD.PERCENT_VAR_CLOSE_TARGET}
                    <input type="hidden" name="data_cump_two" value="{$dataCumpTwo}">
                    <input type="hidden" name="fulfillment_two" value="Near the goal">
                </td>
                <td class="">
                    <select id="margin_close_target-{$idBoxScore}" {*id="dao_inf_1"*} name="margin_close_target" class="form-control">
                        {include file="modules/indicatorspanel/Objets/TargetPercentageOptions.tpl" selectedValue=$marginCloseTarget}
                    </select>
                    <input type="hidden" name="type_variance_close" id="type_dao_inf_1"
                           value="{$typeValueVarianceClose}">
                </td>
            </tr>
            {* fuente de datos *}
            <tr class="even" role="row">
                <td style="font-size:12px; width:50%">{$MOD.DATA_SOURCE}
                </td>
                <td class="">
                    {include file="modules/indicatorspanel/Objets/BoxScoreDataSource.tpl"}
                </td>
            </tr>
            {* / fuente de datos *}
            {* modulos *}
            <tr id="box_score-row" class="even {if $FLD_MODULE eq NULL}hide{/if}" role="row">
                <td style="font-size:12px; width:50%">{$MOD.MODULE_FOR_CALCULATION}
                </td>
                <td class="">
                    <select id="fldmodule-{$idBoxScore}" name="fldmodule" class="form-control"
                            onchange="IndicatorUtils.selectedModule(this, '{$idBoxScore}')"
                            {if (!$IS_MOTHER && $isEditable eq 'NO')}disabled
                            title="Bloqueado para edición"{/if}>
                        <option value="">{$MOD.LBL_SELECTION_DEFAULT}</option>
                        {foreach $MODULES as $module}
                            <option value="{$module.name}"
                                    {if $FLD_MODULE eq $module.name}selected="selected"{/if}>{$module.tablabel}</option>
                        {/foreach}
                    </select>
                </td>
            </tr>
            {* / modulos *}
            {* campos calculados *}
            <tr id="calculated-field-row-{$idBoxScore}" class="even {if $FIELD_NAME eq NULL}hide{/if}" role="row">
                <td style="font-size:12px; width:50%">{$MOD.CALCULATED_FIELDS}
                </td>
                <td id="calculated-field-select" class="">
                    <select id="calculateField-{$idBoxScore}" name="calculateField" class="form-control"
                            {if (!$IS_MOTHER && $isEditable eq 'NO')}disabled
                            title="Bloqueado para edición"{/if}>
                        <option value="">{$MOD.LBL_SELECTION_DEFAULT}</option>
                        {if $FIELD_NAME neq NULL}
                            {foreach $FIELDS as $field}
                                <option value="{$field.fieldname}"
                                   {if $FIELD_NAME eq $field.fieldname}selected="selected"{/if}>
                                    {$field.fieldlabel}</option>
                            {/foreach}
                        {/if}
                    </select>
                    <span id="calculateField-help" class="help-block"></span>
                </td>
            </tr>
            {* calculation engine *}
            <tr id="calculated-system-row" class="even {if $CALCULATED_SYSTEM eq NULL}hide{/if}"
                role="row">
                <td style="font-size:12px; width:50%">{$MOD.CALCULATION_ENGINE}
                </td>
                <td id="calculation_engine-field-select" class="">
                    <select id="calculationEngine-{$idBoxScore}" name="calculationEngine" class="form-control"
                            {if (!$IS_MOTHER && $isEditable eq 'NO')}disabled
                            title="Bloqueado para edición"{/if}>
                        <option value="">{$MOD.LBL_SELECTION_DEFAULT}</option>
                        {if $CALCULATION_ENGINE neq NULL}
                            {foreach $CALCULATION_ENGINE as $calculation}
                                <option value="{$calculation->getCalculationName()}"
                                        {if $calculation->getCalculationName() eq $CALCULATED_SYSTEM}selected{/if}
                                >{$calculation->getModuleName()|module_label: $ADB}
                                    : {$calculation->getName ()}</option>
                            {/foreach}
                        {/if}
                    </select>
                    <span id="calculateField-help" class="help-block"></span>
                </td>
            </tr>
            {* calculation engine *}
            </tbody>
        </table>
    </div>
{/block}
{block name="modal-footer"}
    <button class="btn btn-warning" id="btnclose"
            onclick="jQuery ('#addIndicators').removeClass ('md-show'); jQuery ('.md-overlay').css ({ldelim} opacity: 0.0, visibility: 'hidden' {rdelim}); jQuery ('#addIndicators').html(''); return false;">{$APP.LBL_CANCEL_BUTTON_LABEL}</button>
    <button type="submit" id="btnSave" name="btnSave" class="btn btn-success"
            onclick="IndicatorUtils.saveIndicator(this, '{$idBoxScore}')">{$APP.LBL_SAVE_BUTTON_LABEL}</button>
{/block}
{block name="script_template"}
    <script type="text/html" id="WEEK_TEMPLATE-{$idBoxScore}">
        {include file='modules/indicatorspanel/Objets/WeekObjetiveTemplate.tpl'}
    </script>
    <script type="text/html" id="MONTH_TEMPLATE-{$idBoxScore}">
        {include file='modules/indicatorspanel/Objets/MonthObjetiveTemplate.tpl'}
    </script>
{/block}
{strip}
    <div class="modal-dialog">
        <div class="modal-content">
            {*$BOX_SCORE->boxs[0]|var_dump*}
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"
                        onclick="jQuery ('#addIndicators').removeClass ('md-show'); jQuery ('.md-overlay').css ({ldelim} opacity: 0.0, visibility: 'hidden' {rdelim}); jQuery ('#addIndicators').html(''); return false;">
                    ×
                </button>
                <h4 class="modal-title">{if (isset ($RECORD))}{$MOD.MESS_EDIT_BOX_SCORE}{else}{$MOD.MESS_ADD_BOX_SCORE}{/if}</h4>
            </div>
            <form role="form" name="{$MODULE}" id="{$MODULE}" action="index.php" method="post">
                <div class="modal-body">
                    <input type="hidden" name="type"
                           value="{if ($BOX_SCORE->boxs[0]['type'])}{$BOX_SCORE->boxs[0]['type']}{else}{$TYPE}{/if}">
                    <input type="hidden" name="record" id="record" value="{$BOX_SCORE->boxs[0]['box_score_dataid']}">
                    <input type="hidden" name="boxscoreid" value="{$ACCOUNT_ID}">
                    <input type="hidden" name="monthsearch" value="{$MONTH_SEARCH}">
                    <input type="hidden" name="box_score_objectiveid"
                           value="{$BOX_SCORE->boxs[0]['box_score_objectiveid']}">
                    <input type="hidden" id="create_indicator" name="create_indicator" value="">
                    <input type="hidden" id="action" name="action" value="SaveBox">
                    <input type="hidden" id="module" name="module" value="{$MODULE}">
                    <input type="hidden" id="app" name="app" value="{$CODE_APP}">
                    <input type="hidden" id="mode" name="mode" value="{$MODE}">
                    <input type="hidden" id="viewScale" name="viewScale" value="{$VIEW_SEARCH}">
                    {if $IS_HOME neq NULL}
                        <input type="hidden" name="is_home" value="1">
                    {/if}

                    <div class="form-group">
                        <label for="box_score">{$MOD.LBL_TITLE_INDICATOR}</label>
                        <input type="text" class="form-control" value="{$BOX_SCORE->boxs[0]['box_score']}"
                               id="box_score" name="box_score">
                    </div>
                    <div class="form-group">
                        <label for="description">{$MOD.LBL_DESCRIPTION}</label>
                        <textarea id="description" class="form-control" rows="2" tabindex=""
                                  name="description">{$BOX_SCORE->boxs[0]['description']}</textarea>
                    </div>
                    <div class="form-group" align="right">
                        <button type="button" name="addallobjetive" id="addallobjetive"
                                class="btn btn-primary">{$MOD.LBL_ADD_BUTTON_ALL_OBJECT}</button>
                        &nbsp;&nbsp;
                        <button type="button" name="addobjetive" id="addobjetive"
                                class="btn btn-primary">{$MOD.LBL_ADD_BUTTON_OBJECT}</button>
                    </div>
                    <div class="form-inline form-inline-box">
                        <table id="table-objetive" class="table table-hover dataTable no-footer" role="grid"
                               width="100%">
                            <thead>
                            <tr role="row">
                                <th>{$MOD.LBL_MONTH}</th>
                                <th>{$MOD.LBL_OPERATOR}</th>
                                <th>{$MOD.LBL_OBJECT}</th>
                                <th>&nbsp;&nbsp;</th>
                            </tr>
                            </thead>
                            <tbody id="bodyObjtable">
                            {if (!isset ($RECORD)) && (count ($BOX_SCORE->boxs[0]['all_objetive']) == 0)}
                                <tr style="background-color: #e8e8e8;" role="row" id="fileObject_0"
                                    class="addtargetmonth">
                                    <td>
                                        <select class="form-control targetmonth" id="targetmonth_0" name="targetmonth[]"
                                                title="">
                                            <option value="">{$MOD.LBL_SELECTION_MONTH}</option>
                                            <option value="01">{$MOD.LBL_ENERO}</option>
                                            <option value="02">{$MOD.LBL_FEBRERO}</option>
                                            <option value="03">{$MOD.LBL_MARZO}</option>
                                            <option value="04">{$MOD.LBL_ABRIL}</option>
                                            <option value="05">{$MOD.LBL_MAYO}</option>
                                            <option value="06">{$MOD.LBL_JUNIO}</option>
                                            <option value="07">{$MOD.LBL_JULIO}</option>
                                            <option value="08">{$MOD.LBL_AGOSTO}</option>
                                            <option value="09">{$MOD.LBL_SEPTIEMBRE}</option>
                                            <option value="10">{$MOD.LBL_OCTUBRE}</option>
                                            <option value="11">{$MOD.LBL_NOVIEMBRE}</option>
                                            <option value="12">{$MOD.LBL_DICIEMBRE}</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-control operator" id="operator_0" name="operator[]" title=""
                                                onchange="selectAllOperator()">
                                            <option value="less-equal">&lt;=</option>
                                            <option value="greater-equal">&gt;=</option>
                                        </select>
                                        <input type="hidden" id="all_operator" name="all_operator" value="less-equal">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control objetive"
                                               value="" id="objetive_0"
                                               name="objetive[]" placeholder="{$MOD.Ingresar} {$MOD.LBL_OBJECT}">
                                    </td>
                                    <td>
                                        &nbsp;&nbsp;
                                    </td>
                                </tr>
                            {elseif (isset ($RECORD))}
                                {for $i = 0; $i < count($BOX_SCORE->boxs[0]['all_objetive']); $i++}
                                    <tr style="background-color: #e8e8e8;" role="row" id="fileObject_{$i}"
                                        class="addtargetmonth">
                                        <td>
                                            <select class="form-control targetmonth addtargetmonth"
                                                    id="targetmonth_{$i}" name="targetmonth[]" title="">
                                                <option value="">{$MOD.LBL_SELECTION_MONTH}</option>
                                                <option value="01"{if ($BOX_SCORE->boxs[0]['all_objetive'][$i]['month_apli'] == '01')} selected="selected"{/if}>{$MOD.LBL_ENERO}</option>
                                                <option value="02"{if ($BOX_SCORE->boxs[0]['all_objetive'][$i]['month_apli'] == '02')} selected="selected"{/if}>{$MOD.LBL_FEBRERO}</option>
                                                <option value="03"{if ($BOX_SCORE->boxs[0]['all_objetive'][$i]['month_apli'] == '03')} selected="selected"{/if}>{$MOD.LBL_MARZO}</option>
                                                <option value="04"{if ($BOX_SCORE->boxs[0]['all_objetive'][$i]['month_apli'] == '04')} selected="selected"{/if}>{$MOD.LBL_ABRIL}</option>
                                                <option value="05"{if ($BOX_SCORE->boxs[0]['all_objetive'][$i]['month_apli'] == '05')} selected="selected"{/if}>{$MOD.LBL_MAYO}</option>
                                                <option value="06"{if ($BOX_SCORE->boxs[0]['all_objetive'][$i]['month_apli'] == '06')} selected="selected"{/if}>{$MOD.LBL_JUNIO}</option>
                                                <option value="07"{if ($BOX_SCORE->boxs[0]['all_objetive'][$i]['month_apli'] == '07')} selected="selected"{/if}>{$MOD.LBL_JULIO}</option>
                                                <option value="08"{if ($BOX_SCORE->boxs[0]['all_objetive'][$i]['month_apli'] == '08')} selected="selected"{/if}>{$MOD.LBL_AGOSTO}</option>
                                                <option value="09"{if ($BOX_SCORE->boxs[0]['all_objetive'][$i]['month_apli'] == '09')} selected="selected"{/if}>{$MOD.LBL_SEPTIEMBRE}</option>
                                                <option value="10"{if ($BOX_SCORE->boxs[0]['all_objetive'][$i]['month_apli'] == '10')} selected="selected"{/if}>{$MOD.LBL_OCTUBRE}</option>
                                                <option value="11"{if ($BOX_SCORE->boxs[0]['all_objetive'][$i]['month_apli'] == '11')} selected="selected"{/if}>{$MOD.LBL_NOVIEMBRE}</option>
                                                <option value="12"{if ($BOX_SCORE->boxs[0]['all_objetive'][$i]['month_apli'] == '12')} selected="selected"{/if}>{$MOD.LBL_DICIEMBRE}</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select class="form-control operator" id="operator_{$i}" name="operator[]"
                                                    title="">
                                                <option value="less-equal"{if ($BOX_SCORE->boxs[0]['all_objetive'][$i]['operator'] == 'less-equal')} selected="selected"{/if}>
                                                    <=
                                                </option>
                                                <option value="greater-equal"{if ($BOX_SCORE->boxs[0]['all_objetive'][$i]['operator'] == 'greater-equal')} selected="selected"{/if}>
                                                    &gt;=
                                                </option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control objetive"
                                                   value="{$BOX_SCORE->boxs[0]['all_objetive'][$i]['objective']}"
                                                   id="objetive_{$i}" name="objetive[]"
                                                   placeholder="{$MOD.Ingresar} {$MOD.LBL_OBJECT}">
                                        </td>
                                        <td>
                                            {if ($i == 0)}
                                                &nbsp;&nbsp;&nbsp;
                                            {else}
                                                <input width="16" type="image" height="16" title="Delete"
                                                       src="themes/images/remove.png"
                                                       onclick="deleteOtherOperation('fileObject_{$i}')">
                                            {/if}
                                        </td>
                                    </tr>
                                {/for}
                            {/if}
                            </tbody>
                        </table>
                    </div>
                    <div class="form-group">
                        <table id="table-example" class="table table-hover dataTable no-footer" role="grid"
                               width="100%">
                            <tbody>
                            <tr class="odd" role="row">
                                <td style=" font-size:12px; width:50%">{$MOD.PERCENT_VAR_OF_TARGET}
                                    <input type="hidden" name="boxscorecump_dao_0" value="{$FULFILLMENT[0]['id']}">
                                    <input type="hidden" name="fulfillment_0" value="According to the objective">
                                </td>
                                <td>
                                    <select id="dao_inf_0" name="dao_inf_0" class="form-control">
                                        <option value="">{$MOD.LBL_SELECTION_DEFAULT}</option>
                                        <option value="0" {if ($FULFILLMENT[0]['value_variance'] == '0')} selected="selected"{/if}>
                                            0%
                                        </option>
                                        <option value="5" {if ($FULFILLMENT[0]['value_variance'] == '5')} selected="selected"{/if}>
                                            5%
                                        </option>
                                        <option value="10" {if ($FULFILLMENT[0]['value_variance'] == '10')} selected="selected"{/if}>
                                            10%
                                        </option>
                                        <option value="15" {if ($FULFILLMENT[0]['value_variance'] == '15')} selected="selected"{/if}>
                                            15%
                                        </option>
                                        <option value="20" {if ($FULFILLMENT[0]['value_variance'] == '20')} selected="selected"{/if}>
                                            20%
                                        </option>
                                        <option value="25" {if ($FULFILLMENT[0]['value_variance'] == '25')} selected="selected"{/if}>
                                            25%
                                        </option>
                                        <option value="30" {if ($FULFILLMENT[0]['value_variance'] == '30')} selected="selected"{/if}>
                                            30%
                                        </option>
                                        <option value="35" {if ($FULFILLMENT[0]['value_variance'] == '35')} selected="selected"{/if}>
                                            35%
                                        </option>
                                        <option value="40" {if ($FULFILLMENT[0]['value_variance'] == '40')} selected="selected"{/if}>
                                            40%
                                        </option>
                                        <option value="45" {if ($FULFILLMENT[0]['value_variance'] == '45')} selected="selected"{/if}>
                                            45%
                                        </option>
                                        <option value="50" {if ($FULFILLMENT[0]['value_variance'] == '50')} selected="selected"{/if}>
                                            50%
                                        </option>
                                        <option value="55" {if ($FULFILLMENT[0]['value_variance'] == '55')} selected="selected"{/if}>
                                            55%
                                        </option>
                                        <option value="60" {if ($FULFILLMENT[0]['value_variance'] == '60')} selected="selected"{/if}>
                                            60%
                                        </option>
                                        <option value="65" {if ($FULFILLMENT[0]['value_variance'] == '65')} selected="selected"{/if}>
                                            65%
                                        </option>
                                        <option value="70" {if ($FULFILLMENT[0]['value_variance'] == '70')} selected="selected"{/if}>
                                            70%
                                        </option>
                                        <option value="75" {if ($FULFILLMENT[0]['value_variance'] == '75')} selected="selected"{/if}>
                                            75%
                                        </option>
                                        <option value="80" {if ($FULFILLMENT[0]['value_variance'] == '80')} selected="selected"{/if}>
                                            80%
                                        </option>
                                        <option value="85" {if ($FULFILLMENT[0]['value_variance'] == '85')} selected="selected"{/if}>
                                            85%
                                        </option>
                                        <option value="90" {if ($FULFILLMENT[0]['value_variance'] == '90')} selected="selected"{/if}>
                                            90%
                                        </option>
                                        <option value="95" {if ($FULFILLMENT[0]['value_variance'] == '95')} selected="selected"{/if}>
                                            95%
                                        </option>
                                        <option value="100" {if ($FULFILLMENT[0]['value_variance'] == '100')} selected="selected"{/if}>
                                            100%
                                        </option>
                                    </select>
                                    <input type="hidden" name="type_dao_inf_0" id="type_dao_inf_0"
                                           value="{$FULFILLMENT[0]['type_variance']}">
                                </td>
                            </tr>
                            <tr class="even" role="row">
                                <td style="font-size:12px; width:50%">{$MOD.PERCENT_VAR_CLOSE_TARGET}
                                    <input type="hidden" name="boxscorecump_dao_1" value="{$FULFILLMENT[1]['id']}">
                                    <input type="hidden" name="fulfillment_1" value="Near the goal">
                                </td>
                                <td>
                                    <select id="dao_inf_1" name="dao_inf_1" class="form-control">
                                        <option value="">{$MOD.LBL_SELECTION_DEFAULT}</option>
                                        <option value="0" {if ($FULFILLMENT[1]['value_variance'] == '0')} selected="selected"{/if}>
                                            0%
                                        </option>
                                        <option value="5" {if ($FULFILLMENT[1]['value_variance'] == '5')} selected="selected"{/if}>
                                            5%
                                        </option>
                                        <option value="10" {if ($FULFILLMENT[1]['value_variance'] == '10')} selected="selected"{/if}>
                                            10%
                                        </option>
                                        <option value="15" {if ($FULFILLMENT[1]['value_variance'] == '15')} selected="selected"{/if}>
                                            15%
                                        </option>
                                        <option value="20" {if ($FULFILLMENT[1]['value_variance'] == '20')} selected="selected"{/if}>
                                            20%
                                        </option>
                                        <option value="25" {if ($FULFILLMENT[1]['value_variance'] == '25')} selected="selected"{/if}>
                                            25%
                                        </option>
                                        <option value="30" {if ($FULFILLMENT[1]['value_variance'] == '30')} selected="selected"{/if}>
                                            30%
                                        </option>
                                        <option value="35" {if ($FULFILLMENT[1]['value_variance'] == '35')} selected="selected"{/if}>
                                            35%
                                        </option>
                                        <option value="40" {if ($FULFILLMENT[1]['value_variance'] == '40')} selected="selected"{/if}>
                                            40%
                                        </option>
                                        <option value="45" {if ($FULFILLMENT[1]['value_variance'] == '45')} selected="selected"{/if}>
                                            45%
                                        </option>
                                        <option value="50" {if ($FULFILLMENT[1]['value_variance'] == '50')} selected="selected"{/if}>
                                            50%
                                        </option>
                                        <option value="55" {if ($FULFILLMENT[1]['value_variance'] == '55')} selected="selected"{/if}>
                                            55%
                                        </option>
                                        <option value="60" {if ($FULFILLMENT[1]['value_variance'] == '60')} selected="selected"{/if}>
                                            60%
                                        </option>
                                        <option value="65" {if ($FULFILLMENT[1]['value_variance'] == '65')} selected="selected"{/if}>
                                            65%
                                        </option>
                                        <option value="70" {if ($FULFILLMENT[1]['value_variance'] == '70')} selected="selected"{/if}>
                                            70%
                                        </option>
                                        <option value="75" {if ($FULFILLMENT[1]['value_variance'] == '75')} selected="selected"{/if}>
                                            75%
                                        </option>
                                        <option value="80" {if ($FULFILLMENT[1]['value_variance'] == '80')} selected="selected"{/if}>
                                            80%
                                        </option>
                                        <option value="85" {if ($FULFILLMENT[1]['value_variance'] == '85')} selected="selected"{/if}>
                                            85%
                                        </option>
                                        <option value="90" {if ($FULFILLMENT[1]['value_variance'] == '90')} selected="selected"{/if}>
                                            90%
                                        </option>
                                        <option value="95" {if ($FULFILLMENT[1]['value_variance'] == '95')} selected="selected"{/if}>
                                            95%
                                        </option>
                                        <option value="100" {if ($FULFILLMENT[1]['value_variance'] == '100')} selected="selected"{/if}>
                                            100%
                                        </option>
                                    </select>
                                    <input type="hidden" name="type_dao_inf_1" id="type_dao_inf_1"
                                           value="{$FULFILLMENT[1]['type_variance']}">
                                </td>
                            </tr>
                            {* fuente de datos *}
                            <tr class="even" role="row">
                                <td style="font-size:12px; width:50%">{$MOD.DATA_SOURCE}
                                </td>
                                <td>
                                    <select id="data_source" name="dataSource" class="form-control"
                                            {if (!$IS_MOTHER && $BOX_SCORE->boxs[0]['is_editable'] eq 'NO')}disabled
                                            title="Bloqueado para edición"{/if}>
                                        <option value=""
                                                {if $MODE eq 'create'}selected="selected"{/if}>{$MOD.LBL_SELECTION_DEFAULT}</option>
                                        <option value="0"
                                                {if (($FIELD_NAME eq NULL) && ($CALCULATED_SYSTEM eq NULL)) && ($MODE eq 'edit')}selected="selected"{/if}>{$MOD.OP_MANUAL}</option>
                                        <option value="1"
                                                {if ($FIELD_NAME neq NULL) && ($MODE eq 'edit')}selected="selected"{/if}>{$MOD.OP_AUTOMATIC}</option>
                                        <option value="2"
                                                {if ($CALCULATED_SYSTEM neq NULL) && ($MODE eq 'edit')}selected="selected"{/if}>{$MOD.OP_CALCULATION_ENGINE}</option>
                                    </select>
                                </td>
                            </tr>
                            {* / fuente de datos *}
                            {* modulos *}
                            <tr id="box_score-row" class="even {if $FLD_MODULE eq NULL}hide{/if}" role="row">
                                <td style="font-size:12px; width:50%">{$MOD.MODULE_FOR_CALCULATION}
                                </td>
                                <td>
                                    <select id="fldmodule" name="fldmodule" class="form-control"
                                            {if (!$IS_MOTHER && $BOX_SCORE->boxs[0]['is_editable'] eq 'NO')}disabled
                                            title="Bloqueado para edición"{/if}>
                                        <option value="">{$MOD.LBL_SELECTION_DEFAULT}</option>
                                        {foreach $MODULES as $module}
                                            <option value="{$module.name}"
                                                    {if $FLD_MODULE eq $module.name}selected="selected"{/if}>{$module.tablabel}</option>
                                        {/foreach}
                                    </select>
                                </td>
                            </tr>
                            {* / modulos *}
                            {* campos calculados *}
                            <tr id="calculated-field-row" class="even {if $FIELD_NAME eq NULL}hide{/if}" role="row">
                                <td style="font-size:12px; width:50%">{$MOD.CALCULATED_FIELDS}
                                </td>
                                <td id="calculated-field-select">
                                    <select id="calculateField" name="calculateField" class="form-control"
                                            {if (!$IS_MOTHER && $BOX_SCORE->boxs[0]['is_editable'] eq 'NO')}disabled
                                            title="Bloqueado para edición"{/if}>
                                        <option value="">{$MOD.LBL_SELECTION_DEFAULT}</option>
                                        {if $FIELD_NAME neq NULL}
                                            {foreach $FIELDS as $field}
                                                <option value="{$field.fieldname}"
                                                        {if $FIELD_NAME eq $field.fieldname}selected="selected"{/if}>{$field.fieldlabel}</option>
                                            {/foreach}
                                        {/if}
                                    </select>
                                    <span id="calculateField-help" class="help-block"></span>
                                </td>
                            </tr>
                            {* calculation engine *}
                            <tr id="calculated-system-row" class="even {if $CALCULATED_SYSTEM eq NULL}hide{/if}"
                                role="row">
                                <td style="font-size:12px; width:50%">{$MOD.CALCULATION_ENGINE}
                                </td>
                                <td id="calculation_engine-field-select">
                                    <select id="calculationEngine" name="calculationEngine" class="form-control"
                                            {if (!$IS_MOTHER && $BOX_SCORE->boxs[0]['is_editable'] eq 'NO')}disabled
                                            title="Bloqueado para edición"{/if}>
                                        <option value="">{$MOD.LBL_SELECTION_DEFAULT}</option>
                                        {if $CALCULATION_ENGINE neq NULL}
                                            {foreach $CALCULATION_ENGINE as $calculation}
                                                <option value="{$calculation->getCalculationName()}"
                                                        {if $calculation->getCalculationName() eq $CALCULATED_SYSTEM}selected{/if}
                                                >{$calculation->getModuleName()|module_label: $ADB}
                                                    : {$calculation->getName ()}</option>
                                            {/foreach}
                                        {/if}
                                    </select>
                                    <span id="calculateField-help" class="help-block"></span>
                                </td>
                            </tr>
                            {* calculation engine *}
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-warning" id="btnclose"
                            onclick="jQuery ('#addIndicators').removeClass ('md-show'); jQuery ('.md-overlay').css ({ldelim} opacity: 0.0, visibility: 'hidden' {rdelim}); jQuery ('#addIndicators').html(''); return false;">{$APP.LBL_CANCEL_BUTTON_LABEL}</button>
                    <button type="submit" id="btnSave" name="btnSave" class="btn btn-success"
                            onclick="return validateIndicator()">{$APP.LBL_SAVE_BUTTON_LABEL}</button>
                </div>
            </form>
        </div>
    </div>

    <script src="modules/{$MODULE}/{$MODULE}.js" type="text/javascript"></script>
    <script type="text/javascript">
        {literal}
        jQuery(document).ready(function () {
            var count = {/literal}{if (isset ($RECORD))}{$BOX_SCORE->boxs[0]['all_objetive']|@count}{else}0{/if},
                defaultOption = "{$MOD.LBL_SELECTION_DEFAULT}";
            {literal}

            var calculatedFieldRow = jQuery('#calculated-field-row'),
                forModuleRow = jQuery('#box_score-row'),
                forModule = jQuery("#fldmodule"),
                calculateFieldSelect = jQuery('#calculateField'),
                calculationEngineRow = jQuery('#calculated-system-row'),
                calculationEngineSelect = jQuery('#calculationEngine');

            jQuery("#addobjetive").click(function () {
                if (jQuery("#targetmonth_0").val() == 'all') {
                    jQuery('.addtargetmonth').remove();
                    var html = getHtmlObjective(0);
                } else {
                    count = count + 1;
                    var html = getHtmlObjective(count);
                }
                jQuery("#bodyObjtable").append(html);
            });

            jQuery("#addallobjetive").click(function () {
                jQuery('.addtargetmonth').remove();
                console.log('addallobjetive');
                var html = getHtmlObjective(0);
                jQuery("#bodyObjtable").append(html);
                jQuery('#targetmonth_0').append('<option value="all" selected="selected">' + alert_arr.ALL_MONTH_OBJECT + '</option>');
                jQuery('#targetmonth_0').attr("disabled", true);
            });


            jQuery("#targetmonth").change(function () {
                var record = jQuery("input[name=record]").val();
                var boxscoreid = jQuery("input[name=boxscoreid]").val();
                var monthsearch = jQuery("#targetmonth").val();
                new Ajax.Request(
                    'index.php',
                    {
                        queue: {position: 'end', scope: 'command'},
                        method: 'post',
                        postBody: 'module=boxscore&action=indicatorspanelAjax&file=Searchkpiboxscore&record=' + record + '&boxscoreid=' + boxscoreid + '&monthsearch=' + monthsearch,
                        onComplete: function (response) {
                            if (response.responseText != '') {
                                var str = response.responseText;
                                var res = str.split("---");
                                var v = res[0].split("@@");
                                var v1 = res[1].split("@@");

                                jQuery("#objetive").val(v[1]);
                                jQuery("#boxscorecump_dao_0").val(v[2]);
                                jQuery("#dao_inf_0").val(v[3]);
                                jQuery("#type_dao_inf_0").val(v[5]);
                                jQuery("#operator").val(v[6]);

                                jQuery("#boxscorecump_dao_1").val(v1[2]);
                                jQuery("#dao_inf_1").val(v1[3]);
                                jQuery("#type_dao_inf_1").val(v1[5]);

                            }
                        }
                    }
                );
            });

            jQuery('#data_source').change(function () {
                var value = jQuery(this).val();

                if (value == '1' && forModuleRow.hasClass('hide')) {
                    forModuleRow.removeClass('hide');
                    calculationEngineRow.addClass('hide');
                    calculationEngineSelect.val('');
                    forModule.focus();
                } else if (value == '2' && calculationEngineRow.hasClass('hide')) {
                    calculationEngineRow.removeClass('hide');
                    forModuleRow.addClass('hide');
                    calculatedFieldRow.addClass('hide');
                    calculateFieldSelect.val('');
                    forModule.val('');
                    calculationEngineSelect.focus();
                } else {
                    forModuleRow.addClass('hide');
                    forModule.val('');
                    if (!calculatedFieldRow.hasClass('hide')) {
                        calculatedFieldRow.addClass('hide');
                        forModule.val('');
                        calculateFieldSelect.val('');
                    }
                    if (!calculationEngineRow.hasClass('hide')) {
                        calculationEngineRow.addClass('hide');
                        calculationEngineSelect.val('');
                    }
                }
            });

            forModule.change(function () {
                var value = jQuery(this).val(),
                    errorText = jQuery('#calculateField-help');
                errorText.html('');
                if (value != '') {
                    if (calculatedFieldRow.hasClass('hide')) {
                        calculatedFieldRow.addClass('hide');
                    }
                    new Ajax.Request(
                        'index.php',
                        {
                            queue: {position: 'end', scope: 'command'},
                            method: 'post',
                            postBody: 'module=indicatorspanel&action=indicatorspanelAjax&file=AjaxBoxScore&fldmodule=' + value + '&function=getFields',
                            onComplete: function (response) {
                                if (response.responseText != '') {
                                    var fields = JSON.parse(response.responseText);
                                    calculateFieldSelect.empty();
                                    calculateFieldSelect.append(
                                        jQuery(
                                            '<option>',
                                            {
                                                value: '',
                                                text: defaultOption
                                            }
                                        )
                                    );
                                    jQuery.each(
                                        fields,
                                        function (i, field) {
                                            calculateFieldSelect.append(
                                                jQuery(
                                                    '<option>',
                                                    {
                                                        value: field.fieldname,
                                                        text: field.fieldlabel
                                                    }
                                                )
                                            )
                                        }
                                    );
                                    calculatedFieldRow.removeClass('hide');
                                    calculateFieldSelect.focus();
                                } else {
                                    errorText.html(response.responseText);
                                }
                            }
                        }
                    );
                }
            })

        });

        function getHtmlObjective(count) {
            var html = '<tr class="addtargetmonth" style="background-color: #e8e8e8;" role="row" id="fileObject_' + count + '"> ' +
                '<td>' +
                '<select class="form-control targetmonth" id="targetmonth_' + count + '" name="targetmonth[]">' +
                '<option value="">{/literal}{$MOD.LBL_SELECTION_MONTH}{literal}</option>' +
                '<option value="01">{/literal}{$MOD.LBL_ENERO}{literal}</option>' +
                '<option value="02">{/literal}{$MOD.LBL_FEBRERO}{literal}</option>' +
                '<option value="03">{/literal}{$MOD.LBL_MARZO}{literal}</option>' +
                '<option value="04">{/literal}{$MOD.LBL_ABRIL}{literal}</option>' +
                '<option value="05">{/literal}{$MOD.LBL_MAYO}{literal}</option>' +
                '<option value="06">{/literal}{$MOD.LBL_JUNIO}{literal}</option>' +
                '<option value="07">{/literal}{$MOD.LBL_JULIO}{literal}</option>' +
                '<option value="08">{/literal}{$MOD.LBL_AGOSTO}{literal}</option>' +
                '<option value="09">{/literal}{$MOD.LBL_SEPTIEMBRE}{literal}</option>' +
                '<option value="10">{/literal}{$MOD.LBL_OCTUBRE}{literal}</option>' +
                '<option value="11">{/literal}{$MOD.LBL_NOVIEMBRE}{literal}</option>' +
                '<option value="12">{/literal}{$MOD.LBL_DICIEMBRE}{literal}</option>' +
                '</select>' +
                '</td>' +
                '<td>' +
                '<select class="form-control operator" id="operator_' + count + '" name="operator[]"';
            if (count == 0) {
                html += 'onchange="selectAllOperator()"';
            }
            html += ' >' +
                '<option value="less-equal">&lt;=</option>' +
                '<option value="greater-equal">&gt;=</option>' +
                '</select>';
            if (count == 0) {
                html += '<input type="hidden" id="all_operator" name="all_operator" value="less-equal">';
            }
            html += '</td>' +
                '<td>' +
                '<input type="text" class="form-control objetive" onkeyup="validateDecimal32General(\'objetive_' + count + '\')" value="" id="objetive_' + count + '" name="objetive[]" placeholder="{/literal}{$MOD.Ingresar} {$MOD.LBL_OBJECT}{literal}">' +
                '</td>' +
                '<td>';
            if (count > 0) {
                html += '<input width="16" type="image" height="16" title="Delete" src="themes/images/remove.png" onclick="deleteOtherOperation(\'fileObject_' + count + '\')">';
            }
            html += '</td>' +
                '</tr>';
            return html;
        }
        {/literal}
    </script>
{/strip}