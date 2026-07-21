{strip}
{if (isset ($NEWS_ITEM))}
	{assign var='newsEndDateTime' value=explode (' ', $NEWS_ITEM->getEndDate())}
	{assign var='newsStartDateTime' value=explode (' ', $NEWS_ITEM->getStartDate())}
	{assign var='newsId' value=$NEWS_ITEM->getId()}
	{assign var='newsContent' value=$NEWS_ITEM->getContent()}
	{assign var='newsEndDate' value=$newsEndDateTime[0]}
	{assign var='newsEndTime' value=$newsEndDateTime[1]}
	{assign var='newsSharing' value=$NEWS_ITEM->getSharing()}
	{assign var='newsSharingEntityIds' value=array()}
	{assign var='newsSharingUserIds' value=array()}
	{assign var='newsStartDate' value=$newsStartDateTime[0]}
	{assign var='newsStartTime' value=$newsStartDateTime[1]}
	{assign var='newsTitle' value=$NEWS_ITEM->getTitle()}
    {assign var='newsCategories' value=$NEWS_ITEM->getCategories()}
    {assign var='newsStatus' value=$NEWS_ITEM->getStatus()}
	{foreach $NEWS_ITEM->getSharing() as $sharingElement}
		{if (!empty ($sharingElement['entityid']))}
			{$newsSharingEntityIds[] = $sharingElement['entityid']}
		{/if}
		{if (!empty ($sharingElement['userid']))}
			{$newsSharingUserIds[] = $sharingElement['userid']}
		{/if}
	{/foreach}
	{if $NEWS_ITEM->getAdQueue() neq NULL}
        {assign var='newsQueue' value=$NEWS_ITEM->getAdQueue()->getId()}
	{else}
        {assign var='newsQueue' value=null}
	{/if}
{else}
	{assign var='newsId' value=null}
	{assign var='newsContent' value=null}
	{assign var='newsEndDate' value=null}
	{assign var='newsEndTime' value=null}
	{assign var='newsSharing' value=null}
	{assign var='newsSharingEntityIds' value=array()}
	{assign var='newsSharingUserIds' value=array()}
	{assign var='newsTitle' value=null}
	{assign var='newsStartDate' value=null}
	{assign var='newsStartTime' value=null}
    {assign var='newsCategories' value='PLATZILLA'}
    {assign var='newsStatus' value=null}
    {assign var='newsQueue' value=null}
{/if}
<link rel="stylesheet" href="themes/centaurus/css/libs/bootstrap-timepicker.css" type="text/css" />
<link rel="stylesheet" type="text/css" href="modules/News/News.css" />
<form method="post" action="index.php" onsubmit="return NewsUtils.validateForm (this);">
	<input type="hidden" name="module" value="News" />
	<input type="hidden" name="action" value="Save" />
	<input type="hidden" name="record" value="{$newsId}" />
	<input type="hidden" name="return_action" value="{$RETURN_ACTION}" />
	<input type="hidden" name="return_module" value="{$RETURN_MODULE}" />
	<input type="hidden" name="Ajax" value="true" />
	<div class="row">
		<div class="col-xs-12">
			<h1 class="pull-left">
				<a href="index.php?module=News&action=ListView&parenttab=Settings">Anuncios</a>
			</h1>
			<div class="action-bar pull-right">
				<button type="submit" class="btn btn-info">Guardar</button>
				<a href="index.php?module=News&action=ListView&parenttab=Settings" class="btn btn-warning" style="margin-left: 5px;">Cancelar</a>
			</div>
		</div>
	</div>
{if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
	<div class="row">
		<div class="alert alert-{if (isset ($IS_ERROR)) && ($IS_ERROR)}danger{else}success{/if}">
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
				<div class="main-box-body">
					<div class="row">
						<div class="col-xs-12">
							<div class="label-input" style="text-align: left;">
								<label for="news-title">Título <span class="required">*</span></label>
							</div>
							<div class="form-group field-container">
								<input type="text" id="news-title" name="title" value="{$newsTitle}" maxlength="255" class="form-control" />
							</div>
						</div>
						{* Categories and status news *}
						<div class="col-md-6">
							<div class="label-input" style="text-align: left;">
								<label for="news-from">Categoria</label>
							</div>
							<div class="form-group field-container">
								<select class="form-control" name="category" id="category" onchange="NewsUtils.selectedCatogory(this)">
                                    {foreach $CATEGORIES as $key => $newsCategory}
										<option value="{$key}"
                                                {if $key eq $newsCategories}selected{/if} > {$newsCategory}{*$MOD[$newsCategory]*}</option>
                                    {/foreach}
								</select>
							</div>
						</div>
						<div class="col-md-6">
							<div class="label-input" style="text-align: left;">
								<label for="news-from">Estatus</label>
							</div>
							<div class="form-group field-container">
								<select class="form-control" name="status" id="status">
                                    {foreach $AVAILABLE_STATUS as $availableStatus}
										<option value="{$availableStatus}"
                                                {if $availableStatus eq $newsStatus}selected{/if} >{$MOD[$availableStatus]}</option>
                                    {/foreach}
								</select>
							</div>
						</div>
						{* Categories and status news  *}
						<div id="PLATZILLA" class="{if ($newsCategories neq 'PLATZILLA')} hide {/if}">
						<div class="col-xs-12 col-md-3 col-lg-2">
							<div class="label-input" style="text-align: left;">
								<label for="news-from">Visible desde</label>
							</div>
							<div class="form-group field-container">
								<div class="input-group" style="margin-bottom: 0.5em;">
									<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
									<input type="text" id="news-from" name="startdate" value="{$newsStartDate}" class="form-control date" placeholder="" />
								</div>
							</div>
						</div>
						<div class="col-xs-12 col-md-3 col-lg-2">
							<div class="form-group field-container" style="margin-top: 34px;">
								<div class="input-group bootstrap-timepicker timepicker">
									<input type="text" name="starttime" value="{$newsEndTime}" class="form-control time" placeholder="" />
									<span class="input-group-addon"><i class="fa fa-clock-o"></i></span>
								</div>
							</div>
						</div>
						<div class="col-xs-12 col-md-3 col-lg-2">
							<div class="label-input" style="text-align: left;">
								<label for="news-to">Visible hasta</label>
							</div>
							<div class="form-group field-container">
								<div class="input-group" style="margin-bottom: 0.5em;">
									<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
									<input type="text" id="news-to" name="enddate" value="{$newsEndDate}" class="form-control date" placeholder="" />
								</div>
							</div>
						</div>
						<div class="col-xs-12 col-md-3 col-lg-2">
							<div class="form-group field-container" style="margin-top: 34px;">
								<div class="input-group bootstrap-timepicker timepicker">
									<input type="text" name="endtime" value="{$newsEndTime}" class="form-control time" placeholder="" />
									<span class="input-group-addon"><i class="fa fa-clock-o"></i></span>
								</div>
							</div>
						</div>
					</div>
						<div id="QUEUE-CATEGORY" class="{if $newsCategories eq 'PLATZILLA'} hide {/if}">
							<div class="col-md-6">
								<div class="label-input" style="text-align: left;">
									<label for="news-from">Cola de anuncios</label>
								</div>
								<div class="form-group field-container">
									<select class="form-control" name="queue" id="queue">
										{if $AD_QUEUES neq NULL}
                                        {foreach $AD_QUEUES as $adQueue}
											<option value="{$adQueue->getId()}"
													{if $adQueue->getId() eq $newsQueue}selected{/if}
                                                    >{$adQueue->getName()} - {$MOD[$AVAILABLE_PERIODS[$adQueue->getPeriod()]]}</option>
                                        {/foreach}
                                        {/if}
									</select>
								</div>
							</div>
							<div class="col-md-6">&nbsp;</div>
						</div>
						<div class="col-xs-12">
							<div class="label-input" style="text-align: left;">
								<label for="news-content">Contenido del anuncio <span class="required">*</span></label>
							</div>
							<div class="form-group field-container">
								<textarea id="news-content" name="content" class="form-control">{$newsContent}</textarea>
							</div>
						</div>
					</div>
					<div class="row sharing-data">
						<div class="col-xs-12 col-md-6">
							<div class="row sharing-items">
								<div class="label-input col-xs-12" style="text-align: left;">
									<label>Compartir con usuarios</label>
								</div>
								<div class="form-group col-xs-12 col-md-5">
									<label for="available-users">Usuarios disponibles</label>
									<select id="available-users" class="form-control available-items" multiple="multiple">
										<option value="-ALL-">Todos</option>
{foreach $AVAILABLE_USERS_DATA as $availableUserData}
										<option value="{$availableUserData.id};{$availableUserData.email}"{if (in_array ($availableUserData.id, $newsSharingUserIds))} style="display: none;"{/if}>{$availableUserData.fullname}</option>
{/foreach}
									</select>
								</div>
								<div class="form-group col-xs-12 col-md-1 columns-actions">
									<div class="vertical-group">
										<button type="button" class="btn btn-primary btn-icon center-block" onclick="NewsUtils.addAllSharingItems (this);"><i class="fa fa-angle-double-right"></i></button>
										<button type="button" class="btn btn-primary btn-icon center-block" onclick="NewsUtils.addSelectedSharingItems (this);"><i class="fa fa-angle-right"></i></button>
										<button type="button" class="btn btn-warning btn-icon center-block" onclick="NewsUtils.removeSelectedSharingItems (this);"><i class="fa fa-angle-left"></i></button>
										<button type="button" class="btn btn-warning btn-icon center-block" onclick="NewsUtils.removeAllSharingItems (this);"><i class="fa fa-angle-double-left"></i></button>
									</div>
								</div>
								<div class="form-group col-xs-12 col-md-5">
									<label for="selected-users">Usuarios seleccionados <span class="required">*</span></label>
									<select id="selected-users" name="sharing[USERS][]" class="form-control selected-items" multiple="multiple">
										<option value="-ALL-" disabled="disabled" style="display: none;">Todos</option>
{foreach $AVAILABLE_USERS_DATA as $availableUserData}
										<option value="{$availableUserData.id};{$availableUserData.email}"{if (!in_array ($availableUserData.id, $newsSharingUserIds))} disabled="disabled" style="display: none;"{/if}>{$availableUserData.fullname}</option>
{/foreach}
									</select>
								</div>
							</div>
						</div>
						<div class="col-xs-12 col-md-6">
							<div class="row sharing-items">
								<div class="label-input col-xs-12" style="text-align: left;">
									<label>Compartir con clientes</label>
								</div>
								<div class="form-group col-xs-12 col-md-5">
									<label for="available-customers">Clientes disponibles</label>
									<select id="available-customers" class="form-control available-items" multiple="multiple">
										<option value="-ALL-CUSTOMERS-">Todos</option>
{foreach $AVAILABLE_CUSTOMERS_DATA as $availableCustomerData}
										<option value="{$availableCustomerData.id};{$availableCustomerData.email}"{if (in_array ($availableCustomerData.id, $newsSharingEntityIds))} style="display: none;"{/if}>{$availableCustomerData.fullname}</option>
{/foreach}
									</select>
								</div>
								<div class="form-group col-xs-12 col-md-1 columns-actions">
									<div class="vertical-group">
										<button type="button" class="btn btn-primary btn-icon center-block" onclick="NewsUtils.addAllSharingItems (this);"><i class="fa fa-angle-double-right"></i></button>
										<button type="button" class="btn btn-primary btn-icon center-block" onclick="NewsUtils.addSelectedSharingItems (this);"><i class="fa fa-angle-right"></i></button>
										<button type="button" class="btn btn-warning btn-icon center-block" onclick="NewsUtils.removeSelectedSharingItems (this);"><i class="fa fa-angle-left"></i></button>
										<button type="button" class="btn btn-warning btn-icon center-block" onclick="NewsUtils.removeAllSharingItems (this);"><i class="fa fa-angle-double-left"></i></button>
									</div>
								</div>
								<div class="form-group col-xs-12 col-md-5">
									<label for="selected-customers">Clientes seleccionados</label>
									<select id="selected-customers" name="sharing[CUSTOMERS][]" class="form-control selected-items" multiple="multiple">
										<option value="-ALL-CUSTOMERS-" disabled="disabled" style="display: none;">Todos</option>
{foreach $AVAILABLE_CUSTOMERS_DATA as $availableCustomerData}
										<option value="{$availableCustomerData.id};{$availableCustomerData.email}"{if (!in_array ($availableCustomerData.id, $newsSharingEntityIds))} disabled="disabled" style="display: none;"{/if}>{$availableCustomerData.fullname}</option>
{/foreach}
									</select>
								</div>
							</div>
						</div>
						<div class="col-xs-12 col-md-6">
							<div class="row sharing-items">
								<div class="label-input col-xs-12" style="text-align: left;">
									<label>Compartir con proveedores</label>
								</div>
								<div class="form-group col-xs-12 col-md-5">
									<label for="available-customers">Proveedores disponibles</label>
									<select id="available-customers" class="form-control available-items" multiple="multiple">
										<option value="-ALL-PROVIDERS-">Todos</option>
{foreach $AVAILABLE_PROVIDERS_DATA as $availableProviderData}
										<option value="{$availableProviderData.id};{$availableProviderData.email}"{if (in_array ($availableProviderData.id, $newsSharingEntityIds))} style="display: none;"{/if}>{$availableProviderData.fullname}</option>
{/foreach}
									</select>
								</div>
								<div class="form-group col-xs-12 col-md-1 columns-actions">
									<div class="vertical-group">
										<button type="button" class="btn btn-primary btn-icon center-block" onclick="NewsUtils.addAllSharingItems (this);"><i class="fa fa-angle-double-right"></i></button>
										<button type="button" class="btn btn-primary btn-icon center-block" onclick="NewsUtils.addSelectedSharingItems (this);"><i class="fa fa-angle-right"></i></button>
										<button type="button" class="btn btn-warning btn-icon center-block" onclick="NewsUtils.removeSelectedSharingItems (this);"><i class="fa fa-angle-left"></i></button>
										<button type="button" class="btn btn-warning btn-icon center-block" onclick="NewsUtils.removeAllSharingItems (this);"><i class="fa fa-angle-double-left"></i></button>
									</div>
								</div>
								<div class="form-group col-xs-12 col-md-5">
									<label for="selected-customers">Proveedores seleccionados</label>
									<select id="selected-customers" name="sharing[PROVIDERS][]" class="form-control selected-items" multiple="multiple">
										<option value="-ALL-PROVIDERS-" disabled="disabled" style="display: none;">Todos</option>
{foreach $AVAILABLE_PROVIDERS_DATA as $availableProviderData}
										<option value="{$availableProviderData.id};{$availableProviderData.email}"{if (!in_array ($availableProviderData.id, $newsSharingEntityIds))} disabled="disabled" style="display: none;"{/if}>{$availableProviderData.fullname}</option>
{/foreach}
									</select>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</form>
<script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.es.js"></script>
<script type="text/javascript" src="themes/centaurus/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript" src="include/ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="modules/News/news-utils.js"></script>
{/strip}