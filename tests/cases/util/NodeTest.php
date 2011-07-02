<?php
/**
 * Slicedup: a fancy tag line here
 *
 * @copyright	Copyright 2011, Paul Webster / Slicedup (http://slicedup.org)
 * @license 	http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace sli_tom\tests\cases\util;

use sli_tom\util\Node;

class NodeTest extends \lithium\test\Unit {

	public function testParentScope() {
		$n1 = new Node();
		$n2 = new Node();
		$n3 = new Node(array(
			'parent' => $n2
		));
		$n4 = new Node();
		$n2->setParent($n1);
		$n3->insert($n4);
		$this->assertIdentical($n1, $n2->parent());
		$this->assertIdentical($n1, $n2->root());
		$this->assertIdentical($n1, $n3->root());
		$this->assertIdentical($n1, $n2->parents()->first());
		$this->assertIdentical($n1, $n3->parents()->end());
		$this->assertIdentical($n2, $n1->first());
		$this->assertIdentical($n3, $n2->first());
		$this->assertIdentical($n1, $n3->parent(function($item) use($n1) {
			return $item == $n1;
		}));
		$result = $n4->parents(function($item) use($n1, $n3) {
			return $item == $n1 || $item == $n3;
		});
		$this->assertIdentical($n3, $result->first());
		$this->assertIdentical($n1, $result->end());
	}

	public function testAdd() {
		$n2 = new Node();
		$n1 = new Node(array('children' => array($n2)));
		$n3 = new Node();
		$n4 = new Node();
		$n1->insert($n3);
		$n1->insert($n4);

		$this->assertTrue($n1->hasChildren());
		$this->assertIdentical($n1, $n2->parent());
		$this->assertIdentical($n1, $n3->parent());
		$this->assertIdentical($n1, $n3->parent());
		$this->assertIdentical($n2, $n1->first());
		$this->assertIdentical($n3, $n1->next());
		$this->assertIdentical($n4, $n1->next());
	}

	public function testAddBeforeAndAfter() {
		$n1 = new Node();
		$n2 = new Node();
		$n3 = new Node();
		$n4 = new Node();
		$n5 = new Node();
		$n6 = new Node();
		$n1->insert($n2);
		$this->assertEqual(0, $n1->indexOf($n2));
		$this->assertIdentical($n3, $n1->insert($n3, 'after', $n2));
		$this->assertEqual(0, $n1->indexOf($n2));
		$this->assertEqual(1, $n1->indexOf($n3));

		$this->assertIdentical($n4, $n1->insert($n4, 'before', $n2));
		$this->assertEqual(0, $n1->indexOf($n4));
		$this->assertEqual(1, $n1->indexOf($n2));
		$this->assertEqual(2, $n1->indexOf($n3));

		$this->assertIdentical($n5, $n1->insert($n5, 'before', $n3));
		$this->assertEqual(0, $n1->indexOf($n4));
		$this->assertEqual(1, $n1->indexOf($n2));
		$this->assertEqual(2, $n1->indexOf($n5));
		$this->assertEqual(3, $n1->indexOf($n3));

		$this->assertIdentical($n6, $n1->insert($n6, 'after', $n3));
		$this->assertEqual(0, $n1->indexOf($n4));
		$this->assertEqual(1, $n1->indexOf($n2));
		$this->assertEqual(2, $n1->indexOf($n5));
		$this->assertEqual(3, $n1->indexOf($n3));
		$this->assertEqual(4, $n1->indexOf($n6));
	}

	public function testInsert() {
		$n1 = new Node();
		$n2 = new Node();
		$n3 = new Node();
		$n4 = new Node();

		$n1->insert($n2);
		$this->assertEqual(0, $n1->indexOf($n2));

		$n1->insert($n3, 'before', $n2);
		$this->assertEqual(0, $n1->indexOf($n3));
		$this->assertEqual(1, $n1->indexOf($n2));

		$n1->insert($n4, 'after', $n3);
		$this->assertEqual(0, $n1->indexOf($n3));
		$this->assertEqual(1, $n1->indexOf($n4));
		$this->assertEqual(2, $n1->indexOf($n2));
	}

	public function testInject() {
		$n1 = new Node();
		$n2 = new Node();
		$n3 = new Node();
		$n4 = new Node();

		$n2->inject($n1);
		$this->assertEqual(0, $n1->indexOf($n2));

		$n3->inject($n1, 'before', $n2);
		$this->assertEqual(0, $n1->indexOf($n3));
		$this->assertEqual(1, $n1->indexOf($n2));

		$n4->inject($n1, 'after', $n3);
		$this->assertEqual(0, $n1->indexOf($n3));
		$this->assertEqual(1, $n1->indexOf($n4));
		$this->assertEqual(2, $n1->indexOf($n2));
	}

	public function testRemove() {
		$n2 = new Node();
		$n3 = new Node();
		$n4 = new Node();
		$n5 = new Node();
		$n1 = new Node(array('children' => array($n2, $n3, $n4, $n5)));

		$this->assertIdentical($n2, $n1->remove(0));
		$this->assertFalse($n1->indexOf($n2));
		$this->assertNull($n2->parent());
		$this->assertIdentical($n5, $n1->remove());
		$this->assertFalse($n1->indexOf($n5));
		$this->assertNull($n5->parent());
		$this->assertIdentical($n4, $n1->remove(1));
		$this->assertFalse($n1->indexOf($n4));
		$this->assertNull($n4->parent());
		$this->assertIdentical($n3, $n1->remove($n3));
		$this->assertFalse($n1->indexOf($n3));
		$this->assertNull($n3->parent());

		$n1 = new Node(array('children' => array($n2, $n3, $n4, $n5)));
		$n1->removeAll();
		$this->assertFalse($n1->hasChildren());
		$this->assertFalse($n1->indexOf($n2));
		$this->assertNull($n2->parent());
	}

	public function testFirstAndLast() {
		$n2 = new Node();
		$n3 = new Node();
		$n4 = new Node();
		$n5 = new Node();
		$n1 = new Node(array('children' => array($n2, $n3, $n4)));
		$this->assertIdentical($n2, $n1->first());
		$this->assertIdentical($n4, $n1->last());
		$this->assertIdentical($n4, $n1->last());
		$this->assertIdentical($n2, $n1->first(function($item) use($n2){
			return $item === $n2;
		}));
		$this->assertIdentical($n2, $n1->last(function($item) use($n2){
			return $item === $n2;
		}));
		$this->assertIdentical($n3, $n1->first(function($item) use($n3){
			return $item === $n3;
		}));
		$this->assertIdentical($n3, $n1->last(function($item) use($n3){
			return $item === $n3;
		}));
	}

	public function testIndex() {
		$n1 = new Node();
		$n2 = new Node();
		$n3 = new Node();
		$n4 = new Node();
		$n1->insert($n2);
		$n1->insert($n3);
		$n1->insert($n4);
		$this->assertEqual(0, $n1->indexOf($n2));
		$this->assertEqual(1, $n1->indexOf($n3));
		$this->assertEqual(2, $n1->indexOf($n4));

		$n1->remove(1);
		$this->assertEqual(0, $n1->indexOf($n2));
		$this->assertFalse($n1->indexOf($n3));
		$this->assertEqual(1, $n1->indexOf($n4));

		$n1->remove(0);
		$this->assertFalse($n1->indexOf($n2));
		$this->assertEqual(0, $n1->indexOf($n4));
	}

	public function testNth() {
		$n2 = new Node();
		$n3 = new Node();
		$n4 = new Node();
		$n5 = new Node();
		$n1 = new Node(array('children' => array($n2, $n3, $n4, $n5)));

		$this->assertFalse($n1->nth(0));
		$this->assertIdentical($n2, $n1->nth(1));
		$this->assertIdentical($n3, $n1->nth(2));
		$this->assertIdentical($n4, $n1->nth(3));
		$this->assertIdentical($n5, $n1->nth(4));

		$this->assertIdentical($n2, $n1->nth(-4));
		$this->assertIdentical($n3, $n1->nth(-3));
		$this->assertIdentical($n4, $n1->nth(-2));
		$this->assertIdentical($n5, $n1->nth(-1));
	}

	public function testArrayAccess() {
		$n1 = new Node();
		$n2 = new Node();
		$n3 = new Node();
		$n4 = new Node();
		$n1[] = $n2;
		$n1[] = $n3;
		$n1[] = $n4;
		foreach ($n1 as $i => $child) {
			$this->assertIdentical($n1[$i], $child);
		}
	}
}

?>