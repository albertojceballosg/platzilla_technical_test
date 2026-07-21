<tr>
    <td id="wd-div-workig-day_{$day->getWorkingDayName()}" aria-controls="table_list">
        <input type="text" class="form-control"
               name="working_days[{$day->getWorkingDayName()}]"
               value="{$day->getWorkingDayName()}"
               title="{$day->getWorkingDayName()}"
               id="workig-day_{$day->getWorkingDayName()}"
               placeholder="Día">
        <span id="wd-workig-day_{$day->getWorkingDayName()}" class="help-block" style="color: red;"></span>
    </td>
    <td id="wd-div-workig-hours_{$day->getWorkingDayName()}" aria-controls="table_list">
        <input type="text" class="form-control"
               id="workig-hours_{$day->getWorkingDayName()}"
               title="horas del día {$day->getWorkingDayName()}"
               name="working_days[{$day->getWorkingDayName()}][hours]"
               value="{$day->getWorkingHours()}"
               onkeydown="WorkingDayUtils.normalizeWorkingTime (this, event);"
               placeholder="Horas">
        <span id="wd-workig-hours_{$day->getWorkingDayName()}" class="help-block" style="color: red;"></span>
    </td>
    <td id="wd-div-mornig-init-hour_{$day->getWorkingDayName()}"  aria-controls="table_list" class="bootstrap-timepicker">
        <input type="text" class="form-control wd-timepicker"
               id="mornig-init-hour_{$day->getWorkingDayName()}"
               title="hora inicio del día {$day->getWorkingDayName()}"
               name="working_days[{$day->getWorkingDayName()}][gai]"
               value="{$day->getMorningStartTime()}"
               placeholder="HH:MM:SS">
        <span id="wd-mornig-init-hour_{$day->getWorkingDayName()}" class="help-block" style="color: red;"></span>
    </td>
    <td id="wd-div-mornig-end-hour_{$day->getWorkingDayName()}" aria-controls="table_list" class="bootstrap-timepicker">
        <input type="text" class="form-control wd-timepicker"
               id="mornig-end-hour_{$day->getWorkingDayName()}"
               title="hora fin del día {$day->getWorkingDayName()}"
               name="working_days[{$day->getWorkingDayName()}][gaf]"
               value="{$day->getMorningDueTime()}"
               placeholder="HH:MM:SS">
        <span id="wd-mornig-end-hour_{$day->getWorkingDayName()}" class="help-block" style="color: red;"></span>
    </td>
    <td id="wd-div-afternoon-init-hour_{$day->getWorkingDayName()}" aria-controls="table_list" class="bootstrap-timepicker">
        <input type="text" class="form-control wd-timepicker"
               id="afternoon-init-hour_{$day->getWorkingDayName()}"
               title="hora inicio del día {$day->getWorkingDayName()}"
               name="working_days[{$day->getWorkingDayName()}][gbi]"
               value="{$day->getAfternoonStartTime()}"
               placeholder="HH:MM:SS">
        <span id="wd-afternoon-init-hour_{$day->getWorkingDayName()}" class="help-block" style="color: red;"></span>
    </td>
    <td id="wd-div-afternoon-end-hour_{$day->getWorkingDayName()}" aria-controls="table_list" class="bootstrap-timepicker">
        <input type="text" class="form-control wd-timepicker"
               id="afternoon-end-hour_{$day->getWorkingDayName()}"
               title="hora fin del día {$day->getWorkingDayName()}"
               name="working_days[{$day->getWorkingDayName()}][gbf]"
               value="{$day->getAfternoonDueTime()}"
               placeholder="HH:MM:SS">
        <span id="wd-afternoon-end-hour_{$day->getWorkingDayName()}" class="help-block" style="color: red;"></span>
    </td>
    <td><button type="button" onclick="WorkingDayUtils.removeDay(this, '{$day->getWorkingDayName()}')" class="btn btn-danger">
            <i class="fa fa-trash-o"></i>
        </button></td>
</tr>