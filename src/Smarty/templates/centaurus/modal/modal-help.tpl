{strip}
<script type="text/html" id="help-modal-template">
{strip}
<div id="help-modal" class="modal fade" role="dialog">
	<div class="modal-dialog modal-help">
		<div class="modal-content">
			<div class="modal-header">
				<ul class="nav nav-tabs">
					<li class="tab-tips-control">
						<a href="#tab-tips" data-toggle="tab" onclick="jQuery ('#send_help_request').hide ();">Platzilla Tips</a>
					</li>
					<li class="tab-walkthroughs-control">
						<a href="#tab-walkthroughs" data-toggle="tab" onclick="jQuery ('#send_help_request').hide ();">Tutoriales</a>
					</li>
					<li class="tab-usecases-control">
						<a href="#tab-usecases" data-toggle="tab" onclick="jQuery ('#send_help_request').hide ();">Casos de uso</a>
					</li>
					<li class="tab-faq-control">
						<a href="#tab-faq" data-toggle="tab" onclick="jQuery ('#send_help_request').hide ();">Preguntas frecuentes</a>
					</li>
					<li>
						<a href="#tab-support" data-toggle="tab" onclick="jQuery ('#send_help_request').show ();">Contacto</a>
					</li>
				</ul>
			</div>
			<div class="modal-body">
				<div class="tabs-wrapper">
					<div class="col-xs-12 tab-content">
						<div id="tab-tips" class="tab-pane fade in tab-tips-control">
							<p class="description"></p>
							<form onsubmit="HelpUtils.filterByKeyword (this); return false;">
								<div class="input-group filter-group">
									<input type="search" class="form-control keyword" placeholder="Filtrar..." />
									<div class="input-group-btn">
										<button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
										<button type="submit" class="btn btn-default" onclick="HelpUtils.clearFilter (this);"><i class="fa fa-eraser"></i></button>
									</div>
								</div>
							</form>
							<div id="panel-tips" class="panel-group accordion help-items"></div>
						</div>
						<div id="tab-walkthroughs" class="tab-pane fade in tab-walkthroughs-control">
							<p class="description"></p>
							<form onsubmit="HelpUtils.filterByKeywordAndApplicationCode (this); return false;">
								<div class="row">
									<div class="col-xs-12 col-md-8">
										<div class="input-group filter-group">
											<input type="search" class="form-control keyword" placeholder="Filtrar..." />
											<div class="input-group-btn">
												<button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
												<button type="submit" class="btn btn-default" onclick="HelpUtils.clearFilter (this);"><i class="fa fa-eraser"></i></button>
											</div>
										</div>
									</div>
									<div class="col-xs-12 col-md-4">
										<div class="input-group filter-group" style="width: 100%;">
											<select class="form-control application-code" title="Aplicaciones" onchange="HelpUtils.filterByKeywordAndApplicationCode (this.form);">
												<option value="">Todas las aplicaciones</option>
{if (!empty ($HELP_AVAILABLE_APPLICATIONS))}
	{foreach $HELP_AVAILABLE_APPLICATIONS as $applicationCode => $applicationData}
												<option value="{$applicationCode}">{$applicationData.app_name}</option>
	{/foreach}
{/if}
											</select>
										</div>
									</div>
								</div>
							</form>
							<div class="row">
								<div class="col-xs-12 col-md-8 videos-container">
									<h1 class="section-title h4 clearfix">
										<i class="line"></i><i class="fa fa-youtube-play fa-fw text-primary"></i> Videos
									</h1>
									<div id="panel-videos" class="help-items"></div>
								</div>
								<div class="col-xs-12 col-md-4 articles-container">
									<h1 class="section-title h4 clearfix">
										<i class="line"></i><i class="fa fa-file-text-o fa-fw text-primary"></i> Artículos
									</h1>
									<div id="panel-articles" class="help-items"></div>
								</div>
							</div>
						</div>
						<div id="tab-usecases" class="tab-pane fade in tab-usecases-control">
							<p class="description"></p>
							<form onsubmit="HelpUtils.filterByKeywordAndApplicationCode (this); return false;">
								<div class="row">
									<div class="col-xs-12 col-md-8">
										<div class="input-group filter-group">
											<input type="search" class="form-control keyword" placeholder="Filtrar..." />
											<div class="input-group-btn">
												<button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
												<button type="submit" class="btn btn-default" onclick="HelpUtils.clearFilter (this);"><i class="fa fa-eraser"></i></button>
											</div>
										</div>
									</div>
									<div class="col-xs-12 col-md-4">
										<div class="input-group filter-group" style="width: 100%;">
											<select class="form-control application-code" title="Aplicaciones" onchange="HelpUtils.filterByKeywordAndApplicationCode (this.form);">
												<option value="">Todas las aplicaciones</option>
{if (!empty ($HELP_AVAILABLE_APPLICATIONS))}
	{foreach $HELP_AVAILABLE_APPLICATIONS as $applicationCode => $applicationData}
												<option value="{$applicationCode}">{$applicationData.app_name}</option>
	{/foreach}
{/if}
											</select>
										</div>
									</div>
								</div>
							</form>
							<div class="row">
								<div id="panel-usecases" class="help-items"></div>
							</div>
						</div>
						<div id="tab-faq" class="tab-pane fade in tab-faq-control">
							<p class="description"></p>
							<form onsubmit="HelpUtils.filterByKeyword (this); return false;">
								<div class="input-group filter-group">
									<input type="search" class="form-control keyword" placeholder="Filtrar..." />
									<div class="input-group-btn">
										<button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
										<button type="submit" class="btn btn-default" onclick="HelpUtils.clearFilter (this);"><i class="fa fa-eraser"></i></button>
									</div>
								</div>
							</form>
							<div id="panel-faq" class="panel-group accordion help-items"></div>
						</div>
						<div id="tab-support" class="tab-pane fade in">
							<div id="ticket-form">
								<div class="form-title">
									<h1>Solicitar Ticket</h1>
									<hr class="linea">
								</div>
								<div class="help-form">
									<div class="form-group">
										<label for="categoria">Categoría</label>
										<select class="form-control" id="categoria" name="categoria">
											<option value="Dudas de facturación en mi cuenta">Dudas de facturación en mi cuenta</option>
											<option value="Dudas de uso del sistema">Dudas de uso del sistema</option>
											<option value="Problemas encontrados">Reportar problemas encontrados</option>
											<option value="Sugerencias">Sugerencias</option>
										</select>
									</div>
									<div class="form-group">
										<label for="help-request-description" style="font-size: 1.1em;">Mensaje</label>
										<textarea class="form-control" id="help-request-description" name="descripcion"></textarea>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn modal-help-cancel" data-dismiss="modal">Cerrar</button>
				<input id="send_help_request" class="btn btn-primary" type="button" onclick="HelpUtils.sendSupportRequest ();" value="Enviar solicitud" />
			</div>
		</div>
	</div>
</div>
{/strip}
</script>
<script type="text/html" id="help-modal-tip-template">
{strip}
<div id="" class="panel panel-default tip-item help-item">
	<div class="panel-heading">
		<h4 class="panel-title">
			<a href="" class="accordion-toggle collapsed tip-title" data-toggle="collapse" data-parent="#panel-tips"></a>
		</h4>
	</div>
	<div class="panel-collapse collapse tip-content">
		<div class="panel-body tip-description"></div>
	</div>
</div>
{/strip}
</script>
<script type="text/html" id="help-modal-video-template">
{strip}
<div id="" class="col-xs-12 col-md-6 video help-item">
	<p class="video-title" style="height: 2.5em;"></p>
	<iframe src="" frameborder="0" allowfullscreen="allowfullscreen" width="235px"></iframe>
</div>
{/strip}
</script>
<script type="text/html" id="help-modal-article-template">
{strip}
<div id="" class="col-xs-12 article help-item">
	<a target="_blank" href="" class="article-title"></a>
</div>
{/strip}
</script>
<script type="text/html" id="help-modal-question-template">
{strip}
<div id="" class="panel panel-default question-item help-item">
	<div class="panel-heading">
		<h4 class="panel-title">
			<a href="" class="accordion-toggle collapsed question-title" data-toggle="collapse" data-parent="#panel-faq"></a>
		</h4>
	</div>
	<div id="" class="panel-collapse collapse question-content">
		<div class="panel-body question-description"></div>
	</div>
</div>
{/strip}
</script>
<script type="text/javascript" src="include/js/help-utils.js"></script>
{/strip}