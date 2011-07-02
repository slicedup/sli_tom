<?php
/**
 * Slicedup: a fancy tag line here
 *
 * @copyright	Copyright 2011, Paul Webster / Slicedup (http://slicedup.org)
 * @license 	http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace sli_tom\template\element;

class Form extends Helper {

	protected static $_types = array(
		'base' => 'sli_tom\template\element\Form',
		'form' => 'sli_tom\template\element\form\Form',
		'fieldset' => 'sli_tom\template\element\form\Fieldset',
		'legend' => 'sli_tom\template\element\form\Legend',
		'field' => 'sli_tom\template\element\form\Field',
		'submit' => 'sli_tom\template\element\form\Submit',
	);

	protected $_helper = 'Form';
}

?>