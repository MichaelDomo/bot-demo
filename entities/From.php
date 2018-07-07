<?php

namespace app\components\bot\entities;

/**
 * Class From
 * @package app\components\bot\entities
 */
class From
{
    public $id;
    public $name;

    /**
     * From constructor.
     * @param $id
     * @param string $name
     */
    public function __construct($id, $name)
    {
        $this->id = $id;
        $this->name = $name;
    }
}
