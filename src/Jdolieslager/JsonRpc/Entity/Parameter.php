<?php
namespace Jdolieslager\JsonRpc\Entity;

/**
 * @category    Jdolieslager
 * @package     JsonRpc
 * @subpackage  Entity
 */
class Parameter
{
    /**
     * @var integer
     */
    protected $index;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var boolean
     */
    protected $required;

    /**
     * @var mixed
     */
    protected $default;

    /**
     * @param integer $index
     * @return Parameter
     */
    public function setIndex($index)
    {
        $this->index = $index;

        return $this;
    }

    /**
     * @return integer
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * @param string $name
     * @return Parameter
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
     * @param boolean $required
     * @return Parameter
     */
    public function setRequired($required)
    {
        $this->required = (bool) $required;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * @param mixed $default
     * @return Parameter
     */
    public function setDefault($default)
    {
        $this->default = $default;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * Return an array copy
     *
     * @return array
     */
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}
