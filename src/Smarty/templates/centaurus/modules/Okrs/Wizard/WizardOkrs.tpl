{strip}
    <div class="row">
        {if (!empty ($OBJECTIVES))}
            <fieldset>
                <legend style="display: none">{$MOD['NAV_STEP1']}</legend>
                <div class="col-xs-12">
                    <div class="row data-section">
                        <div class="col-md-12">
                            <div class="row">
                                {* objectives *}
                                <div id="dv-objectivesid" class="col-md-6">
                                    <label for="objectivesid">{$MOD['WIZARD_LABEL_OBJECTIVES']}:</label>
                                    <div class="form-group field-container">
                                        <div class="input-group" style="width: 100%;">
                                            {if (!empty ($OBJECTIVES))}
                                                <select multiple id="objectivesid" name="objectivesid[]"
                                                        class="form-control"
                                                        onclick="OKRsUtils.selectedObjective(this)"
                                                        size="10">
                                                    {foreach  $COMPANY_AREAS as $area}
                                                        <optgroup label="{$MOD[$area]}">
                                                            {foreach $OBJECTIVES as $objective}
                                                                {if $objective->getCompanyArea() neq $area}{continue}{/if}
                                                                <option value="{$objective->getId()}">{$objective->getToDo()}</option>
                                                            {/foreach}
                                                        </optgroup>
                                                    {/foreach}
                                                </select>
                                                <span id="sp-objectivesid" class="help-block"></span>
                                            {/if}
                                        </div>
                                    </div>
                                </div>
                                {* /objectives *}
                                {* key results *}
                                <div id="dv-keyresultsid" class="col-md-6">
                                    <label for="keyresultsid">{$MOD['WIZARD_LABEL_KEY_RESULT']}:</label>
                                    <div class="form-group field-container">
                                        <div class="input-group" style="width: 100%;">
                                            {if (!empty ($KEY_RESULTS))}
                                                <select multiple id="keyresultsid" name="keyresultsid[]"
                                                        class="form-control"
                                                        size="10">
                                                    {foreach  $COMPANY_AREAS as $area}
                                                        <optgroup label="{$MOD[$area]}">
                                                            {foreach $KEY_RESULTS as $keyResult}
                                                                {if $keyResult->getCompanyArea() neq $area}{continue}{/if}
                                                                {if !in_array($keyResult->getObjectiveId(), $OBJECTIVE_IDS)}{continue}{/if}
                                                                <option data-objective="{$keyResult->getObjectiveId()}"
                                                                        value="{$keyResult->getId()}">{$keyResult->getDescription()}</option>
                                                            {/foreach}
                                                        </optgroup>
                                                    {/foreach}
                                                </select>
                                                <span id="sp-keyresultsid" class="help-block"></span>
                                            {/if}
                                        </div>
                                    </div>
                                </div>
                                {* /key results *}
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>
        {else}
            <div class="col-xs-12">
                <div class="alert alert-info">{$MOD['WIZARD_OBJECTIVE_ALERT']}</div>
            </div>
        {/if}
    </div>
{/strip}