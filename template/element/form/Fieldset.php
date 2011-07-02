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

	protected function _init() {
		$this->_config += array(
			'legend' => null
		);
		parent::_init();
		if (isset($this->_config['fields'])) {
			foreach($this->_config['fields'] as $name => $field) {
				if (!is_int($name)) {
					$field = array(
						'name' => $name,
						'attributes' => $field
					);
				}
				$this->insert(static::create('field', $field));
			}
			unset($this->_config['fields']);
		}
	}

	public function legend($content = null) {
		if (isset($content)) {
			$this->_config['legend'] = (string) $content;
		}
		return $this->_config['legend'];
	}

	protected function _render() {
		if ($legend = $this->legend()) {
			$this->insert(static::create('legend', array('content' => $legend)), 'start');
			$this->legend(false);
		}
		$content = implode($this->invoke('render'));
		return compact('content');
	}
}

?>