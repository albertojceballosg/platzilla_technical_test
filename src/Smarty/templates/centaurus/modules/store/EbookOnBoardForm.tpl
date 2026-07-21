{extends file="base/BaseForm.tpl"}
{block name="header-logo"}
    {strip}
        <img alt="" style="width: 80%; height: 80% " src="themes/centaurus/img/three-logos.jpeg">
        {*logo-platzilla-vert.png*}
    {/strip}
{/block}
{block name="box-title"}
    {strip}
        {if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
            <div class="row">
                <div class="alert alert-{if (isset ($IS_ERROR)) && ($IS_ERROR)}danger{else}success{/if}">
                    <strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
                </div>
            </div>
        {/if}
        {*<h1>Selecciona el contenido que deseas recibir periódicamente.</h1>*}
        {if $EBOOK neq NULL}
            <p class="title-description text-center" style="text-align: center!important;">Para descargar el documento&nbsp;
                <strong>"{$EBOOK->getPublicName()}"</strong> por favor introduce tu correo</p>
        {else}
            <p class="title-description text-center" style="text-align: center!important;">Algo salío mal!<samp
                        style="color: red; font-weight: bold"> No se encontro el documento solicitado</samp>&nbsp;para
                &nbsp;descargar mas documentos, introduce tu correo</p>
        {/if}
    {/strip}
{/block}
{block name="box-form"}
    {strip}
        <div class="row" style="">
            {math equation= rand() assign='idDesLink'}
            <div id="onbording-email" class="input-group col-xs-12">
                <input type="email" class="form-control login-form" id="usuarioEmail" name="usuarioEmail"
                       placeholder="Email">
                <div class="login-error">
                    <span id="error_email" class="help-block" style="color: red"></span>
                </div>
            </div>
            <div id="onbording-password" class="input-group col-xs-12 hide">
                <div class="well well-sm">
                    <small id="onbording-info" class="text-justify">Hemos enviado una contraseña temporal al correo:&nbsp;<samp style="color: red;">__EMAIL__</samp>&nbsp;para que puedas entrar a descargar documentos y micro cursos, por favor no cierres esta página, encuentra tu contraseña e introdúcela en el siguiente cuadro para que puedas descargar el documento seleccionado.</small>
                </div>
                <input type="text" class="form-control login-form" id="userPass" name="userPass"
                       value=""
                       placeholder="Clave enviada">
                <div class="login-error">
                    <span id="error_pass" class="help-block" style="color: red"></span>
                </div>
            </div>
            <div id="onbordig-process" class="input-group col-xs-12">&nbsp;</div>
            <div class="input-group col-xs-12">
                <script src='https://www.google.com/recaptcha/api.js'></script>
                <div class="g-recaptcha" data-sitekey="6LeHHKMaAAAAAIQG9wiEruq30LBmpAZsTEnVcoqB"
                     data-callback="recaptchaCallback" data-expired-callback="recaptchaCallbackExpired"></div>
                <div class="login">
                    <p class="social-text" style="text-align:justify" id="error_grecaptcha"></p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <button type="button" class="btn btn-success col-xs-12 nextBtn" id="submitBtn" disabled="disabled"
                        onclick="BulletinBoardUtils.sendPassWord ()">
                    <span>Continuar </span> <span class="fa fa-arrow-right"></span>
                </button>
            </div>
        </div>
        <script type="text/javascript">
            var errorgrecaptcha = jQuery('#error_grecaptcha');
            errorgrecaptcha.html('El botón "Continuar" se habilitará una vez se verifique que Ud. no es un robot');

            function recaptchaCallback() {
                jQuery('#submitBtn').prop('disabled', false);
                errorgrecaptcha.html('Se ha verificado que Ud. no es un robot, el botón "Continuar" está habilitado');
            }

            function recaptchaCallbackExpired() {
                jQuery('#submitBtn').prop('disabled', true);
                errorgrecaptcha.html('La verificación ha caducado. El botón "Continuar" se habilitará una vez se verifique que Ud. no es un robot');
            }
        </script>
        <script type="text/javascript" src="https://player.vimeo.com/api/player.js"></script>
    {/strip}
{/block}
{block name="box-content"}{/block}