{strip}
    {math equation= rand() assign= "idAlertFilter"}
    <fieldset name="alert-filter" id="{$idAlertFilter}">
        <div class="row filters-section">
            <div class="col-xs-12 col-md-12 col-lg-12">
                <h4 class="pull-left">Filtros para alerta</h4>
                <div class="action-bar pull-right">
                    <button type="button" class="btn btn-success btn-icon" onclick="SystemAlertUtils.addFilterGroup ('{$idAlertFilter}');"
                            title="Agregar grupo de filtros">
                        <i class="fa fa-plus"></i></button>
                </div>
            </div>
            <div class="col-xs-12 col-md-12 col-lg-12 filter-groups">
                {if (!empty ($AVAILABLE_FILTER_ALERT))}
                    {foreach $AVAILABLE_FILTER_ALERT as $group}
                        {include file='modules/systemalerts/Wizard/SystemAlertFilterConditionGroup.tpl' GROUP=$group}
                    {/foreach}
                {/if}
            </div>
        </div>

    </fieldset>
    <script type = "text/html" id = "system-alert-filter-template" >
    {include file='modules/systemalerts/Wizard/SystemAlertFilterCondition.tpl'}
    </script>
    <script type="text/html" id="system-alert-filter-group-template">
        {include file='modules/systemalerts/Wizard/SystemAlertFilterConditionGroup.tpl'}
    </script>
    <script type="text/html" id="system-alert-date-field-template">
        {foreach $AVAILABLE_DATE_OPTION as $key => $value}
            <option value="{$key}">{$value}</option>
        {/foreach}
    </script>
{/strip}