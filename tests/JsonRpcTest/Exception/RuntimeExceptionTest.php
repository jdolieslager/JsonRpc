<?php
namespace JsonRpcTest\Exception;

use Jdolieslager\JsonRpc\Exception\RuntimeException;

/**
 * RuntimeException test case.
 */
class RuntimeExceptionTest extends \PHPUnit_Framework_TestCase
{
	/**
	 *
	 * @var RuntimeException
	 */
	private $runtimeException;
	
	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp() 
	{
		parent::setUp ();
		$this->runtimeException = new RuntimeException('Unittest', 1, new \Exception());
	}
	
	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() 
	{
		$this->runtimeException = null;
		parent::tearDown ();
	}

	public function testException()
	{
		$this->assertInstanceOf('Jdolieslager\\JsonRpc\\Exception\\ExceptionInterface', $this->runtimeException);
		$this->assertEquals('Unittest', $this->runtimeException->getMessage());
		$this->assertEquals(1, $this->runtimeException->getCode());
		$this->assertInstanceOf('Exception', $this->runtimeException->getPrevious());
	}
}

