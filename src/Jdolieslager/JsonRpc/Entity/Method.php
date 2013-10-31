<?php
namespace Jdolieslager\JsonRpc\Entity;

use Jdolieslager\JsonRpc\Collection;

/**
 * @category    Jdolieslager
 * @package     JsonRpc
 * @subpackage  Entity
 */
class Method
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var Collection\Parameter
     */
    protected $parameters;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->parameters = new Collection\Parameter();
    }

    /**
     * @param string $name
     * @return Method
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param Parameter $parameter
     * @return Method
     */
    public function addParameter(Parameter $parameter)
    {
        $this->parameters->offsetSet($parameter->getIndex(), $parameter);

        return $this;
    }

    /**
     * @return Collection\Parameter
     */
    public function getParameters()
    {
        return $this->parameters;
    }
}
