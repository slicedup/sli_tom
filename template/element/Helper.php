<?php
/**
 * Slicedup: a fancy tag line here
 *
 * @copyright	Copyright 2011, Paul Webster / Slicedup (http://slicedup.org)
 * @license 	http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace sli_tom\template\element;

/**
 * The `Helper` element class build upon the `Element` by also requiring
 * a configurable helper to either source the rendered string from, or to
 * explicitly be called for rendering of the element by also setting a method
 * & configured params for invocation.
 */
abstract class Helper extends \sli_tom\template\Element {

	/**
	 * Auto config vars
	 *
	 * @var array
	 */
	protected $_autoConfig = array(
		'attributes' => 'merge',
		'params' => 'merge',
		'options' => 'merge',
		'method',
		'template',
		'context'
	);

	/**
	 * Attributes are passed as the last argument to helper methods when a method
	 * is configured for rendering. They are also combined with options.
	 *
	 * @see sli_tom\template\Element::attributes
	 * @see sli_tom\template\element\Helper::attributes
	 * @var array
	 */
	protected $_attributes = array();

	/**
	 * Params are passed as the arguments to helper methods when a method
	 * is configured for rendering.
	 *
	 * @see sli_tom\template\Element::params
	 * @var array
	 */
	protected $_params = array();

	/**
	 * Options are combined with attributes when passed to helper methods when
	 * a method is configured for rendering. This is to allow for configuration
	 * of any non element attributes such as those that control rendering.
	 *
	 * @see sli_tom\template\Element::options
	 * @var array
	 */
	protected $_options = array();

	/**
	 * String helper name set in subclasses.
	 *
	 * @var string
	 */
	protected $_helper = null;

	/**
	 * String helper method
	 *
	 * @var string
	 */
	protected $_method = null;

	/**
	 * Render element to string, loading helper, and calling configured helper
	 * method for rendering if configured.
	 *
	 * @see sli_tom\template\Element::render()
	 */
	public function render($context = null) {
		if ($context) {
			$this->context($context);
		}
		$helper =& $this->_helper();
		if ($this->_method) {
			$params = array_values($this->params());
			$params[] = $this->attributes() + $this->options();
			return $helper->invokeMethod($this->_method, $params);
		}
		return parent::render();
	}

	/**
	 * Load helper from rendering context
	 */
	protected function &_helper() {
		$helper = $this->context(true)->helper($this->_helper);
		return $helper;
	}
}

?>