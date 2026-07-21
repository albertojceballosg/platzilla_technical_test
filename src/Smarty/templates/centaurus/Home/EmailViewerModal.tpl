{strip}
<div id="email-viewer-modal" class="modal fade" role="dialog">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">Mensaje de correo</h4>
			</div>
			<div id="email-viewer">
				<div class="modal-body">
					<div class="row">
						<label for="from" class="col-xs-12 col-md-3 col-lg-2">Remitente</label>
						<div class="col-xs-12 col-md-7 col-lg-10">
							<input type="text" id="from" class="form-control" readonly="readonly" />
						</div>
					</div>
					<div class="row">
						<label for="to" class="col-xs-12 col-md-3 col-lg-2">Destinatario</label>
						<div class="col-xs-12 col-md-7 col-lg-10">
							<input type="text" id="to" class="form-control" readonly="readonly" />
						</div>
					</div>
					<div class="row">
						<label for="cc" class="col-xs-12 col-md-3 col-lg-2">Copia a</label>
						<div class="col-xs-12 col-md-7 col-lg-10">
							<input type="text" id="cc" class="form-control" readonly="readonly" />
						</div>
					</div>
					<div class="row">
						<label for="bcc" class="col-xs-12 col-md-3 col-lg-2">Copia oculta a</label>
						<div class="col-xs-12 col-md-7 col-lg-10">
							<input type="text" id="bcc" class="form-control" readonly="readonly" />
						</div>
					</div>
					<div class="row">
						<label for="subject" class="col-xs-12 col-md-3 col-lg-2">Asunto</label>
						<div class="col-xs-12 col-md-7 col-lg-10">
							<input type="text" id="subject" class="form-control" readonly="readonly" />
						</div>
					</div>
					<div class="row">
						<label for="attachments" class="col-xs-12 col-md-3 col-lg-2">Anexos</label>
						<div class="col-xs-12 col-md-7 col-lg-10">
							<ul id="attachments" class="form-control"></ul>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-12">
							<iframe id="email-body"></iframe>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-info" onclick="WebmailUtils.composeEmail ();">Responder</button>
				</div>
			</div>
			<div id="email-composer" class="email-composer-stuff">
				<form action="index.php" method="post" onsubmit="WebmailUtils.sendEmailMessage (this); return false;">
					<input type="hidden" name="module" value="webmail" />
					<input type="hidden" name="action" value="SendEmailMessage" />
					<input type="hidden" name="Ajax" value="true" />
					<div class="modal-body">
						<div class="row">
							<label for="from" class="col-xs-12 col-md-3 col-lg-2">Remitente</label>
							<div class="col-xs-12 col-md-7 col-lg-10">
								<input type="text" id="from" name="from" class="form-control" readonly="readonly" />
							</div>
						</div>
						<div class="row">
							<label for="to" class="col-xs-12 col-md-3 col-lg-2">Destinatario</label>
							<div class="col-xs-12 col-md-7 col-lg-10">
								<input type="text" id="to" name="to" class="form-control" />
							</div>
						</div>
						<div class="row">
							<label for="cc" class="col-xs-12 col-md-3 col-lg-2">Copia a</label>
							<div class="col-xs-12 col-md-7 col-lg-10">
								<input type="text" id="cc" name="cc" class="form-control" />
							</div>
						</div>
						<div class="row">
							<label for="bcc" class="col-xs-12 col-md-3 col-lg-2">Copia oculta a</label>
							<div class="col-xs-12 col-md-7 col-lg-10">
								<input type="text" id="bcc" name="bcc" class="form-control" />
							</div>
						</div>
						<div class="row">
							<label for="subject" class="col-xs-12 col-md-3 col-lg-2">Asunto</label>
							<div class="col-xs-12 col-md-7 col-lg-10">
								<input type="text" id="subject" name="subject" class="form-control" />
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12">
								<div class="input-group" style="width: 100%;">
									<textarea id="body" name="body" class="form-control body ckeditor" placeholder=""></textarea>
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="submit" class="btn btn-success">Enviar</button>
						<button type="button" class="btn btn-default" onclick="WebmailUtils.viewEmail ();">Cancelar</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
{/strip}