/**
 * Utilidades para la pestaña "Por Proveedor" en Acciones en curso
 * @version 1.0
 */
var SupplierWorkUtils = (function () {
  "use strict";

  /**
   * Maneja la selección de un proveedor del dropdown
   * @param {Event} event
   * @param {HTMLElement} element
   * @param {string} tabId
   */
  var selectedSupplier = function (event, element, tabId) {
    event.preventDefault();
    var $element = jQuery(element);
    var supplierId = $element.attr("rel");
    var supplierName = $element.attr("title");

    // Actualizar UI - marcar como activo
    jQuery("#supplier-menu-" + tabId + " li").removeClass("active");
    $element.parent().addClass("active");

    // Actualizar el nombre del proveedor mostrado
    jQuery("#supplier-name-" + tabId).text(supplierName);

    // Actualizar el campo hidden con el ID del proveedor
    jQuery("#supplierid-" + tabId).val(supplierId);

    // Ejecutar búsqueda automáticamente
    var form = jQuery("#supplier_work_form-" + tabId);
    var searchButton = form.find('button[name="submitSearch"]')[0];
    if (searchButton) {
      goToPage(event, searchButton, tabId);
    }
  };

  /**
   * Maneja el cambio de periodo - muestra/oculta los campos de fecha personalizada
   * y actualiza el conteo de tareas en el dropdown de proveedores
   * @param {HTMLElement} element
   * @param {string} tabId
   */
  var selectedPeriod = function (element, tabId) {
    var period = jQuery(element).val();
    var $dateFromContainer = jQuery("#date-from-container-" + tabId);
    var $dateToContainer = jQuery("#date-to-container-" + tabId);

    if (period === "custom") {
      // Mostrar campos de fecha
      $dateFromContainer.removeClass("hide");
      $dateToContainer.removeClass("hide");
      // Inicializar datepickers si no están inicializados
      initDatePickers(tabId);
    } else {
      // Ocultar campos de fecha
      $dateFromContainer.addClass("hide");
      $dateToContainer.addClass("hide");
      // Actualizar el dropdown de proveedores con el nuevo período (solo si no es custom)
      if (period !== "") {
        refreshSuppliersDropdown(tabId);
      }
    }
  };

  /**
   * Actualiza el dropdown de proveedores con el conteo de tareas según el período seleccionado
   * @param {string} tabId
   */
  var refreshSuppliersDropdown = function (tabId) {
    var period = jQuery("#period-dates-" + tabId).val();
    var fromDate = jQuery("#start-date-" + tabId).val();
    var toDate = jQuery("#end-date-" + tabId).val();
    var currentSupplierId = jQuery("#supplierid-" + tabId).val();

    jQuery.ajax({
      type: "POST",
      url: "index.php",
      data: {
        module: "Home",
        action: "AjaxDeskUtils",
        Ajax: true,
        function: "REFRESH_SUPPLIERS",
        periodtask: period,
        datestart: fromDate,
        duedate: toDate,
      },
      dataType: "json",
      success: function (response) {
        if (response.error === "OK" && response.suppliers) {
          updateSuppliersMenu(tabId, response.suppliers, currentSupplierId);
          // Si el proveedor actual ya no está en la lista, seleccionar el primero
          if (response.suppliers.length > 0) {
            var supplierIds = response.suppliers.map(function(s) { return String(s.id); });
            if (!currentSupplierId || supplierIds.indexOf(String(currentSupplierId)) === -1) {
              var firstSupplierId = response.suppliers[0].id;
              jQuery("#supplierid-" + tabId).val(firstSupplierId);
              jQuery("#supplier-menu-" + tabId + " li").removeClass("active");
              jQuery("#supplier-menu-" + tabId + " li:first").addClass("active");
            }
          } else {
            // No hay proveedores, limpiar selección
            jQuery("#supplierid-" + tabId).val("");
          }
        }
      },
      error: function () {
        console.error("Error al actualizar proveedores");
      },
    });
  };

  /**
   * Actualiza el menú de proveedores con los nuevos datos
   * @param {string} tabId
   * @param {Array} suppliers
   * @param {string} currentSupplierId
   */
  var updateSuppliersMenu = function (tabId, suppliers, currentSupplierId) {
    var $menu = jQuery("#supplier-menu-" + tabId);
    $menu.empty();

    if (suppliers && suppliers.length > 0) {
      suppliers.forEach(function (supplier) {
        var isActive = supplier.id == currentSupplierId ? ' class="active"' : "";
        var html =
          "<li" + isActive + ">" +
          '<a href="#" title="' + supplier.name + '" rel="' + supplier.id + '" ' +
          "onclick=\"SupplierWorkUtils.selectedSupplier(event, this, '" + tabId + "')\">" +
          '<i class="fa fa-building-o"></i>&nbsp;' +
          supplier.name +
          "</a>" +
          "</li>";
        $menu.append(html);
      });
    } else {
      $menu.append(
        '<li class="disabled">' +
        '<a href="#" style="color: #999; cursor: default;">' +
        '<i class="fa fa-info-circle"></i>&nbsp;No hay proveedores con tareas en este período' +
        "</a>" +
        "</li>"
      );
    }
  };

  /**
   * Inicializa los datepickers para los campos de fecha
   * @param {string} tabId
   */
  var initDatePickers = function (tabId) {
    var $startDate = jQuery("#start-date-" + tabId);
    var $endDate = jQuery("#end-date-" + tabId);

    // Solo inicializar si no tienen datepicker
    if (!$startDate.data("datepicker")) {
      $startDate.datepicker({
        format: "yyyy-mm-dd",
        autoclose: true,
        todayHighlight: true,
        language: "es",
      });
    }
    if (!$endDate.data("datepicker")) {
      $endDate.datepicker({
        format: "yyyy-mm-dd",
        autoclose: true,
        todayHighlight: true,
        language: "es",
      });
    }
  };

  /**
   * Actualiza proveedores y luego ejecuta la búsqueda automáticamente
   * @param {string} tabId
   */
  var refreshSuppliersDropdownAndSearch = function (tabId) {
    var period = jQuery("#period-dates-" + tabId).val();
    var fromDate = jQuery("#start-date-" + tabId).val();
    var toDate = jQuery("#end-date-" + tabId).val();

    // Mostrar indicador de carga en la tabla
    var tbody = jQuery("#supplier-work-" + tabId);
    tbody.html(
      '<tr><td colspan="8" class="text-center" style="padding: 20px;"><i class="fa fa-spinner fa-spin"></i> Actualizando proveedores...</td></tr>'
    );

    jQuery.ajax({
      type: "POST",
      url: "index.php",
      data: {
        module: "Home",
        action: "AjaxDeskUtils",
        Ajax: true,
        function: "REFRESH_SUPPLIERS",
        periodtask: period,
        datestart: fromDate,
        duedate: toDate,
      },
      dataType: "json",
      success: function (response) {
        if (response.error === "OK" && response.suppliers && response.suppliers.length > 0) {
          // Actualizar menú de proveedores
          updateSuppliersMenu(tabId, response.suppliers, null);
          // Seleccionar el primer proveedor automáticamente
          var firstSupplierId = response.suppliers[0].id;
          jQuery("#supplierid-" + tabId).val(firstSupplierId);
          // Marcar como activo en el menú
          jQuery("#supplier-menu-" + tabId + " li:first").addClass("active");
          // Ahora ejecutar la búsqueda con el proveedor seleccionado
          executeSearch(tabId);
        } else {
          tbody.html(
            '<tr><td colspan="8" class="text-center" style="padding: 20px;"><i class="fa fa-info-circle"></i> No hay proveedores con tareas en este período</td></tr>'
          );
          updateSuppliersMenu(tabId, [], null);
        }
      },
      error: function () {
        console.error("Error al actualizar proveedores");
        tbody.html(
          '<tr><td colspan="8" class="text-center text-danger" style="padding: 20px;"><i class="fa fa-exclamation-triangle"></i> Error al cargar proveedores</td></tr>'
        );
      },
    });
  };

  /**
   * Ejecuta la búsqueda AJAX (función interna)
   * @param {string} tabId
   */
  var executeSearch = function (tabId) {
    var form = jQuery("#supplier_work_form-" + tabId);
    var formData = form.serialize();
    var tbody = jQuery("#supplier-work-" + tabId);

    tbody.html(
      '<tr><td colspan="8" class="text-center" style="padding: 20px;"><i class="fa fa-spinner fa-spin"></i> Cargando...</td></tr>'
    );

    jQuery.ajax({
      url: "index.php",
      type: "POST",
      data: formData,
      dataType: "json",
      success: function (response) {
        if (response.error === "OK") {
          tbody.html(response.html.rows);
          jQuery("#pager-" + tabId).html(response.html.paginator);
          jQuery("#show-records-" + tabId).html(response.html.records);
        } else {
          tbody.html(
            '<tr><td colspan="8" class="text-center text-danger" style="padding: 20px;"><i class="fa fa-exclamation-triangle"></i> ' +
              response.error +
              "</td></tr>"
          );
        }
      },
      error: function (xhr, status, error) {
        console.error("Error en executeSearch:", error);
        tbody.html(
          '<tr><td colspan="8" class="text-center text-danger" style="padding: 20px;"><i class="fa fa-exclamation-triangle"></i> Error al cargar los datos</td></tr>'
        );
      },
    });
  };

  /**
   * Ejecuta la búsqueda AJAX y actualiza la tabla
   * @param {Event} event
   * @param {HTMLElement} buttonElement
   * @param {string} tabId
   */
  var goToPage = function (event, buttonElement, tabId) {
    event.preventDefault();
    var form = jQuery("#supplier_work_form-" + tabId);
    var period = jQuery("#period-dates-" + tabId).val();
    var supplierId = jQuery("#supplierid-" + tabId).val();

    // Si es período custom y no hay proveedor seleccionado, primero actualizar proveedores
    if (period === "custom" && !supplierId) {
      refreshSuppliersDropdownAndSearch(tabId);
      return;
    }

    var formData = form.serialize();

    // Mostrar indicador de carga
    var tbody = jQuery("#supplier-work-" + tabId);
    tbody.html(
      '<tr><td colspan="8" class="text-center" style="padding: 20px;"><i class="fa fa-spinner fa-spin"></i> Cargando...</td></tr>'
    );

    jQuery.ajax({
      url: "index.php",
      type: "POST",
      data: formData,
      dataType: "json",
      success: function (response) {
        if (response.error === "OK") {
          // Actualizar tabla
          tbody.html(response.html.rows);
          // Actualizar paginador
          jQuery("#pager-" + tabId).html(response.html.paginator);
          // Actualizar contador de registros
          jQuery("#show-records-" + tabId).html(response.html.records);
        } else {
          tbody.html(
            '<tr><td colspan="8" class="text-center text-danger" style="padding: 20px;"><i class="fa fa-exclamation-triangle"></i> ' +
              response.error +
              "</td></tr>"
          );
        }
      },
      error: function (xhr, status, error) {
        console.error("Error en SupplierWorkUtils.goToPage:", error);
        tbody.html(
          '<tr><td colspan="8" class="text-center text-danger" style="padding: 20px;"><i class="fa fa-exclamation-triangle"></i> Error al cargar los datos</td></tr>'
        );
      },
    });
  };

  /**
   * Navega a una página específica del paginador
   * @param {Event} event
   * @param {number} page
   * @param {string} tabId
   */
  var navigateToPage = function (event, page, tabId) {
    event.preventDefault();
    var form = jQuery("#supplier_work_form-" + tabId);
    form.find('input[name="page"]').val(page);
    var searchButton = form.find('button[name="submitSearch"]')[0];
    if (searchButton) {
      goToPage(event, searchButton, tabId);
    }
  };

  /**
   * Navega a la vista de Parte de Trabajo del proveedor
   * @param {Event} event
   * @param {HTMLElement} buttonElement
   * @param {string} tabId
   */
  var goToPartWork = function (event, buttonElement, tabId) {
    event.preventDefault();
    var form = jQuery("#supplier_work_form-" + tabId);
    var period = jQuery("#period-dates-" + tabId).val();
    var supplierId = jQuery("#supplierid-" + tabId).val();
    var fromDate = jQuery("#start-date-" + tabId).val();
    var toDate = jQuery("#end-date-" + tabId).val();

    // Validaciones
    if (!supplierId || supplierId === "") {
      alert("¡Debe seleccionar un proveedor!");
      return;
    }
    if (period === "") {
      alert("¡Debe seleccionar un período!");
      return;
    }
    if (period === "custom") {
      if (fromDate === "" || toDate === "") {
        alert("¡Debe especificar las fechas del período personalizado!");
        return;
      }
      // Validar que fecha desde sea menor o igual a fecha hasta
      var dateFrom = fromDate.split("/");
      var dateTo = toDate.split("/");
      var dateFromObj = new Date(dateFrom[2], dateFrom[1] - 1, dateFrom[0]);
      var dateToObj = new Date(dateTo[2], dateTo[1] - 1, dateTo[0]);
      if (dateFromObj > dateToObj) {
        alert('¡La fecha "Desde" no puede ser mayor que la fecha "Hasta"!');
        return;
      }
    }

    // Configurar el formulario para enviar a la vista de Parte de Trabajo
    form.find('input[name="module"]').val("part_work");
    form.find('input[name="action"]').val("SupplierListView");
    form.find('input[name="function"]').val("SUPPLIER_PART_WORK");
    form.find('input[name="page"]').val("0");
    form.find('input[name="Ajax"]').val("false");
    form.submit();
  };

  /**
   * Muestra el diagrama Gantt de las tareas del proveedor en una modal
   * @param {Event} event
   * @param {HTMLElement} buttonElement
   * @param {string} tabId
   */
  var showGantt = function (event, buttonElement, tabId) {
    event.preventDefault();
    var period = jQuery("#period-dates-" + tabId).val();
    var supplierId = jQuery("#supplierid-" + tabId).val();
    var fromDate = jQuery("#start-date-" + tabId).val();
    var toDate = jQuery("#end-date-" + tabId).val();
    var supplierName = jQuery("#supplier-name-" + tabId).text();

    // Validaciones
    if (!supplierId || supplierId === "") {
      alert("¡Debe seleccionar un proveedor!");
      return;
    }
    if (period === "") {
      alert("¡Debe seleccionar un período!");
      return;
    }
    if (period === "custom") {
      if (fromDate === "" || toDate === "") {
        alert("¡Debe especificar las fechas del período personalizado!");
        return;
      }
    }

    // Mostrar modal con loader y scroll horizontal siempre visible (barras finas)
    var modalHtml =
      '<style>' +
      '#supplier-gantt-modal .modal-body { overflow-x: scroll !important; overflow-y: auto !important; scrollbar-width: thin; scrollbar-color: #c1c1c1 #f1f1f1; }' +
      '#supplier-gantt-modal .modal-body::-webkit-scrollbar { height: 6px !important; width: 6px !important; }' +
      '#supplier-gantt-modal .modal-body::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 3px; }' +
      '#supplier-gantt-modal .modal-body::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 3px; }' +
      '#supplier-gantt-modal .modal-body::-webkit-scrollbar-thumb:hover { background: #a1a1a1; }' +
      '#supplier-gantt-modal .card-task-gantt { width: auto !important; min-width: 100% !important; overflow: visible !important; height: auto !important; max-height: none !important; scrollbar-width: thin; }' +
      '#supplier-gantt-modal .card-task-gantt::-webkit-scrollbar { height: 6px !important; width: 6px !important; }' +
      '#supplier-gantt-modal .card-task-gantt::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 3px; }' +
      '#supplier-gantt-modal .card-task-gantt::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 3px; }' +
      '#supplier-gantt-modal .gantt-target { display: block !important; width: auto !important; min-width: 100% !important; }' +
      '#supplier-gantt-modal .gantt-target svg.gantt { display: block !important; min-height: 300px !important; }' +
      '#supplier-gantt-modal .gantt-container { overflow: visible !important; }' +
      '#supplier-gantt-modal [id^="task-gantt-"] { overflow: visible !important; }' +
      '</style>' +
      '<div class="modal fade" id="supplier-gantt-modal" tabindex="-1" role="dialog">' +
      '<div class="modal-dialog modal-lg" style="width: 95%; max-width: 1400px;">' +
      '<div class="modal-content">' +
      '<div class="modal-header">' +
      '<button type="button" class="close" data-dismiss="modal">&times;</button>' +
      '<h4 class="modal-title"><span class="glyphicon glyphicon-indent-left"></span> Diagrama Gantt - Tareas de ' +
      supplierName +
      "</h4>" +
      "</div>" +
      '<div class="modal-body" id="supplier-gantt-content" style="min-height: 400px; max-height: 80vh; padding: 15px; overflow-x: scroll !important;">' +
      '<div class="text-center" style="padding: 50px;">' +
      '<i class="fa fa-spinner fa-spin fa-3x"></i>' +
      '<p style="margin-top: 20px;"><strong>Cargando diagrama Gantt...</strong></p>' +
      "</div>" +
      "</div>" +
      "</div>" +
      "</div>" +
      "</div>";

    // Remover modal anterior si existe
    jQuery("#supplier-gantt-modal").remove();
    jQuery("body").append(modalHtml);
    jQuery("#supplier-gantt-modal").modal("show");

    // Llamada AJAX para obtener el Gantt
    jQuery.ajax({
      type: "POST",
      url: "index.php",
      data: {
        module: "Home",
        action: "AjaxDeskUtils",
        Ajax: true,
        function: "SUPPLIER_GANTT",
        supplierid: supplierId,
        periodtask: period,
        datestart: fromDate,
        duedate: toDate,
      },
      dataType: "json",
      success: function (response) {
        if (response.error === "OK") {
          jQuery("#supplier-gantt-content").html(response.html);
        } else {
          var errorHtml =
            '<div class="alert alert-danger" style="margin: 20px;">' +
            "<strong>Error:</strong> " +
            (response.error || "Error desconocido") +
            "</div>";
          jQuery("#supplier-gantt-content").html(errorHtml);
        }
      },
      error: function (xhr, status, error) {
        var errorHtml =
          '<div class="alert alert-danger" style="margin: 20px;">' +
          "<strong>Error de conexión:</strong> No se pudo cargar el diagrama Gantt." +
          "</div>";
        jQuery("#supplier-gantt-content").html(errorHtml);
      },
    });
  };

  /**
   * Imprimir el diagrama Gantt del proveedor
   */
  var printSupplierGantt = function () {
    var ganttContainer = jQuery("#supplier-gantt-content .card-task-gantt");
    var ganttSvg = ganttContainer.find("svg.gantt");

    if (ganttSvg.length === 0) {
      alert("No hay diagrama Gantt para imprimir");
      return;
    }

    var supplierName =
      jQuery("#supplier-gantt-modal .modal-title").text() || "Gantt Proveedor";
    var svgClone = ganttSvg[0].cloneNode(true);

    // Copiar colores de las barras
    var originalBars = ganttSvg[0].querySelectorAll(".bar");
    var clonedBars = svgClone.querySelectorAll(".bar");
    for (var i = 0; i < originalBars.length && i < clonedBars.length; i++) {
      var computedStyle = window.getComputedStyle(originalBars[i]);
      if (computedStyle.fill) {
        clonedBars[i].setAttribute("fill", computedStyle.fill);
        clonedBars[i].style.fill = computedStyle.fill;
      }
    }

    // Crear ventana de impresión
    var printWindow = window.open("", "_blank", "width=1200,height=800");
    var printContent =
      '<!DOCTYPE html><html><head><meta charset="UTF-8">' +
      "<title>" +
      supplierName +
      "</title>" +
      "<style>@page { size: landscape; margin: 5mm; } body { font-family: Arial, sans-serif; margin: 10px; }" +
      ".print-header { text-align: center; margin-bottom: 10px; border-bottom: 2px solid #3498db; padding-bottom: 8px; }" +
      ".print-header h1 { margin: 0; font-size: 18px; color: #2c3e50; }</style></head>" +
      '<body><div class="print-header"><h1>' +
      supplierName +
      "</h1></div>" +
      "<div>" +
      svgClone.outerHTML +
      "</div>" +
      "<script>window.onload = function() { setTimeout(function() { window.print(); }, 300); };</script>" +
      "</body></html>";

    printWindow.document.write(printContent);
    printWindow.document.close();
  };

  // Exponer métodos públicos
  return {
    selectedSupplier: selectedSupplier,
    selectedPeriod: selectedPeriod,
    goToPage: goToPage,
    goToPartWork: goToPartWork,
    navigateToPage: navigateToPage,
    showGantt: showGantt,
    printSupplierGantt: printSupplierGantt,
    refreshSuppliersDropdown: refreshSuppliersDropdown,
  };
})();
