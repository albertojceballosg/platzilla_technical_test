{extends file="base/TopNavigation.tpl"}
{block name="css"}
	<link rel="stylesheet" type="text/css" href="themes/centaurus/css/compiled/box-frame.css" />
	<link rel="stylesheet" href="themes/centaurus/css/compiled/store.css">
	<link rel="stylesheet" href="themes/centaurus/css/compiled/section/calculator.css">
	<link rel="stylesheet" href="themes/centaurus/css/libs/animate.css">
	<style type="text/css">
		.setup-content.active {
			display: block !important;
		}
		.setup-content {
			display: none !important;
		}
	</style>
{/block}
{block name="body-content"}
	<div class="row">
		<form role="form" action="index.php?module=store&action=createInstanceFromInvitation" method="post" name="signup-form">
			<input type="hidden" name="invitation" value="{$INVITATION}" />
			<input type="hidden" name="instance" value="{$INSTANCE}" />
			<div class="row setup-content active" id="step-1">
				<div class="col-xs-12">
{include file="modules/invitations/SignUpStep-1.tpl"}
				</div>
			</div>
			<div class="row setup-content" id="step-2">
				<div class="col-md-12">
{include file="modules/invitations/SignUpStep-2.tpl"}
				</div>
			</div>
		</form>
	</div>
{/block}
{block name="scripts"}
{literal}
	<script type="text/javascript">
		function wizardInit () {
			var allNextBtn   = jQuery ('.nextBtn');

			allNextBtn.click (function (evt) {
				var thiz = jQuery (this),
					currentStep = thiz.attr ('data-current-step'),
					nextStep = thiz.attr ('data-next-step'),
					nextSection;
				if ((!currentStep) || (!validateForm (currentStep))) {
					evt.preventDefault ();
					return;
				}
				if (!nextStep) {
					return;
				}
				nextSection = jQuery ('#' + nextStep);
				thiz.closest ('.setup-content').removeClass ('active');
				nextSection.addClass ('active animated fadeInLeft');
			});
		}

		function validateForm (step) {
			jQuery ("#error_name").html ("");
			jQuery ("#error_lastname").html ("");
			jQuery ("#error_email").html ("");
			jQuery ("#error_password").html ("");
			jQuery ("#error_password_confirm").html ("");

			if (step == 'step-1') {
				if (!(jQuery ("#name").val ())) {
					jQuery ("#name").trigger ('focus');
					jQuery ("#error_name").html ("Especifique su Nombre");
					return false;
				}
				if (!(jQuery ("#lastname").val ())) {
					jQuery ("#lastname").trigger ('focus');
					jQuery ("#error_lastname").html ("Especifique su Apellido");
					return false;
				}
			} else if (step == 'step-2') {
				if (!(jQuery ("#usuarioEmail").val ())) {
					jQuery ("#usuarioEmail").trigger ('focus');
					jQuery ("#error_email").html ("Especifique su Email");
					return false;
				} else {
					var regex = /^(([^<>()[\]\.,;:\s@\"]+(\.[^<>()[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i;
					if (!regex.test (jQuery ("#usuarioEmail").val ())) {
						jQuery ("#usuarioEmail").trigger ('focus');
						jQuery ("#error_email").html ("Ingrese un Email válido");
						return false;
					}
				}
				if (!(jQuery ("#clave").val ())) {
					jQuery ("#clave").trigger ('focus');
					jQuery ("#error_password").html ("Especifique su Clave");
					return false;
				}
				if (!(jQuery ("#claveConfirm").val ())) {
					jQuery ("#claveConfirm").trigger ('focus');
					jQuery ("#error_password_confirm").html ("Confirme su Clave");
					return false;
				}
				if (jQuery ("#clave").val () !== jQuery ("#claveConfirm").val ()) {
					jQuery ("claveConfirm").trigger ('focus');
					jQuery ("#error_password_confirm").html ("Las claves no coinciden");
					return false;
				}
			}
			return true;
		}

		jQuery (document).ready (function () {
			//Inicialización del formulario en tres pasos
			wizardInit ();
			jQuery ('form[name="signup-form"]').on ('submit', function () { return validateForm ('step-2'); });
		});
	</script>
{/literal}

{/block}

