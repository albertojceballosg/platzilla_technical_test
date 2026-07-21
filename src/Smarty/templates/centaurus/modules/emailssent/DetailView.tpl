{strip}
{extends file="DetailView.tpl"}
{block name="css"}{/block}
{block name="js"}
	<script type="text/javascript" src="include/js/dtlviewajax.js"></script>
{/block}
{block name="content-after-blocks"}
{if (isset ($MAILBOX)) && (isset ($MESSAGE_UID))}
	<div class="row">
		<div class="col-lg-12">
			<div class="main-box">
				<div class="main-box-body clearfix" style="padding-top: 20px;">
					<a href="/index.php?module=webmail&action=index&_task=mail&_mbox={$MAILBOX}&_uid={$MESSAGE_UID}&_action=show&Popup=true" target="_blank">Ver en cliente de correo</a>
				</div>
			</div>
		</div>
	</div>
{/if}
{/block}
{/strip}