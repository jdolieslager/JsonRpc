<?php
namespace JsonRpcTest;

use Jdolieslager\JsonRpc\HandlerReflectionMaker;

require_once __DIR__ . '/../data/Demo.php';

/**
 * HandlerReflectionMaker test case.
 */
class HandlerReflectionMakerTest extends\ PHPUnit_Framework_TestCase 
{
	/**
	 *
	 * @var HandlerReflectionMaker
	 */
	private $handlerReflectionMaker;
	
	private $invalidArgument     = 'Jdolieslager\\JsonRpc\\Exception\\InvalidArgument';
	private $methodCollection    = 'Jdolieslager\\JsonRpc\\Collection\\Method';
	private $methodEntity        = 'Jdolieslager\\JsonRpc\\Entity\\Method';
	private $parameterCollection = 'Jdolieslager\\JsonRpc\\Collection\\Parameter';
	private $parameterEntity     = 'Jdolieslager\\JsonRpc\\Entity\\Parameter';
	
	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp() 
	{
		parent::setUp ();
		$this->handlerReflectionMaker = new HandlerReflectionMaker();
	}
	
	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() 
	{
		$this->handlerReflectionMaker = null;
		parent::tearDown ();
	}
	
	/**
	 * Tests HandlerReflectionMaker->reflect()
	 */
	public function testNoClassReflect() 
	{
		$this->setExpectedException($this->invalidArgument);
		$this->handlerReflectionMaker->reflect('non_existing_class');
	}
	
	public function testReflect()
	{
		$results = $this->handlerReflectionMaker->reflect('Demo');
		$this->assertInstanceOf($this->methodCollection, $results);
		
		//  Test runtime cache
		$runtime = $this->handlerReflectionMaker->reflect('Demo');
		$this->assertEquals(true, $results === $runtime);
		
		// Check if the method count is correct
		$this->assertEquals(6, $results->count());
		
		foreach ($results as $result) {
			$this->assertInstanceOf($this->methodEntity, $result);
			$this->assertNotEquals('protectedNotReflected', $result->getName());
			$this->assertNotEquals('privateNotReflected', $result->getName());
			$this->assertInstanceOf($this->parameterCollection, $result->getParameters());
			
			$parameters = $result->getParameters();
			
			switch ($result->getName()) {
				case 'noArgument':
					$this->assertEquals(0, $parameters->count());
					break;
				case 'oneArgument':
					$this->assertEquals(1, $parameters->count());
					$this->assertEquals(true, $parameters->offsetExists(0));
					$this->assertInstanceOf($this->parameterEntity, $parameters->offsetGet(0));
					$this->assertEquals(0, $parameters->offsetGet(0)->getIndex());
					$this->assertEquals('argument1', $parameters->offsetGet(0)->getName());
					$this->assertEquals(true, $parameters->offsetGet(0)->getRequired());					
					break;
				case 'oneArgumentOptional':
					$this->assertEquals(1, $parameters->count());
					$this->assertEquals(true, $parameters->offsetExists(0));
					$this->assertInstanceOf($this->parameterEntity, $parameters->offsetGet(0));
					$this->assertEquals(0, $parameters->offsetGet(0)->getIndex());
					$this->assertEquals('argument1', $parameters->offsetGet(0)->getName());
					$this->assertEquals(false, $parameters->offsetGet(0)->getRequired());
					$this->assertEquals('optional', $parameters->offsetGet(0)->getDefault());
					break;
				case 'twoArgument':
					$this->assertEquals(2, $parameters->count());
					break;
				case 'twoArgumentOneOptional':
					$this->assertEquals(2, $parameters->count());
					break;
				case 'twoArgumentTwoOptional':
					$this->assertEquals(2, $parameters->count());
					break;
				default:
					$this->throwException(new \Exception('Method ' . $result->getName() . ' is not tested!'));
					break;
			}
		}
	}
}

