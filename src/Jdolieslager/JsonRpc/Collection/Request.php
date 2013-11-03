<?php
namespace Jdolieslager\JsonRpc\Collection;

use Jdolieslager\JsonRpc\Exception;
use Jdolieslager\JsonRpc\Entity\Request as EntityRequest;
use Jdolieslager\JsonRpc\Entity\Response as EntityResponse;

/**
 * @category    Jdolieslager
 * @package     JsonRpc
 * @subpackage  Collection
 *
 * @method EntityRequest current
 * @method EntityRequest offsetGet
 * @method void offsetSet($index, EntityRequest $newval)
 */
class Request extends \ArrayIterator
{
    protected $ids = array();

    /**
     * Is this a batch request?
     *
     * @return boolean
     */
    public function isBatch()
    {
        return (bool) ($this->count() > 1);
    }

    public function addRequest(EntityRequest $request, $callable = null)
    {
        $id = $request->getId();

        if ($id === null && $callable !== null) {
            throw new Exception\InvalidArgument(
                'Request ID is NULL. This means no response for server. Cannot ' .
                    'attach callable to this request.',
                    1
            );
        }

        if ($id !== null && array_key_exists($id, $this->ids)) {
            throw new Exception\RuntimeException(
                'Cannot add same id `' . $id . '` twice to the stack!'
            );
        }

        // Add the callable to the stack
        if ($id !== null) {
            $this->ids[$id] = $callable;
        }

        return parent::offsetSet(null, $request);
    }

    public function hasIdCallback($id)
    {
        return (bool) (array_key_exists($id, $this->ids) && $this->ids[$id] !== null);
    }

    public function performCallback(EntityResponse $response)
    {
        if ($this->hasIdCallback($response->getId()) === false) {
            throw new Exception\InvalidArgument(
                'There is no callback for id `' . $response->getId() . '`',
                1
            );
        }

        $callable = $this->ids[$response->getId()];

        call_user_func($callable, $response);
    }


    /**
     * Method will call addRequest.
     *
     * {@inheritdoc}
     */
    public function append($value)
    {
        return $this->addRequest($value);
    }

    /**
     * Method will call addRequest. The offset will be ignored
     *
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        return $this->addRequest($value);
    }

    /**
     * {@inheritdoc}
     */
    public function getArrayCopy()
    {
        $data = parent::getArrayCopy();
        foreach ($data as $index => $value) {
            $data[$index] = $value->getArrayCopy();
        }

        return $data;
    }
}
