{strip}
    {math equation= rand() assign= "idCalendar"}
    <div class="col-lg-12">
        <div class="row">
            <div class="col-lg-12">
                <h1>Calendario de&nbsp;{$MOD[$MODULE]}</h1>
            </div>
        </div>
        {*   Header     *}
        {*$DATA|var_dump*}
        <div class="main-box no-header clearfix">
            <div class="main-box-body clearfix pull-right">
                {if (isset ($CALENDAR_VIEWS)) && ($CALENDAR_VIEWS neq NULL)}
                    {assign var="viewsModule" value=$MODULE}
                    {assign var="moduleLabel" value=$MOD[$MODULE]}
                    <div class="btn-group" style="margin-right: 5px;">
                        <button type="button" class="btn btn-default btn-sm" data-toggle="dropdown">Calendarios por módulos
                            &nbsp;<span class="caret"></span></button>
                        <ul class="dropdown-menu" role="menu">
                            {foreach $CALENDAR_VIEWS as $keyModule => $views}
                                <li class="{if $keyModule eq  $viewsModule}active{/if}">
                                    <a href="#"
                                       onclick="ActivityUtils.viewModule(event, this,'{$keyModule}', '{$idCalendar}')">{$views[0]['tablabel']}</a>
                                </li>
                            {/foreach}
                        </ul>
                    </div>
                    <div class="btn-group" style="margin-right: 5px;">
                        <button id="btn-{$idCalendar}" type="button" class="btn btn-info btn-sm" data-toggle="dropdown">
                            Vistas de {$moduleLabel}&nbsp;<span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu" role="menu" id="rules-{$idCalendar}">
                            <li>
                                <a href="index.php?module=Calendar&amp;action=index&amp;calendar_main=1" title="Ver calendario principal">Principal</a>
                            </li>
                            {foreach $CALENDAR_VIEWS as $keyModule => $views}
                                {foreach $views as $view}
                                    <li class="divider {$keyModule}-{$idCalendar} {if $keyModule neq  $viewsModule}hide{/if}"></li>
                                    <li class="list-btn-header {$keyModule}-{$idCalendar} {if $keyModule neq  $viewsModule}hide{/if}"
                                        title="{$view['tablabel']}" style="text-align: center!important;">
                                        <small>{$view['label']}</small>
                                    </li>
                                    <li class="divider {$keyModule}-{$idCalendar} {if $keyModule neq  $viewsModule}hide{/if}"></li>
                                    <li class="{$keyModule}-{$idCalendar} {if $keyModule neq  $viewsModule}hide{/if} {if ($view['calendarviewid'] eq  $VIEW_ID) && (empty($RULE_ID))}active{/if}">
                                        <a href="index.php?module={$keyModule}&amp;action=CalendarView&amp;record={$view['calendarviewid']}"
                                           title="Todos los registros sin reglas">Todos los registros&nbsp;
                                            de&nbsp;{$view['label']}</a></li>
                                    {foreach $view['rules'] as $rule}
                                        <li class="{$keyModule}-{$idCalendar} {if $keyModule neq  $viewsModule}hide{/if} {if $RULE_ID eq $rule.ruleId}active{/if}">
                                            <a href="index.php?module={$keyModule}&amp;action=CalendarView&amp;record={$view['calendarviewid']}&amp;rule={$rule.ruleId}"
                                               title="{$rule.title}">{$rule.option}</a></li>
                                    {/foreach}
                                {/foreach}
                            {/foreach}
                        </ul>
                    </div>
                {/if}
                <a href="index.php?module={$MODULE}&amp;action=EditView&amp;return_action=DetailView"
                   class="btn btn-primary btn-sm">Crear {$MOD["SINGLE_{$MODULE}"]}</a>
            </div>
        </div>
        {*   Calender     *}
        <div class="col-md-12">
            <div class="main-box">
                <!-- waraujo 29-01-25 -->
                <div class="main-box-body clearfix">
                    <div class="fc fc-ltr" id="calendar-{$idCalendar}"></div>
                </div>
            </div>
        </div>
    </div>{*
    <script type="text/javascript" src="themes/centaurus/js/jquery-ui.custom.min.js"></script>
    <script type="text/javascript" src="themes/centaurus/js/fullcalendar.js"></script>
    <script type="text/javascript" src="themes/centaurus/js/moment.min.js"></script>*}
{strip}
    <script type="text/javascript">
        {literal}
            jQuery(document).ready(function() {
                CalendarManager.init({
                    currentModule: '{/literal}{$MODULE}{literal}',
                    currentViewId: '{/literal}{$idCalendar}{literal}',
                    type: '{/literal}{$CALENDAR_TYPE}{literal}',
                    currentLangCode: 'es',
                    events: {/literal}{$DATA|json_encode}{literal}

                });
            });
        {/literal}

</script>
{/strip}
{strip}
<script type="text/javascript">
    {/strip}
    {literal}
    (function (jQuery) {
        var viewModule = function (e, obj, module, id) {
            var objA = jQuery(obj),
                btnViews = jQuery('#btn-' + id),
                moduleRows = objA.parent().parent(),
                viewRows = jQuery('#rules-' + id + ' li'),
                activeClass = module + '-' + id;
            btnViews.html('Vistas de ' + objA.html() + '&nbsp;<span class="caret"></span>');
            jQuery('.' + activeClass).removeClass('hide');

            viewRows.each(function (i) {
                var li = jQuery(this);
                if (!li.hasClass(activeClass) && i > 0) {
                    li.addClass('hide')
                }
            });

            moduleRows.find('li').each(function () {
                var li = jQuery(this);
                li.removeClass('active');
            });
            objA.parent().addClass('active');
            //btnViews.trigger('click');
            e.preventDefault();
            btnViews.parent().addClass('open');
        };

        window.ActivityUtils = {
            viewModule: viewModule
        };
    }(jQuery));
    {/literal}
    {strip}
</script>
{/strip}