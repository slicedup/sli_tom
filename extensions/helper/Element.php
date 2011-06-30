<?php
/**
 * Slicedup: a fancy tag line here
 *
 * @copyright	Copyright 2011, Paul Webster / Slicedup (http://slicedup.org)
 * @license 	http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace sli_tom\extensions\helper;

use sli_tom\template\Element as ElementClass;

class Element extends \lithium\template\Helper {

	public function create($type, $params = array()){
		return ElementClass::create($type, $params);
	}

	public function context(sli_tom\template\Element &$element, $context = null) {
		$context = is_object($context) ? $context : $this->_context;
		$element->context($context);
	}

	public function render(sli_tom\template\Element &$element, $context = true) {
		if ($context) {
			$this->context($element, $context);
		}
		return $element->render();
	}

	public function string($string, $params = array(), array $options = array()) {
		return $this->_render(__METHOD__, $string, $params, $options);
	}

	public static function attributes($params, array $options = array()) {
		return $this->_attributes($params, null, $options);
	}
}
?>