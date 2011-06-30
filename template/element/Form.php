<?php
/**
 * Slicedup: a fancy tag line here
 *
 * @copyright	Copyright 2011, Paul Webster / Slicedup (http://slicedup.org)
 * @license 	http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace sli_dom\template\element;

class Form extends Helper {

	protected $_classes = array(
		'base' => 'sli_dom\template\element\Form',
		'form' => 'sli_dom\template\element\form\Form',
		'fieldset' => 'sli_dom\template\element\form\Fieldset',
		'legend' => 'sli_dom\template\element\form\Legend',
		'field' => 'sli_dom\template\element\form\Field',
	);

	protected $_helper = 'Form';

	protected function _init() {
		$this->autoConfig['classes'] = 'merge';
		parent::_init();
	}

	public function form() {
		return $this->_parentType('form');
	}

	public function fieldset() {
		return $this->_parentType('fieldset');
	}

	protected function _parentType($type = 'form') {
		$class = $this->_classes[$type];
		if (get_class($this) == $class) {
			return $this;
		}
		return $this->parent(function($self) use($class) {
			return get_class($self) == $class;
		});
	}
}

?>