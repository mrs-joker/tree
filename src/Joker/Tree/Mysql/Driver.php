<?php

namespace MrsJoker\Tree\Mysql;

class Driver extends \MrsJoker\Tree\AbstractDriver
{
    /**
     * Creates new instance of driver
     *
     * @param Decoder $decoder
     * @param Encoder $encoder
     */
    public function __construct()
    {
        if ( ! $this->coreAvailable()) {
            throw new \Intervention\Image\Exception\NotSupportedException(
                "Tree module not available with this PHP Joker."
            );
        }
    }

    public function newItem($itemData)
    {
        // TODO: Implement newItem() method.
    }

    public function getItemData($id)
    {
        // TODO: Implement getItemData() method.
    }

    public function editItem($itemData)
    {
        // TODO: Implement editItem() method.
    }


    /**
     * Checks if core module installation is available
     *
     * @return boolean
     */
    protected function coreAvailable()
    {
//        return (extension_loaded('imagick') && class_exists('Imagick'));
        return true;
    }
}
