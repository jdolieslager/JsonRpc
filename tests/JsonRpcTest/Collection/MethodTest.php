<?php
namespace JsonRpcTest\Collection;

use Jdolieslager\JsonRpc\Collection\Method;

class MethodTest extends \PHPUnit_Framework_TestCase
{
	public function testCollection()
	{
		$collection = new Method();
		$collection->offsetSet('test', true);
		
		$this->assertInstanceOf('\Jdolieslager\JsonRpc\Collection\Method', $collection);
		$this->assertEquals(true, $collection->offsetGet('test'));
	}
}