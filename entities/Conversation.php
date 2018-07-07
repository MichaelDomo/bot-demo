<?php

namespace app\components\bot\entities;

/**
 * Class Conversation
 * @package app\components\bot\entities
 */
class Conversation
{
    public $id;

    /**
     * Conversation constructor.
     * @param $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }
}
