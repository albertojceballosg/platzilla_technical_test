{strip}
    <div class="table-responsive la_table" >
        {*$BOX_SCORE->dates|var_dump*}
        <table class="table">
            <tr>
                <th colspan="8" class="alert-grey lft">
                    {if isset ($itemApp)}
                     {$MODSTRING.CATEGORY}:&nbsp;&nbsp;{$itemApp.app_name}
                    {/if}
                </th>
            </tr>
            <tr>
                <th class="lft" style="width: 230px;">{$MODSTRING.LBL_INDICATORS}</th>
                <th class="alert-grey ctr">{$MODSTRING.LBL_OBJECT}</th>
                <th>{$MODSTRING.LBL_CUMPL}</th>
                {assign var='countdate' value=1}
                {foreach $BOX_SCORE->dates as $date}
                    <th>{*$date.date|var_dump*}
                        {if ($BOX_SCORE->scale == 'Week')}
                            <div>
                                <small>{$WEEKS[$date['week']]['start']} <br> {$WEEKS[$date['week']]['end']}</small>
                            </div>
                        {else}
                            {assign var='month' value=$date.date|date_format: 'M'} {$MODSTRING.MONTHS[$month]} - {$YEAR_DATE}
                            {*$month|var_dump*}
                            {/if}
                    </th>
                    {assign var='countdate' value=$countdate + 1}
                {/foreach}
            </tr>
            {for $i=0; $i<count($BLOCKS); $i++}
                {assign var='countbox' value=0}
                {foreach $BOX_SCORE->boxs as $boxScoreData}
                    {if ($boxScoreData.type == $BLOCKS[$i]['type'])}
                        <tr id="row-{$boxScoreData.box_score_dataid}">
                            <td class="show-tools" style="color: #566573;">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="pi-tools">
                                            {if $IS_MOTHER  || true}
                                                <a href="addIndicators" data-toggle="modal" style="margin: 0 1px"
                                                   onclick="callAddEditIndicators('{$MODULE}', '{$BLOCKS[$i].type}', '{$boxScoreData.boxscoreid}', '{$MONTH_SEARCH}', '{$APPCODE}','{$boxScoreData.box_score_dataid}', 'edit');"><i
                                                            title="{$MODSTRING.LBL_EDIT}"
                                                            class="fa fa-edit"></i></a>
                                            {/if}
                                            {if $IS_MOTHER}
                                                <a href="javascript:void(0)" fn="delete-row"
                                                   id="{$boxScoreData.box_score_dataid}" style="color:red;margin: 0 1px"
                                                   onclick="callDeleteIndicator(this)"><i
                                                            title="{$MODSTRING.LBL_DELETE}"
                                                            class="fa fa-trash-o"></i></a>
                                            {/if}
                                            <a href="#modalInfo_{$boxScoreData.box_score_dataid}" style="margin: 0 1px"
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
                                                    <a href="#" id="favorite_{$boxScoreData.box_score_dataid}" style="margin: 0 1px"
                                                       rel="{$boxScoreData.name}"
                                                       onclick="BoxScoreUtils.updateFavorite(this, event)"
                                                       title="{$favoriteTitle}">
                                                        <span id="fa-{$boxScoreData.name}"
                                                              class="{$favoriteIcon}"></span>
                                                    </a>
                                                </span>
                                            {/if}
                                            <a href="#" class="{if $boxScoreData.on_railes eq 'SHOW'}railes_green{else}railes_red{/if}"
                                               style="margin: 0 1px;"
                                               title="{if $boxScoreData.on_railes eq 'SHOW'}No mostrar en Raíles{else}Mostrar en Raíles{/if}"
                                               rel="{$boxScoreData.name}"
                                               data-status="{$boxScoreData.on_railes}"
                                               onclick="BoxScoreUtils.updateRailes(this, event)">&#128646;
                                               {*<i class="fa fa-taxi" aria-hidden="true"></i>*}
                                            </a>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <span class="text">
                                            {$boxScoreData.box_score}
                                        </span>
                                    </div>
                                </div>
                                <div id="modalInfo_{$boxScoreData.box_score_dataid}" class="modal fade"
                                     aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1"
                                     style="display: none;">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header" style="text-align:center">
                                                <button class="close" aria-hidden="true" data-dismiss="modal"
                                                        type="button"
                                                        onclick="jQuery ('.md-overlay').css ({ldelim} opacity: 0.0, visibility: 'hidden' {rdelim});">
                                                    ×
                                                </button>
                                                <h4 class="modal-title">
                                                    <span style="color: black">{$MODSTRING.LBL_MOREINFO}</span>
                                                </h4>
                                            </div>
                                            <div class="modal-body">
                                                <span style="color: black">{$boxScoreData.description}</span>
                                            </div>
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
                                    id="td-ed-{$boxScoreData.box_score_dataid}-{$date.week}" class="rgt show-tools">
                                    {if ($value > 0) && ($BOX_SCORE->warning[$boxScoreData.box_score_dataid][$dummy] == '1')}
                                    <i class="fa fa-warning red"></i>&nbsp;{/if}<span
                                            id="bs-id-{$boxScoreData.box_score_dataid}-{$date.week}">{$value|number_format:2:",":"."}</span>
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
                                              title="{$calculation.calculation}">{$MODSTRING.LBL_CALCULATE} {$countcalc}</span>
                                </div>
                                <div class="col-md-6">
                                    <div class="pi-tools">
                                        <a href="addCalcules" data-toggle="modal"
                                           onclick="callAddCalcules('{$MODULE}', '{$BLOCKS[$i].type}', '{$RECORD}', '{$MONTH_SEARCH}', '{$APPCODE}', 'edit', {$calculation.operation_id});"><i
                                                    title="{$MODSTRING.LBL_EDIT}" class="fa fa-edit"></i></a>
                                        <a href="javascript:void(0)"
                                           onclick="deleteCalc('{$calculation.operation_id}')"
                                           fn="delete-row-cal" class="delete-row-cal"
                                           idop="{$calculation.operation_id}"
                                           id="idoperation{$calculation.operation_id}" style="color: red;"><i
                                                    title="{$MODSTRING.LBL_DELETE}" class="fa fa-trash-o"></i></a>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="alert-grey rgt">&nbsp;</td>
                        <td class="ctr"><span class="label">&nbsp;</span></td>
                        {foreach $BOX_SCORE->dates as $date}
                            {assign var='value' value='&nbsp;'}
                            {if ($calculation.weeklytotal[$date.week]['cal'])}
                                {assign var='value' value=$calculation.weeklytotal[$date.week]['cal']}
                            {/if}
                            <td style="padding-right: 20px; background-color: {if ($date.month == $MONTH_SEARCH)}{$boxScoreData.colordegrade}{else}{$boxScoreData.colorbase}{/if}"
                                id="td-edcal-{$calculation.operation_id}-{$date.week}-{$calculation.weeklytotal[$date.week]['cal']}"
                                class="rgt show-tools">
                                            <span id="bs-idcal-{$calculation.operation_id}-{$date.week}-{$calculation.weeklytotal[$date.week]['cal']}"><span
                                                        style="color: #566573;">{$value|number_format:2:".":""}</span></span>
                            </td>
                        {/foreach}
                        {assign var='countcalc' value=$countcalc + 1}
                    {/if}
                {/foreach}
                <tr>
                    <td class="show-tools">
                        {if $IS_MOTHER}
                            <button type="button" class="md-trigger btn btn-primary" data-modal="addIndicators"
                                    onclick="jQuery ('#addElement-{$BLOCKS[$i].type}').modal('hide'); callAddEditIndicators('{$MODULE}', '{$BLOCKS[$i].type}', '{$BLOCKS[$i].boxscoreid}', '{$MONTH_SEARCH}', '{$APPCODE}', '', 'create');">
                                <i class="fa fa-edit"></i> {$MODSTRING.MESS_ADD_BOX_SCORE}</button>
                        {/if}
                        {* a futuro incluir calculos entre indicadores
                            <a href="#addElement-{$BLOCKS[$i].type}" data-toggle="modal" class="btn btn-primary btn-sm" onclick="jQuery('.md-overlay').css({ldelim}opacity: 1, visibility: 'visible'{rdelim});"><i class="fa fa-edit"></i>&nbsp;{$MODSTRING.LBL_ADD_BS}</a>
                        *}
                        &nbsp;&nbsp;
                        <div class="pi-tools">
                            {if $IS_MOTHER}
                                <a href="#crearblock" data-toggle="modal"
                                   onclick="jQuery('.md-overlay').css({ldelim}opacity: 1, visibility: 'visible'{rdelim});jQuery('#type').val('{$BLOCKS[$i].type}');jQuery('#titleBlock').html('{$MODSTRING.LBL_EDIT_BLOCK}');"><i
                                            title="{$MODSTRING.LBL_EDIT_BLOCK}" class="fa fa-edit"></i></a>
                                <a href="javascript:void(0)" fn="delete-row" id="deleteBlock-{$BLOCKS[$i].type}"
                                   style="color:red;"
                                   onclick="callDeleteBlock('{$BLOCKS[$i].type}', '{$countbox}', '{$countcalc-1}')"><i
                                            title="{$MODSTRING.LBL_DELETE_BLOCK}" class="fa fa-trash-o"></i></a>
                            {/if}
                        </div>

                        <div id="addElement-{$BLOCKS[$i].type}" class="modal fade" aria-hidden="true"
                             aria-labelledby="myModalLabelElement" role="dialog" tabindex="-1"
                             style="display: none;">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header" style="text-align:center">
                                        <button class="close" aria-hidden="true" data-dismiss="modal" type="button"
                                                onclick="jQuery ('.md-overlay').css ({ldelim} opacity: 0.0, visibility: 'hidden' {rdelim});jQuery ('#addElement-{$BLOCKS[$i].type}').modal('show')">
                                            ×
                                        </button>
                                        <h4 class="modal-title">
                                            <span id="titleBlock"
                                                  style="color: black;">{$MODSTRING.LBL_TITLE_ADD_ELEMNT}
                                            </span>
                                        </h4>
                                    </div>
                                    <div class="modal-body" style="text-align: center;">
                                        <div class="table-responsive">
                                            <form role="form">
                                                <div class="form-group">
                                                    <button type="button" class="md-trigger btn btn-primary"
                                                            data-modal="addIndicators"
                                                            onclick="jQuery ('#addElement-{$BLOCKS[$i].type}').modal('hide'); callAddEditIndicators('{$MODULE}', '{$BLOCKS[$i].type}', '{$BLOCKS[$i].boxscoreid}', '{$MONTH_SEARCH}', '{$APPCODE}', '', 'create');">
                                                        <i class="fa fa-edit"></i> {$MODSTRING.MESS_ADD_BOX_SCORE}
                                                    </button>
                                                </div>
                                                {if ($countbox > 0)}
                                                    <div class="form-group">
                                                        <button type="button" class="md-trigger btn btn-primary"
                                                                data-modal="addCalcules"
                                                                onclick="jQuery ('#addElement-{$BLOCKS[$i].type}').modal('hide'); callAddCalcules('{$MODULE}', '{$BLOCKS[$i].type}', '{$BLOCKS[$i].boxscoreid}', '{$MONTH_SEARCH}', '{$APPCODE}', 'create', '');">
                                                            <i class="fa fa-edit"></i> {$MODSTRING.LBL_ADD_BS_CALC}
                                                        </button>
                                                    </div>
                                                {/if}
                                            </form>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-default" data-dismiss="modal" type="button"
                                                onclick="jQuery ('.md-overlay').css ({ldelim} opacity: 0.0, visibility: 'hidden' {rdelim});">{$MODSTRING.LBL_CLOSE}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td colspan="2"></td>
                    <td colspan="10" align="center" style="background-color: {$BLOCKS[$i].colordegrade};">
                        {if ($countbox > 0) && ($IS_MOTHER || ($IS_ADMIN eq 'on')) }
                            <button type="button" class="md-trigger btn btn-primary" data-modal="addValues"
                                    onclick="callAddValues('{$MODULE}', '{$BLOCKS[$i].type}', '{$RECORD}', '{$MONTH_SEARCH}', '{$APPCODE}');">
                                <i class="fa fa-edit"></i> {$MODSTRING.LBL_EDIT_VALUE}</button>
                        {else}
                            &nbsp;&nbsp;
                        {/if}
                    </td>
                </tr>
                <tr>
                    <td colspan="13"></td>
                </tr>
            {/for}
        </table>
    </div>
{/strip}