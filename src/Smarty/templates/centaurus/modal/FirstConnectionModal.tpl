{strip}
<style type="text/css">
	.modal-backdrop {
		bottom:  0;
		left:    0;
		right:   0;
		top:     0;
		z-index: 1039;
	}
	.missing {
		border-color: #990000 !important;
	}
</style>
<script type="text/html" id="first-connection-modal-template">
{strip}
<div class="modal fade" id="first-connection-modal" tabindex="-1" role="dialog" aria-hidden="false" style="top: 0;">
	<div class="modal-dialog modal-lg">
		<div class="modal-content" style="border-radius: 10px;">
			<div class="modal-header" style="background: #4e8dea url('themes/centaurus/img/i-platzi-welcome.png') 55% 20px no-repeat; border: 0; border-top-left-radius: 10px; border-top-right-radius: 10px; min-height: 200px; padding: 5px 10px;">
				<figure>
					<img src="themes/centaurus/img/logo-platzilla-white.png" class="img-responsive" style="display: inline-block; max-width: 150px;">
				</figure>
			</div>
			<div class="modal-body" style="background-color: #f9f9f9; border: 0; min-height: 281px; padding: 30px;">
				<div id="page-final-configuration" class="page">
					<p class="text-center" style="font-size: 1.75em; font-weight: bold; margin-bottom: 1em;">Terminemos de configurar tu cuenta...</p>
					<div class="row">
						<div class="col-xs-12 col-md-4 col-md-push-4 text-center">
							<input type="text" id="first-name" placeholder="Nombre..." style="border: 1px solid #2ecc71; border-radius: 30px; outline: none; padding: 10px 15px;" />
							<input type="text" id="last-name" placeholder="Apellido..." style="border: 1px solid #2ecc71; border-radius: 30px; margin-top: 1.25em; outline: none; padding: 10px 15px;" />
							<input type="password" id="password" placeholder="Contraseña..." style="border: 1px solid #2ecc71; border-radius: 30px; margin-top: 1.25em; outline: none; padding: 10px 15px;" />
						</div>
					</div>
				</div>
				<div id="page-user-type" class="page" style="display: none;">
					<p class="text-center" style="font-size: 1.75em; font-weight: bold; margin-bottom: 1em;">¿Con quién te identificas más?</p>
					<div class="row">
						<figure class="col-xs-12 col-md-3 text-center">
							<img src="themes/centaurus/img/i-entrepreneur.png" class="img-responsive" style="display: inline-block; margin-bottom: 15px;">
							<button id="link-entrepreneur" type="button" style="background-color: #2ecc71; border: 0; border-radius: 30px; color: #ffffff; font-size: 1.1em; font-weight: bold; outline: 0; padding: 15px; width: 100%;">Emprendedor</button>
						</figure>
						<figure class="col-xs-12 col-md-3 text-center">
							<img src="themes/centaurus/img/i-microbusinessman.png" class="img-responsive" style="display: inline-block; margin-bottom: 15px;">
							<button id="link-microbusinessman" type="button" style="background-color: #2ecc71; border: 0; border-radius: 30px; color: #ffffff; font-size: 1.1em; font-weight: bold; outline: 0; padding: 15px; width: 100%;">Microempresario</button>
						</figure>
						<figure class="col-xs-12 col-md-3 text-center">
							<img src="themes/centaurus/img/i-sme.png" class="img-responsive" style="display: inline-block; margin-bottom: 15px;">
							<button id="link-sme" type="button" style="background-color: #2ecc71; border: 0; border-radius: 30px; color: #ffffff; font-size: 1.1em; font-weight: bold; outline: 0; padding: 5px 15px; width: 100%;">Miembro de una PYME en crecimiento</button>
						</figure>
						<figure class="col-xs-12 col-md-3 text-center">
							<img src="themes/centaurus/img/i-bigcompany.png" class="img-responsive" style="display: inline-block; margin-bottom: 15px;">
							<button id="link-bigcompany" type="button" style="background-color: #2ecc71; border: 0; border-radius: 30px; color: #ffffff; font-size: 1.1em; font-weight: bold; outline: 0; padding: 5px 15px; width: 100%;">Miembro de una empresa grande</button>
						</figure>
					</div>
				</div>
				<div id="page-goodbye" class="page" style="display: none;">
					<p class="text-center" style="font-size: 1.75em; font-weight: bold; margin-bottom: 1em;">¡Gracias por escogernos!</p>
					<div class="row">
						<div class="col-xs-12 col-md-7 col-md-push-1 text-left" style="margin-top: 2em;">
							<p style="font-size: 1.1em;">Todos los módulos tienen datos de prueba. Te recomendamos explorar el sistema con estos datos, y cuando quieras, puedes eliminarlos en la zona de usuario, arriba a la derecha.</p>
							<p style="font-size: 1.1em; margin-top: 1.25em;">También puedes contactarnos a través del chat de soporte, abajo a la derecha. ¡Queremos ayudarte a progresar al máximo!</p>
						</div>
						<figure class="col-xs-12 col-md-3 col-md-push-1 text-center" style="margin-top: 3em;">
							<img src="themes/centaurus/img/i-usermenu.png" class="img-responsive" style="display: inline-block;">
						</figure>
						<figure class="col-xs-12 col-md-3 col-md-push-1 text-center" style="margin-top: 2.25em;">
							<img src="themes/centaurus/img/i-support-chat.png" class="img-responsive" style="display: inline-block;">
						</figure>
					</div>
				</div>
			</div>
			<div class="modal-footer" style="background-color: #f9f9f9; border: 0; border-bottom-left-radius: 10px; border-bottom-right-radius: 10px; min-height: 61px;">
				<div style="color: #4e8dea; float: left; font-size: 1.25em; font-weight: bold;"><span id="page-number">1</span>/3</div>
				<button type="button" id="back" style="background-color: #4e8dea; border: 0; border-radius: 15px; color: #ffffff; display: none; font-size: 1.1em; font-weight: bold; margin-left: 10px; outline: none; padding: 5px 15px; width: 125px;">Atrás</button>
				<button type="button" id="forward" style="background-color: #4e8dea; border: 0; border-radius: 15px; color: #ffffff; display: none; font-size: 1.1em; font-weight: bold; margin-left: 10px; outline: none; padding: 5px 15px; width: 125px;">Siguiente</button>
				<button type="button" id="done" style="background-color: #2ecc71; border: 0; border-radius: 15px; color: #ffffff; display: none; font-size: 1.1em; font-weight: bold; margin-left: 10px; outline: none; padding: 5px 15px; width: 125px;">¡Listo!</button>
			</div>
		</div>
	</div>
</div>
{/strip}
</script>
<script type="text/javascript">
{strip}
(function (jQuery) {
	var selectedProfile = null,
		modal = null;

	var destroyModal = function () {
		jQuery (this).remove ();
		modal = null;
		selectedProfile = null;
	};

	var openFinalConfigurationPage = function () {
		var footer = modal.find ('.modal-footer');
		footer.find ('#back').off ('click').hide ();
		footer.find ('#forward').off ('click').on ('click', validateFinalConfiguration).show ();
		footer.find ('#done').off ('click').hide ();
		modal.find ('.modal-footer #page-number').text ('1');
		modal.find ('.page').hide ();
		modal.find ('#page-final-configuration').show ();
	};

	var openGoodByePage = function () {
		var footer = modal.find ('.modal-footer');

		footer.find ('#page-number').text ('3');
		footer.find ('#back').off ('click').on ('click', openUserTypePage).show ();
		footer.find ('#forward').off ('click').hide ();
		footer.find ('#done').off ('click').on ('click', submitData).show ();
		modal.find ('.page').hide ();
		modal.find ('#page-goodbye').show ();
	};

	var openUserTypePage = function () {
		var footer = modal.find ('.modal-footer');

		selectedProfile = null;
		footer.find ('#back').off ('click').on ('click', openFinalConfigurationPage).show ();
		footer.find ('#forward').off ('click').hide ();
		footer.find ('#done').off ('click').hide ();
		modal.find ('.modal-footer #page-number').text ('2');
		modal.find ('.page').hide ();
		modal.find ('#page-user-type').show ();
	};

	var setBigCompanyProfile = function () {
		selectedProfile = 'Miembro de una empresa grande';
		openGoodByePage ();
	};

	var setEntrepreneurProfile = function () {
		selectedProfile = 'Emprendedor';
		openGoodByePage ();
	};

	var setMicroBusinessmanProfile = function () {
		selectedProfile = 'Microempresario';
		openGoodByePage ();
	};

	var setSMEProfile = function () {
		selectedProfile = 'Miembro de una PYME en crecimiento';
		openGoodByePage ();
	};

	var validateFinalConfiguration = function () {
		var field, value;

		field = modal.find ('#first-name');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			field.addClass ('missing');
			field.focus ();
			return;
		} else {
			field.removeClass ('missing');
		}
		field = modal.find ('#last-name');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			field.addClass ('missing');
			field.focus ();
			return;
		} else {
			field.removeClass ('missing');
		}
		field = modal.find ('#password');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			field.addClass ('missing');
			field.focus ();
			return;
		} else {
			field.removeClass ('missing');
		}

		openUserTypePage ();
	};

	var openModal = function () {
		var modalTemplate = jQuery ('#first-connection-modal-template');

		modal = jQuery (modalTemplate.html ());
		modal.find ('#link-entrepreneur').click (setEntrepreneurProfile);
		modal.find ('#link-microbusinessman').click (setMicroBusinessmanProfile);
		modal.find ('#link-sme').click (setSMEProfile);
		modal.find ('#link-bigcompany').click (setBigCompanyProfile);
		openFinalConfigurationPage ();
		modal.modal ({ backdrop: 'static', keyboard: false }).on ('hidden.bs.modal', destroyModal);
	};

	var submitData = function () {
		var arguments = [
			'module=store',
			'action=AddContactData',
			'profile=' + encodeURIComponent (selectedProfile),
			'phonenumber=' + encodeURIComponent (modal.find ('#phone-number').val ()),
			'firstname=' + encodeURIComponent (modal.find ('#first-name').val ()),
			'lastname=' + encodeURIComponent (modal.find ('#last-name').val ()),
			'password=' + encodeURIComponent (modal.find ('#password').val ()),
			'Ajax=true'
		];
		jQuery.ajax ('index.php', {
			data: arguments.join ('&'),
			dataType: 'json',
			method: 'post'
		}).done (function () {
			modal.modal ('hide');
			window.location.reload ();
		}).fail (function () {
			modal.modal ('hide');
			window.location.reload ();
		});
	};

	jQuery (document).ready (openModal);
} (jQuery));
{/strip}
</script>
{/strip}