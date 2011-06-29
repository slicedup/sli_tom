<?php
/**
 * Slicedup: a fancy tag line here
 *
 * @copyright	Copyright 2011, Paul Webster / Slicedup (http://slicedup.org)
 * @license 	http://opensource.org/licenses/bsd-license.php The BSD License
 */

use lithium\core\Libraries;

/**
 * Add `'behavior'` type configured class types.
 */
Libraries::paths(array(
	'element' => array(
		'{:library}\extensions\element\{:class}\{:namespace}\{:name}',
		'{:library}\template\element\{:class}\{:namespace}\{:name}' => array('libraries' => 'sli_dom'),
	)
));

?>