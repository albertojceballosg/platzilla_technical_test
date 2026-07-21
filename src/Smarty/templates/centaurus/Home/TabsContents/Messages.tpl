{strip}
{assign var='today' value=date('Y-m-d')}
{assign var='lastWeek' value=date_format(date_sub(date_create(), date_interval_create_from_date_string('7 days')), 'Y-m-d')}
{assign var='lastMonth' value=date_format(date_sub(date_create(), date_interval_create_from_date_string('1 month')), 'Y-m-d')}
{assign var='lastQuarter' value=date_format(date_sub(date_create(), date_interval_create_from_date_string('3 months')), 'Y-m-d')}
{assign var='lastTime' value=date_format(date_sub(date_create(), date_interval_create_from_date_string('+3 year')), 'Y-m-d')}
<div class="main-box clearfix{*no-header*}" style="padding-top: 12px">
	<div class="main-box-body clearfix">
{if (empty ($MAIL_ACCOUNTS))}
		<div class="alert alert-warning text-center">
			No tienes cuentas de correo electrónico asociadas a Platzilla
			<a href="index.php?module=webmail&action=AccountEditView&return_module=Home&return_action=index" class="btn btn-warning">Asociar una cuenta</a>
		</div>
{else}
		<form action="index.php" method="get" class="row filters-form" onsubmit="WebmailUtils.searchEmailMessages (this); return false;">
			<input type="hidden" name="module" value="webmail" />
			<input type="hidden" name="action" value="SearchMails" />
			<input type="hidden" name="Ajax" value="true" />
			<div class="col-xs-12 col-md-2">
				<div class="form-group">
					<label for="emails-tab-mail-status">Tipo:</label>
					<select id="emails-tab-mail-status" name="status" class="form-control" title="" onchange="WebmailUtils.setEmailType (this);">
						<option value="{WebmailUtils::STATUS_ALL}"{if (empty ($SELECTED_MAIL_STATUS)) || ($SELECTED_MAIL_STATUS == WebmailUtils::STATUS_ALL)} selected="selected"{/if}>Todos</option>
						<option value="{WebmailUtils::STATUS_RELATED}"{if ($SELECTED_MAIL_STATUS == WebmailUtils::STATUS_RELATED)} selected="selected"{/if}>Todos los relacionados</option>
						<option value="{WebmailUtils::STATUS_UNRELATED}"{if ($SELECTED_MAIL_STATUS == WebmailUtils::STATUS_UNRELATED)} selected="selected"{/if}>Todos los no relacionados</option>
						<option value="{WebmailUtils::STATUS_INCOMING}"{if ($SELECTED_MAIL_STATUS == WebmailUtils::STATUS_INCOMING)} selected="selected"{/if}>Recibidos</option>
						<option value="{WebmailUtils::STATUS_UNREAD_EMAIL}"{if ($SELECTED_MAIL_STATUS == WebmailUtils::STATUS_UNREAD_EMAIL)} selected="selected"{/if}>No leídos</option>
						<option value="{WebmailUtils::STATUS_INCOMING_RELATED}"{if ($SELECTED_MAIL_STATUS == WebmailUtils::STATUS_INCOMING_RELATED)} selected="selected"{/if}>Recibidos relacionados</option>
						<option value="{WebmailUtils::STATUS_INCOMING_UNRELATED}"{if ($SELECTED_MAIL_STATUS == WebmailUtils::STATUS_INCOMING_UNRELATED)} selected="selected"{/if}>Recibidos no relacionados</option>
						<option value="{WebmailUtils::STATUS_OUTGOING}"{if ($SELECTED_MAIL_STATUS == WebmailUtils::STATUS_OUTGOING)} selected="selected"{/if}>Enviados</option>
						<option value="{WebmailUtils::STATUS_OUTGOING_RELATED}"{if ($SELECTED_MAIL_STATUS == WebmailUtils::STATUS_OUTGOING_RELATED)} selected="selected"{/if}>Enviados relacionados</option>
						<option value="{WebmailUtils::STATUS_OUTGOING_UNRELATED}"{if ($SELECTED_MAIL_STATUS == WebmailUtils::STATUS_OUTGOING_UNRELATED)} selected="selected"{/if}>Enviados no relacionados</option>
					</select>
				</div>
			</div>
			<div class="col-xs-12 col-md-2 col-lg-3">
				<div class="form-group">
					<label for="emails-tab-period">Período:</label>
					<div class="input-group">
						<span class="input-group-addon hidden-md"><i class="fa fa-clock-o"></i></span>
						<select name="emailsperiod" id="emails-tab-period" class="form-control" title="Buscar por tiempo"  data-last-time="{$lastTime}" data-today="{$today}" onchange="WebmailUtils.setFilterPeriod (this, 0);">
							<option value="{$today} {if ($PERIOD eq $today)} selected="selected" {/if}">Hoy</option>
							<option value="{$lastWeek}"{if ($PERIOD eq $lastWeek) || ($PERIOD eq NULL)} selected="selected" {/if}>Última semana</option>
							<option value="{$lastMonth}" {if ($PERIOD eq $lastMonth)} selected="selected" {/if}>Último mes</option>
							<option value="{$lastQuarter}" {if ($PERIOD eq $lastQuarter)} selected="selected" {/if}>Último trimestre</option>
							<option value="CUSTOMIZE" {if ($PERIOD eq 'CUSTOMIZE')} selected="selected" {/if}>Personalizado</option>
						</select>
					</div>
					<span id="emails-tab-help"  class="help-block"></span>
				</div>
			</div>
			<div class="col-xs-12 col-md-2">
				<div class="form-group">
					<label for="emails-tab-from">Desde:</label>
					<div class="input-group">
						<span class="input-group-addon hidden-md"><i class="fa fa-calendar"></i></span>
						<input type="text" id="emails-tab-from" name="from" value="{if $FROM eq NULL}{$lastWeek}{else}{$FROM}{/if}" class="form-control from-field" readonly="readonly" onchange="WebmailUtils.setDatePeriod ();" />
					</div>
				</div>
			</div>
			<div class="col-xs-12 col-md-2">
				<div class="form-group">
					<label for="emails-tab-to">Hasta:</label>
					<div class="input-group">
						<span class="input-group-addon hidden-md"><i class="fa fa-calendar"></i></span>
						<input type="text" id="emails-tab-to" name="to" value="{if $TO eq NULL}{$today}{else}{$TO}{/if}" class="form-control to-field" readonly="readonly" onchange="WebmailUtils.setDatePeriod ();" />
					</div>
				</div>
			</div>
			<div class="col-xs-12 col-md-4 col-lg-3 action-bar">
				<a href="index.php?module=webmail&action=FetchMailFromServer&Ajax=true" class="btn btn-success"><i class="fa fa-play hidden-xs"></i> Obtener correos</a>
				<a href="index.php?module=webmail&action=AccountListView&return_module=Home&return_action=index" class="btn btn-default"><i class="fa fa-cogs hidden-xs"></i> Configurar</a>
			</div>
		</form>
		<div id="emails-conversation" class="conversation-wrapper">
			<div class="conversation-content">
				<div class="conversation-inner">
	{foreach $EMAILS_DATA as $emailData}
		{if ($emailData.type == WebmailUtils::TYPE_INCOMING)}
			{include file='Home/TabsContents/MessageReceived.tpl'
				IS_EMAIL=true
				MESSAGE_ID=$emailData.crmid
				ACCOUNT_NAME=$emailData.account
				SENDER=$emailData.sender
				SUBJECT=$emailData.subject
				SINCE=$emailData.timesince
				REGISTERED_AS=$emailData.registeredas
				RELATED_ENTITIES_DATA=$emailData.relatedentities
            	STATUS_EMAIL=$emailData.status_email
			}
		{else}
			{include file='Home/TabsContents/MessageSent.tpl'
				IS_EMAIL=true
				MESSAGE_ID=$emailData.crmid
				ACCOUNT_NAME=$emailData.account
				SENDER=$emailData.sender
				SUBJECT=$emailData.subject
				SINCE=$emailData.timesince
				REGISTERED_AS=$emailData.registeredas
				RELATED_ENTITIES_DATA=$emailData.relatedentities
			}
		{/if}
	{foreachelse}
		{include file='Home/TabsContents/MessageReceived.tpl' SENDER='Platzilla' SUBJECT='No se encontraron correos en el período seleccionado'}
	{/foreach}
				</div>
			</div>
		</div>
{/if}
	</div>
</div>
{/strip}