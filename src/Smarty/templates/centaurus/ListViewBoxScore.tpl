{block name="css"}
    <style type="text/css" xmlns="http://www.w3.org/1999/html">
        {literal}
        .alert-grey {
            background-color: #eee;
            text-align: center !important;
        }

        .rgt {
            text-align: right;
        }

        th, .ctr {
            text-align: center !important;
        }

        .lft {
            text-align: left !important;
        }

        .pi-tools {
            display: block !important;
            float: right !important;
            color: #f56954;

        }

        .show-tools:hover .pi-tools {
            display: inline-block;
        }

        .show-tools {
            color: #566573 !important;
        }

        .main-box-body button {
            font-size: 12px !important;
        }

        .isPiDisabled > a {
            color: currentColor;
            display: inline-block; /* For IE11/ MS Edge bug */
            pointer-events: none;
            text-decoration: none;
        }
        @media (min-width: 1280px) and (max-width: 1300px) {
            #box-score-favorites {
                width: 101% !important;
                margin-left: -10px;
            }
        }
        @media (min-width: 1400px) and (max-width: 1580px) {
            #box-score-favorites {
                width: 102.5% !important;
                margin-left: -20px;
            }
        }
        @media (min-width: 1600px) and (max-width: 1800px) {
            #box-score-favorites {
                width: 100% !important;
                margin-left: -10px;
            }

        }
        .flex-container {
            display: flex;
            align-items: stretch;
            flex-direction: row;
            flex-wrap: nowrap;
        }

        .flex-container > div {
            margin: 0 0.15em 0 0.15em;
            text-align:left;
        }
        {/literal}
    </style>
{/block}
{block name="js"}
    <script src="modules/indicatorspanel/indicatorspanel-utils.js" type="text/javascript"></script>
{/block}
{math equation='x - y' x=12 y=$STATUS_TOTAL_BUTTONS assign='col'}
{block name="first-content"}
    <div class="container-fluid base-list-container">
        <div class="row">
            <div class="col-lg-12">
                <div class="main-box clearfix">
                    <div class="main-box-header clearfix">
                        <div class="row">
                            <div class="col-md-6" style="margin-left: -10px!important;">
                                {* /new combobxo button to  option *}
                                <div class="btn-group pull-left">
                                    {* LIST-VIEW
                                    <a data-toggle="tab" href="#ListViewContents" class="btn btn-default"
                                       style=" font-size: 15px!important;"
                                       onclick="ListViewTabUtils.activeListTab(event)"
                                       data-toggle="tab" title="Listado de registros"><i
                                                class="fa fa-list-ul"></i></a>
                                     *}
                                    {* LIST-VIEW-KANBAN-VIEW *}
                                    {if $STATUS_BUTTONS['kanban'] && false}
                                        <a data-toggle="tab" href="#LIST-VIEW-KANBAN-VIEW"
                                           class="btn btn-default" style=" font-size: 15px!important;"
                                           title="Vista kanban"
                                           onclick="ListViewTabUtils.activeBoxScoreTab (event)"
                                           data-toggle="tab"><i class="fa fa-trello" aria-hidden="true"></i></a>
                                    {/if}
                                    {* LIST-VIEW-BOX-SCORE *}
                                    {if $STATUS_BUTTONS['boxscore']}
                                        <button type="button" class="btn btn-primary"
                                                style=" font-size: 15px!important;"><i
                                                    class="fa fa-heart-o"></i>
                                        </button>
                                    {/if}
                                    {* LIST-VIEW-GRAPHIC *}
                                    {if $STATUS_BUTTONS['graphic']}
                                        <a data-toggle="tab" href="#LIST-VIEW-GRAPHIC" class="btn btn-default"
                                           style=" font-size: 15px!important;"
                                           TITLE="Graficos"
                                           onclick="ListViewTabUtils.activeGraphicTab (event)"
                                           data-toggle="tab"><i class="fa fa-bar-chart-o"></i></a>
                                    {/if}
                                    {* report *}
                                    {if $STATUS_BUTTONS['report']}
                                        <a data-toggle="tab" href="#LIST-VIEW-REPORT" class="btn btn-default"
                                           style=" font-size: 15px!important;"
                                           title="Informes"
                                           onclick="ListViewTabUtils.activeReportTab (event)"
                                           data-toggle="tab"><i class="fa fa-file" aria-hidden="true"></i></a>
                                    {/if}
                                    {* LIST-VIEW-CALENDAR *}
                                    {if $STATUS_BUTTONS['calendar']  && false}
                                        <a data-toggle="tab" href="#LIST-VIEW-CALENDAR" class="btn btn-default"
                                           style=" font-size: 15px!important;"
                                           title="vista calendario"
                                           onclick="ListViewTabUtils.activeCalendarTab (event)"
                                           data-toggle="tab"><i class="fa fa-calendar"></i></a>
                                    {/if}
                                </div>
                                {* new combobxo button to  option *}
                            </div>
                            <div class="col-md-6">
                                        <div class="form-group col-md-12 pull-right list-view-filter"
                                             style="{if isset($IS_HOME_TAB)}display: none{/if}">
                                            <form name="form-box-score" id="form-box-score" method="POST"
                                                  action="index.php">
                                                <input type="hidden" name="module" id="module" value="indicatorspanel">
                                                <input type="hidden" name="action" id="action" value="allAppDetailView">
                                                <input type="hidden" name="function" id="appcode"
                                                       value="searchHomeBoxScore">
                                                <input type="hidden" name="Ajax" value="true">
                                                <input type="hidden" name="date_from" id="date_from" value="">
                                                <input type="hidden" name="date_to" id="date_to" value="">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <div class="input-group">
                                                                <div class="input-group-addon">
                                                                    <i class="fa fa-calendar"></i>
                                                                </div>
                                                                <select class="form-control" id="monthsearch"
                                                                        name="monthsearch" title=""
                                                                        onchange="BoxScoreUtils.getIndicatorsMonths(this)">
                                                                    <option value="">{$MODSTRING.LBL_SELECTION_MONTH}</option>
                                                                    <option value="01"{if ($MONTH_SEARCH == '01')} selected="selected"{/if}>{$MODSTRING.LBL_ENERO}</option>
                                                                    <option value="02"{if ($MONTH_SEARCH == '02')} selected="selected"{/if}>{$MODSTRING.LBL_FEBRERO}</option>
                                                                    <option value="03"{if ($MONTH_SEARCH == '03')} selected="selected"{/if}>{$MODSTRING.LBL_MARZO}</option>
                                                                    <option value="04"{if ($MONTH_SEARCH == '04')} selected="selected"{/if}>{$MODSTRING.LBL_ABRIL}</option>
                                                                    <option value="05"{if ($MONTH_SEARCH == '05')} selected="selected"{/if}>{$MODSTRING.LBL_MAYO}</option>
                                                                    <option value="06"{if ($MONTH_SEARCH == '06')} selected="selected"{/if}>{$MODSTRING.LBL_JUNIO}</option>
                                                                    <option value="07"{if ($MONTH_SEARCH == '07')} selected="selected"{/if}>{$MODSTRING.LBL_JULIO}</option>
                                                                    <option value="08"{if ($MONTH_SEARCH == '08')} selected="selected"{/if}>{$MODSTRING.LBL_AGOSTO}</option>
                                                                    <option value="09"{if ($MONTH_SEARCH == '09')} selected="selected"{/if}>{$MODSTRING.LBL_SEPTIEMBRE}</option>
                                                                    <option value="10"{if ($MONTH_SEARCH == '10')} selected="selected"{/if}>{$MODSTRING.LBL_OCTUBRE}</option>
                                                                    <option value="11"{if ($MONTH_SEARCH == '11')} selected="selected"{/if}>{$MODSTRING.LBL_NOVIEMBRE}</option>
                                                                    <option value="12"{if ($MONTH_SEARCH == '12')} selected="selected"{/if}>{$MODSTRING.LBL_DICIEMBRE}</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6" style="padding-right:0px;">
                                                        <div class="form-group">
                                                            <div class="input-group">
                                                                <div class="input-group-addon">
                                                                    <i class="fa fa-exchange"></i>
                                                                </div>
                                                                <select class="form-control" id="viewScale"
                                                                        name="viewScale" title=""
                                                                        onchange="BoxScoreUtils.getIndicatorsView(this)">
                                                                    <option value="">{$MODSTRING.LBL_SELECTION_VIEW}</option>
                                                                    <option value="Month" {if ($VIEW_SEARCH == 'Month')} selected="selected"{/if}>{$MODSTRING.LBL_VIEW_MONTH}</option>
                                                                    <option value="Week" {if ($VIEW_SEARCH == 'Week')} selected="selected"{/if}>{$MODSTRING.LBL_VIEW_WEEK}</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                            </div>
                            <div class="col-md-12">
                                <div id="box-score-favorites">
                                    <div class="main-box-body clearfix" style="padding: 0!important;">
                                        {foreach $APPLICATIONS as $keyApp => $itemApp}
                                            {assign var='BLOCKS' value=$ALL_BOX_SCORE[$keyApp][1]}
                                            {assign var='BOX_SCORE' value=$ALL_BOX_SCORE[$keyApp][0]}
                                            {assign var='CALCULATIONS' value=$ALL_BOX_SCORE[$keyApp][2]}
                                            {if ($IS_HOME) && (empty ($BOX_SCORE->boxs))}
                                                {continue}
                                            {/if}
                                            {if count($BLOCKS) > 0}
                                                <div class="table-responsive">
                                                    <table class="table">
                                                        <tr>
                                                            <th colspan="8" class="alert-grey lft">{$MODSTRING.CATEGORY}
                                                                :
                                                                &nbsp;&nbsp;{$itemApp.app_name}</th>
                                                        </tr>
                                                        <tr>
                                                            <th class="ctr"
                                                                style="width: 220px; ">{$MODSTRING.LBL_INDICATORS}</th>
                                                            <th class="alert-grey ctr">{$MODSTRING.LBL_OBJECT}</th>
                                                            <th>{$MODSTRING.LBL_CUMPL}</th>
                                                            {assign var='countdate' value=1}
                                                            {foreach $ALL_BOX_SCORE[$keyApp][0]->dates as $date}
                                                                <th>{if ($ALL_BOX_SCORE[$CODE_FIRST][0]->scale == 'Week')} {$countdate} {else}  {assign var='month' value=$date.date|date_format: 'M'} {$MODSTRING.MONTHS[$month]}{/if}</th>
                                                                {assign var='countdate' value=$countdate + 1}
                                                            {/foreach}
                                                        </tr>
                                                        {for $i=0; $i<count($BLOCKS); $i++}
                                                            {assign var='countbox' value=0}
                                                            {foreach $BOX_SCORE->boxs as $boxScoreData}
                                                                {if $RELATED_VIEW neq NULL}
                                                                    {if !in_array($boxScoreData.box_score_dataid, $RELATED_VIEW)}
                                                                        {continue}
                                                                    {/if}
                                                                {/if}
                                                                {if ($boxScoreData.type == $BLOCKS[$i]['type'])}
                                                                    <tr id="row-{$boxScoreData.box_score_dataid}">
                                                                        <td class="show-tools" style="color: #566573;">
                                                                            <div id="modalInfo_{$boxScoreData.box_score_dataid}"
                                                                                 class="modal fade"
                                                                                 aria-hidden="true"
                                                                                 aria-labelledby="myModalLabel"
                                                                                 role="dialog"
                                                                                 tabindex="-1" style="display: none;">
                                                                                <div class="modal-dialog">
                                                                                    <div class="modal-content">
                                                                                        <div class="modal-header"
                                                                                             style="text-align:center">
                                                                                            <button class="close"
                                                                                                    aria-hidden="true"
                                                                                                    data-dismiss="modal"
                                                                                                    type="button"
                                                                                                    onclick="jQuery ('.md-overlay').css ({ldelim} opacity: 0.0, visibility: 'hidden' {rdelim});">
                                                                                                ×
                                                                                            </button>
                                                                                            <h4 class="modal-title"><span
                                                                                                        style="color: black">{$MODSTRING.LBL_MOREINFO}</span>
                                                                                            </h4>
                                                                                        </div>
                                                                                        <div class="modal-body">
                                                                                            <span style="color: black">{$boxScoreData.description}</span>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="row">
                                                                                <div class="col-md-6">
                                                                                    <span class="text">
                                                                                        {$boxScoreData.box_score}
                                                                                    </span>
                                                                                </div>
                                                                                <div class="col-md-6">
                                                                                    <div class="pi-tools">
                                                                                        {if $IS_MOTHER}
                                                                                            <a href="addIndicators"
                                                                                               data-toggle="modal"
                                                                                               onclick="BoxSoreUtcils.callAddEditIndicators('indicatorspanel', '{$BLOCKS[$i].type}', '{$boxScoreData.boxscoreid}', '{$MONTH_SEARCH}', '{$APPCODE}','{$boxScoreData.box_score_dataid}', 'edit');"><i
                                                                                                        title="{$MODSTRING.LBL_EDIT}"
                                                                                                        class="fa fa-edit"></i></a>
                                                                                            <a href="javascript:void(0)"
                                                                                               fn="delete-row"
                                                                                               id="{$boxScoreData.box_score_dataid}"
                                                                                               style="color:red;"
                                                                                               onclick="BoxScoreUtils.callDeleteIndicator(this)"><i
                                                                                                        title="{$MODSTRING.LBL_DELETE}"
                                                                                                        class="fa fa-trash-o"></i></a>
                                                                                        {/if}
                                                                                        <a href="#modalInfo_{$boxScoreData.box_score_dataid}"
                                                                                           data-toggle="modal"
                                                                                           onclick="jQuery('.md-overlay').css({ldelim}opacity: 1, visibility: 'visible'{rdelim});"><i
                                                                                                    title="{$MODSTRING.LBL_MOREINFO}"
                                                                                                    class="fa fa-info-circle"></i></a>
                                                                                        {if $boxScoreData.name neq NULL}
                                                                                            {if (in_array ($boxScoreData.name, $FAVORITES))}
                                                                                                {assign var='favoriteTitle' value='Ya no es mi favorito'}
                                                                                                {assign var='favoriteIcon' value='fa fa-star'}
                                                                                            {else}
                                                                                                {assign var='favoriteTitle' value='Convertir en mi favorito'}
                                                                                                {assign var='favoriteIcon' value='fa fa-star-o'}
                                                                                            {/if}
                                                                                            <span>
                                                                                                <a href="#"
                                                                                                   id="favorite_{$boxScoreData.box_score_dataid}"
                                                                                                   rel="{$boxScoreData.name}"
                                                                                                   onclick="BoxScoreUtils.updateFavorite(this, event)"
                                                                                                   title="{$favoriteTitle}"><span
                                                                                                            id="fa-{$boxScoreData.name}"
                                                                                                            class="{$favoriteIcon}"></span></a>
                                                                                            </span>
                                                                                        {/if}
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                        <td class="alert-grey">
                                                                            {if ($boxScoreData.objective)}
                                                                                {if ($boxScoreData.operator == 'less-equal')}&lt;={elseif (!empty($boxScoreData.operator))}&gt;={/if} {$boxScoreData.objective}
                                                                            {/if}
                                                                        </td>
                                                                        <td class="ctr">
                                                                            <span class="label label-{if (preg_match ('/Near/i', $boxScoreData.fulfillment))}warning{elseif (preg_match ('/Far/i', $boxScoreData.fulfillment))}danger{else}success{/if}">{$MODSTRING[$boxScoreData.fulfillment]}</span>
                                                                        </td>
                                                                        {foreach $BOX_SCORE->dates as $date}
                                                                            {assign var='value' value='&nbsp;'}
                                                                            {if ($boxScoreData.scale == 'Week')}
                                                                                {if (isset ($boxScoreData.weekly[$date.week]['value'])) && ($date.year == $YEAR_DATE)}
                                                                                    {assign var='value' value=$boxScoreData.weekly[$date.week]['value']}
                                                                                {else}
                                                                                    {assign var='value' value=''}
                                                                                {/if}
                                                                            {else}
                                                                                {foreach $boxScoreData.weekly as $key => $data}
                                                                                    {assign var='dummy' value=$boxScoreData.weekly.$key.date|date_format:"%m"}
                                                                                    {if ($dummy == $date.month) && ($date.year == $YEAR_DATE)}
                                                                                        {assign var='value' value=$boxScoreData.weekly.$key.value}
                                                                                        {break}
                                                                                    {/if}
                                                                                {/foreach}
                                                                            {/if}
                                                                            <td style="padding-right: 20px; background-color: {if ($date.month == $MONTH_SEARCH)}{$boxScoreData.colordegrade}{else}{$boxScoreData.colorbase}{/if}"
                                                                                id="td-ed-{$boxScoreData.box_score_dataid}-{$date.week}"
                                                                                class="rgt show-tools">
                                                                                {if ($value > 0) && ($BOX_SCORE->warning[$boxScoreData.box_score_dataid][$dummy] == '1')}
                                                                                    <i class="fa fa-warning red"></i>
                                                                                    &nbsp;{/if}
                                                                                <span
                                                                                        id="bs-id-{$boxScoreData.box_score_dataid}-{$date.week}">{$value}</span>
                                                                            </td>
                                                                        {/foreach}
                                                                    </tr>
                                                                    {assign var='countbox' value=$countbox + 1}
                                                                {/if}
                                                            {/foreach}

                                                            {assign var='countcalc' value=1}
                                                            {foreach $CALCULATIONS as $calculation}
                                                                {if ($calculation.type == $BLOCKS[$i]['type'])}
                                                                    <tr id="row-cal-{$calculation.operation_id}">
                                                                    <td class="show-tools" style="color: #566573;">
                                                                        <div class="row">
                                                                            <div class="col-md-6">
                                                                            <span class="text"
                                                                                  title="{$calculation.calculation}">{$MODSTRING.LBL_CALCULATE} {$countcalc}
                                                                            </span>
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <div class="pi-tools">&nbsp;</div>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td class="alert-grey rgt">&nbsp;</td>
                                                                    <td class="ctr"><span class="label">&nbsp;</span>
                                                                    </td>
                                                                    {foreach $BOX_SCORE->dates as $date}
                                                                        {assign var='value' value='&nbsp;'}
                                                                        {if ($calculation.weeklytotal[$date.week]['cal'])}
                                                                            {assign var='value' value=$calculation.weeklytotal[$date.week]['cal']}
                                                                        {/if}
                                                                        <td style="padding-right: 20px; background-color: {if ($date.month == $MONTH_SEARCH)}{$boxScoreData.colordegrade}{else}{$boxScoreData.colorbase}{/if}"
                                                                            id="td-edcal-{$calculation.operation_id}-{$date.week}-{$calculation.weeklytotal[$date.week]['cal']}"
                                                                            class="rgt show-tools">
                                                                            <span id="bs-idcal-{$calculation.operation_id}-{$date.week}-{$calculation.weeklytotal[$date.week]['cal']}"><span
                                                                                        style="color: #566573;">{if ($value neq '&nbsp;') && !empty($value)}{$value|number_format:2:'.':''}{/if}</span>
                                                                            </span>
                                                                        </td>
                                                                    {/foreach}
                                                                    {assign var='countcalc' value=$countcalc + 1}
                                                                {/if}
                                                            {/foreach}
                                                            <tr>
                                                                <td class="show-tools">

                                                                </td>
                                                                <td colspan="2"></td>
                                                                <td colspan="10" align="center"
                                                                    style="background-color: {$BLOCKS[$i].colordegrade};">
                                                                    {if ($countbox > 0) && $IS_MOTHER}
                                                                        <button type="button"
                                                                                class="md-trigger btn btn-primary"
                                                                                data-modal="addValues"
                                                                                onclick="BoxScoreUtils.callAddValues('{$MODULE}', '{$BLOCKS[$i].type}', '{$BLOCKS[$i].boxscoreid}', '{$MONTH_SEARCH}', '{$APPCODE}');">
                                                                            <i class="fa fa-edit"></i> {$MODSTRING.LBL_EDIT_VALUE}
                                                                        </button>
                                                                    {else}
                                                                        &nbsp;
                                                                    {/if}
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="13"></td>
                                                            </tr>
                                                        {/for}
                                                    </table>
                                                </div>
                                            {/if}
                                        {/foreach}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="md-modal md-effect-1" id="addIndicators"></div>
    <div class="md-modal md-effect-1" id="addValues"></div>
    <div class="md-modal md-effect-1" id="addCalcules"></div>
{/block}