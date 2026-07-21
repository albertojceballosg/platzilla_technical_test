	<div {$STYLEBK}>
		<div {$STYLE}>
			<input type="hidden" name="notificacionid" id="notificacionid" value="{$NOTIFICACIONID}"/>
			<input type="hidden" name="conversacionid" id="conversacionid" value="{$CONVERSATIONID}"/>
			<input type="hidden" name="subject" id="subject" value="{$SUBJECT}"/>
			<input type="hidden" name="accountid" id="accountid" value="{$ACCOUNTID}"/>
			<input type="hidden" name="ticketid" id="ticketid" value="{$TICKETID}"/>
			<div class="row form-group">
				<label for="s2id_autogen1" class="col-md-2">{$MOD.LBL_ACCOUNT_NAME}</label>
				<div class="col-md-4">
					{$ACCOUNTNAME}
				</div>
				<label for="s2id_autogen1" class="col-md-2">{$MOD.LBL_DATETIME_NOTIFICATION}</label>
				<div class="col-md-4">
					{$DATETIME}
				</div>
			</div>
			<div class="row form-group">
				<label for="s2id_autogen1" class="col-md-2">{$MOD.LBL_SEND_BY}</label>
				<div class="col-md-4">
					{$SENDBY}
				</div>
				<label for="s2id_autogen1" class="col-md-2">{$MOD.LBL_ASSOCIATED_RECORD}</label>
				<div class="col-md-4">
					{$ASSOCIATED_RECORD}
				</div>
			</div>
			<div class="row form-group">
				<label for="s2id_autogen1" class="col-md-2">{$MOD.LBL_SUBJECT}</label>
				<div class="col-md-10">
					{$SUBJECT}
				</div>
			</div>
			<div class="row form-group">				
				<label for="s2id_autogen1" class="col-md-2">{$MOD.LBL_DOCUMENTATION}</label>
				<div class="col-md-10">
					{$DOCUMENTATION}
				</div>
			</div>
		</div>
		<div {$STYLE}>
			<header>
			<h4>{$MOD.LBL_MESSAGE_DETAIL}</h4>
			</header>
			<div class="row form-group">
			{$MESSAGE}
			</div>
		</div>
		<hr></hr>
	</div>
	