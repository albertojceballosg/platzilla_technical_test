{strip}
    {if (isset ($VIEW))}
        {assign var='viewAdvancedFilterGroups' value=$VIEW->getAdvancedFilterGroups ()}
        {assign var='viewColorFilterGroups' value=$VIEW->getColorFilterGroups ()}
        {assign var='viewColumns' value=$VIEW->getColumns ()}
        {assign var='viewId' value=$VIEW->getId ()}
        {assign var='viewIsDefault' value=$VIEW->getDefault ()}
        {assign var='viewIsSearch' value=$VIEW->getSearchView ()}
        {assign var='viewIsDesk' value=$VIEW->getDeskView ()}
        {assign var='viewName' value=$VIEW->getName ()}
        {assign var='viewStandardFilter' value=$VIEW->getStandardFilter ()}
        {assign var='viewStatus' value=$VIEW->getStatus ()}
        {assign var="isLockedView" value=$VIEW->isLocked()}
        {if $VIEW->getViewGroup() neq NULL}
            {assign var='viewGroupId' value=$VIEW->getViewGroup()->getId()}
            {assign var='viewGroupName' value=$VIEW->getViewGroup()->getName()}
        {else}
            {assign var='viewGroupId' value=null}
            {assign var='viewGroupName' value=null}
        {/if}
    {else}
        {assign var='viewAdvancedFilterGroups' value=null}
        {assign var='viewColorFilterGroups' value=null}
        {assign var='viewColumns' value=null}
        {assign var='viewId' value=null}
        {assign var='viewIsDefault' value=null}
        {assign var='viewIsSearch' value=null}
        {assign var='viewIsDesk' value=null}
        {assign var='viewName' value=null}
        {assign var='viewStandardFilter' value=null}
        {assign var='viewStatus' value=null}
        {assign var='viewGroupId' value=null}
        {assign var='viewGroupName' value=null}
        {assign var="isLockedView" value=1}
    {/if}

    {if (isset ($viewStandardFilter))}
        {assign var='viewStandardFilterPeriod' value=$viewStandardFilter->getPeriod ()}
    {else}
        {assign var='viewStandardFilterPeriod' value=null}
    {/if}
    {if {$CATEGORY} eq 'Home'}
        {assign var='urlCancel' value="index.php?module=Home&action=index&tab=ACTIVITY_REPORT"}
    {else}
        {assign var='urlCancel' value="index.php?module={$CURRENT_MODULE}&action=ListView"}
    {/if}
    <link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/libs/datepicker.css"/>
    <style type="text/css">
        .btn {
            margin-left: 5px;
        }

        label {
            font-size: 1.11em;
            font-weight: 300;
        }

        .required {
            color: #FF0000;
        }

        .filter-group {
            margin-bottom: 0;
        }

        .filter-group-header {
            padding: 5px 5px 5px 15px
        }

        .filter-group-body {
            padding: 0;
        }

        .filter-group-body > .filters {
            margin-bottom: 0;
            padding: 5px 0;
        }

        .filter-group-body > .filters > .filter {
            border: 0;
            padding: 5px 10px 5px 15px;
        }

        .filter-group-operator {
            margin: 5px 0;
        }
    </style>
    <form action="index.php" method="post"
          onsubmit="if (CustomViewUtils.validateForm (this)) { VtigerJS_DialogBox.block (); } else { return false; }">
        <input type="hidden" name="module" value="CustomView"/>
        <input type="hidden" name="action" value="Save"/>
        <input type="hidden" name="parenttab" value="{$CATEGORY}"/>
        <input type="hidden" name="modulename" value="{$CURRENT_MODULE}"/>
        <input type="hidden" name="record" value="{$viewId}"/>
        <input type="hidden" name="locked" value="{$isLockedView}">
        <input id="cv-group-id" type="hidden" name="groupid" value="{$viewGroupId}"/>
        <div class="row">
            <div class="col-lg-12">
                <h1 class="pull-left">
                    <a href="index.php?action=ListView&module={$CURRENT_MODULE}&parenttab={$CATEGORY}">{$MODULE_LABEL}</a>
                    &gt;
                    <span style="color:#000">{if (isset ($VIEW))}{$MOD.Edit_Custom_View}{else}{$MOD.New_Custom_View}{/if}</span>
                </h1>
                <div class="pull-right">
                    <button type="submit" class="btn btn-success">{$APP.LBL_SAVE_BUTTON_LABEL}</button>
                    <a href="{$urlCancel}" class="btn btn-warning"
                       style="margin-left: 5px;">{$APP.LBL_CANCEL_BUTTON_LABEL}</a>
                </div>
            </div>
        </div>
        {if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
            <div class="row">
                <div class="alert {if (isset ($IS_ERROR)) && ($IS_ERROR)}alert-danger{else}alert-success{/if}">
                    <strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
                </div>
            </div>
        {/if}
        <div class="row">
            <div class="col-xs-12">
                <div class="alert alert-info alert-dismissable"
                     style="background-image: url('themes/{$THEME}/img/platzillaman.png'); background-position: 5px 5px; background-repeat: no-repeat; background-size: auto 60px; min-height: 75px;">
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                    <p class="text" style="padding-left: 70px;"><b>{$MOD.LBL_HELP_FILTER}</b></p>
                </div>
                <div class="main-box">
                    <header class="title-section main-box-header clearfix"><h2>Información básica</h2></header>
                    <div class="main-box-body clearfix">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="label-input"><label for="name">Nombre <span
                                                        class="required">*</span></label></div>
                                    </div>
                                    <div class="form-group col-md-8 field-container">
                                        <div class="input-group" style="width: 100%;">
                                            <input type="text" id="name" name="name" value="{$viewName}"
                                                   class="form-control" placeholder="{$MOD.LBL_VIEW_NAME}"/>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="label-input"><label for="name">{if $IS_MOTHER}Grupos{else}Grupo{/if}</label></div>
                                    </div>
                                    <div class="input-group col-md-8">
                                        <div class="input-group-btn">
                                            {if $IS_MOTHER}
                                            <button type="button" class="btn btn-default dropdown-toggle"
                                                    data-toggle="dropdown">
                                                Seleccionar <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu" role="menu">
                                                <li><a rel="" href="#"  onclick="CustomViewUtils.selectGroup(event, this)" >Nuevo grupo</a></li>
                                                {if $VIEW_GROUP neq NULL}
                                                    <li class="divider"></li>
                                                    {foreach $VIEW_GROUP as $viewGroup}
                                                    <li><a  rel="{$viewGroup->getId()}" onclick="CustomViewUtils.selectGroup(event, this)" href="#">{$viewGroup->getName()}</a></li>
                                                    {/foreach}
                                                {/if}
                                            </ul>
                                            {/if}
                                        </div>
                                        <input id="cv-group-name" type="text" name="groupname" class="form-control" {if ($viewId eq NULL) || !$IS_MOTHER}readonly{/if} style="width: 98%" value="{if $IS_MOTHER}{$viewGroupName}{else}{$CUSTOM_GROUP}{/if}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    {if ($IS_ADMIN)}
                                        <div class="checkbox-nice">
                                            <input type="checkbox" id="is-default" name="isdefault"
                                                   value="1"{if ($viewIsDefault)} checked="checked"{/if}>
                                            <label for="is-default">{$MOD.LBL_SETDEFAULT}</label>
                                        </div>
                                        <div class="checkbox-nice">
                                            <input type="checkbox" id="is-desk" name="isDeskView"
                                                   value="1"{if ($viewIsDesk)} checked="checked"{/if}>
                                            <label for="is-desk">{$MOD.LBL_SETDESKVIEW}</label>
                                        </div>
                                    {/if}
                                    {if ($IS_MOTHER)}
                                        <div class="checkbox-nice">
                                            <input type="checkbox" id="is-search" name="isSearchView"
                                                   value="1"{if ($viewIsSearch)} checked="checked"{/if}>
                                            <label for="is-search">{$MOD.LBL_SETSEARCH}</label>
                                        </div>
                                    {/if}
                                    <div class="checkbox-nice">
                                        <input type="checkbox" id="status" name="status"
                                               value="1"{if (in_array ($viewStatus, array (0, 3)))} checked="checked"{/if}>
                                        <label for="status">{$MOD.LBL_SET_AS_PUBLIC}</label>
                                    </div>
                                </div>
                            </div>
                            <!-- wa -->
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="main-box">
                            <header class="main-box-header clearfix"><h2>{$MOD.LBL_STEP_2_TITLE}</h2></header>
                            <div class="main-box-body clearfix">
                                <div class="row columns">
                                    {for $i = 1; $i < 10; $i++}
                                        {if (isset ($viewColumns)) && (isset ($viewColumns[($i - 1)]))}
                                            {assign var='selectedViewColumn' value=$viewColumns[($i - 1)]}
                                        {else}
                                            {assign var='selectedViewColumn' value=null}
                                        {/if}
                                        <div class="form-group col-xs-12 col-md-6 col-lg-2">
                                            <div class="input-group">
                                                <label for="column{$i}">Columna {$i}</label>
                                                <select id="column{$i}" name="columns[{$i}]" class="form-control column"
                                                        onchange="CustomViewUtils.preventDuplicates (this);"
                                                        onclick="CustomViewUtils.setLastSelectedValue (this);">
                                                    <option value="">{$MOD.LBL_NONE}</option>
                                                    {foreach $AVAILABLE_COLUMNS AS $columnName => $columnLabel}
                                                        {assign var='dummy' value=explode(':', $columnName)}
                                                        <option value="{$columnName}"{if (isset ($selectedViewColumn)) && ($selectedViewColumn->getTableName () == $dummy[0]) && ($selectedViewColumn->getColumnName () == $dummy[1]) && ($selectedViewColumn->getFieldName () == $dummy[2])} selected="selected"{/if}>{$columnLabel}</option>
                                                    {/foreach}
                                                </select>
                                            </div>
                                        </div>
                                    {/for}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="main-box">
                            <header class="main-box-header clearfix"><h2>Filtros</h2></header>
                            <div class="main-box-body clearfix">
                                <div class="tabs-wrapper">
                                    <ul class="nav nav-tabs">
                                        <li class="active"><a href="#pi" data-toggle="tab">{$MOD.LBL_STEP_3_TITLE}</a>
                                        </li>
                                        <li class=""><a href="#mi" data-toggle="tab">{$MOD.LBL_STEP_4_TITLE}</a></li>
                                        <li class=""><a href="#ci" data-toggle="tab">{$MOD.LBL_COLOR_FILTER}</a></li>
                                    </ul>
                                    <div class="tab-content">
                                        <div class="tab-pane fade active in" id="pi">
                                            <div class="row standard-filter" style="margin-top: 20px;">
                                                <div class="form-group col-xs-12 col-md-6 col-lg-3">
                                                    <label for="standard-filter-column">{$MOD.LBL_Select_a_Column}</label>
                                                    <select id="standard-filter-column" name="standardfilter[column]"
                                                            class="form-control"
                                                            onchange="CustomViewUtils.setStandardFilter (this);">
                                                        <option value=""></option>
                                                        {foreach $AVAILABLE_DATE_COLUMNS as $columnName => $columnLabel}
                                                            {assign var='dummy' value=explode(':', $columnName)}
                                                            <option value="{$columnName}"{if (isset ($viewStandardFilter)) && ($viewStandardFilter->getTableName () == $dummy[0]) && ($viewStandardFilter->getColumnName () == $dummy[1]) && ($viewStandardFilter->getFieldName () == $dummy[2])} selected="selected"{/if}>{$columnLabel}</option>
                                                        {/foreach}
                                                    </select>
                                                </div>
                                                <div class="form-group col-xs-12 col-md-6 col-lg-3">
                                                    <label for="standard-filter-period">{$MOD.Select_Duration}</label>
                                                    <select id="standard-filter-period" name="standardfilter[period]"
                                                            class="form-control"
                                                            onchange="CustomViewUtils.setPeriod (this);">
                                                        <option value=""></option>
                                                        {foreach $AVAILABLE_PERIODS as $periodName => $periodLabel}
                                                            <option value="{$periodName}"{if ($viewStandardFilterPeriod == $periodName)} selected="selected"{/if}>{$periodLabel}</option>
                                                        {/foreach}
                                                    </select>
                                                </div>
                                                <div class="form-group col-xs-12 col-md-6 col-lg-3 standard-filter-date"{if ($viewStandardFilterPeriod != 'custom')} style="display: none;"{/if}>
                                                    <label for="standard-filter-start-date">{$MOD.Start_Date}</label>
                                                    <div class="input-group">
                                                        <span class="input-group-addon"><i
                                                                    class="fa fa-calendar"></i></span>
                                                        <input type="text" id="standard-filter-start-date"
                                                               name="standardfilter[startdate]"
                                                               value="{if (isset ($viewStandardFilter)) && (!empty ($viewStandardFilter->getStartDate ()))}{$viewStandardFilter->getStartDate ()->format ('Y-m-d')}{/if}"
                                                               class="form-control"
                                                               placeholder="9999/99/99"{if ($viewStandardFilterPeriod != 'custom')} disabled="disabled"{/if} />
                                                    </div>
                                                </div>
                                                <div class="form-group col-xs-12 col-md-6 col-lg-3 standard-filter-date"{if ($viewStandardFilterPeriod != 'custom')} style="display: none;"{/if}>
                                                    <label for="standard-filter-end-date">{$MOD.End_Date}</label>
                                                    <div class="input-group">
                                                        <span class="input-group-addon"><i
                                                                    class="fa fa-calendar"></i></span>
                                                        <input type="text" id="standard-filter-end-date"
                                                               name="standardfilter[enddate]"
                                                               value="{if (isset ($viewStandardFilter)) && (!empty ($viewStandardFilter->getEndDate ()))}{$viewStandardFilter->getEndDate ()->format ('Y-m-d')}{/if}"
                                                               class="form-control"
                                                               placeholder="9999/99/99"{if ($viewStandardFilterPeriod != 'custom')} disabled="disabled"{/if} />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade filters-container" id="mi">
                                            <div class="row filter-groups" style="margin-top: 20px;">
                                                {if (!empty ($viewAdvancedFilterGroups))}
                                                    {foreach $viewAdvancedFilterGroups as $group}
                                                        {include file='CustomViewAdvancedFilterGroup.tpl' GROUP=$group}
                                                    {/foreach}
                                                {/if}
                                            </div>
                                            <div class="col-xs-12 text-center">
                                                <button type="button" class="btn btn-info"
                                                        onclick="CustomViewUtils.addAdvancedFilterGroup (this);"
                                                        title="Agregar grupo de filtros"><i class="fa fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade filters-container" id="ci">
                                            <div class="row filter-groups" style="margin-top: 20px;">
                                                {if (!empty ($viewColorFilterGroups))}
                                                    {foreach $viewColorFilterGroups as $group}
                                                        {include file='CustomViewColorFilterGroup.tpl' GROUP=$group}
                                                    {/foreach}
                                                {/if}
                                            </div>
                                            <div class="col-xs-12 text-center">
                                                <button type="button" class="btn btn-info"
                                                        onclick="CustomViewUtils.addColorFilterGroup (this);"
                                                        title="Agregar grupo de filtros"><i class="fa fa-plus"></i>
                                                </button>
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
                                <button type="submit" class="btn btn-success">{$APP.LBL_SAVE_BUTTON_LABEL}</button>
                                <a href="{$urlCancel}" class="btn btn-warning"
                                   style="margin-left: 5px;">{$APP.LBL_CANCEL_BUTTON_LABEL}</a>
                            </div>
                        </div>
                    </div>
                </div>
    </form>
{/strip}
<script type="text/html" id="advanced-filter-template">
    {include file='CustomViewAdvancedFilter.tpl' GROUP_ID='__GROUP_ID__' FILTER_ID='__FILTER_ID__'}
</script>
<script type="text/html" id="advanced-filter-group-template">
    {include file='CustomViewAdvancedFilterGroup.tpl' GROUP_ID='__GROUP_ID__'}
</script>
<script type="text/html" id="color-filter-template">
    {include file='CustomViewColorFilter.tpl' GROUP_ID='__GROUP_ID__' FILTER_ID='__FILTER_ID__'}
</script>
<script type="text/html" id="color-filter-group-template">
    {include file='CustomViewColorFilterGroup.tpl' GROUP_ID='__GROUP_ID__'}
</script>
<script type="text/javascript" src="themes/{$THEME}/js/jquery.maskedinput.min.js"></script>
<script type="text/javascript" src="themes/{$THEME}/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="themes/{$THEME}/js/bootstrap-datepicker.es.js"></script>
<script type="text/javascript" src="themes/{$THEME}/js/daterangepicker.js"></script>
<script type="text/javascript" src="themes/{$THEME}/js/moment.min.js"></script>
<script type="text/javascript" src="themes/{$THEME}/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript" src="themes/{$THEME}/js/select2.min.js"></script>
<script type="text/javascript" src="modules/CustomView/custom-view-utils.js"></script>
<script type="text/javascript">
    CustomViewUtils.init({$AVAILABLE_COLUMNS|json_encode});
</script>
