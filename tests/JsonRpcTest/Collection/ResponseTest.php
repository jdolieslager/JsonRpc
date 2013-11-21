<?php
namespace JsonRpcTest\Collection;

use Jdolieslager\JsonRpc\Collection\Response;
use Jdolieslager\JsonRpc\Entity\Response as EntityResponse;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
	public function testCollection()
	{
		$collection = new Response();
		$entity     = new EntityResponse();
		$entity->setId(1);
		
		$collection->offsetSet('test', $entity);
		
		$this->assertInstanceOf('\Jdolieslager\JsonRpc\Collection\Response', $collection);
		$this->assertInstanceOf(
			'Jdolieslager\JsonRpc\Entity\Response',
			$collection->offsetGet('test')
		);
		
		$copy = $collection->getArrayCopy();
		$this->assertInternalType('array', $copy);
		$this->assertArrayHasKey('test', $copy);
		$this->assertArrayHasKey('id', $copy['test']);
		$this->assertEquals(1, $copy['test']['id']);
	}
	
	public function testBatch()
	{
		$collection = new Response();
		$this->assertEquals(false, $collection->isBatch());
		
		$entity = new EntityResponse();
		$collection->append($entity);
		
		$this->assertEquals(false, $collection->isBatch());
		
		$entity = new EntityResponse();
		$collection->append($entity);
		
		$this->assertEquals(true, $collection->isBatch());
	}
}