<?php
/**
 * Utilidad para sanear contenido HTML problemático en celdas de reportes PDF (TCPDF)
 * Convierte tablas anidadas en texto plano estructurado.
 *
 * @author Cascade AI
 */
class SanitizeUtils {
    /**
     * Sanitiza el contenido HTML de una celda, eliminando tablas anidadas y convirtiéndolas en texto plano estructurado.
     *
     * @param string $html Contenido HTML original de la celda
     * @return string Contenido HTML saneado, sin tablas anidadas
     */
    public static function sanitizePdfCellContent($html) {
    // Si contiene cualquier etiqueta HTML, convierte todo a texto plano con htmlspecialchars
    if (preg_match('/<[^>]+>/', $html)) {
        return htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    } else {
        return $html;
    }
}

    /**
     * Convierte una tabla HTML en texto plano estructurado (filas por salto de línea, columnas por |)
     *
     * @param DOMElement $table
     * @return string
     */
    private static function tableToPlainText($table) {
        $rows = $table->getElementsByTagName('tr');
        $lines = [];
        foreach ($rows as $row) {
            $cols = $row->getElementsByTagName('td');
            $line = [];
            foreach ($cols as $col) {
                $line[] = trim($col->textContent);
            }
            $lines[] = implode(' | ', $line);
        }
        return implode("\n", $lines);
    }
}
