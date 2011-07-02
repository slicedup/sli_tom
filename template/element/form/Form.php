<?php
/**
 * Slicedup: a fancy tag line here
 *
 * @copyright	Copyright 2011, Paul Webster / Slicedup (http://slicedup.org)
 * @license 	http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace sli_tom\template\element\form;

class Form extends \sli_tom\template\element\Form {

	protected $_method = 'create';

	protected $_params = array(
		'binding' => null
	);

	protected function _init() {
		parent::_init();
		if (isset($this->_config['fieldsets'])) {
			foreach($this->_config['fieldsets'] as $fieldset) {
				if(!is_object($fieldset)) {
					$fieldset = self::create('fieldset', $fieldset);
				}
				$this->insert($fieldset);
			}
			unset($this->_config['fieldsets']);
		}
	}

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

	public function render($context = null) {
		$start = parent::render($context);
		$context = $this->context(true);
		$content = implode('', $this->_render());
		$end = $context->helper('elements')->string('form-end');
		return "{$start}{$content}{$end}";
	}
}

?>