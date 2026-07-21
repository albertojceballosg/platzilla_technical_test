{math equation= rand() assign= "idGanttDiagram"}

{* Cargar CSS y JS de Frappe Gantt solo una vez *}
{if !isset($FRAPPE_GANTT_LOADED)}
    {assign var="FRAPPE_GANTT_LOADED" value=true scope="global"}
    <link rel="stylesheet" href="themes/centaurus/js/gantt/frappe-gantt.css" />
    <link rel="stylesheet" href="themes/centaurus/css/gantt-scroll-fix.css" />
    <script type="text/javascript" src="themes/centaurus/js/gantt/frappe-gantt.js"></script>
{/if}
<!-- Los estilos fueron movidos a la hoja de estilos themes/centaurus/js/gantt/frappe-gantt.css GGC 2025-11-25-->

<div class="row" id="task-gantt-{$idGanttDiagram}">
    <div id="scale-gannt-{$idGanttDiagram}" class="row" style="margin-bottom: 5px; margin-top: -5px;">
        <div class="col-md-12" style="margin-left: 9px">
            <div style="display: inline-flex; align-items: center; gap: 10px;">
                <select class="form-control" id="gantt-scale-{$idGanttDiagram}" title="Escala de visualización del diagrama Gantt" style="width: auto; min-width: 200px;">
                    <option value="" selected="">Mes (por defecto)</option>
                    <option value="Quarter Day">Cuarto de día</option>
                    <option value="Half Day">Medio día</option>
                    <option value="Day">Día</option>
                    <option value="Week">Semana</option>
                    {*<option value="Month">Mes</option>*}
                    <option value="Year">Año</option>
                </select>
                <button type="button" class="btn btn-primary btn-gantt-print-diagram" 
                        onclick="GanttPrintUtils.printGantt('{$idGanttDiagram}')" 
                        title="Imprimir diagrama Gantt">
                    <i class="fa fa-print"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="col-md-12" style="padding: 0;">
        <div class="card-task-gantt" id="card-task-gantt-{$idGanttDiagram}">
            <div id="gantt-target-{$idGanttDiagram}" class="gantt-target"></div>
        </div>
    </div>
    <script>
    // IIFE para encapsular variables y evitar conflictos entre múltiples instancias de Gantt
    (function() {
        var ganttId          = '{$idGanttDiagram}',
            isTaskGroup      = false,
            isRefresh        = false,
            relatedModule    = '{$RELATED_MODULE}',
            taskGrouped      = [],
            totalInGroup     = 0,
            gantt_chart      = null;
        
        // Cargar tareas crudas desde el servidor
        var rawTasks = {$TASKS_GANTT};
        
        // Normalizar tareas: asegurar fechas válidas y que end >= start
        var myTask = [];
        {literal}
        (function() {
            var invalidTasks = [];
            var discardedTasks = [];
            
            // =====================================================================
            // PASO 1: Calcular fechas de padres sin fechas basándose en sus hijos
            // Si un registro (categoría, etapa, grupo) no tiene fechas pero tiene
            // hijos con fechas, tomar la fecha más antigua como inicio y la más
            // reciente como fin.
            // =====================================================================
            function calculateParentDatesFromChildren(tasks) {
                if (!Array.isArray(tasks) || tasks.length === 0) return;
                
                // Crear mapa de tareas por ID para búsqueda rápida
                var taskMap = {};
                tasks.forEach(function(t) {
                    if (t && t.id) {
                        taskMap[t.id] = t;
                    }
                });
                
                // Crear mapa de hijos por padre
                var childrenByParent = {};
                tasks.forEach(function(t) {
                    if (t && t.dependencies) {
                        // dependencies puede ser string o array
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
                
                // Función recursiva para obtener todas las fechas de descendientes
                function getDescendantDates(taskId, visited) {
                    if (!visited) visited = {};
                    if (visited[taskId]) return { starts: [], ends: [] };
                    visited[taskId] = true;
                    
                    var dates = { starts: [], ends: [] };
                    var children = childrenByParent[taskId] || [];
                    
                    children.forEach(function(child) {
                        // Agregar fechas del hijo si existen
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
                        
                        // Recursivamente obtener fechas de los descendientes del hijo
                        var childDates = getDescendantDates(child.id, visited);
                        dates.starts = dates.starts.concat(childDates.starts);
                        dates.ends = dates.ends.concat(childDates.ends);
                    });
                    
                    return dates;
                }
                
                // Procesar cada tarea que tenga hijos - recalcular fechas para cubrir todos los descendientes
                tasks.forEach(function(t) {
                    if (!t) return;
                    
                    // Obtener fechas de todos los descendientes
                    var descendantDates = getDescendantDates(t.id, {});
                    
                    // Si no tiene hijos con fechas, mantener las fechas originales
                    if (descendantDates.starts.length === 0 && descendantDates.ends.length === 0) return;
                    
                    // Calcular fecha de inicio (la más antigua de los descendientes)
                    if (descendantDates.starts.length > 0) {
                        var minStart = descendantDates.starts.reduce(function(min, d) {
                            return d < min ? d : min;
                        });
                        
                        // Si la tarea ya tiene fecha de inicio, usar la más antigua entre ambas
                        var currentStart = t.start ? new Date(t.start) : null;
                        if (!currentStart || isNaN(currentStart.getTime()) || minStart < currentStart) {
                            t.start = minStart.getFullYear() + '-' + 
                                      String(minStart.getMonth() + 1).padStart(2, '0') + '-' + 
                                      String(minStart.getDate()).padStart(2, '0');
                        }
                    }
                    
                    // Calcular fecha de fin (la más reciente de los descendientes)
                    var allEndDates = descendantDates.ends.length > 0 ? descendantDates.ends : descendantDates.starts;
                    if (allEndDates.length > 0) {
                        var maxEnd = allEndDates.reduce(function(max, d) {
                            return d > max ? d : max;
                        });
                        
                        // Si la tarea ya tiene fecha de fin, usar la más reciente entre ambas
                        var currentEnd = t.end ? new Date(t.end) : null;
                        if (!currentEnd || isNaN(currentEnd.getTime()) || maxEnd > currentEnd) {
                            t.end = maxEnd.getFullYear() + '-' + 
                                    String(maxEnd.getMonth() + 1).padStart(2, '0') + '-' + 
                                    String(maxEnd.getDate()).padStart(2, '0');
                        }
                    }
                });
            }
            
            // Ejecutar cálculo de fechas de padres
            if (Array.isArray(rawTasks)) {
                calculateParentDatesFromChildren(rawTasks);
            }
            
            // =====================================================================
            // PASO 2: Validar y normalizar cada tarea individualmente
            // =====================================================================
            if (Array.isArray(rawTasks)) {
                rawTasks.forEach(function(t) {
                    if (!t) {
                        return;
                    }

                    var hasStart = !!t.start;
                    var hasEnd = !!t.end;
                    
                    // Convertir a string y limpiar espacios
                    if (hasStart) t.start = String(t.start).trim();
                    if (hasEnd) t.end = String(t.end).trim();
                    
                    // Normalizar formato de fecha: convertir dd-mm-yyyy a yyyy-mm-dd si es necesario
                    // Patrón: dd-mm-yyyy o dd/mm/yyyy
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
                    
                    var s = hasStart ? new Date(t.start) : null;
                    var e = hasEnd ? new Date(t.end) : null;

                    // Si la fecha de inicio no es válida, descartamos la tarea
                    if (!s || isNaN(s.getTime())) {
                        discardedTasks.push({ reason: 'start inválido', task: t });
                        return;
                    }

                    // Si la fecha de fin no es válida o no existe, usamos la misma que inicio
                    if (!e || isNaN(e.getTime())) {
                        t.end = t.start;
                        e = new Date(t.end);
                    }

                    // Si end < start, intentar corregir intercambiando
                    if (e < s) {
                        invalidTasks.push({ before: { start: t.start, end: t.end } });
                        var tmp = t.start;
                        t.start = t.end;
                        t.end = tmp;
                        s = new Date(t.start);
                        e = new Date(t.end);

                        // Si sigue siendo inconsistente, descartamos la tarea
                        if (e < s) {
                            discardedTasks.push({ reason: 'end < start después de normalizar', task: t });
                            return;
                        }
                    }

                    myTask.push(t);
                });
            }
            
            // Resumen de tareas descartadas (sin mostrar cada una)
            if (discardedTasks.length > 0) {
                console.warn('[GanttDiagram ' + ganttId + '] Tareas descartadas: ' + discardedTasks.length);
            }
        })();
        {/literal}
        
        {literal}
        function setWidthBoard () {
            if (!gantt_chart || !gantt_chart.$svg) return false;
            // Obtener el ancho real del SVG generado por Frappe Gantt
            var ganttWidth = parseInt(gantt_chart.$svg.getAttribute('width'));
            var ganttContainer = jQuery('#card-task-gantt-' + ganttId);
            var containerWidth = ganttContainer.width();
            
            // Asegurar que el contenedor tenga el ancho suficiente
            if (ganttWidth > 0) {
                // Forzar que el gantt-target sea más ancho que el contenedor
                var targetWidth = Math.max(ganttWidth + 100, containerWidth + 500);
                
                jQuery('#gantt-target-' + ganttId).css({
                    "width": targetWidth + "px",
                    "min-width": targetWidth + "px",
                    "display": "block",
                    "overflow": "visible"
                });
                
                // Asegurar que el SVG también tenga el ancho correcto
                ganttContainer.find('.gantt-container').css({
                    "width": ganttWidth + "px",
                    "overflow": "visible"
                });
            }
            
            // Resetear scroll al inicio con animación
            setTimeout(function() {
                ganttContainer.animate({scrollLeft: 0}, 800);
            }, 100);
            
            return false;
        }
        {/literal}
        
        {literal}
        // Función para recalcular fechas de padres basándose en sus hijos
        function recalculateParentDates(tasks) {
            // Crear mapa de hijos por padre
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
            
            // Función recursiva para obtener fechas de descendientes
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
            
            // Recalcular fechas de cada padre
            tasks.forEach(function(t) {
                if (!t) return;
                
                var descendantDates = getDescendantDates(t.id, {});
                if (descendantDates.starts.length === 0 && descendantDates.ends.length === 0) return;
                
                // Calcular fecha de inicio (la más antigua)
                if (descendantDates.starts.length > 0) {
                    var minStart = descendantDates.starts.reduce(function(min, d) {
                        return d < min ? d : min;
                    });
                    t.start = minStart.getFullYear() + '-' + 
                              String(minStart.getMonth() + 1).padStart(2, '0') + '-' + 
                              String(minStart.getDate()).padStart(2, '0');
                }
                
                // Calcular fecha de fin (la más reciente)
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
        
        // =====================================================================
        // PASO 3: Calcular progreso ponderado para Etapas (nivel 2)
        // El progreso de una etapa se calcula como promedio ponderado del
        // progreso de sus trabajos hijos, usando la duración como peso.
        // =====================================================================
        function calcularProgresoEtapas(tasks) {
            // Crear mapa de tareas por ID
            var taskMap = {};
            tasks.forEach(function(t) {
                if (t && t.id) taskMap[t.id] = t;
            });
            
            // Crear mapa de hijos por padre
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
            
            // Identificar etapas (nivel 2) y calcular su progreso ponderado
            tasks.forEach(function(etapa) {
                if (!etapa || !etapa.custom_class) return;
                if (etapa.custom_class.indexOf('task-level-2') === -1 && 
                    etapa.custom_class.indexOf('task-stage') === -1) return;
                
                // Obtener trabajos hijos (nivel 3)
                var hijos = childrenByParent[etapa.id] || [];
                var trabajosHijos = hijos.filter(function(h) {
                    return h.custom_class && 
                           (h.custom_class.indexOf('task-level-3') !== -1 || 
                            h.custom_class.indexOf('task-job') !== -1);
                });
                
                if (trabajosHijos.length === 0) return;
                
                var sumaPonderada = 0;
                var sumaDuraciones = 0;
                
                trabajosHijos.forEach(function(trabajo) {
                    // Calcular duración en días
                    var startDate = new Date(trabajo.start);
                    var endDate = new Date(trabajo.end);
                    var duracionMs = endDate.getTime() - startDate.getTime();
                    var duracionDias = Math.max(1, duracionMs / (1000 * 60 * 60 * 24));
                    var progreso = trabajo.progress || 0;
                    
                    sumaPonderada += progreso * duracionDias;
                    sumaDuraciones += duracionDias;
                });
                
                // Asignar progreso ponderado a la etapa
                etapa.progress = sumaDuraciones > 0 
                    ? Math.round(sumaPonderada / sumaDuraciones) 
                    : 0;
            });
            
            return tasks;
        }
        
        // Ejecutar cálculo de progreso ponderado para etapas
        myTask = calcularProgresoEtapas(myTask);
        {/literal}
        
        try {
            gantt_chart = new Gantt ("#gantt-target-{$idGanttDiagram}", myTask, {
                on_click: function (task) {
                    // Click en tarea - sin log para reducir ruido en consola
                },
            on_date_change: function (task, start, end) {
                var info           = '',
                    options        = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' },
                    moduleName     = task.relModule;
                
                // Función para formatear fecha a YYYY-MM-DD con padding de ceros
                function formatDateToYMD(date) {
                    var year = date.getFullYear();
                    var month = String(date.getMonth() + 1).padStart(2, '0');
                    var day = String(date.getDate()).padStart(2, '0');
                    return year + '-' + month + '-' + day;
                }
                
                var formattedStart = formatDateToYMD(start),
                    formattedEnd   = formatDateToYMD(end);
                
                if (isRefresh) {
                    return false;
                }
                if (jQuery.isNumeric (task.id)) {
                    info = '¿Estás seguro que quieres cambiar las fechas a la tarea: ' + task.name + '?';
                    info += '\n Inicio: ' +  start.toLocaleDateString (undefined, options);
                    info += '\n Fin: ' +  end.toLocaleDateString (undefined, options);

                    if (!isTaskGroup) {
                        if (!confirm(info)) {
                            taskGrouped  = [];
                            totalInGroup = 0;
                            isTaskGroup  = false;
                            isRefresh    = true;
                            gantt_chart.refresh (myTask);
                            setTimeout (function(){ isRefresh = false;}, 5000);
                            return false;
                        }
                    }

                    task.start    = formattedStart;
                    task.end      = formattedEnd;
                    totalInGroup -= 1;

                    taskGrouped.push (task);
                    if (totalInGroup <= 0) {
                        arguments = {
                            'module':   moduleName,
                            'action':   'AjaxDetailViewUtils',
                            'function': 'CHANGE-DATES-TASK',
                            'taskData': taskGrouped,
                            'Ajax':     'true'
                        };
                        jQuery.post('index.php', arguments, function (data) {
                            try {
                                var message = JSON.parse(JSON.stringify(data));
                                if (message.error !== 'OK') {
                                    throw message.error;
                                } else if(isTaskGroup) {
                                    alert('Las tareas del grupo han sido actualizadas)')
                                } else {
                                    alert('La tarea ha sido actualizada')
                                }
                                
                                // Recalcular fechas de padres y refrescar el Gantt
                                recalculateParentDates(myTask);
                                isRefresh = true;
                                gantt_chart.refresh(myTask);
                                setTimeout(function(){ isRefresh = false; }, 2000);
                            }
                            catch (e) {
                                alert (e);
                            }
                        });
                        taskGrouped  = [];
                        totalInGroup = 0;
                        isTaskGroup  = false;
                    }
                } else {
                    info = '¿Estás seguro que quieres cambiar las fechas al grupo de tareas en: ' + task.name + '?';
                    info += '\n Inicio: ' +  start.toLocaleDateString (undefined, options);
                    info += '\n Fin: ' +  end.toLocaleDateString (undefined, options);
                    if (!confirm (info)) {
                        taskGrouped  = [];
                        totalInGroup = 0;
                        isRefresh    = true;
                        gantt_chart.refresh (myTask);
                        setTimeout (function(){ isRefresh = false;}, 5000);
                        return false;
                    }
                    task.start = formattedStart;
                    task.end   = formattedEnd;
                    isTaskGroup  = true;
                    totalInGroup = task.totalGroup;
                    taskGrouped  = [];
                }
            },
            on_progress_change: function(task, progress) {
                var oldProgress = task.actProgress;
                if (isRefresh) {
                    return false;
                }
                if (jQuery.isNumeric (task.id)) {
                    info = '¿Estás seguro que quieres cambiar el % de avance de la tarea: ' + task.name + '?';
                    info += '\n actual: ' +  task.progress;
                    info += '\n cambiar a: ' + progress;
                    if (!confirm(info)) {
                        task.progress = oldProgress;
                        isRefresh    = true;
                        gantt_chart.refresh (myTask);
                        setTimeout (function(){ isRefresh = false;}, 5000);
                        return false;
                    }
                    if (totalInGroup <= 0) {
                        arguments = {
                            'module':   encodeURIComponent (relatedModule),
                            'action':   'AjaxDetailViewUtils',
                            'function': 'CHANGE-PROGRESS-TASK',
                            'taskData': task,
                            'Ajax':     'true'
                        };
                        jQuery.post ('index.php', arguments, function (data) {
                            try {
                                var message = JSON.parse(JSON.stringify(data));
                                if (message.error !== 'OK') {
                                    throw message.error;
                                } else {
                                    alert('La tarea ha sido actualizada')
                                }
                            }
                            catch (e) {
                                alert (e);
                            }
                        });
                    }
                } else {
                    task.progress = oldProgress;
                    isRefresh    = true;
                    gantt_chart.refresh (myTask);
                    setTimeout (function(){ isRefresh = false;}, 5000);
                    return false;
                }

            },
            on_view_change: function(mode) {
                return false;
            },
            view_mode: 'Month',
            language: 'es',
            column_width: 32
        });
        
        } catch(error) {
            console.error('[GanttDiagram ' + ganttId + '] Error al inicializar Gantt:', error);
        }

        function setGanttScale (scale) {
            if (!gantt_chart) return;
            if (scale !== '') {
                gantt_chart.options.view_mode = scale;
                // Ajustar column_width según la escala
                if (scale === 'Week') {
                    gantt_chart.options.column_width = 20;
                } else {
                    gantt_chart.options.column_width = 32;
                }
            } else {
                gantt_chart.options.view_mode = 'Month';
                gantt_chart.options.column_width = 32;
            }
            gantt_chart.refresh (myTask);
            setTimeout(function() {
                setWidthBoard();
                if (typeof updateScrollbar === 'function') {
                    updateScrollbar();
                }
            }, 100);
        }

        // Ejecutar después de que el Gantt esté completamente renderizado
        setTimeout(function() {
            setWidthBoard();
            initFixedScrollbar();
        }, 200);

        // Usar selectores específicos para esta instancia
        var $ganttContainer = jQuery('#card-task-gantt-' + ganttId);
        
        $ganttContainer.find('.gantt').click(function (e) {
            e.stopPropagation ();
            e.preventDefault ();
            return false;
        });

        $ganttContainer.find('.gantt').mousemove(function (e) {
            e.stopPropagation ();
            e.preventDefault ();
            return false;
        });

        // ============================================
        // Barra de scroll horizontal fija
        // ============================================
        var updateScrollbar = null; // Referencia para actualizar desde setGanttScale
        
        function initFixedScrollbar() {
            var ganttContainer = document.getElementById('card-task-gantt-' + ganttId);
            var ganttSvg = ganttContainer ? ganttContainer.querySelector('svg.gantt') : null;
            
            if (!ganttContainer || !ganttSvg) {
                return;
            }
            
            // Remover barra existente si hay (usando ID único)
            var scrollbarId = 'gantt-fixed-scrollbar-' + ganttId;
            var existingBar = document.getElementById(scrollbarId);
            if (existingBar) {
                existingBar.remove();
            }
            
            // Crear barra de scroll fija
            var scrollbar = document.createElement('div');
            scrollbar.id = scrollbarId;
            scrollbar.className = 'gantt-fixed-scrollbar';
            
            var scrollbarInner = document.createElement('div');
            scrollbarInner.className = 'gantt-fixed-scrollbar-inner';
            scrollbarInner.style.width = ganttSvg.scrollWidth + 'px';
            
            scrollbar.appendChild(scrollbarInner);
            document.body.appendChild(scrollbar);
            
            // Habilitar scroll horizontal en el contenedor
            ganttContainer.style.overflowX = 'scroll';
            
            // Posicionar la barra con el mismo ancho y posición horizontal del contenedor
            function positionScrollbar() {
                var rect = ganttContainer.getBoundingClientRect();
                scrollbar.style.left = rect.left + 'px';
                scrollbar.style.width = (rect.width - 18) + 'px'; // Restar ancho del scrollbar vertical
            }
            positionScrollbar();
            
            // Sincronizar scroll: barra fija -> contenedor
            var isSyncing = false;
            scrollbar.addEventListener('scroll', function() {
                if (isSyncing) return;
                isSyncing = true;
                ganttContainer.scrollLeft = scrollbar.scrollLeft;
                isSyncing = false;
            });
            
            // Sincronizar scroll: contenedor -> barra fija
            ganttContainer.addEventListener('scroll', function() {
                if (isSyncing) return;
                isSyncing = true;
                scrollbar.scrollLeft = ganttContainer.scrollLeft;
                isSyncing = false;
            });
            
            // Actualizar ancho y posición de la barra
            updateScrollbar = function() {
                var svgWidth = ganttSvg.getBoundingClientRect().width;
                scrollbarInner.style.width = Math.max(svgWidth, ganttContainer.scrollWidth) + 'px';
                positionScrollbar();
                checkNativeScrollbarVisibility();
            };
            
            // Observar cambios en el tamaño
            if (window.ResizeObserver) {
                var resizeObserver = new ResizeObserver(updateScrollbar);
                resizeObserver.observe(ganttSvg);
                resizeObserver.observe(ganttContainer);
            }
            
            // Reposicionar al redimensionar ventana
            window.addEventListener('resize', positionScrollbar);
            
            // Ocultar barra fija si el scroll nativo del contenedor está visible
            function checkNativeScrollbarVisibility() {
                var rect = ganttContainer.getBoundingClientRect();
                var containerBottom = rect.bottom;
                var viewportHeight = window.innerHeight;
                var footerHeight = 30; // Altura aproximada del footer
                
                // Si la parte inferior del contenedor (donde está el scroll nativo) está visible
                if (containerBottom <= viewportHeight - footerHeight) {
                    scrollbar.style.display = 'none';
                } else {
                    scrollbar.style.display = 'block';
                    positionScrollbar();
                }
            }
            
            // Verificar visibilidad en scroll de la página
            window.addEventListener('scroll', checkNativeScrollbarVisibility);
            document.addEventListener('scroll', checkNativeScrollbarVisibility, true);
            
            // Verificar inicialmente
            checkNativeScrollbarVisibility();
        }
        
        // Vincular evento de cambio de escala al select específico de esta instancia
        jQuery('#gantt-scale-' + ganttId).on('change', function() {
            setGanttScale(jQuery(this).val());
        });
        
    })(); // Fin del IIFE
    
    // Utilidad global para imprimir Gantt (solo se define una vez)
    if (typeof GanttPrintUtils === 'undefined') {
        window.GanttPrintUtils = {
            printGantt: function(ganttId) {
                var ganttContainer = jQuery('#card-task-gantt-' + ganttId);
                var ganttSvg = ganttContainer.find('svg.gantt');
                
                if (ganttSvg.length === 0) {
                    alert('No hay diagrama Gantt para imprimir');
                    return;
                }
                
                // Obtener título del contexto
                var pageTitle = jQuery('.page-title h3').text() || jQuery('h3:first').text() || 'Diagrama Gantt';
                var viewTitle = jQuery('#gantt-module-view-selector option:selected').text() || 
                               jQuery('.breadcrumb li:last').text() || '';
                
                // Clonar el SVG
                var svgClone = ganttSvg[0].cloneNode(true);
                
                // Eliminar elementos innecesarios
                var handles = svgClone.querySelectorAll('.handle, .handle-group');
                handles.forEach(function(el) { el.remove(); });
                var arrows = svgClone.querySelectorAll('.arrow');
                arrows.forEach(function(el) { el.remove(); });
                
                // Copiar colores de las barras
                var originalBars = ganttSvg[0].querySelectorAll('.bar');
                var clonedBars = svgClone.querySelectorAll('.bar');
                for (var i = 0; i < originalBars.length && i < clonedBars.length; i++) {
                    var computedStyle = window.getComputedStyle(originalBars[i]);
                    if (computedStyle.fill) {
                        clonedBars[i].setAttribute('fill', computedStyle.fill);
                    }
                }
                
                // Copiar colores de progreso
                var originalProgress = ganttSvg[0].querySelectorAll('.bar-progress');
                var clonedProgress = svgClone.querySelectorAll('.bar-progress');
                for (var i = 0; i < originalProgress.length && i < clonedProgress.length; i++) {
                    var computedStyle = window.getComputedStyle(originalProgress[i]);
                    if (computedStyle.fill) {
                        clonedProgress[i].setAttribute('fill', computedStyle.fill);
                    }
                }
                
                // Aplicar estilos a textos
                var clonedLabels = svgClone.querySelectorAll('text, .bar-label');
                clonedLabels.forEach(function(label) {
                    label.setAttribute('fill', '#000000');
                    label.style.fontFamily = '"Roboto", Arial, sans-serif';
                    label.style.fontSize = '11px';
                });
                
                // Copiar estilos del grid
                var gridSelectors = '.grid-row, .grid-header, .tick, .row-line, .today-highlight';
                var gridElements = ganttSvg[0].querySelectorAll(gridSelectors);
                var clonedGridElements = svgClone.querySelectorAll(gridSelectors);
                for (var i = 0; i < gridElements.length && i < clonedGridElements.length; i++) {
                    var computedStyle = window.getComputedStyle(gridElements[i]);
                    if (computedStyle.fill && computedStyle.fill !== 'none') {
                        clonedGridElements[i].setAttribute('fill', computedStyle.fill);
                    }
                    if (computedStyle.stroke && computedStyle.stroke !== 'none') {
                        clonedGridElements[i].setAttribute('stroke', computedStyle.stroke);
                    }
                }
                
                // Crear ventana de impresión
                var printWindow = window.open('', '_blank', 'width=1200,height=800');
                
                var printContent = '<!DOCTYPE html>' +
                    '<html><head><meta charset="UTF-8">' +
                    '<title>Imprimir Gantt</title>' +
                    '<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">' +
                    '<style>' +
                    '@page { size: landscape; margin: 5mm; }' +
                    '*, *::before, *::after { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }' +
                    'body { font-family: "Roboto", Arial, sans-serif; margin: 0; padding: 10px; background: white; }' +
                    '.print-header { text-align: center; margin-bottom: 10px; border-bottom: 2px solid #3498db; padding-bottom: 8px; }' +
                    '.print-header h1 { margin: 0 0 3px 0; font-size: 18px; color: #2c3e50; }' +
                    '.print-header h2 { margin: 0; font-size: 12px; color: #7f8c8d; font-weight: normal; }' +
                    '.print-header .print-date { font-size: 10px; color: #95a5a6; margin-top: 3px; }' +
                    '.gantt-print-container { overflow: visible; }' +
                    '.gantt-print-container svg { display: block; }' +
                    '.gantt-print-container svg text { font-family: "Roboto", Arial, sans-serif !important; fill: #000000 !important; }' +
                    '</style></head><body>' +
                    '<div class="print-header">' +
                    '<h1>' + pageTitle + '</h1>' +
                    (viewTitle ? '<h2>' + viewTitle + '</h2>' : '') +
                    '<div class="print-date">Impreso el: ' + new Date().toLocaleDateString('es-ES', { 
                        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', 
                        hour: '2-digit', minute: '2-digit' 
                    }) + '</div></div>' +
                    '<div class="gantt-print-container">' + svgClone.outerHTML + '</div>' +
                    '<script>window.onload = function() { setTimeout(function() { window.print(); }, 300); ' +
                    'window.onafterprint = function() { window.close(); }; };<\/script>' +
                    '</body></html>';
                
                printWindow.document.write(printContent);
                printWindow.document.close();
            }
        };
    }
    </script>
</div>