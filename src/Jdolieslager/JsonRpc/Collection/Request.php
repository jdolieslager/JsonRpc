<?php
namespace Jdolieslager\JsonRpc\Collection;

/**
 * @category    Jdolieslager
 * @package     JsonRpc
 * @subpackage  Collection
 *
 * @method \Jdolieslager\JsonRpc\Entity\Request current
 * @method \Jdolieslager\JsonRpc\Entity\Request offsetGet
 */
class Request extends \ArrayIterator
{
    /**
     * Is this a batch request?
     *
     * @return boolean
     */
    public function isBatch()
    {
        return (bool) ($this->count() > 1);
    }
}
