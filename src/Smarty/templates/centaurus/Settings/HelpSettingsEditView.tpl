<style type="text/css">
	.no-resize {
		resize: none;
	}
</style>
<form action="index.php" method="post" name="index" id="form" onsubmit="return HelpSettingsUtils.validateForm (this);">
	<input type="hidden" name="module" value="Settings" />
	<input type="hidden" name="action" value="SaveHelpSettings" />
{if (!empty ($HELP_ITEM))}
	<input type="hidden" name="record" value="{$HELP_ITEM.id}" id="record" />
{/if}
	<div class="row">
		<div class="col-xs-12">
			<h1 class="pull-left">
				<a href="index.php?module=Settings&action=HelpSettingsListView">{$MOD.LBL_CONFIG_HELP}</a></h1>
			<div class="action-bar pull-right">
				<button type="submit" class="btn btn-info">Guardar</button>
				<a href="index.php?module=Settings&action=HelpSettingsListView&parenttab=Settings" class="btn btn-warning">Cancelar</a>
			</div>
		</div>
	</div>
{if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
	<div class="row">
		<div class="alert {if (isset ($IS_ERROR)) && ($IS_ERROR)}alert-danger{else}alert-success{/if}">
			<strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
		</div>
	</div>
{/if}
	<div class="row">
		<div class="col-xs-12">
			<div class="main-box">
				<header class="main-box-header clearfix">
					<h2 class="pull-left">Información general</h2>
				</header>
				<div class="main-box-body clearfix">
					<div class="row">
						<div class="col-md-4">
							<div class="form-group">
								<label for="application">{$MOD.LBL_APP}</label>
								<select id="application" name="applicationid" class="form-control" onchange="HelpSettingsUtils.setModuleSelectsOptions (this);">
									<option value="">Seleccione</option>
{foreach $APPLICATIONS as $application}
									<option value="{$application.config_applicationsid}"{if ($HELP_ITEM.applicationid == $application.config_applicationsid)} selected="selected"{/if}>{$application.app_name}</option>
{/foreach}
								</select>
							</div>
						</div>
					</div>
					<div class="panel-group accordion" id="accordion">
						<div class="panel panel-default">
							<div class="panel-heading">
								<h4 class="panel-title">
									<a href="#tips" class="accordion-toggle" data-toggle="collapse" data-parent="#accordion">{$MOD.LBL_ACCORDION_ONE}</a>
								</h4>
							</div>
							<div id="tips" class="panel-collapse collapse in">
								<div class="panel-body">
									<textarea id="tips-description" name="tipsdescription" class="form-control no-resize" placeholder="{$MOD.LBL_DESCRIPTION}" maxlength="540">{$HELP_ITEM.tipsdescription}</textarea>
									<br />
									<div>
										<h5><strong>{$MOD.LBL_SUGERENCIAS}</strong></h5>
										<table id="tips-list" class="table table-bordered table-striped table-hover">
											<tr>
												<th>{$MOD.LBL_TITLE}</th>
												<th>{$MOD.LBL_DESCRIPTION}</th>
												<th>{$MOD.LBL_RELATED_MODULE}</th>
												<th>{$MOD.LBL_RELATED_FIELD}</th>
												<th>{$MOD.LBL_CONFIG_APPS_ACTION}</th>
											</tr>
{if (isset ($HELP_ITEM)) && (count ($HELP_ITEM.tips) > 0)}
	{foreach $HELP_ITEM.tips as $tip}
		{include file='Settings/HelpSettingsTipEditView.tpl' ID=$tip.id DATA=$tip}
	{/foreach}
{/if}
										</table>
										<div class="text-center">
											<button type="button" class="btn btn-success" onclick="HelpSettingsUtils.addTip ();"><i class="fa fa-plus"></i></button>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="panel panel-default">
							<div class="panel-heading">
								<h4 class="panel-title">
									<a href="#tutorials" class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion">{$MOD.LBL_ACCORDION_TWO}</a>
								</h4>
							</div>
							<div id="tutorials" class="panel-collapse collapse">
								<div class="panel-body">
									<textarea id="tutorials-description" name="tutorialsdescription" class="form-control no-resize" placeholder="{$MOD.LBL_DESCRIPTION}" maxlength="540">{$HELP_ITEM.tutorialsdescription}</textarea>
									<br />
									<div id="videos">
										<h5><strong>{$MOD.LBL_VIDEO}</strong></h5>
										<table id="videos-list" class="table table-bordered table-striped table-hover">
											<tr>
												<th>{$MOD.LBL_NAME}</th>
												<th>{$MOD.LBL_CUSTOM_BUTTONS_LINKACTION}</th>
												<th>{$MOD.LBL_RELATED_MODULE}</th>
												<th>{$MOD.LBL_RELATED_FIELD}</th>
												<th>{$MOD.LBL_CONFIG_APPS_ACTION}</th>
											</tr>
{if (isset ($HELP_ITEM)) && (count ($HELP_ITEM.videos) > 0)}
	{foreach $HELP_ITEM.videos as $video}
		{include file='Settings/HelpSettingsVideoEditView.tpl' ID=$video.id DATA=$video}
	{/foreach}
{/if}
										</table>
										<div class="text-center">
											<button type="button" class="btn btn-success" onclick="HelpSettingsUtils.addVideo ();"><i class="fa fa-plus"></i></button>
										</div>
									</div>
									<div id="articles">
										<h5><strong>{$MOD.LBL_ARTICULOS}</strong></h5>
										<table id="articles-list" class="table table-bordered table-striped table-hover">
											<tr>
												<th>{$MOD.LBL_NAME}</th>
												<th>{$MOD.LBL_CUSTOM_BUTTONS_LINKACTION}</th>
												<th>{$MOD.LBL_RELATED_MODULE}</th>
												<th>{$MOD.LBL_RELATED_FIELD}</th>
												<th>{$MOD.LBL_CONFIG_APPS_ACTION}</th>
											</tr>
{if (isset ($HELP_ITEM)) && (count ($HELP_ITEM.articles) > 0)}
	{foreach $HELP_ITEM.articles as $article}
		{include file='Settings/HelpSettingsArticleEditView.tpl' ID=$article.id DATA=$article}
	{/foreach}
{/if}
										</table>
										<div class="text-center">
											<button type="button" class="btn btn-success" onclick="HelpSettingsUtils.addArticle ();"><i class="fa fa-plus"></i></button>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="panel panel-default">
							<div class="panel-heading">
								<h4 class="panel-title">
									<a href="#questions" class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion">{$MOD.LBL_ACCORDION_THREE}</a>
								</h4>
							</div>
							<div id="questions" class="panel-collapse collapse">
								<div class="panel-body">
									<textarea id="questions-description" name="questionsdescription" class="form-control no-resize" placeholder="{$MOD.LBL_DESCRIPTION}" maxlength="540">{$HELP_ITEM.questionsdescription}</textarea>
									<br />
									<div>
										<h5><strong>{$MOD.LBL_PREGUNTAS}</strong></h5>
										<table id="questions-list" class="table table-bordered table-striped table-hover">
											<tr>
												<th>{$MOD.LBL_TITLE}</th>
												<th>{$MOD.LBL_DESCRIPTION}</th>
												<th>{$MOD.LBL_RELATED_MODULE}</th>
												<th>{$MOD.LBL_RELATED_FIELD}</th>
												<th>{$MOD.LBL_CONFIG_APPS_ACTION}</th>
											</tr>
{if (isset ($HELP_ITEM)) && (count ($HELP_ITEM.questions) > 0)}
	{foreach $HELP_ITEM.questions as $question}
		{include file='Settings/HelpSettingsQuestionEditView.tpl' ID=$question.id DATA=$question}
	{/foreach}
{/if}
										</table>
										<div class="text-center">
											<button type="button" class="btn btn-success" onclick="HelpSettingsUtils.addQuestion ();"><i class="fa fa-plus"></i></button>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="panel panel-default">
							<div class="panel-heading">
								<h4 class="panel-title">
									<a href="#more" class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion">{$MOD.LBL_ACCORDION_FOUR}</a>
								</h4>
							</div>
							<div id="more" class="panel-collapse collapse">
								<div class="panel-body">
									<textarea id="more-description" name="moredescription" class="form-control no-resize" placeholder="{$MOD.LBL_DESCRIPTION}" maxlength="540">{$HELP_ITEM.moredescription}</textarea>
									<br />
									<input type="text" id="more-url" name="moreurl" class="form-control" placeholder="Link a la página" value="{$HELP_ITEM.moreurl}" />
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-lg-12">
			<div class="action-bar pull-right">
				<button type="submit" class="btn btn-info">Guardar</button>
				<a href="index.php?module=Settings&action=HelpSettingsListView&parenttab=Settings" class="btn btn-warning">Cancelar</a>
			</div>
		</div>
	</div>
</form>
<script type="text/html" id="article-template">
{include file='Settings/HelpSettingsArticleEditView.tpl' ID='__ARTICLE_ID__' DATA=null}
</script>
<script type="text/html" id="tip-template">
{include file='Settings/HelpSettingsTipEditView.tpl' ID='__TIP_ID__' DATA=null}
</script>
<script type="text/html" id="question-template">
{include file='Settings/HelpSettingsQuestionEditView.tpl' ID='__QUESTION_ID__' DATA=null}
</script>
<script type="text/html" id="video-template">
{include file='Settings/HelpSettingsVideoEditView.tpl' ID='__VIDEO_ID__' DATA=null}
</script>
<script type="text/javascript" src="modules/Settings/help-settings.js"></script>
<script type="text/javascript">
	HelpSettingsUtils.init ({$APPLICATIONS|@json_encode});
</script>