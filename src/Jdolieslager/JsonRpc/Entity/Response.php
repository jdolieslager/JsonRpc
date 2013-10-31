<?php
namespace Jdolieslager\JsonRpc\Entity;

/**
 * @category    Jdolieslager
 * @package     JsonRpc
 * @subpackage  Entity
 */
class Response
{
    const VERSION_1 = null;
    const VERSION_2 = '2.0';

    /**
     * @var string|NULL
     */
    protected $jsonrpc;

    /**
     * @var mixed
     */
    protected $result;

    /**
     * @var Error|NULL
     */
    protected $error;

    /**
     * @var string|integer|NULL
     */
    protected $id;

    /**
     * @param string|NULL $jsonrpc
     * @return Response
     */
    public function setJsonrpc($jsonrpc)
    {
        $this->jsonrpc = $jsonrpc;

        return $this;
    }

    /**
     * @return string|NULL
     */
    public function getJsonrpc()
    {
        return $this->jsonrpc;
    }

    /**
     * @param mixed $result
     * @return Response
     */
    public function setResult($result)
    {
        $this->result = $result;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param Error $error
     * @return Response
     */
    public function setError(Error $error = null)
    {
        $this->error = $error;

        return $this;
    }

    /**
     * @return Error
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param string|integer|NULL $id
     * @return Response
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string|integer|NULL
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get an array copy
     */
    public function getArrayCopy()
    {
        $vars = get_object_vars($this);

        if ($vars['error'] instanceof Error) {
            $vars['error'] = $vars['error']->getArrayCopy();
        }

        return $vars;
    }
}
