{math equation= rand() assign= "idActionPlanView"}
<script type="text/javascript" src="themes/centaurus/js/charts/loader.js"></script>
<link rel="stylesheet" type="text/css" href="modules/model_action_plan/model-action-plan.css">
<style>
    {literal}
    #main-{/literal}{$idActionPlanView}{literal} {
        background-color: #FFFFFF;
        min-height: 300px;
        padding: 10px;
    }

    #main-{/literal}{$idActionPlanView}{literal} .form-control {
        display: inline-block;
        border: 1px solid #dee2e6 !important;
        width: 92% !important;
        margin-right: 0.1em !important;
        background-color: red;
        min-height: 300px;
    }

    .add_button {
        margin: 10px 0px 10px 0px;
    }

    .badge {
        padding: 0.4em !important;
        vertical-align: top !important;
    }

    .badge small {
        vertical-align: center !important;
    }

    .car-task {
        padding: 1.2em;
        margin-bottom: 1.2em;
    }

    .completed_item {
        text-decoration: line-through;
    }

    .text_holder {
        max-width: 100%;
        word-wrap: break-word;
    }

    #main-{/literal}{$idActionPlanView}{literal} {
        margin-top: 0;
        border-radius: 5px;
        width: 100%;
    }

    .flex-container {
        padding: 0;
        margin: 0;
        list-style: none;
        -ms-box-orient: horizontal;
        display: -webkit-box;
        display: -moz-box;
        display: -ms-flexbox;
        display: -moz-flex;
        display: -webkit-flex;
        display: flex;
    }

    .nowrap {
        -webkit-flex-wrap: nowrap;
        flex-wrap: nowrap;
    }

    .wrap {
        -webkit-flex-wrap: wrap;
        flex-wrap: wrap;
    }

    .flex-start {
        justify-content: flex-start;
    }

    .flex-end {
        justify-content: flex-end;
    }

    .space-evenly {
        justify-content: space-evenly;
    }

    .space-between {
        justify-content: space-between;
    }

    .flex-item {
        padding: 5px;
        width: 100px;
        height: 100px;
        margin: 10px;
        line-height: 100px;
    }

    .items-align-baseline {
        align-items: baseline;
    }

    .items-align-star {
        align-items: flex-start;
    }

    .item-date {
        font-size: small;
        font-style: italic;
    }

    .list-form {
        display: none;
    }

    .list-btn-header {
        text-align: center;
        font-weight: bold;
        font-size: small;
        background-color: #F6F6F6;
        margin-top: -5px;
        margin-bottom: -9px;
        padding-bottom: 0.3em;
    }

    .task-group-header {
        font-weight: bold;
        border-bottom: none !important;
        margin: 0.4em 0 !important;
    }

    .input-group-addon {
        color: #555555;
        background-color: #eeeeee;
        border-color: #cccccc !important;
    }

    }
    {/literal}
</style>
<section class="">
    <div class="container" id="main-{$idActionPlanView}">
        {if $MODEL neq NULL}
            {assign var="destination" value=$MODEL.destination}
            {assign var="plans" value=$MODEL.plans}
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12" style="margin-top: 25px">
                    <div class="platzilla-card-header" style="">
                        <p class="text-center pull-left" style="font-weight: bold">Modelo de plan de acción.</p>
                    </div>
                </div>
                <div class="main-box clearfix">
                    <div class="tabs-wrapper tabs-no-header">
                        {* info text introduction *}
                        <div class="col-sm-12 col-md-12 col-lg-12" style="margin-top: 10px; margin-left: 10px">
                            <h4 class="pull-left" style="font-weight: bold">Destino<span
                                        style="color: #777777;font-size: 0.8em;font-weight: bold">&nbsp;&gt;</span><span
                                        style="font-weight: bold">&nbsp;{$destination['destination_name']}</span></h4>
                        </div>
                        <div class="col-sm-12 col-md-12 col-lg-12  text-justify"
                             style="margin:2px 360px 2px 20px; padding-right: 60px">
                            {$destination['destination_description']}
                        </div>
                        <div class="col-sm-11 col-md-11 col-lg-11" style="margin: 0 10px">
                            <h4 class="pull-left" style="font-weight: bold">Requisitos para alcanzar destino:</h4>
                            <div class="row" style="padding: 0 10px">
                                {*<div class="col-sm-12 col-md-12 col-lg-12"><h4 class="pull-left" style="font-weight: bold">Requisitos para alcanzar destino:</h4></div>*}
                                <div class="col-sm-12 col-md-12 col-lg-12 text-justify"
                                     style="{*width: 100%; margin:2px 60px 2px 20px*}">
                                    {$destination['main_organizational_aspects']}
                                </div>
                            </div>
                        </div>
                        {* tabs for action plan *}
                        {if ($plans neq NULL) && ($ACTION_PLAN eq NULL)}
                            {assign var="totalPlans" value=$plans|count}
                            <div class="col-sm-12" style="margin-top: 15px">
                                <ul class="nav nav-tabs{if $totalPlans > 2}nav-justified{/if}">
                                    {for $t=1 to $totalPlans}
                                        <li class="{if $t eq 1}active{/if}">
                                            <a data-toggle="tab" href="#plan-{$idActionPlanView}-{$t}"
                                               style="font-weight: bold">Plan de acción&nbsp;{$t}
                                                &nbsp;</a>
                                        </li>
                                    {/for}
                                </ul>
                                {* destination tabs *}
                                <div class="tab-content tab-content-body clearfix" style="margin-top: 10px">
                                    {for $num=1 to $totalPlans}
                                        {assign var="index" value=($num -1)}
                                        {math equation= rand() assign= "idActionPlan"}
                                        {assign var="destinationData" value=$DESTINATION_ID|cat:'@'|cat:$plans[$index]['record_id']|cat:'@'|cat:$RECORD}
                                        <div class="tab-pane fade {if $num eq 1}active in{/if}"
                                             id="plan-{$idActionPlanView}-{$num}">
                                            <ul class="nav nav-tabs nav-justified">
                                                <li class="active">
                                                    <a data-toggle="tab"
                                                       href="#presentation-{$idActionPlan}">Presentación</a>
                                                </li>
                                                {if $plans[$index]['plan_initiatives'] neq NULL}
                                                    <li>
                                                        <a data-toggle="tab"
                                                           href="#strategies-{$idActionPlan}">Estragias e inicitivas</a>
                                                    </li>
                                                {/if}
                                            </ul>
                                            {* Action plan tabs *}
                                            <div class="tab-content tab-content-body clearfix">
                                                {* Presentación del plan *}
                                                <div class="tab-pane fade  active in" id="presentation-{$idActionPlan}">
                                                    <div class="row">
                                                        <div class="col-xs-12 col-md-12 col-lg-12"
                                                             style="margin: 10px 0">
                                                            <h2 class="text-center"
                                                                style="font-weight: bold">{$plans[$index]['action_plan_name']}</h2>
                                                        </div>
                                                        <div class="col-xs-7 col-md-7 col-lg-7">
                                                            <p class="text-justify">{$plans[$index]['plan_summary']}</p>
                                                        </div>
                                                        <div class="col-xs-5 col-md-5 col-lg-5">
                                                            {* Video *}
                                                            {if $plans[$index]['video_type'] neq NULL}
                                                                <div>
                                                                    {if $plans[$index]['video_type'] eq 'VIMEO'}
                                                                        {math equation= rand() assign= "idVideo"}
                                                                        <div id="video-{$idVideo}"
                                                                             class="embed-responsive embed-responsive-16by9"
                                                                             style="text-align: center;"
                                                                             data-vimeo-url="{$plans[$index]['informative_video']}">
                                                                        </div>
                                                                        <script type="text/javascript"
                                                                                src="https://player.vimeo.com/api/player.js"></script>
                                                                    {elseif ($plans[$index]['video_type'] eq 'YOUTUBE')}
                                                                        <div class="embed-responsive embed-responsive-16by9 video">
                                                                            <iframe class="embed-responsive-item"
                                                                                    src="{$plans[$index]['informative_video']}"
                                                                                    allow="autoplay; fullscreen"
                                                                                    allowfullscreen="" frameborder="0">
                                                                            </iframe>
                                                                        </div>
                                                                    {/if}
                                                                </div>
                                                                {* image *}
                                                            {elseif $plans[$index]['image_action_plan'] neq NULL}
                                                                <div>
                                                                    <img src="{$plans[$index]['image_action_plan']}"
                                                                         alt="Plan de acción" class="img-responsive">
                                                                </div>
                                                            {else}
                                                                <p class="text-center" style="font-weight: bold">
                                                                    Presentación del plan</p>
                                                            {/if}
                                                        </div>
                                                    </div>
                                                    <div class="row-model-action justify-content-center"
                                                         style="margin-top: 25px">
                                                        <div class="col-xs-4 col-md-4 col-lg-4">
                                                            <div class="input-group">
                                                                <input type="hidden"
                                                                       id="client-email-{$idActionPlan}"
                                                                value="">
                                                                <span class="input-group-btn">
                                                            <button class="btn btn-primary"
                                                                    data-destination="{$destinationData}"
                                                                    onclick="ModelActionPlanUtls.selectedPlan(this, '{$idActionPlan}')"
                                                                    type="button">
                                                                Seleccionar modelo
                                                            </button>
                                                        </span>
                                                            </div>
                                                        </div>
                                                        <div id="info-plan-selected-{$idActionPlan}"
                                                             class="col-md-12 hide"
                                                             style="margin-top: 2px">
                                                            <img id="loading-graphic" src="themes/images/loading.gif"
                                                                 alt="Loading" style="padding 0!important;"
                                                                 class="img-responsive center-block"/>
                                                            <p class="text-center text-danger"></p>
                                                        </div>
                                                    </div>
                                                </div>
                                                {* estraegias del pla*}
                                                <div class="tab-pane fade" id="strategies-{$idActionPlan}">
                                                    <div class="row">
                                                        <div class="col-xs-12 col-md-12 col-lg-12"
                                                             style="margin: 10px 0">
                                                            <h2 class="text-center"
                                                                style="font-weight: bold">{$plans[$index]['action_plan_name']}</h2>
                                                        </div>
                                                        {if $plans[$index]['plan_directives'] neq NULL}
                                                            <div class="col-xs-12 col-md-12 col-lg-12">
                                                                <ul class="list-group">
                                                                    <li class="list-group-item"
                                                                        style="background:#f9f8f7;font-weight: bold">
                                                                        Directrices del plan: Enunciado
                                                                    </li>
                                                                    {foreach $plans[$index]['plan_directives']['directive_enunciate_'] as $enunciate}
                                                                        <li class="list-group-item">{$enunciate}</li>
                                                                    {/foreach}
                                                                </ul>
                                                            </div>
                                                        {/if}
                                                        {if $plans[$index]['plan_initiatives'] neq NULL}
                                                            {assign var="totalInitiative" value=$plans[$index]['plan_initiatives']['action_plantfid']|count}
                                                            <div class="col-xs-12 col-md-12 col-lg-12"
                                                                 style="margin: 10px 0">
                                                                <table id="plan_initiatives-table"
                                                                       class="table table-bordered tablegridvalidate">
                                                                    <thead>
                                                                    <tr valign="top">
                                                                        <td class="text-center" width="45%">
                                                                            <span style="font-weight: bold">Iniciativa</span>
                                                                        </td>
                                                                        <td class="text-center" width="10%">
                                                                            <span style="font-weight: bold">% importancia</span>
                                                                        </td>
                                                                        <td class="text-center;" width="45%">
                                                                            <span style="font-weight: bold">Beneficios</span>
                                                                        </td>
                                                                    </tr>
                                                                    </thead>
                                                                    <tbody id="inititives-{$idActionPlan}">
                                                                    {for $k= 0 to ($totalInitiative -1)}
                                                                        <tr id="{$plans[$index]['plan_initiatives']['plan_initiativeid'][$k]}">
                                                                            <td>{$plans[$index]['plan_initiatives']['plan_initiative'][$k]}</td>
                                                                            <td class="text-right"
                                                                                style="padding: 0 6px">{$plans[$index]['plan_initiatives']['importance_factor'][$k]}</td>
                                                                            <td>{$plans[$index]['plan_initiatives']['initiative_benefit'][$k]}</td>
                                                                        </tr>
                                                                    {/for}
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        {/if}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    {/for}
                                </div>
                            </div>
                        {elseif $ACTION_PLAN neq NULL}
                            <div class="col-sm-12">
                                <div class="row  well well-sm" style="margin: 8px">
                                    <strong>Plan de acción seleccionado:</strong><br>
                                    <a href="index.php?module=action_plan&parenttab=&action=DetailView&record={$ACTION_PLAN['record_id']}"
                                       target="_blank">{$ACTION_PLAN['action_plan_name']}</a>
                                </div>
                            </div>
                        {else}
                            <div class="col-sm-12">&nbsp;</div>
                        {/if}
                    </div>
                </div>
            </div>
        {else}
            <div class="row">
                <div class="main-box clearfix"></div>
            </div>
        {/if}
        <script src="modules/model_action_plan/model-action-plan.js"></script>
    </div>
</section>