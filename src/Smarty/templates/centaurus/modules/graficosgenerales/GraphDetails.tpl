{strip}
    {if (isset ($GRAPH))}
        {assign var='graphApplicationCodes' value=$GRAPH->getApplicationCodes ()}
        {assign var='graphDateGrouping' value=$GRAPH->getDateGrouping ()}
        {assign var='graphFieldGrouping' value=$GRAPH->getGroupBy ()}
        {assign var='graphFieldOperation' value=$GRAPH->getFieldName ()|json_decode}
        {assign var='isLocked' value=$GRAPH->isLocked ()}
        {assign var='graphModuleName' value=$GRAPH->getModuleName ()|json_decode}
        {assign var='graphOperation' value=$GRAPH->getOperation ()|json_decode}
        {assign var='graphTitle' value=$GRAPH->getTitle ()}
        {assign var='graphType' value=$GRAPH->getType ()}
        {assign var='graphFilters' value=$GRAPH->getVariables ()|json_decode:true}
        {assign var='graphTypeName' value=null}
        {assign var='graphOptions' value=$GRAPH->getGraphicOptions ()|json_decode:true}
        {assign var='selectedFieldText' value=null scope="global"}
        {assign var='graphTypeStyle' value=nul}
        {assign var='numModules' value=$graphModuleName|@count}
    {else}
        {assign var='graphApplicationCodes' value=null}
        {assign var='graphDateGrouping' value=null}
        {assign var='graphFieldGrouping' value=null}
        {assign var='graphFieldOperation' value=null}
        {assign var='isLocked' value=0}
        {assign var='graphModuleName' value=null}
        {assign var='graphOperation' value=null}
        {assign var='graphTitle' value='Crear gráfico'}
        {assign var='graphType' value=null}
        {assign var='graphOptions' value=null}
        {assign var='graphTypeName' value=''}
        {assign var='graphTypeStyle' value=nul}
        {assign var 'numModules' value=4}
    {/if}
    <link type="text/css" rel="stylesheet" href="modules/graficosgenerales/graficosgenerales.css"/>
    <script type="text/javascript" src="themes/centaurus/js/charts/loader.js"></script>
    <form name="graphform" method="post" action="index.php" onsubmit="return GraphUtils.validateGraphForm ();">
        <input type="hidden" name="module" value="{$MODULE}"/>
        <input  id="action-form" type="hidden" name="action" value="SaveEditGraph"/>
        <input  id="ajax-action-form" type="hidden" name="Ajax" value="false"/>
        <input type="hidden" name="parenttab" value="Settings"/>
        <input type="hidden" id="is-instance" value="{$IS_INSTANCE}"/>
        <input type="hidden" name="activeTab" id="activeTab" value="{$activeTab}">
        <input type="hidden" name="return_module" value="{$RTN_MODULE}"/>
        <input type="hidden" name="isLocked" value="{$isLocked}">
      <input type="hidden" name="hasCalculation" id="hasCalculation" value="{if $graphFieldGrouping neq NULL}{$graphFieldGrouping}{else}0{/if}">
        {if (isset ($RECORD)) && (!empty ($RECORD))}
            <input type="hidden" name="record" value="{$RECORD}"/>
            {assign var="totalField" value=$GRAPH_FILTER['filterField']|@count}
        {else}
            {assign var="totalField" value=0}
        {/if}
        <div class="row" style="margin-bottom: 0px  !important;">
            <div class="col-lg-12">
                <div class="col-lg-6 pull-left">
                    <h1><a href="index.php?module={$RTN_MODULE}&action=index&tab=graphic">{$graphTitle}</a></h1>
                </div>
                <div class="col-lg-6 pull-right text-right">
                    <button type="button" class="btn btn-info"
                            onclick="GraphUtils.openGraphPreview ();">{$MOD.LBL_PREVIEW}</button>
                    &nbsp;
                    <button type="submit" id="btnsave" class="btn btn-primary">{$MOD.LBL_SAVE}</button>
                    &nbsp;
                    <a href="index.php?module={$RTN_MODULE}&action=index&activeTab={$activeTab}&tab=graphic"
                       class="btn btn-warning">{$MOD.LBL_CANCEL_BUTTON}</a>
                </div>
            </div>
        </div>
        <div class="row" style="margin-top: 0px !important;">
            <div id="wtwid" class="col-lg-12">
                <div class="main-box no-header clearfix">
                    <div class="col-md-12 page-header" style="margin: 0px;padding: 0px 2px; !important;">
                        <h4 class="pull-left" style="padding:0px 0px 0px 24px !important; margin-top: 0px;font-weight: bold ">Datos a graficar:</h4>
                    </div>
                    <div class="main-box-body clearfix">
                        <table class="table table-borderless">
                            <tr>
                                <td colspan="2">
                                    <p class="pull-left" style="padding-left: 4px;font-weight: bold">1.- Identificación del gráfico:</p>
                                </td>
                            </tr>
                            {* Titulo del Grafico *}
                            <tr>
                                <td class="col-labels">Título del gráfico</td>
                                <td id="gr-td-graphcTitule">
                                    <input type="text" class="form-control" id="graphcTitule" name="graphcTitule" title = 'El título del gráfico'
                                           placeholder="Personalizar el título del gráfico"
                                           value="{if $graphTitle neq 'Crear gráfico'}{$graphTitle|trim}{/if}">
                                    <span id="gr-graphcTitule" class="help-block"></span>
                                </td>
                            </tr>
                            {* Titulo del Grafico *}
                            {* Categoría donde ubicarlo*}
                            <tr>
                                <td class="col-labels" valign="top">Categoría donde ubicarlo</td>
                                <td id="gr-td-applicationcodes">
                                    <select multiple="multiple" id="applicationcodes" name="applicationcodes[]"
                                            class="form-control" title="">
                                        {foreach $ACTIVE_APPLICATIONS as $key => $application}
                                            {if $graphApplicationCodes neq NULL}
                                                <option value="{$key}" {if in_array ($key, $graphApplicationCodes)} selected="selected" {/if} >{$application}</option>
                                            {else}
                                                <option value="{$key}">{$application}</option>
                                            {/if}
                                        {/foreach}
                                    </select>
                                    <span id="gr-applicationcodes" class="help-block"></span>
                                </td>
                            </tr>
                            {* /Categoría donde ubicarlo*}
                            {* Tipo de gráfico*}
                            <tr>
                                <td class="col-labels">{$MOD.LBL_TIPO_GRAFICO}</td>
                                <td id="gr-td-graphictype">
                                    <select name="graphictype" id="graphictype" class="form-control" title="El tipo de gráfico"
                                            onchange="GraphUtils.setGraphicType (this);">
                                        <option value="" style="color:#cccccc">Seleccionar</option>
                                        {foreach $AVAILABLE_TYPES as $types}
                                            {foreach $types as $typeId => $typeName}
                                                {if $typeId neq 'columns'}
                                                    {assign var='optionValue' value=$typeId}
                                                    {assign var='optionLabel' value=$typeName}
                                                {else}
                                                    {assign var='columnsType' value=$typeName}
                                                {/if}
                                            {/foreach}
                                            <option value="{$optionValue}"{if ($graphType == $optionValue)}
                                                    {assign var='graphTypeName' value=$optionLabel} selected="selected"
                                                    {assign var='graphTypeStyle' value=$columnsType}
                                                    {/if}
                                                    data-column="{$columnsType}">{$optionLabel}</option>
                                        {/foreach}
                                    </select>
                                    <span id="gr-graphictype" class="help-block"></span>
                                </td>
                            </tr>
                            {* /tipo de gráfico *}
                            {* Incluir tabla de datos*}
                            <tr id="graph-table-include" {if $graphType eq 'table'}class="hide"{/if}>
                                <td class="col-labels">Crear tabla de datos</td>
                                <td id="gr-td-createTable">
                                    <label>
                                        <input id="check-include-table" name="createTable" type="checkbox" value="table">
                                    </label>

                                </td>
                            </tr>
                            {* /Incluir tabla de datos*}
                            <tr>
                                <td colspan="2">
                                    <p class="pull-left" style="padding-left: 4px;font-weight: bold">2.- Fuentes de datos:</p>
                                </td>
                            </tr>
                            {* modulo - campo - operacion - grouping + filtro*}
                            <tr>
                                <td colspan="2">
                                    <ul id="graph-module-column" class="list-group module-column">
                                        {if $graphModuleName neq NULL}
                                        {section name=key loop=$graphModuleName}
                                            {assign var='selectedField' value=null}
                                            {include file="modules/graficosgenerales/GraphEditModulesFiled.tpl"}
                                            {$selectedFieldText[$smarty.section.key.index] = $smarty.capture.optionSelected}
                                        {/section}
                                        {else}
                                            {include file="modules/graficosgenerales/GraphModulesFiled.tpl"}
                                        {/if}
                                        {if $CALCULATION_ROW neq NULL}
                                            {include file="modules/graficosgenerales/GraphOperationsModuleEdit.tpl"}
                                        {/if}
                                    </ul>
                                </td>
                            </tr>
                            {* / modulo - campo - operacion - grouping - borrar + filtro*}
                            {* mas modulos - campo *}
                            <tr id="graph-more-fields" class="{if ($numModules > 2) || ($graphTypeStyle neq 'MULTIPLE') || ($CALCULATION_ROW neq NULL)}hide{/if}">
                                <td colspan="2" style="margin-top: 0 !important;padding-top: 0 !important;">
                                    <div class="action-bar-group text-center" style="margin-top: 0 !important;padding-top: 0 !important;">
                                        <button type="button" class="btn btn-success " data-group="0"
                                                onclick="GraphUtils.addFieldGroup (this);"
                                                title="Agregar datos a graficar">
                                            <i class="fa fa-plus" aria-hidden="true"></i>&nbsp;Fuente de datos</button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <p class="pull-left" style="padding-left: 4px;font-weight: bold">3.- Visualización del gráfico:</p>
                                </td>
                            </tr>
                            {* / mas modulo - campo *}
                            {* propiedades de los tipos de gráficos *}
                            <tr class="header {if $graphOptions eq NULL} hide {/if}" onclick="GraphUtils.setGraphicProperties (this)">
                                <td id="graphic-properties-title" colspan="2" align="left"><span class="pull-left" style="padding-left: 20px;font-weight: bold" id="span-properties-title"> Propiedades para la gráfica&nbsp;{$graphTypeName}</span>&nbsp;&nbsp;<span id="span-properties-arrow"><i class="fa fa-arrow-up"></i></span></td>
                            </tr>
                            <tr>
                                <td id="graphic-properties-contenect" colspan="2" bgcolor="white">
                                    {if $graphOptions neq NULL}
                                        {include file="modules/graficosgenerales/GraphicEditProperties.tpl"}
                                    {/if}
                                </td>
                            </tr>
                            <tr class="header">
                            </tr>
                            {* /propiedades de los tipos de gráficos *}
                            {* agrupar por fecha *}
                            <tr id="graph-dategrouping-row">
                                <td class="col-labels">
                                    <div class="row">
                                        <div class="col-xs-6"><p style="text-align: left; padding-left: 24px">Agrupado:{* $MOD.LBL_DATE_GROUPING *}</p></div>
                                        <div class="col-xs-6">
                                            <select id="grouping-by" class="form-control col-xs-4" title="" onchange="GraphUtils.setGroupingBy (this)">
                                                <option value="TEMP" {if $graphDateGrouping neq NULL} selected="selected" {/if}>Temporal (Fecha de creación) </option>
                                                <option value="FIELD" {if $graphFieldGrouping neq NULL} selected="selected"  {/if}>Por campos</option>
                                            </select>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="row">
                                        <div id="gr-td-dategrouping" class="col-xs-12 {if $graphFieldGrouping neq NULL} hide {/if}" style="padding-top: 4px">
                                            <select name="dategrouping" id="dategrouping" class="form-control" title="">
                                                <option value="">Seleccione</option>
                                                {foreach $AVAILABLE_DATE_GROUPINGS as $groupingId => $groupingName}
                                                    <option value="{$groupingId}"{if ($graphDateGrouping == $groupingId)} selected="selected"{/if}>{$groupingName}</option>
                                                {/foreach}
                                            </select>
                                            <span id="gr-dategrouping" class="help-block"></span>
                                        </div>
                                        <div id="gr-td-fieldgrouping" class="col-xs-12 {if $graphFieldGrouping eq NULL} hide {/if}">
                                            <select id="fieldgrouping" name="fieldgrouping" class="form-control col-xs-4" title="">
                                                {if $FIELDS_MODULE_GROUPING neq NULL}
                                                    <option value="">Seleccione</option>
                                                    {foreach $FIELDS_MODULE_GROUPING as $field}
                                                        {assign var='textSelected' value='('|cat:$field.label|cat:') '|cat:$field.dep_label|cat:' - '|cat:$field.f_label}
                                                        {assign var='valueSelected' value=$field.main|cat:'.'|cat:$field.dep|cat:'.'|cat:$field.field}
                                                        <option value="{$valueSelected}"{if $valueSelected eq $graphFieldGrouping}selected="selected"{/if}>{$textSelected}</option>
                                                    {/foreach}
                                                    {elseif  $FIELDS_GROUPING neq NULL }
                                                    <option value="">Seleccione</option>
                                                    {foreach $FIELDS_GROUPING as $key => $field }
                                                        <option value="{$field}"{if $field eq $graphFieldGrouping}selected="selected"{/if}>{$key}</option>
                                                    {/foreach}
                                                    {else}
                                                    <option value="">Cargando...</option>
                                                {/if}

                                            </select>
                                            <span id="gr-fieldgrouping" class="help-block"></span>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                        </table>
                    </div>
                </div>
            </div>
        </div>
        </div>
        <div class="row" style="margin-top: 0px !important;z-index: 10000">
            <div class="col-lg-12">
                <div class="col-lg-6 pull-left"></div>
                <div class="col-lg-6 pull-right text-right">
                    <button type="button" class="btn btn-info"
                            onclick="GraphUtils.openGraphPreview ();">{$MOD.LBL_PREVIEW}</button>
                    &nbsp;
                    <button type="submit" id="btnsave" class="btn btn-primary">{$MOD.LBL_SAVE}</button>
                    &nbsp;
                    <a href="index.php?module={$MODULE}&action=index&activeTab={$activeTab}"
                       class="btn btn-warning">{$MOD.LBL_CANCEL_BUTTON}</a>
                </div>
            </div>
        </div>
    </form>
	    <div id="preview" class="modal fade" role="dialog" style="top: 0;z-index: 10000" data-backdrop="static">
	        <div class="modal-dialog preview-modal" style="margin: 0 auto; padding: 0; width: 90vw; max-width: 90vw;">
	            <div class="modal-content" style="height: 95vh; min-height: 95vh; position: relative;">
	                <div class="modal-header">
	                    <button type="button" class="close" data-dismiss="modal" onclick="GraphUtils.closeGraphPreview ();">
	                        &times;
	                    </button>
	                    <h4 class="modal-title">Vista preliminar</h4>
	                </div>
	                <div class="modal-body" style="bottom: 0; left: 0; position: absolute; right: 0; top: 60px; display: flex; justify-content: center; align-items: flex-start; overflow: hidden;">
                    <div id="graphic-preview" style="overflow-x: hidden; overflow-y: auto; width: 100%; height: 100%;">
                    </div>
                </div>
	            </div>
	        </div>
	    </div>
	    <div class="clearfix" style="min-height: 45px">&nbsp;</div>
    <script type="text/html" id="condition-template">
        {include file="modules/graficosgenerales/filterGraphCondition.tpl"}
    </script>
    <script type="text/html" id="condition-group-template">
        {include file="modules/graficosgenerales/filterGraphGroup.tpl"}
    </script>
    <script type="text/html" id="operation-group-template">
        {include file="modules/graficosgenerales/GraphOperationsModule.tpl"}
    </script>
    <script type="text/javascript" src="modules/graficosgenerales/graficosgenerales.js"></script>
    <script type="text/javascript">
        jQuery(document).ready(function () {
            {literal}
            jQuery('#footer-bar').attr('style', 'position: fixed!important;');
            {/literal}
            totalFilterGroup = {($totalGroup + 1)};
            totalFilterRow = {($totalIndex + 1)};
        });
    </script>
{/strip}