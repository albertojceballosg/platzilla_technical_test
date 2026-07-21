(function (jQuery) {
  //private var
  var projectTableId = "";

  var americanToEuropean = function (number) {
    return number.toLocaleString("de-DE", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    });
  };

  var europeanToAmerican = function (numberString) {
    return parseFloat(numberString.replace(/\./g, "").replace(",", "."));
  };

  var americanToAmerican = function (number) {
    return number.toLocaleString("en-US", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    });
  };

  var getTableIdFromAnyRow = function () {
    if (projectTableId !== "") {
      return projectTableId;
    }
    var summaryCost = jQuery("input[id^=summary-work_estimated_cost-]").first();
    if (summaryCost.length > 0) {
      var id = summaryCost.attr("id").replace("summary-work_estimated_cost-", "");
      projectTableId = id;
      return id;
    }
    return "";
  };

  var updateTotalEstimatedCost = function (tableId) {
    var idTable = tableId || getTableIdFromAnyRow();
    if (!idTable) {
      return;
    }

    var summaryCost = jQuery("#summary-work_estimated_cost-" + idTable);
    if (summaryCost.length === 0) {
      return;
    }

    var numFormat = "AMERICAN_FORMAT";
    var firstRow = jQuery("tr[id^=tr-row-]").first();
    if (firstRow.length > 0) {
      numFormat = firstRow.attr("data-num-format") || "AMERICAN_FORMAT";
    }

    var total = 0;
    jQuery("input[id^=work_estimated_cost-]").each(function (index, obj) {
      var fieldVal = jQuery(obj).val();
      if (fieldVal === "" || fieldVal === undefined) {
        return;
      }
      var parsed = 0;
      if (numFormat === "EUROPEAN_FORMAT") {
        parsed = europeanToAmerican(fieldVal);
      } else {
        parsed = parseFloat(fieldVal.toString().replace(/,/g, ""));
      }
      if (!isNaN(parsed)) {
        total += parsed;
      }
    });

    // Formatear total
    var formatted = "0.00";
    if (numFormat === "EUROPEAN_FORMAT") {
      formatted = americanToEuropean(total);
    } else {
      formatted = americanToAmerican(total);
    }
    summaryCost.val(formatted.toString());
  };

  var setTotalContribution = function (id) {
    var contributionFactor = jQuery("#job_contribution_factor-" + id).val(),
      resourceProgress = jQuery("#percentage_completion-" + id).val(),
      progressSummary = jQuery("#summary-project_progress-" + projectTableId),
      progressFields = jQuery("input[id ^= project_progress-]"),
      projProgress = jQuery("#project_progress-" + id),
      numFormat = jQuery("#tr-row-" + id).attr("data-num-format"),
      thisProgress = 0,
      sumProgress = 0,
      multiplication = 0;

    if (numFormat === "EUROPEAN_FORMAT") {
      contributionFactor = europeanToAmerican(contributionFactor);
      resourceProgress = europeanToAmerican(resourceProgress);
    } else {
      contributionFactor = parseFloat(contributionFactor);
      resourceProgress = parseFloat(resourceProgress);
    }

    if (!isNaN(contributionFactor) && !isNaN(resourceProgress)) {
      multiplication = (contributionFactor * resourceProgress) / 100;
      thisProgress = multiplication;
    }
    if (numFormat === "EUROPEAN_FORMAT") {
      multiplication = americanToEuropean(multiplication);
      projProgress.val(multiplication.toString());
    } else {
      projProgress.val(multiplication.toFixed(2).toString());
    }

    if (!isNaN(thisProgress)) {
      progressFields.each(function (index, obj) {
        var fieldId = jQuery(obj).attr("id"),
          fieldVal = jQuery(obj).val();
        if (numFormat === "EUROPEAN_FORMAT") {
          fieldVal = europeanToAmerican(fieldVal);
        } else {
          fieldVal = parseFloat(fieldVal);
        }

        if (!isNaN(fieldVal) && fieldId !== "project_progress-" + id) {
          sumProgress += fieldVal;
        }
      });
      if (sumProgress > 100 || sumProgress + thisProgress > 100) {
        alert("La suma de lo avances del proyecto no puede ser mayor que 100%");
        if (numFormat === "EUROPEAN_FORMAT") {
          projProgress.val("0,00");
          progressSummary.val("0,00");
        } else {
          projProgress.val("0.00");
          progressSummary.val("0.00");
        }
        if (sumProgress < 100) {
          if (numFormat === "EUROPEAN_FORMAT") {
            sumProgress = americanToEuropean(sumProgress);
          }
          progressSummary.val(sumProgress.toString());
        }
      } else {
        sumProgress += thisProgress;
        if (numFormat === "EUROPEAN_FORMAT") {
          sumProgress = americanToEuropean(sumProgress);
          progressSummary.val(sumProgress.toString());
        } else {
          progressSummary.val(sumProgress.toFixed(2).toString());
        }
      }
    }
  };

  var validateFactor = function (id) {
    var contributionFactor = jQuery("#job_contribution_factor-" + id),
      thisFactor = contributionFactor.val(),
      factorSummary = jQuery(
        "#summary-job_contribution_factor-" + projectTableId
      ),
      factorFields = jQuery("input[id ^= job_contribution_factor-]"),
      numFormat = jQuery("#tr-row-" + id).attr("data-num-format"),
      sumContribution = 0;

    if (numFormat === "EUROPEAN_FORMAT") {
      thisFactor = europeanToAmerican(thisFactor);
    } else {
      thisFactor = parseFloat(thisFactor);
    }

    if (!isNaN(thisFactor)) {
      factorFields.each(function (index, obj) {
        var fieldId = jQuery(obj).attr("id"),
          fieldVal = jQuery(obj).val();

        if (
          fieldVal !== "" &&
          fieldVal !== undefined &&
          fieldId !== "job_contribution_factor-" + id
        ) {
          if (numFormat === "EUROPEAN_FORMAT") {
            fieldVal = europeanToAmerican(fieldVal);
          } else {
            fieldVal = parseFloat(fieldVal);
          }
          sumContribution += fieldVal;
        }
      });
      if (sumContribution > 100 || sumContribution + thisFactor > 100) {
        alert("La suma de los factores de avance no puede ser mayor que 100%");
        if (numFormat === "EUROPEAN_FORMAT") {
          contributionFactor.val("0,00");
          factorSummary.val("0,00");
        } else {
          contributionFactor.val("0.00");
          factorSummary.val("0.00");
        }
        if (sumContribution < 100) {
          if (numFormat === "EUROPEAN_FORMAT") {
            sumContribution = americanToEuropean(sumContribution);
            factorSummary.val(sumContribution.toString());
          } else {
            factorSummary.val(sumContribution.toFixed(2).toString());
          }
        }
      } else {
        sumContribution += thisFactor;
        if (numFormat === "EUROPEAN_FORMAT") {
          sumContribution = americanToEuropean(sumContribution);
          factorSummary.val(sumContribution.toString());
        } else {
          factorSummary.val(sumContribution.toFixed(2).toString());
        }
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
    if (projectTableId === "") {
      projectTableId = idTable;
    }
    if (sequence === "0") {
      row.empty();
    }
    jQuery(taskRow)
      .find(".datepickerDate")
      .each(function () {
        var $input = jQuery(this);
        var currentValue = $input.val();

        // Si el valor es 0000-00-00, establecer fecha actual para el datepicker
        if (currentValue === "0000-00-00") {
          $input.val(""); // Limpiar el campo
        }

        $input.datepicker({
          format:
            typeof gUserDateFormat !== "undefined"
              ? gUserDateFormat
              : "yyyy-mm-dd",
          language: "es",
          weekStart: 1,
          defaultViewDate: currentValue === "0000-00-00" ? new Date() : "today",
        });
      });
    
    // BLOQUEAR datepickers en campos de fecha readonly de la nueva fila
    // Estos campos muestran datos del trabajo y no deben ser editables
    taskRow.find("input[id^='job-start_date-'], input[id^='job-due_date-']")
      .each(function() {
        var $input = jQuery(this);
        // Destruir cualquier datepicker que pudiera haberse inicializado
        if ($input.data('datepicker')) {
          $input.datepicker('destroy');
        }
        // Remover clase datepickerDate si existe
        $input.removeClass('datepickerDate');
        // Bloquear eventos
        $input.off('focus.datepicker click.datepicker');
      });
    
    row.append(taskRow);
    sequence = parseInt(sequence) + 1;
    btn.attr("data-sequence", sequence.toString());

    updateTotalEstimatedCost(idTable);
  };

  var delRowToTable = function (buttonElement, tr, idTable) {
    var tBody = jQuery("#task-project-" + idTable),
      row = jQuery("#" + tr),
      rows = row.parent(),
      trs = rows.find("tr").length;

    if (
      !confirm("¿Estás seguro que quieres eliminar el elemento seleccionado?")
    ) {
      return;
    }
    jQuery(buttonElement).closest("tr").remove();
    if (trs === 1) {
      var template = jQuery("#task-project-tr-" + idTable).html();
      tBody.append(template);
    }

    updateTotalEstimatedCost(idTable);
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

  var setCalendar = function (obj) {
    jQuery(obj)
      .find(".datepickerDate")
      .each(function () {
        var $input = jQuery(this);
        var currentValue = $input.val();

        // Si el valor es 0000-00-00, establecer fecha actual para el datepicker
        if (currentValue === "0000-00-00") {
          $input.val(""); // Limpiar el campo
        }

        $input.datepicker({
          format:
            typeof gUserDateFormat !== "undefined"
              ? gUserDateFormat
              : "yyyy-mm-dd",
          language: "es",
          weekStart: 1,
          defaultViewDate: currentValue === "0000-00-00" ? new Date() : "today",
        });
      });
  };

  var updateNumFields = function (obj, idTable) {
    var field = jQuery(obj);
    field.val(field.val().replace(/[^\d.,-]/g, ""));

    validateFactor(idTable);
    setTotalContribution(idTable);

    updateTotalEstimatedCost(idTable);
  };

  var calculateInitialTotals = function (tableId, isDetailView) {
    if (projectTableId === "") {
      projectTableId = tableId;
    }

    // En DetailView, los valores ya vienen calculados y formateados desde PHP
    // No necesitamos recalcular, solo asegurarnos de que se muestren correctamente
    if (isDetailView) {
      // Los valores del summaryRow ya están en los inputs del footer
      // No hacer nada, dejar los valores que vienen de PHP
      return;
    }

    // En EditView, calcular los totales desde los inputs
    var factorSummary = jQuery("#summary-job_contribution_factor-" + tableId),
      progressSummary = jQuery("#summary-project_progress-" + tableId),
      sumContribution = 0,
      sumProgress = 0,
      numFormat = "AMERICAN_FORMAT";

    // Detectar formato numérico desde el primer row
    var firstRow = jQuery("tr[id ^= tr-row-]").first();
    if (firstRow.length > 0) {
      numFormat = firstRow.attr("data-num-format") || "AMERICAN_FORMAT";
    }

    var factorFields = jQuery("input[id ^= job_contribution_factor-]"),
      progressFields = jQuery("input[id ^= project_progress-]");

    // Calcular suma de factores de contribución
    factorFields.each(function (index, obj) {
      var fieldVal = jQuery(obj).val();
      if (fieldVal !== "" && fieldVal !== undefined) {
        if (numFormat === "EUROPEAN_FORMAT") {
          fieldVal = europeanToAmerican(fieldVal);
        } else {
          fieldVal = parseFloat(fieldVal);
        }
        if (!isNaN(fieldVal)) {
          sumContribution += fieldVal;
        }
      }
    });

    // Calcular suma de avances del proyecto
    progressFields.each(function (index, obj) {
      var fieldVal = jQuery(obj).val();
      if (fieldVal !== "" && fieldVal !== undefined) {
        if (numFormat === "EUROPEAN_FORMAT") {
          fieldVal = europeanToAmerican(fieldVal);
        } else {
          fieldVal = parseFloat(fieldVal);
        }
        if (!isNaN(fieldVal)) {
          sumProgress += fieldVal;
        }
      }
    });

    // Actualizar campos de totales
    if (numFormat === "EUROPEAN_FORMAT") {
      factorSummary.val(americanToEuropean(sumContribution).toString());
      progressSummary.val(americanToEuropean(sumProgress).toString());
    } else {
      factorSummary.val(sumContribution.toFixed(2).toString());
      progressSummary.val(sumProgress.toFixed(2).toString());
    }

    updateTotalEstimatedCost(tableId);
  };

  window.TaskProjectUtls = {
    addRowToTable: addRowToTable,
    delRowToTable: delRowToTable,
    moveRowDown: moveRowDown,
    moveRowUp: moveRowUp,
    setCalendar: setCalendar,
    updateNumFields: updateNumFields,
    calculateInitialTotals: calculateInitialTotals,
  };

  var onDocumentReadyHandler = function () {
    // DESACTIVAR datepickers en campos de fecha readonly del project_work
    // Estos campos muestran datos del trabajo y no deben ser editables
    jQuery("input[id^='job-start_date-'], input[id^='job-due_date-']")
      .each(function() {
        var $input = jQuery(this);
        // Destruir cualquier datepicker existente
        if ($input.data('datepicker')) {
          $input.datepicker('destroy');
        }
        // Prevenir que se reinicialice
        $input.removeClass('datepickerDate');
        // Forzar readonly
        $input.attr('readonly', 'readonly');
        // Bloquear eventos de focus/click que puedan activar datepickers
        $input.off('focus.datepicker click.datepicker');
      });

    // Manejar campos de fecha con valor 0000-00-00 al hacer focus
    jQuery(document).on("focus", ".datepickerDate", function () {
      var $input = jQuery(this);
      var currentValue = $input.val();

      // Si el valor es 0000-00-00, limpiar el campo y establecer fecha actual en el datepicker
      if (currentValue === "0000-00-00") {
        $input.val("");
        // Reinicializar el datepicker con la fecha actual
        $input.datepicker("destroy");
        $input.datepicker({
          format:
            typeof gUserDateFormat !== "undefined"
              ? gUserDateFormat
              : "yyyy-mm-dd",
          language: "es",
          weekStart: 1,
          defaultViewDate: new Date(),
        });
        $input.datepicker("show");
      }
    });

    // Si el usuario cambia el costo estimado del proyecto (costo_total_estimad),
    // recalcular el estado del total de la columna costo (rojo + tooltip)
    jQuery(document).on(
      "keyup change",
      "input[name='costo_total_estimad'], #costo_total_estimad",
      function () {
        var tableId = getTableIdFromAnyRow();
        if (!tableId) {
          return;
        }

        var numFormat = "AMERICAN_FORMAT";
        var firstRow = jQuery("tr[id^=tr-row-]").first();
        if (firstRow.length > 0) {
          numFormat = firstRow.attr("data-num-format") || "AMERICAN_FORMAT";
        }

        var raw = 0;
        var val = jQuery(this).val();
        if (val !== "" && val !== undefined) {
          if (numFormat === "EUROPEAN_FORMAT") {
            raw = europeanToAmerican(val);
          } else {
            raw = parseFloat(val.toString().replace(/,/g, ""));
          }
        }
        if (isNaN(raw)) {
          raw = 0;
        }

        jQuery("#project-user-proposed-cost-" + tableId).val(raw.toString());
        updateTotalEstimatedCost(tableId);
      }
    );
  };
  if (!window._taskProjectJobListenerRegistered) {
    window._taskProjectJobListenerRegistered = true;
  jQuery(document)
    .off("relatedModuleRecordSelected")
    .on(
      "relatedModuleRecordSelected",
      function (evt, modalTitle, targetDisplayFieldId, targetDataFieldId) {
        console.log("[TASK_PROJECT] relatedModuleRecordSelected fired | targetDataFieldId="+targetDataFieldId+" | targetDisplayFieldId="+targetDisplayFieldId);
        var record = jQuery("#" + targetDataFieldId).val(),
          dummy = targetDataFieldId.split("-"),
          id = dummy[dummy.length - 1],
          dateStart = jQuery("#job-start_date-" + id),
          dateEnd = jQuery("#job-due_date-" + id),
          factorField = jQuery("#percentage_completion-" + id),
          responsible = jQuery("#responsible_job-" + id),
          userName = jQuery("#name_responsible_job-" + id),
          moduleName = "orden_de_trabajo",
          numFormat = jQuery("#tr-row-" + id).attr("data-num-format"),
          progress,
          estimatedCost,
          costField = jQuery("#work_estimated_cost-" + id),
          costWorkPerformedField = jQuery("#cost_work_performed-" + id),
          postArgs = {
            module: moduleName,
            action: "AjaxEditViewUtils",
            "function": "GET_PROJECT_JOB",
            record: record,
            Ajax: "true",
          };
        console.log("[TASK_PROJECT] record="+record+" | id="+id+" | dateStart.length="+dateStart.length+" | guard="+(targetDataFieldId.indexOf("select_job-") === -1 ? "BLOQUEADO" : "PASA"));
        if (targetDataFieldId.indexOf("select_job-") === -1) {
          evt.stopPropagation();
          return false;
        }
        jQuery.ajax({
          url: "index.php",
          type: "POST",
          data: postArgs,
          dataType: "json",
          error: function(xhr, status, err) {
            console.log("[TASK_PROJECT] AJAX ERROR: status="+status+" | err="+err+" | response="+xhr.responseText.substring(0,300));
          },
          success: function (data) {
          console.log("[TASK_PROJECT] AJAX response raw:", JSON.stringify(data).substring(0, 300));
          try {
            var message = JSON.parse(JSON.stringify(data));
            console.log("[TASK_PROJECT] message.error="+message.error);
            if (message.error !== "OK") {
              throw message.error;
            } else {
              var formatDateForDisplay = function (isoDate) {
                if (!isoDate || isoDate === "") return "";
                var parts = isoDate.split("-");
                if (parts.length !== 3) return isoDate;
                var y = parts[0], mm = parts[1], dd = parts[2];
                var fmt = (typeof gUserDateFormat !== "undefined") ? gUserDateFormat : "yyyy-mm-dd";
                if (fmt === "dd-mm-yyyy") return dd + "-" + mm + "-" + y;
                if (fmt === "mm-dd-yyyy") return mm + "-" + dd + "-" + y;
                if (fmt === "dd/mm/yyyy") return dd + "/" + mm + "/" + y;
                if (fmt === "mm/dd/yyyy") return mm + "/" + dd + "/" + y;
                return y + "-" + mm + "-" + dd;
              };
              console.log("[GET_PROJECT_JOB] id="+id+" | dateStart.length="+dateStart.length+" | fecha_prevista="+message.html.fecha_prevista+" | fecha_estim_fin="+message.html.fecha_estim_fin);
              if (message.html.fecha_prevista !== "") {
                var fmtStart = formatDateForDisplay(message.html.fecha_prevista);
                console.log("[GET_PROJECT_JOB] fmtStart="+fmtStart+" | dateStart id=#job-start_date-"+id);
                dateStart.val(fmtStart);
              }
              if (message.html.fecha_estim_fin !== "") {
                var fmtEnd = formatDateForDisplay(message.html.fecha_estim_fin);
                console.log("[GET_PROJECT_JOB] fmtEnd="+fmtEnd+" | dateEnd id=#job-due_date-"+id);
                dateEnd.val(fmtEnd);
              }
              
              // DESTRUIR datepickers en campos de fecha - estos campos son readonly
              if (dateStart.data("datepicker")) {
                dateStart.datepicker("destroy");
                dateStart.removeClass("datepickerDate");
              }
              if (dateEnd.data("datepicker")) {
                dateEnd.datepicker("destroy");
                dateEnd.removeClass("datepickerDate");
              }
              var formatNum = function(val, fmt) {
                var n = parseFloat(val) || 0;
                var fixed = n.toFixed(2);
                if (fmt === "EUROPEAN_FORMAT") {
                  return fixed.replace(".", ",");
                }
                return fixed;
              };
              progress = formatNum(message.html.overall_progress_perc, numFormat);

              userName.val(message.html.user_full_name);
              responsible.val(message.html.assigned_user_id);
              factorField.val(progress);

              // Costo estimado del trabajo
              if (message.html.work_estimated_cost !== undefined && message.html.work_estimated_cost !== null) {
                estimatedCost = formatNum(message.html.work_estimated_cost, numFormat);
                costField.val(estimatedCost);
              } else {
                if (numFormat === "EUROPEAN_FORMAT") {
                  costField.val("0,00");
                } else {
                  costField.val("0.00");
                }
              }

              // Costo ejecutado del trabajo
              console.log("[GET_PROJECT_JOB] cost_work_performed raw="+message.html.cost_work_performed+" | field id=#cost_work_performed-"+id+" | field.length="+costWorkPerformedField.length+" | field.val antes="+costWorkPerformedField.val());
              if (message.html.cost_work_performed !== undefined && message.html.cost_work_performed !== null) {
                var costPerformed;
                costPerformed = formatNum(message.html.cost_work_performed, numFormat);
                costWorkPerformedField.val(costPerformed);
                console.log("[GET_PROJECT_JOB] cost_work_performed seteado="+costWorkPerformedField.val());
              } else {
                costWorkPerformedField.val(numFormat === "EUROPEAN_FORMAT" ? "0,00" : "0.00");
              }

              // Situación del trabajo
              var situacionField = jQuery("#work_situation-" + id);
              if (situacionField.length && message.html.work_situation !== undefined && message.html.work_situation !== null) {
                situacionField.val(message.html.work_situation);
              }

              setTotalContribution(id);
              validateFactor(id);

              updateTotalEstimatedCost(projectTableId);
            }
          } catch (e) {
            console.log("[TASK_PROJECT] CATCH error:", e);
            alert(e);
          }
          }
        });
        evt.preventDefault();
      }
    );
  } // end if !_taskProjectJobListenerRegistered
  jQuery(document).ready(onDocumentReadyHandler);
})(jQuery);
