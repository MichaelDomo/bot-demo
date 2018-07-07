<?php

namespace app\components\bot\entities;

/**
 * Class Item
 * @package app\components\bot\entities
 */
class Item
{
    public $title;
    public $subtitle;
    public $image;
    public $price;
    public $quantity;

    /**
     * Item constructor.
     * @param $title
     * @param $subtitle
     * @param Image $image
     * @param $price
     * @param $quantity
     */
    public function __construct($title, Image $image, $price, $quantity, $subtitle = '')
    {
        $this->title = $title;
        $this->subtitle = $subtitle;
        $this->image = $image;
        $this->price = $price;
        $this->quantity = $quantity;
    }
}
