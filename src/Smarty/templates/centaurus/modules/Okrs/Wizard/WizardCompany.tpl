{strip}
    <div class="row">
        <fieldset>
            <legend style="display: none">{$MOD['NAV_STEP1']}</legend>
            <div class="col-xs-12">
                <div class="row data-section">
                    <div class="col-md-12">
                        <div class="row">
                            {* company types *}
                            <div id="dv-companytype" class="col-md-6">
                                <label for="companytype">{$MOD['OBJECTIVE_LABEL_COMPAY_TYPE']}:</label>
                                <div class="form-group field-container">
                                    <div class="input-group" style="width: 100%;">
                                        {if (!empty ($COMPANY_TYPE))}
                                            <select id="companytype" name="companytype" class="form-control">
                                                <option value="">Seleccionar tipo de empresa</option>
                                                {foreach $COMPANY_TYPE as $type}
                                                    <option value="{$type}">{$MOD[$type]}</option>
                                                {/foreach}
                                            </select>
                                            <span id="sp-companytype" class="help-block"></span>
                                        {/if}
                                    </div>
                                </div>
                            </div>
                            {* /company types *}
                            {* company phase *}
                            <div id="dv-companyphase" class="col-md-6">
                                <label for="companyphase">{$MOD['OBJECTIVE_LABEL_COMPAY_PHASE']}:</label>
                                <div class="form-group field-container">
                                    <div class="input-group" style="width: 100%;">
                                        {if (!empty ($COMPANY_PHASE))}
                                            <select id="companyphase" name="companyphase" class="form-control">
                                                <option value="">Seleccionar etapa de la empresa</option>
                                                {foreach $COMPANY_PHASE as $phase}
                                                    <option value="{$phase}">{$MOD[$phase]}</option>
                                                {/foreach}
                                            </select>
                                            <span id="sp-companyphase" class="help-block"></span>
                                        {/if}
                                    </div>
                                </div>
                            </div>
                            {* /company phase *}
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>
    </div>
{/strip}