(function (jQuery) {
  //private variables
  var ekkoLightBox,
    tableFields = new Map(),
    totaJobInitValue = 0,
    myTimyMce = [],
    index = 0,
    idFieldTable = "",
    MODAL_ID = "";

  // private method
  var americanToEuropean = function (number) {
    if (isNaN(number) || number === "" || number === undefined) {
      return "0,00";
    }
    return number.toLocaleString("de-DE", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    });
  };

  var europeanToAmerican = function (numberString) {
    if (numberString === "" || numberString === undefined || numberString === null) {
      return 0.0;
    }
    // Eliminar espacios y convertir formato europeo a americano
    var cleanString = String(numberString).trim().replace(/\./g, "").replace(",", ".");
    var result = parseFloat(cleanString);
    return isNaN(result) ? 0.0 : result;
  };
  var updateAllTableFields = function () {
    var tableIds = jQuery('tbody[id ^= "tbody-planned-tasks-"]'),
      totalHoursReported = jQuery("#total_hours_reported"),
      totalCostReported = jQuery("#total_cost_reported"),
      total = 0,
      totalCost = 0,
      numFormat = "",
      granTotal = 0;

    tableIds.each(function (index, obj) {
      var dummy = jQuery(obj).attr("id").split("-");
      updateTableWorks(dummy[3]);
    });
    tableIds.each(function (index, obj) {
      var dummy = jQuery(obj).attr("id").split("-"),
        idTable = dummy[3];
      if (numFormat === "" || numFormat === undefined) {
        numFormat = jQuery("#tbody-job-report-" + idTable).attr(
          "data-num-format",
        );
      }
      tableFields.forEach((value, key) => {
        var total = 0;
        if (value !== "estimated_time") {
          if (value === "time_used") {
            if (numFormat === "EUROPEAN_FORMAT") {
              total = europeanToAmerican(
                jQuery("#total_time_reported-" + idTable).val(),
              );
            } else {
              total = parseFloat(
                jQuery("#total_time_reported-" + idTable).val(),
              );
            }
          } else {
            if (numFormat === "EUROPEAN_FORMAT") {
              total = europeanToAmerican(
                jQuery("#" + value + "-" + idTable).val(),
              );
            } else {
              total = parseFloat(jQuery("#" + value + "-" + idTable).val());
            }
          }
        }
        if (!isNaN(total)) {
          granTotal += total;
        }
      });
    });
    total = parseFloat(totaJobInitValue) + granTotal;
    if (numFormat === "EUROPEAN_FORMAT") {
      total = americanToEuropean(total);
      totalHoursReported.val(total);
    } else {
      total = total.toFixed(2);
      totalHoursReported.val(total);
    }

    // Calcular total de costos
    totalCost = calculateTotalCost(numFormat);
    if (numFormat === "EUROPEAN_FORMAT") {
      totalCostReported.val(americanToEuropean(totalCost));
    } else {
      totalCostReported.val(totalCost.toFixed(2));
    }
  };

  var calculateTotalCost = function (numFormat) {
    var totalCost = 0;
    // Sumar costos de todas las filas de tareas y acciones
    jQuery(
      'input[name="planned_tasks[actual_cost][]"], input[name="performed_tasks[actual_cost][]"], input[name="planned_actions[actual_cost][]"], input[name="performed_actions[actual_cost][]"], input[name="report_job[actual_cost][]"]',
    ).each(function () {
      var val = jQuery(this).val();
      if (val && val !== "") {
        if (numFormat === "EUROPEAN_FORMAT") {
          totalCost += europeanToAmerican(val);
        } else {
          totalCost += parseFloat(val) || 0;
        }
      }
    });
    return totalCost;
  };

  var updateTableField = function (idTable) {
    var totalTaskAndAction = 0,
      numFormat = "";
    if (idTable !== "") {
      tableFields.forEach((value, key) => {
        var dummy = key.split("@"),
          total = 0,
          tBody = jQuery("#" + dummy[1] + idTable),
          numFormat = tBody.attr("data-num-format");
        tBody.find("." + value).each(function (index, field) {
          var fieldObj = jQuery(field);
          if (fieldObj.val()) {
            if (numFormat === "EUROPEAN_FORMAT") {
              total += europeanToAmerican(fieldObj.val());
            } else {
              total += parseFloat(fieldObj.val());
            }
          }
        });
        if (value !== "time_used" && value !== "estimated_time") {
          totalTaskAndAction += total;
          if (numFormat === "EUROPEAN_FORMAT") {
            total = americanToEuropean(total);
            jQuery("#" + value + "-" + idTable).val(total);
          } else {
            total = total.toFixed(2);
            jQuery("#" + value + "-" + idTable).val(total);
          }
        }
      });
      if (totaJobInitValue > totalTaskAndAction) {
        totaJobInitValue -= totalTaskAndAction;
      }
      
      // Calcular totales de costo por sección
      updateSectionCostTotals(idTable, numFormat);
    }
    jQuery(".tox-statusbar__branding").addClass("hide");
  };
  
  var updateSectionCostTotals = function (idTable, numFormat) {
    // Tareas planeadas
    var plannedTasksCost = 0;
    jQuery("#tbody-planned-tasks-" + idTable).find('input[name="planned_tasks[actual_cost][]"]').each(function () {
      var val = jQuery(this).val();
      if (val && val !== "") {
        plannedTasksCost += (numFormat === "EUROPEAN_FORMAT") ? europeanToAmerican(val) : (parseFloat(val) || 0);
      }
    });
    var plannedTasksCostField = jQuery("#planned-tasks-total-cost-" + idTable);
    if (plannedTasksCostField.length) {
      plannedTasksCostField.val(numFormat === "EUROPEAN_FORMAT" ? americanToEuropean(plannedTasksCost) : plannedTasksCost.toFixed(2));
    }
    
    // Tareas realizadas
    var performedTasksCost = 0;
    jQuery("#tbody-tasks-performed-" + idTable).find('input[name="performed_tasks[actual_cost][]"]').each(function () {
      var val = jQuery(this).val();
      if (val && val !== "") {
        performedTasksCost += (numFormat === "EUROPEAN_FORMAT") ? europeanToAmerican(val) : (parseFloat(val) || 0);
      }
    });
    var performedTasksCostField = jQuery("#performed-tasks-total-cost-" + idTable);
    if (performedTasksCostField.length) {
      performedTasksCostField.val(numFormat === "EUROPEAN_FORMAT" ? americanToEuropean(performedTasksCost) : performedTasksCost.toFixed(2));
    }
    
    // Acciones planeadas
    var plannedActionsCost = 0;
    jQuery("#tbody-planned-tasks-" + idTable).find('input[name="planned_actions[actual_cost][]"]').each(function () {
      var val = jQuery(this).val();
      if (val && val !== "") {
        plannedActionsCost += (numFormat === "EUROPEAN_FORMAT") ? europeanToAmerican(val) : (parseFloat(val) || 0);
      }
    });
    var plannedActionsCostField = jQuery("#planned-action-total-cost-" + idTable);
    if (plannedActionsCostField.length) {
      plannedActionsCostField.val(numFormat === "EUROPEAN_FORMAT" ? americanToEuropean(plannedActionsCost) : plannedActionsCost.toFixed(2));
    }
    
    // Acciones realizadas
    var performedActionsCost = 0;
    jQuery("#tbody-tasks-performed-" + idTable).find('input[name="performed_actions[actual_cost][]"]').each(function () {
      var val = jQuery(this).val();
      if (val && val !== "") {
        performedActionsCost += (numFormat === "EUROPEAN_FORMAT") ? europeanToAmerican(val) : (parseFloat(val) || 0);
      }
    });
    var performedActionsCostField = jQuery("#performed-action-total-cost-" + idTable);
    if (performedActionsCostField.length) {
      performedActionsCostField.val(numFormat === "EUROPEAN_FORMAT" ? americanToEuropean(performedActionsCost) : performedActionsCost.toFixed(2));
    }
  };

  var updateTableWorks = function (idTable) {
    var totalEstimatedTime = jQuery("#total_estimated_time-" + idTable),
      totalTimeUsed = jQuery("#total_time_reported-" + idTable),
      total = 0.0;
    if (idTable !== "") {
      tableFields.forEach((value, key) => {
        var dummy = key.split("@"),
          fieldFound = false,
          tBody = jQuery("#" + dummy[1] + idTable),
          numFormat = tBody.attr("data-num-format");
        // Si no se encuentra el formato, intentar obtenerlo del tbody principal
        if (!numFormat) {
          numFormat = jQuery("#tbody-planned-tasks-" + idTable).attr("data-num-format");
        }
        total = 0;
        tBody.find("." + value).each(function (index, field) {
          var fieldObj = jQuery(field);
          if (fieldObj.val()) {
            fieldFound = true;
            var fieldValue = fieldObj.val().toString().trim();
            var parsedValue = 0;
            if (numFormat === "EUROPEAN_FORMAT") {
              parsedValue = europeanToAmerican(fieldValue);
            } else {
              parsedValue = parseFloat(fieldValue) || 0;
            }
            total += parsedValue;
          }
        });

        if (!isNaN(total)) {
          total += 0.0;
          if (
            value === "time_used" &&
            totalTimeUsed.val() !== "undefined" &&
            totalTimeUsed.val() !== undefined &&
            fieldFound
          ) {
            if (numFormat === "EUROPEAN_FORMAT") {
              total = americanToEuropean(total);
              totalTimeUsed.val(total);
            } else {
              total = total.toFixed(2);
              totalTimeUsed.val(total);
            }
          } else if (
            value === "estimated_time" &&
            totalEstimatedTime.val() !== "undefined" &&
            totalEstimatedTime.val() !== undefined &&
            fieldFound
          ) {
            if (numFormat === "EUROPEAN_FORMAT") {
              total = americanToEuropean(total);
              totalEstimatedTime.val(total);
            } else {
              total = total.toFixed(2);
              totalEstimatedTime.val(total);
            }
          } else if (fieldFound) {
            if (numFormat === "EUROPEAN_FORMAT") {
              total = americanToEuropean(total);
              jQuery("#" + value + "-" + idTable).val(total);
            } else {
              total = total.toFixed(2);
              jQuery("#" + value + "-" + idTable).val(total);
            }
          }
        }
      });
    }
  };

  var tinyMce = function (id) {
    tinymce.init({
      selector: "textarea" + id,
      height: 180,
      menubar: false,
      plugins: [
        "advlist autolink lists link image charmap print preview anchor",
        "searchreplace visualblocks code fullscreen",
        "insertdatetime media table paste code help wordcount",
      ],
      toolbar: "",
      content_style:
        "body { font-family:Helvetica,Arial,sans-serif; font-size:12px }",
    });
  };

  //public method
  var addRowToTable = function (obj, tBody, idTable) {
    var row = jQuery("#" + tBody),
      btn = jQuery(obj),
      sequence = btn.attr("data-sequence"),
      tableName = jQuery("#tfoot-" + idTable).attr("data-field-name"),
      templateName = btn.attr("data-template"),
      rowId = Math.floor(Math.random() * 50000) + 1,
      template = jQuery("#" + templateName)
        .html()
        .replace(/__NUM__/g, sequence)
        .replace(/__ID__/g, rowId),
      taskRow = jQuery(template);
    if (sequence === "0") {
      row.empty();
    }
    row.append(taskRow);
    sequence = parseInt(sequence) + 1;
    btn.attr("data-sequence", sequence.toString());
    if (tableName === "planned_unregistered-table") {
      tinyMce("#task_advanced_report-" + rowId);
      setTimeout(function () {
        jQuery(".tox-statusbar__branding").addClass("hide");
      }, 800);
    }
  };

  var deleteRow = function (buttonElement, tr, idTable) {
    var dummy = tr.split("-"),
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
      var addBtn = rows.parent().find("tfoot").eq(0).find("button").eq(0),
        colspan = jQuery(jQuery(buttonElement).attr("data-colspan") + idTable),
        template = colspan.html();
      //template = jQuery ('#' + rows.attr('id') + '-template').html ();
      rows.append(template);
      addBtn.attr("data-sequence", "0");
    }
    updateAllTableFields();
    //updateTableWorks (idTable)
  };

  var filterTaskView = function (obj, idTaskView) {
    var filter = jQuery(obj).val(),
      tBody = jQuery("#task-view-" + idTaskView);

    tBody.find("tr").each(function (index, tr) {
      var row = jQuery(tr),
        dummy = row.attr("id").split("_");
      if (filter !== "") {
        if (dummy[0] === filter || dummy[1] === filter) {
          row.removeClass("hide");
        } else {
          row.addClass("hide");
        }
      } else {
        row.removeClass("hide");
      }
    });
  };

  var getReportsTask = function (obj, idRow, id) {
    var arguments = {},
      progressRow = jQuery("#task_progress_perc-" + idRow),
      record = jQuery(obj).val(),
      reportDate = jQuery("#jscal_field_daily_report_date"),
      reportRow = "#task_advanced_report-" + idRow,
      timeRow = jQuery("#time_reported-" + idRow),
      reportIds = jQuery("#task_advanced_report-id-" + idRow);

    tinyMce(reportRow);
    if (record !== "") {
      arguments = {
        module: "daily_report",
        action: "AjaxEditViewUtils",
        function: "DAILY_REPORT_DATA",
        record: record,
        period: encodeURIComponent(reportDate.val()),
        Ajax: "true",
      };
      jQuery.post("index.php", arguments, function (data) {
        try {
          var message = JSON.parse(JSON.stringify(data));
          if (message.error !== "OK") {
            throw message.error;
          } else {
            progressRow.val(message.html.progress);
            timeRow.val(message.html.time);
            reportIds.val(message.html.ids);
            tinymce.activeEditor.setContent(message.html.html);
            updateAllTableFields();
            //updateTableWorks (idTable)
            idFieldTable = "";
          }
        } catch (e) {
          jQuery(".tox-statusbar__branding").addClass("hide");
          alert(e);
        }
      });
    } else {
      tinymce.activeEditor.setContent("");
    }
  };

  var getTaskFromModal = function (obj, e) {
    var arguments = {},
      row = jQuery(obj),
      btn =
        MODAL_ID !== ""
          ? jQuery("#" + MODAL_ID)
              .find("button")
              .eq(0)
          : "",
      infoTask = row.html().substring(0, 60),
      dummyData = row.attr("rel").split("@"),
      moduleName = row.attr("data-module"),
      displayTask = jQuery("#reported_task-display-" + dummyData[2]),
      idTask = jQuery("#reported_task-id-" + dummyData[2]),
      moduleTask = jQuery("#reported_task-module-" + dummyData[2]).val(
        moduleName,
      ),
      progressRow = jQuery("#task_progress_perc-" + dummyData[2]),
      timeRow = jQuery("#time_reported-" + dummyData[2]),
      reportRow = "#task_advanced_report-" + dummyData[2],
      reportIds = jQuery("#task_advanced_report-id-" + dummyData[2]);

    displayTask.val(infoTask);
    idTask.val(dummyData[0]);
    tinyMce(reportRow);
    arguments = {
      module: "daily_report",
      action: "AjaxEditViewUtils",
      function: "DAILY_REPORT_DATA",
      record: dummyData[0],
      period: encodeURIComponent(dummyData[3]),
      Ajax: "true",
    };

    jQuery.post("index.php", arguments, function (data) {
      try {
        var message = JSON.parse(JSON.stringify(data));
        if (message.error !== "OK") {
          throw message.error;
        } else {
          progressRow.val(message.html.progress);
          timeRow.val(message.html.time);
          reportIds.val(message.html.ids);
          tinymce.activeEditor.setContent(message.html.html);
          updateAllTableFields();
          //updateTableWorks (idTable)
        }
      } catch (e) {
        jQuery(".tox-statusbar__branding").addClass("hide");
        alert(e);
      }
    });
    if (btn !== "") {
      btn.trigger("click");
    }

    e.preventDefault();
    e.stopPropagation();
  };

  var getUnfinishedJob = function (obj, idRow, idTable) {
    var unfinishedJob = jQuery(obj),
      selectedJob = jQuery("#global_record-" + idRow),
      estimatedTime = jQuery("#estimated_time-" + idRow),
      advanceRate = jQuery("#advance_rate-" + idRow),
      dataJob = unfinishedJob
        .find("option:selected")
        .attr("data-job")
        .split("@");
    estimatedTime.val(dataJob[0]);
    advanceRate.val(dataJob[1]);
    updateAllTableFields();
    //updateTableWorks(idTable)
    selectedJob.val(unfinishedJob.val());
  };

  var moveRowUp = function (btn, tr) {
    var rowToMove = jQuery("#" + tr),
      prev = rowToMove.prev("tr.tabla-field-row");
    prev.before(rowToMove);
  };

  var moveRowDown = function (btn, tr) {
    var rowToMove = jQuery("#" + tr),
      next = rowToMove.next("tr.tabla-field-row");
    next.after(rowToMove);
  };

  var openModal = function (obj, e, id) {
    var url = jQuery(obj).attr("href"),
      titleModal = jQuery(obj).attr("title"),
      regex = new RegExp("_JOBS", "i"),
      reportDate = jQuery("#jscal_field_daily_report_date");
    if (reportDate.val() !== "") {
      url = url + "&period=" + encodeURIComponent(reportDate.val());
      ekkoLightBox = jQuery(
        '<a href="' +
          url +
          '" data-width="950" data-toggle="lightbox" data-gallery="remoteload" data-title="' +
          titleModal +
          '">&nbsp;</a>',
      );
      idFieldTable = id;

      ekkoLightBox.ekkoLightbox({
        loadingMessage: "Cargando...",
        onShown: function () {
          MODAL_ID = this.modal_id;
        },
        onNavigate: function (direction, itemIndex) {},
        onHidden: function () {
          MODAL_ID = "";
        },
      });
    } else {
      alert("Fecha del reporte?");
      reportDate.focus();
    }
    e.preventDefault();
  };

  var searchJob = function (obj, id) {
    var word = jQuery(obj).val().toLowerCase(),
      regex = new RegExp(word, "i"),
      jobList = jQuery(".search-" + id);

    if (word !== "") {
      jobList.each(function () {
        const content = jQuery(this).html().toLowerCase();
        if (regex.test(content)) {
          jQuery(this).parent().removeClass("hidden");
        } else {
          jQuery(this).parent().addClass("hidden");
        }
      });
    } else {
      jobList.each(function () {
        jQuery(this).parent().removeClass("hidden");
      });
    }
  };

  var setModuleActivity = function (obj, id) {
    var taskCondition = jQuery("#reported_task_condition-" + id).val(),
      module = jQuery(obj),
      record = jQuery("#module_related_record" + id),
      tabName = jQuery("#module_related-" + id),
      dummy,
      label;
    if (module.val() !== "") {
      dummy = module.val().split("@");
      module.attr("data-referenced-module", dummy[0]);
      label = module.find("option:selected").text();
      module.attr("data-title", label);
      tabName.val(dummy[0]);
      record.val("");
      RelatedModuleModalUtils.openModal(obj);
    } else {
      module.attr("data-referenced-module", "");
      module.attr("data-title", "");
      record.val("");
    }
  };

  var setTasksBayJob = function (obj, e) {
    var job = jQuery(obj),
      dummyData = job.attr("rel").split("@"),
      taskList = jQuery("#planned_task_list-" + dummyData[1]),
      crmId = jQuery("#planned_job-id-" + dummyData[1]),
      crmTitle = jQuery("#planned_job-display-" + dummyData[1]);

    taskList.empty();
    crmId.val(dummyData[0]);
    crmTitle.val(job.attr("data-display"));
    taskList.append(
      jQuery("<option>", {
        value: "",
        text: "Seleccione una tarea",
      }),
    );
    this.taskByJob.get(parseInt(dummyData[0])).forEach((data, index) => {
      var doc = new DOMParser().parseFromString(data[1], "text/html");
      taskList.append(
        jQuery("<option>", {
          value: data[0],
          text: doc.documentElement.textContent,
        }),
      );
    });
    jQuery(".modal-header").find("button").trigger("click");
    e.preventDefault();
    e.stopPropagation();
  };

  var setUnregisteredActivity = function (obj, id) {
    var module = jQuery("#reported_task_module-" + id),
      taskCondition = jQuery(obj).val();
    if (taskCondition !== "") {
      module.removeClass("hide");
    } else {
      module.addClass("hide");
    }
  };

  var updateNumFields = function (obj, idTable) {
    var field = jQuery(obj);
    field.val(field.val().replace(/[^\d.,-]/g, ""));
    updateAllTableFields();
    //updateTableWorks (idTable);
  };

  var uploadDoc = function (obj, id) {
    var moduleName = jQuery("#" + jQuery(obj).attr("data-module")).val(),
      recordId = jQuery("#" + jQuery(obj).attr("data-id")).val(),
      url =
        "index.php?module=daily_report&action=AjaxEditViewUtils&function=ATTACHMENT_DOC&record=" +
        recordId +
        "&formodule=" +
        moduleName +
        "&Ajax=true";
    if (moduleName !== "" && recordId !== "") {
      ekkoLightBox = jQuery(
        '<a href="' +
          url +
          '" data-width="950" data-toggle="lightbox" data-gallery="remoteload" data-title="Documentos:">&nbsp;</a>',
      );
      idFieldTable = id;

      ekkoLightBox.ekkoLightbox({
        loadingMessage: "Cargando tareas...",
        onShown: function () {
          MODAL_ID = this.modal_id;
        },
        onNavigate: function (direction, itemIndex) {},
        onHidden: function () {
          MODAL_ID = "";
        },
      });
    } else {
      alert("No has seleccionado un registro?");
    }
  };

  var uploadTaskEvidence = function (obj, rowId, taskSelectId) {
    var activityId = jQuery("#" + taskSelectId).val();
    console.log("uploadTaskEvidence - rowId:", rowId, "taskSelectId:", taskSelectId, "activityId:", activityId);
    if (!activityId || activityId === "" || !jQuery.isNumeric(activityId)) {
      alert("Primero selecciona una tarea válida");
      return;
    }
    var url =
      "index.php?module=daily_report&action=AjaxEditViewUtils&function=ATTACHMENT_DOC&record=" +
      activityId +
      "&formodule=Calendar&Ajax=true";

    // Guardar referencia al contenedor de evidencias (hermano del enlace clickeado)
    var evidenceContainer = jQuery(obj).siblings(".evidence-list");
    var currentActivityId = activityId;

    ekkoLightBox = jQuery(
      '<a href="' +
        url +
        '" data-width="950" data-toggle="lightbox" data-gallery="remoteload" data-title="Evidencias de la tarea:">&nbsp;</a>'
    );

    ekkoLightBox.ekkoLightbox({
      loadingMessage: "Cargando...",
      onShown: function () {
        MODAL_ID = this.modal_id;
      },
      onHidden: function () {
        MODAL_ID = "";
        // Actualizar la lista de evidencias después de cerrar el modal
        console.log("Modal closed - refreshing evidence list for activityId:", currentActivityId);
        setTimeout(function() {
          refreshEvidenceListDirect(evidenceContainer, currentActivityId);
        }, 300);
      },
    });
  };

  var refreshEvidenceListDirect = function (container, activityId) {
    console.log("refreshEvidenceListDirect called with activityId:", activityId, "container:", container.length);
    
    if (container.length === 0) {
      console.log("Container not found, aborting");
      return;
    }
    
    jQuery.ajax({
      url: "index.php?module=daily_report&action=AjaxEditViewUtils&function=FETCH_TASK_EVIDENCES&activityId=" + activityId,
      type: "GET",
      dataType: "json",
      success: function (response) {
        console.log("AJAX response:", response);
        if (response.success && response.attachments) {
          var html = "";
          response.attachments.forEach(function (attachment) {
            html += '<div class="evidence-item" style="color: #333333;">';
            html += '<i class="fa fa-file" aria-hidden="true"></i> ' + attachment.name;
            html += '</div>';
          });
          container.html(html);
        }
      },
      error: function (xhr, status, error) {
        console.log("Error al obtener las evidencias:", status, error);
      }
    });
  };

  var refreshEvidenceList = function (rowId, activityId) {
    console.log("refreshEvidenceList called with rowId:", rowId, "activityId:", activityId);
    var evidenceListContainer = jQuery("#evidence-list-" + rowId);
    console.log("Container found:", evidenceListContainer.length);
    
    if (evidenceListContainer.length === 0) {
      console.log("Container not found, trying to find by task select...");
      // Intentar encontrar el contenedor buscando el select de tarea y luego el contenedor hermano
      var taskSelect = jQuery("#planned_task_list-" + rowId);
      if (taskSelect.length > 0) {
        evidenceListContainer = taskSelect.closest("tr").find(".evidence-list");
        console.log("Container found via task select:", evidenceListContainer.length);
      }
    }
    
    if (evidenceListContainer.length === 0) {
      console.log("Container still not found, aborting");
      return;
    }
    
    jQuery.ajax({
      url: "index.php?module=daily_report&action=AjaxEditViewUtils&function=FETCH_TASK_EVIDENCES&activityId=" + activityId,
      type: "GET",
      dataType: "json",
      success: function (response) {
        console.log("AJAX response:", response);
        if (response.success && response.attachments) {
          var html = "";
          response.attachments.forEach(function (attachment) {
            html += '<div class="evidence-item" style="color: #333333;">';
            html += '<i class="fa fa-file" aria-hidden="true"></i> ' + attachment.name;
            html += '</div>';
          });
          evidenceListContainer.html(html);
        }
      },
      error: function (xhr, status, error) {
        console.log("Error al obtener las evidencias:", status, error);
      }
    });
  };

  window.DailyReportUtils = {
    addRowToTable: addRowToTable,
    delRowToTable: deleteRow,
    filterTaskView: filterTaskView,
    getReportsTask: getReportsTask,
    getTaskFromModal: getTaskFromModal,
    getUnfinishedJob: getUnfinishedJob,
    moveRowDown: moveRowDown,
    moveRowUp: moveRowUp,
    openModal: openModal,
    searchJob: searchJob,
    setModuleActivity: setModuleActivity,
    setTasksBayJob: setTasksBayJob,
    setUnregisteredActivity: setUnregisteredActivity,
    updateNumFields: updateNumFields,
    uploadDoc: uploadDoc,
    uploadTaskEvidence: uploadTaskEvidence,
    taskByJob: new Map(),
  };

  var onDocumentReadyHandler = function () {
    var tableIds = jQuery('table[id ^= "planned-tasks-table-"]'),
      totalHoursReported = jQuery("#total_hours_reported"),
      userAssingTo = jQuery("#assigntype-g"),
      numFormat = "",
      reportDate = jQuery("#jscal_field_daily_report_date"),
      userSelect = jQuery("#td_assigned_user_id").find("select").eq(0);
    jQuery(".edit-tinyMce").each(function (index, obj) {
      var id = jQuery(obj).attr("id");
      tinyMce("#" + id);
    });
    reportDate.next("script[type='text/javascript']").remove();
    reportDate.on("click", function (e) {
      e.preventDefault();
      e.stopPropagation();
      jQuery(".datepicker").remove();
      reportDate
        .parent()
        .parent()
        .append('<span class="required">Imposible cambiar fecha!</span>');
      setTimeout(function () {
        reportDate
          .parent()
          .parent()
          .find("span.required")
          .fadeOut(500, function () {
            $(this).remove(); // Elimina el elemento del DOM después de que desaparezca
          });
      }, 3000);
    });
    reportDate.on("contextmenu", function (e) {
      e.preventDefault();
      e.stopPropagation();
      jQuery(".datepicker").remove();
    });

    // Manejador para precargar tareas con reportes existentes
    jQuery(document).on(
      "dailyReportPreloadTasks",
      function (event, preloadedTasks, rowId) {
        if (!preloadedTasks || preloadedTasks.length === 0) {
          return;
        }

        preloadedTasks.forEach(function (task) {
          // Llenar campos del trabajo
          var crmId = jQuery("#planned_job-id-" + rowId);
          var crmTitle = jQuery("#planned_job-display-" + rowId);
          var taskList = jQuery("#planned_task_list-" + rowId);

          crmId.val(task.jobid);
          crmTitle.val(task.jobtitle);

          // Llenar dropdown de tareas si está vacío
          if (taskList.find("option").length <= 1) {
            taskList.empty();
            taskList.append(
              jQuery("<option>", {
                value: "",
                text: "Seleccione una tarea",
              }),
            );

            // Agregar tareas del trabajo
            if (DailyReportUtils.taskByJob.has(task.jobid)) {
              DailyReportUtils.taskByJob
                .get(task.jobid)
                .forEach(function (data) {
                  var doc = new DOMParser().parseFromString(
                    data[1],
                    "text/html",
                  );
                  taskList.append(
                    jQuery("<option>", {
                      value: data[0],
                      text: doc.documentElement.textContent,
                    }),
                  );
                });
            }
          }

          // Seleccionar la tarea
          taskList.val(task.activityid);

          // Disparar el evento change para cargar los datos del reporte
          taskList.trigger("change");
        });
      },
    );

    tableFields.set("1@tbody-job-report-", "time_used");
    tableFields.set("2@tbody-job-report-", "estimated_time");
    tableFields.set("3@tbody-planned-tasks-", "planned-tasks-total-time");
    tableFields.set("4@tbody-tasks-performed-", "performed-tasks-total-time");
    tableFields.set("5@tbody-planned-tasks-", "planned-action-total-time");
    tableFields.set("6@tbody-tasks-performed-", "performed-action-total-time");
    setTimeout(function () {
      tableIds.each(function (index, obj) {
        var dummy = jQuery(obj).attr("id").split("-");
        if (numFormat === "" || numFormat === undefined) {
          numFormat = jQuery("#tbody-job-report-" + dummy[3]).attr(
            "data-num-format",
          );
        }
        updateTableField(dummy[3]);
      });
    }, 800);
    userAssingTo.attr("readonly", true);
    userSelect.prop("disabled", true);
    if (totalHoursReported.val() === "NaN") {
      totalHoursReported.val("0");
      totaJobInitValue = 0;
    } else {
      if (numFormat === "EUROPEAN_FORMAT") {
        totaJobInitValue = europeanToAmerican(totalHoursReported.val());
      } else {
        totaJobInitValue = totalHoursReported.val();
      }
    }
    totalHoursReported.attr("readonly", true);
    jQuery("#total_cost_reported").attr("readonly", true);
    updateAllTableFields();
  };

  jQuery(document).ready(onDocumentReadyHandler);
})(jQuery);
