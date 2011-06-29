<?php
/**
 * Slicedup: a fancy tag line here
 *
 * @copyright	Copyright 2011, Paul Webster / Slicedup (http://slicedup.org)
 * @license 	http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace sli_dom\template;

use lithium\core\Libraries;

/**
 * The `Element` class provides a means to create & manipulate a dom like
 * structure of nested Element nodes, and export these to rendered strings
 * for output in view context or elsewhere as required.
 */
class Element extends \sli_dom\util\Node {

	/**
	 * Formatters
	 *
	 * @var array
	 */
	protected static $_formats = array(
		'array' => 'sli_dom\template\Element::toArray',
		'json' => 'sli_dom\template\Element::toJson',
		'string' => 'sli_dom\template\Element::toString'
	);

	/**
	 * Auto config vars
	 *
	 * @var array
	 */
	protected $_autoConfig = array(
		'attributes' => 'merge',
		'params' => 'merge',
		'options' => 'merge',
		'template',
		'context'
	);

	/**
	 * Attributes
	 *
	 * @see sli_dom\template\Element::attributes()
	 * @var array
	 */
	protected $_attributes = array();

	/**
	 * Params
	 *
	 * @see sli_dom\template\Element::params()
	 * @var array
	 */
	protected $_params = array();

	/**
	 * Options
	 *
	 * @see sli_dom\template\Element::options()
	 * @var array
	 */
	protected $_options = array();

	/**
	 * Rendering context
	 *
	 * @var \lithium\template\view\Renderer
	 */
	protected $_context = null;

	/**
	 * String output template
	 *
	 * @var string
	 */
	protected $_template = '{:content}';

	/**
	 * Lazy element loading/creation
	 *
	 * @param string $type element name
	 * @param array $params arguments to pass to new element on construct
	 * @return sli_dom\template\Element or null if not found
	 */
	public static function create($type = null, $params = array()){
		if (!isset($type) || is_array($type)) {
			$params = $type ?: $params;
			$class = get_called_class();
			return new $class($params);
		}
		$locate = 'element';
		if (strpos($type, '\\') !== false) {
			if (class_exists($type, false)) {
				return new $class($params);
			} else {
				$path = explode('\\', $type);
				$type = array_pop($path);
				array_unshift($path, $locate);
				$locate = implode('.', $path);
			}
		}
		if($class = Libraries::locate($locate, $type)) {
			return new $class($params);
		}
	}

	/**
	 * Get/Set attributes.
	 *
	 * Attributes are used as actual html attributes in the default rendering
	 * process, and will be passed to any template for rendering as the
	 * `options` param. For example, given you have a template string of
	 * `<div{:options}>{:content}</div>`, any attribuutes will be formatted and
	 * output in place of `{:options}`.
	 *
	 * @param array $params attrbutes
	 * @return array
	 */
	public function attributes(array $params = array()) {
		if ($params) {
			$this->_attributes = $params;
		}
		return $this->_attributes;
	}

	/**
	 * Attribute convenience
	 *
	 * @param array $params attrbutes
	 * @return array
	 */
	public function attr(array $params = array()) {
		return $this->attributes($params);
	}

	/**
	 * Get/Set params.
	 *
	 * Params are data keys for an element, in the default rendering process
	 * for example, given a template string of `<div>Hello {:name}.....</div>`,
	 * setting a param with key `name` this will be inserted into your template
	 * in place of `{:name}`.
	 *
	 * @param array $params
	 */
	public function params(array $params = array()) {
		if ($params) {
			$this->_params = $params;
		}
		return $this->_params;
	}

	/**
	 * Get/Set rendering options.
	 *
	 * Default rendering of template strings is handled by Helper::_render(),
	 * set any additional options that are required to be passed through to
	 * this method and subsequently the context handlers.
	 *
	 * @param array $params
	 */
	public function options(array $params = array()) {
		if ($params) {
			$this->_options = $params;
		}
		return $this->_options;
	}

	/**
	 * Element rendering context.
	 *
	 * Default rendering of elements passes template strings through a
	 * rendering context, at least the root element of any element tree must
	 * have a conext set to allow rendering.
	 *
	 * @param \lithium\template\view\Renderer $context
	 */
	public function context($context = null) {
		$contextClass = '\lithium\template\view\Renderer';
		if ($context instanceOf $contextClass) {
			$this->_context = $context;
		}
		if ($this->_context instanceOf $contextClass) {
			return $this->_context;
		} elseif ($context === true) {
			$parent = $this;
			while($parent = $parent->parent()) {
				if ($context = $parent->context()) {
					return $context;
				}
			}
			$message = "%s requires a renderrer context %s.";
			throw new \RuntimeException(sprintf($message, get_class(), $contextClass));
		}
	}

	/**
	 * Render element to string.
	 *
	 * Passes configured attributes as `options`, child elements as `content`,
	 * appends to params, and passes of to Element helper for string rendering.
	 *
	 * @param \lithium\template\view\Renderer $context
	 * @return string output
	 */
	public function render($context = null) {
		if ($context) {
			$this->context($context);
		}
		$context = $this->context(true);
		$options = $this->_attributes;
		$params = $this->_params;
		$params += compact('options') + $this->_render();
		return $context->helper('element')->string($this->_template, $params, $this->options());
	}

	/**
	 * Export element.......
	 */
	public function export() {}

	/**
	 * Render child elements, and set to params key used for rendering
	 *
	 * @return string output of rendered child elements
	 */
	protected function _render() {
		return array('content' => implode($this->invoke('render')));
	}

	/**
	 * Export Element collection to array
	 */
	public static function toArray($data, array $options = array()){
		$self = get_called_class();
		if (!is_object($data) || !($data instanceOf $self)) {
			return;
		}
		return $data->params() + array(
			'options' => $data->attributes(),
			'content' => $data->invoke('to', array('array'))
		);
	}

	/**
	 * Export Element collection to json
	 */
	public static function toJson($data, array $options = array()){
		$self = get_called_class();
		if (!is_object($data) || !($data instanceOf $self)) {
			return;
		}
		$options += array(
			'json' => 0
		);
		$jsonOptions = $options['json'];
		unset($options['json']);
		$data = static::toArray($data, $options);
		return json_encode($data, $jsonOptions);
	}

	/**
	 * Export Element collection to string (render)
	 */
	public static function toString($data, array $options = array()){
		$self = get_called_class();
		if (!is_object($data) || !($data instanceOf $self)) {
			return;
		}
		return $data->render();
	}

	/**
	 * @see sli_dom\template\Element::toString()
	 */
	public function __toString() {
		try {
			return static::toString($this);
		} catch (\RuntimeException $e) {
			return $e->getMessage();
		}
	}
}

?>