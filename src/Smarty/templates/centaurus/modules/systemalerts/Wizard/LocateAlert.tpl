{strip}
    {math equation= rand() assign= "idAlertLocation"}
    <fieldset name="alert-location" id="{$idAlertLocation}">
        <input type="hidden" id="scaledatarel" name="scaledatarel"
               value="{if $DETAIL_ALERT neq NULL}{$DETAIL_ALERT.scale}{/if}">
        <div class="row">
            <div class="col-xs-12 col-md-12 col-lg-12">
                <div class="col-xs-6 col-md-6 col-lg-6">
                    {* alert location *}
                    {*$DETAIL_ALERT|var_dump*}
                    <div class="form-group" id="div-codeApp">
                        <label for="colorbase">{$MODSTRING.AREA}</label>
                        <select class="form-control" id="codeApp" name="codeApp"
                                onchange="SystemAlertUtils.selectApp(this)"
                                title="{$MODSTRING.AREA}">
                            <option value="">{$MODSTRING.LBL_SELECTION_APP}</option>
                            {foreach $APPLICATIONS as $keyApp => $itemApp}
                                {if $keyApp == $DETAIL_ALERT.code_app}
                                    {assign var='selected' value='selected="selected"'}
                                {else}
                                    {assign var='selected' value=''}
                                {/if}
                                {if $itemApp.app_name != $LABEL_ALL_APLICATIONS}
                                    <option value="{$keyApp}" {$selected}>{$itemApp}</option>
                                {/if}
                            {/foreach}
                        </select>
                        <span id="sp-codeApp" class="help-block" style="color: red"></span>
                    </div>
                </div>
                <div class="col-xs-6 col-md-6 col-lg-6">
                    {* looking for  *}
                    <div class="form-group" id="div-assigned_user_id"
                         {if $IS_INSTANCIA && $IS_ADMIN eq 'off'}style="display: none"{/if}>
                        <label for="colorbase">{$MODSTRING.LBL_ALERT_USER}</label>
                        <select id="assigned_user_id" name="assigned_user_id" class="form-control"
                                title="{$MODSTRING.LBL_ALERT_USER}">
                            {$USER_OWER}
                        </select>
                        <span id="sp-assigned_user_id" class="help-block" style="color: red"></span>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-md-12 col-lg-12">
                <div class="col-xs-6 col-md-6 col-lg-6">
                    {*Alert name or title.*}
                    <div id="div-titleAlert" class="form-group">
                        <label for="colorbase">{$MODSTRING.LBL_ALERT_TITLE}</label>
                        <input type="text" title="Titulo de la alerta" name="titleAlert" id="titleAlert"
                               class="form-control"
                               value="{$DETAIL_ALERT.alert}">
                        <span id="sp-titleAlert" class="help-block" style="color: red"></span>
                    </div>
                </div>
                <div class="col-xs-6 col-md-6 col-lg-6">
                    {* Alert status *}
                    <div class="form-group" id="div-status">
                        <label for="colorbase">{$MODSTRING.LBL_ALERT_ACTIVE}</label>
                        <select id="status" name="status" class="form-control" title="{$MODSTRING.LBL_ALERT_ACTIVE}">
                            <option value="1"
                                    {if $DETAIL_ALERT.status == '1'}selected="selected"{/if}>{$MODSTRING.ENABLED}</option>
                            <option value="0"
                                    {if $DETAIL_ALERT.status == '0'}selected="selected"{/if}>{$MODSTRING.DISABLED}</option>
                        </select>
                        <span id="sp-status" class="help-block" style="color: red"></span>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-md-12 col-lg-12">
                <div id="div-titleAlert" class="form-group">
                    <label for="colorbase">{$MODSTRING.LBL_ALERT_DESCRIPTION}</label>
                    <textarea class="form-control" name="description"  rows="3" placeholder="Breve descripción de la alerta">{$DETAIL_ALERT.description}</textarea>
                </div>

            </div>
        </div>
    </fieldset>
{/strip}