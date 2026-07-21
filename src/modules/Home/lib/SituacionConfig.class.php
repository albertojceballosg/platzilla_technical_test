<?php
/*********************************************************************************
 * Sistema de Configuración para Columna "Situación" - Multi-idioma
 * 
 * Descripción:
 * Clase centralizada para manejar la traducción y configuración de colores
 * de la columna "Situación" en el Home de Gestión de Platzilla.
 * 
 * Características:
 * - Soporte multi-idioma usando sistema de traducción de Platzilla
 * - Mapeo de valores internos a valores traducidos
 * - Configuración centralizada de colores
 * - Cache para optimizar rendimiento
 ********************************************************************************/

class SituacionConfig {
    
    /**
     * Cache de configuración por idioma
     * @var array
     */
    private static $configCache = array();
    
    /**
     * Mapeo de valores internos a claves de traducción
     * @var array
     */
    const INTERNAL_VALUES = array(
        'PICK_ACTIVITY_ON_TIME_ON_BUDGET' => 'PICK_ACTIVITY_ON_TIME_ON_BUDGET',
        'PICK_ACTIVITY_ON_TIME_OVER_BUDGET' => 'PICK_ACTIVITY_ON_TIME_OVER_BUDGET',
        'PICK_ACTIVITY_DELAYED_ON_BUDGET' => 'PICK_ACTIVITY_DELAYED_ON_BUDGET',
        'PICK_ACTIVITY_DELAYED_OVER_BUDGET' => 'PICK_ACTIVITY_DELAYED_OVER_BUDGET',
    );
    
    /**
     * Configuración de colores por valor interno
     * @var array
     */
    const COLORS = array(
        'PICK_ACTIVITY_ON_TIME_ON_BUDGET' => '#388E3C',    // Verde
        'PICK_ACTIVITY_ON_TIME_OVER_BUDGET' => '#7B1FA2',  // Púrpura
        'PICK_ACTIVITY_DELAYED_ON_BUDGET' => '#F57C00',    // Naranja
        'PICK_ACTIVITY_DELAYED_OVER_BUDGET' => '#D32F2F',  // Rojo claro
    );
    
    /**
     * Obtiene la configuración completa para el idioma actual
     * 
     * @return array Configuración con valores traducidos y colores
     */
    public static function getConfig() {
        global $current_language;
        
        // Verificar cache
        if (isset(self::$configCache[$current_language])) {
            return self::$configCache[$current_language];
        }
        
        $module = 'Calendar';  // Usar Calendar para traducciones de combined_condition
        $config = array();
        
        // Construir configuración con traducciones
        foreach (self::INTERNAL_VALUES as $internalKey => $translationKey) {
            $translatedValue = getTranslatedString($translationKey, $module);
            
            $config[$internalKey] = array(
                'display' => $translatedValue,
                'color' => self::COLORS[$internalKey],
                'translation_key' => $translationKey,
            );
        }
        
        // Guardar en cache
        self::$configCache[$current_language] = $config;
        
        return $config;
    }
    
    /**
     * Obtiene el color correspondiente a un valor de campo
     * 
     * @param string $fieldValue Valor del campo (clave LBL_ON_TIME_* o traducido)
     * @return string Código de color o vacío si no encuentra
     */
    public static function getColorByValue($fieldValue) {
        $fieldValue = trim($fieldValue);
        
        // Si es una clave LBL_ON_TIME_*, buscar directamente
        if (strpos($fieldValue, 'LBL_ON_TIME_') === 0 || strpos($fieldValue, 'LBL_DELAYED_') === 0 || strpos($fieldValue, 'PICK_ACTIVITY_') === 0) {
            foreach (self::INTERNAL_VALUES as $internalKey => $translationKey) {
                if ($translationKey === $fieldValue) {
                    return self::COLORS[$internalKey];
                }
            }
        }
        
        // Si es un valor traducido, buscar por el valor traducido
        $config = self::getConfig();
        foreach ($config as $internalKey => $data) {
            if (trim($data['display']) === $fieldValue) {
                return $data['color'];
            }
        }
        
        return '';
    }
    
    /**
     * Obtiene el valor interno a partir del valor traducido
     * 
     * @param string $fieldValue Valor del campo (traducido)
     * @return string Clave interna o vacío si no encuentra
     */
    public static function getInternalKeyByValue($fieldValue) {
        $config = self::getConfig();
        $fieldValue = trim($fieldValue);
        
        foreach ($config as $internalKey => $data) {
            if (trim($data['display']) === $fieldValue) {
                return $internalKey;
            }
        }
        
        return '';
    }
    
    /**
     * Obtiene todos los valores traducidos posibles
     * 
     * @return array Array de valores traducidos
     */
    public static function getTranslatedValues() {
        $config = self::getConfig();
        $values = array();
        
        foreach ($config as $data) {
            $values[] = $data['display'];
        }
        
        return $values;
    }
    
    /**
     * Limpia el cache (útil para pruebas o cambios dinámicos)
     */
    public static function clearCache() {
        self::$configCache = array();
    }
    
    /**
     * Obtiene estadísticas de configuración (para debugging)
     * 
     * @return array Información de configuración
     */
    public static function getDebugInfo() {
        global $current_language;
        
        return array(
            'current_language' => $current_language,
            'cache_size' => count(self::$configCache),
            'internal_values_count' => count(self::INTERNAL_VALUES),
            'colors_count' => count(self::COLORS),
            'cached_languages' => array_keys(self::$configCache),
        );
    }
    
    /**
     * Valida que un valor sea uno de los valores configurados
     * 
     * @param string $fieldValue Valor a validar
     * @return bool True si es válido, false si no
     */
    public static function isValidValue($fieldValue) {
        return self::getColorByValue($fieldValue) !== '';
    }
    
    /**
     * Obtiene configuración para uso en JavaScript (JSON)
     * 
     * @return string JSON con configuración para frontend
     */
    public static function getJavaScriptConfig() {
        $config = self::getConfig();
        $jsConfig = array();
        
        foreach ($config as $internalKey => $data) {
            $jsConfig[$internalKey] = array(
                'display' => $data['display'],
                'color' => $data['color'],
            );
        }
        
        return json_encode($jsConfig);
    }
}
