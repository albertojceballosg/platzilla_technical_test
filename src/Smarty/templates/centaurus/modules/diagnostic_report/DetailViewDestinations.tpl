{math equation= rand() assign= "idDestinationsView"}
<link type="text/css" rel="stylesheet" href="themes/centaurus/css/libs/datepicker.css"/>
<link rel="stylesheet" href="themes/centaurus/css/libs/bootstrap-timepicker.css" type="text/css"/>
<style>
    {literal}
    #main-{/literal}{$idDestinationsView}{literal} {
        background-color: #FFFFFF;
        min-height: 300px;
        padding: 10px;
    }

    #main-{/literal}{$idDestinationsView}{literal} .form-control {
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

    #main-{/literal}{$idDestinationsView}{literal} {
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
{if $GRID_VIEW neq NULL}
        {assign var='gridPosition' value=$GRID_VIEW->getPosition()}
    {else}
        {assign var='gridPosition' value=null}
    {/if}
<section class="">
    <div class="container" id="main-{$idDestinationsView}">
        <div class="row">
            <input type="hidden" id="destination-id-{$ID_TAB}" value="">
            {*$TOTAL_DESTINATION|var_dump*}
            <div class="col-lg-12 col-md-12 col-sm-12" style="margin-top: 25px">
                <div class="platzilla-card-header" style="">
                    <p class="text-center pull-left" style="font-weight: bold">¿Hasta dónde quieres llegar?</p>
                </div>
            </div>
            <div class="col-md-9 col-lg-9 col-sm-9">
                <div class=" card rounded" style="min-height: 150px;padding: 10px">
                    <p style="text-align: justify">
                        <strong>{if $DIAGNOSTIC_REPORT['business_name'] neq NULL}{$DIAGNOSTIC_REPORT['business_name']}{else}Su empresa{/if}</strong>&nbsp;
                        manifiesta, a través de las respuestas dadas al cuestionario, el
                        propósito de alcanzar lo siguiente:&nbsp;</p>
                    <div class="row  well well-sm" style="margin: 8px">
                        <h4>
                            <em>{if $DIAGNOSTIC_REPORT['target_category'] neq NULL}{$DIAGNOSTIC_REPORT['target_category']}{else}Seleccione un destino.{/if}{if $BUSINESS_DESTINATION neq NULL}:{/if}</em>
                        </h4>
                        {if $BUSINESS_DESTINATION neq NULL}
                            <div style="margin-left: 35px">
                            <a href="index.php?module=business_destination&parenttab=&action=DetailView&record={$BUSINESS_DESTINATION['record_id']}"
                               target=_blank">{$BUSINESS_DESTINATION['destination_name']}</a>
                            </div>
                        {/if}
                    </div>
                    <div id="selected-destination-{$idDestinationsView}" class="card rounded{if ($BUSINESS_DESTINATION neq NULL) || ($TOTAL_DESTINATION gt 1)} hide{/if}" style="padding: 10px;margin: 20px 0">
                        {if $AVAILABLE_DESTINATIONS neq NULL}
                        <ul class="dd-list ">
                            <li class="dd-item block-field" >
                                <div class="dd-links" style="display: inline-block; float: left; ">
                                    <button
                                        id="btn-view-{$idDestinationsView}"
                                        data-id="{$AVAILABLE_DESTINATIONS[0]['crmid']}"
                                        class="btn btn btn-link"
                                        type="button"
                                        title="ver destino"
                                         onclick="DiagnosticRerportUtils.getDestination(this,'{$idDestinationsView}')">
                                        <span class="glyphicon glyphicon-eye-open"></span>
                                    </button>

                                </div>
                                <div class="" style="display: inline-block; float: none; margin: 0 20px;width: 80%">
                                    <h4 id="destination-text-{$idDestinationsView}" style="text-align: center;"><em>{$AVAILABLE_DESTINATIONS[0]['destinationName']}</em></h4>
                                </div>
                                <div class="dd-links" style="display: inline-block; float: right; ">
                                    <div class="checkbox">
                                        <label>
                                            <input data-id="{$AVAILABLE_DESTINATIONS[0]['crmid']}" id="btn-set-{$idDestinationsView}"
                                                   onclick="DiagnosticRerportUtils.setDestination(this, '{$ID_TAB}', '{$idDestinationsView}')"
                                                   title="Seleccionar destino" type="checkbox" value="">
                                        </label>
                                    </div>
                                </div>
                            </li>
                        </ul>
                        {/if}
                    </div>
                    {str_replace('<br />', "", str_replace('<br>', "", {$DIAGNOSTIC_REPORT['explanatory_destination']}))}
                    <div style="margin-top: 20px">
                        {if $BUSINESS_DESTINATION eq NULL}

                        {if $TOTAL_DESTINATION gt 1}
                            <p style="text-align: justify; margin-top: 10px">A través del siguiente botón tiene disponible
                                                        un grupo de destinos asociados a la categoría preseleccionada en el cuestionario, entre los
                                                        cuales puedes seleccionar uno para obtener opciones de plan de acción.</p>
                        <button id="go-modal-{$idDestinationsView}" type="button" class="btn btn-success center-block"
                                data-toggle="modal"
                                href="#related-destinations-{$idDestinationsView}">&nbsp;Seleccionar destino&nbsp;</button>
                        {/if}
                        {/if}
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-lg-3 col-sm-3">
                <div class=" card rounded" style="min-height: 150px;padding: 10px">
                    <img src="storage/media/destination.png" alt="Destinos" class="img-rounded">

                </div>
            </div>
        </div>
    </div>
    <script type="text/html" id="empty-template-{$ID_TAB}">
        {include file='utils/HTMLPageLoanding.tpl'}
    </script>
</section>
<section>
    <div class="{if ($gridPosition eq 'SIDE') ||($gridPosition eq MULL)}col-md-4{else}col-md-12{/if}" style="margin-top: 25px; padding: 0!important;">
        {include file='modules/grid_view/DetailCardView.tpl'}
    </div>
</section>
{include file='modules/diagnostic_report/RelatedBusinessDestinations.tpl'}