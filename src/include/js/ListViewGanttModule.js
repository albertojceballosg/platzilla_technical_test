/**
 * ListViewGanttModule.js - Utilidades JavaScript para Vista Gantt de Módulos en ListView
 *
 * Gestiona la activación y carga dinámica de vistas Gantt en el contexto de ListView
 * siguiendo el mismo patrón que las vistas Calendar y Kanban.
 *
 * @author Platzilla Development Team
 * @date 2025-11-25
 */

var ListViewGanttModuleUtils = {
  // Variable para almacenar el viewId seleccionado
  currentViewId: null,

  /**
   * Activar pestaña de vista Gantt
   * @param {string} module - Nombre del módulo
   * @param {number} viewId - ID de la vista Gantt (opcional)
   */
  activeGanttModuleTab: function (module, viewId) {
    // Remover clase activa de otros botones
    jQuery(".list-view-tab").removeClass("active");
    jQuery("#gantt-module-tab").addClass("active");

    // Ocultar otros contenidos de pestañas
    jQuery(".tab-pane").removeClass("active");

    // Activar el contenedor padre del Gantt
    jQuery("#LIST-VIEW-GANTT-MODULE").addClass("active");

    // Mostrar loader mientras se carga
    var loadingHTML =
      '<div class="text-center" style="padding: 50px;">' +
      '<div class="loading-bars" style="display: flex; justify-content: center; align-items: flex-end; height: 40px; gap: 4px;">' +
      '<div class="bar" style="width: 12px; background: linear-gradient(to top, #b3d9f2 0%, #4a9fd8 100%); animation: loading-bar 1.2s ease-in-out infinite; animation-delay: 0s;"></div>' +
      '<div class="bar" style="width: 12px; background: linear-gradient(to top, #b3d9f2 0%, #4a9fd8 100%); animation: loading-bar 1.2s ease-in-out infinite; animation-delay: 0.1s;"></div>' +
      '<div class="bar" style="width: 12px; background: linear-gradient(to top, #b3d9f2 0%, #4a9fd8 100%); animation: loading-bar 1.2s ease-in-out infinite; animation-delay: 0.2s;"></div>' +
      '<div class="bar" style="width: 12px; background: linear-gradient(to top, #b3d9f2 0%, #4a9fd8 100%); animation: loading-bar 1.2s ease-in-out infinite; animation-delay: 0.3s;"></div>' +
      '<div class="bar" style="width: 12px; background: linear-gradient(to top, #b3d9f2 0%, #4a9fd8 100%); animation: loading-bar 1.2s ease-in-out infinite; animation-delay: 0.4s;"></div>' +
      '<div class="bar" style="width: 12px; background: linear-gradient(to top, #b3d9f2 0%, #4a9fd8 100%); animation: loading-bar 1.2s ease-in-out infinite; animation-delay: 0.5s;"></div>' +
      '<div class="bar" style="width: 12px; background: linear-gradient(to top, #b3d9f2 0%, #4a9fd8 100%); animation: loading-bar 1.2s ease-in-out infinite; animation-delay: 0.6s;"></div>' +
      '<div class="bar" style="width: 12px; background: linear-gradient(to top, #b3d9f2 0%, #4a9fd8 100%); animation: loading-bar 1.2s ease-in-out infinite; animation-delay: 0.7s;"></div>' +
      '<div class="bar" style="width: 12px; background: linear-gradient(to top, #b3d9f2 0%, #4a9fd8 100%); animation: loading-bar 1.2s ease-in-out infinite; animation-delay: 0.8s;"></div>' +
      '<div class="bar" style="width: 12px; background: linear-gradient(to top, #b3d9f2 0%, #4a9fd8 100%); animation: loading-bar 1.2s ease-in-out infinite; animation-delay: 0.9s;"></div>' +
      '<div class="bar" style="width: 12px; background: linear-gradient(to top, #1e5a8e 0%, #2874b5 100%); animation: loading-bar 1.2s ease-in-out infinite; animation-delay: 1.0s;"></div>' +
      '<div class="bar" style="width: 12px; background: linear-gradient(to top, #0d3d5c 0%, #1e5a8e 100%); animation: loading-bar 1.2s ease-in-out infinite; animation-delay: 1.1s;"></div>' +
      '</div>' +
      '<p style="margin-top: 20px;"><strong>Cargando vista Gantt...</strong></p>' +
      '<style>' +
      '@keyframes loading-bar {' +
      '0%, 100% { height: 10px; }' +
      '50% { height: 40px; }' +
      '}' +
      '</style>' +
      "</div>";

    jQuery("#gantt-module-content").html(loadingHTML);

    // Usar viewId pasado como parámetro, o el almacenado, o el del selector si existe
    if (viewId) {
      this.currentViewId = viewId;
    } else if (!this.currentViewId) {
      // Solo intentar leer del selector si no tenemos un viewId almacenado
      var selectorVal = jQuery("#gantt-module-view-selector").val();
      if (selectorVal) {
        this.currentViewId = selectorVal;
      }
    }
    viewId = this.currentViewId;

    // Obtener customview activo de la ListView
    var customViewId = jQuery("#customFilter option:selected").val();

    // Preparar datos para AJAX
    var ajaxData = {
      module: module,
      action: "AjaxListViewUtils",
      file: "AjaxListViewUtils",
      function: "VIEW-GANTT-MODULE",
      Ajax: true,
    };

    // Agregar viewId solo si está presente
    if (viewId) {
      ajaxData.gantt_view_id = viewId;
    }

    // Agregar customview activo
    if (customViewId) {
      ajaxData.viewname = customViewId;
    }

    // Llamada AJAX
    jQuery.ajax({
      type: "POST",
      url: "index.php",
      data: ajaxData,
      dataType: "json",
      success: function (response) {
        if (response.error === "OK") {
          // Insertar HTML de la vista Gantt
          jQuery("#gantt-module-content").html(response.html);
        } else {
          var errorHTML =
            '<div class="alert alert-danger" style="margin: 20px;">' +
            "<strong>Error:</strong> " +
            response.error +
            "</div>";
          jQuery("#gantt-module-content").html(errorHTML);
        }
      },
      error: function (xhr, status, error) {
        var errorHTML =
          '<div class="alert alert-danger" style="margin: 20px;">' +
          "<strong>Error de conexión:</strong> No se pudo cargar la vista Gantt." +
          "</div>";
        jQuery("#gantt-module-content").html(errorHTML);
      },
    });
  },

  /**
   * Cambiar vista Gantt activa
   * @param {string} module - Nombre del módulo
   * @param {number} viewId - ID de la nueva vista
   */
  changeGanttView: function (module, viewId) {
    // Almacenar el viewId seleccionado y recargar
    this.currentViewId = viewId;
    this.activeGanttModuleTab(module, viewId);
  },

  /**
   * Refrescar vista Gantt actual
   * @param {string} module - Nombre del módulo
   */
  refreshGanttView: function (module) {
    this.activeGanttModuleTab(module);
  },

  /**
   * Inicializar eventos después de cargar el Gantt
   */
  initializeGanttEvents: function () {
    // Evento para selector de vista
    jQuery("#gantt-module-view-selector")
      .off("change")
      .on("change", function () {
        var module = jQuery(this).data("module");
        var viewId = jQuery(this).val();
        ListViewGanttModuleUtils.changeGanttView(module, viewId);
      });
  },

  /**
   * Imprimir el diagrama Gantt a color
   */
  printGantt: function () {
    // Obtener el contenedor del Gantt
    var ganttContainer = jQuery("#gantt-module-content .card-task-gantt");
    var ganttSvg = ganttContainer.find("svg.gantt");

    if (ganttSvg.length === 0) {
      alert("No hay diagrama Gantt para imprimir");
      return;
    }

    // Obtener el título de la vista
    var viewTitle =
      jQuery("#gantt-module-view-selector option:selected").text() ||
      "Vista Gantt";
    var moduleTitle = jQuery(".page-title h3").text() || "Gantt";

    // Clonar el SVG para no modificar el original
    var svgClone = ganttSvg[0].cloneNode(true);

    // Eliminar elementos que no se necesitan en la impresión
    // Handles de redimensionamiento
    var handles = svgClone.querySelectorAll(".handle, .handle-group");
    handles.forEach(function (el) {
      el.remove();
    });

    // Flechas de dependencia (pueden verse mal en impresión)
    var arrows = svgClone.querySelectorAll(".arrow");
    arrows.forEach(function (el) {
      el.remove();
    });

    // Copiar colores de las barras directamente del SVG original
    var originalBars = ganttSvg[0].querySelectorAll(".bar");
    var clonedBars = svgClone.querySelectorAll(".bar");

    for (var i = 0; i < originalBars.length && i < clonedBars.length; i++) {
      var origBar = originalBars[i];
      var cloneBar = clonedBars[i];
      var computedStyle = window.getComputedStyle(origBar);
      var fillColor = computedStyle.fill;
      if (fillColor) {
        cloneBar.setAttribute("fill", fillColor);
        cloneBar.style.fill = fillColor;
      }
    }

    // Copiar colores de las barras de progreso
    var originalProgress = ganttSvg[0].querySelectorAll(".bar-progress");
    var clonedProgress = svgClone.querySelectorAll(".bar-progress");

    for (
      var i = 0;
      i < originalProgress.length && i < clonedProgress.length;
      i++
    ) {
      var origProg = originalProgress[i];
      var cloneProg = clonedProgress[i];
      var computedStyle = window.getComputedStyle(origProg);
      var fillColor = computedStyle.fill;
      if (fillColor) {
        cloneProg.setAttribute("fill", fillColor);
        cloneProg.style.fill = fillColor;
      }
    }

    // Aplicar estilos a los textos (labels) - forzar negro y Roboto
    var clonedLabels = svgClone.querySelectorAll("text, .bar-label");
    clonedLabels.forEach(function (label) {
      label.setAttribute("fill", "#000000");
      label.style.fontFamily = '"Roboto", Arial, sans-serif';
      label.style.fontSize = "11px";
    });

    // Copiar estilos del grid y otros elementos
    var gridElements = ganttSvg[0].querySelectorAll(
      ".grid-row, .grid-header, .tick, .row-line, .today-highlight"
    );
    var clonedGridElements = svgClone.querySelectorAll(
      ".grid-row, .grid-header, .tick, .row-line, .today-highlight"
    );

    for (
      var i = 0;
      i < gridElements.length && i < clonedGridElements.length;
      i++
    ) {
      var origEl = gridElements[i];
      var cloneEl = clonedGridElements[i];
      var computedStyle = window.getComputedStyle(origEl);

      if (computedStyle.fill && computedStyle.fill !== "none") {
        cloneEl.setAttribute("fill", computedStyle.fill);
      }
      if (computedStyle.stroke && computedStyle.stroke !== "none") {
        cloneEl.setAttribute("stroke", computedStyle.stroke);
      }
    }

    // Crear ventana de impresión
    var printWindow = window.open("", "_blank", "width=1200,height=800");

    // Construir el HTML para imprimir con colores inline
    var printContent =
      "<!DOCTYPE html>" +
      "<html>" +
      "<head>" +
      '<meta charset="UTF-8">' +
      "<title>Imprimir Gantt - " +
      viewTitle +
      "</title>" +
      '<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">' +
      "<style>" +
      "@page { size: landscape; margin: 5mm; }" +
      "*, *::before, *::after { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; color-adjust: exact !important; }" +
      'body { font-family: "Roboto", Arial, sans-serif; margin: 0; padding: 10px; background: white; }' +
      ".print-header { text-align: center; margin-bottom: 10px; border-bottom: 2px solid #3498db; padding-bottom: 8px; }" +
      ".print-header h1 { margin: 0 0 3px 0; font-size: 18px; color: #2c3e50; }" +
      ".print-header h2 { margin: 0; font-size: 12px; color: #7f8c8d; font-weight: normal; }" +
      ".print-header .print-date { font-size: 10px; color: #95a5a6; margin-top: 3px; }" +
      ".gantt-print-container { overflow: visible; }" +
      ".gantt-print-container svg { display: block; }" +
      '.gantt-print-container svg text { font-family: "Roboto", Arial, sans-serif !important; fill: #000000 !important; }' +
      ".gantt-print-container svg .bar-label { fill: #000000 !important; font-weight: 500; }" +
      ".gantt-print-container svg .bar-label.big { fill: #000000 !important; }" +
      "</style>" +
      "</head>" +
      "<body>" +
      '<div class="print-header">' +
      "<h1>" +
      moduleTitle +
      "</h1>" +
      "<h2>" +
      viewTitle +
      "</h2>" +
      '<div class="print-date">Impreso el: ' +
      new Date().toLocaleDateString("es-ES", {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
      }) +
      "</div>" +
      "</div>" +
      '<div class="gantt-print-container">' +
      svgClone.outerHTML +
      "</div>" +
      "<script>" +
      "window.onload = function() { setTimeout(function() { window.print(); }, 300); window.onafterprint = function() { window.close(); }; };" +
      "</script>" +
      "</body>" +
      "</html>";

    printWindow.document.write(printContent);
    printWindow.document.close();
  },
};

// Inicializar cuando el documento esté listo
jQuery(document).ready(function () {
  // Los eventos se inicializarán después de cargar el contenido del Gantt
});
