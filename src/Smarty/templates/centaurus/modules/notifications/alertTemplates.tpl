{strip}
    <script type="text/html" id="simple-template">
        <div class="alert __ACTION__  alert-dismissable notification __HIDDEN__ " data-id="__ID__" style="background-image: url('themes/centaurus/img/platzillaman.png'); background-position: 5px 5px; background-repeat: no-repeat; background-size: auto 60px; min-height: 75px;">
            <button type="button" class='close' style="font-weight: bold" data-dismiss="alert">&times;</button>
            <strong style="padding-left: 60px;">¡__MESSAGE__!</strong><p style="padding-left: 60px;">Escriba aquí su mensaje</p>
        </div>
    </script>
    <script type="text/html" id="simple-template-link">
        <div class="alert __ACTION__  alert-dismissable notification __HIDDEN__" data-id="__ID__" style="background-image: url('themes/centaurus/img/platzillaman.png'); background-position: 5px 5px; background-repeat: no-repeat; background-size: auto 60px; min-height: 75px;">
            <button type="button" class='close' data-dismiss="alert">&times;</button>
            <strong style="padding-left: 60px;">¡__MESSAGE__!</strong><p style="padding-left: 60px;">Escriba aquí su mensaje <a href="#"  target="_blank"  class="alert-link">Configurar enlace</a></p>
        </div>
    </script>
    <script type="text/html" id="simple-alert-template">
        <div class="alert __ACTION__  alert-dismissable">
            Escriba aquí su mensaje
        </div>
    </script>
    <script type="text/html" id="simple-template-collapse">
        <div class="alert __ACTION__  alert-dismissable notification __HIDDEN__" data-id="__ID__">
            <button type="button" class='close' style="font-weight: bold" data-dismiss="alert">&#88;</button>
            <p><strong>¡__MESSAGE__!</strong> Escriba aquí su mensaje <a data-target="#new-notify" data-toggle="collapse" href="#"><span style="font-size: small;font-weight: bold">[ ver mas ]</span></a></p>
            <div id="new-notify" class="__COLLAPSE_IN__" style="padding: 6px 4px;">
                <p>Escriba el contenido a mostrar/ocultar aquí</p>
            </div>
        </div>
    </script>
    <script type="text/html" id="simple-modal-0">
        <div class="modal-dialog" style="width: auto; max-width: 600px;">
            <div class="modal-content">
                <div class="modal-header">
                    <button aria-hidden="true" class="close" data-dismiss="modal" type="button">×</button>
                    <h4 class="modal-title"></h4>
                </div>
                <div class="modal-body">
                    <div class="ekko-lightbox-container">
                        <div id="div-input-text" style="margin-top: 12px!important;display: __INPUT_DISPLAY__;">
                            __INPUT_TEXT__
                        </div>
                        <div id="div-exit-text" style="margin-top: 12px!important;display: __EXIT_DISPLAY__;">
                            __EXIT_TEXT__
                        </div>
                        <div style="margin-top: 12px!important;">
                            <label class="checkbox-inline"><input id="modalCheck" type="checkbox" value="NO" /> No volver a mostrar esta notificación </label></div>
                    </div>
                </div>
                <div class="modal-footer" style="display:none">
                    null</div>
            </div>
        </div>
    </script>
    <script type="text/html" id="simple-modal-1">
    <div class="modal-dialog" style="width: auto; max-width: 600px;">
        <div class="modal-content">
            <div class="modal-header">
                <button aria-hidden="true" class="close" data-dismiss="modal" type="button">×</button>
                <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body">
                <div class="ekko-lightbox-container">
                    <div id="div-input-text" style="margin-top: 12px!important;display: __INPUT_DISPLAY__;">
                        __INPUT_TEXT__
                    </div>
                    <div id="div-exit-text" style="margin-top: 12px!important;display: __EXIT_DISPLAY__;">
                        __EXIT_TEXT__
                    </div>
                    <div style="margin: 6px 0px; display: __BUTTON_DISPLAY__;">
                        <a id="modal-boton" class="center-block btn btn-__ACTION1__" href="#" role="button">__LABEL1__</a>
                    </div>
                    <div style="margin-top: 12px!important;">
                        <label class="checkbox-inline"><input id="modalCheck" type="checkbox" value="NO" /> No volver a mostrar esta notificación </label></div>
                </div>
            </div>
            <div class="modal-footer" style="display:none">
                null</div>
        </div>
    </div>
</script>
    <script type="text/html" id="simple-modal-2">
        <div class="modal-dialog" style="width: auto; max-width: 600px;">
            <div class="modal-content">
                <div class="modal-header">
                    <button aria-hidden="true" class="close" data-dismiss="modal" type="button">×</button>
                    <h4 class="modal-title"></h4>
                </div>
                <div class="modal-body">
                    <div class="ekko-lightbox-container">
                        <div id="div-input-text" style="margin-top: 12px!important;display: __INPUT_DISPLAY__;">
                            __INPUT_TEXT__
                        </div>
                        <div id="div-exit-text" style="margin-top: 12px!important;display: __EXIT_DISPLAY__;">
                            __EXIT_TEXT__
                        </div>
                        <div class="center-block" style="margin: 6px 0px; display: __BUTTON_DISPLAY__;">
                            <div class="btn-toolbar row" role="toolbar">
                                <div class="btn-group col-xs-12">
                                    <a href="#"  role="button" class="col-xs-6 btn btn-__ACTION1__">__LABEL1__</a>
                                    <a href="#"  role="button" class="col-xs-6 btn btn-__ACTION2__">__LABEL2__</a>

                                </div>
                            </div>
                        </div>
                        <div style="margin-top: 12px!important;">
                            <label class="checkbox-inline"><input id="modalCheck" type="checkbox" value="NO" /> No volver a mostrar esta notificación </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="display:none">
                </div>
            </div>
        </div>
</script>
    <script type="text/html" id="simple-modal-3">
        <div class="modal-dialog" style="width: auto; max-width: 600px;">
            <div class="modal-content">
                <div class="modal-header">
                    <button aria-hidden="true" class="close" data-dismiss="modal" type="button">×</button>
                    <h4 class="modal-title"></h4>
                </div>
                <div class="modal-body">
                    <div class="ekko-lightbox-container">
                        <div id="div-input-text" style="margin-top: 12px!important;display: __INPUT_DISPLAY__;">
                            __INPUT_TEXT__
                        </div>
                        <div id="div-exit-text" style="margin-top: 12px!important;display: __EXIT_DISPLAY__;">
                            __EXIT_TEXT__
                        </div >
                        <div class="center-block" style="margin: 6px 0px; display: __BUTTON_DISPLAY__;">
                            <div class="btn-toolbar row" role="toolbar">
                                <div class="btn-group col-xs-12">
                                    <a href="#"  role="button" class="col-xs-4 btn btn-__ACTION1__">__LABEL1__</a>
                                    <a href="#"  role="button" class="col-xs-4 btn btn-__ACTION2__">__LABEL2__</a>
                                    <a href="#"  role="button" class="col-xs-4 btn btn-__ACTION3__">__LABEL3__</a>
                                </div>
                            </div>
                        </div>
                        <div style="margin-top: 12px!important;">
                            <label class="checkbox-inline"><input id="modalCheck" type="checkbox" value="NO" /> No volver a mostrar esta notificación </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="display:none">
                </div>
            </div>
        </div>
    </script>
    <script type="text/html" id="simple-modal-4">
        <div class="modal-dialog" style="width: auto; max-width: 600px;">
            <div class="modal-content">
                <div class="modal-header">
                    <button aria-hidden="true" class="close" data-dismiss="modal" type="button">×</button>
                    <h4 class="modal-title"></h4>
                </div>
                <div class="modal-body">
                    <div class="ekko-lightbox-container">
                        <div id="div-input-text" style="margin-top: 12px!important;display: __INPUT_DISPLAY__;">
                            __INPUT_TEXT__
                        </div>
                        <div id="div-exit-text" style="margin-top: 12px!important;display: __EXIT_DISPLAY__;">
                            __EXIT_TEXT__
                        </div>

                        <div class="center-block" style="margin: 6px 0px; display: __BUTTON_DISPLAY__;">
                            <div class="btn-toolbar row" role="toolbar">
                                <div class="btn-group col-xs-12">
                                    <a href="#"  role="button" class="col-xs-4 btn btn-__ACTION1__">__LABEL1__</a>
                                    <a href="#"  role="button" class="col-xs-4 btn btn-__ACTION2__">__LABEL2__</a>
                                    <a href="#"  role="button" class="col-xs-4 btn btn-__ACTION3__">__LABEL3__</a>
                                    <a href="#"  role="button" class="col-xs-4 btn btn-__ACTION4__">__LABEL4__</a>
                                </div>
                            </div>
                        </div>
                        <div style="margin-top: 12px!important;">
                            <label class="checkbox-inline"><input id="modalCheck" type="checkbox" value="NO" /> No volver a mostrar esta notificación </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="display:none">
                </div>
            </div>
        </div>
    </script>
    {/strip}