<?php
/**
 * Slicedup: a fancy tag line here
 *
 * @copyright	Copyright 2011, Paul Webster / Slicedup (http://slicedup.org)
 * @license 	http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace sli_dom\template\element\form;

class Form extends \sli_dom\template\element\Form {

	protected $_method = 'create';

	protected $_params = array(
		'binding' => null
	);

	/**
	 * Get/Set form binding
	 *
	 * @param mixed $binding
	 */
	public function binding($binding = null) {
		if ($binding) {
			$this->_params['binding'] =& $binding;
		}
		return $this->_params['binding'];
	}

	public function addFieldset($config = array()) {
		if (!is_object($config)) {
			$fieldset = $this->createFieldset($config);
		} else {
			$fieldset = $config;
		}
		return $this->insert($fieldset);
	}

	public function createFieldset($config = array()) {
		$class = $this->_classes['fieldset'];
		return new $class($config);
	}

	public function render($context = null) {
		$start = parent::render($context);
		$context = $this->context(true);
		$content = implode('', $this->_render());
		$end = $context->helper('element')->string('form-end');
		return "{$start}{$content}{$end}";
	}
}

?>