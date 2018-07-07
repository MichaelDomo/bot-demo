<?php

namespace app\components\bot\entities;

/**
 * Class Recipient
 * @package app\components\bot\entities
 */
class Recipient
{
    public $id;
    public $name;

    /**
     * Recipient constructor.
     * @param $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }
}
