<?php
	$headerImage = "themes/centaurus/img/i-platzi-welcome.png";
	echo '<div class="modal fade" id="first-connection-modal" tabindex="-1" role="dialog" aria-hidden="false" style="top: 0;">
			<div class="modal-dialog modal-lg">
				<div class="modal-content" style="border-radius: 10px;">
				<div class="modal-header" style="background: #4E8DEA url(' . $headerImage . ') 55% 20px no-repeat; border: 0; border-top-left-radius: 10px; border-top-right-radius: 10px; min-height: 200px; padding: 5px 10px;">
					<figure><img src="themes/centaurus/img/logo-platzilla-white.png" class="img-responsive" style="display: inline-block; max-width: 150px;"></figure>
				</div>
				<div class="modal-body" style="background-color: #F9F9F9; border: 0; min-height: 281px; padding: 30px;">
					<div id="page-final-configuration" class="page"><p class="text-center" style="font-size: 1.25em; font-weight: bold; margin-bottom: 1em;">Necesitamos unos datos para darte acceso a experimentar cómo gestionar tu empresa</p>
						<div class="row">
							<div class="col-xs-12 col-md-4 col-md-push-4 text-center">
								<input type="text" id="email" placeholder="Email..." style="border: 1px solid #2ECC71; border-radius: 30px; outline: none; padding: 7px 15px;" />
								<input type="text" id="first-name" placeholder="Nombre..." style="border: 1px solid #2ECC71; border-radius: 30px; margin-top: 0.5em; outline: none; padding: 7px 15px;" />
								<input type="text" id="last-name" placeholder="Apellido..." style="border: 1px solid #2ECC71; border-radius: 30px; margin-top: 0.5em; outline: none; padding: 7px 15px;" />
								<input type="password" id="password" placeholder="Contraseña..." style="border: 1px solid #2ECC71; border-radius: 30px; margin-top: 0.5em; outline: none; padding: 7px 15px;" />
							</div>
						</div>
					</div>
					<div id="page-user-type" class="page" style="display: none;"><p class="text-center" style="font-size: 1.75em; font-weight: bold; margin-bottom: 1em;">¿Con quién te identificas más?</p>
						<div class="row">
							<figure class="col-xs-12 col-md-3 text-center"><img src="themes/centaurus/img/i-entrepreneur.png" class="img-responsive" style="display: inline-block; margin-bottom: 15px;">
								<button id="link-entrepreneur" type="button" style="background-color: #2ECC71; border: 0; border-radius: 30px; color: #FFFFFF; font-size: 1.1em; font-weight: bold; outline: 0; padding: 15px; width: 100%;">Emprendedor</button>
							</figure>
							<figure class="col-xs-12 col-md-3 text-center"><img src="themes/centaurus/img/i-microbusinessman.png" class="img-responsive" style="display: inline-block; margin-bottom: 15px;">
								<button id="link-microbusinessman" type="button" style="background-color: #2ECC71; border: 0; border-radius: 30px; color: #FFFFFF; font-size: 1.1em; font-weight: bold; outline: 0; padding: 15px; width: 100%;">Microempresario</button>
							</figure>
							<figure class="col-xs-12 col-md-3 text-center"><img src="themes/centaurus/img/i-sme.png" class="img-responsive" style="display: inline-block; margin-bottom: 15px;">
								<button id="link-sme" type="button" style="background-color: #2ECC71; border: 0; border-radius: 30px; color: #FFFFFF; font-size: 1.1em; font-weight: bold; outline: 0; padding: 5px 15px; width: 100%;">Miembro de una PYME en crecimiento</button>
							</figure>
							<figure class="col-xs-12 col-md-3 text-center"><img src="themes/centaurus/img/i-bigcompany.png" class="img-responsive" style="display: inline-block; margin-bottom: 15px;">
								<button id="link-bigcompany" type="button" style="background-color: #2ECC71; border: 0; border-radius: 30px; color: #FFFFFF; font-size: 1.1em; font-weight: bold; outline: 0; padding: 5px 15px; width: 100%;">Miembro de una empresa grande</button>
							</figure>
						</div>
				</div>
				<div id="page-goodbye" class="page" style="display: none;"><p class="text-center" style="font-size: 1.75em; font-weight: bold; margin-bottom: 1em;">¡Gracias por escogernos!</p>
					<div class="row">
					<div class="col-xs-12 col-md-7 col-md-push-1 text-left" style="margin-top: 2em;"><p style="font-size: 1.1em;">Todos los módulos tienen datos de prueba. Te recomendamos explorar el sistema con estos datos, y cuando quieras, puedes eliminarlos en la zona de usuario, arriba a la derecha.</p>
						<p style="font-size: 1.1em; margin-top: 1.25em;">También puedes contactarnos a través del chat de soporte, abajo a la derecha. ¡Queremos ayudarte a progresar al máximo!</p></div>
						<figure class="col-xs-12 col-md-3 col-md-push-1 text-center" style="margin-top: 3em;"><img src="themes/centaurus/img/i-usermenu.png" class="img-responsive" style="display: inline-block;"></figure>
						<figure class="col-xs-12 col-md-3 col-md-push-1 text-center" style="margin-top: 2.25em;"><img src="themes/centaurus/img/i-support-chat.png" class="img-responsive" style="display: inline-block;"></figure>
					</div>
				</div>
				</div>
					<div class="modal-footer" style="background-color: #F9F9F9; border: 0; border-bottom-left-radius: 10px; border-bottom-right-radius: 10px; min-height: 61px;">
						<div style="color: #4E8DEA; float: left; font-size: 1.25em; font-weight: bold;display: none"><span id="page-number">1</span>/2</div>
						<button type="button" id="back" style="background-color: #4E8DEA; border: 0; border-radius: 15px; color: #FFFFFF; display: none; font-size: 1.1em; font-weight: bold; margin-left: 10px; outline: none; padding: 5px 15px; width: 125px;">Atrás</button>
						<button type="button" id="forward" style="background-color: #4E8DEA; border: 0; border-radius: 15px; color: #FFFFFF; display: none; font-size: 1.1em; font-weight: bold; margin-left: 10px; outline: none; padding: 5px 15px; width: 125px;">Siguiente</button>
						<button type="button" id="done" style="background-color: #2ECC71; border: 0; border-radius: 15px; color: #FFFFFF; display: none; font-size: 1.1em; font-weight: bold; margin-left: 10px; outline: none; padding: 5px 15px; width: 125px;">Listo</button>
					</div>
				</div>
			</div>
	</div>';