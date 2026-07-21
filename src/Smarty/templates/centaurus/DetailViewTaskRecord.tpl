{if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
    <div class="row">
        <div class="alert alert-danger">
            <strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
        </div>
    </div>
{/if}
<div class="row">
    {if $TASK neq NULL}
    <div class="col-md-12">
        <p class="text-left small" style="margin-bottom: 1px; padding-bottom: 1px; font-weight: bold">Tarea:</p>
        <p class="text-justify small">{$TASK['subject']}</p>
    </div>
    {if {$TASK['description']} neq NULL}
    <div class="col-md-12">
        <p class="text-left small" style="margin-bottom: 1px; padding-bottom: 1px; font-weight: bold">Descripción:</p>
        {$TASK['description']}
    </div>
    {/if}
    <div class="col-md-12">
        <table class="table table-striped">
            <tbody>
            <tr>
                <td>&nbsp;</td>
                <td>Tipo de tarea:</td>
                <td>{$MOD[$TASK['activitytype']]}</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>Estado:</td>
                <td>{$MOD[$TASK['eventstatus']]}</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>Prioridad:</td>
                <td>{$TASK['taskpriority']}</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>Importancia:</td>
                <td>{if $TASK['importance'] neq NULL}
                    {if $TASK['importance'] eq 'HIGH'}Alta{else}Baja{/if}
                    {/if}
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>Fecha de inicio - Fin:</td>
                <td>{$TASK['date_start']} - {$TASK['due_date']}</td>
            </tr>
            {if $TASK['estimated_time'] neq NULL}
            <tr>
                <td>&nbsp;</td>
                <td>Duración estimada:</td>
                <td>{$TASK['estimated_time']}</td>
            </tr>
            {/if}
            {if $TASK['progress'] neq NULL && false}
                <tr>
                    <td>&nbsp;</td>
                    <td>% avance:</td>
                    <td>{$TASK['progress']}</td>
                </tr>
            {/if}
            </tbody>
        </table>
    </div>
    {/if}
    <div class="col-md-12">
        &nbsp;
    </div>
</div>