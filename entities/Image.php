<?php

namespace app\components\bot\entities;

/**
 * Class Image
 * @package app\components\bot\entities
 */
class Image
{
    public $url;

    /**
     * Button constructor.
     * @param $url
     */
    public function __construct($url)
    {
        $this->url = $url;
    }
}
