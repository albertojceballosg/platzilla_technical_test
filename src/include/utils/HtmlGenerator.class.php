<?php
	require_once ('Smarty_setup.php');

	abstract class HtmlGenerator {

		public static function renderButton ($label, $id, $class = 'btn btn-primary', $type = 'button', array $additionalAttributes = null) {
			$attributes = '';
			if ((isset ($additionalAttributes)) && (is_array ($additionalAttributes)) && (count ($additionalAttributes) > 0)) {
				foreach ($additionalAttributes as $key => $value) {
					$attributes .= ("$key=\"$value\" ");
				}
			}
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('LABEL', $label);
			$smarty->assign ('ID', $id);
			$smarty->assign ('CLASS', $class);
			$smarty->assign ('TYPE', $type);
			$smarty->assign ('ADDITIONAL_ATTRIBUTES', trim ($attributes));
			return $smarty->fetch ('utils/HTMLButton.tpl');
		}

		public static function renderCheckbox ($id, $name, $class, $value, $checked = false, array $additionalAttributes = null) {
			$attributes = '';
			if ((isset ($additionalAttributes)) && (is_array ($additionalAttributes)) && (count ($additionalAttributes) > 0)) {
				foreach ($additionalAttributes as $key => $value) {
					$attributes .= ("$key=\"$value\" ");
				}
			}
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('ID', $id);
			$smarty->assign ('NAME', $name);
			$smarty->assign ('VALUE', $value);
			$smarty->assign ('CHECKED', $checked);
			if ($class) {
				$smarty->assign ('CLASS', $class);
			}
			if ($attributes) {
				$smarty->assign ('ADDITIONAL_ATTRIBUTES', trim ($attributes));
			}
			return $smarty->fetch ('utils/HTMLCheckbox.tpl');
		}

		public static function renderImage ($src, $id = null, $class = null, $title = null, array $additionalAttributes = null) {
			$attributes = '';
			if ((isset ($additionalAttributes)) && (is_array ($additionalAttributes)) && (count ($additionalAttributes) > 0)) {
				foreach ($additionalAttributes as $key => $value) {
					$attributes .= ("$key=\"$value\" ");
				}
			}
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('SRC', $src);
			if ($id) {
				$smarty->assign ('ID', $id);
			}
			if ($class) {
				$smarty->assign ('CLASS', $class);
			}
			if ($title) {
				$smarty->assign ('TITLE', $title);
			}
			if ($attributes) {
				$smarty->assign ('ADDITIONAL_ATTRIBUTES', trim ($attributes));
			}
			return $smarty->fetch ('utils/HTMLImage.tpl');
		}

		public static function renderJavascriptLocation ($url) {
			return "<script type=\"text/javascript\">window.location.href = '{$url}';</script>";
		}

		public static function renderLink ($label, $href, $id, $class = 'btn btn-primary', array $additionalAttributes = null) {
			$attributes = '';
			if ((isset ($additionalAttributes)) && (is_array ($additionalAttributes)) && (count ($additionalAttributes) > 0)) {
				foreach ($additionalAttributes as $key => $value) {
					$attributes .= ("$key=\"$value\" ");
				}
			}
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('LABEL', $label);
			$smarty->assign ('HREF', $href);
			$smarty->assign ('ID', $id);
			$smarty->assign ('CLASS', $class);
			$smarty->assign ('ADDITIONAL_ATTRIBUTES', trim ($attributes));
			return $smarty->fetch ('utils/HTMLLink.tpl');
		}

		public static function renderSelect ($id, $name, $options, $selectedValue = null, $class = '', $title = '') {
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('ID', $id);
			$smarty->assign ('NAME', $name);
			$smarty->assign ('OPTIONS', $options);
			$smarty->assign ('SELECTED_VALUE', $selectedValue);
			$smarty->assign ('TITLE', $title);
			if ($class) {
				$smarty->assign ('CLASS', $class);
			}
			return $smarty->fetch ('utils/HTMLSelect.tpl');
		}

		public static function renderSelectMultiple ($id, $name, $options, $selectedValues = null, $class = '', $title = '') {
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('ID', $id);
			$smarty->assign ('NAME', $name);
			$smarty->assign ('OPTIONS', $options);
			$smarty->assign ('SELECTED_VALUES', $selectedValues);
			$smarty->assign ('TITLE', $title);
			if ($class) {
				$smarty->assign ('CLASS', $class);
			}
			return $smarty->fetch ('utils/HTMLSelectMultiple.tpl');
		}

		public static function renderSelectOptions ($options, $selectedValue = null) {
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('OPTIONS', $options);
			$smarty->assign ('SELECTED_VALUE', $selectedValue);
			return $smarty->fetch ('utils/HTMLSelectOptions.tpl');
		}

		public static function renderSelectOptionsUsingArrayKey ($options, $selectedValue = null) {
			if ((is_array ($options)) && (!empty ($options))) {
				$refactoredOptions = array ();
				foreach ($options as $key => $value) {
					$refactoredOptions [] = array ('text' => $value, 'value' => $key);
				}
			} else {
				$refactoredOptions = $options;
			}
			return self::renderSelectOptions ($refactoredOptions, $selectedValue);
		}

		public static function renderUnorderedList ($entries, $id = null, $class = null) {
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('ENTRIES', $entries);
			if ($id) {
				$smarty->assign ('ID', $class);
			}
			if ($class) {
				$smarty->assign ('CLASS', $class);
			}
			return $smarty->fetch ('utils/HTMLUnorderedList.tpl');
		}

	}
