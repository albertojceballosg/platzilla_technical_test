{extends file="boilerplate.tpl"}

{block name="head_meta"}
    <!-- head_meta -->
    {* Preload critical resources *}
    {include file="includes/Preload.tpl"}

    {* Regular meta tags *}
    <!-- head_meta -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
{/block}

{block name="css"}
    {* Ejemplo de inclusión de CSS minificados desde la carpeta compiled *}
    <link rel="stylesheet" href="themes/centaurus/css/compiled/daterangepicker.min.css">
    <link rel="stylesheet" href="themes/centaurus/css/compiled/morris.min.css">
    <link rel="stylesheet" href="themes/centaurus/css/compiled/summernote.min.css">
    <link rel="stylesheet" href="themes/centaurus/css/compiled/nprogress.min.css">
    <link rel="stylesheet"  href="themes/centaurus/css/libs/ekko-lightbox.min.css"  />
    {* Agrega aquí otros CSS minificados según necesidad de la vista *}
{/block}

{block name="action_css"}{/block}

{block name="body"}
    {if $IS_MODAL}
        {block name="action_content"}{/block}
    {else}
    {include file="Header.navbar.inc.tpl"}

    {if $MODULE_NAME eq 'Users' && $ACTION_NAME neq 'Logout'}
        {assign var="NAV_SMALL" value=" nav-small"}
    {/if}

    {if !$HIDE_MENU}
<div id="page-wrapper" class="container{$NAV_SMALL}">
    <div>
        <div class="row fix-h">
            <div id="nav-col">
                {* include menu navigation *}
                {include file="Header.menu.inc.tpl"}
            </div>
            <div id="content-wrapper">
                {else}
                <div id="page-wrapper" style="">
                    <div class="row fix-h">
                        <div id="content-wrapper" style="margin-left:0px;">

                            {/if}
                            <div class="row" {*style="margin-bottom:4em*}>
                           <div class="col-lg-12">
                               {*Contenido específico de la pantalla en cuestión.*}
                               <!-- Aquí se coloca el contenido específico de la vista hija -->
                            {block name="action_content"}{/block}
    {/if}
{/block}

{block name="js"}
    {* Ejemplo de inclusión de JS minificados desde la carpeta compiled *}
    <script type="text/javascript" src="include/scriptaculous/prototype.compatible.js"></script>
    <script src="themes/centaurus/js/compiled/daterangepicker.min.js"></script>
    <script src="themes/centaurus/js/compiled/morris.min.js"></script>
    <script src="themes/centaurus/js/compiled/summernote.min.js"></script>
    <!-- <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script> -->
    <script src="/include/js/general.js"></script>
    <script src="include/js/daily_report_navbar.js"></script>
    <script src="themes/centaurus/js/compiled/nprogress.min.js"></script>
    {* NProgress: Barra de progreso superior para carga de página y AJAX
        - Se inicializa solo cuando el DOM está listo (DOMContentLoaded), evitando errores si <body> aún no existe.
        - NProgress.start() se activa al inicio de la carga; NProgress.done() al finalizar (evento load).
        - Si jQuery está presente, la barra también se muestra durante peticiones AJAX.
        - Personaliza color y altura editando el CSS en nprogress.min.css.
        - Documentación oficial: https://ricostacruz.com/nprogress/
     *}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            NProgress.start();
            window.addEventListener('load', function () {
                NProgress.done();
            });
            if (window.jQuery) {
                jQuery(document).ajaxStart(function () {
                    NProgress.start();
                });
                jQuery(document).ajaxComplete(function () {
                    NProgress.done();
                });
            }
        });
        jQuery(document).delegate('*[data-toggle="lightbox"]:not([data-gallery="navigateTo"])', 'click', function(event) {
            var modalTitle;
            event.preventDefault();
            return jQuery(this).ekkoLightbox({
                footer: "",
                onShown: function() {
                    var dataType,
                        ekkoModal,
                        modalBackdrop,
                        modalId   = this.modal_id;
                    dataType   = jQuery(event.target).attr('data-type');
                    ekkoModal  = jQuery('#' + modalId);
                    modalTitle = ekkoModal.find ('.modal-title').html ();
                    if (dataType == 'vimeo') {
                        modalBackdrop = jQuery ('.modal-backdrop');
                        ekkoModal.find ('.modal-header').css ('display', 'none');
                        ekkoModal.find ('button').eq(0).css ('margin-right', '50px')
                            .css ('float', 'right')
                            .css ('top',0)
                            .css ('font-weight','bold')
                            .css ('font-size','xx-large');
                        ekkoModal.find ('button').eq(0).insertBefore (ekkoModal.find ('.modal-dialog'));
                        ekkoModal.find ('.modal-body').css ('padding', '0px').css ('margin', '0px');
                        modalBackdrop.css ('background-color', '#FFFF');
                        modalBackdrop.css ('opacity', 0.9);
                        modalBackdrop.css ('bottom', 0);
                        modalBackdrop.css ('z-index', 1001);
                    }
                },
                onNavigate: function(direction, itemIndex) {
                },
                onHidden:function() {
                    var modalBackdrop = jQuery ('.modal-backdrop'),
                        url           = window.location.href,
                        module        = getUrlValue ('module'),
                        dummy         = url.split ('&');
                    modalBackdrop.removeClass ('bottom');
                    modalBackdrop.removeClass ('z-index');
                    if (modalTitle === 'Mensajes:') {
                        if (module === 'diagnostic_report') {
                            if (dummy[4] !== 'tab=destination') {
                                window.location.href += '&tab=destination';
                            } else {
                                window.location.reload ();
                            }
                        } else {
                            window.location.reload ();
                        }
                    }
                }
            });
        });
    </script>
{/block}

{block name="action_js"}{/block}