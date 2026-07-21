{strip}
    {math equation= rand() assign= "idOKRs"}
    <div class="row">
        {if (!empty ($OBJECTIVES))}
            <fieldset>
                <legend style="display: none">{$MOD['NAV_STEP1']}</legend>
                <div class="col-xs-12">
                    <div class="row data-section">
                        {* Company area *}
                        <div id="dv-companytype" class="col-md-12">
                            <label for="companytype">{$MOD['WIZARD_LABEL_COMPAY_AREA']}:</label>
                            <div class="form-group field-container">
                                <div class="input-group" style="width: 100%;">
                                    {if (!empty ($COMPANY_AREAS))}
                                        <select id="companytype-{$idOKRs}" name="companytype"
                                                onclick="OKRsUtils.selectedArea(this, '{$idOKRs}')"
                                                class="form-control">
                                            <option value="">Seleccionar área</option>
                                            {foreach $COMPANY_AREAS as $areas}
                                                <option value="{$areas}">{$MOD[$areas]}</option>
                                            {/foreach}
                                        </select>
                                        <span id="sp-companytype" class="help-block"></span>
                                    {/if}
                                </div>
                            </div>
                        </div>
                        {* /Company area *}
                        {* objectives *}
                        <div id="dv-objectivesid" class="col-md-12">
                            <label for="objectivesid">{$MOD['WIZARD_LABEL_OBJECTIVES']}:</label>
                            <div class="form-group field-container">
                                <div class="input-group" style="width: 100%;">
                                    {if (!empty ($OBJECTIVES))}
                                        <select id="objectivesid-{$idOKRs}" name="objectivesid[]"
                                                class="form-control"
                                                onchange="OKRsUtils.selectedObjective (this, '{$idOKRs}')">
                                            <option value="">Seleccionar objetivo</option>
                                            {foreach  $COMPANY_AREAS as $area}
                                                <optgroup id="{$area}" class='hide' label="{$MOD[$area]}">
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
                        <div id="objective-data-{$idOKRs}" class="col-md-12 hide">
                            <div class="row">
                                {* How to do *}
                                <div id="dv-howtodo" class="col-md-8">
                                    <label for="howtodo">{$MOD['WIZARD_LABEL_HOW_TODO']}:</label>
                                    <div class="form-group field-container">
                                        <div class="input-group" style="width: 100%;">
                                            <textarea name="howtodo" id="howtodo-{$idOKRs}"
                                                      readonly class="form-control" rows="3">

                                            </textarea>
                                        </div>
                                    </div>
                                </div>
                                {* /How to do *}
                                {* frequency *}
                                <div id="dv-frequency" class="col-md-4">
                                    <label for="frequency">{$MOD['WIZARD_LABEL_FREQUENCY']}:</label>
                                    <div class="form-group field-container">
                                        <div class="input-group" style="width: 100%;">
                                            {if (!empty ($FREQUENCY))}
                                                <select id="frequency-{$idOKRs}" name="frequency"
                                                        class="form-control">
                                                    <option value="">Seleccionar frecuencia</option>
                                                    {foreach $FREQUENCY as $freq}
                                                        <option value="{$freq}">{$MOD[$freq]}</option>
                                                    {/foreach}
                                                </select>
                                                <span id="sp-frequency" class="help-block"></span>
                                            {/if}
                                        </div>
                                    </div>
                                </div>
                                {* /frequency *}
                                {* key results *}
                                <div class="col-xs-12">
                                    <div class="row">
                                        <div id="dv-keyresultsid" class="col-md-8">
                                            <label for="keyresultsid">{$MOD['WIZARD_LABEL_KEY_RESULT']}:</label>
                                            <div class="form-group field-container">
                                                <div class="input-group" style="width: 100%;">
                                                    {if (!empty ($KEY_RESULTS))}
                                                        <select multiple id="keyresultsid-{$idOKRs}"
                                                                name="keyresultsid[]"
                                                                class="form-control"
                                                                onchange="OKRsUtils.selectedKeyResult (this, '{$idOKRs}')"
                                                                size="10">
                                                            {foreach  $COMPANY_AREAS as $area}
                                                                {foreach $KEY_RESULTS as $keyResult}
                                                                    {if $keyResult->getCompanyArea() neq $area}{continue}{/if}
                                                                    {if !in_array($keyResult->getObjectiveId(), $OBJECTIVE_IDS)}{continue}{/if}
                                                                    <option class="hide"
                                                                            data-objective="{$keyResult->getObjectiveId()}"
                                                                            value="{$keyResult->getId()}">{$keyResult->getDescription()}</option>
                                                                {/foreach}
                                                            {/foreach}
                                                        </select>
                                                        <span id="sp-keyresultsid" class="help-block"></span>
                                                    {/if}
                                                </div>
                                            </div>
                                        </div>
                                        {* /key results *}
                                        <div class="col-md-4">
                                            <div class="row">
                                                <div class="col-xs-12">
                                                    <label for="description">{$MOD['WIZARD_LABEL_KR_DESCRIPTION']}:</label>
                                                    <div class="form-group field-container">
                                                        <div class="input-group" style="width: 100%;">
                                                        <textarea name="description" id="description-{$idOKRs}"
                                                                  readonly class="form-control" rows="3">
                                                        </textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12">
                                                    <label for="goal">{$MOD['WIZARD_LABEL_KR_GOAL']}:</label>
                                                    <div class="form-group field-container">
                                                        <div class="input-group" style="width: 100%;">
                                                        <input type="text" name="goal" id="goal-{$idOKRs}"
                                                                  readonly class="form-control" >
                                                        </div>
                                                    </div>
                                                    <ul  id="pager-{$idOKRs}" class="pager hide">
                                                        <li><a href="#" onclick="OKRsUtils.prevKeyResult (this, event, '{$idOKRs}')">Anterior</a></li>&nbsp;
                                                        <li><a href="#" onclick="OKRsUtils.nextdKeyResult (this, event, '{$idOKRs}')">Siguiente</a></li>
                                                    </ul>
                                                </div>
                                                <div  id="pagerhhh-{$idOKRs}" class="col-xs-12 hide">

                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="row">

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
    <script type="text/javascript">
        OKRsUtils.objectiveData({$DATA_OBJECTIVES});
        OKRsUtils.keyResultData({$DATA_KEY_RESULTS});
    </script>
{/strip}