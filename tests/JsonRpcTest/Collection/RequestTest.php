<?php
namespace JsonRpcTest\Collection;

use Jdolieslager\JsonRpc\Collection\Request;
use Jdolieslager\JsonRpc\Entity\Request as EntityRequest;
use Jdolieslager\JsonRpc\Entity\Response;

class RequestTest extends \PHPUnit_Framework_TestCase
{
	public function testCollection()
	{
		$collection = new Request();
		$entity     = new EntityRequest();
		$entity->setId(1);
		
		$collection->addRequest($entity);
		
		$this->assertInstanceOf('\Jdolieslager\JsonRpc\Collection\Request', $collection);
		$this->assertInstanceOf(
			'Jdolieslager\JsonRpc\Entity\Request',
			$collection->offsetGet(0)
		);
		
		$copy = $collection->getArrayCopy();
		$this->assertInternalType('array', $copy);
		$this->assertArrayHasKey(0, $copy);
		$this->assertArrayHasKey('id', $copy[0]);
		$this->assertEquals(1, $copy[0]['id']);
	}
	
	public function testBatch()
	{
		$collection = new Request();
		$this->assertEquals(false, $collection->isBatch());
		
		$entity = new EntityRequest();
		$collection->append($entity);
		
		$this->assertEquals(false, $collection->isBatch());
		
		$entity = new EntityRequest();
		$collection->offsetSet('will_not_be_used', $entity);
		
		$this->assertEquals(true, $collection->isBatch());
	}
	
	public function testNotNeededCallable()
	{
		$this->setExpectedException('\Jdolieslager\JsonRpc\Exception\InvalidArgument');
		
		$collection = new Request();
		$entity = new EntityRequest();
		$collection->addRequest($entity, function() {});
	}
	
	public function testHasIdCallback()
	{
		$collection = new Request();
		$entityA = new EntityRequest();
		$entityA->setId(1);

		$collection->addRequest($entityA, function(){});
		
		$this->assertEquals(false, $collection->hasIdCallback(2));
		$this->assertEquals(true, $collection->hasIdCallback(1));
		
		$entityB = new EntityRequest();
		$entityB->setId(2);
		
		$collection->addRequest($entityB, function(){});
		
		$this->assertEquals(true, $collection->hasIdCallback(2));
		$this->assertEquals(true, $collection->hasIdCallback(1));
	}
	
	public function testDuplicateId()
	{
		$this->setExpectedException('\Jdolieslager\JsonRpc\Exception\RuntimeException');
		
		$collection = new Request();
		$entityA = new EntityRequest();
		$entityA->setId(1);
		
		$collection->addRequest($entityA);
		
		$entityB = new EntityRequest();
		$entityB->setId(1);
		
		$collection->addRequest($entityB);
	}
	
	public function testPerformCallback()
	{
		$collection = new Request();
		$entityA = new EntityRequest();
		$entityA->setId(1);
		
		$response = new Response();
		$response->setId(1);
		
		$self = $this;
		
		$collection->addRequest($entityA, function($data) use ($self, $response) {
			$self->assertInstanceOf('Jdolieslager\JsonRpc\Entity\Response', $data);
			$self->assertEquals(true, $data === $response);
		});
		
		$collection->performCallback($response);
	}
	
	public function testPerformCallbackFailure()
	{
		$this->setExpectedException('\Jdolieslager\JsonRpc\Exception\InvalidArgument');
		
		$collection = new Request();
		
		$response = new Response();
		$response->setId(1);
		
		$collection->performCallback($response);
	}
}