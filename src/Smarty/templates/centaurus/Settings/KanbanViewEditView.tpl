{strip}
    {if (isset ($VIEW))}
        {assign var='viewAdvancedFilterGroups' value=$VIEW->getAdvancedFilterGroups ()}
        {assign var='viewId' value=$VIEW->getIdKanban ()}
        {assign var='fieldName' value=$VIEW->getFieldName ()}
        {assign var='moduleName' value=$VIEW->getModuleName ()}
        {assign var='isDefaultView' value=$VIEW->getIsDefaultView ()}
        {assign var='label' value=$VIEW->getLabel ()}
        {assign var='moduleTabId' value=$VIEW->getIdTabModule}
        {assign var='codeAplplication' value=$VIEW->getCodeApplication ()}
        {assign var='fieldId' value=$VIEW->getIdField ()}
        {assign var='viewStandardFilter' value=$VIEW->getStandardFilter ()}
        {assign var='cardField' value=$VIEW->getKanbanCards ()}
        {assign var='rules' value=$VIEW->getKanbanField ()}
        {assign var='isvisibleinlist' value=$VIEW->getInListView ()}
        {assign var='isLocked' value=$VIEW->isLocked ()}
    {else}
        {assign var='viewAdvancedFilterGroups' value=null}
        {assign var='viewId' value=null}
        {assign var='fieldName' value=null}
        {assign var='moduleName' value=$RTN_MODULE}
        {assign var='isDefaultView' value=null}
        {assign var='label' value=null}
        {assign var='moduleTabId' value=null}
        {if $IS_INSTANCE}
            {assign var='codeAplplication' value='crm'}
        {else}
            {assign var='codeAplplication' value=null}
        {/if}
        {assign var='fieldId' value=null}
        {assign var='viewStandardFilter' value=null}
        {assign var='cardField' value=null}
        {assign var='rules' value=null}
        {assign var='isvisibleinlist' value=null}
        {assign var='isLocked' value=0}
    {/if}
    {if (isset ($viewStandardFilter))}
        {assign var='viewStandardFilterPeriod' value=$viewStandardFilter->getPeriod ()}
    {else}
        {assign var='viewStandardFilterPeriod' value=null}
    {/if}
    {assign var='kanbaModuleName' value=null}
    {assign var='fieldOperType' value=null}
    <link rel="stylesheet" href="include/colorpicker/css/colorpicker.css" type="text/css" />
    <link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/libs/datepicker.css" />
    <style type="text/css">
        {literal}
            .required {
                color: #FF0000;
            }

            label {
                font-size: inherit;
                font-weight: 300;
            }

            .color {
                border: 1px solid #DDDDDD;
                border-radius: 3px;
                cursor: pointer;
                height: 34px;
            }

            .field-container>label>.form-radio {
                margin-bottom: 0;
                margin-top: 0;
                padding-bottom: 0;
                padding-top: 0;
            }

            .col-constraints>.form-control {
                display: inline-block;
                margin-right: 5px;
                width: auto;
            }

            .col-constraints>.glue[disabled="disabled"] {
                display: none;
            }

            .col-actions {
                text-align: center;
                width: 80px;
            }

            .btn.btn-icon {
                font-size: 12px;
                line-height: 1.5;
                padding: 3px 7px;
            }

            .main-box>.main-box-header {
                padding: 20px;
            }

            .action-bar .btn {
                margin-left: 5px;
            }

        {/literal}
    </style>
    <div class="row">
        <div class="col-xs-12">
            <h1>
                <a href="{if $IS_INSTANCE}index.php?module={$RTN_MODULE}&action=index&tab=kanban
                        {else}
                        index.php?module=Settings&action=KanbanViewListView&parenttab=Settings
                        {/if}">Vistas de Kanban</a>
            </h1>
        </div>
    </div>
    {if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
        <div class="row">
            <div class="alert alert-{if (isset ($IS_ERROR)) && ($IS_ERROR)}danger{else}success{/if}">
                <strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
            </div>
        </div>
    {/if}
    <form method="post" action="index.php" id="KambanViewForm" name="KanbanView"
        onsubmit="return KanbanUtils.validateForm (this);">
        <input type="hidden" name="module" value="Settings" />
        <input type="hidden" name="action" value="KanbanViewSaveView" />
        <input type="hidden" name="record" id="record" value="{$viewId}" />
        <input type="hidden" name="fieldname" id="fieldname" value="{$fieldName}" />
        <input type="hidden" name="modulename" id="modulename" value="{$MODULENAME}" />
        <input type="hidden" name="mode" id="mode" value="{if (!empty ($MODE))}{$MODE}{/if}" />
        <input type="hidden" name="firstaccion" id="firstaccion" value="{if (!empty ($MODE))}1{else}0{/if}" />
        <input type="hidden" name="isDefaultView" value="{$isDefaultView}">
        <input type="hidden" name="prevDefaultView" id="prevDefaultView" value="">
        <input type="hidden" name="return_module" value="{$RTN_MODULE}">
        <input type="hidden" name="locked" value="{$isLocked}">
        <input type="hidden" id="isInstance" value="{$IS_INSTANCE}">
        <input type="hidden" name="Ajax" value="true" />
        <div class="row">
            <div class="col-xs-12">
                <div class="main-box">
                    <header class="main-box-header clearfix">
                        <h2 class="pull-left">Información general</h2>
                        <div class="action-bar pull-right">
                            <button type="submit" class="btn btn-info">Guardar</button>
                            <a href="{if $IS_INSTANCE}index.php?module={$RTN_MODULE}&action=index&tab=kanban
                                    {else}
                                    index.php?module={$MODULENAME}&action=ListView&parenttab=
                                    {/if}" class="btn btn-warning">Cancelar</a>
                            {*index.php?module=Settings&action=KanbanViewListView&parenttab=Settings*}
                        </div>
                    </header>
                    <div class="main-box-body">

                        <div class="row" {if $IS_INSTANCE}style="display: none" {/if}>
                            <div class="col-md-6">
                                {if !$IS_INSTANCE}
                                    {* Nombre *}
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="label-input">
                                                <label for="label">Nombre <span class="required">*</span></label>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-8 field-container">
                                            <div class="input-group" style="width: 100%;">
                                                <input type="text" id="label" name="label" value="{$label}" maxlength="50"
                                                    class="form-control" />
                                            </div>
                                        </div>
                                    </div>
                                    {* Nombre *}
                                {/if}
                            </div>
                            <div class="col-md-6">
                                {* Aplicación *}
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="label-input">
                                            <label for="appname">Aplicación <span class="required">*</span></label>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-8 field-container">
                                        <select id="codeApp" name="codeApp" class="form-control"
                                            onchange="KanbanUtils.selectApp('{$moduleTabId}','{$MODE}');">
                                            <option value="">Seleccione ...</option>
                                            {foreach $APPLICATIONS as $keyApp => $itemApp}
                                                {if $keyApp == $codeAplplication}
                                                    {assign var='selected' value='selected="selected"'}
                                                {else}
                                                    {assign var='selected' value=''}
                                                {/if}
                                                <option value="{$keyApp}" {$selected}>{$itemApp.app_name}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                </div>
                                {* Aplicación *}
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="row" {if $IS_INSTANCE}style="display: none" {/if}>
                                    <div class="col-md-4 text-right">
                                        <label for="fromfieldname">Módulo <span class="required">*</span></label>
                                    </div>
                                    <div class="form-group col-md-8 field-container">
                                        <input type="hidden" name="fromfieldid" id="fromfieldid" value="{$fieldId}" />
                                        <select class="form-control" id="codeElement" name="codeElement" title="Modules"
                                            onchange="KanbanUtils.selectModule(this, '{$fieldId}','{$MODE}')">
                                            {if isset($AVAILABLE_MODULES)}
                                                <option value="">Seleccione ...</option>
                                                {foreach $AVAILABLE_MODULES as $row}
                                                    <option value="{$row.tabid}" {if ($row.name == $MODULENAME)} selected="selected"
                                                        {$kanbaModuleName = $row.tablabel}{/if} tabname="{$row.name}"
                                                        tablabel="{$row.tablabel}">{$row.tablabel}</option>
                                                {/foreach}
                                            {else}
                                                <option value="">Seleccione ...</option>
                                            {/if}
                                        </select>
                                    </div>
                                </div>
                                {if $IS_INSTANCE}
                                    {* Nombre *}
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="label-input">
                                                <label for="label">Nombre <span class="required">*</span></label>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-8 field-container">
                                            <div class="input-group" style="width: 100%;">
                                                <input type="text" id="label" name="label" value="{$label}" maxlength="50"
                                                    class="form-control" />
                                            </div>
                                        </div>
                                    </div>
                                    {* Nombre *}

                                {/if}
                            </div>
                            <div class="col-md-6">

                                <div class="row">
                                    <div class="col-md-4 text-right">
                                        <label for="fromfieldname">Campo <span class="required">*</span></label>
                                    </div>
                                    <div class="form-group col-md-8 field-container">
                                        <select class="form-control" id="codeElementField" name="codeElementField"
                                            title="Field" onchange="KanbanUtils.selectField(this, '{$MODE}')">
                                            {if isset($AVAILABLE_MODULE_FIELDS)}
                                                <option value="">Seleccione ...</option>
                                                {foreach $AVAILABLE_MODULE_FIELDS as $row}
                                                    <option value="{$row.fieldid}" {if ($row.fieldid == $fieldId)}
                                                        selected="selected" {/if} fieldname="{$row.fieldname}">{$row.fieldlabel}
                                                    </option>
                                                {/foreach}
                                            {else}
                                                <option value="">Seleccione ...</option>
                                            {/if}
                                        </select>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-4 text-right">

                                    </div>
                                    <div class="form-group col-md-8 field-container">

                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-4 text-right">&nbsp;</div>
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <div class="checkbox">
                                                <label>
                                                    <input {if $kanbaModuleName eq NULL}disabled {/if} type="checkbox"
                                                        id="isIncluded" name="isIncluded" value="1"
                                                        {if $isvisibleinlist eq 1} checked="checked" {/if}
                                                        onclick="KanbanUtils.setViewIncluded ()">
                                                    <label style="margin-left: 0; padding-left: 0" id="is_Included"
                                                        for="is-Included"> Incluir en la vista de lista del
                                                        módulo {$kanbaModuleName}</label>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div id="isDefault-row" class="row {if $isvisibleinlist eq 0} hide{/if}">
                            <div class="col-md-6">&nbsp;</div>
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-4 text-right">&nbsp;</div>
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <div class="checkbox">
                                                <label>
                                                    <input {if $kanbaModuleName eq NULL}disabled {/if} type="checkbox"
                                                        type="checkbox" id="isDefault" name="setDefault" value="1"
                                                        {if $isDefaultView eq 1} checked="checked" {/if}
                                                        onclick="KanbanUtils.setDefaultView ()">
                                                    <label style="margin-left: 0; padding-left: 0" id="isDefault_kv"
                                                        for="isDefault"> Convertir en la vista principal del
                                                        módulo {$kanbaModuleName}</label>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="main-box">
                    <header class="main-box-header clearfix">
                        <h2 class="pull-left">Campos de Tarjetas</h2>
                    </header>
                    <div class="main-box-body" id="cardField">
                        <div class="row">
                            <div class="table-responsive">
                                <table id="card-field-table" class="table cardField">
                                    <thead>
                                        <tr>
                                            <th class="col-constraints" style="width: 88%">Campo</th>
                                            <th class="col-actions" style="text-align: center;width: 12%">Eliminar</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {if ($cardField neq NULL)}
                                            {foreach $cardField as $cardItem}
                                                {if $fieldId != $cardItem->getIdField ()}
                                                    <tr class="card">
                                                        <td class="col-constraints">
                                                            <input name="fieldsIds[]" value="{$cardItem->getIdField ()}" type="hidden">
                                                            <input type="hidden" name="fieldcardId[]"
                                                                value="{$cardItem->getIdCardField ()}">
                                                            <input type="hidden" name="fieldId_{$cardItem->getIdField ()}"
                                                                class="hiddenField" value="{$cardItem->getIdField ()}">
                                                            <span>{$cardItem->getFieldLabel ()}</span>
                                                        </td>
                                                        <td class="col-actions">
                                                            <button type="button" class="btn btn-danger"
                                                                fieldId="{$cardItem->getIdField ()}" title="Eliminar"
                                                                onclick="KanbanUtils.deleteField (this);">
                                                                <i class="fa fa-trash-o"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                {/if}
                                            {/foreach}
                                        {/if}
                                    </tbody>
                                </table>
                            </div>
                            <div class="action-bar text-center">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="label-input">
                                                    <label for="label">Campos Diponibles <span
                                                            class="required">*</span></label>
                                                </div>
                                            </div>
                                            <div class="form-group col-md-4 field-container">
                                                <div class="input-group" style="width: 100%;">
                                                    <select class="form-control" id="cardFieldElement"
                                                        name="cardFieldElement" onchange="KanbanUtils.addTableCardField();">
                                                        {if isset($AVAILABLE_MODULE_FIELDS_CARDS)}
                                                            <option value="">Seleccione ...</option>
                                                            {foreach $AVAILABLE_MODULE_FIELDS_CARDS as $row}
                                                                <option value="{$row.fieldid}" {if ($row.fieldid == fieldId)}
                                                                    selected="selected" {/if} fieldname="{$row.fieldname}"
                                                                    typeofdata="{$row.typeofdata}">{$row.fieldlabel}</option>
                                                            {/foreach}
                                                        {else}
                                                            <option value="">Seleccione ...</option>
                                                        {/if}
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="main-box">
                    <header class="main-box-header clearfix">
                        <h2 class="pull-left">Reglas</h2>
                        {if (isset ($AVAILABLE_FIELD_VALUES)) && (!empty ($AVAILABLE_FIELD_VALUES))}
                            <div class="action-bar pull-right">
                                <button type="button" class="btn btn-primary" id="sync-rules-btn"
                                    title="Agrega reglas para los valores del campo que aun no tienen regla (por ejemplo, valores nuevos en el picklist o pipeline)."
                                    onclick="KanbanUtils.syncRulesWithFieldValues();">
                                    <i class="fa fa-refresh" aria-hidden="true"></i> Sincronizar valores
                                </button>
                            </div>
                        {/if}
                    </header>
                    {if (isset ($AVAILABLE_FIELD_VALUES))}
                        <script type="application/json" id="available-field-values">
                            {$AVAILABLE_FIELD_VALUES|@json_encode nofilter}
                        </script>
                    {/if}
                    <div class="main-box-body" id="rules">
                        <div class="row">
                            <div class="table-responsive">
                                <table class="table rules">
                                    <thead>
                                        <tr>
                                            <th class="col-constraints" style="width: 35%">Valor</th>
                                            <th class="col-color" style="width: 10%">Color</th>
                                            <th colspan="2" class="col-actions" style="text-align: center;width: 40%">
                                                cálculo
                                            </th>
                                            <th class="col-color" style="width: 15%">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {if (!empty ($rules))}
                                            {foreach $rules as $rule}
                                                <tr class="rule">
                                                    <td class="col-constraints">
                                                        <input name="ruleids[]" value="{$rule->getIdKanbanFieldConfig ()}"
                                                            type="hidden">
                                                        <input type="hidden" name="pickfieldid[]"
                                                            value="{$rule->getIdPickField ()}">
                                                        <input type="hidden" name="pickfieldLabel[]" class="hiddenField"
                                                            value="{$rule->getFieldName ()}">
                                                        <span>{$rule->getFieldName ()}</span>
                                                    </td>
                                                    <td class="col-color">
                                                        <input type="text" name="rulebackgroundcolors[]"
                                                            value="{if ($rule->getBackgroundColor () neq NULL)} {$rule->getBackgroundColor ()}{else}#A69E9E{/if}"
                                                            class="color" readonly="readonly" maxlength="6" size="6"
                                                            style="background-color: {if ($rule->getBackgroundColor () neq NULL)} {$rule->getBackgroundColor ()} {else} #A69E9E{/if}; color: {if ($rule->getBackgroundColor () neq NULL)} {$rule->getBackgroundColor ()} {else} #A69E9E{/if};"
                                                            placeholder="" />
                                                    </td>
                                                    <td style="width: 20%">
                                                        <select class="form-control col-md-4" id="calculationSelect"
                                                            name="calculationField[]" title="Campo para el cálculo"
                                                            onchange="KanbanUtils.getCalculationOperators (this)">
                                                            {if isset($AVAILABLE_MODULE_FIELDS_CARDS)}
                                                                <option value="">Seleccione ...</option>
                                                                {foreach $AVAILABLE_MODULE_FIELDS_CARDS as $row}
                                                                    {assign var='tablefield' value="{$row.tablename}{'.'}{$row.fieldname}"}
                                                                    <option value="{$row.tablename}.{$row.fieldname}"
                                                                        {if ($tablefield == $rule->getFieldNameOperation ())}
                                                                            selected="selected"
                                                                        {assign var='fieldOperType' value=$row.typeofdata} {/if}
                                                                        typeofdata="{$row.typeofdata}">{$row.fieldlabel}</option>
                                                                {/foreach}
                                                            {else}
                                                                <option value="">Seleccione ...</option>
                                                            {/if}
                                                        </select>
                                                    </td>
                                                    <td style="width: 20%">
                                                        <select class="form-control col-md-4" id="calculationSelect"
                                                            name="calculationSelect[]" title="Cálculo de la columna">
                                                            {if isset($CALCULATION_OPERATORS) && ($rule->getOperation() neq NULL)}
                                                                <option value="">Seleccione ...</option>
                                                                {foreach $CALCULATION_OPERATORS as $key =>  $row}
                                                                    <option value="{$key}" {if ($key == $rule->getOperation ())}
                                                                            selected="selected"
                                                                        {elseif !in_array($fieldOperType, $row->typeOfData)}
                                                                        disabled="disabled" {/if}>{$row->label}</option>
                                                                {/foreach}
                                                            {else}
                                                                <option value="">Seleccione ...</option>
                                                            {/if}
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-danger btn-xs"
                                                            onclick="KanbanUtils.rowDown(this)"><i class="fa fa-arrow-down"
                                                                aria-hidden="true"></i>
                                                        </button>&nbsp;
                                                        <button type="button" class="btn btn-primary btn-xs"
                                                            onclick="KanbanUtils.rowUp(this)"><i class="fa fa-arrow-up"
                                                                aria-hidden="true"></i>
                                                        </button>&nbsp;
                                                        <button type="button" class="btn btn-danger" title="Eliminar"
                                                            onclick="KanbanUtils.deleteRole (this);"><i class="fa fa-trash-o"></i>
                                                    </td>
                                                </tr>
                                            {/foreach}
                                        {/if}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {* CV Filters *}
        <div class="row">
            <div class="col-lg-12">
                <div class="main-box">
                    <header class="main-box-header clearfix">
                        <h2>Filtros</h2>
                    </header>
                    <div class="main-box-body clearfix">
                        <div class="tabs-wrapper">
                            <ul class="nav nav-tabs">
                                <li class="active"><a href="#pi" data-toggle="tab">{$MOD.LBL_STEP_3_TITLE}</a></li>
                                <li class=""><a href="#mi" data-toggle="tab">{$MOD.LBL_STEP_4_TITLE}</a></li>
                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane fade active in" id="pi">
                                    <div class="row standard-filter" style="margin-top: 20px;">
                                        <div class="form-group col-xs-12 col-md-6 col-lg-3">
                                            <label for="standard-filter-column">{$MOD.LBL_Select_a_Column}</label>
                                            <select id="standard-filter-column" name="standardfilter[column]"
                                                class="form-control" onchange="CustomViewUtils.setStandardFilter (this);">
                                                <option value="">Seleccione ...</option>
                                                {foreach $AVAILABLE_DATE_COLUMNS as $columnName => $columnLabel}
                                                    {assign var='dummy' value=explode(':', $columnName)}
                                                    <option value="{$columnName}"
                                                        {if (isset ($viewStandardFilter)) && ($viewStandardFilter->getTableName () == $dummy[0]) && ($viewStandardFilter->getColumnName () == $dummy[1]) && ($viewStandardFilter->getFieldName () == $dummy[2])}
                                                        selected="selected" {/if}>{$columnLabel}</option>
                                                {/foreach}
                                            </select>
                                        </div>
                                        <div class="form-group col-xs-12 col-md-6 col-lg-3">
                                            <label for="standard-filter-period">{$MOD.Select_Duration}</label>
                                            <select id="standard-filter-period" name="standardfilter[period]"
                                                class="form-control" onchange="CustomViewUtils.setPeriod (this);">
                                                <option value="">Seleccione ...</option>
                                                {foreach $AVAILABLE_PERIODS as $periodName => $periodLabel}
                                                    <option value="{$periodName}"
                                                        {if ($viewStandardFilterPeriod == $periodName)} selected="selected"
                                                        {/if}>{$periodLabel}</option>
                                                {/foreach}
                                            </select>
                                        </div>
                                        <div class="form-group col-xs-12 col-md-6 col-lg-3 standard-filter-date"
                                            {if ($viewStandardFilterPeriod != 'custom')} style="display: none;" {/if}>
                                            <label for="standard-filter-start-date">{$MOD.Start_Date}</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                                <input type="text" id="standard-filter-start-date"
                                                    name="standardfilter[startdate]"
                                                    value="{if (isset ($viewStandardFilter)) && (!empty ($viewStandardFilter->getStartDate ()))}{$viewStandardFilter->getStartDate ()->format ('Y-m-d')}{/if}"
                                                    class="form-control" placeholder="9999/99/99"
                                                    {if ($viewStandardFilterPeriod != 'custom')} disabled="disabled"
                                                    {/if} />
                                            </div>
                                        </div>
                                        <div class="form-group col-xs-12 col-md-6 col-lg-3 standard-filter-date"
                                            {if ($viewStandardFilterPeriod != 'custom')} style="display: none;" {/if}>
                                            <label for="standard-filter-end-date">{$MOD.End_Date}</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                                <input type="text" id="standard-filter-end-date"
                                                    name="standardfilter[enddate]"
                                                    value="{if (isset ($viewStandardFilter)) && (!empty ($viewStandardFilter->getEndDate ()))}{$viewStandardFilter->getEndDate ()->format ('Y-m-d')}{/if}"
                                                    class="form-control" placeholder="9999/99/99"
                                                    {if ($viewStandardFilterPeriod != 'custom')} disabled="disabled"
                                                    {/if} />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade filters-container" id="mi">
                                    <div id="kanban-advanced-filter" class="row filter-groups" style="margin-top: 20px;">
                                        {if (!empty ($viewAdvancedFilterGroups))}
                                            {foreach $viewAdvancedFilterGroups as $group}
                                                {include file='CustomViewAdvancedFilterGroup.tpl' GROUP=$group}
                                            {/foreach}
                                        {/if}
                                    </div>
                                    <div class="col-xs-12 text-center">
                                        <button type="button" class="btn btn-info"
                                            onclick="CustomViewUtils.addAdvancedFilterGroup (this);"
                                            title="Agregar grupo de filtros"><i class="fa fa-plus"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12 action-bar">
                    <div class="pull-right">
                        <button type="submit" class="btn btn-info">Guardar</button>
                        <a href="index.php?module={$MODULENAME}&action=ListView&parenttab="
                            class="btn btn-warning">Cancelar</a>
                    </div>
                </div>
            </div>
        </div>
        {* /cv Filtrs*}
    </form>
    <script type="text/javascript" src="themes/{$THEME}/js/jquery.maskedinput.min.js"></script>
    <script type="text/javascript" src="themes/{$THEME}/js/bootstrap-datepicker.js"></script>
    <script type="text/javascript" src="themes/{$THEME}/js/bootstrap-datepicker.es.js"></script>
    <script type="text/javascript" src="themes/{$THEME}/js/moment.min.js"></script>
    <script type="text/javascript" src="themes/{$THEME}js/bootstrap-timepicker.min.js"></script>
    <script type="text/javascript" src="include/colorpicker/js/colorpicker.js"></script>
    <script type="text/javascript" src="modules/Settings/kanban-view.js"></script>
    <script type="text/javascript" src="modules/CustomView/custom-view-utils.js"></script>
    <script type="text/html" id="advanced-filter-template">
        {include file='CustomViewAdvancedFilter.tpl' GROUP_ID='__GROUP_ID__' FILTER_ID='__FILTER_ID__'}
    </script>
    <script type="text/html" id="advanced-filter-group-template">
        {include file='CustomViewAdvancedFilterGroup.tpl' GROUP_ID='__GROUP_ID__'}
    </script>
    <script type="text/html" id="row-card-template">
        <tr class="card">
            <td class="col-constraints">
                <input name="fieldsIds[]" value="" type="hidden">
                <input type="hidden" name="fieldcardId[]" value="">
                <input type="hidden" name="fieldId_" class="hiddenField" value="">
                <span></span>
            </td>
            <td class="col-actions">
                <button type="button" class="btn btn-danger" fieldId="" title="Eliminar"
                    onclick="KanbanUtils.deleteField (this);">
                    <i class="fa fa-trash-o"></i>
                </button>
            </td>
        </tr>
    </script>
    <script type="text/html" id="row-rule-template">
        <tr class="rule">
            <td class="col-constraints">
                <input name="ruleids[]" value="" type="hidden">
                <input type="hidden" name="pickfieldid[]" value="">
                <input type="hidden" name="pickfieldLabel[]" class="hiddenField" value="">
                <span></span>
            </td>
            <td class="col-color">
                <input type="text" name="rulebackgroundcolors[]" value="#A69E9E" class="color" readonly="readonly"
                    maxlength="6" size="6" style="background-color: #A69E9E; color: #A69E9E;" placeholder="" />
            </td>
            <td style="width: 20%">
                <select class="form-control col-md-4" id="calculationSelect" name="calculationField[]"
                    title="Campo para el cálculo" onchange="KanbanUtils.getCalculationOperators (this)">
                    <option value="">Seleccione ...</option>
                </select>
            </td style="width: 20%">
            <td>
                <select class="form-control col-md-4" id="calculationSelect" name="calculationSelect[]"
                    title="Cálculo de la columna">
                    <option value="">Seleccione ...</option>
                </select>
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-xs" onclick="KanbanUtils.rowDown(this)"><i
                        class="fa fa-arrow-down" aria-hidden="true"></i></button>&nbsp;
                <button type="button" class="btn btn-primary btn-xs" onclick="KanbanUtils.rowUp(this)"><i
                        class="fa fa-arrow-up" aria-hidden="true"></i></button>&nbsp;
                <button type="button" class="btn btn-danger" title="Eliminar" onclick="KanbanUtils.deleteRole (this);">
                    <i class="fa fa-trash-o"></i>
                </button>
            </td>
        </tr>
    </script>
{/strip}
{if isset($AVAILABLE_COLUMNS)}
    <script type="text/javascript">
        CustomViewUtils.init({$AVAILABLE_COLUMNS|json_encode});
    </script>
{/if}