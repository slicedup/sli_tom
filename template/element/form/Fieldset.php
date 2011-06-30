<?php
/**
 * Slicedup: a fancy tag line here
 *
 * @copyright	Copyright 2011, Paul Webster / Slicedup (http://slicedup.org)
 * @license 	http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace sli_tom\template\element\form;

class Fieldset extends \sli_tom\template\element\Form {

	protected $_template = 'fieldset';

	protected $_params = array(
		'legend' => null
	);

	public function addField($config = array()) {
		if (!is_object($config)) {
			$field = $this->createField($config);
		} else {
			$fieldset = $config;
		}
		return $this->insert($fieldset);
	}

	public function createField($config = array()) {
		$class = $this->_classes['field'];
		return new $class($config);
	}

	public function legend($content = null) {
		if (isset($content)) {
			$this->_params['legend'] = (string) $content;
		}
		return $this->_params['legend'];
	}

	protected function _render() {
		if ($legend = $this->legend()) {
			$class = $this->_classes['legend'];
			$this->insert(new $class(array('content' => $legend)), 'before');
			$this->legend(false);
		}
		$content = implode($this->invoke('render'));
		return compact('content');
	}
}

?>