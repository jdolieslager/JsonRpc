<?php
namespace Jdolieslager\JsonRpc;

/**
 * @category    Jdolieslager
 * @package     JsonRpc
 */
class HandlerReflectionMaker
{
    /**
     * All the handler reflections
     *
     * @var \ArrayIterator
     */
    protected static $reflections;

    /**
     * Holds all the exceptions of this class
     *
     * @var array
     */
    protected $exceptions = array(
        1 => 'Handler class `%s` does not exists!',
    );


    /**
     *  Constructor for Handler reflections
     *
     */
    public function __construct()
    {
        if ((static::$reflections instanceof \ArrayIterator) === false) {
            static::$reflections = new \ArrayIterator();
        }
    }

    /**
     * Reflect the handle class
     *
     * @param string $class
     * @return Collection\Method
     * @throws Exception\InvalidArgument
     */
    public function reflect($class)
    {
        if (class_exists($class) === false) {
            throw new Exception\InvalidArgument(
                sprintf($this->excepetions[1], $class),
                1
            );
        }

        // Check if we've already reflected this class
        if (static::$reflections->offsetExists($class)) {
            return static::$reflections->offsetGet($class);
        }

        // Create reflection
        $reflectionClass   = new \ReflectionClass($class);
        $reflectionMethods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);

        // Method container
        $methods = new Collection\Method();

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
            $methods->offsetSet(strtolower($method->getName()), $method);
        }

        // Cache reflection in the runtime
        self::$reflections->offsetSet($class, $methods);

        // Return the methods
        return $methods;
    }
}
