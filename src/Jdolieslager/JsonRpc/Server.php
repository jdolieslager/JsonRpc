<?php
namespace Jdolieslager\JsonRpc;

/**
 * @category    Jdolieslager
 * @package     JsonRpc
 */
class Server
{
    /**
     * Error constants
     */
    const PARSE_ERROR      = -32700;
    const INVALID_REQUEST  = -32600;
    const METHOD_NOT_FOUND = -32601;
    const INVALID_PARAMS   = -32602;
    const INTERNAL_ERROR   = -32603;

    /**
     * This object will be called with the methods
     *
     * @var object
     */
    protected $handleObject;

    /**
     * Contains all the methods of the handle object
     *
     * @var Collection\Method
     */
    protected $methods;

    /**
     * Keep track if we've reflected the handle object
     *
     * @var boolean
     */
    protected $reflected = false;

    /**
     * @var boolean
     */
    protected $debugMode = false;

    /**
     * Construct the JSON RPC Server
     *
     * @param object  $handleObject  An Object that handles the request
     * @param boolean $debugMode
     * @return Server
     */
    public function __construct($handleObject, $debugMode = false)
    {
        $this->handleObject = $handleObject;
        $this->methods      = new Collection\Method();
        $this->debugMode    = $debugMode;
    }

    /**
     * Parse a raw json encoded string to a Response object
     *
     * @param string $string    A JSON encode RPC string
     * @return Entity\Response | NULL when id is NULL
     */
    public function getResponseForRawRequest($string)
    {
        // Create reflection
        $this->reflectHandleObject();

        try {
            // Always hace request object
            $request = new Entity\Request();

            // Decode the data
            $data = @json_decode($string, true);

            // No array means decoding failed
            if (is_array($data) === false) {
                throw new Exception\InvalidRequest('Parse error', static::PARSE_ERROR);
            }

            // Get the available arguments
            $jsonRpc = $this->getArrayItem('jsonrpc', $data, null);
            $method  = $this->getArrayItem('method', $data, null);
            $params  = $this->getArrayItem('params', $data, array());
            $id      = $this->getArrayItem('id', $data, null);

            // Method should be set and params should be an array when set
            if ($method === null || is_array($params) === false) {
                throw new Exception\InvalidRequest('Invalid Request', static::INVALID_REQUEST);
            }

            // Set the values on the request object
            $request->setJsonrpc($jsonRpc)->setId($id)->setMethod($method);

            // Set all the parameters
            foreach ($params as $offset => $value) {
                $request->addParam($value, $offset);
            }

            // Perform the request
            $response = $this->getResponseForRequest($request);
        } catch (\Exception $e) {
            // Error occured and create
            $response = $this->createErrorFromException($request, $e);
        }

        // Return the response
        return $response;
    }

    /**
     * Create an correct JSON response based on Response object
     *
     * @param  Entity\Response $response
     * @return string
     */
    public function responseToRaw(Entity\Response $response)
    {
        // Get the array
        $array = $response->getArrayCopy();

        // JSON RPC should be set for returning
        if ($response->getJsonrpc() === null) {
            unset($array['jsonrpc']);
        }

        // Check if we should return result or error
        if ($response->getError() instanceof Entity\Error) {
            unset($array['result']);
        } else {
            unset($array['error']);
        }

        // Encode the remaining array
        return json_encode($array);
    }

    /**
     * Handle the incoming request
     *
     * @param Entity\Request $request
     */
    public function getResponseForRequest(Entity\Request $request)
    {
        // Make reflection
        $this->reflectHandleObject();

        try {
            $response = $this->parseRequest($request);
        } catch (\Exception $e) {
            $response = $this->createErrorFromException($request, $e);
        }

        return $response;
    }

    /**
     * Parse the incoming request
     *
     * @param  Entity\Request $request
     * @return Entity\Response
     */
    protected function parseRequest(Entity\Request $request)
    {
        // normalize the name
        $methodName = strtolower($request->getMethod());

        // Check if the method exists
        if ($this->methods->offsetExists($methodName) === false) {
            throw new Exception\InvalidRequest('Method not found', static::METHOD_NOT_FOUND);
        }

        // Runtime variables
        $numericArray   = true;
        $firstIteration = true;

        // Loop through the request params for the type of arguments
        foreach ($request->getParams() as $offset => $param) {
            $isNumeric = ((int) $offset === $offset);

            // Associative and numeric cannot be used together
            if ($firstIteration === false && $isNumeric !== $numericArray) {
                throw new Exception\InvalidRequest('Invalid params', static::INVALID_PARAMS);
            }

            // Set the isNumeric flag
            $numericArray = $isNumeric;

            // We are not iterating the first anymore
            $firstIteration = false;
        }

        // List of arguments for the callable
        $arguments = array();

        // Get method information
        $method    = $this->methods->offsetGet($methodName);

        // Loop through method information
        foreach ($method->getParameters() as $parameter) {
            // decide which offset to use
            if ($numericArray === true) {
                $offset = $parameter->getIndex();
            } else {
                $offset = $parameter->getName();
            }

            // check if the offset exists
            $offsetExists = $request->getParams()->offsetExists($offset);

            // Check if the argument is required
            if ($parameter->getRequired() &&  $offsetExists === false) {
                throw new Exception\InvalidRequest('Invalid params', static::INVALID_PARAMS);
            }

            // Add value to the argument list
            $arguments[] = $offsetExists ?
                $request->getParams()->offsetGet($offset) :
                $parameter->getDefault();
        }

        // Perform action on the handle object
        $result = call_user_func_array(
            array($this->handleObject, $method->getName()),
            $arguments
        );

        // NULL means no response output
        if ($request->getId() === null) {
            return null;
        }

        // Create response object
        $response = new Entity\Response();
        $response->setId($request->getId());
        $response->setJsonrpc($request->getJsonrpc());
        $response->setResult($result);

        // Return the response
        return $response;
    }

    /**
     * General response creator for exceptions
     *
     * @param  Entity\Request $request
     * @param  \Exception $e
     * @return Entity\Response
     */
    protected function createErrorFromException(Entity\Request $request, \Exception $e)
    {
        if (($e instanceof Exception\ExceptionInterface) === false) {
            $e = new Exception\InvalidRequest(
                'Internal error',
                static::INTERNAL_ERROR,
                $e
            );
        }

        // Create required objects
        $response = new Entity\Response();
        $error    = new Entity\Error();

        // Attach error the response object
        $response->setError($error);
        $response->setJsonrpc($request->getJsonrpc());

        // Set the error data
        $error->setCode($e->getCode());
        $error->setMessage($e->getMessage());

        // The error code
        $code = $e->getCode();

        // Set the request ID based on error code
        if ($code !== static::PARSE_ERROR && $code !== static::INVALID_REQUEST) {
            $response->setId($request->getId());
        }

        $data = array();

        if ($response->getId() !== null && $code !== static::METHOD_NOT_FOUND) {
            $methodName = strtolower($request->getMethod());
            $method     = $this->methods->offsetGet($methodName);

            $data['parameters'] = $method->getParameters()->getArrayCopy();
        }

        if ($response->getId() !== null) {
            $data['request'] = $request->getArrayCopy();
        }

        if ($this->debugMode === true) {
            $data['backtrace'] = $e->getTrace();
            $data['exceptions'] = array();

            while (($e = $e->getPrevious())) {
                $data['exceptions'][] = array($e->getCode() => $e->getMessage());
            }
        }

        // Set all the data
        $error->setData($data);

        return $response;
    }

    /**
     * Parse the handle class for method information
     *
     * @return void
     */
    protected function reflectHandleObject()
    {
        if ($this->reflected === true) {
            return;
        }

        // Create reflection
        $reflectionClass   = new \ReflectionClass($this->handleObject);
        $reflectionMethods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);

        // Loop through the class methods (Only public);
        foreach ($reflectionMethods as $reflectionMethod) {
            // Create method information entity
            $method = new Entity\Method();
            $method->setName($reflectionMethod->getName());

            // Get method parameters
            $reflectionParams = $reflectionMethod->getParameters();

            // Evaluate the method params
            foreach ($reflectionParams as $reflectionParam) {
                // Create parameter information entity
                $parameter = new Entity\Parameter();
                $parameter->setIndex($reflectionParam->getPosition());
                $parameter->setName($reflectionParam->getName());
                $parameter->setRequired(
                    ($reflectionParam->isDefaultValueAvailable() === false)
                );

                // Only set default value when param is optional
                if ($parameter->getRequired() === false) {
                    $parameter->setDefault($reflectionParam->getDefaultValue());
                }

                // Add the parameter to the container
                $method->addParameter($parameter);
            }

            // Add the method to the method container
            $this->methods->offsetSet(
                strtolower($method->getName()),
                $method
            );
        }

        // Mark object as reflected
        $this->reflected = true;

        // Return void
        return;
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