<?php

class NestedCheckboxSetField extends CheckboxSetField {

	protected
		$labelField = 'Title',
		$idField = 'ID',
		$className,
		$childFunction;

	function __construct(DataObject $controller, $name, $title = null, $className = null, $childFunction = 'Children', DataObjectSet $source = null, $value = "", $form = null, $emptyString = null) {
		if (!$title) {
			$this->title = self::name_to_label($name);
		}
		if (!$className) {
			if ((!$className = $controller->has_one($name)) && (!$className = $controller->belongs_to($name)) && (($settings = $controller->has_many($name)) || ($settings = $controller->many_many($name)))) {
				$className = $settings[1];
			}
			else {
				trigger_error('Couldn\'t determine class type');
			}
		}
		elseif (!class_exists($className)) {
			trigger_error($className . ' class doesn\'t exist');
		}
		elseif (!singleton($className)->hasMethod($childFunction)) {
			trigger_error($childFunction . ' does not exist on object type: ' . $className);
		}
		$this->className = $className;
		$this->childFunction = $childFunction;
		parent::__construct($name, $title, $source, $value, $form, $emptyString);
	}

	function setChildFunction($functionName) {
		if (!singleton($this->className)->hasMethod($functionName)) {
			trigger_error($childFunction . ' does not exist on object type: ' . $className . '. Not making change', E_USER_WARNING);
		}
		else {
			$this->childFunction = $functionName;
		}
	}

	function Field() {
		Requirements::css(SAPPHIRE_DIR . '/css/CheckboxSetField.css');
		Requirements::javascript(BASE_URL . 'mysite/javascript/indeterminateCheckboxes.js');
		if ($this->source) {
			$source = $this->source;
		}
		else {
			$source = DataObject::get($this->className);
		}
		$options = '';
		if (!$this->source) {
			$options = "<li>No options available</li>";
		}
		else {
			$options = $this->buildNestedList($source);
		}
		return "<ul id=\"{$this->id()}\" class=\"optionset checkboxsetfield{$this->extraClass()}\">\n$options</ul>\n";
	}

	function buildNestedList($source) {
		if ($source && $source->exists()) {
			$odd = 0;
			$html = '<ul>';
			foreach ($source as $item) {
				$key = $item->{$this->idField};
				$value = $item->{$this->labelField};
				$odd = ($odd + 1) % 2;
				$extraClass = $odd ? 'odd' : 'even';
				$extraClass .= ' val' . str_replace(' ', '', $key);
				$itemID = $this->id() . '_' . ereg_replace('[^a-zA-Z0-9]+', '', $key);
				$checked = '';

				if(!empty($items)) {
					$checked = (in_array($key, $items) || in_array($key, $this->defaultItems)) ? ' checked="checked"' : '';
				}

				$disabled = ($this->disabled || in_array($key, $this->disabledItems)) ? $disabled = ' disabled="disabled"' : '';
				$html .= "<li class=\"$extraClass\"><input id=\"$itemID\" name=\"$this->name[$key]\" type=\"checkbox\" value=\"$key\"$checked $disabled class=\"checkbox\" /> <label for=\"$itemID\">$value</label>" . $this->buildNestedList($item->{$this->childFunction}()) . "</li>\n";
			}
			return $html . '</ul>';
		}
	}

	function makeLevel($source) {
		if (!$source || !is_array($source)) {
			return;
		}
		$level = '';
		foreach ($source as $val => $label) {
			if (is_string($label)) {
			}
			else {
				$level .= $this->makeLevel($source);
			}
		}
		return $level;
	}

}