<?php
namespace JsonRpcTest\Exception;

use Jdolieslager\JsonRpc\Exception\InvalidRequest;

/**
 * InvalidRequest test case.
 */
class InvalidRequestTest extends \PHPUnit_Framework_TestCase
{
	/**
	 *
	 * @var InvalidRequest
	 */
	private $invalidRequest;
	
	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp()
	{
		parent::setUp ();
		$this->invalidRequest = new InvalidRequest('Unittest', 1, new \Exception());
	}
	
	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() 
	{
		$this->invalidRequest = null;
		parent::tearDown ();
	}
	
	public function testException()
	{
		$this->assertInstanceOf('Jdolieslager\\JsonRpc\\Exception\\ExceptionInterface', $this->invalidRequest);
		$this->assertEquals('Unittest', $this->invalidRequest->getMessage());
		$this->assertEquals(1, $this->invalidRequest->getCode());
		$this->assertInstanceOf('Exception', $this->invalidRequest->getPrevious());		
	}
}
