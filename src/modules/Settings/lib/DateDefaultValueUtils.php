<?php
/**
 * Utilidades para procesar valores por defecto dinámicos en campos de fecha
 * Compatible con vtiger CRM 5.4.0
 * 
 * Soporta expresiones como:
 * - CURRENT_DATE o TODAY: fecha actual
 * - TODAY+3: fecha actual + 3 días
 * - TODAY-5: fecha actual - 5 días
 * - CURRENT_DATE+10: fecha actual + 10 días
 * - CURRENT_DATE-7: fecha actual - 7 días
 */

/**
 * Procesa un valor de fecha por defecto y retorna la fecha calculada
 * 
 * @param string $defaultValue - Valor configurado en el campo (ej: "TODAY+3", "CURRENT_DATE-5")
 * @return string - Fecha en formato YYYY-MM-DD o el valor original si no es una expresión válida
 */
function processDateDefaultValue($defaultValue) {
    if (empty($defaultValue)) {
        return '';
    }
    
    // Normalizar el valor: mayúsculas, sin espacios, sin caracteres extras al final
    $value = strtoupper(trim($defaultValue));
    // Remover espacios internos
    $value = preg_replace('/\s+/', '', $value);
    // Remover caracteres no válidos al final (guiones extras, etc.)
    $value = preg_replace('/[^A-Z0-9]+$/', '', $value);
    
    // Caso 1: CURRENT_DATE o TODAY sin operación
    if ($value === 'CURRENT_DATE' || $value === 'TODAY') {
        return date('Y-m-d');
    }
    
    // Caso 2: Expresiones con operaciones (TODAY+3, CURRENT_DATE-5, etc.)
    // Patrón: (CURRENT_DATE|TODAY)([+-])(\d+)
    if (preg_match('/^(CURRENT_DATE|TODAY)([+-])(\d+)$/i', $value, $matches)) {
        $operator = $matches[2];
        $days = intval($matches[3]);
        
        if ($operator === '+') {
            return date('Y-m-d', strtotime("+{$days} days"));
        } else {
            return date('Y-m-d', strtotime("-{$days} days"));
        }
    }
    
    // Caso 3: Si es una fecha válida en formato YYYY-MM-DD, retornarla
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
        return $value;
    }
    
    // Caso 4: Valor no reconocido, retornar vacío
    return '';
}

/**
 * Valida si una expresión de fecha por defecto es válida
 * 
 * @param string $expression - Expresión a validar
 * @return array - ['valid' => bool, 'message' => string, 'example' => string]
 */
function validateDateDefaultExpression($expression) {
    if (empty($expression)) {
        return array(
            'valid' => true,
            'message' => 'Valor vacío (sin valor por defecto)',
            'example' => ''
        );
    }
    
    $value = strtoupper(trim($expression));
    
    // Validar CURRENT_DATE o TODAY
    if ($value === 'CURRENT_DATE' || $value === 'TODAY') {
        return array(
            'valid' => true,
            'message' => 'Expresión válida: fecha actual',
            'example' => date('Y-m-d')
        );
    }
    
    // Validar expresiones con operaciones
    if (preg_match('/^(CURRENT_DATE|TODAY)\s*([+-])\s*(\d+)$/i', $value, $matches)) {
        $operator = $matches[2];
        $days = intval($matches[3]);
        
        if ($days > 365) {
            return array(
                'valid' => false,
                'message' => 'El número de días no debe exceder 365',
                'example' => ''
            );
        }
        
        $exampleDate = ($operator === '+') 
            ? date('Y-m-d', strtotime("+{$days} days"))
            : date('Y-m-d', strtotime("-{$days} days"));
        
        return array(
            'valid' => true,
            'message' => "Expresión válida: fecha actual {$operator} {$days} días",
            'example' => $exampleDate
        );
    }
    
    // Validar fecha fija en formato YYYY-MM-DD
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
        return array(
            'valid' => true,
            'message' => 'Fecha fija válida',
            'example' => $value
        );
    }
    
    // Expresión no válida
    return array(
        'valid' => false,
        'message' => 'Expresión no válida. Use: TODAY, CURRENT_DATE, TODAY+N, TODAY-N, CURRENT_DATE+N, CURRENT_DATE-N',
        'example' => ''
    );
}

/**
 * Obtiene ejemplos de expresiones de fecha válidas
 * 
 * @return array - Array de ejemplos con descripción
 */
function getDateDefaultExpressionExamples() {
    return array(
        array(
            'expression' => 'CURRENT_DATE',
            'description' => 'Fecha actual',
            'example' => date('Y-m-d')
        ),
        array(
            'expression' => 'TODAY',
            'description' => 'Fecha actual (alias de CURRENT_DATE)',
            'example' => date('Y-m-d')
        ),
        array(
            'expression' => 'TODAY+3',
            'description' => 'Fecha actual + 3 días',
            'example' => date('Y-m-d', strtotime('+3 days'))
        ),
        array(
            'expression' => 'TODAY+5',
            'description' => 'Fecha actual + 5 días',
            'example' => date('Y-m-d', strtotime('+5 days'))
        ),
        array(
            'expression' => 'TODAY+10',
            'description' => 'Fecha actual + 10 días',
            'example' => date('Y-m-d', strtotime('+10 days'))
        ),
        array(
            'expression' => 'TODAY-3',
            'description' => 'Fecha actual - 3 días',
            'example' => date('Y-m-d', strtotime('-3 days'))
        ),
        array(
            'expression' => 'TODAY-5',
            'description' => 'Fecha actual - 5 días',
            'example' => date('Y-m-d', strtotime('-5 days'))
        ),
        array(
            'expression' => 'TODAY-10',
            'description' => 'Fecha actual - 10 días',
            'example' => date('Y-m-d', strtotime('-10 days'))
        ),
        array(
            'expression' => 'CURRENT_DATE+7',
            'description' => 'Fecha actual + 7 días (una semana)',
            'example' => date('Y-m-d', strtotime('+7 days'))
        ),
        array(
            'expression' => 'CURRENT_DATE+30',
            'description' => 'Fecha actual + 30 días (un mes aprox.)',
            'example' => date('Y-m-d', strtotime('+30 days'))
        ),
        array(
            'expression' => '2025-12-31',
            'description' => 'Fecha fija específica',
            'example' => '2025-12-31'
        ),
    );
}

/**
 * Convierte una expresión de fecha a un formato legible para el usuario
 * 
 * @param string $expression - Expresión de fecha
 * @return string - Descripción legible
 */
function getDateDefaultExpressionDescription($expression) {
    if (empty($expression)) {
        return 'Sin valor por defecto';
    }
    
    $value = strtoupper(trim($expression));
    
    if ($value === 'CURRENT_DATE' || $value === 'TODAY') {
        return 'Fecha actual';
    }
    
    if (preg_match('/^(CURRENT_DATE|TODAY)\s*([+-])\s*(\d+)$/i', $value, $matches)) {
        $operator = $matches[2];
        $days = intval($matches[3]);
        
        if ($operator === '+') {
            return "Fecha actual + {$days} día" . ($days != 1 ? 's' : '');
        } else {
            return "Fecha actual - {$days} día" . ($days != 1 ? 's' : '');
        }
    }
    
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
        return "Fecha fija: {$value}";
    }
    
    return $expression;
}
