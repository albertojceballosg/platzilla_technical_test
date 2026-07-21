{strip}
    <div class="main-box-header clearfix" style="padding: 0 1.2em">
        <div class="row" style="margin: 1.1em 0">
            <form role="form" method="post" id="platzi-issabel_form-{$idPlatziIsabel}">
                <input type="hidden" name="module" value="platzi_issabel">
                <input type="hidden" id="function-name-{$idPlatziIsabel}" name="function" value="SEARCH_MONITORING">
                <input type="hidden" name="action" value="AjaxPlatziIssabelUtils">
                <input type="hidden" name="Ajax" value="true">
                <input type="hidden" name="page" value="{$page}">
                <input type="hidden" name="tabid" value="{$idPlatziIsabel}">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12" {if $actionId neq NULL}style="margin-top: 0"{/if}>
                        {* search option *}
                        <div id="btn-toolbar-{$idPlatziIsabel}" class="col-lg-2 col-md-2 col-xs-2  btn-toolbar" role="toolbar">
                            <div class="input-group">
                                <div class="input-group-addon"><i class="fa fa-search"></i></div>
                                <select id="search_option-{$idPlatziIsabel}"
                                        onchange="PlatziIssabelUtils.searchBy (this, '{$idPlatziIsabel}')"
                                        name="search_option"
                                        class="form-control col-md-3" title="Seleccionar tipo">
                                    <option value="" selected="selected">Buscar en</option>
                                    <option value="src">Origen</option>
                                    <option value="dst">Destino</option>
                                    <option value="recordingfile">Tipo</option>
                                </select>
                            </div>
                        </div>
                        {* recording_type *}
                        <div id="platzi-issabel-type-{$idPlatziIsabel}"  class="btn-group col-lg-2 col-md-2 col-xs-2  hide"
                             style="margin-bottom: 4px; margin-left: 2px!important;">
                            <select id="recording_type-{$idPlatziIsabel}"
                                    name="recording_type" class="form-control col-md-3" title="Seleccionar tipo">
                                <option value="" >Seleccionar tipo</option>
                                <option value="i" >Entrante</option>
                                <option value="o">Saliente</option>
                                <option value="q">Cola</option>
                                <option value="g">Grupo</option>
                            </select>
                        </div>
                        {* search_input *}
                        <div id="platzi-issabel-search-{$idPlatziIsabel}" class="btn-group col-lg-2 col-md-2 col-xs-2  hide"
                             style="margin-bottom: 4px; margin-left: 2px!important;">
                            <input id="search_input-{$idPlatziIsabel}" type="text" name="search_input"
                                   class="form-control from-field"
                                   value=""
                                   style="margin: 0!important;"/>
                        </div>
                        {* date period filters *}
                        <div class="col-lg-3 col-md-3 col-xs-3 btn-group date-time-{$idPlatziIsabel}"
                             style="margin-bottom: 4px; margin-right: 0!important;">
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <i class="fa  fa-clock-o"></i>
                                </div>
                                <select id="period-dates-{$idPlatziIsabel}"
                                        onchange="PlatziIssabelUtils.selectedPeriod (this, '{$idPlatziIsabel}')"
                                        name="period_dates"
                                        class="form-control" title="Seleccionar periodo">
                                    {if $PERIOD_DATES neq NULL}
                                        <option value="">Seleccionar periodo</option>
                                        {foreach $PERIOD_DATES as $period => $perioName}
                                            <option value="{$period}" {if $period eq $PERIOD_SELECTED}selected{/if} >
                                                {$perioName}
                                            </option>
                                        {/foreach}
                                    {/if}
                                </select>
                            </div>
                        </div>
                        {* date start filters *}
                        <div class="btn-group col-lg-2 col-md-2 col-xs-2  hide"
                             style="margin-bottom: 4px; margin-left: 2px!important;">
                            <div class="input-group">
                                <span class="input-group-addon "><i class="fa fa-calendar"></i></span>
                                <input id="start-date-{$idPlatziIsabel}" type="text" name="datestart"
                                       readonly="readonly"
                                       class="form-control from-field platzi-issabel-date-{$idPlatziIsabel} date start-date"
                                       value=""
                                       style="margin: 0!important;" placeholder="Desde"/>
                            </div>
                        </div>
                        {* date duedate filters *}
                        <div class="btn-group  col-lg-2 col-md-2 col-xs-2  hide"
                             style="margin-bottom: 4px;margin-right: 0!important;">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                <input id="end-date-{$idPlatziIsabel}" type="text" name="duedate"
                                       readonly="readonly"
                                       class="form-control platzi-issabel-date-{$idPlatziIsabel} date end-date"
                                       value=""
                                       style="margin: 0!important;" placeholder="Hasta"/>
                            </div>
                        </div>
                        <div class="pull-left" style="margin-left: 2px">
                            <div class="btn-group">
                                <button name="submitSearch" id="submitSearch" class="btn btn-primary"
                                        title="{$searchTitle}"
                                        data-pagination-page="{$page}"
                                        onclick="PlatziIssabelUtils.goToPage (event, this, '{$idPlatziIsabel}')" type="button">
                                    <i class="fa fa-search" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                        {* action button right *}
                        <div class="btn-group pull-right" style="margin-bottom: 2px">&nbsp;</div>
                    </div>
                </div>
            </form>
        </div>
    </div>
{/strip}