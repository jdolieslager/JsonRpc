<?php
namespace Jdolieslager\JsonRpc\Entity;

/**
 * The request object for JSON RPC requests
 *
 * @category    Jdolieslager
 * @package     JsonRpc
 * @subpackage  Entity
 */
class Request
{
    const VERSION_1 = null;
    const VERSION_2 = '2.0';

    /**
     * @var NULL | string
     */
    protected $jsonrpc;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var \ArrayIterator
     */
    protected $params;

    /**
     * @var integer|string|NULL
     */
    protected $id;

    /**
     * @param string | NULL $jsonrpc
     * @return Request
     */
    public function setJsonrpc($jsonrpc)
    {
        $this->jsonrpc = $jsonrpc;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getJsonrpc()
    {
        return $this->jsonrpc;
    }

    /**
     * @param string $method
     * @return Request
     */
    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Add parameter
     *
     * @param string $value
     * @param string $offset
     * @return Request
     */
    public function addParam($value, $offset = null)
    {
        if ($offset === null) {
            $this->getParams()->append($value);
        } else {
            $this->getParams()->offsetSet($offset, $value);
        }

        return $this;
    }

    /**
     * @return \ArrayIterator
     */
    public function getParams()
    {
        if ($this->params === null) {
            $this->params = new \ArrayIterator();
        }

        return $this->params;
    }

    /**
     * @param integer|string|NULL $id
     * @return Request
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return integer|string|NULL
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get an array of the object
     *
     * @return array
     */
    public function getArrayCopy()
    {
        $return           = get_object_vars($this);
        $return['params'] = $this->getParams()->getArrayCopy();

        return $return;
    }
}
