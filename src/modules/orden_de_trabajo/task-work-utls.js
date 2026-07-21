(function (jQuery) {
  //private var
  var americanToEuropean = function (number) {
    return number.toLocaleString("de-DE", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    });
  };

  var americanToAmerican = function (number) {
    return number.toLocaleString("en-US", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    });
  };

  var europeanToAmerican = function (numberString) {
    return parseFloat(numberString.replace(/\./g, "").replace(",", "."));
  };
  var updateTotalTime = function (idTable) {
    var tBody = jQuery("#tbody-task-project-" + idTable),
      totalTime = jQuery("#summary-duration-" + idTable),
      numFormat = tBody.attr("data-num-format"),
      plannedUnits = jQuery("#numero_unidades_planificadas"),
      workUnit = tBody.attr("data-work-unit"),
      total = 0;
    if (idTable !== "") {
      tBody.find(".duration-time").each(function (index, field) {
        var fieldObj = jQuery(field);
        if (fieldObj.val()) {
          // Obtener el select de unidad de la misma fila
          var row = fieldObj.closest("tr");
          var unitSelect = row.find(
            'select[name="projec_task[estimated_time_unit][]"]',
          );
          var taskUnit = unitSelect.val();

          // Solo sumar si ambas unidades tienen valor y coinciden exactamente
          if (workUnit && taskUnit && taskUnit === workUnit) {
            if (numFormat === "EUROPEAN_FORMAT") {
              total += europeanToAmerican(fieldObj.val());
            } else {
              total += parseFloat(fieldObj.val());
            }
          }
        }
      });
      var formattedTotal;
      if (numFormat === "EUROPEAN_FORMAT") {
        formattedTotal = americanToEuropean(total);
      } else {
        formattedTotal = americanToAmerican(total);
      }

      // Agregar la unidad al valor mostrado si existe
      var displayValue = formattedTotal.toString();
      if (workUnit) {
        displayValue += " " + workUnit;
      }

      totalTime.val(displayValue);
      // El campo numero_unidades_planificadas solo guarda el número
      plannedUnits.val(formattedTotal.toString());
    }
  };

  var updateTotalEstimatedCost = function (idTable) {
    var tBody = jQuery("#tbody-task-project-" + idTable),
      totalCost = jQuery("#summary-estimated-cost-" + idTable),
      numFormat = tBody.attr("data-num-format"),
      total = 0;

    if (idTable !== "") {
      tBody.find(".estimated-cost-field").each(function (index, field) {
        var fieldObj = jQuery(field);
        if (fieldObj.val()) {
          if (numFormat === "EUROPEAN_FORMAT") {
            total += europeanToAmerican(fieldObj.val());
          } else {
            total += parseFloat(fieldObj.val());
          }
        }
      });
      if (numFormat === "EUROPEAN_FORMAT") {
        total = americanToEuropean(total);
        totalCost.val(total.toString());
      } else {
        total = americanToAmerican(total);
        totalCost.val(total.toString());
      }
    }
  };

  //public method
  var addRowToTable = function (obj, tBody, idTable) {
    var row = jQuery("#" + tBody),
      btn = jQuery(obj),
      sequence = btn.attr("data-sequence"),
      rowId = Math.floor(Math.random() * 500) + 1,
      template = jQuery("#task-project-template-" + idTable)
        .html()
        .replace(/__ID__/g, rowId),
      taskRow = jQuery(template);

    if (sequence === "0") {
      row.empty();
    }
    jQuery(taskRow)
      .find(".datepickerDate")
      .datepicker({
        format:
          typeof gUserDateFormat !== "undefined"
            ? gUserDateFormat
            : "yyyy-mm-dd",
        language: "es",
        weekStart: 1,
      });
    row.append(taskRow);
    sequence = parseInt(sequence) + 1;
    btn.attr("data-sequence", sequence.toString());
  };

  var delRowToTable = function (buttonElement, tr, idTable) {
    var tBody = jQuery("#tbody-task-project-" + idTable),
      row = jQuery("#" + tr),
      rows = row.parent(),
      trs = rows.find("tr").length;

    // Obtener el ID de la tarea y del trabajo
    var taskIdInput = row.find('input[name="projec_task[taskId][]"]');
    var taskId = taskIdInput.val();
    var workId = jQuery('input[name="record"]').val();

    // Si la tarea no tiene ID (es nueva), eliminar directamente
    if (!taskId || taskId === "" || taskId === "0") {
      if (!confirm("¿Estás seguro que quieres eliminar esta tarea?")) {
        return;
      }
      jQuery(buttonElement).closest("tr").remove();
      if (trs === 1) {
        var template = jQuery("#task-project-tr-" + idTable).html();
        tBody.append(template);
      }
      updateTotalTime(idTable);
      updateTotalEstimatedCost(idTable);
      return;
    }

    // Para tareas existentes, verificar relaciones
    jQuery.ajax({
      url: "index.php?module=orden_de_trabajo&action=orden_de_trabajoAjax&file=CheckTaskRelations",
      type: "POST",
      data: {
        activityid: taskId,
        workid: workId,
      },
      dataType: "json",
      success: function (response) {
        if (!response.success) {
          alert(
            "Error al verificar relaciones de la tarea: " + response.message,
          );
          return;
        }

        var data = response.data;
        var message = "";

        // REGLA 3: Si tiene reportes, BLOQUEAR eliminación
        if (data.hasReports) {
          alert(
            "ERROR: No se puede eliminar esta tarea.\n\n" +
              "La tarea tiene reportes de avance registrados y no puede ser eliminada ni desvinculada del trabajo.",
          );
          return;
        }

        // REGLA 1 y 2: Solo trabajo (con o sin ejecutor) -> Eliminar completamente
        if (data.canDelete) {
          message =
            "¿Estás seguro que quieres eliminar esta tarea?\n\n";
          message += "La tarea será eliminada completamente del sistema.";

          // Informar si tiene ejecutor (no impide eliminación)
          if (data.supplierName) {
            message += "\n\nNota: La tarea tiene asignado el ejecutor '" + data.supplierName + "'.";
          }
        }
        // REGLA 4: Tiene otras relaciones -> Solo desvincular
        else if (data.hasOtherRecords) {
          message =
            "ADVERTENCIA: Esta tarea está relacionada con otros registros.\n\n";

          if (data.warnings && data.warnings.length > 0) {
            data.warnings.forEach(function (warning) {
              message += "• " + warning + "\n";
            });
          }

          message += "\nSolo se eliminará el vínculo con este trabajo.\n";
          message += "La tarea seguirá existiendo en el sistema.\n\n";
          message += "¿Deseas continuar?";
        }

        if (confirm(message)) {
          jQuery(buttonElement).closest("tr").remove();
          if (trs === 1) {
            var template = jQuery("#task-project-tr-" + idTable).html();
            tBody.append(template);
          }
          updateTotalTime(idTable);
          updateTotalEstimatedCost(idTable);
        }
      },
      error: function () {
        // Si falla la verificación, usar comportamiento por defecto
        if (
          confirm(
            "No se pudo verificar las relaciones de la tarea.\n¿Estás seguro que quieres eliminar el elemento seleccionado?",
          )
        ) {
          jQuery(buttonElement).closest("tr").remove();
          if (trs === 1) {
            var template = jQuery("#task-project-tr-" + idTable).html();
            tBody.append(template);
          }
          updateTotalTime(idTable);
          updateTotalEstimatedCost(idTable);
        }
      },
    });
  };

  var moveRowDown = function (btn, tr) {
    var rowToMove = jQuery("#" + tr),
      next = rowToMove.next("tr.tabla-field-row");
    next.after(rowToMove);
  };

  var moveRowUp = function (btn, tr) {
    var rowToMove = jQuery("#" + tr),
      prev = rowToMove.prev("tr.tabla-field-row");
    prev.before(rowToMove);
  };

  var setCalendar = function (idTable) {
    var tBody = jQuery("#tbody-task-project-" + idTable);
    jQuery(tBody)
      .find(".datepickerDate")
      .datepicker({
        format:
          typeof gUserDateFormat !== "undefined"
            ? gUserDateFormat
            : "yyyy-mm-dd",
        language: "es",
        weekStart: 1,
      });
  };

  var updateNumFields = function (obj, idTable) {
    var field = jQuery(obj);
    // Solo limpiar valores numéricos si NO es un select (picklist)
    if (field.prop("tagName") !== "SELECT") {
      field.val(field.val().replace(/[^\d.,-]/g, ""));
    }
    updateTotalTime(idTable);
    updateTotalEstimatedCost(idTable);
  };

  window.TaskWorkUtls = {
    addRowToTable: addRowToTable,
    delRowToTable: delRowToTable,
    moveRowDown: moveRowDown,
    moveRowUp: moveRowUp,
    setCalendar: setCalendar,
    updateNumFields: updateNumFields,
  };

  var onDocumentReadyHandler = function () {};
  jQuery(document).ready(onDocumentReadyHandler);
})(jQuery);
