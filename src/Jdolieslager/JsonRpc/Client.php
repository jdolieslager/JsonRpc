<?php
namespace Jdolieslager\JsonRpc;

use Jdolieslager\JsonRpc\ProtocolLayer\ProtocolLayerStack;

/**
 * @category    Jdolieslager
 * @package     JsonRpc
 */
class Client
{
    const VERSION_1 = null;
    const VERSION_2 = '2.0';

    /**
     * Server Error constants
     */
    const PARSE_ERROR      = -32700;
    const INVALID_REQUEST  = -32600;
    const METHOD_NOT_FOUND = -32601;
    const INVALID_PARAMS   = -32602;
    const INTERNAL_ERROR   = -32603;

    /**
     * Client Error constants
     */
    const CLIENT_PARSE_ERROR     = -22700;
    const CLIENT_HOST_NOT_FOUND  = -22600;
    const CLIENT_EXPECTED_BATCH  = -22601;
    const CLIENT_EXPECTED_SINGLE = -22602;

    /**
     * Protocol stack layer placement constants
     */
    const LAYER_PLACEMENT_TOP    = ProtocolLayerStack::PLACEMENT_TOP;
    const LAYER_PLACEMENT_BOTTOM = ProtocolLayerStack::PLACEMENT_BOTTOM;
    const LAYER_PLACEMENT_BELOW  = ProtocolLayerStack::PLACEMENT_BELOW;
    const LAYER_PLACEMENT_ABOVE  = ProtocolLayerStack::PLACEMENT_ABOVE;

    /**
     * @var string
     */
    protected $url;

    /**
     * Holds additional layers for the JSON RPC communication
     *
     * @var ProtocolLayer\ProtocolLayerStack
     */
    protected $protocolLayerStack;

    /**
     * Holds the current protocal layer
     *
     * @var mixed
     */
    protected $currentProtocolLayer;

    /**
     * Add a layer to the stack
     *
     * @param ProtocolLayer\ProtocolLayerInterface $layer
     * @param constant $placement   (self::LAYER_PLACEMENT_*)
     * @return Server
     */
    public function addProtocolLayer(ProtocolLayer\ProtocolLayerInterface $layer, $placement)
    {
        $this->protocolLayerStack->addLayer($layer, $placement, $this->currentProtocolLayer);
        $this->currentProtocolLayer = $layer;

        return $this;
    }

    /**
     * The constructor for the JSON RPC Client
     *
     * @param string $url
     */
    public function __construct($url)
    {
        $this->setUrl($url);
        $this->protocolLayerStack = new ProtocolLayerStack();
    }

    /**
     * The url to the JSON RPC Server
     *
     * @param string $url
     * @return Client
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Send a single request to the server
     *
     * @param  Entity\Request $request
     * @return Entity\Response
     */
    public function sendSingleRequest(Entity\Request $request)
    {
        // Create collection
        $collection = new Collection\Request();
        $collection->addRequest($request);

        // Send the collection
        $result = $this->sendRequest($collection);

        // Return the single result
        return $result->current();
    }

    /**
     * Send simple request to the server.
     *
     * @param  string $method
     * @param  array $params
     * @return Entity\Response
     */
    public function sendSimpleRequest($method, array $params = array())
    {
        $request = new Entity\Request();
        $request->setId(1);
        $request->setJsonrpc(self::VERSION_2);
        $request->setMethod($method);

        foreach ($params as $index => $value) {
            $request->addParam($value, $index);
        }

        return $this->sendSingleRequest($request);
    }

    /**
     * Send notification. This is a fire and forget action. No response given
     *
     * @param string $method
     * @param array $params
     * @return null
     */
    public function sendNotification($method, array $params = array())
    {
        $request = new Entity\Request();
        $request->setId(null);
        $request->setJsonrpc(self::VERSION_2);
        $request->setMethod($method);

        foreach ($params as $index => $value) {
            $request->addParam($index, $value);
        }

        return null;
    }

    /**
     * Send the request(s)
     *
     * @param  Collection\Request $request
     * @return Collection\Response
     */
    public function sendRequest(Collection\Request $request)
    {
        $rawPost = $this->prepareRequest($request);

        // First go through the layer stack
        $rawPost = $this->protocolLayerStack->handleRequest($rawPost);

        $ch = curl_init($this->getUrl());
        curl_setopt_array($ch, array(
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $rawPost,
            CURLOPT_USERAGENT      => 'Jdolieslager JsonRpc Client',
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true
        ));

        $result = curl_exec($ch);
        $info   = curl_getinfo($ch);

        curl_close($ch);

        if ($info['http_code'] === 404) {
            throw new Exception\InvalidRequest(
                'Host ' . $this->getUrl() . ' not found',
                2
            );
        }

        return $this->parseResponse($request, $result, $info);
    }

    /**
     * Parse the result given by the JSON RPC Server
     *
     * @param Collection\Request $request
     * @param mixed $result
     * @param mixed $info
     * @return Collection\Response
     */
    protected function parseResponse(Collection\Request $request, $result, $info)
    {
        if (
            empty($result) &&
            $request->isBatch() === false &&
            $request->current()->getId() !== null
        ) {
            throw new Exception\InvalidResponse(
                'Client parse error',
                static::CLIENT_PARSE_ERROR
            );
        }

        var_dump($result);

        // First go through the layer stack
        $result = $this->protocolLayerStack->handleResponse($result);

        var_dump($result);
        exit;

        $json = json_decode($result, true);
        if (is_array($json) === false) {
            throw new Exception\InvalidResponse(
                'Client parse error',
                static::CLIENT_PARSE_ERROR
            );
        }

        // Check varaibles
        $hasIdKey = array_key_exists('id', $json);
        $isBatch  = $request->isBatch();

        // Check if we have a batch result
        if ($isBatch === true && $hasIdKey === true) {
            throw new Exception\InvalidResponse(
                'Client expected batch result',
                static::CLIENT_EXPECTED_BATCH
            );
            // check if we have single result
        } else if ($isBatch === false && $hasIdKey === false) {
            throw new Exception\InvalidResponse(
                'Client expected single result',
                static::CLIENT_EXPECTED_SINGLE
            );
        }

        // No batch, so we make it one
        if ($isBatch === false) {
            $json = array($json);
        }

        // Create collection response
        $responses = new Collection\Response();

        // Loop through all the responses
        foreach ($json as $rawResponse) {
            $response = new Entity\Response();

            // Fetch the items from the response array
            $id      = $this->getArrayItem('id', $rawResponse);
            $jsonrpc = $this->getArrayItem('jsonrpc', $rawResponse);
            $result  = $this->getArrayItem('result', $rawResponse, null);
            $error   = $this->getArrayItem('error', $rawResponse, null);

            // Set general data
            $response->setId($id)->setJsonrpc($jsonrpc);

            // Determine which item to set
            if ($error === null) {
                $response->setResult($result);
            } else {
                // hydrate error information
                $errorObject = new Entity\Error();
                $errorObject->setCode($this->getArrayItem('code', $error));
                $errorObject->setData($this->getArrayItem('data', $error));
                $errorObject->setMessage($this->getArrayItem('message', $error));

                $response->setError($errorObject);
            }

            // When callback has been registered use this
            if ($request->hasIdCallback($response->getId())) {
                $request->performCallback($response);
            }

            // append the response to the collection
            $responses->append($response);
        }

        // return the collection
        return $responses;
    }

    /**
     * Prepare the request for sending
     *
     * @param Collection\Request $request
     * @return string A JSON encoded JSON RPC string
     */
    protected function prepareRequest(Collection\Request $request)
    {
        $data = $request->getArrayCopy();
        foreach ($data as $index => $value) {
            if ($value['jsonrpc'] === null) {
                unset($data[$index]['jsonrpc']);
            }

            if (empty($value['params'])) {
                unset($data[$index]['params']);
            }

            if ($value['id'] === null) {
                unset($data[$index]['id']);
            }
        }

        // On single request we do not send in batch mode
        if ($request->isBatch() === false) {
            $data = array_shift($data);
        }

        $json = json_encode($data);
        if ($json === false) {
            throw new Exception\RuntimeException(
                'Could not encode request data',
                1
            );
        }

        return $json;
    }

    /**
     * Get softly an item from an array
     *
     * @param  string $needle
     * @param  array  $haystack
     * @param  mixed  $default
     * @return mixed
     */
    protected function getArrayItem($needle, $haystack, $default = null)
    {
        if (array_key_exists($needle, $haystack)) {
            return $haystack[$needle];
        }

        return $default;
    }
}
