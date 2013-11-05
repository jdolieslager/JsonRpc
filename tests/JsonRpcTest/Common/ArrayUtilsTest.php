<?php
namespace JsonRpcTest\Common;

use Jdolieslager\JsonRpc\Common\ArrayUtils;

class ArrayUtilsTest extends \PHPUnit_Framework_TestCase
{
	public function testIsNumeric()
	{
		$result = ArrayUtils::isNumeric(array('a', 'b'));
		$this->assertEquals(true, $result);
		
		$result = ArrayUtils::isNumeric(array('a' => 'b'));
		$this->assertEquals(false, $result);
	}	

	public function testIsAssociative()
	{
		$result = ArrayUtils::isAssociative(array('a' => 'b'));
		$this->assertEquals(true, $result);
		
		$result = ArrayUtils::isAssociative(array('a', 'b'));
		$this->assertEquals(false, $result);
	}
	
	public function testArrayTarget()
	{
		$testArray = array('suite' => array('test' => true, 'live' => false));
		
		$result = ArrayUtils::arrayTarget('suite', $testArray, false);
		$this->assertInternalType('array', $result);
		
		$result = ArrayUtils::arrayTarget('suite.live', $testArray, null);
		$this->assertEquals(null, $result);
		
		$result = ArrayUtils::arrayTarget('suite.non', $testArray, false);
		$this->assertEquals(false, $result);
		
		$result = ArrayUtils::arrayTarget('suite.test.live', $result, 'nothing');
		$this->assertEquals('nothing', $result);
	}
}
