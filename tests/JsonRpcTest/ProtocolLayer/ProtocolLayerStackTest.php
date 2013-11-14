<?php
namespace JsonRpcTest\ProtocolLayer;

use Jdolieslager\JsonRpc\ProtocolLayer\ProtocolLayerStack;

require_once DATA_ROOT . 'ClientProtocolLayer.php';

class ProtocolLayerStackTest extends \PHPUnit_Framework_TestCase
{
	public function	testTopPlacement()
	{
		$stack = new ProtocolLayerStack();
		
		$layerA = new \ClientProtocolLayer();
		$layerB = new \ClientProtocolLayer();
				
		$stack->addLayer($layerA, ProtocolLayerStack::PLACEMENT_TOP);
		$this->assertEquals(true, $layerA === $stack->getTopLayer());
		$this->assertEquals(true, $layerA === $stack->getBottomLayer());
		
		$stack->addLayer($layerB, ProtocolLayerStack::PLACEMENT_TOP);
		$this->assertEquals(true, $layerB === $stack->getTopLayer());
		$this->assertEquals(true, $layerA === $stack->getBottomLayer());
		
		$this->assertEquals(true, $layerA === $stack->getTopLayer()->getLowerLayer());
		$this->assertEquals(true, $layerB === $stack->getBottomLayer()->getUpperLayer());
	}
	
	public function testBottomPlacement()
	{
		$stack = new ProtocolLayerStack();
		
		$layerA = new \ClientProtocolLayer();
		$layerB = new \ClientProtocolLayer();
		
		$stack->addLayer($layerA, ProtocolLayerStack::PLACEMENT_BOTTOM);
		$this->assertEquals(true, $layerA === $stack->getTopLayer());
		$this->assertEquals(true, $layerA === $stack->getBottomLayer());
		
		$stack->addLayer($layerB, ProtocolLayerStack::PLACEMENT_BOTTOM);
		$this->assertEquals(true, $layerA === $stack->getTopLayer());
		$this->assertEquals(true, $layerB === $stack->getBottomLayer());
		
		$this->assertEquals(true, $layerA === $stack->getBottomLayer()->getUpperLayer());
		$this->assertEquals(true, $layerB === $stack->getTopLayer()->getLowerLayer());
	}
	
	public function testAbovePlacement()
	{
		$stack  = new ProtocolLayerStack();
		
		$layerA = new \ClientProtocolLayer();
		$layerB = new \ClientProtocolLayer();
		$layerC = new \ClientProtocolLayer();
		
		$stack->addLayer($layerA, ProtocolLayerStack::PLACEMENT_ABOVE);
		$stack->addLayer($layerB, ProtocolLayerStack::PLACEMENT_ABOVE, $layerA);
		$stack->addLayer($layerC, ProtocolLayerStack::PLACEMENT_ABOVE, $layerA);
		
		$this->assertEquals(true, $layerB === $stack->getTopLayer());
		$this->assertEquals(true, $layerA === $stack->getBottomLayer());
		
		$this->assertEquals(true, $layerC === $layerA->getUpperLayer());
		$this->assertEquals(null, $layerA->getLowerLayer());

		$this->assertEquals(true, $layerB === $layerC->getUpperLayer());
		$this->assertEquals(true, $layerA === $layerC->getLowerLayer());
		
		$this->assertEquals(true, $layerC === $layerB->getLowerLayer());
		$this->assertEquals(null, $layerB->getUpperLayer());
	}
	
	public function testBelowPlacement()
	{
		$stack  = new ProtocolLayerStack();
		
		$layerA = new \ClientProtocolLayer();
		$layerB = new \ClientProtocolLayer();
		$layerC = new \ClientProtocolLayer();
		
		$stack->addLayer($layerA, ProtocolLayerStack::PLACEMENT_BELOW);
		$stack->addLayer($layerB, ProtocolLayerStack::PLACEMENT_BELOW, $layerA);
		$stack->addLayer($layerC, ProtocolLayerStack::PLACEMENT_BELOW, $layerA);
		
		$this->assertEquals(true, $layerB === $stack->getBottomLayer());
		$this->assertEquals(true, $layerA === $stack->getTopLayer());
		
		$this->assertEquals(true, $layerC === $layerA->getLowerLayer());
		$this->assertEquals(null, $layerA->getUpperLayer());

		$this->assertEquals(true, $layerB === $layerC->getLowerLayer());
		$this->assertEquals(true, $layerA === $layerC->getUpperLayer());
		
		$this->assertEquals(true, $layerC === $layerB->getUpperLayer());
		$this->assertEquals(null, $layerB->getLowerLayer());
	}
	
	public function testInvalidPositionPlacement()
	{
		$this->setExpectedException('Jdolieslager\\JsonRpc\\Exception\\InvalidArgument', '', 1);
		
		$stack = new ProtocolLayerStack();
		$layer = new \ClientProtocolLayer();
		
		$stack->addLayer($layer, 'placement_that_not_exitsts');
		$stack->addLayer($layer, 'placement_that_not_exitsts');
	}
	
	public function testHandleResponse()
	{
		$stack  = new ProtocolLayerStack();
		$layerA = new \ClientProtocolLayer();
		$layerB = new \ClientProtocolLayer();
		
		$stack->addLayer($layerA, ProtocolLayerStack::PLACEMENT_TOP);
		$stack->addLayer($layerB, ProtocolLayerStack::PLACEMENT_TOP);
		
		$resultA = $stack->handleResponse('world');
		$resultB = $stack->handleResponse('something');
		
		$this->assertEquals('hello', $resultA);
		$this->assertEquals('something', $resultB);
	}
	
	public function testHandleRequest()
	{
		$stack  = new ProtocolLayerStack();
		$layerA = new \ClientProtocolLayer();
		$layerB = new \ClientProtocolLayer();
		
		$stack->addLayer($layerA, ProtocolLayerStack::PLACEMENT_TOP);
		$stack->addLayer($layerB, ProtocolLayerStack::PLACEMENT_TOP);
		
		$resultA = $stack->handleRequest('hello');
		$resultB = $stack->handleRequest('something');
		
		$this->assertEquals('world', $resultA);
		$this->assertEquals('something', $resultB);
	}
}
