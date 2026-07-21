/**
 * TaskWorkProgressColors - Coloreo de celdas de progreso según combined_condition
 *
 * Este script aplica colores a las celdas de Unid. Ejecutadas, Costo Ejecutado,
 * % avance tarea y % progreso trabajo según el campo vtiger_activity.combined_condition
 */
jQuery(document).ready(function () {
  // Función para aplicar colores a las celdas de progreso
  function applyTaskProgressColors() {
    // TaskWorkProgressColors: Aplicando colores...

    jQuery(".task-progress-cell").each(function () {
      var $cell = jQuery(this);
      var $row = $cell.closest("tr");
      var combinedCondition = $row.data("combined-condition");

      // TaskWorkProgressColors: Fila encontrada, combined_condition = " + combinedCondition

      if (combinedCondition) {
        // Mapear condiciones a clases CSS
        var conditionClass = "";
        switch (combinedCondition) {
          case "PICK_ACTIVITY_DELAYED_OVER_BUDGET":
            conditionClass = "task-cell-retraso-sobrecosto";
            break;
          case "PICK_ACTIVITY_DELAYED_ON_BUDGET":
            conditionClass = "task-cell-retraso-costo";
            break;
          case "PICK_ACTIVITY_ON_TIME_OVER_BUDGET":
            conditionClass = "task-cell-tiempo-sobrecosto";
            break;
          case "PICK_ACTIVITY_ON_TIME_ON_BUDGET":
            conditionClass = "task-cell-tiempo-costo";
            break;
        }

        // TaskWorkProgressColors: Clase a aplicar = " + conditionClass

        // Aplicar la clase si hay una condición válida
        if (conditionClass) {
          $cell.addClass(conditionClass);
          // TaskWorkProgressColors: Clase aplicada a la celda
        }
      } else {
        // TaskWorkProgressColors: No hay combined_condition en esta fila
      }
    });

    // TaskWorkProgressColors: Totales encontrados - Celdas: " + jQuery(".task-progress-cell").length + ", Filas: " + jQuery("tr[data-combined-condition]").length
  }

  // Aplicar colores al cargar la página
  applyTaskProgressColors();

  // Re-aplicar colores si se actualiza el contenido dinámicamente
  // (por ejemplo, después de AJAX calls o actualizaciones del grid)
  jQuery(document).on("DOMNodeInserted", ".task-work-table", function () {
    setTimeout(applyTaskProgressColors, 100);
  });

  // También escuchar eventos personalizados si los hay
  jQuery(document).on("taskWorkUpdated", function () {
    setTimeout(applyTaskProgressColors, 100);
  });
});

// Definición de clases CSS (ya están en el template, pero las mantenemos aquí como referencia)
/*
.task-cell-retraso-sobrecosto { background-color: #D32F2F !important; color: white; }
.task-cell-retraso-costo { background-color: #F57C00 !important; color: white; }
.task-cell-tiempo-sobrecosto { background-color: #7B1FA2 !important; color: white; }
.task-cell-tiempo-costo { background-color: #388E3C !important; color: white; }
*/
