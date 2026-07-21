<tr>
    <td id="wd-div-workig-day_{$day['picklistvalue']}" aria-controls="table_list">
        <input type="text" class="form-control"
               name="working_days[{$day['picklistvalue']}]"
               value="{$day['picklistvalue']}"
               title="{$day['picklistvalue']}"
               id="workig-day_{$day['picklistvalue']}"
               placeholder="Día">
        <span id="wd-workig-day_{$day['picklistvalue']}" class="help-block" style="color: red;"></span>
    </td>
    <td id="wd-div-workig-hours_{$day['picklistvalue']}" aria-controls="table_list">
        <input type="text" class="form-control"
               id="workig-hours_{$day['picklistvalue']}"
               title="horas del día {$day['picklistvalue']}"
               name="working_days[{$day['picklistvalue']}][hours]"
               onkeydown="WorkingDayUtils.normalizeWorkingTime (this, event);"
               placeholder="Horas">
        <span id="wd-workig-hours_{$day['picklistvalue']}" class="help-block" style="color: red;"></span>
    </td>
    <td id="wd-div-mornig-init-hour_{$day['picklistvalue']}"  aria-controls="table_list" class="bootstrap-timepicker">
        <input type="text" class="form-control wd-timepicker{if isset($timeClass)} {$timeClass}{/if}"
               id="mornig-init-hour_{$day['picklistvalue']}"
               title="hora inicio del día {$day['picklistvalue']}"
               name="working_days[{$day['picklistvalue']}][gai]"
               value=""
               placeholder="HH:MM:SS">
        <span id="wd-mornig-init-hour_{$day['picklistvalue']}" class="help-block" style="color: red;"></span>
    </td>
    <td id="wd-div-mornig-end-hour_{$day['picklistvalue']}" aria-controls="table_list" class="bootstrap-timepicker">
        <input type="text" class="form-control wd-timepicker{if isset($timeClass)} {$timeClass}{/if}"
               id="mornig-end-hour_{$day['picklistvalue']}"
               title="hora fin del día {$day['picklistvalue']}"
               name="working_days[{$day['picklistvalue']}][gaf]"
               value=""
               placeholder="HH:MM:SS">
        <span id="wd-mornig-end-hour_{$day['picklistvalue']}" class="help-block" style="color: red;"></span>
    </td>
    <td id="wd-div-afternoon-init-hour_{$day['picklistvalue']}" aria-controls="table_list" class="bootstrap-timepicker">
        <input type="text" class="form-control wd-timepicker{if isset($timeClass)} {$timeClass}{/if}"
               id="afternoon-init-hour_{$day['picklistvalue']}"
               title="hora inicio del día {$day['picklistvalue']}"
               name="working_days[{$day['picklistvalue']}][gbi]"
               value=""
               placeholder="HH:MM:SS">
        <span id="wd-afternoon-init-hour_{$day['picklistvalue']}" class="help-block" style="color: red;"></span>
    </td>
    <td id="wd-div-afternoon-end-hour_{$day['picklistvalue']}" aria-controls="table_list" class="bootstrap-timepicker">
        <input type="text" class="form-control wd-timepicker{if isset($timeClass)} {$timeClass}{/if}"
               id="afternoon-end-hour_{$day['picklistvalue']}"
               title="hora fin del día {$day['picklistvalue']}"
               name="working_days[{$day['picklistvalue']}][gbf]"
               value=""
               placeholder="HH:MM:SS">
        <span id="wd-afternoon-end-hour_{$day['picklistvalue']}" class="help-block" style="color: red;"></span>
    </td>
    <td><button type="button" onclick="WorkingDayUtils.removeDay(this, '{$day['picklistvalue']}')" class="btn btn-danger">
            <i class="fa fa-trash-o"></i>
        </button></td>
</tr>