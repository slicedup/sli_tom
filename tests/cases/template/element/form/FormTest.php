<?php
/**
 * Slicedup: a fancy tag line here
 *
 * @copyright	Copyright 2011, Paul Webster / Slicedup (http://slicedup.org)
 * @license 	http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace sli_tom\tests\cases\template\element\form;

use sli_tom\template\element\form\Form;
use sli_tom\template\element\Literal;

class FormTest extends \sli_tom\tests\cases\template\ElementBaseTest {

	protected $model = 'lithium\tests\mocks\template\helper\MockFormPost';

	public function testFormCreate() {
		$context = $this->context;
		$form = new Form(compact('context'));
		$result = $form->render();
		$this->assertTags($result, array(
			array('form' => array('action' => '/posts', 'method' => 'post')),
			'/form'
		));
	}

	public function testFormContent() {
		$context = $this->context;
		$form = new Form(compact('context'));
		$content = new Literal(array(
			'content' => '<legend>This is a form.</legend>'
		));
		$content->inject($form);
		$result = $form->render();
		$this->assertTags($result, array(
			array('form' => array('action' => '/posts', 'method' => 'post')),
			array('legend' => array()),
			'This is a form.',
			'/legend',
			'/form'
		));
	}

	public function testFormWithFields() {
		$model = $this->model;
		$binding = $model::create();
		$context = $this->context;
		$form = new Form(compact('context', 'binding'));
		$result = $form->binding();
		$this->assertIdentical($binding, $result);
	}

	public function testFormWithBasicFields() {
		$model = $this->model;
		$record = $model::create();
		$context = $this->context;
		$form = new Form(compact('context'));
		$form->binding($record);
		$formBase = get_parent_class($form);
		$form->insert(new $formBase(array(
			'method' => 'field',
			'params' => array('title')
		)));
		$result = $form->render();
		$this->assertTags($result, array(
			array('form' => array('action' => '/posts', 'method' => 'post')),
			array('div' => array()),
			array('label' => array('for' => 'MockFormPostTitle')),
			'Title',
			'/label',
			array('input' => array('type' => 'text', 'name' => 'title', 'id' => 'MockFormPostTitle')),
			'/div',
			'/form'
		));
	}

	public function testFieldsetCreation() {
		$context = $this->context;
		$form = new Form(compact('context'));
		$f = $form->addFieldset(array('legend' => 'I am Legend.'));
		$result = $form->render();
		$this->assertTags($result, array(
			array('form' => array('action' => '/posts', 'method' => 'post')),
			array('fieldset' => array()),
			array('legend' => array()),
			'I am Legend.',
			'/legend',
			'/fieldset',
			'/form'
		));
	}
}

?>