{strip}
	<style>
		#instances-data-sharing-share-modal {
			z-index: 10000 !important;
		}
		#related-module-records {
			top:     0;
			z-index: 10010 !important;
		}
	</style>
<div id="instances-data-sharing-share-modal" class="modal fade instance-data-sharing-element" role="dialog">
	<div class="modal-dialog modal-lg">
		<div class="modal-content data-sharing-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">Compartir registros</h4>
			</div>
			<div class="modal-body data-sharing-section">
				<input type="hidden" id="module-name" />
				<div class="row">
					<div class="form-group col-xs-12">
						<label for="rule-id">¿Qué quieres compartir? <span class="required">*</span></label>
						<select id="rule-id" class="form-control">
							<option value=""></option>
							<option value="FULL">Los registros seleccionados y sus registros relacionados</option>
							<option value="MINIMAL">Sólo los registros seleccionados, sin los registros relacionados</option>
							<optgroup id="custom-rules" label="Compartir usando una regla personalizada"></optgroup>
						</select>
					</div>
				</div>
				<div class="row recipients">
					<label for="recipient-type" class="col-xs-12">¿Con quién lo quieres compartir? <span class="required">*</span></label>
					<div class="form-group col-xs-12 col-md-5">
						<select id="recipient-type" class="form-control" onchange="DataSharingUtils.setRecipientType (this);">
							<option value=""></option>
							<option value="LITERAL">Indica las direcciones de correo separadas por comas</option>
							<option value="CUSTOMER" style="display: none" disabled="disabled">Un cliente registrado</option>
							<option value="CONTACT" style="display: none" disabled="disabled">Un contacto registrado</option>
						</select>
					</div>
					<div class="form-group col-xs-12 col-md-7">
						<input type="text" id="recipient-value-literal" class="form-control" placeholder="" style="display: none;" disabled="disabled" />
						<div id="recipient-value-customer" style="display: none;" disabled="disabled">
							<div class="input-group field-container" style="width: 100%;">
								<input id="customer-id" class="for-filter form-control" type="hidden" />
								<input id="customer-display" class="form-control input-readonly b-right" readonly="readonly" placeholder="" type="text" />
								<div class="input-group-addon" data-display-field-id="customer-display" data-field-id="customer-id" data-referenced-module="clientes" data-title="Clientes" onclick="RelatedModuleModalUtils.openModal (this);"><i class="fa fa-plus-circle"></i></div>
								<div class="input-group-addon" onclick="DataSharingUtils.clearSelection (this);"><i class="fa fa-eraser"></i></div>
							</div>
						</div>
						<div id="recipient-value-contact" style="display: none;" disabled="disabled">
							<div class="input-group field-container" style="width: 100%;">
								<input id="contact-id" class="for-filter form-control" type="hidden" />
								<input id="contact-display" class="form-control input-readonly b-right" readonly="readonly" placeholder="" type="text" />
								<div class="input-group-addon" data-display-field-id="contact-display" data-field-id="contact-id" data-referenced-module="contactos" data-title="Contactos" onclick="RelatedModuleModalUtils.openModal (this);"><i class="fa fa-plus-circle"></i></div>
								<div class="input-group-addon" onclick="DataSharingUtils.clearSelection (this);"><i class="fa fa-eraser"></i></div>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="form-group col-xs-12">
						<label for="comments">Escribe algún comentario (opcional)</label>
						<textarea id="comments" name="comments" class="form-control" maxlength="255"></textarea>
					</div>
				</div>
				<div class="panel panel-info">
					<div class="panel-heading">Tip</div>
					<div class="panel-body">
						<p>Compartir registros es una manera de adquirir una eficacia en la gestión muy elevada. Si tus clientes, proveedores u otros colaboradores comparten la misma información que tú tienes, no necesitarás duplicar tareas, enviando o actualizando esa información. Además podrás aprovechar (siempre que lo hayas definido en las reglas de compartir) que dichas personas que comparten el mismo registro puedan editarlo y así pueda estar siempre actualizado.</p>
						<p>Para más información haz click <a href="https://youtu.be/J_iqrxtzmVU" target="_blank">aquí para ver un video explicativo</a></p>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" onclick="DataSharingUtils.sendRequest (this)">Compartir</button>
			</div>
		</div>
	</div>
</div>
{/strip}