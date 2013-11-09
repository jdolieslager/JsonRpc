<?php
namespace JsonRpcTest\Exception;

use Jdolieslager\JsonRpc\Exception\InvalidArgument;

/**
 * InvalidArgument test case.
 */
class InvalidArgumentTest extends \PHPUnit_Framework_TestCase 
{
	
	/**
	 *
	 * @var InvalidArgument
	 */
	private $invalidArgument;
	
	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp() 
	{
		parent::setUp ();
		
		$this->invalidArgument = new InvalidArgument('Unittest', 1, new \Exception());
	}
	
	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() 
	{
		$this->invalidArgument = null;
		parent::tearDown ();
	}
	
	public function testInvalidArgument()
	{
		$this->assertInstanceOf('Jdolieslager\\JsonRpc\\Exception\\ExceptionInterface', $this->invalidArgument);
		$this->assertEquals('Unittest', $this->invalidArgument->getMessage());
		$this->assertEquals(1, $this->invalidArgument->getCode());
		$this->assertInstanceOf('Exception', $this->invalidArgument->getPrevious());	
	}
}
