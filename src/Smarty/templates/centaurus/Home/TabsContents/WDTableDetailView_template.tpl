<tr>
    <td id="wd-div-workig-day_{$day['picklistvalue']}" aria-controls="table_list">
        <span  class="form-control" style="overflow-x: hidden;width: 100%;">
            {$dayOfWeek->getWorkingDayName()}
        </span>
    </td>
    <td id="wd-div-workig-hours_{$dayOfWeek->getWorkingDayName()}" aria-controls="table_list">
        <span  class="form-control" style="overflow-x: hidden;width: 100%;">
            {$dayOfWeek->getWorkingHours()}
        </span>
    </td>
    <td id="wd-div-mornig-init-hour_{$dayOfWeek->getWorkingDayName()}"  aria-controls="table_list" class=" ">
        <span  class="form-control" style="overflow-x: hidden;width: 100%;">
            {$dayOfWeek->getMorningStartTime()}
        </span>
    </td>
    <td id="wd-div-mornig-end-hour_{$dayOfWeek->getWorkingDayName()}" aria-controls="table_list" class=" ">
        <span  class="form-control" style="overflow-x: hidden;width: 100%;">
            {$dayOfWeek->getMorningDueTime()}
        </span>
    </td>
    <td id="wd-div-afternoon-init-hour_{$dayOfWeek->getWorkingDayName()}" aria-controls="table_list" class=" ">
        <span  class="form-control" style="overflow-x: hidden;width: 100%;">
            {$dayOfWeek->getAfternoonStartTime()}
        </span>
    </td>
    <td id="wd-div-afternoon-end-hour_{$dayOfWeek->getWorkingDayName()}" aria-controls="table_list" class=" ">
        <span  class="form-control" style="overflow-x: hidden;width: 100%;">
            {$dayOfWeek->getAfternoonDueTime()}
        </span>
    </td>
</tr>