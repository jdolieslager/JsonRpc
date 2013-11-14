<?php
namespace Jdolieslager\JsonRpc;

use Jdolieslager\JsonRpc\ProtocolLayer\ProtocolLayerStack;

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

    const LAYER_PLACEMENT_TOP    = ProtocolLayerStack::PLACEMENT_TOP;
    const LAYER_PLACEMENT_BOTTOM = ProtocolLayerStack::PLACEMENT_BOTTOM;
    const LAYER_PLACEMENT_BELOW  = ProtocolLayerStack::PLACEMENT_BELOW;
    const LAYER_PLACEMENT_ABOVE  = ProtocolLayerStack::PLACEMENT_ABOVE;

    /**
     * Makes reflection of the handlers
     *
     * @var HandlerReflectionMaker
     */
    protected $reflectionMaker;

    /**
     * Holds a list of handlers
     *
     * @var \ArrayIterator
     */
    protected $handlers;

    /**
     * Holds a list of handlers instances
     *
     * @var \ArrayIterator
     */
    protected $handlerInstances;

    /**
     * @var boolean
     */
    protected $debugMode = false;

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
     * Construct the JSON RPC Server
     *
     * @param boolean $debugMode
     * @param boolean $handleFatalErrors
     * @return Server
     */
    public function __construct($debugMode = false, $handleFatalErrors = true)
    {
        $this->handlers           = new \ArrayIterator();
        $this->handlerInstances   = new \ArrayIterator();
        $this->protocolLayerStack = new ProtocolLayer\ProtocolLayerStack();
        $this->debugMode          = $debugMode;

        if ($handleFatalErrors === true) {
            // Whatever the application says. We do not print any errors. Will be
            // in JSON RPC compactible way

            ini_set('display_errors', 'off');
            set_error_handler(array($this, 'handlePhpError'), E_ALL | E_STRICT);
            register_shutdown_function(array($this, 'handleUncleanShutdown'));
        }
    }

    /**
     * Set handler
     *
     * @param string $handlerClass
     * @param string $namespace     The namespace for the methods (Example profile.)
     * @return Server
     */
    public function registerHandler($handlerClass, $namespace = 'global')
    {
        $this->handlers->offsetSet($namespace, $handlerClass);

        return $this;
    }

    /**
     * Register multiple handlers in one time
     *
     * @param array $handlers
     * @return Server
     */
    public function registerHandlers(array $handlers)
    {
        foreach ($handlers as $namespace => $handlerClass) {
            $this->registerHandler($handlerClass, $namespace);
        }

        return $this;
    }

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
     * Create Request object from Raw Request
     *
     * @param string $string
     * @return Collection\Request | Collection\Response on error
     */
    public function createRequestFromRawRequest($string)
    {
        // Process through the layer before processing
        $string = $this->protocolLayerStack->handleRequest($string);

        $collection = new Collection\Request();

        try {
            // Decode the data
            $data = @json_decode($string, true);
            
            // No array means decoding failed
            if (is_array($data) === false) {
                throw new Exception\InvalidRequest('Parse error', static::PARSE_ERROR);
            }

            // Make it a batch request
            if (Common\ArrayUtils::isAssociative($data)) {
                $data = array($data);
            }

            foreach ($data as $requestData) {
                // Always hace request object
                $request = new Entity\Request();

                // Append the request object
                $collection->append($request);

                // Next checks expects an array
                if (is_array($requestData) === false) {
                    $requestData = array();
                }

                // Get the available arguments
                $jsonRpc = $this->getArrayItem('jsonrpc', $requestData, null);
                $method  = $this->getArrayItem('method', $requestData, null);
                $params  = $this->getArrayItem('params', $requestData, array());
                $id      = $this->getArrayItem('id', $requestData, null);

                // Set the values on the request object
                $request->setJsonrpc($jsonRpc)->setId($id)->setMethod($method);

                // Set all the parameters
                foreach ($params as $offset => $value) {
                    $request->addParam($value, $offset);
                }
            }
        } catch (\Exception $e) {
            // Error occured and create
            $response = $this->createResponseFromException(new Entity\Request(), $e);

            // Create collection response object
            $responseCollection = new Collection\Response();

            // Attach the single response error (parse error);
            $responseCollection->append($response);

            // Return the response collection
            return $responseCollection;
        }

        return $collection;
    }

    /**
     * Parse a raw json encoded string to a Response object
     *
     * @param string $string    A JSON encode RPC string
     * @return Entity\Response | NULL when id is NULL
     */
    public function createResponseForRawRequest($string)
    {
        $collection = $this->createRequestFromRawRequest($string);

        // When we get an response object. An error has occured
        if (($collection instanceof Collection\Response)) {
            return $collection;
        }

        // Perform request action
        return $this->createResponseForRequest($collection);
    }

    /**
     * Create an correct JSON response based on Response object
     *
     * @param  Entity\Response $response
     * @return string
     */
    public function createRawResponseFromResponse(Collection\Response $response)
    {
        // Get the array
        $collection = $response->getArrayCopy();

        foreach ($collection as &$singleResponse) {
             // JSON RPC should be set for returning
            if ($singleResponse['jsonrpc'] === null) {
                unset($singleResponse['jsonrpc']);
            }

            // Check if we should return result or error
            if (empty($singleResponse['error']) === false) {
                unset($singleResponse['result']);
                if (empty($singleResponse['error']['data'])) {
                    unset($singleResponse['error']['data']);
                }
            } else {
                unset($singleResponse['error']);
            }
        }

        // Make it single on a non batch request
        if ($response->isBatch() === false) {
            $collection = array_shift($collection);
        }

        // Encode the remaining array
        return json_encode($collection);
    }

    /**
     * Handle the incoming request
     *
     * @param  Entity\Request $request
     * @return Collection\Response
     */
    public function createResponseForRequest(Collection\Request $requests)
    {
        $collection = new Collection\Response();

        foreach ($requests as $request) {
            try {
                $response = $this->parseRequest($request);
            } catch (\Exception $e) {
                $response = $this->createResponseFromException($request, $e);
            }

            if ($response instanceof Entity\Response) {
                $collection->append($response);
            }
        }

        return $collection;
    }

    /**
     * Parse the incoming request
     *
     * @param  Entity\Request $request
     * @return Entity\Response
     */
    protected function parseRequest(Entity\Request $request)
    {
        // Check if the method has been set
        if ($request->getMethod() === null) {
            throw new Exception\InvalidRequest('Invalid request', static::INVALID_REQUEST);
        }

        // normalize the name
        $methodName = strtolower($request->getMethod());

        $namespace = 'global';
        if (strpos($methodName, '.') !== false) {
            list($namespace, $methodName) = explode('.', $methodName, 2);
        }

        // Check if the namespace has been registered
        if ($this->handlers->offsetExists($namespace) === false) {
            throw new Exception\InvalidRequest('Method not found', static::METHOD_NOT_FOUND);
        }

        $methods = $this->getHandlerReflectionMaker()->reflect(
            $this->handlers->offsetGet($namespace)
        );

        // Check if the method exists
        if ($methods->offsetExists($methodName) === false) {
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
        $method    = $methods->offsetGet($methodName);
        $handler   = $this->getHandler($namespace);

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
        $result = call_user_func_array(array($handler, $method->getName()), $arguments);

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
    public function createResponseFromException(Entity\Request $request, \Exception $e)
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

            $namespace = 'global';
            if (strpos($methodName, '.') !== false) {
                list($namespace, $methodName) = explode('.', $methodName, 2);
            }

            if ($this->handlers->offsetExists($namespace)) {
                $methods = $this->getHandlerReflectionMaker()->reflect(
                    $this->handlers->offsetGet($namespace)
                );

                if ($methods->offsetExists($methodName)) {
                    $method = $methods->offsetGet($methodName);

                    $data['parameters'] = $method->getParameters()->getArrayCopy();
                }
            }
        }

        // In debug mode we print more data
        if ($this->debugMode === true) {
            $data['exceptions'] = array();
            $data['backtrace']  = $e->getTrace();

            while (null !== ($e = $e->getPrevious())) {
                $data['exceptions'][] = array($e->getCode() => $e->getMessage());
            }
        }

        // Set all the data
        $error->setData($data);

        return $response;
    }

    /**
     * Print string to the ouput stream
     *
     * @param Entity\Response | NULL $response  On NULL print No Content
     * @return void
     */
    public function printResponse(Collection\Response $response = null)
    {
        // Response NULL means no content
        if ($response->count() === 0) {
            if (!headers_sent()) {
                header('No Content', null, 204);
            }

            return;
        }

        // Set HTTP headers
        if (!headers_sent()) {
            header('OK', null, 200);
            header('Content-Type: application/json');
            //@TODO correct status code
        }

        // print json encoded string
        $response = $this->createRawResponseFromResponse($response);
        $response = $this->protocolLayerStack->handleResponse($response);

        echo $response;
    }

    /**
     * Print JSON encoded string directly from raw request
     *
     * @param string $string
     * @return void
     */
    public function printResponseForRawRequest($string)
    {
        return $this->printResponse($this->createResponseForRawRequest($string));
    }

    /**
     * Print JSON encoded string directly from a request object
     *
     * @param Entity\Request $request
     * @return void
     */
    public function printResponseForRequest(Entity\Request $request)
    {
        return $this->printResponse($this->createResponseForRequest($request));
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

    /**
     * Handle any PHP error. IT will return error code when something goes wrong.
     * Script will be terminated
     *
     * @param integer $code
     * @param string $message
     * @param string $file
     * @param string $line
     * @param mixed $context
     * @return void
     */
    public function handlePhpError($code, $message, $file = null, $line = null, $context= null)
    {
        // Create response object
        $response = $this->createResponseFromException(
            new Entity\Request(),
            new \Exception(
                $message,
                $code
            )
        );

        $collection = new Collection\Response();
        $collection->append($response);

        // Set response
        $this->printResponse($collection);

        // No use to go further stop the execution
        exit;
    }

    /**
     * Check if any errors occured (Eg. fatal errors)
     *
     * @return void
     */
    public function handleUncleanShutdown()
    {
        $last = error_get_last();
        if (empty($last)) {
            return;
        }

        $code = $this->getArrayItem('type', $last, 1);
        $message = $this->getArrayItem('message', $last, '');
        $line = $this->getArrayItem('line', $last, null);
        $file = $this->getArrayItem('file', $last, null);

        // Handle php error
        return $this->handlePhpError($code, $message, $file, $line);
    }

    /**
     * Reflects handler objects
     *
     * @return HandlerReflectionMaker
     */
    protected function getHandlerReflectionMaker()
    {
        if ($this->reflectionMaker === null) {
            $this->reflectionMaker = new HandlerReflectionMaker();
        }

        return $this->reflectionMaker;
    }

    /**
     * Get the handler based on the namespace
     *
     * @param string $namespace
     * @return object
     * @throws Exception\ArgumentException
     */
    protected function getHandler($namespace)
    {
        // Chack if namespace has been registered
        if ($this->handlers->offsetExists($namespace) === false) {
            throw new Exception\InvalidArgument(
                sprintf($this->exceptions[1], $namespace),
                1
            );
        }

        // Create instance when needed
        if ($this->handlerInstances->offsetExists($namespace) === false) {
            $handler = $this->handlers->offsetGet($namespace);
            $this->handlerInstances->offsetSet($namespace, new $handler);
        }

        // Return instance
        return $this->handlerInstances->offsetGet($namespace);
    }
}
