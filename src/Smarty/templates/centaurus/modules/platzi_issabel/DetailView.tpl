{extends file='modules/platzi_issabel/Base/DetailViewLayout.tpl'}
{block name="css"}
    <link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/libs/emojionearea.min.css"/>
    <link type="text/css" rel="stylesheet" href="modules/instancesdatasharing/instancesdatasharing.css"/>
    <link type="text/css" rel="stylesheet" href="themes/centaurus/css/compiled/pipeline.css"/>
    <link type="text/css" rel="stylesheet" href="themes/centaurus/css/compiled/detailview.css?v1.0.0"/>
    <link type="text/css" rel="stylesheet" href="themes/centaurus/css/compiled/platzilla-detailview.css"/>
    <link type="text/css" rel="stylesheet" href="themes/centaurus/css/bootstrap/bootstrap-editable.css"/>
    <link rel="stylesheet" type="text/css" href="themes/centaurus/css/bootstrap/nifty-component.css"/>
    <link rel="stylesheet" type="text/css" href="themes/centaurus/css/bootstrap/bootstrap-cards.css"/>
    <link rel="stylesheet" type="text/css" href="modules/grid_view/grid-view.css"/>
    <link type="text/css" rel="stylesheet" href="themes/centaurus/css/messageBox.min.css"/>
    <style>
        #card-view-container {
            margin-top: 20px;
            /* max-height: 650px;
            overflow-y: auto;
            padding:    0 20px !important;
            scrollbar-width: thin; */
        }

        #card-view-register-container {
            /*margin-top: 20px;*/
            max-height: 110%;
            overflow-y: auto;
            padding: 0 !important;
            overflow-x: hidden;
            z-index: 10000;
            scrollbar-width: thin;
        }

        .btn-circle.btn-xl {
            width: 70px;
            height: 70px;
            padding: 10px 16px;
            border-radius: 35px;
            font-size: 24px;
            line-height: 1.33;
        }

        .btn-circle {
            width: 30px;
            height: 30px;
            padding: 6px 0px;
            border-radius: 15px;
            text-align: center;
            font-size: 12px;
            line-height: 1.42857;
        }

        .platzilla-card-header {
            background-color: #FFFFFF;
            border-bottom-color: #FFFFFF;
            font-family: helvetica, arial, sans-serif;
            font-size: 1.5em
        }

        .platzilla-card-header p {
            font-size: 1.05em;
            margin-left: 0 !important;
            padding-left: 0 !important;
        }

        .rounded {
            border-radius: .75rem !important
        }

        @media (min-width: 1280px) and (max-width: 1300px) {
            .platzilla-card-header p {
                font-size: 0.85em;
                margin-left: 0 !important;
                padding-left: 0 !important;
            }
        }

        @media (min-width: 1400px) and (max-width: 1580px) {
            .platzilla-card-header p {
                font-size: 0.9em;
                margin-left: 0 !important;
                padding-left: 0 !important;
            }
        }

        @media (min-width: 1600px) and (max-width: 1800px) {
            .platzilla-card-header p {
                font-size: 1.05em;
                margin-left: 0 !important;
                padding-left: 0 !important;
            }
        }
    </style>
{/block}
{block name="card_contenet"}
    <div class="card-header platzilla-card-header rounded">
        <div class="row">
            <div class="col-md-5">
                <p class="text-center pull-left" style="font-weight: bold">Detalle del registro</p>
            </div>
            <div class="col-md-7">
                <div class="pull-right">&nbsp;</div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="row" style="margin-top: -4px">
            <div class="main-box">
                <div class="main-box-body clearfix" style="padding: 0 2px;!important;" id="tblInformaciónbásica">
                    {* recordnig date*}
                    <div class="col-md-6">
                        <div class="col-md-5">
                            <div class="label-input" style="margin-left: 2px !important;">
                                <label for="td_date_" style="line-height: 1.25em !important;"><span
                                            id="helpDV4_date_" name="help" style="font-size:0.2em;">
                                    </span>Fecha de grabación</label>
                            </div>
                        </div>
                        <div class="form-group col-md-7 data-input" id="td_date_" style="display: block;">
                            <div class="input-group" style="width: 100%;">
                                <span class="form-control   b-left" style="overflow-x: hidden;width: 100%"
                                      data-toggle="tooltip">{$RECORDING->getDate()}</span>
                            </div>
                        </div>
                    </div>
                    {* recording time *}
                    <div class="col-md-6">
                        <div class="col-md-5">
                            <div class="label-input">
                                <label for="td_nombre_contacto" style="line-height: 1.25em !important;"><span
                                            id="helpDV1_nombre_contacto" name="help" style="font-size:0.2em;">
                                                    </span>Hora</label>
                            </div>
                        </div>
                        <div class="form-group col-md-7 data-input" id="td_nombre_contacto" style="display: block;">
                            <div class="input-group" style="width: 100%;">
                                <span class="form-control   b-left" style="overflow-x: hidden;width: 100%"
                                      data-toggle="tooltip">{$RECORDING->getTime()}</span>
                            </div>
                        </div>
                    </div>
                    {* recording destination *}
                    <div class="col-md-6">
                        <div class="col-md-5">
                            <div class="label-input">
                                <label for="td_destination" style="line-height: 1.25em !important;">
                                    <span id="helpDV1_destination" name="help"
                                          style="font-size:0.2em;"></span>Destino</label>
                            </div>
                        </div>
                        <div class="form-group col-md-7 data-input" id="td_destination" style="display: block;">
                            <div class="input-group" style="width: 100%;">
                                <span class="form-control   b-left" style="overflow-x: hidden;width: 100%"
                                      data-toggle="tooltip">{$RECORDING->getDestination()}</span>
                            </div>
                        </div>
                    </div>
                    {* recording duration *}
                    <div class="col-md-6">
                        <div class="col-md-5">
                            <div class="label-input">
                                <label for="td_duration" style="line-height: 1.25em !important;">
                                    <span id="helpDV1_duration" name="help" style="font-size:0.2em;">
                                    </span>Duración</label>
                            </div>
                        </div>
                        <div class="form-group col-md-7 data-input" id="td_duration" style="display: block;">
                            <div class="input-group" style="width: 100%;">
                                <span class="form-control   b-left" style="overflow-x: hidden;width: 100%"
                                      data-toggle="tooltip">{$RECORDING->getDuration()}</span>
                            </div>
                        </div>
                    </div>
                    {* recording type *}
                    <div class="col-md-6">
                        <div class="col-md-5">
                            <div class="label-input">
                                <label for="td_apellidos_contacto" style="line-height: 1.25em !important;"><span
                                            id="helpDV1_apellidos_contacto" name="help" style="font-size:0.2em;">
                                    </span>Tipo de grabación</label>
                            </div>
                        </div>
                        <div class="form-group col-md-7 data-input" id="td_apellidos_contacto" style="display: block;">
                            <div class="input-group" style="width: 100%;">
                                <span class="form-control   b-left" style="overflow-x: hidden;width: 100%"
                                      data-toggle="tooltip">{$RECORDING->getType()}</span>
                            </div>
                        </div>
                    </div>
                    {* recording origin *}
                    <div class="col-md-6">
                        <div class="col-md-5">
                            <div class="label-input">
                                <label for="td_origin" style="line-height: 1.25em !important;">
                                    <span id="helpDV13_origin" name="help" style="font-size:0.2em;"></span>Origen</label>
                            </div>
                        </div>
                        <div class="form-group col-md-7 data-input" id="td_origin" style="display: block;">
                            <div class="input-group" style="width: 100%;">
                                <span class="form-control   b-left" style="overflow-x: hidden;width: 100%"
                                      data-toggle="tooltip">{$RECORDING->getOrigin()}</span>
                            </div>
                        </div>
                    </div>
                    {* recording message *}
                    <div class="col-md-6">
                        <div class="col-md-5">
                            <div class="label-input">
                                <label for="td_telefono" style="line-height: 1.25em !important;">
                                    <span id="helpDV11_telefono" name="help" style="font-size:0.2em;"></span><i
                                            class="fa fa-home"></i>&nbsp;Mensaje</label>
                            </div>
                        </div>
                        <div class="form-group col-md-7 data-input" id="td_telefono" style="display: block;">
                            <div class="input-group">
                                {if $RECORDING_AUDIO neq NULL}
                                <audio controls>
                                  <source src="{$RECORDING_AUDIO['fullpath']}" type="{$RECORDING_AUDIO['mimetype']}">
                                Your browser does not support the audio element.
                                </audio>
                                {/if}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
{/block}
