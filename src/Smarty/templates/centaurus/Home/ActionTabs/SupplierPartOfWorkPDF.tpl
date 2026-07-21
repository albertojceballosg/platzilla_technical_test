<h2 style="text-align: center; font-size: 18px; font-weight: bold; margin-bottom: 10px;">Parte de Trabajo - Proveedor</h2>

<table style="width: 100%; font-size: 10px; margin-bottom: 15px;" cellpadding="0">
    <tr>
        <td style="text-align: left;"><strong>Período:</strong> {$PERIOD_DATES['startdate']} al {$PERIOD_DATES['enddate']}</td>
        <td style="text-align: right;"><strong>Emitido:</strong> {$EMISSION_DATE} &nbsp; <strong>Por:</strong> {$CURRENT_USER}</td>
    </tr>
</table>

{if $SUPPLIER_INFO neq NULL}
<table style="width: 100%; background-color: #f8f9fa; border: 1px solid #adb5bd; margin-bottom: 8px;" cellpadding="15">
    <tr>
        <td style="padding: 15px 10px;">
            <span style="color: #17a2b8; font-size: 13px; font-weight: bold;">Proveedor: {$SUPPLIER_INFO.supplier_name}</span><br/><br/>
            <span style="font-size: 10px;">
                {if $SUPPLIER_INFO.nombre_de_la_sociedad neq ''}
                    <strong>Razón Social:</strong> {$SUPPLIER_INFO.nombre_de_la_sociedad} &nbsp;&nbsp;
                {/if}
                {if $SUPPLIER_INFO.telefono neq ''}
                    <strong>Teléfono:</strong> {$SUPPLIER_INFO.telefono} &nbsp;&nbsp;
                {/if}
                {if $SUPPLIER_INFO.email neq ''}
                    <strong>Email:</strong> {$SUPPLIER_INFO.email}
                {/if}
            </span>
        </td>
    </tr>
</table>
{/if}

{assign var="lastProjectId" value=""}
{if $GROUPED_DATA neq NULL}
    {foreach $GROUPED_DATA as $group}
        {if $group.project_id neq $lastProjectId && $lastProjectId neq ""}
        <pagebreak />
        {/if}
        {assign var="lastProjectId" value=$group.project_id}
        {if $group.project_name neq '' || $group.work_name neq ''}
        <table style="width: 100%; border: 1px solid #adb5bd; margin-top: 5px; margin-bottom: 12px;" cellpadding="0">
            {if $group.project_name neq ''}
            <tr>
                <td style="font-size: 12px; padding: 10px; border-bottom: {if $group.work_name neq ''}1px solid #dee2e6{else}none{/if};"><strong>Proyecto:</strong> {$group.project_name}</td>
            </tr>
            {/if}
            {if $group.work_name neq ''}
            <tr>
                <td style="font-size: 11px; padding: 10px 10px 10px 25px;"><strong>Trabajo:</strong> {$group.work_name}</td>
            </tr>
            {/if}
        </table>
        {/if}
        
        {foreach $group.tasks as $task}
        <table style="width: 100%; border-collapse: collapse; font-size: 10px; margin-top: 10px; margin-bottom: 20px;" border="1" bordercolor="#adb5bd" cellpadding="0" cellspacing="0">
            <tr style="background-color: #f1f1f1;">
                <th style="width: 40%; text-align: left; padding: 10px 12px; border: 1px solid #adb5bd;">Tarea / Actividad</th>
                <th style="width: 15%; text-align: center; padding: 10px; border: 1px solid #adb5bd;">Estado</th>
                <th style="width: 20%; text-align: center; padding: 10px; border: 1px solid #adb5bd;">Fecha Est. inicio</th>
                <th style="width: 25%; text-align: center; padding: 10px; border: 1px solid #adb5bd;">Fecha vencimiento</th>
            </tr>
            <tr>
                <td style="padding: 10px 12px; border: 1px solid #adb5bd;">{$task.subject}</td>
                <td style="text-align: center; padding: 10px; border: 1px solid #adb5bd;">{$task.eventstatus}</td>
                <td style="text-align: center; padding: 10px; border: 1px solid #adb5bd;">{$task.date_start}</td>
                <td style="text-align: center; padding: 10px; border: 1px solid #adb5bd;">{$task.due_date}</td>
            </tr>
            <tr>
                <td colspan="4" style="font-weight: bold; padding: 6px 10px; border: 1px solid #adb5bd; font-size: 10px;">PARTE DE TRABAJO</td>
            </tr>
            <tr style="font-size: 9px;">
                <th style="padding: 6px; border: 1px solid #adb5bd;">Tiempo</th>
                <th style="padding: 6px; border: 1px solid #adb5bd;">Materiales</th>
                <th style="padding: 6px; border: 1px solid #adb5bd;">Observaciones</th>
                <th style="padding: 6px; border: 1px solid #adb5bd;">Firma</th>
            </tr>
            <tr style="font-size: 9px;">
                <td style="height: 50px; border: 1px solid #adb5bd;">&nbsp;</td>
                <td style="height: 50px; border: 1px solid #adb5bd;">&nbsp;</td>
                <td style="height: 50px; border: 1px solid #adb5bd;">&nbsp;</td>
                <td style="height: 50px; border: 1px solid #adb5bd;">&nbsp;</td>
            </tr>
        </table>
        {/foreach}
        
    {/foreach}
{else}
    <p style="text-align: center; padding: 20px; color: #999;">
        No hay tareas asignadas a este proveedor en el período seleccionado.
    </p>
{/if}
