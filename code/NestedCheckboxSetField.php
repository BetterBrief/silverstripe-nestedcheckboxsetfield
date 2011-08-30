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
		Requirements::javascript(MOD_NCBSF_DIR . '/javascript/indeterminateCheckboxes.js');
		$items = null;
		if ($this->source) {
			$source = $this->source;
		}
		else {
			$source = DataObject::get($this->className);
		}
		$values = $this->value;

		// Get values from the join, if available
		if(is_object($this->form)) {
			$record = $this->form->getRecord();
			if(!$values && $record && $record->hasMethod($this->name)) {
				$funcName = $this->name;
				$join = $record->$funcName();
				if($join) {
					foreach($join as $joinItem) {
						$values[] = $joinItem->ID;
					}
				}
			}
		}

		// Source is not an array
		if(!is_array($source) && !is_a($source, 'SQLMap')) {
			if(is_array($values)) {
				$items = $values;
			} else {
				// Source and values are DataObject sets.
				if($values && is_a($values, 'DataObjectSet')) {
					foreach($values as $object) {
						if(is_a($object, 'DataObject')) {
							$items[] = $object->ID;
						}
				   }
				} elseif($values && is_string($values)) {
					$items = explode(',', $values);
					$items = str_replace('{comma}', ',', $items);
				}
			}
		} else {
			// Sometimes we pass a singluar default value thats ! an array && !DataObjectSet
			if(is_a($values, 'DataObjectSet') || is_array($values)) {
				$items = $values;
			} else {
				$items = explode(',', $values);
				$items = str_replace('{comma}', ',', $items);
			}
		}

		if(is_array($source)) {
			unset($source['']);
		}
		$options = '';
		if (!$this->source) {
			$options = "<li>No options available</li>";
		}
		else {
			$options = $this->buildNestedList($source,$items);
		}
		return "<ul id=\"{$this->id()}\" class=\"optionset checkboxsetfield{$this->extraClass()}\">\n$options</ul>\n";
	}

	function buildNestedList($source,$items) {
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
				$html .= "<li class=\"$extraClass\"><input id=\"$itemID\" name=\"$this->name[$key]\" type=\"checkbox\" value=\"$key\"$checked $disabled class=\"checkbox\" /> <label for=\"$itemID\">$value</label>" . $this->buildNestedList($item->{$this->childFunction}(),$items) . "</li>\n";
			}
			return $html . '</ul>';
		}
	}

}
