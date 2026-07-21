{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}
<form action="javascript:void(0);" method="POST" style="display:inline;">
<!--span class="dvHeaderText" id="settings_mail_fldrname">{'JSLBL_Settings'|@getTranslatedString}</span-->
<div class="mm_outerborder" id="settings_email_con" name="settings_email_con">
    <input type='hidden' id="selected_servername" value="{$SERVERNAME}" >
    <input type='hidden' id="return_module" value="{if $RETURN_MODULE eq 'undefined'}webmail{else}{$RETURN_MODULE}{/if}" >
    <input type='hidden' id="return_parenttab" value="{if $RETURN_PARENTTAB eq 'undefined'}{else}{$RETURN_PARENTTAB}{/if}" >


    <table width="100%" cellpadding=5 cellspacing=0 border=0 class="table table-striped table-hover" style='clear: both;'>
        <tr>
            <td width="15%">{'LBL_SELECT_ACCOUNT_TYPE'|@getTranslatedString}</td>
            <td>
                <select id="_mbox_helper" class="form-control" onchange="MailManager.handle_settings_confighelper(this);">
                    <option value=''>{'JSLBL_Choose_Server_Type'|@getTranslatedString:'MailManager'}</option>
                    <option value='gmail' {if $SERVERNAME eq 'gmail'} selected {/if}>{'JSLBL_Gmail'|@getTranslatedString:'MailManager'}</option>
                    <option value='outlook' {if $SERVERNAME eq 'outlook'} selected {/if}>{'JSLBL_Outlook'|@getTranslatedString:'MailManager'}</option>
						  <option value='yahoo' {if $SERVERNAME eq 'yahoo'} selected {/if}>{'JSLBL_Yahoo'|@getTranslatedString:'MailManager'}</option>
						  <option value='timemanagement' {if $SERVERNAME eq 'timemanagement'} selected {/if}>{'JSLBL_TimeManagement'|@getTranslatedString:'MailManager'}</option>
                    <option value='other' {if $SERVERNAME eq 'other'} selected {/if}>{'JSLBL_Other'|@getTranslatedString:'MailManager'}</option>
                </select>
            </td>
        </tr>
    </table>
    
    <div id="settings_details" {if $SERVERNAME neq ''} style="display:block;" {else} style="display:none;"{/if}>
        <table width="100%" cellpadding=5 cellspacing=0 border=0 class="table table-striped table-hover" style='clear: both;'>
            <tr>
                <td width="15%" nowrap="nowrap"><font color="red">*</font>{'LBL_Mail_Server'|@getTranslatedString}</td>
                <td>
                    <input name="_mbox_server" value="{$MAILBOX->server()}" type="text" style="width: 60%" class="form-control" placeholder="{'LBL_Like'|@getTranslatedString}, mail.company.com or 192.168.10.20">
                </td>
            </tr>

            <tr>
                <td width="15%" nowrap="nowrap"><font color="red">*</font>{'LBL_Username'|@getTranslatedString}</td>
                <td>
                    <input name="_mbox_user" id="_mbox_user" value="{$MAILBOX->username()}" type="text" style="width: 60%" class="form-control" placeholder="{'LBL_Your_Mailbox_Account'|@getTranslatedString}">
                </td>
            </tr>

            <tr>
                <td width="15%" nowrap="nowrap"><font color="red">*</font>{'LBL_Password'|@getTranslatedString}</td>
                <td>
                    <input name="_mbox_pwd" id="_mbox_pwd" value="{$MAILBOX->password()}" type="password" style="width: 60%" class="form-control" placeholder="{'LBL_Account_Password'|@getTranslatedString}">
                </td>
            </tr>
        </table>
        
        <div id="additional_settings" {if $SERVERNAME eq 'other'} style="display:block;"{else} style="display:none;" {/if}>
        <table width="100%" cellpadding=5 cellspacing=0 border=0 class="table table-striped table-hover" style='clear: both;'>
            <tr>
                <td width="15%" nowrap="nowrap">{'LBL_Protocol'|@getTranslatedString}</td>
                <td>
                    <input type="radio" name="_mbox_protocol" value="IMAP2" {if strcasecmp($MAILBOX->protocol(), 'imap2')===0}checked=true{/if}> {'LBL_Imap2'|@getTranslatedString}
                    <input type="radio" name="_mbox_protocol" value="IMAP4" {if strcasecmp($MAILBOX->protocol(), 'imap4')===0}checked=true{/if}> {'LBL_Imap4'|@getTranslatedString}
                </td>
            </tr>

            <tr>
                <td width="15%" nowrap="nowrap">{'LBL_SSL_Options'|@getTranslatedString}</td>
                <td>
                    <input type="radio" name="_mbox_ssltype" value="notls" {if strcasecmp($MAILBOX->ssltype(), 'notls')===0}checked=true{/if}> {'LBL_No_TLS'|@getTranslatedString}
                    <input type="radio" name="_mbox_ssltype" value="tls" {if strcasecmp($MAILBOX->ssltype(), 'tls')===0}checked=true{/if}> {'LBL_TLS'|@getTranslatedString}
                    <input type="radio"name="_mbox_ssltype" value="ssl" {if strcasecmp($MAILBOX->ssltype(), 'ssl')===0}checked=true{/if}> {'LBL_SSL'|@getTranslatedString}
                </td>
            </tr>

            <tr>
                <td width="15%" nowrap="nowrap">{'LBL_Certificate_Validations'|@getTranslatedString}</td>
                <td>
                    <input type="radio" name="_mbox_certvalidate" value="validate-cert" {if strcasecmp($MAILBOX->certvalidate(), 'validate-cert')===0}checked=true{/if} > {'LBL_Validate_Cert'|@getTranslatedString}
                    <input type="radio" name="_mbox_certvalidate" value="novalidate-cert" {if strcasecmp($MAILBOX->certvalidate(), 'novalidate-cert')===0}checked=true{/if}> {'LBL_Do_Not_Validate_Cert'|@getTranslatedString}
                </td>
            </tr>
				<tr>
                <td width="15%" nowrap="nowrap">{'LBL_OutgoingServer'|@getTranslatedString}</td>
                <td>
                    <input name="_mbox_outgoingserver" value="{$MAILBOX->outgoingserver()}" type="text" style="width: 60%" class="form-control" placeholder="{'LBL_Like'|@getTranslatedString}, smtp.company.com">
                </td>
            </tr>
				<tr>
                <td width="15%" nowrap="nowrap">{'LBL_OutgoingServerPort'|@getTranslatedString}</td>
                <td>
                    <input name="_mbox_outgoingserverport" value="{$MAILBOX->outgoingserverport()}" type="text" style="width: 60%" class="form-control" placeholder="{'LBL_Like'|@getTranslatedString}, 25 or 465 or 587">
                </td>
            </tr>
				<tr>
                <td width="15%" nowrap="nowrap">{'LBL_OutgoingServerProtocol'|@getTranslatedString}</td>
                <td>
                    <select name="_mbox_outgoingprotocol" id="_mbox_outgoingprotocol" class="form-control">
                    <option value=''>{'LBL_No_TLS'|@getTranslatedString}</option>
                    <option value='tls' {if $MAILBOX->outgoingserverprotocol() eq 'tls'} selected{/if}>{'LBL_TLS'|@getTranslatedString}</option>
                    <option value='ssl' {if $MAILBOX->outgoingserverprotocol() eq 'ssl'} selected{/if}>{'LBL_SSL'|@getTranslatedString}</option>
                </select>
                </td>
            </tr>
            </table>
        </div>
        <table width="100%" cellpadding=5 cellspacing=0 border=0 class="table table-striped table-hover" style='clear: both;'>
            <tr style="{$REFRESH_DISPLAY}">
                <td width="15%" nowrap="nowrap">{'LBL_REFRESH_TIME'|@getTranslatedString}</td>
                <td>
                    <select name="_mbox_refresh_timeout" class="form-control">
                        <option value="0" {if $MAILBOX->refreshTimeOut() eq ''}selected{/if}>{$MOD.LBL_NONE}</option>
                        <option value="300000" {if strcasecmp($MAILBOX->refreshTimeOut(), '300000')==0}selected{/if}>{$MOD.LBL_5_MIN}</option>
                        <option value="600000" {if strcasecmp($MAILBOX->refreshTimeOut(), '600000')==0}selected{/if}>{$MOD.LBL_10_MIN}</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td width="15%">&nbsp;</td>
                <td colspan="2" class="text-right">
                    <input type="button" class="btn btn-primary btn-md" value="{'LBL_SAVE_BUTTON_LABEL'|@getTranslatedString}" onclick="MailManager.save_settings(this.form);" >
                    {if $MAILBOX && $MAILBOX->exists()}
                        <input type="button" class="btn btn-warning btn-md" onclick="MailManager.close_settings();" value="{'LBL_CANCEL_BUTTON_LABEL'|@getTranslatedString}" >
                        <input type="button" class="btn btn-error btn-md" onclick="MailManager.remove_settings(this.form);" value="{'LBL_Remove'|@getTranslatedString}" >
                    {/if}
                </td>
            </tr>
        </table>
    </div>
</form>
</div>