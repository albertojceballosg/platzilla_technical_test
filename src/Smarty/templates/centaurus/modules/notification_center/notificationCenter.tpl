{strip}
	<link type="text/css" rel="stylesheet" href="modules/notification_center/notification_center.css" />
	<div id="notification-body" class="row-parley">
		<div class="col-md-12">
			<ul class="nav nav-tabs">
				<li>
					<a href="#tab-notify-system" role="tab" data-toggle="tab"><i class="fa fa-exclamation-triangle"
							aria-hidden="true"></i>{$MOD.LBL_TAB_SYSTEM}</a>
				</li>
				<li class="active">
					<a href="#tab-parley" role="tab" data-toggle="tab"><i class="fa fa-comments-o"
							aria-hidden="true"></i>{$MOD.LBL_TAB_CHAT}</a>
				</li>
				<li>
					<a href="#tab-archived-email" role="tab" data-toggle="tab"><i class="fa fa-archive"
							aria-hidden="true"></i>{$MOD.LBL_TAB_EMAIL_ARCHIVED}</a>
				</li>
				<li>
					<a href="#tab-email" role="tab" data-toggle="tab"><i class="fa fa-envelope-o"
							aria-hidden="true"></i>{$MOD.LBL_TAB_EMAIL}</a>
				</li>
			</ul>
			<div class="tab-content">
				<div id="tab-notify-system" class="tab-pane" role="tabpanel">
					<form method="post" id="system-alerts" role="form">
						<input type="hidden" id="module" name="module" value="notification_center" />
						<input type="hidden" name="action" value="notificationAjax" />
						<input type="hidden" name="file" value="searchSystemAlerts" />
						<input type="hidden" name="Ajax" value="true" />
						<input type="hidden" id="searchFrom" name="searchFrom" value="modalView" />
						<div class="row-parley justify-content-center" style="margin: 12px 0">
							<div class="col-md-3">
								<div class="form-group">
									<label>{$MOD.LBL_PERIODICITY}:</label>
									<div class="input-group">
										<div class="input-group-addon">
											<i class="fa  fa-clock-o"></i>
										</div>
										<select id="viewSysPeriod" name="viewSystemPeriod" class="form-control col-md-4"
											title="Buscar por tiempo"
											onchange="NotificationCenterUtils.searchPeriods (this)">
											{foreach $AVAILABLE_PERIODS as $periodName => $periodLabel}
												<option value="{$periodName}" {if ($INIT_PERIOD eq $periodName)}
													selected="selected" {/if}>{$periodLabel}</option>
											{/foreach}
										</select>
									</div>
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label>{$MOD.LBL_DATE_FROM}:</label>
									<div class="input-group">
										<div class="input-group-addon" style="border: 1px solid #ddd !important">
											<i class="fa fa-calendar"></i>
										</div>
										<input type="text" id="dateSystem-star" name="dateSystem[startdate]"
											value="{$ALERT_PERIOD['startdate']}"
											class="form-control pull-right input-readonly b-left col-md-12"
											readonly="readonly" placeholder="" />
									</div>
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label>{$MOD.LBL_DATE_TO}:</label>
									<div class="input-group">
										<div class="input-group-addon" style="border: 1px solid #ddd !important">
											<i class="fa fa-calendar"></i>
										</div>
										<input type="text" id="dateSystem-end" name="dateSystem[enddate]"
											value="{$ALERT_PERIOD['enddate']}"
											class="form-control pull-right input-readonly b-left col-md-12"
											readonly="readonly" placeholder="" />
									</div>
								</div>
							</div>
							<div class="col-md-2" style="padding-top: 25px">
								<button type="button" id="submitSearch" name="submitSearch" class="btn btn-primary btn-sm"
									onclick="NotificationCenterUtils.searchAlerts (this)">
									<i class="fa fa-search" aria-hidden="true"></i>
								</button>
							</div>
						</div>
					</form>
					<div id="list_alerts" class="list-group">
						{include file='modules/notification_center/listSystemAlerts.tpl'}
					</div>
				</div>
				<div id="tab-parley" class="tab-pane active" role="tabpanel">
					<form method="post" id="search-parley" name="search-parley" role="form">
						<input type="hidden" id="searchFrom" name="searchFrom" value="modalView" />
						<div class="row-parley justify-content-center " style="margin: 12px 0">
							<div class="col-md-3">
								<div class="form-group">
									<label>{$MOD.LBL_PERIODICITY}:</label>
									<div class="input-group">
										<div class="input-group-addon">
											<i class="fa  fa-clock-o"></i>
										</div>
										<select id="viewPeriod" name="viewPeriod" class="form-control col-md-4"
											title="Buscar por tiempo" onchange="NotificationCenterUtils.searchByTime()">
											<option value="{$today}">{$MOD.OPT_TODAY}</option>
											<option value="{$lastWeek}" selected>{$MOD.OPT_LAST_WEEK}</option>
											<option value="{$lastMonth}">{$MOD.OPT_LAST_MONTH}</option>
											<option value="{$lastThMonth}">{$MOD.OPT_LAST_THREE_MONTH}</option>
										</select>
									</div>
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label>{$MOD.LBL_MODULE}:</label>
									<div class="input-group">
										<select id="viewModule" name="viewModule" class="form-control col-md-4"
											title="Buscar por modulo">
											<option value="" selected>{$MOD.OPT_ALL}</option>
											{foreach $PARLEY_MODULES as $module}
												<option value="{$module.module}">{$module.tablabel}</option>
											{/foreach}
										</select>
									</div>
								</div>
							</div>
							<div class="col-md-2">
								<div class="form-group">
									<label>{$MOD.LBL_DATE_FROM}:</label>
									<div class="input-group">
										<div class="input-group-addon" style="border: 1px solid #ddd !important">
											<i class="fa fa-calendar"></i>
										</div>
										<input type="text" id="date_from" name="dateFrom" value="{$dateFrom}"
											class="form-control pull-right input-readonly b-left col-md-12"
											readonly="readonly" placeholder="" />
									</div>
								</div>
							</div>
							<div class="col-md-2">
								<div class="form-group">
									<label>{$MOD.LBL_DATE_TO}:</label>
									<div class="input-group">
										<div class="input-group-addon" style="border: 1px solid #ddd !important">
											<i class="fa fa-calendar"></i>
										</div>
										<input type="text" id="date_to" name="dateTo" value="{$dateTo}"
											class="form-control pull-right input-readonly b-left col-md-12"
											readonly="readonly" placeholder="" />
									</div>
								</div>
							</div>
							<div class="col-md-2" style="padding-top: 25px">
								<button type="button" id="submitSearch" name="submitSearch" class="btn btn-primary btn-sm"
									onclick="NotificationCenterUtils.searchParley()">
									<i class="fa fa-search" aria-hidden="true"></i>
								</button>
							</div>
						</div>
					</form>
					<div id="list_parley" class="list-group">
						{include file='modules/notification_center/listParley.tpl'}
					</div>
				</div>
				<div id="tab-archived-email" class="tab-pane" role="tabpanel">
					<form method="post" id="search-archived-emails" name="search-archived-emails" class="" role="form">
						<input type="hidden" id="searchFrom" name="searchFrom" value="modalView">
						<div class="row-parley justify-content-center " style="margin: 12px 0">
							<div class="col-md-3">
								<div class="form-group">
									<label>{$MOD.LBL_PERIODICITY}:</label>
									<div class="input-group">
										<div class="input-group-addon">
											<i class="fa  fa-clock-o"></i>
										</div>
										<select id="archivedEmailsPeriod" name="archivedEmailsPeriod"
											class="form-control col-md-4" title="Buscar por tiempo"
											onchange="NotificationCenterUtils.searchArchivedEmailsByTime()">
											<option value="{$emailToday}">{$MOD.OPT_TODAY}</option>
											<option value="{$emailWeek}">{$MOD.OPT_LAST_WEEK}</option>
											<option value="{$emailsFrom}" selected>{$MOD.OPT_LAST_MONTH}</option>
											<option value="{$emailThMonth}">{$MOD.OPT_LAST_THREE_MONTH}</option>
										</select>
									</div>
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label>{$MOD.LBL_MODULE}:</label>
									<div class="input-group">
										<select id="viewArchveModule" name="viewArchveModule" class="form-control col-md-4"
											title="Buscar por modulo">
											<option value="" selected>{$MOD.OPT_ALL}</option>
											{foreach $PARLEY_MODULES as $module}
												<option value="{$module.module}">{$module.tablabel}</option>
											{/foreach}
										</select>
									</div>
								</div>
							</div>
							<div class="col-md-2">
								<div class="form-group">
									<label>{$MOD.LBL_DATE_FROM}:</label>
									<div class="input-group">
										<div class="input-group-addon" style="border: 1px solid #ddd !important">
											<i class="fa fa-calendar"></i>
										</div>
										<input type="text" id="archivedEmailsDatefrom" name="archivedEmailsDateFrom"
											value="{$emailsFrom}"
											class="form-control pull-right input-readonly b-left col-md-12"
											readonly="readonly" placeholder="" />
									</div>
								</div>
							</div>
							<div class="col-md-2">
								<div class="form-group">
									<label>{$MOD.LBL_DATE_TO}:</label>
									<div class="input-group">
										<div class="input-group-addon" style="border: 1px solid #ddd !important">
											<i class="fa fa-calendar"></i>
										</div>
										<input type="text" id="archivedEmailsDateTo" name="archivedEmailsDateTo"
											value="{$dateTo}"
											class="form-control pull-right input-readonly b-left col-md-12"
											readonly="readonly" placeholder="" />
									</div>
								</div>
							</div>
							<div class="col-md-2" style="padding-top: 25px">
								<button type="button" id="archivedEmailsSubmitSearch" name="submitSearch"
									class="btn btn-primary btn-sm" onclick="NotificationCenterUtils.searchEmailsArchived()">
									<i class="fa fa-search" aria-hidden="true"></i>
								</button>
							</div>
						</div>
					</form>
					<div id="list_archivedEmails" class="list-group">
						{include file='modules/notification_center/listArchivedEmails.tpl'}
					</div>
				</div>
				<div id="tab-email" class="tab-pane" role="tabpanel">
					{if (empty ($MAIL_ACCOUNT))}
						<div class="alert alert-warning">
							No tienes cuentas de correo electrónico asociadas a Platzilla
							<a href="index.php?module=webmail&action=AccountEditView" class="btn btn-warning">Asociar una
								cuenta</a>
						</div>
					{else}
						<div class="row">
							<form action="index.php" method="get">
								<input type="hidden" name="module" value="webmail" />
								<input type="hidden" name="action" value="FetchMailFromServer" />
								<input type="hidden" name="Ajax" value="true" />
								<div class="col-xs-12 col-md-10">
									<select name="providername" class="form-control" title="">
										<option value="{$MAIL_ACCOUNT->getEmailAddress ()}">{$MAIL_ACCOUNT->getEmailAddress ()}
										</option>
									</select>
								</div>
								<div class="col-xs-12 col-md-2">
									<button type="submit" class="btn btn-primary">Obtener correos</button>
								</div>
							</form>
						</div>
					{/if}
					<form method="post" id="not-archived-emails" name="not-archived-emails" role="form">
						<div class="row-parley justify-content-center " style="margin: 12px 0">
							<div class="col-md-3">
								<div class="form-group">
									<label>{$MOD.LBL_PERIODICITY}:</label>
									<div class="input-group">
										<div class="input-group-addon">
											<i class="fa  fa-clock-o"></i>
										</div>
										<select id="emailsPeriod" name="emailsPeriod" class="form-control col-md-4"
											title="Buscar por tiempo"
											onchange="NotificationCenterUtils.searchEmailsByTime()">
											<option value="{$emailToday}">{$MOD.OPT_TODAY}</option>
											<option value="{$emailWeek}">{$MOD.OPT_LAST_WEEK}</option>
											<option value="{$emailsFrom}" selected>{$MOD.OPT_LAST_MONTH}</option>
											<option value="{$emailThMonth}">{$MOD.OPT_LAST_THREE_MONTH}</option>
										</select>
									</div>
								</div>
							</div>
							<div class="col-md-3">&nbsp;
							</div>
							<div class="col-md-2">
								<div class="form-group">
									<label>{$MOD.LBL_DATE_FROM}:</label>
									<div class="input-group">
										<div class="input-group-addon" style="border: 1px solid #ddd !important">
											<i class="fa fa-calendar"></i>
										</div>
										<input type="text" id="emailsDatefrom" name="emailsDateFrom" value="{$emailsFrom}"
											class="form-control pull-right input-readonly b-left col-md-12"
											readonly="readonly" placeholder="" />
									</div>
								</div>
							</div>
							<div class="col-md-2">
								<div class="form-group">
									<label>{$MOD.LBL_DATE_TO}:</label>
									<div class="input-group">
										<div class="input-group-addon" style="border: 1px solid #ddd !important">
											<i class="fa fa-calendar"></i>
										</div>
										<input type="text" id="emailsDateTo" name="emailsDateTo" value="{$dateTo}"
											class="form-control pull-right input-readonly b-left col-md-12"
											readonly="readonly" placeholder="" />
									</div>
								</div>
							</div>
							<div class="col-md-2" style="padding-top: 25px">
								<button type="button" id="emailsSubmitSearch" name="submitSearch"
									class="btn btn-primary btn-sm" onclick="NotificationCenterUtils.searchEmails()">
									<i class="fa fa-search" aria-hidden="true"></i>
								</button>
							</div>
						</div>
					</form>
					<div id="list_emails" class="list-group">
						{include file='modules/notification_center/listEmails.tpl'}
					</div>
				</div>
			</div>
		</div>
	</div>
	{include file='modules/webmail/AddEmailAccountModal.tpl'}
	<script type="text/html" id="archive-mail-template">
		{include file='modules/notification_center/archiveMail.tpl'}
	</script>
	<script src="themes/centaurus/js/bootstrap-datepicker.js"></script>
	<script src="themes/centaurus/js/bootstrap-datepicker.es.js"></script>
	<script type="text/javascript" src="modules/notification_center/notification_center.js"></script>
	<script type="text/javascript" src="webmail/program/js/common.min.js"></script>
	<script type="text/javascript" src="modules/webmail/webmail.js?v=1.0.1"></script>

	<!-- Definir typeaheadSource para el chat del notification center -->
	<script type="text/javascript">
		// Definir typeaheadSource para el autocomplete del chat
		var typeaheadSource = {$SEARCH_USERS_CHATS|default:'[]'};
	</script>
{/strip}