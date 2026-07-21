{if (isset ($CALENDAR_VIEWS)) && ($CALENDAR_VIEWS.totalRecords > 0)}
<optgroup label="Calendarios">
    {foreach $CALENDAR_VIEWS.records as $calendarView}
        <option value="{$calendarView.calendarviewid}">{$calendarView.label}</option>
    {/foreach}
</optgroup>
{else}
    <optgroup label="Calendario">
        <option value="">No hay calendarios disponibles</option>
    </optgroup>
{/if}