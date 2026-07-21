{strip}
{block name="css"}
<link type="text/css" rel="stylesheet" href="modules/instancesdatasharing/instancesdatasharing.css" />
<link type="text/css" rel="stylesheet" href="themes/centaurus/css/compiled/pipeline.css" />
{/block}
{block name="js"}
<script type="text/javascript" src="include/js/reflection.js"></script>
<script type="text/javascript" src="include/scriptaculous/scriptaculous.js"></script>
<script type="text/javascript" src="include/js/dtlviewajax.js"></script>
{/block}
{block name="messages"}
    {if (!empty ($NOTIFICATIONS))}
        {foreach $NOTIFICATIONS as $index => $notification}
            {if $index >= 1}
                {$notification->getContents ()|regex_replace:"/__ID__/":$notification->getId ()|regex_replace:"/__COLLAPSE_IN__/":'collapse'|regex_replace:"/__HIDDEN__/":'hidden'|unescape:"html"}
            {else}
                {$notification->getContents ()|regex_replace:"/__ID__/":$notification->getId ()|regex_replace:"/__COLLAPSE_IN__/":'collapse'|regex_replace:"/__HIDDEN__/":''|unescape:"html"}
            {/if}

        {/foreach}
		<script type="text/javascript">
            (function (jQuery) {
                jQuery ('.notification').on ('closed.bs.alert', function () {
                    jQuery ('.notification.hidden:first').removeClass ('hidden');
                    var notificationId = jQuery (this).attr ('data-id'),
                        arguments = [
                            'module=notifications',
                            'action=Disable',
                            'record=' + encodeURIComponent (notificationId),
                            'Ajax=true'
                        ];
                    jQuery.ajax ('index.php', {
                        data: arguments.join ('&'),
                        dataType: 'text',
                        method: 'post'
                    }).done (function (responseText) {
                        jQuery ('.notification.hidden:first').removeClass ('hidden');
                    });
                });
            } (jQuery));
		</script>
    {/if}
{/block}
{include file="Buttons_List.tpl"}
{if (isset ($MESSAGE))}
<div class="alert alert-{if (!$IS_ERROR)}success{else}danger{/if}">
	<i class="fa fa-{if (!$IS_ERROR)}check{else}times{/if}-circle fa-fw fa-lg"></i>
	<strong>{if (!$IS_ERROR)}Listo{else}Error{/if}!</strong> {$MESSAGE}
</div>
{/if}
<div class="tabs-wrapper">
	<ul class="nav nav-tabs">
		<li class="active">
			<a data-toggle="tab" href="#tab-detail">{$APP.LBL_REGISTER}</a>
		</li>
{block name="navigation-tabs"}
	{if isset($COL_ACCIONES) && $COL_ACCIONES neq 'false'}
		{include file='DetailViewActions.tpl'}
	{/if}
		<li>
			<a data-toggle="tab" href="#tab-conversations">{$APP.LBL_CHAT}</a>
		</li>
		<li class="dropdown">
			<a class="dropdown-toggle" href="#" data-toggle="dropdown">{$APP.LBL_MORE} {$APP.LBL_INFORMATION}
				&nbsp;<span class="caret"></span>
			</a>
			<ul class="dropdown-menu" role="menu">
	{if !empty($IS_REL_LIST)}
		{foreach key=_RELATION_ID item=_RELATED_MODULE from=$IS_REL_LIST}
				<li><a role="menuitem" tabindex="-1" href="index.php?action=CallRelatedList&module={$MODULE}&record={$ID}&parenttab={$CATEGORY}&selected_header={$_RELATED_MODULE}&relation_id={$_RELATION_ID}">{$_RELATED_MODULE|@getTranslatedString:$MODULE}</a></li>
		{/foreach}
				<li class="divider"></li>
	{/if}
				<li><a role="menuitem" href="index.php?action=RecordHistory&module=historymanager&record={$ID}&parenttab={$CATEGORY}&formodule={$MODULE}">Histórico de Cambios</a></li>
			</ul>
		</li>
{/block}
	</ul>
{if (!empty ($ACTIVE_APPLICATIONS)) && (count ($ACTIVE_APPLICATIONS) > 1) && ($APPLICATION_VIEWS_ENABLED)}
	<div class="row block-container">
		<div class="col-xs-12">
			<div class="main-box" style="margin-bottom: 0;">
				<div class="main-box-body clearfix">
					<form action="index.php" method="get" class="form">
						<input type="hidden" name="module" value="{$MODULE}" />
						<input type="hidden" name="action" value="DetailView" />
	{if (isset ($CREATEMODE))}
						<input type="hidden" name="createmode" value="{$CREATEMODE}" />
	{/if}
	{if (isset ($DUPLICATE))}
						<input type="hidden" name="isDuplicate" value="{$DUPLICATE}" />
	{/if}
	{if (isset ($MODE))}
						<input type="hidden" name="mode" value="{$MODE}" />
	{/if}
	{if (isset ($ID))}
						<input type="hidden" name="record" value="{$ID}" />
	{/if}
	{if (isset ($RETURN_ACTION))}
						<input type="hidden" name="return_action" value="{$RETURN_ACTION}" />
	{/if}
	{if (isset ($RETURN_ID))}
						<input type="hidden" name="return_id" value="{$RETURN_ID}" />
	{/if}
	{if (isset ($RETURN_MODULE))}
						<input type="hidden" name="return_module" value="{$RETURN_MODULE}" />
	{/if}
	{if (isset ($RETURN_MODULE))}
						<input type="hidden" name="return_viewname" value="{$RETURN_VIEWNAME}" />
	{/if}
						<div class="form-group">
							<div class="col-xs-12">
								<select id="profileids" name="profileids" class="form-control" onchange="this.form.submit ();" title="Vista por aplicación">
									<option value="">Vista por aplicación</option>
	{foreach $ACTIVE_APPLICATIONS as $application}
									<option value="{$application.app_profile}"{if (!empty ($PROFILE_IDS)) && (in_array ($application.app_profile, $PROFILE_IDS))} selected="selected"{/if}>{$application.app_name}</option>
	{/foreach}
								</select>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
{/if}
{block name="content"}{/block}
</div>
{block name="content-extra"}{/block}
{block name="scripts"}
<script type="text/html" id="instances-data-sharing-share-modal-template">
{include file='modules/instancesdatasharing/ShareModal.tpl'}
</script>
<script type="text/javascript" src="modules/instancesdatasharing/data-sharing.js"></script>
{/block}
{$DLG_DETALLE_NOTIFICACION}
{$DLG_NUEVA_NOTIFICACION}
{/strip}