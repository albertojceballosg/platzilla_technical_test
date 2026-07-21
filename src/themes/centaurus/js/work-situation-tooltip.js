/**
 * Ajusta dinámicamente el tooltip del campo work_situation
 * cuando el valor es "Alerta de eficiencia"
 *
 * Determina si el problema es por costo o por unidades
 */
(function () {
  "use strict";

  function adjustWorkSituationTooltip() {
    // Work Situation Tooltip: Iniciando ajuste...

    // Buscar el campo work_situation con múltiples selectores
    var workSituationSpan = null;
    var selectors = [
      'span[id*="dtlview_"][id*="Situación del trabajo"]',
      'span[id*="dtlview_"][id*="work_situation"]',
      'span[id*="dtlview_"][id*="Situaci"]',
      'span[id*="dtlview_"][id*="situacion"]',
      'span[id*="dtlview_"][id*="situation"]',
    ];

    for (var i = 0; i < selectors.length; i++) {
      workSituationSpan = document.querySelector(selectors[i]);
      if (workSituationSpan) {
        
        break;
      }
    }

    if (!workSituationSpan) {

      return;
    }

    // Obtener el valor del campo
    var workSituationValue = workSituationSpan.textContent.trim();
    

    // Solo procesar si es "Alerta de eficiencia"
    if (workSituationValue !== "Alerta de eficiencia") {

      return;
    }

    // Buscar los valores de los campos necesarios con selectores mejorados
    var costoEjecutadoSpan = null;
    var costoEstimadoSpan = null;
    var unidadesConsumidasSpan = null;
    var unidadesPlanificadasSpan = null;

    // Selectores para costo ejecutado
    var costoEjecutadoSelectors = [
      'span[id*="dtlview_"][id*="Costo ejecutado"]',
      'span[id*="dtlview_"][id*="Costo"]',
      'span[id*="dtlview_"][id*="costo"]',
      'span[id*="dtlview_"][id*="ejecutado"]',
      'span[id*="dtlview_"][id*="performed"]',
    ];

    // Selectores para costo estimado
    var costoEstimadoSelectors = [
      'span[id*="dtlview_"][id*="Costo estimado"]',
      'span[id*="dtlview_"][id*="Estimado"]',
      'span[id*="dtlview_"][id*="estimado"]',
      'span[id*="dtlview_"][id*="estimated"]',
      'span[id*="dtlview_"][id*="presupuesto"]',
    ];

    // Selectores para unidades consumidas
    var unidadesConsumidasSelectors = [
      'span[id*="dtlview_"][id*="Unidades consumidas"]',
      'span[id*="dtlview_"][id*="Unidades"]',
      'span[id*="dtlview_"][id*="unidades"]',
      'span[id*="dtlview_"][id*="consumidas"]',
      'span[id*="dtlview_"][id*="consumed"]',
    ];

    // Selectores para unidades planificadas
    var unidadesPlanificadasSelectors = [
      'span[id*="dtlview_"][id*="Unidades planificadas"]',
      'span[id*="dtlview_"][id*="planificadas"]',
      'span[id*="dtlview_"][id*="planned"]',
      'span[id*="dtlview_"][id*="estimadas"]',
    ];

    // Buscar cada campo
    for (var i = 0; i < costoEjecutadoSelectors.length; i++) {
      costoEjecutadoSpan = document.querySelector(costoEjecutadoSelectors[i]);
      if (costoEjecutadoSpan) break;
    }

    for (var i = 0; i < costoEstimadoSelectors.length; i++) {
      costoEstimadoSpan = document.querySelector(costoEstimadoSelectors[i]);
      if (costoEstimadoSpan) break;
    }

    for (var i = 0; i < unidadesConsumidasSelectors.length; i++) {
      unidadesConsumidasSpan = document.querySelector(
        unidadesConsumidasSelectors[i],
      );
      if (unidadesConsumidasSpan) break;
    }

    for (var i = 0; i < unidadesPlanificadasSelectors.length; i++) {
      unidadesPlanificadasSpan = document.querySelector(
        unidadesPlanificadasSelectors[i],
      );
      if (unidadesPlanificadasSpan) break;
    }


    

    

    


    if (!costoEjecutadoSpan || !costoEstimadoSpan) {

      return;
    }

    // Extraer valores numéricos (eliminar símbolos de moneda, comas, etc.)
    function parseNumericValue(element) {
      if (!element) return 0;

      var text = element.textContent || element.innerText || '';
      if (!text) return 0;



      // Limpiar completamente el texto: remover todo excepto números, puntos, comas y signos
      var cleaned = text.replace(/\s/g, '').replace(/[^\d.,-]/g, '');


      // Detectar si es formato europeo:
      // - Si tiene coma y NO tiene punto: 0,51 → 0.51
      // - Si tiene coma y punto: 1.234,56 → 1234.56
      // - Si solo tiene punto: 1.234 → 1.234 (ya es formato JS)
      
      var result = 0;
      
      if (cleaned.indexOf(',') !== -1) {
        // Tiene coma - es formato europeo
        if (cleaned.indexOf('.') !== -1) {
          // Formato europeo con miles: 1.234,56 → 1234.56
          cleaned = cleaned.replace(/\./g, '').replace(',', '.');

        } else {
          // Formato europeo simple: 0,51 → 0.51
          cleaned = cleaned.replace(',', '.');

        }
        result = parseFloat(cleaned) || 0;
      } else if (cleaned.indexOf('.') !== -1) {
        // Solo tiene punto - formato americano

        result = parseFloat(cleaned) || 0;
      } else {
        // Solo números

        result = parseFloat(cleaned) || 0;
      }


      return result;
    }

    var costoEjecutado = parseNumericValue(costoEjecutadoSpan);
    var costoEstimado = parseNumericValue(costoEstimadoSpan);
    var unidadesConsumidas = unidadesConsumidasSpan
      ? parseNumericValue(unidadesConsumidasSpan)
      : 0;
    var unidadesPlanificadas = unidadesPlanificadasSpan
      ? parseNumericValue(unidadesPlanificadasSpan)
      : 0;







    // Determinar el tooltip correcto
    var newTooltip;
    var motivo = "";

    if (costoEjecutado > costoEstimado) {
      // Problema de costo
      newTooltip =
        "Se está cumpliendo el tiempo, pero a un costo mayor (poca rentabilidad).";
      motivo = "costo > estimado";
    } else if (
      unidadesPlanificadas > 0 &&
      unidadesConsumidas > unidadesPlanificadas
    ) {
      // Problema de unidades (con unidades planificadas > 0)
      newTooltip =
        "Se está cumpliendo el tiempo, pero las unidades ejecutadas son mayores que las estimadas para la fecha.";
      motivo = "unidades > planificadas";
    } else if (unidadesPlanificadas == 0 && unidadesConsumidas > 0) {
      // Problema de unidades (sin unidades planificadas pero hay consumo)
      newTooltip =
        "Se está cumpliendo el tiempo, pero las unidades ejecutadas son mayores que las estimadas para la fecha.";
      motivo = "unidades > planificadas (sin planificación)";
    } else {
      // Por defecto (no debería llegar aquí, pero por seguridad)
      newTooltip =
        "Se está cumpliendo el tiempo, pero a un costo mayor (poca rentabilidad).";
      motivo = "por defecto";
    }




    // Actualizar el tooltip
    workSituationSpan.setAttribute("title", newTooltip);
    workSituationSpan.setAttribute("data-original-title", newTooltip);

    // Si usa Bootstrap tooltip, reinicializarlo
    if (typeof jQuery !== "undefined" && jQuery.fn.tooltip) {
      jQuery(workSituationSpan).tooltip("destroy").tooltip({
        title: newTooltip,
        placement: "top",
      });

    }


  }

  // Ejecutar cuando el DOM esté listo
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", adjustWorkSituationTooltip);
  } else {
    adjustWorkSituationTooltip();
  }

  // También ejecutar después de un pequeño delay por si los valores se cargan dinámicamente
  setTimeout(adjustWorkSituationTooltip, 500);
  setTimeout(adjustWorkSituationTooltip, 1500);
  setTimeout(adjustWorkSituationTooltip, 3000);
})();
