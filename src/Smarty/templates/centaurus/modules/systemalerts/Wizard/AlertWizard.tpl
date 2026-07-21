{strip}
    <link rel="stylesheet" type="text/css" href="themes/centaurus/css/libs/bootstrap-wizard.css"/>
    <link rel="stylesheet" type="text/css" href="themes/centaurus/css/compiled/section/bootstrap-wizard_custom.css"/>
    <script type="text/html" id="alert-wizard-template-{$idAlertListView}">
        <div id="alert-wizard" class="wizard" data-title="">
            <h1>{$MODSTRING['LBL_ALERT_MODAL_TITLE']}</h1>
            {* Config Alert Area *}
            <div id="start-section" class="wizard-card" data-cardname="start">
                <input type="hidden" id="module" name="module" value="systemalerts">
                <input type="hidden" id="flmodule" name="elementName" value="">
                <input type="hidden" id="flmodulelabel" name="elementLabel" value="">
                <input type="hidden" id="action" name="action" value="SaveAlert">
                <input type="hidden" id="systemAlertId" name="systemAlertId" value="">
                <input type="hidden" id="app" name="app" value="{$TAB_ACTIVE}">
                <input type="hidden" id="cod-type" value="">
                <input type="hidden" id="edit-app" value="">
                <input type="hidden" id="mode" name="mode" value="">
                <input type="hidden" id="systemAlertIdRel" name="systemAlertIdRel" value="">
                <input type="hidden" id="datarel" name="datarel" value="">
                <input type="hidden" id="bxdatarel" name="bxdatarel" value="">
                <input type="hidden" id="boxscoreid" name="boxscoreid" value="">
                <input type="hidden" name="datasource" value="wizard"/>
                <input type="hidden" name="Ajax" value="true"/>
                <h3 class="hide-element">{$MODSTRING['NAV_START']}</h3>
                <h4 class="hidden-md hidden-lg">{$MODSTRING['NAV_START']}</h4>
                <div id="step-0-section" data-load="0" class="row wizard-input-section data-section">
                    <img id="loading-graphic"  src="themes/images/loading.gif" alt="Loading" style="padding 0!important;" class="img-responsive center-block" />
                </div>
            </div>
            {* source alert*}
            <div id="step-1-section" class="wizard-card" data-cardname="step-1">
                <h3 class="hide-element">{$MODSTRING['NAV_STEP1']}</h3>
                <h4 class="hidden-md hidden-lg">{$MODSTRING['NAV_STEP1']}</h4>
                <div class="row wizard-input-section data-section"  data-load="0">
                    <img id="loading-graphic"  src="themes/images/loading.gif" alt="Loading" style="padding 0!important;" class="img-responsive center-block" />
                </div>
            </div>
            {* filter *}
            <div id="step-2-section" class="wizard-card" data-cardname="step-2">
                <h3 class="hide-element">{$MODSTRING['NAV_STEP2']}</h3>
                <h4 class="hidden-md hidden-lg">{$MODSTRING['NAV_STEP2']}</h4>
                <div class="wizard-input-section data-section" data-load="0">
                    <img id="loading-graphic"  src="themes/images/loading.gif" alt="Loading" style="padding 0!important;" class="img-responsive center-block" />
                </div>
            </div>
            {* setting alert
            <div id="step-3-section" class="wizard-card" data-cardname="step-3">
                <h3 class="hide-element">{$MODSTRING['NAV_STEP3']}</h3>
                <div class="wizard-input-section data-section"></div>
            </div>
            *}
            {* Next step
            <div id="step-4-section" class="wizard-card" data-cardname="step-4">
                <h3 class="hide-element">{$MODSTRING['NAV_STEP4']}</h3>
                <div class="wizard-input-section data-section"></div>
            </div>
             *}
            {* End step
            <div id="step-4-section" class="wizard-card" data-cardname="step-4">
                <h3 class="hide-element">{$MODSTRING['NAV_STEP5']}</h3>
                <div class="wizard-input-section data-section"></div>
            </div>
            *}
            <div class="wizard-failure text-center">
                <h4><strong style="color: #880000;">Error!</strong>: Se ha presentado un error al guardar la
                    &nbsp;alerta</h4>
                <p class="message"></p>
            </div>
            <div class="wizard-loading text-center">
                <h4><strong>Por favor espera</strong></h4>
                <p>Estamos guardando la alerta. Por favor espera unos instantes y por favor no cierres esta
                    ventana</p>
                <img src="themes/images/loading.gif" class="img-responsive" style="display: inline-block;"/>
            </div>
            <div class="wizard-success text-center">
                <h4><strong style="color: #008800;">Listo!</strong>: Se ha guardado la alerta</h4>
                <button type="button" class="btn btn-default" style="margin-left: 5px;"
                        onclick="SystemAlertUtils.closeAlertWizard ();">Terminar
                </button>
            </div>
        </div>
    </script>
    <script type="text/javascript" src="include/ckeditor/ckeditor.js"></script>
    <script type="text/javascript" src="themes/centaurus/js/bootstrap-wizard.js"></script>
{/strip}