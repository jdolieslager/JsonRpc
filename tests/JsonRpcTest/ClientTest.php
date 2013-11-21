<?php
namespace JsonRpcTest;

use Jdolieslager\JsonRpc\Entity\Request;
use Jdolieslager\JsonRpc\Client;

require_once DATA_ROOT . 'ClientProtocolLayer.php';
require_once DATA_ROOT . 'RequestFailure.php';

/**
 * Client test case.
 */
class ClientTest extends \PHPUnit_Framework_TestCase
{
	protected $clientClass      	   = 'Jdolieslager\\JsonRpc\\Client';
	protected $responseClass    	   = 'Jdolieslager\\JsonRpc\\Entity\\Response';
	protected $responseCollectionClass = 'Jdolieslager\\JsonRpc\\Collection\\Response';
	protected $errorClass       	   = 'Jdolieslager\\JsonRpc\\Entity\\Error';
	protected $httpRequestClass 	   = 'Jdolieslager\\JsonRpc\\Request\\RequestInterface';
	protected $curlClass			   = 'Jdolieslager\\JsonRpc\\Request\\Curl';
	protected $paramClass			   = 'Jdolieslager\\JsonRpc\\Entity\\Parameter';
	
	public function testRequestObject()
	{
		$request = $this->dummyRequest();
		$params  = $request->getParams();
		
		$this->assertEquals(Request::VERSION_2, $request->getJsonrpc());
		$this->assertEquals(1, $request->getId());
		$this->assertEquals('dummy', $request->getMethod());
			
		// Test params
		$this->assertInstanceOf('\ArrayIterator', $params);
		$this->assertEquals(2, $params->count());
		$this->assertEquals(true, $params->offsetExists(0));
		$this->assertEquals('positional', $params->offsetGet(0));
		$this->assertEquals(true, $params->offsetExists('hello'));
		$this->assertEquals(false, $params->offsetExists('world'));
		$this->assertEquals('world', $params->offsetGet('hello'));
		
		// Test Param copy
		$copy = $params->getArrayCopy();
		$this->assertInternalType('array', $copy);
		$this->assertArrayHasKey('hello', $copy);
		$this->assertArrayNotHasKey('world', $copy);
		$this->assertEquals('world', $copy['hello']);
		
		// Test request copy
		$copy = $request->getArrayCopy();
		$this->assertInternalType('array', $copy);
		$this->assertArrayHasKey('jsonrpc', $copy);
		$this->assertArrayHasKey('method', $copy);
		$this->assertArrayHasKey('id', $copy);
		$this->assertArrayHasKey('params', $copy);
		
		$this->assertEquals(Request::VERSION_2, $copy['jsonrpc']);
		$this->assertEquals('dummy', $copy['method']);
		$this->assertEquals(1, $copy['id']);
		$this->assertInternalType('array', $copy['params']);
	}
	
	public function testSinglePositionalRequest()
	{
		$rawResponse = file_get_contents(DATA_ROOT . 'json/single_positional_response.json');
		$client 	 = $this->mockClient($rawResponse);
		$result 	 = $client->sendSingleRequest($this->dummyRequest());
		
		$this->assertInstanceOf($this->responseClass, $result);
		$this->assertEquals('2.0', $result->getJsonrpc());
		$this->assertEquals(19, $result->getResult());
		$this->assertEquals(1, $result->getId());
	}
	
	public function testSingleSimpleRequest()
	{
		$rawResponse = file_get_contents(DATA_ROOT . 'json/single_positional_response.json');
		$client 	 = $this->mockClient($rawResponse);
		$result 	 = $client->sendSimpleRequest('simple', array('hello'));
		
		$this->assertInstanceOf($this->responseClass, $result);
		$this->assertEquals('2.0', $result->getJsonrpc());
		$this->assertEquals(19, $result->getResult());
		$this->assertEquals(1, $result->getId());
	}
	
	public function testSingleNotificationRequest()
	{
		$client = $this->mockClient('', 204);
		$result = $client->sendNotification('notify', array('world'));
		
		$this->assertEquals(null, $result);
	}
	
	public function testSingleNoMethodRequest()
	{
		$rawResponse = file_get_contents(DATA_ROOT . 'json/single_no_method.json');
		$client 	 = $this->mockClient($rawResponse);
		$result 	 = $client->sendSingleRequest($this->dummyRequest());
		
		$this->assertInstanceOf($this->responseClass, $result);
		$this->assertEquals('2.0', $result->getJsonrpc());
		$this->assertEquals(1, $result->getId());
		
		// Validate the error
		$this->assertInstanceOf($this->errorClass, $result->getError());
		$this->assertEquals(true, $result->hasError());
		$this->assertInternalType('array', $result->getArrayCopy());	
		$this->assertEquals(Client::METHOD_NOT_FOUND, $result->getError()->getCode());
		$this->assertEquals('Method not found', $result->getError()->getMessage());
		$this->assertInternalType('array', $result->getError()->getArrayCopy());
		$this->assertEquals(null, $result->getError()->getData());
		$this->assertEmpty($result->getError()->getData());
	}
	
	public function testCallbackRequest()
	{
		$triggered = false;
		$callback  = function($response) use ($triggered) {
			$triggered = true;
		};
		
		// Initialize vars
		$rawResponse = file_get_contents(DATA_ROOT . 'json/single_positional_response.json');
		$collection  = $this->dummyCollectionRequest(0);
		$request 	 = $this->dummyRequest();
		$client 	 = $this->mockClient($rawResponse);
		 
		// Link request to collection
		$collection->addRequest($request, $callback);
		
		// Execute command
		$result = $client->sendRequest($collection);
		
		$this->assertInstanceOf($this->responseCollectionClass, $result);
	}
	
	public function testSendV1Request()
	{
		$rawResponse = file_get_contents(DATA_ROOT . 'json/single_positional_response.json');
		$request 	 = $this->dummyRequest();
		$client 	 = $this->mockClient($rawResponse);
	
		// set V1 flag
		$request->setJsonrpc(Request::VERSION_1);
		
		$result = $client->sendSingleRequest($request);
		
		$this->assertInstanceOf($this->responseClass, $result);
		$this->assertEquals(19, $result->getResult());
		$this->assertEquals(1, $result->getId());
	}
	
	public function testRequestEncodeFailure()
	{
		return;
		
// 		$this->setExpectedException('Jdolieslager\\JsonRpc\\Exception\\RuntimeException', 'Could not encode request data', 1);
		
		$request = new \RequestFailure();
		$request->setId(1);
		$request->setMethod('hello');
		
		$client = new Client('asdf');
		
// 		set_error_handler(function($err){ error_log(var_export(func_get_args(), true)); });
		$client->sendSingleRequest($request);
// 		restore_error_handler();
	}
	
	public function testGetHttpRequestObject()
	{
		$client     = new Client('http://localhost');
		$reflection = new \ReflectionClass($client);
		$method     = $reflection->getMethod('getHttpRequest');
		$method->setAccessible(true);
		
		$request = $method->invoke($client, 'rawPost');
		$this->assertInstanceOf($this->curlClass, $request);
	}
	
	public function testNotFoundRequest()
	{
		$this->setExpectedException('Jdolieslager\\JsonRpc\\Exception\\InvalidRequest', 'Host http://localhost not found', 2);
		
		$client = $this->mockClient('', 404);
		$result = $client->sendSimpleRequest('hello', array('world'));
	}
	
	public function testGetUrl()
	{
		$client = new Client('http://localhost');
		$this->assertEquals('http://localhost', $client->getUrl());
	}
	
	public function testSetUrl()
	{
		$client = new Client('http://localhost');
		$this->assertInstanceOf($this->clientClass, $client->setUrl('something.nl'));
	}
	
	public function testAddLayer()
	{
		$client = new Client('http://localhost');
		$client->addProtocolLayer(new \ClientProtocolLayer(), Client::LAYER_PLACEMENT_TOP);
	}
	
	public function testClientDecodeError()
	{
		$this->setExpectedException(
			'Jdolieslager\\JsonRpc\\Exception\\InvalidResponse', 
			'Client parse error', 
			Client::CLIENT_PARSE_ERROR
		);
		
		$client = $this->mockClient('unparseable');
		$result = $client->sendSimpleRequest('hello', array('world'));
	}
	
	public function testClientEmptyError()
	{
		$this->setExpectedException(
			'Jdolieslager\\JsonRpc\\Exception\\InvalidResponse',
			'Client parse error',
			Client::CLIENT_PARSE_ERROR
		);
		
		$client = $this->mockClient('');
		$result = $client->sendSimpleRequest('hello', array('world'));
	}
	
	public function testExpectedBatchResponse()
	{
		$this->setExpectedException(
			'Jdolieslager\\JsonRpc\\Exception\\InvalidResponse',
			'Client expected batch result',
			Client::CLIENT_EXPECTED_BATCH
		);
		
		$rawResponse = file_get_contents(DATA_ROOT . 'json/single_named_response.json');
		$client      = $this->mockClient($rawResponse);
		$result      = $client->sendRequest($this->dummyCollectionRequest(3));
	}
	
	public function testExpectedSingleResponse()
	{
		$this->setExpectedException(
			'Jdolieslager\\JsonRpc\\Exception\\InvalidResponse',
			'Client expected single result',
			Client::CLIENT_EXPECTED_SINGLE
		);
		
		$rawResponse = file_get_contents(DATA_ROOT . 'json/batch_request_response.json');
		$client      = $this->mockClient($rawResponse);
		$result      = $client->sendSingleRequest($this->dummyRequest());
	}
	
	
	/**
	 * Get single request
	 * 
	 * @return \Jdolieslager\JsonRpc\Entity\Request
	 */
	protected function dummyRequest($id = 1)
	{
		$request = new Request();
		$request->setJsonrpc(Request::VERSION_2);
		$request->setMethod('dummy');
		$request->setId($id);

		$request->addParam('positional');
		$request->addParam('world', 'hello');
		
		return $request;
	}
	
	/**
	 * Get collection of dummy requests
	 * 
	 * @param number $amount
	 * @return \Jdolieslager\JsonRpc\Collection\Request
	 */
	protected function dummyCollectionRequest($amount = 1)
	{
		$collection = new \Jdolieslager\JsonRpc\Collection\Request();
		
		for ($i = 1; $i <= $amount; ++$i) {
			$collection->append($this->dummyRequest($i));
		}
		
		return $collection;
	}
	

	/**
	 * Mock the Http Request
	 * 
	 * @param  string  $rawResponse
	 * @param  integer $httpCode
	 * @return \Jdolieslager\JsonRpc\Client
	 */
	protected function mockClient($rawResponse, $httpCode = 200)
	{
		// Mock objects
		$client = $this->getMock($this->clientClass, array('getHttpRequest'), array('http://localhost'));
		$http   = $this->getMock($this->httpRequestClass);
		
		// Mock getInfo
		$http->expects($this->once())
			->method('getInfo')
			->will($this->returnValue(array('http_code' => $httpCode)));
		
		// Mock execute
		$http->expects($this->once())
			->method('execute')
			->will($this->returnValue($rawResponse));
		
		// Mock close
		$http->expects($this->once())
			->method('close')
			->will($this->returnValue(null));
		
		$client->expects($this->once())
			->method('getHttpRequest')
			->will($this->returnValue($http));
		
		return $client;
	}
}
