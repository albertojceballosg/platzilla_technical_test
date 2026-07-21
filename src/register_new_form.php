<?php
	echo '<div class="modal fade" id="first-connection-modal" tabindex="-1" role="dialog" aria-hidden="false" style="top: 0;">
			<div class="modal-dialog modal-lg">
				<div class="modal-content" style="border-radius: 10px;">
				<div class="modal-header" style= "background-color: #F9F9F9; border: 0; border-top-left-radius: 10px; border-top-right-radius: 10px; padding: 5px 10px;">
				&nbsp;</div>
				<div class="modal-body" style="background-color: #F9F9F9; border: 0; min-height: 281px; padding: 30px;">
					<div id="page-final-configuration" class="page"><p class="text-center" style="font-size: 1.25em; font-weight: bold; margin-bottom: 1em;">¿Quieres apuntarte al nuevo grupo de empresas tutorizadas con Platzilla y Railes?<br>¡Déjanos tus datos!</p>
						<div class="row" style="margin-top: 50px">
							<div class="col-xs-6 col-md-6 text-center">
							<form id="reg_form" class="form-horizontal" role="form">
							<input type="hidden" name="module" value="store">
							<input type="hidden" name="action"   value="CreateInstance">
							<input type="hidden" name="function" value="PROSPECTUS_DATA">
							<input type="hidden" name="Ajax" value="true">
							  <div class="form-group">
							    <label for="ejemplo_email_3" class="col-lg-2 control-label">Nombre</label>
							    <div class="col-lg-9">
							      <input type="text" class="form-control" id="firstname" name="firstname"
							             placeholder="Nombre">
							    </div>
							  </div>
							  <div class="form-group">
							    <label for="ejemplo_password_3" class="col-lg-2 control-label">Apellido</label>
							    <div class="col-lg-9">
							      <input type="text" class="form-control" id="reg_last_name" name="lastname"
							             placeholder="Apellido">
							    </div>
							  </div>
							  <div class="form-group">
							  <label for="ejemplo_password_3" class="col-lg-2 control-label">Email</label>
							  <div class="col-lg-9">
							  <input type="email" class="form-control" id="email" name="email"
							         placeholder="Email">
							  </div>
							  </div>
							  <div class="form-group">
							  <label for="ejemplo_password_3" class="col-lg-2 control-label">Teléfono</label>
							  <div class="col-lg-9">
							  <input type="text" class="form-control" id="phone" name="phone" placeholder="(099)-999-999-9999">
							  </div>
							  </div>
							  <div class="form-group">
							    <div class="col-lg-offset-2 col-lg-9">
							      <button type="button"
							      onclick="RegisterUtils.getCostumer(this)"
							      class="btn btn-primary">Enviar</button>
							    </div>
							  </div>
							  <span id="reg_error" style="color: red;"></span>
							</form>
							
							</div>
							<div class="col-xs-6 col-md-6 text-center">
								<div style="text-align: center;">
								    <iframe id="video" width="420" height="260" class="youtube-video" src="https://www.youtube.com/embed/kK0zvZ5gPSI?si=tZa4C-pAOY1xdqMn" frameborder="0"
								         allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
								          allowfullscreen="allowfullscreen">
								     </iframe>
								</div>
							</div>
						</div>
					</div>
					<div id="page-user-type" class="page" style="display: none;">
					
				</div>
				<div id="page-goodbye" class="page" style="display: none;">
				</div>
				</div>
					<div class="modal-footer" style="background-color: #F9F9F9; border: 0; border-bottom-left-radius: 10px; border-bottom-right-radius: 10px; min-height: 61px;">
					</div>
				</div>
			</div>
	</div>';