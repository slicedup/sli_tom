<?php
/**
 * Slicedup: a fancy tag line here
 *
 * @copyright	Copyright 2011, Paul Webster / Slicedup (http://slicedup.org)
 * @license 	http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace sli_tom\util;

use lithium\util\Collection;

/**
 * The `Node` class is an extended implmentation of the Lithium native
 * `Collection` to allow for simple handling of nested collections
 * of `Node` objects.
 *
 * Example usage:
 * {{{
 * $root = new Node();
 * $first = new Node();
 * $root->insert($first);
 * $second = new Node(array(
 * 	'parent' => $first
 * ));
 * foreach($child as $root) {
 * 	if ($child->hasChildren()) {
 * 		//do something
 * 	}
 * }
 * }}}
 *
 * NOTE #1: This class does not manage incorrect recursive nesting, nor
 * does it handle exclusion of child `Nodes` added more than once.
 *
 * NOTE #2: When adding/removing children via array access or other and specifying
 * specific keys, items will not retain their keys unless they are the logical
 * numeriacl next key in use. All data is reindexed when updated.
 */
class Node extends \lithium\util\Collection {

	/**
	 * Auto config vars
	 *
	 * @var array
	 */
	protected $_autoConfig = array('final');

	/**
	 * Parent Node
	 *
	 * @var \sli_tom\util\Node
	 */
	protected $_parent = null;

	/**
	 * Final nodes cannot have child nodes.
	 *
	 * @var boolean
	 */
	protected $_final = false;

	/**
	 * Init
	 */
	protected function _init() {
		if (isset($this->_config['data'])) {
			$this->_config['children'] = $this->_config['data'];
			unset($this->_config['data']);
		}
		if (isset($this->_config['children'])) {
			while($child = array_shift($this->_config['children'])) {
				$this->addChild($child);
			}
		}
		if (isset($this->_config['parent'])) {
			$this->setParent($this->_config['parent']);
		}
		unset($this->_config['parent'], $this->_config['children']);
		parent::_init();
	}

	/**
	 * Get root node of Node's tree.
	 *
	 * @return \sli_tom\util\Node
	 */
	public function root() {
		if ($parents = $this->parents()) {
			return $parents->end();
		}
		return $this;
	}

	/**
	 * Get parent node, optionally filtered to find the first ancestors that
	 * matches, startng rom the current node back to the root node.
	 *
	 * @param callback $filter
	 * @return \sli_tom\util\Node
	 */
	public function parent($filter = null) {
		if ($filter && $this->_parent) {
			if (is_string($filter)) {
				$filter = static::_classFilter($filter);
			}
			return $this->parents()->first($filter);
		}
		return $this->_parent;
	}

	/**
	 * Get parent nodes, optionally filtered to find the ancestors that match,
	 * startng rom the current node back to the root node.
	 *
	 * @param callback $filter
	 * @return mixed null or \lithium\util\Collection
	 */
	public function parents($filter = null) {
		if ($parent = $this->parent()) {
			$data = array($parent);
			while($parent = $parent->parent()) {
				$data[] = $parent;
			}
			$parents = new Collection(compact('data'));
			if ($filter) {
				if (is_string($filter)) {
					$filter = static::_classFilter($filter);
				}
				$parents = $parents->find($filter);
			}
			return $parents;
		}
	}

	/**
	 * Set parent node
	 *
	 * @param \sli_tom\util\Node $element
	 * return null
	 */
	public function setParent(\sli_tom\util\Node $element = null) {
		$this->_parent = $element;
		if ($element && $element->indexOf($this) === false) {
			$element->addChild($this);
		}
	}

	/**
	 * Check if node has child nodes
	 *
	 * @return boolean
	 */
	public function hasChildren() {
		return !empty($this->_data);
	}

	public function first($filter = null) {
		if (is_string($filter)) {
			$filter = static::_classFilter($filter);
		}
		return parent::first($filter);
	}

	/**
	 * Returns the last non-empty value in the collection after a filter is
	 * applied, or forwards the collection and returns the last value.
	 *
	 * @param callback $filter
	 * @return mixed null or \sli_tom\util\Node
	 */
	public function last($filter = null) {
		if (!$filter) {
			return $this->end();
		}
		if (is_string($filter)) {
			$filter = static::_classFilter($filter);
		}
		$this->rewind();
		while ($item = $this->prev()) {
			if ($filter($item)) {
				return $item;
			}
		}
	}

	/**
	 * Get N'th child node, by logical numeric position as opposed to
	 * zero indexed key.
	 *
	 * @param int $position numeric position of child node
	 * @return mixed null or \sli_tom\util\Node
	 */
	public function nth($position) {
		if (!$position) {
			return;
		}
		$index = (int) $position;
		if ($index < 0) {
			$count = $this->count();
			while($index < 0) {
				$index += $count;
			}
		} else {
			$index--;
		}
		if (isset($this->_data[$index])) {
			return $this->_data[$index];
		}
	}

	/**
	 * Insert a child node
	 *
	 * @param \sli_tom\util\Node $element
	 * @param string $where
	 * @param mixed $index
	 * @return mixed null or \sli_tom\util\Node
	 */
	public function insert(\sli_tom\util\Node $element, $where = null, $index = null) {
		switch($where){
			case 'start':
				$index = 0;
				$method = 'addBefore';
			break;
			case 'before':
				$index = isset($index) ? $index : 0;
				$method = 'addBefore';
			break;
			case 'after':
				$index = isset($index) ? $index : ($this->end() ? $this->key() : 0);
				$method = 'addAfter';
			break;
			case 'end':
			default:
				return $this->addChild($element);
			break;
		}
		return $this->$method($index, $element);
	}

	/**
	 * Inject node as child node
	 *
	 * @param \sli_tom\util\Node $into
	 * @param string $where
	 * @param mixed $index
	 * @return mixed null or \sli_tom\util\Node
	 */
	public function inject(\sli_tom\util\Node $into, $where = null, $index = null) {
		return $into->insert($this, $where, $index);
	}

	/**
	 * Replace a child node with another node
	 *
	 * @param \sli_tom\util\Node $search
	 * @param \sli_tom\util\Node $replace
	 * @return mixed null or \sli_tom\util\Node
	 */
	public function replace(\sli_tom\util\Node $search, \sli_tom\util\Node $replace) {
		if ($index = $this->indexOf($search)) {
			return $this->addChild($replace, $index);
		}
	}

	/**
	 * Add child node.
	 *
	 * @param \sli_tom\util\Node $element
	 * @param mixed $index
	 * @return mixed null or \sli_tom\util\Node $element
	 */
	public function addChild(\sli_tom\util\Node $element, $index = null) {
		if ($this->_final) {
			return;
		}
		if (isset($index)) {
			$this->_data[$index] =& $element;
			$this->_data = array_values($this->_data);
		} else {
			$this->append($element);
		}
		$element->setParent($this);
		return $element;
	}

	/**
	 * Add child node after an existing child node
	 *
	 * @param mixed $index
	 * @param \sli_tom\util\Node $element
	 * @return mixed null or \sli_tom\util\Node $element
	 */
	public function addAfter($index, \sli_tom\util\Node $element) {
		if (is_object($index)) {
			$index = $this->indexOf($index);
		}
		if (isset($this->_data[$index])) {
			return $this->_insert($element, ++$index);
		}
		return $this->addChild($element, $index);
	}

	/**
	 * Add child node before an existing child node
	 *
	 * @param mixed $index
	 * @param \sli_tom\util\Node $element
	 * @return mixed null or \sli_tom\util\Node $element
	 */
	public function addBefore($index, \sli_tom\util\Node $element) {
		if (is_object($index)) {
			$index = $this->indexOf($index);
		}
		if (isset($this->_data[$index])) {
			return $this->_insert($element, $index);
		}
		return $this->addChild($element, $index);
	}

	/**
	 * Remove a child node
	 *
	 * @param mixed $index
	 * @return mixed null or \sli_tom\util\Node $element
	 */
	public function removeChild($index = null) {
		if (!isset($index)) {
			$index = $this->count() -1;
		} elseif (is_object($index)) {
			$index = $this->indexOf($index);
		}
		if (is_int($index) && $index >= 0 && isset($this->_data[$index])) {
			$value = $this->_data[$index];
			$value->setParent();
			unset($this->_data[$index]);
			$this->_data = array_values($this->_data);
			return $value;
		}
	}

	/**
	 * Remove all children
	 */
	public function removeChildren() {
		$this->invoke('setParent');
		$this->_data = array();
	}

	/**
	 * Lookup numerix index of a child node
	 *
	 * @param \sli_tom\util\Node $element
	 * @return mixed integer index or false if not found
	 */
	public function indexOf(\sli_tom\util\Node $element) {
		return array_search($element, $this->_data, true);
	}

	/**
	 * Lookup numeric position of a child node
	 *
	 * @param \sli_tom\util\Node $element
	 * @return mixed integer index or false if not found
	 */
	public function position(\sli_tom\util\Node $element) {
		if ($index = $this->indexOf($element)) {
			return ++$index;
		}
	}

	/**
	 * ArrayAccess
	 */
	public function offsetSet($offset, $value) {
		return $this->addChild($value, $offset);
	}

	/**
	 * ArrayAccess
	 */
	public function offsetUnset($offset) {
		return $this->removeChild($offset);
	}

	/**
	 * Internal insert handling
	 *
	 * @param \sli_tom\util\Node $element
	 * @param integer $index
	 * @return mixed boolean false or \sli_tom\util\Node $element
	 */
	protected function _insert(\sli_tom\util\Node $element, $index) {
		if ($this->_final) {
			return false;
		}
		$start = array_slice($this->_data, 0, $index);
		$end = array_slice($this->_data, $index);
		$start[] =& $element;
		$this->_data = array_merge($start, $end);
		$element->setParent($this);
		return $element;
	}

	protected static function _classFilter($class) {
		$filter = function($self) use($class) {
			return ($self instanceOf $class);
		};
		return $filter;
	}
}

?>