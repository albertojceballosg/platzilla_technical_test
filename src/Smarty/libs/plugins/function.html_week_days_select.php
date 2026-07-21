<?php
	/**
		 * Smarty plugin
		 *
		 * @package    Smarty
		 * @subpackage PluginsFunction
		 */
		
		/**
		 * Smarty {html_week_days_select} function plugin
		 * Type:     function
		 * Name:     html_week_days_selecte<br>
		 * Purpose:  Prints the option to select week days<br>
		 *           the passed parameters<br>
		 * Params:
		 * <pre>
		 * - init_day  (required) - name of the first day of the week
		 * - offset_month (required) - number of mes offset
		 * </pre>
		 *
		 * @link      app.platzilla.com
		 * @author   ING. Wilfredo Araujo
		 *
		 * @param array $params parameters
		 *
		 * @return string
		 * @uses   smarty_function_escape_special_chars()
		 */
		function smarty_function_html_week_days_select ($params) {
			$htmlOutput   = "";
			$dayOfWeekEn  = array ('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
			$dayOfWeekEs  = array ('Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo');
			$firstDayWeek = $params ['init_day'];
			$selectedWeek = $params ['selected_week'];
			$offset       = $params ['offset_month'];
			$month        = date('m');
			$year         = date('Y');
			$year		  = (intval ($month) <= $offset) ? $year - 1 : $year;
			$month		  = (intval ($month) <= $offset) ? (12 - (($offset - 1) - $month)) : $month - $offset;
			$timestamp    = mktime (0, 0 , 0, $month, 1, $year);
			$startWeek    = date ('W', $timestamp);
			$lastWeek     = date ('W');
			$diffWeek     = abs ($lastWeek - $startWeek);
			for ($i = 0; $i <= $diffWeek; $i++) {
				$j = $i + 1;
				$fromDate = date ('Y-m-d', strtotime("{$firstDayWeek} - {$j} week"));
				$toDate   = date ('Y-m-d', strtotime ($fromDate . '+6 day'));
				$formDateDisplay = date ('l - Y-m-d', strtotime("{$firstDayWeek} - {$j} week"));
				$toDateDisplay   = date ('l - Y-m-d',  strtotime ($fromDate . '+6 day'));
				$daysDisplay     =  str_replace ($dayOfWeekEn, $dayOfWeekEs, $formDateDisplay . ' - ' . $toDateDisplay);
				$daysValue       = $fromDate . '@' . $toDate;
				$selectedDays    = ($selectedWeek == $daysValue) ? 'selected="selected"' : '';
				$htmlOutput     .= "<option value=". $daysValue ." $selectedDays>".$daysDisplay."</option>";
			}
			return $htmlOutput ;
		}