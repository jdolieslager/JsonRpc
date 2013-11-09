<?php
namespace JsonRpcTest\Exception;

use Jdolieslager\JsonRpc\Exception\InvalidResponse;

/**
 * InvalidResponse test case.
 */
class InvalidResponseTest extends \PHPUnit_Framework_TestCase 
{
	/**
	 *
	 * @var InvalidResponse
	 */
	private $invalidResponse;
	
	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp() 
	{
		parent::setUp ();
		$this->invalidResponse = new InvalidResponse('Unittest', 1, new \Exception());
	}
	
	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() 
	{
		$this->invalidResponse = null;
		parent::tearDown ();
	}
	
	public function testException()
	{
		$this->assertInstanceOf('Jdolieslager\\JsonRpc\\Exception\\ExceptionInterface', $this->invalidResponse);
		$this->assertEquals('Unittest', $this->invalidResponse->getMessage());
		$this->assertEquals(1, $this->invalidResponse->getCode());
		$this->assertInstanceOf('Exception', $this->invalidResponse->getPrevious());
	}
}

