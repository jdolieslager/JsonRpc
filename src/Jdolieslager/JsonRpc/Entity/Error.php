<?php
namespace Jdolieslager\JsonRpc\Entity;

/**
 * @category    Jdolieslager
 * @package     JsonRpc
 * @subpackage  Entity
 */
class Error
{
    /**
     * @var integer
     */
    protected $code;

    /**
     * @var string
     */
    protected $message;

    /**
     * @var mixed
     */
    protected $data;

    /**
     * @param integer $code
     * @return Error
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return integer
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $message
     * @return Error
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $data
     * @return Error
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Get an copy of the object as array
     *
     * @return array
     */
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}
