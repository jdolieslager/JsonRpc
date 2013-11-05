<?php
namespace JsonRpcTest\Entity;

use Jdolieslager\JsonRpc\Entity\Error;

class ErrorTest extends \PHPUnit_Framework_TestCase
{
	public function testGettersAndSetters()
	{
		$entity = new Error();
		
		// Check default values
		$this->assertNull($entity->getCode());
		$this->assertNull($entity->getData());
		$this->assertNull($entity->getMessage());
		
		// Set values and returns same object
		$this->assertInstanceOf('Jdolieslager\JsonRpc\Entity\Error', $entity->setCode(1));
		$this->assertInstanceOf('Jdolieslager\JsonRpc\Entity\Error', $entity->setMessage(2));
		$this->assertInstanceOf('Jdolieslager\JsonRpc\Entity\Error', $entity->setData(3));
		
		// Check getters
		$this->assertEquals(1, $entity->getCode());
		$this->assertEquals(2, $entity->getMessage());
		$this->assertEquals(3, $entity->getData());
		
		//copy
		$copy = $entity->getArrayCopy();
		
		// Check result
		$this->assertInternalType('array', $copy);
		$this->assertCount(3, $copy);
		
		// Check copy data
		$this->assertArrayHasKey('code', $copy);
		$this->assertArrayHasKey('message', $copy);
		$this->assertArrayHasKey('data', $copy);
		
		$this->assertEquals(1, $copy['code']);
		$this->assertEquals(2, $copy['message']);
		$this->assertEquals(3, $copy['data']);
	}
}
