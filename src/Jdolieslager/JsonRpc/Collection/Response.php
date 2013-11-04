<?php
namespace Jdolieslager\JsonRpc\Collection;

/**
 * @category    Jdolieslager
 * @package     JsonRpc
 * @subpackage  Collection
 *
 * @method \Jdolieslager\JsonRpc\Entity\Response current
 * @method \Jdolieslager\JsonRpc\Entity\Response offsetGet
 */
class Response extends \ArrayIterator
{
    /**
     * Is this a batch Response?
     *
     * @return boolean
     */
    public function isBatch()
    {
        return (bool) ($this->count() > 1);
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
