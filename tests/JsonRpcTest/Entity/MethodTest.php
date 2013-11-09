<?php
namespace JsonRpcTest\Entity;

use Jdolieslager\JsonRpc\Entity\Method;
use Jdolieslager\JsonRpc\Entity\Parameter;

class MethodTest extends \PHPUnit_Framework_TestCase
{
	public function testGettersAndSetters()
	{
		$entity    = new Method();
		$parameter = new Parameter();
		$parameter->setIndex(0);
		
		// Check defaults
		$this->assertNull($entity->getName());
		$this->assertInstanceOf(
			'Jdolieslager\JsonRpc\Collection\Parameter', 
			$entity->getParameters()
		);
		$this->assertEquals(0, $entity->getParameters()->count());
		
		$this->assertInstanceOf('Jdolieslager\JsonRpc\Entity\Method', $entity->setName('suite'));
		$this->assertInstanceOf('Jdolieslager\JsonRpc\Entity\Method', $entity->addParameter($parameter));
		
		$this->assertEquals('suite', $entity->getName());
		$this->assertEquals(1, $entity->getParameters()->count());
		$this->assertInstanceOf('Jdolieslager\JsonRpc\Entity\Parameter', $entity->getParameters()->offsetGet(0));
	}	
}
