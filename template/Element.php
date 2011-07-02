<?php
/**
 * Slicedup: a fancy tag line here
 *
 * @copyright	Copyright 2011, Paul Webster / Slicedup (http://slicedup.org)
 * @license 	http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace sli_tom\template;

use lithium\core\Libraries;

/**
 * The `Element` class provides a means to create & manipulate a dom like
 * structure of nested Element nodes, and export these to rendered strings
 * for output in view context or elsewhere as required.
 */
class Element extends \sli_tom\util\Node {

	/**
	 * Formatters
	 *
	 * @var array
	 */
	protected static $_formats = array(
		'string' => 'sli_tom\template\Element::toString'
	);

	protected static $_types = array();

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
	 * @see sli_tom\template\Element::attributes()
	 * @var array
	 */
	protected $_attributes = array();

	/**
	 * Params
	 *
	 * @see sli_tom\template\Element::params()
	 * @var array
	 */
	protected $_params = array();

	/**
	 * Options
	 *
	 * @see sli_tom\template\Element::options()
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

	protected function _init() {
		if (!empty($this->_params)) {
			foreach ($this->_params as $param => &$value) {
				if (isset($this->_config[$param])) {
					$value = $this->_config[$param];
				}
			}
		}
		parent::_init();
	}

	/**
	 * Lazy element loading/creation
	 *
	 * @param string $type element name
	 * @param array $params arguments to pass to new element on construct
	 * @return sli_tom\template\Element or null if not found
	 */
	public static function create($type = null, $params = array()){
		if (isset(static::$_types[$type])) {
			$type = static::$_types[$type];
		}
		if (!isset($type) || is_array($type)) {
			$params = $type ?: $params;
			$class = get_called_class();
			return new $class($params);
		}
		$locate = 'element';
		if (strpos($type, '\\') !== false) {
			if (class_exists($type)) {
				return new $type($params);
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

	public function parent($filter = null) {
		if (is_string($filter)) {
			$class = static::$_types[$filter];
			if ($this instanceOf $class) {
				return $this;
			}
			$filter = function($self) use($class) {
				return ($self instanceOf $class);
			};
		}
		return parent::parent($filter);
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
		$options = $this->attributes();
		$params = $this->params();
		$params += compact('options') + $this->_render();
		return $context->helper('elements')->string($this->_template, $params, $this->options());
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
	 * @see sli_tom\template\Element::toString()
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