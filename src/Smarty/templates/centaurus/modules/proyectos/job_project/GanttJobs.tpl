{math equation= rand() assign= "idGanttDiagram"}
<div class="row" id="task-gantt-{$idGanttDiagram}">
    <link rel="stylesheet" href="themes/centaurus/js/gantt/frappe-gantt.css" />
    <link rel="stylesheet" href="themes/centaurus/css/gantt-scroll-fix.css" />
    <script type="text/javascript" src="themes/centaurus/js/gantt/frappe-gantt.js"></script>
    <style>
        /* Sobrescribir altura para vista de proyectos: 80vh */
        .card-task-gantt {
            height: 80vh !important;
            max-height: 80vh !important;
            max-width: 100%;
            overflow: auto;
            margin: 0 auto;
        }
        .gantt .bar-label {
            fill: #555!important;
            dominant-baseline: central;
            text-anchor: start;
            font-size: 12px;
            font-weight: lighter;
            margin-left: 2px;
        }
        .gantt-target {
            /* width: 2698px!important; */
        }
        /* custom class */
        .gantt .bar-milestone .bar {
            fill: tomato;
        }
        .gantt .task-group .bar {
            fill: #3498db;
        }
        .gantt .task-group .bar-label {
            font-weight: bold;
        }
        .gantt .task-group .bar-progress {
            fill: tomato;
        }
    </style>
    <div id="scale-gannt-{$idGanttDiagram}" class="row" style="margin-bottom: 2px;">
        <div class="col-md-12" style="margin-left: 9px;margin-bottom: 2px">
            <div style="display: inline-flex; align-items: center; gap: 10px;">
                <select class="form-control border" id="gantt-scale" title="Módulos" onchange="setGanttScale(this)" style="width: auto; min-width: 280px;">
                    <option value="" selected="">Escala: Mes (Seleccionar otra escala)</option>
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
    <div class="col-md-12 card-task-gantt">
        <div id="gantt-target" class="gantt-target"></div>
    </div>
    <script>
        var isTaskGroup      = false,
            isRefresh        = false,
            relatedModule    = '{$RELATED_MODULE}',
            taskGrouped      = [],
            totalInGroup     = 0,
            ganttId          = '{$idGanttDiagram}';
        
        // Cargar tareas crudas desde el servidor
        var rawTasks = {$WORKS_GANTT};
        var myTask = [];
        
        {literal}
        // =====================================================================
        // Normalizar tareas: convertir fechas y calcular fechas de padres
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
                // Primero normalizar formato de fechas
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
                    
                    var hasValidStart = t.start && !isNaN(new Date(t.start).getTime());
                    var hasValidEnd = t.end && !isNaN(new Date(t.end).getTime());
                    
                    if (!hasValidStart && !hasValidEnd) {
                        discardedTasks.push({id: t.id, name: t.name, reason: 'Sin fechas válidas'});
                        return;
                    }
                    
                    if (!hasValidStart) t.start = t.end;
                    if (!hasValidEnd) t.end = t.start;
                    
                    // Validar que end >= start, si no intercambiar
                    var startDate = new Date(t.start);
                    var endDate = new Date(t.end);
                    if (endDate < startDate) {
                        var tmp = t.start;
                        t.start = t.end;
                        t.end = tmp;
                    }
                    
                    myTask.push(t);
                });
            }
        })();
        
        // Función para recalcular fechas de padres después de cambios
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
        
        function setWidthBoard () {
                var ganttWidth = (gantt_chart.$svg.getAttribute('width') + 20);
                jQuery ('#gantt-target').css("width", ganttWidth);

            jQuery('.card-task-gantt').animate({scrollLeft: 0}, 800);
            return false;
        }
        
        // Calcular progreso ponderado para Etapas (nivel 2)
        function calcularProgresoEtapas(tasks) {
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
                    var startDate = new Date(trabajo.start);
                    var endDate = new Date(trabajo.end);
                    var duracionMs = endDate.getTime() - startDate.getTime();
                    var duracionDias = Math.max(1, duracionMs / (1000 * 60 * 60 * 24));
                    var progreso = trabajo.progress || 0;
                    
                    sumaPonderada += progreso * duracionDias;
                    sumaDuraciones += duracionDias;
                });
                
                etapa.progress = sumaDuraciones > 0 
                    ? Math.round(sumaPonderada / sumaDuraciones) 
                    : 0;
            });
            
            return tasks;
        }
        
        // Ejecutar cálculo de progreso ponderado para etapas
        myTask = calcularProgresoEtapas(myTask);
        
        var gantt_chart = new Gantt (".gantt-target", myTask, {
            on_click: function (task) {

            },
            on_date_change: function (task, start, end) {
                var info           = '',
                    options        = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' },
                    dummyStartDate = task._start.toLocaleDateString ('en-US').split('/'),
                    dummyEndDate   = task._end.toLocaleDateString ('en-US').split('/'),
                    moduleName     = task.relModule,
                    taskIdStr      = String(task.id),
                    dummy          = taskIdStr.indexOf('@') !== -1 ? taskIdStr.split('@') : [taskIdStr],
                    jobId          = parseInt (dummy[0]),
                    tableId        = dummy.length > 1 ? parseInt (dummy[1]) : null,
                    isCalendarTask = (moduleName === 'Calendar');
                if (isRefresh) {
                    return false;
                }
                
                // Si es una tarea de Calendar (nivel 4)
                if (isCalendarTask && jQuery.isNumeric(jobId)) {
                    info = '¿Estás seguro que quieres cambiar las fechas a la tarea: ' + task.name + '?';
                    info += '\n Inicio: ' +  start.toLocaleDateString (undefined, options);
                    info += '\n Fin: ' +  end.toLocaleDateString (undefined, options);
                    
                    if (!confirm(info)) {
                        isRefresh = true;
                        gantt_chart.refresh (myTask);
                        setTimeout (function(){ isRefresh = false;}, 5000);
                        return false;
                    }
                    
                    var taskStart = dummyStartDate[2] + '-' + dummyStartDate[0].padStart(2,'0') + '-' + dummyStartDate[1].padStart(2,'0');
                    var taskEnd = dummyEndDate[2] + '-' + dummyEndDate[0].padStart(2,'0') + '-' + dummyEndDate[1].padStart(2,'0');
                    
                    var taskData = [{
                        id: jobId,
                        start: taskStart,
                        end: taskEnd
                    }];
                    
                    // Actualizar las fechas en el objeto task local
                    task.start = taskStart;
                    task.end = taskEnd;
                    
                    jQuery.post('index.php', {
                        'module':   'Calendar',
                        'action':   'AjaxDetailViewUtils',
                        'function': 'CHANGE-DATES-TASK',
                        'taskData': taskData,
                        'Ajax':     'true'
                    }, function (data) {
                        try {
                            var message = (typeof data === 'string') ? JSON.parse(data) : data;
                            if (!message || message.error !== 'OK') {
                                throw (message && message.error) ? message.error : 'Error al actualizar la tarea';
                            } else {
                                // Recalcular fechas de padres y refrescar Gantt
                                recalculateParentDatesForGantt(myTask);
                                isRefresh = true;
                                gantt_chart.refresh(myTask);
                                setTimeout(function(){ isRefresh = false; }, 2000);
                                alert('La tarea ha sido actualizada correctamente');
                            }
                        }
                        catch (e) {
                            alert (e || 'Error desconocido');
                        }
                    });
                    return;
                }
                
                // Si es un trabajo (tiene formato id@tableId)
                if (jQuery.isNumeric (jobId) && tableId !== null) {
                    info = '¿Estás seguro que quieres cambiar las fechas al trabajo: ' + task.name + '?';
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

                    task.start    = dummyStartDate[2] + '-' + dummyStartDate[0] + '-' + dummyStartDate[1];
                    task.end      = dummyEndDate[2] + '-' + dummyEndDate[0] + '-' + dummyEndDate[1];
                    totalInGroup -= 1;

                    taskGrouped.push (task);
                    if (totalInGroup <= 0) {
                        var postArgs = {
                            'module':   moduleName,
                            'action':   'AjaxDetailViewUtils',
                            'function': 'CHANGE-DATES-JOB',
                            'jobData': taskGrouped,
                            'Ajax':     'true'
                        };
                        jQuery.post('index.php', postArgs, function (data) {
                            try {
                                var message = (typeof data === 'string') ? JSON.parse(data) : data;
                                if (!message || message.error !== 'OK') {
                                    throw (message && message.error) ? message.error : 'Error al actualizar el trabajo';
                                } else {
                                    // Recalcular fechas de padres y refrescar Gantt
                                    recalculateParentDatesForGantt(myTask);
                                    isRefresh = true;
                                    gantt_chart.refresh(myTask);
                                    setTimeout(function(){ isRefresh = false; }, 2000);
                                    
                                    if(isTaskGroup) {
                                        alert('Los trabajos del grupo han sido actualizados')
                                    } else {
                                        alert('El trabajo ha sido actualizado correctamente')
                                    }
                                }
                            }
                            catch (e) {
                                alert (e || 'Error desconocido');
                            }
                        });
                        taskGrouped  = [];
                        totalInGroup = 0;
                        isTaskGroup  = false;
                    }
                } else if (!isCalendarTask) {
                    info = '¿Estás seguro que quieres cambiar las fechas al grupo de trabajos en: ' + task.name + '?';
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
                    task.start = dummyStartDate[2] + '-' + dummyStartDate[0] + '-' + dummyStartDate[1];
                    task.end   = dummyEndDate[2] + '-' + dummyEndDate[0] + '-' + dummyEndDate[1];
                    isTaskGroup  = true;
                    totalInGroup = task.totalGroup;
                    taskGrouped  = [];
                }
            },
            on_progress_change: function(task, progress) {
                var oldProgress = task.actProgress,
                    dummy       = task.id.split('@'),
                    jobId       = parseInt (dummy[0]),
                    tableId     = parseInt (dummy[1]);
                if (isRefresh) {
                    return false;
                }

                if (jQuery.isNumeric (jobId)) {
                    info = '¿Estás seguro que quieres cambiar el % de avance del trabajo: ' + task.name + '?';
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
                            'function': 'CHANGE-PROGRESS-JOB',
                            'jobData':  task,
                            'Ajax':     'true'
                        };
                        jQuery.post ('index.php', arguments, function (data) {
                            try {
                                var message = JSON.parse(JSON.stringify(data));
                                if (message.error !== 'OK') {
                                    throw message.error;
                                } else {
                                    alert('El trabajo ha sido actualizado. Refresque la página del proyecto para actualizar la tabla de trabajos')
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

        function setGanttScale (obj) {
            var scale = jQuery (obj).val();
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
            setWidthBoard ();
        }

        setWidthBoard ();
        
        // Inicializar barra de scroll fija después de renderizar
        setTimeout(function() {
            initFixedScrollbar();
        }, 200);

        jQuery('.gantt').click(function (e) {
            e.stopPropagation ();
            e.preventDefault ();
            return false;

        });

        jQuery('.gantt').mousemove(function (e) {
            e.stopPropagation ();
            e.preventDefault ();
            return false;

        });

        // ============================================
        // Barra de scroll horizontal fija
        // ============================================
        function initFixedScrollbar() {
            var ganttContainer = document.querySelector('.card-task-gantt');
            var ganttSvg = document.querySelector('.card-task-gantt svg.gantt');
            
            if (!ganttContainer || !ganttSvg) {
                return;
            }
            
            // Remover barra existente si hay
            var existingBar = document.getElementById('gantt-fixed-scrollbar');
            if (existingBar) {
                existingBar.remove();
            }
            
            // Crear barra de scroll fija
            var scrollbar = document.createElement('div');
            scrollbar.id = 'gantt-fixed-scrollbar';
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
            function updateScrollbar() {
                var svgWidth = ganttSvg.getBoundingClientRect().width;
                scrollbarInner.style.width = Math.max(svgWidth, ganttContainer.scrollWidth) + 'px';
                positionScrollbar();
            }
            
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
            
            // Actualizar al cambiar escala
            window.updateGanttScrollbar = function() {
                var svgWidth = ganttSvg.getBoundingClientRect().width;
                scrollbarInner.style.width = Math.max(svgWidth, ganttContainer.scrollWidth) + 'px';
                positionScrollbar();
                checkNativeScrollbarVisibility();
            };
        }
        
        // Actualizar barra al cambiar escala
        var originalSetGanttScale = setGanttScale;
        setGanttScale = function(obj) {
            originalSetGanttScale(obj);
            setTimeout(function() {
                if (window.updateGanttScrollbar) {
                    window.updateGanttScrollbar();
                }
            }, 150);
        };
        
        // Utilidad global para imprimir Gantt (solo se define una vez)
        if (typeof GanttPrintUtils === 'undefined') {
            window.GanttPrintUtils = {
                printGantt: function(ganttId) {
                    // Buscar el contenedor del Gantt por ID o por clase
                    var ganttContainer = jQuery('#card-task-gantt-' + ganttId);
                    if (ganttContainer.length === 0) {
                        ganttContainer = jQuery('#task-gantt-' + ganttId + ' .card-task-gantt');
                    }
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
        {/literal}
    </script>
</div>