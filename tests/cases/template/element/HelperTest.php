<?php
/**
 * Slicedup: a fancy tag line here
 *
 * @copyright	Copyright 2011, Paul Webster / Slicedup (http://slicedup.org)
 * @license 	http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace sli_dom\tests\cases\template\element;

use lithium\test;

use lithium\action\Request;
use lithium\net\http\Router;
use lithium\data\entity\Record;
use lithium\tests\mocks\template\helper\MockFormRenderer;
use sli_dom\tests\mocks\template\element\MockHelperElement;

class HelperTest extends \lithium\test\Unit {

	public function setUp() {
		$this->_routes = Router::get();
		Router::reset();
		Router::connect('/{:controller}/{:action}/{:id}.{:type}', array('id' => null));
		Router::connect('/{:controller}/{:action}/{:args}');

		$request = new Request();
		$request->params = array('controller' => 'posts', 'action' => 'index');
		$request->persist = array('controller');

		$this->context = new MockFormRenderer(compact('request'));
	}

	public function tearDown() {
		Router::reset();

		foreach ($this->_routes as $route) {
			Router::connect($route);
		}
		unset($this->_routes, $this->context);
	}

	public function testLoadHelper() {
		$element = new MockHelperElement(array(
			'helper' => 'html',
			'context' => $this->context
		));
		$result = $element->invokeMethod('_helper');
		$this->assertTrue($result instanceOf \lithium\template\helper\Html);
	}

	public function testHelperString() {
		$element = new MockHelperElement(array(
			'context' => $this->context,
			'helper' => 'html',
			'template' => 'tag',
			'params' => array(
				'name' => 'div',
				'content' => 'This is the content'
			),
			'attributes' => array(
				'id' => 'TestID',
				'class' => 'TestClass'
			)
		));
		$expected = '<div id="TestID" class="TestClass">This is the content</div>';
		$result = $element->render();
		$this->assertEqual($expected, $result);
	}

	public function testHelperMethod() {
		$element = new MockHelperElement(array(
			'context' => $this->context,
			'helper' => 'html',
			'method' => 'link',
			'params' => array(
				'This is a link',
				'/this-is-a-url'
			),
			'attributes' => array(
				'id' => 'TestID',
				'class' => 'TestClass'
			)
		));
		$expected = '<a href="/this-is-a-url" id="TestID" class="TestClass">This is a link</a>';
		$result = $element->render();
		$this->assertEqual($expected, $result);
	}
}
?>