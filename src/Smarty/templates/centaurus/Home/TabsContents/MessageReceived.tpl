{strip}
{if ($IS_EMAIL)}
	{assign var='dummy' value=explode (' ', $SENDER)}
	{assign var='emailAddress' value=str_replace ('>', '', str_replace ('<', '', array_pop ($dummy)))}
	{assign var='fullName' value=join (' ', $dummy)}
{/if}
<div class="conversation-item item-left clearfix"{if ($IS_EMAIL) && (isset ($MESSAGE_ID))} data-id="{$MESSAGE_ID}"{/if}{if ($IS_EMAIL) && (isset ($ACCOUNT_NAME))} data-account="{$ACCOUNT_NAME}"{/if}>
	<div class="conversation-user">
		<img src="{if $REGISTERED_AS.contacts[0]['photo'] neq NULL}{$REGISTERED_AS.contacts[0]['photo']}{else}themes/centaurus/img/avatar_2x.png{/if}" class="img-responsive" alt="">
	</div>
	<div class="conversation-body{if ($IS_EMAIL)} email{/if}{if ($STATUS_EMAIL eq 'UNREAD_EMAIL')} unread-email{/if}">
		<div class="row">
			<div class="col-xs-12 col-sm-9 col-md-10 name">{$SENDER|htmlentities}{if ($IS_EMAIL)} (en la cuenta {$ACCOUNT_NAME}){/if}</div>
			<div class="col-xs-12 col-sm-3 col-md-2 time hidden-xs">{$SINCE}</div>
			<div class="col-xs-12{if ($IS_EMAIL)} col-sm-9 col-md-10{/if} text">{$SUBJECT}</div>
{if (($IS_EMAIL) && (!$HIDE_ACTIONS))}
			<div class="col-xs-12 col-sm-3 col-md-2 actions">
				<button type="button" title="Asociar a un registro" class="btn btn-{if (!empty ($RELATED_ENTITIES_DATA))}success{else}default{/if} action" data-toggle="collapse" data-target="#related-entities-{$MESSAGE_ID}">
					<i class="fa fa-link"></i> <span class="caret"></span>
				</button>
				<div class="dropdown">
					<button type="button" title="Crear un registro a partir del correo"  class="btn btn-{if (!empty ($REGISTERED_AS.customers)) || (!empty ($REGISTERED_AS.contacts)) || (!empty ($REGISTERED_AS.potentials)) || (!empty ($REGISTERED_AS.providers))}success{else}default{/if} dropdown-toggle action" data-toggle="dropdown">
						<i class="fa fa-plus"></i> <span class="caret"></span>
					</button>
					<ul class="dropdown-menu dropdown-menu-right">
	{if (empty ($REGISTERED_AS.customers))}
						<li>
							<a href="index.php?module=clientes&action=EditView&mode=create&e_mail={$emailAddress}&alias={$fullName}&return_module=Home&return_action=index">Cliente</a>
						</li>
	{/if}
	{if (empty ($REGISTERED_AS.contacts))}
						<li>
							<a href="index.php?module=contactos&action=EditView&mode=create&email={$emailAddress}&apellidos={$fullName}&return_module=Home&return_action=index">Contacto</a>
						</li>
	{/if}
	{if (empty ($REGISTERED_AS.potentials))}
						<li>
							<a href="index.php?module=potenciales_clientes&action=EditView&mode=create&e_mail={$emailAddress}&alias={$fullName}&return_module=Home&return_action=index">Prospecto</a>
						</li>
	{/if}
	{if (empty ($REGISTERED_AS.providers))}
						<li>
							<a href="index.php?module=proveedores&action=EditView&mode=create&email={$emailAddress}&alias={$fullName}&return_module=Home&return_action=index">Proveedor</a>
						</li>
	{/if}
	{if (!empty ($REGISTERED_AS.customers)) || (!empty ($REGISTERED_AS.contacts)) || (!empty ($REGISTERED_AS.potentials)) || (!empty ($REGISTERED_AS.providers))}
						<li class="divider"></li>
						<li class="dropdown-header">Registrado como</li>
		{if (!empty ($REGISTERED_AS.customers))}
			{foreach $REGISTERED_AS.customers as $customerData}
						<li>
							<a href="index.php?module=clientes&action=DetailView&record={$customerData.id}" target="_blank">Cliente: {$customerData.fullname}</a>
						</li>
			{/foreach}
		{/if}
		{if (!empty ($REGISTERED_AS.contacts))}
			{foreach $REGISTERED_AS.contacts as $contactData}
						<li>
							<a href="index.php?module=contactos&action=DetailView&record={$contactData.id}" target="_blank">Contacto: {$contactData.fullname}</a>
						</li>
			{/foreach}
		{/if}
		{if (!empty ($REGISTERED_AS.potentials))}
			{foreach $REGISTERED_AS.potentials as $potentialData}
						<li>
							<a href="index.php?module=potenciales_clientes&action=DetailView&record={$potentialData.id}" target="_blank">Prospecto: {$potentialData.fullname}</a>
						</li>
			{/foreach}
		{/if}
		{if (!empty ($REGISTERED_AS.providers))}
			{foreach $REGISTERED_AS.providers as $providerData}
						<li>
							<a href="index.php?module=proveedores&action=DetailView&record={$providerData.id}" target="_blank">Proveedor: {$providerData.fullname}</a>
						</li>
			{/foreach}
		{/if}
	{/if}
					</ul>
				</div>
			</div>
			<div id="related-entities-{$MESSAGE_ID}" class="col-xs-12 collapse related-entities">
				<form action="index.php" method="post" onsubmit="WebmailUtils.relateEntities (this); return false;">
					<input type="hidden" name="module" value="webmail" />
					<input type="hidden" name="action" value="RelateEntities" />
					<input type="hidden" name="Ajax" value="true" />
					<input type="hidden" name="record" value="{$MESSAGE_ID}" />
					<div class="table-responsive">
                        <div style="font-weight: bold;padding: 4px 6px">Asociar el correo a un registro</div>
						<table class="table">
							<thead>

							<tr>
								<th class="col-module-name">Módulo</th>
								<th class="col-related-entity">Entidad relacionada</th>
								<th class="col-actions"></th>
							</tr>
							</thead>
							<tbody>
	{foreach $RELATED_ENTITIES_DATA as $relatedEntitydata}
		{include file='Home/TabsContents/RelatedEntityRow.tpl' MESSAGE_ID=$MESSAGE_ID RELATED_ENTITY_DATA=$relatedEntitydata}
	{foreachelse}
		{include file='Home/TabsContents/RelatedEntityRow.tpl' MESSAGE_ID=$MESSAGE_ID}
	{/foreach}
							</tbody>
							<tfoot>
							<tr>
								<td colspan="3" class="text-center">
									<button type="button" class="btn btn-link" onclick="WebmailUtils.addRelatedEntityRow (this);"><i class="fa fa-plus"></i></button>
								</td>
							</tr>
							</tfoot>
						</table>
						<div class="action-bar text-center">
							<button type="submit" class="btn btn-primary">Asociar</button>
						</div>
					</div>
				</form>
			</div>
{/if}
		</div>
	</div>
</div>
{/strip}