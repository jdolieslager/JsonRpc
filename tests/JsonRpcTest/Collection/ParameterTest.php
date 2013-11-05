<?php
namespace JsonRpcTest\Collection;

use Jdolieslager\JsonRpc\Collection\Parameter;
use Jdolieslager\JsonRpc\Entity\Parameter as EntityParameter;

class ParameterTest extends \PHPUnit_Framework_TestCase
{
	public function testCollection()
	{
		$collection = new Parameter();
		$entity     = new EntityParameter();
		$entity->setName('test');
		
		$collection->offsetSet('test', $entity);
		
		$this->assertInstanceOf('\Jdolieslager\JsonRpc\Collection\Parameter', $collection);
		$this->assertInstanceOf(
			'Jdolieslager\JsonRpc\Entity\Parameter',
			$collection->offsetGet('test')
		);
		
		$copy = $collection->getArrayCopy();
		$this->assertInternalType('array', $copy);
		$this->assertArrayHasKey('test', $copy);
		$this->assertArrayHasKey('name', $copy['test']);
		$this->assertEquals('test', $copy['test']['name']);
	}
}