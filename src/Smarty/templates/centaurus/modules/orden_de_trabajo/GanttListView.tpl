{*
 * GanttListView.tpl - Vista Gantt para ListView de orden_de_trabajo
 * 
 * Muestra trabajos planificados con jerarquía:
 * - Nivel 1: Proyecto (si existe)
 * - Nivel 2: Etapa del proyecto (si existe)
 * - Nivel 3: Trabajo (orden_de_trabajo)
 * - Nivel 4: Tareas del trabajo
 * 
 * Trabajos sin proyecto se muestran directamente en nivel 3
 *}

<link rel="stylesheet" href="themes/{$THEME}/js/gantt/frappe-gantt.css">
<link rel="stylesheet" href="themes/{$THEME}/css/gantt-scroll-fix.css">
<script src="themes/{$THEME}/js/gantt/frappe-gantt.js"></script>

<style>
    .gantt-listview-container {
        padding: 15px;
        background: #fff;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    .gantt-listview-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }
    .gantt-listview-title {
        font-size: 18px;
        font-weight: 600;
        color: #333;
    }
    .gantt-listview-controls {
        display: flex;
        gap: 10px;
        align-items: center;
    }
    .gantt-scale-select {
        padding: 6px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        background: #fff;
    }
    .gantt-chart-wrapper {
        overflow-x: auto;
        background: #F6F6F6;
        border: 1px solid #ECECEC;
        border-radius: 4px;
    }
    .gantt-chart-wrapper::-webkit-scrollbar {
        height: 12px;
    }
    .gantt-chart-wrapper::-webkit-scrollbar-track {
        background: #F6F6F6;
        border-radius: 6px;
    }
    .gantt-chart-wrapper::-webkit-scrollbar-thumb {
        background: #d2e7f5;
        border-radius: 6px;
        border: 2px solid #F6F6F6;
    }
    .gantt-empty-message {
        padding: 40px;
        text-align: center;
        color: #888;
        font-size: 14px;
    }
    .gantt-legend {
        display: flex;
        gap: 20px;
        margin-top: 15px;
        padding: 10px;
        background: #f9f9f9;
        border-radius: 4px;
        font-size: 12px;
    }
    .gantt-legend-item {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .gantt-legend-color {
        width: 16px;
        height: 16px;
        border-radius: 3px;
    }
    .legend-level-1 { background: #FF9900; }
    .legend-level-2 { background: #f2ece5; border: 1px solid #ddd; }
    .legend-level-3 { background: #e74c3c; }
    .legend-level-4 { background: #3498db; }
</style>

<div class="gantt-listview-container">
    <div class="gantt-listview-header">
        <div class="gantt-listview-title">
            <i class="fa fa-bar-chart"></i> {$GANTT_VIEW_NAME|default:'Trabajos planificados'}
        </div>
        <div class="gantt-listview-controls">
            <select class="gantt-scale-select" id="gantt-scale-selector" onchange="changeGanttScale(this.value)">
                <option value="Month" selected>Escala: Mes</option>
                <option value="Quarter Day">Cuarto de día</option>
                <option value="Half Day">Medio día</option>
                <option value="Day">Día</option>
                <option value="Week">Semana</option>
                <option value="Year">Año</option>
            </select>
            <button class="btn btn-default btn-sm" onclick="window.location.reload()">
                <i class="fa fa-refresh"></i> Actualizar
            </button>
            <a href="index.php?module=orden_de_trabajo&action=ListView" class="btn btn-default btn-sm">
                <i class="fa fa-list"></i> Volver a Lista
            </a>
        </div>
    </div>
    
    {if $GANTT_TASKS && count($GANTT_TASKS) > 0}
        <div class="gantt-chart-wrapper" id="gantt-chart-wrapper">
            <div id="gantt-target"></div>
        </div>
        
        <div class="gantt-legend">
            <div class="gantt-legend-item">
                <div class="gantt-legend-color legend-level-1"></div>
                <span>Proyecto</span>
            </div>
            <div class="gantt-legend-item">
                <div class="gantt-legend-color legend-level-2"></div>
                <span>Etapa</span>
            </div>
            <div class="gantt-legend-item">
                <div class="gantt-legend-color legend-level-3"></div>
                <span>Trabajo</span>
            </div>
            <div class="gantt-legend-item">
                <div class="gantt-legend-color legend-level-4"></div>
                <span>Tarea</span>
            </div>
        </div>
    {else}
        <div class="gantt-empty-message">
            <i class="fa fa-calendar-o fa-3x" style="color: #ddd; margin-bottom: 15px;"></i>
            <p>No hay trabajos con fechas planificadas para mostrar en el Gantt.</p>
            <p>Asegúrate de que los trabajos tengan configuradas las fechas de inicio y fin.</p>
        </div>
    {/if}
</div>

{if $GANTT_TASKS && count($GANTT_TASKS) > 0}
<script>
    var isRefresh = false;
    var rawTasks = {$GANTT_TASKS_JSON};
    var myTasks = [];
    
    console.log('[GanttListView] Inicializando con', rawTasks ? rawTasks.length : 0, 'elementos');
    
    {literal}
    // =====================================================================
    // Normalizar tareas y calcular fechas de padres
    // =====================================================================
    (function() {
        var discardedTasks = [];
        
        // Función para calcular fechas de padres basándose en sus hijos
        function calculateParentDatesFromChildren(tasks) {
            if (!Array.isArray(tasks) || tasks.length === 0) return;
            
            var childrenByParent = {};
            tasks.forEach(function(t) {
                if (t && t.dependencies) {
                    var deps = Array.isArray(t.dependencies) ? t.dependencies : [t.dependencies];
                    deps.forEach(function(parentId) {
                        if (parentId && parentId !== '') {
                            if (!childrenByParent[parentId]) {
                                childrenByParent[parentId] = [];
                            }
                            childrenByParent[parentId].push(t);
                        }
                    });
                }
            });
            
            function getDescendantDates(taskId, visited) {
                if (!visited) visited = {};
                if (visited[taskId]) return { starts: [], ends: [] };
                visited[taskId] = true;
                
                var dates = { starts: [], ends: [] };
                var children = childrenByParent[taskId] || [];
                
                children.forEach(function(child) {
                    if (child.start) {
                        var startDate = new Date(child.start);
                        if (!isNaN(startDate.getTime())) {
                            dates.starts.push(startDate);
                        }
                    }
                    if (child.end) {
                        var endDate = new Date(child.end);
                        if (!isNaN(endDate.getTime())) {
                            dates.ends.push(endDate);
                        }
                    }
                    var childDates = getDescendantDates(child.id, visited);
                    dates.starts = dates.starts.concat(childDates.starts);
                    dates.ends = dates.ends.concat(childDates.ends);
                });
                
                return dates;
            }
            
            tasks.forEach(function(t) {
                if (!t) return;
                
                var descendantDates = getDescendantDates(t.id, {});
                if (descendantDates.starts.length === 0 && descendantDates.ends.length === 0) return;
                
                if (descendantDates.starts.length > 0) {
                    var minStart = descendantDates.starts.reduce(function(min, d) {
                        return d < min ? d : min;
                    });
                    var currentStart = t.start ? new Date(t.start) : null;
                    if (!currentStart || isNaN(currentStart.getTime()) || minStart < currentStart) {
                        t.start = minStart.getFullYear() + '-' + 
                                  String(minStart.getMonth() + 1).padStart(2, '0') + '-' + 
                                  String(minStart.getDate()).padStart(2, '0');
                    }
                }
                
                var allEndDates = descendantDates.ends.length > 0 ? descendantDates.ends : descendantDates.starts;
                if (allEndDates.length > 0) {
                    var maxEnd = allEndDates.reduce(function(max, d) {
                        return d > max ? d : max;
                    });
                    var currentEnd = t.end ? new Date(t.end) : null;
                    if (!currentEnd || isNaN(currentEnd.getTime()) || maxEnd > currentEnd) {
                        t.end = maxEnd.getFullYear() + '-' + 
                                String(maxEnd.getMonth() + 1).padStart(2, '0') + '-' + 
                                String(maxEnd.getDate()).padStart(2, '0');
                    }
                }
            });
        }
        
        if (Array.isArray(rawTasks)) {
            // Normalizar formato de fechas
            rawTasks.forEach(function(t) {
                if (!t) return;
                
                var hasStart = !!t.start;
                var hasEnd = !!t.end;
                
                if (hasStart) t.start = String(t.start).trim();
                if (hasEnd) t.end = String(t.end).trim();
                
                // Normalizar formato: dd-mm-yyyy o dd/mm/yyyy a yyyy-mm-dd
                var datePatternDash = /^(\d{1,2})-(\d{1,2})-(\d{4})$/;
                var datePatternSlash = /^(\d{1,2})\/(\d{1,2})\/(\d{4})$/;
                
                if (hasStart) {
                    var matchDash = t.start.match(datePatternDash);
                    var matchSlash = t.start.match(datePatternSlash);
                    if (matchDash) {
                        t.start = matchDash[3] + '-' + matchDash[2].padStart(2,'0') + '-' + matchDash[1].padStart(2,'0');
                    } else if (matchSlash) {
                        t.start = matchSlash[3] + '-' + matchSlash[2].padStart(2,'0') + '-' + matchSlash[1].padStart(2,'0');
                    }
                }
                if (hasEnd) {
                    var matchDash = t.end.match(datePatternDash);
                    var matchSlash = t.end.match(datePatternSlash);
                    if (matchDash) {
                        t.end = matchDash[3] + '-' + matchDash[2].padStart(2,'0') + '-' + matchDash[1].padStart(2,'0');
                    } else if (matchSlash) {
                        t.end = matchSlash[3] + '-' + matchSlash[2].padStart(2,'0') + '-' + matchSlash[1].padStart(2,'0');
                    }
                }
            });
            
            // Calcular fechas de padres
            calculateParentDatesFromChildren(rawTasks);
            
            // Validar y agregar tareas
            rawTasks.forEach(function(t) {
                if (!t) return;
                
                var s = t.start ? new Date(t.start) : null;
                var e = t.end ? new Date(t.end) : null;
                
                if (!s || isNaN(s.getTime())) {
                    discardedTasks.push({ reason: 'start inválido', task: t });
                    console.warn('[GanttListView] Elemento descartado - start inválido:', t.name, t.start);
                    return;
                }
                
                if (!e || isNaN(e.getTime())) {
                    t.end = t.start;
                    e = new Date(t.end);
                }
                
                if (e < s) {
                    var tmp = t.start;
                    t.start = t.end;
                    t.end = tmp;
                }
                
                myTasks.push(t);
            });
        }
        
        if (discardedTasks.length > 0) {
            console.warn('[GanttListView] Elementos descartados:', discardedTasks);
        }
        console.log('[GanttListView] Elementos válidos:', myTasks.length);
    })();
    
    // Función para recalcular fechas de padres
    function recalculateParentDatesForGantt(tasks) {
        if (!Array.isArray(tasks) || tasks.length === 0) return;
        
        var childrenByParent = {};
        tasks.forEach(function(t) {
            if (t && t.dependencies) {
                var deps = Array.isArray(t.dependencies) ? t.dependencies : [t.dependencies];
                deps.forEach(function(parentId) {
                    if (parentId && parentId !== '') {
                        if (!childrenByParent[parentId]) {
                            childrenByParent[parentId] = [];
                        }
                        childrenByParent[parentId].push(t);
                    }
                });
            }
        });
        
        function getDescendantDates(taskId, visited) {
            if (!visited) visited = {};
            if (visited[taskId]) return { starts: [], ends: [] };
            visited[taskId] = true;
            
            var dates = { starts: [], ends: [] };
            var children = childrenByParent[taskId] || [];
            
            children.forEach(function(child) {
                if (child.start) {
                    var startDate = new Date(child.start);
                    if (!isNaN(startDate.getTime())) {
                        dates.starts.push(startDate);
                    }
                }
                if (child.end) {
                    var endDate = new Date(child.end);
                    if (!isNaN(endDate.getTime())) {
                        dates.ends.push(endDate);
                    }
                }
                var childDates = getDescendantDates(child.id, visited);
                dates.starts = dates.starts.concat(childDates.starts);
                dates.ends = dates.ends.concat(childDates.ends);
            });
            
            return dates;
        }
        
        tasks.forEach(function(t) {
            if (!t) return;
            
            var descendantDates = getDescendantDates(t.id, {});
            if (descendantDates.starts.length === 0 && descendantDates.ends.length === 0) return;
            
            if (descendantDates.starts.length > 0) {
                var minStart = descendantDates.starts.reduce(function(min, d) {
                    return d < min ? d : min;
                });
                t.start = minStart.getFullYear() + '-' + 
                          String(minStart.getMonth() + 1).padStart(2, '0') + '-' + 
                          String(minStart.getDate()).padStart(2, '0');
            }
            
            var allEndDates = descendantDates.ends.length > 0 ? descendantDates.ends : descendantDates.starts;
            if (allEndDates.length > 0) {
                var maxEnd = allEndDates.reduce(function(max, d) {
                    return d > max ? d : max;
                });
                t.end = maxEnd.getFullYear() + '-' + 
                        String(maxEnd.getMonth() + 1).padStart(2, '0') + '-' + 
                        String(maxEnd.getDate()).padStart(2, '0');
            }
        });
    }
    
    // Inicializar Gantt
    var gantt_chart = null;
    if (myTasks.length > 0) {
        gantt_chart = new Gantt("#gantt-target", myTasks, {
            on_click: function(task) {
                // Abrir registro al hacer clic
                var taskId = String(task.id);
                var module = task.relModule || 'orden_de_trabajo';
                var recordId = null;
                
                if (taskId.indexOf('project-') === 0) {
                    recordId = taskId.replace('project-', '');
                    module = 'proyectos';
                } else if (taskId.indexOf('stage-') === 0) {
                    // Las etapas no tienen registro individual
                    return;
                } else if (taskId.indexOf('@') !== -1) {
                    recordId = taskId.split('@')[0];
                    module = 'orden_de_trabajo';
                } else if (!isNaN(parseInt(taskId))) {
                    recordId = taskId;
                }
                
                if (recordId) {
                    window.open('index.php?module=' + module + '&action=DetailView&record=' + recordId, '_blank');
                }
            },
            on_date_change: function(task, start, end) {
                if (isRefresh) return false;
                
                var taskId = String(task.id);
                var moduleName = task.relModule;
                var options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                
                // Solo permitir edición de trabajos (nivel 3) y tareas (nivel 4)
                if (taskId.indexOf('project-') === 0 || taskId.indexOf('stage-') === 0) {
                    alert('Las fechas de proyectos y etapas se calculan automáticamente basándose en sus trabajos.');
                    isRefresh = true;
                    gantt_chart.refresh(myTasks);
                    setTimeout(function() { isRefresh = false; }, 2000);
                    return false;
                }
                
                var dummyStartDate = task._start.toLocaleDateString('en-US').split('/');
                var dummyEndDate = task._end.toLocaleDateString('en-US').split('/');
                var newStart = dummyStartDate[2] + '-' + dummyStartDate[0].padStart(2,'0') + '-' + dummyStartDate[1].padStart(2,'0');
                var newEnd = dummyEndDate[2] + '-' + dummyEndDate[0].padStart(2,'0') + '-' + dummyEndDate[1].padStart(2,'0');
                
                var info = '¿Estás seguro que quieres cambiar las fechas?\n';
                info += 'Elemento: ' + task.name + '\n';
                info += 'Inicio: ' + start.toLocaleDateString(undefined, options) + '\n';
                info += 'Fin: ' + end.toLocaleDateString(undefined, options);
                
                if (!confirm(info)) {
                    isRefresh = true;
                    gantt_chart.refresh(myTasks);
                    setTimeout(function() { isRefresh = false; }, 2000);
                    return false;
                }
                
                // Actualizar fechas localmente
                task.start = newStart;
                task.end = newEnd;
                
                // Determinar función y datos según el tipo
                var ajaxFunction, ajaxData;
                
                if (moduleName === 'Calendar') {
                    // Tarea (nivel 4)
                    ajaxFunction = 'CHANGE-DATES-TASK';
                    ajaxData = [{ id: parseInt(taskId), start: newStart, end: newEnd }];
                } else if (moduleName === 'orden_de_trabajo' && taskId.indexOf('@') !== -1) {
                    // Trabajo (nivel 3)
                    ajaxFunction = 'CHANGE-DATES-JOB';
                    ajaxData = [{ id: taskId, start: newStart, end: newEnd }];
                } else {
                    alert('No se puede actualizar este elemento');
                    return false;
                }
                
                jQuery.post('index.php', {
                    'module': moduleName === 'Calendar' ? 'Calendar' : 'orden_de_trabajo',
                    'action': 'AjaxDetailViewUtils',
                    'function': ajaxFunction,
                    'taskData': moduleName === 'Calendar' ? ajaxData : undefined,
                    'jobData': moduleName !== 'Calendar' ? ajaxData : undefined,
                    'Ajax': 'true'
                }, function(data) {
                    try {
                        var message = (typeof data === 'string') ? JSON.parse(data) : data;
                        if (!message || message.error !== 'OK') {
                            throw (message && message.error) ? message.error : 'Error al actualizar';
                        }
                        // Recalcular fechas de padres y refrescar
                        recalculateParentDatesForGantt(myTasks);
                        isRefresh = true;
                        gantt_chart.refresh(myTasks);
                        setTimeout(function() { isRefresh = false; }, 2000);
                        alert('Actualizado correctamente');
                    } catch (e) {
                        alert(e || 'Error desconocido');
                    }
                });
            },
            on_progress_change: function(task, progress) {
                // Por ahora solo mostrar mensaje
                alert('Cambio de progreso no implementado en esta vista');
                isRefresh = true;
                gantt_chart.refresh(myTasks);
                setTimeout(function() { isRefresh = false; }, 2000);
                return false;
            },
            view_mode: 'Month',
            language: 'es',
            column_width: 32
        });
    }
    
    function changeGanttScale(scale) {
        if (gantt_chart) {
            // Ajustar column_width según la escala
            if (scale === 'Week') {
                gantt_chart.options.column_width = 20;
            } else {
                gantt_chart.options.column_width = 32;
            }
            gantt_chart.change_view_mode(scale);
        }
    }
    {/literal}
</script>
{/if}
